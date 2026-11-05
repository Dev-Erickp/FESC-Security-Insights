<?php

namespace App\Support\ProjectIndex;

use RuntimeException;

/**
 * Generates a real filesystem map of the repository for agent context.
 */
final class CodebaseMapper
{
    /**
     * @var list<string>
     */
    private const EXCLUDED_DIRS = [
        '.git',
        'vendor',
        'node_modules',
        'storage/framework',
        'storage/logs',
        'storage/pail',
        'bootstrap/cache',
        'public/build',
        'public/hot',
        '.idea',
        '.vscode',
        '.fleet',
        '.zed',
        '.agents',
        '.amp',
        '.claude',
        '.codex',
        '.factory',
        '.grok',
        '.junie',
        '.kiro',
        '.pi',
    ];

    /**
     * @var list<string>
     */
    private const EXCLUDED_FILES = [
        '.env',
        '.env.backup',
        '.env.production',
        'auth.json',
        '.project/fingerprints.json',
    ];

    public function __construct(
        private readonly string $repoRoot,
    ) {}

    /**
     * @return array{path: string, directories: int, files: int}
     */
    public function generate(string $relativeOutput = 'docs/AI/CODEBASE_MAP.md'): array
    {
        $tree = $this->buildTree($this->repoRoot, '');
        $counts = $this->countTree($tree);

        $markdown = $this->renderMarkdown($tree, $counts['directories'], $counts['files']);
        $target = $this->resolveOutputPath($relativeOutput);
        $dir = dirname($target);

        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException("Unable to create {$dir}");
        }

        if (file_put_contents($target, $markdown) === false) {
            throw new RuntimeException("Unable to write {$target}");
        }

        return [
            'path' => str_replace('\\', '/', $relativeOutput),
            'directories' => $counts['directories'],
            'files' => $counts['files'],
        ];
    }

    private function resolveOutputPath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        return $this->absolute($path);
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTree(string $absolute, string $relative): array
    {
        $entries = scandir($absolute) ?: [];
        $dirs = [];
        $files = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $childRelative = $relative === '' ? $entry : $relative.'/'.$entry;
            $normalized = str_replace('\\', '/', $childRelative);

            if ($this->isExcluded($normalized)) {
                continue;
            }

            $childAbsolute = $absolute.DIRECTORY_SEPARATOR.$entry;

            if (is_dir($childAbsolute)) {
                $dirs[$entry] = $this->buildTree($childAbsolute, $normalized);
            } elseif (is_file($childAbsolute)) {
                $files[] = $entry;
            }
        }

        ksort($dirs);
        sort($files);

        return [
            'dirs' => $dirs,
            'files' => $files,
        ];
    }

    /**
     * @param  array<string, mixed>  $tree
     * @return array{directories: int, files: int}
     */
    private function countTree(array $tree): array
    {
        $directories = 0;
        $files = count($tree['files'] ?? []);

        foreach ($tree['dirs'] ?? [] as $child) {
            $directories++;
            $childCounts = $this->countTree($child);
            $directories += $childCounts['directories'];
            $files += $childCounts['files'];
        }

        return compact('directories', 'files');
    }

    /**
     * @param  array<string, mixed>  $tree
     */
    private function renderMarkdown(array $tree, int $directories, int $files): string
    {
        $generatedAt = gmdate('c');
        $lines = [
            '# Codebase Map',
            '',
            'Árbol **real** del repositorio. Generado automáticamente. No inventa módulos inexistentes.',
            '',
            '- Generado: `'.$generatedAt.'`',
            '- Directorios: `'.$directories.'`',
            '- Archivos: `'.$files.'`',
            '- Comando: `php artisan project:map`',
            '',
            'Excluye: `vendor/`, `node_modules/`, `.git/`, `storage/framework/`, secretos (`.env`), temporales y cachés.',
            '',
            '```text',
            'FESC Security Insights/',
        ];

        $lines = array_merge($lines, $this->renderTreeLines($tree, ''));
        $lines[] = '```';
        $lines[] = '';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $tree
     * @return list<string>
     */
    private function renderTreeLines(array $tree, string $prefix): array
    {
        $lines = [];
        $dirs = $tree['dirs'] ?? [];
        $files = $tree['files'] ?? [];
        $names = array_merge(array_keys($dirs), $files);
        $total = count($names);

        foreach ($names as $index => $name) {
            $isLast = $index === $total - 1;
            $branch = $isLast ? '└── ' : '├── ';
            $isDir = array_key_exists($name, $dirs);
            $lines[] = $prefix.$branch.$name.($isDir ? '/' : '');

            if ($isDir) {
                $extension = $isLast ? '    ' : '│   ';
                $lines = array_merge($lines, $this->renderTreeLines($dirs[$name], $prefix.$extension));
            }
        }

        return $lines;
    }

    private function isExcluded(string $relative): bool
    {
        $relative = str_replace('\\', '/', $relative);

        foreach (self::EXCLUDED_FILES as $file) {
            if ($relative === $file) {
                return true;
            }
        }

        foreach (self::EXCLUDED_DIRS as $dir) {
            if ($relative === $dir || str_starts_with($relative, rtrim($dir, '/').'/')) {
                return true;
            }
        }

        if (str_ends_with($relative, '.key') || str_ends_with($relative, '.log')) {
            return true;
        }

        return false;
    }

    private function absolute(string $relative): string
    {
        return $this->repoRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }
}
