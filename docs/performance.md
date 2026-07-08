# Performance

Laravel Modular is designed to be safe in development and fast in production.

## Discovery in Development

During development, the registry scans the modules directory, reads each `module.json`, and discovers module metadata. This keeps local changes visible without forcing a manual rebuild after every edit.

Use these commands while developing:

```bash
php artisan modular:list
php artisan modular:debug Blog
php artisan modular:doctor
```

## Discovery in Production

Production should use cached discovery:

```bash
php artisan modular:cache
```

The cache file stores:

- modules
- enabled/disabled statuses
- discovered policies and events
- resource flags
- manifest hashes
- dependency hashes
- provider lists
- cache timestamp

The cache file is written to `bootstrap/cache/modular.php` by default.

## Cache Freshness

Laravel Modular records manifest hashes when building the discovery cache. If a module manifest changes after caching, `modular:doctor` can detect stale discovery:

```bash
php artisan modular:doctor
```

Refresh discovery when module metadata changes:

```bash
php artisan modular:refresh
```

## Deployment Recommendation

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan modular:cache
```

If your deployment platform reuses build artifacts or cache directories between releases, prefer:

```bash
php artisan modular:refresh
```

## Dependency Syncing

For projects that use module-level Composer dependencies, use:

```bash
php artisan modular:sync
```

This lets teams move from dynamic module dependency loading toward optimized root dependency resolution for production.

## Runtime Status Files

Module enabled/disabled statuses are stored under `bootstrap/cache` by default. Treat these as runtime state. Do not commit local status files unless your deployment process intentionally manages module states through source control.
