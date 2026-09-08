<?php
/**
 * Plugin activation and deactivation handling.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates the plugin's tables, default options, and capabilities on
 * activation. Deactivation intentionally leaves data in place — only
 * uninstall.php (gated by an explicit setting) ever deletes data.
 */
class SDI_Activator {

	/**
	 * Current schema version. Bump when create_tables() changes so
	 * maybe_upgrade() re-runs dbDelta() on the next admin page load.
	 *
	 * @var string
	 */
	const SCHEMA_VERSION = '1.0.0';

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::add_default_options();
		self::add_capabilities();

		update_option( 'sdi_tc_schema_version', self::SCHEMA_VERSION );

		// Referral approval/rejection emails are cron-free and synchronous,
		// but we still flush rewrite rules in case future versions add
		// member-portal endpoints.
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation. Deliberately non-destructive.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create the ledger and referrals tables via dbDelta(). Safe to call
	 * repeatedly — dbDelta() only applies the diff.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$ledger_table    = $wpdb->prefix . 'sdi_points_ledger';
		$referrals_table = $wpdb->prefix . 'sdi_referrals';

		$sql = "CREATE TABLE {$ledger_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NOT NULL,
			points INT NOT NULL,
			reason VARCHAR(50) NOT NULL,
			reference_id BIGINT UNSIGNED NULL,
			note TEXT NULL,
			created_by BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY created_at (created_at)
		) {$charset_collate};

		CREATE TABLE {$referrals_table} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			referrer_id BIGINT UNSIGNED NOT NULL,
			referred_name VARCHAR(200) NOT NULL,
			referred_email VARCHAR(200) NOT NULL,
			referred_phone VARCHAR(50) NULL,
			status VARCHAR(20) NOT NULL,
			points_awarded INT NOT NULL DEFAULT 0,
			admin_note TEXT NULL,
			reviewed_by BIGINT UNSIGNED NULL,
			reviewed_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY referrer_id (referrer_id),
			KEY referred_email (referred_email),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Register default options if they don't already exist. Every one of
	 * these is also exposed as a `sdi_*` filter for programmatic overrides
	 * and editable from Settings.
	 */
	public static function add_default_options() {
		add_option( 'sdi_points_per_referral', 30 );
		add_option(
			'sdi_tier_thresholds',
			array(
				300  => 3000,
				600  => 6000,
				900  => 9000,
				1200 => 12000,
			)
		);
		add_option( 'sdi_referral_notification_emails', get_option( 'admin_email' ) );
		add_option( 'sdi_referral_rate_limit_count', 5 );
		add_option( 'sdi_referral_rate_limit_window', HOUR_IN_SECONDS );
		add_option( 'sdi_membership_individual_product_id', 0 );
		add_option( 'sdi_membership_family_product_id', 0 );
		add_option( 'sdi_uninstall_delete_data', 'no' );
		add_option(
			'sdi_enabled_modules',
			array(
				'referrals'    => 1,
				'points'       => 1,
				'scholarships' => 1,
				'fintech'      => 0,
			)
		);
	}

	/**
	 * Grant plugin-specific capabilities to the administrator role so
	 * managing referrals/points doesn't require the blanket
	 * `manage_options` capability. Never touches other roles.
	 */
	public static function add_capabilities() {
		$admin = get_role( 'administrator' );

		if ( ! $admin ) {
			return;
		}

		$admin->add_cap( 'sdi_manage_referrals' );
		$admin->add_cap( 'sdi_manage_points' );
		$admin->add_cap( 'sdi_manage_settings' );
	}

	/**
	 * Re-run dbDelta() if the stored schema version is behind the plugin's
	 * current schema version. Hooked to admin_init so it runs at most once
	 * per version bump, never on the front end.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'sdi_tc_schema_version' ) === self::SCHEMA_VERSION ) {
			return;
		}

		self::create_tables();
		update_option( 'sdi_tc_schema_version', self::SCHEMA_VERSION );
	}
}
