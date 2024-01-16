import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

import vue from '@vitejs/plugin-vue';

export default defineConfig({
    optimizeDeps: {
        include: ['@syncfusion/ej2-base', '@syncfusion/ej2-vue-navigations', '@syncfusion/ej2-inputs', '@syncfusion/ej2-buttons']
      },
    server: {
        host: '192.168.1.128',
      },
    plugins: [
        laravel(['resources/js/app.js',
    ]),
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