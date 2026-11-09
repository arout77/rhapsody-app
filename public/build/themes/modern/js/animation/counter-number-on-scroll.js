const initCounterNumberOnScroll = () => {
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;

  gsap.registerPlugin(ScrollTrigger);

  const counterTriggers = document.querySelectorAll('[data-counter-trigger]');

  counterTriggers.forEach((counterTrigger) => {
    const counterFlow = counterTrigger.querySelector('[data-counter-number]');
    const counterValue = Number(counterTrigger.dataset.counterValue) || 0;
    const counterDuration = Number(counterTrigger.dataset.counterDuration) || 1.8;
    const counterFractionDigits = Number(counterTrigger.dataset.counterFractionDigits) || 0;

    if (!counterFlow || typeof counterFlow.update !== 'function') return;

    counterFlow.trend = 0;
    counterFlow.format = {
      useGrouping: true,
      maximumFractionDigits: counterFractionDigits,
      minimumFractionDigits: counterFractionDigits,
    };
    counterFlow.update(0);

    ScrollTrigger.create({
      trigger: counterTrigger,
      start: 'top 90%',
      once: true,
      onEnter: () => {
        counterFlow.transformTiming = { duration: counterDuration * 1000, easing: 'ease-out' };
        counterFlow.spinTiming = { duration: counterDuration * 1000, easing: 'ease-out' };
        counterFlow.opacityTiming = {
          duration: Math.max(250, counterDuration * 450),
          easing: 'ease-out',
        };
        counterFlow.update(counterValue);
      },
    });
  });
};

const bootCounterNumberOnScroll = () => {
  if (customElements.get('number-flow')) {
    initCounterNumberOnScroll();
    return;
  }

  customElements.whenDefined('number-flow').then(initCounterNumberOnScroll);
};

document.addEventListener('DOMContentLoaded', bootCounterNumberOnScroll);
