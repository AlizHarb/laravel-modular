<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ProviderMakeCommand;

/**
 * Console command to create a new modular service provider class.
 */
final class ModularProviderMakeCommand extends ProviderMakeCommand
{
    use ModularCommand, ModularGenerator;
}
