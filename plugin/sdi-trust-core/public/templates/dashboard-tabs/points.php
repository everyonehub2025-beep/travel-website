<?php
/**
 * Dashboard tab: Points — full ledger history.
 *
 * @package SDI_Trust_Core
 * @var array<int,array> $ledger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reason_labels = array(
	'referral_approved' => __( 'Referral approved', 'sdi-trust-core' ),
	'manual_adjust'      => __( 'Manual adjustment', 'sdi-trust-core' ),
	'admin_revoke'       => __( 'Administrative correction', 'sdi-trust-core' ),
	'fintech_purchase'   => __( 'Eligible purchase', 'sdi-trust-core' ),
);
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Points History', 'sdi-trust-core' ); ?></h2>

<?php if ( empty( $ledger ) ) : ?>
	<p class="sdi-empty"><?php esc_html_e( 'No points activity yet.', 'sdi-trust-core' ); ?></p>
<?php else : ?>
	<div class="sdi-table-scroll">
		<table class="sdi-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Date', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Reason', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Amount', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Balance', 'sdi-trust-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $ledger as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $entry['created_at'] ) ); ?></td>
						<td>
							<?php echo esc_html( $reason_labels[ $entry['reason'] ] ?? $entry['reason'] ); ?>
							<?php if ( ! empty( $entry['note'] ) ) : ?>
								<span class="sdi-table__note"><?php echo esc_html( $entry['note'] ); ?></span>
							<?php endif; ?>
						</td>
						<td class="sdi-table__amount <?php echo ( $entry['points'] >= 0 ) ? 'is-positive' : 'is-negative'; ?>">
							<?php echo esc_html( ( $entry['points'] >= 0 ? '+' : '' ) . number_format_i18n( $entry['points'] ) ); ?>
						</td>
						<td><?php echo esc_html( number_format_i18n( $entry['running_balance'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
