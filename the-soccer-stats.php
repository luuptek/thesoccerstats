<?php
/**
 * Plugin Name: The Soccer Stats
 * Plugin URI: https://wordpress.org/plugins/the-soccer-stats/
 * Description: Modern football statistics plugin built around dynamic blocks.
 * Version: 2.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Timo Leppanen
 * License: GPLv2 or later
 * Text Domain: tss
 */

defined( 'ABSPATH' ) || exit;

define( 'TSS_VERSION', '2.0.0' );
define( 'TSS_PLUGIN_FILE', __FILE__ );
define( 'TSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once TSS_PLUGIN_DIR . 'includes/Core/Autoloader.php';

\TSS\Core\Autoloader::register();
\TSS\Core\Plugin::boot();
