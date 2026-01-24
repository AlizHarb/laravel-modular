<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use AlizHarb\Modular\Concerns\ModularGenerator;
use Illuminate\Foundation\Console\NotificationMakeCommand;

/**
 * Console command to create a new modular notification class.
 */
final class ModularNotificationMakeCommand extends NotificationMakeCommand
{
    use ModularCommand, ModularGenerator;
}
