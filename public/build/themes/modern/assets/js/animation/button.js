const magneticButton = {
  init() {
    document.querySelectorAll('.magnetic-button').forEach((button) => {
      const gradients = button.querySelectorAll('.magnetic-button-gradient');
      if (gradients.length === 0) return;

      const gradientHalf = 41;
      const moveGradient = gsap.quickTo(gradients, 'x', {
        duration: 0.75,
        ease: 'power2.out',
      });

      button.addEventListener('mousemove', (e) => {
        const rect = button.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        moveGradient(mouseX - rect.width + gradientHalf);
      });

      button.addEventListener('mouseleave', () => {
        moveGradient(0);
      });
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  magneticButton.init();
});
