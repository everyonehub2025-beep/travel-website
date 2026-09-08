<?php
/**
 * Centralized, filterable user-facing strings.
 *
 * Every string a member or admin can see funnels through sdi_tc_text() so
 * that compliance/legal wording changes (in particular anything describing
 * scholarship eligibility) are a one-line filter, never a code rewrite.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry and lookup for filterable/translatable plugin strings.
 */
class SDI_Strings {

	/**
	 * Default string values, keyed by string id.
	 *
	 * @var array<string,string>
	 */
	private static $defaults = null;

	/**
	 * Build the defaults table (lazy — needs __() available).
	 *
	 * @return array<string,string>
	 */
	private static function defaults() {
		if ( null !== self::$defaults ) {
			return self::$defaults;
		}

		self::$defaults = array(
			'scholarship_disclaimer'   => __( 'Scholarship eligibility levels reflect points accumulated through the referral program. They describe potential eligibility only and do not guarantee an award. All scholarships are subject to program rules, available funding, verification, and applicable law.', 'sdi-trust-core' ),
			'tier_label_progress'      => __( 'You are %1$d points from the next scholarship eligibility level.', 'sdi-trust-core' ),
			'tier_label_max'           => __( 'You have reached the highest scholarship eligibility level currently offered.', 'sdi-trust-core' ),
			'tier_label_none'          => __( 'Refer new members to begin building toward scholarship eligibility.', 'sdi-trust-core' ),
			'referral_pending_notice'  => __( 'Thank you — your referral has been submitted and is pending review. Points are awarded only after an administrator approves a referral.', 'sdi-trust-core' ),
			'referral_duplicate'       => __( 'This person has already been referred and cannot be submitted again.', 'sdi-trust-core' ),
			'referral_approved_email'  => __( 'Your referral for %1$s has been approved. %2$d points have been added to your account.', 'sdi-trust-core' ),
			'referral_rejected_email'  => __( 'Your referral for %1$s was not approved. %2$s', 'sdi-trust-core' ),
			'admin_new_referral_email' => __( 'A new referral from %1$s is awaiting review in the SDI Trust admin dashboard.', 'sdi-trust-core' ),
			'points_awarded_reason'    => __( 'Referral approved', 'sdi-trust-core' ),
			'points_manual_reason'     => __( 'Manual adjustment by administrator', 'sdi-trust-core' ),
			'membership_required'      => __( 'An active SDI Travel Trust membership is required to use this feature.', 'sdi-trust-core' ),
			'login_required'           => __( 'Please log in to your member account to continue.', 'sdi-trust-core' ),
			'rate_limited'             => __( 'You have submitted several referrals recently. Please try again in a little while.', 'sdi-trust-core' ),
			'fintech_pending'          => __( 'Integration pending — Phase 2. Eligible-purchase tracking through our participating fintech partner is not yet active.', 'sdi-trust-core' ),
		);

		return self::$defaults;
	}

	/**
	 * Look up a filterable, translatable string.
	 *
	 * @param string       $key     String identifier.
	 * @param array<mixed> $args    Optional sprintf() args.
	 * @return string
	 */
	public static function get( $key, $args = array() ) {
		$defaults = self::defaults();
		$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';

		/**
		 * Filters an individual SDI Trust Core user-facing string.
		 *
		 * @param string $default The default string.
		 * @param string $key     The string identifier.
		 */
		$string = apply_filters( 'sdi_tc_string', $default, $key );

		/**
		 * Filters one specific string by key, e.g. `sdi_tc_string_scholarship_disclaimer`.
		 *
		 * @param string $string The string value.
		 */
		$string = apply_filters( "sdi_tc_string_{$key}", $string );

		if ( ! empty( $args ) ) {
			return vsprintf( $string, $args );
		}

		return $string;
	}
}

/**
 * Convenience wrapper around SDI_Strings::get().
 *
 * @param string       $key  String identifier.
 * @param array<mixed> $args Optional sprintf() args.
 * @return string
 */
function sdi_tc_text( $key, $args = array() ) {
	return SDI_Strings::get( $key, $args );
}
