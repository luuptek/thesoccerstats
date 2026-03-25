<?php

defined( 'ABSPATH' ) || exit;

$use_current = ! isset( $attributes['useCurrent'] ) || (bool) $attributes['useCurrent'];
$match_id    = isset( $attributes['matchId'] ) ? (int) $attributes['matchId'] : 0;

if ( $use_current && get_post_type() === 'tss-matches' ) {
	$match_id = get_the_ID();
}

$match = ( new \TSS\Data\MatchRepository() )->get_match_summary( $match_id );

if ( array() === $match ) {
	return '';
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'tss-match-summary' ) ); ?>>
	<div class="tss-match-summary__meta">
		<span><?php echo esc_html( $match['formatted_datetime'] ); ?></span>
		<span><?php echo esc_html( $match['type_label'] ); ?></span>
		<span><?php echo esc_html( $match['status_label'] ); ?></span>
	</div>
	<div class="tss-match-summary__score">
		<div class="tss-match-summary__team">
			<span class="tss-match-summary__team-name"><?php echo esc_html( $match['home_team'] ); ?></span>
			<strong><?php echo esc_html( $match['home_goals'] ); ?></strong>
		</div>
		<div class="tss-match-summary__divider">:</div>
		<div class="tss-match-summary__team">
			<strong><?php echo esc_html( $match['away_goals'] ); ?></strong>
			<span class="tss-match-summary__team-name"><?php echo esc_html( $match['away_team'] ); ?></span>
		</div>
	</div>
	<?php if ( $match['attendance'] ) : ?>
		<div class="tss-match-summary__attendance"><?php esc_html_e( 'Attendance', 'tss' ); ?>: <?php echo esc_html( number_format_i18n( $match['attendance'] ) ); ?></div>
	<?php endif; ?>
	<div class="tss-match-summary__details">
		<div class="tss-match-summary__column">
			<div class="tss-match-summary__card">
				<h3><?php esc_html_e( 'Lineup', 'tss' ); ?></h3>
				<?php if ( $match['starter_names'] ) : ?>
					<ul class="tss-match-summary__list">
						<?php foreach ( $match['starter_names'] as $player_name ) : ?>
							<li><?php echo esc_html( $player_name ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No starters recorded.', 'tss' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="tss-match-summary__card">
				<h3><?php esc_html_e( 'Bench', 'tss' ); ?></h3>
				<?php if ( $match['substitute_names'] ) : ?>
					<ul class="tss-match-summary__list">
						<?php foreach ( $match['substitute_names'] as $player_name ) : ?>
							<li><?php echo esc_html( $player_name ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No substitutes recorded.', 'tss' ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $match['has_opponent_stats'] ) : ?>
				<div class="tss-match-summary__card">
					<h3><?php echo esc_html( sprintf( __( '%s Details', 'tss' ), $match['opponent'] ?: __( 'Opponent', 'tss' ) ) ); ?></h3>
					<div class="tss-match-summary__opponent-sections">
						<?php foreach ( $match['opponent_stats'] as $section ) : ?>
							<div class="tss-match-summary__opponent-section">
								<h4><?php echo esc_html( $section['label'] ); ?></h4>
								<ul class="tss-match-summary__list">
									<?php foreach ( $section['items'] as $item ) : ?>
										<li><?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<div class="tss-match-summary__column">
			<div class="tss-match-summary__card">
				<h3><?php esc_html_e( 'Goals', 'tss' ); ?></h3>
				<?php if ( $match['goals_list'] ) : ?>
					<ul class="tss-match-summary__list">
						<?php foreach ( $match['goals_list'] as $goal ) : ?>
							<li>
								<?php echo esc_html( $goal['label'] ); ?>
								<span class="tss-match-summary__minute">(<?php echo esc_html( $goal['minute'] ); ?>')</span>
								<?php if ( $goal['is_penalty'] ) : ?><span class="tss-match-summary__tag"><?php esc_html_e( 'pen.', 'tss' ); ?></span><?php endif; ?>
								<?php if ( $goal['is_own_goal'] ) : ?><span class="tss-match-summary__tag"><?php esc_html_e( 'own goal', 'tss' ); ?></span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No goals recorded.', 'tss' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="tss-match-summary__card">
				<h3><?php esc_html_e( 'Substitutions', 'tss' ); ?></h3>
				<?php if ( $match['substitution_list'] ) : ?>
					<ul class="tss-match-summary__list">
						<?php foreach ( $match['substitution_list'] as $substitution ) : ?>
							<li>
								<?php echo esc_html( $substitution['player_in'] ); ?>
								<span class="tss-match-summary__arrow">→</span>
								<?php echo esc_html( $substitution['player_out'] ); ?>
								<span class="tss-match-summary__minute">(<?php echo esc_html( $substitution['minute'] ); ?>')</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'No substitutions recorded.', 'tss' ); ?></p>
				<?php endif; ?>
			</div>
			<div class="tss-match-summary__cards-grid">
				<div class="tss-match-summary__card">
					<h3><?php esc_html_e( 'Yellow Cards', 'tss' ); ?></h3>
					<?php if ( $match['yellow_card_list'] ) : ?>
						<ul class="tss-match-summary__list">
							<?php foreach ( $match['yellow_card_list'] as $card ) : ?>
								<li><?php echo esc_html( $card['label'] ); ?> <span class="tss-match-summary__minute">(<?php echo esc_html( $card['minute'] ); ?>')</span></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p><?php esc_html_e( 'None', 'tss' ); ?></p>
					<?php endif; ?>
				</div>
				<div class="tss-match-summary__card">
					<h3><?php esc_html_e( 'Red Cards', 'tss' ); ?></h3>
					<?php if ( $match['red_card_list'] ) : ?>
						<ul class="tss-match-summary__list">
							<?php foreach ( $match['red_card_list'] as $card ) : ?>
								<li><?php echo esc_html( $card['label'] ); ?> <span class="tss-match-summary__minute">(<?php echo esc_html( $card['minute'] ); ?>')</span></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p><?php esc_html_e( 'None', 'tss' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php if ( $match['content'] ) : ?>
		<div class="tss-match-summary__content"><?php echo $match['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
</section>
<?php
