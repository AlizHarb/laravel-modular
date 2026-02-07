<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::ensureDirectoryExists(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    config(['modular.paths.modules' => base_path('modules')]);
});

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    File::delete(base_path('bootstrap/cache/modular.php'));
});

it('prevents enabling a module if dependencies are missing', function () {
    // Create Module B which requires Module A
    $moduleBPath = base_path('modules/ModuleB');
    File::ensureDirectoryExists($moduleBPath);
    File::put($moduleBPath.'/module.json', json_encode([
        'name' => 'ModuleB',
        'requires' => ['ModuleA'],
    ]));

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);
    $registry = app(ModuleRegistry::class);

    // Try to enable Module B
    $this->artisan('module:enable', ['module' => 'ModuleB'])
        ->expectsOutput('Cannot enable module [ModuleB]. Missing dependencies: ModuleA')
        ->assertExitCode(1);
});

it('allows enabling a module if dependencies are enabled', function () {
    // Create Module A
    $moduleAPath = base_path('modules/ModuleA');
    File::ensureDirectoryExists($moduleAPath);
    File::put($moduleAPath.'/module.json', json_encode([
        'name' => 'ModuleA',
    ]));

    // Create Module B which requires Module A
    $moduleBPath = base_path('modules/ModuleB');
    File::ensureDirectoryExists($moduleBPath);
    File::put($moduleBPath.'/module.json', json_encode([
        'name' => 'ModuleB',
        'requires' => ['ModuleA'],
    ]));

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);
    $registry = app(ModuleRegistry::class);

    // Enable Module A first
    $this->artisan('module:enable', ['module' => 'ModuleA'])
        ->assertExitCode(0);

    // Refresh registry to pickup enabled status of A
    app()->forgetInstance(ModuleRegistry::class);
    app()->forgetInstance(\AlizHarb\Modular\Activators\FileActivator::class);

    // Now enable Module B
    // We expect success because A is enabled
    $this->artisan('module:enable', ['module' => 'ModuleB'])
        ->assertExitCode(0);

    $registry = app(ModuleRegistry::class);
    expect($registry->isEnabled('ModuleB'))->toBeTrue();
});
