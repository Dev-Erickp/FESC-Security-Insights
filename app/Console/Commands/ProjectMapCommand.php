<?php

namespace App\Console\Commands;

use App\Support\ProjectIndex\CodebaseMapper;
use App\Support\ProjectIndex\IndexStore;
use Illuminate\Console\Command;

class ProjectMapCommand extends Command
{
    protected $signature = 'project:map
        {--output=docs/AI/CODEBASE_MAP.md : Relative output path for the real codebase tree}';

    protected $description = 'Generate the real repository codebase map for AI agent context';

    public function handle(): int
    {
        $mapper = new CodebaseMapper(base_path());
        $result = $mapper->generate((string) $this->option('output'));

        $this->line(json_encode([
            'ok' => true,
            'path' => $result['path'],
            'directories' => $result['directories'],
            'files' => $result['files'],
        ], IndexStore::JSON_FLAGS) ?: '{}');

        return self::SUCCESS;
    }
}
