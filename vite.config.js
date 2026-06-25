import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

/**
 * Rhapsody + Vite configuration
 *
 * Dev server:  npm run dev      (set VITE_DEV_SERVER=true in .env)
 * Production:  npm run build    (set VITE_DEV_SERVER=false in .env)
 *
 * Vite writes the production manifest to:
 *   public/build/.vite/manifest.json  (Vite 5)
 *   public/build/manifest.json        (Vite 4 fallback, also read by Rhapsody)
 */
export default defineConfig({
    plugins: [
        react(),
    ],

    // ---------------------------------------------------------------------------
    // Build output
    // ---------------------------------------------------------------------------
    build: {
        // Rhapsody expects assets under public/build/
        outDir: 'public/build',
        emptyOutDir: true,

        // Required — BaseController::react() reads this to resolve asset URLs.
        manifest: true,

        rollupOptions: {
            input: {
                // Add additional entry points here if you need separate bundles.
                app: resolve(__dirname, 'resources/js/app.jsx'),
            },
        },
    },

    // ---------------------------------------------------------------------------
    // Development server
    // ---------------------------------------------------------------------------
    server: {
        // Must match VITE_PORT in .env (default 5173)
        port: parseInt(process.env.VITE_PORT ?? '5173', 10),

        // Allow the PHP server to load assets cross-origin.
        cors: true,

        // The origin Vite dev server tags into asset URLs.
        // Must be accessible from the browser.
        origin: `http://localhost:${parseInt(process.env.VITE_PORT ?? '5173', 10)}`,
    },
});
