<?php
/**
 * Public-facing asset loading and read-only third-party bridges
 * (Charitable donation history, Directorist directory links).
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end asset manager and integration helpers.
 */
class SDI_Public {

	/**
	 * Singleton instance.
	 *
	 * @var SDI_Public|null
	 */
	private static $instance = null;

	/**
	 * Whether front-end assets have already been enqueued this request.
	 *
	 * @var bool
	 */
	private $assets_enqueued = false;

	/**
	 * Get the singleton instance.
	 *
	 * @return SDI_Public
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register (but don't necessarily print) assets on wp_enqueue_scripts
	 * so enqueue_front_assets() can cheaply flag them for output even when
	 * called from inside a shortcode callback, after the hook has fired.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register (not enqueue) the plugin's public CSS/JS.
	 */
	public function register_assets() {
		wp_register_style(
			'sdi-trust-core-public',
			SDI_TC_URL . 'public/assets/css/sdi-public.css',
			array(),
			SDI_TC_VERSION
		);

		wp_register_script(
			'sdi-trust-core-public',
			SDI_TC_URL . 'public/assets/js/sdi-public.js',
			array(),
			SDI_TC_VERSION,
			true
		);
	}

	/**
	 * Enqueue the plugin's public CSS/JS. Safe to call repeatedly — a
	 * page with several `[sdi_*]` shortcodes only loads the assets once.
	 */
	public function enqueue_front_assets() {
		if ( $this->assets_enqueued ) {
			return;
		}

		wp_enqueue_style( 'sdi-trust-core-public' );
		wp_enqueue_script( 'sdi-trust-core-public' );

		$this->assets_enqueued = true;
	}

	/**
	 * Whether Charitable is active on this site.
	 *
	 * @return bool
	 */
	public static function is_charitable_active() {
		return class_exists( 'Charitable' ) && function_exists( 'charitable_get_table' );
	}

	/**
	 * Read-only donation history for a member from Charitable. Never
	 * writes to Charitable's tables — display only.
	 *
	 * @param int $user_id Member user ID.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_donation_history( $user_id ) {
		if ( ! self::is_charitable_active() ) {
			return array();
		}

		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return array();
		}

		$donations = get_posts(
			array(
				'post_type'      => 'donation',
				'post_status'    => array( 'charitable-completed', 'charitable-pending' ),
				'author'         => $user_id,
				'posts_per_page' => 100,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'no_found_rows'  => true,
			)
		);

		$history = array();

		foreach ( $donations as $donation_post ) {
			$amount = get_post_meta( $donation_post->ID, '_charitable_donation_amount', true );

			$history[] = array(
				'id'     => $donation_post->ID,
				'date'   => get_the_date( '', $donation_post ),
				'amount' => $amount ? (float) $amount : 0.0,
				'status' => get_post_status( $donation_post ),
			);
		}

		return $history;
	}

	/**
	 * Whether Directorist is active on this site.
	 *
	 * @return bool
	 */
	public static function is_directorist_active() {
		return class_exists( 'Directorist\\Helper' ) || function_exists( 'directorist_get_listing_url' );
	}

	/**
	 * Get the front-end directory URL to link the dashboard's Directory
	 * panel into, filterable so an admin can point it at a specific page.
	 *
	 * @return string
	 */
	public static function get_directory_url() {
		/**
		 * Filters the URL the member dashboard's Directory panel links to.
		 *
		 * @param string $url Default directory URL.
		 */
		return apply_filters( 'sdi_directory_url', home_url( '/travel-directory/' ) );
	}
}
