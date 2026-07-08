# Laravel Modular

Laravel Modular provides a native-feeling module architecture for Laravel applications. It keeps modules under `modules/{ModuleName}` while preserving standard Laravel concepts such as service providers, routes, config files, migrations, seeders, tests, views, translations, Vite assets, policies, events, middleware, and Artisan commands.

## Core Principles

- Prefer native Laravel APIs and conventions. A modular class should still look like normal Laravel code.
- Use Laravel Modular's `--module` option on supported `make:*` commands instead of manually creating files in module folders.
- Keep each module focused on one domain or bounded context.
- Do not couple modules by directly reaching into another module's internals. Use explicit `requires` metadata, contracts, events, or application services.
- Do not add custom scaffolding systems or app-specific stubs unless the project already uses them.
- Run diagnostics after changing module manifests, dependencies, service providers, or discovery-sensitive files.

## Module Structure

The default module root is `modules/{ModuleName}`. Important paths:

- `module.json`: module metadata, providers, dependencies, route prefix, events, policies, middleware, and lifecycle flags.
- `app/`: PSR-4 application code for the module namespace.
- `routes/web.php`, `routes/api.php`, `routes/channels.php`, `routes/console.php`: module routes.
- `config/*.php`: module config files, available as `Module::file.key` and usually lowercase aliases such as `module::file.key`.
- `database/migrations`, `database/seeders`, `database/factories`: module database resources.
- `resources/views`, `resources/lang`, `resources/assets`: module presentation and frontend resources.
- `tests/Feature`, `tests/Unit`: module tests.

## Creating Modules

Use the package command to create a module:

@verbatim
<code-snippet name="Create a Module" lang="shell">
php artisan make:module Blog
</code-snippet>
@endverbatim

After creating or changing modules, check the module registry:

@verbatim
<code-snippet name="Inspect Modules" lang="shell">
php artisan modular:list
php artisan modular:doctor
php artisan modular:debug Blog
php artisan modular:status
php artisan modular:graph
php artisan modular:why Blog
</code-snippet>
@endverbatim

## Generating Laravel Code in Modules

Use native Laravel generator commands with `--module={ModuleName}` whenever available:

@verbatim
<code-snippet name="Generate Native Laravel Classes in a Module" lang="shell">
php artisan make:model Post --module=Blog -mcf
php artisan make:controller PostController --module=Blog --resource
php artisan make:request StorePostRequest --module=Blog
php artisan make:policy PostPolicy --module=Blog --model=Post
php artisan make:test PostFeatureTest --module=Blog
</code-snippet>
@endverbatim

When generating module code:

- Keep namespaces under the module namespace, usually `Modules\Blog`.
- Put HTTP classes under `app/Http`.
- Put Eloquent models under `app/Models`.
- Put providers under `app/Providers`.
- Put console commands under `app/Console/Commands`.
- Put tests under the module's `tests` directory.

## Module Manifests

`module.json` is the source of truth for module metadata. Keep it valid and explicit:

@verbatim
<code-snippet name="Module Manifest Example" lang="json">
{
  "name": "Blog",
  "namespace": "Modules\\Blog\\",
  "provider": "Modules\\Blog\\Providers\\BlogServiceProvider",
  "version": "1.0.0",
  "requires": [],
  "removable": true,
  "disableable": true
}
</code-snippet>
@endverbatim

When editing `module.json`:

- Keep `name` aligned with the module directory name.
- Use semantic versions such as `1.2.0`.
- List required modules in `requires`.
- List incompatible modules in `conflicts`.
- List capabilities in `provides` when it helps diagnostics or documentation.
- Use `disableable: false` or `removable: false` for critical modules.
- Run `php artisan modular:doctor` after edits.

## Routes, Config, and Resources

- Add web routes to `routes/web.php`; they are loaded with the `web` middleware.
- Add API routes to `routes/api.php`; they are loaded with the `api` middleware and API prefix handling.
- Use `route_prefix` in `module.json` when all module routes need a common prefix.
- Add module config files to `config/*.php`.
- Add Blade views to `resources/views` and reference them with the module view namespace.
- Add module migrations to `database/migrations` and run them with `modular:migrate`.

@verbatim
<code-snippet name="Module Database Commands" lang="shell">
php artisan modular:migrate Blog
php artisan modular:seed Blog
php artisan modular:test Blog
</code-snippet>
@endverbatim

## Diagnostics and Production

Use diagnostics before and after structural changes:

@verbatim
<code-snippet name="Module Diagnostics" lang="shell">
php artisan modular:check
php artisan modular:doctor
php artisan modular:doctor --json
php artisan modular:debug Blog --json
php artisan modular:status --json
</code-snippet>
@endverbatim

For production deployments:

- Run `php artisan modular:cache` after installing dependencies and before serving traffic.
- Run `php artisan modular:refresh` when module manifests, providers, or dependency metadata may have changed.
- Run `php artisan modular:sync` when module composer dependencies should be merged into the root application.
- Do not commit runtime module status files from `bootstrap/cache`.

## Conditional UI

Use Blade conditionals when rendering UI for optional modules:

@verbatim
<code-snippet name="Module Blade Conditionals" lang="blade">
@moduleEnabled('Blog')
    <a href="{{ route('blog.index') }}">Blog</a>
@endmoduleEnabled

@moduleDisabled('Shop')
    <span>Shop is unavailable.</span>
@endmoduleDisabled
</code-snippet>
@endverbatim

## Best Practices for AI Agents

- First inspect the module with `modular:debug {Module}` before changing it.
- Prefer `make:* --module={Module}` commands over manual file creation.
- Keep generated code idiomatic Laravel; the module boundary is the folder and namespace, not a separate framework.
- Update or validate `module.json` whenever adding providers, dependencies, route prefixes, explicit events, policies, middleware, or lifecycle metadata.
- Add focused tests inside the module when changing module behavior.
- Run `modular:doctor` and the relevant module tests before considering the change complete.
- Use `modular:import-nwidart --dry-run` before migrating from nwidart-style module folders.
