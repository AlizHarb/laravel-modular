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
```

### `modular:cache`

Create a cache file for faster module discovery. Highly recommended for production.

```bash
php artisan modular:cache
```

### `modular:clear`

Remove the modular cache file.

```bash
php artisan modular:clear
```

### `module:enable`

Enable a specific module dynamically.

```bash
php artisan module:enable Blog
```

### `module:disable`

Disable a specific module dynamically.

```bash
php artisan module:disable Blog
```

### `modular:check`

Check for circular dependencies between modules.

```bash
php artisan modular:check
```

### `modular:debug`

Visualize module status, providers, paths, and middleware configuration.

```bash
php artisan modular:debug [Module]
```

### `modular:publish`

Publish module assets, views, config, and translations.

```bash
php artisan modular:publish Blog
```

### `modular:test`

Run tests for a specific module.

```bash
php artisan modular:test Blog
```

### `modular:ide-helper`

Generate an IDE helper file (`_ide_helper_modular.php`) for better autocompletion.

```bash
php artisan modular:ide-helper
```

### `modular:list`

List all modules and their discovered resources (Policies, Events, etc.).

```bash
php artisan modular:list
```

**Options:**
- `--only`: Filter by type (`modules`, `policies`, or `events`).

### `modular:sync`

Sync module-level dependencies into the root `composer.json` for performance.

```bash
php artisan modular:sync
```

**Options:**
- `--dry-run`: Show what would be synced without making changes.

### `modular:npm`

Run npm commands in a specific module's workspace from the root.

```bash
php artisan modular:npm Blog install
php artisan modular:npm Blog build
```

**What it does:**
- Automatically targets the `@modules/blog` workspace.
- Runs 'npm install' at the root level if the command is `install` (global sync).
- Simplifies managing isolated module dependencies.

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
