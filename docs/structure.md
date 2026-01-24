# Directory Structure

When you create a module using `php artisan make:module Blog`, Laravel Modular generates a complete, organized directory structure:

```
modules/
└── Blog/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Middleware/
    │   │   └── Requests/
    │   ├── Models/
    │   ├── Providers/
    │   │   └── BlogServiceProvider.php
    │   └── Services/
    ├── config/
    │   └── blog.php
    ├── database/
    │   ├── factories/
    │   ├── migrations/
    │   └── seeders/
    ├── resources/
    │   ├── assets/
    │   │   ├── css/
    │   │   └── js/
    │   ├── lang/
    │   └── views/
    ├── routes/
    │   ├── api.php
    │   └── web.php
    ├── tests/
    │   ├── Feature/
    │   └── Unit/
    ├── composer.json
    └── module.json
```

## Key Files

### `module.json`
This file contains metadata about your module:

```json
{
  "name": "Blog",
  "alias": "blog",
  "description": "Blog Module",
  "keywords": [],
  "priority": 0,
  "providers": [
    "Modules\\Blog\\Providers\\BlogServiceProvider"
  ],
  "files": []
}
```

### `composer.json`
Each module has its own `composer.json`, allowing isolated dependency management:

```json
{
  "name": "modules/blog",
  "description": "Blog Module",
  "type": "library",
  "require": {
    "php": "^8.2"
  }
}
```

## Customization

You can customize the default structure by publishing and modifying the stubs:

```bash
php artisan vendor:publish --tag="modular-stubs"
```
