<?php

declare(strict_types=1);

namespace Vortech\Stash\Drivers;

use Vortech\Stash\Contracts\Driver;

final class ArrayDriver implements Driver
{
    /** @var array<string, array<string, mixed>> */
    private array $stores = [];

    public function read(string $store): array
    {
        return $this->stores[$store] ?? [];
    }

    public function write(string $store, array $values): void
    {
        $this->stores[$store] = $values;
    }

    public function exists(string $store): bool
    {
        return isset($this->stores[$store]);
    }

    public function delete(string $store): void
    {
        unset($this->stores[$store]);
    }
}
