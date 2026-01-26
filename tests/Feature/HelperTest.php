<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

it('can access the module registry via helper', function () {
    expect(module())->toBeInstanceOf(ModuleRegistry::class);
});

it('can access a specific module config via helper', function () {
    $this->artisan('make:module', ['name' => 'HelperTestModule']);

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    $config = module('HelperTestModule');

    expect($config)->toBeArray()
        ->and($config['name'])->toBe('HelperTestModule');

    File::deleteDirectory(base_path('modules/HelperTestModule'));
});

it('can resolve a module path via helper', function () {
    $this->artisan('make:module', ['name' => 'PathModule']);

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    $path = module_path('PathModule', 'Resources/views');

    expect($path)->toBe(base_path('modules/PathModule/Resources/views'));

    File::deleteDirectory(base_path('modules/PathModule'));
});

it('can generate a module asset url via helper', function () {
    $url = module_asset('AssetModule', 'css/app.css');

    expect($url)->toBe('http://localhost/modules/assetmodule/css/app.css');
});

it('can resolve a module config path via helper', function () {
    $this->artisan('make:module', ['name' => 'ConfigPathModule']);

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    $path = module_config_path('ConfigPathModule', 'settings.php');

    expect($path)->toBe(base_path('modules/ConfigPathModule/config/settings.php'));

    File::deleteDirectory(base_path('modules/ConfigPathModule'));
});
