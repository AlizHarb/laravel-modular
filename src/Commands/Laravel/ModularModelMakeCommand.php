<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;

/**
 * Console command to create a new modular Eloquent model class.
 */
final class ModularModelMakeCommand extends ModelMakeCommand
{
    use ModularCommand, ModularGenerator;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * Get the default namespace for modular models.
     *
     * @param string $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->isModular()) {
            return $rootNamespace.'\\Models';
        }

        return parent::getDefaultNamespace($rootNamespace);
    }

    /**
     * Create a model factory for the model.
     */
    protected function createFactory(): void
    {
        $factory = Str::studly($this->argument('name'));

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
            '--model' => $this->qualifyClass($this->getNameInput()),
            '--module' => $this->getModule(),
        ]);
    }

    /**
     * Create a migration file for the model.
     */
    protected function createMigration(): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $this->call('make:migration', array_filter([
            'name' => "create_{$table}_table",
            '--create' => $table,
            '--module' => $this->getModule(),
        ]));
    }

    /**
     * Create a seeder file for the model.
     */
    final protected function createSeeder(): void
    {
        $seeder = Str::studly((string) $this->argument('name'));

        $this->call('make:seeder', [
            'name' => "{$seeder}Seeder",
            '--module' => $this->getModule(),
        ]);
    }

    /**
     * Create a controller for the model.
     */
    final protected function createController(): void
    {
        $controller = Str::studly((string) $this->argument('name'));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
            '--api' => $this->option('api'),
            '--requests' => $this->option('requests') || $this->option('api'),
            '--module' => $this->getModule(),
        ]));
    }

    /**
     * Create a policy file for the model.
     */
    final protected function createPolicy(): void
    {
        $policy = Str::studly((string) $this->argument('name'));

        $this->call('make:policy', [
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
            '--module' => $this->getModule(),
        ]);
    }

    /**
     * Create a form request file for the model.
     */
    final protected function createRequest(): void
    {
        $request = Str::studly((string) $this->argument('name'));

        $this->call('make:request', [
            'name' => "Store{$request}Request",
            '--module' => $this->getModule(),
        ]);

        $this->call('make:request', [
            'name' => "Update{$request}Request",
            '--module' => $this->getModule(),
        ]);
    }

    /**
     * Create a resource file for the model.
     */
    final protected function createResource(): void
    {
        $resource = Str::studly((string) $this->argument('name'));

        $this->call('make:resource', [
            'name' => "{$resource}Resource",
            '--module' => $this->getModule(),
        ]);
    }
}
