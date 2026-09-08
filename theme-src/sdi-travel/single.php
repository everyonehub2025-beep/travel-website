<?php
/**
 * Single News & Resources post.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<article class="sdi-section sdi-section--light">
	<div class="sdi-container" style="max-width: 800px;">
		<?php while ( have_posts() ) : the_post(); ?>
			<p class="sdi-card__meta" style="color: var(--sdi-text-muted);"><?php echo esc_html( get_the_date() ); ?></p>
			<h1><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<div style="margin-bottom: 2em;">
					<?php the_post_thumbnail( 'sdi-wide' ); ?>
				</div>
			<?php else : ?>
				<div style="margin-bottom: 2em;">
					<?php sdi_image_placeholder( get_the_title() . ' — featured image', '16/9' ); ?>
				</div>
			<?php endif; ?>

			<div class="sdi-entry-content">
				<?php the_content(); ?>
			</div>

			<?php
			the_tags( '<p class="sdi-tags">', ', ', '</p>' );

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		<?php endwhile; ?>
	</div>
</article>

<?php get_footer(); ?>
