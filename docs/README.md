# Introduction

**Laravel Modular** is a professional, framework-agnostic modular architecture designed specifically for **Laravel 11+**. It empowers you to build scalable, strictly typed, and decoupled applications without the overhead of complex configuration.

We override **29+ native Artisan commands** to provide a seamless "first-class" modular experience. If you know Laravel, you already know Laravel Modular.

## ✨ Why Laravel Modular?

building a modular application often feels like fighting the framework. You have to manually register service providers, configure weird autoloading rules, and fight with paths.

**Laravel Modular fixes this.**

- 🏗️ **Native Experience**: Use `php artisan make:model Post --module=Blog` just like you normally would.
- ⚡ **Zero Config**: Autoloading works out of the box via an intelligent `composer-merge-plugin` integration.
- 🚀 **Performance First**: Built-in discovery caching for near-zero overhead in production.
- 🔄 **Dynamic Activation**: Enable or disable modules on the fly without touching code.
- 🔍 **Auto-Discovery**: Automatic registration of Artisan commands, Policies, and more.
- 🔌 **Frontend Agnostic**: Works perfectly with Blade, Vue, React, Livewire, and Filament.
- ✅ **Laravel 11 & 12 Ready**: Built on the latest PHP 8.2+ standards.

## 🚀 Quick Start

If you want to see it in action immediately:

```bash
composer require alizharb/laravel-modular
php artisan modular:install
php artisan make:module Blog
php artisan make:controller PostController --module=Blog
```

That's it. You just built a modular feature.
