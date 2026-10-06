<?php

declare(strict_types=1);

use Vortech\Stash\Contracts\Driver;
use Vortech\Stash\Drivers\ArrayDriver;
use Vortech\Stash\Drivers\FileDriver;

dataset('drivers', [
    'array' => fn () => new ArrayDriver,
    'file' => fn () => new FileDriver(sys_get_temp_dir().'/stash-unit-'.uniqid()),
]);

it('reads nothing from a missing store', function (Driver $driver) {
    expect($driver->read('missing'))->toBe([])
        ->and($driver->exists('missing'))->toBeFalse();
})->with('drivers');

it('writes, reads and deletes a store', function (Driver $driver) {
    $driver->write('store', ['a' => 1, 'b' => ['c' => 'é']]);

    expect($driver->exists('store'))->toBeTrue()
        ->and($driver->read('store'))->toBe(['a' => 1, 'b' => ['c' => 'é']]);

    $driver->delete('store');

    expect($driver->exists('store'))->toBeFalse();
})->with('drivers');

it('builds the file path from the directory and name', function () {
    expect((new FileDriver('/tmp/dir/'))->path('name'))->toBe('/tmp/dir/name.json');
});
