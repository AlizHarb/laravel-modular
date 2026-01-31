# Helper Functions



## `module()`

Access the Module Registry or get configuration for a specific module.

**Usage 1: Get Registry Instance**
```php
$registry = module();
// Returns \AlizHarb\Modular\ModuleRegistry
```
```php
$config = module('Shop');

if ($config) {
    echo $config['version']; // "1.0.0"
    echo $config['namespace']; // "Modules\Shop\"
}
```

## `Modular` Facade
Alternatively, you can use the facade for object-oriented access.

```php
use AlizHarb\Modular\Facades\Modular;

// Get all modules
$modules = Modular::getModules();

// Check existence
if (Modular::moduleExists('Shop')) {
    // ...
}
```

---

## `module_path()`

Get the absolute filesystem path to a module.

**Usage 1: Module Root**
```php
$path = module_path('Shop');
// Result: "/var/www/modules/Shop"
```

**Usage 2: Specific File**
```php
$path = module_path('Shop', 'resources/views/index.blade.php');
// Result: "/var/www/modules/Shop/resources/views/index.blade.php"
```

---

## `module_config_path()`

Shortcut to get the path of a config file inside a module.

```php
$path = module_config_path('Shop', 'permissions.php');
// Result: "/var/www/modules/Shop/config/permissions.php"
```

---

## `module_asset()`

Generates a public URL for an asset. Use this when your asset has been published (via `modular:link` or Vite build).

**Usage:**
```blade
<img src="{{ module_asset('Shop', 'images/logo.png') }}" />
```
**Result:** `http://your-app.test/modules/shop/images/logo.png`

*Note: The assumption is that assets are symlinked to `public/modules/{module-lower}`.*

---

## `modular_vite()`

Renders the `<script>` and `<link>` tags for Vite HMR (Hot Module Replacement) or production builds.

**Important:** This implementation supports loading assets from **any** directory, not just the root `resources/`.

**Usage in Blade:**
```blade
<head>
    <!-- 
       Load the specific JS/CSS entry points for the Shop module.
       This points to modules/Shop/resources/assets/...
    -->
    {{ modular_vite([
        'resources/assets/css/app.css', 
        'resources/assets/js/app.js'
    ], 'modules/shop/build') }}
</head>
```
