const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        index: './src/index.js',      // Editor block
        frontend: './src/frontend.js', // Frontend styles and JS
    },
};