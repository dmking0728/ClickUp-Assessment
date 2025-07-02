<?php
/**
 * Pricing Table Template
 * 
 * Available variables:
 * $selected_plans - array of plan slugs to display
 * $show_banner - boolean for banner display
 * $banner_text - string for banner text
 * $banner_subtext - string for banner subtext
 * $cta_text - string for CTA button text
 * $pricing_data - array of pricing data from API
 */

// Debug: Log template execution
error_log('Template executed. Pricing data type: ' . gettype($pricing_data));
if (is_array($pricing_data)) {
    error_log('Template: Pricing data keys: ' . implode(', ', array_keys($pricing_data)));
    error_log('Template: Has pricingV3TierCards: ' . (isset($pricing_data['pricingV3TierCards']) ? 'YES' : 'NO'));
}

// Ensure we have data
if (!$pricing_data || !isset($pricing_data['pricingV3TierCards'])) {
    error_log('Template: Pricing data check failed');
    echo '<div class="cu-pricing-error">Pricing data unavailable</div>';
    return;
}

$tier_cards = $pricing_data['pricingV3TierCards'];
$filtered_plans = array();

// Create a mapping for plan names to make matching easier
$plan_name_mapping = array(
    'free' => 'free forever',
    'unlimited' => 'unlimited', 
    'business' => 'business',
    'enterprise' => 'enterprise'
);

// Filter plans based on selection
foreach ($tier_cards as $plan) {
    $plan_tier = strtolower($plan['tier']);
    
    // Check if this plan is selected
    foreach ($selected_plans as $selected_plan) {
        if (isset($plan_name_mapping[$selected_plan]) && 
            $plan_name_mapping[$selected_plan] === $plan_tier) {
            $filtered_plans[] = $plan;
            break;
        }
    }
}

if (empty($filtered_plans)) {
    echo '<div class="cu-pricing-error">No plans selected or plans not found</div>';
    return;
}
?>

<div class="cu-pricing-table-wrapper" id="cu-pricing-table-<?php echo uniqid(); ?>">
    <div class="cu-pricing-table">
        <div class="cu-pricing-plans">
            <?php foreach ($filtered_plans as $index => $plan): ?>
                <?php 
                $is_popular = strtolower($plan['tier']) === 'business'; // Business plan is typically popular
                $is_unlimited = strtolower($plan['tier']) === 'unlimited';
                $is_free = strtolower($plan['tier']) === 'free forever';
                $is_enterprise = strtolower($plan['tier']) === 'enterprise';
                ?>
                
                <div class="cu-pricing-plan<?php echo $is_popular ? ' cu-plan-popular' : ''; ?>">
                    <?php if ($is_popular): ?>
                        <div class="cu-plan-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16" height="16" viewBox="0 0 24 24" style="margin-right: 6px;">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 
                            6.5 3.5 5 5.5 5c1.54 0 3.04.99 3.57 
                            2.36h1.87C13.46 5.99 14.96 5 16.5 
                            5 18.5 5 20 6.5 20 8.5c0 3.78-3.4 
                            6.86-8.55 11.54L12 21.35z" />
                            </svg>    
                        Most Popular</div>
                    <?php endif; ?>
                    
                    <div class="cu-plan-header">
                        <h3 class="cu-plan-name"><?php echo esc_html(ucwords($plan['tier'])); ?></h3>
                        <div class="cu-plan-description">
                            <?php echo esc_html($plan['tierSubText']); ?>
                        </div>
                        <div class="cu-plan-price">
                            <?php if ($is_free): ?>
                                <span class="cu-price-amount free">Free</span>
                            <?php elseif ($is_enterprise): ?>
                                <span class="cu-price-amount enterprise"><?php echo esc_html($enterprise_description ?: 'Get a custom demo and see how ClickUp aligns with your goals.'); ?></span>
                            <?php else: ?>
                                <span class="cu-price-currency">$</span>
                                <span class="cu-price-amount">
                                    <?php echo esc_html(str_replace('$', '', $plan['priceMonthly'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                    </div>

                    <div class="cu-plan-footer">
                        <?php 
                        // Handle button text with proper fallbacks
                        if ($is_free) {
                            $button_text = $plan['ctaButton']; // "Free forever"
                        } elseif ($is_enterprise) {
                            $button_text = $plan['ctaButton']; // "Contact sales"
                        } else {
                            // Use the CTA text (which already has fallback to "Get started")
                            $button_text = $cta_text;
                        }
                        ?>
                        <a 
                            href="https://clickup.com/pricing" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="cu-cta-button <?php echo $is_popular ? 'cu-cta-primary' : 'cu-cta-secondary'; ?>"
                        >
                            <?php echo esc_html($button_text); ?>
                        </a>

                        <?php if ($is_unlimited || $is_popular): ?>
                            <p class="cu-plan-footer-text">Billed per user per month</p>
                        <?php endif ?>
                    </div>

                    <div class="cu-plan-features">
                        <?php if ($is_free): ?>
                            <p class="cu-features-list-header">Key Features:</p>
                        <?php elseif ($is_unlimited): ?>
                            <p class="cu-features-list-header">Everything in Free Forever, plus:</p>
                        <?php elseif ($is_popular): ?>
                            <p class="cu-features-list-header">Everything in Unlimited, plus:</p>
                        <?php elseif ($is_enterprise): ?>
                            <p class="cu-features-list-header">Everything in <span class="highlight">Business</span>, plus:</p>
                        <?php endif; ?>
                        <ul class="cu-features-list" data-all-features='<?php echo json_encode(array_column($plan["features"], "feature")); ?>'>

                            <?php 
                            // Limit to 5 features as requested
                            $features = array_slice($plan['features'], 0, 5);
                            foreach ($features as $feature): 
                            ?>
                                <li class="cu-feature-item">
                                    <span class="cu-feature-icon">✓</span>
                                    <span class="cu-feature-text"><?php echo esc_html($feature['feature']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
            <button class="cu-see-more-features" id="cu-see-more-btn">See more features</button>
        </div>
    </div>

    <?php if ($show_banner): ?>
    <div class="cu-ai-banner">
        <div class="cu-ai-banner-content">
            <div class="cu-ai-banner-text">
                <span class="cu-ai-banner-title"><?php echo esc_html($banner_text ?: "The world's most complete work AI, starting at $9 per month"); ?></span>
                <span class="cu-ai-banner-subtitle"><?php echo esc_html($banner_subtext ?: "ClickUp Brain is a no Brainer. One AI to manage your work, at a fraction of the cost."); ?></span>
            </div>
            <a href="https://clickup.com/ai" target="_blank" rel="noopener noreferrer" class="cu-ai-banner-cta">
                <?php echo esc_html($banner_cta_text ?: 'Try for free'); ?>
            </a>
        </div>
    </div>
<?php endif; ?>
</div>