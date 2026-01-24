<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Concerns;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

trait HasResources
{
    /**
     * Register modular resources during the registration phase.
     */
    protected function registerModularResources(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $moduleName => $module) {
            $this->loadModuleConfigs($moduleName, $registry);
        }
    }

    /**
     * Boot modular resources during the booting phase.
     */
    protected function bootModularResources(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $moduleName => $module) {
            $lowerName = strtolower($moduleName);

            $this->loadModuleViews($moduleName, $lowerName, $registry);
            $this->loadModuleTranslations($moduleName, $lowerName, $registry);
            $this->loadModuleMigrations($moduleName, $registry);
        }

        $this->app->booted(fn () => $this->bootModuleRoutes($registry, $modules));
    }

    /**
     * Load configuration files for a specific module.
     */
    protected function loadModuleConfigs(string $moduleName, ModuleRegistry $registry): void
    {
        $lowerName = strtolower($moduleName);
        $configPath = $registry->resolvePath($moduleName, 'config');

        if (is_dir($configPath)) {
            foreach (File::files($configPath) as $file) {
                $configName = $file->getBasename('.php');
                $this->mergeConfigFrom($file->getRealPath(), "{$lowerName}::{$configName}");
            }
        }
    }

    /**
     * Load views and components for a specific module.
     */
    protected function loadModuleViews(string $moduleName, string $lowerName, ModuleRegistry $registry): void
    {
        $viewsPath = $registry->resolvePath($moduleName, 'resources/views');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, $lowerName);

            $componentPath = $viewsPath . '/components';
            if (is_dir($componentPath)) {
                Blade::anonymousComponentPath($componentPath, $lowerName);
            }
        }
    }

    /**
     * Load translations for a specific module.
     */
    protected function loadModuleTranslations(string $moduleName, string $lowerName, ModuleRegistry $registry): void
    {
        $langPath = $registry->resolvePath($moduleName, 'lang');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $lowerName);
        }
    }

    /**
     * Load migrations for a specific module.
     */
    protected function loadModuleMigrations(string $moduleName, ModuleRegistry $registry): void
    {
        $migrationPath = $registry->resolvePath($moduleName, 'database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Boot routes for all modules.
     *
     * @param  ModuleRegistry  $registry
     * @param  array<string, mixed>  $modules
     */
    protected function bootModuleRoutes(ModuleRegistry $registry, array $modules): void
    {
        foreach ($modules as $moduleName => $module) {
            $providerClass = $module['provider'] ?? '';
            if ($providerClass && class_exists($providerClass)) {
                $this->app->register($providerClass);
            }

            $routesPath = $registry->resolvePath($moduleName, 'routes');
            
            if (file_exists($routesPath . '/web.php')) {
                $this->loadRoutesFrom($routesPath . '/web.php');
            }
            
            if (file_exists($routesPath . '/api.php')) {
                $this->loadRoutesFrom($routesPath . '/api.php');
            }
        }
    }

    /**
     * Get the modular registry instance.
     */
    protected function getModuleRegistry(): ModuleRegistry
    {
        return $this->app->make(ModuleRegistry::class);
    }
}

