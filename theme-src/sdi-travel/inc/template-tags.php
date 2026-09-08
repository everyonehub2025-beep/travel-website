<?php
/**
 * Small reusable template helpers shared by header.php/footer.php and the
 * page templates.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output a styled placeholder block in place of a photograph that hasn't
 * been supplied yet. Never ships a stock/demo photo — see
 * IMAGE-SHOT-LIST.md for what belongs in each slot.
 *
 * @param string $label   What image belongs here, e.g. "Hero — members traveling together".
 * @param string $ratio   CSS aspect-ratio value, e.g. "16/9", "4/3", "1/1".
 * @param string $variant 'navy' (default, on light sections) or 'light' (on navy sections).
 */
function sdi_image_placeholder( $label, $ratio = '16/9', $variant = 'navy' ) {
	$class = ( 'light' === $variant ) ? 'sdi-placeholder sdi-placeholder--light' : 'sdi-placeholder';
	?>
	<div class="<?php echo esc_attr( $class ); ?>" style="aspect-ratio: <?php echo esc_attr( $ratio ); ?>;">
		<span class="sdi-placeholder__label"><?php echo esc_html( $label ); ?></span>
	</div>
	<?php
}

/**
 * Print the site logo (image if set, text fallback otherwise), linked home.
 */
function sdi_the_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	?>
	<a class="sdi-header__logo-text" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
	<?php
}

/**
 * Print a footer nav menu, falling back to nothing (not an error) when a
 * client hasn't assigned one yet.
 *
 * @param string $location Registered nav menu location.
 */
function sdi_footer_menu( $location ) {
	if ( ! has_nav_menu( $location ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'sdi-footer__menu',
			'depth'          => 1,
		)
	);
}
