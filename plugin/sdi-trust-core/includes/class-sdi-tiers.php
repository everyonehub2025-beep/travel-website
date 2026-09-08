<?php
/**
 * Scholarship eligibility tier resolution.
 *
 * Tiers describe a *scholarship eligibility level*, never a guaranteed
 * award. Every string returned here must stay conditional — see
 * SDI_Strings for the shared disclaimer text.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves a member's points balance into a tier / progress structure.
 */
class SDI_Tiers {

	/**
	 * Get the configured tier thresholds, points => award-level label.
	 *
	 * Filterable via `sdi_tier_thresholds` so compliance review can change
	 * wording (or point values) without touching code. Keys are always
	 * sorted ascending regardless of how the filter/option supplies them.
	 *
	 * @return array<int,string>
	 */
	public static function get_thresholds() {
		$stored = get_option( 'sdi_tier_thresholds', array() );

		if ( ! is_array( $stored ) || empty( $stored ) ) {
			$stored = array(
				300  => 3000,
				600  => 6000,
				900  => 9000,
				1200 => 12000,
			);
		}

		/**
		 * Filters the tier point thresholds and their associated award-level
		 * amounts (in dollars, used only for display).
		 *
		 * @param array<int,int> $stored Points => award level amount.
		 */
		$thresholds = apply_filters( 'sdi_tier_thresholds', $stored );

		if ( ! is_array( $thresholds ) ) {
			$thresholds = $stored;
		}

		ksort( $thresholds, SORT_NUMERIC );

		return $thresholds;
	}

	/**
	 * Get points-per-approved-referral, filterable.
	 *
	 * @return int
	 */
	public static function get_points_per_referral() {
		$default = (int) get_option( 'sdi_points_per_referral', 30 );

		/**
		 * Filters the number of points awarded per approved referral.
		 *
		 * @param int $default Default points value.
		 */
		return (int) apply_filters( 'sdi_points_per_referral', $default );
	}

	/**
	 * Resolve a full tier status structure for a member.
	 *
	 * @param int $user_id Member user ID.
	 * @return array{
	 *     balance:int,
	 *     tier_index:int,
	 *     tier_threshold:int|null,
	 *     tier_award_amount:int|null,
	 *     next_threshold:int|null,
	 *     points_to_next:int|null,
	 *     percent_progress:float,
	 *     is_max_tier:bool,
	 *     label:string,
	 *     disclaimer:string
	 * }
	 */
	public static function get_status( $user_id ) {
		$balance    = SDI_Points::get_balance( $user_id );
		$thresholds = self::get_thresholds();
		$points     = array_keys( $thresholds );

		$tier_index     = -1;
		$tier_threshold = null;
		$award_amount   = null;

		foreach ( $points as $index => $threshold ) {
			if ( $balance >= $threshold ) {
				$tier_index     = $index;
				$tier_threshold = $threshold;
				$award_amount   = $thresholds[ $threshold ];
			}
		}

		$is_max_tier    = ( $tier_index >= 0 && $tier_index === ( count( $points ) - 1 ) );
		$next_threshold = null;
		$points_to_next = null;

		if ( ! $is_max_tier ) {
			$next_threshold = isset( $points[ $tier_index + 1 ] ) ? $points[ $tier_index + 1 ] : ( $points[0] ?? null );
			if ( null !== $next_threshold ) {
				$points_to_next = max( 0, $next_threshold - $balance );
			}
		}

		// Progress bar percentage toward the *next* threshold, measured
		// from the previous threshold (or zero for the first tier).
		$percent_progress = 0.0;
		if ( $is_max_tier ) {
			$percent_progress = 100.0;
		} elseif ( null !== $next_threshold ) {
			$floor             = ( $tier_index >= 0 ) ? $tier_threshold : 0;
			$span              = max( 1, $next_threshold - $floor );
			$percent_progress = min( 100.0, max( 0.0, ( ( $balance - $floor ) / $span ) * 100 ) );
		}

		if ( $is_max_tier ) {
			$label = sdi_tc_text( 'tier_label_max' );
		} elseif ( $tier_index < 0 ) {
			$label = sdi_tc_text( 'tier_label_none' );
		} else {
			$label = sdi_tc_text( 'tier_label_progress', array( $points_to_next ) );
		}

		return array(
			'balance'           => $balance,
			'tier_index'        => $tier_index,
			'tier_threshold'    => $tier_threshold,
			'tier_award_amount' => $award_amount,
			'next_threshold'    => $next_threshold,
			'points_to_next'    => $points_to_next,
			'percent_progress'  => round( $percent_progress, 1 ),
			'is_max_tier'       => $is_max_tier,
			'label'             => $label,
			'disclaimer'        => sdi_tc_text( 'scholarship_disclaimer' ),
		);
	}

	/**
	 * Count how many members currently fall in each tier band, for the
	 * admin Members & Tiers screen. Necessarily iterates all members with
	 * ledger activity — acceptable at nonprofit membership scale; revisit
	 * with a materialized rollup if the roster grows into the tens of
	 * thousands.
	 *
	 * @return array<string,int> Tier label (or "no_tier") => member count.
	 */
	public static function get_tier_distribution() {
		global $wpdb;

		$table    = SDI_Points::table();
		$user_ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- static query, no user input.

		$thresholds  = self::get_thresholds();
		$distribution = array( 'no_tier' => 0 );
		foreach ( array_keys( $thresholds ) as $threshold ) {
			$distribution[ (string) $threshold ] = 0;
		}

		foreach ( $user_ids as $user_id ) {
			$status = self::get_status( (int) $user_id );
			$key    = $status['tier_threshold'] ? (string) $status['tier_threshold'] : 'no_tier';
			if ( ! isset( $distribution[ $key ] ) ) {
				$distribution[ $key ] = 0;
			}
			++$distribution[ $key ];
		}

		return $distribution;
	}
}
