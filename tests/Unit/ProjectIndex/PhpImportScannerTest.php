<?php

use App\Support\ProjectIndex\PhpImportScanner;

it('parses simple and grouped use imports', function () {
    $scanner = new PhpImportScanner;
    $source = <<<'PHP'
        <?php
        namespace App\Demo;
        use App\Support\Tenancy\OrganizationContext;
        use App\Models\{User, Role as AppRole};
        use Illuminate\Support\Facades\Session;
        use function App\Helpers\foo;
        PHP;

    $imports = $scanner->scan($source);

    expect($imports)->toContain('App\Support\Tenancy\OrganizationContext')
        ->and($imports)->toContain('App\Models\User')
        ->and($imports)->toContain('App\Models\Role')
        ->and($imports)->not->toContain('Illuminate\Support\Facades\Session');
});

it('resolves App FQCN to existing php path', function () {
    $scanner = new PhpImportScanner;
    $path = $scanner->fqcnToPath('App\Support\Tenancy\OrganizationContext', base_path());

    expect($path)->toBe('app/Support/Tenancy/OrganizationContext.php');
});
