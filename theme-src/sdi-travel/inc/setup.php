<?php
/**
 * Core theme setup: supports, menus, image sizes.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme supports and navigation menus.
 */
function sdi_theme_setup() {
	load_theme_textdomain( 'sdi-travel', SDI_THEME_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );

	// Elementor works best when the theme steps out of the way of full-width content.
	add_theme_support( 'elementor' );

	register_nav_menus(
		array(
			'primary'            => __( 'Primary Navigation', 'sdi-travel' ),
			'footer-organization' => __( 'Footer — Organization', 'sdi-travel' ),
			'footer-policies'     => __( 'Footer — Policies', 'sdi-travel' ),
		)
	);

	add_image_size( 'sdi-card', 640, 480, true );
	add_image_size( 'sdi-wide', 1600, 900, true );
}
add_action( 'after_setup_theme', 'sdi_theme_setup' );

/**
 * Fall back to the bundled SDI mark as the browser-tab favicon until the
 * client sets a real Site Icon in Customizer > Site Identity.
 */
function sdi_fallback_site_icon() {
	if ( has_site_icon() ) {
		return;
	}
	?>
	<link rel="icon" href="<?php echo esc_url( SDI_THEME_URI . '/assets/images/sdi-favicon-32.png' ); ?>" sizes="32x32" />
	<link rel="icon" href="<?php echo esc_url( SDI_THEME_URI . '/assets/images/sdi-favicon-192.png' ); ?>" sizes="192x192" />
	<link rel="apple-touch-icon" href="<?php echo esc_url( SDI_THEME_URI . '/assets/images/sdi-favicon-192.png' ); ?>" />
	<?php
}
add_action( 'wp_head', 'sdi_fallback_site_icon' );
add_action( 'login_head', 'sdi_fallback_site_icon' );
add_action( 'admin_head', 'sdi_fallback_site_icon' );

/**
 * Content width for embeds/oEmbed, matching the container max-width token.
 *
 * @global int $content_width
 */
function sdi_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'sdi_content_width', 1200 );
}
add_action( 'after_setup_theme', 'sdi_content_width', 0 );

/**
 * Strip demo/legacy bloat inherited from the base theme's feature set that
 * SDI Travel Trust does not use: no WooCommerce catalog, no portfolio/
 * event/team custom post types — the site's only content is Pages
 * (Elementor-built) plus a standard blog for News & Resources.
 */
function sdi_disable_unused_supports() {
	remove_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'sdi_disable_unused_supports', 20 );
