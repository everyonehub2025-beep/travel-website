<?php
/**
 * Dashboard tab: Offers — active partner discount codes.
 *
 * No offers data source is defined yet (partner/retailer integrations are
 * managed outside this plugin). Exposed via a filter so offers can be fed
 * in from a future partner-management plugin or a simple options page
 * without touching this template.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters the list of active partner offers shown in the member dashboard.
 *
 * @param array<int,array{partner:string,description:string,code:string}> $offers
 */
$offers = apply_filters( 'sdi_partner_offers', array() );
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Partner Offers', 'sdi-trust-core' ); ?></h2>

<?php if ( empty( $offers ) ) : ?>
	<p class="sdi-empty"><?php esc_html_e( 'No active partner offers at this time. Check back soon.', 'sdi-trust-core' ); ?></p>
<?php else : ?>
	<div class="sdi-offers-grid">
		<?php foreach ( $offers as $offer ) : ?>
			<div class="sdi-offer-card">
				<h3 class="sdi-offer-card__partner"><?php echo esc_html( $offer['partner'] ?? '' ); ?></h3>
				<p class="sdi-offer-card__description"><?php echo esc_html( $offer['description'] ?? '' ); ?></p>
				<?php if ( ! empty( $offer['code'] ) ) : ?>
					<code class="sdi-offer-card__code"><?php echo esc_html( $offer['code'] ); ?></code>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
