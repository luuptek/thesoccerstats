<?php

namespace TSS\Core;

use TSS\Admin\Assets;
use TSS\Blocks\Registry;
use TSS\CLI\TestDataCommand;
use TSS\Data\Schema;
use TSS\Data\Sync;
use TSS\Domain\Meta;
use TSS\Domain\PostTypes;
use TSS\Rest\MatchEditorController;

defined( 'ABSPATH' ) || exit;

class Plugin {
	public static function boot(): void {
		add_action( 'init', array( PostTypes::class, 'register' ) );
		add_action( 'init', array( Meta::class, 'register' ) );
		add_action( 'init', array( Assets::class, 'register' ) );
		add_action( 'init', array( Registry::class, 'register' ) );
		add_action( 'init', array( Sync::class, 'register' ) );
		add_action( 'rest_api_init', array( MatchEditorController::class, 'register' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		register_activation_hook( TSS_PLUGIN_FILE, array( __CLASS__, 'activate' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			TestDataCommand::register();
		}
	}

	public static function activate(): void {
		PostTypes::register();
		Schema::install();
		flush_rewrite_rules();
	}

	public static function load_textdomain(): void {
		load_plugin_textdomain( 'tss', false, dirname( plugin_basename( TSS_PLUGIN_FILE ) ) . '/lang/' );
	}
}
