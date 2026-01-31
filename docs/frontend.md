# Assets & Frontend

This package supports two major workflows for handling frontend assets:
1.  **Vite Integration (HMR)**: Developing locally with Hot Module Replacement.
2.  **Symlinking**: Linking raw assets to `public/` for simple serving.

---

## 1. Vite Integration

Laravel Modular is designed to work seamlessly with Vite.

### Step 1: Root Configuration
To enable Vite to "see" your module assets, you need to tell it where to look.

Create a `vite.modular.js` (or similar) helper to glob your module inputs:

```javascript
// vite.modular.js
import { globSync } from 'glob';

export const modularLoader = {
    inputs() {
        // Scans all modules for JS/CSS entry points
        return globSync('modules/*/resources/assets/{css,js,ts}/*.{css,js,ts}');
    },
    refreshPaths() {
        // Tells Vite to reload when you edit a module's blade file
        return ['modules/*/resources/views/**/*.blade.php'];
    }
};
```

### Step 2: Update `vite.config.js`
Import the helper and add it to the Laravel plugin config.

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { modularLoader } from './vite.modular.js';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                ...modularLoader.inputs() // <--- Auto-discover module assets
            ],
            refresh: [
                ...modularLoader.refreshPaths() // <--- Auto-refresh on changes
            ],
        }),
    ],
});
```

### Step 3: Usage in Layouts
Use the global helper `modular_vite()` to inject the script tags. This wrapper automatically points to the correct build directory.

```blade
<head>
    <!-- Loads main app assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Loads module assets -->
    {{ modular_vite([
        'modules/Shop/resources/assets/css/app.css',
        'modules/Shop/resources/assets/js/app.js'
    ]) }}
</head>
```

---

## 2. NPM Workspaces (Dependencies)

Each module is treated as a separate package with its own `package.json`.

### Installing Dependencies
Do not run `npm install` inside the module directory manually. Instead, use the `modular:npm` command to ensure context is correct (especially if using workspaces).

```bash
# Install 'chart.js' into the 'Dashboard' module
php artisan modular:npm Dashboard install chart.js
```

### Running Scripts
You can run any script defined in a module's `package.json`.

```bash
# Run 'build' script for 'Dashboard'
php artisan modular:npm Dashboard run build
```

---

## 3. Serving Static Assets (`modular:link`)

If you have static images, fonts, or compiled CSS/JS that you simply want to serve from the `public/` directory without Vite build steps:

1.  Place files in `modules/{Module}/resources/assets`.
2.  Run the link command:

```bash
php artisan modular:link
```

This creates a symbolic link:
`public/modules/shop` -> `modules/Shop/resources/assets`

You can then reference them:
```blade
<img src="{{ module_asset('Shop', 'images/logo.png') }}" />
{{-- Resolves to: http://site.test/modules/shop/images/logo.png --}}
```
