<?php
/**
 * Template Name: Member Dashboard
 *
 * Thin wrapper around [sdi_member_dashboard] — the page header band shows
 * real membership status, and the shortcode does the rest.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sdi_status_line = __( 'Member portal', 'sdi-travel' );
if ( is_user_logged_in() && class_exists( 'SDI_Membership' ) ) {
	$sdi_status_line = SDI_Membership::get_status_label( get_current_user_id() );
}
?>

<?php
sdi_page_header_band(
	array(
		'breadcrumb' => __( 'Member Portal', 'sdi-travel' ),
		'eyebrow'    => $sdi_status_line,
		'title'      => __( 'Member Dashboard', 'sdi-travel' ),
		'subhead'    => __( 'Your points balance, referral link, partner offers, giving history and account settings in one place.', 'sdi-travel' ),
	)
);
?>

<section class="sdi-section sdi-section--light" style="padding-block: 48px 70px;">
	<div class="sdi-container">
		<?php echo do_shortcode( '[sdi_member_dashboard]' ); ?>
	</div>
</section>

<?php get_footer(); ?>
