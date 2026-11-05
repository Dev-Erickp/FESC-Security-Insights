<?php

namespace App\Support\ProjectIndex;

use RuntimeException;

final class Indexer
{
    private const INDEXABLE_EXTENSIONS = [
        'php', 'ts', 'tsx', 'js', 'jsx', 'md', 'mdc', 'json', 'yml', 'yaml',
        'css', 'blade.php', 'xml', 'neon', 'example',
    ];

    private const NON_PRIMARY_TYPES = [
        'project', 'domain', 'test', 'documentation', 'configuration', 'infrastructure',
    ];

    public function __construct(
        private readonly string $repoRoot,
        private readonly IndexStore $store,
        private readonly PhpImportScanner $scanner = new PhpImportScanner,
        private readonly ?array $catalogOverride = null,
    ) {}

    /**
     * @return array{mode: string, affected_nodes: list<string>, warnings: list<string>, orphans: list<string>}
     */
    public function build(bool $full = false): array
    {
        $catalog = $this->catalog();
        $this->validateCatalog($catalog);

        $catalogHash = sha1((string) json_encode($catalog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $previousFingerprints = $this->store->readOrEmpty('fingerprints.json');
        $previousFiles = is_array($previousFingerprints['files'] ?? null) ? $previousFingerprints['files'] : [];
        $previousCatalogHash = is_string($previousFingerprints['catalog_hash'] ?? null) ? $previousFingerprints['catalog_hash'] : null;

        $needsFull = $full
            || ! $this->store->exists()
            || $previousCatalogHash === null
            || $previousCatalogHash !== $catalogHash;

        $scanned = $this->scanFiles($catalog);
        $filesHash = $this->computeFilesHash($scanned);

        $changedPaths = [];
        $fingerprints = ['catalog_hash' => $catalogHash, 'files' => []];

        foreach ($scanned as $relative => $meta) {
            $old = $previousFiles[$relative] ?? null;
            $oldHash = is_array($old) ? ($old['hash'] ?? null) : null;
            $imports = [];

            if ($needsFull || $oldHash !== $meta['hash'] || ! is_array($old)) {
                $changedPaths[] = $relative;

                if (str_ends_with($relative, '.php')) {
                    $absolute = $this->absolute($relative);
                    $source = is_file($absolute) ? (string) file_get_contents($absolute) : '';
                    $imports = $this->scanner->scan($source);
                }
            } else {
                $imports = is_array($old['imports'] ?? null) ? array_values($old['imports']) : [];
            }

            $fingerprints['files'][$relative] = [
                'hash' => $meta['hash'],
                'imports' => $imports,
            ];
        }

        ksort($fingerprints['files']);

        $nodesById = [];
        foreach ($catalog['nodes'] as $node) {
            $nodesById[$node['id']] = $node;
        }

        $fileOwners = $this->mapFilesToNodes($scanned, $nodesById);
        $primaryOwner = $this->resolvePrimaryOwners($fileOwners, $nodesById);
        $filemap = $this->buildFilemap($fileOwners, $nodesById);
        $edges = $this->buildEdges($nodesById, $fingerprints['files'], $primaryOwner);
        $indexNodes = $this->buildIndexNodes($nodesById, $filemap);
        $physical = $this->buildPhysicalMap($fileOwners);
        $overview = $this->buildOverview($catalog, $nodesById);
        $warnings = $this->gitWarnings();
        $orphans = $this->findAppOrphans($fileOwners);

        $commit = $this->gitOutput(['rev-parse', 'HEAD']) ?? 'unknown';
        $dirty = $this->isWorkingTreeDirty();

        $mode = $needsFull ? 'full' : 'incremental';
        $affected = $needsFull
            ? array_keys($nodesById)
            : $this->affectedNodes($changedPaths, $fileOwners);

        sort($affected);

        $metadata = [
            'schema_version' => Catalog::SCHEMA_VERSION,
            'generated_at' => gmdate('c'),
            'commit' => $commit,
            'dirty' => $dirty,
            'files_hash' => $filesHash,
            'catalog_hash' => $catalogHash,
            'mode' => $mode,
            'node_count' => count($indexNodes),
            'edge_count' => count($edges),
            'truth_rule' => Catalog::truthRule(),
            'affected_nodes' => $affected,
            'warnings' => $warnings,
            'orphan_count' => count($orphans),
        ];

        $this->store->write('metadata.json', $metadata);
        $this->store->write('overview.json', $overview);
        $this->store->write('index.json', [
            'nodes' => array_values($indexNodes),
            'physical' => $physical,
        ]);
        $this->store->write('graph.json', ['edges' => $edges]);
        $this->store->write('filemap.json', $filemap);
        $this->store->write('fingerprints.json', $fingerprints);

        return [
            'mode' => $mode,
            'affected_nodes' => $affected,
            'warnings' => $warnings,
            'orphans' => $orphans,
        ];
    }

    /**
     * @return array{stale: bool, reasons: list<string>, warnings: list<string>}
     */
    public function staleReport(): array
    {
        $warnings = $this->gitWarnings();

        if (! $this->store->exists()) {
            return [
                'stale' => true,
                'reasons' => ['missing-index'],
                'warnings' => $warnings,
            ];
        }

        $metadata = $this->store->read('metadata.json');
        $catalog = $this->catalog();
        $catalogHash = sha1((string) json_encode($catalog, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $reasons = [];

        if (($metadata['catalog_hash'] ?? null) !== $catalogHash) {
            $reasons[] = 'catalog-changed';
        }

        $scanned = $this->scanFiles($catalog);
        $filesHash = $this->computeFilesHash($scanned);

        if (($metadata['files_hash'] ?? null) !== $filesHash) {
            $reasons[] = 'source-files-changed';
        }

        return [
            'stale' => $reasons !== [],
            'reasons' => $reasons,
            'warnings' => $warnings,
            'commit' => $metadata['commit'] ?? null,
            'current_commit' => $this->gitOutput(['rev-parse', 'HEAD']),
            'dirty' => $this->isWorkingTreeDirty(),
        ];
    }

    /**
     * @return array{refreshed: bool, mode: string, report?: array<string, mixed>}
     */
    public function refreshIfStale(): array
    {
        $report = $this->staleReport();

        if (! $report['stale']) {
            return ['refreshed' => false, 'mode' => 'current', 'report' => $report];
        }

        $full = in_array('missing-index', $report['reasons'], true)
            || in_array('catalog-changed', $report['reasons'], true);

        $result = $this->build($full);

        return [
            'refreshed' => true,
            'mode' => $result['mode'],
            'report' => $report,
            'result' => $result,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function catalog(): array
    {
        return $this->catalogOverride ?? Catalog::definition();
    }

    /**
     * @param  array<string, mixed>  $catalog
     */
    private function validateCatalog(array $catalog): void
    {
        $ids = [];

        foreach ($catalog['nodes'] as $node) {
            $id = $node['id'] ?? null;

            if (! is_string($id) || $id === '') {
                throw new RuntimeException('Catalog node missing id.');
            }

            if (isset($ids[$id])) {
                throw new RuntimeException("Duplicate catalog id: {$id}");
            }

            $ids[$id] = true;
        }

        foreach ($catalog['nodes'] as $node) {
            $parent = $node['parent'] ?? null;

            if ($parent !== null && ! isset($ids[$parent])) {
                throw new RuntimeException("Missing parent {$parent} for {$node['id']}");
            }

            foreach ($node['depends_on'] ?? [] as $dep) {
                if (! isset($ids[$dep])) {
                    throw new RuntimeException("Invalid depends_on {$dep} on {$node['id']}");
                }
            }

            foreach ($node['entrypoints'] ?? [] as $entrypoint) {
                if (! $this->pathExists($entrypoint) && ! str_contains($entrypoint, '*')) {
                    // Allow missing generated .project files during first build
                    if (str_starts_with($entrypoint, '.project/')) {
                        continue;
                    }

                    throw new RuntimeException("Missing entrypoint {$entrypoint} for {$node['id']}");
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @return array<string, array{hash: string, absolute: string}>
     */
    private function scanFiles(array $catalog): array
    {
        $ignore = array_map(fn (string $p): string => $this->normalize($p), $catalog['ignore'] ?? []);
        $files = [];

        foreach ($catalog['scan_roots'] as $root) {
            $absolute = $this->absolute($root);

            if (is_file($absolute)) {
                if ($this->isIgnored($this->normalize($root), $ignore)) {
                    continue;
                }

                if ($this->isIndexable($absolute)) {
                    $relative = $this->normalize($root);
                    $files[$relative] = [
                        'hash' => sha1_file($absolute) ?: sha1(''),
                        'absolute' => $absolute,
                    ];
                }

                continue;
            }

            if (! is_dir($absolute)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absolute, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $relative = $this->normalize(substr($file->getPathname(), strlen($this->repoRoot) + 1));

                if ($this->isIgnored($relative, $ignore)) {
                    continue;
                }

                if (! $this->isIndexable($file->getPathname())) {
                    continue;
                }

                $files[$relative] = [
                    'hash' => sha1_file($file->getPathname()) ?: sha1(''),
                    'absolute' => $file->getPathname(),
                ];
            }
        }

        ksort($files);

        return $files;
    }

    /**
     * @param  array<string, array{hash: string, absolute: string}>  $scanned
     */
    private function computeFilesHash(array $scanned): string
    {
        $parts = [];

        foreach ($scanned as $path => $meta) {
            $parts[] = $path.':'.$meta['hash'];
        }

        return sha1(implode("\n", $parts));
    }

    /**
     * @param  array<string, array{hash: string, absolute: string}>  $scanned
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, list<string>>
     */
    private function mapFilesToNodes(array $scanned, array $nodesById): array
    {
        $owners = [];

        foreach ($scanned as $relative => $meta) {
            unset($meta);
            $matches = [];

            foreach ($nodesById as $id => $node) {
                foreach ($node['paths'] ?? [] as $pattern) {
                    if ($this->pathMatches($relative, $this->normalize((string) $pattern))) {
                        $matches[] = $id;
                        break;
                    }
                }
            }

            if ($matches !== []) {
                $owners[$relative] = array_values(array_unique($matches));
            }
        }

        return $owners;
    }

    /**
     * @param  array<string, list<string>>  $fileOwners
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, string>
     */
    private function resolvePrimaryOwners(array $fileOwners, array $nodesById): array
    {
        $primary = [];

        foreach ($fileOwners as $file => $nodeIds) {
            $candidates = [];

            foreach ($nodeIds as $id) {
                $type = $nodesById[$id]['type'] ?? null;

                if (in_array($type, self::NON_PRIMARY_TYPES, true)) {
                    continue;
                }

                $bestPattern = '';

                foreach ($nodesById[$id]['paths'] ?? [] as $pattern) {
                    $normalized = $this->normalize((string) $pattern);

                    if ($this->pathMatches($file, $normalized) && strlen($normalized) >= strlen($bestPattern)) {
                        $bestPattern = $normalized;
                    }
                }

                $candidates[] = [
                    'id' => $id,
                    'pattern' => $bestPattern,
                    'type' => $type,
                ];
            }

            if ($candidates === []) {
                continue;
            }

            usort($candidates, function (array $a, array $b): int {
                $len = strlen($b['pattern']) <=> strlen($a['pattern']);

                if ($len !== 0) {
                    return $len;
                }

                $aService = $a['type'] === 'service' ? 0 : 1;
                $bService = $b['type'] === 'service' ? 0 : 1;

                return $aService <=> $bService;
            });

            $primary[$file] = $candidates[0]['id'];
        }

        return $primary;
    }

    /**
     * @param  array<string, list<string>>  $fileOwners
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, array{entrypoints: list<string>, dirs: list<string>, files: list<string>, tests: list<string>}>
     */
    private function buildFilemap(array $fileOwners, array $nodesById): array
    {
        $byNode = [];

        foreach ($fileOwners as $file => $nodeIds) {
            foreach ($nodeIds as $nodeId) {
                $byNode[$nodeId][] = $file;
            }
        }

        $filemap = [];

        foreach ($nodesById as $id => $node) {
            $type = $node['type'] ?? '';

            if (in_array($type, ['project', 'domain'], true) && empty($byNode[$id])) {
                continue;
            }

            $files = $byNode[$id] ?? [];
            sort($files);

            $tests = array_values(array_filter($files, fn (string $f): bool => str_starts_with($f, 'tests/')));
            $nonTests = array_values(array_filter($files, fn (string $f): bool => ! str_starts_with($f, 'tests/')));

            [$dirs, $listedFiles] = $this->collapsePaths($nonTests, 12);
            [$testDirs, $listedTests] = $this->collapsePaths($tests, 8);

            $filemap[$id] = [
                'entrypoints' => array_values(array_filter(
                    $node['entrypoints'] ?? [],
                    fn (string $e): bool => $this->pathExists($e) || str_starts_with($e, '.project/')
                )),
                'dirs' => array_values(array_unique([...$dirs, ...$testDirs])),
                'files' => $listedFiles,
                'tests' => $listedTests,
            ];
        }

        ksort($filemap);

        return $filemap;
    }

    /**
     * @param  list<string>  $paths
     * @return array{0: list<string>, 1: list<string>}
     */
    private function collapsePaths(array $paths, int $threshold): array
    {
        $byDir = [];

        foreach ($paths as $path) {
            $dir = str_contains($path, '/') ? dirname($path).'/' : '';
            $byDir[$dir][] = $path;
        }

        $dirs = [];
        $files = [];

        foreach ($byDir as $dir => $group) {
            if ($dir !== '' && count($group) > $threshold) {
                $dirs[] = $dir;
            } else {
                foreach ($group as $path) {
                    $files[] = $path;
                }
            }
        }

        sort($dirs);
        sort($files);

        return [$dirs, $files];
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  array<string, array{hash: string, imports: list<string>}>  $fingerprints
     * @param  array<string, string>  $primaryOwner
     * @return list<array<string, mixed>>
     */
    private function buildEdges(array $nodesById, array $fingerprints, array $primaryOwner): array
    {
        $edges = [];
        $edgeKey = [];

        foreach ($nodesById as $node) {
            foreach ($node['depends_on'] ?? [] as $dep) {
                $key = $node['id'].'>depends_on>'.$dep;

                if (isset($edgeKey[$key])) {
                    continue;
                }

                $edgeKey[$key] = true;
                $edges[] = [
                    'from' => $node['id'],
                    'to' => $dep,
                    'type' => 'depends_on',
                    'confidence' => 1,
                    'source' => 'catalog',
                    'inferred' => false,
                    'weight' => 1,
                ];
            }
        }

        foreach ($fingerprints as $file => $meta) {
            $from = $primaryOwner[$file] ?? null;

            if ($from === null) {
                continue;
            }

            foreach ($meta['imports'] as $fqcn) {
                $targetPath = $this->scanner->fqcnToPath($fqcn, $this->repoRoot);

                if ($targetPath === null) {
                    continue;
                }

                $to = $primaryOwner[$targetPath] ?? null;

                if ($to === null || $to === $from) {
                    continue;
                }

                $key = $from.'>uses>'.$to;

                if (isset($edgeKey[$key])) {
                    foreach ($edges as &$edge) {
                        if (($edge['from'] ?? null) === $from && ($edge['to'] ?? null) === $to && ($edge['type'] ?? null) === 'uses') {
                            $edge['weight'] = (int) $edge['weight'] + 1;
                        }
                    }
                    unset($edge);

                    continue;
                }

                $edgeKey[$key] = true;
                $edges[] = [
                    'from' => $from,
                    'to' => $to,
                    'type' => 'uses',
                    'confidence' => 0.85,
                    'source' => 'import-analysis',
                    'inferred' => true,
                    'weight' => 1,
                ];
            }
        }

        if (isset($nodesById['test-suite'])) {
            foreach ($primaryOwner as $file => $featureId) {
                if (! str_starts_with($file, 'tests/')) {
                    continue;
                }

                if (($nodesById[$featureId]['type'] ?? null) === 'test') {
                    continue;
                }

                $key = 'test-suite>tests>'.$featureId;

                if (isset($edgeKey[$key])) {
                    continue;
                }

                $edgeKey[$key] = true;
                $edges[] = [
                    'from' => 'test-suite',
                    'to' => $featureId,
                    'type' => 'tests',
                    'confidence' => 0.9,
                    'source' => 'path-map',
                    'inferred' => true,
                    'weight' => 1,
                ];
            }
        }

        return $edges;
    }

    /**
     * @param  array<string, array<string, mixed>>  $nodesById
     * @param  array<string, array{entrypoints: list<string>, dirs: list<string>, files: list<string>, tests: list<string>}>  $filemap
     * @return array<string, array<string, mixed>>
     */
    private function buildIndexNodes(array $nodesById, array $filemap): array
    {
        $children = [];

        foreach ($nodesById as $id => $node) {
            $parent = $node['parent'] ?? null;

            if (is_string($parent)) {
                $children[$parent][] = $id;
            }
        }

        $index = [];

        foreach ($nodesById as $id => $node) {
            $kids = $children[$id] ?? [];
            sort($kids);

            $index[$id] = [
                'id' => $id,
                'type' => $node['type'],
                'name' => $node['name'],
                'summary' => $node['summary'],
                'status' => $node['status'] ?? 'implemented',
                'parent' => $node['parent'] ?? null,
                'children' => $kids,
                'depends_on' => array_values($node['depends_on'] ?? []),
                'entrypoints' => $filemap[$id]['entrypoints'] ?? array_values($node['entrypoints'] ?? []),
            ];

            if (! empty($node['aliases'])) {
                $index[$id]['aliases'] = array_values($node['aliases']);
            }
        }

        return $index;
    }

    /**
     * @param  array<string, list<string>>  $fileOwners
     * @return array<string, list<string>>
     */
    private function buildPhysicalMap(array $fileOwners): array
    {
        $physical = [];

        foreach ($fileOwners as $file => $nodeIds) {
            $dir = str_contains($file, '/') ? dirname($file).'/' : './';
            foreach ($nodeIds as $nodeId) {
                $physical[$dir][] = $nodeId;
            }
        }

        foreach ($physical as $dir => $ids) {
            $physical[$dir] = array_values(array_unique($ids));
            sort($physical[$dir]);
        }

        ksort($physical);

        return $physical;
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  array<string, array<string, mixed>>  $nodesById
     * @return array<string, mixed>
     */
    private function buildOverview(array $catalog, array $nodesById): array
    {
        $children = [];

        foreach ($nodesById as $id => $node) {
            $parent = $node['parent'] ?? null;

            if (is_string($parent)) {
                $children[$parent][] = $id;
            }
        }

        $domains = [];
        $projectId = $catalog['project']['id'];

        foreach ($children[$projectId] ?? [] as $domainId) {
            $domain = $nodesById[$domainId];
            $domainChildren = [];

            foreach ($children[$domainId] ?? [] as $childId) {
                $child = $nodesById[$childId];
                $domainChildren[] = [
                    'id' => $childId,
                    'type' => $child['type'],
                    'name' => $child['name'],
                    'summary' => $child['summary'],
                ];
            }

            $domains[] = [
                'id' => $domainId,
                'type' => $domain['type'],
                'name' => $domain['name'],
                'summary' => $domain['summary'],
                'children' => $domainChildren,
            ];
        }

        return [
            'project' => $catalog['project'],
            'truth_rule' => Catalog::truthRule(),
            'context_budget' => [
                'prefer' => ['metadata', 'overview', 'query relevant|context'],
                'overview_target_kb' => 10,
                'avoid_loading_all_artifacts' => true,
            ],
            'how_to_query' => [
                'overview' => 'php artisan project:query overview',
                'relevant' => 'php artisan project:query relevant --q="tarea"',
                'context' => 'php artisan project:query context {id}',
                'stale' => 'php artisan project:query stale',
            ],
            'domains' => $domains,
        ];
    }

    /**
     * @param  array<string, list<string>>  $fileOwners
     * @return list<string>
     */
    private function findAppOrphans(array $fileOwners): array
    {
        $orphans = [];
        $appDir = $this->absolute('app');

        if (! is_dir($appDir)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($appDir, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = $this->normalize(substr($file->getPathname(), strlen($this->repoRoot) + 1));

            if (! isset($fileOwners[$relative])) {
                $orphans[] = $relative;
            }
        }

        sort($orphans);

        return $orphans;
    }

    /**
     * @param  list<string>  $changedPaths
     * @param  array<string, list<string>>  $fileOwners
     * @return list<string>
     */
    private function affectedNodes(array $changedPaths, array $fileOwners): array
    {
        $nodes = [];

        foreach ($changedPaths as $path) {
            foreach ($fileOwners[$path] ?? [] as $nodeId) {
                $nodes[$nodeId] = true;
            }
        }

        return array_keys($nodes);
    }

    private function pathMatches(string $file, string $pattern): bool
    {
        $file = $this->normalize($file);
        $pattern = $this->normalize($pattern);

        if ($pattern === $file) {
            return true;
        }

        if (str_ends_with($pattern, '/')) {
            return str_starts_with($file, $pattern);
        }

        if (str_contains($pattern, '*') || str_contains($pattern, '?')) {
            return fnmatch($pattern, $file, FNM_CASEFOLD);
        }

        return false;
    }

    private function pathExists(string $relative): bool
    {
        return file_exists($this->absolute($relative));
    }

    private function absolute(string $relative): string
    {
        return $this->repoRoot.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }

    private function normalize(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * @param  list<string>  $ignore
     */
    private function isIgnored(string $relative, array $ignore): bool
    {
        $relative = $this->normalize($relative);

        foreach ($ignore as $pattern) {
            if ($relative === $pattern || str_starts_with($relative, rtrim($pattern, '/').'/')) {
                return true;
            }

            if (str_contains($pattern, '*') && fnmatch($pattern, $relative, FNM_CASEFOLD)) {
                return true;
            }
        }

        return false;
    }

    private function isIndexable(string $absolute): bool
    {
        $basename = basename($absolute);

        if ($basename === 'artisan' || $basename === 'Dockerfile') {
            return true;
        }

        $normalized = $this->normalize($absolute);

        if (str_ends_with($normalized, '.blade.php')) {
            return true;
        }

        $ext = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        return in_array($ext, self::INDEXABLE_EXTENSIONS, true);
    }

    /**
     * @return list<string>
     */
    private function gitWarnings(): array
    {
        $warnings = [];

        if ($this->isWorkingTreeDirty()) {
            $warnings[] = 'working-tree-dirty';
        }

        $head = $this->gitOutput(['rev-parse', 'HEAD']);
        $metadataCommit = null;

        if ($this->store->exists()) {
            $metadataCommit = $this->store->read('metadata.json')['commit'] ?? null;
        }

        if (is_string($metadataCommit) && is_string($head) && $metadataCommit !== $head) {
            $warnings[] = 'commit-changed';
        }

        return $warnings;
    }

    private function isWorkingTreeDirty(): bool
    {
        $output = $this->gitOutput(['status', '--porcelain']);

        return is_string($output) && trim($output) !== '';
    }

    /**
     * @param  list<string>  $args
     */
    private function gitOutput(array $args): ?string
    {
        if (! is_dir($this->repoRoot.DIRECTORY_SEPARATOR.'.git')) {
            return null;
        }

        $command = array_merge(['git', '-C', $this->repoRoot], $args);
        $process = proc_open(
            $command,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->repoRoot
        );

        if (! is_resource($process)) {
            return null;
        }

        $stdout = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0) {
            return null;
        }

        return trim($stdout);
    }
}
