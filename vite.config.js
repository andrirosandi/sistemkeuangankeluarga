import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Breeze auth pages (Tailwind)
                'resources/css/app.css',
                'resources/js/app.js',
                // Admin dashboard (Tabler)
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
});
