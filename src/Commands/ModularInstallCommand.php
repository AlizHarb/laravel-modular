<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Console command to install and configure Laravel Modular.
 */
class ModularInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure Laravel Modular';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Installing Laravel Modular...');

        $this->publishResources();
        $this->configureAutoloading();
        $this->configureVite();

        $this->components->info('Laravel Modular has been successfully installed! 🚀');
        $this->comment('You can now create your first module using: php artisan make:module {name}');

        if ($this->confirm('Would you like to show some love by starring the repo on GitHub? ⭐', true)) {
            $url = 'https://github.com/alizharb/laravel-modular';
            if (PHP_OS_FAMILY === 'Darwin') {
                exec("open {$url}");
            } elseif (PHP_OS_FAMILY === 'Windows') {
                exec("start {$url}");
            } elseif (PHP_OS_FAMILY === 'Linux') {
                exec("xdg-open {$url}");
            }
            $this->line("Thanks! You're awesome! 💙");
        }

        return self::SUCCESS;
    }

    /**
     * Publish the package resources.
     */
    protected function publishResources(): void
    {
        $this->info('Publishing resources...');

        $this->call('vendor:publish', [
            '--provider' => "AlizHarb\Modular\ModularServiceProvider",
            '--tag' => 'modular-config',
        ]);

        if ($this->confirm('Would you like to publish the modular stubs for customization?', true)) {
            $this->call('vendor:publish', [
                '--provider' => "AlizHarb\Modular\ModularServiceProvider",
                '--tag' => 'modular-stubs',
            ]);
        }
    }

    /**
     * Configure composer.json for autoloading.
     */
    protected function configureAutoloading(): void
    {
        $composerJsonPath = base_path('composer.json');

        if (! File::exists($composerJsonPath)) {
            return;
        }

        /** @var array<string, mixed> $composer */
        $composer = json_decode((string) File::get($composerJsonPath), true);
        $mergeConfig = $composer['extra']['merge-plugin'] ?? [];
        $include = (array) ($mergeConfig['include'] ?? []);

        $modulesPath = config('modular.paths.modules', base_path('modules'));
        $relativeModulesPath = Str::after((string) $modulesPath, base_path().'/').'/*/composer.json';

        if (! in_array($relativeModulesPath, $include)) {
            $this->components->warn('Modular autoloading needs to be configured in composer.json.');

            if ($this->confirm('Would you like to automatically configure composer.json for modular autoloading?', true)) {
                $include[] = $relativeModulesPath;

                $composer['extra']['merge-plugin']['include'] = $include;

                File::put($composerJsonPath, (string) json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->components->info("Configured composer.json to include {$relativeModulesPath}");
                $this->warn('Please run "composer dump-autoload" to apply the changes.');
            } else {
                $this->components->info('To manually configure modular autoloading, add the following to your composer.json:');

                $manualJson = [
                    'extra' => [
                        'merge-plugin' => [
                            'include' => array_values(array_unique(array_merge($include, [$relativeModulesPath]))),
                        ],
                    ],
                ];

                $this->line("\n".(string) json_encode($manualJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
                $this->components->warn('Modular autoloading might not work until you add this configuration.');
            }
        } else {
            $this->components->info('Composer merge settings already configured.');
        }

        if (isset($composer['autoload']['psr-4']['Modules\\'])) {
            if ($this->confirm('Legacy "Modules\\" PSR-4 autoloading found. Remove it?', true)) {
                unset($composer['autoload']['psr-4']['Modules\\']);
                File::put($composerJsonPath, (string) json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->info('Removed legacy "Modules\\" PSR-4 autoloading.');
            }
        }
    }

    /**
     * Configure Vite for modular views and assets.
     */
    protected function configureVite(): void
    {
        $viteConfigPath = base_path('vite.config.js');

        if (! File::exists($viteConfigPath)) {
            return;
        }

        $this->createViteModularLoader();

        $content = (string) File::get($viteConfigPath);

        if (! str_contains($content, 'vite.modular.js')) {
            $this->components->warn('Vite needs to be configured to load modular assets.');

            if ($this->confirm('Would you like to automatically configure vite.config.js?', true)) {
                // Add modularLoader import - handle both single-line and multiline imports
                if (! str_contains($content, 'modularLoader')) {
                    // Try to find the vite import (single or multiline)
                    if (preg_match('/import\s+\{[^}]*defineConfig[^}]*\}\s+from\s+[\'"]vite[\'"];?/', $content, $matches)) {
                        $viteImport = $matches[0];
                        $content = str_replace(
                            $viteImport,
                            $viteImport."\nimport { modularLoader } from './vite.modular.js';",
                            $content
                        );
                    }
                }

                $content = preg_replace(
                    "/input:\s*\[([^\]]+)\],/",
                    "input: [\n                $1,\n                ...modularLoader.inputs()\n            ],",
                    $content
                );

                // Try to find if refresh is already an array or true
                if (str_contains($content, 'refresh: [')) {
                    $content = str_replace(
                        'refresh: [',
                        "refresh: [\n                ...modularLoader.refreshPaths(),",
                        $content
                    );
                } else {
                    $content = preg_replace(
                        "/refresh:\s*true,/",
                        "refresh: [\n                ...modularLoader.refreshPaths(),\n                'resources/views/**',\n                'routes/**',\n            ],",
                        $content
                    );
                }

                File::put($viteConfigPath, $content);
                $this->components->info('Configured vite.config.js to use the modular loader.');
            } else {
                $this->components->info('To manually configure Vite, add the following to your vite.config.js:');
                $this->line("\nimport { modularLoader } from './vite.modular.js';\n");
                $this->line('// In plugins -> laravel() configuration:');
                $this->line("input: [\n    // ... existing inputs,\n    ...modularLoader.inputs()\n],");
                $this->line("refresh: [\n    // ... existing paths,\n    ...modularLoader.refreshPaths()\n],");
            }
        }
    }

    /**
     * Create the vite.modular.js loader file.
     */
    protected function createViteModularLoader(): void
    {
        $path = base_path('vite.modular.js');

        if (File::exists($path)) {
            return;
        }

        $stubPath = __DIR__.'/../../resources/stubs/vite.modular.js.stub';

        if (File::exists($stubPath)) {
            $content = (string) File::get($stubPath);
            File::put($path, $content);
            $this->components->info('Created vite.modular.js loader.');
        }
    }
}
