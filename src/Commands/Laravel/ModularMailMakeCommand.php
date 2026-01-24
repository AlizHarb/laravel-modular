<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\MailMakeCommand;

/**
 * Console command to create a new modular mail class.
 */
final class ModularMailMakeCommand extends MailMakeCommand
{
    use ModularCommand, ModularGenerator;
}
