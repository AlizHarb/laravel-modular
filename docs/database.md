# Database & Migrations

Laravel Modular provides dedicated commands for managing module databases.

## Creating Migrations

```bash
php artisan make:migration create_posts_table --module=Blog
```

This creates a migration in `modules/Blog/database/migrations/`.

## Running Migrations

### Migrate All Modules

```bash
php artisan modular:migrate
```

### Migrate Specific Module

```bash
php artisan modular:migrate Blog
```

### Fresh Migration

```bash
php artisan modular:migrate Blog --fresh
```

### With Seeding

```bash
php artisan modular:migrate Blog --fresh --seed
```

## Seeders

### Creating Seeders

```bash
php artisan make:seeder PostSeeder --module=Blog
```

### Running Seeders

```bash
# Seed all modules
php artisan modular:seed

# Seed specific module
php artisan modular:seed Blog
```

## Factories

### Creating Factories

```bash
php artisan make:factory PostFactory --module=Blog
```

### Using Factories

```php
use Modules\Blog\Models\Post;

Post::factory()->count(10)->create();
```

## Models

### Creating Models

```bash
php artisan make:model Post --module=Blog -mcf
```

This creates:
- Model
- Migration
- Controller
- Factory

### Model Location

Models are placed in `modules/Blog/app/Models/Post.php`.

### Relationships

```php
namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Comments\Models\Comment;

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

## Best Practices

- Keep migrations focused on a single module's tables
- Use factories for testing
- Leverage Eloquent relationships across modules
- Run `modular:migrate --fresh` in development for clean resets
