<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class MatchLineupRepository {
	public function get_players_by_role( int $match_id, string $role ): array {
		global $wpdb;

		$table = Tables::match_lineups();
		$query = $wpdb->prepare(
			"SELECT player_id FROM {$table} WHERE match_id = %d AND role = %s ORDER BY sort_order ASC, id ASC",
			$match_id,
			$role
		);

		return array_map( 'intval', (array) $wpdb->get_col( $query ) );
	}

	public function replace_role_players( int $match_id, string $role, array $player_ids ): void {
		global $wpdb;

		$table = Tables::match_lineups();
		$wpdb->delete(
			$table,
			array(
				'match_id' => $match_id,
				'role'     => $role,
			),
			array( '%d', '%s' )
		);

		$player_ids = array_values( array_filter( array_map( 'intval', $player_ids ) ) );

		foreach ( $player_ids as $index => $player_id ) {
			$wpdb->insert(
				$table,
				array(
					'match_id'   => $match_id,
					'player_id'  => $player_id,
					'role'       => $role,
					'sort_order' => $index,
				),
				array( '%d', '%d', '%s', '%d' )
			);
		}
	}
}
