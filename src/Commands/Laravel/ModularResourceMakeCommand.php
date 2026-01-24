<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\ResourceMakeCommand;

/**
 * Console command to create a new modular resource class.
 */
final class ModularResourceMakeCommand extends ResourceMakeCommand
{
    use ModularCommand, ModularGenerator;
}
