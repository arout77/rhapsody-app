const glowingCardsAnimation = {
  init() {
    // Find all gradient card wrapper elements
    const wrappers = document.querySelectorAll('.glowing-cards');

    wrappers.forEach((wrapper) => {
      const cards = wrapper.querySelectorAll('.glowing-card, .glowing-card-v2');
      let lastMouseX = 0;
      let lastMouseY = 0;
      let isMouseInside = false;

      wrapper.addEventListener('mouseenter', (event) => {
        isMouseInside = true;
        cards.forEach((card) => {
          const rect = card.getBoundingClientRect();
          const x = event.clientX - rect.left;
          const y = event.clientY - rect.top;

          // Set initial position to mouse entry point
          lastMouseX = x;
          lastMouseY = y;
          card.style.setProperty('--xPos', `${x}px`);
          card.style.setProperty('--yPos', `${y}px`);
        });
      });

      wrapper.addEventListener('mousemove', (event) => {
        if (!isMouseInside) return;

        cards.forEach((card) => {
          const rect = card.getBoundingClientRect();
          const x = event.clientX - rect.left;
          const y = event.clientY - rect.top;

          // Store last mouse position
          lastMouseX = x;
          lastMouseY = y;

          // Set CSS custom properties for radial gradient position
          card.style.setProperty('--xPos', `${x}px`);
          card.style.setProperty('--yPos', `${y}px`);
        });
      });

      // Keep gradient at last mouse position when mouse leaves
      wrapper.addEventListener('mouseleave', () => {
        isMouseInside = false;
        // Don't change position - let it fade out from last position
        // The CSS transition will handle the fade out
      });
    });
  },
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    glowingCardsAnimation.init();
  });
} else {
  glowingCardsAnimation.init();
}
