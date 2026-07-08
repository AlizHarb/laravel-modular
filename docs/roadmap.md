# Roadmap

This roadmap keeps Laravel Modular focused on native Laravel conventions while making the package stronger for large applications, AI-assisted development, production deployments, and migration from other module systems.

## Principles

- Keep generated application code native Laravel.
- Prefer module-aware Laravel commands over custom scaffolding.
- Do not add new stub systems as a default workflow.
- Keep the core package lean; ecosystem integrations should remain optional packages.
- Make diagnostics and production behavior inspectable.

## Phase 1: Laravel Boost Support

- Ship `resources/boost/guidelines/core.blade.php`.
- Ship `resources/boost/skills/laravel-modular-development/SKILL.md`.
- Document `boost:install` and `boost:update --discover`.
- Teach agents to use `make:* --module`, `modular:doctor`, `modular:debug`, `modular:check`, `modular:test`, and `modular:refresh`.

## Phase 2: Native Command Parity

- Audit every Laravel generator override.
- Add tests for important native flags such as `--api`, `--resource`, `--model`, `--policy`, `--pest`, `-m`, `-c`, `-f`, and combinations like `-mcf`.
- Document command parity and known exceptions.
- Avoid adding non-native alternatives where Laravel already has a command.

## Phase 3: Observability Commands

- Add `modular:status` for a compact project health summary.
- Add `modular:graph` for dependency graph output.
- Add `modular:why {module}` to explain why a module is present, enabled, or required.
- Keep JSON output available for automation.

## Phase 4: Cache Freshness and Lock Metadata

- Track manifest hashes, provider lists, dependency hashes, and cached timestamps.
- Warn when module manifests changed after cache generation.
- Add stale-cache checks to `modular:doctor`.
- Keep runtime status files out of source control.

## Phase 5: Doctor Fixes

- Add safe `modular:doctor --fix` repairs.
- Fix missing module test directories, missing README placeholders, missing autoload-dev entries, stale cache, and missing asset links.
- Never rewrite business code automatically.

## Phase 6: Dependency Model

- Support richer dependency metadata while preserving the current simple array format.
- Add conflict and capability checks when the package can do so without breaking existing manifests.
- Keep dependency errors clear and actionable.

## Phase 7: Migration From Other Packages

- Add `modular:import-nwidart` as a guided migration path.
- Convert common module structure, manifests, providers, routes, config, and statuses.
- Include a dry-run mode before writing migration changes.

## Phase 8: Documentation and Positioning

- Add a respectful comparison page for Laravel Modular and established module packages.
- Document when to use this package, when not to, and how to migrate safely.
- Keep examples focused on native Laravel code inside module boundaries.
