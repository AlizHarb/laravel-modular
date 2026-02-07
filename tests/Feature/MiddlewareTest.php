<?php

namespace AlizHarb\Modular\Tests\Feature;

use AlizHarb\Modular\ModuleRegistry;
use AlizHarb\Modular\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class MiddlewareTest extends TestCase
{
    protected string $modulePath;

    protected string $moduleName = 'MiddlewareTestModule';

    protected string $testModulesPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testModulesPath = base_path('tests/temp_middleware_modules');
        $this->modulePath = "{$this->testModulesPath}/{$this->moduleName}";

        // Clear cached registry
        $cachePath = base_path('bootstrap/cache/modular.php');
        if (File::exists($cachePath)) {
            File::delete($cachePath);
        }

        Config::set('modular.paths.modules', $this->testModulesPath);
        Config::set('modular.cache.path', $cachePath);

        if (File::exists($this->testModulesPath)) {
            File::deleteDirectory($this->testModulesPath);
        }
        File::makeDirectory($this->testModulesPath, 0755, true);

        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry());
    }

    protected function tearDown(): void
    {
        if (File::exists($this->testModulesPath)) {
            File::deleteDirectory($this->testModulesPath);
        }
        parent::tearDown();
    }

    public function test_it_registers_middleware_from_module_json()
    {
        // 1. Create Module
        File::makeDirectory($this->modulePath, 0755, true);

        // 2. Mock Middleware Class (We don't need real class if we just check router registration aliases,
        // but for safety let's assume it's a string)
        $middlewareClass = 'Modules\MiddlewareTestModule\Http\Middleware\TestMiddleware';

        File::put("{$this->modulePath}/module.json", json_encode([
            'name' => $this->moduleName,
            'active' => true,
            'middleware' => [
                'test.alias' => $middlewareClass,
            ],
        ]));

        // 3. Register
        $sp = new \AlizHarb\Modular\ModularServiceProvider($this->app);
        // Reflection to call protected method if needed, or just call packageRegistered?
        // Typically packageRegistered registers configs and providers.
        // We added registerModuleMiddleware to packageRegistered.
        $sp->packageRegistered();

        // 4. Verify
        $router = $this->app['router'];
        $aliases = $router->getMiddleware();

        $this->assertArrayHasKey('test.alias', $aliases);
        $this->assertEquals($middlewareClass, $aliases['test.alias']);
    }
}
