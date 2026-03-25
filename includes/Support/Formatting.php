<?php

namespace TSS\Support;

defined( 'ABSPATH' ) || exit;

class Formatting {
	public static function player_position_label( string $position ): string {
		$labels = array(
			'goalkeeper' => __( 'Goalkeeper', 'tss' ),
			'defender'   => __( 'Defender', 'tss' ),
			'midfield'   => __( 'Midfield', 'tss' ),
			'striker'    => __( 'Striker', 'tss' ),
		);

		return $labels[ $position ] ?? $position;
	}

	public static function match_status( array $match ): string {
		if ( ! empty( $match['penalties'] ) ) {
			return __( 'PEN.', 'tss' );
		}

		if ( ! empty( $match['overtime'] ) ) {
			return __( 'AET', 'tss' );
		}

		return __( 'FT', 'tss' );
	}

	public static function match_type_label( string $primary, string $secondary = '' ): string {
		if ( '' === $secondary ) {
			return $primary;
		}

		return $primary . ' / ' . $secondary;
	}

	public static function result_label( array $match ): string {
		if ( '' === (string) $match['home_goals'] || '' === (string) $match['away_goals'] ) {
			return '';
		}

		$result = $match['home_goals'] . '-' . $match['away_goals'];

		if ( ! empty( $match['penalties'] ) ) {
			$result .= ' (' . $match['home_penalties'] . '-' . $match['away_penalties'] . ') ' . __( 'PEN.', 'tss' );
		} elseif ( ! empty( $match['overtime'] ) ) {
			$result .= ' ' . __( 'AET', 'tss' );
		}

		return $result;
	}

	public static function formatted_match_datetime( string $date, string $time ): string {
		if ( '' === $date ) {
			return '';
		}

		$formatted = wp_date( get_option( 'date_format' ), strtotime( $date ) );

		if ( '' !== $time ) {
			$formatted .= ' @ ' . $time;
		}

		return $formatted;
	}
}
