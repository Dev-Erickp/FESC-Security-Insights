<?php

namespace App\Support\ProjectIndex;

use RuntimeException;

final class IndexQuery
{
    private const STOPWORDS = [
        'the', 'and', 'for', 'with', 'that', 'this', 'from', 'into', 'sobre', 'para', 'como',
        'una', 'uno', 'unos', 'unas', 'del', 'los', 'las', 'que', 'por', 'con', 'sin', 'el', 'la',
        'de', 'en', 'a', 'un', 'y', 'o', 'al', 'se', 'su', 'sus',
    ];

    public function __construct(
        private readonly IndexStore $store,
        private readonly Indexer $indexer,
    ) {}

    /**
     * @return array{refreshed: bool, mode: string}
     */
    public function ensureFresh(bool $refresh = true): array
    {
        if (! $refresh) {
            if (! $this->store->exists()) {
                throw new RuntimeException('Project index is missing. Run php artisan project:index --full');
            }

            return ['refreshed' => false, 'mode' => 'current'];
        }

        $result = $this->indexer->refreshIfStale();

        return [
            'refreshed' => (bool) $result['refreshed'],
            'mode' => (string) $result['mode'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $action, ?string $id = null, ?string $q = null, int $depth = 1): array
    {
        return match ($action) {
            'overview' => $this->store->read('overview.json'),
            'node' => $this->node($this->requireId($id)),
            'children' => $this->children($this->requireId($id)),
            'parent' => $this->parent($this->requireId($id)),
            'dependencies' => $this->dependencies($this->requireId($id), $depth),
            'dependents' => $this->dependents($this->requireId($id), $depth),
            'find' => $this->find($this->requireQuery($q)),
            'path' => $this->path($this->requireQuery($q)),
            'files' => $this->files($this->requireId($id)),
            'related' => $this->related($this->requireId($id)),
            'context' => $this->context($this->requireId($id)),
            'relevant' => $this->relevant($this->requireQuery($q)),
            'stale' => $this->indexer->staleReport(),
            default => throw new RuntimeException("Unknown query action: {$action}"),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function node(string $id): array
    {
        return $this->nodesById()[$id] ?? throw new RuntimeException("Unknown node: {$id}");
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function children(string $id): array
    {
        $node = $this->node($id);
        $nodes = $this->nodesById();
        $children = [];

        foreach ($node['children'] ?? [] as $childId) {
            if (isset($nodes[$childId])) {
                $children[] = $this->summarize($nodes[$childId]);
            }
        }

        return $children;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parent(string $id): ?array
    {
        $node = $this->node($id);
        $parentId = $node['parent'] ?? null;

        if (! is_string($parentId)) {
            return null;
        }

        $parent = $this->nodesById()[$parentId] ?? null;

        return $parent ? $this->summarize($parent) : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function dependencies(string $id, int $depth = 1): array
    {
        return $this->walk($id, 'out', $depth);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function dependents(string $id, int $depth = 1): array
    {
        return $this->walk($id, 'in', $depth);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function find(string $query): array
    {
        return $this->relevant($query)['matches'];
    }

    /**
     * @return array{path: string, nodes: list<string>}
     */
    public function path(string $path): array
    {
        $normalized = str_replace('\\', '/', $path);
        $index = $this->store->read('index.json');
        $physical = $index['physical'] ?? [];
        $filemap = $this->store->read('filemap.json');
        $nodes = [];

        foreach ($filemap as $nodeId => $map) {
            foreach (['files', 'tests', 'entrypoints'] as $bucket) {
                foreach ($map[$bucket] ?? [] as $file) {
                    if ($file === $normalized || str_starts_with($normalized, rtrim((string) $file, '/').'/')) {
                        $nodes[] = $nodeId;
                    }
                }
            }

            foreach ($map['dirs'] ?? [] as $dir) {
                if (str_starts_with($normalized, $dir)) {
                    $nodes[] = $nodeId;
                }
            }
        }

        $dir = str_contains($normalized, '/') ? dirname($normalized).'/' : './';

        foreach ($physical[$dir] ?? [] as $nodeId) {
            $nodes[] = $nodeId;
        }

        return [
            'path' => $normalized,
            'nodes' => array_values(array_unique($nodes)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function files(string $id): array
    {
        $filemap = $this->store->read('filemap.json');

        return $filemap[$id] ?? [
            'entrypoints' => [],
            'dirs' => [],
            'files' => [],
            'tests' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function related(string $id): array
    {
        return [
            'node' => $this->summarize($this->node($id)),
            'dependencies' => $this->dependencies($id, 1),
            'dependents' => $this->dependents($id, 1),
            'children' => $this->children($id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function context(string $id): array
    {
        return [
            'truth_rule' => Catalog::truthRule(),
            'node' => $this->node($id),
            'parent' => $this->parent($id),
            'children' => $this->children($id),
            'dependencies' => $this->dependencies($id, 1),
            'dependents' => $this->dependents($id, 1),
            'files' => $this->files($id),
            'next_step' => 'Leer entrypoints y validar en el código antes de ampliar el contexto.',
        ];
    }

    /**
     * @return array{truth_rule: string, query: string, matches: list<array<string, mixed>>}
     */
    public function relevant(string $query): array
    {
        $tokens = $this->tokenize($query);
        $nodes = $this->nodesById();
        $scored = [];

        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';

            if (in_array($type, ['project', 'domain'], true)) {
                continue;
            }

            $score = 0;
            $haystack = $this->normalize($node['summary'] ?? '');

            foreach ($tokens as $token) {
                if ($this->normalize((string) $node['id']) === $token) {
                    $score += 5;
                } elseif (str_contains($this->normalize((string) $node['id']), $token)) {
                    $score += 5;
                }

                if (str_contains($this->normalize((string) $node['name']), $token)) {
                    $score += 4;
                }

                foreach ($node['aliases'] ?? [] as $alias) {
                    if (str_contains($this->normalize((string) $alias), $token)) {
                        $score += 4;
                    }
                }

                if (str_contains($haystack, $token)) {
                    $score += 1;
                }
            }

            if ($score <= 0) {
                continue;
            }

            $scored[] = [
                'id' => $node['id'],
                'name' => $node['name'],
                'summary' => $node['summary'],
                'score' => $score,
                'entrypoints' => $node['entrypoints'] ?? [],
                'depends_on' => $node['depends_on'] ?? [],
                'suggested_files' => $this->suggestedFiles($node['id']),
            ];
        }

        usort($scored, fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return [
            'truth_rule' => Catalog::truthRule(),
            'query' => $query,
            'matches' => array_slice($scored, 0, 5),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function nodesById(): array
    {
        $index = $this->store->read('index.json');
        $nodes = [];

        foreach ($index['nodes'] ?? [] as $node) {
            $nodes[$node['id']] = $node;
        }

        return $nodes;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function summarize(array $node): array
    {
        return [
            'id' => $node['id'],
            'type' => $node['type'],
            'name' => $node['name'],
            'summary' => $node['summary'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function walk(string $id, string $direction, int $depth): array
    {
        $graph = $this->store->read('graph.json');
        $nodes = $this->nodesById();
        $visited = [$id => true];
        $frontier = [$id];
        $found = [];
        $level = 0;

        while ($frontier !== [] && $level < max(1, $depth)) {
            $next = [];

            foreach ($frontier as $current) {
                foreach ($graph['edges'] ?? [] as $edge) {
                    $from = $edge['from'] ?? null;
                    $to = $edge['to'] ?? null;

                    if ($direction === 'out' && $from === $current && is_string($to)) {
                        $target = $to;
                    } elseif ($direction === 'in' && $to === $current && is_string($from)) {
                        $target = $from;
                    } else {
                        continue;
                    }

                    if (isset($visited[$target])) {
                        continue;
                    }

                    $visited[$target] = true;
                    $next[] = $target;

                    if (isset($nodes[$target])) {
                        $found[] = [
                            ...$this->summarize($nodes[$target]),
                            'edge_type' => $edge['type'] ?? null,
                            'inferred' => $edge['inferred'] ?? false,
                        ];
                    }
                }
            }

            $frontier = $next;
            $level++;
        }

        return $found;
    }

    /**
     * @return list<string>
     */
    private function suggestedFiles(string $id): array
    {
        $map = $this->files($id);
        $files = array_merge($map['entrypoints'] ?? [], array_slice($map['files'] ?? [], 0, 5));

        return array_values(array_unique($files));
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $query): array
    {
        $normalized = $this->normalize($query);
        $parts = preg_split('/[^a-z0-9]+/', $normalized) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            if (strlen($part) < 3 || in_array($part, self::STOPWORDS, true)) {
                continue;
            }

            $tokens[] = $part;
        }

        return array_values(array_unique($tokens));
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ];

        return strtr($value, $replacements);
    }

    private function requireId(?string $id): string
    {
        if (! is_string($id) || $id === '') {
            throw new RuntimeException('This action requires a node id.');
        }

        return $id;
    }

    private function requireQuery(?string $q): string
    {
        if (! is_string($q) || trim($q) === '') {
            throw new RuntimeException('This action requires --q.');
        }

        return $q;
    }
}
