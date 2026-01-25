<?php

declare(strict_types=1);

namespace AlizHarb\Modular;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class ModularServiceProvider extends PackageServiceProvider
{
    use Concerns\HasCommands;
    use Concerns\HasResources;

    /**
     * The registered modular plugins.
     *
     * @var array<string, Contracts\ModularPlugin>
     */
    protected static array $plugins = [];

    /**
     * Configure the package service provider.
     *
     * @param  Package  $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-modular')
            ->hasConfigFile('modular')
            ->hasViews();

        $this->publishes([
            __DIR__ . '/../resources/stubs' => base_path('stubs/modular'),
        ], 'modular-stubs');
    }

    /**
     * Register a modular plugin.
     *
     * @param  Contracts\ModularPlugin  $plugin
     * @return void
     */
    public static function registerPlugin(Contracts\ModularPlugin $plugin): void
    {
        self::$plugins[$plugin->getId()] = $plugin;
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry);

        $this->app->alias(ModuleRegistry::class, 'modular.registry');
        $this->app->alias('Modular', Facades\Modular::class);

        $this->registerAutoloading();
        $this->registerModularResources();
        $this->registerModuleProviders();
        $this->registerModuleConfigs();
        $this->registerModuleMiddleware();

        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach (self::$plugins as $plugin) {
            $plugin->register($this->app, $registry, $modules);
        }

        $this->registerModularCommands();
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function packageBooted(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach (self::$plugins as $plugin) {
            $plugin->boot($this->app, $registry, $modules);
        }

        $this->bootModularResources();
        $this->registerModuleRoutes();
    }

    /**
     * Register module service providers.
     * 
     * @return void
     */
    protected function registerModuleProviders(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $module) {
            foreach ($module['providers'] as $provider) {
                $this->app->register($provider);
            }
        }
    }

    /**
     * Register module configurations.
     * 
     * @return void
     */
    protected function registerModuleConfigs(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $module) {
            $configPath = $registry->resolvePath($module['name'], 'config');

            if (! File::isDirectory($configPath)) {
                continue;
            }

            foreach (File::files($configPath) as $file) {
                $filename = $file->getFilenameWithoutExtension();
                $name = $module['name'];
                $lowerName = strtolower($name);
                
                // Case-sensitive "Blog::settings"
                $this->mergeConfigFrom($file->getPathname(), "{$name}::{$filename}");
                
                // Lowercase "blog::settings" (alias)
                if (config('modular.config.alias', true) && $name !== $lowerName) {
                    $this->mergeConfigFrom($file->getPathname(), "{$lowerName}::{$filename}");
                }
            }
        }
    }

    /**
     * Register module middleware.
     * 
     * @return void
     */
    protected function registerModuleMiddleware(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();
        $router = $this->app['router'];

        foreach ($modules as $module) {
            foreach ($module['middleware'] ?? [] as $key => $middleware) {
                if (is_string($key)) {
                   if (is_array($middleware)) {
                       foreach ($middleware as $m) {
                           $router->pushMiddlewareToGroup($key, $m);
                       }
                   } else {
                       $router->aliasMiddleware($key, $middleware);
                   }
                }
            }
        }
    }

    /**
     * Register module routes.
     * 
     * @return void
     */
    protected function registerModuleRoutes(): void
    {
        if ($this->app instanceof \Illuminate\Foundation\Application && $this->app->routesAreCached()) {
            return;
        }

        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $module) {
            $routesPath = $registry->resolvePath($module['name'], 'routes');

            if (! File::isDirectory($routesPath)) {
                continue;
            }

            // Web Routes
            if (File::exists($web = "{$routesPath}/web.php")) {
                Route::middleware('web')
                    ->group($web);
            }

            // API Routes
            if (File::exists($api = "{$routesPath}/api.php")) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group($api);
            }
            
            // Channel Routes (Broadcasting)
             if (File::exists($channels = "{$routesPath}/channels.php")) {
                 require $channels;
             }
             
             // Console Routes
             if (File::exists($console = "{$routesPath}/console.php")) {
                 require $console;
             }
        }
    }

    /**
     * Register PSR-4 autoloading for modules.
     *
     * @return void
     */
    protected function registerAutoloading(): void
    {
        $registry = $this->getModuleRegistry();

        spl_autoload_register(function (string $class) use ($registry) {
            if (! str_starts_with($class, 'Modules\\')) {
                return;
            }

            $modules = $registry->getModules();

            foreach ($modules as $module) {
                $namespace = $module['namespace'];

                if (str_starts_with($class, $namespace)) {
                    $relativePath = str_replace(['\\', $namespace], ['/', ''], $class);
                    $path = $registry->resolvePath($module['name'], "app/{$relativePath}.php");

                    if (file_exists($path)) {
                        require_once $path;

                        return;
                    }
                }
            }
        }, true, true);
    }
}
