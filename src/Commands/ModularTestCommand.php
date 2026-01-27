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

        if ($moduleName) {
            if (! $registry->moduleExists($moduleName)) {
                $this->error("Module [{$moduleName}] not found!");

                return self::FAILURE;
            }

            $module = $registry->getModule($moduleName);
            $modulePath = $module['path'];
            $testPath = $modulePath.'/tests';

            if (! is_dir($testPath)) {
                $this->error("Module [{$moduleName}] has no tests directory.");

                return self::FAILURE;
            }

            // Check if module has its own test configuration (Zero-Config mode)
            if (file_exists($modulePath.'/phpunit.xml')) {
                $this->info("Running tests for module [{$moduleName}] using local configuration...");

                $command = [
                    base_path('vendor/bin/pest'),
                ];

                if ($filter) {
                    $command[] = "--filter={$filter}";
                }

                if ($this->option('ansi')) {
                    $command[] = '--colors=always';
                }

                // Execute the command from the module's directory
                $process = new \Symfony\Component\Process\Process(
                    $command,
                    $modulePath,
                    ['APP_ENV' => 'testing']
                );

                $process->setTty(false);
                $process->run(function ($type, $buffer) {
                    $this->output->write($buffer);
                });

                return $process->getExitCode();
            }

            // Fallback to standard 'php artisan test' if no local config
            $args = ['test', $testPath];
        } else {
            $this->info('Running tests for all modules...');
            $args = ['test', 'modules'];
        }

        if ($filter) {
            $args[] = "--filter={$filter}";
        }

        return $this->call('test', $args);
    }
}
