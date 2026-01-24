# Testing

Laravel Modular fully supports testing with PHPUnit and Pest.

## Creating Tests

### Feature Tests

```bash
php artisan make:test PostTest --module=Blog
```

Creates: `modules/Blog/tests/Feature/PostTest.php`

### Unit Tests

```bash
php artisan make:test PostServiceTest --module=Blog --unit
```

Creates: `modules/Blog/tests/Unit/PostServiceTest.php`

## Running Tests

### All Tests

```bash
php artisan test
```

### Module-Specific Tests

```bash
php artisan test modules/Blog/tests
```

### Specific Test File

```bash
php artisan test modules/Blog/tests/Feature/PostTest.php
```

## Pest Example

```php
<?php

use Modules\Blog\Models\Post;

it('can create a post', function () {
    $post = Post::factory()->create([
        'title' => 'Test Post',
    ]);

    expect($post->title)->toBe('Test Post');
});

it('can list posts', function () {
    Post::factory()->count(5)->create();

    $response = $this->get(route('blog.posts.index'));

    $response->assertStatus(200);
    $response->assertViewHas('posts');
});
```

## PHPUnit Example

```php
<?php

namespace Modules\Blog\Tests\Feature;

use Tests\TestCase;
use Modules\Blog\Models\Post;

class PostTest extends TestCase
{
    public function test_can_create_post()
    {
        $post = Post::factory()->create([
            'title' => 'Test Post',
        ]);

        $this->assertEquals('Test Post', $post->title);
    }
}
```

## Testing Best Practices

- Use factories for model creation
- Test both happy paths and edge cases
- Keep tests focused and isolated
- Use database transactions for cleanup
- Mock external dependencies

## Database Testing

### Using RefreshDatabase

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_example()
    {
        // Database is automatically reset
    }
}
```

### Module Seeders in Tests

```php
public function test_with_seeded_data()
{
    $this->artisan('modular:seed Blog');
    
    $this->assertDatabaseCount('posts', 10);
}
```

## Mocking

```php
use Modules\Blog\Services\PostService;

it('can mock post service', function () {
    $mock = Mockery::mock(PostService::class);
    $mock->shouldReceive('create')
         ->once()
         ->andReturn(new Post());

    $this->app->instance(PostService::class, $mock);
});
```
