<?php
/**
 * Admin view: Members & Tiers.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new SDI_Members_List_Table();
$list_table->prepare_items();

$export_url = wp_nonce_url( admin_url( 'admin-post.php?action=sdi_export_members_csv' ), 'sdi_export_members_csv' );
?>
<div class="wrap sdi-admin">
	<h1>
		<?php esc_html_e( 'Members & Tiers', 'sdi-trust-core' ); ?>
		<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action"><?php esc_html_e( 'Export CSV', 'sdi-trust-core' ); ?></a>
	</h1>

	<form method="get">
		<input type="hidden" name="page" value="sdi-trust-members" />
		<?php $list_table->search_box( __( 'Search members', 'sdi-trust-core' ), 'sdi-member-search' ); ?>
		<?php $list_table->display(); ?>
	</form>
</div>
