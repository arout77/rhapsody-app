/**
 * Rhapsody React Bridge — resources/js/app.jsx
 *
 * Handles two rendering modes transparently:
 *   Full SPA mode   — controller returns $this->react('Page', $props)
 *   Islands mode    — {{ react_component('Widget', {props}) }} in Twig
 *
 * Both modes read props from a <script type="application/json"> block.
 *
 * The bridge also exposes a global mount function so that dynamic islands
 * (e.g. inside the debug toolbar) can be mounted after they are inserted.
 */
import React from 'react';
import { createRoot } from 'react-dom/client';

// ---------------------------------------------------------------------------
// Component registry — Vite glob-imports every .jsx file under components/
// ---------------------------------------------------------------------------
const modules = import.meta.glob('./components/**/*.jsx', { eager: true });

/**
 * Locate and return the default export for a given component name.
 *
 * @param {string} name
 * @returns {React.ComponentType|null}
 */
function resolveComponent(name) {
    const normalised = name.replace(/\\/g, '/');

    const key =
        Object.keys(modules).find(
            (k) =>
                k === `./components/${normalised}.jsx` ||
                k === `./components/${normalised}/index.jsx`
        ) ?? null;

    if (!key) {
        console.error(
            `[Rhapsody] Component "${name}" not found.\n` +
            `Expected: resources/js/components/${normalised}.jsx\n` +
            `Run:      php rhapsody make:react ${name}`
        );
        return null;
    }

    return modules[key].default ?? null;
}

/**
 * Parse JSON props from a <script type="application/json"> block.
 *
 * @param {Element} scopeEl   The element to search within.
 * @param {string}  selector  CSS selector for the props block.
 * @returns {object}
 */
function parseProps(scopeEl, selector) {
    const el = scopeEl.querySelector(selector);
    if (!el) return {};
    try {
        return JSON.parse(el.textContent);
    } catch (err) {
        console.error('[Rhapsody] Failed to parse component props:', err);
        return {};
    }
}

/**
 * Mount all React islands on the page.
 * Also mounts the full-page SPA if #rhapsody-root exists.
 * Exposed globally so dynamic islands (e.g. toolbar panels) can call it.
 */
function mountAll() {
    console.log('[Rhapsody] Mounting React components...');

    // ─── 1. Full SPA mode ──────────────────────────────────────────
    const spaRoot = document.getElementById('rhapsody-root');

    if (spaRoot) {
        const componentName = spaRoot.dataset.component ?? null;

        if (!componentName) {
            console.error('[Rhapsody] #rhapsody-root found but data-component is missing.');
        } else {
            // Check if already mounted (avoid duplicate roots)
            if (spaRoot._reactRoot) {
                console.log('[Rhapsody] SPA already mounted, skipping.');
            } else {
                const props = parseProps(document, '#rhapsody-props');
                const Component = resolveComponent(componentName);

                if (Component) {
                    const root = createRoot(spaRoot);
                    spaRoot._reactRoot = root;
                    root.render(
                        <React.StrictMode>
                            <Component {...props} />
                        </React.StrictMode>
                    );
                    console.log('[Rhapsody] SPA mounted:', componentName);
                }
            }
        }
    }

    // ─── 2. Islands mode ────────────────────────────────────────────
    const islands = document.querySelectorAll('.rhapsody-island');

    islands.forEach((island) => {
        // Skip if already mounted
        if (island._reactRoot) {
            return;
        }

        const componentName = island.dataset.component ?? null;

        if (!componentName) {
            console.warn('[Rhapsody] .rhapsody-island element found but data-component is missing.');
            return;
        }

        const props = parseProps(island, '.rhapsody-island-props');
        const Component = resolveComponent(componentName);

        if (Component) {
            const root = createRoot(island);
            island._reactRoot = root;
            root.render(
                <React.StrictMode>
                    <Component {...props} />
                </React.StrictMode>
            );
            console.log('[Rhapsody] Island mounted:', componentName);
        }
    });
}

// ─── Expose globally for dynamic island mounting ─────────────────────
if (typeof window !== 'undefined') {
    if (!window.Rhapsody) {
        window.Rhapsody = {};
    }
    window.Rhapsody.mountAll = mountAll;
    window.Rhapsody.mountIslands = function() {
        // Only mount islands (for toolbar panel reuse)
        const islands = document.querySelectorAll('.rhapsody-island');
        islands.forEach((island) => {
            if (island._reactRoot) return;

            const componentName = island.dataset.component ?? null;
            if (!componentName) return;

            const props = parseProps(island, '.rhapsody-island-props');
            const Component = resolveComponent(componentName);
            if (Component) {
                const root = createRoot(island);
                island._reactRoot = root;
                root.render(
                    <React.StrictMode>
                        <Component {...props} />
                    </React.StrictMode>
                );
                console.log('[Rhapsody] Island mounted dynamically:', componentName);
            }
        });
    };
}

// ─── Auto-mount on DOM ready ─────────────────────────────────────────
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountAll);
} else {
    // DOM already loaded
    mountAll();
}