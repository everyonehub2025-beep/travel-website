<?php
/**
 * Site footer: logo/address/EIN, Organization links, Policies links, and
 * a compact "send us a note" form — matching the design system's footer.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_footer_notice = null;
if ( isset( $_GET['sdi_inquiry'], $_GET['sdi_inquiry_type'] ) && 'footer_note' === sanitize_key( wp_unslash( $_GET['sdi_inquiry_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
	$sdi_footer_result  = sanitize_key( wp_unslash( $_GET['sdi_inquiry'] ) );
	$sdi_footer_notice = ( 'success' === $sdi_footer_result )
		? __( 'Message sent. We reply within two business days.', 'sdi-travel' )
		: __( 'Please enter a valid email and message.', 'sdi-travel' );
}
?>
</main>

<footer class="sdi-footer">
	<div class="sdi-container sdi-footer__grid">
		<div class="sdi-footer__col sdi-footer__col--org">
			<div class="sdi-footer__logo">
				<?php sdi_the_logo(); ?>
			</div>
			<p class="sdi-footer__address">
				<?php echo wp_kses_post( nl2br( esc_html( get_theme_mod( 'sdi_mailing_address', '[PLACEHOLDER: mailing address]' ) ) ) ); ?><br />
				<a href="mailto:<?php echo esc_attr( get_theme_mod( 'sdi_contact_email', '[PLACEHOLDER: contact email]' ) ); ?>"><?php echo esc_html( get_theme_mod( 'sdi_contact_email', '[PLACEHOLDER: contact email]' ) ); ?></a>
			</p>
			<p class="sdi-footer__ein">
				<?php
				printf(
					/* translators: %s: EIN number */
					esc_html__( '501(c)(3) nonprofit · EIN %s', 'sdi-travel' ),
					esc_html( get_theme_mod( 'sdi_ein', '[PLACEHOLDER: EIN]' ) )
				);
				?>
			</p>
		</div>

		<div class="sdi-footer__col">
			<h3 class="sdi-footer__heading"><?php esc_html_e( 'Organization', 'sdi-travel' ); ?></h3>
			<?php sdi_footer_menu( 'footer-organization' ); ?>
		</div>

		<div class="sdi-footer__col">
			<h3 class="sdi-footer__heading"><?php esc_html_e( 'Policies', 'sdi-travel' ); ?></h3>
			<?php sdi_footer_menu( 'footer-policies' ); ?>
		</div>

		<div class="sdi-footer__col sdi-footer__col--note">
			<h3 class="sdi-footer__heading"><?php esc_html_e( 'Send us a note', 'sdi-travel' ); ?></h3>
			<?php if ( $sdi_footer_notice ) : ?>
				<p class="sdi-footer__note-status"><?php echo esc_html( $sdi_footer_notice ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-footer__note-form">
				<input type="hidden" name="action" value="sdi_inquiry_submit" />
				<input type="hidden" name="sdi_inquiry_type" value="footer_note" />
				<?php wp_nonce_field( 'sdi_inquiry_submit_footer_note', 'sdi_inquiry_nonce' ); ?>
				<input type="text" name="sdi_inquiry_hp" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true" />
				<label class="screen-reader-text" for="sdi-footer-email"><?php esc_html_e( 'Your email', 'sdi-travel' ); ?></label>
				<input type="email" id="sdi-footer-email" name="email" required="required" placeholder="<?php esc_attr_e( 'Your email', 'sdi-travel' ); ?>" />
				<label class="screen-reader-text" for="sdi-footer-message"><?php esc_html_e( 'How can we help?', 'sdi-travel' ); ?></label>
				<textarea id="sdi-footer-message" name="message" rows="3" required="required" placeholder="<?php esc_attr_e( 'How can we help?', 'sdi-travel' ); ?>"></textarea>
				<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Send message', 'sdi-travel' ); ?></button>
			</form>
		</div>
	</div>

	<div class="sdi-container sdi-footer__bottom">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'sdi-travel' ); ?></span>
		<span><?php esc_html_e( 'Statistics, contact details and policy terms are placeholders pending client confirmation — see CONTENT-GAPS.md.', 'sdi-travel' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
