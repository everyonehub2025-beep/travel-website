<?php
/**
 * Dashboard tab: Account Settings.
 *
 * @package SDI_Trust_Core
 * @var WP_User               $user
 * @var string                $plan
 * @var bool                  $auto_renew
 * @var array<int,array>      $dependents
 * @var array<string,bool>    $email_prefs
 * @var array<int,array>      $donations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_account_notice = isset( $_GET['sdi_account'] ) ? sanitize_key( wp_unslash( $_GET['sdi_account'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display flag.
$sdi_dependent_notice = isset( $_GET['sdi_dependent'] ) ? sanitize_key( wp_unslash( $_GET['sdi_dependent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Account Settings', 'sdi-trust-core' ); ?></h2>

<div style="display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, .85fr); gap: 1.75em; align-items: start;">
	<div style="background: var(--sdi-white); border: 1px solid var(--sdi-border); padding: 1.75em;">
		<?php if ( 'saved' === $sdi_account_notice ) : ?>
			<div class="sdi-notice sdi-notice--success"><p><?php esc_html_e( 'Changes saved.', 'sdi-trust-core' ); ?></p></div>
		<?php elseif ( 'error' === $sdi_account_notice ) : ?>
			<div class="sdi-notice sdi-notice--error"><p><?php esc_html_e( 'Something went wrong — please try again.', 'sdi-trust-core' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="sdi_account_update" />
			<?php wp_nonce_field( 'sdi_account_update', 'sdi_account_nonce' ); ?>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.1em;">
				<p class="sdi-form__field">
					<label for="sdi-account-name"><?php esc_html_e( 'Full name', 'sdi-trust-core' ); ?></label>
					<input type="text" id="sdi-account-name" name="name" value="<?php echo esc_attr( $user->display_name ); ?>" />
				</p>
				<p class="sdi-form__field">
					<label for="sdi-account-email"><?php esc_html_e( 'Account email', 'sdi-trust-core' ); ?></label>
					<input type="email" id="sdi-account-email" value="<?php echo esc_attr( $user->user_email ); ?>" disabled="disabled" />
					<span class="sdi-form__note"><?php esc_html_e( 'Contact the membership team to change your email address.', 'sdi-trust-core' ); ?></span>
				</p>
				<p class="sdi-form__field">
					<label><?php esc_html_e( 'Membership tier', 'sdi-trust-core' ); ?></label>
					<input type="text" value="<?php echo esc_attr( 'family' === $plan ? __( 'Family — $300 / year', 'sdi-trust-core' ) : ( 'individual' === $plan ? __( 'Individual — $180 / year', 'sdi-trust-core' ) : __( 'No plan selected', 'sdi-trust-core' ) ) ); ?>" disabled="disabled" />
					<span class="sdi-form__note"><?php esc_html_e( 'To change tiers, contact the membership team.', 'sdi-trust-core' ); ?></span>
				</p>
				<p class="sdi-form__field">
					<label for="sdi-account-renew"><?php esc_html_e( 'Renewal', 'sdi-trust-core' ); ?></label>
					<select id="sdi-account-renew" name="auto_renew">
						<option value="1" <?php selected( $auto_renew ); ?>><?php esc_html_e( 'Renew automatically', 'sdi-trust-core' ); ?></option>
						<option value="" <?php selected( ! $auto_renew ); ?>><?php esc_html_e( 'Remind me, do not auto-renew', 'sdi-trust-core' ); ?></option>
					</select>
				</p>
			</div>

			<div style="margin-top: 0.5em; padding-top: 1.1em; border-top: 1px solid var(--sdi-border);">
				<p class="sdi-form__note" style="font-weight: 600; color: var(--sdi-silver); text-transform: uppercase; letter-spacing: 0.06em;"><?php esc_html_e( 'Dependents on this account', 'sdi-trust-core' ); ?></p>
				<div style="margin-top: 0.75em;">
					<?php foreach ( $dependents as $sdi_dependent ) : ?>
						<span class="sdi-chip"><?php echo esc_html( $sdi_dependent['name'] . ' · ' . $sdi_dependent['age'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>

			<div style="margin-top: 1.5em; padding-top: 1.1em; border-top: 1px solid var(--sdi-border);">
				<p class="sdi-form__note" style="font-weight: 600; color: var(--sdi-silver); text-transform: uppercase; letter-spacing: 0.06em;"><?php esc_html_e( 'Email preferences', 'sdi-trust-core' ); ?></p>
				<div style="margin-top: 0.9em; display: flex; flex-direction: column; gap: 0.7em;">
					<label style="display: flex; align-items: center; gap: 0.7em; cursor: pointer;">
						<input type="checkbox" name="pref_newsletter" value="1" <?php checked( ! empty( $email_prefs['newsletter'] ) ); ?> style="width: 18px; height: 18px; accent-color: var(--sdi-gold);" />
						<?php esc_html_e( 'Monthly newsletter', 'sdi-trust-core' ); ?>
					</label>
					<label style="display: flex; align-items: center; gap: 0.7em; cursor: pointer;">
						<input type="checkbox" name="pref_scholarships" value="1" <?php checked( ! empty( $email_prefs['scholarships'] ) ); ?> style="width: 18px; height: 18px; accent-color: var(--sdi-gold);" />
						<?php esc_html_e( 'Scholarship announcements', 'sdi-trust-core' ); ?>
					</label>
					<label style="display: flex; align-items: center; gap: 0.7em; cursor: pointer;">
						<input type="checkbox" name="pref_partner_offers" value="1" <?php checked( ! empty( $email_prefs['partner_offers'] ) ); ?> style="width: 18px; height: 18px; accent-color: var(--sdi-gold);" />
						<?php esc_html_e( 'New partner offers as they launch', 'sdi-trust-core' ); ?>
					</label>
				</div>
			</div>

			<p class="sdi-form__submit" style="margin-top: 1.5em;">
				<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Save changes', 'sdi-trust-core' ); ?></button>
			</p>
		</form>

		<div style="margin-top: 1.5em; padding-top: 1.5em; border-top: 1px solid var(--sdi-border);">
			<p class="sdi-form__note" style="font-weight: 600; color: var(--sdi-silver); text-transform: uppercase; letter-spacing: 0.06em;"><?php esc_html_e( '+ Add dependent', 'sdi-trust-core' ); ?></p>
			<?php if ( 'error' === $sdi_dependent_notice ) : ?>
				<p style="color: var(--sdi-color-rust, #9A3324); font-size: 0.85rem;"><?php esc_html_e( 'Dependents must be 22 or younger. Please try again.', 'sdi-trust-core' ); ?></p>
			<?php elseif ( 'added' === $sdi_dependent_notice ) : ?>
				<p style="color: var(--sdi-gold-deep); font-size: 0.85rem;"><?php esc_html_e( 'Dependent added.', 'sdi-trust-core' ); ?></p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display: flex; flex-wrap: wrap; gap: 0.6em; margin-top: 0.75em;">
				<input type="hidden" name="action" value="sdi_add_dependent" />
				<?php wp_nonce_field( 'sdi_add_dependent', 'sdi_dependent_nonce' ); ?>
				<input type="text" name="dependent_name" required="required" placeholder="<?php esc_attr_e( 'Name', 'sdi-trust-core' ); ?>" style="flex: 1 1 160px; padding: 0.8em 0.9em; border: 1px solid var(--sdi-platinum); border-radius: 2px;" />
				<input type="number" name="dependent_age" min="0" max="22" required="required" placeholder="<?php esc_attr_e( 'Age', 'sdi-trust-core' ); ?>" style="width: 90px; padding: 0.8em 0.9em; border: 1px solid var(--sdi-platinum); border-radius: 2px;" />
				<button type="submit" class="sdi-btn sdi-btn--outline"><?php esc_html_e( 'Add', 'sdi-trust-core' ); ?></button>
			</form>
		</div>
	</div>

	<div style="display: flex; flex-direction: column; gap: 1.25em;">
		<div class="sdi-stat-card" style="align-items: flex-start;">
			<span class="sdi-stat-card__label"><?php esc_html_e( 'Giving history', 'sdi-trust-core' ); ?></span>
			<?php if ( empty( $donations ) ) : ?>
				<p class="sdi-empty" style="margin: 0.5em 0 0;"><?php esc_html_e( 'No gifts recorded yet.', 'sdi-trust-core' ); ?></p>
			<?php else : ?>
				<ul style="list-style: none; margin: 0.6em 0 0; padding: 0; width: 100%; display: flex; flex-direction: column; gap: 0.6em;">
					<?php foreach ( array_slice( $donations, 0, 6 ) as $sdi_gift ) : ?>
						<li style="display: flex; justify-content: space-between; gap: 1em; padding: 0.5em 0; border-bottom: 1px solid var(--sdi-border);">
							<span style="font-size: 0.9rem;"><?php echo esc_html( $sdi_gift['date'] ); ?></span>
							<span style="font-weight: 600; color: var(--sdi-gold-deep);">$<?php echo esc_html( number_format_i18n( $sdi_gift['amount'] ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="sdi-stat-card" style="background: var(--sdi-offwhite); align-items: flex-start;">
			<span class="sdi-stat-card__label" style="color: var(--sdi-gold-deep);"><?php esc_html_e( 'Cancelling', 'sdi-trust-core' ); ?></span>
			<p style="margin: 0.7em 0 0; font-size: 0.9rem;"><?php esc_html_e( 'Cancel at least 30 days before your renewal date to avoid the next charge. Points are forfeited after a 90-day lapse.', 'sdi-trust-core' ); ?></p>
			<div style="margin-top: 0.9em; display: flex; flex-wrap: wrap; gap: 1em;">
				<a href="<?php echo esc_url( home_url( '/legal/#membership-terms' ) ); ?>" style="font-size: 0.9rem; text-decoration: underline;"><?php esc_html_e( 'Membership Terms', 'sdi-trust-core' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" style="font-size: 0.9rem; text-decoration: underline;"><?php esc_html_e( 'Request cancellation', 'sdi-trust-core' ); ?></a>
			</div>
		</div>
	</div>
</div>
