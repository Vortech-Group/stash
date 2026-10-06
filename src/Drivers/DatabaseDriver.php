<?php

declare(strict_types=1);

namespace Vortech\Stash\Drivers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use JsonException;
use RuntimeException;
use Vortech\Stash\Contracts\Driver;

final readonly class DatabaseDriver implements Driver
{
    public function __construct(
        private ConnectionInterface $connection,
        private string $table = 'stash',
    ) {}

    public function read(string $store): array
    {
        $json = $this->query()->where('name', $store)->value('value');

        if (! is_string($json) || blank($json)) {
            return [];
        }

        try {
            $values = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Stash [{$store}] contains invalid JSON.", previous: $e);
        }

        return is_array($values) ? $values : [];
    }

    /**
     * @throws JsonException
     */
    public function write(string $store, array $values): void
    {
        $now = now();

        $this->query()->upsert(
            [[
                'name' => $store,
                'value' => json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['name'],
            ['value', 'updated_at'],
        );
    }

    public function exists(string $store): bool
    {
        return $this->query()->where('name', $store)->exists();
    }

    public function delete(string $store): void
    {
        $this->query()->where('name', $store)->delete();
    }

    private function query(): Builder
    {
        return $this->connection->table($this->table);
    }
}
