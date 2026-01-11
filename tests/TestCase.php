<?php

namespace Vortech\Stash\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Vortech\Stash\Providers\StashServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            StashServiceProvider::class
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('stash.path', __DIR__.'/temp');
    }
}