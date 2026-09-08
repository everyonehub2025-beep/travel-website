<?php
/**
 * News & Resources archive/blog listing — page 1 leads with a featured
 * story + "Latest" mini-list (matching the design's featured-story
 * section), remaining posts fall into a plain grid, then a static
 * Resource Library and the newsletter signup. Later pages fall back to a
 * plain grid only.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

global $wp_query;
$sdi_all_posts   = ( ! is_paged() && have_posts() ) ? $wp_query->posts : array();
$sdi_featured    = ! empty( $sdi_all_posts ) ? $sdi_all_posts[0] : null;
$sdi_latest      = ! empty( $sdi_all_posts ) ? array_slice( $sdi_all_posts, 1, 3 ) : array();
$sdi_grid_posts  = ! empty( $sdi_all_posts ) ? array_slice( $sdi_all_posts, 4 ) : array();

$sdi_resources = array(
	array( 'name' => __( 'Membership handbook', 'sdi-travel' ), 'detail' => __( 'Benefits, renewal and cancellation, in one document.', 'sdi-travel' ), 'meta' => __( 'PDF', 'sdi-travel' ), 'member_only' => false ),
	array( 'name' => __( 'Scholarship application checklist', 'sdi-travel' ), 'detail' => __( 'Every document required, listed against the cycle deadline.', 'sdi-travel' ), 'meta' => __( 'PDF', 'sdi-travel' ), 'member_only' => false ),
	array( 'name' => __( 'Referral program quick reference', 'sdi-travel' ), 'detail' => __( 'Points, tiers and confirmation windows on one page.', 'sdi-travel' ), 'meta' => __( 'PDF', 'sdi-travel' ), 'member_only' => false ),
	array( 'name' => __( 'Annual financial summary', 'sdi-travel' ), 'detail' => __( 'The published allocation and award totals for the year.', 'sdi-travel' ), 'meta' => __( 'PDF · Member', 'sdi-travel' ), 'member_only' => true ),
	array( 'name' => __( 'Directory submission guide', 'sdi-travel' ), 'detail' => __( 'What we check before a listing goes live, and how long it takes.', 'sdi-travel' ), 'meta' => __( 'PDF · Member', 'sdi-travel' ), 'member_only' => true ),
);
?>

<?php
sdi_page_header_band(
	array(
		'breadcrumb' => __( 'News &amp; Resources', 'sdi-travel' ),
		'eyebrow'    => __( 'Announcements &amp; guidance', 'sdi-travel' ),
		'title'      => __( 'News & Resources', 'sdi-travel' ),
		'subhead'    => __( 'Scholarship announcements, directory additions, partner news, and the travel guidance we write for members.', 'sdi-travel' ),
	)
);
?>

<?php if ( $sdi_featured ) : ?>
<section class="sdi-section sdi-section--light" style="border-bottom: 1px solid var(--sdi-border); padding-block: 56px;">
	<div class="sdi-container sdi-news-grid" style="margin-top: 0;">
		<a class="sdi-news-feature" href="<?php echo esc_url( get_permalink( $sdi_featured ) ); ?>" style="background: var(--sdi-color-midnight); color: var(--sdi-color-white);">
			<div>
				<div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
					<span class="sdi-news-feature__tag"><?php echo esc_html( get_the_category( $sdi_featured->ID ) ? get_the_category( $sdi_featured->ID )[0]->name : __( 'News', 'sdi-travel' ) ); ?></span>
					<span class="sdi-news-feature__date" style="color: var(--sdi-color-silver);"><?php echo esc_html( get_the_date( '', $sdi_featured ) . ' · ' . sdi_reading_time( $sdi_featured ) ); ?></span>
				</div>
				<h2 style="margin: 20px 0 0; color: var(--sdi-color-white);"><?php echo esc_html( get_the_title( $sdi_featured ) ); ?></h2>
				<p style="color: var(--sdi-color-border);"><?php echo esc_html( wp_trim_words( get_the_excerpt( $sdi_featured ), 34 ) ); ?></p>
			</div>
			<div style="margin-top: 24px;">
				<span class="sdi-btn sdi-btn--primary">Read the story</span>
			</div>
		</a>
		<div class="sdi-news-side">
			<?php if ( $sdi_latest ) : ?>
				<div class="sdi-news-side__list">
					<p class="sdi-auth-eyebrow-label">Latest</p>
					<?php foreach ( $sdi_latest as $sdi_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $sdi_post ) ); ?>">
							<span><?php echo esc_html( get_the_date( '', $sdi_post ) . ' · ' . ( get_the_category( $sdi_post->ID ) ? get_the_category( $sdi_post->ID )[0]->name : __( 'News', 'sdi-travel' ) ) ); ?></span>
							<strong><?php echo esc_html( get_the_title( $sdi_post ) ); ?></strong>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div class="sdi-news-side__cycle">
				<p class="sdi-auth-kicker">Next cycle closes</p>
				<p class="sdi-news-side__date">Nov 15</p>
				<p><?php esc_html_e( 'Applications and referral confirmations must be in by the deadline to count toward the cycle.', 'sdi-travel' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/scholarships/#apply' ) ); ?>" class="sdi-auth-link" style="display: inline-block; margin-top: 10px;"><?php esc_html_e( 'Application process', 'sdi-travel' ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<div class="sdi-section sdi-section--light">
	<div class="sdi-container">
		<?php if ( ! is_home() && have_posts() ) : ?>
			<h1><?php printf( '%s', wp_kses_post( get_the_archive_title() ) ); ?></h1>
		<?php elseif ( ! empty( $sdi_grid_posts ) || is_paged() ) : ?>
			<h2><?php esc_html_e( 'The archive', 'sdi-travel' ); ?></h2>
			<p style="color: var(--sdi-color-text-secondary); max-width: 48ch;"><?php esc_html_e( 'Everything here is public; member-only guidance sits in the portal.', 'sdi-travel' ); ?></p>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php $sdi_show_posts = is_paged() ? null : $sdi_grid_posts; ?>
			<?php if ( null === $sdi_show_posts || ! empty( $sdi_show_posts ) ) : ?>
				<div class="sdi-grid sdi-grid--3" data-sdi-stagger style="margin-top: 1.5em;">
					<?php
					if ( null === $sdi_show_posts ) :
						while ( have_posts() ) :
							the_post();
							sdi_news_card();
						endwhile;
					else :
						foreach ( $sdi_show_posts as $sdi_grid_post ) :
							setup_postdata( $sdi_grid_post );
							sdi_news_card();
						endforeach;
						wp_reset_postdata();
					endif;
					?>
				</div>
			<?php endif; ?>

			<div class="sdi-pagination" style="margin-top: 2.5em;">
				<?php the_posts_pagination(); ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No articles published yet.', 'sdi-travel' ); ?></p>
		<?php endif; ?>
	</div>
</div>

<section class="sdi-section sdi-section--light" style="background: var(--sdi-color-fill-muted); border-top: 1px solid var(--sdi-border); border-bottom: 1px solid var(--sdi-border);">
	<div class="sdi-container sdi-grid sdi-grid--2">
		<div>
			<p class="sdi-eyebrow" style="color: #8a6a1f;">Download and keep</p>
			<h2>Resource library</h2>
			<p style="color: var(--sdi-color-text-secondary); max-width: 44ch;"><?php esc_html_e( 'Programme documents and travel guidance, kept current. Two items are member-only and open once you log in.', 'sdi-travel' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/member-login/' ) ); ?>" class="sdi-btn sdi-btn--outline" style="margin-top: 12px;"><?php esc_html_e( 'Log in for member files', 'sdi-travel' ); ?></a>
		</div>
		<div style="border-top: 1px solid var(--sdi-color-platinum);">
			<?php foreach ( $sdi_resources as $sdi_resource ) : ?>
				<div style="padding: 20px 0; border-bottom: 1px solid var(--sdi-color-platinum); display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 20px; align-items: center;">
					<div>
						<div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
							<p style="margin: 0; font-weight: 600;"><?php echo esc_html( $sdi_resource['name'] ); ?></p>
							<?php if ( $sdi_resource['member_only'] ) : ?>
								<span style="font-weight: 600; font-size: 0.65rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--sdi-color-navy); background: var(--sdi-color-gold); padding: 5px 8px; border-radius: var(--sdi-radius);">Member</span>
							<?php endif; ?>
						</div>
						<p style="margin: 6px 0 0; color: var(--sdi-color-text-secondary); font-size: 0.9rem;"><?php echo esc_html( $sdi_resource['detail'] ); ?></p>
					</div>
					<p style="margin: 0; font-family: ui-monospace, Menlo, monospace; font-size: 0.8rem; color: var(--sdi-color-silver); white-space: nowrap;"><?php echo esc_html( $sdi_resource['meta'] ); ?></p>
				</div>
			<?php endforeach; ?>
			<p style="color: var(--sdi-color-silver); font-size: 0.82rem; margin-top: 16px;"><?php esc_html_e( 'Documents are placeholders pending final programme copy and legal review.', 'sdi-travel' ); ?></p>
		</div>
	</div>
</section>

<section class="sdi-section sdi-section--navy">
	<div class="sdi-container sdi-grid sdi-grid--2" style="align-items: center;">
		<div>
			<p class="sdi-eyebrow" style="color: var(--sdi-color-gold);">Monthly, and nothing else</p>
			<h2 style="color: var(--sdi-color-white);">The member newsletter</h2>
			<p style="color: var(--sdi-color-border);"><?php esc_html_e( 'Scholarship announcements, new directory listings and partner offers. One email a month, no drip sequences.', 'sdi-travel' ); ?></p>
		</div>
		<div>
			<?php echo do_shortcode( '[sdi_newsletter_form]' ); ?>
			<p style="color: var(--sdi-color-silver); font-size: 0.8rem; margin-top: 10px;">
				<?php
				printf(
					/* translators: %s: Privacy Policy link */
					esc_html__( 'You can unsubscribe from any email. We never sell or share member email addresses — see the %s.', 'sdi-travel' ),
					'<a href="' . esc_url( home_url( '/legal/#privacy' ) ) . '" style="color: var(--sdi-color-platinum); text-decoration: underline;">' . esc_html__( 'Privacy Policy', 'sdi-travel' ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_html().
				);
				?>
			</p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
