<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::deleteDirectory(public_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    app()->forgetInstance(ModuleRegistry::class);
});

it('can create safe missing infrastructure with doctor fix', function () {
    File::ensureDirectoryExists(base_path('config'));
    File::put(base_path('config/modular.php'), '<?php return [];');
    File::put(base_path('composer.json'), json_encode([
        'autoload' => ['psr-4' => ['Modules\\' => 'modules/']],
    ]));
    File::deleteDirectory(base_path('modules'));
    File::deleteDirectory(public_path('modules'));

    $this->artisan('modular:doctor --fix')
        ->expectsOutputToContain('Created modules directory')
        ->expectsOutputToContain('Created module assets directory')
        ->assertExitCode(0);

    expect(File::isDirectory(base_path('modules')))->toBeTrue();
    expect(File::isDirectory(public_path('modules')))->toBeTrue();
});

it('reports stale cache when manifests change', function () {
    File::ensureDirectoryExists(base_path('modules/Reports'));
    File::put(base_path('modules/Reports/module.json'), json_encode([
        'name' => 'Reports',
        'version' => '1.0.0',
    ]));
    File::ensureDirectoryExists(public_path('modules'));

    $this->artisan('modular:cache')->assertExitCode(0);

    File::put(base_path('modules/Reports/module.json'), json_encode([
        'name' => 'Reports',
        'version' => '1.0.1',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:doctor')
        ->expectsOutputToContain('Modular discovery cache is stale.')
        ->assertExitCode(1);
});
