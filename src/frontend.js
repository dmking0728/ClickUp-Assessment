// Import the frontend styles
import './style.scss';

// Frontend JavaScript for ClickUp Pricing Table
document.addEventListener('DOMContentLoaded', function () {
  // Add click tracking for analytics (optional)
  const ctaButtons = document.querySelectorAll('.cu-cta-button');

  ctaButtons.forEach(button => {
    button.addEventListener('click', function () {
      const planCard = this.closest('.cu-pricing-plan');
      const planName = planCard.querySelector('.cu-plan-name')?.textContent;

      if (typeof gtag !== 'undefined') {
        gtag('event', 'click', {
          'event_category': 'pricing_table',
          'event_label': planName,
          'value': 1
        });
      }

      console.log('CTA clicked for plan:', planName);
    });
  });

  // Add smooth hover animations
  const planCards = document.querySelectorAll('.cu-pricing-plan');
  planCards.forEach(card => {
    card.addEventListener('mouseenter', function () {
      this.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
  });

  // Handle responsive behavior
  function handleResize() {
    const tables = document.querySelectorAll('.cu-pricing-table');
    const windowWidth = window.innerWidth;

    tables.forEach(table => {
      if (windowWidth < 768) {
        table.classList.add('cu-mobile-view');
      } else {
        table.classList.remove('cu-mobile-view');
      }
    });
  }

  handleResize();
  window.addEventListener('resize', handleResize);

  // Expand all features on table click
  const seeMoreBtn = document.getElementById('cu-see-more-btn');
if (seeMoreBtn) {
  seeMoreBtn.addEventListener('click', () => {
    const plans = document.querySelectorAll('.cu-pricing-plan');

    plans.forEach(plan => {
      const list = plan.querySelector('.cu-features-list');
      const rawData = list.getAttribute('data-all-features') || '[]';
      let allFeatures = [];

      try {
        allFeatures = JSON.parse(rawData);
      } catch (e) {
        console.error('Invalid JSON in data-all-features:', rawData);
      }

      list.innerHTML = '';

      allFeatures.forEach(featureText => {
        const li = document.createElement('li');
        li.className = 'cu-feature-item';
        li.innerHTML = `
          <span class="cu-feature-icon">✓</span>
          <span class="cu-feature-text">${featureText}</span>
        `;
        list.appendChild(li);
      });
    });

    // Optional: hide button after expanding
    seeMoreBtn.style.display = 'none';
  });
}
});
