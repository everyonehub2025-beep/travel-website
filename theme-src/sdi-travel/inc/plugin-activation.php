<?php
/**
 * Required/recommended plugins — TGM Plugin Activation.
 *
 * Shows the familiar "This theme requires the following plugins" admin
 * notice on activation, with one-click bulk install/activate, the same
 * way most premium themes (Astra, Avada, OceanWP, ...) do it.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SDI_THEME_DIR . '/inc/lib/tgmpa/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'sdi_register_required_plugins' );

/**
 * Registers Elementor (page builder — required) and One Click Demo Import
 * (used for the guided "Import Demo Data" step right after activation).
 */
function sdi_register_required_plugins() {
	$plugins = array(
		array(
			'name'     => 'Elementor',
			'slug'     => 'elementor',
			'required' => true,
		),
		array(
			'name'     => 'One Click Demo Import',
			'slug'     => 'one-click-demo-import',
			'required' => true,
		),
	);

	$config = array(
		'id'           => 'sdi-travel',
		'default_path' => '',
		'menu'         => 'sdi-install-required-plugins',
		'parent_slug'  => 'themes.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => false,
		'is_automatic' => false,
		'strings'      => array(
			'notice_can_install_required' => _n_noop(
				'SDI Travel Trust requires the following plugin: %1$s. Installing and activating it unlocks the guided "Import Demo Data" step, which sets up every page, the header, footer, and menus automatically.',
				'SDI Travel Trust requires the following plugins: %1$s. Installing and activating them unlocks the guided "Import Demo Data" step, which sets up every page, the header, footer, and menus automatically.',
				'sdi-travel'
			),
		),
	);

	tgmpa( $plugins, $config );
}
