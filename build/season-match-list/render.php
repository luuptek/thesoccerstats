<?php

defined( 'ABSPATH' ) || exit;

$season_id     = isset( $attributes['seasonId'] ) ? (int) $attributes['seasonId'] : 0;
$match_type_id = isset( $attributes['matchTypeId'] ) ? (int) $attributes['matchTypeId'] : 0;

if ( ! $season_id && get_post_type() === 'tss-seasons' ) {
	$season_id = get_the_ID();
}

if ( ! $season_id ) {
	return '';
}

$repository = new \TSS\Data\MatchRepository();
$matches    = $repository->get_matches_by_season( $season_id, $match_type_id );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'tss-card' ) ); ?>>
	<div class="tss-card__header">
		<h3><?php esc_html_e( 'Matches', 'tss' ); ?></h3>
	</div>
	<div class="tss-table-wrap">
		<table class="tss-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Home Team', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Away Team', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Match Type', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Result', 'tss' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $matches as $match ) : ?>
					<tr>
						<td><?php echo esc_html( \TSS\Support\Formatting::formatted_match_datetime( $match['date'], $match['time'] ) ); ?></td>
						<td><?php echo esc_html( $match['home_team'] ); ?></td>
						<td><?php echo esc_html( $match['away_team'] ); ?></td>
						<td><?php echo esc_html( \TSS\Support\Formatting::match_type_label( $match['match_type'], $match['additional_match_type'] ) ); ?></td>
						<td><a href="<?php echo esc_url( get_permalink( $match['id'] ) ); ?>"><?php echo esc_html( \TSS\Support\Formatting::result_label( $match ) ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
<?php
