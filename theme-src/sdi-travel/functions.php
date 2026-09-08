<?php
/**
 * SDI Travel theme bootstrap.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SDI_THEME_VERSION', '1.1.0' );
define( 'SDI_THEME_DIR', get_template_directory() );
define( 'SDI_THEME_URI', get_template_directory_uri() );

require_once SDI_THEME_DIR . '/inc/setup.php';
require_once SDI_THEME_DIR . '/inc/plugin-activation.php';
require_once SDI_THEME_DIR . '/inc/demo-import.php';
require_once SDI_THEME_DIR . '/inc/enqueue.php';
require_once SDI_THEME_DIR . '/inc/customizer.php';
require_once SDI_THEME_DIR . '/inc/newsletter.php';
require_once SDI_THEME_DIR . '/inc/class-sdi-forms.php';
require_once SDI_THEME_DIR . '/inc/login-branding.php';
require_once SDI_THEME_DIR . '/inc/class-sdi-elementor-kit.php';
require_once SDI_THEME_DIR . '/inc/template-tags.php';
