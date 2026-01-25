# Creating Modules

Creating a module is as simple as running a single command. Laravel Modular handles all the boilerplate for you.

## Basic Module Creation

```bash
php artisan make:module Blog
```

This command:
1. Creates the complete directory structure
2. Generates a `BlogServiceProvider`
3. Creates `module.json` and `composer.json`
4. Registers routes, views, and translations automatically
5. Configures auto-discovery for commands and policies

## Module Configuration (`module.json`)

Each module contains a `module.json` file that defines its metadata.

```json
{
    "$schema": "../../vendor/alizharb/laravel-modular/resources/schemas/module.schema.json",
    "name": "Blog",
    "description": "The official blog module.",
    "version": "1.0.0",
    "authors": [
        {
            "name": "Ali Harb",
            "email": "harbzali@gmail.com"
            "role": "Developer"
        }
    ],
    "namespace": "Modules\\Blog\\",
    "namespace": "Modules\\Blog\\",
    "provider": "Modules\\Blog\\app\\Providers\\BlogServiceProvider",
    "middleware": {
        "web": ["Modules\\Blog\\Http\\Middleware\\TrackVisits"],
        "blog.admin": "Modules\\Blog\\Http\\Middleware\\AdminGuard"
    },
    "requires": []
}
```

### Metadata Features
- **$schema**: Provides IDE autocompletion and validation for your module configuration.
- **version**: Track module versions using semantic versioning.
- **authors**: Define one or multiple authors with roles.
- **requires**: Specify other modules that this module depends on.

> [!NOTE]
> The `active` status is now managed by the **Activator** system. Use `php artisan module:enable` and `disable` to manage it dynamically.

## Generating resources

Once you have a module, you can generate resources using the standard Laravel commands with the `--module` flag:

### Models

```bash
php artisan make:model Post --module=Blog -mcf
```

This creates:
- `modules/Blog/app/Models/Post.php`
- `modules/Blog/database/migrations/xxxx_create_posts_table.php`
- `modules/Blog/app/Http/Controllers/PostController.php`
- `modules/Blog/database/factories/PostFactory.php`

### Controllers

```bash
php artisan make:controller API/PostController --module=Blog --api
```

### Requests

```bash
php artisan make:request StorePostRequest --module=Blog
```

### Middleware

```bash
php artisan make:middleware CheckBlogAccess --module=Blog
```

## All Supported Commands

Laravel Modular overrides **29+ Artisan commands**. Here are the most commonly used:

- `make:model`
- `make:controller`
- `make:request`
- `make:middleware`
- `make:migration`
- `make:seeder`
- `make:factory`
- `make:policy`
- `make:rule`
- `make:event`
- `make:listener`
- `make:job`
- `make:mail`
- `make:notification`
- `make:observer`
- `make:resource`
- `make:test`
- `make:view`
- `make:component`
- `make:interface`
- `make:trait`
- `make:enum`
- `make:class`

All of these work exactly like their standard Laravel counterparts, just add `--module=YourModuleName`.
