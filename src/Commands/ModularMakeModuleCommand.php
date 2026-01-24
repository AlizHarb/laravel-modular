<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Console command to create a new module.
 */
final class ModularMakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name : The name of the module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $nameArg = $this->argument('name');
        $name = Str::studly((string) $nameArg);
        
        $basePath = config('modular.paths.modules', base_path('modules'));
        $path = "{$basePath}/{$name}";

        if (File::exists($path)) {
            $this->error("Module [{$name}] already exists!");

            return self::FAILURE;
        }

        $this->info("Creating module: {$name}...");

        $this->createDirectories($path);
        $this->createModuleJson($path, $name);
        $this->createComposerJson($path, $name);
        $this->createServiceProvider($path, $name);
        $this->createRoutes($path, $name);

        $this->info("Module [{$name}] created successfully.");
        $this->comment("Please run 'composer dump-autoload' and 'php artisan modular:install' if you haven't yet.");

        $moduleLower = strtolower($name);
        /** @var \Illuminate\Contracts\Cache\Repository $cache */
        $cache = app()->make('cache');
        $cache->forget("modular.livewire.components.{$moduleLower}");

        return self::SUCCESS;
    }

    /**
     * Create the module directory structure.
     *
     * @param  string  $path
     * @return void
     */
    protected function createDirectories(string $path): void
    {
        $directories = [
            'app/Console',
            'app/Http/Controllers',
            'app/Http/Middleware',
            'app/Models',
            'app/Providers',
            'database/factories',
            'database/migrations',
            'database/seeders',
            'resources/assets',
            'resources/views/components',
            'routes',
            'config',
            'lang/en',
            'tests/Feature',
            'tests/Unit',
        ];

        foreach ($directories as $dir) {
            File::makeDirectory("{$path}/{$dir}", 0755, true);
        }
    }

    /**
     * Create the module.json file.
     *
     * @param  string  $path
     * @param  string  $name
     * @return void
     */
    protected function createModuleJson(string $path, string $name): void
    {
        $content = $this->getStubContents('module.json.stub', ['name' => $name]);
        File::put("{$path}/module.json", $content);
    }

    /**
     * Create the composer.json file for the module.
     *
     * @param  string  $path
     * @param  string  $name
     * @return void
     */
    protected function createComposerJson(string $path, string $name): void
    {
        $content = $this->getStubContents('composer.json.stub', [
            'name' => $name,
            'lowerName' => strtolower($name),
            'vendor' => (string) config('modular.composer.vendor', 'alizharb'),
            'authorName' => (string) config('modular.composer.author.name', 'Ali Harb'),
            'authorEmail' => (string) config('modular.composer.author.email', 'harbzali@gmail.com'),
            'type' => (string) config('composer.type', 'library'),
            'license' => (string) config('composer.license', 'MIT'),
        ]);
        File::put("{$path}/composer.json", $content);
    }

    /**
     * Create the main service provider for the module.
     *
     * @param  string  $path
     * @param  string  $name
     * @return void
     */
    protected function createServiceProvider(string $path, string $name): void
    {
        $content = $this->getStubContents('provider.stub', ['name' => $name]);
        File::put("{$path}/app/Providers/{$name}ServiceProvider.php", $content);
    }

    /**
     * Create the route files for the module.
     *
     * @param  string  $path
     * @param  string  $name
     * @return void
     */
    protected function createRoutes(string $path, string $name): void
    {
        $vars = ['name' => $name, 'lowerName' => strtolower($name)];

        File::put("{$path}/routes/web.php", $this->getStubContents('routes-web.stub', $vars));
        File::put("{$path}/routes/api.php", $this->getStubContents('routes-api.stub', $vars));
    }

    /**
     * Get the contents of a stub file and replace placeholders.
     *
     * @param  string  $stub
     * @param  array<string, string>  $replace
     * @return string
     * @throws \Exception
     */
    protected function getStubContents(string $stub, array $replace = []): string
    {
        $stubPath = __DIR__ . '/../../resources/stubs/' . $stub;
        
        if (config('modular.stubs.enabled') && File::exists($customPath = (string) config('modular.stubs.path') . '/' . $stub)) {
            $stubPath = $customPath;
        }
        
        if (! File::exists($stubPath)) {
            throw new \Exception("Stub not found: {$stubPath}");
        }

        $content = (string) File::get($stubPath);

        foreach ($replace as $key => $value) {
            $content = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], (string) $value, $content);
        }

        return $content;
    }
}
