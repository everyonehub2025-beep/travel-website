<?php
/**
 * [sdi_points_balance]
 *
 * @package SDI_Trust_Core
 * @var int $balance
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="sdi-points-balance">
	<span class="sdi-points-balance__value"><?php echo esc_html( number_format_i18n( $balance ) ); ?></span>
	<span class="sdi-points-balance__label"><?php esc_html_e( 'Points', 'sdi-trust-core' ); ?></span>
</div>
