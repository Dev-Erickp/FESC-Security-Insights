<?php

namespace App\Console\Commands;

use App\Support\ProjectIndex\GitHookInstaller;
use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexStore;
use Illuminate\Console\Command;

class ProjectIndexCommand extends Command
{
    protected $signature = 'project:index
        {--full : Rebuild the conceptual index from scratch}
        {--check : Report staleness as JSON and exit 1 when stale}
        {--if-stale : Refresh only when stale}
        {--install-hooks : Copy .githooks into .git/hooks}
        {--path=.project : Index artifact directory relative to repo root}';

    protected $description = 'Build or refresh the Project Intelligence Index under .project/';

    public function handle(GitHookInstaller $hooks): int
    {
        $repoRoot = base_path();
        $relative = (string) $this->option('path');
        $storePath = $this->resolvePath($repoRoot, $relative);
        $store = new IndexStore($storePath);
        $indexer = new Indexer($repoRoot, $store);

        if ($this->option('install-hooks')) {
            $installed = $hooks->install($repoRoot);

            if (! $this->option('if-stale') && ! $this->option('check')) {
                $this->info('Hooks installed: '.(implode(', ', $installed) ?: '(none)'));
            }
        }

        if ($this->option('check')) {
            $report = $indexer->staleReport();
            $this->line(json_encode($report, IndexStore::JSON_FLAGS) ?: '{}');

            return $report['stale'] ? self::FAILURE : self::SUCCESS;
        }

        if ($this->option('if-stale')) {
            $result = $indexer->refreshIfStale();

            if ($result['refreshed']) {
                $this->line('Índice actualizado ('.$result['mode'].')');
            }

            return self::SUCCESS;
        }

        $result = $indexer->build((bool) $this->option('full'));
        $payload = [
            'ok' => true,
            'mode' => $result['mode'],
            'affected_nodes' => $result['affected_nodes'],
            'warnings' => $result['warnings'],
            'orphan_count' => count($result['orphans']),
            'orphans' => $result['orphans'],
            'path' => $relative,
        ];

        $this->line(json_encode($payload, IndexStore::JSON_FLAGS) ?: '{}');

        return count($result['orphans']) > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function resolvePath(string $repoRoot, string $relative): string
    {
        if ($this->isAbsolute($relative)) {
            return $relative;
        }

        return $repoRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }

    private function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
            || (strlen($path) > 2 && ctype_alpha($path[0]) && $path[1] === ':');
    }
}
