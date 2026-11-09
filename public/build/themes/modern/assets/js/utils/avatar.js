const avatar = {
  init() {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    gsap.registerPlugin(ScrollTrigger);

    const avatars = document.querySelectorAll('[data-opai-avatar]');
    avatars.forEach((el) => {
      const delay = el.dataset.avatarDelay ? Number.parseFloat(el.dataset.avatarDelay) : 0;
      const direction = el.dataset.avatarDirection || 'left';
      const offset = el.dataset.avatarOffset ? Number.parseFloat(el.dataset.avatarOffset) : 0;

      const from = { opacity: 0, scale: 0, filter: 'blur(5px)' };
      switch (direction) {
        case 'left':
          from.x = -offset;
          break;
        case 'right':
          from.x = offset;
          break;
        case 'down':
          from.y = offset;
          break;
        case 'up':
        default:
          from.y = -offset;
          break;
      }

      gsap.fromTo(
        el,
        from,
        {
          opacity: 1,
          scale: 1,
          x: 0,
          y: 0,
          filter: 'blur(0px)',
          duration: 1.5,
          delay,
          ease: 'elastic.out(1, 0.7)',
          scrollTrigger: {
            trigger: el,
            start: 'top 90%',
            end: 'bottom 20%',
          },
        }
      );
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  avatar.init();
});
