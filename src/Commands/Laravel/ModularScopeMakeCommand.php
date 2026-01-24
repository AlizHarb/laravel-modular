<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ScopeMakeCommand;

/**
 * Console command to create a new modular query scope.
 */
final class ModularScopeMakeCommand extends ScopeMakeCommand
{
    use ModularCommand, ModularGenerator;
}
