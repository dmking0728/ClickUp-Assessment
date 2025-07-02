<?php
/**
 * Plugin Name: ClickUp Pricing Table
 * Description: Dynamic pricing table block that fetches live data from ClickUp API
 * Version: 1.0.0
 * Author: Your Name
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
                'showBanner' => array(
                    'type' => 'boolean',
                    'default' => false
                ),
                'bannerText' => array(
                    'type' => 'string',
                    'default' => 'AI-powered features available'
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
        
        error_log('Enqueuing frontend CSS: ' . $css_file);
        error_log('CSS file exists: ' . (file_exists(CLICKUP_PRICING_TABLE_PLUGIN_DIR . 'build/style-frontend.css') ? 'YES' : 'NO'));
        
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
        $show_banner = $attributes['showBanner'];
        $banner_text = $attributes['bannerText'];
        $cta_text = $attributes['ctaText'];
        
        // Get cached pricing data
        $pricing_data = $this->get_pricing_data();
        
        if (!$pricing_data) {
            return '<div class="clickup-pricing-error">Unable to load pricing data</div>';
        }
        
        error_log('Template path: ' . CLICKUP_PRICING_TABLE_PLUGIN_DIR . 'templates/pricing-table.php');
        error_log('Template exists: ' . (file_exists(CLICKUP_PRICING_TABLE_PLUGIN_DIR . 'templates/pricing-table.php') ? 'YES' : 'NO'));
        
        ob_start();
        $template_result = include CLICKUP_PRICING_TABLE_PLUGIN_DIR . 'templates/pricing-table.php';
        $output = ob_get_clean();
        
        error_log('Template include result: ' . var_export($template_result, true));
        error_log('Template output length: ' . strlen($output));
        
        if (empty($output)) {
            error_log('Template produced no output');
            return '<div class="cu-pricing-error">Template error - check logs</div>';
        }
        
        return $output;
    }
    
    public function get_pricing_data() {
        // Check for cached data first
        $cached_data = get_transient('clickup_pricing_data');
        if ($cached_data !== false) {
            error_log('ClickUp: Using cached pricing data');
            return $cached_data;
        }
        
        error_log('ClickUp: Fetching fresh pricing data from API');
        
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
        error_log('ClickUp API Response Code: ' . $response_code);
        
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
            error_log('ClickUp Pricing API: Invalid JSON response');
            error_log('JSON Error: ' . json_last_error_msg());
            error_log('Raw response (first 500 chars): ' . substr($body, 0, 500));
            return false;
        }
        
        // Verify the expected structure exists
        if (!isset($data['pricingV3TierCards'])) {
            error_log('ClickUp API: Missing pricingV3TierCards in response');
            error_log('Available keys: ' . implode(', ', array_keys($data)));
            return false;
        }
        
        error_log('ClickUp: Successfully fetched pricing data with ' . count($data['pricingV3TierCards']) . ' tier cards');
        
        // Cache for 1 hour
        set_transient('clickup_pricing_data', $data, HOUR_IN_SECONDS);
        
        return $data;
    }
    
    public function conditional_enqueue() {
        global $post;
        
        if (is_singular() && has_block('clickup/pricing-table', $post)) {
            error_log('Found ClickUp pricing block on page, force enqueuing styles');
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