<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ObserverMakeCommand;

/**
 * Console command to create a new modular observer class.
 */
final class ModularObserverMakeCommand extends ObserverMakeCommand
{
    use ModularCommand, ModularGenerator;
}
