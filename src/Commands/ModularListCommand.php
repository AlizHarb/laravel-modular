<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

/**
 * Console command to list all modules and their discovered resources.
 */
final class ModularListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:list {--only= : Only show a specific type [modules, policies, events]}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all modules and their discovered resources';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $registry = app(ModuleRegistry::class);
        $modules = $registry->getModules();
        $only = $this->option('only');

        if (empty($modules)) {
            $this->components->warn('No modules found.');

            return self::SUCCESS;
        }

        if (!$only || $only === 'modules') {
            $this->displayModules($modules);
        }

        if (!$only || $only === 'policies') {
            $this->displayPolicies($modules, $registry);
        }

        if (!$only || $only === 'events') {
            $this->displayEvents($modules, $registry);
        }

        return self::SUCCESS;
    }

    /**
     * Display the list of modules.
     *
     * @param array<string, array> $modules
     */
    private function displayModules(array $modules): void
    {
        $this->components->info('Registered Modules');
        
        $rows = [];
        foreach ($modules as $name => $module) {
            $rows[] = [
                $name,
                $module['version'] ?? '1.0.0',
                $module['namespace'],
                str_replace(base_path() . '/', '', $module['path']),
                ($module['has_migrations'] ?? false) ? '✅' : '❌',
            ];
        }

        $this->table(['Name', 'Version', 'Namespace', 'Path', 'Migrations'], $rows);
        $this->line('');
    }

    /**
     * Display discovered policies.
     */
    private function displayPolicies(array $modules, ModuleRegistry $registry): void
    {
        $this->components->info('Discovered Policies');

        $rows = [];
        foreach ($modules as $name => $module) {
            $info = $registry->getDiscoveryInfo($name);
            foreach ($info['policies'] as $model => $source) {
                // Get policy from Gate if possible, or just show model
                $policy = 'Unknown';
                if (class_exists($model)) {
                    $policyClass = \Illuminate\Support\Facades\Gate::getPolicyFor($model);
                    $policy = $policyClass ? (is_string($policyClass) ? $policyClass : get_class($policyClass)) : 'None';
                }

                $rows[] = [
                    $name,
                    $model,
                    $policy,
                    $source,
                ];
            }
        }

        if (empty($rows)) {
            $this->line('No policies discovered.');
        } else {
            $this->table(['Module', 'Model', 'Policy', 'Source'], $rows);
        }
        $this->line('');
    }

    /**
     * Display discovered events/listeners.
     */
    private function displayEvents(array $modules, ModuleRegistry $registry): void
    {
        $this->components->info('Discovered Events/Subscribers');

        $rows = [];
        foreach ($modules as $name => $module) {
            $info = $registry->getDiscoveryInfo($name);
            foreach ($info['events'] as $listener => $source) {
                $rows[] = [
                    $name,
                    $listener,
                    $source,
                ];
            }
        }

        if (empty($rows)) {
            $this->line('No events discovered.');
        } else {
            $this->table(['Module', 'Listener/Subscriber', 'Source'], $rows);
        }
        $this->line('');
    }
}
