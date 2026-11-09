const wordSlideReveal = {
  init() {
    const elements = document.querySelectorAll('[data-opai-word-slide-reveal]');
    
    elements.forEach((elem) => {
      
      const wordDelay = parseFloat(elem.getAttribute('data-word-delay') || '0.15');
        const duration = parseFloat(elem.getAttribute('data-word-duration') || '1');
      const offsetX = parseFloat(elem.getAttribute('data-word-offset-x') || '20');
      const blur = parseFloat(elem.getAttribute('data-word-blur') || '10');
      const instant = elem.hasAttribute('data-word-instant') &&
        elem.getAttribute('data-word-instant') !== 'false';
      const start = elem.getAttribute('data-word-start') || 'top 90%';
      const delay = parseFloat(elem.getAttribute('data-delay') || '0.1');

      
      if (typeof SplitText === 'undefined') {
      
        return;
      }

      
      const splitText = new SplitText(elem, { 
        type: 'words', 
        tag: 'span' 
      });
      
      const wordSpans = splitText.words;
      
      if (!wordSpans || wordSpans.length === 0) return;

      elem.style.opacity = 1;
      elem._splitTextInstance = splitText;

      
      wordSpans.forEach((word) => {
        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'display: inline-block;';
        word.parentNode?.insertBefore(wrapper, word);
        wrapper.appendChild(word);
      });

      
      gsap.fromTo(
        wordSpans,
        { 
          x: offsetX, 
          opacity: 0, 
          display: 'block', 
          filter: `blur(${blur}px)` 
        },
        {
          x: 0,
          opacity: 1,
          filter: 'blur(0px)',
          duration: duration,
          delay: delay,
          ease: 'power2.inOut',
          stagger: wordDelay,
          scrollTrigger: instant
            ? null
            : {
                trigger: elem,
                start: start,
                once: true,
              },
        }
      );
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  wordSlideReveal.init();
});
