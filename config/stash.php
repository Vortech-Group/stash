<?php

declare(strict_types=1);

return [
    /*
    | The driver used when none is given: file, database or array.
    | Custom drivers can be added with Stash::extend('name', fn ($app) => new MyDriver).
    */
    'default' => env('STASH_DRIVER', 'file'),

    'drivers' => [
        'file' => [
            'path' => env('STASH_PATH', storage_path('stash')),
        ],

        'database' => [
            'connection' => env('STASH_DB_CONNECTION'),
            'table' => env('STASH_DB_TABLE', 'stash'),
        ],

        'array' => [],
    ],
];
