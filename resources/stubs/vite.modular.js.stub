import fs from 'fs';
import path from 'path';

/**
 * Automatically discover assets and refresh paths for modules.
 */
export const modularLoader = {
    /**
     * Get all entry point assets from modules.
     */
    inputs(directories = ['modules']) {
        const inputs = [];

        directories.forEach(baseDir => {
            const absoluteBase = path.resolve(process.cwd(), baseDir);
            
            if (!fs.existsSync(absoluteBase)) {
                return;
            }

            const items = fs.readdirSync(absoluteBase);

            items.forEach(item => {
                const itemPath = path.join(absoluteBase, item);
                if (!fs.statSync(itemPath).isDirectory()) {
                    return;
                }

                // New logic: Check for module-specific vite.config.js
                const moduleViteConfig = path.join(itemPath, 'vite.config.js');
                if (fs.existsSync(moduleViteConfig)) {
                    try {
                        // We use a simple regex to extract inputs from the stubbed config
                        // as we can't easily 'import' ESM in a sync context here reliably
                        const configContent = fs.readFileSync(moduleViteConfig, 'utf8');
                        const inputMatch = configContent.match(/inputs:\s*\[([\s\S]*?)\]/);
                        
                        if (inputMatch && inputMatch[1]) {
                            const foundInputs = inputMatch[1]
                                .split(',')
                                .map(line => line.trim().replace(/['"]/g, ''))
                                .filter(line => line.length > 0);

                            foundInputs.forEach(input => {
                                inputs.push(path.relative(process.cwd(), path.join(itemPath, input)));
                            });
                            
                            return; // Found explicit config, skip fallback
                        }
                    } catch (e) {
                        // Fallback to manual discovery on error
                    }
                }

                // Fallback: Check for resources/assets or resources/js|css
                const searchPaths = [
                    path.join(itemPath, 'resources/assets'),
                    path.join(itemPath, 'resources/js'),
                    path.join(itemPath, 'resources/css'),
                ];

                searchPaths.forEach(searchPath => {
                    if (fs.existsSync(searchPath)) {
                        const files = fs.readdirSync(searchPath);
                        files.forEach(file => {
                            if (file.match(/\.(js|ts|css|scss)$/) && !file.startsWith('_')) {
                                inputs.push(path.relative(process.cwd(), path.join(searchPath, file)));
                            }
                        });
                    }
                });
            });
        });

        return inputs;
    },

    /**
     * Get all paths that should trigger a full page reload when changed.
     */
    refreshPaths(directories = ['modules']) {
        const paths = [];

        directories.forEach(baseDir => {
            if (fs.existsSync(path.resolve(process.cwd(), baseDir))) {
                paths.push(`${baseDir}/**/resources/views/**/*.blade.php`);
                paths.push(`${baseDir}/**/app/Livewire/**/*.php`);
                paths.push(`${baseDir}/**/routes/**/*.php`);
            }
        });

        return paths;
    }
};

export default modularLoader;
