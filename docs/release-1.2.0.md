# Laravel Modular v1.2.0 Release Notes

Laravel Modular v1.2.0 is focused on making modular Laravel applications easier to inspect, safer to deploy, easier to migrate, and friendlier to AI-assisted development.

## Highlights

- Laravel Boost package guidelines and development skill.
- Native command parity improvements for module-aware Laravel generators.
- `modular:status`, `modular:graph`, and `modular:why`.
- `modular:doctor --json` and `modular:doctor --fix`.
- Stale cache detection using manifest hashes.
- `conflicts` and `provides` module metadata.
- `modular:import-nwidart` with dry-run support.
- Lifecycle events for module toggling and cache refreshes.

## Native Laravel Development

Continue using the Laravel commands you already know:

```bash
php artisan make:model Post --module=Blog -mcf
php artisan make:controller Api/PostController --module=Blog --api
php artisan make:test PostFeatureTest --module=Blog
```

## Production Diagnostics

```bash
php artisan modular:status
php artisan modular:doctor
php artisan modular:doctor --json
php artisan modular:graph
php artisan modular:why Blog
```

## Migration From nwidart

```bash
php artisan modular:import-nwidart --dry-run
php artisan modular:import-nwidart Blog --from=NwidartModules
```

## Recommended Upgrade Checks

```bash
composer update alizharb/laravel-modular
php artisan modular:refresh
php artisan modular:doctor
php artisan modular:check
php artisan test
```

## Notes

This release keeps the core philosophy intact: native Laravel code organized into modules, with strong diagnostics and production tooling around the module boundary.
