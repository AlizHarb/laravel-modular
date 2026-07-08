<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands;

use AlizHarb\Modular\Events\ModularRefreshed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

final class ModularRefreshCommand extends Command
{
    protected $signature = 'modular:refresh';

    protected $description = 'Clear and rebuild the modular discovery cache';

    public function handle(): int
    {
        $clearResult = $this->call('modular:clear');

        if ($clearResult !== self::SUCCESS) {
            return $clearResult;
        }

        $cacheResult = $this->call('modular:cache');

        if ($cacheResult !== self::SUCCESS) {
            return $cacheResult;
        }

        Event::dispatch(new ModularRefreshed((string) config('modular.cache.path', base_path('bootstrap/cache/modular.php'))));
        $this->components->info('Modular discovery refreshed successfully.');

        return self::SUCCESS;
    }
}
