const commonAnimation = {
  init() {
    // Register ScrollTrigger plugin
    gsap.registerPlugin(ScrollTrigger);
    const imagesLoadingReveal = document.querySelectorAll('.image-loading-reveal img');
    const stepLine = document.querySelectorAll('.step-line');
    const progressBars = document.querySelectorAll(
      '.progress-bar-blue, .progress-bar-black, .progress-bar-lemon, .progress-bar-cyan'
    );
    // ============ step line ============
    if (stepLine.length > 0) {
      // Set initial state for all step lines
      gsap.set(stepLine, { height: '0px' });

      const firstStepLine = stepLine[0];
      const triggerElement =
        firstStepLine.closest('.step-line-container') ||
        firstStepLine.parentElement ||
        firstStepLine;

      const stepTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: triggerElement,
          start: 'top 79%',
          end: 'top 15%',
          toggleActions: 'play none none',
        },
      });

      stepLine.forEach((line, index) => {
        stepTimeline.to(
          line,
          {
            height: '100%',
            duration: 1.4,
            ease: 'power3.out',
          },
          index * 0.7 // Each animation with 0.7s delay duration)
        );
      });
    }
    // ============ progress bars ============
    if (progressBars.length > 0) {
      gsap.set(progressBars, { height: '0px' });

      const progressTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: '.progress-bar-blue',
          start: 'top 88%',
          end: 'bottom 20%',
          toggleActions: 'play none none ',
        },
      });

      progressTimeline
        .to(
          '.progress-bar-blue',
          {
            height: '30%',
            duration: 1.2,
            ease: 'power2.out',
          },
          '-=0.3'
        )
        .to(
          '.progress-bar-black',
          {
            height: '65%',
            duration: 1.2,
            ease: 'power2.out',
          },
          '-=0.3'
        )
        .to(
          '.progress-bar-lemon',
          {
            height: '45%',
            duration: 1.2,
            ease: 'power2.out',
          },
          '-=0.8'
        )
        .to(
          '.progress-bar-cyan',
          {
            height: '30%',
            duration: 1.2,
            ease: 'power2.out',
          },
          '-=0.9'
        );
    }

    // ============ image loading reveal ============
    if (imagesLoadingReveal.length > 0) {
      imagesLoadingReveal.forEach((img) => {
        const triggerEl = img.closest('.image-loading-reveal') || img;

        gsap.set(img, {
          clipPath: 'polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%)',
          opacity: 0,
          scale: 0.9,
        });

        gsap.fromTo(
          img,
          {
            clipPath: 'polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%)',
            opacity: 0,
            scale: 0.9,
          },
          {
            clipPath: 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)',
            opacity: 1,
            scale: 1,
            ease: 'back.in',
            duration: 0.9,
            scrollTrigger: {
              trigger: triggerEl,
              start: 'top 94%',
              end: 'bottom 58%',
              toggleActions: 'play none none none',
            },
          }
        );
      });
    }
   
  },
};

// Footer Year  text content function
const updateFooterYear = () => {
  const footerYearElements = document.querySelectorAll('[data-footer-year]');

  if (footerYearElements.length > 0) {
    const currentYear = new Date().getFullYear();
    footerYearElements.forEach((element) => {
      element.textContent = currentYear;
    });
  }
};

if (typeof window !== 'undefined') {
  commonAnimation.init();
  updateFooterYear();
}
