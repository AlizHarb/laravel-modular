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
     * Get the destination class path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        if ($this->isModular()) {
            $module = $this->getModule();
            $name = Str::replaceFirst($this->rootNamespace(), '', $name);

            return $this->getModuleRegistry()->resolvePath($module, 'database/factories/'.str_replace('\\', '/', $name).'.php');
        }

        return parent::getPath($name);
    }
}
