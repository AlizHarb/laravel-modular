<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Concerns;

use AlizHarb\Modular\Commands\Laravel\ModularCastMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularChannelMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularClassMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularComponentMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularConsoleMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularControllerMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularEnumMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularEventMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularExceptionMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularFactoryMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularInterfaceMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularJobMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularListenerMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularMailMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularMiddlewareMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularMigrateMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularModelMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularNotificationMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularObserverMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularPolicyMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularProviderMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularRequestMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularResourceMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularRuleMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularScopeMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularSeederMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularTestMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularTraitMakeCommand;
use AlizHarb\Modular\Commands\Laravel\ModularViewMakeCommand;
use AlizHarb\Modular\Commands\ModularInstallCommand;
use AlizHarb\Modular\Commands\ModularLinkCommand;
use AlizHarb\Modular\Commands\ModularMakeModuleCommand;
use AlizHarb\Modular\Commands\ModularMigrateCommand;
use AlizHarb\Modular\Commands\ModularSeedCommand;
use AlizHarb\Modular\Commands\ModularSyncCommand;
use AlizHarb\Modular\Commands\ModularListCommand;
use AlizHarb\Modular\Commands\ModularNpmCommand;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Console\Factories\FactoryMakeCommand;
use Illuminate\Database\Console\Migrations\MigrateMakeCommand;
use Illuminate\Database\Console\Seeds\SeederMakeCommand;
use Illuminate\Foundation\Console\CastMakeCommand;
use Illuminate\Foundation\Console\ChannelMakeCommand;
use Illuminate\Foundation\Console\ClassMakeCommand;
use Illuminate\Foundation\Console\ComponentMakeCommand;
use Illuminate\Foundation\Console\ConsoleMakeCommand;
use Illuminate\Foundation\Console\EnumMakeCommand;
use Illuminate\Foundation\Console\EventMakeCommand;
use Illuminate\Foundation\Console\ExceptionMakeCommand;
use Illuminate\Foundation\Console\InterfaceMakeCommand;
use Illuminate\Foundation\Console\JobMakeCommand;
use Illuminate\Foundation\Console\ListenerMakeCommand;
use Illuminate\Foundation\Console\MailMakeCommand;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Foundation\Console\NotificationMakeCommand;
use Illuminate\Foundation\Console\ObserverMakeCommand;
use Illuminate\Foundation\Console\PolicyMakeCommand;
use Illuminate\Foundation\Console\ProviderMakeCommand;
use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Foundation\Console\ResourceMakeCommand;
use Illuminate\Foundation\Console\RuleMakeCommand;
use Illuminate\Foundation\Console\ScopeMakeCommand;
use Illuminate\Foundation\Console\TestMakeCommand;
use Illuminate\Foundation\Console\TraitMakeCommand;
use Illuminate\Foundation\Console\ViewMakeCommand;
use Illuminate\Routing\Console\ControllerMakeCommand;
use Illuminate\Routing\Console\MiddlewareMakeCommand;

trait HasCommands
{
    protected function registerModularCommands(): void
    {
        $this->registerMakeOverrides();

        $this->commands([
            ModularMakeModuleCommand::class,
            ModularInstallCommand::class,
            ModularMigrateCommand::class,
            ModularSeedCommand::class,
            ModularLinkCommand::class,
            \AlizHarb\Modular\Commands\ModularCacheCommand::class,
            \AlizHarb\Modular\Commands\ModularClearCommand::class,
            \AlizHarb\Modular\Commands\ModuleEnableCommand::class,
            \AlizHarb\Modular\Commands\ModuleDisableCommand::class,
            \AlizHarb\Modular\Commands\ModuleUninstallCommand::class,
            \AlizHarb\Modular\Commands\ModularCheckCommand::class,
            \AlizHarb\Modular\Commands\ModularPublishCommand::class,
            \AlizHarb\Modular\Commands\ModularTestCommand::class,
            \AlizHarb\Modular\Commands\ModularDebugCommand::class,
            \AlizHarb\Modular\Commands\ModularIdeHelperCommand::class,
            ModularSyncCommand::class,
            ModularListCommand::class,
            ModularNpmCommand::class,
        ]);

        if (config('modular.discovery.commands', true)) {
            $this->discoverModuleCommands();
        }
    }

    /**
     * Discover Artisan commands within modules.
     */
    protected function discoverModuleCommands(): void
    {
        $registry = $this->getModuleRegistry();
        $modules = $registry->getModules();

        foreach ($modules as $moduleName => $module) {
            $commandPath = $module['path'].'/app/Console/Commands';

            if (! is_dir($commandPath)) {
                continue;
            }

            foreach (\Illuminate\Support\Facades\File::allFiles($commandPath) as $file) {
                $relativePath = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
                $class = rtrim($module['namespace'], '\\').'\\Console\\Commands\\'.$relativePath;

                if (class_exists($class) && ! (new \ReflectionClass($class))->isAbstract()) {
                    $this->commands($class);
                }
            }
        }
    }

    protected function registerMakeOverrides(): void
    {
        $commands = [
            CastMakeCommand::class => ModularCastMakeCommand::class,
            ChannelMakeCommand::class => ModularChannelMakeCommand::class,
            ClassMakeCommand::class => ModularClassMakeCommand::class,
            ComponentMakeCommand::class => ModularComponentMakeCommand::class,
            ConsoleMakeCommand::class => ModularConsoleMakeCommand::class,
            ControllerMakeCommand::class => ModularControllerMakeCommand::class,
            EnumMakeCommand::class => ModularEnumMakeCommand::class,
            EventMakeCommand::class => ModularEventMakeCommand::class,
            ExceptionMakeCommand::class => ModularExceptionMakeCommand::class,
            FactoryMakeCommand::class => ModularFactoryMakeCommand::class,
            InterfaceMakeCommand::class => ModularInterfaceMakeCommand::class,
            JobMakeCommand::class => ModularJobMakeCommand::class,
            ListenerMakeCommand::class => ModularListenerMakeCommand::class,
            MailMakeCommand::class => ModularMailMakeCommand::class,
            MiddlewareMakeCommand::class => ModularMiddlewareMakeCommand::class,
            MigrateMakeCommand::class => ModularMigrateMakeCommand::class,
            ModelMakeCommand::class => ModularModelMakeCommand::class,
            NotificationMakeCommand::class => ModularNotificationMakeCommand::class,
            ObserverMakeCommand::class => ModularObserverMakeCommand::class,
            PolicyMakeCommand::class => ModularPolicyMakeCommand::class,
            ProviderMakeCommand::class => ModularProviderMakeCommand::class,
            RequestMakeCommand::class => ModularRequestMakeCommand::class,
            ResourceMakeCommand::class => ModularResourceMakeCommand::class,
            RuleMakeCommand::class => ModularRuleMakeCommand::class,
            ScopeMakeCommand::class => ModularScopeMakeCommand::class,
            SeederMakeCommand::class => ModularSeederMakeCommand::class,
            TestMakeCommand::class => ModularTestMakeCommand::class,
            TraitMakeCommand::class => ModularTraitMakeCommand::class,
            ViewMakeCommand::class => ModularViewMakeCommand::class,
        ];

        foreach ($commands as $original => $modular) {
            $this->app->extend($original, function (mixed $command, Application $app) use ($modular) {
                if ($modular === ModularMigrateMakeCommand::class) {
                    return new $modular($app->make('migration.creator'), $app->make('composer'));
                }

                return new $modular($app->make('files'));
            });
        }
    }
}
