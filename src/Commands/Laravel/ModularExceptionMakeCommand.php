<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ExceptionMakeCommand;

/**
 * Console command to create a new modular exception class.
 */
final class ModularExceptionMakeCommand extends ExceptionMakeCommand
{
    use ModularCommand, ModularGenerator;
}
