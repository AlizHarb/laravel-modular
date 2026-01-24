<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Routing\Console\MiddlewareMakeCommand;

/**
 * Console command to create a new modular middleware class.
 */
final class ModularMiddlewareMakeCommand extends MiddlewareMakeCommand
{
    use ModularCommand, ModularGenerator;
}
