<?php

declare(strict_types=1);

namespace AlizHarb\Modular;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Traits\Macroable;

final class ModuleRegistry
{
    use Macroable;

    /**
     * @var array<string, array{path: string, name: string, namespace: string, provider: ?string}>
     */
    protected array $modules = [];

    /**
     * Create a new module registry instance.
     */
    public function __construct()
    {
        $this->discoverModules();
    }

    /**
     * Discover all available modules in the configured paths.
     */
    public function discoverModules(): void
    {
        $path = config('modular.paths.modules', base_path('modules'));

        if (! is_string($path) || ! File::isDirectory($path)) {
            return;
        }

        $directories = File::directories($path);

        foreach ($directories as $directory) {
            $moduleJsonPath = $directory.'/module.json';

            if (File::exists($moduleJsonPath)) {
                $content = File::get($moduleJsonPath);
                /** @var array<string, mixed> $config */
                $config = json_decode($content, true) ?: [];

                if (($config['active'] ?? true)) {
                    $name = (string) ($config['name'] ?? basename($directory));
                    $namespace = (string) ($config['namespace'] ?? "Modules\\{$name}\\");
                    $provider = isset($config['provider']) ? (string) $config['provider'] : null;

                    $this->modules[$name] = [
                        'path' => $directory,
                        'name' => $name,
                        'namespace' => $namespace,
                        'provider' => $provider,
                    ];
                }
            } else {
                $name = basename($directory);
                $this->modules[$name] = [
                    'path' => $directory,
                    'name' => $name,
                    'namespace' => "Modules\\{$name}\\",
                    'provider' => null,
                ];
            }
        }
    }

    /**
     * @return array{path: string, name: string, namespace: string, provider: ?string}|null
     */
    public function getModule(string $name): ?array
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * @return array<string, array{path: string, name: string, namespace: string, provider: ?string}>
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Check if a module exists in the registry.
     *
     * @param  string  $name
     * @return bool
     */
    public function moduleExists(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    public function resolveNamespace(string $module, string $class): string
    {
        $moduleData = $this->getModule($module);
        
        if (! $moduleData) {
            return "Modules\\{$module}\\{$class}";
        }

        return rtrim($moduleData['namespace'], '\\').'\\'.trim($class, '\\');
    }

    public function resolvePath(string $module, string $path = ''): string
    {
        $moduleData = $this->getModule($module);
        
        if (! $moduleData) {
            return base_path("modules/{$module}/".trim($path, '/'));
        }

        return $moduleData['path'].'/'.trim($path, '/');
    }
}
