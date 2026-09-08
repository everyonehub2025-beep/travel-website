<?php
/**
 * Dashboard tab: Refer — submission form + the member's own referral list.
 *
 * @package SDI_Trust_Core
 * @var array<int,array> $referrals
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notice = null;
if ( isset( $_GET['sdi_referral'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
	$result = sanitize_key( wp_unslash( $_GET['sdi_referral'] ) );
	$notice = array(
		'type'    => ( 'success' === $result ) ? 'success' : 'error',
		'message' => isset( $_GET['sdi_message'] ) ? sanitize_text_field( wp_unslash( $_GET['sdi_message'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	);
}

$action_url  = admin_url( 'admin-post.php' );
$nonce_field = wp_nonce_field( 'sdi_submit_referral', 'sdi_referral_nonce', true, false );
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Refer a New Member', 'sdi-trust-core' ); ?></h2>

<?php include __DIR__ . '/../shortcode-referral-form.php'; ?>

<h3 class="sdi-panel__subtitle"><?php esc_html_e( 'Your Referrals', 'sdi-trust-core' ); ?></h3>

<?php include __DIR__ . '/../shortcode-referral-list.php'; ?>
