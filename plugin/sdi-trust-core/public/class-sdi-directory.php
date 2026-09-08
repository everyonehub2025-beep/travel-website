<?php
/**
 * Native Travel Directory: a custom post type + taxonomy replacing the
 * Directorist plugin per "no need any core and any type plugin". Listings
 * are managed from the ordinary WordPress admin (Add New Travel Listing);
 * the front end is a client-side filtered search-and-grid, built to match
 * the Travel Directory design without needing a REST endpoint.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT, taxonomy, meta box, shortcode, and single-listing template for the
 * Travel Directory.
 */
class SDI_Directory {

	const POST_TYPE = 'sdi_listing';
	const TAXONOMY  = 'sdi_listing_category';

	/**
	 * Singleton instance.
	 *
	 * @var SDI_Directory|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return SDI_Directory
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire up hooks.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'save_meta_box' ) );
		add_shortcode( 'sdi_directory', array( $this, 'render_directory' ) );
		add_filter( 'single_template', array( $this, 'single_template' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
		add_action( 'sdi_inquiry_submitted', array( $this, 'maybe_create_draft_from_submission' ), 10, 2 );
	}

	/**
	 * When the theme's [sdi_inquiry_form type="directory_submission"] is
	 * submitted, create a draft listing so the admin reviews/edits/publishes
	 * it in one place instead of retyping details from an email.
	 *
	 * @param string               $type Inquiry form type.
	 * @param array<string,string> $data Submitted, sanitized field data.
	 */
	public function maybe_create_draft_from_submission( $type, $data ) {
		if ( 'directory_submission' !== $type || empty( $data['organization'] ) ) {
			return;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => 'draft',
				'post_title'   => sanitize_text_field( $data['organization'] ),
				'post_content' => isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '',
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return;
		}

		if ( ! empty( $data['website'] ) ) {
			update_post_meta( $post_id, '_sdi_listing_website', esc_url_raw( $data['website'] ) );
		}
		if ( ! empty( $data['email'] ) ) {
			update_post_meta( $post_id, '_sdi_listing_email', sanitize_email( $data['email'] ) );
		}

		if ( ! empty( $data['category'] ) && term_exists( $data['category'], self::TAXONOMY ) ) {
			wp_set_object_terms( $post_id, array( $data['category'] ), self::TAXONOMY );
		}
	}

	/**
	 * The 8 listing categories, shared with the theme's directory-submission
	 * inquiry form so both stay in sync from one place.
	 *
	 * @return array<string,string>
	 */
	public static function get_categories() {
		return apply_filters(
			'sdi_directory_categories',
			array(
				'travel_organizations'  => __( 'Travel Organizations', 'sdi-trust-core' ),
				'agencies_providers'    => __( 'Travel Agencies & Service Providers', 'sdi-trust-core' ),
				'equipment_accessories' => __( 'Travel Equipment & Accessories', 'sdi-trust-core' ),
				'transport_lodging'     => __( 'Transportation & Lodging Resources', 'sdi-trust-core' ),
				'technology_financial'  => __( 'Travel Technology & Financial Services', 'sdi-trust-core' ),
				'federal_government'    => __( 'Federal Government Agencies', 'sdi-trust-core' ),
				'state_government'      => __( 'State Government Agencies', 'sdi-trust-core' ),
				'community_nonprofit'   => __( 'Community & Nonprofit Resources', 'sdi-trust-core' ),
			)
		);
	}

	/**
	 * Register the sdi_listing CPT.
	 */
	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'               => __( 'Travel Directory', 'sdi-trust-core' ),
					'singular_name'      => __( 'Listing', 'sdi-trust-core' ),
					'add_new_item'       => __( 'Add New Listing', 'sdi-trust-core' ),
					'edit_item'          => __( 'Edit Listing', 'sdi-trust-core' ),
					'all_items'          => __( 'Travel Directory', 'sdi-trust-core' ),
					'search_items'       => __( 'Search Listings', 'sdi-trust-core' ),
					'not_found'          => __( 'No listings found.', 'sdi-trust-core' ),
				),
				'public'       => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-location-alt',
				'menu_position' => 26,
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
				'has_archive'  => false,
				'rewrite'      => array( 'slug' => 'travel-directory-listing' ),
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Register the listing category taxonomy.
	 */
	public function register_taxonomy() {
		register_taxonomy(
			self::TAXONOMY,
			self::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Listing Categories', 'sdi-trust-core' ),
					'singular_name' => __( 'Category', 'sdi-trust-core' ),
				),
				'hierarchical'      => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => 'travel-directory-category' ),
			)
		);

		if ( did_action( 'init' ) && ! wp_count_terms( self::TAXONOMY ) ) {
			foreach ( self::get_categories() as $slug => $label ) {
				if ( ! term_exists( $slug, self::TAXONOMY ) ) {
					wp_insert_term( $label, self::TAXONOMY, array( 'slug' => $slug ) );
				}
			}
		}
	}

	/**
	 * Register the listing details meta box.
	 */
	public function add_meta_box() {
		add_meta_box(
			'sdi_listing_details',
			__( 'Listing Details', 'sdi-trust-core' ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render the listing details meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'sdi_listing_meta', 'sdi_listing_meta_nonce' );

		$website      = get_post_meta( $post->ID, '_sdi_listing_website', true );
		$email        = get_post_meta( $post->ID, '_sdi_listing_email', true );
		$phone        = get_post_meta( $post->ID, '_sdi_listing_phone', true );
		$location     = get_post_meta( $post->ID, '_sdi_listing_location', true );
		$member_only  = get_post_meta( $post->ID, '_sdi_listing_member_only', true );
		?>
		<p>
			<label for="sdi_listing_website"><strong><?php esc_html_e( 'Website', 'sdi-trust-core' ); ?></strong></label><br />
			<input type="url" id="sdi_listing_website" name="sdi_listing_website" class="widefat" value="<?php echo esc_attr( $website ); ?>" placeholder="https://" />
		</p>
		<p>
			<label for="sdi_listing_email"><strong><?php esc_html_e( 'Contact Email', 'sdi-trust-core' ); ?></strong></label><br />
			<input type="email" id="sdi_listing_email" name="sdi_listing_email" class="widefat" value="<?php echo esc_attr( $email ); ?>" />
		</p>
		<p>
			<label for="sdi_listing_phone"><strong><?php esc_html_e( 'Phone', 'sdi-trust-core' ); ?></strong></label><br />
			<input type="text" id="sdi_listing_phone" name="sdi_listing_phone" class="widefat" value="<?php echo esc_attr( $phone ); ?>" />
		</p>
		<p>
			<label for="sdi_listing_location"><strong><?php esc_html_e( 'Location (City, State)', 'sdi-trust-core' ); ?></strong></label><br />
			<input type="text" id="sdi_listing_location" name="sdi_listing_location" class="widefat" value="<?php echo esc_attr( $location ); ?>" />
		</p>
		<p>
			<label>
				<input type="checkbox" name="sdi_listing_member_only" value="1" <?php checked( $member_only, '1' ); ?> />
				<?php esc_html_e( 'Member-only listing (full details hidden from non-members)', 'sdi-trust-core' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save the listing details meta box.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST['sdi_listing_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_listing_meta_nonce'] ) ), 'sdi_listing_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, '_sdi_listing_website', isset( $_POST['sdi_listing_website'] ) ? esc_url_raw( wp_unslash( $_POST['sdi_listing_website'] ) ) : '' );
		update_post_meta( $post_id, '_sdi_listing_email', isset( $_POST['sdi_listing_email'] ) ? sanitize_email( wp_unslash( $_POST['sdi_listing_email'] ) ) : '' );
		update_post_meta( $post_id, '_sdi_listing_phone', isset( $_POST['sdi_listing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sdi_listing_phone'] ) ) : '' );
		update_post_meta( $post_id, '_sdi_listing_location', isset( $_POST['sdi_listing_location'] ) ? sanitize_text_field( wp_unslash( $_POST['sdi_listing_location'] ) ) : '' );
		update_post_meta( $post_id, '_sdi_listing_member_only', empty( $_POST['sdi_listing_member_only'] ) ? '' : '1' );
	}

	/**
	 * Add Category/Location columns to the admin listing table.
	 *
	 * @param array<string,string> $columns Existing columns.
	 * @return array<string,string>
	 */
	public function admin_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['sdi_location']     = __( 'Location', 'sdi-trust-core' );
				$new['sdi_member_only']  = __( 'Member Only', 'sdi-trust-core' );
			}
		}
		return $new;
	}

	/**
	 * Render the custom admin column content.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function admin_column_content( $column, $post_id ) {
		if ( 'sdi_location' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_sdi_listing_location', true ) );
		} elseif ( 'sdi_member_only' === $column ) {
			echo get_post_meta( $post_id, '_sdi_listing_member_only', true ) ? '✓' : '—'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static characters.
		}
	}

	/**
	 * Whether the current visitor may see a member-only listing's full
	 * details.
	 *
	 * @return bool
	 */
	private function visitor_can_see_member_only() {
		return is_user_logged_in() && class_exists( 'SDI_Membership' ) && SDI_Membership::is_active_member( get_current_user_id() );
	}

	/**
	 * [sdi_directory] — search/filter tool and listing grid.
	 *
	 * @return string
	 */
	public function render_directory() {
		$listings = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 300,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$categories       = self::get_categories();
		$can_see_gated    = $this->visitor_can_see_member_only();

		ob_start();
		?>
		<div class="sdi-directory" data-sdi-directory>
			<form class="sdi-directory__search" onsubmit="return false;">
				<input type="search" class="sdi-directory__field" data-sdi-filter="keyword" placeholder="<?php esc_attr_e( 'Search by name or keyword', 'sdi-trust-core' ); ?>" aria-label="<?php esc_attr_e( 'Search by name or keyword', 'sdi-trust-core' ); ?>" />
				<select class="sdi-directory__field" data-sdi-filter="category" aria-label="<?php esc_attr_e( 'Category', 'sdi-trust-core' ); ?>">
					<option value=""><?php esc_html_e( 'All categories', 'sdi-trust-core' ); ?></option>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<input type="text" class="sdi-directory__field" data-sdi-filter="location" placeholder="<?php esc_attr_e( 'City or state', 'sdi-trust-core' ); ?>" aria-label="<?php esc_attr_e( 'City or state', 'sdi-trust-core' ); ?>" />
				<label class="sdi-directory__check">
					<input type="checkbox" data-sdi-filter="member-only" />
					<?php esc_html_e( 'Member-only listings', 'sdi-trust-core' ); ?>
				</label>
			</form>

			<p class="sdi-directory__count" data-sdi-directory-count></p>

			<div class="sdi-directory__grid">
				<?php if ( empty( $listings ) ) : ?>
					<p class="sdi-empty"><?php esc_html_e( 'Listings are added by the SDI Travel Trust team — check back soon.', 'sdi-trust-core' ); ?></p>
				<?php endif; ?>
				<?php foreach ( $listings as $listing ) : ?>
					<?php
					$terms         = get_the_terms( $listing->ID, self::TAXONOMY );
					$term_slugs    = $terms && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'slug' ) : array();
					$term_labels   = $terms && ! is_wp_error( $terms ) ? wp_list_pluck( $terms, 'name' ) : array();
					$location      = get_post_meta( $listing->ID, '_sdi_listing_location', true );
					$member_only   = (bool) get_post_meta( $listing->ID, '_sdi_listing_member_only', true );
					$gated         = $member_only && ! $can_see_gated;
					?>
					<a class="sdi-listing-card"
						href="<?php echo esc_url( get_permalink( $listing ) ); ?>"
						data-sdi-listing
						data-category="<?php echo esc_attr( implode( ' ', $term_slugs ) ); ?>"
						data-location="<?php echo esc_attr( strtolower( $location ) ); ?>"
						data-member-only="<?php echo $member_only ? '1' : '0'; ?>"
						data-keyword="<?php echo esc_attr( strtolower( $listing->post_title . ' ' . implode( ' ', $term_labels ) ) ); ?>"
					>
						<span class="sdi-listing-card__category"><?php echo esc_html( implode( ', ', $term_labels ) ); ?></span>
						<h3 class="sdi-listing-card__title"><?php echo esc_html( $listing->post_title ); ?></h3>
						<?php if ( $location ) : ?>
							<p class="sdi-listing-card__location"><?php echo esc_html( $location ); ?></p>
						<?php endif; ?>
						<?php if ( $gated ) : ?>
							<p><?php esc_html_e( 'Member-only listing — log in or join to view full details.', 'sdi-trust-core' ); ?></p>
						<?php else : ?>
							<p><?php echo esc_html( wp_trim_words( $listing->post_excerpt ? $listing->post_excerpt : $listing->post_content, 18 ) ); ?></p>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Load the single-listing template, preferring a theme override.
	 *
	 * @param string $template Default template path.
	 * @return string
	 */
	public function single_template( $template ) {
		if ( ! is_singular( self::POST_TYPE ) ) {
			return $template;
		}

		$theme_override = locate_template( 'sdi-trust-core/single-listing.php' );
		if ( $theme_override ) {
			return $theme_override;
		}

		return SDI_TC_PATH . 'public/templates/single-listing.php';
	}
}
