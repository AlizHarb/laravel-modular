# TALL Stack Integration

Laravel Modular works seamlessly with the TALL stack (Tailwind, Alpine.js, Livewire, Laravel).

## Livewire

### Installation

Install the Livewire bridge package:

```bash
composer require alizharb/laravel-modular-livewire
```

### Creating Livewire Components

```bash
php artisan make:livewire PostList --module=Blog
```

This creates:
- `modules/Blog/app/Livewire/PostList.php`
- `modules/Blog/resources/views/livewire/post-list.blade.php`

### Using Components

```blade
<livewire:blog.post-list />
```

### Component Namespacing

Components are automatically namespaced by module:

```php
namespace Modules\Blog\Livewire;

use Livewire\Component;

class PostList extends Component
{
    public function render()
    {
        return view('blog::livewire.post-list');
    }
}
```

## Filament

### Installation

Install the Filament bridge package:

```bash
composer require alizharb/laravel-modular-filament
```

### Creating resources

```bash
php artisan make:filament-resource Post --module=Blog
```

### Panel Registration

resources are automatically discovered and registered in your Filament panels.

## Alpine.js

Alpine.js works out of the box. Include it in your module views:

```blade
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>
```

## Tailwind CSS

### Module-Specific Styles

Add module paths to your `tailwind.config.js`:

```javascript
export default {
  content: [
    './resources/**/*.blade.php',
    './modules/**/resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### Module CSS Files

Create module-specific Tailwind files:

```css
/* modules/Blog/resources/assets/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

.blog-card {
    @apply rounded-lg shadow-md p-4;
}
```

## Full TALL Example

```blade
{{-- modules/Blog/resources/views/livewire/post-list.blade.php --}}
<div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($posts as $post)
            <div 
                x-data="{ expanded: false }"
                class="bg-white rounded-lg shadow-md p-6"
            >
                <h3 class="text-xl font-bold mb-2">{{ $post->title }}</h3>
                <p x-show="!expanded" class="text-gray-600">
                    {{ Str::limit($post->excerpt, 100) }}
                </p>
                <button 
                    @click="expanded = !expanded"
                    class="text-blue-500 hover:text-blue-700"
                >
                    <span x-text="expanded ? 'Show Less' : 'Read More'"></span>
                </button>
            </div>
        @endforeach
    </div>
</div>
```
