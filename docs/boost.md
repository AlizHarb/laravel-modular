# Laravel Boost

Laravel Modular ships first-class Laravel Boost resources so AI agents understand the package's native module workflow.

## Included Resources

- `resources/boost/guidelines/core.blade.php`: always-loaded package guidance for Laravel Modular conventions.
- `resources/boost/skills/laravel-modular-development/SKILL.md`: an on-demand skill for creating, inspecting, testing, and maintaining modules.

## Install or Refresh Boost Resources

After installing Laravel Boost in your application, run:

```bash
php artisan boost:install
```

If Laravel Modular was added after Boost was already installed, ask Boost to discover newly installed package resources:

```bash
php artisan boost:update --discover
```

## What Boost Learns

The package guidance teaches agents to:

- Use native Laravel `make:*` commands with `--module`.
- Keep module classes in normal Laravel locations under `modules/{ModuleName}`.
- Treat `module.json` as the source of truth for providers, dependencies, route prefixes, events, policies, middleware, and lifecycle flags.
- Run `modular:doctor`, `modular:debug`, `modular:check`, and module tests after structural changes.
- Avoid introducing custom scaffolding systems or app-specific stubs when native Laravel commands are available.
- Refresh or cache module discovery in production workflows.

## Recommended Agent Workflow

```bash
php artisan modular:list
php artisan modular:debug Blog
php artisan make:model Post --module=Blog -mcf
php artisan modular:test Blog
php artisan modular:doctor
```

This keeps AI-generated changes aligned with Laravel Modular's core promise: native Laravel code, organized by modules.
