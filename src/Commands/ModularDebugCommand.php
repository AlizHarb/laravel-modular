<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use AlizHarb\Modular\Support\ModuleManifestValidator;
use Illuminate\Console\Command;

class ModularDebugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:debug
        {module? : The name of the module to debug}
        {--json : Output module details as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug module configuration, providers, and middleware';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $name = $this->argument('module');

        if ($this->option('json')) {
            return $this->debugJson($registry, is_string($name) ? $name : null);
        }

        if ($name) {
            return $this->debugModule($registry, $name);
        }

        $this->info('Debugging All Modules');
        $this->table(
            ['Name', 'Status', 'Path', 'Version'],
            array_map(fn ($m) => [
                $m['name'],
                $registry->getActivator()->isEnabled($m['name']) ? 'Enabled' : 'Disabled',
                $m['path'],
                (string) ($m['version'] ?? '1.0.0'),
            ], $registry->getModules())
        );

        return self::SUCCESS;
    }

    protected function debugJson(ModuleRegistry $registry, ?string $name): int
    {
        if ($name !== null && ! $registry->moduleExists($name)) {
            $this->line((string) json_encode([
                'error' => "Module [{$name}] not found.",
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::FAILURE;
        }

        $modules = $name !== null
            ? [$name => $registry->getModule($name)]
            : $registry->getModules();

        $payload = [];
        $validator = app(ModuleManifestValidator::class);

        foreach ($modules as $moduleName => $module) {
            if ($module === null) {
                continue;
            }

            $payload[] = [
                'name' => $module['name'],
                'enabled' => $registry->isEnabled($moduleName),
                'path' => $module['path'],
                'namespace' => $module['namespace'],
                'version' => $module['version'],
                'providers' => $module['providers'],
                'middleware' => $module['middleware'] ?? [],
                'requires' => $module['requires'],
                'conflicts' => $module['conflicts'] ?? [],
                'provides' => $module['provides'] ?? [],
                'route_prefix' => $module['route_prefix'] ?? '',
                'policies' => $registry->getDiscoveredPolicies($moduleName),
                'events' => $registry->getDiscoveredEvents($moduleName),
                'discovery_info' => $registry->getDiscoveryInfo($moduleName),
                'resources' => [
                    'views' => $registry->hasViews($moduleName),
                    'translations' => $registry->hasTranslations($moduleName),
                    'migrations' => $registry->hasMigrations($moduleName),
                ],
                'validation_errors' => $validator->validate($module['path'], basename((string) $module['path'])),
            ];
        }

        $this->line((string) json_encode([
            'modules' => $payload,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    protected function debugModule(ModuleRegistry $registry, string $name): int
    {
        if (! $registry->moduleExists($name)) {
            $this->error("Module [{$name}] not found.");

            return self::FAILURE;
        }

        $module = $registry->getModule($name);
        $status = $registry->getActivator()->isEnabled($name) ? 'Enabled' : 'Disabled';

        $this->components->twoColumnDetail('Name', $module['name']);
        $this->components->twoColumnDetail('Status', $status);
        $this->components->twoColumnDetail('Namespace', $module['namespace']);
        $this->components->twoColumnDetail('Path', $module['path']);
        $this->components->twoColumnDetail('Version', $module['version']);

        $this->newLine();

        $this->info('Providers:');
        if (empty($module['providers'])) {
            $this->line('  <fg=gray>None</>');
        } else {
            foreach ($module['providers'] as $provider) {
                $this->line("  - {$provider}");
            }
        }

        $this->newLine();

        $this->info('Middleware / Groups:');
        $moduleMiddleware = (array) ($module['middleware'] ?? []);
        if (empty($moduleMiddleware)) {
            $this->line('  <fg=gray>None</>');
        } else {
            /** @var array<string, string|array<int, string>> $moduleMiddleware */
            foreach ($moduleMiddleware as $key => $mw) {
                if (is_array($mw)) {
                    $this->line("  - Method: Push to Group [{$key}]");
                    foreach ($mw as $m) {
                        $this->line("    * {$m}");
                    }
                } else {
                    $this->line("  - Alias: [{$key}] => {$mw}");
                }
            }
        }

        $this->newLine();
        $this->info('Requires:');
        if (empty($module['requires'])) {
            $this->line('  <fg=gray>None</>');
        } else {
            foreach ($module['requires'] as $req) {
                $this->line("  - {$req}");
            }
        }

        return self::SUCCESS;
    }
}
