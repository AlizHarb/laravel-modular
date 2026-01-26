<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ModularLinkCommand extends Command
{
    protected $signature = 'modular:link {module? : The name of the module to link} {--force : Overwrite existing symlinks}';

    protected $description = 'Create the symbolic links configured for the modules';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $moduleName = $this->argument('module');
        $assetPath = config('modular.paths.assets', 'modules');

        if ($moduleName) {
            return $this->linkModule((string) $moduleName, $registry, (string) $assetPath);
        }

        foreach ($registry->getModules() as $module) {
            $this->linkModule($module['name'], $registry, (string) $assetPath);
        }

        $this->info("The [public/{$assetPath}] directory has been linked.");

        return self::SUCCESS;
    }

    /**
     * Create the symbolic link for a specific module.
     */
    protected function linkModule(string $name, ModuleRegistry $registry, string $assetPath): int
    {
        $source = $registry->resolvePath($name, 'Resources/assets');
        $target = public_path($assetPath.'/'.strtolower($name));

        if (! File::exists($source)) {
            return self::SUCCESS;
        }

        if (File::exists($target) && ! $this->option('force')) {
            $this->warn("The [public/{$assetPath}/".strtolower($name).'] link already exists.');

            return self::FAILURE;
        }

        if (File::exists($target)) {
            File::delete($target);
        }

        // Ensure parent directory exists
        if (! File::exists(public_path($assetPath))) {
            File::makeDirectory(public_path($assetPath));
        }

        $this->laravel->make('files')->link($source, $target);

        $this->info("The [{$assetPath}/{$name}] directory has been linked.");

        return self::SUCCESS;
    }
}
