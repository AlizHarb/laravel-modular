<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:disable {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable the specified module';

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

        if ($module['disableable'] === false) {
            $this->error("Module [{$moduleName}] cannot be disabled.");

            return self::FAILURE;
        }

        $activator = $registry->getActivator();
        $activator->setStatus($moduleName, false);

        $this->components->info("Module [{$moduleName}] disabled successfully.");

        $this->call('modular:clear');

        return self::SUCCESS;
    }
}
