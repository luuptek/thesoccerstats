process.env.WP_SOURCE_PATH = process.env.WP_SOURCE_PATH || 'blocks';

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: () => ( {
		...getWebpackEntryPoints( 'script' )(),
		editor: path.resolve( process.cwd(), 'resources/js/editor.js' ),
	} ),
};
