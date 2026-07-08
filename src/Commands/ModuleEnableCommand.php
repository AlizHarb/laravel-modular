<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\Events\ModuleEnabled;
use AlizHarb\Modular\Events\ModuleEnabling;
use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

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

        // Dependency Enforcement
        $check = $registry->checkDependencies($moduleName);
        if (! $check['satisfied']) {
            $this->error("Cannot enable module [{$moduleName}]. Missing dependencies: ".implode(', ', $check['missing']));

            return self::FAILURE;
        }

        Event::dispatch(new ModuleEnabling($moduleName));

        $activator->setStatus($moduleName, true);
        $this->components->info("Module [{$moduleName}] enabled successfully.");

        $this->call('modular:clear');
        Event::dispatch(new ModuleEnabled($moduleName));

        return self::SUCCESS;
    }
}
