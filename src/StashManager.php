<?php

declare(strict_types=1);

namespace Vortech\Stash;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;
use Vortech\Stash\Contracts\Driver;
use Vortech\Stash\Drivers\ArrayDriver;
use Vortech\Stash\Drivers\DatabaseDriver;
use Vortech\Stash\Drivers\FileDriver;

/**
 * @mixin Stash
 */
class StashManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('stash.default', 'file');
    }

    /**
     * Gets a named stash, optionally on a specific driver.
     */
    public function store(string $name = 'default', ?string $driver = null): Stash
    {
        return new Stash(parent::driver($driver), $name);
    }

    /**
     * Gets a stash on the given driver: Stash::driver('database')->increment('visits').
     */
    public function driver($driver = null, string $name = 'default'): Stash
    {
        return $this->store($name, $driver);
    }

    protected function createFileDriver(): Driver
    {
        return new FileDriver(
            $this->config->get('stash.drivers.file.path') ?? storage_path('stash')
        );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createDatabaseDriver(): Driver
    {
        return new DatabaseDriver(
            $this->container->make('db')->connection($this->config->get('stash.drivers.database.connection')),
            $this->config->get('stash.drivers.database.table', 'stash'),
        );
    }

    protected function createArrayDriver(): Driver
    {
        return new ArrayDriver;
    }

    /**
     * Calls on the default stash of the default driver.
     */
    public function __call($method, $parameters): mixed
    {
        return $this->store()->$method(...$parameters);
    }
}
