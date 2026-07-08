<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    // We don't set paths here to avoid early base_path() calls
});

afterEach(function () {
    File::delete(base_path('vite.config.js'));
    File::delete(base_path('composer.json'));
    File::delete(base_path('vite.modular.js'));
    File::delete(base_path('vite.base.js'));
    File::delete(base_path('phpunit.xml'));
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

    $phpunitXml = base_path('phpunit.xml');
    File::put($phpunitXml, '<?xml version="1.0" encoding="UTF-8"?><phpunit><testsuites><testsuite name="Feature"><directory suffix="Test.php">./tests/Feature</directory></testsuite><testsuite name="Unit"><directory suffix="Test.php">./tests/Unit</directory></testsuite></testsuites></phpunit>');

    $this->artisan('modular:install')
        ->expectsConfirmation('Would you like to publish the modular stubs for customization?', 'yes')
        ->expectsConfirmation("Would you like to add optimized PSR-4 autoloading for 'Modules\\' to your composer.json?", 'yes')
        ->expectsConfirmation('Would you like to automatically configure it?', 'yes')
        ->expectsConfirmation('Would you like to automatically configure vite.config.js?', 'yes')
        ->expectsConfirmation('Would you like to automatically configure PHPUnit to run module tests natively?', 'yes')
        ->expectsConfirmation('Would you like to add a "test" script to your composer.json?', 'yes')
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    expect(File::exists($viteLoader))->toBeTrue();

    $config = File::get($viteConfig);
    expect($config)->toContain("import { modularLoader } from './vite.modular.js'");
    expect($config)->toContain('...modularLoader.inputs()');

    $phpunitContent = File::get($phpunitXml);
    expect($phpunitContent)->toContain('./modules/*/tests/Feature');
    expect($phpunitContent)->toContain('./modules/*/tests/Unit');
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

    $phpunitXml = base_path('phpunit.xml');
    File::put($phpunitXml, '<?xml version="1.0" encoding="UTF-8"?><phpunit><testsuites><testsuite name="Feature"><directory suffix="Test.php">./tests/Feature</directory></testsuite><testsuite name="Unit"><directory suffix="Test.php">./tests/Unit</directory></testsuite></testsuites></phpunit>');

    $this->artisan('modular:install')
        ->expectsConfirmation('Would you like to publish the modular stubs for customization?', 'no')
        ->expectsConfirmation("Would you like to add optimized PSR-4 autoloading for 'Modules\\' to your composer.json?", 'no')
        ->expectsConfirmation('Would you like to automatically configure it?', 'no')
        ->expectsConfirmation('Would you like to automatically configure vite.config.js?', 'no')
        ->expectsConfirmation('Would you like to automatically configure PHPUnit to run module tests natively?', 'no')
        ->expectsConfirmation('Would you like to add a "test" script to your composer.json?', 'no')
        ->expectsConfirmation('Would you like to show some love by starring the repo on GitHub? ⭐', 'no')
        ->assertExitCode(0);

    expect(File::exists($viteLoader))->toBeTrue();

    $config = File::get($viteConfig);
    expect($config)->not->toContain("import { modularLoader } from './vite.modular.js'");

    $phpunitContent = File::get($phpunitXml);
    expect($phpunitContent)->not->toContain('./modules/*/tests/Feature');
    expect($phpunitContent)->not->toContain('./modules/*/tests/Unit');
});

it('can preview install changes without writing files', function () {
    $viteConfig = base_path('vite.config.js');
    $composerJson = base_path('composer.json');
    $phpUnitXml = base_path('phpunit.xml');

    $originalVite = "import { defineConfig } from 'vite';\nimport laravel from 'laravel-vite-plugin';\n\nexport default defineConfig({\n    plugins: [\n        laravel({\n            input: ['resources/css/app.css', 'resources/js/app.js'],\n            refresh: true,\n        }),\n    ],\n});";
    $originalComposer = json_encode([
        'autoload' => [
            'psr-4' => [
                'App\\' => 'app/',
            ],
        ],
    ], JSON_PRETTY_PRINT);
    $originalPhpUnit = '<?xml version="1.0" encoding="UTF-8"?><phpunit><testsuites><testsuite name="Feature"><directory suffix="Test.php">./tests/Feature</directory></testsuite><testsuite name="Unit"><directory suffix="Test.php">./tests/Unit</directory></testsuite></testsuites></phpunit>';

    File::put($viteConfig, $originalVite);
    File::put($composerJson, $originalComposer);
    File::put($phpUnitXml, $originalPhpUnit);

    $this->artisan('modular:install --dry-run')
        ->expectsOutputToContain('Dry run enabled. No files will be changed and no resources will be published.')
        ->expectsOutputToContain('Laravel Modular install preview completed.')
        ->assertExitCode(0);

    expect(File::get($viteConfig))->toBe($originalVite);
    expect(File::get($composerJson))->toBe($originalComposer);
    expect(File::get($phpUnitXml))->toBe($originalPhpUnit);
    expect(File::exists(base_path('vite.modular.js')))->toBeFalse();
    expect(File::exists(base_path('vite.base.js')))->toBeFalse();
});
