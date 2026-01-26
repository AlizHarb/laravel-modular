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

            if (config('modular.discovery.policies', true)) {
                $this->discoverModulePolicies($moduleName, $registry);
            }

            if (config('modular.discovery.events', true)) {
                $this->discoverModuleEvents($moduleName, $registry);
            }
        }

        $this->registerThemerIntegration($registry, $modules);
    }

    /**
     * Discover and register policies within a module.
     */
    protected function discoverModulePolicies(string $moduleName, ModuleRegistry $registry): void
    {
        $cachedPolicies = $registry->getDiscoveredPolicies($moduleName);

        if (! empty($cachedPolicies)) {
            foreach ($cachedPolicies as $model => $policy) {
                \Illuminate\Support\Facades\Gate::policy($model, $policy);
            }

            return;
        }

        $policyPath = $registry->resolvePath($moduleName, 'app/Policies');
        if (! is_dir($policyPath)) {
            return;
        }

        foreach (File::allFiles($policyPath) as $file) {
            $className = $file->getBasename('.php');
            $module = $registry->getModule($moduleName);
            $policyClass = rtrim($module['namespace'], '\\')."\\Policies\\{$className}";

            if (class_exists($policyClass)) {
                $modelName = str_replace('Policy', '', $className);
                $modelClass = rtrim($module['namespace'], '\\')."\\Models\\{$modelName}";

                if (class_exists($modelClass)) {
                    \Illuminate\Support\Facades\Gate::policy($modelClass, $policyClass);
                }
            }
        }
    }

    /**
     * Discover and register event listeners within a module.
     */
    protected function discoverModuleEvents(string $moduleName, ModuleRegistry $registry): void
    {
        $cachedEvents = $registry->getDiscoveredEvents($moduleName);

        if (! empty($cachedEvents)) {
            foreach ($cachedEvents as $subscriber) {
                // We currently only support subscribers in deep discovery for simplicity
                \Illuminate\Support\Facades\Event::subscribe($subscriber);
            }

            return;
        }

        $eventsPath = $registry->resolvePath($moduleName, 'app/Listeners');
        if (! is_dir($eventsPath)) {
            return;
        }

        foreach (File::allFiles($eventsPath) as $file) {
            $className = $file->getBasename('.php');
            $module = $registry->getModule($moduleName);
            $listenerClass = rtrim($module['namespace'], '\\')."\\Listeners\\{$className}";

            if (class_exists($listenerClass)) {
                if (method_exists($listenerClass, 'subscribe')) {
                    \Illuminate\Support\Facades\Event::subscribe($listenerClass);

                    continue;
                }
            }
        }
    }

    /**
     * Register integration with Laravel Themer if available.
     *
     * @param  array<string, mixed>  $modules
     */
    protected function registerThemerIntegration(ModuleRegistry $registry, array $modules): void
    {
        if (class_exists('AlizHarb\\Themer\\ThemeServiceProvider')) {
            /** @var \AlizHarb\Themer\ThemeServiceProvider $themer */
            $themer = app('AlizHarb\\Themer\\ThemeServiceProvider');

            if (class_exists('AlizHarb\\Themer\\Plugins\\ModulesPlugin')) {
                $pluginClass = 'AlizHarb\\Themer\\Plugins\\ModulesPlugin';
                $themer::registerPlugin(new $pluginClass);
            }
        }
    }

    /**
     * Load views and components for a specific module.
     */
    protected function loadModuleViews(string $moduleName, string $lowerName, ModuleRegistry $registry): void
    {
        // Performance optimization: check cache first
        if (config('modular.cache.enabled', false) || file_exists(config('modular.cache.path'))) {
            if (! $registry->hasViews($moduleName)) {
                return;
            }
        }

        $viewsPath = $registry->resolvePath($moduleName, 'resources/views');

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, $lowerName);

            $componentPath = $viewsPath.'/components';
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
        // Performance optimization: check cache first
        if (config('modular.cache.enabled', false) || file_exists(config('modular.cache.path'))) {
            if (! $registry->hasTranslations($moduleName)) {
                return;
            }
        }

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
        // Performance optimization: check cache first
        if (config('modular.cache.enabled', false) || file_exists(config('modular.cache.path'))) {
            if (! $registry->hasMigrations($moduleName)) {
                return;
            }
        }

        $migrationPath = $registry->resolvePath($moduleName, 'database/migrations');

        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
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
