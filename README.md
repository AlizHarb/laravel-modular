# Laravel Modular 🚀

<img src="art/banner.png" alt="Laravel Modular Banner" width="100%" height="300">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/alizharb/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-modular)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/alizharb/laravel-modular/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/alizharb/laravel-modular/actions?query=workflow%3ATests+branch%3Amain)
[![GitHub PHPStan Action Status](https://img.shields.io/github/actions/workflow/status/alizharb/laravel-modular/phpstan.yml?branch=main&label=phpstan&style=flat-square)](https://github.com/alizharb/laravel-modular/actions?query=workflow%3APHPStan+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/alizharb/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-modular)
[![Licence](https://img.shields.io/packagist/l/alizharb/laravel-modular.svg?style=flat-square)](https://packagist.org/packages/alizharb/laravel-modular)

**Laravel Modular** is a professional, framework-agnostic modular system engineered for Laravel 11/12. It empowers you to build scalable, strictly typed, and decoupled applications with zero configuration overhead.

We override 29+ native Artisan commands to provide a seamless "first-class" modular experience, feeling exactly like standard Laravel but better.

## ✨ Features

- 🏗️ **Native Experience**: 29+ Artisan commands (`make:model`, `make:controller`, etc.) fully support `--module`.
- ⚡ **Zero Config Autoloading**: Intelligent `composer-merge-plugin` integration for isolated module dependencies.
- 🚀 **Performance First**: Built-in discovery caching (`modular:cache`) for near-zero overhead in production.
- 🔄 **Dynamic Activation**: Enable or disable modules on the fly via `module:enable` and `module:disable`.
- 🔍 **Auto-Discovery**: Automatic registration of Artisan commands, Policies, and Event Listeners within modules.
- 🔌 **Decoupled Architecture**: Strictly typed `ModuleRegistry` and traits for maximum stability.
- 🛠️ **Full Customizability**: Publishable stubs, dynamic config paths, and global helpers.
- ✅ **Laravel 11 & 12 Ready**: Optimized for PHP 8.2+ and the latest framework features.
- 🎨 **Asset Management**: Seamless Vite integration via `modular_vite()` and asset linking.

---

## 🌍 Ecosystem

Enhance your modular application with our official packages:

- **[Laravel Hooks](https://github.com/AlizHarb/laravel-hooks)**: specific modular hook system support.
- **[Filament Integration](https://github.com/AlizHarb/laravel-modular-filament)**: Seamless Filament admin panel integration in modules.
- **[Livewire Integration](https://github.com/AlizHarb/laravel-modular-livewire)**: First-class Livewire component support in modules.
- **[Laravel Themer](https://github.com/AlizHarb/laravel-themer)**: Advanced theme management system.

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require alizharb/laravel-modular
```

Run the installation command to automatically configure your application:

```bash
php artisan modular:install
```

> **Note**: This will automatically install and configure `wikimedia/composer-merge-plugin` to handle your module dependencies.

### Manual Setup
If you prefer to configure things manually, follow these steps:

**1. Composer Autoloading**
Add the following to your root `composer.json` to ensure module namespaces are autoloaded:

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Modules\\": "modules/"
    }
},
"extra": {
    "merge-plugin": {
        "include": [
            "modules/*/composer.json"
        ]
    }
}
```

**2. Vite Configuration**
To enable hot-readling for module assets, create a `vite.modular.js` file in your root and update `vite.config.js`:

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { modularLoader } from './vite.modular.js';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                ...modularLoader.inputs() // Add this line
            ],
            refresh: [
                ...modularLoader.refreshPaths() // Add this line
            ],
        }),
    ],
});
```

---

## 📖 Usage

### Creating a Module

Generate a fully structured module in seconds:

```bash
php artisan make:module Blog
```

### Generating Resources

Every standard Laravel `make:` command acts as a modular command when you pass the `--module` flag:

```bash
# Create a Model with Migration, Controller, and Factory in 'Blog' module
php artisan make:model Post --module=Blog -mcf

# Create a resource controller
php artisan make:controller API/PostController --module=Blog --api
```

### Modular Database

Run migrations and seeders specifically for your modules:

```bash
# Migrate all modules
php artisan modular:migrate

# Migrate a specific module
php artisan modular:migrate Blog --fresh --seed

# Migrate a specific module
php artisan modular:migrate Blog --fresh --seed

# Run module seeders
php artisan modular:seed Blog

# List all modules and discovered resources
php artisan modular:list

# Sync module dependencies to root composer.json
php artisan modular:sync

# Run npm commands for a module (Workspaces)
php artisan modular:npm Blog install
php artisan modular:npm Blog build

# Check for circular dependencies
php artisan modular:check

# Debug module configuration
php artisan modular:debug Blog

# Run module tests
php artisan modular:test Blog
```

### 🏎️ Performance Optimization

For maximum production performance, we recommended the following:

1.  **Optimized PSR-4**: Ensure `"Modules\\": "modules/"` is in your root `composer.json`. `modular:install` handles this for you.
2.  **Dependency Syncing**: Use `php artisan modular:sync` to merge module dependencies into your root `composer.json` and disable the merge-plugin.
3.  **Discovery Caching**: Always run `php artisan modular:cache` in your deployment pipeline.

### Middleware & Config

Define middleware in your `module.json`:

```json
"middleware": {
    "web": ["Modules\\Blog\\Http\\Middleware\\TrackVisits"],
    "blog.admin": "Modules\\Blog\\Http\\Middleware\\AdminGuard"
}
```

Access config case-insensitively:

```php
// Both work!
config('Blog::settings.key');
config('blog::settings.key');
```

---

## 🛠️ Helpers & Assets

### Global Helpers

Access module information globally with strictly typed helpers:

```php
// Get the registry or specific module config
$modules = module(); 
$blogConfig = module('Blog');

// Get absolute path to a resource
$viewPath = module_path('Blog', 'Resources/views');

// Get absolute path to a config file
$configPath = module_config_path('Blog', 'settings.php');
```

### Asset Management

Link your module assets to `public/modules` for easy serving:

```bash
php artisan modular:link
```

Use the helper to generate asset URLs in your Blade views:

```blade
<link rel="stylesheet" href="{{ module_asset('Blog', 'css/app.css') }}">
<img src="{{ module_asset('Blog', 'images/logo.png') }}" alt="Blog Logo">
```

---

## ⚙️ Configuration

Publish the configuration file for advanced customization:

```bash
php artisan vendor:publish --tag="modular-config"
```

You can customize:
- **Paths**: Move modules to `packages/` or any custom directory.
- **Stubs**: Enable custom stubs to strictly enforce your team's coding standards.
- **Composer**: Set default fields (`vendor`, `author`, `license`) for generated `composer.json` files.

---

## 🧪 Testing

We strictly enforce testing. Use the provided test suite to verify your modules:

```bash
vendor/bin/pest
```

---

## 💖 Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel Modular development. If you are interested in becoming a sponsor, please visit the [Laravel Modular GitHub Sponsors page](https://github.com/sponsors/alizharb).

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 🌟 Acknowledgments

- **Laravel**: For creating the most elegant PHP framework.
- **Spatie**: For setting the standard on Laravel package development.

---

## 🔒 Security

If you discover any security-related issues, please email **Ali Harb** at [harbzali@gmail.com](mailto:harbzali@gmail.com).

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

<p align="center">
    Made with ❤️ by <strong>Ali Harb</strong>
</p>
