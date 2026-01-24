<?php

use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(base_path('modules/TestCommandModule'));
});

it('can create a new module with standard structure', function () {
    $this->artisan('make:module', ['name' => 'TestCommandModule'])
        ->assertExitCode(0);

    // Refresh registry to pickup new module
    app()->forgetInstance(\AlizHarb\Modular\ModuleRegistry::class);

    $base = base_path('modules/TestCommandModule');

    expect(File::exists($base.'/module.json'))->toBeTrue()
        ->and(File::exists($base.'/composer.json'))->toBeTrue()
        ->and(File::exists($base.'/app/Http/Controllers'))->toBeTrue()
        ->and(File::exists($base.'/app/Models'))->toBeTrue()
        ->and(File::exists($base.'/database/migrations'))->toBeTrue()
        ->and(File::exists($base.'/resources/views'))->toBeTrue();

    $moduleJson = json_decode(File::get($base.'/module.json'), true);
    expect($moduleJson['name'])->toBe('TestCommandModule');
});

it('can create a controller in the module app directory', function () {
    $this->artisan('make:module', ['name' => 'TestCommandModule']);

    // Refresh registry
    app()->forgetInstance(\AlizHarb\Modular\ModuleRegistry::class);

    // Refresh registry to pickup new module
    // In a test environment, this might require rebooting the app/registry
    // For now, we manually mock the registry awareness or assume the command finds it via config path

    // We need to ensure the module exists for the command to work
    config(['modular.paths.modules' => base_path('modules')]);

    $this->artisan('make:controller', [
        'name' => 'TestController',
        '--module' => 'TestCommandModule',
    ])->assertExitCode(0);

    $file = base_path('modules/TestCommandModule/app/Http/Controllers/TestController.php');
    expect(File::exists($file))->toBeTrue();
    expect(File::get($file))->toContain('class TestController');
});
