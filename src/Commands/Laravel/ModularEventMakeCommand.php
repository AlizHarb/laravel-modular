<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\EventMakeCommand;

/**
 * Console command to create a new modular event class.
 */
final class ModularEventMakeCommand extends EventMakeCommand
{
    use ModularCommand, ModularGenerator;
}
