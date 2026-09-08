<?php
/**
 * [sdi_referral_list] — a member's own referrals only.
 *
 * @package SDI_Trust_Core
 * @var array<int,array<string,mixed>> $referrals
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_labels = array(
	'pending'   => __( 'Pending', 'sdi-trust-core' ),
	'approved'  => __( 'Approved', 'sdi-trust-core' ),
	'rejected'  => __( 'Not Approved', 'sdi-trust-core' ),
	'duplicate' => __( 'Duplicate', 'sdi-trust-core' ),
);
?>
<div class="sdi-referral-list">
	<?php if ( empty( $referrals ) ) : ?>
		<p class="sdi-empty"><?php esc_html_e( 'You haven\'t submitted any referrals yet.', 'sdi-trust-core' ); ?></p>
	<?php else : ?>
		<table class="sdi-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Name', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Date', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Status', 'sdi-trust-core' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Points', 'sdi-trust-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $referrals as $referral ) : ?>
					<tr>
						<td><?php echo esc_html( $referral['referred_name'] ); ?></td>
						<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $referral['created_at'] ) ); ?></td>
						<td>
							<span class="sdi-badge sdi-badge--<?php echo esc_attr( $referral['status'] ); ?>">
								<?php echo esc_html( $status_labels[ $referral['status'] ] ?? $referral['status'] ); ?>
							</span>
						</td>
						<td><?php echo esc_html( $referral['points_awarded'] ? '+' . (int) $referral['points_awarded'] : '—' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
