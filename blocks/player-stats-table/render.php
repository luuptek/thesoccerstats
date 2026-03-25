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

$rows = ( new \TSS\Data\PlayerRepository() )->get_player_stats_table( $season_id, $match_type_id );
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'tss-card' ) ); ?>>
	<div class="tss-card__header">
		<h3><?php esc_html_e( 'Players', 'tss' ); ?></h3>
	</div>
	<div class="tss-table-wrap">
		<table class="tss-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Player', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Starts', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Subs In', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Goals', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Yellow', 'tss' ); ?></th>
					<th><?php esc_html_e( 'Red', 'tss' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( get_permalink( $row['id'] ) ); ?>"><?php echo esc_html( trim( '#' . $row['number'] . ' ' . $row['name'] ) ); ?></a></td>
						<td><?php echo esc_html( $row['stats']['starts'] ); ?></td>
						<td><?php echo esc_html( $row['stats']['subs'] ); ?></td>
						<td><?php echo esc_html( $row['stats']['goals'] ); ?></td>
						<td><?php echo esc_html( $row['stats']['yellows'] ); ?></td>
						<td><?php echo esc_html( $row['stats']['reds'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</section>
<?php
