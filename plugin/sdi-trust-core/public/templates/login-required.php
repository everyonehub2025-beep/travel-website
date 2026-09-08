<?php
/**
 * Shown in place of any `[sdi_*]` shortcode when the visitor is logged out.
 *
 * @package SDI_Trust_Core
 * @var string $notice_override Unused placeholder for theme overrides.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sdi-notice sdi-notice--login">
	<p><?php echo esc_html( sdi_tc_text( 'login_required' ) ); ?></p>
	<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( function_exists( 'sdi_get_login_url' ) ? sdi_get_login_url() : wp_login_url( get_permalink() ? get_permalink() : home_url( '/' ) ) ); ?>">
		<?php esc_html_e( 'Member Login', 'sdi-trust-core' ); ?>
	</a>
</div>
