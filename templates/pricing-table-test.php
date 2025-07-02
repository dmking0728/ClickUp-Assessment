<?php
// Simple test template
error_log('Simple template executed');

echo '<div class="cu-pricing-table-wrapper">';
echo '<h3>ClickUp Pricing Test</h3>';

if (!$pricing_data) {
    echo '<p>No pricing data</p>';
    return;
}

if (!isset($pricing_data['pricingV3TierCards'])) {
    echo '<p>No tier cards found</p>';
    return;
}

echo '<p>Found ' . count($pricing_data['pricingV3TierCards']) . ' plans:</p>';

foreach ($pricing_data['pricingV3TierCards'] as $plan) {
    echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px;">';
    echo '<h4>' . esc_html($plan['tier']) . '</h4>';
    echo '<p>Price: ' . esc_html($plan['priceMonthly']) . '</p>';
    echo '</div>';
}

echo '</div>';
?>