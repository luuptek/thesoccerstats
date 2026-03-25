<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class Schema {
	public static function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$player_seasons  = Tables::player_seasons();
		$match_lineups   = Tables::match_lineups();
		$match_events    = Tables::match_events();

		$queries = array(
			"CREATE TABLE {$player_seasons} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				player_id bigint(20) unsigned NOT NULL,
				season_id bigint(20) unsigned NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY player_season (player_id, season_id),
				KEY season_id (season_id)
			) {$charset_collate};",
			"CREATE TABLE {$match_lineups} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				match_id bigint(20) unsigned NOT NULL,
				player_id bigint(20) unsigned NOT NULL,
				role varchar(20) NOT NULL,
				sort_order int(11) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				UNIQUE KEY match_player_role (match_id, player_id, role),
				KEY match_role_sort (match_id, role, sort_order),
				KEY player_id (player_id)
			) {$charset_collate};",
			"CREATE TABLE {$match_events} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				match_id bigint(20) unsigned NOT NULL,
				player_id bigint(20) unsigned NOT NULL,
				related_player_id bigint(20) unsigned DEFAULT NULL,
				event_type varchar(32) NOT NULL,
				minute int(11) unsigned NOT NULL DEFAULT 0,
				is_penalty tinyint(1) unsigned NOT NULL DEFAULT 0,
				is_own_goal tinyint(1) unsigned NOT NULL DEFAULT 0,
				note text DEFAULT NULL,
				sort_order int(11) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				KEY match_id (match_id),
				KEY player_id (player_id),
				KEY event_type (event_type),
				KEY match_event_sort (match_id, event_type, sort_order)
			) {$charset_collate};",
		);

		foreach ( $queries as $query ) {
			dbDelta( $query );
		}
	}
}
