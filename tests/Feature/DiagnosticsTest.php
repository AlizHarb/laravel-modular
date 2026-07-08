<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('composer.json'));
    File::delete(base_path('config/modular.php'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    File::deleteDirectory(public_path('modules'));
    app()->forgetInstance(ModuleRegistry::class);
});

it('reports manifest validation errors through modular doctor', function () {
    File::ensureDirectoryExists(base_path('config'));
    File::put(base_path('composer.json'), json_encode([
        'autoload' => ['psr-4' => ['Modules\\' => 'modules/']],
    ]));
    File::put(base_path('config/modular.php'), '<?php return [];');
    File::ensureDirectoryExists(public_path('modules'));

    $modulePath = base_path('modules/Billing');
    File::ensureDirectoryExists($modulePath);
    File::put($modulePath.'/module.json', json_encode([
        'name' => 'Invoices',
        'version' => '1',
        'requires' => ['Accounts', 123],
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:doctor')
        ->expectsOutputToContain('Module [Invoices]: version must be a semantic version like 1.2.0.')
        ->expectsOutputToContain('Module [Invoices]: requires.1 must be a non-empty string.')
        ->expectsOutputToContain('Module [Invoices]: name [Invoices] does not match directory [Billing].')
        ->assertExitCode(1);
});

it('can output doctor diagnostics as json', function () {
    File::ensureDirectoryExists(base_path('modules/Blog'));
    File::put(base_path('modules/Blog/module.json'), json_encode([
        'name' => 'Blog',
        'version' => '1.2.0',
        'namespace' => 'Modules\\Blog\\',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $exitCode = Artisan::call('modular:doctor', ['--json' => true]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('"modules"');
    expect($output)->toContain('"name": "Blog"');
    expect($output)->toContain('"validation_errors": []');
});

it('can output debug details as json', function () {
    File::ensureDirectoryExists(base_path('modules/Shop'));
    File::put(base_path('modules/Shop/module.json'), json_encode([
        'name' => 'Shop',
        'version' => '1.2.0',
        'namespace' => 'Modules\\Shop\\',
        'provider' => 'Modules\\Shop\\Providers\\ShopServiceProvider',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $exitCode = Artisan::call('modular:debug', [
        'module' => 'Shop',
        '--json' => true,
    ]);
    $output = Artisan::output();

    expect($exitCode)->toBe(0);
    expect($output)->toContain('"name": "Shop"');
    expect($output)->toContain('"providers"');
    expect($output)->toContain('"validation_errors": []');
});

it('refreshes the modular cache in one command', function () {
    File::ensureDirectoryExists(base_path('modules/News'));
    File::put(base_path('modules/News/module.json'), json_encode([
        'name' => 'News',
        'version' => '1.2.0',
    ]));

    $this->artisan('modular:refresh')
        ->expectsOutputToContain('Modular discovery refreshed successfully.')
        ->assertExitCode(0);

    expect(File::exists(base_path('bootstrap/cache/modular.php')))->toBeTrue();
});
