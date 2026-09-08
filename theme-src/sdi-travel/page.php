<?php
/**
 * Default page template. Elementor-built pages using an Elementor page
 * template (Canvas / Elementor Full Width) bypass this entirely via
 * Elementor's own template_include hook — this only renders for a page
 * that has no Elementor data yet, or uses the default WordPress template.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
	<article <?php post_class( 'sdi-section sdi-section--light' ); ?>>
		<div class="sdi-container" style="max-width: 800px;">
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</div>
	</article>
<?php endwhile; ?>

<?php get_footer(); ?>
