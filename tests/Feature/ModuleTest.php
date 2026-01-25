<?php

use AlizHarb\Modular\Facades\Modular;
use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->moduleName = 'FlowTestModule';
    // Use a unique temp path for this test to avoid collision
    $this->testModulesPath = base_path('tests/temp_modules');
    $this->modulePath = "{$this->testModulesPath}/{$this->moduleName}";
    
    // Clear cached registry if exists to prevent pollution from other tests
    $cachePath = base_path('bootstrap/cache/modular.php');
    if (File::exists($cachePath)) {
        File::delete($cachePath);
    }
    
    // Configure package to use this path
    Config::set('modular.paths.modules', $this->testModulesPath);
    Config::set('modular.cache.path', $cachePath); // Ensure we control the cache path too
    
    // Clean up before test
    if (File::exists($this->testModulesPath)) {
        File::deleteDirectory($this->testModulesPath);
    }
    
    // Ensure modules dir exists
    File::makeDirectory($this->testModulesPath, 0755, true);
    
    // Ensure registry is bound and refreshed with new config
    $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry);
    
    Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    Config::set('app.cipher', 'AES-256-CBC');
});

afterEach(function () {
    if (File::exists($this->testModulesPath)) {
         File::deleteDirectory($this->testModulesPath);
    }
});

it('can register the facade', function () {
    expect(app('modular.registry'))->toBeInstanceOf(\AlizHarb\Modular\ModuleRegistry::class);
});

it('can resolve a module path', function () {
    $path = Modular::resolvePath('TestModule', 'app/Http/Controllers');
    expect($path)->toContain('modules/TestModule/app/Http/Controllers');
});

it('runs the full module lifecycle', function () {
    // 1. Create Module Structure
    File::makeDirectory($this->modulePath, 0755, true);
    File::makeDirectory("{$this->modulePath}/routes", 0755, true);
    File::makeDirectory("{$this->modulePath}/config", 0755, true);
    File::makeDirectory("{$this->modulePath}/app/Providers", 0755, true);

    // 2. Create module.json
    $moduleJson = json_encode([
        'name' => $this->moduleName,
        'active' => true,
        'providers' => [
            "Modules\\{$this->moduleName}\\Providers\\TestServiceProvider"
        ],
        'middleware' => [
            'flow.middleware' => "Modules\\{$this->moduleName}\\Http\\Middleware\\FlowMiddleware"
        ]
    ]);
    File::put("{$this->modulePath}/module.json", $moduleJson);

    // 3. Create a Route
    $routeContent = "<?php use Illuminate\Support\Facades\Route; Route::get('/flow-test', fn() => 'flow-success');";
    File::put("{$this->modulePath}/routes/web.php", $routeContent);

    // 4. Create a Config
    $configContent = "<?php return ['key' => 'value'];";
    File::put("{$this->modulePath}/config/test.php", $configContent);

    // Create Middleware stub so it exists? (Not strictly needed for alias registration check but good for completeness)
    
    // 5. Create a Service Provider
    $providerContent = <<<PHP
<?php
namespace Modules\\{$this->moduleName}\\Providers;
use Illuminate\Support\ServiceProvider;
class TestServiceProvider extends ServiceProvider {
    public function boot() {
        app()->instance('flow_provider_booted', true);
    }
}
PHP;
    File::put("{$this->modulePath}/app/Providers/TestServiceProvider.php", $providerContent);
    
    // CRITICAL: Require the provider manually for the test runner
    require_once "{$this->modulePath}/app/Providers/TestServiceProvider.php";

    // 6. Boot the Package Logic
    // Re-instantiate registry to pick up new module
    $registry = new ModuleRegistry(); 
    $this->app->instance(ModuleRegistry::class, $registry);
    
    $sp = new \AlizHarb\Modular\ModularServiceProvider($this->app);
    $sp->packageRegistered();
    $sp->packageBooted();

    // 7. Verify Route
    $this->get('/flow-test')
        ->assertStatus(200)
        ->assertSee('flow-success');

    // 8. Verify Config
    // Key should be ModuleName::filename
    expect(Config::get("{$this->moduleName}::test.key"))->toBe('value');
    // Verify Alias (Case Insensitive)
    expect(Config::get(strtolower($this->moduleName) . "::test.key"))->toBe('value');
    
    // 10. Verify Toggle (Memory Solution)
    // Clean up registry to force reload config
    $this->app->forgetInstance(ModuleRegistry::class);
    $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry);
    
    // Disable aliasing
    Config::set('modular.config.alias', false);
    
    // Reload provider
    $sp = new \AlizHarb\Modular\ModularServiceProvider($this->app);
    $sp->packageRegistered();
    
    // Should NOT have lowercase alias now
    // Note: Previous run might have polluted global config even if we rebuild SP? 
    // Config::get() persists. We need to clear the specific key.
    // Actually, "lowerName" alias wouldn't be merged if we start fresh.
    // But since config is global singleton, we can't easily "unmerge". 
    // We can assume if we were running in a fresh request it would work.
    // For this test, let's just assert that the *logic* respects the config if we were to load a *new* file.
    // Creating a second config file to test this specific scenario
    File::put("{$this->modulePath}/config/memorytest.php", "<?php return ['foo' => 'bar'];");
    
    $sp->packageRegistered();
    
    // Original Name works
    expect(Config::get("{$this->moduleName}::memorytest.foo"))->toBe('bar');
    // Alias should NOT work
    expect(Config::get(strtolower($this->moduleName) . "::memorytest.foo"))->toBeNull();
    expect($this->app->has('flow_provider_booted'))->toBeTrue();
    
    // 10. Verify Middleware
    $aliases = $this->app['router']->getMiddleware();
    expect($aliases)->toHaveKey('flow.middleware');
});
