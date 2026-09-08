<?php
/**
 * Admin view: Dashboard — at-a-glance operational summary.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$status_counts   = SDI_Referrals::get_status_counts();
$total_issued    = SDI_Points::get_total_issued();
$tier_distribution = SDI_Tiers::get_tier_distribution();
$thresholds      = SDI_Tiers::get_thresholds();

global $wpdb;
$recent_referrals = $wpdb->get_results( 'SELECT * FROM ' . SDI_Referrals::table() . ' ORDER BY created_at DESC LIMIT 10', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- static query, no user input.
$recent_ledger     = $wpdb->get_results( 'SELECT * FROM ' . SDI_Points::table() . ' ORDER BY created_at DESC LIMIT 10', ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
?>
<div class="wrap sdi-admin">
	<h1><?php esc_html_e( 'SDI Trust — Dashboard', 'sdi-trust-core' ); ?></h1>

	<div class="sdi-admin-stats">
		<div class="sdi-admin-stat-card">
			<span class="sdi-admin-stat-card__value"><?php echo esc_html( number_format_i18n( $status_counts['pending'] ) ); ?></span>
			<span class="sdi-admin-stat-card__label"><?php esc_html_e( 'Pending Referrals', 'sdi-trust-core' ); ?></span>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=sdi-trust-referrals&status=pending' ) ); ?>"><?php esc_html_e( 'Review now', 'sdi-trust-core' ); ?> &rarr;</a>
		</div>
		<div class="sdi-admin-stat-card">
			<span class="sdi-admin-stat-card__value"><?php echo esc_html( number_format_i18n( $total_issued ) ); ?></span>
			<span class="sdi-admin-stat-card__label"><?php esc_html_e( 'Total Points Issued', 'sdi-trust-core' ); ?></span>
		</div>
		<div class="sdi-admin-stat-card">
			<span class="sdi-admin-stat-card__value"><?php echo esc_html( number_format_i18n( $status_counts['approved'] ) ); ?></span>
			<span class="sdi-admin-stat-card__label"><?php esc_html_e( 'Approved Referrals', 'sdi-trust-core' ); ?></span>
		</div>
	</div>

	<h2><?php esc_html_e( 'Members Per Eligibility Level', 'sdi-trust-core' ); ?></h2>
	<table class="widefat striped sdi-admin-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Level', 'sdi-trust-core' ); ?></th>
				<th><?php esc_html_e( 'Members', 'sdi-trust-core' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php esc_html_e( 'No eligibility level reached', 'sdi-trust-core' ); ?></td>
				<td><?php echo esc_html( number_format_i18n( $tier_distribution['no_tier'] ?? 0 ) ); ?></td>
			</tr>
			<?php foreach ( $thresholds as $points => $amount ) : ?>
				<tr>
					<td><?php echo esc_html( number_format_i18n( $points ) . ' pts — up to $' . number_format_i18n( $amount ) ); ?></td>
					<td><?php echo esc_html( number_format_i18n( $tier_distribution[ (string) $points ] ?? 0 ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="sdi-admin-columns">
		<div class="sdi-admin-column">
			<h2><?php esc_html_e( 'Recent Referrals', 'sdi-trust-core' ); ?></h2>
			<?php if ( empty( $recent_referrals ) ) : ?>
				<p><?php esc_html_e( 'No referrals yet.', 'sdi-trust-core' ); ?></p>
			<?php else : ?>
				<ul class="sdi-admin-activity-list">
					<?php foreach ( $recent_referrals as $referral ) : ?>
						<li>
							<strong><?php echo esc_html( $referral['referred_name'] ); ?></strong>
							— <span class="sdi-admin-badge sdi-admin-badge--<?php echo esc_attr( $referral['status'] ); ?>"><?php echo esc_html( ucfirst( $referral['status'] ) ); ?></span>
							<span class="description"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $referral['created_at'] ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="sdi-admin-column">
			<h2><?php esc_html_e( 'Recent Points Activity', 'sdi-trust-core' ); ?></h2>
			<?php if ( empty( $recent_ledger ) ) : ?>
				<p><?php esc_html_e( 'No points activity yet.', 'sdi-trust-core' ); ?></p>
			<?php else : ?>
				<ul class="sdi-admin-activity-list">
					<?php foreach ( $recent_ledger as $entry ) : ?>
						<?php $user = get_userdata( $entry['user_id'] ); ?>
						<li>
							<strong><?php echo esc_html( $user ? $user->display_name : __( '(deleted user)', 'sdi-trust-core' ) ); ?></strong>
							<span class="<?php echo ( $entry['points'] >= 0 ) ? 'sdi-admin-amount--positive' : 'sdi-admin-amount--negative'; ?>">
								<?php echo esc_html( ( $entry['points'] >= 0 ? '+' : '' ) . $entry['points'] ); ?>
							</span>
							<span class="description"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $entry['created_at'] ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</div>
