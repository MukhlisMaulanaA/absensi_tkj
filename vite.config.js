import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    server: {
        host: "127.0.0.1", // Memungkinkan akses dari IP jaringan
        hmr: {
            host: "127.0.0.1", // Contoh: 192.168.1.10
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
});
