<?php
/**
 * Newsletter signup: a small, ESP-agnostic capture form.
 *
 * Stores signups locally (visible to the client without any extra plugin)
 * and fires an action any real email service provider integration can
 * hook — swapping in Mailchimp/Constant Contact/etc. later is a single
 * `add_action( 'sdi_newsletter_signup', ... )` call, not a rebuild.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * [sdi_newsletter_form] — used in the footer and the homepage newsletter
 * section alike.
 *
 * @return string
 */
function sdi_newsletter_form_shortcode() {
	$notice = null;

	if ( isset( $_GET['sdi_newsletter'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag, submission itself is nonce-verified.
		$result = sanitize_key( wp_unslash( $_GET['sdi_newsletter'] ) );
		$notice = ( 'success' === $result )
			? __( 'Thank you for subscribing.', 'sdi-travel' )
			: __( 'Please enter a valid email address.', 'sdi-travel' );
	}

	ob_start();
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-footer__newsletter-form">
		<input type="hidden" name="action" value="sdi_newsletter_signup" />
		<?php wp_nonce_field( 'sdi_newsletter_signup', 'sdi_newsletter_nonce', true, false ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nonce_field() output is already safe markup. ?>
		<input type="text" name="sdi_newsletter_hp" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true" />
		<label class="screen-reader-text" for="sdi_newsletter_email"><?php esc_html_e( 'Email address', 'sdi-travel' ); ?></label>
		<input type="email" id="sdi_newsletter_email" name="sdi_newsletter_email" placeholder="<?php esc_attr_e( 'Your email address', 'sdi-travel' ); ?>" required="required" />
		<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Subscribe', 'sdi-travel' ); ?></button>
	</form>
	<?php if ( $notice ) : ?>
		<p class="sdi-footer__newsletter-notice"><?php echo esc_html( $notice ); ?></p>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}
add_shortcode( 'sdi_newsletter_form', 'sdi_newsletter_form_shortcode' );

/**
 * Handle the newsletter form POST.
 */
function sdi_handle_newsletter_signup() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	$nonce_ok = isset( $_POST['sdi_newsletter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_newsletter_nonce'] ) ), 'sdi_newsletter_signup' );
	$honeypot_empty = empty( $_POST['sdi_newsletter_hp'] );
	$email = isset( $_POST['sdi_newsletter_email'] ) ? sanitize_email( wp_unslash( $_POST['sdi_newsletter_email'] ) ) : '';

	if ( ! $nonce_ok || ! $honeypot_empty || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'sdi_newsletter', 'error', $redirect ) );
		exit;
	}

	$signups = get_option( 'sdi_newsletter_signups', array() );

	if ( ! is_array( $signups ) ) {
		$signups = array();
	}

	if ( ! in_array( $email, wp_list_pluck( $signups, 'email' ), true ) ) {
		$signups[] = array(
			'email'   => $email,
			'date'    => current_time( 'mysql' ),
		);
		update_option( 'sdi_newsletter_signups', $signups, false );
	}

	/**
	 * Fires when a visitor subscribes via the newsletter form. Hook this
	 * to push the address into a real email service provider.
	 *
	 * @param string $email Subscriber email address.
	 */
	do_action( 'sdi_newsletter_signup', $email );

	wp_safe_redirect( add_query_arg( 'sdi_newsletter', 'success', $redirect ) );
	exit;
}
add_action( 'admin_post_sdi_newsletter_signup', 'sdi_handle_newsletter_signup' );
add_action( 'admin_post_nopriv_sdi_newsletter_signup', 'sdi_handle_newsletter_signup' );
