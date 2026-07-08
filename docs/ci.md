# Continuous Integration

Laravel Modular exposes JSON diagnostics and dependency checks that fit naturally into CI.

## Minimal Workflow

```yaml
name: modular-quality

on:
  pull_request:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none

      - run: composer install --no-interaction --prefer-dist --ansi

      - run: php artisan modular:doctor --json

      - run: php artisan modular:check

      - run: php artisan test
```

## Recommended Package Workflow

For package development, also run Pint and static analysis:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pest
```

## Application Workflow

For applications using modules:

```bash
php artisan modular:status --json
php artisan modular:doctor --json
php artisan modular:check
php artisan test
```

Use `modular:status --json` when your CI system wants to store or display module counts, enabled/disabled totals, cache freshness, and dependency issue counts.

## Module Matrix Testing

For large applications, test important modules separately:

```bash
php artisan modular:test Billing
php artisan modular:test Shop
php artisan modular:test Admin
```

This keeps failures closer to the owning module and makes large pull requests easier to review.
