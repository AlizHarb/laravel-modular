<?php

declare(strict_types=1);

use AlizHarb\Modular\Facades\Modular;
use AlizHarb\Modular\ModuleRegistry;

if (! function_exists('module')) {
    /**
     * Get the module registry or a specific module configuration.
     *
     * @return ModuleRegistry|array<string, mixed>|null
     */
    function module(?string $name = null): mixed
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);

        if (is_null($name)) {
            return $registry;
        }

        return $registry->getModule($name);
    }
}

if (! function_exists('module_path')) {
    /**
     * Get the absolute path to a module or a file within a module.
     */
    function module_path(string $module, string $path = ''): string
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);

        return $registry->resolvePath($module, $path);
    }
}

if (! function_exists('module_config_path')) {
    /**
     * Get the absolute path to a module configuration file.
     */
    function module_config_path(string $module, string $path = ''): string
    {
        return module_path($module, 'config/'.trim($path, '/'));
    }
}

if (! function_exists('module_asset')) {
    /**
     * Get the URL for a modular asset.
     */
    function module_asset(string $module, string $path): string
    {
        $module = strtolower($module);
        $assetPath = config('modular.paths.assets', 'modules');

        return asset("{$assetPath}/{$module}/".ltrim($path, '/'));
    }
}

if (! function_exists('modular_vite')) {
    /**
     * Get the Vite tags for modular assets.
     *
     * @param  string|array<int, string>  $entryPoints
     * @return \Illuminate\Support\HtmlString
     */
    function modular_vite(string|array $entryPoints, ?string $buildDirectory = null): mixed
    {
        $buildDirectory = $buildDirectory ?: config('modular.paths.assets', 'modules');

        /** @var \Illuminate\Foundation\Vite $vite */
        $vite = app(\Illuminate\Foundation\Vite::class);

        return $vite($entryPoints, $buildDirectory);
    }
}
