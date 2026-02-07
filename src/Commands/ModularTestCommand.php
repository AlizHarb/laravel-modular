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
    protected $signature = 'modular:test {module? : The name of the module to test} {--filter=} {--pest} {--coverage} {--coverage-clover=} {--coverage-html=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tests for a specific module with unified coverage support';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');
        $filter = $this->option('filter');
        $pest = $this->option('pest');
        $coverage = $this->option('coverage') || $this->option('coverage-clover') || $this->option('coverage-html');

        if ($coverage && ! $this->ensureCoverageRequirements()) {
            return self::FAILURE;
        }

        if ($moduleName) {
            $this->info("Running tests for module [{$moduleName}]...");

            return $this->runTestsForModule($registry, $moduleName, $filter, $pest, $coverage);
        }

        $this->info('Running tests for all modules...');

        $modules = $registry->getModules();
        $exitCode = self::SUCCESS;
        $coverageDir = storage_path('framework/coverage');

        if ($coverage) {
            if (! is_dir($coverageDir)) {
                mkdir($coverageDir, 0755, true);
            }
            // Clear previous coverage files
            array_map('unlink', glob("{$coverageDir}/*.cov"));
        }

        foreach ($modules as $module) {
            $result = $this->runTestsForModule($registry, $module['name'], $filter, $pest, $coverage, $coverageDir);

            if ($result !== self::SUCCESS) {
                $exitCode = self::FAILURE;
            }
        }

        if ($coverage && $exitCode === self::SUCCESS) {
            $this->mergeCoverage($coverageDir);
        }

        return $exitCode;
    }

    /**
     * Run tests for a specific module.
     */
    protected function runTestsForModule(ModuleRegistry $registry, string $moduleName, ?string $filter, bool $pest, bool $coverage = false, ?string $coverageDir = null): int
    {
        if (! $registry->moduleExists($moduleName)) {
            $this->error("Module [{$moduleName}] not found!");

            return self::FAILURE;
        }

        $module = $registry->getModule($moduleName);
        $modulePath = $module['path'];
        $testPath = $modulePath.'/tests';

        if (! is_dir($testPath)) {
            return self::SUCCESS;
        }

        // Check if module has its own test configuration (Zero-Config mode)
        if (file_exists($modulePath.'/phpunit.xml')) {
            $this->info("Running tests for module [{$moduleName}]...");

            $command = [
                PHP_BINARY,
                base_path('vendor/bin/pest'),
            ];

            if ($filter) {
                $command[] = "--filter={$filter}";
            }

            if ($this->option('ansi')) {
                $command[] = '--colors=always';
            }

            if ($coverage && $coverageDir) {
                $command[] = "--coverage-php={$coverageDir}/{$moduleName}.cov";
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

        $args = ['test', $testPath];

        if ($filter) {
            $args[] = "--filter={$filter}";
        }

        if ($this->getApplication()->has('test')) {
            return $this->call('test', $args);
        }
        $command = [
            PHP_BINARY,
            base_path('vendor/bin/pest'),
            $testPath,
        ];

        if ($filter) {
            $command[] = "--filter={$filter}";
        }

        $process = new \Symfony\Component\Process\Process($command);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->getExitCode();
    }

    protected function ensureCoverageRequirements(): bool
    {
        if (! extension_loaded('pcov') && ! extension_loaded('xdebug')) {
            $this->error('Coverage requires PCOV or Xdebug extension.');

            return false;
        }

        return true;
    }

    protected function mergeCoverage(string $coverageDir): void
    {
        $this->info('Merging coverage reports...');

        $covFiles = glob("{$coverageDir}/*.cov");

        if (empty($covFiles)) {
            $this->warn('No coverage files found to merge.');

            return;
        }

        // We use the phpcov binary if available, otherwise we warn.
        // In a real package, you'd likely depend on sebastian/phpcov
        $phpcov = base_path('vendor/bin/phpcov');

        if (! file_exists($phpcov)) {
            $this->warn('phpunit/phpcov is required to merge coverage reports. Please install it: composer require --dev phpunit/phpcov');

            return;
        }

        $mergeCommand = [
            PHP_BINARY,
            $phpcov,
            'merge',
            $coverageDir,
        ];

        if ($this->option('coverage-clover')) {
            $mergeCommand[] = '--clover';
            $mergeCommand[] = $this->option('coverage-clover');
        }

        if ($this->option('coverage-html')) {
            $mergeCommand[] = '--html';
            $mergeCommand[] = $this->option('coverage-html');
        }

        $process = new \Symfony\Component\Process\Process($mergeCommand);
        $process->run();

        if ($process->isSuccessful()) {
            $this->info('Coverage merged successfully.');
        } else {
            $this->error('Failed to merge coverage: '.$process->getErrorOutput());
        }
    }
}
