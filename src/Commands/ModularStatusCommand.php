<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

final class ModularStatusCommand extends Command
{
    protected $signature = 'modular:status {--json : Output status as JSON}';

    protected $description = 'Show a compact health and discovery summary for all modules';

    public function handle(ModuleRegistry $registry): int
    {
        $modules = $registry->getModules();
        $enabled = 0;
        $disabled = 0;
        $missingDependencies = 0;
        $withMigrations = 0;
        $withViews = 0;

        foreach ($modules as $name => $module) {
            $registry->isEnabled($name) ? $enabled++ : $disabled++;

            if (! $registry->checkDependencies($name)['satisfied']) {
                $missingDependencies++;
            }

            if (($module['has_migrations'] ?? false) || $registry->hasMigrations($name)) {
                $withMigrations++;
            }

            if (($module['has_views'] ?? false) || $registry->hasViews($name)) {
                $withViews++;
            }
        }

        $payload = [
            'modules' => count($modules),
            'enabled' => $enabled,
            'disabled' => $disabled,
            'missing_dependencies' => $missingDependencies,
            'with_migrations' => $withMigrations,
            'with_views' => $withViews,
            'cache' => [
                'cached' => $registry->getCacheMeta() !== [],
                'stale' => $registry->cacheIsStale(),
                'cached_at' => $registry->getCacheMeta()['cached_at'] ?? null,
            ],
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info('Modular Status');
        $this->table(['Metric', 'Value'], [
            ['Modules', (string) $payload['modules']],
            ['Enabled', (string) $payload['enabled']],
            ['Disabled', (string) $payload['disabled']],
            ['Dependency issues', (string) $payload['missing_dependencies']],
            ['Modules with migrations', (string) $payload['with_migrations']],
            ['Modules with views', (string) $payload['with_views']],
            ['Cache status', $payload['cache']['cached'] ? ($payload['cache']['stale'] ? 'Stale' : 'Fresh') : 'Not cached'],
        ]);

        return $missingDependencies > 0 || $registry->cacheIsStale() ? self::FAILURE : self::SUCCESS;
    }
}
