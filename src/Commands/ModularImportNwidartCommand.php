<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ModularImportNwidartCommand extends Command
{
    protected $signature = 'modular:import-nwidart
        {module? : Import only one module}
        {--from=Modules : Source directory used by nwidart/laravel-modules}
        {--to=modules : Target Laravel Modular directory}
        {--dry-run : Show the import plan without writing files}
        {--force : Overwrite existing target modules}';

    protected $description = 'Import modules from a nwidart/laravel-modules style directory';

    public function handle(): int
    {
        $sourceRoot = base_path(trim((string) $this->option('from'), '/'));
        $targetRoot = base_path(trim((string) $this->option('to'), '/'));
        $only = $this->argument('module');
        $dryRun = (bool) $this->option('dry-run');

        if (! File::isDirectory($sourceRoot)) {
            $this->components->error("Source directory [{$sourceRoot}] does not exist.");

            return self::FAILURE;
        }

        if (strtolower($sourceRoot) === strtolower($targetRoot)) {
            $this->components->error('Source and target module directories resolve to the same path. Use --to with a distinct directory.');

            return self::FAILURE;
        }

        $directories = File::directories($sourceRoot);
        $plans = [];

        foreach ($directories as $directory) {
            $name = basename($directory);

            if (is_string($only) && $only !== $name) {
                continue;
            }

            $plans[] = [
                'name' => $name,
                'source' => $directory,
                'target' => $targetRoot.'/'.$name,
                'manifest' => $this->manifestFor($directory, $name),
            ];
        }

        if ($plans === []) {
            $this->components->warn('No matching nwidart modules found.');

            return self::SUCCESS;
        }

        $this->components->info($dryRun ? 'nwidart import plan' : 'Importing nwidart modules');

        foreach ($plans as $plan) {
            $this->components->twoColumnDetail($plan['name'], str_replace(base_path().'/', '', $plan['target']));

            if ($dryRun) {
                continue;
            }

            if (File::exists($plan['target']) && ! $this->option('force')) {
                $this->components->warn("Skipping [{$plan['name']}], target already exists. Use --force to overwrite.");

                continue;
            }

            File::ensureDirectoryExists($targetRoot);
            File::copyDirectory($plan['source'], $plan['target']);
            File::put($plan['target'].'/module.json', (string) json_encode($plan['manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        if (! $dryRun) {
            $this->call('modular:refresh');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function manifestFor(string $directory, string $name): array
    {
        $manifestPath = $directory.'/module.json';
        $existing = [];

        if (File::exists($manifestPath)) {
            $decoded = json_decode((string) File::get($manifestPath), true);
            $existing = is_array($decoded) ? $decoded : [];
        }

        $providers = (array) ($existing['providers'] ?? []);

        if (isset($existing['provider'])) {
            $providers[] = $existing['provider'];
        }

        return [
            'name' => (string) ($existing['name'] ?? $name),
            'namespace' => (string) ($existing['namespace'] ?? "Modules\\{$name}\\"),
            'providers' => array_values(array_unique(array_filter($providers, 'is_string'))),
            'version' => (string) ($existing['version'] ?? '1.0.0'),
            'requires' => array_values(array_filter((array) ($existing['requires'] ?? []), 'is_string')),
            'conflicts' => array_values(array_filter((array) ($existing['conflicts'] ?? []), 'is_string')),
            'provides' => array_values(array_filter((array) ($existing['provides'] ?? []), 'is_string')),
            'removable' => (bool) ($existing['removable'] ?? true),
            'disableable' => (bool) ($existing['disableable'] ?? true),
        ];
    }
}
