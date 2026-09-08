<?php
/**
 * Members & Tiers admin list table.
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
 * Every member with their points balance and resolved scholarship
 * eligibility tier. Balances/tiers are derived values (not DB columns),
 * so sorting and pagination happen in PHP after computing stats for the
 * matching user set — acceptable at nonprofit membership scale.
 */
class SDI_Members_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'member',
				'plural'   => 'members',
				'ajax'     => false,
			)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_columns() {
		return array(
			'name'    => __( 'Member', 'sdi-trust-core' ),
			'plan'    => __( 'Plan', 'sdi-trust-core' ),
			'balance' => __( 'Points Balance', 'sdi-trust-core' ),
			'tier'    => __( 'Eligibility Level', 'sdi-trust-core' ),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_sortable_columns() {
		return array(
			'name'    => array( 'name', false ),
			'balance' => array( 'balance', false ),
		);
	}

	/**
	 * Column: member name + email.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_name( $item ) {
		return sprintf(
			'<strong><a href="%1$s">%2$s</a></strong><br /><span class="description">%3$s</span>',
			esc_url( get_edit_user_link( $item['id'] ) ),
			esc_html( $item['name'] ),
			esc_html( $item['email'] )
		);
	}

	/**
	 * Column: membership plan.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_plan( $item ) {
		return esc_html( ucfirst( $item['plan'] ) );
	}

	/**
	 * Column: points balance.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_balance( $item ) {
		return esc_html( number_format_i18n( $item['balance'] ) );
	}

	/**
	 * Column: resolved tier.
	 *
	 * @param array<string,mixed> $item Row data.
	 * @return string
	 */
	public function column_tier( $item ) {
		return $item['tier_award_amount']
			? esc_html( 'up to $' . number_format_i18n( $item['tier_award_amount'] ) )
			: esc_html__( 'Not yet eligible', 'sdi-trust-core' );
	}

	/**
	 * Fetch every relevant user (excluding administrators) and compute
	 * their points/tier stats. Shared between the list table and the CSV
	 * export so both always reflect exactly the same rows.
	 *
	 * @param string $search Optional search term against name/email.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_all_members_with_stats( $search = '' ) {
		$args = array(
			'role__not_in' => array( 'administrator' ),
			'orderby'      => 'display_name',
			'order'        => 'ASC',
			'number'       => 500,
		);

		if ( $search ) {
			$args['search']         = '*' . $search . '*';
			$args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
		}

		$users = get_users( $args );
		$rows  = array();

		foreach ( $users as $user ) {
			$status = SDI_Tiers::get_status( $user->ID );

			$rows[] = array(
				'id'                => $user->ID,
				'name'              => $user->display_name,
				'email'             => $user->user_email,
				'plan'              => SDI_Membership::get_plan( $user->ID ),
				'balance'           => $status['balance'],
				'tier_award_amount' => $status['tier_award_amount'],
			);
		}

		return $rows;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare_items() {
		$per_page = 25;
		$search   = ! empty( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only search box.

		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		$all_rows = self::get_all_members_with_stats( $search );

		$orderby = ( ! empty( $_GET['orderby'] ) && in_array( wp_unslash( $_GET['orderby'] ), array( 'name', 'balance' ), true ) ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'name'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = ( ! empty( $_GET['order'] ) && 'desc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ) ? 'desc' : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		usort(
			$all_rows,
			function ( $a, $b ) use ( $orderby, $order ) {
				$result = is_numeric( $a[ $orderby ] ) ? ( $a[ $orderby ] <=> $b[ $orderby ] ) : strcasecmp( $a[ $orderby ], $b[ $orderby ] );
				return ( 'desc' === $order ) ? -$result : $result;
			}
		);

		$total        = count( $all_rows );
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		$this->items = array_slice( $all_rows, $offset, $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total / $per_page ),
			)
		);
	}
}
