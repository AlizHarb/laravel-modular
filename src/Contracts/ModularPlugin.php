<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Contracts;

use AlizHarb\Modular\ModuleRegistry;
use Illuminate\Contracts\Foundation\Application;

/**
 * Interface for modular plugins.
 */
interface ModularPlugin
{
    /**
     * Get the unique identifier for the plugin.
     */
    public function getId(): string;

    /**
     * Register services for all modules.
     *
     * @param array<string, mixed> $modules
     */
    public function register(Application $app, ModuleRegistry $registry, array $modules): void;

    /**
     * Boot services for all modules.
     *
     * @param array<string, mixed> $modules
     */
    public function boot(Application $app, ModuleRegistry $registry, array $modules): void;
}
