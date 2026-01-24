<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\TraitMakeCommand;

/**
 * Console command to create a new modular trait.
 */
final class ModularTraitMakeCommand extends TraitMakeCommand
{
    use ModularCommand, ModularGenerator;
}
