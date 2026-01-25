# Configuration

Laravel Modular is designed to work with zero configuration, but offers extensive customization options when you need them.

## Publishing Configuration

```bash
php artisan vendor:publish --tag="modular-config"
```

This creates `config/modular.php` with the following options:

## Configuration Options

### Paths

```php
'paths' => [
    'modules' => base_path('modules'),
    'assets' => public_path('modules'),
],
```

Change the default `modules` directory to anything you want (e.g., `packages`, `features`).

### Naming

```php
'naming' => [
    'namespace' => 'Modules',
    'resource_prefix' => 'module',
],
```

Customize the root namespace and resource prefix for views/translations.

### Composer Defaults

```php
'composer' => [
    'vendor' => 'modules',
    'author' => [
        'name' => env('COMPOSER_AUTHOR_NAME', 'Your Name'),
        'email' => env('COMPOSER_AUTHOR_EMAIL', 'your@email.com'),
    ],
    'license' => 'MIT',
    'type' => 'library',
],
```

Set default values for generated `composer.json` files in each module.

### Stubs

```php
'stubs' => [
    'enabled' => false,
    'path' => base_path('stubs/modular'),
],
```

Enable custom stubs to enforce your team's coding standards.

### Resource Discovery

```php
'discovery' => [
    'routes' => true,
    'views' => true,
    'translations' => true,
    'migrations' => true,
    'config' => true,
],
```

Toggle automatic discovery of module resources.

### Config Aliasing

You can access module configuration using either the case-sensitive name or the lowercase alias:

```php
// Both work!
config('Blog::settings.key');
// Both work!
config('Blog::settings.key');
config('blog::settings.key');
```

> [!TIP]
> You can disable aliasing to save memory by setting `modular.config.alias` to `false` in `config/modular.php`.

## Environment Variables

You can set composer defaults in your `.env`:

```env
COMPOSER_AUTHOR_NAME="Ali Harb"
COMPOSER_AUTHOR_EMAIL="harbzali@gmail.com"
```
