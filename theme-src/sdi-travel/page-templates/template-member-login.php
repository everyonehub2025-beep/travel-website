<?php
/**
 * Template Name: Member Login
 *
 * Real sign-in: posts to admin-post.php (SDI_Auth::handle_login), a
 * WordPress user with the "New Sign Up" wp_signon() call underneath. Not a
 * prototype — the "member@example.com, any password" placeholder from the
 * design handoff has been replaced with an actual authentication system.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_error_messages = array(
	'invalid'      => __( 'Something went wrong submitting the form — please try again.', 'sdi-travel' ),
	'credentials'  => __( 'That email or password is incorrect. Please try again.', 'sdi-travel' ),
	'unverified'   => __( 'Please verify your email before signing in — check your inbox for the verification link we sent when you joined.', 'sdi-travel' ),
	'verify'       => __( 'That verification link is invalid or has expired. Please sign up again or contact the membership team.', 'sdi-travel' ),
	'rate_limited' => __( 'Too many attempts. Please wait a few minutes and try again.', 'sdi-travel' ),
);

$sdi_error_code = isset( $_GET['sdi_login_error'] ) ? sanitize_key( wp_unslash( $_GET['sdi_login_error'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
$sdi_error_note = isset( $sdi_error_messages[ $sdi_error_code ] ) ? $sdi_error_messages[ $sdi_error_code ] : '';

$sdi_tiers = class_exists( 'SDI_Auth' ) ? SDI_Auth::get_tiers() : array(
	'individual' => array( 'label' => __( 'Individual', 'sdi-travel' ), 'price' => '$180', 'unit' => __( '/ year', 'sdi-travel' ) ),
	'family'     => array( 'label' => __( 'Family', 'sdi-travel' ), 'price' => '$300', 'unit' => __( '/ year', 'sdi-travel' ) ),
);

get_header();
?>

<?php
sdi_page_header_band(
	array(
		'breadcrumb' => __( 'Member Login', 'sdi-travel' ),
		'eyebrow'    => __( 'Welcome back', 'sdi-travel' ),
		'title'      => __( 'Member Login', 'sdi-travel' ),
		'subhead'    => __( 'Your referral link, points balance, partner offers, and account settings live behind this form.', 'sdi-travel' ),
	)
);
?>

<section class="sdi-section sdi-section--light" style="border-bottom: 1px solid var(--sdi-border); padding-block: 60px;">
	<div class="sdi-container sdi-auth-grid">

		<div class="sdi-auth-panel">
			<p class="sdi-auth-kicker"><?php esc_html_e( 'Existing members', 'sdi-travel' ); ?></p>
			<h2><?php esc_html_e( 'Sign in to the portal', 'sdi-travel' ); ?></h2>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sdi-form" style="margin-top: 26px;">
				<input type="hidden" name="action" value="sdi_member_login" />
				<?php wp_nonce_field( 'sdi_member_login', 'sdi_login_nonce' ); ?>
				<input type="text" name="sdi_login_hp" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;" aria-hidden="true" />

				<p class="sdi-form__field">
					<label for="sdi-login-email"><?php esc_html_e( 'Account email', 'sdi-travel' ); ?></label>
					<input type="email" id="sdi-login-email" name="email" required="required" placeholder="you@example.com" />
				</p>
				<p class="sdi-form__field">
					<label for="sdi-login-password"><?php esc_html_e( 'Password', 'sdi-travel' ); ?></label>
					<input type="password" id="sdi-login-password" name="password" required="required" placeholder="••••••••" />
				</p>

				<div class="sdi-auth-row" style="margin-bottom: 1.25em;">
					<label class="sdi-auth-checkbox">
						<input type="checkbox" name="remember" value="1" checked="checked" />
						<?php esc_html_e( 'Keep me signed in', 'sdi-travel' ); ?>
					</label>
					<a class="sdi-auth-link" href="<?php echo esc_url( wp_lostpassword_url( home_url( '/member-login/' ) ) ); ?>"><?php esc_html_e( 'Forgot your password?', 'sdi-travel' ); ?></a>
				</div>

				<?php if ( $sdi_error_note ) : ?>
					<p class="sdi-auth-error" style="margin-bottom: 1.25em;"><?php echo esc_html( $sdi_error_note ); ?></p>
				<?php endif; ?>

				<p class="sdi-form__submit" style="margin-top: 0;">
					<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Log In', 'sdi-travel' ); ?></button>
				</p>
			</form>

			<div style="margin-top: 26px; padding-top: 22px; border-top: 1px solid var(--sdi-border-light);">
				<p class="sdi-auth-eyebrow-label"><?php esc_html_e( 'New here?', 'sdi-travel' ); ?></p>
				<p style="margin: 12px 0 0; color: var(--sdi-color-text-secondary); max-width: 52ch;">
					<?php esc_html_e( 'Create an account in a few minutes — you\'ll verify your email, then your dashboard, referral link and directory access open right away.', 'sdi-travel' ); ?>
				</p>
				<a href="<?php echo esc_url( home_url( '/sign-up/' ) ); ?>" class="sdi-btn sdi-btn--outline" style="margin-top: 16px;"><?php esc_html_e( 'Create an account', 'sdi-travel' ); ?></a>
			</div>
		</div>

		<div style="min-width: 0; display: flex; flex-direction: column; gap: 16px;">
			<div class="sdi-auth-panel--dark">
				<p class="sdi-auth-kicker"><?php esc_html_e( 'Not a member yet', 'sdi-travel' ); ?></p>
				<h2><?php esc_html_e( 'Join, and the portal opens as soon as you\'re verified', 'sdi-travel' ); ?></h2>
				<p><?php esc_html_e( 'Directory access, partner offers and your referral link activate the moment your email is confirmed.', 'sdi-travel' ); ?></p>
				<div class="sdi-auth-price-row">
					<?php foreach ( $sdi_tiers as $sdi_tier ) : ?>
						<div>
							<p class="sdi-auth-price"><?php echo esc_html( $sdi_tier['price'] ); ?></p>
							<p class="sdi-auth-price-label"><?php echo esc_html( $sdi_tier['label'] . ' ' . $sdi_tier['unit'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
				<a href="<?php echo esc_url( home_url( '/membership/' ) ); ?>" class="sdi-btn sdi-btn--primary" style="margin-top: 26px;"><?php esc_html_e( 'Become a Member', 'sdi-travel' ); ?></a>
			</div>

			<div class="sdi-auth-sidecard">
				<p class="sdi-auth-eyebrow-label"><?php esc_html_e( 'Trouble signing in', 'sdi-travel' ); ?></p>
				<div style="margin-top: 16px; border-top: 1px solid var(--sdi-border-light);">
					<div class="sdi-auth-faq-item">
						<p><?php esc_html_e( 'Never verified your email', 'sdi-travel' ); ?></p>
						<p><?php esc_html_e( 'Accounts stay inactive until the verification link is used. Sign up again if the link expired, or contact us for a new one.', 'sdi-travel' ); ?></p>
					</div>
					<div class="sdi-auth-faq-item">
						<p><?php esc_html_e( 'Family account, second adult', 'sdi-travel' ); ?></p>
						<p><?php esc_html_e( 'A household has one login today. Ask the membership team to add a second sign-in to your account.', 'sdi-travel' ); ?></p>
					</div>
					<div class="sdi-auth-faq-item">
						<p><?php esc_html_e( 'Lapsed membership', 'sdi-travel' ); ?></p>
						<p><?php esc_html_e( 'You can still sign in to renew; the directory and referral link reactivate once dues are current.', 'sdi-travel' ); ?></p>
					</div>
				</div>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="sdi-auth-link" style="display: inline-block; margin-top: 16px;"><?php esc_html_e( 'Contact the membership team', 'sdi-travel' ); ?></a>
			</div>
		</div>

	</div>
</section>

<section class="sdi-section sdi-section--light" style="background: var(--sdi-color-fill-muted); border-bottom: 1px solid var(--sdi-border); padding-block: 56px;">
	<div class="sdi-container" style="display: grid; grid-template-columns: minmax(0, .85fr) minmax(0, 1.4fr); gap: 48px; align-items: start;">
		<div>
			<p class="sdi-auth-kicker"><?php esc_html_e( 'How we handle your account', 'sdi-travel' ); ?></p>
			<h2 style="max-width: 20ch;"><?php esc_html_e( 'Account security', 'sdi-travel' ); ?></h2>
		</div>
		<div class="sdi-auth-security-grid">
			<div>
				<p><?php esc_html_e( 'We never email for your password', 'sdi-travel' ); ?></p>
				<p><?php esc_html_e( 'Reset links come from this website and expire in 60 minutes.', 'sdi-travel' ); ?></p>
			</div>
			<div>
				<p><?php esc_html_e( 'Payment details are not stored here', 'sdi-travel' ); ?></p>
				<p><?php esc_html_e( 'Card data is held by our payment processor, not in the portal.', 'sdi-travel' ); ?></p>
			</div>
			<div>
				<p><?php esc_html_e( 'Documents on request only', 'sdi-travel' ); ?></p>
				<p><?php esc_html_e( 'Scholarship documents are requested after eligibility is confirmed, never by unsolicited email.', 'sdi-travel' ); ?></p>
			</div>
			<div>
				<p><?php esc_html_e( 'You control your data', 'sdi-travel' ); ?></p>
				<p>
					<?php
					printf(
						/* translators: %s: Privacy Policy link */
						esc_html__( 'Request an export or deletion at any time — see the %s.', 'sdi-travel' ),
						'<a href="' . esc_url( home_url( '/legal/#privacy' ) ) . '" class="sdi-auth-link">' . esc_html__( 'Privacy Policy', 'sdi-travel' ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_url()/esc_html() above.
					);
					?>
				</p>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
