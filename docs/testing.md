# Testing Modules

Laravel Modular seamlessly integrates with **Pest** (recommended) and PHPUnit.

## Test Structure

Every module has its own `tests/` folder.

```text
modules/Shop/
└── tests/
    ├── Feature/
    ├── Unit/
    ├── Pest.php      <-- Module-specific test configuration
    └── TestCase.php  <-- Base test case
```

## Running Tests

### 1. Running All Tests (Global)
To run tests for **all** modules, use the `modular:test` command without any arguments:

```bash
php artisan modular:test
```

This will sequentially run the test suite for every module in its own isolated process, ensuring that each module's configuration (e.g., specific `phpunit.xml` settings) is respected.

> [!WARNING]
> Running `php artisan test` or `vendor/bin/pest` from the root might cause failures if your modules require specific environment settings or isolated database states that conflict with the root configuration. Using `php artisan modular:test` is recommended for reliability.

### 2. Running Specific Module Tests
Use the Artisan command to isolate tests for one module.

```bash
# Run tests ONLY for Shop
php artisan modular:test Shop
```

This command:
1.  Points PHPUnit to `modules/Shop/phpunit.xml`.
2.  Ensures strictly that only this module's tests run.

---

## Writing Tests

Your module's `TestCase.php` usually extends `Tests\TestCase` (the application's base test case).

**Example Application Test:**
```php
// packages/modular/Shop/tests/Feature/CartTest.php

use Modules\Shop\Models\Product;

it('can add items to cart', function () {
    $product = Product::factory()->create();
    
    $response = this->post('/cart', [
        'product_id' => $product->id
    ]);
    
    $response->assertRedirect('/cart');
});
```

### Mocking Other Modules
Since modules are isolated, how do you test interactions?

**Scenario:** Shop module needs to notify the User module.
**Approach:** The Shop module should depend on an Interface, not the User class. You can then mock that interface.

```php
// In ShopServiceProvider
$this->app->bind(UserRepositoryInterface::class, RealUserRepository::class);

// In Tests
$this->mock(UserRepositoryInterface::class, function ($mock) {
    $mock->shouldReceive('find')->andReturn(...);
});
```
