import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/postcss'; // ✅ FIXED
import autoprefixer from 'autoprefixer';
import { viteStaticCopy } from 'vite-plugin-static-copy';

export default defineConfig({
    build: {
        manifest: true,
        rtl: true,
        outDir: 'public/build/',
        cssCodeSplit: true,
        rollupOptions: {
            output: {
                assetFileNames: (css) => {
                    if (css.name.split('.').pop() === 'css') {
                        return 'css/[name].css';
                    } else {
                        return 'icons/' + css.name;
                    }
                },
                entryFileNames: 'js/[name].js',
            },
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/scss/landing.scss',
                'resources/scss/style-preset.scss',
                'resources/scss/style.scss',
                'resources/scss/uikit.scss',
                'resources/css/app.css', // Tailwind file
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [
                { src: 'resources/plugins', dest: 'css' },
                { src: 'resources/fonts', dest: '' },
                { src: 'resources/images', dest: '' },
                { src: 'resources/js', dest: '' },
                { src: 'resources/json', dest: '' },
            ],
        }),
    ],
    css: {
        postcss: {
            plugins: [
                tailwindcss, // ✅ Now valid
                autoprefixer,
            ],
        },
    },
});
