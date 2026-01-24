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
2. **Setup Composer Merging**: Installs `wikimedia/composer-merge-plugin` and links your root `composer.json` to our package's Zero Config setup. This allows each module to have its own `composer.json` file.
3. **Vite Detection**: Checks your `vite.config.js` and warns if you need to add module paths (though we usually handle this automatically in the backend).

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
