<?php
/**
 * Referral submission, validation, and approval workflow.
 *
 * Points are never awarded automatically on submission — approval is
 * manual and always has been, as a fraud control. See approve()/reject().
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CRUD + workflow for the sdi_referrals table.
 */
class SDI_Referrals {

	/**
	 * Valid referral statuses.
	 *
	 * @var string[]
	 */
	const STATUSES = array( 'pending', 'approved', 'rejected', 'duplicate' );

	/**
	 * Get the referrals table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'sdi_referrals';
	}

	/**
	 * Submit a new referral on behalf of a member.
	 *
	 * @param int    $referrer_id Referring member's user ID.
	 * @param string $name        Referred person's name.
	 * @param string $email       Referred person's email.
	 * @param string $phone       Referred person's phone (optional).
	 * @param string $context     'member' (default — requires the referrer to be the
	 *                            logged-in user, e.g. the dashboard form) or 'system'
	 *                            (a trusted server-side caller, e.g. a verified sign-up
	 *                            that arrived via a member's referral link).
	 * @return int|WP_Error Inserted referral id, or WP_Error describing the failure.
	 */
	public static function submit( $referrer_id, $name, $email, $phone = '', $context = 'member' ) {
		global $wpdb;

		$referrer_id = absint( $referrer_id );
		$name        = sanitize_text_field( $name );
		$email       = sanitize_email( $email );
		$phone       = sanitize_text_field( $phone );

		if ( ! $referrer_id ) {
			return new WP_Error( 'sdi_not_authorized', __( 'You must be logged in to submit a referral.', 'sdi-trust-core' ) );
		}

		if ( 'system' !== $context && ( ! is_user_logged_in() || get_current_user_id() !== $referrer_id ) ) {
			return new WP_Error( 'sdi_not_authorized', __( 'You must be logged in to submit a referral.', 'sdi-trust-core' ) );
		}

		if ( ! SDI_Membership::is_active_member( $referrer_id ) ) {
			return new WP_Error( 'sdi_membership_required', sdi_tc_text( 'membership_required' ) );
		}

		if ( empty( $name ) ) {
			return new WP_Error( 'sdi_missing_name', __( 'Please enter the name of the person you are referring.', 'sdi-trust-core' ) );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'sdi_invalid_email', __( 'Please enter a valid email address.', 'sdi-trust-core' ) );
		}

		$referrer = get_userdata( $referrer_id );
		if ( $referrer && strtolower( $referrer->user_email ) === strtolower( $email ) ) {
			return new WP_Error( 'sdi_self_referral', __( 'You cannot refer yourself.', 'sdi-trust-core' ) );
		}

		$rl_max    = (int) get_option( 'sdi_referral_rate_limit_count', 5 );
		$rl_window = (int) get_option( 'sdi_referral_rate_limit_window', HOUR_IN_SECONDS );
		if ( ! SDI_Rate_Limiter::allow( 'referral_submit', $referrer_id, $rl_max, $rl_window ) ) {
			return new WP_Error( 'sdi_rate_limited', sdi_tc_text( 'rate_limited' ) );
		}

		// Duplicate detection: has this referrer already referred this
		// email address, in any status?
		$table    = self::table();
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE referrer_id = %d AND referred_email = %s LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$referrer_id,
				$email
			)
		);

		$status = $existing ? 'duplicate' : 'pending';

		$data   = array(
			'referrer_id'    => $referrer_id,
			'referred_name'  => $name,
			'referred_email' => $email,
			'referred_phone' => $phone,
			'status'         => $status,
			'points_awarded' => 0,
			'created_at'     => current_time( 'mysql' ),
		);
		$format = array( '%d', '%s', '%s', '%s', '%s', '%d', '%s' );

		$inserted = $wpdb->insert( $table, $data, $format ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $inserted ) {
			return new WP_Error( 'sdi_db_error', __( 'Could not save the referral. Please try again.', 'sdi-trust-core' ) );
		}

		$referral_id = $wpdb->insert_id;

		if ( 'duplicate' === $status ) {
			return new WP_Error( 'sdi_duplicate_referral', sdi_tc_text( 'referral_duplicate' ) );
		}

		self::notify_admin_new_referral( $referral_id );

		do_action( 'sdi_referral_submitted', $referral_id, $referrer_id );

		return $referral_id;
	}

	/**
	 * Approve a pending referral: awards points, updates status, notifies
	 * the member. Only ever moves pending -> approved.
	 *
	 * @param int    $referral_id Referral row id.
	 * @param int    $admin_id    Reviewing admin's user id.
	 * @param string $note        Optional admin note.
	 * @return true|WP_Error
	 */
	public static function approve( $referral_id, $admin_id, $note = '' ) {
		if ( ! current_user_can( 'sdi_manage_referrals' ) ) {
			return new WP_Error( 'sdi_forbidden', __( 'You do not have permission to approve referrals.', 'sdi-trust-core' ) );
		}

		$referral = self::get( $referral_id );

		if ( ! $referral ) {
			return new WP_Error( 'sdi_not_found', __( 'Referral not found.', 'sdi-trust-core' ) );
		}

		if ( 'pending' !== $referral['status'] ) {
			return new WP_Error( 'sdi_invalid_status', __( 'Only pending referrals can be approved.', 'sdi-trust-core' ) );
		}

		$points = SDI_Tiers::get_points_per_referral();

		$ledger_result = SDI_Points::add_entry(
			$referral['referrer_id'],
			$points,
			'referral_approved',
			$referral_id,
			sdi_tc_text( 'points_awarded_reason' ),
			$admin_id
		);

		if ( is_wp_error( $ledger_result ) ) {
			return $ledger_result;
		}

		$updated = self::update_status( $referral_id, 'approved', $admin_id, $note, $points );

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		self::notify_member_approved( $referral_id );

		do_action( 'sdi_referral_approved', $referral_id, $referral['referrer_id'], $points );

		return true;
	}

	/**
	 * Reject a pending referral. No points are ever awarded.
	 *
	 * @param int    $referral_id Referral row id.
	 * @param int    $admin_id    Reviewing admin's user id.
	 * @param string $note        Admin note explaining the rejection.
	 * @return true|WP_Error
	 */
	public static function reject( $referral_id, $admin_id, $note = '' ) {
		if ( ! current_user_can( 'sdi_manage_referrals' ) ) {
			return new WP_Error( 'sdi_forbidden', __( 'You do not have permission to reject referrals.', 'sdi-trust-core' ) );
		}

		$referral = self::get( $referral_id );

		if ( ! $referral ) {
			return new WP_Error( 'sdi_not_found', __( 'Referral not found.', 'sdi-trust-core' ) );
		}

		if ( 'pending' !== $referral['status'] ) {
			return new WP_Error( 'sdi_invalid_status', __( 'Only pending referrals can be rejected.', 'sdi-trust-core' ) );
		}

		$updated = self::update_status( $referral_id, 'rejected', $admin_id, $note, 0 );

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		self::notify_member_rejected( $referral_id );

		do_action( 'sdi_referral_rejected', $referral_id, $referral['referrer_id'] );

		return true;
	}

	/**
	 * Shared status-update writer used by approve()/reject().
	 *
	 * @param int    $referral_id    Referral row id.
	 * @param string $status         New status.
	 * @param int    $admin_id       Reviewing admin id.
	 * @param string $note           Admin note.
	 * @param int    $points_awarded Points awarded (0 for rejections).
	 * @return true|WP_Error
	 */
	private static function update_status( $referral_id, $status, $admin_id, $note, $points_awarded ) {
		global $wpdb;

		$updated = $wpdb->update(
			self::table(),
			array(
				'status'         => $status,
				'points_awarded' => $points_awarded,
				'admin_note'     => $note ? sanitize_textarea_field( $note ) : null,
				'reviewed_by'    => absint( $admin_id ),
				'reviewed_at'    => current_time( 'mysql' ),
			),
			array( 'id' => absint( $referral_id ) ),
			array( '%s', '%d', '%s', '%d', '%s' ),
			array( '%d' )
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( false === $updated ) {
			return new WP_Error( 'sdi_db_error', __( 'Could not update the referral.', 'sdi-trust-core' ) );
		}

		return true;
	}

	/**
	 * Get a single referral row.
	 *
	 * @param int $referral_id Referral row id.
	 * @return array<string,mixed>|null
	 */
	public static function get( $referral_id ) {
		global $wpdb;
		$table = self::table();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $referral_id ) ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Get a member's own referrals. Always scoped to the given
	 * referrer_id — callers must pass the current user's own id; this
	 * method never trusts a caller-supplied "whose data" beyond that.
	 *
	 * @param int $referrer_id Member user ID.
	 * @param int $limit       Max rows.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_for_member( $referrer_id, $limit = 50 ) {
		global $wpdb;
		$table = self::table();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE referrer_id = %d ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $referrer_id ),
				absint( $limit )
			),
			ARRAY_A
		);

		return $rows ? $rows : array();
	}

	/**
	 * Count referrals by status, for the admin dashboard summary.
	 *
	 * @return array<string,int>
	 */
	public static function get_status_counts() {
		global $wpdb;
		$table = self::table();

		$rows = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- static query, no user input.

		$counts = array_fill_keys( self::STATUSES, 0 );

		foreach ( (array) $rows as $row ) {
			if ( isset( $counts[ $row['status'] ] ) ) {
				$counts[ $row['status'] ] = (int) $row['total'];
			}
		}

		return $counts;
	}

	/**
	 * Notify configured admin recipients of a new pending referral.
	 *
	 * @param int $referral_id Referral row id.
	 */
	private static function notify_admin_new_referral( $referral_id ) {
		$referral = self::get( $referral_id );
		if ( ! $referral ) {
			return;
		}

		$referrer     = get_userdata( $referral['referrer_id'] );
		$referrer_name = $referrer ? $referrer->display_name : __( 'A member', 'sdi-trust-core' );
		$recipients   = get_option( 'sdi_referral_notification_emails', get_option( 'admin_email' ) );

		wp_mail(
			$recipients,
			__( 'New referral awaiting review — SDI Travel Trust', 'sdi-trust-core' ),
			sdi_tc_text( 'admin_new_referral_email', array( $referrer_name ) )
		);
	}

	/**
	 * Notify the referring member that their referral was approved.
	 *
	 * @param int $referral_id Referral row id.
	 */
	private static function notify_member_approved( $referral_id ) {
		$referral = self::get( $referral_id );
		if ( ! $referral ) {
			return;
		}

		$referrer = get_userdata( $referral['referrer_id'] );
		if ( ! $referrer ) {
			return;
		}

		wp_mail(
			$referrer->user_email,
			__( 'Your referral was approved — SDI Travel Trust', 'sdi-trust-core' ),
			sdi_tc_text( 'referral_approved_email', array( $referral['referred_name'], (int) $referral['points_awarded'] ) )
		);
	}

	/**
	 * Notify the referring member that their referral was not approved.
	 *
	 * @param int $referral_id Referral row id.
	 */
	private static function notify_member_rejected( $referral_id ) {
		$referral = self::get( $referral_id );
		if ( ! $referral ) {
			return;
		}

		$referrer = get_userdata( $referral['referrer_id'] );
		if ( ! $referrer ) {
			return;
		}

		$reason = $referral['admin_note'] ? $referral['admin_note'] : __( 'Please contact us with any questions.', 'sdi-trust-core' );

		wp_mail(
			$referrer->user_email,
			__( 'An update on your referral — SDI Travel Trust', 'sdi-trust-core' ),
			sdi_tc_text( 'referral_rejected_email', array( $referral['referred_name'], $reason ) )
		);
	}
}
