<?php
/**
 * Admin menu registration, asset loading, and form/action handlers.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires up the "SDI Trust" admin menu and everything under it.
 */
class SDI_Admin {

	/**
	 * Singleton instance.
	 *
	 * @var SDI_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return SDI_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire up hooks.
	 */
	/**
	 * Email-related string keys that Settings exposes as editable
	 * templates, on top of the full filterable string registry in
	 * SDI_Strings.
	 *
	 * @var string[]
	 */
	const EMAIL_TEMPLATE_KEYS = array( 'referral_approved_email', 'referral_rejected_email', 'admin_new_referral_email' );

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );

		add_action( 'admin_post_sdi_referral_action', array( $this, 'handle_referral_action' ) );
		add_action( 'admin_post_sdi_points_adjust', array( $this, 'handle_points_adjust' ) );
		add_action( 'admin_post_sdi_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_post_sdi_export_members_csv', array( $this, 'handle_export_members_csv' ) );

		$this->register_email_template_overrides();
	}

	/**
	 * If an admin has saved a custom email template via Settings, prefer
	 * it over the built-in default — without ever touching SDI_Strings
	 * itself, keeping the option storage entirely an admin-layer concern.
	 */
	private function register_email_template_overrides() {
		foreach ( self::EMAIL_TEMPLATE_KEYS as $key ) {
			add_filter(
				"sdi_tc_string_{$key}",
				function ( $default ) use ( $key ) {
					$override = get_option( "sdi_email_tpl_{$key}", '' );
					return ( '' !== trim( (string) $override ) ) ? $override : $default;
				}
			);
		}
	}

	/**
	 * Register the top-level "SDI Trust" menu and its submenus.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'SDI Trust', 'sdi-trust-core' ),
			__( 'SDI Trust', 'sdi-trust-core' ),
			'sdi_manage_referrals',
			'sdi-trust',
			array( $this, 'render_dashboard_page' ),
			'dashicons-groups',
			26
		);

		add_submenu_page( 'sdi-trust', __( 'Dashboard', 'sdi-trust-core' ), __( 'Dashboard', 'sdi-trust-core' ), 'sdi_manage_referrals', 'sdi-trust', array( $this, 'render_dashboard_page' ) );
		add_submenu_page( 'sdi-trust', __( 'Referrals', 'sdi-trust-core' ), __( 'Referrals', 'sdi-trust-core' ), 'sdi_manage_referrals', 'sdi-trust-referrals', array( $this, 'render_referrals_page' ) );
		add_submenu_page( 'sdi-trust', __( 'Points Ledger', 'sdi-trust-core' ), __( 'Points Ledger', 'sdi-trust-core' ), 'sdi_manage_points', 'sdi-trust-ledger', array( $this, 'render_ledger_page' ) );
		add_submenu_page( 'sdi-trust', __( 'Members & Tiers', 'sdi-trust-core' ), __( 'Members & Tiers', 'sdi-trust-core' ), 'sdi_manage_points', 'sdi-trust-members', array( $this, 'render_members_page' ) );
		add_submenu_page( 'sdi-trust', __( 'Settings', 'sdi-trust-core' ), __( 'Settings', 'sdi-trust-core' ), 'sdi_manage_settings', 'sdi-trust-settings', array( $this, 'render_settings_page' ) );
	}

	/**
	 * Enqueue admin CSS/JS, only on SDI Trust screens.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'sdi-trust' ) ) {
			return;
		}

		wp_enqueue_style( 'sdi-trust-core-admin', SDI_TC_URL . 'admin/assets/css/sdi-admin.css', array(), SDI_TC_VERSION );
		wp_enqueue_script( 'sdi-trust-core-admin', SDI_TC_URL . 'admin/assets/js/sdi-admin.js', array(), SDI_TC_VERSION, true );
	}

	/**
	 * Render admin_notices set by our redirect-based handlers.
	 */
	public function render_notices() {
		if ( empty( $_GET['sdi_notice'] ) || ! isset( $_GET['page'] ) || false === strpos( sanitize_key( wp_unslash( $_GET['page'] ) ), 'sdi-trust' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flag.
			return;
		}

		$type    = ( 'error' === sanitize_key( wp_unslash( $_GET['sdi_notice'] ) ) ) ? 'error' : 'success'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$message = isset( $_GET['sdi_message'] ) ? sanitize_text_field( wp_unslash( $_GET['sdi_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $message ) {
			return;
		}

		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $type ), esc_html( $message ) );
	}

	/**
	 * Render: Dashboard.
	 */
	public function render_dashboard_page() {
		include SDI_TC_PATH . 'admin/views/dashboard.php';
	}

	/**
	 * Render: Referrals.
	 */
	public function render_referrals_page() {
		if ( ! current_user_can( 'sdi_manage_referrals' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'sdi-trust-core' ) );
		}
		include SDI_TC_PATH . 'admin/views/referrals.php';
	}

	/**
	 * Render: Points Ledger.
	 */
	public function render_ledger_page() {
		if ( ! current_user_can( 'sdi_manage_points' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'sdi-trust-core' ) );
		}
		include SDI_TC_PATH . 'admin/views/points-ledger.php';
	}

	/**
	 * Render: Members & Tiers.
	 */
	public function render_members_page() {
		if ( ! current_user_can( 'sdi_manage_points' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'sdi-trust-core' ) );
		}
		include SDI_TC_PATH . 'admin/views/members.php';
	}

	/**
	 * Render: Settings.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'sdi_manage_settings' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'sdi-trust-core' ) );
		}
		include SDI_TC_PATH . 'admin/views/settings.php';
	}

	/**
	 * Handle POST/GET from the Referrals screen's row-level and bulk
	 * approve/reject actions.
	 */
	public function handle_referral_action() {
		$redirect = admin_url( 'admin.php?page=sdi-trust-referrals' );

		// Bulk action (list table form POST).
		if ( isset( $_POST['referral_ids'] ) && isset( $_POST['action'] ) ) {
			check_admin_referer( 'bulk-referrals' );

			if ( ! current_user_can( 'sdi_manage_referrals' ) ) {
				wp_die( esc_html__( 'You do not have permission to manage referrals.', 'sdi-trust-core' ) );
			}

			$bulk_action = sanitize_key( wp_unslash( $_POST['action'] ) );
			$ids         = array_map( 'absint', (array) wp_unslash( $_POST['referral_ids'] ) );
			$errors      = 0;

			foreach ( $ids as $id ) {
				$result = ( 'approve' === $bulk_action )
					? SDI_Referrals::approve( $id, get_current_user_id() )
					: SDI_Referrals::reject( $id, get_current_user_id(), __( 'Rejected via bulk action.', 'sdi-trust-core' ) );

				if ( is_wp_error( $result ) ) {
					++$errors;
				}
			}

			$message = $errors
				? sprintf( /* translators: %d: number of referrals that could not be processed */ __( '%d referral(s) could not be processed.', 'sdi-trust-core' ), $errors )
				: __( 'Referrals updated.', 'sdi-trust-core' );

			wp_safe_redirect( add_query_arg( array( 'sdi_notice' => $errors ? 'error' : 'success', 'sdi_message' => rawurlencode( $message ) ), $redirect ) );
			exit;
		}

		// Row-level action (nonce'd GET link).
		$referral_id = isset( $_GET['referral_id'] ) ? absint( wp_unslash( $_GET['referral_id'] ) ) : 0;
		$do          = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';

		if ( ! $referral_id || ! in_array( $do, array( 'approve', 'reject' ), true ) ) {
			wp_die( esc_html__( 'Invalid request.', 'sdi-trust-core' ) );
		}

		check_admin_referer( 'sdi_referral_action_' . $referral_id );

		if ( ! current_user_can( 'sdi_manage_referrals' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage referrals.', 'sdi-trust-core' ) );
		}

		$result = ( 'approve' === $do )
			? SDI_Referrals::approve( $referral_id, get_current_user_id() )
			: SDI_Referrals::reject( $referral_id, get_current_user_id(), __( 'Rejected by administrator.', 'sdi-trust-core' ) );

		$notice  = is_wp_error( $result ) ? 'error' : 'success';
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Referral updated.', 'sdi-trust-core' );

		wp_safe_redirect( add_query_arg( array( 'sdi_notice' => $notice, 'sdi_message' => rawurlencode( $message ) ), $redirect ) );
		exit;
	}

	/**
	 * Handle a manual points add/deduct from the Points Ledger screen.
	 * A note is mandatory for every manual entry.
	 */
	public function handle_points_adjust() {
		check_admin_referer( 'sdi_points_adjust' );

		$redirect = admin_url( 'admin.php?page=sdi-trust-ledger' );

		if ( ! current_user_can( 'sdi_manage_points' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage points.', 'sdi-trust-core' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? absint( wp_unslash( $_POST['user_id'] ) ) : 0;
		$amount  = isset( $_POST['amount'] ) ? intval( wp_unslash( $_POST['amount'] ) ) : 0;
		$note    = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
		$reason  = ( $amount >= 0 ) ? 'manual_adjust' : 'admin_revoke';

		if ( ! $user_id || 0 === $amount || '' === trim( $note ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_notice' => 'error', 'sdi_message' => rawurlencode( __( 'A member, a non-zero amount, and a note are all required.', 'sdi-trust-core' ) ) ), $redirect ) );
			exit;
		}

		$result = SDI_Points::add_entry( $user_id, $amount, $reason, null, $note, get_current_user_id() );

		$notice  = is_wp_error( $result ) ? 'error' : 'success';
		$message = is_wp_error( $result ) ? $result->get_error_message() : __( 'Points entry recorded.', 'sdi-trust-core' );

		wp_safe_redirect( add_query_arg( array( 'sdi_notice' => $notice, 'sdi_message' => rawurlencode( $message ) ), $redirect ) );
		exit;
	}

	/**
	 * Handle the Settings screen form.
	 */
	public function handle_save_settings() {
		check_admin_referer( 'sdi_save_settings' );

		$redirect = admin_url( 'admin.php?page=sdi-trust-settings' );

		if ( ! current_user_can( 'sdi_manage_settings' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage settings.', 'sdi-trust-core' ) );
		}

		update_option( 'sdi_points_per_referral', max( 0, intval( wp_unslash( $_POST['sdi_points_per_referral'] ?? 30 ) ) ) );

		$thresholds = array();
		if ( ! empty( $_POST['tier_points'] ) && ! empty( $_POST['tier_amounts'] ) ) {
			$points_raw  = array_map( 'absint', (array) wp_unslash( $_POST['tier_points'] ) );
			$amounts_raw = array_map( 'absint', (array) wp_unslash( $_POST['tier_amounts'] ) );
			foreach ( $points_raw as $index => $points ) {
				if ( $points > 0 && isset( $amounts_raw[ $index ] ) ) {
					$thresholds[ $points ] = $amounts_raw[ $index ];
				}
			}
		}
		if ( ! empty( $thresholds ) ) {
			ksort( $thresholds, SORT_NUMERIC );
			update_option( 'sdi_tier_thresholds', $thresholds );
		}

		update_option( 'sdi_referral_notification_emails', sanitize_text_field( wp_unslash( $_POST['sdi_referral_notification_emails'] ?? get_option( 'admin_email' ) ) ) );
		update_option( 'sdi_referral_rate_limit_count', max( 1, intval( wp_unslash( $_POST['sdi_referral_rate_limit_count'] ?? 5 ) ) ) );
		update_option( 'sdi_membership_individual_product_id', absint( wp_unslash( $_POST['sdi_membership_individual_product_id'] ?? 0 ) ) );
		update_option( 'sdi_membership_family_product_id', absint( wp_unslash( $_POST['sdi_membership_family_product_id'] ?? 0 ) ) );
		update_option( 'sdi_uninstall_delete_data', ! empty( $_POST['sdi_uninstall_delete_data'] ) ? 'yes' : 'no' );

		foreach ( self::EMAIL_TEMPLATE_KEYS as $key ) {
			$field = "sdi_email_tpl_{$key}";
			if ( isset( $_POST[ $field ] ) ) {
				update_option( $field, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		$modules = array(
			'referrals'    => ! empty( $_POST['module_referrals'] ) ? 1 : 0,
			'points'       => ! empty( $_POST['module_points'] ) ? 1 : 0,
			'scholarships' => ! empty( $_POST['module_scholarships'] ) ? 1 : 0,
			'fintech'      => ! empty( $_POST['module_fintech'] ) ? 1 : 0,
		);
		update_option( 'sdi_enabled_modules', $modules );

		wp_safe_redirect( add_query_arg( array( 'sdi_notice' => 'success', 'sdi_message' => rawurlencode( __( 'Settings saved.', 'sdi-trust-core' ) ) ), $redirect ) );
		exit;
	}

	/**
	 * Stream a CSV export of the Members & Tiers table.
	 */
	public function handle_export_members_csv() {
		check_admin_referer( 'sdi_export_members_csv' );

		if ( ! current_user_can( 'sdi_manage_points' ) ) {
			wp_die( esc_html__( 'You do not have permission to export this data.', 'sdi-trust-core' ) );
		}

		$rows = SDI_Members_List_Table::get_all_members_with_stats();

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=sdi-members-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'Name', 'Email', 'Plan', 'Points Balance', 'Eligibility Level ($)' ) );

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					$row['name'],
					$row['email'],
					$row['plan'],
					$row['balance'],
					$row['tier_award_amount'] ? $row['tier_award_amount'] : '',
				)
			);
		}

		fclose( $out );
		exit;
	}
}
