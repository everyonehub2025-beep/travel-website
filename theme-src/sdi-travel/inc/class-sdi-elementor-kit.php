<?php
/**
 * One-time sync of the SDI brand palette/typography into Elementor's
 * active Global Kit, so every Elementor widget on the site can pick
 * "Primary" / "Secondary" / etc. from Elementor's own color and
 * typography pickers instead of a hand-typed hex value or font name.
 *
 * This intentionally never overwrites the kit on every page load — it
 * runs once per theme version (tracked in `sdi_kit_synced_version`) so an
 * editor's own deliberate changes in Site Settings aren't fought with on
 * every request. Re-run manually any time via Appearance > Sync Brand Kit.
 *
 * IMPORTANT: this has not been exercised against a live Elementor install
 * (see SETUP.md). Elementor's kit settings schema is stable but
 * undocumented as a formal public API — spot-check Site Settings > Global
 * Colors/Fonts after first activation and adjust by hand if anything
 * didn't take.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the target kit settings array from the brand tokens.
 *
 * @return array<string,mixed>
 */
function sdi_get_brand_kit_settings() {
	return array(
		'system_colors'   => array(
			array(
				'_id'   => 'primary',
				'title' => __( 'Primary — Navy', 'sdi-travel' ),
				'color' => '#0A1D3B',
			),
			array(
				'_id'   => 'secondary',
				'title' => __( 'Secondary — Midnight Navy', 'sdi-travel' ),
				'color' => '#071527',
			),
			array(
				'_id'   => 'text',
				'title' => __( 'Text', 'sdi-travel' ),
				'color' => '#0A1D3B',
			),
			array(
				'_id'   => 'accent',
				'title' => __( 'Accent — Warm Gold (CTAs only)', 'sdi-travel' ),
				'color' => '#C89B3C',
			),
		),
		'custom_colors'   => array(
			array(
				'_id'          => 'sdi_silver',
				'title'        => __( 'Cool Silver', 'sdi-travel' ),
				'color'        => '#97999D',
			),
			array(
				'_id'          => 'sdi_platinum',
				'title'        => __( 'Platinum', 'sdi-travel' ),
				'color'        => '#BFBFC5',
			),
			array(
				'_id'          => 'sdi_offwhite',
				'title'        => __( 'Off-White', 'sdi-travel' ),
				'color'        => '#F7F8FA',
			),
			array(
				'_id'          => 'sdi_white',
				'title'        => __( 'White', 'sdi-travel' ),
				'color'        => '#FFFFFF',
			),
			array(
				'_id'          => 'sdi_gold_deep',
				'title'        => __( 'Deep Gold (text on light)', 'sdi-travel' ),
				'color'        => '#8a6a1f',
			),
			array(
				'_id'          => 'sdi_text_secondary',
				'title'        => __( 'Text Secondary', 'sdi-travel' ),
				'color'        => '#47546c',
			),
			array(
				'_id'          => 'sdi_border',
				'title'        => __( 'Border', 'sdi-travel' ),
				'color'        => '#DCDEE3',
			),
			array(
				'_id'          => 'sdi_rust',
				'title'        => __( 'Rust Red (errors only)', 'sdi-travel' ),
				'color'        => '#9A3324',
			),
		),
		'system_typography' => array(
			array(
				'_id'                          => 'primary',
				'title'                        => __( 'Primary — Headings', 'sdi-travel' ),
				'typography_typography'        => 'custom',
				'typography_font_family'       => 'Bodoni Moda',
				'typography_font_weight'       => '600',
			),
			array(
				'_id'                          => 'secondary',
				'title'                        => __( 'Secondary — Body', 'sdi-travel' ),
				'typography_typography'        => 'custom',
				'typography_font_family'       => 'Manrope',
				'typography_font_weight'       => '400',
			),
			array(
				'_id'                          => 'text',
				'title'                        => __( 'Text', 'sdi-travel' ),
				'typography_typography'        => 'custom',
				'typography_font_family'       => 'Manrope',
				'typography_font_weight'       => '400',
			),
			array(
				'_id'                          => 'accent',
				'title'                        => __( 'Accent — Pull-quote Italic', 'sdi-travel' ),
				'typography_typography'        => 'custom',
				'typography_font_family'       => 'Bodoni Moda',
				'typography_font_style'        => 'italic',
				'typography_font_weight'       => '500',
			),
		),
	);
}

/**
 * Push sdi_get_brand_kit_settings() into Elementor's active Kit document.
 *
 * @return bool True on (attempted) success, false if Elementor isn't ready.
 */
function sdi_sync_elementor_kit() {
	if ( ! did_action( 'elementor/loaded' ) && ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}

	if ( ! class_exists( '\Elementor\Plugin' ) || empty( \Elementor\Plugin::$instance ) ) {
		return false;
	}

	$elementor = \Elementor\Plugin::$instance;

	if ( empty( $elementor->kits_manager ) || ! method_exists( $elementor->kits_manager, 'get_active_id' ) ) {
		return false;
	}

	$kit_id = $elementor->kits_manager->get_active_id();

	if ( ! $kit_id ) {
		return false;
	}

	$document = $elementor->documents->get( $kit_id );

	if ( ! $document || ! method_exists( $document, 'update_settings' ) ) {
		return false;
	}

	$document->update_settings( sdi_get_brand_kit_settings() );

	return true;
}

/**
 * Run the sync once per theme version.
 */
function sdi_maybe_sync_elementor_kit() {
	if ( get_option( 'sdi_kit_synced_version' ) === SDI_THEME_VERSION ) {
		return;
	}

	if ( sdi_sync_elementor_kit() ) {
		update_option( 'sdi_kit_synced_version', SDI_THEME_VERSION );
	}
}
add_action( 'admin_init', 'sdi_maybe_sync_elementor_kit' );

/**
 * Manual re-sync entry point: Appearance > Sync Brand Kit.
 */
function sdi_register_kit_sync_page() {
	add_theme_page(
		__( 'Sync Brand Kit', 'sdi-travel' ),
		__( 'Sync Brand Kit', 'sdi-travel' ),
		'edit_theme_options',
		'sdi-sync-brand-kit',
		'sdi_render_kit_sync_page'
	);
}
add_action( 'admin_menu', 'sdi_register_kit_sync_page' );

/**
 * Render the manual re-sync screen.
 */
function sdi_render_kit_sync_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'sdi-travel' ) );
	}

	$synced = null;

	if ( isset( $_POST['sdi_sync_kit_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_sync_kit_nonce'] ) ), 'sdi_sync_kit' ) ) {
		$synced = sdi_sync_elementor_kit();
		if ( $synced ) {
			update_option( 'sdi_kit_synced_version', SDI_THEME_VERSION );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Sync Brand Kit', 'sdi-travel' ); ?></h1>
		<p><?php esc_html_e( 'Pushes the SDI brand colors and fonts into Elementor\'s Global Kit (Site Settings > Global Colors / Global Fonts). Runs automatically once after theme activation — use this only if you need to force it again, for example after resetting the Elementor kit.', 'sdi-travel' ); ?></p>

		<?php if ( null !== $synced ) : ?>
			<?php if ( $synced ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Brand kit synced.', 'sdi-travel' ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Could not sync — is Elementor installed and active?', 'sdi-travel' ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'sdi_sync_kit', 'sdi_sync_kit_nonce' ); ?>
			<?php submit_button( __( 'Sync Now', 'sdi-travel' ) ); ?>
		</form>
	</div>
	<?php
}
