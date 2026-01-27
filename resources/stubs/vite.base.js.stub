import { defineConfig, mergeConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

/**
 * Helper to define a module-specific Vite configuration.
 */
export function defineModuleConfig(config) {
    return defineConfig((env) => {
        const baseConfig = {
            plugins: [
                laravel({
                    input: config.input || [],
                    refresh: true,
                }),
            ],
            // Add shared plugins here (Tailwind, AutoPrefixer, etc.)
        };

        return mergeConfig(baseConfig, config);
    });
}

export default defineModuleConfig;
