<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\RequestMakeCommand;

/**
 * Console command to create a new modular form request class.
 */
final class ModularRequestMakeCommand extends RequestMakeCommand
{
    use ModularCommand, ModularGenerator;
}
