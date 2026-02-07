<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Concerns;

use AlizHarb\Modular\Commands;
use AlizHarb\Modular\Commands\Laravel as ModularConsole;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Console as DBConsole;
use Illuminate\Foundation\Console as LaravelConsole;
use Illuminate\Routing\Console as RoutingConsole;

trait HasCommands
{
    protected function registerModularCommands(): void
    {
        $this->registerMakeOverrides();

        $this->commands([
            Commands\ModularMakeModuleCommand::class,
            Commands\ModularInstallCommand::class,
            Commands\ModularMigrateCommand::class,
            Commands\ModularSeedCommand::class,
            Commands\ModularLinkCommand::class,
            Commands\ModularCacheCommand::class,
            Commands\ModularClearCommand::class,
            Commands\ModuleEnableCommand::class,
            Commands\ModuleDisableCommand::class,
            Commands\ModuleUninstallCommand::class,
            Commands\ModularCheckCommand::class,
            Commands\ModularPublishCommand::class,
            Commands\ModularTestCommand::class,
            Commands\ModularDebugCommand::class,
            Commands\ModularIdeHelperCommand::class,
            Commands\ModularSyncCommand::class,
            Commands\ModularListCommand::class,
            Commands\ModularNpmCommand::class,
            Commands\ModularDoctorCommand::class,
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
            LaravelConsole\CastMakeCommand::class => ModularConsole\ModularCastMakeCommand::class,
            LaravelConsole\ChannelMakeCommand::class => ModularConsole\ModularChannelMakeCommand::class,
            LaravelConsole\ClassMakeCommand::class => ModularConsole\ModularClassMakeCommand::class,
            LaravelConsole\ComponentMakeCommand::class => ModularConsole\ModularComponentMakeCommand::class,
            LaravelConsole\ConsoleMakeCommand::class => ModularConsole\ModularConsoleMakeCommand::class,
            RoutingConsole\ControllerMakeCommand::class => ModularConsole\ModularControllerMakeCommand::class,
            LaravelConsole\EnumMakeCommand::class => ModularConsole\ModularEnumMakeCommand::class,
            LaravelConsole\EventMakeCommand::class => ModularConsole\ModularEventMakeCommand::class,
            LaravelConsole\ExceptionMakeCommand::class => ModularConsole\ModularExceptionMakeCommand::class,
            DBConsole\Factories\FactoryMakeCommand::class => ModularConsole\ModularFactoryMakeCommand::class,
            LaravelConsole\InterfaceMakeCommand::class => ModularConsole\ModularInterfaceMakeCommand::class,
            LaravelConsole\JobMakeCommand::class => ModularConsole\ModularJobMakeCommand::class,
            LaravelConsole\ListenerMakeCommand::class => ModularConsole\ModularListenerMakeCommand::class,
            LaravelConsole\MailMakeCommand::class => ModularConsole\ModularMailMakeCommand::class,
            RoutingConsole\MiddlewareMakeCommand::class => ModularConsole\ModularMiddlewareMakeCommand::class,
            DBConsole\Migrations\MigrateMakeCommand::class => ModularConsole\ModularMigrateMakeCommand::class,
            LaravelConsole\ModelMakeCommand::class => ModularConsole\ModularModelMakeCommand::class,
            LaravelConsole\NotificationMakeCommand::class => ModularConsole\ModularNotificationMakeCommand::class,
            LaravelConsole\ObserverMakeCommand::class => ModularConsole\ModularObserverMakeCommand::class,
            LaravelConsole\PolicyMakeCommand::class => ModularConsole\ModularPolicyMakeCommand::class,
            LaravelConsole\ProviderMakeCommand::class => ModularConsole\ModularProviderMakeCommand::class,
            LaravelConsole\RequestMakeCommand::class => ModularConsole\ModularRequestMakeCommand::class,
            LaravelConsole\ResourceMakeCommand::class => ModularConsole\ModularResourceMakeCommand::class,
            LaravelConsole\RuleMakeCommand::class => ModularConsole\ModularRuleMakeCommand::class,
            LaravelConsole\ScopeMakeCommand::class => ModularConsole\ModularScopeMakeCommand::class,
            DBConsole\Seeds\SeederMakeCommand::class => ModularConsole\ModularSeederMakeCommand::class,
            LaravelConsole\TestMakeCommand::class => ModularConsole\ModularTestMakeCommand::class,
            LaravelConsole\TraitMakeCommand::class => ModularConsole\ModularTraitMakeCommand::class,
            LaravelConsole\ViewMakeCommand::class => ModularConsole\ModularViewMakeCommand::class,
        ];

        foreach ($commands as $original => $modular) {
            $this->app->extend($original, function (mixed $command, Application $app) use ($modular) {
                if ($modular === ModularConsole\ModularMigrateMakeCommand::class) {
                    return new $modular($app->make('migration.creator'), $app->make('composer'));
                }

                return new $modular($app->make('files'));
            });
        }
    }
}
