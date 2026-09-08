<?php
/**
 * Simple per-user rate limiting for referral submissions (fraud control).
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient-backed rate limiter. Not intended for high-precision throttling
 * — just enough friction to make scripted mass-submission impractical.
 */
class SDI_Rate_Limiter {

	/**
	 * Check whether a user is currently rate limited for a given action,
	 * and record this attempt if not.
	 *
	 * @param string $action  Short action key, e.g. 'referral_submit'.
	 * @param int    $user_id User ID being throttled.
	 * @param int    $max     Maximum attempts allowed within the window.
	 * @param int    $window  Window length in seconds.
	 * @return bool True if the action is allowed (and now recorded), false if rate limited.
	 */
	public static function allow( $action, $user_id, $max, $window ) {
		$key   = self::key( $action, $user_id );
		$count = (int) get_transient( $key );

		if ( $count >= $max ) {
			return false;
		}

		if ( 0 === $count ) {
			set_transient( $key, 1, $window );
		} else {
			set_transient( $key, $count + 1, $window );
		}

		return true;
	}

	/**
	 * Build the transient key for a rate-limited action.
	 *
	 * @param string $action  Action key.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	private static function key( $action, $user_id ) {
		return 'sdi_rl_' . sanitize_key( $action ) . '_' . absint( $user_id );
	}
}
