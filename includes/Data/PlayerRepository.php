<?php

namespace TSS\Data;

use TSS\Support\Content;
use TSS\Support\Formatting;

defined( 'ABSPATH' ) || exit;

class PlayerRepository {
	public function get_player_profile( int $player_id ): array {
		$post = get_post( $player_id );

		if ( ! $post || 'tss-players' !== $post->post_type ) {
			return array();
		}

		$dob = (string) get_post_meta( $player_id, 'tss_date_of_birth', true );

		return array(
			'id'             => $player_id,
			'name'           => get_the_title( $player_id ),
			'number'         => (int) get_post_meta( $player_id, 'tss_shirt_number', true ),
			'place_of_birth' => (string) get_post_meta( $player_id, 'tss_place_of_birth', true ),
			'height'         => (string) get_post_meta( $player_id, 'tss_height', true ),
			'weight'         => (string) get_post_meta( $player_id, 'tss_weight', true ),
			'date_of_birth'  => $dob ? wp_date( get_option( 'date_format' ), strtotime( $dob ) ) : '',
			'position'       => Formatting::player_position_label( (string) get_post_meta( $player_id, 'tss_position', true ) ),
			'previous_clubs' => (string) get_post_meta( $player_id, 'tss_previous_clubs', true ),
			'description'    => Content::render_post_content_without_recursive_block( $post, 'tss/player-profile' ),
			'image'          => get_the_post_thumbnail_url( $player_id, 'large' ) ?: '',
			'season_ids'     => $this->get_player_season_ids( $player_id ),
		);
	}

	public function get_player_stats_by_season( int $player_id, int $season_id, int $match_type_id = 0 ): array {
		$matches = ( new MatchRepository() )->get_matches_by_season( $season_id, $match_type_id );
		$stats   = array(
			'starts'  => 0,
			'subs'    => 0,
			'goals'   => 0,
			'yellows' => 0,
			'reds'    => 0,
		);

		foreach ( $matches as $match ) {
			if ( ! $match['calculate_stats'] ) {
				continue;
			}

			if ( in_array( $player_id, $match['starters'], true ) ) {
				++$stats['starts'];
			}

			foreach ( $match['events'] as $event ) {
				if ( 'substitution' === $event['event_type'] && $player_id === (int) $event['player_id'] ) {
					++$stats['subs'];
				}
				if ( 'goal' === $event['event_type'] && $player_id === (int) $event['player_id'] && ! $event['is_own_goal'] ) {
					++$stats['goals'];
				}
				if ( 'yellow_card' === $event['event_type'] && $player_id === (int) $event['player_id'] ) {
					++$stats['yellows'];
				}
				if ( 'red_card' === $event['event_type'] && $player_id === (int) $event['player_id'] ) {
					++$stats['reds'];
				}
			}
		}

		return $stats;
	}

	public function get_player_stats_table( int $season_id, int $match_type_id = 0 ): array {
		$players = get_posts(
			array(
				'post_type'      => 'tss-players',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$rows = array();

		foreach ( $players as $player ) {
			$season_ids = $this->get_player_season_ids( $player->ID );

			if ( ! in_array( $season_id, $season_ids, true ) ) {
				continue;
			}

			$rows[] = array(
				'id'     => $player->ID,
				'name'   => get_the_title( $player->ID ),
				'number' => (int) get_post_meta( $player->ID, 'tss_shirt_number', true ),
				'stats'  => $this->get_player_stats_by_season( $player->ID, $season_id, $match_type_id ),
			);
		}

		usort(
			$rows,
			static function ( array $left, array $right ): int {
				return $left['number'] <=> $right['number'];
			}
		);

		return $rows;
	}

	private function get_player_season_ids( int $player_id ): array {
		$season_ids = ( new PlayerSeasonRepository() )->get_season_ids_for_player( $player_id );

		if ( array() !== $season_ids ) {
			return $season_ids;
		}

		return array_map( 'intval', (array) get_post_meta( $player_id, 'tss_player_season_ids', true ) );
	}
}
