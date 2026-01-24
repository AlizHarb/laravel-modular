<?php

declare(strict_types=1);

namespace AlizHarb\Modular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void discoverModules()
 * @method static array<string, array{path: string, name: string, namespace: string, provider: ?string}> getModules()
 * @method static array{path: string, name: string, namespace: string, provider: ?string}|null getModule(string $name)
 * @method static bool moduleExists(string $name)
 * @method static string resolveNamespace(string $module, string $class)
 * @method static string resolvePath(string $module, string $path = '')
 * @method static void registerAutoloading()
 *
 * @see \AlizHarb\Modular\ModuleRegistry
 */
class Modular extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'modular.registry';
    }
}
