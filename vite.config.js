import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss'; // Importa tailwindcss

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    // Añade esta sección para la configuración de PostCSS
    css: {
        postcss: {
            plugins: [
                tailwindcss('./tailwind.config.js'), // Ruta explícita al archivo de configuración
            ],
        },
    },
});