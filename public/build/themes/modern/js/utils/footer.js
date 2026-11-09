/* =========================
Footer Text Shuffle Animation
=========================== */

const footerTextShuffle = {
  init() {
    const elements = document.querySelectorAll('.footer-title, .footer-title-2');
    if (!elements.length) return;

    elements.forEach((element) => {
      element.style.whiteSpace = 'nowrap';

      // Get the text content, handling both direct text and span elements
      const spanElement = element.querySelector('span');
      const textNode = spanElement || element;
      const originalText = textNode.textContent?.trim() || 'OPTIM AI';
      const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const duration = 2000;
      let startTime = null;

      const animate = (currentTime) => {
        if (!startTime) startTime = currentTime;
        const progress = Math.min((currentTime - startTime) / duration, 1);
        const revealed = Math.floor(progress * originalText.length);

        const animatedText = originalText
          .split('')
          .map((char, i) => {
            if (char === ' ') return ' ';
            return i < revealed ? originalText[i] : chars[Math.floor(Math.random() * 26)];
          })
          .join('');

        // Update the text while preserving span structure if it exists
        if (spanElement) {
          spanElement.textContent = animatedText;
        } else {
          element.textContent = animatedText;
        }

        if (progress < 1) {
          requestAnimationFrame(animate);
        } else {
          // Restore original text
          if (spanElement) {
            spanElement.textContent = originalText;
          } else {
            element.textContent = originalText;
          }
        }
      };

      const shuffle = () => {
        startTime = null;
        requestAnimationFrame(animate);
      };

      if (typeof ScrollTrigger !== 'undefined') {
        ScrollTrigger.create({
          trigger: element,
          start: 'top 100%',
          onEnter: shuffle,
          once: true,
        });
      } else {
        // Fallback: run animation immediately if ScrollTrigger is not available
        shuffle();
      }
    });
  },
};

document.addEventListener('DOMContentLoaded', () => footerTextShuffle.init());
