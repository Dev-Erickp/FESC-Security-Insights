<?php

namespace App\Support\ProjectIndex;

final class PhpImportScanner
{
    /**
     * Extract App\* FQCNs from PHP use statements (including grouped/multiline).
     *
     * @return list<string>
     */
    public function scan(string $source): array
    {
        $imports = [];

        if (preg_match_all('/^use\s+([^;]+);/mi', $source, $matches) === false) {
            return [];
        }

        foreach ($matches[1] as $statement) {
            $statement = trim($statement);

            if (str_starts_with(strtolower($statement), 'function ') || str_starts_with(strtolower($statement), 'const ')) {
                continue;
            }

            if (str_contains($statement, '{')) {
                if (! preg_match('/^(.*?)\{([^}]*)\}/s', $statement, $grouped)) {
                    continue;
                }

                $prefix = rtrim(trim($grouped[1]), '\\');
                $parts = preg_split('/\s*,\s*/', trim($grouped[2])) ?: [];

                foreach ($parts as $part) {
                    $part = trim($part);

                    if ($part === '') {
                        continue;
                    }

                    $name = preg_split('/\s+as\s+/i', $part)[0] ?? $part;
                    $fqcn = $prefix.'\\'.ltrim(trim($name), '\\');
                    $this->pushAppImport($imports, $fqcn);
                }

                continue;
            }

            $fqcn = preg_split('/\s+as\s+/i', $statement)[0] ?? $statement;
            $this->pushAppImport($imports, trim($fqcn));
        }

        sort($imports);

        return array_values(array_unique($imports));
    }

    /**
     * Resolve an App\* FQCN to a relative path under the repository root.
     */
    public function fqcnToPath(string $fqcn, string $root): ?string
    {
        if (! str_starts_with($fqcn, 'App\\')) {
            return null;
        }

        $relative = 'app/'.str_replace('\\', '/', substr($fqcn, 4)).'.php';
        $absolute = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

        if (! is_file($absolute)) {
            return null;
        }

        return str_replace('\\', '/', $relative);
    }

    /**
     * @param  list<string>  $imports
     */
    private function pushAppImport(array &$imports, string $fqcn): void
    {
        $fqcn = ltrim($fqcn, '\\');

        if ($fqcn === '' || ! str_starts_with($fqcn, 'App\\')) {
            return;
        }

        if (str_starts_with($fqcn, 'Illuminate\\') || str_starts_with($fqcn, 'Laravel\\')) {
            return;
        }

        $imports[] = $fqcn;
    }
}
