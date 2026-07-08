<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    app()->forgetInstance(ModuleRegistry::class);
});

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    app()->forgetInstance(ModuleRegistry::class);
});

it('shows modular status', function () {
    File::ensureDirectoryExists(base_path('modules/Billing'));
    File::put(base_path('modules/Billing/module.json'), json_encode([
        'name' => 'Billing',
        'version' => '1.0.0',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:status')
        ->expectsOutputToContain('Modular Status')
        ->assertExitCode(0);
});

it('shows modular status as json', function () {
    File::ensureDirectoryExists(base_path('modules/Billing'));
    File::put(base_path('modules/Billing/module.json'), json_encode([
        'name' => 'Billing',
        'version' => '1.0.0',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    Artisan::call('modular:status', ['--json' => true]);

    expect(Artisan::output())
        ->toContain('"modules": 1')
        ->toContain('"enabled": 1');
});

it('renders dependency graphs', function () {
    File::ensureDirectoryExists(base_path('modules/Core'));
    File::put(base_path('modules/Core/module.json'), json_encode([
        'name' => 'Core',
        'version' => '1.0.0',
    ]));

    File::ensureDirectoryExists(base_path('modules/Shop'));
    File::put(base_path('modules/Shop/module.json'), json_encode([
        'name' => 'Shop',
        'version' => '1.0.0',
        'requires' => ['Core'],
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:graph')
        ->expectsOutputToContain('Shop -> Core')
        ->assertExitCode(0);

    $this->artisan('modular:graph --format=dot')
        ->expectsOutputToContain('"Shop" -> "Core";')
        ->assertExitCode(0);
});

it('explains why a module exists', function () {
    File::ensureDirectoryExists(base_path('modules/Core'));
    File::put(base_path('modules/Core/module.json'), json_encode([
        'name' => 'Core',
        'version' => '1.0.0',
    ]));

    File::ensureDirectoryExists(base_path('modules/Shop'));
    File::put(base_path('modules/Shop/module.json'), json_encode([
        'name' => 'Shop',
        'version' => '1.0.0',
        'requires' => ['Core'],
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:why Core')
        ->expectsOutputToContain('Why module [Core]?')
        ->expectsOutputToContain('Shop')
        ->assertExitCode(0);
});

it('fails checks when conflicting modules are enabled', function () {
    File::ensureDirectoryExists(base_path('modules/LegacyShop'));
    File::put(base_path('modules/LegacyShop/module.json'), json_encode([
        'name' => 'LegacyShop',
        'version' => '1.0.0',
    ]));

    File::ensureDirectoryExists(base_path('modules/Shop'));
    File::put(base_path('modules/Shop/module.json'), json_encode([
        'name' => 'Shop',
        'version' => '1.0.0',
        'conflicts' => ['LegacyShop'],
        'provides' => ['commerce'],
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $module = app(ModuleRegistry::class)->getModule('Shop');

    expect($module['conflicts'])->toContain('LegacyShop');

    $this->artisan('modular:check')
        ->expectsOutputToContain('Dependency violations found!')
        ->assertExitCode(1);
});
