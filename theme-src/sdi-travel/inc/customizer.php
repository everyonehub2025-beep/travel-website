<?php
/**
 * Customizer: editable footer/org info that isn't page content.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the "SDI Site Info" panel used by the footer template.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function sdi_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'sdi_site_info',
		array(
			'title'    => __( 'SDI Site Info', 'sdi-travel' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'sdi_footer_blurb'   => array(
			'default'     => __( 'SDI Travel Trust connects individuals and families with curated travel resources while generating support for academic scholarships and community programs.', 'sdi-travel' ),
			'label'       => __( 'Footer organization blurb', 'sdi-travel' ),
			'type'        => 'textarea',
			'sanitize_cb' => 'sanitize_textarea_field',
		),
		'sdi_contact_email'  => array(
			'default'     => '[PLACEHOLDER: contact email]',
			'label'       => __( 'Contact email', 'sdi-travel' ),
			'type'        => 'text',
			'sanitize_cb' => 'sanitize_text_field',
		),
		'sdi_contact_phone'  => array(
			'default'     => '[PLACEHOLDER: contact phone]',
			'label'       => __( 'Contact phone', 'sdi-travel' ),
			'type'        => 'text',
			'sanitize_cb' => 'sanitize_text_field',
		),
		'sdi_mailing_address' => array(
			'default'     => '[PLACEHOLDER: mailing address]',
			'label'       => __( 'Mailing address', 'sdi-travel' ),
			'type'        => 'text',
			'sanitize_cb' => 'sanitize_text_field',
		),
		'sdi_social_facebook' => array(
			'default'     => '',
			'label'       => __( 'Facebook URL', 'sdi-travel' ),
			'type'        => 'url',
			'sanitize_cb' => 'esc_url_raw',
		),
		'sdi_social_instagram' => array(
			'default'     => '',
			'label'       => __( 'Instagram URL', 'sdi-travel' ),
			'type'        => 'url',
			'sanitize_cb' => 'esc_url_raw',
		),
		'sdi_social_linkedin' => array(
			'default'     => '',
			'label'       => __( 'LinkedIn URL', 'sdi-travel' ),
			'type'        => 'url',
			'sanitize_cb' => 'esc_url_raw',
		),
		'sdi_social_x'       => array(
			'default'     => '',
			'label'       => __( 'X (Twitter) URL', 'sdi-travel' ),
			'type'        => 'url',
			'sanitize_cb' => 'esc_url_raw',
		),
	);

	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => $field['sanitize_cb'],
			)
		);

		$wp_customize->add_control(
			$id,
			array(
				'section' => 'sdi_site_info',
				'label'   => $field['label'],
				'type'    => $field['type'],
			)
		);
	}
}
add_action( 'customize_register', 'sdi_customize_register' );
