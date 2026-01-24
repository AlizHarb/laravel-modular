<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

/**
 * Console command to run modular migrations.
 */
final class ModularMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:migrate {module? : The name of the module} {--fresh : Fresh the database before migrating} {--seed : Seed the database after migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations for modules';

    /**
     * Execute the console command.
     *
     * @param  ModuleRegistry  $registry
     * @return int
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');

        if ($moduleName) {
            return $this->migrateModule((string) $moduleName, $registry);
        }

        foreach ($registry->getModules() as $module) {
            $this->migrateModule($module['name'], $registry);
        }

        return self::SUCCESS;
    }

    /**
     * Run migrations for a specific module.
     *
     * @param  string  $name
     * @param  ModuleRegistry  $registry
     * @return int
     */
    protected function migrateModule(string $name, ModuleRegistry $registry): int
    {
        $path = $registry->resolvePath($name, 'Database/Migrations');

        if (! is_dir($path)) {
            $this->warn("No migrations found for module: {$name}");
            return self::SUCCESS;
        }

        $this->info("Migrating module: {$name}...");

        $this->call('migrate', array_filter([
            '--path' => $path,
            '--realpath' => true,
            '--fresh' => $this->option('fresh'),
            '--seed' => $this->option('seed'),
        ]));

        return self::SUCCESS;
    }
}
