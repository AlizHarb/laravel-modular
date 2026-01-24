<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ClassMakeCommand;

/**
 * Console command to create a new modular class.
 */
final class ModularClassMakeCommand extends ClassMakeCommand
{
    use ModularCommand, ModularGenerator;
}
