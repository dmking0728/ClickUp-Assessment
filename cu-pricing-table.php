<?php
/**
 * Plugin Name: ClickUp Pricing Table
 * Description: Dynamic pricing table block that fetches live data from ClickUp API
 * Version: 1.0.0
 * Author: Daniel King
 * Text Domain: clickup-pricing-table
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CLICKUP_PRICING_TABLE_VERSION', '1.0.0');
define('CLICKUP_PRICING_TABLE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLICKUP_PRICING_TABLE_PLUGIN_URL', plugin_dir_url(__FILE__));

class ClickUpPricingTable {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'));
        add_action('wp_ajax_clickup_get_pricing', array($this, 'ajax_get_pricing'));
        add_action('wp_ajax_nopriv_clickup_get_pricing', array($this, 'ajax_get_pricing'));
        
        // Force enqueue on pages with our block
        add_action('wp_enqueue_scripts', array($this, 'conditional_enqueue'), 20);
    }
    
    public function init() {
        // Register the block
        register_block_type('clickup/pricing-table', array(
            'attributes' => array(
                'selectedPlans' => array(
                    'type' => 'array',
                    'default' => array('free', 'unlimited', 'business', 'enterprise')
                ),
                'enterpriseDescription' => array(
                    'type' => 'string',
                    'default' => 'Get a custom demo and see how ClickUp aligns with your goals.'
                ),
                'showBanner' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'bannerText' => array(
                    'type' => 'string',
                    'default' => "The world's most complete work AI, starting at $9 per month"
                ),
                'bannerSubtext' => array(
                    'type' => 'string',
                    'default' => 'ClickUp Brain is a no Brainer. One AI to manage your work, at a fraction of the cost.'
                ),
                'bannerCtaText' => array(
                    'type' => 'string',
                    'default' => 'Try for free'
                ),
                'ctaText' => array(
                    'type' => 'string',
                    'default' => 'Get started'
                )
            ),
            'render_callback' => array($this, 'render_block'),
        ));
    }
    
    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'clickup-pricing-table-editor',
            CLICKUP_PRICING_TABLE_PLUGIN_URL . 'build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            CLICKUP_PRICING_TABLE_VERSION
        );
        
        wp_enqueue_style(
            'clickup-pricing-table-editor',
            CLICKUP_PRICING_TABLE_PLUGIN_URL . 'build/index.css',
            array(),
            CLICKUP_PRICING_TABLE_VERSION
        );
        
        // Localize script for AJAX
        wp_localize_script('clickup-pricing-table-editor', 'clickupPricing', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('clickup_pricing_nonce')
        ));
    }
    
    public function enqueue_frontend_assets() {
        $css_file = CLICKUP_PRICING_TABLE_PLUGIN_URL . 'build/style-frontend.css';
        $js_file = CLICKUP_PRICING_TABLE_PLUGIN_URL . 'build/frontend.js';
        
        wp_enqueue_style(
            'clickup-pricing-table',
            $css_file,
            array(),
            CLICKUP_PRICING_TABLE_VERSION
        );
        
        wp_enqueue_script(
            'clickup-pricing-table',
            $js_file,
            array('jquery'),
            CLICKUP_PRICING_TABLE_VERSION,
            true
        );
    }
    
    public function render_block($attributes) {
        $selected_plans = $attributes['selectedPlans'];
        $enterprise_description = $attributes['enterpriseDescription'];
        $show_banner = $attributes['showBanner'];
        $banner_text = $attributes['bannerText'];
        $banner_subtext = $attributes['bannerSubtext'];
        $banner_cta_text = $attributes['bannerCtaText'];
        
        // Handle empty CTA text with proper fallback
        $cta_text = !empty($attributes['ctaText']) ? $attributes['ctaText'] : 'Get started';
        
        // Get cached pricing data
        $pricing_data = $this->get_pricing_data();
        
        if (!$pricing_data) {
            return '<div class="clickup-pricing-error">Unable to load pricing data</div>';
        }
        
        ob_start();
        $template_result = include CLICKUP_PRICING_TABLE_PLUGIN_DIR . 'templates/pricing-table.php';
        $output = ob_get_clean();
        
        // Only log if there's actually an error
        if (empty($output)) {
            error_log('ClickUp Pricing Table: Template produced no output');
            return '<div class="cu-pricing-error">Template error - please check plugin installation</div>';
        }
        
        return $output;
    }
    
    public function get_pricing_data() {
        // Check for cached data first
        $cached_data = get_transient('clickup_pricing_data');
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        // Fetch fresh data
        $response = wp_remote_get('https://clickup.com/data/pricing/pricing-en.json', array(
            'timeout' => 10,
            'headers' => array(
                'User-Agent' => 'WordPress/ClickUp-Pricing-Plugin'
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('ClickUp Pricing API Error: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code !== 200) {
            error_log('ClickUp API returned non-200 status: ' . $response_code);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        if (empty($body)) {
            error_log('ClickUp API returned empty response body');
            return false;
        }
        
        $data = json_decode($body, true);
        
        if (!$data) {
            error_log('ClickUp Pricing API: Invalid JSON response - ' . json_last_error_msg());
            return false;
        }
        
        // Verify the expected structure exists
        if (!isset($data['pricingV3TierCards'])) {
            error_log('ClickUp API: Missing pricingV3TierCards in response. Available keys: ' . implode(', ', array_keys($data)));
            return false;
        }
        
        // Cache for 1 hour
        set_transient('clickup_pricing_data', $data, HOUR_IN_SECONDS);
        
        return $data;
    }
    
    public function conditional_enqueue() {
        global $post;
        
        if (is_singular() && has_block('clickup/pricing-table', $post)) {
            $this->enqueue_frontend_assets();
        }
    }
    
    public function ajax_get_pricing() {
        check_ajax_referer('clickup_pricing_nonce', 'nonce');
        
        $data = $this->get_pricing_data();
        
        if ($data) {
            wp_send_json_success($data);
        } else {
            wp_send_json_error('Failed to fetch pricing data');
        }
    }
}

// Initialize the plugin
new ClickUpPricingTable();