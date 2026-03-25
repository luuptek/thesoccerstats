<?php

namespace TSS\Blocks;

defined( 'ABSPATH' ) || exit;

class Registry {
	public static function register(): void {
		$build_dir      = TSS_PLUGIN_DIR . 'build';
		$manifest_file  = $build_dir . '/blocks-manifest.php';
		$manifest_paths = array(
			'match-summary',
			'player-profile',
			'player-stats-table',
			'season-match-list',
		);

		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && file_exists( $manifest_file ) ) {
			wp_register_block_types_from_metadata_collection( $build_dir, $manifest_file );
			return;
		}

		if ( function_exists( 'wp_register_block_metadata_collection' ) && file_exists( $manifest_file ) ) {
			wp_register_block_metadata_collection( $build_dir, $manifest_file );
		}

		foreach ( $manifest_paths as $block ) {
			register_block_type( $build_dir . '/' . $block );
		}
	}
}
