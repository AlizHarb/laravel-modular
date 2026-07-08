# Security Policy

## Supported Versions

Security fixes are provided for the actively maintained major version of Laravel Modular.

| Package Version | Supported |
| :-------------- | :-------- |
| `^1.2` | Yes |
| `<1.2` | Best effort |

Laravel Modular targets currently supported Laravel and PHP combinations declared in `composer.json`.

## Reporting a Vulnerability

Please do not open public issues for security vulnerabilities.

Report security concerns by emailing:

```text
harbzali@gmail.com
```

Please include:

- affected package version
- Laravel and PHP versions
- a clear description of the issue
- reproduction steps or proof of concept when possible
- impact assessment if known

## Disclosure Process

After receiving a report, maintainers will review the issue, confirm impact, prepare a fix, and coordinate release timing. Public disclosure should happen after a patched version is available.

## Scope

Security-sensitive areas include:

- module import/export
- filesystem path handling
- module manifest parsing
- dynamic provider, route, event, policy, and command discovery
- cache/status files
- generated Composer and Vite configuration

## Hardening Recommendations

- Review imported modules before enabling them.
- Run `php artisan modular:doctor` after changing module manifests.
- Run `php artisan modular:check` before deployment.
- Do not commit runtime status files from `bootstrap/cache`.
- Use `modular:import-nwidart --dry-run` before migration imports.
