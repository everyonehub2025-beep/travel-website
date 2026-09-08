<?php
/**
 * Dashboard tab: Referrals & Points.
 *
 * @package SDI_Trust_Core
 * @var array<int,array> $referrals
 * @var int              $confirmed_count
 * @var int              $pending_count
 * @var array<string,mixed> $tier_status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_invite_notice = null;
if ( isset( $_GET['sdi_referral'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
	$sdi_result = sanitize_key( wp_unslash( $_GET['sdi_referral'] ) );
	$sdi_invite_notice = array(
		'type'    => ( 'success' === $sdi_result ) ? 'success' : 'error',
		'message' => isset( $_GET['sdi_message'] ) ? sanitize_text_field( wp_unslash( $_GET['sdi_message'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
}
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Referrals & Points', 'sdi-trust-core' ); ?></h2>
<p style="max-width: 60ch; color: var(--sdi-silver);"><?php esc_html_e( 'One combined balance for your account. Pending referrals do not count toward a tier until the new membership is paid and the referral is approved.', 'sdi-trust-core' ); ?></p>

<div style="display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.4fr); gap: 1.75em; align-items: start; margin-top: 1.5em;">
	<div>
		<div class="sdi-overview-grid" style="grid-template-columns: 1fr; gap: 0.75em;">
			<div class="sdi-stat-card" style="flex-direction: row; align-items: baseline; justify-content: space-between;">
				<span class="sdi-stat-card__label"><?php esc_html_e( 'Confirmed', 'sdi-trust-core' ); ?></span>
				<span class="sdi-stat-card__value sdi-stat-card__value--small" style="color: var(--sdi-gold-deep);"><?php echo esc_html( $confirmed_count ); ?></span>
			</div>
			<div class="sdi-stat-card" style="flex-direction: row; align-items: baseline; justify-content: space-between;">
				<span class="sdi-stat-card__label"><?php esc_html_e( 'Pending', 'sdi-trust-core' ); ?></span>
				<span class="sdi-stat-card__value sdi-stat-card__value--small" style="color: var(--sdi-gold-deep);"><?php echo esc_html( $pending_count ); ?></span>
			</div>
			<div class="sdi-stat-card" style="flex-direction: row; align-items: baseline; justify-content: space-between;">
				<span class="sdi-stat-card__label"><?php esc_html_e( 'Points', 'sdi-trust-core' ); ?></span>
				<span class="sdi-stat-card__value sdi-stat-card__value--small" style="color: var(--sdi-gold-deep);"><?php echo esc_html( number_format_i18n( $tier_status['balance'] ) ); ?></span>
			</div>
		</div>

		<div style="margin-top: 1.5em; padding: 1.3em 1.2em; background: var(--sdi-offwhite); border: 1px solid var(--sdi-border);">
			<p style="margin: 0 0 0.7em; font-weight: 600; font-size: 0.8rem; color: var(--sdi-silver); text-transform: uppercase; letter-spacing: 0.08em;"><?php esc_html_e( 'Invite by email', 'sdi-trust-core' ); ?></p>
			<?php if ( $sdi_invite_notice ) : ?>
				<div class="sdi-notice sdi-notice--<?php echo esc_attr( $sdi_invite_notice['type'] ); ?>" style="margin-bottom: 1em;"><p><?php echo esc_html( $sdi_invite_notice['message'] ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: flex; flex-wrap: wrap; gap: 0.6em;">
				<input type="hidden" name="action" value="sdi_submit_referral" />
				<?php wp_nonce_field( 'sdi_submit_referral', 'sdi_referral_nonce' ); ?>
				<input type="email" name="sdi_referred_email" required="required" placeholder="<?php esc_attr_e( 'friend@example.com', 'sdi-trust-core' ); ?>" style="flex: 1 1 180px; min-width: 0; padding: 0.8em 0.9em; border: 1px solid var(--sdi-platinum); border-radius: 2px; background: var(--sdi-white);" />
				<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Send invite', 'sdi-trust-core' ); ?></button>
			</form>
		</div>
	</div>

	<div>
		<?php include __DIR__ . '/../shortcode-referral-list.php'; ?>
		<p class="sdi-table__note" style="margin-top: 1em;">
			<?php esc_html_e( 'Points do not expire while membership is active; a 90-day lapse forfeits the balance. See the Referral Program Rules on the Legal page.', 'sdi-trust-core' ); ?>
		</p>
	</div>
</div>
