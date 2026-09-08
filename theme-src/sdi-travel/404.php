<?php
/**
 * 404 template.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="sdi-section sdi-section--navy" style="text-align:center;">
	<div class="sdi-container">
		<h1><?php esc_html_e( 'Page Not Found', 'sdi-travel' ); ?></h1>
		<p><?php esc_html_e( 'The page you\'re looking for may have moved. Try searching, or head back to the homepage.', 'sdi-travel' ); ?></p>
		<div style="max-width: 480px; margin: 2em auto;">
			<?php get_search_form(); ?>
		</div>
		<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Homepage', 'sdi-travel' ); ?></a>
	</div>
</div>

<?php get_footer(); ?>
