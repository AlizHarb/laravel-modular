<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ModularPublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:publish {module : The name of the module} {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish a module\'s assets, configuration, views, and translations';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry, Filesystem $files): int
    {
        $name = $this->argument('module');
        
        if (! $registry->moduleExists($name)) {
            $this->error("Module [{$name}] not found.");
            return self::FAILURE;
        }

        $module = $registry->getModule($name);
        $force = $this->option('force');
        $lowerName = strtolower($name);

        // Publish Config
        $configPath = $registry->resolvePath($name, 'config');
        if ($files->isDirectory($configPath)) {
            $target = config_path("modules/{$lowerName}");
            $this->publishDirectory($files, $configPath, $target, 'config', $force);
        }

        // Publish Views
        $viewsPath = $registry->resolvePath($name, 'resources/views');
        if ($files->isDirectory($viewsPath)) {
            $target = resource_path("views/vendor/{$lowerName}");
            $this->publishDirectory($files, $viewsPath, $target, 'views', $force);
        }

        // Publish Lang
        $langPath = $registry->resolvePath($name, 'lang');
        if ($files->isDirectory($langPath)) {
            $target = lang_path("vendor/{$lowerName}");
            $this->publishDirectory($files, $langPath, $target, 'lang', $force);
        }
        
        // Publish Assets (Public)
        $assetsPath = $registry->resolvePath($name, 'resources/assets'); // Or public?
        // Usually assets are source assets. 
        // If we want to publish compiled assets, we'd look for public.
        // Assuming we might have 'public' folder in module for static assets?
        // Let's assume standard structure: 'resources/css', 'resources/js' are handled by Vite.
        // If there is a 'public' directory in the module, publish it.
        $publicPath = $registry->resolvePath($name, 'public');
        if ($files->isDirectory($publicPath)) {
            $target = public_path("vendor/{$lowerName}");
            $this->publishDirectory($files, $publicPath, $target, 'public', $force);
        }

        $this->info("Module [{$name}] published successfully.");

        return self::SUCCESS;
    }

    protected function publishDirectory(Filesystem $files, string $from, string $to, string $type, bool $force): void
    {
        if (! $files->isDirectory($to) || $force) {
            $files->ensureDirectoryExists(dirname($to));
            $files->copyDirectory($from, $to);
            $this->components->task("Publishing {$type}", fn () => true);
        } else {
             $this->components->warn("Skipped {$type} (already exists). Use --force to overwrite.");
        }
    }
}
