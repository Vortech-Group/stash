<?php

declare(strict_types=1);

namespace Vortech\Stash\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vortech\Stash\Providers\StashServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            StashServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('stash.drivers.file.path', __DIR__ . '/temp');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        (require __DIR__ . '/../database/migrations/create_stash_table.php.stub')->up();
    }

    protected function tearDown(): void
    {
        foreach (glob(__DIR__ . '/temp/*.json') ?: [] as $file) {
            unlink($file);
        }

        parent::tearDown();
    }
}
