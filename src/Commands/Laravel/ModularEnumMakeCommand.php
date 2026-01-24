<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\EnumMakeCommand;

/**
 * Console command to create a new modular enumeration.
 */
final class ModularEnumMakeCommand extends EnumMakeCommand
{
    use ModularCommand, ModularGenerator;
}
