<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\InterfaceMakeCommand;

/**
 * Console command to create a new modular interface.
 */
final class ModularInterfaceMakeCommand extends InterfaceMakeCommand
{
    use ModularCommand, ModularGenerator;
}
