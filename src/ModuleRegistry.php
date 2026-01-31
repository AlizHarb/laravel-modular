<?php

declare(strict_types=1);

namespace AlizHarb\Modular;

use AlizHarb\Modular\Contracts\Activator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Traits\Macroable;

final class ModuleRegistry
{
    use Macroable;

    /**
     * The collection of discovered modules.
     *
     * @var array<string, array{
     *     path: string,
     *     name: string,
     *     namespace: string,
     *     providers: array<int, string>,
     *     middleware: array<int, string>,
     *     requires: array<int, string>,
     *     version: string,
     *     authors: array<int, array{name: string, email?: string, role?: string}>,
     *     removable: bool,
     *     disableable: bool,
     *     policies?: array<string, string>,
     *     events?: array<string, array<int, string>|string>,
     *     has_views?: bool,
     *     has_translations?: bool,
     *     has_migrations?: bool,
     *     discovery_info?: array{
     *         policies: array<string, string>,
     *         events: array<string, string>
     *     }
     * }>
     */
    protected array $modules = [];

    /**
     * The module activator instance.
     */
    protected ?Activator $activator = null;

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
        $cachePath = config('modular.cache.path', base_path('bootstrap/cache/modular.php'));

        if (file_exists($cachePath)) {
            $this->modules = require $cachePath;

            return;
        }

        $path = config('modular.paths.modules', base_path('modules'));

        if (! is_string($path) || ! File::isDirectory($path)) {
            return;
        }

        $directories = File::directories($path);
        $activator = $this->getActivator();

        foreach ($directories as $directory) {
            $moduleJsonPath = $directory.'/module.json';
            $name = basename($directory);

            if (! $activator->isEnabled($name)) {
                continue;
            }

            if (File::exists($moduleJsonPath)) {
                $content = File::get($moduleJsonPath);
                /** @var array<string, mixed> $config */
                $config = json_decode($content, true) ?: [];

                $name = (string) ($config['name'] ?? $name);
                $namespace = (string) ($config['namespace'] ?? "Modules\\{$name}\\");
                $providers = isset($config['providers']) ? (array) $config['providers'] : [];
                if (isset($config['provider'])) {
                    $providers[] = (string) $config['provider'];
                }

                $middleware = isset($config['middleware']) ? (array) $config['middleware'] : [];

                $requires = (array) ($config['requires'] ?? []);
                $version = (string) ($config['version'] ?? '1.0.0');
                $authors = (array) ($config['authors'] ?? []);
                $removable = (bool) ($config['removable'] ?? true);
                $disableable = (bool) ($config['disableable'] ?? true);

                $this->modules[$name] = [
                    'path' => $directory,
                    'name' => $name,
                    'namespace' => $namespace,
                    'providers' => $providers,
                    'middleware' => $middleware,
                    'requires' => $requires,
                    'version' => $version,
                    'authors' => $authors,
                    'removable' => $removable,
                    'disableable' => $disableable,
                    'policies' => $config['policies'] ?? [],
                    'events' => $config['events'] ?? [],
                    'has_views' => false,
                    'has_translations' => false,
                    'has_migrations' => false,
                    'discovery_info' => [
                        'policies' => [],
                        'events' => [],
                    ],
                ];
            } else {
                $this->modules[$name] = [
                    'path' => $directory,
                    'name' => $name,
                    'namespace' => "Modules\\{$name}\\",
                    'providers' => [],
                    'middleware' => [],
                    'requires' => [],
                    'version' => '1.0.0',
                    'authors' => [],
                    'removable' => true,
                    'disableable' => true,
                    'policies' => [],
                    'events' => [],
                    'has_views' => false,
                    'has_translations' => false,
                    'has_migrations' => false,
                    'discovery_info' => [
                        'policies' => [],
                        'events' => [],
                    ],
                ];
            }
        }
    }

    /**
     * Get the module activator instance.
     */
    public function getActivator(): Activator
    {
        if ($this->activator === null) {
            $activatorName = config('modular.activator', 'file');
            $activatorClass = config("modular.activators.{$activatorName}.class");

            if ($activatorClass) {
                $this->activator = app($activatorClass);
            }
        }

        return $this->activator;
    }

    /**
     * Cache the current module registry state.
     */
    public function cache(): void
    {
        $cachePath = config('modular.cache.path', base_path('bootstrap/cache/modular.php'));
        $content = '<?php return '.var_export($this->modules, true).';'.PHP_EOL;

        File::put($cachePath, $content);
    }

    /**
     * Clear the modular discovery cache.
     */
    public function clearCache(): void
    {
        $cachePath = config('modular.cache.path', base_path('bootstrap/cache/modular.php'));

        if (File::exists($cachePath)) {
            File::delete($cachePath);
        }
    }

    /**
     * Get the metadata for a specific module.
     *
     * @return array{path: string, name: string, namespace: string, providers: array<int, string>, middleware: array<int, string>, requires: array<int, string>, version: string, authors: array<int, array{name: string, email?: string, role?: string}>, removable: bool, disableable: bool, policies?: array<string, string>, events?: array<string, array<int, string>|string>, has_views?: bool, has_translations?: bool, has_migrations?: bool}|null
     */
    public function getModule(string $name): ?array
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all registered modules.
     *
     * @return array<string, array>
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Check if a module exists in the registry.
     */
    public function moduleExists(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Resolve a fully qualified class name for a module.
     */
    public function resolveNamespace(string $module, string $class): string
    {
        $moduleData = $this->getModule($module);

        if (! $moduleData) {
            return "Modules\\{$module}\\{$class}";
        }

        return rtrim($moduleData['namespace'], '\\').'\\'.trim($class, '\\');
    }

    /**
     * Resolve the absolute path to a module resource.
     */
    public function resolvePath(string $module, string $path = ''): string
    {
        $moduleData = $this->getModule($module);

        if (! $moduleData) {
            return base_path("modules/{$module}/".trim($path, '/'));
        }

        return $moduleData['path'].'/'.trim($path, '/');
    }

    /**
     * Set the discovered resource mappings for a module.
     *
     * @param  array<string, string>  $policies
     * @param  array<int, string>  $events
     */
    public function setDiscoveredResources(string $moduleName, array $policies, array $events): void
    {
        if (isset($this->modules[$moduleName])) {
            $this->modules[$moduleName]['policies'] = $policies;
            $this->modules[$moduleName]['events'] = $events;
        }
    }

    /**
     * Get the cached policies for a module.
     *
     * @return array<string, string>
     */
    public function getDiscoveredPolicies(string $moduleName): array
    {
        return $this->modules[$moduleName]['policies'] ?? [];
    }

    /**
     * Get the cached event listeners for a module.
     *
     * @return array<string, array<int, string>|string>
     */
    public function getDiscoveredEvents(string $moduleName): array
    {
        return $this->modules[$moduleName]['events'] ?? [];
    }

    /**
     * Set the existence flags for module resources.
     */
    public function setDiscoveredFlags(string $moduleName, bool $views, bool $translations, bool $migrations): void
    {
        if (isset($this->modules[$moduleName])) {
            $this->modules[$moduleName]['has_views'] = $views;
            $this->modules[$moduleName]['has_translations'] = $translations;
            $this->modules[$moduleName]['has_migrations'] = $migrations;
        }
    }

    /**
     * Determine if the module has views.
     */
    public function hasViews(string $moduleName): bool
    {
        return $this->modules[$moduleName]['has_views'] ?? false;
    }

    /**
     * Determine if the module has translations.
     */
    public function hasTranslations(string $moduleName): bool
    {
        return $this->modules[$moduleName]['has_translations'] ?? false;
    }

    /**
     * Determine if the module has migrations.
     */
    public function hasMigrations(string $moduleName): bool
    {
        return $this->modules[$moduleName]['has_migrations'] ?? false;
    }

    /**
     * Set discovery source information for a specific resource type.
     */
    public function setDiscoverySource(string $moduleName, string $type, string $resource, string $source): void
    {
        if (isset($this->modules[$moduleName])) {
            $this->modules[$moduleName]['discovery_info'][$type][$resource] = $source;
        }
    }

    /**
     * Get discovery source information for a module.
     *
     * @return array{policies: array<string, string>, events: array<string, string>}
     */
    public function getDiscoveryInfo(string $moduleName): array
    {
        return $this->modules[$moduleName]['discovery_info'] ?? ['policies' => [], 'events' => []];
    }
}
