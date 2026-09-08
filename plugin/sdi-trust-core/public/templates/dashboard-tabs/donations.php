<?php
/**
 * Dashboard tab: Donations — read-only history pulled from Charitable.
 *
 * @package SDI_Trust_Core
 * @var int $user_id
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$donations = SDI_Public::get_donation_history( $user_id );
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Donation History', 'sdi-trust-core' ); ?></h2>

<?php if ( ! SDI_Public::is_charitable_active() ) : ?>
	<p class="sdi-empty"><?php esc_html_e( 'Donation history is not available yet — this feature connects to our donation platform once it is installed.', 'sdi-trust-core' ); ?></p>
<?php elseif ( empty( $donations ) ) : ?>
	<p class="sdi-empty"><?php esc_html_e( 'No donations on record yet.', 'sdi-trust-core' ); ?></p>
<?php else : ?>
	<table class="sdi-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Date', 'sdi-trust-core' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Amount', 'sdi-trust-core' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'sdi-trust-core' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $donations as $donation ) : ?>
				<tr>
					<td><?php echo esc_html( $donation['date'] ); ?></td>
					<td><?php echo esc_html( '$' . number_format_i18n( $donation['amount'], 2 ) ); ?></td>
					<td><?php echo esc_html( ucfirst( str_replace( 'charitable-', '', $donation['status'] ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
