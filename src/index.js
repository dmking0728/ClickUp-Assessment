import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    CheckboxControl, 
    ToggleControl, 
    TextControl,
    Spinner,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

const PLAN_OPTIONS = [
    { value: 'free', label: 'Free Forever' },
    { value: 'unlimited', label: 'Unlimited' },
    { value: 'business', label: 'Business' },
    { value: 'enterprise', label: 'Enterprise' }
];

registerBlockType('clickup/pricing-table', {
    title: __('ClickUp Pricing Table', 'cu-pricing-table'),
    description: __('Display ClickUp pricing plans with live data', 'cu-pricing-table'),
    category: 'widgets',
    icon: 'money-alt',
    keywords: [
        __('pricing', 'cu-pricing-table'),
        __('clickup', 'cu-pricing-table'),
        __('table', 'cu-pricing-table'),
    ],
    attributes: {
        selectedPlans: {
            type: 'array',
            default: ['free', 'unlimited', 'business', 'enterprise']
        },
        showBanner: {
            type: 'boolean',
            default: false
        },
        bannerText: {
            type: 'string',
            default: 'AI-powered features available'
        },
        ctaText: {
            type: 'string',
            default: 'See more features'
        }
    },

    edit: function({ attributes, setAttributes }) {
        const { selectedPlans, showBanner, bannerText, ctaText } = attributes;
        const [pricingData, setPricingData] = useState(null);
        const [loading, setLoading] = useState(true);
        const [error, setError] = useState(null);

        // Fetch pricing data for preview
        useEffect(() => {
            const fetchPricingData = async () => {
                try {
                    const response = await fetch(clickupPricing.ajaxUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'clickup_get_pricing',
                            nonce: clickupPricing.nonce
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        setPricingData(result.data);
                        setError(null);
                    } else {
                        setError(result.data || 'Failed to fetch pricing data');
                    }
                } catch (err) {
                    setError('Network error: ' + err.message);
                } finally {
                    setLoading(false);
                }
            };

            fetchPricingData();
        }, []);

        const onPlanChange = (planValue, isChecked) => {
            const newPlans = isChecked 
                ? [...selectedPlans, planValue]
                : selectedPlans.filter(plan => plan !== planValue);
            
            setAttributes({ selectedPlans: newPlans });
        };

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Plan Selection', 'cu-pricing-table')}>
                        {PLAN_OPTIONS.map(plan => (
                            <CheckboxControl
                                key={plan.value}
                                label={plan.label}
                                checked={selectedPlans.includes(plan.value)}
                                onChange={(isChecked) => onPlanChange(plan.value, isChecked)}
                            />
                        ))}
                    </PanelBody>
                    
                    <PanelBody title={__('Banner Settings', 'cu-pricing-table')}>
                        <ToggleControl
                            label={__('Show AI Banner', 'cu-pricing-table')}
                            checked={showBanner}
                            onChange={(value) => setAttributes({ showBanner: value })}
                        />
                        
                        {showBanner && (
                            <TextControl
                                label={__('Banner Text', 'cu-pricing-table')}
                                value={bannerText}
                                onChange={(value) => setAttributes({ bannerText: value })}
                            />
                        )}
                    </PanelBody>
                    
                    <PanelBody title={__('CTA Settings', 'cu-pricing-table')}>
                        <TextControl
                            label={__('CTA Button Text', 'cu-pricing-table')}
                            value={ctaText}
                            onChange={(value) => setAttributes({ ctaText: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="cu-pricing-table-editor">
                    {loading && (
                        <div className="cu-pricing-loading">
                            <Spinner />
                            <p>{__('Loading pricing data...', 'cu-pricing-table')}</p>
                        </div>
                    )}

                    {error && (
                        <Notice status="error" isDismissible={false}>
                            {error}
                        </Notice>
                    )}

                    {!loading && !error && (
                        <div className="cu-pricing-preview">
                            <h3>{__('ClickUp Pricing Table Preview', 'cu-pricing-table')}</h3>
                            <p>{__('Selected plans:', 'cu-pricing-table')} {selectedPlans.join(', ')}</p>
                            {showBanner && (
                                <div className="cu-banner-preview">
                                    <strong>{__('Banner:', 'cu-pricing-table')}</strong> {bannerText}
                                </div>
                            )}
                            
                            {/* Server-side render for accurate preview */}
                            <ServerSideRender
                                block="clickup/pricing-table"
                                attributes={attributes}
                            />
                        </div>
                    )}
                </div>
            </>
        );
    },

    save: function() {
        // Server-side rendered block
        return null;
    },
});