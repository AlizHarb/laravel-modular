<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Contracts;

interface Activator
{
    /**
     * Check if a module is enabled.
     */
    public function isEnabled(string $name): bool;

    /**
     * Set the enabled status of a module.
     */
    public function setStatus(string $name, bool $status): void;

    /**
     * Delete the activation status for a module.
     */
    public function delete(string $name): void;

    /**
     * Reset all module activation statuses.
     */
    public function reset(): void;
}
