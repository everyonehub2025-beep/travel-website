<?php
/**
 * [sdi_tier_progress] — gold-on-platinum progress bar toward next tier.
 *
 * @package SDI_Trust_Core
 * @var array<string,mixed> $tier_status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sdi-progress">
	<div class="sdi-progress__track">
		<div class="sdi-progress__fill" style="width: <?php echo esc_attr( $tier_status['percent_progress'] ); ?>%;"></div>
	</div>
	<p class="sdi-progress__label">
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
