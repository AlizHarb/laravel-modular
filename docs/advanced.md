# Advanced Internals

## The Runtime Autoloader

One of the "magic" features of Laravel Modular is that you likely don't need to run `composer dump-autoload` when creating new modules or classes during development.

### How it works
The `ModularServiceProvider` registers a custom `spl_autoload_register` function.
1.  It intercepts any class beginning with `Modules\`.
2.  It checks the `ModuleRegistry` for a matching module namespace.
3.  It translates the namespace to a file path (e.g., `Modules\Shop\Services\Cart` -> `modules/Shop/app/Services/Cart.php`).
4.  It requires the file immediately.

**Trade-off**: This is slightly slower than Composer's optimized class map.
**Production**: In production, your `composer.json` (root) handles the autoloading via PSR-4, bypassing this runtime check entirely if you run `composer dump-autoload -o`.

---

## Production Caching Strategy

When you run:
```bash
php artisan modular:cache
```

The package performs the following steps:
1.  **Scans Filesystem**: Finds all `module.json` files.
2.  **Resolves State**: Checks if they are enabled/disabled via the Activator.
3.  **Compiles Configuration**: Builds a massive PHP array containing *all* metadata for *all* modules.
4.  **Writes File**: Saves to `bootstrap/cache/modular.php`.

On the next request, `ModuleRegistry` checks for this file. If found, it **skips all filesystem IO**.

**Important**: If you add a new module or change `module.json` in production, you **MUST** re-run `modular:cache`.

---

## Plugin System

The `ModularServiceProvider` allows external packages to hook into the lifecycle.

```php
use AlizHarb\Modular\ModularServiceProvider;
use AlizHarb\Modular\Contracts\ModularPlugin;

class MyPlugin implements ModularPlugin
{
    public function register($app, $registry, $modules) {
        // Run logic when modules are registered
    }

    public function boot($app, $registry, $modules) {
        // Run logic when modules are booted
    }
}

// In your AppServiceProvider:
ModularServiceProvider::registerPlugin(new MyPlugin());
```

This is how extensions like `laravel-modular-livewire` inject their own component discovery logic without modifying the core.

---

## Circular Dependencies

If Module A requires Module B, and Module B requires Module A, your application might crash during boot.

Use the check command to find these:
```bash
php artisan modular:check
```

This builds a dependency graph and alerts you to any cycles.

---

## Custom Activators

By default, we use the `FileActivator` which stores enabled/disabled state in `bootstrap/cache/modules_statuses.json`.

You can implement your own (e.g., Database storage) by implementing `AlizHarb\Modular\Contracts\Activator` and changing `config/modular.php`.
