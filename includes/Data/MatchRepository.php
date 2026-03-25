<?php

namespace TSS\Data;

use TSS\Support\Content;
use TSS\Support\Formatting;

defined( 'ABSPATH' ) || exit;

class MatchRepository {
	public function get_match( int $match_id ): array {
		$post = get_post( $match_id );

		if ( ! $post || 'tss-matches' !== $post->post_type ) {
			return array();
		}

		$opponent_id = (int) get_post_meta( $match_id, 'tss_match_opponent', true );
		$location    = (string) get_post_meta( $match_id, 'tss_match_location', true );
		$my_team_id  = (int) ( get_option( 'tss_options', array() )['my_team'] ?? 0 );
		$my_team     = $my_team_id ? get_the_title( $my_team_id ) : '';
		$opponent    = $opponent_id ? get_the_title( $opponent_id ) : '';

		$home_team = $my_team;
		$away_team = $opponent;
		$home_goals = (int) get_post_meta( $match_id, 'tss_match_goals_for', true );
		$away_goals = (int) get_post_meta( $match_id, 'tss_match_goals_against', true );
		$home_penalties = (int) get_post_meta( $match_id, 'tss_match_goals_for_penalties', true );
		$away_penalties = (int) get_post_meta( $match_id, 'tss_match_goals_against_penalties', true );

		if ( 'away' === $location ) {
			$home_team      = $opponent;
			$away_team      = $my_team;
			$home_goals     = (int) get_post_meta( $match_id, 'tss_match_goals_against', true );
			$away_goals     = (int) get_post_meta( $match_id, 'tss_match_goals_for', true );
			$home_penalties = (int) get_post_meta( $match_id, 'tss_match_goals_against_penalties', true );
			$away_penalties = (int) get_post_meta( $match_id, 'tss_match_goals_for_penalties', true );
		}

		$lineups = new MatchLineupRepository();
		$events  = new MatchEventRepository();

		$starters    = $lineups->get_players_by_role( $match_id, 'starter' );
		$substitutes = $lineups->get_players_by_role( $match_id, 'substitute' );
		$event_rows  = $events->get_events_for_match( $match_id );

		if ( array() === $starters ) {
			$starters = array_map( 'intval', (array) get_post_meta( $match_id, 'tss_match_starters', true ) );
		}

		if ( array() === $substitutes ) {
			$substitutes = array_map( 'intval', (array) get_post_meta( $match_id, 'tss_match_substitutes', true ) );
		}

		if ( array() === $event_rows ) {
			$event_rows = $this->events_from_meta( $match_id );
		}

		return array(
			'id'                    => $match_id,
			'title'                 => get_the_title( $match_id ),
			'content'               => Content::render_post_content_without_recursive_block( $post, 'tss/match-summary' ),
			'date'                  => (string) get_post_meta( $match_id, 'tss_match_date', true ),
			'time'                  => (string) get_post_meta( $match_id, 'tss_match_time', true ),
			'location'              => $location,
			'season_id'             => (int) get_post_meta( $match_id, 'tss_match_season', true ),
			'match_type_id'         => (int) get_post_meta( $match_id, 'tss_match_matchtype', true ),
			'match_type'            => get_the_title( (int) get_post_meta( $match_id, 'tss_match_matchtype', true ) ),
			'additional_match_type' => (string) get_post_meta( $match_id, 'tss_match_additional_matchtype', true ),
			'attendance'            => (int) get_post_meta( $match_id, 'tss_match_attendance', true ),
			'opponent_id'           => $opponent_id,
			'opponent'              => $opponent,
			'overtime'              => (bool) get_post_meta( $match_id, 'tss_match_overtime', true ),
			'penalties'             => (bool) get_post_meta( $match_id, 'tss_match_penalties', true ),
			'calculate_stats'       => (bool) get_post_meta( $match_id, 'tss_match_calculate_stats', true ),
			'show_opponent_stats'   => (bool) get_post_meta( $match_id, 'tss_match_show_opponent_stats', true ),
			'home_team'             => $home_team,
			'away_team'             => $away_team,
			'home_goals'            => $home_goals,
			'away_goals'            => $away_goals,
			'home_penalties'        => $home_penalties,
			'away_penalties'        => $away_penalties,
			'starters'              => $starters,
			'substitutes'           => $substitutes,
			'events'                => $event_rows,
			'opponent_starters'     => (string) get_post_meta( $match_id, 'tss_match_opponent_starters', true ),
			'opponent_substitutes'  => (string) get_post_meta( $match_id, 'tss_match_opponent_substitutes', true ),
			'opponent_substitutions' => (string) get_post_meta( $match_id, 'tss_match_opponent_substitutions', true ),
			'opponent_goals'        => (string) get_post_meta( $match_id, 'tss_match_opponent_goals', true ),
			'opponent_yellows'      => (string) get_post_meta( $match_id, 'tss_match_opponent_yellows', true ),
			'opponent_reds'         => (string) get_post_meta( $match_id, 'tss_match_opponent_reds', true ),
		);
	}

	public function get_matches_by_season( int $season_id, int $match_type_id = 0 ): array {
		$query = new \WP_Query(
			array(
				'post_type'      => 'tss-matches',
				'posts_per_page' => -1,
				'meta_key'       => 'tss_match_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array_filter(
					array(
						array(
							'key'   => 'tss_match_season',
							'value' => $season_id,
						),
						$match_type_id ? array(
							'key'   => 'tss_match_matchtype',
							'value' => $match_type_id,
						) : null,
					)
				),
			)
		);

		$matches = array();

		foreach ( $query->posts as $post ) {
			$matches[] = $this->get_match( $post->ID );
		}

		return $matches;
	}

	public function get_player_name( int $player_id ): string {
		return $player_id ? get_the_title( $player_id ) : '';
	}

	public function get_match_summary( int $match_id ): array {
		$match = $this->get_match( $match_id );

		if ( array() === $match ) {
			return array();
		}

		$match['formatted_datetime'] = Formatting::formatted_match_datetime( $match['date'], $match['time'] );
		$match['status_label']       = Formatting::match_status( $match );
		$match['type_label']         = Formatting::match_type_label( $match['match_type'], $match['additional_match_type'] );
		$match['result_label']       = Formatting::result_label( $match );
		$match['starter_names']      = $this->map_player_ids_to_names( $match['starters'] );
		$match['substitute_names']   = $this->map_player_ids_to_names( $match['substitutes'] );
		$match['goals_list']         = array();
		$match['yellow_card_list']   = array();
		$match['red_card_list']      = array();
		$match['substitution_list']  = array();
		$match['opponent_stats']     = array_filter(
			array(
				array(
					'label' => __( 'Lineup', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_starters'] ),
				),
				array(
					'label' => __( 'Bench', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_substitutes'] ),
				),
				array(
					'label' => __( 'Substitutions', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_substitutions'] ),
				),
				array(
					'label' => __( 'Goals', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_goals'] ),
				),
				array(
					'label' => __( 'Yellow Cards', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_yellows'] ),
				),
				array(
					'label' => __( 'Red Cards', 'tss' ),
					'items' => $this->parse_textarea_lines( $match['opponent_reds'] ),
				),
			),
			static fn( array $section ): bool => array() !== $section['items']
		);
		$match['has_opponent_stats'] = $match['show_opponent_stats'] && array() !== $match['opponent_stats'];

		foreach ( $match['events'] as $event ) {
			switch ( $event['event_type'] ) {
				case 'goal':
					$match['goals_list'][] = $this->format_goal_event( $event );
					break;
				case 'yellow_card':
					$match['yellow_card_list'][] = $this->format_simple_event( $event );
					break;
				case 'red_card':
					$match['red_card_list'][] = $this->format_simple_event( $event );
					break;
				case 'substitution':
					$match['substitution_list'][] = array(
						'player_in'  => $this->get_player_name( (int) $event['player_id'] ),
						'player_out' => $this->get_player_name( (int) ( $event['related_player_id'] ?? 0 ) ),
						'minute'     => (int) $event['minute'],
					);
					break;
			}
		}

		return $match;
	}

	private function map_player_ids_to_names( array $player_ids ): array {
		return array_values(
			array_filter(
				array_map(
					fn( int $player_id ): string => $this->get_player_name( $player_id ),
					array_map( 'intval', $player_ids )
				)
			)
		);
	}

	private function format_goal_event( array $event ): array {
		$label = $this->get_player_name( (int) $event['player_id'] );

		if ( $event['is_own_goal'] && '' !== $event['note'] ) {
			$label = $event['note'];
		}

		return array(
			'label'       => $label,
			'minute'      => (int) $event['minute'],
			'is_penalty'  => (bool) $event['is_penalty'],
			'is_own_goal' => (bool) $event['is_own_goal'],
		);
	}

	private function format_simple_event( array $event ): array {
		return array(
			'label'  => $this->get_player_name( (int) $event['player_id'] ),
			'minute' => (int) $event['minute'],
		);
	}

	private function parse_textarea_lines( string $value ): array {
		$lines = preg_split( '/\r\n|\r|\n/', trim( $value ) );

		if ( false === $lines ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'trim', $lines ),
				static fn( string $line ): bool => '' !== $line
			)
		);
	}

	private function events_from_meta( int $match_id ): array {
		$events = array();

		foreach ( (array) get_post_meta( $match_id, 'tss_match_goals', true ) as $index => $goal ) {
			$events[] = array(
				'player_id'         => (int) ( $goal['playerId'] ?? 0 ),
				'related_player_id' => null,
				'event_type'        => 'goal',
				'minute'            => (int) ( $goal['minute'] ?? 0 ),
				'is_penalty'        => ! empty( $goal['isPenalty'] ),
				'is_own_goal'       => ! empty( $goal['isOwnGoal'] ),
				'note'              => (string) ( $goal['ownScorer'] ?? '' ),
				'sort_order'        => $index,
			);
		}

		foreach ( (array) get_post_meta( $match_id, 'tss_match_yellow_cards', true ) as $index => $card ) {
			$events[] = array(
				'player_id'         => (int) ( $card['playerId'] ?? 0 ),
				'related_player_id' => null,
				'event_type'        => 'yellow_card',
				'minute'            => (int) ( $card['minute'] ?? 0 ),
				'is_penalty'        => false,
				'is_own_goal'       => false,
				'note'              => '',
				'sort_order'        => $index + 1000,
			);
		}

		foreach ( (array) get_post_meta( $match_id, 'tss_match_red_cards', true ) as $index => $card ) {
			$events[] = array(
				'player_id'         => (int) ( $card['playerId'] ?? 0 ),
				'related_player_id' => null,
				'event_type'        => 'red_card',
				'minute'            => (int) ( $card['minute'] ?? 0 ),
				'is_penalty'        => false,
				'is_own_goal'       => false,
				'note'              => '',
				'sort_order'        => $index + 2000,
			);
		}

		foreach ( (array) get_post_meta( $match_id, 'tss_match_substitutions', true ) as $index => $substitution ) {
			$events[] = array(
				'player_id'         => (int) ( $substitution['playerIn'] ?? 0 ),
				'related_player_id' => (int) ( $substitution['playerOut'] ?? 0 ),
				'event_type'        => 'substitution',
				'minute'            => (int) ( $substitution['minute'] ?? 0 ),
				'is_penalty'        => false,
				'is_own_goal'       => false,
				'note'              => '',
				'sort_order'        => $index + 3000,
			);
		}

		usort(
			$events,
			static function ( array $left, array $right ): int {
				if ( $left['minute'] === $right['minute'] ) {
					return $left['sort_order'] <=> $right['sort_order'];
				}

				return $left['minute'] <=> $right['minute'];
			}
		);

		return $events;
	}
}
