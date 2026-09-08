<?php
/**
 * Brand the wp-login.php screen: custom logo + brand colors.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Swap the login screen's WordPress logo for the site's custom logo, and
 * apply the SDI brand palette to the form. Uses the same custom-logo
 * setting as the header, so there is exactly one place to update the mark.
 */
function sdi_login_styles() {
	$logo_id  = get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : SDI_THEME_URI . '/assets/images/sdi-logo-900.png';
	?>
	<style>
		body.login {
			background: #F7F8FA;
			font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
		}
		body.login #login h1 a {
			background-image: url('<?php echo esc_url( $logo_url ); ?>');
			background-size: contain;
			width: 240px;
			height: 80px;
		}
		body.login #login form {
			border-radius: 2px;
			border: 1px solid #DCDEE3;
			box-shadow: none;
		}
		body.login .button-primary {
			background: #C89B3C !important;
			border-color: #C89B3C !important;
			color: #0A1D3B !important;
			text-shadow: none !important;
			box-shadow: none !important;
			border-radius: 2px !important;
		}
		body.login .button-primary:hover,
		body.login .button-primary:focus {
			background: #b98c30 !important;
			border-color: #b98c30 !important;
		}
		body.login input[type="text"],
		body.login input[type="password"],
		body.login input[type="email"] {
			border-radius: 2px;
			border-color: #DCDEE3;
		}
		body.login input[type="text"]:focus,
		body.login input[type="password"]:focus,
		body.login input[type="email"]:focus {
			border-color: #C89B3C;
			box-shadow: 0 0 0 1px #C89B3C;
		}
		body.login #nav a,
		body.login #backtoblog a {
			color: #47546c;
		}
		body.login #nav a:hover,
		body.login #backtoblog a:hover {
			color: #0A1D3B;
		}
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'sdi_login_styles' );

/**
 * Point the login logo link at the site home instead of WordPress.org.
 *
 * @return string
 */
function sdi_login_logo_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'sdi_login_logo_url' );

/**
 * Replace the login logo's title text.
 *
 * @return string
 */
function sdi_login_logo_url_title() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'sdi_login_logo_url_title' );
