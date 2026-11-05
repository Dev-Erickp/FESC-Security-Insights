<?php

use Illuminate\Support\Facades\Artisan;

function tempProjectPath(): string
{
    $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fesc-pi-cmd-'.bin2hex(random_bytes(4));
    mkdir($path, 0755, true);

    return $path;
}

it('indexes the real repository into a temp path', function () {
    $path = tempProjectPath();

    $exit = Artisan::call('project:index', [
        '--full' => true,
        '--path' => $path,
    ]);

    expect($exit)->toBe(0)
        ->and(is_file($path.'/metadata.json'))->toBeTrue()
        ->and(is_file($path.'/overview.json'))->toBeTrue()
        ->and(is_file($path.'/index.json'))->toBeTrue()
        ->and(is_file($path.'/graph.json'))->toBeTrue()
        ->and(is_file($path.'/filemap.json'))->toBeTrue();

    $payload = json_decode(Artisan::output(), true);
    expect($payload['orphan_count'] ?? null)->toBe(0);
});

it('relevant finds a real domain feature with concrete entrypoints', function () {
    $path = tempProjectPath();

    Artisan::call('project:index', ['--full' => true, '--path' => $path]);

    $exit = Artisan::call('project:query', [
        'action' => 'relevant',
        '--q' => 'organización activa',
        '--path' => $path,
        '--no-refresh' => true,
    ]);

    expect($exit)->toBe(0);
    $payload = json_decode(Artisan::output(), true);
    expect($payload['matches'] ?? [])->not->toBeEmpty();

    $ids = array_column($payload['matches'], 'id');
    expect($ids)->toContain('organization-context');

    $match = collect($payload['matches'])->firstWhere('id', 'organization-context');
    expect($match['entrypoints'] ?? [])->toContain('app/Support/Tenancy/OrganizationContext.php');
});

it('keeps a known entrypoint in the filemap', function () {
    $path = tempProjectPath();
    Artisan::call('project:index', ['--full' => true, '--path' => $path]);

    $filemap = json_decode((string) file_get_contents($path.'/filemap.json'), true);
    expect($filemap['organization-context']['entrypoints'] ?? [])
        ->toContain('app/Support/Tenancy/OrganizationContext.php');
});

it('check is not stale right after a full index', function () {
    $path = tempProjectPath();
    Artisan::call('project:index', ['--full' => true, '--path' => $path]);

    $exit = Artisan::call('project:index', [
        '--check' => true,
        '--path' => $path,
    ]);

    expect($exit)->toBe(0);
    $report = json_decode(Artisan::output(), true);
    expect($report['stale'])->toBeFalse();
});

it('query --no-refresh without index fails', function () {
    $path = tempProjectPath();

    $exit = Artisan::call('project:query', [
        'action' => 'overview',
        '--path' => $path,
        '--no-refresh' => true,
    ]);

    expect($exit)->not->toBe(0);
    $payload = json_decode(Artisan::output(), true);
    expect($payload['error'] ?? '')->toContain('missing');
});

it('query without index creates it via refresh and responds', function () {
    $path = tempProjectPath();

    $exit = Artisan::call('project:query', [
        'action' => 'overview',
        '--path' => $path,
    ]);

    expect($exit)->toBe(0)
        ->and(is_file($path.'/overview.json'))->toBeTrue();

    $payload = json_decode(Artisan::output(), true);
    expect($payload['project']['id'] ?? null)->toBe('fesc-security-insights');
});

it('if-stale when current does not print updated message', function () {
    $path = tempProjectPath();
    Artisan::call('project:index', ['--full' => true, '--path' => $path]);

    Artisan::call('project:index', [
        '--if-stale' => true,
        '--path' => $path,
    ]);

    expect(trim(Artisan::output()))->toBe('');
});
