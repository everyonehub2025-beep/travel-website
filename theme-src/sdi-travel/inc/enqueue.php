<?php
/**
 * Front-end and editor asset loading.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end styles/scripts. Split into small files (tokens, fonts,
 * base, animations) so any one of them can be swapped without touching
 * the others — rebranding is a tokens.css edit, not a hunt through a
 * monolithic stylesheet.
 */
function sdi_enqueue_assets() {
	wp_enqueue_style( 'sdi-fonts', SDI_THEME_URI . '/assets/css/fonts.css', array(), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-tokens', SDI_THEME_URI . '/assets/css/tokens.css', array(), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-base', SDI_THEME_URI . '/assets/css/base.css', array( 'sdi-tokens' ), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-animations', SDI_THEME_URI . '/assets/css/animations.css', array( 'sdi-base' ), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-layout', SDI_THEME_URI . '/assets/css/layout.css', array( 'sdi-base' ), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-elementor-overrides', SDI_THEME_URI . '/assets/css/elementor-overrides.css', array( 'sdi-base', 'elementor-frontend' ), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-forms', SDI_THEME_URI . '/assets/css/forms.css', array( 'sdi-base' ), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-components', SDI_THEME_URI . '/assets/css/components.css', array( 'sdi-base' ), SDI_THEME_VERSION );

	wp_enqueue_script( 'sdi-animations', SDI_THEME_URI . '/assets/js/animations.js', array(), SDI_THEME_VERSION, true );
	wp_enqueue_script( 'sdi-main', SDI_THEME_URI . '/assets/js/main.js', array(), SDI_THEME_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'sdi_enqueue_assets' );

/**
 * Load the same design tokens into the block/Elementor editor so authors
 * see real brand colors while editing, not editor defaults.
 */
function sdi_enqueue_editor_assets() {
	wp_enqueue_style( 'sdi-tokens', SDI_THEME_URI . '/assets/css/tokens.css', array(), SDI_THEME_VERSION );
	wp_enqueue_style( 'sdi-fonts', SDI_THEME_URI . '/assets/css/fonts.css', array(), SDI_THEME_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'sdi_enqueue_editor_assets' );

/**
 * Print the `js` class on <html> synchronously, before first paint, so
 * animations.css only hides .sdi-animate content when JavaScript is
 * actually available to reveal it again.
 */
function sdi_print_js_class() {
	echo "<script>document.documentElement.classList.add('js');</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, no dynamic data.
}
add_action( 'wp_head', 'sdi_print_js_class', 1 );
