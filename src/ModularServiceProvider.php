<?php

declare(strict_types=1);

namespace AlizHarb\Modular;

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
    }

    /**
     * Register PSR-4 autoloading for modules.
     *
     * @return void
     */
    protected function registerAutoloading(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        if (empty($modules)) {
            return;
        }

        spl_autoload_register(function (string $class) use ($registry, $modules) {
            if (! str_starts_with($class, 'Modules\\')) {
                return;
            }

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
