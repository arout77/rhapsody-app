export function dividerExpand(dividers) {
  const elements = dividers instanceof Element ? [dividers] : Array.from(dividers || []);

  elements.forEach((divider) => {
    if (!divider) return;

    gsap.to(divider, {
      scrollTrigger: {
        trigger: divider,
        start: 'top 100%',
        end: 'top 50%',
        toggleActions: 'play none none none',
      },
      width: '50%',
      duration: 1,
      delay: 0.7,
      ease: 'power2.out',
    });
  });
}
