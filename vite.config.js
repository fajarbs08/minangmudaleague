import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    define: {
        global: 'globalThis',
    },
    plugins: [
        laravel({
            input: [
                'resources/css/public-tailwind.css',
                'resources/js/public-brackets.js',
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/js/config.js',
                'resources/js/layout.js',
            ],
            refresh: true,
        }),
    ],
});
