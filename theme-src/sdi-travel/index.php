<?php
/**
 * News & Resources archive/blog listing.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php if ( is_home() && ! is_front_page() ) : ?>
	<div class="sdi-section sdi-section--navy" style="text-align:center;">
		<div class="sdi-container">
			<h1><?php esc_html_e( 'News & Resources', 'sdi-travel' ); ?></h1>
			<p><?php esc_html_e( 'Travel education articles, member announcements, scholarship updates, partner news, and community impact reports.', 'sdi-travel' ); ?></p>
		</div>
	</div>
<?php endif; ?>

<div class="sdi-section sdi-section--light">
	<div class="sdi-container">
		<?php if ( ! is_home() && have_posts() ) : ?>
			<h1><?php printf( '%s', wp_kses_post( get_the_archive_title() ) ); ?></h1>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="sdi-grid sdi-grid--3" data-sdi-stagger>
				<?php
				while ( have_posts() ) :
					the_post();
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
						<p class="sdi-card__meta" style="color: var(--sdi-text-muted); font-size: 0.85rem; margin-bottom: 0.5em;"><?php echo esc_html( get_the_date() ); ?></p>
						<h2 style="font-size: 1.25rem;"><a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						<a class="sdi-btn sdi-btn--outline" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read More', 'sdi-travel' ); ?></a>
					</article>
				<?php endwhile; ?>
			</div>

			<div class="sdi-pagination" style="margin-top: 2.5em;">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No articles published yet.', 'sdi-travel' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
