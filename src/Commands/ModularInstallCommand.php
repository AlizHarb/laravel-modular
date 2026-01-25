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
     *
     * @return int
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
     *
     * @return void
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
     *
     * @return void
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
        $include = $mergeConfig['include'] ?? [];

        $packageConfig = 'vendor/alizharb/laravel-modular/composer.json';

        if (! in_array($packageConfig, $include)) {
            $include[] = $packageConfig;

            $modulesPath = config('modular.paths.modules', base_path('modules'));
            $relativeModulesPath = Str::after((string) $modulesPath, base_path().'/').'/*/composer.json';

            if (! in_array($relativeModulesPath, $include)) {
                $include[] = $relativeModulesPath;
            }

            $composer['extra']['merge-plugin']['include'] = $include;
            $composer['extra']['merge-plugin']['recurse'] = false;
            $composer['extra']['merge-plugin']['replace'] = false;
            $composer['extra']['merge-plugin']['merge-dev'] = true;

            File::put($composerJsonPath, (string) json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->components->info("Configured composer.json to include {$packageConfig} and {$relativeModulesPath}");

            if ($this->confirm('Run "composer dump-autoload" now?', true)) {
                $this->call('composer', ['dump-autoload']);
            }
        } else {
            $this->components->info('Composer merge settings already configured.');
        }

        if (isset($composer['autoload']['psr-4']['Modules\\'])) {
            unset($composer['autoload']['psr-4']['Modules\\']);
            File::put($composerJsonPath, (string) json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Removed legacy "Modules\\" PSR-4 autoloading.');
        }
    }

    /**
     * Configure Vite for modular views.
     *
     * @return void
     */
    protected function configureVite(): void
    {
        $viteConfigPath = base_path('vite.config.js');

        if (! File::exists($viteConfigPath)) {
            return;
        }

        $content = (string) File::get($viteConfigPath);

        if (! str_contains($content, 'modules/')) {
            $this->warn('Tip: To enable HMR for modular views, add the following to your Vite config watch or input paths:');
            $this->line("'modules/**/resources/views/**/*.blade.php'");
        }
    }
}
