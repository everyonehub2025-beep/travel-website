<?php
/**
 * Points ledger admin list table.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Full, filterable log of every points ledger entry.
 */
class SDI_Ledger_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'entry',
				'plural'   => 'entries',
				'ajax'     => false,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns() {
		return array(
			'created_at' => __( 'Date', 'sdi-trust-core' ),
			'user'       => __( 'Member', 'sdi-trust-core' ),
			'points'     => __( 'Points', 'sdi-trust-core' ),
			'reason'     => __( 'Reason', 'sdi-trust-core' ),
			'note'       => __( 'Note', 'sdi-trust-core' ),
			'created_by' => __( 'Recorded By', 'sdi-trust-core' ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
			'points'     => array( 'points', false ),
		);
	}

	/**
	 * Column: date.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_created_at( $item ) {
		return esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['created_at'] ) );
	}

	/**
	 * Column: member.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_user( $item ) {
		$user = get_userdata( $item['user_id'] );

		if ( ! $user ) {
			return esc_html__( '(deleted user)', 'sdi-trust-core' );
		}

		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_user_link( $user->ID ) ), esc_html( $user->display_name ) );
	}

	/**
	 * Column: signed point delta.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_points( $item ) {
		$points = (int) $item['points'];
		$class  = ( $points >= 0 ) ? 'sdi-admin-amount--positive' : 'sdi-admin-amount--negative';

		return sprintf( '<span class="%1$s">%2$s</span>', esc_attr( $class ), esc_html( ( $points >= 0 ? '+' : '' ) . $points ) );
	}

	/**
	 * Column: reason code, humanized.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_reason( $item ) {
		$labels = array(
			'referral_approved' => __( 'Referral approved', 'sdi-trust-core' ),
			'manual_adjust'      => __( 'Manual adjustment', 'sdi-trust-core' ),
			'admin_revoke'       => __( 'Administrative correction', 'sdi-trust-core' ),
			'fintech_purchase'   => __( 'Eligible purchase', 'sdi-trust-core' ),
		);

		return esc_html( $labels[ $item['reason'] ] ?? $item['reason'] );
	}

	/**
	 * Column: free-text note.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_note( $item ) {
		return $item['note'] ? esc_html( $item['note'] ) : '&#8212;';
	}

	/**
	 * Column: admin who recorded a manual entry.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_created_by( $item ) {
		if ( ! $item['created_by'] ) {
			return esc_html__( 'System', 'sdi-trust-core' );
		}

		$user = get_userdata( $item['created_by'] );

		return $user ? esc_html( $user->display_name ) : esc_html__( '(deleted user)', 'sdi-trust-core' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_items() {
		global $wpdb;

		$table    = SDI_Points::table();
		$per_page = 25;

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $_GET['user_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
			$where[]  = 'user_id = %d';
			$params[] = absint( wp_unslash( $_GET['user_id'] ) );
		}

		if ( ! empty( $_GET['reason'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$reason = sanitize_key( wp_unslash( $_GET['reason'] ) );
			if ( in_array( $reason, SDI_Points::VALID_REASONS, true ) ) {
				$where[]  = 'reason = %s';
				$params[] = $reason;
			}
		}

		if ( ! empty( $_GET['date_from'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$where[]  = 'created_at >= %s';
			$params[] = sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) . ' 00:00:00';
		}

		if ( ! empty( $_GET['date_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$where[]  = 'created_at <= %s';
			$params[] = sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) . ' 23:59:59';
		}

		$where_sql = implode( ' AND ', $where );

		$orderby = ( ! empty( $_GET['orderby'] ) && in_array( wp_unslash( $_GET['orderby'] ), array( 'created_at', 'points' ), true ) ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'created_at'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = ( ! empty( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where_sql/$orderby/$order come from a fixed allow-list, $params are prepared.
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql );

		$sql        = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$row_params = array_merge( $params, array( $per_page, $offset ) );
		$this->items = $wpdb->get_results( $wpdb->prepare( $sql, $row_params ), ARRAY_A );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$this->set_pagination_args(
			array(
				'total_items' => (int) $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}
}
