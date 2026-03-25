<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class MatchEventRepository {
	public function get_events_for_match( int $match_id, ?string $event_type = null ): array {
		global $wpdb;

		$table = Tables::match_events();

		if ( null === $event_type ) {
			$query = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE match_id = %d ORDER BY minute ASC, sort_order ASC, id ASC",
				$match_id
			);
		} else {
			$query = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE match_id = %d AND event_type = %s ORDER BY minute ASC, sort_order ASC, id ASC",
				$match_id,
				$event_type
			);
		}

		return array_map( array( $this, 'normalize_event' ), (array) $wpdb->get_results( $query, ARRAY_A ) );
	}

	public function replace_events( int $match_id, array $events ): void {
		global $wpdb;

		$table = Tables::match_events();
		$wpdb->delete( $table, array( 'match_id' => $match_id ), array( '%d' ) );

		foreach ( array_values( $events ) as $index => $event ) {
			$normalized = $this->normalize_event_input( $event, $index );

			$wpdb->insert(
				$table,
				array(
					'match_id'          => $match_id,
					'player_id'         => $normalized['player_id'],
					'related_player_id' => $normalized['related_player_id'],
					'event_type'        => $normalized['event_type'],
					'minute'            => $normalized['minute'],
					'is_penalty'        => $normalized['is_penalty'],
					'is_own_goal'       => $normalized['is_own_goal'],
					'note'              => $normalized['note'],
					'sort_order'        => $normalized['sort_order'],
				),
				array( '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%d' )
			);
		}
	}

	private function normalize_event_input( array $event, int $sort_order ): array {
		return array(
			'player_id'         => (int) ( $event['player_id'] ?? $event['playerId'] ?? 0 ),
			'related_player_id' => $this->nullable_int( $event['related_player_id'] ?? $event['relatedPlayerId'] ?? null ),
			'event_type'        => (string) ( $event['event_type'] ?? $event['eventType'] ?? '' ),
			'minute'            => (int) ( $event['minute'] ?? 0 ),
			'is_penalty'        => $this->bool_to_int( $event, 'is_penalty', 'isPenalty' ),
			'is_own_goal'       => $this->bool_to_int( $event, 'is_own_goal', 'isOwnGoal' ),
			'note'              => (string) ( $event['note'] ?? $event['ownScorer'] ?? '' ),
			'sort_order'        => (int) ( $event['sort_order'] ?? $event['sortOrder'] ?? $sort_order ),
		);
	}

	private function normalize_event( array $event ): array {
		return array(
			'id'               => (int) $event['id'],
			'match_id'         => (int) $event['match_id'],
			'player_id'        => (int) $event['player_id'],
			'related_player_id'=> null !== $event['related_player_id'] ? (int) $event['related_player_id'] : null,
			'event_type'       => (string) $event['event_type'],
			'minute'           => (int) $event['minute'],
			'is_penalty'       => (bool) $event['is_penalty'],
			'is_own_goal'      => (bool) $event['is_own_goal'],
			'note'             => (string) ( $event['note'] ?? '' ),
			'sort_order'       => (int) $event['sort_order'],
		);
	}

	private function nullable_int( $value ): ?int {
		if ( null === $value || '' === $value ) {
			return null;
		}

		return (int) $value;
	}

	private function bool_to_int( array $event, string $snake_key, string $camel_key ): int {
		if ( array_key_exists( $snake_key, $event ) ) {
			return ! empty( $event[ $snake_key ] ) ? 1 : 0;
		}

		if ( array_key_exists( $camel_key, $event ) ) {
			return ! empty( $event[ $camel_key ] ) ? 1 : 0;
		}

		return 0;
	}
}
