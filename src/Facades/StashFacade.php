<?php

declare(strict_types=1);

namespace Vortech\Stash\Facades;

use Illuminate\Support\Facades\Facade;
use Vortech\Stash\Stash;
use Vortech\Stash\StashManager;

/**
 * @method static Stash store(string $name = 'default', ?string $driver = null)
 * @method static Stash driver(?string $driver = null, string $name = 'default')
 * @method static StashManager extend(string $driver, \Closure $callback)
 * @method static Stash put(array|string $key, mixed $value = null)
 * @method static Stash push(string $key, mixed $value)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static mixed remember(string $key, \Closure $callback)
 * @method static bool has(string $key)
 * @method static array all()
 * @method static Stash forget(string|array $keys)
 * @method static Stash flush()
 * @method static mixed pull(string $key, mixed $default = null)
 * @method static int|float increment(string $key, int|float $by = 1)
 * @method static int|float decrement(string $key, int|float $by = 1)
 * @method static int count()
 *
 * @see StashManager
 * @see Stash
 */
final class StashFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'stash';
    }
}
