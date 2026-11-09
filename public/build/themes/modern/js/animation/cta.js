const CtaAnimation = {
  initGlobeImageAnimation() {
    const globeImage = document.querySelector('.globe-image');

    gsap.from(globeImage, {
      opacity: 0,
      y: 50,
      duration: 1,
      ease: 'power3.inOut',
      scrollTrigger: {
        trigger: globeImage,
        start: 'top 80%',
        end: 'bottom 20%',
      },
    });
  },

  initFancySlider() {
    const slider = document.getElementById('fancy-slider');
    if (slider) {
      const slides = slider.querySelectorAll('figure');

      let index = 0;
      function updateSlides() {
        slides.forEach((slide, i) => {
          const offset = (i - index + slides.length) % slides.length;

          // Reset common styles
          slide.style.zIndex = 0;
          slide.style.border = '4px solid';
          slide.style.borderRadius = '8px';
          slide.style.borderColor = 'var(--color-background-1)';
          slide.style.filter = 'blur(4px)';
          slide.style.transform = 'scale(0.7) translateX(0) rotate(0deg)';
          slide.style.width = '180px';
          slide.style.height = '180px';

          if (offset === 0) {
            // Center (active)
            slide.style.zIndex = 10;
            slide.style.filter = 'blur(0)';
            slide.style.transform = 'scale(1) translateX(0) rotate(0)';
            slide.style.border = '4px solid';
            slide.style.boxShadow = '0 0 40px #000';
            slide.style.borderColor = 'white';
            slide.style.borderRadius = '11px';
            slide.style.width = '200px';
            slide.style.height = '200px';
          } else if (offset === 1 || offset === slides.length - 1) {
            // Sides
            slide.style.zIndex = 5;
            slide.style.transform = `scale(1) translateX(${offset === 1 ? 185 : -185}px) translateY(40px) rotate(${offset === 1 ? '20deg' : '-20deg'})`;
          } else {
            // Back (faded)
            slide.style.zIndex = 1;
            slide.style.transform = `scale(0.87) translateX(${offset === 2 ? 350 : -350}px) translateY(140px) rotate(${offset === 2 ? '50deg' : '-50deg'}) opacity(0)`; // opacity is 0 to hide the slide 2 slides
          }
        });
      }

      function nextSlide() {
        index = (index + 1) % slides.length;
        updateSlides();
      }
      updateSlides();
      setInterval(nextSlide, 2500);
    }
  }
};



document.addEventListener('DOMContentLoaded', () => {
  CtaAnimation.initGlobeImageAnimation();
  CtaAnimation.initFancySlider();
});
