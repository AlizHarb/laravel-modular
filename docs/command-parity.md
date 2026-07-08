# Command Parity

Laravel Modular's main developer-experience goal is native Laravel command parity. Instead of learning a separate command vocabulary, add `--module=Name` to the Laravel generator command you already know.

## Daily Generators

| Command | Module Support | Notes |
| :------ | :------------- | :---- |
| `make:model` | Yes | Supports common native flags such as `-m`, `-c`, `-f`, `-s`, `--policy`, `--api`, and `--all`. |
| `make:controller` | Yes | Supports `--api`, `--resource`, `--model`, and nested names such as `Api/PostController`. |
| `make:request` | Yes | Creates requests under the module HTTP namespace. |
| `make:policy` | Yes | Supports module models. |
| `make:factory` | Yes | Creates factories under module database factories and uses module factory namespace. |
| `make:migration` | Yes | Creates migrations under the module migration path. |
| `make:seeder` | Yes | Creates seeders under the module database seeders path. |
| `make:test` | Yes | Creates tests under the module test path. |

## Additional Supported Generators

| Command | Module Support |
| :------ | :------------- |
| `make:cast` | Yes |
| `make:channel` | Yes |
| `make:class` | Yes |
| `make:command` | Yes |
| `make:component` | Yes |
| `make:enum` | Yes |
| `make:event` | Yes |
| `make:exception` | Yes |
| `make:interface` | Yes |
| `make:job` | Yes |
| `make:listener` | Yes |
| `make:mail` | Yes |
| `make:middleware` | Yes |
| `make:notification` | Yes |
| `make:observer` | Yes |
| `make:provider` | Yes |
| `make:resource` | Yes |
| `make:rule` | Yes |
| `make:scope` | Yes |
| `make:trait` | Yes |
| `make:view` | Yes |

## Examples

```bash
php artisan make:model Post --module=Blog -mcf
php artisan make:controller Api/PostController --module=Blog --api
php artisan make:request StorePostRequest --module=Blog
php artisan make:policy PostPolicy --module=Blog --model=Post
php artisan make:test PostFeatureTest --module=Blog
```

## Design Rule

If Laravel already has a generator command, prefer that command with `--module`. Laravel Modular should feel like Laravel with module-aware paths, not a separate framework inside Laravel.
