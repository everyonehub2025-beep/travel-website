<?php
/**
 * Admin view: Referrals.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new SDI_Referrals_List_Table();
$list_table->prepare_items();
?>
<div class="wrap sdi-admin">
	<h1><?php esc_html_e( 'Referrals', 'sdi-trust-core' ); ?></h1>

	<form method="get">
		<input type="hidden" name="page" value="sdi-trust-referrals" />
		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Search by name or email', 'sdi-trust-core' ), 'sdi-referral-search' ); ?>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="sdi_referral_action" />
		<?php wp_nonce_field( 'bulk-referrals' ); ?>
		<?php $list_table->display(); ?>
	</form>
</div>
