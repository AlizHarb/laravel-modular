<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ConsoleMakeCommand;

/**
 * Console command to create a new modular Artisan command.
 */
final class ModularConsoleMakeCommand extends ConsoleMakeCommand
{
    use ModularCommand, ModularGenerator;
}
