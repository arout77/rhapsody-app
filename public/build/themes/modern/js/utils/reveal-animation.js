const animation = {
  init() {
    const elements = document.querySelectorAll('[data-opai-animate]');
    const Springer = window.Springer.default;
    elements.forEach((elem) => {
      const duration = elem.getAttribute('data-duration')
        ? parseFloat(elem.getAttribute('data-duration'))
        : 0.6;
      const blur = elem.getAttribute('data-blur') ? parseFloat(elem.getAttribute('data-blur')) : 0;
      const delay = elem.getAttribute('data-delay')
        ? parseFloat(elem.getAttribute('data-delay'))
        : 0;
      const offset = elem.getAttribute('data-offset')
        ? parseFloat(elem.getAttribute('data-offset'))
        : 60;
      const instant =
        elem.hasAttribute('data-instant') && elem.getAttribute('data-instant') !== 'false';
      const start = elem.getAttribute('data-start') || 'top 90%';
      const end = elem.getAttribute('data-end') || 'top 50%';
      const direction = elem.getAttribute('data-direction') || 'down';
      const useSpring = elem.hasAttribute('data-spring');
      const spring = useSpring ? Springer(0.2, 0.8) : null;
      const rotation = elem.getAttribute('data-rotation')
        ? parseFloat(elem.getAttribute('data-rotation'))
        : 0;
      const scale = elem.getAttribute('data-scale')
        ? parseFloat(elem.getAttribute('data-scale'))
        : 1;
      const animationType = elem.getAttribute('data-animation-type') || 'from';
      // Force initial state with !important equivalent
      elem.style.opacity = '1';
      elem.style.filter = `blur(${blur}px)`;

      // Set animation properties based on animation type
      let animationProps;

      if (animationType === 'to') {
        // gsap.to() - animate TO the specified values
        animationProps = {
          opacity: 1,
          filter: 'blur(0)',
          duration: duration,
          delay: delay,
          ease: useSpring ? spring : 'power2.out',
          scale: scale,
        };

        // Add rotation if specified
        if (rotation !== 0) {
          animationProps.rotation = rotation;
        }
      } else {
        // gsap.from() - animate FROM the specified values to normal
        animationProps = {
          opacity: 0,
          filter: 'blur(16px)',
          duration: duration,
          delay: delay,
          ease: useSpring ? spring : 'power2.out',
        };

        // Add rotation if specified
        if (rotation !== 0) {
          animationProps.rotation = rotation;
        }
      }

      if (!instant) {
        animationProps.scrollTrigger = {
          trigger: elem,
          start: start,
          end: end,
          scrub: false,
        };
      }

      // Set animation direction based on data-direction
      switch (direction) {
        case 'left':
          animationProps.x = -offset;
          break;
        case 'right':
          animationProps.x = offset;
          break;
        case 'down':
          animationProps.y = offset;
          break;
        case 'up':
        default:
          animationProps.y = -offset;
          break;
      }

      // Use appropriate GSAP method based on animation type
      if (animationType === 'to') {
        gsap.to(elem, animationProps);
      } else {
        gsap.from(elem, animationProps);
      }
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  animation.init();
});
