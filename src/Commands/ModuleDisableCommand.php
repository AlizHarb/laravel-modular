<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\Events\ModuleDisabled;
use AlizHarb\Modular\Events\ModuleDisabling;
use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

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
        Event::dispatch(new ModuleDisabling($moduleName));
        $activator->setStatus($moduleName, false);

        $this->components->info("Module [{$moduleName}] disabled successfully.");

        $this->call('modular:clear');
        Event::dispatch(new ModuleDisabled($moduleName));

        return self::SUCCESS;
    }
}
