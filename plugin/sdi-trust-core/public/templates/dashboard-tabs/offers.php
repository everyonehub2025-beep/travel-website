<?php
/**
 * Dashboard tab: Partner Offers.
 *
 * No fintech/offer-issuance provider is connected yet (see
 * SDI_Fintech_Null_Provider) — offers populate via the `sdi_partner_offers`
 * filter once one is. Never fabricates sample codes or partners here.
 *
 * @package SDI_Trust_Core
 * @var array<int,array<string,mixed>> $offers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Partner Offers & Codes', 'sdi-trust-core' ); ?></h2>
<p style="max-width: 60ch; color: var(--sdi-silver);"><?php esc_html_e( 'Offers appear here once a donation-linked code is issued to your account.', 'sdi-trust-core' ); ?></p>

<?php if ( empty( $offers ) ) : ?>
	<div class="sdi-stat-card" style="margin-top: 1.5em; align-items: flex-start;">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'No offers yet', 'sdi-trust-core' ); ?></span>
		<p style="margin: 0.6em 0 0; color: var(--sdi-navy); max-width: 52ch;"><?php esc_html_e( 'Partner offers are issued after a qualifying donation to the scholarship fund. Give through Fundraising and a code will appear here.', 'sdi-trust-core' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/fundraising/#offers' ) ); ?>" class="sdi-btn sdi-btn--primary" style="margin-top: 1em;"><?php esc_html_e( 'See all offers', 'sdi-trust-core' ); ?></a>
	</div>
<?php else : ?>
	<div class="sdi-offers-grid" style="margin-top: 1.5em;">
		<?php foreach ( $offers as $sdi_offer ) : ?>
			<div class="sdi-offer-card">
				<div style="display: flex; justify-content: space-between; gap: 0.75em;">
					<span class="sdi-form__note" style="text-transform: uppercase; letter-spacing: 0.08em; font-weight: 600;"><?php echo esc_html( $sdi_offer['category'] ?? '' ); ?></span>
					<span class="sdi-form__note" style="font-weight: 600; color: var(--sdi-gold-deep);"><?php echo esc_html( $sdi_offer['state'] ?? '' ); ?></span>
				</div>
				<p class="sdi-offer-card__partner"><?php echo esc_html( $sdi_offer['partner'] ?? '' ); ?></p>
				<p style="font-size: 0.9rem; color: var(--sdi-navy);"><?php echo esc_html( $sdi_offer['terms'] ?? '' ); ?></p>
				<?php if ( ! empty( $sdi_offer['code'] ) ) : ?>
					<span class="sdi-offer-card__code"><?php echo esc_html( $sdi_offer['code'] ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $sdi_offer['expiry'] ) ) : ?>
					<p class="sdi-form__note" style="margin-top: 0.6em;"><?php echo esc_html( $sdi_offer['expiry'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
