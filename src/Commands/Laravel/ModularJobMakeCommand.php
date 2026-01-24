<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\JobMakeCommand;

/**
 * Console command to create a new modular job class.
 */
final class ModularJobMakeCommand extends JobMakeCommand
{
    use ModularCommand, ModularGenerator;
}
