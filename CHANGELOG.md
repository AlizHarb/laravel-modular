# Changelog

All notable changes to `laravel-modular` will be documented in this file.

## v1.2.0 - 2026-07-07

### Added

- **Manifest Validation Engine**: Added reusable runtime validation for `module.json`, covering invalid JSON, required names, semantic versions, typed metadata, providers, dependencies, middleware, events, and directory/name mismatches.
- **Machine-Readable Diagnostics**: Added `--json` output to `modular:doctor` for CI pipelines, admin interfaces, and automated health checks.
- **Structured Module Inspection**: Added `--json` output to `modular:debug`, including module metadata, providers, middleware, dependencies, discovered policies/events, resource flags, and validation errors.
- **Safe Installer Preview**: Added `modular:install --dry-run` to preview installer changes without publishing resources or writing to Composer, Vite, PHPUnit, or workspace configuration files.
- **Cache Refresh Command**: Added `modular:refresh` to clear and rebuild modular discovery in one command.
- **Laravel Boost Support**: Added package AI guidelines and an on-demand `laravel-modular-development` skill so Boost-aware agents can generate native module code, inspect modules, validate manifests, and run the right diagnostics.
- **Observability Commands**: Added `modular:status`, `modular:graph`, and `modular:why` for project health, dependency graphs, and module explanations.
- **Doctor Fix Mode**: Added `modular:doctor --fix` for safe infrastructure repairs and stale cache refreshes.
- **Richer Module Metadata**: Added `conflicts` and `provides` support to module discovery, validation, diagnostics, and schema.
- **nwidart Importer**: Added `modular:import-nwidart` with dry-run support for migrating nwidart-style module directories.
- **Lifecycle Events**: Added module enable/disable and cache/refresh lifecycle events for integrations.
- **Adoption Documentation**: Added migration, command parity, performance, CI, comparison, security, and release note documentation for production teams evaluating Laravel Modular.

### Changed

- **Dependency Diagnostics**: Malformed dependency declarations now surface as validation or dependency-check issues instead of causing command errors.
- **Activator Safety**: Misconfigured custom activators now fail with explicit runtime errors when the class is missing or does not implement the activator contract.
- **Native Generator Parity**: Improved modular model and factory generation so native flags like `make:model --module=Blog -mcf` keep output in module-native Laravel paths.
- **README Positioning**: Rebuilt the README around native Laravel workflow, production diagnostics, Laravel Boost, nwidart migration, and adoption confidence.

---

## v1.1.5 - 2026-02-28

### Added

- **Class-Based Blade Components**: Properly mapped `Blade::componentNamespace` internally, allowing native `<x-module::component>` usage for PHP class-based components located in `app/View/Components/`.
- **Custom Eloquent Factory Resolution**: Intercepted Laravel's `Factory::guessFactoryNamesUsing` to successfully map `Modules\Shop\Models\Item` to `Modules\Shop\Database\Factories\ItemFactory` for intuitive testing.
- **Route Prefixes**: Automatically mounts all modular web/API routes using `route_prefix` defined within `module.json` (defaults to none for backward compatibility).
- **Topological Boot Sorting**: `ModuleRegistry` now implements Kahn's graph sorting algorithm to guarantee that modules are booted in strict order of their dependencies.
- **Custom Per-Module Stubs**: `make:*` commands will now dynamically check a module's `stubs/` directory first, allowing you to override scaffolding templates on a per-module basis.
- **Blade Directives**: Introduced `@moduleEnabled('ModuleName')` and `@moduleDisabled('ModuleName')` directives for clean conditional rendering in views.
- **Explicit Event Mapping**: Added support for explicit event-to-listener registration defined in `module.json`'s `events` array, operating alongside convention-based subscriber discovery.
- **Migration Rollbacks**: `modular:migrate` now fully mimics native Laravel by supporting `--rollback` and `--step=N` flags to intelligently revert single-module migrations.
- **Dependency Tree Visualization**: `modular:list` now accepts a `--tree` flag that prints a visual ASCII tree of module dependencies and their enabled/disabled status.
- **Module Health Score**: `modular:doctor` now features a comprehensive 100-point Health Score per module, intelligently evaluating criteria like testing coverage, readme presence, dependencies, and valid manifests.
- **Module Extractor**: Added a powerful new `modular:export {module}` command to detach and export a module to a target directory as a fully-functional, standalone Composer package.
- **Native Testing Support**: Running `php artisan test` or `./vendor/bin/pest` from the root of a Laravel application natively discovers and runs all tests inside `modules/*/tests/`.
- **Dynamic PHPUnit Injection**: `modular:install` seamlessly injects the module test paths into the host application's `phpunit.xml` or `phpunit.xml.dist`.
- **Automatic Test Autoloading**: Newly generated modules via `make:module` now include proper `autoload-dev` mappings for PSR-4 compliance. Legacy modules are automatically patched during `modular:install`.
- **Laravel 13 Support**: Official compatibility with Laravel 13.
- **New Helper Method**: Added `modules_path($path = '')` helper to easily retrieve the absolute path to the project's root modules directory.

### Changed

- **Composer Test Script**: `modular:install` optimizes `composer.json`'s test script to prevent duplicated tests.

---

## v1.1.4 - 2026-02-06

### Added

- **Enhanced `make:module`**: Automatically generate `.gitignore` and `.gitattributes` files within new modules to encourage a "module-as-a-package" workflow.
- **Inter-Module Dependency Enforcement**: The `module:enable` command now verifies that required dependencies (defined in `module.json`) are also enabled.
- **Activation Caching**: Introduced a high-performance caching layer for module activation statuses. Caching is now integrated into the `modular:cache` command, reducing filesystem hits during bootstrap.
- **Advanced Diagnostics**: Expanded `modular:doctor` with new checks for ghost modules (directories without `module.json`), duplicate service provider registrations, and asset linking verification.
- **New `modular:doctor` Command**: Introduced `php artisan modular:doctor` to diagnose common configuration issues, verify dependencies, and validate module structure.
- **PHP 8.4 & 8.5 Support**: Updated CI workflows to verify compatibility with upcoming PHP versions.
- **Test Script Configuration**: `modular:install` now automatically configures the `composer.json` "test" script to run both application and module tests in isolation.

### Changed

- **Standardized Configuration**: enforced consistent `pint.json` and `composer.json` dev-dependencies across the entire ecosystem.
- **Refactored `HasCommands`**: Simplified internal trait by replacing ~60 lines of imports with clean namespace aliases.
- **Isolated Testing**: `php artisan modular:test` (without arguments) now sequentially runs tests for _all_ modules in their own isolated environments.

## v1.1.3 - 2026-01-31

### Added

- **New Command**: Introduced `module:uninstall {module}` command to safely remove modules. This command checks for the `removable` flag and clears the module cache upon completion.
- **Module Metadata**: Added `removable` and `disableable` fields to `module.json` schema and registry.
    - `removable`: Controls whether the module can be uninstalled via CLI (default: `true`).
    - `disableable`: Controls whether the module can be disabled via CLI (default: `true`).
- **Documentation**: Enhanced documentation site.

### Changed

- **Command Security**: The `module:disable` command now respects the `disableable` metadata flag, preventing critical modules from being disabled accidentally.
- **Stubs & Schema**: Updated `module.json` stub and schema to include and validate the new metadata fields.

---

## v1.1.2 - 2026-01-27

### Added

- **New `modular:list` Command**: Visualize all registered modules, discovered policies, events, and their discovery sources (Convention vs Explicit).
- **New `modular:sync` Command**: Sync module-specific dependencies from `modules/*/composer.json` into the root `composer.json` for optimized production performance.
- **New `modular:npm` Command**: Manage module-level assets easily using NPM Workspaces from the Artisan console.
- **Monorepo-lite Assets**: Each module now gets its own `package.json` and `vite.config.js` for isolated dependency and asset management.
- **Discovery Tracking**: `ModuleRegistry` now tracks the source of discovered resources for better transparency.

### Changed

- **Optimized Autoloading**: `modular:install` now automatically adds PSR-4 autoloading for the Modules namespace to the root `composer.json`, significantly improving class loading performance.
- **NPM Workspaces**: `modular:install` now configures the root `package.json` with NPM Workspaces for efficient module asset management.
- **Improved Installation Flow**: The installation process is now more performance-focused and provides better guidance on optimized vs fallback autoloading.

---

## v1.1.1 - 2026-01-26

### Added

- **Independent Vite Loader**: Introduced `vite.modular.js` for clean, standalone asset discovery in `modules/`.
- **Improved Installation**: `modular:install` now asks for user consent before automatically updating `composer.json` and `vite.config.js`.
- **Manual Configuration Guide**: Added detailed instructions and code snippets when the user chooses to manually configure Vite.

### Fixed

- **Module Stub Namespace**: Fixed incorrect `app` segment in the Service Provider namespace in `module.json.stub`.
- **Test Infrastructure**: Optimized `phpunit.xml` and `phpstan.neon` for independent package verification.

---

## v1.1.0 - 2026-01-25

### Added

- **Native Routing**: Support for `web.php`, `api.php`, and `console.php` with full Route Caching support.
- **Config Merging**: Automatic merging of module config files into `modules.{module}.{file}`.
- **Provider Auto-Discovery**: Support for `providers` array in `module.json` for auto-registration.
- **New Commands**:
    - `modular:check`: Detect circular dependencies.
    - `modular:publish`: Publish module assets, views, and config.
    - `modular:test`: Run tests for specific modules.
    - `modular:debug`: Visualize module status, providers, paths, and middleware.
    - `modular:ide-helper`: Generate IDE autocomplete helper for modules.
- **Config Aliasing**: Case-insensitive config access with opt-out support via `modular.config.alias`.
- **Middleware Registry**: Support for defining middleware aliases and groups in `module.json`.
- **Performance First**: Built-in discovery caching (`modular:cache` and `modular:clear`) for near-zero overhead in production.
- **Dynamic Activation**: Enable or disable modules dynamically using the new `FileActivator` system.
- **Artisan Management**: New commands `module:enable {module}` and `module:disable {module}`.
- **Auto-Discovery**:
    - Automatic registration of Artisan commands within `app/Console/Commands`.
    - Automatic registration of Policies within `app/Policies`.
    - Support for custom Event Listener discovery logic.
- **JSON Schema**: Added `module.schema.json` for IDE autocompletion and validation of `module.json`.
- **Versioning**: Modules now support a `version` field in `module.json`.
- **Vite Integration**: Added `modular_vite()` helper for effortless asset loading across modules.
- **Themer Integration**: Optional, first-class support for `alizharb/laravel-themer`.

### Changed

- Improved `ModuleRegistry` with lazy discovery and caching.
- Optimized `HasCommands` and `HasResources` traits for better performance.
- Updated module stubs to include the latest conventions and schema support.

### Fixed

- Improved path resolution in test environments.
- Fixed command registration timing in feature tests.
- Resolved various linting and static analysis warnings.

---

## v1.0.0 - 2026-01-24

- Initial release with core modular architecture.
- 29+ Artisan command overrides.
- Zero-config autoloading.
