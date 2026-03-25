<?php

namespace TSS\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {
	public static function register(): void {
		$asset_file = TSS_PLUGIN_DIR . 'build/editor.asset.php';
		$asset_data = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-data', 'wp-core-data', 'wp-edit-post', 'wp-i18n' ),
				'version'      => TSS_VERSION,
			);

		wp_register_script(
			'tss-editor',
			TSS_PLUGIN_URL . 'build/editor.js',
			$asset_data['dependencies'],
			$asset_data['version'],
			true
		);
	}
}
