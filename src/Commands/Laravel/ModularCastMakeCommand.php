<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\CastMakeCommand;

/**
 * Console command to create a new modular Eloquent cast class.
 */
final class ModularCastMakeCommand extends CastMakeCommand
{
    use ModularCommand, ModularGenerator;
}
