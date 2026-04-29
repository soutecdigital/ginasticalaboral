import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import obfuscator from 'vite-plugin-javascript-obfuscator'; // Importa o ofuscador

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        // Ativa a ofuscação apenas quando você rodar "npm run build"
        obfuscator({
            options: {
                compact: true,
                controlFlowFlattening: true,
                deadCodeInjection: true,
                debugProtection: true,
                disableConsoleOutput: true,
                numbersToExpressions: true,
                simplify: true,
                stringArray: true,
                stringArrayThreshold: 0.75,
            },
        }),
    ],
});