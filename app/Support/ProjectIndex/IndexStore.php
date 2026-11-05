<?php

namespace App\Support\ProjectIndex;

use RuntimeException;

final class IndexStore
{
    public const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    public function __construct(
        private readonly string $path,
    ) {}

    public function path(): string
    {
        return $this->path;
    }

    public function ensureDirectory(): void
    {
        if (! is_dir($this->path) && ! mkdir($this->path, 0755, true) && ! is_dir($this->path)) {
            throw new RuntimeException("Unable to create project index directory: {$this->path}");
        }
    }

    public function exists(): bool
    {
        return is_file($this->path.DIRECTORY_SEPARATOR.'metadata.json')
            && is_file($this->path.DIRECTORY_SEPARATOR.'index.json');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function write(string $filename, array $data): void
    {
        $this->ensureDirectory();
        $target = $this->path.DIRECTORY_SEPARATOR.$filename;
        $json = json_encode($data, self::JSON_FLAGS);

        if ($json === false) {
            throw new RuntimeException("Unable to encode {$filename}");
        }

        if (file_put_contents($target, $json.PHP_EOL) === false) {
            throw new RuntimeException("Unable to write {$target}");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function read(string $filename): array
    {
        $target = $this->path.DIRECTORY_SEPARATOR.$filename;

        if (! is_file($target)) {
            throw new RuntimeException("Missing index artifact: {$filename}");
        }

        $raw = file_get_contents($target);

        if ($raw === false) {
            throw new RuntimeException("Unable to read {$target}");
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid JSON in {$filename}");
        }

        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    public function readOrEmpty(string $filename): array
    {
        if (! is_file($this->path.DIRECTORY_SEPARATOR.$filename)) {
            return [];
        }

        return $this->read($filename);
    }
}
