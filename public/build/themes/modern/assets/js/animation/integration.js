const animation = {
  init() {
    gsap.registerPlugin(MotionPathPlugin);
    gsap.registerPlugin(ScrollTrigger);

    

    // Homepage 01 04 Features Integration pill
    const integrationFeaturesPill = () => {
      const paths = document.querySelectorAll('.features-integration-pill svg path.connector');
      if (!paths.length) return;

      paths.forEach((path, i) => {
        const len = path.getTotalLength();

        // draw-on
        gsap.set(path, { strokeDasharray: len, strokeDashoffset: len });
        gsap.to(path, {
          strokeDashoffset: 0,
          duration: 1.1,
          ease: 'power3.out',
          delay: i * 0.08,
        });

        // moving glow dot (center-aligned + on top)
        const svg = path.closest('svg');

        // add a tiny glow filter once per SVG (optional)
        if (!svg.querySelector('defs #glow')) {
          const defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
          defs.innerHTML = `
        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
          <feGaussianBlur stdDeviation="1" result="b"/>
          <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>`;
          svg.prepend(defs);
        }

        // Get dot color from data attribute on features-integration-pill
        const pillContainer = path.closest('.features-integration-pill');
        const dotColorAttr = pillContainer?.getAttribute('data-dot-color') || 'white';
        const dotColor = dotColorAttr === 'black' ? '#000' : '#fff';

        const dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        dot.setAttribute('r', '1.6');
        dot.setAttribute('fill', dotColor);
        dot.setAttribute('opacity', '1');
        dot.setAttribute('filter', 'url(#glow)');
        dot.style.pointerEvents = 'none';

        // append AFTER the path so it paints on top
        svg.appendChild(dot);

        // center the element for motion-path alignment
        gsap.set(dot, { transformBox: 'fill-box', transformOrigin: '50% 50%' });

        gsap.fromTo(
          dot,
          { opacity: 0 },
          {
            opacity: 1,
            duration: 2.2,
            repeat: -1,
            ease: 'none',
            yoyo: false,
            // key part: align the element's CENTER to the path
            motionPath: {
              path,
              align: path,
              alignOrigin: [0.5, 0.5],
              autoRotate: false,
              start: 0,
              end: 1,
            },
            delay: 0.5 + i * 0.15,
          }
        );
      });
    };

    integrationFeaturesPill();
  },
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    animation.init();
  });
} else {
  animation.init();
}
