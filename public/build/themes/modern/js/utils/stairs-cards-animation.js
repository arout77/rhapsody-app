const stairsCardsAnimation = {
  init() {
    const containers = document.querySelectorAll('[data-stairs-wrapper]');
    if (containers.length === 0) return;

    containers.forEach((container) => {
      const cardSelector = container.getAttribute('data-stairs-wrapper');

      const cards = container.querySelectorAll(cardSelector);

      const baseOffset = container.getAttribute('data-base-offset')
        ? parseFloat(container.getAttribute('data-base-offset'))
        : 150;
      const stepOffset = container.getAttribute('data-step-offset')
        ? parseFloat(container.getAttribute('data-step-offset'))
        : 22;
      const duration = container.getAttribute('data-duration')
        ? parseFloat(container.getAttribute('data-duration'))
        : 1.2;
      const stagger = container.getAttribute('data-stagger')
        ? parseFloat(container.getAttribute('data-stagger'))
        : 0.2;
      const start = container.getAttribute('data-start') || 'top 80%';
      const end = container.getAttribute('data-end') || 'top 20%';
      const useScrub =
        container.hasAttribute('data-scrub') && container.getAttribute('data-scrub') !== 'false';

      const once =
        container.hasAttribute('data-once') && container.getAttribute('data-once') !== 'false';

      cards.forEach((card, index) => {
        const offsetY = baseOffset + index * stepOffset;
        gsap.set(card, {
          y: offsetY,
        });
      });

      const animationProps = {
        y: 0,
        duration: duration,
        ease: 'power3.out',
        stagger: stagger,
        scrollTrigger: {
          trigger: container,
          start: start,
          end: end,
          once: once,
        },
      };

      if (useScrub) {
        animationProps.scrollTrigger.scrub = true;
      }

      gsap.to(cards, animationProps);
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  stairsCardsAnimation.init();
});
