import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    // Lee el .env del proyecto. Por defecto el dev server escucha en
    // localhost, asi arranca en cualquier red. Para exponerlo a la LAN
    // (probar desde un celular, por ejemplo) poner en el .env:
    //   VITE_DEV_HOST=0.0.0.0
    const env = loadEnv(mode, process.cwd(), '');

    return {
        server: {
            host: env.VITE_DEV_HOST || 'localhost',
            port: 5173,
            cors: true,
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    };
});
