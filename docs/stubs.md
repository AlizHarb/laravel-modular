# Custom Stubs

Customize the generated code to match your team's coding standards.

## Publishing Stubs

```bash
php artisan vendor:publish --tag="modular-stubs"
```

This creates stub files in `stubs/modular/`:

```
stubs/modular/
├── composer.json.stub
├── module.json.stub
├── service-provider.stub
└── routes/
    ├── api.stub
    └── web.stub
```

## Enabling Custom Stubs

Update `config/modular.php`:

```php
'stubs' => [
    'enabled' => true,
    'path' => base_path('stubs/modular'),
],
```

## Stub Variables

Stubs support the following placeholders:

- `{{name}}` - Module name (e.g., "Blog")
- `{{lowerName}}` - Lowercase module name (e.g., "blog")
- `{{vendor}}` - Composer vendor name
- `{{authorName}}` - Author name
- `{{authorEmail}}` - Author email
- `{{type}}` - Package type
- `{{license}}` - License type

## Example: Custom Service Provider

```php
<?php

namespace Modules\{{name}}\Providers;

use Illuminate\Support\ServiceProvider;

class {{name}}ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Custom registration logic
        $this->app->singleton('{{lowerName}}', function () {
            return new \Modules\{{name}}\{{name}}Service();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', '{{lowerName}}');
    }
}
```

## Example: Custom composer.json

```json
{
    "name": "{{vendor}}/{{lowerName}}",
    "description": "{{name}} Module",
    "type": "{{type}}",
    "license": "{{license}}",
    "authors": [
        {
            "name": "{{authorName}}",
            "email": "{{authorEmail}}"
        }
    ],
    "require": {
        "php": "^8.2"
    },
    "autoload": {
        "psr-4": {
            "Modules\\{{name}}\\": "app/"
        }
    }
}
```

## Best Practices

- Keep stubs minimal and focused
- Use placeholders for all variable content
- Test stub changes by creating a new module
- Version control your custom stubs
- Document any custom placeholders you add
