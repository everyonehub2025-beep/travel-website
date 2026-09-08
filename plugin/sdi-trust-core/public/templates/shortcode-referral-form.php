<?php
/**
 * [sdi_referral_form]
 *
 * @package SDI_Trust_Core
 * @var array{type:string,message:string}|null $notice
 * @var string $action_url
 * @var string $nonce_field
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sdi-referral-form">
	<?php if ( $notice ) : ?>
		<div class="sdi-notice sdi-notice--<?php echo esc_attr( $notice['type'] ); ?>">
			<p><?php echo esc_html( $notice['message'] ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( $action_url ); ?>" class="sdi-form">
		<input type="hidden" name="action" value="sdi_submit_referral" />
		<?php echo $nonce_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nonce_field() output is already safe markup. ?>

		<p class="sdi-form__field">
			<label for="sdi_referred_name"><?php esc_html_e( 'Referred person\'s name', 'sdi-trust-core' ); ?></label>
			<input type="text" id="sdi_referred_name" name="sdi_referred_name" required="required" maxlength="200" />
		</p>

		<p class="sdi-form__field">
			<label for="sdi_referred_email"><?php esc_html_e( 'Referred person\'s email', 'sdi-trust-core' ); ?></label>
			<input type="email" id="sdi_referred_email" name="sdi_referred_email" required="required" maxlength="200" />
		</p>

		<p class="sdi-form__field">
			<label for="sdi_referred_phone"><?php esc_html_e( 'Referred person\'s phone (optional)', 'sdi-trust-core' ); ?></label>
			<input type="tel" id="sdi_referred_phone" name="sdi_referred_phone" maxlength="50" />
		</p>

		<p class="sdi-form__submit">
			<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Submit Referral', 'sdi-trust-core' ); ?></button>
		</p>

		<p class="sdi-form__note"><?php echo esc_html( sdi_tc_text( 'referral_pending_notice' ) ); ?></p>
	</form>
</div>
