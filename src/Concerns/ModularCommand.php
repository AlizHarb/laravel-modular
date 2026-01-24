<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Concerns;

use AlizHarb\Modular\ModuleRegistry;
use Symfony\Component\Console\Input\InputOption;

trait ModularCommand
{
    /**
     * Get the console command options.
     *
     * @return array<int, array|InputOption>
     */
    protected function getOptions(): array
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_REQUIRED, 'The module to create the component in'],
        ]);
    }

    /**
     * Get the modular registry instance.
     *
     * @return ModuleRegistry
     */
    protected function getModuleRegistry(): ModuleRegistry
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);

        return $registry;
    }

    /**
     * Check if the current command execution is targeted at a module.
     *
     * @return bool
     */
    protected function isModular(): bool
    {
        $module = $this->option('module');

        return $this->hasOption('module') && 
               is_string($module) && 
               ! empty($module);
    }

    /**
     * Get the name of the target module.
     *
     * @return string|null
     */
    protected function getModule(): ?string
    {
        $module = $this->option('module');

        return is_string($module) ? $module : null;
    }
}
