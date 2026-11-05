<?php

use App\Support\ProjectIndex\GitHookInstaller;
use App\Support\ProjectIndex\Indexer;
use App\Support\ProjectIndex\IndexQuery;
use App\Support\ProjectIndex\IndexStore;
use App\Support\ProjectIndex\PhpImportScanner;

function projectIndexFixture(): array
{
    $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fesc-pi-'.bin2hex(random_bytes(4));
    $project = $root.DIRECTORY_SEPARATOR.'.project';
    mkdir($root.'/app/Alpha', 0755, true);
    mkdir($root.'/app/Beta', 0755, true);
    mkdir($root.'/tests', 0755, true);
    mkdir($root.'/.githooks', 0755, true);
    mkdir($root.'/.git/hooks', 0755, true);

    file_put_contents($root.'/app/Alpha/ServiceA.php', <<<'PHP'
        <?php
        namespace App\Alpha;
        use App\Beta\ServiceB;
        class ServiceA { public function x(ServiceB $b) {} }
        PHP);

    file_put_contents($root.'/app/Beta/ServiceB.php', <<<'PHP'
        <?php
        namespace App\Beta;
        class ServiceB {}
        PHP);

    file_put_contents($root.'/tests/AlphaTest.php', "<?php\n");
    file_put_contents($root.'/.githooks/post-commit', "#!/usr/bin/env bash\nexit 0\n");

    $catalog = [
        'project' => [
            'id' => 'demo',
            'name' => 'Demo',
            'summary' => 'Fixture project',
        ],
        'scan_roots' => ['app', 'tests', '.githooks'],
        'ignore' => ['.project/fingerprints.json'],
        'nodes' => [
            [
                'id' => 'demo',
                'type' => 'project',
                'name' => 'Demo',
                'parent' => null,
                'summary' => 'Root',
                'status' => 'implemented',
                'paths' => [],
                'entrypoints' => [],
                'depends_on' => [],
            ],
            [
                'id' => 'alpha-domain',
                'type' => 'domain',
                'name' => 'Alpha Domain',
                'parent' => 'demo',
                'summary' => 'Domain A',
                'status' => 'implemented',
                'paths' => ['app/Alpha/'],
                'entrypoints' => ['app/Alpha/ServiceA.php'],
                'depends_on' => [],
            ],
            [
                'id' => 'alpha-feature',
                'type' => 'feature',
                'name' => 'Alpha Feature',
                'parent' => 'alpha-domain',
                'summary' => 'Feature that uses beta',
                'status' => 'implemented',
                'aliases' => ['servicio alpha', 'alpha'],
                'paths' => ['app/Alpha/'],
                'entrypoints' => ['app/Alpha/ServiceA.php'],
                'depends_on' => ['beta-feature'],
            ],
            [
                'id' => 'beta-feature',
                'type' => 'feature',
                'name' => 'Beta Feature',
                'parent' => 'alpha-domain',
                'summary' => 'Independent beta service',
                'status' => 'implemented',
                'aliases' => ['beta'],
                'paths' => ['app/Beta/'],
                'entrypoints' => ['app/Beta/ServiceB.php'],
                'depends_on' => [],
            ],
            [
                'id' => 'test-suite',
                'type' => 'test',
                'name' => 'Tests',
                'parent' => 'demo',
                'summary' => 'Tests',
                'status' => 'implemented',
                'paths' => ['tests/'],
                'entrypoints' => ['tests/AlphaTest.php'],
                'depends_on' => [],
            ],
        ],
    ];

    $store = new IndexStore($project);
    $indexer = new Indexer($root, $store, new PhpImportScanner, $catalog);

    return compact('root', 'project', 'catalog', 'store', 'indexer');
}

it('validates unique ids parents and depends_on while building graph and filemap', function () {
    $fx = projectIndexFixture();
    $result = $fx['indexer']->build(true);

    expect($result['mode'])->toBe('full')
        ->and($result['orphans'])->toBe([]);

    $index = $fx['store']->read('index.json');
    $ids = array_column($index['nodes'], 'id');
    expect($ids)->toContain('alpha-feature', 'beta-feature');

    $graph = $fx['store']->read('graph.json');
    $types = array_column($graph['edges'], 'type');
    expect($types)->toContain('depends_on')
        ->and($types)->toContain('uses');

    $filemap = $fx['store']->read('filemap.json');
    expect($filemap['alpha-feature']['entrypoints'])->toContain('app/Alpha/ServiceA.php');
});

it('incremental rebuild only reports affected nodes after a file change', function () {
    $fx = projectIndexFixture();
    $fx['indexer']->build(true);

    file_put_contents($fx['root'].'/app/Alpha/ServiceA.php', <<<'PHP'
        <?php
        namespace App\Alpha;
        use App\Beta\ServiceB;
        class ServiceA { public function y(ServiceB $b) { return 1; } }
        PHP);

    $result = $fx['indexer']->build(false);

    expect($result['mode'])->toBe('incremental')
        ->and($result['affected_nodes'])->toContain('alpha-feature');
});

it('relevant finds the alpha feature and stale detects source changes', function () {
    $fx = projectIndexFixture();
    $fx['indexer']->build(true);
    $query = new IndexQuery($fx['store'], $fx['indexer']);

    $relevant = $query->relevant('servicio alpha');
    expect($relevant['matches'][0]['id'])->toBe('alpha-feature');

    expect($fx['indexer']->staleReport()['stale'])->toBeFalse();

    file_put_contents($fx['root'].'/app/Beta/ServiceB.php', "<?php\nnamespace App\\Beta;\nclass ServiceB { public int \$n = 1; }\n");

    $stale = $fx['indexer']->staleReport();
    expect($stale['stale'])->toBeTrue()
        ->and($stale['reasons'])->toContain('source-files-changed');
});

it('refreshIfStale skips rebuild when current and rebuilds when a file changes', function () {
    $fx = projectIndexFixture();
    $fx['indexer']->build(true);

    $current = $fx['indexer']->refreshIfStale();
    expect($current['refreshed'])->toBeFalse()->and($current['mode'])->toBe('current');

    file_put_contents($fx['root'].'/app/Beta/ServiceB.php', "<?php\nnamespace App\\Beta;\nclass ServiceB { public int \$n = 2; }\n");

    $refreshed = $fx['indexer']->refreshIfStale();
    expect($refreshed['refreshed'])->toBeTrue();
});

it('GitHookInstaller copies hooks into .git/hooks', function () {
    $fx = projectIndexFixture();
    $installer = new GitHookInstaller;
    $installed = $installer->install($fx['root']);

    expect($installed)->toContain('post-commit')
        ->and(is_file($fx['root'].'/.git/hooks/post-commit'))->toBeTrue();
});
