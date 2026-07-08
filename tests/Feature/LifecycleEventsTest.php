<?php

use AlizHarb\Modular\Events\ModularCached;
use AlizHarb\Modular\Events\ModularRefreshed;
use AlizHarb\Modular\Events\ModuleDisabled;
use AlizHarb\Modular\Events\ModuleDisabling;
use AlizHarb\Modular\Events\ModuleEnabled;
use AlizHarb\Modular\Events\ModuleEnabling;
use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

afterEach(function () {
    File::deleteDirectory(base_path('modules'));
    File::delete(base_path('bootstrap/cache/modular.php'));
    File::delete(base_path('bootstrap/cache/modules_statuses.json'));
    app()->forgetInstance(ModuleRegistry::class);
});

it('dispatches lifecycle events when modules are toggled', function () {
    Event::fake();

    File::ensureDirectoryExists(base_path('modules/Billing'));
    File::put(base_path('modules/Billing/module.json'), json_encode([
        'name' => 'Billing',
        'version' => '1.0.0',
    ]));

    app()->forgetInstance(ModuleRegistry::class);

    $this->artisan('module:enable Billing')->assertExitCode(0);
    $this->artisan('module:disable Billing')->assertExitCode(0);

    Event::assertDispatched(ModuleEnabling::class);
    Event::assertDispatched(ModuleEnabled::class);
    Event::assertDispatched(ModuleDisabling::class);
    Event::assertDispatched(ModuleDisabled::class);
});

it('dispatches cache lifecycle events', function () {
    Event::fake();

    $this->artisan('modular:cache')->assertExitCode(0);
    $this->artisan('modular:refresh')->assertExitCode(0);

    Event::assertDispatched(ModularCached::class);
    Event::assertDispatched(ModularRefreshed::class);
});
