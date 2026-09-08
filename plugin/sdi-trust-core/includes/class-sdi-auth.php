<?php
/**
 * Native member accounts: registration, email verification, and login —
 * the "system" behind the theme's Sign Up and Member Login pages. Built
 * without a third-party membership plugin: a real WordPress user with the
 * `sdi_member` role, gated by a `sdi_membership_status` user meta value
 * rather than a payment gateway (there isn't one wired up yet — see
 * SETUP.md "Connecting a payment processor").
 *
 * Status lifecycle: pending_verification -> active (on email click) ->
 * lapsed (set manually, or by a future renewal job) -> cancelled.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registration, verification, and login handlers for native member accounts.
 */
class SDI_Auth {

	const ROLE                = 'sdi_member';
	const STATUS_PENDING      = 'pending_verification';
	const STATUS_ACTIVE       = 'active';
	const STATUS_LAPSED       = 'lapsed';
	const STATUS_CANCELLED    = 'cancelled';
	const VERIFY_TTL          = DAY_IN_SECONDS;

	/**
	 * Register the sdi_member role. Idempotent — safe to call on every
	 * activation/upgrade.
	 */
	public static function register_role() {
		if ( ! get_role( self::ROLE ) ) {
			add_role(
				self::ROLE,
				__( 'SDI Member', 'sdi-trust-core' ),
				array(
					'read' => true,
				)
			);
		}
	}

	/**
	 * Valid membership tiers and their headline annual price, used by both
	 * the Sign Up form and the dashboard/admin display. Filterable so a
	 * price change is a filter, not a template edit.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_tiers() {
		return apply_filters(
			'sdi_membership_tiers',
			array(
				'individual' => array(
					'label' => __( 'Individual', 'sdi-trust-core' ),
					'price' => '$180',
					'unit'  => __( '/ year', 'sdi-trust-core' ),
				),
				'family'     => array(
					'label' => __( 'Family', 'sdi-trust-core' ),
					'price' => '$300',
					'unit'  => __( '/ year', 'sdi-trust-core' ),
				),
			)
		);
	}

	/**
	 * A user's current membership status, defaulting to cancelled for any
	 * account that somehow has no status set yet.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_status( $user_id ) {
		$status = get_user_meta( absint( $user_id ), 'sdi_membership_status', true );
		return $status ? $status : self::STATUS_CANCELLED;
	}

	/**
	 * A user's selected membership tier ('individual'|'family'), or ''.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_tier( $user_id ) {
		return (string) get_user_meta( absint( $user_id ), 'sdi_membership_tier', true );
	}

	/**
	 * A member's personal referral code, generating and storing one on
	 * first use. Short and URL-safe — used as `?ref=` on the Sign Up page.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_referral_code( $user_id ) {
		$user_id = absint( $user_id );
		$code    = get_user_meta( $user_id, 'sdi_referral_code', true );

		if ( $code ) {
			return $code;
		}

		$code = 'SDI-' . strtoupper( substr( md5( $user_id . wp_generate_password( 6, false ) ), 0, 6 ) );
		update_user_meta( $user_id, 'sdi_referral_code', $code );

		return $code;
	}

	/**
	 * A member's personal referral link, pointing at the Sign Up page.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	public static function get_referral_link( $user_id ) {
		return add_query_arg( array( 'ref' => self::get_referral_code( $user_id ) ), home_url( '/sign-up/' ) );
	}

	/**
	 * Resolve a referral code back to the member who owns it.
	 *
	 * @param string $code Referral code.
	 * @return int User ID, or 0 if not found.
	 */
	public static function get_user_by_referral_code( $code ) {
		if ( ! $code ) {
			return 0;
		}

		$users = get_users(
			array(
				'meta_key'   => 'sdi_referral_code', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $code, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
				'fields'     => 'ID',
			)
		);

		return $users ? absint( $users[0] ) : 0;
	}

	/**
	 * Handle the Sign Up form POST. Creates an inactive account and emails
	 * a verification link — never logs the visitor in directly, matching
	 * the account-security copy on the Member Login page ("accounts stay
	 * inactive until the verification link is used").
	 */
	public static function handle_signup() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/sign-up/' );

		$fail = function ( $code ) use ( $redirect ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_signup_error' => $code ), $redirect ) );
			exit;
		};

		$nonce_ok       = isset( $_POST['sdi_signup_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_signup_nonce'] ) ), 'sdi_signup' );
		$honeypot_empty = empty( $_POST['sdi_signup_hp'] );

		if ( ! $nonce_ok || ! $honeypot_empty ) {
			$fail( 'invalid' );
		}

		$name     = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- passwords are never sanitized, only validated.
		$confirm  = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- see above.
		$tier     = isset( $_POST['tier'] ) ? sanitize_key( wp_unslash( $_POST['tier'] ) ) : '';
		$terms_ok = ! empty( $_POST['terms'] );

		$tiers = self::get_tiers();

		if ( '' === $name || ! is_email( $email ) || ! isset( $tiers[ $tier ] ) || ! $terms_ok ) {
			$fail( 'fields' );
		}

		if ( strlen( $password ) < 8 || $password !== $confirm ) {
			$fail( 'password' );
		}

		if ( email_exists( $email ) ) {
			$fail( 'exists' );
		}

		$ip_key = isset( $_SERVER['REMOTE_ADDR'] ) ? md5( sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) ) : 'unknown';
		if ( ! SDI_Rate_Limiter::allow( 'sdi_signup_' . $ip_key, 0, 5, HOUR_IN_SECONDS ) ) {
			$fail( 'rate_limited' );
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $password,
				'display_name' => $name,
				'first_name'   => $name,
				'role'         => self::ROLE,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			$fail( 'exists' );
		}

		$token = wp_generate_password( 32, false );

		update_user_meta( $user_id, 'sdi_membership_tier', $tier );
		update_user_meta( $user_id, 'sdi_membership_status', self::STATUS_PENDING );
		update_user_meta( $user_id, 'sdi_verify_token', $token );
		update_user_meta( $user_id, 'sdi_verify_expires', time() + self::VERIFY_TTL );

		$ref_code = isset( $_POST['ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ref'] ) ) : '';
		if ( $ref_code ) {
			update_user_meta( $user_id, 'sdi_referred_by_code', $ref_code );
		}

		self::send_verification_email( $user_id, $token );

		/**
		 * Fires after a new member account is created (still unverified).
		 *
		 * @param int    $user_id User ID.
		 * @param string $tier    Selected membership tier.
		 */
		do_action( 'sdi_member_registered', $user_id, $tier );

		wp_safe_redirect( add_query_arg( array( 'sdi_signup' => 'pending' ), home_url( '/sign-up/' ) ) );
		exit;
	}

	/**
	 * Email the account-verification link.
	 *
	 * @param int    $user_id User ID.
	 * @param string $token   Verification token.
	 */
	private static function send_verification_email( $user_id, $token ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		$verify_url = add_query_arg(
			array(
				'action' => 'sdi_verify_email',
				'uid'    => $user_id,
				'token'  => $token,
			),
			admin_url( 'admin-post.php' )
		);

		$subject = __( 'Verify your SDI Travel Trust account', 'sdi-trust-core' );
		$body    = sprintf(
			/* translators: 1: display name, 2: verification link */
			__( "Hi %1\$s,\n\nWelcome to SDI Travel Trust. Confirm your email address to activate your account:\n\n%2\$s\n\nThis link expires in 24 hours. If you didn't create this account, you can ignore this email.\n", 'sdi-trust-core' ),
			$user->display_name,
			$verify_url
		);

		wp_mail( $user->user_email, $subject, $body );
	}

	/**
	 * Handle the verification link click: activates the account and signs
	 * the member in.
	 */
	public static function handle_verify_email() {
		$login_url = home_url( '/member-login/' );

		$uid   = isset( $_GET['uid'] ) ? absint( $_GET['uid'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- token itself is the credential, checked below.
		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- see above.

		$stored_token   = $uid ? get_user_meta( $uid, 'sdi_verify_token', true ) : '';
		$stored_expires = $uid ? (int) get_user_meta( $uid, 'sdi_verify_expires', true ) : 0;

		if ( ! $uid || ! $token || ! $stored_token || ! hash_equals( $stored_token, $token ) || time() > $stored_expires ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_login_error' => 'verify' ), $login_url ) );
			exit;
		}

		update_user_meta( $uid, 'sdi_membership_status', self::STATUS_ACTIVE );
		update_user_meta( $uid, 'sdi_member_since', current_time( 'mysql' ) );
		delete_user_meta( $uid, 'sdi_verify_token' );
		delete_user_meta( $uid, 'sdi_verify_expires' );

		self::maybe_record_referral( $uid );

		do_action( 'sdi_member_activated', $uid );

		wp_set_current_user( $uid );
		wp_set_auth_cookie( $uid );

		wp_safe_redirect( add_query_arg( array( 'sdi_verified' => '1' ), home_url( '/member-dashboard/' ) ) );
		exit;
	}

	/**
	 * If a newly-verified member signed up via another member's referral
	 * link, record it as a pending referral now that we know the account
	 * is real — mirrors the same manual-approval fraud control the
	 * dashboard's own referral form uses. Runs at most once per account.
	 *
	 * @param int $user_id Newly-verified user ID.
	 */
	private static function maybe_record_referral( $user_id ) {
		$ref_code = get_user_meta( $user_id, 'sdi_referred_by_code', true );

		if ( ! $ref_code ) {
			return;
		}

		delete_user_meta( $user_id, 'sdi_referred_by_code' );

		$referrer_id = self::get_user_by_referral_code( $ref_code );

		if ( ! $referrer_id || $referrer_id === $user_id || ! SDI_Membership::is_active_member( $referrer_id ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		SDI_Referrals::submit( $referrer_id, $user->display_name, $user->user_email, '', 'system' );
	}

	/**
	 * Handle the Member Login form POST.
	 */
	public static function handle_login() {
		$login_url = home_url( '/member-login/' );

		$nonce_ok       = isset( $_POST['sdi_login_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_login_nonce'] ) ), 'sdi_member_login' );
		$honeypot_empty = empty( $_POST['sdi_login_hp'] );

		if ( ! $nonce_ok || ! $honeypot_empty ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_login_error' => 'invalid' ), $login_url ) );
			exit;
		}

		$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- passwords are never sanitized.
		$remember = ! empty( $_POST['remember'] );

		if ( ! SDI_Rate_Limiter::allow( 'sdi_login_' . md5( $email ), 0, 8, 15 * MINUTE_IN_SECONDS ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_login_error' => 'rate_limited' ), $login_url ) );
			exit;
		}

		$user = wp_signon(
			array(
				'user_login'    => $email,
				'user_password' => $password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_login_error' => 'credentials' ), $login_url ) );
			exit;
		}

		if ( in_array( self::ROLE, (array) $user->roles, true ) && self::STATUS_PENDING === self::get_status( $user->ID ) ) {
			wp_logout();
			wp_safe_redirect( add_query_arg( array( 'sdi_login_error' => 'unverified' ), $login_url ) );
			exit;
		}

		wp_safe_redirect( home_url( '/member-dashboard/' ) );
		exit;
	}

	/**
	 * Handle the Account Settings tab's "Save changes" form: display name,
	 * renewal preference, email preferences. Email address changes are
	 * intentionally out of scope here — see SETUP.md.
	 */
	public static function handle_account_update() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/member-dashboard/' );

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$user_id = get_current_user_id();

		if ( ! isset( $_POST['sdi_account_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_account_nonce'] ) ), 'sdi_account_update' ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_account' => 'error' ), $redirect ) );
			exit;
		}

		if ( isset( $_POST['name'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			if ( '' !== $name ) {
				wp_update_user( array( 'ID' => $user_id, 'display_name' => $name ) );
			}
		}

		update_user_meta( $user_id, 'sdi_auto_renew', empty( $_POST['auto_renew'] ) ? 'no' : 'yes' );

		$prefs = array(
			'newsletter'    => ! empty( $_POST['pref_newsletter'] ),
			'scholarships'  => ! empty( $_POST['pref_scholarships'] ),
			'partner_offers' => ! empty( $_POST['pref_partner_offers'] ),
		);
		update_user_meta( $user_id, 'sdi_email_prefs', $prefs );

		wp_safe_redirect( add_query_arg( array( 'sdi_account' => 'saved' ), $redirect ) );
		exit;
	}

	/**
	 * Handle the Account Settings tab's "+ Add dependent" form. Dependents
	 * are a simple name+age list stored as user meta — no billing impact
	 * (there's no payment gateway wired up to reprice a family plan yet).
	 */
	public static function handle_add_dependent() {
		$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/member-dashboard/' );

		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( $redirect );
			exit;
		}

		$user_id = get_current_user_id();

		if ( ! isset( $_POST['sdi_dependent_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_dependent_nonce'] ) ), 'sdi_add_dependent' ) ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_dependent' => 'error' ), $redirect ) );
			exit;
		}

		$name = isset( $_POST['dependent_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dependent_name'] ) ) : '';
		$age  = isset( $_POST['dependent_age'] ) ? absint( $_POST['dependent_age'] ) : 0;

		if ( '' === $name || ! $age || $age > 22 ) {
			wp_safe_redirect( add_query_arg( array( 'sdi_dependent' => 'error' ), $redirect ) );
			exit;
		}

		$dependents   = get_user_meta( $user_id, 'sdi_dependents', true );
		$dependents   = is_array( $dependents ) ? $dependents : array();
		$dependents[] = array( 'name' => $name, 'age' => $age );
		update_user_meta( $user_id, 'sdi_dependents', $dependents );

		wp_safe_redirect( add_query_arg( array( 'sdi_dependent' => 'added' ), $redirect ) );
		exit;
	}

	/**
	 * Redirect a logged-in visitor away from the login/sign-up pages
	 * straight to the dashboard — there's nothing for them to do there.
	 */
	public static function maybe_redirect_logged_in() {
		if ( is_user_logged_in() && ( is_page( 'member-login' ) || is_page( 'sign-up' ) ) ) {
			wp_safe_redirect( home_url( '/member-dashboard/' ) );
			exit;
		}
	}

	/**
	 * Add membership status/tier fields to the standard user-profile screen
	 * so an admin can activate, renew, or cancel an account by hand until a
	 * payment processor is wired up to do it automatically.
	 *
	 * @param WP_User $user User being edited.
	 */
	public static function render_profile_fields( $user ) {
		if ( ! current_user_can( 'edit_users' ) || ! in_array( self::ROLE, (array) $user->roles, true ) ) {
			return;
		}

		$status = self::get_status( $user->ID );
		$tier   = self::get_tier( $user->ID );
		$since  = get_user_meta( $user->ID, 'sdi_member_since', true );
		?>
		<h2><?php esc_html_e( 'SDI Membership', 'sdi-trust-core' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="sdi_membership_status"><?php esc_html_e( 'Status', 'sdi-trust-core' ); ?></label></th>
				<td>
					<select name="sdi_membership_status" id="sdi_membership_status">
						<?php foreach ( array( self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_LAPSED, self::STATUS_CANCELLED ) as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $status, $option ); ?>><?php echo esc_html( ucwords( str_replace( '_', ' ', $option ) ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="sdi_membership_tier"><?php esc_html_e( 'Tier', 'sdi-trust-core' ); ?></label></th>
				<td>
					<select name="sdi_membership_tier" id="sdi_membership_tier">
						<?php foreach ( self::get_tiers() as $key => $data ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $tier, $key ); ?>><?php echo esc_html( $data['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php if ( $since ) : ?>
			<tr>
				<th><?php esc_html_e( 'Member since', 'sdi-trust-core' ); ?></th>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $since ) ); ?></td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
		wp_nonce_field( 'sdi_profile_membership', 'sdi_profile_membership_nonce' );
	}

	/**
	 * Save the membership fields added in render_profile_fields().
	 *
	 * @param int $user_id User being saved.
	 */
	public static function save_profile_fields( $user_id ) {
		if ( ! current_user_can( 'edit_users' ) ) {
			return;
		}

		if ( ! isset( $_POST['sdi_profile_membership_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sdi_profile_membership_nonce'] ) ), 'sdi_profile_membership' ) ) {
			return;
		}

		if ( isset( $_POST['sdi_membership_status'] ) ) {
			$status = sanitize_key( wp_unslash( $_POST['sdi_membership_status'] ) );
			update_user_meta( $user_id, 'sdi_membership_status', $status );

			if ( self::STATUS_ACTIVE === $status && ! get_user_meta( $user_id, 'sdi_member_since', true ) ) {
				update_user_meta( $user_id, 'sdi_member_since', current_time( 'mysql' ) );
			}
		}

		if ( isset( $_POST['sdi_membership_tier'] ) ) {
			update_user_meta( $user_id, 'sdi_membership_tier', sanitize_key( wp_unslash( $_POST['sdi_membership_tier'] ) ) );
		}
	}
}
