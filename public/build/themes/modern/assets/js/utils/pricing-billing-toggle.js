const initPricingBillingToggle = () => {
  document.querySelectorAll('.price-scope').forEach((scope) => {
    const toggle = scope.querySelector('[data-pricing-billing-toggle]');
    const priceBlocks = scope.querySelectorAll('[data-pricing-price]');
    if (!toggle || !priceBlocks.length) return;

    const setPrice = (block, isYearly) => {
      const flow = block.querySelector('[data-pricing-number]');
      if (!flow?.update) return;

      const value = Number(
        isYearly ? block.dataset.pricingYearly : block.dataset.pricingMonthly,
      );
      const period = block.querySelector('[data-pricing-period]');

      if (period) period.textContent = isYearly ? 'yr' : 'mo';

      flow.transformTiming = { duration: 1200, easing: 'ease-out' };
      flow.spinTiming = { duration: 1200, easing: 'ease-out' };
      flow.update(value);
    };

    priceBlocks.forEach((block) => {
      const flow = block.querySelector('[data-pricing-number]');
      if (!flow?.update) return;

      flow.format = {
        useGrouping: true,
        maximumFractionDigits: 0,
        minimumFractionDigits: 0,
      };
      flow.update(Number(block.dataset.pricingMonthly) || 0);
    });

    toggle.addEventListener('change', () => {
      const isYearly = toggle.checked;
      priceBlocks.forEach((block) => setPrice(block, isYearly));
    });
  });
};

const bootPricingBillingToggle = () => {
  if (customElements.get('number-flow')) {
    initPricingBillingToggle();
    return;
  }

  customElements.whenDefined('number-flow').then(initPricingBillingToggle);
};

document.addEventListener('DOMContentLoaded', bootPricingBillingToggle);
