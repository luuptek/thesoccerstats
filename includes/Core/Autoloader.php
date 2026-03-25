<?php

namespace TSS\Core;

defined( 'ABSPATH' ) || exit;

class Autoloader {
	public static function register(): void {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	public static function autoload( string $class_name ): void {
		$prefix = 'TSS\\';

		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$relative = str_replace( '\\', '/', $relative );
		$file     = TSS_PLUGIN_DIR . 'includes/' . $relative . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}
