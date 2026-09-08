<?php
/**
 * Dashboard tab: Overview.
 *
 * @package SDI_Trust_Core
 * @var int                 $user_id
 * @var array<string,mixed> $tier_status
 * @var string              $membership
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Membership Overview', 'sdi-trust-core' ); ?></h2>

<div class="sdi-overview-grid">
	<div class="sdi-stat-card">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Membership Status', 'sdi-trust-core' ); ?></span>
		<span class="sdi-stat-card__value sdi-stat-card__value--small"><?php echo esc_html( $membership ); ?></span>
	</div>

	<div class="sdi-stat-card">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Points Balance', 'sdi-trust-core' ); ?></span>
		<span class="sdi-stat-card__value"><?php echo esc_html( number_format_i18n( $tier_status['balance'] ) ); ?></span>
	</div>

	<div class="sdi-stat-card">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Scholarship Eligibility Level', 'sdi-trust-core' ); ?></span>
		<span class="sdi-stat-card__value sdi-stat-card__value--small">
			<?php
			echo $tier_status['tier_award_amount']
				? esc_html( '$' . number_format_i18n( $tier_status['tier_award_amount'] ) )
				: esc_html__( 'Not yet eligible', 'sdi-trust-core' );
			?>
		</span>
	</div>
</div>

<?php include __DIR__ . '/../shortcode-tier-progress.php'; ?>

<p class="sdi-panel__disclaimer"><?php echo esc_html( $tier_status['disclaimer'] ); ?></p>
