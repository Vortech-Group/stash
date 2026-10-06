<?php

declare(strict_types=1);

use Vortech\Stash\Stash;

if (! function_exists('stash')) {
    /**
     * @param array<string, mixed>|null $values
     */
    function stash(string $name = 'default', ?array $values = null, ?string $driver = null): Stash
    {
        return Stash::make($name, $values, $driver);
    }
}
