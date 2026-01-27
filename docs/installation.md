# Installation

Installing Laravel Modular is designed to be as "lazy" as possible. We handle the heavy lifting for you.

## 1. Require the Package

Run the following command in your terminal:

```bash
composer require alizharb/laravel-modular
```

## 2. Install & Configure

Our installer does more than just publish config files. It intelligently detects your environment, sets up **Vite**, and configures **Composer** dependency merging.

```bash
php artisan modular:install
```

### What does this command do?
1. **Publishes Config**: Creates `config/modular.php`.
2. **Setup Optimized PSR-4**: Adds `"Modules\\": "modules/"` to your root `composer.json` for high-performance class loading.
3. **Setup Composer Merging**: Configures `wikimedia/composer-merge-plugin` to discover module-specific `composer.json` files and dependencies.
4. **NPM Workspaces**: Adds `"workspaces": ["modules/*"]` to your root `package.json` for isolated module asset management.
5. **Vite Detection**: Configures `vite.modular.js` and `vite.base.js` to enable seamless modular asset discovery and hot reloading.

## 3. Verify Installation

To verify everything is working, create your first dummy module:

```bash
php artisan make:module Playground
```

If you see a successful output, you are ready to build! 🚀

## Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 11.0 or higher
- **Composer**: 2.0+
