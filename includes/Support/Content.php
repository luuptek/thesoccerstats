<?php

namespace TSS\Support;

defined( 'ABSPATH' ) || exit;

class Content {
	public static function render_post_content_without_recursive_block( \WP_Post $post, string $block_name ): string {
		if ( has_block( $block_name, $post ) ) {
			return '';
		}

		return apply_filters( 'the_content', $post->post_content );
	}
}
