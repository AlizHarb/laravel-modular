# Artisan Commands

## Generator Commands (The Daily Drivers)

These commands create files inside your modules. They support **all** standard Laravel flags (like `-m`, `-c`, `-r`).

### `make:module`

Creates a brand new module with the full directory structure.

```bash
php artisan make:module Shop
```

**What it does:**

1. Creates the directory structure (App, Database, Resources, etc).
2. Generates initial files: `module.json`, `composer.json`, `package.json`, `vite.config.js`.
3. **New in v1.1.4**: Automatically generates `.gitignore` and `.gitattributes`.
4. Creates `ShopServiceProvider`.
5. Updates `composer.json` autoloading (PSR-4).
6. **New in v1.1.4**: Automatically clears modular cache and optionally links assets (see [Configuration](configuration.md)).

---

### `make:model`

Creates an Eloquent model.

```bash
php artisan make:model Product --module=Shop
```

**Options:**

- `-m`, `--migration`: Create a migration file.
- `-c`, `--controller`: Create a controller.
- `-r`, `--resource`: Controller should be a Resource Controller.
- `-f`, `--factory`: Create a factory.
- `-s`, `--seed`: Create a seeder.
- `--policy`: Create a policy.
- `-a`, `--all`: Do EVERYTHING (migration, factory, seeder, policy, controller, resource).

**Example:**

```bash
# Create Model + Migration + Factory + Resource Controller
php artisan make:model Order --module=Shop -mfr
```

---

### `make:controller`

Creates a controller class.

```bash
php artisan make:controller ProductController --module=Shop
```

**Options:**

- `--resource`: Generate a resource controller (index, create, store...).
- `--api`: Generate an API controller (no create/edit methods).
- `--model=Product`: Bind the controller to a model.

---

### `make:migration`

Creates a database migration.

```bash
php artisan make:migration create_orders_table --module=Shop
```

**File Location:**

Creates `modules/Shop/database/migrations/xxxx_xx_xx_create_orders_table.php`.

---

### Other Generators

All of these work exactly as you expect, just add `--module=Name`.

- `make:command` (Console Command)
- `make:component` (Blade Component)
- `make:event`
- `make:factory`
- `make:job`
- `make:listener`
- `make:mail`
- `make:middleware`
- `make:notification`
- `make:observer`
- `make:policy`
- `make:provider`
- `make:request` (Form Request)
- `make:resource` (API Resource)
- `make:rule`
- `make:seeder`
- `make:test`

---

## Management Commands (`modular:*`)

commands to manage the lifecycle and state of your modules.

### `modular:list`

Displays a table of all modules, their status (Enabled/Disabled), and path.

```bash
php artisan modular:list
```

---

### `modular:migrate`

Migrate the database.

```bash
# Migrate ALL enabled modules + core app
php artisan modular:migrate

# Migrate ONLY the Shop module
php artisan modular:migrate Shop

# Rollback
php artisan modular:migrate:rollback Shop
```

---

### `modular:seed`

Run database seeders.

```bash
# Seed 'Shop' module (looks for Shop\Database\Seeders\ShopSeeder)
php artisan modular:seed Shop
```

---

### `modular:test`

Run PHPUnit/Pest tests with isolation.

```bash
php artisan modular:test Shop

# Run tests with unified coverage (requires phpunit/phpcov)
php artisan modular:test --coverage-html=coverage-report
```

**Coverage Options:**

- `--coverage`: Enable coverage collection.
- `--coverage-clover={path}`: Export Clover XML.
- `--coverage-html={path}`: Export HTML report.

*Note: Individual module coverage is collected in isolation and merged into a single report.*

---

### `modular:npm`

Run NPM commands inside a module's directory.

```bash
# Install a package for Shop
php artisan modular:npm Shop install chart.js

# Build assets for Shop
php artisan modular:npm Shop run build
```

---

### `modular:sync`

This command is critical for large teams. It scans all `packages/modular/*/composer.json` files and merges their requirements into the root `composer.json` (into a `requires` section managed by the package).

*Note: This usually happens automatically during `make:module`, but run this if you manually edit dependencies.*

```bash
php artisan modular:sync
```

---

### `modular:check`

Checks for circular dependencies between modules.

```bash
php artisan modular:check
```

---

### `modular:link`

Symlinks module public assets to the `public/` directory.

```bash
php artisan modular:link
```

---

### `modular:cache`

Create a cache file for faster module discovery. Checks for config, views, translations, and migrations.

```bash
php artisan modular:cache
```

---

### `modular:clear`

Remove the modular discovery cache file.

```bash
php artisan modular:clear
```

---

### `modular:debug`

Debug module configuration, providers, and middleware.

```bash
# Debug all modules summary
php artisan modular:debug

# Debug a specific module (deep dive)
php artisan modular:debug Shop
```

---

### `modular:ide-helper`

Generate a helper file (`_ide_helper_modular.php`) to help IDEs auto-complete module names.

```bash
php artisan modular:ide-helper
```

---

### `modular:doctor`

Diagnose common configuration issues and architectural integrity.

```bash
php artisan modular:doctor
```

**What it checks:**

- **Autoloading**: Verifies PSR-4 registration in `composer.json`.
- **Circular Dependencies**: Integrates `modular:check` logic.
- **Metadata**: Validates `module.json` and basic directory structure.
- **Ghost Modules**: Detects directories in the modules path that are missing a `module.json`.
- **Duplicate Providers**: Identifies if the same Service Provider is registered in multiple modules.
- **Asset Linking**: Verifies that the required `public/modules` directory exists.

---

### `modular:publish`

Publish configuration and stub files for customization.

```bash
php artisan modular:publish
```

- Select `config` to publish `config/modular.php`.
- Select `stubs` to publish generator stubs.

---

### `module:enable`

Enable a module instantly.

```bash
php artisan module:enable Shop
```

*Note: Enabled modules are verified for dependencies. If a module requires another module that is currently disabled, the command will fail.*

---

### `module:disable`

Disable a module instantly.

```bash
php artisan module:disable Shop
```

*Disabled modules are not loaded, their routes are 404, and their services are not booted.*

---

### `module:uninstall`

Uninstall (delete) a module.

```bash
# Uninstall the Shop module
php artisan module:uninstall Shop

# Force uninstall in production
php artisan module:uninstall Shop --force
```
