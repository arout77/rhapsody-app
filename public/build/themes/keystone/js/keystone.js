/*! Keystone for Rhapsody — vanilla JS, no dependencies, no eval, no inline handlers. */
(function () {
  'use strict';

  var KEY = 'rhapsody-theme'; // shared with other Rhapsody themes; values: light | dark | (absent = system)
  var root = document.documentElement;
  var mq = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;
  var ORDER = ['system', 'light', 'dark'];
  var LABEL = { system: 'System', light: 'Light', dark: 'Dark' };

  function read() {
    try {
      var v = localStorage.getItem(KEY);
      return v === 'light' || v === 'dark' ? v : 'system';
    } catch (e) { return 'system'; }
  }
  function write(pref) {
    try {
      if (pref === 'system') localStorage.removeItem(KEY);
      else localStorage.setItem(KEY, pref);
    } catch (e) { /* storage blocked: the choice simply won't persist */ }
  }

  function apply(pref) {
    var dark = pref === 'dark' || (pref === 'system' && mq && mq.matches);
    root.setAttribute('data-theme-pref', pref);
    if (pref === 'system') root.removeAttribute('data-theme');
    else root.setAttribute('data-theme', dark ? 'dark' : 'light');
    var meta = document.querySelector('meta[name="color-scheme"]');
    if (meta) meta.setAttribute('content', pref === 'system' ? 'light dark' : pref);
    var tm = document.querySelector('meta[name="theme-color"]');
    if (tm) tm.setAttribute('content', dark ? '#0a101b' : '#f6f8fb');
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      var next = ORDER[(ORDER.indexOf(pref) + 1) % ORDER.length];
      btn.setAttribute('aria-label', 'Colour theme: ' + LABEL[pref] + '. Switch to ' + LABEL[next]);
      btn.setAttribute('title', 'Theme: ' + LABEL[pref]);
      var out = btn.querySelector('[data-theme-live]');
      if (out) out.textContent = 'Theme: ' + LABEL[pref];
    });
  }

  /* Theme toggle: system -> light -> dark -> system */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-theme-toggle]');
    if (!btn) return;
    var cur = read();
    var next = ORDER[(ORDER.indexOf(cur) + 1) % ORDER.length];
    write(next);
    apply(next);
  });
  if (mq) {
    var onChange = function () { if (read() === 'system') apply('system'); };
    if (mq.addEventListener) mq.addEventListener('change', onChange);
    else if (mq.addListener) mq.addListener(onChange);
  }
  apply(read());

  /* Mobile menu (<details>): close on Escape, outside click, or link tap */
  var menu = document.querySelector('details.ks-menu');
  if (menu) {
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && menu.open) { menu.open = false; var s = menu.querySelector('summary'); if (s) s.focus(); }
    });
    document.addEventListener('click', function (e) {
      if (menu.open && !menu.contains(e.target)) menu.open = false;
      else if (e.target.closest && e.target.closest('details.ks-menu a')) menu.open = false;
    });
  }

  /* Mark the current page in the navigation */
  var path = location.pathname.replace(/\/+$/, '') || '/';
  document.querySelectorAll('[data-nav]').forEach(function (a) {
    var p;
    try { p = new URL(a.href, location.href).pathname.replace(/\/+$/, '') || '/'; } catch (err) { return; }
    if (p === path) a.setAttribute('aria-current', 'page');
  });

  /* Show / hide password */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest && e.target.closest('[data-reveal]');
    if (!btn) return;
    var input = document.getElementById(btn.getAttribute('data-reveal'));
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    btn.classList.toggle('opacity-100', show);
  });

  /* Prevent double submits once the browser says the form is valid */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.hasAttribute('data-no-guard') || e.defaultPrevented) return;
    var btn = form.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.setAttribute('aria-busy', 'true');
    // Disable on the next tick so the button's own name/value still posts.
    setTimeout(function () { btn.disabled = true; }, 0);
  });
  // Back/forward cache: re-enable buttons when the page is restored.
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    document.querySelectorAll('button[aria-busy="true"]').forEach(function (b) {
      b.disabled = false; b.removeAttribute('aria-busy');
    });
  });
})();
