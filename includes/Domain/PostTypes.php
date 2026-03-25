<?php

namespace TSS\Domain;

defined( 'ABSPATH' ) || exit;

class PostTypes {
	public static function register(): void {
		self::register_type(
			'tss-seasons',
			__( 'Seasons', 'tss' ),
			__( 'Season', 'tss' ),
			array( 'title', 'editor', 'thumbnail' ),
			'season'
		);

		self::register_type(
			'tss-opponents',
			__( 'Opponents', 'tss' ),
			__( 'Opponent', 'tss' ),
			array( 'title', 'editor', 'thumbnail' ),
			'opponent'
		);

		self::register_type(
			'tss-players',
			__( 'Players', 'tss' ),
			__( 'Player', 'tss' ),
			array( 'title', 'editor', 'thumbnail' ),
			'player'
		);

		self::register_type(
			'tss-matches',
			__( 'Matches', 'tss' ),
			__( 'Match', 'tss' ),
			array( 'title', 'editor' ),
			'match'
		);

		self::register_type(
			'tss-matchtypes',
			__( 'Match Types', 'tss' ),
			__( 'Match Type', 'tss' ),
			array( 'title' ),
			'match-type'
		);
	}

	private static function register_type( string $slug, string $plural, string $singular, array $supports, string $rewrite_slug ): void {
		if ( ! in_array( 'custom-fields', $supports, true ) ) {
			$supports[] = 'custom-fields';
		}

		register_post_type(
			$slug,
			array(
				'labels' => array(
					'name'               => $plural,
					'singular_name'      => $singular,
					'menu_name'          => $plural,
					'name_admin_bar'     => $singular,
					'add_new'            => __( 'Add New', 'tss' ),
					'add_new_item'       => sprintf( __( 'Add New %s', 'tss' ), $singular ),
					'edit_item'          => sprintf( __( 'Edit %s', 'tss' ), $singular ),
					'new_item'           => sprintf( __( 'New %s', 'tss' ), $singular ),
					'view_item'          => sprintf( __( 'View %s', 'tss' ), $singular ),
					'all_items'          => sprintf( __( 'All %s', 'tss' ), $plural ),
					'search_items'       => sprintf( __( 'Search %s', 'tss' ), $plural ),
					'not_found'          => sprintf( __( 'No %s found.', 'tss' ), strtolower( $plural ) ),
					'not_found_in_trash' => sprintf( __( 'No %s found in Trash.', 'tss' ), strtolower( $plural ) ),
				),
				'public'             => true,
				'has_archive'        => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-chart-bar',
				'rewrite'            => array( 'slug' => $rewrite_slug ),
				'supports'           => $supports,
			)
		);
	}
}
