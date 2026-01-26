<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModularCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster module discovery';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $this->call('modular:clear');

        $this->components->info('Discovering module resources...');

        // Deep Discovery:
        // We need to scan all modules for Policies and Events and inject them into the registry
        // BEFORE we cache it. This allows HasResources to use the cached list instead of scanning.

        $modules = $registry->getModules();

        // We need to access the protected methods of HasResources or logic to scan.
        // Since HasResources traits are usually protected, we might duplicate the scanning logic here
        // OR make HasResources methods public? Making them public is a BC break potentially but traits...
        // Better: Duplicate scanning logic specialized for caching or rely on reflection.
        // Let's implement robust scanning here to avoid runtime trait dependency issues.

        foreach ($modules as $name => $module) {
            $policies = $this->scanPolicies($registry, $name, $module);
            $events = $this->scanEvents($registry, $name, $module);

            $registry->setDiscoveredResources($name, $policies, $events);

            // Resource existence flags
            $registry->setDiscoveredFlags(
                $name,
                is_dir($registry->resolvePath($name, 'resources/views')),
                is_dir($registry->resolvePath($name, 'lang')),
                is_dir($registry->resolvePath($name, 'database/migrations'))
            );
        }

        $registry->cache();

        $this->components->info('Modular registry cached successfully with deep discovery.');

        return self::SUCCESS;
    }

    protected function scanPolicies(ModuleRegistry $registry, string $name, array $module): array
    {
        $policyPath = $registry->resolvePath($name, 'app/Policies');
        $policies = [];

        if (! is_dir($policyPath)) {
            return [];
        }

        foreach (\Illuminate\Support\Facades\File::allFiles($policyPath) as $file) {
            $className = $file->getBasename('.php');
            $policyClass = rtrim($module['namespace'], '\\')."\\Policies\\{$className}";

            if (class_exists($policyClass)) {
                $modelName = str_replace('Policy', '', $className);
                $modelClass = rtrim($module['namespace'], '\\')."\\Models\\{$modelName}";

                if (class_exists($modelClass)) {
                    $policies[$modelClass] = $policyClass;
                }
            }
        }

        return $policies;
    }

    protected function scanEvents(ModuleRegistry $registry, string $name, array $module): array
    {
        $eventsPath = $registry->resolvePath($name, 'app/Listeners');
        $events = [];

        if (! is_dir($eventsPath)) {
            return [];
        }

        foreach (\Illuminate\Support\Facades\File::allFiles($eventsPath) as $file) {
            $className = $file->getBasename('.php');
            $listenerClass = rtrim($module['namespace'], '\\')."\\Listeners\\{$className}";

            if (class_exists($listenerClass)) {
                if (method_exists($listenerClass, 'subscribe')) {
                    $events[] = $listenerClass;
                }
            }
        }

        return $events;
    }
}
