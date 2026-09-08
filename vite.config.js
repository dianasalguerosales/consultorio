import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    // El host del HMR se deriva de la URL de la app. VITE_APP_URL es opcional:
    // si no está definida se usa APP_URL, y si ninguna parsea se cae a localhost.
    const hmrHost = (() => {
        try {
            return new URL(env.VITE_APP_URL || env.APP_URL).hostname;
        } catch {
            return 'localhost';
        }
    })();

    return {
        server: {
            host: env.VITE_DEV_HOST || 'localhost',
            port: 5173,
            cors: true,
            hmr: {
                host: hmrHost,
            },
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
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
