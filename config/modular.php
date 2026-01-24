<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Modular Base Paths
    |--------------------------------------------------------------------------
    |
    | This configuration defines where your application's modules will live.
    | By default, they are stored in the root "modules" directory, which
    | provides a clean separation from your core "app" namespace while
    | maintaining standard PSR-4 compatibility.
    |
    | Professional Tip: You can move this to "packages" or any other
    | directory if your architecture requires specialized structuring.
    |
    */

    'paths' => [
        'modules' => base_path('modules'),
        'assets' => 'modules',
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming & Namespacing Conventions
    |--------------------------------------------------------------------------
    |
    | Laravel Modular relies on consistent naming to automate discovery.
    |
    | root_namespace: 
    |   The primary namespace prefix used for all modules. When you run 
    |   'php artisan make:module Blog', it will use 'Modules\Blog' by default.
    |
    | resource_prefix: 
    |   The unique identifier used when accessing modular resources like 
    |   views, translations, or Blade components. 
    |   Example: <x-module:button /> or view('blog::index')
    |
    */

    'naming' => [
        'root_namespace' => 'Modules',
        'resource_prefix' => 'module',
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Resource Discovery
    |--------------------------------------------------------------------------
    |
    | To ensure high performance, Laravel Modular lazily discovers resources.
    | You can fine-tune exactly what components are automatically registered.
    |
    | configs: Merges module-specific config files into the global config tree.
    | views: Registers the 'Resources/views' directory as a standard view hint.
    | translations: Registers the 'lang' directory for modular localization.
    | migrations: Adds the 'Database/Migrations' path to the migration creator.
    | routes: Automatically loads web.php and api.php from the 'Routes' directory.
    | blade_components: Registers anonymous Blade components for easy reuse.
    |
    */

    'discovery' => [
        'configs' => true,
        'views' => true,
        'translations' => true,
        'migrations' => true,
        'routes' => true,
        'blade_components' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Customization
    |--------------------------------------------------------------------------
    |
    | Laravel Modular uses stubs to generate new modules. You can publish
    | these stubs to the 'stubs/modular' directory and customize them to
    | match your team's coding style and standard practices.
    |
    | path: The path where your customized stubs are located.
    | enabled: Whether to prefer custom stubs over the package's defaults.
    |
    */

    'stubs' => [
        'enabled' => false,
        'path' => base_path('stubs/modular'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Composer Metadata & Generation
    |--------------------------------------------------------------------------
    |
    | When creating new modules via 'make:module', these values populate the
    | generated composer.json. This ensures your modules are professionals,
    | personalized, and ready for distribution or internal consistency.
    |
    | vendor: 
    |   Your organization or github handle. Used in 'vendor/module-name'.
    |
    | author: 
    |   Populates the 'authors' array in the generated modular composer.json.
    |
    | type:
    |   The default package type for the generated composer.json.
    |
    | license:
    |   The default license for the generated composer.json.
    |
    | composer-output:
    |   If enabled, Artisan will display raw output from any automated 
    |   composer commands triggered during module creation.
    |
    */

    'composer' => [
        'vendor' => 'alizharb',
        'author' => [
            'name' => 'Ali Harb',
            'email' => 'harbzali@gmail.com',
        ],
        'type' => 'library',
        'license' => 'MIT',
        'composer-output' => false,
    ],

];

