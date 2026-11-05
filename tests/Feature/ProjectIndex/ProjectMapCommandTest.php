<?php

use Illuminate\Support\Facades\Artisan;

it('generates a real codebase map markdown file', function () {
    $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fesc-codebase-map-'.bin2hex(random_bytes(4)).'.md';

    $exit = Artisan::call('project:map', [
        '--output' => $output,
    ]);

    expect($exit)->toBe(0)
        ->and(is_file($output))->toBeTrue();

    $contents = (string) file_get_contents($output);
    expect($contents)->toContain('# Codebase Map')
        ->and($contents)->toContain('app/')
        ->and($contents)->not->toContain("\n├── vendor/")
        ->and($contents)->not->toContain("\n└── vendor/")
        ->and($contents)->not->toMatch('/^\s*\.env$/m');

    @unlink($output);
});
