# Resource Discovery

Laravel Modular automatically discovers and registers your module resources. No manual configuration required.

## Views

Views are automatically registered using the module's lowercase name as the namespace.

### Creating Views

```bash
php artisan make:view posts.index --module=Blog
```

This creates `modules/Blog/resources/views/posts/index.blade.php`.

### Using Views

```php
return view('blog::posts.index');
```

### Blade Components

Components are automatically registered:

```blade
<x-blog:card title="Hello World">
    Content here
</x-blog:card>
```

This resolves to `modules/Blog/resources/views/components/card.blade.php`.

## Translations

Translations work exactly like views:

### File Structure

```
modules/Blog/lang/
├── en/
│   └── messages.php
└── es/
    └── messages.php
```

### Usage

```php
__('blog::messages.welcome')
```

## Configuration

Module config files are automatically loaded:

### File Location

`modules/Blog/config/blog.php`

### Usage

```php
config('blog::settings.posts_per_page')
```

## Routes

Routes are automatically loaded from:

- `modules/Blog/routes/web.php` - Web routes
- `modules/Blog/routes/api.php` - API routes

### Route Naming

Prefix your route names with the module:

```php
Route::get('/posts', [PostController::class, 'index'])
    ->name('blog.posts.index');
```

### Route Groups

Routes are automatically grouped by module:

```php
// In modules/Blog/routes/web.php
Route::prefix('blog')->group(function () {
    Route::get('/posts', [PostController::class, 'index']);
});
```
