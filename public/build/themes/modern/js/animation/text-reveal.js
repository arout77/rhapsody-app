const parseFloatDataset = (dataset, key, defaultValue) => {
  const value = dataset[key];
  return value === undefined ? defaultValue : Number.parseFloat(value);
};

const revealElement = (elem) => {
  const { dataset } = elem;
  const wordDelay = parseFloatDataset(dataset, 'wordDelay', 0);
  const duration = parseFloatDataset(dataset, 'wordDuration', 0.6);
  const offsetY = parseFloatDataset(dataset, 'wordOffsetY', 30);
  const rotationX = parseFloatDataset(dataset, 'wordRotationX', 95);
  const instant = dataset.wordInstant !== undefined && dataset.wordInstant !== 'false';
  const start = dataset.wordStart || 'top 85%';
  const end = dataset.wordEnd || 'top 40%';
  const delay = parseFloatDataset(dataset, 'delay', 0);

  if (typeof SplitText === 'undefined') {
    console.warn('GSAP SplitText is not available');
    return;
  }

  const splitText = new SplitText(elem, {
    type: 'words',
  });

  const wordSpans = splitText.words;

  if (!wordSpans?.length) {
    return;
  }

  elem.style.opacity = 1;
  elem._splitTextInstance = splitText;

  gsap.set(elem, { overflow: 'hidden' });

  gsap.set(wordSpans, {
    y: offsetY,
    rotationX,
    transformOrigin: 'center center',
    transformStyle: 'preserve-3d',
    perspective: '1000px',
  });

  const animationProps = {
    opacity: 1,
    y: 0,
    rotationX: 0,
    duration,
    delay,
    ease: 'cubic-bezier(0.22, 1, 0.36, 1)',
  };

  if (wordDelay > 0) {
    animationProps.stagger = wordDelay;
  }

  if (!instant) {
    animationProps.scrollTrigger = {
      trigger: elem,
      start,
      end,
      once: true,
    };
  }

  gsap.to(wordSpans, animationProps);
};

const headingTextReveal = {
  init() {
    document.querySelectorAll('[data-opai-text-reveal]').forEach(revealElement);
  },
};

document.addEventListener('DOMContentLoaded', () => {
  headingTextReveal.init();
});
