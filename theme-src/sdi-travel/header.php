<?php
/**
 * Site header: sticky/blurred bar, logo left, single collapsing nav
 * (overflow items move into a "More ▾" dropdown), Log In + Become a
 * Member actions right.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_login_url = apply_filters( 'sdi_member_login_url', sdi_get_login_url() );
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

<header class="sdi-header" id="top">
	<div class="sdi-container sdi-header__inner">
		<a class="sdi-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php sdi_the_logo(); ?>
		</a>

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
			<div class="sdi-nav-more">
				<button type="button" class="sdi-nav-more__toggle" aria-expanded="false" aria-controls="sdi-nav-more-menu">
					<?php esc_html_e( 'More', 'sdi-travel' ); ?> <span aria-hidden="true">▾</span>
				</button>
				<div class="sdi-nav-more__menu" id="sdi-nav-more-menu">
					<p class="sdi-nav-more__label"><?php esc_html_e( 'More pages', 'sdi-travel' ); ?></p>
					<ul></ul>
				</div>
			</div>
		</nav>

		<button class="sdi-header__toggle" type="button" aria-expanded="false" aria-controls="sdi-primary-nav">
			<span></span><span></span><span></span>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'sdi-travel' ); ?></span>
		</button>

		<div class="sdi-header__actions">
			<?php if ( is_user_logged_in() ) : ?>
				<?php $sdi_current_user = wp_get_current_user(); ?>
				<span class="sdi-header__login" style="cursor: default;"><?php echo esc_html( $sdi_current_user->display_name ); ?></span>
				<a class="sdi-btn sdi-btn--outline" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log Out', 'sdi-travel' ); ?></a>
			<?php else : ?>
				<a class="sdi-header__login" href="<?php echo esc_url( $sdi_login_url ); ?>"><?php esc_html_e( 'Log In', 'sdi-travel' ); ?></a>
				<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( $sdi_join_url ); ?>"><?php esc_html_e( 'Become a Member', 'sdi-travel' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="sdi-content">
