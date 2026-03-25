<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class Tables {
	public static function player_seasons(): string {
		global $wpdb;

		return $wpdb->prefix . 'tss_player_seasons';
	}

	public static function match_lineups(): string {
		global $wpdb;

		return $wpdb->prefix . 'tss_match_lineups';
	}

	public static function match_events(): string {
		global $wpdb;

		return $wpdb->prefix . 'tss_match_events';
	}
}
