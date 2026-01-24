# Artisan Commands

Laravel Modular provides custom Artisan commands for managing your modules efficiently.

## Module Management

### `modular:install`

Installs and configures Laravel Modular in your application.

```bash
php artisan modular:install
```

**What it does:**
- Publishes configuration files
- Configures `composer-merge-plugin`
- Optionally publishes stubs for customization
- Checks Vite configuration

### `modular:migrate`

Run migrations for all modules or a specific module.

```bash
# Migrate all modules
php artisan modular:migrate

# Migrate specific module
php artisan modular:migrate Blog

# Fresh migration with seeding
php artisan modular:migrate Blog --fresh --seed
```

### `modular:seed`

Seed the database for all modules or a specific module.

```bash
# Seed all modules
php artisan modular:seed

# Seed specific module
php artisan modular:seed Blog
```

### `modular:link`

Create symbolic links from module assets to the public directory.

```bash
# Link all module assets
php artisan modular:link

# Link specific module
php artisan modular:link Blog
```

This creates symlinks from `modules/Blog/resources/assets` to `public/modules/blog`.

## Standard Laravel Commands

All standard Laravel `make:` commands work with the `--module` flag:

```bash
# Create a model
php artisan make:model Product --module=Shop -mcf

# Create a controller
php artisan make:controller ProductController --module=Shop --resource

# Create a request
php artisan make:request StoreProductRequest --module=Shop

# Create a test
php artisan make:test ProductTest --module=Shop
```

## Tips

- Use `--help` on any command to see all available options
- The `--module` flag is case-sensitive and should match your module name exactly
- You can chain flags just like standard Laravel commands
