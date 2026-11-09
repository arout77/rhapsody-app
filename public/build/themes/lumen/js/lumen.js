/*!
 * Lumen theme — vanilla JavaScript (no dependencies, no inline scripts, CSP friendly).
 * Features: mobile menu, floating-menubar scroll state, cursor-following nav highlight,
 * scroll reveal, how-it-works stepper, FAQ accordion, pricing switcher, footer year.
 */
(function () {
  'use strict';

  var doc = document;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function $(sel, ctx) { return (ctx || doc).querySelector(sel); }
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); }

  /* ---- Menubar: fixed state + mobile toggle ------------------------------ */
  var bar = $('[data-menubar]');
  if (bar) {
    var toggle = $('[data-menu-toggle]', bar);
    var ticking = false;

    var onScroll = function () {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () {
        bar.classList.toggle('has-fixed', window.scrollY > 8);
        ticking = false;
      });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    var setMenu = function (open) {
      bar.classList.toggle('menu-shown', open);
      doc.documentElement.style.overflow = open ? 'hidden' : '';
      if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    if (toggle) {
      toggle.addEventListener('click', function () { setMenu(!bar.classList.contains('menu-shown')); });
    }
    doc.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && bar.classList.contains('menu-shown')) { setMenu(false); if (toggle) toggle.focus(); }
    });
    $$('.lm-menu__link', bar).forEach(function (a) {
      a.addEventListener('click', function () { setMenu(false); });
    });
    window.addEventListener('resize', function () { if (window.innerWidth >= 1200) setMenu(false); });

    /* Cursor-following highlight (desktop only, pure CSS transform driven by 4 vars) */
    var list = $('[data-menu-list]', bar);
    if (list && window.matchMedia('(min-width: 1200px) and (hover: hover)').matches) {
      list.classList.add('is-following');
      var move = function (el) {
        var lr = list.getBoundingClientRect(), er = el.getBoundingClientRect();
        list.style.setProperty('--menu-w', er.width + 'px');
        list.style.setProperty('--menu-h', er.height + 'px');
        list.style.setProperty('--menu-x', (er.left - lr.left) + 'px');
        list.style.setProperty('--menu-y', (er.top - lr.top) + 'px');
        list.style.setProperty('--menu-visible', '1');
      };
      $$('.lm-menu__link', list).forEach(function (a) {
        a.addEventListener('mouseenter', function () { move(a); });
        a.addEventListener('focus', function () { move(a); });
      });
      list.addEventListener('mouseleave', function () { list.style.setProperty('--menu-visible', '0'); });
    }
  }

  /* ---- Reveal on scroll --------------------------------------------------- */
  var revealEls = $$('[data-reveal]');
  if (revealEls.length) {
    var show = function (el) {
      var d = el.getAttribute('data-reveal-delay');
      if (d) el.style.setProperty('--reveal-delay', d + 's');
      el.classList.add('is-in');
    };
    if (reduce || !('IntersectionObserver' in window)) {
      revealEls.forEach(show);
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting || en.boundingClientRect.top < 0) { show(en.target); io.unobserve(en.target); }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
      revealEls.forEach(function (el) { io.observe(el); });
    }
  }

  /* ---- Stepper (how it works) -------------------------------------------- */
  $$('[data-stepper]').forEach(function (root) {
    var steps = $$('[data-step]', root);
    var slides = $$('[data-slide]', root);
    var current = 0, timer = null;
    var go = function (i) {
      current = i;
      steps.forEach(function (s, n) { s.classList.toggle('is-active', n === i); s.setAttribute('aria-selected', n === i ? 'true' : 'false'); });
      slides.forEach(function (s, n) { s.classList.toggle('is-active', n === i); });
    };
    var play = function () {
      if (reduce) return;
      stop();
      timer = window.setInterval(function () { go((current + 1) % steps.length); }, 5000);
    };
    var stop = function () { if (timer) { window.clearInterval(timer); timer = null; } };
    steps.forEach(function (s, n) {
      s.addEventListener('click', function () { go(n); play(); });
    });
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', play);
    go(0);
    play();
  });

  /* ---- Accordion ---------------------------------------------------------- */
  $$('[data-accordion]').forEach(function (acc) {
    var single = acc.getAttribute('data-accordion') !== 'multi';
    var items = $$('.lm-acc__item', acc);
    var set = function (item, open) {
      item.classList.toggle('is-open', open);
      var btn = $('.lm-acc__btn', item);
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    items.forEach(function (item) {
      var btn = $('.lm-acc__btn', item);
      if (!btn) return;
      btn.addEventListener('click', function () {
        var open = !item.classList.contains('is-open');
        if (single && open) items.forEach(function (o) { if (o !== item) set(o, false); });
        set(item, open);
      });
    });
  });

  /* ---- Pricing switcher --------------------------------------------------- */
  $$('[data-billing-switch]').forEach(function (sw) {
    var scope = sw.closest('[data-billing-scope]') || doc.body;
    var buttons = $$('button[data-billing]', sw);
    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        var mode = b.getAttribute('data-billing');
        scope.setAttribute('data-billing', mode);
        sw.classList.toggle('is-yearly', mode === 'yearly');
        buttons.forEach(function (o) { o.setAttribute('aria-pressed', o === b ? 'true' : 'false'); });
      });
    });
  });

  /* ---- Password visibility toggles --------------------------------------- */
  $$('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = doc.getElementById(btn.getAttribute('data-toggle-password'));
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });


  /* ---- Docs: collapsible menu, topic filter, current-page marker ---------- */
  $$('[data-docs-menu]').forEach(function (menu) {
    var mq = window.matchMedia('(min-width: 1024px)');
    var sync = function () { menu.open = mq.matches; };
    sync();
    if (mq.addEventListener) mq.addEventListener('change', sync); else if (mq.addListener) mq.addListener(sync);

    var here = window.location.pathname.replace(/\/+$/, '');
    $$('.lm-docs__link', menu).forEach(function (a) {
      if (a.pathname.replace(/\/+$/, '') === here) {
        a.setAttribute('aria-current', 'page');
        var nav = a.closest('[data-docs-nav]');
        if (nav && nav.scrollHeight > nav.clientHeight) { nav.scrollTop = Math.max(0, a.offsetTop - nav.clientHeight / 3); }
      }
    });

    var input = $('[data-docs-filter]', menu);
    var empty = $('[data-docs-empty]', menu);
    if (input) {
      input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase(), any = false;
        $$('[data-docs-group]', menu).forEach(function (g) {
          var shown = 0;
          $$('li', g).forEach(function (li) {
            var hit = !q || li.textContent.toLowerCase().indexOf(q) !== -1;
            li.hidden = !hit;
            if (hit) shown++;
          });
          g.hidden = shown === 0;
          if (shown) any = true;
        });
        if (empty) empty.hidden = any;
      });
    }
  });

  /* ---- Footer year -------------------------------------------------------- */
  $$('[data-year]').forEach(function (el) { el.textContent = String(new Date().getFullYear()); });
})();
