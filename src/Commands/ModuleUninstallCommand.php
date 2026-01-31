<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModuleUninstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:uninstall {module} {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstall the specified module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = (string) $this->argument('module');
        $module = $registry->getModule($moduleName);

        if (! $module) {
            $this->components->error("Module [{$moduleName}] not found.");

            return self::FAILURE;
        }

        if ($module['removable'] === false) {
            $this->error("Module [{$moduleName}] cannot be removed.");

            return self::FAILURE;
        }

        if (! $this->confirm("Are you sure you want to uninstall module [{$moduleName}]? This will delete all module files.", $this->option('force'))) {
            $this->info('Operation cancelled.');
            
            return self::SUCCESS;
        }

        $path = $module['path'];

        if (File::deleteDirectory($path)) {
            $this->info("Module [{$moduleName}] uninstalled successfully.");
            $this->call('modular:clear');

            return self::SUCCESS;
        }

        $this->error("Failed to uninstall module [{$moduleName}].");

        return self::FAILURE;
    }
}
