# Global Helpers

Laravel Modular provides convenient global helper functions for accessing module information.

## `module()`

Get the module registry or specific module configuration.

### Get Registry

```php
$registry = module();
```

### Get Specific Module

```php
$config = module('Blog');
// Returns: ['name' => 'Blog', 'alias' => 'blog', ...]
```

## `module_path()`

Get the absolute path to a module or a file within a module.

### Get Module Root

```php
$path = module_path('Blog');
// Returns: /path/to/modules/Blog
```

### Get Specific File

```php
$viewPath = module_path('Blog', 'resources/views');
// Returns: /path/to/modules/Blog/resources/views

$modelPath = module_path('Blog', 'app/Models/Post.php');
// Returns: /path/to/modules/Blog/app/Models/Post.php
```

## `module_asset()`

Generate a public URL for a module asset.

### Usage

```php
$cssUrl = module_asset('Blog', 'css/app.css');
// Returns: http://yourapp.test/modules/blog/css/app.css

$imageUrl = module_asset('Blog', 'images/logo.png');
// Returns: http://yourapp.test/modules/blog/images/logo.png
```

### In Blade

```blade
<link rel="stylesheet" href="{{ module_asset('Blog', 'css/app.css') }}">
<img src="{{ module_asset('Blog', 'images/logo.png') }}" alt="Logo">
```

## `modular_vite()`

A dedicated Vite helper for modular assets. It automatically targets the correct build directory.

### Usage

```blade
{{ modular_vite(['resources/css/app.css', 'resources/js/app.js'], 'blog') }}
```

By default, it uses the `modular.paths.assets` configuration (usually `modules`).

## `module_config_path()`

Get the absolute path to a module's config directory or file.

### Get Config Directory

```php
$configDir = module_config_path('Blog');
// Returns: /path/to/modules/Blog/config
```

### Get Specific Config File

```php
$settingsPath = module_config_path('Blog', 'settings.php');
// Returns: /path/to/modules/Blog/config/settings.php
```

## Practical Examples

### Loading Module Views Dynamically

```php
$moduleName = 'Blog';
$viewPath = module_path($moduleName, 'resources/views/posts/index.blade.php');

if (file_exists($viewPath)) {
    return view("{$moduleName}::posts.index");
}
```

### Checking Module Existence

```php
if (module('Blog')) {
    // Blog module exists
}
```

### Building Asset URLs

```php
$assets = [
    module_asset('Blog', 'css/app.css'),
    module_asset('Blog', 'js/app.js'),
];
```
