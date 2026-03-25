<?php

defined( 'ABSPATH' ) || exit;

$player_id = isset( $attributes['playerId'] ) ? (int) $attributes['playerId'] : 0;

if ( ! $player_id && get_post_type() === 'tss-players' ) {
	$player_id = get_the_ID();
}

$profile = ( new \TSS\Data\PlayerRepository() )->get_player_profile( $player_id );

if ( array() === $profile ) {
	return '';
}


?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'tss-player-profile' ) ); ?>>
	<div class="tss-player-profile__header">
		<div>
			<h2 class="tss-player-profile__title">
				<?php echo esc_html( $profile['name'] ); ?>
				<?php if ( $profile['number'] ) : ?>
					<span class="tss-player-profile__number"><?php echo esc_html( '#' . $profile['number'] ); ?></span>
				<?php endif; ?>
			</h2>
			<div class="tss-meta-grid">
				<?php if ( $profile['date_of_birth'] ) : ?><div><strong><?php esc_html_e( 'Date of birth', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['date_of_birth'] ); ?></div><?php endif; ?>
				<?php if ( $profile['place_of_birth'] ) : ?><div><strong><?php esc_html_e( 'Place of birth', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['place_of_birth'] ); ?></div><?php endif; ?>
				<?php if ( $profile['position'] ) : ?><div><strong><?php esc_html_e( 'Position', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['position'] ); ?></div><?php endif; ?>
				<?php if ( $profile['height'] ) : ?><div><strong><?php esc_html_e( 'Height', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['height'] ); ?></div><?php endif; ?>
				<?php if ( $profile['weight'] ) : ?><div><strong><?php esc_html_e( 'Weight', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['weight'] ); ?></div><?php endif; ?>
				<?php if ( $profile['previous_clubs'] ) : ?><div><strong><?php esc_html_e( 'Previous clubs', 'tss' ); ?>:</strong> <?php echo esc_html( $profile['previous_clubs'] ); ?></div><?php endif; ?>
			</div>
		</div>
		<?php if ( $profile['image'] ) : ?>
			<img class="tss-player-profile__image" src="<?php echo esc_url( $profile['image'] ); ?>" alt="<?php echo esc_attr( $profile['name'] ); ?>">
		<?php endif; ?>
	</div>
	<?php if ( $profile['description'] ) : ?>
		<div class="tss-player-profile__content">
			<?php echo $profile['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</section>
