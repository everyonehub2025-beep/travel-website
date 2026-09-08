<?php
/**
 * Points ledger — append-only balance tracking.
 *
 * The ledger table is the single source of truth. A member's balance is
 * always SUM(points), cached in a short-lived transient that is busted on
 * every write. No mutable balance column exists anywhere.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read/write access to the sdi_points_ledger table.
 */
class SDI_Points {

	/**
	 * Valid ledger reason codes.
	 *
	 * @var string[]
	 */
	const VALID_REASONS = array( 'referral_approved', 'manual_adjust', 'admin_revoke', 'fintech_purchase' );

	/**
	 * How long a cached balance transient lives, as a safety net in case a
	 * direct database write ever bypasses add_entry() (it shouldn't).
	 *
	 * @var int
	 */
	const CACHE_TTL = DAY_IN_SECONDS;

	/**
	 * Get the ledger table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'sdi_points_ledger';
	}

	/**
	 * Append a ledger entry. This is the only method anywhere in the
	 * plugin that is allowed to INSERT into the ledger table.
	 *
	 * @param int         $user_id      Member user ID.
	 * @param int         $points       Signed point delta.
	 * @param string      $reason       One of VALID_REASONS.
	 * @param int|null    $reference_id Optional related record id (e.g. a referral id).
	 * @param string|null $note         Optional note. Required for manual adjustments by callers.
	 * @param int|null    $created_by   Admin user id for manual entries; null for system entries.
	 * @return int|WP_Error Inserted row id, or WP_Error on failure.
	 */
	public static function add_entry( $user_id, $points, $reason, $reference_id = null, $note = null, $created_by = null ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$points  = (int) $points;

		if ( ! $user_id ) {
			return new WP_Error( 'sdi_invalid_user', __( 'A valid member is required to record a points entry.', 'sdi-trust-core' ) );
		}

		if ( ! in_array( $reason, self::VALID_REASONS, true ) ) {
			return new WP_Error( 'sdi_invalid_reason', __( 'Unrecognized points ledger reason.', 'sdi-trust-core' ) );
		}

		if ( 0 === $points ) {
			return new WP_Error( 'sdi_zero_points', __( 'A points entry of zero is not permitted.', 'sdi-trust-core' ) );
		}

		$data   = array(
			'user_id'      => $user_id,
			'points'       => $points,
			'reason'       => sanitize_key( $reason ),
			'reference_id' => $reference_id ? absint( $reference_id ) : null,
			'note'         => $note ? sanitize_textarea_field( $note ) : null,
			'created_by'   => $created_by ? absint( $created_by ) : null,
			'created_at'   => current_time( 'mysql' ),
		);
		$format = array( '%d', '%d', '%s', '%d', '%s', '%d', '%s' );

		$inserted = $wpdb->insert( self::table(), $data, $format ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- $wpdb->insert() already prepares/escapes.

		if ( false === $inserted ) {
			return new WP_Error( 'sdi_db_error', __( 'Could not record the points ledger entry.', 'sdi-trust-core' ) );
		}

		self::bust_cache( $user_id );

		/**
		 * Fires after a points ledger entry is written.
		 *
		 * @param int    $user_id      Member user id.
		 * @param int    $points       Signed point delta.
		 * @param string $reason       Ledger reason code.
		 * @param int    $ledger_id    Inserted row id.
		 */
		do_action( 'sdi_points_entry_added', $user_id, $points, $reason, $wpdb->insert_id );

		return $wpdb->insert_id;
	}

	/**
	 * Get a member's current point balance (SUM of the ledger), cached.
	 *
	 * @param int $user_id Member user ID.
	 * @return int
	 */
	public static function get_balance( $user_id ) {
		global $wpdb;

		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return 0;
		}

		$cache_key = self::cache_key( $user_id );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return (int) $cached;
		}

		$table   = self::table();
		$balance = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(points), 0) FROM {$table} WHERE user_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name only, values are prepared.
				$user_id
			)
		);

		set_transient( $cache_key, $balance, self::CACHE_TTL );

		return $balance;
	}

	/**
	 * Get a member's full ledger history, most recent first, with a
	 * running balance computed for display.
	 *
	 * @param int $user_id Member user ID.
	 * @param int $limit   Max rows (0 = all).
	 * @param int $offset  Row offset for pagination.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_history( $user_id, $limit = 50, $offset = 0 ) {
		global $wpdb;

		$user_id = absint( $user_id );
		$table   = self::table();

		if ( $limit > 0 ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$user_id,
					$limit,
					$offset
				),
				ARRAY_A
			);
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC, id DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$user_id
				),
				ARRAY_A
			);
		}

		if ( ! $rows ) {
			return array();
		}

		// Running balance reads chronologically forward, so compute it on
		// the full (unlimited) ledger, not the possibly-paginated slice.
		$all_points = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT points FROM {$table} WHERE user_id = %d ORDER BY created_at ASC, id ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$user_id
			)
		);

		$running        = array_sum( array_map( 'intval', $all_points ) );
		$running_by_row = array();
		$cursor         = $running;

		// Walk the DESC-ordered $rows and unwind the running balance
		// backwards from the current total.
		foreach ( $rows as $row ) {
			$running_by_row[ $row['id'] ] = $cursor;
			$cursor                       -= (int) $row['points'];
		}

		foreach ( $rows as &$row ) {
			$row['running_balance'] = $running_by_row[ $row['id'] ];
		}
		unset( $row );

		return $rows;
	}

	/**
	 * Total points a member has earned via approved referrals (excludes
	 * manual adjustments/revokes) — used for admin reporting.
	 *
	 * @param int $user_id Member user ID.
	 * @return int
	 */
	public static function get_total_issued() {
		global $wpdb;
		$table = self::table();

		return (int) $wpdb->get_var( "SELECT COALESCE(SUM(points), 0) FROM {$table} WHERE points > 0" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- static query, no user input.
	}

	/**
	 * Bust the cached balance for a member.
	 *
	 * @param int $user_id Member user ID.
	 */
	public static function bust_cache( $user_id ) {
		delete_transient( self::cache_key( absint( $user_id ) ) );
	}

	/**
	 * Build the transient key for a member's cached balance.
	 *
	 * @param int $user_id Member user ID.
	 * @return string
	 */
	private static function cache_key( $user_id ) {
		return 'sdi_balance_' . $user_id;
	}
}
