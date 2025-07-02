# ClickUp Pricing Table Plugin

A WordPress Gutenberg block plugin that displays live ClickUp pricing data in a responsive, customizable pricing table.

## Features

- **Live Data Integration**: Fetches real-time pricing information from ClickUp's API
- **Gutenberg Block**: Native WordPress block editor integration
- **Responsive Design**: Mobile-friendly pricing table layout
- **Customizable Content**: Editable CTA buttons, banner content, and descriptions
- **Caching**: API responses cached for 1 hour to improve performance
- **Popular Plan Highlighting**: Business plan marked as "Most Popular" with special styling
- **Expandable Features**: "See more features" functionality to show complete feature lists
- **AI Banner**: Optional promotional banner for ClickUp Brain

## Installation

1. Download or clone this plugin to your WordPress plugins directory:
   ```
   wp-content/plugins/clickup-pricing-table/
   ```

2. Install dependencies and build the plugin:
   ```bash
   npm install
   npm run build
   ```

3. Activate the plugin through the WordPress admin dashboard

## Usage

### Adding the Block

1. Edit any post or page in WordPress
2. Click the "+" button to add a new block
3. Search for "ClickUp Pricing Table"
4. Insert the block

### Block Settings

The block includes several customizable options accessible via the block sidebar:

#### Banner Settings
- **Show AI Banner**: Toggle to display/hide the promotional banner
- **Banner Text**: Main banner headline
- **Banner Subtext**: Secondary banner text
- **Banner CTA Button Text**: Call-to-action button text

#### Pricing Plans Settings
- **Enterprise Plan Description**: Custom text for the Enterprise plan pricing area

#### CTA Settings
- **CTA Button Text**: Default button text for Unlimited and Business plans

### Default Configuration

The plugin displays all four ClickUp plans by default:
- **Free Forever**: Free plan with basic features
- **Unlimited**: Paid plan with expanded features
- **Business**: Most popular plan with advanced features (highlighted)
- **Enterprise**: Custom pricing with enterprise features

## API Integration

### Data Source
The plugin fetches pricing data from:
```
https://clickup.com/data/pricing/pricing-en.json
```

### Caching
- API responses are cached for 1 hour using WordPress transients
- Cache key: `clickup_pricing_data`
- Failed requests are logged to WordPress error log

### Error Handling
- Graceful fallback for API failures
- User-friendly error messages
- Detailed error logging for debugging

## Customization

### Styling
The plugin uses CSS custom properties for easy theme customization:

```css
:root {
  --cu-business-color: #7b68ee;
  --cu-border-light: #e1e5e9;
  /* Add more custom properties as needed */
}
```

### Frontend JavaScript
The frontend includes expandable features functionality. Users can click "See more features" to view complete feature lists for all plans.

## Development

### Building Assets
```bash
# Development build with watch
npm run start

# Production build
npm run build

# Linting
npm run lint:js
npm run lint:css
```

### File Structure
- `src/index.js`: Block registration and editor interface
- `src/frontend.js`: Frontend JavaScript functionality
- `src/editor.scss`: Editor-specific styles
- `src/style.scss`: Frontend styles
- `templates/pricing-table.php`: Server-side rendering template

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- IE11+ (with potential limitations)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Caching**: 1-hour API response caching
- **Conditional Loading**: Assets only load on pages containing the block
- **Optimized Rendering**: Server-side rendering for better initial load times

## Troubleshooting

### Common Issues

1. **"Unable to load pricing data" error**
   - Check internet connection
   - Verify ClickUp API endpoint is accessible
   - Check WordPress error logs for detailed error messages

2. **Block not appearing in editor**
   - Ensure plugin is activated
   - Verify build files exist in `/build/` directory
   - Check for JavaScript console errors

3. **Styling issues**
   - Verify CSS files are loading correctly
   - Check for theme conflicts
   - Ensure custom properties are supported

### Debug Mode
Enable WordPress debug mode to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Changelog

### Version 1.0.0
- Initial release
- Live API integration
- Gutenberg block implementation
- Responsive design
- Caching system
- Customizable content options


## Author

**Daniel King**

## Support

For support and bug reports, please check the WordPress error logs and ensure all dependencies are properly installed or you can contact me :)

---

*This plugin is not officially affiliated with ClickUp. It uses publicly available API endpoints to display pricing information.*