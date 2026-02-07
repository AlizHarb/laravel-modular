# Configuration Reference

## `modular.php` (Global Config)

This file lives in `config/modular.php`.

### Paths

Where your code lives.

```php
'paths' => [
    // Where modules are stored. Change to base_path('packages') if you want.
    'modules' => base_path('modules'),
    
    // Where assets are symlinked to in public/
    'assets' => 'modules', 
],

// Automatically link assets after make:module
'auto_link' => true,
```

### Naming

Code generation defaults.

```php
'naming' => [
    // The PSR-4 namespace prefix. 
    'modules' => 'Modules\\',
],
```

### Generators

Default flags for `make:module`.

```php
'generators' => [
    'test' => [
        'type' => 'pest', // or 'phpunit'
    ],
],
```

### Activators

How we remember if a module is enabled or disabled.

```php
'activators' => [
    'file' => [
        'class' => \AlizHarb\Modular\Activators\FileActivator::class,
        'statuses-file' => base_path('modules_statuses.json'),
        'cache-key' => 'activator.installed_modules',
        'cache-lifetime' => 604800,
    ],
],
```

### Cache

Speed up discovery in production.

```php
'cache' => [
    'enabled' => false,
    'key' => 'modular.modules.cache',
    'lifetime' => 0,
    'path' => storage_path('framework/cache/modular.php'),
],
```
