<?php

declare(strict_types=1);

namespace Vortech\Stash;

use Closure;
use Countable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use Vortech\Stash\Contracts\Driver;

final readonly class Stash implements Countable
{
    public function __construct(
        private Driver $driver,
        private string $name = 'default',
    ) {
        // The name ends up in file names and queries, so keep it boring.
        if (! preg_match('/^[A-Za-z0-9_-]+(\.[A-Za-z0-9_-]+)*$/', $name)) {
            throw new InvalidArgumentException(
                "Invalid stash name [{$name}]. Use letters, numbers, dashes, underscores and dots only."
            );
        }
    }

    /**
     * @param  array<array-key, mixed>|null  $values
     */
    public static function make(string $name = 'default', ?array $values = null, ?string $driver = null): self
    {
        $stash = app(StashManager::class)->store($name, $driver);

        if ($values !== null) {
            $stash->put($values);
        }

        return $stash;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function driver(): Driver
    {
        return $this->driver;
    }

    /**
     * Whether anything is persisted for this stash.
     */
    public function exists(): bool
    {
        return $this->driver->exists($this->name);
    }

    /**
     * Stores a value (dot notation is supported), or an array of key => value pairs.
     *
     * @param  array<array-key, mixed>|string  $key
     */
    public function put(array|string $key, mixed $value = null): self
    {
        $pairs = is_array($key) ? $key : [$key => $value];

        if ($pairs === []) {
            return $this;
        }

        $values = $this->all();

        foreach ($pairs as $k => $v) {
            Arr::set($values, (string) $k, $v);
        }

        return $this->save($values);
    }

    /**
     * Appends to a list. Existing scalar values are turned into a list first.
     */
    public function push(string $key, mixed $value): self
    {
        $current = Arr::wrap($this->get($key));

        return $this->put($key, [...$current, ...Arr::wrap($value)]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    /**
     * Gets a value, or stores and returns the callback result when it is missing.
     */
    public function remember(string $key, Closure $callback): mixed
    {
        $values = $this->all();

        if (Arr::has($values, $key)) {
            return Arr::get($values, $key);
        }

        $value = $callback();

        $this->put($key, $value);

        return $value;
    }

    /**
     * @return Fluent<array-key, mixed>
     */
    public function fluent(string $key, mixed $default = null): Fluent
    {
        return new Fluent(Arr::wrap($this->get($key, $default)));
    }

    public function has(string $key): bool
    {
        return Arr::has($this->all(), $key);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->driver->read($this->name);
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function collect(): Collection
    {
        return new Collection($this->all());
    }

    /**
     * @param  string|array<int, string>  $keys
     */
    public function forget(string|array $keys): self
    {
        $values = $this->all();

        Arr::forget($values, $keys);

        return $this->save($values);
    }

    public function flush(): self
    {
        $this->driver->delete($this->name);

        return $this;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);

        $this->forget($key);

        return $value;
    }

    public function increment(string $key, int|float $by = 1): int|float
    {
        $current = $this->get($key) ?? 0;

        if (! is_int($current) && ! is_float($current)) {
            throw new InvalidArgumentException("Stash value [{$key}] is not a number.");
        }

        $new = $current + $by;

        $this->put($key, $new);

        return $new;
    }

    public function decrement(string $key, int|float $by = 1): int|float
    {
        return $this->increment($key, -$by);
    }

    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @param  array<array-key, mixed>  $values
     */
    private function save(array $values): self
    {
        if ($values === []) {
            return $this->flush();
        }

        $this->driver->write($this->name, $values);

        return $this;
    }
}
