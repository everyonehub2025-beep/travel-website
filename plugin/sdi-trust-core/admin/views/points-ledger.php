<?php
/**
 * Admin view: Points Ledger — full log + manual add/deduct.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$list_table = new SDI_Ledger_List_Table();
$list_table->prepare_items();

$members = get_users(
	array(
		'role__not_in' => array( 'administrator' ),
		'orderby'      => 'display_name',
		'order'        => 'ASC',
		'number'       => 500,
	)
);
?>
<div class="wrap sdi-admin">
	<h1><?php esc_html_e( 'Points Ledger', 'sdi-trust-core' ); ?></h1>

	<div class="sdi-admin-panel">
		<h2><?php esc_html_e( 'Manual Adjustment', 'sdi-trust-core' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-admin-form">
			<input type="hidden" name="action" value="sdi_points_adjust" />
			<?php wp_nonce_field( 'sdi_points_adjust' ); ?>

			<label for="sdi-adjust-user"><?php esc_html_e( 'Member', 'sdi-trust-core' ); ?></label>
			<select id="sdi-adjust-user" name="user_id" required="required">
				<option value=""><?php esc_html_e( '— Select a member —', 'sdi-trust-core' ); ?></option>
				<?php foreach ( $members as $member ) : ?>
					<option value="<?php echo esc_attr( $member->ID ); ?>"><?php echo esc_html( $member->display_name . ' (' . $member->user_email . ')' ); ?></option>
				<?php endforeach; ?>
			</select>

			<label for="sdi-adjust-amount"><?php esc_html_e( 'Amount (use a negative number to deduct)', 'sdi-trust-core' ); ?></label>
			<input type="number" id="sdi-adjust-amount" name="amount" required="required" />

			<label for="sdi-adjust-note"><?php esc_html_e( 'Note (required)', 'sdi-trust-core' ); ?></label>
			<textarea id="sdi-adjust-note" name="note" required="required" rows="2"></textarea>

			<button type="submit" class="button button-primary"><?php esc_html_e( 'Record Entry', 'sdi-trust-core' ); ?></button>
		</form>
	</div>

	<form method="get">
		<input type="hidden" name="page" value="sdi-trust-ledger" />
		<p class="sdi-admin-filters">
			<select name="reason">
				<option value=""><?php esc_html_e( 'All reasons', 'sdi-trust-core' ); ?></option>
				<?php foreach ( SDI_Points::VALID_REASONS as $reason ) : ?>
					<option value="<?php echo esc_attr( $reason ); ?>" <?php selected( isset( $_GET['reason'] ) ? sanitize_key( wp_unslash( $_GET['reason'] ) ) : '', $reason ); ?>><?php echo esc_html( ucfirst( str_replace( '_', ' ', $reason ) ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="date" name="date_from" value="<?php echo esc_attr( isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '' ); ?>" />
			<input type="date" name="date_to" value="<?php echo esc_attr( isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '' ); ?>" />
			<button type="submit" class="button"><?php esc_html_e( 'Filter', 'sdi-trust-core' ); ?></button>
		</p>
		<?php $list_table->display(); ?>
	</form>
</div>
