<?php
/**
 * Plugin Name:       SDI Trust Core
 * Plugin URI:        https://sditraveltrust.org
 * Description:       Membership points, referral scholarship program, and admin tools for SDI Travel Trust. Survives a theme change — membership data is never coupled to presentation.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Author:            SDI Travel Trust
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sdi-trust-core
 * Domain Path:       /languages
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// -----------------------------------------------------------------------
// Constants.
// -----------------------------------------------------------------------

define( 'SDI_TC_VERSION', '1.0.0' );
define( 'SDI_TC_FILE', __FILE__ );
define( 'SDI_TC_PATH', plugin_dir_path( __FILE__ ) );
define( 'SDI_TC_URL', plugin_dir_url( __FILE__ ) );
define( 'SDI_TC_BASENAME', plugin_basename( __FILE__ ) );
define( 'SDI_TC_TEXT_DOMAIN', 'sdi-trust-core' );

// Minimum requirements. Bail (with an admin notice) rather than fatal-error
// on an unsupported environment.
define( 'SDI_TC_MIN_WP', '6.4' );
define( 'SDI_TC_MIN_PHP', '8.0' );

/**
 * Check environment requirements before loading anything else.
 *
 * @return bool
 */
function sdi_tc_meets_requirements() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, SDI_TC_MIN_PHP, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, SDI_TC_MIN_WP, '<' ) ) {
		return false;
	}

	return true;
}

if ( ! sdi_tc_meets_requirements() ) {
	add_action(
		'admin_notices',
		function () {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: 1: minimum PHP version, 2: minimum WordPress version */
						__( 'SDI Trust Core requires PHP %1$s+ and WordPress %2$s+. Please update your environment to activate this plugin.', 'sdi-trust-core' ),
						SDI_TC_MIN_PHP,
						SDI_TC_MIN_WP
					)
				)
			);
		}
	);
	return;
}

// -----------------------------------------------------------------------
// Includes.
// -----------------------------------------------------------------------

require_once SDI_TC_PATH . 'includes/class-sdi-strings.php';
require_once SDI_TC_PATH . 'includes/class-sdi-activator.php';
require_once SDI_TC_PATH . 'includes/class-sdi-points.php';
require_once SDI_TC_PATH . 'includes/class-sdi-tiers.php';
require_once SDI_TC_PATH . 'includes/class-sdi-referrals.php';
require_once SDI_TC_PATH . 'includes/class-sdi-membership.php';
require_once SDI_TC_PATH . 'includes/class-sdi-auth.php';
require_once SDI_TC_PATH . 'includes/interface-sdi-fintech-provider.php';
require_once SDI_TC_PATH . 'includes/class-sdi-fintech-null-provider.php';
require_once SDI_TC_PATH . 'includes/class-sdi-fintech.php';
require_once SDI_TC_PATH . 'includes/class-sdi-shortcodes.php';
require_once SDI_TC_PATH . 'includes/class-sdi-rate-limiter.php';

if ( is_admin() ) {
	require_once SDI_TC_PATH . 'admin/class-sdi-admin.php';
	require_once SDI_TC_PATH . 'admin/class-sdi-referrals-list-table.php';
	require_once SDI_TC_PATH . 'admin/class-sdi-ledger-list-table.php';
	require_once SDI_TC_PATH . 'admin/class-sdi-members-list-table.php';
}

require_once SDI_TC_PATH . 'public/class-sdi-public.php';
require_once SDI_TC_PATH . 'public/class-sdi-directory.php';

// -----------------------------------------------------------------------
// Activation / deactivation.
// -----------------------------------------------------------------------

register_activation_hook( __FILE__, array( 'SDI_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'SDI_Activator', 'deactivate' ) );

// -----------------------------------------------------------------------
// Bootstrap.
// -----------------------------------------------------------------------

/**
 * Load the plugin text domain for translations.
 */
function sdi_tc_load_textdomain() {
	load_plugin_textdomain( 'sdi-trust-core', false, dirname( SDI_TC_BASENAME ) . '/languages' );
}
add_action( 'init', 'sdi_tc_load_textdomain' );

/**
 * Initialize plugin components once all plugins are loaded, so optional
 * integrations (MemberPress, Charitable, Directorist) can be detected
 * reliably via class_exists()/function_exists().
 */
function sdi_tc_bootstrap() {
	SDI_Shortcodes::instance();
	SDI_Public::instance();
	SDI_Directory::instance();

	if ( is_admin() ) {
		SDI_Admin::instance();
	}

	add_action( 'admin_post_sdi_signup', array( 'SDI_Auth', 'handle_signup' ) );
	add_action( 'admin_post_nopriv_sdi_signup', array( 'SDI_Auth', 'handle_signup' ) );
	add_action( 'admin_post_sdi_verify_email', array( 'SDI_Auth', 'handle_verify_email' ) );
	add_action( 'admin_post_nopriv_sdi_verify_email', array( 'SDI_Auth', 'handle_verify_email' ) );
	add_action( 'admin_post_sdi_member_login', array( 'SDI_Auth', 'handle_login' ) );
	add_action( 'admin_post_nopriv_sdi_member_login', array( 'SDI_Auth', 'handle_login' ) );
	add_action( 'admin_post_sdi_account_update', array( 'SDI_Auth', 'handle_account_update' ) );
	add_action( 'admin_post_sdi_add_dependent', array( 'SDI_Auth', 'handle_add_dependent' ) );
	add_action( 'template_redirect', array( 'SDI_Auth', 'maybe_redirect_logged_in' ) );
	add_action( 'show_user_profile', array( 'SDI_Auth', 'render_profile_fields' ) );
	add_action( 'edit_user_profile', array( 'SDI_Auth', 'render_profile_fields' ) );
	add_action( 'personal_options_update', array( 'SDI_Auth', 'save_profile_fields' ) );
	add_action( 'edit_user_profile_update', array( 'SDI_Auth', 'save_profile_fields' ) );
}
add_action( 'plugins_loaded', 'sdi_tc_bootstrap' );
add_action( 'admin_init', array( 'SDI_Activator', 'maybe_upgrade' ) );
