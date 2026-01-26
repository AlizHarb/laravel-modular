<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModuleEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:enable {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable the specified module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = (string) $this->argument('module');

        $activator = $registry->getActivator();
        $activator->setStatus($moduleName, true);

        $this->components->info("Module [{$moduleName}] enabled successfully.");

        $this->call('modular:clear');

        return self::SUCCESS;
    }
}
