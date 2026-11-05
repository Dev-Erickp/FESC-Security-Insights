<?php

namespace App\Console\Commands;

use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexQuery;
use App\Support\ProjectIndex\IndexStore;
use Illuminate\Console\Command;
use Throwable;

class ProjectQueryCommand extends Command
{
    protected $signature = 'project:query
        {action : overview|node|children|parent|dependencies|dependents|find|path|files|related|context|relevant|stale}
        {id? : Node id when required}
        {--q= : Free-text query for relevant/find/path}
        {--depth=1 : Dependency walk depth}
        {--path=.project : Index artifact directory relative to repo root}
        {--no-refresh : Do not auto-refresh a stale or missing index}';

    protected $description = 'Query the Project Intelligence Index (JSON on STDOUT)';

    public function handle(): int
    {
        $repoRoot = base_path();
        $relative = (string) $this->option('path');
        $storePath = $this->resolvePath($repoRoot, $relative);
        $store = new IndexStore($storePath);
        $indexer = new Indexer($repoRoot, $store);
        $query = new IndexQuery($store, $indexer);

        $action = (string) $this->argument('action');
        $refresh = ! $this->option('no-refresh') && $action !== 'stale';

        try {
            $query->ensureFresh($refresh);
            $result = $query->run(
                $action,
                $this->argument('id'),
                $this->option('q'),
                (int) $this->option('depth'),
            );
        } catch (Throwable $e) {
            $this->line(json_encode([
                'error' => $e->getMessage(),
            ], IndexStore::JSON_FLAGS) ?: '{"error":"unknown"}');

            return self::FAILURE;
        }

        $this->line(json_encode($result, IndexStore::JSON_FLAGS) ?: '{}');

        return self::SUCCESS;
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
