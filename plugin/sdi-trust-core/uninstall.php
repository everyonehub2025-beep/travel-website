<?php
/**
 * Uninstall handler.
 *
 * Runs only when the plugin is deleted from the Plugins screen (never on
 * deactivation). Destructive cleanup — dropping the ledger/referrals
 * tables and deleting options — only happens if an administrator has
 * explicitly opted in via Settings ("Delete all data on uninstall").
 * Otherwise this is a no-op and all membership/points data is left in
 * place, exactly as if the plugin were only deactivated.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$sdi_delete_data = get_option( 'sdi_uninstall_delete_data', 'no' );

if ( 'yes' !== $sdi_delete_data ) {
	return;
}

global $wpdb;

$sdi_tables = array(
	$wpdb->prefix . 'sdi_points_ledger',
	$wpdb->prefix . 'sdi_referrals',
);

foreach ( $sdi_tables as $sdi_table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$sdi_table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name built from $wpdb->prefix, not user input.
}

$sdi_options = array(
	'sdi_points_per_referral',
	'sdi_tier_thresholds',
	'sdi_referral_notification_emails',
	'sdi_referral_rate_limit_count',
	'sdi_referral_rate_limit_window',
	'sdi_membership_individual_product_id',
	'sdi_membership_family_product_id',
	'sdi_uninstall_delete_data',
	'sdi_enabled_modules',
	'sdi_tc_schema_version',
	'sdi_fintech_last_sync_attempt',
);

foreach ( $sdi_options as $sdi_option ) {
	delete_option( $sdi_option );
}
