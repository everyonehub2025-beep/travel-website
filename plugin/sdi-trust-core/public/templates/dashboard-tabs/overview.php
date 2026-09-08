<?php
/**
 * Dashboard tab: Overview.
 *
 * @package SDI_Trust_Core
 * @var int                  $user_id
 * @var array<string,mixed>  $tier_status
 * @var string               $membership
 * @var string               $plan
 * @var bool                 $is_active
 * @var string               $referral_link
 * @var string               $member_since
 * @var array<int,array>     $ledger
 * @var array<int,array>     $donations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sdi_given_to_date = array_sum( wp_list_pluck( $donations, 'amount' ) );
$sdi_plan_label    = ( 'family' === $plan ) ? __( 'Family', 'sdi-trust-core' ) : ( ( 'individual' === $plan ) ? __( 'Individual', 'sdi-trust-core' ) : __( 'No plan selected', 'sdi-trust-core' ) );
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Membership Overview', 'sdi-trust-core' ); ?></h2>

<div style="display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.85fr); gap: 1.25em; align-items: stretch;">
	<div class="sdi-hero-card">
		<p class="sdi-hero-card__kicker"><?php esc_html_e( 'Your progress', 'sdi-trust-core' ); ?></p>
		<div class="sdi-hero-card__stats">
			<div>
				<span class="sdi-hero-card__stat-value"><?php echo esc_html( number_format_i18n( $tier_status['balance'] ) ); ?></span>
				<span class="sdi-hero-card__stat-label"><?php esc_html_e( 'Referral points', 'sdi-trust-core' ); ?></span>
			</div>
			<div>
				<span class="sdi-hero-card__stat-value" style="color: var(--sdi-white); font-size: 1.9rem;">
					<?php echo $tier_status['tier_award_amount'] ? esc_html( '$' . number_format_i18n( $tier_status['tier_award_amount'] ) ) : esc_html__( 'Pre-tier', 'sdi-trust-core' ); ?>
				</span>
				<span class="sdi-hero-card__stat-label"><?php esc_html_e( 'Current tier', 'sdi-trust-core' ); ?></span>
			</div>
		</div>
		<div class="sdi-hero-card__track">
			<div class="sdi-hero-card__fill" style="width: <?php echo esc_attr( $tier_status['percent_progress'] ); ?>%;"></div>
		</div>
		<p style="margin: 0.9em 0 0; font-size: 0.9rem; color: var(--sdi-platinum);">
			<?php
			if ( $tier_status['is_max_tier'] ) {
				echo esc_html( sdi_tc_text( 'tier_label_max' ) );
			} elseif ( null !== $tier_status['points_to_next'] ) {
				echo esc_html( sdi_tc_text( 'tier_label_progress', array( $tier_status['points_to_next'] ) ) );
			} else {
				echo esc_html( sdi_tc_text( 'tier_label_none' ) );
			}
			?>
		</p>
	</div>

	<div style="display: flex; flex-direction: column; gap: 1.25em;">
		<div class="sdi-stat-card" style="gap: 0.7em;">
			<span class="sdi-stat-card__label"><?php esc_html_e( 'Membership', 'sdi-trust-core' ); ?></span>
			<span class="sdi-stat-card__value sdi-stat-card__value--small"><?php echo esc_html( $sdi_plan_label . ' — ' . $membership ); ?></span>
			<?php if ( $member_since ) : ?>
				<span class="sdi-form__note"><?php printf( /* translators: %s: date */ esc_html__( 'Member since %s', 'sdi-trust-core' ), esc_html( mysql2date( get_option( 'date_format' ), $member_since ) ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="sdi-stat-card" style="background: var(--sdi-offwhite);">
			<span class="sdi-stat-card__label"><?php esc_html_e( 'Given to date', 'sdi-trust-core' ); ?></span>
			<span class="sdi-stat-card__value" style="color: var(--sdi-gold-deep);">$<?php echo esc_html( number_format_i18n( $sdi_given_to_date ) ); ?></span>
			<span class="sdi-form__note">
				<?php
				echo $sdi_given_to_date
					? esc_html__( 'Thank you for your generosity.', 'sdi-trust-core' )
					: esc_html__( 'No gifts recorded yet.', 'sdi-trust-core' );
				?>
			</span>
		</div>
	</div>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 1.25em; margin-top: 1.25em;">
	<div class="sdi-stat-card" style="flex: 1 1 260px;">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Your referral link', 'sdi-trust-core' ); ?></span>
		<div class="sdi-referral-box"><?php echo esc_html( $referral_link ); ?></div>
		<button type="button" class="sdi-btn sdi-btn--primary" data-sdi-copy="<?php echo esc_attr( $referral_link ); ?>" data-sdi-copied-label="<?php esc_attr_e( 'Copied!', 'sdi-trust-core' ); ?>" style="margin-top: 0.9em; align-self: flex-start;"><?php esc_html_e( 'Copy link', 'sdi-trust-core' ); ?></button>
		<span class="sdi-form__note" style="margin-top: 0.6em;"><?php esc_html_e( '30 points per confirmed referral. Confirmation follows a paid membership and manual review.', 'sdi-trust-core' ); ?></span>
	</div>

	<div class="sdi-stat-card" style="flex: 1 1 260px;">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Recent activity', 'sdi-trust-core' ); ?></span>
		<?php if ( empty( $ledger ) ) : ?>
			<p class="sdi-empty" style="margin: 0.5em 0 0;"><?php esc_html_e( 'Nothing recorded yet — activity appears here once a referral is approved.', 'sdi-trust-core' ); ?></p>
		<?php else : ?>
			<ul style="list-style: none; margin: 0.6em 0 0; padding: 0; display: flex; flex-direction: column; gap: 0.6em;">
				<?php foreach ( array_slice( $ledger, 0, 4 ) as $sdi_entry ) : ?>
					<li style="display: flex; justify-content: space-between; gap: 1em; padding: 0.5em 0; border-bottom: 1px solid var(--sdi-border);">
						<span style="font-size: 0.9rem;"><?php echo esc_html( mysql2date( get_option( 'date_format' ), $sdi_entry['created_at'] ) ); ?></span>
						<span class="sdi-table__amount <?php echo ( $sdi_entry['points'] >= 0 ) ? 'is-positive' : 'is-negative'; ?>"><?php echo esc_html( ( $sdi_entry['points'] >= 0 ? '+' : '' ) . $sdi_entry['points'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="sdi-stat-card" style="flex: 1 1 260px;">
		<span class="sdi-stat-card__label"><?php esc_html_e( 'Shortcuts', 'sdi-trust-core' ); ?></span>
		<div style="margin-top: 0.6em; display: flex; flex-direction: column; gap: 0.6em;">
			<a class="sdi-chip" style="text-align: left;" href="<?php echo esc_url( SDI_Public::get_directory_url() ); ?>"><?php esc_html_e( 'Browse the travel directory', 'sdi-trust-core' ); ?></a>
			<a class="sdi-chip" style="text-align: left;" href="<?php echo esc_url( home_url( '/fundraising/#donate' ) ); ?>"><?php esc_html_e( 'Make a donation', 'sdi-trust-core' ); ?></a>
			<a class="sdi-chip" style="text-align: left;" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact the membership team', 'sdi-trust-core' ); ?></a>
		</div>
	</div>
</div>

<p class="sdi-panel__disclaimer"><?php echo esc_html( $tier_status['disclaimer'] ); ?></p>
