<?php

declare(strict_types=1);

namespace Vortech\Stash\Contracts;

interface Driver
{
    /**
     * Returns the stored values of the given store, or an empty array if it does not exist.
     *
     * @return array<array-key, mixed>
     */
    public function read(string $store): array;

    /**
     * Replaces the stored values of the given store.
     *
     * @param  array<array-key, mixed>  $values
     */
    public function write(string $store, array $values): void;

    public function exists(string $store): bool;

    public function delete(string $store): void;
}
