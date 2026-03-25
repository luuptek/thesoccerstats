<?php

namespace TSS\Data;

defined( 'ABSPATH' ) || exit;

class Sync {
	public static function register(): void {
		add_action( 'save_post_tss-players', array( __CLASS__, 'sync_player' ), 20, 2 );
	}

	public static function sync_player( int $post_id, \WP_Post $post ): void {
		if ( self::should_skip( $post_id, $post ) ) {
			return;
		}

		$season_ids = array_map( 'intval', (array) get_post_meta( $post_id, 'tss_player_season_ids', true ) );
		( new PlayerSeasonRepository() )->replace_player_seasons( $post_id, $season_ids );
	}

	public static function sync_match( int $post_id, \WP_Post $post ): void {
		if ( self::should_skip( $post_id, $post ) ) {
			return;
		}

		$lineups = new MatchLineupRepository();
		$events  = new MatchEventRepository();

		$lineups->replace_role_players(
			$post_id,
			'starter',
			array_map( 'intval', (array) get_post_meta( $post_id, 'tss_match_starters', true ) )
		);
		$lineups->replace_role_players(
			$post_id,
			'substitute',
			array_map( 'intval', (array) get_post_meta( $post_id, 'tss_match_substitutes', true ) )
		);

		$event_rows = array();

		foreach ( (array) get_post_meta( $post_id, 'tss_match_goals', true ) as $index => $goal ) {
			$event_rows[] = array(
				'player_id'   => (int) ( $goal['playerId'] ?? 0 ),
				'event_type'  => 'goal',
				'minute'      => (int) ( $goal['minute'] ?? 0 ),
				'is_penalty'  => ! empty( $goal['isPenalty'] ),
				'is_own_goal' => ! empty( $goal['isOwnGoal'] ),
				'note'        => (string) ( $goal['ownScorer'] ?? '' ),
				'sort_order'  => $index,
			);
		}

		foreach ( (array) get_post_meta( $post_id, 'tss_match_yellow_cards', true ) as $index => $card ) {
			$event_rows[] = array(
				'player_id'  => (int) ( $card['playerId'] ?? 0 ),
				'event_type' => 'yellow_card',
				'minute'     => (int) ( $card['minute'] ?? 0 ),
				'sort_order' => $index + 1000,
			);
		}

		foreach ( (array) get_post_meta( $post_id, 'tss_match_red_cards', true ) as $index => $card ) {
			$event_rows[] = array(
				'player_id'  => (int) ( $card['playerId'] ?? 0 ),
				'event_type' => 'red_card',
				'minute'     => (int) ( $card['minute'] ?? 0 ),
				'sort_order' => $index + 2000,
			);
		}

		foreach ( (array) get_post_meta( $post_id, 'tss_match_substitutions', true ) as $index => $substitution ) {
			$event_rows[] = array(
				'player_id'         => (int) ( $substitution['playerIn'] ?? 0 ),
				'related_player_id' => (int) ( $substitution['playerOut'] ?? 0 ),
				'event_type'        => 'substitution',
				'minute'            => (int) ( $substitution['minute'] ?? 0 ),
				'sort_order'        => $index + 3000,
			);
		}

		$events->replace_events( $post_id, $event_rows );
	}

	private static function should_skip( int $post_id, \WP_Post $post ): bool {
		if ( 'auto-draft' === $post->post_status ) {
			return true;
		}

		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return true;
		}

		return false;
	}
}
