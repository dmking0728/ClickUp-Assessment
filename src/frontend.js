// Import the frontend styles
import './style.scss';

// Frontend JavaScript for ClickUp Pricing Table
document.addEventListener('DOMContentLoaded', function () {
  // Expand all features on table click
const seeMoreBtn = document.getElementById('cu-see-more-btn');
if (seeMoreBtn) {
  seeMoreBtn.addEventListener('click', () => {
    const plans = document.querySelectorAll('.cu-pricing-plan');
    const pricingTable = document.querySelector('.cu-pricing-table'); // ✅ select the table container

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

    
    if (pricingTable) {
      pricingTable.classList.add('open');
    }

    // Hide the "See more" button
    seeMoreBtn.style.display = 'none';
  });
}

});
