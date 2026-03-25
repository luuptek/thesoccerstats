<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class PlayerSeasonRepository {
	public function get_season_ids_for_player( int $player_id ): array {
		global $wpdb;

		$table = Tables::player_seasons();
		$query = $wpdb->prepare(
			"SELECT season_id FROM {$table} WHERE player_id = %d ORDER BY season_id DESC",
			$player_id
		);

		return array_map( 'intval', (array) $wpdb->get_col( $query ) );
	}

	public function get_player_ids_for_season( int $season_id ): array {
		global $wpdb;

		$table = Tables::player_seasons();
		$query = $wpdb->prepare(
			"SELECT player_id FROM {$table} WHERE season_id = %d ORDER BY player_id ASC",
			$season_id
		);

		return array_map( 'intval', (array) $wpdb->get_col( $query ) );
	}

	public function replace_player_seasons( int $player_id, array $season_ids ): void {
		global $wpdb;

		$table = Tables::player_seasons();
		$wpdb->delete( $table, array( 'player_id' => $player_id ), array( '%d' ) );

		$season_ids = array_values( array_unique( array_filter( array_map( 'intval', $season_ids ) ) ) );

		foreach ( $season_ids as $season_id ) {
			$wpdb->insert(
				$table,
				array(
					'player_id' => $player_id,
					'season_id' => $season_id,
				),
				array( '%d', '%d' )
			);
		}
	}
}
