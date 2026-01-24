<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ChannelMakeCommand;

/**
 * Console command to create a new modular broadcast channel class.
 */
final class ModularChannelMakeCommand extends ChannelMakeCommand
{
    use ModularCommand, ModularGenerator;
}
