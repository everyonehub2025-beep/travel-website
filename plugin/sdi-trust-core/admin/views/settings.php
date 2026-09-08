<?php
/**
 * Admin view: Settings.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$points_per_referral = SDI_Tiers::get_points_per_referral();
$thresholds           = SDI_Tiers::get_thresholds();
$modules              = get_option( 'sdi_enabled_modules', array() );
?>
<div class="wrap sdi-admin">
	<h1><?php esc_html_e( 'SDI Trust — Settings', 'sdi-trust-core' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-admin-form sdi-admin-form--settings">
		<input type="hidden" name="action" value="sdi_save_settings" />
		<?php wp_nonce_field( 'sdi_save_settings' ); ?>

		<h2><?php esc_html_e( 'Points & Referrals', 'sdi-trust-core' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="sdi_points_per_referral"><?php esc_html_e( 'Points per approved referral', 'sdi-trust-core' ); ?></label></th>
				<td><input type="number" min="0" id="sdi_points_per_referral" name="sdi_points_per_referral" value="<?php echo esc_attr( $points_per_referral ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="sdi_referral_notification_emails"><?php esc_html_e( 'Admin notification recipients', 'sdi-trust-core' ); ?></label></th>
				<td><input type="text" class="regular-text" id="sdi_referral_notification_emails" name="sdi_referral_notification_emails" value="<?php echo esc_attr( get_option( 'sdi_referral_notification_emails', get_option( 'admin_email' ) ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="sdi_referral_rate_limit_count"><?php esc_html_e( 'Max referral submissions per hour, per member', 'sdi-trust-core' ); ?></label></th>
				<td><input type="number" min="1" id="sdi_referral_rate_limit_count" name="sdi_referral_rate_limit_count" value="<?php echo esc_attr( get_option( 'sdi_referral_rate_limit_count', 5 ) ); ?>" /></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Scholarship Eligibility Tiers', 'sdi-trust-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'These describe scholarship eligibility levels only, never a guaranteed award amount.', 'sdi-trust-core' ); ?></p>
		<table class="widefat sdi-admin-table" id="sdi-tier-rows">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Points Threshold', 'sdi-trust-core' ); ?></th>
					<th><?php esc_html_e( 'Eligibility Amount ($)', 'sdi-trust-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $thresholds as $points => $amount ) : ?>
					<tr>
						<td><input type="number" min="0" name="tier_points[]" value="<?php echo esc_attr( $points ); ?>" /></td>
						<td><input type="number" min="0" name="tier_amounts[]" value="<?php echo esc_attr( $amount ); ?>" /></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Membership Bridge (optional — MemberPress)', 'sdi-trust-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Membership works natively by default — sign-up, email verification, login and status are all built into this plugin and need nothing below. These two fields only matter if this site also runs MemberPress and you want its product purchases to control membership status instead of the native system.', 'sdi-trust-core' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="sdi_membership_individual_product_id"><?php esc_html_e( 'Individual Membership product ID', 'sdi-trust-core' ); ?></label></th>
				<td><input type="number" min="0" id="sdi_membership_individual_product_id" name="sdi_membership_individual_product_id" value="<?php echo esc_attr( get_option( 'sdi_membership_individual_product_id', 0 ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="sdi_membership_family_product_id"><?php esc_html_e( 'Family Membership product ID', 'sdi-trust-core' ); ?></label></th>
				<td><input type="number" min="0" id="sdi_membership_family_product_id" name="sdi_membership_family_product_id" value="<?php echo esc_attr( get_option( 'sdi_membership_family_product_id', 0 ) ); ?>" /></td>
			</tr>
		</table>
		<?php if ( ! SDI_Membership::is_memberpress_active() ) : ?>
			<p class="description"><?php esc_html_e( 'MemberPress is not currently active, so these fields have no effect right now.', 'sdi-trust-core' ); ?></p>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Email Templates', 'sdi-trust-core' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Leave a field blank to use the built-in default wording. Placeholders like %1$s and %2$d are replaced automatically — do not remove them.', 'sdi-trust-core' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="sdi_email_tpl_referral_approved_email"><?php esc_html_e( 'Referral approved (sent to member)', 'sdi-trust-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="sdi_email_tpl_referral_approved_email" name="sdi_email_tpl_referral_approved_email" placeholder="<?php echo esc_attr( sdi_tc_text( 'referral_approved_email', array( '%1$s', 0 ) ) ); ?>"><?php echo esc_textarea( get_option( 'sdi_email_tpl_referral_approved_email', '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="sdi_email_tpl_referral_rejected_email"><?php esc_html_e( 'Referral not approved (sent to member)', 'sdi-trust-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="sdi_email_tpl_referral_rejected_email" name="sdi_email_tpl_referral_rejected_email" placeholder="<?php echo esc_attr( sdi_tc_text( 'referral_rejected_email', array( '%1$s', '%2$s' ) ) ); ?>"><?php echo esc_textarea( get_option( 'sdi_email_tpl_referral_rejected_email', '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="sdi_email_tpl_admin_new_referral_email"><?php esc_html_e( 'New referral (sent to admin)', 'sdi-trust-core' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="sdi_email_tpl_admin_new_referral_email" name="sdi_email_tpl_admin_new_referral_email" placeholder="<?php echo esc_attr( sdi_tc_text( 'admin_new_referral_email', array( '%1$s' ) ) ); ?>"><?php echo esc_textarea( get_option( 'sdi_email_tpl_admin_new_referral_email', '' ) ); ?></textarea></td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Modules', 'sdi-trust-core' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Enabled Modules', 'sdi-trust-core' ); ?></th>
				<td>
					<label><input type="checkbox" name="module_referrals" value="1" <?php checked( ! empty( $modules['referrals'] ) ); ?> /> <?php esc_html_e( 'Referral Program', 'sdi-trust-core' ); ?></label><br />
					<label><input type="checkbox" name="module_points" value="1" <?php checked( ! empty( $modules['points'] ) ); ?> /> <?php esc_html_e( 'Points Ledger', 'sdi-trust-core' ); ?></label><br />
					<label><input type="checkbox" name="module_scholarships" value="1" <?php checked( ! empty( $modules['scholarships'] ) ); ?> /> <?php esc_html_e( 'Scholarship Eligibility Display', 'sdi-trust-core' ); ?></label><br />
					<label><input type="checkbox" name="module_fintech" value="1" <?php checked( ! empty( $modules['fintech'] ) ); ?> /> <?php esc_html_e( 'Fintech Module (Phase 2 — stub only)', 'sdi-trust-core' ); ?></label>
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Data', 'sdi-trust-core' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'On plugin deletion', 'sdi-trust-core' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="sdi_uninstall_delete_data" value="1" <?php checked( 'yes' === get_option( 'sdi_uninstall_delete_data', 'no' ) ); ?> />
						<?php esc_html_e( 'Permanently delete all points ledger, referral, and settings data if this plugin is ever deleted (not deactivated). Leave unchecked to keep your data safe.', 'sdi-trust-core' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Settings', 'sdi-trust-core' ) ); ?>
	</form>
</div>
