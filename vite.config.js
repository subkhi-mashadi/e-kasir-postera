import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'script',
            base: '/',
            scope: '/',
            manifest: {
                name: 'E-Kasir POS',
                short_name: 'E-Kasir',
                description: 'Sistem Kasir F&B',
                theme_color: '#92400e',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'landscape',
                start_url: '/pos',
                scope: '/',
                icons: [
                    {
                        src: '/icons/icon-192.png',
                        sizes: '192x192',
                        type: 'image/png',
                        purpose: 'any',
                    },
                    {
                        src: '/icons/icon-512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,svg,png,woff2}'],
                navigateFallback: null,
                runtimeCaching: [
                    {
                        urlPattern: /\/pos\/products/,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pos-products',
                            expiration: { maxAgeSeconds: 60 * 60 * 24 },
                            networkTimeoutSeconds: 5,
                        },
                    },
                    {
                        urlPattern: /\/fonts\//,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'fonts',
                            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 365 },
                        },
                    },
                ],
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
