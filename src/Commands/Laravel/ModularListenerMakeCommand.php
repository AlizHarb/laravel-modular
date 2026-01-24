<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ListenerMakeCommand;

/**
 * Console command to create a new modular listener class.
 */
final class ModularListenerMakeCommand extends ListenerMakeCommand
{
    use ModularCommand, ModularGenerator;
}
