<?php

declare(strict_types=1);

use Vortech\Stash\Stash;

it('can be called by helper', function () {
    $stash = stash()->put('test', 'value');

    expect($stash->get('test'))->toBe('value');
});

it('can be called statically', function () {
    $stash = Stash::make()->put('test', 'value');

    expect($stash->get('test'))->toBe('value');
});

it('can be called statically with a file name', function () {
    $stash = Stash::make('test')->put('test', 'value');

    expect($stash->get('test'))->toBe('value');
});

it('can be called statically with values', function () {
    $stash = Stash::make('test', ['test' => 'value']);

    expect($stash->get('test'))->toBe('value');
});

it('can store arrays', function () {
    $array = ['one' => 1, 'two' => 2];

    $stash = Stash::make()->put('array', $array);

    expect($stash->get('array'))->toBe($array);
});

it('can unlink the created file', function () {
    $stash = stash()->put('test', 'value');

    expect($stash->get('test'))->toBe('value');

    $stash->flush();

    expect($stash->path())->toBeNull();
});