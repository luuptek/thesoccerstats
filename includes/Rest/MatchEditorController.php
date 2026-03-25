<?php

namespace TSS\Rest;

use TSS\Data\MatchEventRepository;
use TSS\Data\MatchLineupRepository;
use TSS\Data\PlayerSeasonRepository;

defined( 'ABSPATH' ) || exit;

class MatchEditorController {
	public static function register(): void {
		register_rest_route(
			'tss/v1',
			'/seasons/(?P<id>\d+)/players',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_players_for_season' ),
				'permission_callback' => array( __CLASS__, 'can_edit_posts' ),
			)
		);

		register_rest_route(
			'tss/v1',
			'/matches/(?P<id>\d+)/editor-data',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( __CLASS__, 'get_editor_data' ),
					'permission_callback' => array( __CLASS__, 'can_edit_match' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( __CLASS__, 'update_editor_data' ),
					'permission_callback' => array( __CLASS__, 'can_edit_match' ),
					'args'                => array(
						'starters'       => array( 'type' => 'array', 'required' => true ),
						'substitutes'    => array( 'type' => 'array', 'required' => true ),
						'substitutions'  => array( 'type' => 'array', 'required' => true ),
						'goals'          => array( 'type' => 'array', 'required' => true ),
						'yellow_cards'   => array( 'type' => 'array', 'required' => true ),
						'red_cards'      => array( 'type' => 'array', 'required' => true ),
					),
				),
			)
		);
	}

	public static function get_editor_data( \WP_REST_Request $request ): \WP_REST_Response {
		$match_id = (int) $request['id'];
		$lineups  = new MatchLineupRepository();
		$events   = new MatchEventRepository();

		$all_events = $events->get_events_for_match( $match_id );

		$data = array(
			'starters'      => $lineups->get_players_by_role( $match_id, 'starter' ),
			'substitutes'   => $lineups->get_players_by_role( $match_id, 'substitute' ),
			'substitutions' => array(),
			'goals'         => array(),
			'yellow_cards'  => array(),
			'red_cards'     => array(),
		);

		foreach ( $all_events as $event ) {
			switch ( $event['event_type'] ) {
				case 'substitution':
					$data['substitutions'][] = array(
						'playerIn'  => $event['player_id'],
						'playerOut' => $event['related_player_id'] ?? 0,
						'minute'    => $event['minute'],
					);
					break;
				case 'goal':
					$data['goals'][] = array(
						'playerId'  => $event['player_id'],
						'minute'    => $event['minute'],
						'isPenalty' => $event['is_penalty'],
						'isOwnGoal' => $event['is_own_goal'],
						'ownScorer' => $event['note'],
					);
					break;
				case 'yellow_card':
					$data['yellow_cards'][] = array(
						'playerId' => $event['player_id'],
						'minute'   => $event['minute'],
					);
					break;
				case 'red_card':
					$data['red_cards'][] = array(
						'playerId' => $event['player_id'],
						'minute'   => $event['minute'],
					);
					break;
			}
		}

		return new \WP_REST_Response( $data );
	}

	public static function get_players_for_season( \WP_REST_Request $request ): \WP_REST_Response {
		$season_id = (int) $request['id'];

		if ( $season_id <= 0 ) {
			$players = get_posts(
				array(
					'post_type'      => 'tss-players',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
				)
			);
		} else {
			$player_ids = ( new PlayerSeasonRepository() )->get_player_ids_for_season( $season_id );

			$players = array();

			if ( array() !== $player_ids ) {
				$players = get_posts(
					array(
						'post_type'      => 'tss-players',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'post__in'       => $player_ids,
						'orderby'        => 'title',
						'order'          => 'ASC',
					)
				);
			}
		}

		$data = array_map(
			static fn( \WP_Post $player ): array => array(
				'label' => $player->post_title ?: __( '(no title)', 'tss' ),
				'value' => (int) $player->ID,
			),
			$players
		);

		return new \WP_REST_Response( $data );
	}

	public static function update_editor_data( \WP_REST_Request $request ): \WP_REST_Response {
		$match_id = (int) $request['id'];
		$lineups  = new MatchLineupRepository();
		$events   = new MatchEventRepository();

		$starters      = array_map( 'intval', (array) $request->get_param( 'starters' ) );
		$substitutes   = array_map( 'intval', (array) $request->get_param( 'substitutes' ) );
		$substitutions = (array) $request->get_param( 'substitutions' );
		$goals         = (array) $request->get_param( 'goals' );
		$yellow_cards  = (array) $request->get_param( 'yellow_cards' );
		$red_cards     = (array) $request->get_param( 'red_cards' );

		$lineups->replace_role_players( $match_id, 'starter', $starters );
		$lineups->replace_role_players( $match_id, 'substitute', $substitutes );

		$event_rows = array();

		foreach ( $goals as $index => $goal ) {
			$event_rows[] = array(
				'playerId'   => (int) ( $goal['playerId'] ?? 0 ),
				'eventType'  => 'goal',
				'minute'     => (int) ( $goal['minute'] ?? 0 ),
				'isPenalty'  => ! empty( $goal['isPenalty'] ),
				'isOwnGoal'  => ! empty( $goal['isOwnGoal'] ),
				'ownScorer'  => (string) ( $goal['ownScorer'] ?? '' ),
				'sortOrder'  => $index,
			);
		}

		foreach ( $yellow_cards as $index => $card ) {
			$event_rows[] = array(
				'playerId'  => (int) ( $card['playerId'] ?? 0 ),
				'eventType' => 'yellow_card',
				'minute'    => (int) ( $card['minute'] ?? 0 ),
				'sortOrder' => $index + 1000,
			);
		}

		foreach ( $red_cards as $index => $card ) {
			$event_rows[] = array(
				'playerId'  => (int) ( $card['playerId'] ?? 0 ),
				'eventType' => 'red_card',
				'minute'    => (int) ( $card['minute'] ?? 0 ),
				'sortOrder' => $index + 2000,
			);
		}

		foreach ( $substitutions as $index => $substitution ) {
			$event_rows[] = array(
				'playerId'        => (int) ( $substitution['playerIn'] ?? 0 ),
				'relatedPlayerId' => (int) ( $substitution['playerOut'] ?? 0 ),
				'eventType'       => 'substitution',
				'minute'          => (int) ( $substitution['minute'] ?? 0 ),
				'sortOrder'       => $index + 3000,
			);
		}

		$events->replace_events( $match_id, $event_rows );

		return self::get_editor_data( $request );
	}

	public static function can_edit_match( \WP_REST_Request $request ): bool {
		$match_id = (int) $request['id'];

		return current_user_can( 'edit_post', $match_id );
	}

	public static function can_edit_posts(): bool {
		return current_user_can( 'edit_posts' );
	}
}
