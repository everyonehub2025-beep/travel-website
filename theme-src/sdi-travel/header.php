<?php
/**
 * Site header: logo left, nav center/right, Member Login link, gold Join Now button.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_login_url = apply_filters( 'sdi_member_login_url', wp_login_url( get_permalink() ? get_permalink() : home_url( '/' ) ) );
$sdi_join_url  = apply_filters( 'sdi_join_now_url', home_url( '/membership/' ) );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="sdi-skip-link" href="#sdi-content"><?php esc_html_e( 'Skip to content', 'sdi-travel' ); ?></a>

<header class="sdi-header">
	<div class="sdi-container sdi-header__inner">
		<div class="sdi-header__logo">
			<?php sdi_the_logo(); ?>
		</div>

		<button class="sdi-header__toggle" type="button" aria-expanded="false" aria-controls="sdi-primary-nav">
			<span></span><span></span><span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'sdi-travel' ); ?></span>
		</button>

		<nav class="sdi-header__nav" id="sdi-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'sdi-travel' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'sdi-primary-menu',
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>

		<div class="sdi-header__actions">
			<a class="sdi-header__login" href="<?php echo esc_url( $sdi_login_url ); ?>"><?php esc_html_e( 'Member Login', 'sdi-travel' ); ?></a>
			<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( $sdi_join_url ); ?>"><?php esc_html_e( 'Join Now', 'sdi-travel' ); ?></a>
		</div>
	</div>
</header>

<main id="sdi-content">
