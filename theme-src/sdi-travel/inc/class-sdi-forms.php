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
 * The Contact page's topic routing: each key maps to a department mailbox
 * (set in Settings, or a sensible fallback) and a short note shown once a
 * visitor picks that topic — matches the design's team-routing pattern.
 *
 * @return array<string,array<string,string>>
 */
function sdi_contact_topics() {
	return array(
		'membership'   => array(
			'label'      => __( 'Membership', 'sdi-travel' ),
			'team'       => __( 'Membership team', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email_membership',
			'email_default' => '[PLACEHOLDER: membership team email]',
			'note'       => __( 'Dues, renewals, cancellations, upgrades and account access. Include your account email and we can answer without a second exchange.', 'sdi-travel' ),
		),
		'scholarships' => array(
			'label'      => __( 'Scholarships', 'sdi-travel' ),
			'team'       => __( 'Scholarship committee', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email_scholarships',
			'email_default' => '[PLACEHOLDER: scholarships team email]',
			'note'       => __( 'Eligibility, documents, cycle dates and award notifications. Never attach transcripts here — we request documents once eligibility is confirmed.', 'sdi-travel' ),
		),
		'directory'    => array(
			'label'      => __( 'Directory', 'sdi-travel' ),
			'team'       => __( 'Programme team', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email',
			'email_default' => '[PLACEHOLDER: contact email]',
			'note'       => __( 'Corrections to a listing, a broken link, or an organization you want reviewed for inclusion.', 'sdi-travel' ),
		),
		'partnerships' => array(
			'label'      => __( 'Partnerships', 'sdi-travel' ),
			'team'       => __( 'Partnerships lead', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email_partnerships',
			'email_default' => '[PLACEHOLDER: partnerships team email]',
			'note'       => __( 'Listings, retail offers, award sponsorships and employer placements. The Partnerships page has the full review checklist.', 'sdi-travel' ),
		),
		'giving'       => array(
			'label'      => __( 'Giving', 'sdi-travel' ),
			'team'       => __( 'Giving team', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email_giving',
			'email_default' => '[PLACEHOLDER: giving team email]',
			'note'       => __( 'Donations, tribute gifts, acknowledgement letters and donor-advised fund distributions.', 'sdi-travel' ),
		),
		'press'        => array(
			'label'      => __( 'Press', 'sdi-travel' ),
			'team'       => __( 'Executive office', 'sdi-travel' ),
			'email_mod'  => 'sdi_contact_email',
			'email_default' => '[PLACEHOLDER: contact email]',
			'note'       => __( 'Interview requests, financial questions and anything governance-related.', 'sdi-travel' ),
		),
	);
}

/**
 * Topic select options built from sdi_contact_topics().
 *
 * @return array<string,string>
 */
function sdi_contact_topics_options() {
	$options = array();
	foreach ( sdi_contact_topics() as $key => $topic ) {
		$options[ $key ] = $topic['label'];
	}
	return $options;
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
					'options' => sdi_contact_topics_options(),
				),
				'message' => array( 'type' => 'textarea', 'label' => __( 'Message', 'sdi-travel' ), 'required' => true ),
			),
		),
		'partnership'              => array(
			'label'  => __( 'Partnership Inquiry', 'sdi-travel' ),
			'fields' => array(
				'organization' => array( 'type' => 'text', 'label' => __( 'Organization', 'sdi-travel' ), 'required' => true ),
				'partner_type' => array(
					'type'    => 'select',
					'label'   => __( 'Partnership Type', 'sdi-travel' ),
					'options' => array(
						'directory_listing' => __( 'Directory listing', 'sdi-travel' ),
						'retail_offer'      => __( 'Retail & restaurant offer', 'sdi-travel' ),
						'award_sponsorship' => __( 'Award sponsorship', 'sdi-travel' ),
						'employer'          => __( 'Employer & institutional', 'sdi-travel' ),
						'unsure'            => __( 'Not sure yet', 'sdi-travel' ),
					),
				),
				'name'         => array( 'type' => 'text', 'label' => __( 'Contact Name', 'sdi-travel' ), 'required' => true ),
				'email'        => array( 'type' => 'email', 'label' => __( 'Work Email', 'sdi-travel' ), 'required' => true ),
				'website'      => array( 'type' => 'url', 'label' => __( 'Website', 'sdi-travel' ), 'required' => false ),
				'category'     => array(
					'type'    => 'select',
					'label'   => __( 'Category', 'sdi-travel' ),
					'options' => class_exists( 'SDI_Directory' ) ? SDI_Directory::get_categories() : array(),
				),
				'message'      => array( 'type' => 'textarea', 'label' => __( 'What would you offer members?', 'sdi-travel' ), 'required' => true ),
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
				'name'          => array( 'type' => 'text', 'label' => __( 'Student Full Name', 'sdi-travel' ), 'required' => true ),
				'email'         => array( 'type' => 'email', 'label' => __( 'Email Address', 'sdi-travel' ), 'required' => true ),
				'institution'   => array( 'type' => 'text', 'label' => __( 'Institution', 'sdi-travel' ), 'required' => true ),
				'award_level'   => array(
					'type'    => 'select',
					'label'   => __( 'Award Level Applying For', 'sdi-travel' ),
					'options' => array(
						'300'  => __( '300 points — up to $3,000', 'sdi-travel' ),
						'600'  => __( '600 points — up to $6,000', 'sdi-travel' ),
						'900'  => __( '900 points — up to $9,000', 'sdi-travel' ),
						'1200' => __( '1,200 points — up to $12,000', 'sdi-travel' ),
						'unsure' => __( 'Not sure yet', 'sdi-travel' ),
					),
				),
				'sponsor_name'  => array( 'type' => 'text', 'label' => __( 'Sponsoring Member Name', 'sdi-travel' ), 'required' => false ),
				'sponsor_email' => array( 'type' => 'email', 'label' => __( 'Sponsoring Member Email', 'sdi-travel' ), 'required' => false ),
				'message'       => array( 'type' => 'textarea', 'label' => __( 'Brief Statement (optional now, required with a full application)', 'sdi-travel' ), 'required' => false ),
			),
		),
		'donation_interest'        => array(
			'label'  => __( 'Donation Interest', 'sdi-travel' ),
			'fields' => array(
				'name'        => array( 'type' => 'text', 'label' => __( 'Full Name', 'sdi-travel' ), 'required' => true ),
				'email'       => array( 'type' => 'email', 'label' => __( 'Email Address', 'sdi-travel' ), 'required' => true ),
				'amount'      => array( 'type' => 'text', 'label' => __( 'Amount (USD)', 'sdi-travel' ), 'required' => true ),
				'frequency'   => array(
					'type'    => 'select',
					'label'   => __( 'Frequency', 'sdi-travel' ),
					'options' => array(
						'one-time' => __( 'One-time gift', 'sdi-travel' ),
						'monthly'  => __( 'Monthly', 'sdi-travel' ),
						'annual'   => __( 'Annually', 'sdi-travel' ),
					),
				),
				'designation' => array(
					'type'    => 'select',
					'label'   => __( 'Designation', 'sdi-travel' ),
					'options' => array(
						'unrestricted' => __( 'Scholarship fund — unrestricted', 'sdi-travel' ),
						'named_award'  => __( 'Scholarship fund — named award level', 'sdi-travel' ),
						'most_needed'  => __( 'Where it is needed most', 'sdi-travel' ),
					),
				),
				'tribute'     => array( 'type' => 'text', 'label' => __( 'Tribute (optional — "In honor of…")', 'sdi-travel' ), 'required' => false ),
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

	$default_recipient = get_theme_mod( 'sdi_contact_email', get_option( 'admin_email' ) );

	if ( 'contact' === $type && ! empty( $data['topic'] ) ) {
		$topics = sdi_contact_topics();
		if ( isset( $topics[ $data['topic'] ] ) ) {
			$topic             = $topics[ $data['topic'] ];
			$default_recipient = get_theme_mod( $topic['email_mod'], $topic['email_default'] );
		}
	} elseif ( 'donation_interest' === $type ) {
		$default_recipient = get_theme_mod( 'sdi_contact_email_giving', '[PLACEHOLDER: giving team email]' );
	} elseif ( 'scholarship_application' === $type ) {
		$default_recipient = get_theme_mod( 'sdi_contact_email_scholarships', '[PLACEHOLDER: scholarships team email]' );
	} elseif ( 'partnership' === $type ) {
		$default_recipient = get_theme_mod( 'sdi_contact_email_partnerships', '[PLACEHOLDER: partnerships team email]' );
	}

	/**
	 * Filters the recipient email for a given inquiry form type.
	 *
	 * @param string               $recipient Default recipient (topic-routed for 'contact').
	 * @param string               $type      Form type key.
	 * @param array<string,string> $data      Sanitized submitted field data.
	 */
	$recipient = apply_filters( 'sdi_inquiry_recipient', $default_recipient, $type, $data );
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
