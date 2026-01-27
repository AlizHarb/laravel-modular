# Changelog

All notable changes to `laravel-modular` will be documented in this file.

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
