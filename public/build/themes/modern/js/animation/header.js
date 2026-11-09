const CLOSE_DELAY = 120;
const MENU_DURATION = 0.5;
const ITEM_STAGGER = 0.025;

function initHeaderMenus() {
  const header = document.querySelector('header');
  const nav = header?.querySelector('nav');
  const root = nav?.querySelector('[data-dropdown-root]');
  const container = root?.querySelector('[data-dropdown-container]');
  if (!header || !nav || !root || !container) return;

  const triggers = Array.from(nav.querySelectorAll('[data-menu]'));
  const sectionEls = Array.from(container.querySelectorAll('[data-dropdown-section]'));
  if (!triggers.length || !sectionEls.length) return;

  const sections = new Map();
  sectionEls.forEach((section) => {
    const name = section.dataset.dropdownSection;
    if (!name) return;
    sections.set(name, { section });
  });

  let activeName = null;
  let activeTrigger = null;
  let activeTween = null;
  let closeTimer = null;
  const gsap = globalThis.gsap;

  const getMenuLayout = (trigger, section) => {
    const navRect = nav.getBoundingClientRect();
    const triggerRect = trigger.getBoundingClientRect();
    const triggerCenter = triggerRect.left + triggerRect.width / 2 - navRect.left;

    return {
      left: triggerCenter,
      width: section.offsetWidth,
      height: section.offsetHeight,
    };
  };

  const setMenu = (name, trigger) => {
    const target = sections.get(name);
    if (!target) return;

    const layout = getMenuLayout(trigger, target.section);
    root.style.left = `${layout.left}px`;
    root.style.width = `${layout.width}px`;
    container.style.width = `${layout.width}px`;
    container.style.height = `${layout.height}px`;

    sections.forEach((entry, key) => {
      const isActive = key === name;

      entry.section.classList.toggle('opacity-100', isActive);
      entry.section.classList.toggle('opacity-0', !isActive);
      entry.section.classList.toggle('pointer-events-auto', isActive);
      entry.section.classList.toggle('pointer-events-none', !isActive);
    });
  };

  const animateMenu = (name, trigger, shouldAnimateItems, wasClosed) => {
    const target = sections.get(name);
    if (!target) return;

    if (!gsap) {
      setMenu(name, trigger);
      return;
    }

    const layout = getMenuLayout(trigger, target.section);
    const items = target.section.querySelectorAll('a');
    const inactiveSections = sectionEls.filter((section) => section !== target.section);

    if (activeTween) activeTween.kill();
    gsap.killTweensOf([root, container, target.section, ...inactiveSections, ...items]);

    if (wasClosed) {
      gsap.set(root, { left: layout.left, width: layout.width, opacity: 0, y: -4 });
      gsap.set(container, { width: layout.width, height: layout.height });
    }

    if (shouldAnimateItems && items.length) {
      gsap.set(items, { transition: 'none', willChange: 'transform, opacity' });
    }

    root.classList.remove('opacity-0');
    target.section.classList.remove('opacity-0', 'pointer-events-none');
    target.section.classList.add('opacity-100', 'pointer-events-auto');
    inactiveSections.forEach((section) => {
      section.classList.remove('opacity-100', 'pointer-events-auto');
      section.classList.add('opacity-0', 'pointer-events-none');
    });

    activeTween = gsap.timeline({ defaults: { ease: 'power3.out', force3D: true } });
    activeTween.to(
      root,
      { left: layout.left, width: layout.width, opacity: 1, y: 0, duration: MENU_DURATION },
      0
    );
    activeTween.to(
      container,
      { width: layout.width, height: layout.height, duration: MENU_DURATION },
      0
    );
    activeTween.to(inactiveSections, { opacity: 0, duration: 0.12, overwrite: 'auto' }, 0);
    activeTween.set(target.section, { opacity: 1 }, 0);

    if (shouldAnimateItems && items.length) {
      activeTween.fromTo(
        items,
        { x: 14, opacity: 0 },
        {
          x: 0,
          opacity: 1,
          duration: 0.4,
          stagger: ITEM_STAGGER,
          ease: 'power2.out',
          overwrite: 'auto',
          force3D: true,
          onComplete: () => gsap.set(items, { clearProps: 'transition,willChange,transform' }),
        },
        0.05
      );
    }
  };

  const openMenu = (trigger) => {
    const name = trigger.dataset.menu;
    if (!sections.has(name)) return;

    if (activeName === name) {
      activeTrigger = trigger;
      return;
    }

    const wasClosed = !activeName;
    activeName = name;
    activeTrigger = trigger;
    root.classList.remove('pointer-events-none', 'opacity-0');
    root.classList.add('pointer-events-auto', 'opacity-100');
    animateMenu(name, trigger, true, wasClosed);
  };

  const closeMenu = () => {
    if (!activeName) return;
    activeName = null;
    activeTrigger = null;

    if (activeTween) activeTween.kill();

    const finishClose = () => {
      root.classList.remove('pointer-events-auto', 'opacity-100');
      root.classList.add('pointer-events-none', 'opacity-0');

      sections.forEach((entry) => {
        entry.section.classList.remove('opacity-100', 'pointer-events-auto');
        entry.section.classList.add('opacity-0', 'pointer-events-none');
      });
    };

    if (!gsap) {
      finishClose();
      return;
    }

    activeTween = gsap.to(root, {
      opacity: 0,
      duration: 0.16,
      ease: 'power2.out',
      overwrite: 'auto',
      onComplete: finishClose,
    });
  };

  const stopClose = () => {
    globalThis.clearTimeout(closeTimer);
  };

  const startClose = () => {
    stopClose();
    closeTimer = globalThis.setTimeout(closeMenu, CLOSE_DELAY);
  };

  triggers.forEach((trigger) => {
    const name = trigger.dataset.menu;
    if (!sections.has(name)) return;

    trigger.addEventListener('mouseenter', () => {
      stopClose();
      openMenu(trigger);
    });

    trigger.addEventListener('mouseleave', startClose);

    trigger.addEventListener('click', (event) => {
      event.preventDefault();
      if (activeTrigger === trigger) {
        closeMenu();
      } else {
        openMenu(trigger);
      }
    });
  });

  root.addEventListener('mouseenter', stopClose);
  root.addEventListener('mouseleave', startClose);

  document.addEventListener('click', (event) => {
    if (!activeName || header.contains(event.target)) return;
    closeMenu();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeMenu();
  });

  globalThis.addEventListener('resize', () => {
    if (!activeName || !activeTrigger) return;
    setMenu(activeName, activeTrigger);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initHeaderMenus);
} else {
  initHeaderMenus();
}
