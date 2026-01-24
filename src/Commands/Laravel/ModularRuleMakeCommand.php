<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\RuleMakeCommand;

/**
 * Console command to create a new modular validation rule.
 */
final class ModularRuleMakeCommand extends RuleMakeCommand
{
    use ModularCommand, ModularGenerator;
}
