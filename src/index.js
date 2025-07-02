import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    ToggleControl, 
    TextControl,
    Spinner,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

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
        showBanner: {
            type: 'boolean',
            default: false
        },
        bannerText: {
            type: 'string',
            default: "The world's most complete work AI, starting at $9 per month"
        },
        bannerSubtext: {
            type: 'string',
            default: 'ClickUp Brain is a no Brainer. One AI to manage your work, at a fraction of the cost.'
        },
        bannerCtaText: {
            type: 'string',
            default: 'Try for free'
        },
        enterpriseDescription: {
            type: 'string',
            default: 'Get a custom demo and see how ClickUp aligns with your goals.'
        },
        ctaText: {
            type: 'string',
            default: 'Get started'
        }
    },

    edit: function({ attributes, setAttributes }) {
        const { showBanner, bannerText, bannerSubtext, bannerCtaText, enterpriseDescription, ctaText } = attributes;
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

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('Banner Settings', 'cu-pricing-table')}>
                        <ToggleControl
                            label={__('Show AI Banner', 'cu-pricing-table')}
                            checked={showBanner}
                            onChange={(value) => setAttributes({ showBanner: value })}
                        />
                        
                        {showBanner && (
                            <>
                                <TextControl
                                    label={__('Banner Text', 'cu-pricing-table')}
                                    value={bannerText}
                                    onChange={(value) => setAttributes({ bannerText: value })}
                                />
                                
                                <TextControl
                                    label={__('Banner Subtext', 'cu-pricing-table')}
                                    value={bannerSubtext}
                                    onChange={(value) => setAttributes({ bannerSubtext: value })}
                                />
                                
                                <TextControl
                                    label={__('Banner CTA Button Text', 'cu-pricing-table')}
                                    value={bannerCtaText}
                                    onChange={(value) => setAttributes({ bannerCtaText: value })}
                                />
                            </>
                        )}
                    </PanelBody>
                    
                    <PanelBody title={__('Pricing Plans Settings', 'cu-pricing-table')}>
                        <TextControl
                            label={__('Enterprise Plan Description', 'cu-pricing-table')}
                            value={enterpriseDescription}
                            onChange={(value) => setAttributes({ enterpriseDescription: value })}
                            help={__('Custom description text for the Enterprise plan pricing area', 'cu-pricing-table')}
                        />
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
                            
                            {/* Show banner preview at the top */}
                            {showBanner && (
                                <div className="cu-banner-preview">
                                    <strong>{__('Banner:', 'cu-pricing-table')}</strong> {bannerText || "The world's most complete work AI, starting at $9 per month"}
                                    <br />
                                    <strong>{__('Subtext:', 'cu-pricing-table')}</strong> {bannerSubtext || "ClickUp Brain is a no Brainer. One AI to manage your work, at a fraction of the cost."}
                                    <br />
                                    <strong>{__('CTA Button:', 'cu-pricing-table')}</strong> {bannerCtaText || 'Try for free'}
                                </div>
                            )}
                            
                            <p>{__('Displaying all ClickUp pricing plans', 'cu-pricing-table')}</p>
                            
                            {/* Server-side render with banner disabled for preview */}
                            <ServerSideRender
                                block="clickup/pricing-table"
                                attributes={{
                                    ...attributes,
                                    showBanner: false // Disable banner in server render to avoid duplication
                                }}
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