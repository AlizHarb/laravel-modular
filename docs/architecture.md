# Architecture & Concepts



## The Core Philosophy

Laravel Modular is built on **Strict Domain-Driven Design (DDD)** principles, but implemented with the **Laravel conventions** you already know.

### 1. The "Wall of Separation"
Modules are designed to be isolated.
- **Rule #1:** A module should generally not "know" about other modules.
- **Rule #2:** If Module A needs Module B, it must be an explicit dependency (defined in `module.json` and `composer.json`).
- **Rule #3:** Code sharing should happen via **Contracts (Interfaces)** or **Events**, not direct class instantiation.

### 2. Native Feel
We do not invent new concepts.
- A module's controller is just a Laravel controller.
- A module's provider is just a `Illuminate\Support\ServiceProvider`.
- There are no "Magic Facades" or proprietary layers you have to learn.

---

## Directory Structure

A typical modular application looks like this. We deviate slightly from the default `app/` structure to keep things tighter for modules.

```text
modules/
└── Shop/                      <-- The Module Root (StudlyCase)
    ├── composer.json          <-- PHP Dependencies (Isolated)
    ├── module.json            <-- Module Configuration
    ├── package.json           <-- JS/CSS Dependencies (Isolated)
    ├── vite.config.js         <-- Asset Build Config
    │
    ├── app/                   <-- PSR-4 Source Root (Mapped to Modules\Shop\)
    │   ├── Models/            <-- Eloquent Models
    │   │   └── Product.php
    │   ├── Http/
    │   │   ├── Controllers/   <-- Http Classes
    │   │   ├── Requests/
    │   │   └── Middleware/
    │   ├── Providers/         <-- derived from ServiceProvider
    │   │   ├── ShopServiceProvider.php (Main)
    │   │   └── EventServiceProvider.php
    │   └── Services/          <-- Business Logic (Optional)
    │
    ├── database/
    │   ├── migrations/        <-- Database Schema
    │   ├── seeders/           <-- Database Seeding
    │   └── factories/         <-- Model Factories
    │
    ├── resources/
    │   ├── views/             <-- Blade Templates
    │   ├── lang/              <-- Translations
    │   └── assets/            <-- Raw CSS/JS
    │
    ├── routes/
    │   ├── web.php            <-- Web Routes
    │   └── api.php            <-- API Routes
    │
    └── tests/
        ├── Unit/              <-- Isolated Unit Tests
        └── Feature/           <-- Integration Tests
```

### Key Differences from Standard Laravel
1.  **`app/` directory**: We use an `app` folder for PHP code, mirroring the standard Laravel application structure.
2.  **`composer.json`**: Each module has one. This allows you to extract a module into a standalone package easily in the future.
3.  **`Providers/`**: A module **must** have at least one ServiceProvider to boot itself.

---

## The Bootstrapping Process

How does Laravel know your module exists?

### 1. Auto-Discovery (`ModuleRegistry`)
When the framework boots, the `LaravelModularServiceProvider` (in the core package) instantiates the `ModuleRegistry`.
- It scans `modules/*` (or your configured path).
- It reads `module.json` for every folder.
- If the module is **enabled**, it registers the module's namespace and Service Provider.

### 2. Runtime Registration
Unlike standard packages where you manually add them to `config/app.php`, **Laravel Modular does this dynamically**.
- **Namespaces**: `Modules\Shop\` is registered to `modules/Shop/app/` via a runtime PSR-4 loader (or `composer.json` autoloading in production).
- **Providers**: The class defined in `module.json` (`provider` key) is called.

### 3. Deep Discovery
The `ModularServiceProvider` and `ModuleRegistry` automatically discover standard Laravel components without manual registration in your `ServiceProvider`.

| Component | Path (inside module) | Logic |
| :--- | :--- | :--- |
| **Routes** | `routes/web.php`, `routes/api.php`, `routes/channels.php`, `routes/console.php` | Automatically loaded. `web` middleware applied to `web.php`, `api` prefix + middleware to `api.php`. |
| **Config** | `config/*.php` | Merged into global config. Accessed via `Module::file.key` (or lowercase alias `module::file.key`). |
| **Commands** | `app/Console/Commands/*.php` | Automatically registered if class is not abstract. |
| **Policies** | `app/Policies/*.php` | Discovered if naming follows `ModelPolicy` convention (e.g., `ProductPolicy` for `Product` model). |
| **Events** | `app/Listeners/*.php` | Discovered if the class has a `subscribe` method. |
| **Views** | `resources/views/` | Registered under namespace `module-name::` (lowercase). |
| **Migrations** | `database/migrations/` | Registered and run by `migrate` command. |
| **Translations** | `lang/` | Registered under namespace `module-name::` (lowercase). |

### 4. Production Optimization
Scanning the filesystem is slow. In production, you run `php artisan modular:cache`.
- This creates `bootstrap/cache/modular.php`.
- It contains a static array of all enabled modules and their configs.
- The `ModuleRegistry` loads this array instantly, bypassing all filesystem checks.

---

## Service Providers

The `ShopServiceProvider.php` (generated by `make:module`) is your entry point. It automatically handles:

1.  **Loading Routes**: Loads `web.php` and `api.php`.
2.  **Loading Views**: Registers `resources/views` under the namespace `shop::`.
3.  **Loading Migrations**: Registers `database/migrations`.
4.  **Loading Translations**: Registers `resources/lang`.

You can modify this file exactly like `AppServiceProvider`.

```php
public function boot(): void
{
    parent::boot();

    // Your custom boot logic
    Model::preventLazyLoading();
}
```

---

## The `module.json` File

This is the brain of your module.

```json
{
    "name": "Shop",
    "namespace": "Modules\\Shop\\",
    "provider": "Modules\\Shop\\Providers\\ShopServiceProvider",
    "removable": true,
    "disableable": true,
    "requires": []
}
```

- **`disableable`**: (Boolean) If false, the module cannot be disabled via CLI/UI.
