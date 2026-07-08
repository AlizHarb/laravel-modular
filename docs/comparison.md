# Comparison

Laravel Modular is designed for teams that want module boundaries without leaving native Laravel conventions.

## Positioning

Established packages such as `nwidart/laravel-modules` have a mature ecosystem and a large command surface. Laravel Modular takes a different path: keep Laravel's own generator commands, service providers, routes, migrations, factories, policies, events, tests, Vite assets, and package workflows recognizable.

## Laravel Modular Focus

- Native Laravel command flow via `make:* --module=Name`.
- Module metadata validated through `module.json`.
- First-class diagnostics with `modular:doctor`, `modular:status`, `modular:debug`, `modular:graph`, and `modular:why`.
- Machine-readable JSON output for CI and tools.
- Laravel Boost package guidelines and skills for AI-assisted development.
- Production discovery caching with cache freshness checks.
- Safe migration path from nwidart-style module directories.
- Optional ecosystem integrations instead of hard dependencies in the core package.

## When to Choose Laravel Modular

Choose Laravel Modular when:

- You want module boundaries but still want code to look like ordinary Laravel.
- Your team relies on Laravel's native generator commands and flags.
- You need diagnostics that explain module state in CI or production.
- You want Laravel Boost-aware AI agents to understand your module architecture.
- You may later extract modules into standalone Composer packages.

## When Another Package May Fit Better

Another package may fit better when:

- Your team already standardized on its command vocabulary and file layout.
- You need a legacy module structure without migration work.
- You prefer a package-specific scaffolding workflow over native Laravel generator parity.

## Migration

Use `modular:import-nwidart` to preview and import nwidart-style modules:

```bash
php artisan modular:import-nwidart --dry-run
php artisan modular:import-nwidart Blog --from=NwidartModules
```

After migration:

```bash
php artisan modular:doctor
php artisan modular:graph
php artisan modular:test Blog
```
