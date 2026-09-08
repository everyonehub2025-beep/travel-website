<?php
/**
 * Dashboard tab: Profile — editing is handed off to MemberPress where
 * available; otherwise a minimal read-only summary from core WordPress
 * user data.
 *
 * @package SDI_Trust_Core
 * @var int $user_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = get_userdata( $user_id );
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Profile', 'sdi-trust-core' ); ?></h2>

<?php if ( SDI_Membership::is_memberpress_active() ) : ?>
	<p><?php esc_html_e( 'Manage your account details, payment methods, and subscriptions from your membership account page.', 'sdi-trust-core' ); ?></p>
	<p>
		<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( apply_filters( 'sdi_membership_account_url', home_url( '/member-account/' ) ) ); ?>">
			<?php esc_html_e( 'Manage Account', 'sdi-trust-core' ); ?>
		</a>
	</p>
<?php else : ?>
	<ul class="sdi-profile-summary">
		<li><strong><?php esc_html_e( 'Name:', 'sdi-trust-core' ); ?></strong> <?php echo esc_html( $user ? $user->display_name : '' ); ?></li>
		<li><strong><?php esc_html_e( 'Email:', 'sdi-trust-core' ); ?></strong> <?php echo esc_html( $user ? $user->user_email : '' ); ?></li>
	</ul>
	<p>
		<a class="sdi-btn sdi-btn--outline" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">
			<?php esc_html_e( 'Edit Profile', 'sdi-trust-core' ); ?>
		</a>
	</p>
<?php endif; ?>
