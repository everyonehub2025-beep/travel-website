<?php
/**
 * Template Name: Sign Up
 *
 * The real member registration form. Not part of the design handoff bundle
 * (its Membership page "Join Now" buttons pointed back at themselves) —
 * built to match the same visual language, since the theme now creates
 * accounts natively instead of through MemberPress. See SDI_Auth for the
 * verification-then-activate flow this form kicks off.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_error_messages = array(
	'invalid'      => __( 'Something went wrong submitting the form — please try again.', 'sdi-travel' ),
	'fields'       => __( 'Please fill in every field, choose a membership tier, and agree to the terms.', 'sdi-travel' ),
	'password'     => __( 'Passwords must match and be at least 8 characters long.', 'sdi-travel' ),
	'exists'       => __( 'An account already exists for that email — try logging in instead.', 'sdi-travel' ),
	'rate_limited' => __( 'Too many sign-ups from this connection recently. Please try again in an hour, or contact us for help.', 'sdi-travel' ),
);

$sdi_error_code  = isset( $_GET['sdi_signup_error'] ) ? sanitize_key( wp_unslash( $_GET['sdi_signup_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
$sdi_error_note  = isset( $sdi_error_messages[ $sdi_error_code ] ) ? $sdi_error_messages[ $sdi_error_code ] : '';
$sdi_pending     = isset( $_GET['sdi_signup'] ) && 'pending' === sanitize_key( wp_unslash( $_GET['sdi_signup'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
$sdi_preselected = isset( $_GET['tier'] ) ? sanitize_key( wp_unslash( $_GET['tier'] ) ) : 'individual'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- pre-selects a radio button, not used for anything else.
$sdi_ref_code    = isset( $_GET['ref'] ) ? sanitize_text_field( wp_unslash( $_GET['ref'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- carried through to the signup handler, which re-validates it.

$sdi_tiers = class_exists( 'SDI_Auth' ) ? SDI_Auth::get_tiers() : array(
	'individual' => array( 'label' => __( 'Individual', 'sdi-travel' ), 'price' => '$180', 'unit' => __( '/ year', 'sdi-travel' ) ),
	'family'     => array( 'label' => __( 'Family', 'sdi-travel' ), 'price' => '$300', 'unit' => __( '/ year', 'sdi-travel' ) ),
);

if ( ! isset( $sdi_tiers[ $sdi_preselected ] ) ) {
	$sdi_preselected = 'individual';
}

get_header();
?>

<?php
sdi_page_header_band(
	array(
		'breadcrumb' => __( 'Sign Up', 'sdi-travel' ),
		'eyebrow'    => __( 'Join the trust', 'sdi-travel' ),
		'title'      => __( 'Create Your Account', 'sdi-travel' ),
		'subhead'    => __( 'Choose a membership tier and verify your email — your dashboard, referral link and the travel directory open as soon as it\'s confirmed.', 'sdi-travel' ),
	)
);
?>

<section class="sdi-section sdi-section--light" style="border-bottom: 1px solid var(--sdi-border); padding-block: 60px;">
	<div class="sdi-container sdi-auth-grid">

		<div class="sdi-auth-panel">
			<?php if ( $sdi_pending ) : ?>
				<p class="sdi-auth-kicker"><?php esc_html_e( 'Almost there', 'sdi-travel' ); ?></p>
				<h2><?php esc_html_e( 'Check your email', 'sdi-travel' ); ?></h2>
				<p style="margin-top: 16px; max-width: 52ch; color: var(--sdi-color-text-secondary);">
					<?php esc_html_e( 'We sent a verification link to the address you entered. Click it to activate your account — the link expires in 24 hours. If it doesn\'t arrive in a few minutes, check spam or contact the membership team.', 'sdi-travel' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="sdi-btn sdi-btn--outline" style="margin-top: 22px;"><?php esc_html_e( 'Contact the membership team', 'sdi-travel' ); ?></a>
			<?php else : ?>
				<p class="sdi-auth-kicker"><?php esc_html_e( 'New members', 'sdi-travel' ); ?></p>
				<h2><?php esc_html_e( 'Set up your account', 'sdi-travel' ); ?></h2>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-form" style="margin-top: 26px;">
					<input type="hidden" name="action" value="sdi_signup" />
					<?php wp_nonce_field( 'sdi_signup', 'sdi_signup_nonce' ); ?>
					<input type="text" name="sdi_signup_hp" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true" />
					<?php if ( $sdi_ref_code ) : ?>
						<input type="hidden" name="ref" value="<?php echo esc_attr( $sdi_ref_code ); ?>" />
					<?php endif; ?>

					<p class="sdi-form__field">
						<label for="sdi-signup-name"><?php esc_html_e( 'Full Name', 'sdi-travel' ); ?> *</label>
						<input type="text" id="sdi-signup-name" name="name" required="required" />
					</p>
					<p class="sdi-form__field">
						<label for="sdi-signup-email"><?php esc_html_e( 'Email Address', 'sdi-travel' ); ?> *</label>
						<input type="email" id="sdi-signup-email" name="email" required="required" placeholder="you@example.com" />
					</p>
					<p class="sdi-form__field">
						<label for="sdi-signup-password"><?php esc_html_e( 'Password', 'sdi-travel' ); ?> *</label>
						<input type="password" id="sdi-signup-password" name="password" minlength="8" required="required" placeholder="<?php esc_attr_e( 'At least 8 characters', 'sdi-travel' ); ?>" />
					</p>
					<p class="sdi-form__field">
						<label for="sdi-signup-password-confirm"><?php esc_html_e( 'Confirm Password', 'sdi-travel' ); ?> *</label>
						<input type="password" id="sdi-signup-password-confirm" name="password_confirm" minlength="8" required="required" />
					</p>

					<p class="sdi-form__field">
						<label><?php esc_html_e( 'Membership Tier', 'sdi-travel' ); ?> *</label>
						<span class="sdi-tier-pick" role="radiogroup" aria-label="<?php esc_attr_e( 'Membership tier', 'sdi-travel' ); ?>">
							<?php foreach ( $sdi_tiers as $sdi_key => $sdi_tier ) : ?>
								<label class="sdi-tier-pick__option">
									<input type="radio" name="tier" value="<?php echo esc_attr( $sdi_key ); ?>" <?php checked( $sdi_preselected, $sdi_key ); ?> required="required" />
									<span class="sdi-tier-pick__label"><?php echo esc_html( $sdi_tier['label'] ); ?></span>
									<span class="sdi-tier-pick__price"><?php echo esc_html( $sdi_tier['price'] ); ?> <span><?php echo esc_html( $sdi_tier['unit'] ); ?></span></span>
								</label>
							<?php endforeach; ?>
						</span>
					</p>

					<p class="sdi-form__field">
						<label class="sdi-auth-checkbox" style="font-weight: 400;">
							<input type="checkbox" name="terms" value="1" required="required" />
							<span>
								<?php
								printf(
									/* translators: 1: Membership Terms link, 2: Privacy Policy link */
									esc_html__( 'I agree to the %1$s and %2$s.', 'sdi-travel' ),
									'<a href="' . esc_url( home_url( '/legal/#membership-terms' ) ) . '" class="sdi-auth-link">' . esc_html__( 'Membership Terms', 'sdi-travel' ) . '</a>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_html().
									'<a href="' . esc_url( home_url( '/legal/#privacy' ) ) . '" class="sdi-auth-link">' . esc_html__( 'Privacy Policy', 'sdi-travel' ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_html().
								);
								?>
							</span>
						</label>
					</p>

					<?php if ( $sdi_error_note ) : ?>
						<p class="sdi-auth-error" style="margin-bottom: 1.25em;"><?php echo esc_html( $sdi_error_note ); ?></p>
					<?php endif; ?>

					<p class="sdi-form__submit" style="margin-top: 0;">
						<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Create Account', 'sdi-travel' ); ?></button>
					</p>
				</form>

				<p style="margin-top: 20px; font-size: 0.9rem; color: var(--sdi-color-text-secondary);">
					<?php
					printf(
						/* translators: %s: Log In link */
						esc_html__( 'Already a member? %s', 'sdi-travel' ),
						'<a href="' . esc_url( home_url( '/member-login/' ) ) . '" class="sdi-auth-link">' . esc_html__( 'Log in', 'sdi-travel' ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_html().
					);
					?>
				</p>
			<?php endif; ?>
		</div>

		<div style="min-width: 0; display: flex; flex-direction: column; gap: 16px;">
			<div class="sdi-auth-panel--dark">
				<p class="sdi-auth-kicker"><?php esc_html_e( 'What membership includes', 'sdi-travel' ); ?></p>
				<h2><?php esc_html_e( 'One account, the whole program', 'sdi-travel' ); ?></h2>
				<ul style="margin: 16px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px;">
					<li style="padding-left: 1.4em; position: relative;"><span style="position:absolute;left:0;color:var(--sdi-color-gold);">—</span> <?php esc_html_e( 'A referral link that earns points toward a scholarship-tier award', 'sdi-travel' ); ?></li>
					<li style="padding-left: 1.4em; position: relative;"><span style="position:absolute;left:0;color:var(--sdi-color-gold);">—</span> <?php esc_html_e( 'Full access to the Travel Directory\'s member-only listings', 'sdi-travel' ); ?></li>
					<li style="padding-left: 1.4em; position: relative;"><span style="position:absolute;left:0;color:var(--sdi-color-gold);">—</span> <?php esc_html_e( 'Partner offers and retail discounts as they\'re published', 'sdi-travel' ); ?></li>
					<li style="padding-left: 1.4em; position: relative;"><span style="position:absolute;left:0;color:var(--sdi-color-gold);">—</span> <?php esc_html_e( 'A dashboard tracking your points, tier progress and history', 'sdi-travel' ); ?></li>
				</ul>
				<a href="<?php echo esc_url( home_url( '/membership/' ) ); ?>" class="sdi-btn sdi-btn--outline" style="margin-top: 26px; border-color: var(--sdi-color-silver); color: var(--sdi-color-white);"><?php esc_html_e( 'Compare tiers in detail', 'sdi-travel' ); ?></a>
			</div>

			<div class="sdi-auth-sidecard">
				<p class="sdi-auth-eyebrow-label"><?php esc_html_e( 'Please note', 'sdi-travel' ); ?></p>
				<p style="margin: 12px 0 0; font-size: 0.9rem; color: var(--sdi-color-text-secondary);">
					<?php esc_html_e( 'A scholarship-tier referral program does not guarantee an award. Every award is subject to program rules, available funding, verification, and applicable law.', 'sdi-travel' ); ?>
				</p>
				<p style="margin: 12px 0 0; font-size: 0.9rem; color: var(--sdi-color-text-secondary);">
					<?php esc_html_e( 'Payment collection is not yet connected to this form — accounts activate on email verification, and membership dues are confirmed by the membership team.', 'sdi-travel' ); ?>
				</p>
			</div>
		</div>

	</div>
</section>

<?php get_footer(); ?>
