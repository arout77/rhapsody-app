const headerAnimation = {
  headerOne() {
    const header = document.querySelector('.header-scroll');

    if (header) {
      window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
          header.style.transition = 'all 0.5s ease-in-out';
          header.classList.add('scroll-header');
        } else {
          header.classList.remove('scroll-header');
        }
      });
    }
  },
};

if (globalThis.window !== undefined) {
  headerAnimation.headerOne();
}
