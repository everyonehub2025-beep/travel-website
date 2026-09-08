<?php
/**
 * MemberPress bridge — loosely coupled.
 *
 * Every call into MemberPress is wrapped in class_exists()/function_exists()
 * so the plugin activates and runs cleanly whether or not MemberPress is
 * installed. When it's absent, membership gating degrades open (features
 * work for any logged-in user) rather than fatal-erroring or silently
 * locking everyone out.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin read-only bridge into MemberPress membership state.
 */
class SDI_Membership {

	/**
	 * Whether MemberPress is active on this site.
	 *
	 * @return bool
	 */
	public static function is_memberpress_active() {
		return class_exists( '\MeprUser' ) && class_exists( '\MeprProduct' );
	}

	/**
	 * Whether a user currently holds an active SDI membership.
	 *
	 * Degrades to "true" for any logged-in user when MemberPress is not
	 * installed, so referral/points features remain usable during setup
	 * or on a site that manages membership another way.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_active_member( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return false;
		}

		if ( ! self::is_memberpress_active() ) {
			return (bool) get_userdata( $user_id );
		}

		$mepr_user = new \MeprUser( $user_id );

		return (bool) $mepr_user->is_active();
	}

	/**
	 * Resolve a user's membership plan as 'individual', 'family', or
	 * 'none'. Product IDs are configured in Settings so this stays correct
	 * if the client renames or re-creates MemberPress products.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_plan( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! self::is_memberpress_active() || ! $user_id ) {
			return 'none';
		}

		$mepr_user       = new \MeprUser( $user_id );
		$active_products = method_exists( $mepr_user, 'active_product_subscriptions' )
			? $mepr_user->active_product_subscriptions( 'ids' )
			: array();

		if ( empty( $active_products ) ) {
			return 'none';
		}

		$individual_id = absint( get_option( 'sdi_membership_individual_product_id', 0 ) );
		$family_id     = absint( get_option( 'sdi_membership_family_product_id', 0 ) );

		if ( $family_id && in_array( $family_id, $active_products, true ) ) {
			return 'family';
		}

		if ( $individual_id && in_array( $individual_id, $active_products, true ) ) {
			return 'individual';
		}

		return 'none';
	}

	/**
	 * Get a short, human-readable membership status label for display in
	 * the member dashboard Overview panel.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_status_label( $user_id ) {
		if ( ! self::is_memberpress_active() ) {
			return __( 'Membership status unavailable — MemberPress is not connected.', 'sdi-trust-core' );
		}

		$plan = self::get_plan( $user_id );

		if ( 'none' === $plan ) {
			return __( 'No active membership', 'sdi-trust-core' );
		}

		$is_active = self::is_active_member( $user_id );

		if ( 'family' === $plan ) {
			return $is_active
				? __( 'Family Membership — Active', 'sdi-trust-core' )
				: __( 'Family Membership — Inactive', 'sdi-trust-core' );
		}

		return $is_active
			? __( 'Individual Membership — Active', 'sdi-trust-core' )
			: __( 'Individual Membership — Inactive', 'sdi-trust-core' );
	}
}
