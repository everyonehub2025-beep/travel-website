<?php
/**
 * Membership state — native by default, with an optional MemberPress
 * bridge for a site that already runs it.
 *
 * The primary source of truth is the `sdi_member` role plus the
 * `sdi_membership_status`/`sdi_membership_tier` user meta set by
 * SDI_Auth (native sign-up, email verification, and the profile-screen
 * admin override). If MemberPress is also active — e.g. a client migrating
 * from an existing MemberPress setup — its product subscriptions take
 * precedence, so nothing here breaks a site that still relies on it.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Membership status/plan lookups.
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
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_active_member( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return false;
		}

		if ( self::is_memberpress_active() ) {
			$mepr_user = new \MeprUser( $user_id );
			return (bool) $mepr_user->is_active();
		}

		$user = get_userdata( $user_id );

		if ( ! $user || ! in_array( SDI_Auth::ROLE, (array) $user->roles, true ) ) {
			return false;
		}

		return SDI_Auth::STATUS_ACTIVE === SDI_Auth::get_status( $user_id );
	}

	/**
	 * Resolve a user's membership plan as 'individual', 'family', or
	 * 'none'.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_plan( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return 'none';
		}

		if ( self::is_memberpress_active() ) {
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

		$tier = SDI_Auth::get_tier( $user_id );

		return $tier ? $tier : 'none';
	}

	/**
	 * Get a short, human-readable membership status label for display in
	 * the member dashboard Overview panel.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_status_label( $user_id ) {
		$plan = self::get_plan( $user_id );

		if ( 'none' === $plan ) {
			return __( 'No active membership', 'sdi-trust-core' );
		}

		$is_active = self::is_active_member( $user_id );
		$plan_label = ( 'family' === $plan ) ? __( 'Family', 'sdi-trust-core' ) : __( 'Individual', 'sdi-trust-core' );

		if ( $is_active ) {
			/* translators: %s: plan label (Individual/Family) */
			return sprintf( __( '%s Membership — Active', 'sdi-trust-core' ), $plan_label );
		}

		if ( ! self::is_memberpress_active() && SDI_Auth::STATUS_PENDING === SDI_Auth::get_status( $user_id ) ) {
			return __( 'Email verification pending', 'sdi-trust-core' );
		}

		/* translators: %s: plan label (Individual/Family) */
		return sprintf( __( '%s Membership — Inactive', 'sdi-trust-core' ), $plan_label );
	}
}
