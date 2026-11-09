class AccordionManager {
  constructor(options = {}) {
    this.options = {
      // Default options
      allowMultiple: false,
      animationDuration: 300,
      easing: 'ease-in-out',
      autoClose: true,
      keyboardNavigation: true,
      ...options,
    };

    this.accordions = new Map();
    this.init();
  }

  init() {
    document.addEventListener('DOMContentLoaded', () => {
      this.initializeAllAccordions();
    });
  }

  initializeAllAccordions() {
    const containers = document.querySelectorAll('.accordion');
    containers.forEach((container, index) => {
      this.createAccordion(container, index);
    });

    // Ensure only one default-open item per container is open
    this.ensureSingleActiveItem();

    // Add stagger animation for accordion items
    this.addStaggerToAccordionItems();
  }

  createAccordion(container, id) {
    const items = container.querySelectorAll('.accordion-item');
    if (!items.length) return;

    // Add FAQ page schema to container
    container.setAttribute('itemscope', '');
    container.setAttribute('itemtype', 'https://schema.org/FAQPage');
    container.setAttribute('role', 'tablist');
    container.setAttribute('aria-label', 'Frequently Asked Questions');

    const accordion = {
      id,
      container,
      items: new Map(),
      activeItems: new Set(),
      isAnimating: false,
    };

    // Initialize each accordion item
    items.forEach((item, itemIndex) => {
      this.initializeAccordionItem(accordion, item, itemIndex);
    });

    // Set up keyboard navigation
    if (this.options.keyboardNavigation) {
      this.setupKeyboardNavigation(accordion);
    }

    this.accordions.set(id, accordion);
  }

  initializeAccordionItem(accordion, item, itemIndex) {
    const button = item.querySelector('.accordion-action');
    const content = item.querySelector('.accordion-content');

    if (!button || !content) return;

    // Add SEO and accessibility attributes
    this.addSEOAndAccessibilityAttributes(accordion, item, button, content, itemIndex);

    // Detect icon type
    const iconWrapper = item.querySelector('.accordion-action span');
    const svgIcon = item.querySelector('.accordion-action svg');
    const plusIcon = item.querySelector('.accordion-plus-icon');

    let icon = null;
    let iconType = 'none';

    if (svgIcon) {
      icon = svgIcon;
      iconType = 'svg';
    } else if (plusIcon) {
      icon = plusIcon;
      iconType = 'div';
    } else if (iconWrapper?.querySelector('svg')) {
      icon = iconWrapper.querySelector('svg');
      iconType = 'svg-in-span';
    }

    const itemData = {
      item,
      button,
      content,
      icon,
      iconType,
      isOpen: false,
      isAnimating: false,
      itemIndex,
    };

    // Set up initial state
    this.setInitialState(itemData);

    // Add click event listener
    button.addEventListener('click', (e) => {
      e.preventDefault();
      this.toggleItem(accordion, itemData);
    });

    accordion.items.set(itemIndex, itemData);
  }

  setInitialState(itemData) {
    const { item, button, content } = itemData;

    // Only open items that have default-open class
    // Other indicators are ignored to prevent unwanted opening
    const isInitiallyOpen = item.classList.contains('default-open');

    if (isInitiallyOpen) {
      this.openItem(itemData, false); // Open without animation
      // Ensure the accordion container knows this item is active
      const accordion = this.findAccordionByItem(item);
      if (accordion) {
        accordion.activeItems.add(itemData.itemIndex);
      }
      // Apply background color for initially open items
      this.applyBackgroundColor(itemData, 'open');
    } else {
      this.closeItem(itemData, false); // Close without animation
      // Ensure background color is removed for closed items
      this.applyBackgroundColor(itemData, 'closed');
    }
  }

  toggleItem(accordion, itemData) {
    if (accordion.isAnimating || itemData.isAnimating) return;

    if (itemData.isOpen) {
      this.closeItem(itemData);
      accordion.activeItems.delete(itemData.itemIndex);
    } else {
      // Close other items if not allowing multiple
      if (!this.options.allowMultiple) {
        this.closeAllItems(accordion);
      }

      this.openItem(itemData);
      accordion.activeItems.add(itemData.itemIndex);
    }
  }

  openItem(itemData, animate = true) {
    const { item, button, content, icon, iconType } = itemData;

    if (itemData.isAnimating) return;

    itemData.isAnimating = true;
    itemData.isOpen = true;

    // Set up content for animation
    content.style.overflow = 'hidden';
    content.style.transition = `height ${this.options.animationDuration}ms ${this.options.easing}, opacity ${this.options.animationDuration}ms ${this.options.easing}`;

    if (animate) {
      // Measure target height
      content.style.height = 'auto';
      const targetHeight = content.scrollHeight;
      content.style.height = '0px';
      content.style.opacity = '0';

      // Force reflow
      content.offsetHeight;

      // Prepare content for animation and start GSAP SplitText animation
      this.prepareContentForAnimation(content, 'in');
      this.animateContentIn(content);

      // Animate to target height
      requestAnimationFrame(() => {
        content.style.height = `${targetHeight}px`;
        content.style.opacity = '1';
      });

      // Handle animation end
      const handleTransitionEnd = (e) => {
        if (e.propertyName === 'height') {
          content.style.height = 'auto';
          content.removeEventListener('transitionend', handleTransitionEnd);
          itemData.isAnimating = false;
        }
      };

      content.addEventListener('transitionend', handleTransitionEnd);
    } else {
      // Set final state without animation
      content.style.height = 'auto';
      content.style.opacity = '1';
      itemData.isAnimating = false;

      // Still animate text content even without height animation
      this.prepareContentForAnimation(content, 'in');
      this.animateContentIn(content);
    }

    // Update icon
    this.updateIcon(itemData, 'open');

    // Update ARIA attributes
    button.setAttribute('aria-expanded', 'true');
    content.setAttribute('aria-hidden', 'false');
    item.setAttribute('aria-expanded', 'true');
    item.setAttribute('aria-selected', 'true');

    // Add active class
    item.classList.add('active');

    // Apply background color if data-active-bg-color is set
    this.applyBackgroundColor(itemData, 'open');
  }

  closeItem(itemData, animate = true) {
    const { item, button, content, icon, iconType } = itemData;

    if (itemData.isAnimating) return;

    itemData.isAnimating = true;
    itemData.isOpen = false;

    // Set up content for animation
    content.style.overflow = 'hidden';
    content.style.transition = `height ${this.options.animationDuration}ms ${this.options.easing}, opacity ${this.options.animationDuration}ms ${this.options.easing}`;

    if (animate) {
      // Prepare content for animation and start GSAP SplitText animation for closing
      this.prepareContentForAnimation(content, 'out');
      this.animateContentOut(content);

      // Get current height
      const currentHeight = content.scrollHeight;
      content.style.height = `${currentHeight}px`;

      // Force reflow
      content.offsetHeight;

      // Delay height animation to allow text animation to start
      setTimeout(() => {
        requestAnimationFrame(() => {
          content.style.height = '0px';
          content.style.opacity = '0';
        });
      }, 100); // Small delay to let text animation start

      // Handle animation end
      const handleTransitionEnd = (e) => {
        if (e.propertyName === 'height') {
          content.removeEventListener('transitionend', handleTransitionEnd);
          itemData.isAnimating = false;
          // Clean up SplitText instances after close animation
          this.cleanupSplitText(content);
        }
      };

      content.addEventListener('transitionend', handleTransitionEnd);
    } else {
      // Set final state without animation
      content.style.height = '0px';
      content.style.opacity = '0';
      itemData.isAnimating = false;
      // Clean up SplitText instances
      this.cleanupSplitText(content);
    }

    // Update icon
    this.updateIcon(itemData, 'closed');

    // Update ARIA attributes
    button.setAttribute('aria-expanded', 'false');
    content.setAttribute('aria-hidden', 'true');
    item.setAttribute('aria-expanded', 'false');
    item.setAttribute('aria-selected', 'false');

    // Remove active class
    item.classList.remove('active');

    // Remove background color if data-active-bg-color was set
    this.applyBackgroundColor(itemData, 'closed');
  }

  closeAllItems(accordion) {
    accordion.items.forEach((itemData) => {
      if (itemData.isOpen) {
        // Clean up SplitText instances before closing
        this.cleanupSplitText(itemData.content);
        this.closeItem(itemData);
        accordion.activeItems.delete(itemData.itemIndex);
      }
    });
  }

  findAccordionByItem(item) {
    for (const accordion of this.accordions.values()) {
      for (const itemData of accordion.items.values()) {
        if (itemData.item === item) {
          return accordion;
        }
      }
    }
    return null;
  }

  ensureSingleActiveItem() {
    // For each accordion container, ensure only one item with default-open is open
    this.accordions.forEach((accordion) => {
      const activeItems = Array.from(accordion.items.values()).filter((itemData) =>
        itemData.item.classList.contains('default-open')
      );

      // If multiple items have default-open class, close all but the first one
      if (activeItems.length > 1) {
        activeItems.slice(1).forEach((itemData) => {
          itemData.item.classList.remove('default-open');
          this.closeItem(itemData, false);
          accordion.activeItems.delete(itemData.itemIndex);
        });
      }

      // If no items have default-open class, ensure all items are closed
      if (activeItems.length === 0) {
        accordion.items.forEach((itemData) => {
          if (itemData.isOpen) {
            this.closeItem(itemData, false);
            accordion.activeItems.delete(itemData.itemIndex);
          }
        });
      }
    });
  }

  updateIcon(itemData, state) {
    const { icon, iconType } = itemData;

    if (!icon) return;

    if (iconType === 'svg' || iconType === 'svg-in-span') {
      // Use GSAP if available, otherwise use CSS transitions
      if (typeof gsap !== 'undefined') {
        gsap.killTweensOf(icon);
        gsap.to(icon, {
          rotation: state === 'open' ? 180 : 0,
          duration: this.options.animationDuration / 1000,
          ease: state === 'open' ? 'power3.out' : 'power3.in',
        });
      } else {
        icon.style.transform = state === 'open' ? 'rotate(180deg)' : 'rotate(0deg)';
        icon.style.transition = `transform ${this.options.animationDuration}ms ${this.options.easing}`;
      }
    } else if (iconType === 'div') {
      // Handle div-based icons (like plus/minus)
      if (state === 'open') {
        itemData.item.classList.add('active');
      } else {
        itemData.item.classList.remove('active');
      }
    }
  }

  applyBackgroundColor(itemData, state) {
    const { item } = itemData;
    const bgColor = item.getAttribute('data-active-bg-color');

    if (!bgColor) return;

    // Ensure smooth transition for background color
    item.style.transition = `background-color ${this.options.animationDuration}ms ${this.options.easing}`;

    if (state === 'open') {
      // Apply the background color with smooth transition
      if (typeof gsap !== 'undefined') {
        gsap.killTweensOf(item);
        gsap.to(item, {
          backgroundColor: bgColor,
          duration: this.options.animationDuration / 1000,
          ease: 'power2.out',
        });
      } else {
        item.style.backgroundColor = bgColor;
      }
    } else {
      // Remove background color with smooth transition
      if (typeof gsap !== 'undefined') {
        gsap.killTweensOf(item);
        gsap.to(item, {
          backgroundColor: 'transparent',
          duration: this.options.animationDuration / 1000,
          ease: 'power2.in',
        });
      } else {
        item.style.backgroundColor = 'transparent';
      }
    }
  }

  animateContentIn(content) {
    // Check if GSAP and SplitText are available
    if (typeof gsap === 'undefined' || typeof SplitText === 'undefined') {
      return;
    }

    // Find all text elements in the content
    const textElements = content.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6');

    if (!textElements.length) return;

    // Calculate timing based on accordion duration
    const accordionDuration = this.options.animationDuration / 1000; // Convert to seconds
    const textDuration = Math.max(0.6, accordionDuration * 0.8); // 80% of accordion duration, min 0.6s

    textElements.forEach((element, index) => {
      // Clean up any existing SplitText instances
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }

      // Kill any existing animations
      gsap.killTweensOf(element);

      // Reset transforms
      gsap.set(element, { clearProps: 'all' });

      // Split text into lines for paragraph elements
      if (element.tagName === 'P' && element.textContent.trim()) {
        const splitText = new SplitText(element, { type: 'lines' });
        const lines = splitText.lines;

        // Store SplitText instance for cleanup
        element._splitTextInstance = splitText;

        // Set initial state
        gsap.set(lines, {
          opacity: 0,
          y: 30,
          rotationX: -90,
        });

        // Animate lines in with stagger, synchronized with accordion duration
        gsap.to(lines, {
          opacity: 1,
          y: 0,
          rotationX: 0,
          duration: textDuration,
          ease: 'power2.out',
          stagger: 0.08,
          delay: index * 0.05,
        });
      } else {
        // For non-paragraph elements, use simple stagger
        gsap.set(element, { opacity: 0, y: 20 });
        gsap.to(element, {
          opacity: 1,
          y: 0,
          duration: textDuration * 0.8,
          ease: 'power2.out',
          delay: index * 0.03,
        });
      }
    });
  }

  animateContentOut(content) {
    // Check if GSAP and SplitText are available
    if (typeof gsap === 'undefined' || typeof SplitText === 'undefined') {
      return;
    }

    // Find all text elements in the content
    const textElements = content.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6');

    if (!textElements.length) return;

    // Calculate timing based on accordion duration
    const accordionDuration = this.options.animationDuration / 1000; // Convert to seconds
    const textDuration = Math.max(0.3, accordionDuration * 0.6); // 60% of accordion duration, min 0.3s

    textElements.forEach((element, index) => {
      // Kill any existing animations
      gsap.killTweensOf(element);

      // Split text into lines for paragraph elements
      if (element.tagName === 'P' && element.textContent.trim()) {
        const splitText = new SplitText(element, { type: 'lines' });
        const lines = splitText.lines;

        // Store SplitText instance for cleanup
        element._splitTextInstance = splitText;

        // Animate lines out with stagger, synchronized with accordion duration
        gsap.to(lines, {
          opacity: 0,
          y: -20,
          rotationX: 90,
          duration: textDuration,
          ease: 'power2.in',
          stagger: 0.03,
          delay: index * 0.02,
        });
      } else {
        // For non-paragraph elements, use simple stagger
        gsap.to(element, {
          opacity: 0,
          y: -15,
          duration: textDuration * 0.8,
          ease: 'power2.in',
          delay: index * 0.02,
        });
      }
    });
  }

  cleanupSplitText(content) {
    // Check if GSAP and SplitText are available
    if (typeof gsap === 'undefined' || typeof SplitText === 'undefined') {
      return;
    }

    const textElements = content.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6');

    textElements.forEach((element) => {
      // Kill any existing animations
      gsap.killTweensOf(element);

      // Clean up SplitText instances
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }

      // Reset transforms
      gsap.set(element, { clearProps: 'all' });
    });
  }

  // Prevent animation conflicts by ensuring clean state
  prepareContentForAnimation(content, direction = 'in') {
    // Check if GSAP and SplitText are available
    if (typeof gsap === 'undefined' || typeof SplitText === 'undefined') {
      return;
    }

    const textElements = content.querySelectorAll('p, span, div, h1, h2, h3, h4, h5, h6');

    textElements.forEach((element) => {
      // Kill any existing animations first
      gsap.killTweensOf(element);

      // Clean up existing SplitText instances
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }

      // Reset all transforms and properties
      gsap.set(element, {
        clearProps: 'all',
        opacity: direction === 'in' ? 0 : 1,
      });
    });
  }

  addSEOAndAccessibilityAttributes(accordion, item, button, content, itemIndex) {
    // Add FAQ schema.org structured data
    item.setAttribute('itemscope', '');
    item.setAttribute('itemprop', 'mainEntity');
    item.setAttribute('itemtype', 'https://schema.org/Question');

    // Add accessibility attributes
    item.setAttribute('role', 'tab');
    item.setAttribute('tabindex', '0');

    // Set initial ARIA states
    const isInitiallyOpen = item.classList.contains('default-open');
    item.setAttribute('aria-expanded', isInitiallyOpen ? 'true' : 'false');
    item.setAttribute('aria-selected', isInitiallyOpen ? 'true' : 'false');

    // Add unique IDs for accessibility
    const questionId = `faq-question-${accordion.id}-${itemIndex}`;
    const answerId = `faq-answer-${accordion.id}-${itemIndex}`;

    button.setAttribute('id', questionId);
    button.setAttribute('aria-controls', answerId);
    button.setAttribute('aria-expanded', isInitiallyOpen ? 'true' : 'false');

    content.setAttribute('id', answerId);
    content.setAttribute('role', 'tabpanel');
    content.setAttribute('aria-labelledby', questionId);
    content.setAttribute('aria-hidden', isInitiallyOpen ? 'false' : 'true');

    // Add schema.org attributes to content
    content.setAttribute('itemscope', '');
    content.setAttribute('itemprop', 'acceptedAnswer');
    content.setAttribute('itemtype', 'https://schema.org/Answer');

    // Add schema.org attributes to question text
    // Preserve existing button structure and just add itemprop to the button itself
    button.setAttribute('itemprop', 'name');

    // Add schema.org attributes to answer text
    const answerText = content.querySelector('p');
    if (answerText) {
      answerText.setAttribute('itemprop', 'text');
    }

    // Add aria-hidden to decorative icons
    const plusIcon = item.querySelector('.accordion-plus-icon');
    if (plusIcon) {
      plusIcon.setAttribute('aria-hidden', 'true');
    }
  }

  setupKeyboardNavigation(accordion) {
    accordion.container.addEventListener('keydown', (e) => {
      if (!e.target.closest('.accordion-action')) return;

      const items = Array.from(accordion.items.values());
      const currentIndex = items.findIndex((item) => item.button === e.target);

      if (currentIndex === -1) return;

      switch (e.key) {
        case 'ArrowDown':
          e.preventDefault();
          const nextIndex = (currentIndex + 1) % items.length;
          items[nextIndex].button.focus();
          break;

        case 'ArrowUp':
          e.preventDefault();
          const prevIndex = currentIndex === 0 ? items.length - 1 : currentIndex - 1;
          items[prevIndex].button.focus();
          break;

        case 'Home':
          e.preventDefault();
          items[0].button.focus();
          break;

        case 'End':
          e.preventDefault();
          items[items.length - 1].button.focus();
          break;

        case 'Enter':
        case ' ':
          e.preventDefault();
          this.toggleItem(accordion, items[currentIndex]);
          break;
      }
    });
  }

  addStaggerToAccordionItems() {
    const accordionContainers = document.querySelectorAll('.accordion');

    accordionContainers.forEach((container) => {
      const items = container.querySelectorAll('.accordion-item');
      if (!items.length) return;

      // Add stagger delay to each accordion item
      items.forEach((item, index) => {
        const staggerDelay = index * 0.1; // 0.1s delay between each item
        item.setAttribute('data-opai-animate', '');
        item.setAttribute('data-delay', staggerDelay.toString());
        item.setAttribute('data-accordion-stagger', 'true');
      });
    });
  }

  // Public API methods
  openItemById(accordionId, itemIndex) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;

    const itemData = accordion.items.get(itemIndex);
    if (!itemData) return;

    this.toggleItem(accordion, itemData);
  }

  closeItemById(accordionId, itemIndex) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;

    const itemData = accordion.items.get(itemIndex);
    if (!itemData || !itemData.isOpen) return;

    this.toggleItem(accordion, itemData);
  }

  openAllItems(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;

    accordion.items.forEach((itemData) => {
      if (!itemData.isOpen) {
        this.openItem(itemData);
        accordion.activeItems.add(itemData.itemIndex);
      }
    });
  }

  closeAllItemsById(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;

    this.closeAllItems(accordion);
  }

  destroy(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;

    // Remove event listeners and clean up
    accordion.items.forEach((itemData) => {
      const { button, content } = itemData;
      button.removeEventListener('click', this.toggleItem);
      // Clean up SplitText instances
      this.cleanupSplitText(content);
    });

    this.accordions.delete(accordionId);
  }

  destroyAll() {
    this.accordions.forEach((accordion, id) => {
      this.destroy(id);
    });
  }
}

// Auto-initialize when DOM is ready
const accordionManager = new AccordionManager({
  allowMultiple: false,
  animationDuration: 300,
  easing: 'ease-in-out',
  autoClose: true,
  keyboardNavigation: true,
});
