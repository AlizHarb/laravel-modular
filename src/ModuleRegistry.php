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
     *     conflicts: array<int, string>,
     *     provides: array<int, string>,
     *     version: string,
     *     authors: array<int, array{name: string, email?: string, role?: string}>,
     *     removable: bool,
     *     disableable: bool,
     *     policies?: array<string, string>,
     *     route_prefix?: string,
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
     * The activation statuses of discovered modules.
     *
     * @var array<string, bool>
     */
    protected array $statuses = [];

    /**
     * Metadata stored with the cached registry.
     *
     * @var array<string, mixed>
     */
    protected array $cacheMeta = [];

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
            $cache = require $cachePath;
            $this->modules = $cache['modules'] ?? [];
            $this->statuses = $cache['statuses'] ?? [];
            $this->cacheMeta = $cache['meta'] ?? [];

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
            $dirName = basename($directory);
            $name = $dirName;
            $config = [];

            if (File::exists($moduleJsonPath)) {
                $content = File::get($moduleJsonPath);
                /** @var array<string, mixed> $config */
                $config = json_decode($content, true) ?: [];
                $name = (string) ($config['name'] ?? $dirName);
            }

            // Populate status from activator if not cached
            $this->statuses[$name] = $activator->isEnabled($name);

            $namespace = (string) ($config['namespace'] ?? "Modules\\{$name}\\");
            $providers = isset($config['providers']) ? (array) $config['providers'] : [];
            if (isset($config['provider'])) {
                $providers[] = (string) $config['provider'];
            }

            $middleware = isset($config['middleware']) ? (array) $config['middleware'] : [];

            $requires = array_values(array_filter((array) ($config['requires'] ?? []), 'is_string'));
            $conflicts = array_values(array_filter((array) ($config['conflicts'] ?? []), 'is_string'));
            $provides = array_values(array_filter((array) ($config['provides'] ?? []), 'is_string'));
            $version = (string) ($config['version'] ?? '1.0.0');
            $authors = (array) ($config['authors'] ?? []);
            $removable = (bool) ($config['removable'] ?? true);
            $disableable = (bool) ($config['disableable'] ?? true);
            $routePrefix = (string) ($config['route_prefix'] ?? '');

            $this->modules[$name] = [
                'path' => $directory,
                'name' => $name,
                'namespace' => $namespace,
                'providers' => $providers,
                'middleware' => $middleware,
                'requires' => $requires,
                'conflicts' => $conflicts,
                'provides' => $provides,
                'version' => $version,
                'authors' => $authors,
                'removable' => $removable,
                'disableable' => $disableable,
                'route_prefix' => $routePrefix,
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

            if (! is_string($activatorClass) || $activatorClass === '') {
                throw new \RuntimeException("Module activator [{$activatorName}] is not configured.");
            }

            $activator = app($activatorClass);

            if (! $activator instanceof Activator) {
                throw new \RuntimeException("Module activator [{$activatorClass}] must implement ".Activator::class.'.');
            }

            $this->activator = $activator;
        }

        return $this->activator;
    }

    /**
     * Cache the current module registry state.
     */
    public function cache(): void
    {
        $cachePath = config('modular.cache.path', base_path('bootstrap/cache/modular.php'));

        $this->cacheMeta = $this->buildCacheMeta();

        $cache = [
            'modules' => $this->modules,
            'statuses' => $this->statuses,
            'meta' => $this->cacheMeta,
        ];

        $content = '<?php return '.var_export($cache, true).';'.PHP_EOL;

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
     * Get metadata loaded from the modular discovery cache.
     *
     * @return array<string, mixed>
     */
    public function getCacheMeta(): array
    {
        return $this->cacheMeta;
    }

    /**
     * Determine if the current cache metadata differs from module files on disk.
     */
    public function cacheIsStale(): bool
    {
        if ($this->cacheMeta === []) {
            return false;
        }

        $cachedManifests = $this->cacheMeta['manifest_hashes'] ?? [];

        if (! is_array($cachedManifests)) {
            return true;
        }

        foreach ($this->modules as $name => $module) {
            $manifestPath = $module['path'].'/module.json';
            $currentHash = is_file($manifestPath) ? hash_file('sha256', $manifestPath) : null;

            if (($cachedManifests[$name] ?? null) !== $currentHash) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildCacheMeta(): array
    {
        $manifestHashes = [];
        $dependencyHashes = [];
        $providers = [];

        foreach ($this->modules as $name => $module) {
            $manifestPath = $module['path'].'/module.json';
            $manifestHashes[$name] = is_file($manifestPath) ? hash_file('sha256', $manifestPath) : null;
            $dependencyHashes[$name] = hash('sha256', json_encode($module['requires']));
            $providers[$name] = $module['providers'];
        }

        return [
            'cached_at' => date(DATE_ATOM),
            'manifest_hashes' => $manifestHashes,
            'dependency_hashes' => $dependencyHashes,
            'providers' => $providers,
        ];
    }

    /**
     * Get the metadata for a specific module.
     *
     * @return array{path: string, name: string, namespace: string, providers: array<int, string>, middleware: array<int, string>, requires: array<int, string>, conflicts: array<int, string>, provides: array<int, string>, version: string, authors: array<int, array{name: string, email?: string, role?: string}>, removable: bool, disableable: bool, route_prefix?: string, policies?: array<string, string>, events?: array<string, array<int, string>|string>, has_views?: bool, has_translations?: bool, has_migrations?: bool}|null
     */
    public function getModule(string $name): ?array
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all registered modules in topological order (dependencies first).
     *
     * @return array<string, array>
     */
    public function getModules(): array
    {
        return $this->sortTopologically($this->modules);
    }

    /**
     * Sort modules topologically based on their dependencies.
     *
     * @param array<string, array> $modules
     * @return array<string, array>
     */
    protected function sortTopologically(array $modules): array
    {
        $ordered = [];
        $visited = [];
        $visiting = [];

        $visit = function ($name) use (&$visit, &$ordered, &$visited, &$visiting, $modules) {
            if (isset($visited[$name])) {
                return;
            }

            if (isset($visiting[$name])) {
                throw new \RuntimeException("Circular dependency detected involving module [{$name}]");
            }

            $visiting[$name] = true;

            // Sort dependencies first
            if (isset($modules[$name]['requires'])) {
                foreach ($modules[$name]['requires'] as $dependency) {
                    if (isset($modules[$dependency])) {
                        $visit($dependency);
                    }
                }
            }

            unset($visiting[$name]);
            $visited[$name] = true;
            $ordered[$name] = $modules[$name];
        };

        foreach ($modules as $name => $module) {
            if (! isset($visited[$name])) {
                $visit($name);
            }
        }

        return $ordered;
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
     * @param array<string, string> $policies
     * @param array<int, string> $events
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

    /**
     * Check if a module is enabled.
     */
    public function isEnabled(string $name): bool
    {
        if (isset($this->statuses[$name])) {
            return $this->statuses[$name];
        }

        return $this->getActivator()->isEnabled($name);
    }

    /**
     * Check if a module's dependencies are satisfied.
     *
     * @return array{satisfied: bool, missing: array<int, string>}
     */
    public function checkDependencies(string $moduleName): array
    {
        $module = $this->getModule($moduleName);

        if (! $module) {
            // If not in registry, its dependencies aren't even loadable
            return ['satisfied' => false, 'missing' => [$moduleName]];
        }

        $missing = [];
        foreach ($module['requires'] as $dependency) {
            if (! $this->moduleExists($dependency) || ! $this->isEnabled($dependency)) {
                $missing[] = $dependency;
            }
        }

        return [
            'satisfied' => empty($missing),
            'missing' => $missing,
        ];
    }
}
