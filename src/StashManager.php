<?php

declare(strict_types=1);

namespace Vortech\Stash;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Manager;
use UnexpectedValueException;
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
        return $this->config->string('stash.default', 'file');
    }

    /**
     * Gets a named stash, optionally on a specific driver.
     */
    public function store(string $name = 'default', ?string $driver = null): Stash
    {
        $resolved = parent::driver($driver);

        if (! $resolved instanceof Driver) {
            throw new UnexpectedValueException(
                'Stash driver ['.($driver ?? $this->getDefaultDriver()).'] must implement '.Driver::class.'.'
            );
        }

        return new Stash($resolved, $name);
    }

    /**
     * Gets a stash on the given driver: Stash::driver('database')->increment('visits').
     *
     * @param  string|null  $driver
     */
    public function driver($driver = null, string $name = 'default'): Stash
    {
        return $this->store($name, $driver);
    }

    protected function createFileDriver(): Driver
    {
        return new FileDriver(
            $this->config->string('stash.drivers.file.path', storage_path('stash'))
        );
    }

    /**
     * @throws BindingResolutionException
     */
    protected function createDatabaseDriver(): Driver
    {
        $connection = $this->config->get('stash.drivers.database.connection');

        return new DatabaseDriver(
            $this->container->make('db')->connection(is_string($connection) ? $connection : null),
            $this->config->string('stash.drivers.database.table', 'stash'),
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
