<?php

namespace AlizHarb\Modular\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'modular:doctor')]
class ModularDoctorCommand extends Command
{
    protected $signature = 'modular:doctor';

    protected $description = 'Diagnose common configuration issues within the modular ecosystem.';

    public function handle(): int
    {
        $this->info('Running Modular Doctor...');
        $this->newLine();

        $checks = [
            'checkComposerDependencies',
            'checkModuleStructure',
            'checkVitalConfigFiles',
            'checkAutoloading',
            'checkCircularDependencies',
            'checkModuleValidation',
            'checkGhostModules',
            'checkDuplicateProviders',
            'checkAssetLinking',
        ];

        $hasIssues = false;

        foreach ($checks as $check) {
            if (! $this->{$check}()) {
                $hasIssues = true;
            }
        }

        $this->newLine();

        if ($hasIssues) {
            $this->error('Doctor found some issues. Please review the warnings above.');

            return self::FAILURE;
        }

        $this->info('All systems operational! Modular ecosystem looks healthy.');

        return self::SUCCESS;
    }

    protected function checkComposerDependencies(): bool
    {
        $exists = File::exists(base_path('composer.json'));
        $this->components->task('Checking core dependencies', function () use ($exists) {
            return $exists;
        });

        return $exists;
    }

    protected function checkModuleStructure(): bool
    {
        $modulesPath = config('modular.paths.modules', base_path('modules'));

        if (! File::isDirectory($modulesPath)) {
            $this->components->warn("Modules directory [{$modulesPath}] does not exist.");

            return false;
        }

        $this->components->task('Checking modules directory', function () {
            return true;
        });

        return true;
    }

    protected function checkVitalConfigFiles(): bool
    {
        $requiredFiles = [
            'config/modular.php',
            'composer.json',
        ];

        $missing = [];

        foreach ($requiredFiles as $file) {
            if (! File::exists(base_path($file))) {
                $missing[] = $file;
            }
        }

        if (count($missing) > 0) {
            $this->components->error('Missing vital config files: '.implode(', ', $missing));

            return false;
        }

        $this->components->task('Checking vital configuration files', function () {
            return true;
        });

        return true;
    }

    protected function checkAutoloading(): bool
    {
        $composer = json_decode(File::get(base_path('composer.json')), true);
        $psrs = array_merge(
            $composer['autoload']['psr-4'] ?? [],
            $composer['autoload-dev']['psr-4'] ?? []
        );

        $moduleNamespace = config('modular.paths.namespace', 'Modules\\');

        if (! isset($psrs[$moduleNamespace])) {
            $this->components->warn("Namespace [{$moduleNamespace}] is not registered in composer.json PSR-4 autoloading. Performance will be degraded.");

            return false;
        }

        $this->components->task('Checking PSR-4 autoloading', function () {
            return true;
        });

        return true;
    }

    protected function checkCircularDependencies(): bool
    {
        $result = $this->callSilent('modular:check');

        $this->components->task('Checking circular dependencies', function () use ($result) {
            return $result === Command::SUCCESS;
        });

        return $result === Command::SUCCESS;
    }

    protected function checkModuleValidation(): bool
    {
        $registry = app(\AlizHarb\Modular\ModuleRegistry::class);
        $modules = $registry->getModules();
        $allValid = true;

        foreach ($modules as $name => $config) {
            $path = $config['path'];
            if (! File::exists($path.'/module.json')) {
                $this->components->warn("Module [{$name}] is missing module.json file.");
                $allValid = false;
            }
        }

        $this->components->task('Validating module metadata', function () use ($allValid) {
            return $allValid;
        });

        return $allValid;
    }

    protected function checkGhostModules(): bool
    {
        $modulesPath = config('modular.paths.modules', base_path('modules'));

        if (! File::isDirectory($modulesPath)) {
            return true; // checkModuleStructure handles this
        }

        $directories = File::directories($modulesPath);
        $ghosts = [];

        foreach ($directories as $directory) {
            if (! File::exists($directory.'/module.json')) {
                $ghosts[] = basename($directory);
            }
        }

        if (count($ghosts) > 0) {
            $this->components->warn('Ghost modules detected (directories without module.json): '.implode(', ', $ghosts));

            return false;
        }

        $this->components->task('Checking for ghost modules', function () {
            return true;
        });

        return true;
    }

    protected function checkDuplicateProviders(): bool
    {
        $registry = app(\AlizHarb\Modular\ModuleRegistry::class);
        $modules = $registry->getModules();
        $allProviders = [];
        $duplicates = [];

        foreach ($modules as $name => $config) {
            foreach ($config['providers'] as $provider) {
                if (isset($allProviders[$provider])) {
                    $duplicates[$provider][] = $name;
                } else {
                    $allProviders[$provider] = $name;
                }
            }
        }

        if (count($duplicates) > 0) {
            foreach ($duplicates as $provider => $modNames) {
                $this->components->warn("Duplicate provider [{$provider}] registered in modules: ".implode(', ', array_merge([$allProviders[$provider]], $modNames)));
            }

            return false;
        }

        $this->components->task('Checking for duplicate service providers', function () {
            return true;
        });

        return true;
    }

    protected function checkAssetLinking(): bool
    {
        $modulesPath = config('modular.paths.modules', base_path('modules'));
        $publicPath = public_path('modules');
        
        if (! File::exists($publicPath)) {
            $this->components->warn('Module assets directory [public/modules] does not exist. Run php artisan modular:link.');
            return false;
        }

        $this->components->task('Verifying asset linking', function () {
            return true;
        });

        return true;
    }
}
