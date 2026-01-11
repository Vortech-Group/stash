<?php

declare(strict_types=1);

namespace Vortech\Stash;

use Countable;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

readonly class Stash implements Countable
{
    protected string $fileName;

    protected string $path;

    public static function make(string $fileName = 'default', array|null $values = null): static
    {
        $stash = (new static())->init($fileName);

        if (! is_null($values)) {
            $stash->put($values);
        }

        return $stash;
    }

    protected function init(string $fileName): static
    {
        $this->fileName = Str::of($fileName)->append('.json')->toString();

        $this->path = Str::of(config('stash.path'))->append('/')->append($this->fileName)->toString();

        return $this;
    }

    public function put(array|string $name, mixed $value = null): static
    {
        if ($name === []) {
            return $this;
        }

        $newValues = $name;

        if (! is_array($name)) {
            $newValues = [$name => $value];
        }

        $newContent = array_merge($this->all()->toArray(), $newValues);

        $this->setContent($newContent);

        return $this;
    }

    public function push(string $name, mixed $value): static
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        if (! $this->has($name)) {
            $this->put($name, $value);

            return $this;
        }

        $oldValue = $this->get($name);

        if (! is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        $newValue = array_merge($oldValue, $value);

        $this->put($name, $newValue);

        return $this;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->all()->get($name, $default);
    }

    public function fluent(string $name, mixed $default = null): Fluent
    {
        return fluent($this->get($name, $default));
    }

    public function has(string $name): bool
    {
        return $this->all()->has($name);
    }

    public function all(): Fluent
    {
        if (! file_exists($this->path)) {
            return fluent([]);
        }

        return fluent(json_decode(file_get_contents($this->path), true));
    }

    public function forget(string $key): static
    {
        $content = $this->all()->toArray();

        unset($content[$key]);

        $this->setContent($content);

        return $this;
    }

    public function flush(): static
    {
        return $this->setContent([]);
    }

    public function pull(string $name): mixed
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    public function increment(string $name, int $by = 1): mixed
    {
        $currentValue = $this->get($name) ?? 0;

        if (! $this->isNumber($currentValue)) {
            return $currentValue;
        }

        $newValue = $currentValue + $by;

        $this->put($name, $newValue);

        return $newValue;
    }

    public function decrement(string $name, int $by = 1): mixed
    {
        return $this->increment($name, $by * -1);
    }

    public function count(): int
    {
        return $this->all()->collect()->count();
    }

    protected function isNumber($value): bool
    {
        return is_int($value) || is_float($value);
    }

    protected function setContent(array $values): static
    {
        file_put_contents($this->path, json_encode($values));

        if (! count($values)) {
            unlink($this->path);
        }

        return $this;
    }
}
