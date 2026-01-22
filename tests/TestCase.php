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
            StashServiceProvider::class
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('stash.path', __DIR__.'/temp');
    }
}