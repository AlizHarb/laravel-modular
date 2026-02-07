# Installation

Get started with Laravel Modular in seconds.

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher

## Install Package

Install the package via Composer:

```bash
composer require alizharb/laravel-modular
```

## Setup

Run the installation command to automatically configure your application (including dependency handling):

```bash
php artisan modular:install
```
> **Note:** This command will also offer to configure your `composer.json` test script to run both application and module tests in isolation.

## First Steps

Once installed, you are ready to create your first module.

```bash
php artisan make:module Blog
```

Check out the [Guide](guide) for more details on the workflow.
