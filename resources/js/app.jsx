/**
 * Rhapsody React Bridge — resources/js/app.jsx
 *
 * Handles two rendering modes transparently:
 *
 *   Full SPA mode   — controller returns $this->react('Page', $props)
 *                     Looks for: <div id="rhapsody-root" data-component="...">
 *
 *   Islands mode    — controller returns $this->view('page.twig') with
 *                     {{ react_component('Widget', {props}) }} placed inside the template.
 *                     Looks for: <div class="rhapsody-island" data-component="...">
 *
 * Both modes read props from a sibling <script type="application/json"> block
 * for XSS safety.  Both modes can coexist on the same page if needed (e.g. an
 * island embedded inside a full-SPA shell).
 *
 * You should rarely need to edit this file.
 * Generate new components with: php rhapsody make:react ComponentName
 */
import React from 'react';
import { createRoot } from 'react-dom/client';

// ---------------------------------------------------------------------------
// Component registry — Vite glob-imports every .jsx file under components/
// so new files are picked up automatically without touching this file.
// ---------------------------------------------------------------------------
const modules = import.meta.glob('./components/**/*.jsx', { eager: true });

/**
 * Locate and return the default export for a given component name.
 * Supports flat names ("Dashboard") and nested paths ("Users/ProfileCard").
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
 * Parse the JSON props stored in a non-executable sibling block.
 *
 * Full SPA:   <script type="application/json" id="rhapsody-props">...</script>
 * Islands:    <script type="application/json" class="rhapsody-island-props">...</script>
 *
 * @param {Element} scopeEl   The element to search within (document for SPA, island div for islands).
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

// ─── Full SPA mode ──────────────────────────────────────────────────────────
// Mounts a single component into #rhapsody-root.
// Props come from #rhapsody-props (injected by BaseController::react()).
// ─────────────────────────────────────────────────────────────────────────────
const spaRoot = document.getElementById('rhapsody-root');

if (spaRoot) {
    const componentName = spaRoot.dataset.component ?? null;

    if (!componentName) {
        console.error('[Rhapsody] #rhapsody-root found but data-component is missing.');
    } else {
        const props     = parseProps(document, '#rhapsody-props');
        const Component = resolveComponent(componentName);

        if (Component) {
            createRoot(spaRoot).render(
                <React.StrictMode>
                    <Component {...props} />
                </React.StrictMode>
            );
        }
    }
}

// ─── Islands mode ───────────────────────────────────────────────────────────
// Mounts one React root per .rhapsody-island element found on the page.
// Each island is independent — they do not share state unless you wire them
// up explicitly (e.g. via a shared Zustand/Jotai store or Context).
// Props come from the .rhapsody-island-props block nested inside each island.
// ─────────────────────────────────────────────────────────────────────────────
const islands = document.querySelectorAll('.rhapsody-island');

islands.forEach((island) => {
    const componentName = island.dataset.component ?? null;

    if (!componentName) {
        console.warn('[Rhapsody] .rhapsody-island element found but data-component is missing.');
        return;
    }

    const props     = parseProps(island, '.rhapsody-island-props');
    const Component = resolveComponent(componentName);

    if (Component) {
        createRoot(island).render(
            <React.StrictMode>
                <Component {...props} />
            </React.StrictMode>
        );
    }
});
