const borderExpand = {
  init() {
    const lengthElements = document.querySelectorAll('[data-opai-border-expand]');
    lengthElements.forEach((element) => {
      const ElementFinalWidth = element.offsetWidth;
      // const instant = element.getAttribute('data-instant')
      //   ? element.getAttribute('data-instant')
      //   : 'false';
      const delay = element.getAttribute('data-delay')
        ? parseFloat(element.getAttribute('data-delay'))
        : 0;

      const top = element.getAttribute('data-top') ? element.getAttribute('data-top') : 'top 100%';

      const markerId = element.getAttribute('data-marker-id')
        ? element.getAttribute('data-marker-id')
        : false;
      const duration = element.getAttribute('data-duration')
        ? parseFloat(element.getAttribute('data-duration'))
        : 0.6;

      // set initial width
      gsap.set(element, {
        width: 0,
      });

      gsap.to(element, {
        scrollTrigger: {
          trigger: element,
          start: top,
          end: 'top 100%',
          toggleActions: 'play none none none',
          markers: markerId ? true : false,
          id: markerId && markerId,
        },

        width: ElementFinalWidth,
        duration: duration,
        ease: 'power3.out',
        delay: delay,
      });
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  borderExpand.init();
});
