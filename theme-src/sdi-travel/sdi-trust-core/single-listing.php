<?php
/**
 * Single Travel Directory listing ("partner profile") — theme override of
 * the plugin's fallback template, using full SDI Travel site chrome.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$sdi_listing_id  = get_the_ID();
	$sdi_website     = get_post_meta( $sdi_listing_id, '_sdi_listing_website', true );
	$sdi_email       = get_post_meta( $sdi_listing_id, '_sdi_listing_email', true );
	$sdi_phone       = get_post_meta( $sdi_listing_id, '_sdi_listing_phone', true );
	$sdi_location    = get_post_meta( $sdi_listing_id, '_sdi_listing_location', true );
	$sdi_member_only = (bool) get_post_meta( $sdi_listing_id, '_sdi_listing_member_only', true );
	$sdi_can_see     = is_user_logged_in() && class_exists( 'SDI_Membership' ) && SDI_Membership::is_active_member( get_current_user_id() );
	$sdi_gated       = $sdi_member_only && ! $sdi_can_see;
	$sdi_terms       = get_the_terms( $sdi_listing_id, 'sdi_listing_category' );
	$sdi_categories  = $sdi_terms && ! is_wp_error( $sdi_terms ) ? wp_list_pluck( $sdi_terms, 'name' ) : array();
	?>

	<?php
	sdi_page_header_band(
		array(
			'breadcrumb' => __( 'Travel Directory', 'sdi-travel' ),
			'eyebrow'    => implode( ', ', $sdi_categories ),
			'title'      => get_the_title(),
			'subhead'    => $sdi_location,
		)
	);
	?>

	<section class="sdi-section sdi-section--light" style="padding-block: 56px;">
		<div class="sdi-container sdi-auth-grid">
			<div class="sdi-auth-panel">
				<?php the_content(); ?>
			</div>
			<div class="sdi-auth-sidecard">
				<p class="sdi-auth-eyebrow-label"><?php esc_html_e( 'Contact', 'sdi-travel' ); ?></p>
				<?php if ( $sdi_gated ) : ?>
					<p style="margin-top: 16px;"><?php esc_html_e( 'This is a member-only listing. Log in or join SDI Travel Trust to see contact details.', 'sdi-travel' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/member-login/' ) ); ?>" class="sdi-btn sdi-btn--primary" style="margin-top: 16px;"><?php esc_html_e( 'Log In', 'sdi-travel' ); ?></a>
				<?php else : ?>
					<div style="margin-top: 16px; display: flex; flex-direction: column; gap: 12px;">
						<?php if ( $sdi_website ) : ?>
							<a href="<?php echo esc_url( $sdi_website ); ?>" target="_blank" rel="noopener noreferrer" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Visit website', 'sdi-travel' ); ?></a>
						<?php endif; ?>
						<?php if ( $sdi_email ) : ?>
							<p><a href="mailto:<?php echo esc_attr( $sdi_email ); ?>" class="sdi-auth-link"><?php echo esc_html( $sdi_email ); ?></a></p>
						<?php endif; ?>
						<?php if ( $sdi_phone ) : ?>
							<p><?php echo esc_html( $sdi_phone ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<a href="<?php echo esc_url( home_url( '/travel-directory/' ) ); ?>" class="sdi-auth-link" style="display: inline-block; margin-top: 20px;"><?php esc_html_e( '← Back to the directory', 'sdi-travel' ); ?></a>
			</div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
