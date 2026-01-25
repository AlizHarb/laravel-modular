<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Activators;

use AlizHarb\Modular\Contracts\Activator;
use Illuminate\Support\Facades\File;

class FileActivator implements Activator
{
    /**
     * The path to the statuses file.
     */
    protected string $path;

    /**
     * Cached statuses.
     *
     * @var array<string, bool>|null
     */
    protected ?array $statuses = null;

    /**
     * Create a new FileActivator instance.
     */
    public function __construct()
    {
        $this->path = config('modular.activators.file.statuses-file', base_path('bootstrap/cache/modules_statuses.json'));
    }

    /**
     * Check if a module is enabled.
     */
    public function isEnabled(string $name): bool
    {
        return $this->getStatuses()[$name] ?? true;
    }

    /**
     * Set the enabled status of a module.
     */
    public function setStatus(string $name, bool $status): void
    {
        $statuses = $this->getStatuses();
        $statuses[$name] = $status;
        $this->statuses = $statuses;

        $this->write();
    }

    /**
     * Delete the activation status for a module.
     */
    public function delete(string $name): void
    {
        $statuses = $this->getStatuses();
        unset($statuses[$name]);
        $this->statuses = $statuses;

        $this->write();
    }

    /**
     * Reset all module activation statuses.
     */
    public function reset(): void
    {
        $this->statuses = [];
        $this->write();
    }

    /**
     * Get all activation statuses.
     *
     * @return array<string, bool>
     */
    protected function getStatuses(): array
    {
        if ($this->statuses !== null) {
            return $this->statuses;
        }

        if (! File::exists($this->path)) {
            return $this->statuses = [];
        }

        $content = File::get($this->path);
        
        /** @var array<string, bool> $statuses */
        $statuses = json_decode($content, true) ?: [];

        return $this->statuses = $statuses;
    }

    /**
     * Write the statuses to the file.
     */
    protected function write(): void
    {
        if (! File::isDirectory(dirname($this->path))) {
            File::makeDirectory(dirname($this->path), 0755, true);
        }

        File::put($this->path, json_encode($this->statuses, JSON_PRETTY_PRINT));
    }
}
