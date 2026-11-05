<?php

namespace App\Support\ProjectIndex;

use RuntimeException;

final class GitHookInstaller
{
    /**
     * Copy versioned hooks from .githooks into .git/hooks without changing git config.
     *
     * @return list<string> Installed hook basenames
     */
    public function install(string $repoRoot): array
    {
        $sourceDir = $repoRoot.DIRECTORY_SEPARATOR.'.githooks';
        $targetDir = $repoRoot.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'hooks';

        if (! is_dir($sourceDir)) {
            throw new RuntimeException('Missing .githooks directory.');
        }

        if (! is_dir($repoRoot.DIRECTORY_SEPARATOR.'.git')) {
            return [];
        }

        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            throw new RuntimeException("Unable to create {$targetDir}");
        }

        $installed = [];

        foreach (scandir($sourceDir) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $source = $sourceDir.DIRECTORY_SEPARATOR.$entry;

            if (! is_file($source)) {
                continue;
            }

            $target = $targetDir.DIRECTORY_SEPARATOR.$entry;
            $contents = file_get_contents($source);

            if ($contents === false) {
                throw new RuntimeException("Unable to read hook {$entry}");
            }

            if (file_put_contents($target, $contents) === false) {
                throw new RuntimeException("Unable to write hook {$entry}");
            }

            @chmod($target, 0755);
            $installed[] = $entry;
        }

        return $installed;
    }
}
