<?php

declare(strict_types=1);

namespace Vortech\Stash\Drivers;

use JsonException;
use Random\RandomException;
use RuntimeException;
use Vortech\Stash\Contracts\Driver;

final readonly class FileDriver implements Driver
{
    public function __construct(private string $directory)
    {
    }

    public function path(string $store): string
    {
        return rtrim($this->directory, '/\\') . DIRECTORY_SEPARATOR . $store . '.json';
    }

    public function read(string $store): array
    {
        $path = $this->path($store);

        if (! is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read stash file [{$path}].");
        }

        try {
            $values = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Stash file [{$path}] contains invalid JSON.", previous: $e);
        }

        return is_array($values) ? $values : [];
    }

    /**
     * @throws RandomException
     * @throws JsonException
     */
    public function write(string $store, array $values): void
    {
        $path = $this->path($store);

        if (! is_dir($this->directory) && ! mkdir($this->directory, 0755, true) && ! is_dir($this->directory)) {
            throw new RuntimeException("Unable to create stash directory [{$this->directory}].");
        }

        $json = json_encode($values, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Write to a temp file first, then rename, so readers never see a half-written file.
        $temp = $path . '.' . bin2hex(random_bytes(6)) . '.tmp';

        if (file_put_contents($temp, $json, LOCK_EX) === false || ! rename($temp, $path)) {
            @unlink($temp);

            throw new RuntimeException("Unable to write stash file [{$path}].");
        }
    }

    public function exists(string $store): bool
    {
        return is_file($this->path($store));
    }

    public function delete(string $store): void
    {
        $path = $this->path($store);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
