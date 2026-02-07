<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Console command to run npm commands in a module's workspace.
 */
final class ModularNpmCommand extends Command
{
    use ModularCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:npm {module : The name of the module} {args* : The npm arguments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run npm commands in a specific module workspace';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = (string) $this->argument('module');
        $registry = app(ModuleRegistry::class);
        $module = $registry->getModule($moduleName);

        if (! $module) {
            $this->components->error("Module [{$moduleName}] not found!");

            return self::FAILURE;
        }

        $args = $this->argument('args');
        $npmCommand = array_merge(['npm', 'run'], $args, ['--workspace=@modules/'.strtolower($moduleName)]);

        // Special case for 'install' which should be run at root level for workspaces
        if ($args[0] === 'install') {
            $npmCommand = ['npm', 'install'];
            $this->components->info('Running npm install at root for workspaces...');
        }

        $this->components->info('Running: '.implode(' ', $npmCommand));

        $process = new Process($npmCommand, base_path(), null, null, null);
        $process->setTty(Process::isTtySupported());
        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
