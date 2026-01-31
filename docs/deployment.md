# Deployment

Deploying a modular Laravel application is practically identical to deploying a standard one, with one additional optimization step.

## pre-Deployment Checklist

1.  **Dependencies**: Ensure `composer install` is run (autoloads modules).
2.  **Assets**: Ensure `npm run build` is run (builds module assets).
3.  **Discovery**: Ensure the `bootstrap/cache` directory is writable.

## The Deployment Command

In your deployment script (Forge, Envoyer, GitHub Actions), add this **after** `composer install`:

```bash
# 1. Standard Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Modular optimization (Crucial!)
php artisan modular:cache
```

### What `modular:cache` does
It scans all your enabled modules and compiles a `bootstrap/cache/modular.php` file.
- **Without this:** Laravel scans your filesystem on *every request* to find modules, configs, and providers. (Slow)
- **With this:** Laravel checks one file and loads everything instantly. (Fast)

---

## Continuous Integration (CI/CD)

### GitHub Actions Example

```yaml
steps:
  - uses: actions/checkout@v3

  - name: Setup PHP
    uses: shivammathur/setup-php@v2
    with:
      php-version: '8.2'

  - name: Install Dependencies
    run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress

  - name: Run Tests
    # Runs tests for ALL modules + Core app
    run: php artisan test
```

### Modular specific CI checks

You can add these steps to control code quality:

```bash
# 1. Check for circular dependencies
php artisan modular:check

# 2. Run module-specific tests (optional matrix strategy)
php artisan modular:test Shop
```

---

## Troubleshooting Production

### "Module not found"
If you deploy and a module seems missing:
1.  Run `php artisan modular:list` to check status.
2.  Check `bootstrap/cache/modules_statuses.json`. This file persists enabled/disabled state. **Git ignore this file** so you don't accidentally disable modules in production that were disabled locally.
3.  Run `php artisan modular:clear` to ask the system to rescan.

### "Class not found"
If a module class isn't found:
1.  Ensure `composer dump-autoload -o` ran.
2.  If you changed a module's namespace in `module.json`, you must update `composer.json` autoload paths or re-run `modular:cache`.
