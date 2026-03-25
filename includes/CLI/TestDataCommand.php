<?php

namespace TSS\CLI;

use TSS\Data\PlayerSeasonRepository;
use TSS\Data\Sync;
use TSS\Data\Tables;
use WP_CLI;

defined( 'ABSPATH' ) || exit;

class TestDataCommand {
	public static function register(): void {
		WP_CLI::add_command( 'tss create-testdata', array( __CLASS__, 'create_testdata' ) );
		WP_CLI::add_command( 'tss delete-testdata', array( __CLASS__, 'delete_testdata' ) );
	}

	/**
	 * Create demo content for local testing.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tss create-testdata
	 *
	 * @when after_wp_load
	 */
	public static function create_testdata(): void {
		self::seed();
		WP_CLI::success( 'Created multi-season test data for The Soccer Stats.' );
	}

	/**
	 * Delete generated demo content for local testing.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tss delete-testdata
	 *
	 * @when after_wp_load
	 */
	public static function delete_testdata(): void {
		self::purge();
		WP_CLI::success( 'Deleted The Soccer Stats test data and related custom table rows.' );
	}

	public static function seed(): void {
		self::purge();

		$season_ids = array(
			'2023-24' => self::upsert_post(
				'tss-seasons',
				'2023-24',
				array(
					'post_content' => 'Demo season for The Soccer Stats block editor.',
				)
			),
			'2024-25' => self::upsert_post(
				'tss-seasons',
				'2024-25',
				array(
					'post_content' => 'Demo season for The Soccer Stats block editor.',
				)
			),
			'2025-26' => self::upsert_post(
				'tss-seasons',
				'2025-26',
				array(
					'post_content' => 'Demo season for The Soccer Stats block editor.',
				)
			),
		);

		$match_type_ids = array(
			'Premier League' => self::upsert_post( 'tss-matchtypes', 'Premier League' ),
			'Europa League'  => self::upsert_post( 'tss-matchtypes', 'Europa League' ),
			'FA Cup'         => self::upsert_post( 'tss-matchtypes', 'FA Cup' ),
		);

		$team_ids = array(
			'Manchester United' => self::upsert_post(
				'tss-opponents',
				'Manchester United',
				array(
					'post_content' => 'Primary club for local test data.',
				)
			),
			'Arsenal'           => self::upsert_post( 'tss-opponents', 'Arsenal' ),
			'Liverpool'         => self::upsert_post( 'tss-opponents', 'Liverpool' ),
			'Real Sociedad'     => self::upsert_post( 'tss-opponents', 'Real Sociedad' ),
			'Chelsea'           => self::upsert_post( 'tss-opponents', 'Chelsea' ),
			'Tottenham'         => self::upsert_post( 'tss-opponents', 'Tottenham' ),
		);

		update_option(
			'tss_options',
			array(
				'my_team' => $team_ids['Manchester United'],
			)
		);

		$players = array(
			array(
				'name'           => 'Andre Onana',
				'number'         => 24,
				'position'       => 'goalkeeper',
				'date_of_birth'  => '1996-04-02',
				'place_of_birth' => 'Nkol Ngok',
				'seasons'        => array( '2023-24', '2024-25', '2025-26' ),
			),
			array(
				'name'           => 'Lisandro Martinez',
				'number'         => 6,
				'position'       => 'defender',
				'date_of_birth'  => '1998-01-18',
				'place_of_birth' => 'Gualeguay',
				'seasons'        => array( '2023-24', '2024-25', '2025-26' ),
			),
			array(
				'name'           => 'Bruno Fernandes',
				'number'         => 8,
				'position'       => 'midfield',
				'date_of_birth'  => '1994-09-08',
				'place_of_birth' => 'Maia',
				'seasons'        => array( '2023-24', '2024-25', '2025-26' ),
			),
			array(
				'name'           => 'Harry Maguire',
				'number'         => 5,
				'position'       => 'defender',
				'date_of_birth'  => '1993-03-05',
				'place_of_birth' => 'Sheffield',
				'seasons'        => array( '2023-24', '2024-25' ),
			),
			array(
				'name'           => 'Mason Mount',
				'number'         => 7,
				'position'       => 'midfield',
				'date_of_birth'  => '1999-01-10',
				'place_of_birth' => 'Portsmouth',
				'seasons'        => array( '2023-24' ),
			),
			array(
				'name'           => 'Kobbie Mainoo',
				'number'         => 37,
				'position'       => 'midfield',
				'date_of_birth'  => '2005-04-19',
				'place_of_birth' => 'Stockport',
				'seasons'        => array( '2024-25', '2025-26' ),
			),
			array(
				'name'           => 'Rasmus Hojlund',
				'number'         => 9,
				'position'       => 'striker',
				'date_of_birth'  => '2003-02-04',
				'place_of_birth' => 'Copenhagen',
				'seasons'        => array( '2024-25', '2025-26' ),
			),
			array(
				'name'           => 'Alejandro Garnacho',
				'number'         => 17,
				'position'       => 'striker',
				'date_of_birth'  => '2004-07-01',
				'place_of_birth' => 'Madrid',
				'seasons'        => array( '2024-25', '2025-26' ),
			),
		);

		$player_ids      = array();
		$player_seasons  = new PlayerSeasonRepository();

		foreach ( $players as $player ) {
			$player_id                    = self::upsert_post(
				'tss-players',
				$player['name'],
				array(
					'post_content' => $player['name'] . ' demo profile generated for block editor testing.',
				)
			);
			$player_ids[ $player['name'] ] = $player_id;

			update_post_meta( $player_id, 'tss_shirt_number', $player['number'] );
			update_post_meta( $player_id, 'tss_position', $player['position'] );
			update_post_meta( $player_id, 'tss_date_of_birth', $player['date_of_birth'] );
			update_post_meta( $player_id, 'tss_place_of_birth', $player['place_of_birth'] );
			update_post_meta( $player_id, 'tss_height', '' );
			update_post_meta( $player_id, 'tss_weight', '' );
			update_post_meta( $player_id, 'tss_previous_clubs', '' );
			update_post_meta( $player_id, 'tss_player_season_ids', self::map_season_keys_to_ids( $player['seasons'], $season_ids ) );
			update_post_meta( $player_id, '_test_added', 1 );

			$player_seasons->replace_player_seasons(
				$player_id,
				self::map_season_keys_to_ids( $player['seasons'], $season_ids )
			);
		}

		$matches = array(
			array(
				'title'                   => 'Manchester United vs Arsenal 2023-24',
				'season_key'              => '2023-24',
				'date'                    => '2024-03-17',
				'time'                    => '18:30',
				'location'                => 'home',
				'opponent_id'             => $team_ids['Arsenal'],
				'match_type_id'           => $match_type_ids['Premier League'],
				'goals_for'               => 2,
				'goals_against'           => 1,
				'attendance'              => 73612,
				'starters'                => array(
					$player_ids['Andre Onana'],
					$player_ids['Lisandro Martinez'],
					$player_ids['Harry Maguire'],
					$player_ids['Bruno Fernandes'],
					$player_ids['Mason Mount'],
				),
				'substitutes'             => array( $player_ids['Harry Maguire'] ),
				'substitutions'           => array(
					array(
						'playerIn'  => $player_ids['Harry Maguire'],
						'playerOut' => $player_ids['Mason Mount'],
						'minute'    => 68,
					),
				),
				'goals'                   => array(
					array(
						'playerId'  => $player_ids['Bruno Fernandes'],
						'minute'    => 33,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
					array(
						'playerId'  => $player_ids['Mason Mount'],
						'minute'    => 79,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
				),
				'yellow_cards'            => array(
					array(
						'playerId' => $player_ids['Lisandro Martinez'],
						'minute'   => 55,
					),
				),
				'red_cards'               => array(),
				'show_opponent_stats'     => true,
				'opponent_starters'       => "David Raya\nWilliam Saliba\nDeclan Rice",
				'opponent_substitutes'    => "Gabriel Jesus\nLeandro Trossard",
				'opponent_substitutions'  => "Gabriel Jesus -> Kai Havertz (63)",
				'opponent_goals'          => "Bukayo Saka (51)",
				'opponent_yellows'        => "Declan Rice (72)",
				'opponent_reds'           => '',
			),
			array(
				'title'                   => 'Liverpool vs Manchester United 2024-25',
				'season_key'              => '2024-25',
				'date'                    => '2024-09-14',
				'time'                    => '16:00',
				'location'                => 'away',
				'opponent_id'             => $team_ids['Liverpool'],
				'match_type_id'           => $match_type_ids['Premier League'],
				'goals_for'               => 1,
				'goals_against'           => 1,
				'attendance'              => 61276,
				'starters'                => array(
					$player_ids['Andre Onana'],
					$player_ids['Lisandro Martinez'],
					$player_ids['Harry Maguire'],
					$player_ids['Bruno Fernandes'],
					$player_ids['Kobbie Mainoo'],
					$player_ids['Rasmus Hojlund'],
				),
				'substitutes'             => array( $player_ids['Alejandro Garnacho'] ),
				'substitutions'           => array(
					array(
						'playerIn'  => $player_ids['Alejandro Garnacho'],
						'playerOut' => $player_ids['Harry Maguire'],
						'minute'    => 70,
					),
				),
				'goals'                   => array(
					array(
						'playerId'  => $player_ids['Rasmus Hojlund'],
						'minute'    => 61,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
				),
				'yellow_cards'            => array(
					array(
						'playerId' => $player_ids['Bruno Fernandes'],
						'minute'   => 44,
					),
				),
				'red_cards'               => array(),
				'show_opponent_stats'     => true,
				'opponent_starters'       => "Alisson\nVirgil van Dijk\nAlexis Mac Allister",
				'opponent_substitutes'    => "Darwin Nunez\nHarvey Elliott",
				'opponent_substitutions'  => "Darwin Nunez -> Diogo Jota (70)",
				'opponent_goals'          => "Mohamed Salah (24 pen.)",
				'opponent_yellows'        => '',
				'opponent_reds'           => '',
			),
			array(
				'title'                   => 'Manchester United vs Chelsea 2024-25',
				'season_key'              => '2024-25',
				'date'                    => '2025-02-12',
				'time'                    => '21:00',
				'location'                => 'home',
				'opponent_id'             => $team_ids['Chelsea'],
				'match_type_id'           => $match_type_ids['FA Cup'],
				'goals_for'               => 2,
				'goals_against'           => 0,
				'attendance'              => 70120,
				'starters'                => array(
					$player_ids['Andre Onana'],
					$player_ids['Lisandro Martinez'],
					$player_ids['Bruno Fernandes'],
					$player_ids['Kobbie Mainoo'],
					$player_ids['Rasmus Hojlund'],
					$player_ids['Alejandro Garnacho'],
				),
				'substitutes'             => array( $player_ids['Harry Maguire'] ),
				'substitutions'           => array(),
				'goals'                   => array(
					array(
						'playerId'  => $player_ids['Alejandro Garnacho'],
						'minute'    => 18,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
					array(
						'playerId'  => $player_ids['Bruno Fernandes'],
						'minute'    => 82,
						'isPenalty' => true,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
				),
				'yellow_cards'            => array(),
				'red_cards'               => array(),
				'show_opponent_stats'     => true,
				'opponent_starters'       => "Robert Sanchez\nReece James\nEnzo Fernandez",
				'opponent_substitutes'    => "Nicolas Jackson\nMykhailo Mudryk",
				'opponent_substitutions'  => "Nicolas Jackson -> Christopher Nkunku (62)",
				'opponent_goals'          => '',
				'opponent_yellows'        => "Enzo Fernandez (49)",
				'opponent_reds'           => '',
			),
			array(
				'title'                   => 'Manchester United vs Real Sociedad 2025-26',
				'season_key'              => '2025-26',
				'date'                    => '2025-10-02',
				'time'                    => '20:00',
				'location'                => 'home',
				'opponent_id'             => $team_ids['Real Sociedad'],
				'match_type_id'           => $match_type_ids['Europa League'],
				'goals_for'               => 3,
				'goals_against'           => 0,
				'attendance'              => 70110,
				'starters'                => array(
					$player_ids['Andre Onana'],
					$player_ids['Lisandro Martinez'],
					$player_ids['Bruno Fernandes'],
					$player_ids['Kobbie Mainoo'],
					$player_ids['Rasmus Hojlund'],
					$player_ids['Alejandro Garnacho'],
				),
				'substitutes'             => array( $player_ids['Lisandro Martinez'], $player_ids['Kobbie Mainoo'] ),
				'substitutions'           => array(
					array(
						'playerIn'  => $player_ids['Lisandro Martinez'],
						'playerOut' => $player_ids['Bruno Fernandes'],
						'minute'    => 74,
					),
					array(
						'playerIn'  => $player_ids['Kobbie Mainoo'],
						'playerOut' => $player_ids['Rasmus Hojlund'],
						'minute'    => 81,
					),
				),
				'goals'                   => array(
					array(
						'playerId'  => $player_ids['Bruno Fernandes'],
						'minute'    => 11,
						'isPenalty' => true,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
					array(
						'playerId'  => $player_ids['Kobbie Mainoo'],
						'minute'    => 49,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
					array(
						'playerId'  => $player_ids['Rasmus Hojlund'],
						'minute'    => 87,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
				),
				'yellow_cards'            => array(),
				'red_cards'               => array(),
				'show_opponent_stats'     => true,
				'opponent_starters'       => "Alex Remiro\nIgor Zubeldia\nMikel Oyarzabal",
				'opponent_substitutes'    => "Takefusa Kubo\nArsen Zakharyan",
				'opponent_substitutions'  => "Takefusa Kubo -> Ander Barrenetxea (65)",
				'opponent_goals'          => '',
				'opponent_yellows'        => "Igor Zubeldia (38)",
				'opponent_reds'           => '',
			),
			array(
				'title'                   => 'Tottenham vs Manchester United 2025-26',
				'season_key'              => '2025-26',
				'date'                    => '2025-11-23',
				'time'                    => '18:30',
				'location'                => 'away',
				'opponent_id'             => $team_ids['Tottenham'],
				'match_type_id'           => $match_type_ids['Premier League'],
				'goals_for'               => 2,
				'goals_against'           => 2,
				'attendance'              => 62500,
				'starters'                => array(
					$player_ids['Andre Onana'],
					$player_ids['Lisandro Martinez'],
					$player_ids['Bruno Fernandes'],
					$player_ids['Kobbie Mainoo'],
					$player_ids['Rasmus Hojlund'],
					$player_ids['Alejandro Garnacho'],
				),
				'substitutes'             => array( $player_ids['Kobbie Mainoo'] ),
				'substitutions'           => array(),
				'goals'                   => array(
					array(
						'playerId'  => $player_ids['Alejandro Garnacho'],
						'minute'    => 12,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
					array(
						'playerId'  => $player_ids['Rasmus Hojlund'],
						'minute'    => 76,
						'isPenalty' => false,
						'isOwnGoal' => false,
						'ownScorer' => '',
					),
				),
				'yellow_cards'            => array(
					array(
						'playerId' => $player_ids['Lisandro Martinez'],
						'minute'   => 58,
					),
				),
				'red_cards'               => array(),
				'show_opponent_stats'     => true,
				'opponent_starters'       => "Guglielmo Vicario\nCristian Romero\nJames Maddison",
				'opponent_substitutes'    => "Richarlison\nDejan Kulusevski",
				'opponent_substitutions'  => "Richarlison -> Son Heung-min (71)",
				'opponent_goals'          => "Son Heung-min (34)\nJames Maddison (88)",
				'opponent_yellows'        => '',
				'opponent_reds'           => '',
			),
		);

		foreach ( $matches as $match ) {
			$match_id = self::upsert_post(
				'tss-matches',
				$match['title'],
				array(
					'post_content' => '<!-- wp:tss/match-summary /-->',
				)
			);

			update_post_meta( $match_id, 'tss_match_date', $match['date'] );
			update_post_meta( $match_id, 'tss_match_time', $match['time'] );
			update_post_meta( $match_id, 'tss_match_location', $match['location'] );
			update_post_meta( $match_id, 'tss_match_season', $season_ids[ $match['season_key'] ] );
			update_post_meta( $match_id, 'tss_match_matchtype', $match['match_type_id'] );
			update_post_meta( $match_id, 'tss_match_additional_matchtype', '' );
			update_post_meta( $match_id, 'tss_match_attendance', $match['attendance'] );
			update_post_meta( $match_id, 'tss_match_opponent', $match['opponent_id'] );
			update_post_meta( $match_id, 'tss_match_goals_for', $match['goals_for'] );
			update_post_meta( $match_id, 'tss_match_goals_against', $match['goals_against'] );
			update_post_meta( $match_id, 'tss_match_goals_for_penalties', 0 );
			update_post_meta( $match_id, 'tss_match_goals_against_penalties', 0 );
			update_post_meta( $match_id, 'tss_match_overtime', false );
			update_post_meta( $match_id, 'tss_match_penalties', false );
			update_post_meta( $match_id, 'tss_match_calculate_stats', true );
			update_post_meta( $match_id, 'tss_match_show_opponent_stats', $match['show_opponent_stats'] );
			update_post_meta( $match_id, 'tss_match_starters', $match['starters'] );
			update_post_meta( $match_id, 'tss_match_substitutes', $match['substitutes'] );
			update_post_meta( $match_id, 'tss_match_substitutions', $match['substitutions'] );
			update_post_meta( $match_id, 'tss_match_goals', $match['goals'] );
			update_post_meta( $match_id, 'tss_match_yellow_cards', $match['yellow_cards'] );
			update_post_meta( $match_id, 'tss_match_red_cards', $match['red_cards'] );
			update_post_meta( $match_id, 'tss_match_opponent_starters', $match['opponent_starters'] );
			update_post_meta( $match_id, 'tss_match_opponent_substitutes', $match['opponent_substitutes'] );
			update_post_meta( $match_id, 'tss_match_opponent_substitutions', $match['opponent_substitutions'] );
			update_post_meta( $match_id, 'tss_match_opponent_goals', $match['opponent_goals'] );
			update_post_meta( $match_id, 'tss_match_opponent_yellows', $match['opponent_yellows'] );
			update_post_meta( $match_id, 'tss_match_opponent_reds', $match['opponent_reds'] );
			update_post_meta( $match_id, '_test_added', 1 );
			Sync::sync_match( $match_id, get_post( $match_id ) );
		}
	}

	public static function purge(): void {
		global $wpdb;

		$all_test_posts = array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => array( 'tss-seasons', 'tss-matchtypes', 'tss-opponents', 'tss-players', 'tss-matches' ),
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'meta_key'       => '_test_added',
					'meta_value'     => 1,
					'fields'         => 'ids',
				)
			)
		);

		$player_ids = array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => 'tss-players',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'meta_key'       => '_test_added',
					'meta_value'     => 1,
					'fields'         => 'ids',
				)
			)
		);

		$match_ids = array_map(
			'intval',
			get_posts(
				array(
					'post_type'      => 'tss-matches',
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'meta_key'       => '_test_added',
					'meta_value'     => 1,
					'fields'         => 'ids',
				)
			)
		);

		if ( array() !== $match_ids ) {
			$match_placeholders = implode( ',', array_fill( 0, count( $match_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Tables::match_lineups() . " WHERE match_id IN ({$match_placeholders})", ...$match_ids ) );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Tables::match_events() . " WHERE match_id IN ({$match_placeholders})", ...$match_ids ) );
		}

		if ( array() !== $player_ids ) {
			$player_placeholders = implode( ',', array_fill( 0, count( $player_ids ), '%d' ) );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Tables::player_seasons() . " WHERE player_id IN ({$player_placeholders})", ...$player_ids ) );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Tables::match_lineups() . " WHERE player_id IN ({$player_placeholders})", ...$player_ids ) );
			$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . Tables::match_events() . " WHERE player_id IN ({$player_placeholders}) OR related_player_id IN ({$player_placeholders})", ...array_merge( $player_ids, $player_ids ) ) );
		}

		foreach ( $all_test_posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}

	private static function upsert_post( string $post_type, string $title, array $overrides = array() ): int {
		$existing = self::find_existing_post( $post_type, $title );

		$postarr = array_merge(
			array(
				'post_type'    => $post_type,
				'post_title'   => $title,
				'post_status'  => 'publish',
				'post_content' => '',
			),
			$overrides
		);

		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$post_id       = (int) wp_update_post( $postarr );
			update_post_meta( $post_id, '_test_added', 1 );

			return $post_id;
		}

		$post_id = (int) wp_insert_post( $postarr );
		update_post_meta( $post_id, '_test_added', 1 );

		return $post_id;
	}

	private static function find_existing_post( string $post_type, string $title ): ?\WP_Post {
		$query = new \WP_Query(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'any',
				'posts_per_page'         => 1,
				'title'                  => $title,
				'orderby'                => 'ID',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( empty( $query->posts ) ) {
			return null;
		}

		return $query->posts[0];
	}

	private static function map_season_keys_to_ids( array $season_keys, array $season_ids ): array {
		return array_values(
			array_filter(
				array_map(
					static fn( string $season_key ): int => (int) ( $season_ids[ $season_key ] ?? 0 ),
					$season_keys
				)
			)
		);
	}
}
