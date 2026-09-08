<?php
/**
 * Lightweight, dependency-free inquiry forms.
 *
 * The base theme package recommends Contact Form 7 for this, but every
 * form the site needs (general contact, partnership inquiry, directory
 * submission, scholarship interest) is a handful of fields routed to an
 * email address — building them natively means one fewer plugin to
 * license and keep updated, and reuses the exact same secure
 * nonce+honeypot pattern already used for the newsletter and referral
 * forms. See SETUP.md for the tradeoff if the client later needs
 * conditional logic, file uploads, or payment fields a dedicated forms
 * plugin would be worth adding for.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Field definitions per form type. Keep in sync with sdi_handle_inquiry_submit().
 *
 * @return array<string,array<string,mixed>>
 */
function sdi_inquiry_form_types() {
	return array(
		'contact'                 => array(
			'label'  => __( 'Contact', 'sdi-travel' ),
			'fields' => array(
				'name'    => array( 'type' => 'text', 'label' => __( 'Full Name', 'sdi-travel' ), 'required' => true ),
				'email'   => array( 'type' => 'email', 'label' => __( 'Email Address', 'sdi-travel' ), 'required' => true ),
				'topic'   => array(
					'type'    => 'select',
					'label'   => __( 'Topic', 'sdi-travel' ),
					'options' => array(
						'general'      => __( 'General Inquiry', 'sdi-travel' ),
						'membership'   => __( 'Membership Support', 'sdi-travel' ),
						'partnership'  => __( 'Partnership Inquiry', 'sdi-travel' ),
						'scholarship'  => __( 'Scholarship Inquiry', 'sdi-travel' ),
					),
				),
				'message' => array( 'type' => 'textarea', 'label' => __( 'Message', 'sdi-travel' ), 'required' => true ),
			),
		),
		'partnership'              => array(
			'label'  => __( 'Partnership Inquiry', 'sdi-travel' ),
			'fields' => array(
				'organization' => array( 'type' => 'text', 'label' => __( 'Organization Name', 'sdi-travel' ), 'required' => true ),
				'name'         => array( 'type' => 'text', 'label' => __( 'Contact Name', 'sdi-travel' ), 'required' => true ),
				'email'        => array( 'type' => 'email', 'label' => __( 'Email Address', 'sdi-travel' ), 'required' => true ),
				'phone'        => array( 'type' => 'tel', 'label' => __( 'Phone', 'sdi-travel' ), 'required' => false ),
				'partner_type' => array(
					'type'    => 'select',
					'label'   => __( 'Partnership Type', 'sdi-travel' ),
					'options' => array(
						'travel_industry' => __( 'Travel Industry Company', 'sdi-travel' ),
						'retailer'        => __( 'Retailer or Restaurant', 'sdi-travel' ),
						'fintech'         => __( 'Financial Technology Provider', 'sdi-travel' ),
						'government'      => __( 'Government Agency', 'sdi-travel' ),
						'corporate'       => __( 'Corporate Sponsor', 'sdi-travel' ),
						'foundation'      => __( 'Foundation or Grantmaker', 'sdi-travel' ),
					),
				),
				'message'      => array( 'type' => 'textarea', 'label' => __( 'Tell us about the opportunity', 'sdi-travel' ), 'required' => true ),
			),
		),
		'directory_submission'     => array(
			'label'  => __( 'Directory Submission', 'sdi-travel' ),
			'fields' => array(
				'organization' => array( 'type' => 'text', 'label' => __( 'Organization / Business Name', 'sdi-travel' ), 'required' => true ),
				'category'     => array(
					'type'    => 'select',
					'label'   => __( 'Category', 'sdi-travel' ),
					'options' => class_exists( 'SDI_Directory' ) ? SDI_Directory::get_categories() : array(),
				),
				'website'      => array( 'type' => 'url', 'label' => __( 'Website', 'sdi-travel' ), 'required' => false ),
				'email'        => array( 'type' => 'email', 'label' => __( 'Contact Email', 'sdi-travel' ), 'required' => true ),
				'message'      => array( 'type' => 'textarea', 'label' => __( 'Description', 'sdi-travel' ), 'required' => true ),
			),
		),
		'scholarship_application' => array(
			'label'  => __( 'Scholarship Interest', 'sdi-travel' ),
			'fields' => array(
				'name'    => array( 'type' => 'text', 'label' => __( 'Full Name', 'sdi-travel' ), 'required' => true ),
				'email'   => array( 'type' => 'email', 'label' => __( 'Email Address', 'sdi-travel' ), 'required' => true ),
				'phone'   => array( 'type' => 'tel', 'label' => __( 'Phone', 'sdi-travel' ), 'required' => false ),
				'message' => array( 'type' => 'textarea', 'label' => __( 'Tell us about your interest in a travel scholarship', 'sdi-travel' ), 'required' => true ),
			),
		),
		'footer_note'              => array(
			'label'  => __( 'Footer Note', 'sdi-travel' ),
			'fields' => array(
				'email'   => array( 'type' => 'email', 'label' => __( 'Your Email', 'sdi-travel' ), 'required' => true ),
				'message' => array( 'type' => 'textarea', 'label' => __( 'How can we help?', 'sdi-travel' ), 'required' => true ),
			),
		),
	);
}

/**
 * [sdi_inquiry_form type="contact|partnership|directory_submission|scholarship_application"]
 *
 * @param array<string,mixed> $atts Shortcode attributes.
 * @return string
 */
function sdi_inquiry_form_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'type' => 'contact' ), $atts, 'sdi_inquiry_form' );
	$types = sdi_inquiry_form_types();
	$type  = isset( $types[ $atts['type'] ] ) ? $atts['type'] : 'contact';
	$form  = $types[ $type ];

	$notice = null;
	if ( isset( $_GET['sdi_inquiry'] ) && isset( $_GET['sdi_inquiry_type'] ) && sanitize_key( wp_unslash( $_GET['sdi_inquiry_type'] ) ) === $type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
		$result = sanitize_key( wp_unslash( $_GET['sdi_inquiry'] ) );
		$notice = array(
			'type'    => ( 'success' === $result ) ? 'success' : 'error',
			'message' => ( 'success' === $result )
				? __( 'Thank you — your message has been sent. We will be in touch soon.', 'sdi-travel' )
				: __( 'Please fill in all required fields with valid information and try again.', 'sdi-travel' ),
		);
	}

	ob_start();
	?>
	<div class="sdi-form-wrap">
		<?php if ( $notice ) : ?>
			<div class="sdi-notice sdi-notice--<?php echo esc_attr( $notice['type'] ); ?>">
				<p><?php echo esc_html( $notice['message'] ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-form">
			<input type="hidden" name="action" value="sdi_inquiry_submit" />
			<input type="hidden" name="sdi_inquiry_type" value="<?php echo esc_attr( $type ); ?>" />
			<?php wp_nonce_field( 'sdi_inquiry_submit_' . $type, 'sdi_inquiry_nonce' ); ?>
			<input type="text" name="sdi_inquiry_hp" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true" />

			<?php foreach ( $form['fields'] as $key => $field ) : ?>
				<p class="sdi-form__field">
					<label for="sdi-<?php echo esc_attr( $type . '-' . $key ); ?>">
						<?php echo esc_html( $field['label'] ); ?><?php echo ! empty( $field['required'] ) ? ' *' : ''; ?>
					</label>
					<?php if ( 'textarea' === $field['type'] ) : ?>
						<textarea id="sdi-<?php echo esc_attr( $type . '-' . $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="5" <?php echo ! empty( $field['required'] ) ? 'required="required"' : ''; ?>></textarea>
					<?php elseif ( 'select' === $field['type'] ) : ?>
						<select id="sdi-<?php echo esc_attr( $type . '-' . $key ); ?>" name="<?php echo esc_attr( $key ); ?>" <?php echo ! empty( $field['required'] ) ? 'required="required"' : ''; ?>>
							<option value=""><?php esc_html_e( '— Select —', 'sdi-travel' ); ?></option>
							<?php foreach ( $field['options'] as $value => $option_label ) : ?>
								<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $option_label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="<?php echo esc_attr( $field['type'] ); ?>" id="sdi-<?php echo esc_attr( $type . '-' . $key ); ?>" name="<?php echo esc_attr( $key ); ?>" <?php echo ! empty( $field['required'] ) ? 'required="required"' : ''; ?> />
					<?php endif; ?>
				</p>
			<?php endforeach; ?>

			<p class="sdi-form__submit">
				<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Submit', 'sdi-travel' ); ?></button>
			</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'sdi_inquiry_form', 'sdi_inquiry_form_shortcode' );

/**
 * Handle every inquiry form's POST. Routes to a recipient by type; every
 * recipient is filterable so the client can point each form at a
 * different mailbox without code changes.
 */
function sdi_handle_inquiry_submit() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$types    = sdi_inquiry_form_types();
	$type     = isset( $_POST['sdi_inquiry_type'] ) ? sanitize_key( wp_unslash( $_POST['sdi_inquiry_type'] ) ) : '';

	if ( ! isset( $types[ $type ] ) ) {
		wp_safe_redirect( add_query_arg( array( 'sdi_inquiry' => 'error' ), $redirect ) );
		exit;
	}

	$fail_redirect = add_query_arg( array( 'sdi_inquiry' => 'error', 'sdi_inquiry_type' => $type ), $redirect );

	$nonce_ok = isset( $_POST['sdi_inquiry_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_inquiry_nonce'] ) ), 'sdi_inquiry_submit_' . $type );
	$honeypot_empty = empty( $_POST['sdi_inquiry_hp'] );

	if ( ! $nonce_ok || ! $honeypot_empty ) {
		wp_safe_redirect( $fail_redirect );
		exit;
	}

	$fields = $types[ $type ]['fields'];
	$data   = array();

	foreach ( $fields as $key => $field ) {
		$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- unslashed above, sanitized per-field type below.

		switch ( $field['type'] ) {
			case 'email':
				$value = sanitize_email( $raw );
				break;
			case 'url':
				$value = esc_url_raw( $raw );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}

		if ( ! empty( $field['required'] ) && '' === trim( (string) $value ) ) {
			wp_safe_redirect( $fail_redirect );
			exit;
		}

		if ( 'email' === $field['type'] && '' !== $value && ! is_email( $value ) ) {
			wp_safe_redirect( $fail_redirect );
			exit;
		}

		$data[ $key ] = $value;
	}

	/**
	 * Filters the recipient email for a given inquiry form type.
	 *
	 * @param string $recipient Default admin email.
	 * @param string $type      Form type key.
	 */
	$recipient = apply_filters( 'sdi_inquiry_recipient', get_theme_mod( 'sdi_contact_email', get_option( 'admin_email' ) ), $type );
	if ( ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$subject = sprintf(
		/* translators: %s: form type label */
		__( 'New %s submission — SDI Travel Trust', 'sdi-travel' ),
		$types[ $type ]['label']
	);

	$body_lines = array();
	foreach ( $fields as $key => $field ) {
		$body_lines[] = $field['label'] . ': ' . $data[ $key ];
	}

	wp_mail( $recipient, $subject, implode( "\n", $body_lines ) );

	/**
	 * Fires after an inquiry form is successfully submitted and emailed.
	 *
	 * @param string               $type Form type key.
	 * @param array<string,string> $data Sanitized field data.
	 */
	do_action( 'sdi_inquiry_submitted', $type, $data );

	wp_safe_redirect( add_query_arg( array( 'sdi_inquiry' => 'success', 'sdi_inquiry_type' => $type ), $redirect ) );
	exit;
}
add_action( 'admin_post_sdi_inquiry_submit', 'sdi_handle_inquiry_submit' );
add_action( 'admin_post_nopriv_sdi_inquiry_submit', 'sdi_handle_inquiry_submit' );
