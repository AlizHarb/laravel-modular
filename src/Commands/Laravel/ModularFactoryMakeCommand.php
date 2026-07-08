<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Support\Str;

/**
 * Console command to create a new modular model factory.
 */
final class ModularFactoryMakeCommand extends FactoryMakeCommand
{
    use ModularCommand, ModularGenerator;

    /**
     * Get the default namespace for modular factories.
     *
     * @param string $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->isModular()) {
            return $rootNamespace.'\\Database\\Factories';
        }

        return parent::getDefaultNamespace($rootNamespace);
    }

    /**
     * Build the factory class with a module-aware namespace.
     *
     * @param string $name
     */
    protected function buildClass($name): string
    {
        if (! $this->isModular()) {
            return parent::buildClass($name);
        }

        $factory = class_basename(Str::ucfirst(str_replace('Factory', '', $name)));
        $namespaceModel = $this->option('model')
            ? $this->qualifyModel($this->option('model'))
            : $this->qualifyModel($this->guessModelName($name));
        $model = class_basename($namespaceModel);
        $namespace = rtrim($this->getModuleRegistry()->resolveNamespace((string) $this->getModule(), 'Database\\Factories'), '\\');

        $content = str_replace(
            [
                '{{ factoryNamespace }}',
                'NamespacedDummyModel',
                '{{ namespacedModel }}',
                '{{namespacedModel}}',
                'DummyModel',
                '{{ model }}',
                '{{model}}',
                '{{ factory }}',
                '{{factory}}',
            ],
            [
                $namespace,
                $namespaceModel,
                $namespaceModel,
                $namespaceModel,
                $model,
                $model,
                $model,
                $factory,
                $factory,
            ],
            parent::buildClass($name)
        );

        return str_replace('namespace Database\\Factories;', "namespace {$namespace};", $content);
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     */
    protected function getPath($name): string
    {
        if ($this->isModular()) {
            $module = $this->getModule();
            $name = Str::replaceFirst($this->rootNamespace(), '', $name);
            $name = Str::replaceFirst('Database\\Factories\\', '', $name);

            return $this->getModuleRegistry()->resolvePath($module, 'database/factories/'.str_replace('\\', '/', $name).'.php');
        }

        return parent::getPath($name);
    }
}
