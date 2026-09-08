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
 * Print the site logo image (unlinked — the calling markup wraps it in the
 * home link), text fallback if no custom logo has been set.
 */
function sdi_the_logo() {
	$logo_id = get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'sdi-header__logo-img' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() escapes internally.
		return;
	}
	?>
	<span class="sdi-header__logo-text"><?php bloginfo( 'name' ); ?></span>
	<?php
}

/**
 * Compact navy "page header" band used at the top of every interior page:
 * breadcrumb, small gold eyebrow, serif H1, one-sentence subhead, over a
 * dotted route-line motif. Replaces the old full-height hero pattern on
 * every page except the Homepage, which keeps its own larger hero.
 *
 * @param array<string,string> $args {
 *     @type string $breadcrumb Current page label shown after "Home / ".
 *     @type string $eyebrow    Small gold line above the heading.
 *     @type string $title      Page H1.
 *     @type string $subhead    One-sentence supporting copy.
 * }
 */
function sdi_page_header_band( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'breadcrumb' => '',
			'eyebrow'    => '',
			'title'      => get_the_title(),
			'subhead'    => '',
		)
	);
	?>
	<section class="sdi-page-header">
		<div class="sdi-container sdi-page-header__inner">
			<p class="sdi-page-header__crumb">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'sdi-travel' ); ?></a> / <?php echo esc_html( $args['breadcrumb'] ); ?>
			</p>
			<?php if ( $args['eyebrow'] ) : ?>
				<p class="sdi-page-header__eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h1 class="sdi-page-header__title"><?php echo esc_html( $args['title'] ); ?></h1>
			<?php if ( $args['subhead'] ) : ?>
				<p class="sdi-page-header__subhead"><?php echo esc_html( $args['subhead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Estimate reading time for a post at 200 words per minute — matches the
 * design's "4 min read" byline convention.
 *
 * @param WP_Post|int $post Post object or ID.
 * @return string
 */
function sdi_reading_time( $post ) {
	$content    = get_post_field( 'post_content', $post );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) round( $word_count / 200 ) );

	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'sdi-travel' ), $minutes );
}

/**
 * Render one News & Resources archive card for the current post in the
 * loop. Shared between the featured-page grid and later archive pages so
 * both look identical.
 */
function sdi_news_card() {
	?>
	<article <?php post_class( 'sdi-card sdi-hover-lift sdi-animate' ); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
			<a href="<?php the_permalink(); ?>" style="display:block; margin: -2em -2em 1em;">
				<?php the_post_thumbnail( 'sdi-card' ); ?>
			</a>
		<?php else : ?>
			<a href="<?php the_permalink(); ?>" style="display:block; margin: -2em -2em 1em;">
				<?php sdi_image_placeholder( get_the_title(), '16/10' ); ?>
			</a>
		<?php endif; ?>
		<p class="sdi-card__meta" style="color: var(--sdi-text-muted); font-size: 0.85rem; margin-bottom: 0.5em;"><?php echo esc_html( get_the_date() . ' · ' . sdi_reading_time( get_the_ID() ) ); ?></p>
		<h2 style="font-size: 1.25rem;"><a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
		<a class="sdi-btn sdi-btn--outline" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'sdi-travel' ); ?></a>
	</article>
	<?php
}

/**
 * URL of the site's native Member Login page (a real Page created by the
 * content importer, template "Member Login") — falls back to wp-login.php
 * only if that page hasn't been created yet, e.g. mid-setup.
 *
 * @return string
 */
function sdi_get_login_url() {
	$page = get_page_by_path( 'member-login' );

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	return wp_login_url( get_permalink() ? get_permalink() : home_url( '/' ) );
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
