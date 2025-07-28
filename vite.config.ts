import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { loadEnv } from 'vite';


export default ({ mode }) => {
    const env = loadEnv(mode, process.cwd());    
    
    return ({
    base: env.VITE_APP_ENV == 'production' ? '/public/build/' : '',
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    });
};
