<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

/**
 * Console command to seed modular databases.
 */
final class ModularSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:seed {module? : The name of the module} {--class= : The class name of the root seeder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with modular seeders';

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
            return $this->seedModule((string) $moduleName, $registry);
        }

        foreach ($registry->getModules() as $module) {
            $this->seedModule($module['name'], $registry);
        }

        return self::SUCCESS;
    }

    /**
     * Seed a specific module.
     *
     * @param  string  $name
     * @param  ModuleRegistry  $registry
     * @return int
     */
    protected function seedModule(string $name, ModuleRegistry $registry): int
    {
        $seederClass = $this->option('class') ?: "{$name}DatabaseSeeder";
        $fullClass = "Modules\\{$name}\\Database\\Seeders\\{$seederClass}";

        if (! class_exists((string) $fullClass)) {
            $this->warn("Seeder [{$fullClass}] not found for module: {$name}");
            return self::SUCCESS;
        }

        $this->info("Seeding module: {$name}...");

        $this->call('db:seed', [
            '--class' => $fullClass,
        ]);

        return self::SUCCESS;
    }
}
