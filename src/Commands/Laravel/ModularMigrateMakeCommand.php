<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Commands\Laravel;

use AlizHarb\Modular\Concerns\ModularCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;

/**
 * Console command to create a new modular migration file.
 */
final class ModularMigrateMakeCommand extends MigrateMakeCommand
{
    use ModularCommand;

    /**
     * Create a new migration install command instance.
     *
     * @param  \Illuminate\Database\Migrations\MigrationCreator  $creator
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct($creator, $composer)
    {
        $this->signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration (Deprecated)}
        {--module= : The module to create the migration in}';

        parent::__construct($creator, $composer);
    }

    /**
     * Get the destination class path.
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        if ($this->isModular()) {
            $module = $this->getModule();

            return $this->getModuleRegistry()->resolvePath((string) $module, 'Database/Migrations');
        }

        return parent::getMigrationPath();
    }
}
