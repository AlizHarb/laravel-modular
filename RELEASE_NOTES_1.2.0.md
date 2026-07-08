# Laravel Modular v1.2.0

Laravel Modular v1.2.0 is a major strengthening release focused on production confidence, native Laravel developer experience, Laravel Boost support, richer diagnostics, and easier migration from `nwidart/laravel-modules`.

This release keeps the package philosophy clear: write normal Laravel code, organize it into modules, and use strong tooling around the module boundary.

## Highlights

- Added Laravel Boost package guidelines and a dedicated `laravel-modular-development` skill.
- Added production observability commands: `modular:status`, `modular:graph`, and `modular:why`.
- Added `modular:doctor --json` for CI and machine-readable health checks.
- Added `modular:doctor --fix` for safe infrastructure repairs and stale cache refreshes.
- Added `modular:debug --json` for structured module inspection.
- Added `modular:install --dry-run` to preview installation changes safely.
- Added `modular:refresh` to clear and rebuild module discovery in one command.
- Added `modular:import-nwidart` with dry-run support for migration evaluation.
- Added module lifecycle events for enable, disable, cache, and refresh workflows.
- Added manifest validation for `module.json`.
- Added `conflicts` and `provides` support to module metadata.
- Improved native generator parity for module-aware model and factory generation.
- Rebuilt the README and expanded documentation for production teams.

## Added

### Laravel Boost Support

Laravel Modular now ships first-class Laravel Boost resources:

```text
resources/boost/guidelines/core.blade.php
resources/boost/skills/laravel-modular-development/SKILL.md
```

Boost-aware agents can now understand Laravel Modular conventions, prefer native Laravel `make:* --module` commands, respect module boundaries, validate manifests, and run the correct diagnostics.

### Production Diagnostics

New commands make modular applications easier to inspect and operate:

```bash
php artisan modular:status
php artisan modular:graph
php artisan modular:why Blog
php artisan modular:doctor --json
php artisan modular:debug Blog --json
```

These commands help teams understand module health, dependency relationships, providers, middleware, resources, validation errors, and why a module exists in the application.

### Safe Repair Mode

`modular:doctor --fix` can now perform safe repairs, including creating missing module infrastructure and refreshing stale discovery cache when needed.

```bash
php artisan modular:doctor --fix
```

### nwidart Migration Helper

A new importer helps teams evaluate and migrate existing `nwidart/laravel-modules` style module directories:

```bash
php artisan modular:import-nwidart --dry-run
php artisan modular:import-nwidart Blog --from=NwidartModules
```

### Richer Module Metadata

`module.json` now supports additional production-oriented metadata:

```json
{
  "conflicts": [],
  "provides": []
}
```

These fields are included in validation, diagnostics, schema support, and dependency checks.

### Lifecycle Events

Laravel Modular now dispatches lifecycle events for integrations and automation:

- `ModuleEnabling`
- `ModuleEnabled`
- `ModuleDisabling`
- `ModuleDisabled`
- `ModularCached`
- `ModularRefreshed`

## Changed

### Native Laravel Generator Parity

Module-aware model and factory generation has been improved so native Laravel flags stay inside module-native paths.

Example:

```bash
php artisan make:model Post --module=Blog -mcf
```

This now keeps generated models, migrations, controllers, and factories aligned with the module structure.

### Better Activator Safety

Misconfigured custom activators now fail with explicit runtime errors when the class is missing or does not implement the expected activator contract.

### Stronger Dependency Diagnostics

Malformed dependency declarations now surface through validation and dependency checks instead of causing unclear command failures.

### Professional Documentation

The README and documentation have been expanded and repositioned for production adoption, including:

- Migration from `nwidart/laravel-modules`
- Command parity overview
- Performance guidance
- CI guidance
- Laravel Boost support
- Package comparison
- Security policy
- v1.2.0 release notes

## Recommended Upgrade Steps

```bash
composer update alizharb/laravel-modular

php artisan modular:refresh
php artisan modular:doctor
php artisan modular:check
php artisan test
```

For production deployments, prefer:

```bash
php artisan modular:cache
php artisan modular:doctor --json
```

## Why This Release Matters

v1.2.0 makes Laravel Modular feel more mature, inspectable, and production-ready. It strengthens the package beyond simple module generation by adding diagnostics, migration tooling, Boost support, lifecycle hooks, richer metadata, and documentation that helps teams evaluate the package seriously.

Laravel Modular now has a clearer identity:

> Native Laravel development, organized into modules, backed by production-grade tooling.
