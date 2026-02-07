<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Concerns;

use Illuminate\Support\Str;

/**
 * Trait for modular code generation.
 */
trait ModularGenerator
{
    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        if ($this->isModular()) {
            $module = $this->getModule();

            return "Modules\\{$module}\\";
        }

        return parent::rootNamespace();
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

            return $this->getModuleRegistry()->resolvePath((string) $module, 'app/'.str_replace('\\', '/', $name).'.php');
        }

        return parent::getPath($name);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->isModular()) {
            $module = $this->getModule();
            $subNamespace = Str::replaceFirst("Modules\\{$module}\\", '', parent::getDefaultNamespace($rootNamespace));

            return $this->getModuleRegistry()->resolveNamespace((string) $module, $subNamespace);
        }

        return parent::getDefaultNamespace($rootNamespace);
    }
}
