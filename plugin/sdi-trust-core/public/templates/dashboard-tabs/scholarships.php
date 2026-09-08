<?php
/**
 * Dashboard tab: Scholarships — tier explainer + application link when eligible.
 *
 * @package SDI_Trust_Core
 * @var array<string,mixed> $tier_status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$thresholds        = SDI_Tiers::get_thresholds();
$application_url   = apply_filters( 'sdi_scholarship_application_url', home_url( '/scholarships/#application' ) );
$is_eligible       = null !== $tier_status['tier_threshold'];
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Scholarship Eligibility', 'sdi-trust-core' ); ?></h2>

<p class="sdi-tier-status__label"><?php echo esc_html( $tier_status['label'] ); ?></p>

<table class="sdi-table sdi-table--tiers">
	<thead>
		<tr>
			<th scope="col"><?php esc_html_e( 'Points', 'sdi-trust-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Eligibility Level', 'sdi-trust-core' ); ?></th>
			<th scope="col"><?php esc_html_e( 'Your Status', 'sdi-trust-core' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $thresholds as $points => $amount ) : ?>
			<?php $reached = $tier_status['balance'] >= $points; ?>
			<tr class="<?php echo $reached ? 'is-reached' : ''; ?>">
				<td><?php echo esc_html( number_format_i18n( $points ) ); ?></td>
				<td><?php echo esc_html( 'up to $' . number_format_i18n( $amount ) ); ?></td>
				<td>
					<?php if ( $reached ) : ?>
						<span class="sdi-badge sdi-badge--approved"><?php esc_html_e( 'Reached', 'sdi-trust-core' ); ?></span>
					<?php else : ?>
						<span class="sdi-badge sdi-badge--pending"><?php esc_html_e( 'Not yet reached', 'sdi-trust-core' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if ( $is_eligible ) : ?>
	<p>
		<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( $application_url ); ?>">
			<?php esc_html_e( 'Start a Scholarship Application', 'sdi-trust-core' ); ?>
		</a>
	</p>
<?php else : ?>
	<p class="sdi-empty"><?php esc_html_e( 'The scholarship application link becomes available once you reach the first eligibility level.', 'sdi-trust-core' ); ?></p>
<?php endif; ?>

<p class="sdi-panel__disclaimer"><?php echo esc_html( $tier_status['disclaimer'] ); ?></p>
