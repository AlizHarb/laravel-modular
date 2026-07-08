---
name: laravel-modular-development
description: Build, inspect, test, and maintain Laravel Modular modules using native Laravel conventions and the package's module-aware Artisan commands.
---

# Laravel Modular Development

Use this skill when working in a Laravel application that uses `alizharb/laravel-modular`, especially when creating module classes, changing module manifests, diagnosing module discovery, or moving code between the root app and a module.

## Workflow

1. Inspect the module registry before changing structure:

```shell
php artisan modular:list
php artisan modular:debug ModuleName
php artisan modular:status
```

2. Generate code with native Laravel commands and the `--module` option:

```shell
php artisan make:model Post --module=Blog -mcf
php artisan make:controller PostController --module=Blog --resource
php artisan make:request StorePostRequest --module=Blog
php artisan make:test PostFeatureTest --module=Blog
```

3. Keep module metadata accurate in `modules/{ModuleName}/module.json`.

4. Test the affected module:

```shell
php artisan modular:test ModuleName
```

5. Run diagnostics after structural changes:

```shell
php artisan modular:check
php artisan modular:doctor
php artisan modular:graph
php artisan modular:why ModuleName
```

## Native Laravel Rules

- Prefer Laravel's normal routing, controllers, models, requests, policies, events, listeners, jobs, notifications, mailables, resources, casts, rules, factories, migrations, and tests.
- Use module boundaries for organization, not a custom framework layer.
- Do not create new scaffold or stub systems in the host application unless the project already has one.
- Keep module namespaces under `Modules\{ModuleName}` unless `module.json` declares a custom namespace.
- Put module service providers under `app/Providers`.
- Put module HTTP classes under `app/Http`.
- Put module Eloquent models under `app/Models`.
- Put module tests under `tests/Feature` or `tests/Unit` inside the module.

## Manifest Rules

`module.json` should be explicit and valid:

```json
{
  "name": "Blog",
  "namespace": "Modules\\Blog\\",
  "provider": "Modules\\Blog\\Providers\\BlogServiceProvider",
  "version": "1.0.0",
  "requires": [],
  "removable": true,
  "disableable": true
}
```

- Keep `name` aligned with the module directory.
- Use semantic versions.
- Add dependencies to `requires`.
- Add incompatible modules to `conflicts`.
- Add capabilities to `provides` when useful.
- Use `disableable: false` and `removable: false` only for critical modules.
- Run `php artisan modular:doctor --json` when machine-readable validation is useful.

## Discovery Rules

- Routes are discovered from `routes/web.php`, `routes/api.php`, `routes/channels.php`, and `routes/console.php`.
- Config files are discovered from `config/*.php`.
- Views are discovered from `resources/views`.
- Translations are discovered from `lang`.
- Migrations are discovered from `database/migrations`.
- Commands are discovered from `app/Console/Commands`.
- Policies and events can be explicit in `module.json` or discovered by convention.

## Production Rules

- Run `php artisan modular:cache` during deployment.
- Run `php artisan modular:refresh` when module manifests, providers, or dependency metadata changed.
- Run `php artisan modular:sync` when module composer dependencies should be merged into the root application.
- Do not commit runtime cache/status files from `bootstrap/cache`.
- Use `php artisan modular:import-nwidart --dry-run` before migrating nwidart-style modules.

## Completion Checklist

- Module files are in the correct native Laravel locations.
- `module.json` is valid after edits.
- `php artisan modular:doctor` passes or known warnings are explained.
- The affected module tests pass.
- Cache refresh guidance is included when deployment behavior changes.
