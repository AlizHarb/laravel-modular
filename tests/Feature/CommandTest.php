<?php

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('composer.json'));
    File::delete(base_path('config/modular.php'));
    File::deleteDirectory(base_path('vendor'));
    File::deleteDirectory(storage_path('framework/coverage'));
});

it('can create a new module with standard structure', function () {
    $this->artisan('make:module', ['name' => 'TestCommandModule'])
        ->assertExitCode(0);

    // Refresh registry to pickup new module
    app()->forgetInstance(ModuleRegistry::class);

    $base = base_path('modules/TestCommandModule');

    expect(File::exists($base.'/module.json'))->toBeTrue()
        ->and(File::exists($base.'/composer.json'))->toBeTrue()
        ->and(File::exists($base.'/app/Http/Controllers'))->toBeTrue()
        ->and(File::exists($base.'/app/Models'))->toBeTrue()
        ->and(File::exists($base.'/database/migrations'))->toBeTrue()
        ->and(File::exists($base.'/resources/views'))->toBeTrue()
        ->and(File::exists($base.'/.gitignore'))->toBeTrue()
        ->and(File::exists($base.'/.gitattributes'))->toBeTrue();

    $moduleJson = json_decode(File::get($base.'/module.json'), true);
    expect($moduleJson['name'])->toBe('TestCommandModule');
});

it('can create a controller in the module app directory', function () {
    $this->artisan('make:module', ['name' => 'TestCommandModule']);

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    config(['modular.paths.modules' => base_path('modules')]);

    $this->artisan('make:controller', [
        'name' => 'TestController',
        '--module' => 'TestCommandModule',
    ])->assertExitCode(0);

    $file = base_path('modules/TestCommandModule/app/Http/Controllers/TestController.php');
    expect(File::exists($file))->toBeTrue();
    expect(File::get($file))->toContain('class TestController');
});

it('modular:doctor reports healthy when all requirements are met', function () {
    File::ensureDirectoryExists(base_path('config'));
    File::put(base_path('composer.json'), json_encode([
        'autoload' => [
            'psr-4' => [
                'App\\' => 'app/',
                'Modules\\' => 'modules/',
            ],
        ],
    ]));
    File::put(base_path('config/modular.php'), '<?php return [];');
    File::ensureDirectoryExists(public_path('modules'));

    $this->artisan('modular:doctor')
        ->expectsOutput('Running Modular Doctor...')
        ->assertExitCode(0);
});

it('modular:doctor warns when modules directory is missing', function () {
    config(['modular.paths.modules' => base_path('non-existent')]);

    $this->artisan('modular:doctor')
        ->expectsOutput('Doctor found some issues. Please review the warnings above.')
        ->assertExitCode(1);
});

it('modular:test runs tests for a module', function () {
    $modulePath = base_path('modules/Blog');
    File::ensureDirectoryExists($modulePath.'/tests');
    File::put($modulePath.'/module.json', json_encode(['name' => 'Blog']));
    File::put($modulePath.'/tests/ExampleTest.php', '<?php namespace Modules\Blog\Tests; use AlizHarb\Modular\Tests\TestCase; class ExampleTest extends TestCase { public function test_basic() { $this->assertTrue(true); } }');

    // Create a mock pest binary in the test app's vendor/bin
    File::ensureDirectoryExists(base_path('vendor/bin'));
    File::put(base_path('vendor/bin/pest'), "#!/usr/bin/env php\n<?php exit(0);");
    chmod(base_path('vendor/bin/pest'), 0755);

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('modular:test Blog')
        ->expectsOutputToContain('Running tests for module [Blog]...')
        ->assertExitCode(0);
});

it('modular:test handles coverage flags gracefully', function () {
    $modulePath = base_path('modules/Blog');
    File::ensureDirectoryExists($modulePath.'/tests');
    File::put($modulePath.'/module.json', json_encode(['name' => 'Blog']));

    // Refresh registry
    app()->forgetInstance(ModuleRegistry::class);

    $command = $this->artisan('modular:test Blog --coverage');

    if (! extension_loaded('pcov') && ! extension_loaded('xdebug')) {
        $command->expectsOutputToContain('Coverage requires PCOV or Xdebug extension.')
            ->assertExitCode(1);
    } else {
        // If coverage is available, it might still fail if pest is missing, but we mock it above.
        // But this is a separate test case, let's also mock it here.
        File::ensureDirectoryExists(base_path('vendor/bin'));
        File::put(base_path('vendor/bin/pest'), "#!/usr/bin/env php\n<?php exit(0);");
        chmod(base_path('vendor/bin/pest'), 0755);

        $command->expectsOutputToContain('Running tests for module [Blog]...')
            ->assertExitCode(0);
    }
});
