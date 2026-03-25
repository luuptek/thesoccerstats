<?php

namespace TSS\Domain;

defined( 'ABSPATH' ) || exit;

class Meta {
	public static function register(): void {
		self::register_player_meta();
		self::register_match_meta();
	}

	private static function register_player_meta(): void {
		$fields = array(
			'tss_shirt_number'      => array( 'type' => 'integer' ),
			'tss_place_of_birth'    => array( 'type' => 'string' ),
			'tss_height'            => array( 'type' => 'string' ),
			'tss_weight'            => array( 'type' => 'string' ),
			'tss_date_of_birth'     => array( 'type' => 'string' ),
			'tss_position'          => array( 'type' => 'string' ),
			'tss_previous_clubs'    => array( 'type' => 'string' ),
			'tss_player_season_ids' => array(
				'type'   => 'array',
				'items'  => array( 'type' => 'integer' ),
				'default' => array(),
			),
		);

		foreach ( $fields as $key => $field ) {
			register_post_meta(
				'tss-players',
				$key,
				array(
					'type'              => $field['type'],
					'single'            => true,
					'show_in_rest'      => array(
						'schema' => array_merge(
							array(
								'type'    => $field['type'],
								'default' => $field['default'] ?? ( 'integer' === $field['type'] ? 0 : '' ),
							),
							isset( $field['items'] ) ? array( 'items' => $field['items'] ) : array()
						),
					),
					'sanitize_callback' => array( __CLASS__, 'sanitize_meta' ),
					'auth_callback'     => array( __CLASS__, 'can_edit_posts' ),
					'default'           => $field['default'] ?? ( 'integer' === $field['type'] ? 0 : '' ),
				)
			);
		}
	}

	private static function register_match_meta(): void {
		$fields = array(
			'tss_match_date'                  => array( 'type' => 'string' ),
			'tss_match_time'                  => array( 'type' => 'string' ),
			'tss_match_location'              => array( 'type' => 'string' ),
			'tss_match_season'                => array( 'type' => 'integer' ),
			'tss_match_matchtype'             => array( 'type' => 'integer' ),
			'tss_match_additional_matchtype'  => array( 'type' => 'string' ),
			'tss_match_attendance'            => array( 'type' => 'integer' ),
			'tss_match_opponent'              => array( 'type' => 'integer' ),
			'tss_match_goals_for'             => array( 'type' => 'integer' ),
			'tss_match_goals_against'         => array( 'type' => 'integer' ),
			'tss_match_goals_for_penalties'   => array( 'type' => 'integer' ),
			'tss_match_goals_against_penalties' => array( 'type' => 'integer' ),
			'tss_match_overtime'              => array( 'type' => 'boolean', 'default' => false ),
			'tss_match_penalties'             => array( 'type' => 'boolean', 'default' => false ),
			'tss_match_calculate_stats'       => array( 'type' => 'boolean', 'default' => true ),
			'tss_match_show_opponent_stats'   => array( 'type' => 'boolean', 'default' => false ),
			'tss_match_opponent_starters'     => array( 'type' => 'string' ),
			'tss_match_opponent_substitutes'  => array( 'type' => 'string' ),
			'tss_match_opponent_substitutions' => array( 'type' => 'string' ),
			'tss_match_opponent_goals'        => array( 'type' => 'string' ),
			'tss_match_opponent_yellows'      => array( 'type' => 'string' ),
			'tss_match_opponent_reds'         => array( 'type' => 'string' ),
			'tss_match_starters'              => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'integer' ),
				'default' => array(),
			),
			'tss_match_substitutes'           => array(
				'type'    => 'array',
				'items'   => array( 'type' => 'integer' ),
				'default' => array(),
			),
			'tss_match_substitutions'         => array(
				'type'    => 'array',
				'default' => array(),
				'items'   => array(
					'type'       => 'object',
					'properties' => array(
						'playerIn'  => array( 'type' => 'integer' ),
						'playerOut' => array( 'type' => 'integer' ),
						'minute'    => array( 'type' => 'integer' ),
					),
				),
			),
			'tss_match_goals'                 => array(
				'type'    => 'array',
				'default' => array(),
				'items'   => array(
					'type'       => 'object',
					'properties' => array(
						'playerId'    => array( 'type' => 'integer' ),
						'minute'      => array( 'type' => 'integer' ),
						'isPenalty'   => array( 'type' => 'boolean' ),
						'isOwnGoal'   => array( 'type' => 'boolean' ),
						'ownScorer'   => array( 'type' => 'string' ),
					),
				),
			),
			'tss_match_yellow_cards'          => array(
				'type'    => 'array',
				'default' => array(),
				'items'   => array(
					'type'       => 'object',
					'properties' => array(
						'playerId' => array( 'type' => 'integer' ),
						'minute'   => array( 'type' => 'integer' ),
					),
				),
			),
			'tss_match_red_cards'             => array(
				'type'    => 'array',
				'default' => array(),
				'items'   => array(
					'type'       => 'object',
					'properties' => array(
						'playerId' => array( 'type' => 'integer' ),
						'minute'   => array( 'type' => 'integer' ),
					),
				),
			),
		);

		foreach ( $fields as $key => $field ) {
			register_post_meta(
				'tss-matches',
				$key,
				array(
					'type'              => $field['type'],
					'single'            => true,
					'show_in_rest'      => array(
						'schema' => array_merge(
							array(
								'type'    => $field['type'],
								'default' => $field['default'] ?? self::default_for_type( $field['type'] ),
							),
							isset( $field['items'] ) ? array( 'items' => $field['items'] ) : array()
						),
					),
					'sanitize_callback' => array( __CLASS__, 'sanitize_meta' ),
					'auth_callback'     => array( __CLASS__, 'can_edit_posts' ),
					'default'           => $field['default'] ?? self::default_for_type( $field['type'] ),
				)
			);
		}
	}

	private static function default_for_type( string $type ) {
		switch ( $type ) {
			case 'integer':
				return 0;
			case 'boolean':
				return false;
			case 'array':
				return array();
			default:
				return '';
		}
	}

	public static function sanitize_meta( $value ) {
		return $value;
	}

	public static function can_edit_posts(): bool {
		return current_user_can( 'edit_posts' );
	}
}
