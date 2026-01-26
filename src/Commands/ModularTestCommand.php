<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModularTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:test {module? : The name of the module to test} {--filter=} {--pest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tests for a specific module';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');
        $filter = $this->option('filter');

        $args = ['test'];

        if ($moduleName) {
            if (! $registry->moduleExists($moduleName)) {
                $this->error("Module [{$moduleName}] not found!");

                return self::FAILURE;
            }

            $module = $registry->getModule($moduleName);
            $testPath = $module['path'].'/tests';

            if (! is_dir($testPath)) {
                $this->error("Module [{$moduleName}] has no tests directory.");

                return self::FAILURE;
            }

            // Pass directory as argument to the test command (standard PHPUnit/Pest behavior)
            // But 'php artisan test' accepts file/dir arguments directly
            $args[] = $testPath;
        } else {
            // Run all module tests? Or just fail?
            // If no module, maybe run all modules/* tests?
            $this->info('Running tests for all modules...');
            $args[] = 'modules'; // Assuming modules are in 'modules' directory relative to base
        }

        if ($filter) {
            $args[] = "--filter={$filter}";
        }

        // Pass calling environment variables or usage
        return $this->call('test', $args);
    }
}
