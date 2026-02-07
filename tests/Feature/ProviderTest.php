<?php

namespace AlizHarb\Modular\Tests\Feature;

use AlizHarb\Modular\ModuleRegistry;
use AlizHarb\Modular\Tests\TestCase;

class ProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->singleton(ModuleRegistry::class, fn () => new ModuleRegistry());
    }

    public function test_it_registers_module_providers()
    {
        $provider = $this->app->getProvider(\AlizHarb\Modular\ModularServiceProvider::class);
        $this->assertTrue(method_exists($provider, 'registerModuleProviders'));
    }
}
