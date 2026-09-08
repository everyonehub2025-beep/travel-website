<?php
/**
 * One Click Demo Import integration.
 *
 * Once the "One Click Demo Import" plugin (registered as required in
 * inc/plugin-activation.php) is active, this adds Appearance ▸ Import Demo
 * Data and wires it to the bundled WXR so a single click imports every
 * page, the header, footer, and all navigation menus — then finishes the
 * setup that would otherwise be manual (menu locations, front/posts page,
 * permalinks) automatically.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'pt-ocdi/import_files', 'sdi_ocdi_import_files' );

/**
 * Tells One Click Demo Import which file to offer and what it contains.
 *
 * @return array[]
 */
function sdi_ocdi_import_files() {
	return array(
		array(
			'import_file_name'  => 'SDI Travel Trust — Complete Site',
			'import_file_url'   => SDI_THEME_URI . '/demo-data/sdi-demo-content.xml',
			'preview_image_url' => SDI_THEME_URI . '/screenshot.png',
			'import_notice'     => __(
				'This imports every page (Home, About Us, Membership, Travel Directory, Programs, Scholarships, Fundraising, Partnerships, News & Resources, Contact, Legal), the header, footer, and all navigation menus — fully laid out and ready to customize in Elementor. It also creates demo member accounts, sample Travel Directory listings, and sample news posts for testing; delete those before launch (see CLIENT-HANDOVER.md). Safe to re-run on a fresh install — it does not remove anything already on the site.',
				'sdi-travel'
			),
		),
	);
}

add_action( 'pt-ocdi/before_import', 'sdi_ocdi_before_import' );

/**
 * Requires Elementor to be active before the import runs, since every
 * imported page's layout data is Elementor's own format.
 */
function sdi_ocdi_before_import() {
	if ( ! did_action( 'elementor/loaded' ) ) {
		wp_die(
			esc_html__( 'Elementor must be installed and active before importing the demo content. Go back to Plugins and activate Elementor first.', 'sdi-travel' ),
			esc_html__( 'Elementor required', 'sdi-travel' ),
			array( 'back_link' => true )
		);
	}
}

add_action( 'pt-ocdi/after_import', 'sdi_ocdi_after_import' );

/**
 * Finishes the setup steps a manual "Tools ▸ Import" would leave for a
 * human to do by hand: assign the 3 nav menu locations, set the static
 * front page and posts page, and flush permalinks.
 */
function sdi_ocdi_after_import() {
	$menu_locations = array();
	$slugs_by_location = array(
		'primary'             => 'primary-navigation',
		'footer-organization' => 'footer-organization',
		'footer-policies'     => 'footer-policies',
	);

	foreach ( $slugs_by_location as $location => $menu_slug ) {
		$menu = get_term_by( 'slug', $menu_slug, 'nav_menu' );
		if ( $menu && ! is_wp_error( $menu ) ) {
			$menu_locations[ $location ] = $menu->term_id;
		}
	}

	if ( ! empty( $menu_locations ) ) {
		set_theme_mod( 'nav_menu_locations', $menu_locations );
	}

	$front_page = get_page_by_path( 'home' );
	$posts_page = get_page_by_path( 'news-and-resources' );

	if ( $front_page instanceof WP_Post ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page->ID );
	}

	if ( $posts_page instanceof WP_Post ) {
		update_option( 'page_for_posts', $posts_page->ID );
	}

	flush_rewrite_rules();
}

add_filter( 'pt-ocdi/plugin_page_setup', 'sdi_ocdi_plugin_page_setup' );

/**
 * Renames the importer's admin page/menu label to match this theme.
 *
 * @param array $setup_data Default OCDI page setup.
 * @return array
 */
function sdi_ocdi_plugin_page_setup( $setup_data ) {
	$setup_data['parent_slug'] = 'themes.php';
	$setup_data['page_title']  = __( 'Import SDI Travel Trust Demo Data', 'sdi-travel' );
	$setup_data['menu_title']  = __( 'Import Demo Data', 'sdi-travel' );
	return $setup_data;
}

add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );
