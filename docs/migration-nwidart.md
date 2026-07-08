# Migration From nwidart/laravel-modules

Laravel Modular includes a guided importer for teams evaluating a move from `nwidart/laravel-modules`.

The importer is intentionally conservative. It copies module directories, writes a Laravel Modular-compatible `module.json`, and asks you to validate the result with diagnostics and tests.

## Start With a Dry Run

```bash
php artisan modular:import-nwidart --dry-run
```

Use a custom source directory when your old modules do not live in `Modules/`:

```bash
php artisan modular:import-nwidart --from=NwidartModules --dry-run
```

Import a single module:

```bash
php artisan modular:import-nwidart Blog --from=NwidartModules
```

## Filesystem Case Warning

On case-insensitive filesystems, `Modules` and `modules` can resolve to the same directory. If that happens, choose a distinct target:

```bash
php artisan modular:import-nwidart Blog --from=NwidartModules --to=modules
```

## What Gets Imported

The importer copies the source module directory into the target modular path and writes a normalized manifest:

```json
{
  "name": "Blog",
  "namespace": "Modules\\Blog\\",
  "providers": [
    "Modules\\Blog\\Providers\\BlogServiceProvider"
  ],
  "version": "1.0.0",
  "requires": [],
  "conflicts": [],
  "provides": [],
  "removable": true,
  "disableable": true
}
```

Existing manifest fields are preserved when they are compatible.

## What Still Needs Review

After importing, review:

- provider namespaces
- route file paths and casing
- config namespaces
- Composer autoload mappings
- module-specific dependencies
- public assets
- tests
- hard-coded references to old module paths

## Post-Migration Checklist

```bash
composer dump-autoload
php artisan modular:refresh
php artisan modular:doctor
php artisan modular:graph
php artisan modular:test Blog
```

For multiple modules:

```bash
php artisan modular:status
php artisan modular:check
php artisan test
```

## Recommended Strategy

Migrate one module first. Choose a small module with routes, config, and tests, but limited external dependencies. Once the import and validation flow is proven, migrate larger modules in batches.

Avoid mixing migration with business refactors. First make the module boot and test correctly in Laravel Modular. Then refactor structure or dependencies in a separate pull request.
