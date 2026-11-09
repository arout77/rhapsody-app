class MobileMenuAccordion {
  constructor(options = {}) {
    this.defaultOpenMenu = options.defaultOpenMenu || 'company-mega-menu';
    this.toggleButtons = null;
    this.submenus = null;
    this.arrows = null;
    this.init();
  }

  init() {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.bindEvents());
    } else {
      this.bindEvents();
    }
  }

  bindEvents() {
    this.toggleButtons = document.querySelectorAll('.mobile-menu-toggle[data-menu]');

    if (this.toggleButtons.length === 0) {
      return;
    }

    this.submenus = document.querySelectorAll('.mobile-submenu[data-submenu]');
    this.arrows = document.querySelectorAll('.mobile-menu-toggle .menu-arrow');

    this.setDefaultState();

    this.toggleButtons.forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        const menuId = button.dataset.menu;
        if (!menuId) return;
        this.toggleMenu(menuId);
      });
    });
  }

  setDefaultState() {
    this.submenus.forEach((submenu) => {
      submenu.classList.add('hidden');
      submenu.classList.remove('block');
    });

    this.arrows.forEach((arrow) => {
      arrow.classList.remove('rotate-90');
    });

    if (this.defaultOpenMenu) {
      const defaultSubmenu = document.querySelector(
        `.mobile-submenu[data-submenu="${this.defaultOpenMenu}"]`
      );
      const defaultButton = document.querySelector(
        `.mobile-menu-toggle[data-menu="${this.defaultOpenMenu}"]`
      );
      const defaultArrow = defaultButton?.querySelector('.menu-arrow');

      if (defaultSubmenu) {
        defaultSubmenu.classList.remove('hidden');
        defaultSubmenu.classList.add('block');
      }

      if (defaultArrow) {
        defaultArrow.classList.add('rotate-90');
      }
    }
  }

  toggleMenu(menuId) {
    const submenu = document.querySelector(`.mobile-submenu[data-submenu="${menuId}"]`);
    const button = document.querySelector(`.mobile-menu-toggle[data-menu="${menuId}"]`);
    const arrow = button?.querySelector('.menu-arrow');

    if (!submenu || !button) {
      return;
    }

    const isCurrentlyOpen =
      submenu.classList.contains('block') && !submenu.classList.contains('hidden');

    this.closeAllMenus();

    if (isCurrentlyOpen) {
      submenu.classList.add('hidden');
      submenu.classList.remove('block');
      if (arrow) {
        arrow.classList.remove('rotate-90');
      }
    } else {
      submenu.classList.remove('hidden');
      submenu.classList.add('block');
      if (arrow) {
        arrow.classList.add('rotate-90');
      }
    }
  }

  closeAllMenus() {
    this.submenus.forEach((submenu) => {
      submenu.classList.add('hidden');
      submenu.classList.remove('block');
    });

    this.arrows.forEach((arrow) => {
      arrow.classList.remove('rotate-90');
    });
  }
}

function initMobileSidebar() {
  const openBtn = document.querySelector('header .nav-hamburger');
  const sidebar = document.getElementById('mobile-sidebar');
  const closeBtn = sidebar?.querySelector('.nav-hamburger-close');
  if (!openBtn || !sidebar) return;

  let isOpen = false;

  const open = () => {
    isOpen = true;
    sidebar.classList.remove('translate-x-full');
    sidebar.classList.add('translate-x-0');
    openBtn.setAttribute('aria-expanded', 'true');
    document.documentElement.classList.add('overflow-hidden');
  };

  const close = () => {
    isOpen = false;
    sidebar.classList.add('translate-x-full');
    sidebar.classList.remove('translate-x-0');
    openBtn.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('overflow-hidden');
  };

  const toggle = () => {
    if (isOpen) close();
    else open();
  };

  openBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggle();
  });

  closeBtn?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    close();
  });

  document.addEventListener('click', (e) => {
    if (!isOpen) return;
    const t = e.target;
    if (sidebar.contains(t) || openBtn.contains(t)) return;
    close();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && isOpen) close();
  });
}

document.addEventListener('DOMContentLoaded', () => {
  initMobileSidebar();

  const mobileMenuExists = document.querySelector('.mobile-menu-toggle[data-menu]');

  if (mobileMenuExists) {
    globalThis.mobileMenuAccordion = new MobileMenuAccordion({
      defaultOpenMenu: 'company-mega-menu',
    });
  }
});
