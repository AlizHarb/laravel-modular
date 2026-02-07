<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // We don't set paths here to avoid early base_path() calls
});

afterEach(function () {
    File::delete(base_path('vite.config.js'));
    File::delete(base_path('composer.json'));
    File::delete(base_path('vite.modular.js'));
});

it('can install modular and configure vite automatically', function () {
    $viteConfig = base_path('vite.config.js');
    $composerJson = base_path('composer.json');
    $viteLoader = base_path('vite.modular.js');

    File::ensureDirectoryExists(dirname($viteConfig));

    File::put($viteConfig, "import { defineConfig } from 'vite';\nimport laravel from 'laravel-vite-plugin';\n\nexport default defineConfig({\n    plugins: [\n        laravel({\n            input: ['resources/css/app.css', 'resources/js/app.js'],\n            refresh: true,\n        }),\n    ],\n});");

    File::put($composerJson, json_encode([
        'autoload' => [
            'psr-4' => [
                'App\\' => 'app/',
            ],
        ],
    ], JSON_PRETTY_PRINT));

    $this->artisan('modular:install')
        ->expectsConfirmation('Would you like to publish the modular stubs for customization?', 'yes')
        ->expectsConfirmation("Would you like to add optimized PSR-4 autoloading for 'Modules\\' to your composer.json?", 'yes')
        ->expectsConfirmation('Would you like to automatically configure it?', 'yes')
        ->expectsConfirmation('Would you like to automatically configure vite.config.js?', 'yes')
        ->expectsConfirmation('Would you like to add a "test" script to run both app and module tests?', 'yes')
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    expect(File::exists($viteLoader))->toBeTrue();

    $config = File::get($viteConfig);
    expect($config)->toContain("import { modularLoader } from './vite.modular.js'");
    expect($config)->toContain('...modularLoader.inputs()');
});

it('can install modular and show manual configuration if declined', function () {
    $viteConfig = base_path('vite.config.js');
    $composerJson = base_path('composer.json');
    $viteLoader = base_path('vite.modular.js');

    File::ensureDirectoryExists(dirname($viteConfig));

    File::put($viteConfig, "import { defineConfig } from 'vite';\nimport laravel from 'laravel-vite-plugin';\n\nexport default defineConfig({\n    plugins: [\n        laravel({\n            input: ['resources/css/app.css', 'resources/js/app.js'],\n            refresh: true,\n        }),\n    ],\n});");

    File::put($composerJson, json_encode([
        'autoload' => [
            'psr-4' => [
                'App\\' => 'app/',
            ],
        ],
    ], JSON_PRETTY_PRINT));

    $this->artisan('modular:install')
        ->expectsConfirmation('Would you like to publish the modular stubs for customization?', 'no')
        ->expectsConfirmation("Would you like to add optimized PSR-4 autoloading for 'Modules\\' to your composer.json?", 'no')
        ->expectsConfirmation('Would you like to automatically configure it?', 'no')
        ->expectsConfirmation('Would you like to automatically configure vite.config.js?', 'no')
        ->expectsConfirmation('Would you like to add a "test" script to run both app and module tests?', 'no')
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    expect(File::exists($viteLoader))->toBeTrue();

    $config = File::get($viteConfig);
    expect($config)->not->toContain("import { modularLoader } from './vite.modular.js'");
});
