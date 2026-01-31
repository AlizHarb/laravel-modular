# Introduction

**Laravel Modular** is a strictly typed, scalable, and zero-configuration modular architecture implementation for Laravel 11+.

> Transform your monolithic application into a domain-driven powerhouse without the complexity.

## Why Modular?

Scaling a standard Laravel app can get messy. **Laravel Modular** organizes your code into dedicated "Modules" (e.g., `Blog`, `Shop`, `Auth`), keeping your domains isolated and your implementation clean.

- **Zero Config**: It just works. Auto-discovery for everything.
- **Native Feel**: Use `php artisan make:model --module=Blog`.
- **Production Ready**: Unbeatable performance with built-in caching.
- **Strict & Typed**: Built for professional, large-scale applications.

## Installation

Get started in 30 seconds.

### 1. Require via Composer

```bash
composer require alizharb/laravel-modular
```

### 2. Install & Configure

This command sets up your `modules/` directory, configures autoloading, and prepares your `composer.json` for module dependencies.

```bash
php artisan modular:install
```

> **Pro Tip:** The installer automatically configures `composer-merge-plugin` so each module can have its own `composer.json` dependencies!

## Quick Start

Create your first module instantly:

```bash
php artisan make:module Shop
```

You are now ready to build!
