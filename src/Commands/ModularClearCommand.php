<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Console\Command;

class ModularClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modular:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the modular cache file';

    /**
     * Execute the console command.
     */
    public function handle(ModuleRegistry $registry): int
    {
        $registry->clearCache();

        $this->components->info('Modular cache cleared successfully.');

        return self::SUCCESS;
    }
}
