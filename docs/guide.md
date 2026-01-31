# Documentation Guide

**Build faster with a modular workflow that feels native.**

Stop fighting your architecture. Laravel Modular commands simply extend the Artisan commands you already know and love.

## Scaffolding

Just add `--module={Name}` to **any** standard Laravel command.

### Create a Module

```bash
php artisan make:module Blog
```

### Create Components

```bash
# Model + Migration + Controller + Factory
php artisan make:model Post --module=Blog -mcf

# Livewire Component (requires modular-livewire)
php artisan make:livewire PostsTable --module=Blog

# Form Request
php artisan make:request UpdatePostRequest --module=Blog
```

> **Lazy Tip:** You don't need to learn new commands. If you know `php artisan make:controller`, you already know how to use this package.

## Database

Run migrations for your modules without affecting the core app.

### Migrations

```bash
# Migrate everything (App + Modules)
php artisan modular:migrate

# Migrate ONLY the 'Shop' module
php artisan modular:migrate Shop
```

### Seeding

```bash
# Run seeders for 'Shop'
php artisan modular:seed Shop
```

## Management

Keep your modules organized.

- **List Modules**: `php artisan modular:list` (Visualize everything)
- **Sync Dependencies**: `php artisan modular:sync` (Merge `composer.json` from modules)
- **Uninstall**: `php artisan module:uninstall Blog` (Delete safely)
