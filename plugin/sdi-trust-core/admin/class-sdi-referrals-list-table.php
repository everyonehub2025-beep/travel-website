<?php
/**
 * Referrals admin list table.
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
 * Sortable, filterable, bulk-actionable table of referrals.
 */
class SDI_Referrals_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'referral',
				'plural'   => 'referrals',
				'ajax'     => false,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'referred_name'  => __( 'Referred Person', 'sdi-trust-core' ),
			'referrer'       => __( 'Referred By', 'sdi-trust-core' ),
			'status'         => __( 'Status', 'sdi-trust-core' ),
			'points_awarded' => __( 'Points', 'sdi-trust-core' ),
			'created_at'     => __( 'Submitted', 'sdi-trust-core' ),
			'actions'        => __( 'Actions', 'sdi-trust-core' ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_sortable_columns() {
		return array(
			'created_at' => array( 'created_at', true ),
			'status'     => array( 'status', false ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_bulk_actions() {
		return array(
			'approve' => __( 'Approve', 'sdi-trust-core' ),
			'reject'  => __( 'Reject', 'sdi-trust-core' ),
		);
	}

	/**
	 * Column: row select checkbox.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="referral_ids[]" value="%d" />', absint( $item['id'] ) );
	}

	/**
	 * Column: referred person's name + email.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_referred_name( $item ) {
		return sprintf(
			'<strong>%1$s</strong><br /><span class="description">%2$s</span>',
			esc_html( $item['referred_name'] ),
			esc_html( $item['referred_email'] )
		);
	}

	/**
	 * Column: referring member.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_referrer( $item ) {
		$user = get_userdata( $item['referrer_id'] );

		if ( ! $user ) {
			return esc_html__( '(deleted user)', 'sdi-trust-core' );
		}

		return sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( get_edit_user_link( $user->ID ) ),
			esc_html( $user->display_name )
		);
	}

	/**
	 * Column: status badge.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_status( $item ) {
		return sprintf( '<span class="sdi-admin-badge sdi-admin-badge--%1$s">%2$s</span>', esc_attr( $item['status'] ), esc_html( ucfirst( $item['status'] ) ) );
	}

	/**
	 * Column: points awarded.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_points_awarded( $item ) {
		return $item['points_awarded'] ? esc_html( '+' . (int) $item['points_awarded'] ) : '—';
	}

	/**
	 * Column: submitted date.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_created_at( $item ) {
		return esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['created_at'] ) );
	}

	/**
	 * Column: row-level approve/reject actions.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_actions( $item ) {
		if ( 'pending' !== $item['status'] ) {
			return '&#8212;';
		}

		$base_url = admin_url( 'admin-post.php' );

		$approve_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'      => 'sdi_referral_action',
					'do'          => 'approve',
					'referral_id' => $item['id'],
				),
				$base_url
			),
			'sdi_referral_action_' . $item['id']
		);

		$reject_url = wp_nonce_url(
			add_query_arg(
				array(
					'action'      => 'sdi_referral_action',
					'do'          => 'reject',
					'referral_id' => $item['id'],
				),
				$base_url
			),
			'sdi_referral_action_' . $item['id']
		);

		return sprintf(
			'<a class="button button-primary button-small" href="%1$s">%2$s</a> <a class="button button-small sdi-admin-reject" href="%3$s">%4$s</a>',
			esc_url( $approve_url ),
			esc_html__( 'Approve', 'sdi-trust-core' ),
			esc_url( $reject_url ),
			esc_html__( 'Reject', 'sdi-trust-core' )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_views() {
		$counts       = SDI_Referrals::get_status_counts();
		$current      = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter link state.
		$base_url     = remove_query_arg( array( 'status', 'paged' ) );
		$total        = array_sum( $counts );

		$views = array(
			'all' => sprintf(
				'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
				esc_url( $base_url ),
				( '' === $current ) ? 'current' : '',
				esc_html__( 'All', 'sdi-trust-core' ),
				$total
			),
		);

		foreach ( SDI_Referrals::STATUSES as $status ) {
			$views[ $status ] = sprintf(
				'<a href="%1$s" class="%2$s">%3$s <span class="count">(%4$d)</span></a>',
				esc_url( add_query_arg( 'status', $status, $base_url ) ),
				( $current === $status ) ? 'current' : '',
				esc_html( ucfirst( $status ) ),
				$counts[ $status ]
			);
		}

		return $views;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_items() {
		global $wpdb;

		$table    = SDI_Referrals::table();
		$per_page = 20;

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $_GET['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
			$status = sanitize_key( wp_unslash( $_GET['status'] ) );
			if ( in_array( $status, SDI_Referrals::STATUSES, true ) ) {
				$where[]  = 'status = %s';
				$params[] = $status;
			}
		}

		if ( ! empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$search   = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '%';
			$where[]  = '(referred_name LIKE %s OR referred_email LIKE %s)';
			$params[] = $search;
			$params[] = $search;
		}

		$where_sql = implode( ' AND ', $where );

		$orderby = 'created_at';
		if ( ! empty( $_GET['orderby'] ) && in_array( wp_unslash( $_GET['orderby'] ), array( 'created_at', 'status' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$orderby = sanitize_key( wp_unslash( $_GET['orderby'] ) );
		}
		$order = ( ! empty( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where_sql/$orderby/$order are built from a fixed allow-list above, not raw user input; $params are prepared.
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = $params ? $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ) : $wpdb->get_var( $count_sql );

		$sql       = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
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
