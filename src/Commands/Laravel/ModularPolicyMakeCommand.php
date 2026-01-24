<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\PolicyMakeCommand;

/**
 * Console command to create a new modular policy class.
 */
final class ModularPolicyMakeCommand extends PolicyMakeCommand
{
    use ModularCommand, ModularGenerator;
}
