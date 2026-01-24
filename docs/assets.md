# Assets & Vite

Laravel Modular provides seamless integration with Vite for module assets.

## Asset Structure

Place your assets in:

```
modules/Blog/resources/assets/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── images/
    └── logo.png
```

## Linking Assets

Create symbolic links to make assets publicly accessible:

```bash
php artisan modular:link
```

This creates `public/modules/blog` → `modules/Blog/resources/assets`.

## Using Assets in Views

### The `module_asset()` Helper

```blade
<link rel="stylesheet" href="{{ module_asset('Blog', 'css/app.css') }}">
<script src="{{ module_asset('Blog', 'js/app.js') }}"></script>
<img src="{{ module_asset('Blog', 'images/logo.png') }}" alt="Logo">
```

## Vite Integration

### Global Vite Configuration

Add module paths to your `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'modules/**/resources/assets/js/app.js',
                'modules/**/resources/assets/css/app.css',
            ],
            refresh: [
                'modules/**/resources/views/**/*.blade.php',
            ],
        }),
    ],
});
```

### Using Vite in Module Views

```blade
@vite([
    'modules/Blog/resources/assets/css/app.css',
    'modules/Blog/resources/assets/js/app.js'
])
```

## Hot Module Replacement (HMR)

HMR works automatically when you run:

```bash
npm run dev
```

Changes to module views and assets will trigger instant browser updates.
