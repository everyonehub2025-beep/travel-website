<?php
/**
 * Fallback single Travel Directory listing template — used only if the
 * active theme doesn't provide its own sdi-trust-core/single-listing.php.
 * The SDI Travel theme does provide its own (with full header/footer
 * chrome); this exists so the plugin still renders something usable on
 * any other theme.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$listing_id  = get_the_ID();
	$website     = get_post_meta( $listing_id, '_sdi_listing_website', true );
	$email       = get_post_meta( $listing_id, '_sdi_listing_email', true );
	$phone       = get_post_meta( $listing_id, '_sdi_listing_phone', true );
	$location    = get_post_meta( $listing_id, '_sdi_listing_location', true );
	$member_only = (bool) get_post_meta( $listing_id, '_sdi_listing_member_only', true );
	$can_see     = is_user_logged_in() && class_exists( 'SDI_Membership' ) && SDI_Membership::is_active_member( get_current_user_id() );
	$gated       = $member_only && ! $can_see;
	?>
	<article style="max-width: 720px; margin: 3em auto; padding: 0 1.5em;">
		<h1><?php the_title(); ?></h1>
		<?php if ( $location ) : ?>
			<p><?php echo esc_html( $location ); ?></p>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php if ( $gated ) : ?>
			<p><?php esc_html_e( 'This is a member-only listing. Log in or join to see contact details.', 'sdi-trust-core' ); ?></p>
		<?php else : ?>
			<?php if ( $website ) : ?><p><a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Visit website', 'sdi-trust-core' ); ?></a></p><?php endif; ?>
			<?php if ( $email ) : ?><p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
			<?php if ( $phone ) : ?><p><?php echo esc_html( $phone ); ?></p><?php endif; ?>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
