const progressiveBlurEffect = {
  init() {
    const blurElements = document.querySelectorAll('[data-progressive-blur-effect]');

    blurElements.forEach((element) => {
      // Get attributes with defaults
      const intensity = element.dataset.intensity
        ? Number.parseFloat(element.dataset.intensity)
        : 50;
      const position = element.dataset.position ? element.dataset.position : 'top';
      const className = element.dataset.class ? element.dataset.class : '';

      // Calculate intensity factor
      const intensityFactor = intensity / 50;

      // Define blur layers
      const blurLayers = [
        { blur: `${1 * intensityFactor}px`, maskStart: 0, maskEnd: 25, zIndex: 1 },
        { blur: `${3 * intensityFactor}px`, maskStart: 25, maskEnd: 75, zIndex: 2 },
        { blur: `${6 * intensityFactor}px`, maskStart: 75, maskEnd: 100, zIndex: 3 },
      ];

      // Position styles
      const positionStyles = {
        bottom: { bottom: '0', left: '0', right: '0', top: 'auto' },
        top: { top: '0', left: '0', right: '0', bottom: 'auto' },
        left: { left: '0', top: '0', bottom: '0', right: 'auto' },
        right: { right: '0', top: '0', bottom: '0', left: 'auto' },
      };

      // Gradient direction
      const gradientDirection = {
        bottom: 'to bottom',
        top: 'to top',
        left: 'to left',
        right: 'to right',
      };

      // Apply container styles
      Object.assign(
        element.style,
        {
          position: 'absolute',
          zIndex: '10',
          pointerEvents: 'auto',
        },
        positionStyles[position]
      );

      // Add custom class if provided
      if (className) {
        className.split(' ').forEach((cls) => {
          if (cls.trim()) {
            element.classList.add(cls.trim());
          }
        });
      }

      // Create blur layers
      blurLayers.forEach((layer, index) => {
        const layerElement = document.createElement('div');

        const maskImage = `linear-gradient(${gradientDirection[position]}, transparent ${layer.maskStart}%, black ${layer.maskEnd}%)`;

        Object.assign(layerElement.style, {
          position: 'absolute',
          top: '0',
          left: '0',
          right: '0',
          bottom: '0',
          pointerEvents: 'none',
          zIndex: String(layer.zIndex),
          backdropFilter: `blur(${layer.blur})`,
          WebkitBackdropFilter: `blur(${layer.blur})`,
          maskImage: maskImage,
          WebkitMaskImage: maskImage,
        });

        element.appendChild(layerElement);
      });
    });
  },
};

document.addEventListener('DOMContentLoaded', () => {
  progressiveBlurEffect.init();
});
