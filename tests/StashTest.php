<?php

declare(strict_types=1);

use Vortech\Stash\Contracts\Driver;
use Vortech\Stash\Drivers\ArrayDriver;
use Vortech\Stash\Drivers\DatabaseDriver;
use Vortech\Stash\Drivers\FileDriver;
use Vortech\Stash\Facades\StashFacade;
use Vortech\Stash\Stash;

it('can be called by helper', function () {
    expect(stash()->put('test', 'value')->get('test'))->toBe('value');
});

it('can be called statically', function () {
    expect(Stash::make()->put('test', 'value')->get('test'))->toBe('value');
});

it('can be called through the facade on the default stash', function () {
    StashFacade::put('test', 'value');

    expect(StashFacade::get('test'))->toBe('value')
        ->and(StashFacade::store()->get('test'))->toBe('value');
});

it('can be called with a name and values', function () {
    $stash = Stash::make('test', ['test' => 'value']);

    expect($stash->get('test'))->toBe('value')
        ->and(stash('test')->get('test'))->toBe('value');
});

it('keeps stashes separate', function () {
    stash('a')->put('key', 1);
    stash('b')->put('key', 2);

    expect(stash('a')->get('key'))->toBe(1)
        ->and(stash('b')->get('key'))->toBe(2);
});

it('rejects unsafe names', function (string $name) {
    stash($name);
})->with(['../evil', 'a/b', '', 'a..b', '.hidden'])->throws(InvalidArgumentException::class);

it('uses the file driver by default and creates the directory', function () {
    $dir = sys_get_temp_dir() . '/stash-' . uniqid();
    config()->set('stash.drivers.file.path', $dir);
    app('stash')->forgetDrivers();

    stash('nested')->put('a', 1);

    expect(file_get_contents("{$dir}/nested.json"))->toBe('{"a":1}');

    unlink("{$dir}/nested.json");
    rmdir($dir);
});

it('throws on corrupted files instead of losing data', function () {
    file_put_contents(__DIR__ . '/temp/broken.json', '{nope');

    stash('broken')->all();
})->throws(RuntimeException::class);

it('removes the file when flushed or emptied', function () {
    $stash = stash('gone')->put('test', 'value');

    expect($stash->exists())->toBeTrue();

    $stash->forget('test');

    expect($stash->exists())->toBeFalse()
        ->and(file_exists(__DIR__ . '/temp/gone.json'))->toBeFalse();
});

it('supports dot notation', function () {
    $stash = stash()->put('user.name', 'Mate')->put('user.age', 30);

    expect($stash->get('user'))->toBe(['name' => 'Mate', 'age' => 30])
        ->and($stash->has('user.name'))->toBeTrue()
        ->and($stash->forget('user.age')->get('user'))->toBe(['name' => 'Mate']);
});

it('pushes values to a list', function () {
    $stash = stash();

    $stash->push('list', 1)->push('list', [2, 3]);
    $stash->put('scalar', 'a')->push('scalar', 'b');

    expect($stash->get('list'))->toBe([1, 2, 3])
        ->and($stash->get('scalar'))->toBe(['a', 'b']);
});

it('pulls, counts and remembers', function () {
    $stash = stash()->put(['a' => 1, 'b' => 2]);

    expect($stash->count())->toBe(2)
        ->and($stash->pull('a'))->toBe(1)
        ->and($stash->has('a'))->toBeFalse()
        ->and($stash->remember('c', fn () => 'computed'))->toBe('computed')
        ->and($stash->remember('c', fn () => 'other'))->toBe('computed');
});

it('increments and decrements', function () {
    $stash = stash();

    expect($stash->increment('n'))->toBe(1)
        ->and($stash->increment('n', 4))->toBe(5)
        ->and($stash->decrement('n', 2))->toBe(3)
        ->and($stash->increment('f', 0.5))->toBe(0.5);
});

it('refuses to increment non numbers', function () {
    stash()->put('n', 'text')->increment('n');
})->throws(InvalidArgumentException::class);

it('can switch driver per stash', function () {
    stash('mem', driver: 'array')->put('test', 'value');

    expect(stash('mem', driver: 'array')->get('test'))->toBe('value')
        ->and(stash('mem')->has('test'))->toBeFalse()
        ->and(StashFacade::store('mem', 'array')->get('test'))->toBe('value');
});

it('can pick the driver through the facade', function () {
    expect(StashFacade::driver('database')->increment('visits'))->toBe(1)
        ->and(StashFacade::driver('database')->increment('visits'))->toBe(2)
        ->and(StashFacade::driver('database', 'stats')->put('a', 1)->name())->toBe('stats')
        ->and(DB::table('stash')->pluck('name')->all())->toBe(['default', 'stats'])
        ->and(StashFacade::has('visits'))->toBeFalse();
});

it('can use the database driver', function () {
    $stash = stash('db', ['a' => 1], 'database');

    $stash->put('b', ['x' => 'é'])->put('a', 2);

    expect($stash->all())->toBe(['a' => 2, 'b' => ['x' => 'é']])
        ->and(DB::table('stash')->count())->toBe(1);

    $stash->flush();

    expect($stash->exists())->toBeFalse()
        ->and(DB::table('stash')->count())->toBe(0);
});

it('can use the database driver as default', function () {
    config()->set('stash.default', 'database');

    stash('x')->put('k', 'v');

    expect(DB::table('stash')->where('name', 'x')->exists())->toBeTrue();
});

it('supports custom drivers', function () {
    StashFacade::extend('custom', fn () => new ArrayDriver);

    expect(stash('c', ['a' => 1], 'custom')->get('a'))->toBe(1);
});

it('exposes the driver implementations', function () {
    expect(Stash::make()->driver())->toBeInstanceOf(FileDriver::class)
        ->and(Stash::make(driver: 'database')->driver())->toBeInstanceOf(DatabaseDriver::class)
        ->and(Stash::make(driver: 'array')->driver())->toBeInstanceOf(Driver::class);
});
