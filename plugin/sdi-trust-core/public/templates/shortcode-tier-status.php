<?php
/**
 * [sdi_tier_status]
 *
 * @package SDI_Trust_Core
 * @var array<string,mixed> $tier_status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sdi-tier-status">
	<p class="sdi-tier-status__label"><?php echo esc_html( $tier_status['label'] ); ?></p>
	<?php if ( $tier_status['tier_award_amount'] ) : ?>
		<p class="sdi-tier-status__amount">
			<?php
			printf(
				/* translators: %s: formatted dollar amount */
				esc_html__( 'Current eligibility level: up to %s', 'sdi-trust-core' ),
				esc_html( '$' . number_format_i18n( $tier_status['tier_award_amount'] ) )
			);
			?>
		</p>
	<?php endif; ?>
	<p class="sdi-tier-status__disclaimer"><?php echo esc_html( $tier_status['disclaimer'] ); ?></p>
</div>
