// Cadence theme — mobile nav toggle.
// The navbar is global (included from layouts/main.twig on every page),
// so this script loads site-wide rather than being page-specific.
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('mobile-menu-button');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.getElementById('mobile-menu-icon-open');
    const iconClose = document.getElementById('mobile-menu-icon-close');

    if (!button || !menu) return;

    button.addEventListener('click', () => {
        const isOpen = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        button.setAttribute('aria-expanded', String(!isOpen));
        if (iconOpen && iconClose) {
            iconOpen.classList.toggle('hidden', !isOpen);
            iconClose.classList.toggle('hidden', isOpen);
        }
    });
});

// ---------- Light/dark theme toggle ----------
// The initial .light class is already applied (or not) by the inline
// anti-flash script in layouts/main.twig's <head>, before this file
// even loads — this only handles syncing icon state on load and
// responding to clicks on the toggle button(s).
(function () {
    const STORAGE_KEY = 'rhapsody-theme';
    const root = document.documentElement;

    function currentTheme() {
        return root.classList.contains('light') ? 'light' : 'dark';
    }

    function applyIcons(theme) {
        document.querySelectorAll('.theme-icon-sun').forEach((el) => el.classList.toggle('hidden', theme === 'light'));
        document.querySelectorAll('.theme-icon-moon').forEach((el) => el.classList.toggle('hidden', theme !== 'light'));
        document.querySelectorAll('.theme-toggle-label').forEach((el) => {
            el.textContent = theme === 'light' ? 'Switch to dark mode' : 'Switch to light mode';
        });
    }

    applyIcons(currentTheme());

    document.querySelectorAll('.theme-toggle-button').forEach((button) => {
        button.addEventListener('click', () => {
            const next = currentTheme() === 'light' ? 'dark' : 'light';
            root.classList.toggle('light', next === 'light');
            localStorage.setItem(STORAGE_KEY, next);
            applyIcons(next);
        });
    });
})();
