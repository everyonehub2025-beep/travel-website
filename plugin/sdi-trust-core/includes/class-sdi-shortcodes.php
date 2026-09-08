<?php
/**
 * Front-end shortcodes and their form handlers.
 *
 * Every shortcode here works inside Elementor's Shortcode widget — none
 * depend on a specific page template or theme markup.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders all `[sdi_*]` shortcodes.
 */
class SDI_Shortcodes {

	/**
	 * Singleton instance.
	 *
	 * @var SDI_Shortcodes|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return SDI_Shortcodes
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire up shortcodes and form handlers.
	 */
	private function __construct() {
		add_shortcode( 'sdi_member_dashboard', array( $this, 'render_dashboard' ) );
		add_shortcode( 'sdi_points_balance', array( $this, 'render_points_balance' ) );
		add_shortcode( 'sdi_tier_status', array( $this, 'render_tier_status' ) );
		add_shortcode( 'sdi_referral_form', array( $this, 'render_referral_form' ) );
		add_shortcode( 'sdi_referral_list', array( $this, 'render_referral_list' ) );
		add_shortcode( 'sdi_tier_progress', array( $this, 'render_tier_progress' ) );

		add_action( 'admin_post_sdi_submit_referral', array( $this, 'handle_referral_submission' ) );
	}

	/**
	 * Render a template file with the given variables in a bounded scope
	 * so templates only ever see what's explicitly handed to them.
	 *
	 * @param string              $template Template filename relative to public/templates/.
	 * @param array<string,mixed> $vars     Variables to extract into the template's scope.
	 * @return string
	 */
	private function render( $template, $vars = array() ) {
		$path = SDI_TC_PATH . 'public/templates/' . $template;

		if ( ! file_exists( $path ) ) {
			return '';
		}

		// Allow a theme to override any template by placing a same-named
		// file at get_stylesheet_directory() . '/sdi-trust-core/'.
		$theme_override = locate_template( 'sdi-trust-core/' . $template );
		if ( $theme_override ) {
			$path = $theme_override;
		}

		SDI_Public::instance()->enqueue_front_assets();

		ob_start();
		( function () use ( $path, $vars ) {
			// Local scope closure keeps $path/$vars from leaking into the template as globals.
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- bounded, known keys only, template scope.
			include $path;
		} )();
		return ob_get_clean();
	}

	/**
	 * [sdi_member_dashboard]
	 *
	 * @return string
	 */
	public function render_dashboard() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		$user_id = get_current_user_id();

		return $this->render(
			'dashboard.php',
			array(
				'user_id'      => $user_id,
				'tier_status'  => SDI_Tiers::get_status( $user_id ),
				'referrals'    => SDI_Referrals::get_for_member( $user_id ),
				'ledger'       => SDI_Points::get_history( $user_id, 100 ),
				'membership'   => SDI_Membership::get_status_label( $user_id ),
				'active_tab'   => isset( $_GET['sdi_tab'] ) ? sanitize_key( wp_unslash( $_GET['sdi_tab'] ) ) : 'overview', // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only tab selector, no state change.
			)
		);
	}

	/**
	 * [sdi_points_balance]
	 *
	 * @return string
	 */
	public function render_points_balance() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		$user_id = get_current_user_id();

		return $this->render(
			'shortcode-points-balance.php',
			array( 'balance' => SDI_Points::get_balance( $user_id ) )
		);
	}

	/**
	 * [sdi_tier_status]
	 *
	 * @return string
	 */
	public function render_tier_status() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		return $this->render(
			'shortcode-tier-status.php',
			array( 'tier_status' => SDI_Tiers::get_status( get_current_user_id() ) )
		);
	}

	/**
	 * [sdi_tier_progress]
	 *
	 * @return string
	 */
	public function render_tier_progress() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		return $this->render(
			'shortcode-tier-progress.php',
			array( 'tier_status' => SDI_Tiers::get_status( get_current_user_id() ) )
		);
	}

	/**
	 * [sdi_referral_form]
	 *
	 * @return string
	 */
	public function render_referral_form() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		$notice = null;
		if ( isset( $_GET['sdi_referral'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag, actual submission is nonce-verified in handle_referral_submission().
			$result = sanitize_key( wp_unslash( $_GET['sdi_referral'] ) );
			$notice = array(
				'type'    => ( 'success' === $result ) ? 'success' : 'error',
				'message' => isset( $_GET['sdi_message'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					? sanitize_text_field( wp_unslash( $_GET['sdi_message'] ) )
					: '',
			);
		}

		return $this->render(
			'shortcode-referral-form.php',
			array(
				'notice'    => $notice,
				'action_url' => admin_url( 'admin-post.php' ),
				'nonce_field' => wp_nonce_field( 'sdi_submit_referral', 'sdi_referral_nonce', true, false ),
			)
		);
	}

	/**
	 * [sdi_referral_list]
	 *
	 * @return string
	 */
	public function render_referral_list() {
		if ( ! is_user_logged_in() ) {
			return $this->render( 'login-required.php' );
		}

		return $this->render(
			'shortcode-referral-list.php',
			array( 'referrals' => SDI_Referrals::get_for_member( get_current_user_id() ) )
		);
	}

	/**
	 * Handle POST from [sdi_referral_form] via admin-post.php. Always
	 * redirects back to the referring page with a result flag — never
	 * echoes directly, so this works identically whether the form lives
	 * on a page, a post, or inside an Elementor popup.
	 */
	public function handle_referral_submission() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

		if (
			! isset( $_POST['sdi_referral_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_referral_nonce'] ) ), 'sdi_submit_referral' )
		) {
			wp_safe_redirect( add_query_arg( array( 'sdi_referral' => 'error', 'sdi_message' => rawurlencode( __( 'Security check failed. Please try again.', 'sdi-trust-core' ) ) ), $redirect ) );
			exit;
		}

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_referral' => 'error', 'sdi_message' => rawurlencode( sdi_tc_text( 'login_required' ) ) ), $redirect ) );
			exit;
		}

		$name  = isset( $_POST['sdi_referred_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sdi_referred_name'] ) ) : '';
		$email = isset( $_POST['sdi_referred_email'] ) ? sanitize_email( wp_unslash( $_POST['sdi_referred_email'] ) ) : '';
		$phone = isset( $_POST['sdi_referred_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sdi_referred_phone'] ) ) : '';

		$result = SDI_Referrals::submit( get_current_user_id(), $name, $email, $phone );

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_referral' => 'error', 'sdi_message' => rawurlencode( $result->get_error_message() ) ), $redirect ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( array( 'sdi_referral' => 'success', 'sdi_message' => rawurlencode( sdi_tc_text( 'referral_pending_notice' ) ) ), $redirect ) );
		exit;
	}
}
