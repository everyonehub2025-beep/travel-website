<?php
/**
 * [sdi_member_dashboard] — tabbed member dashboard shell.
 *
 * @package SDI_Trust_Core
 * @var int                  $user_id
 * @var array<string,mixed>  $tier_status
 * @var array<int,array>     $referrals
 * @var array<int,array>     $ledger
 * @var string               $membership
 * @var string               $active_tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tabs = array(
	'overview'     => __( 'Overview', 'sdi-trust-core' ),
	'refer'        => __( 'Refer', 'sdi-trust-core' ),
	'points'       => __( 'Points', 'sdi-trust-core' ),
	'scholarships' => __( 'Scholarships', 'sdi-trust-core' ),
	'donations'    => __( 'Donations', 'sdi-trust-core' ),
	'offers'       => __( 'Offers', 'sdi-trust-core' ),
	'directory'    => __( 'Directory', 'sdi-trust-core' ),
	'profile'      => __( 'Profile', 'sdi-trust-core' ),
);

if ( ! array_key_exists( $active_tab, $tabs ) ) {
	$active_tab = 'overview';
}

$base_url = get_permalink() ? get_permalink() : home_url( '/' );
?>
<div class="sdi-dashboard" data-active-tab="<?php echo esc_attr( $active_tab ); ?>">
	<nav class="sdi-dashboard__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Member dashboard sections', 'sdi-trust-core' ); ?>">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a
				href="<?php echo esc_url( add_query_arg( 'sdi_tab', $key, $base_url ) ); ?>"
				class="sdi-dashboard__tab<?php echo ( $key === $active_tab ) ? ' is-active' : ''; ?>"
				data-sdi-tab="<?php echo esc_attr( $key ); ?>"
				role="tab"
				aria-selected="<?php echo ( $key === $active_tab ) ? 'true' : 'false'; ?>"
			>
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="sdi-dashboard__panels">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<section
				id="sdi-panel-<?php echo esc_attr( $key ); ?>"
				class="sdi-dashboard__panel<?php echo ( $key === $active_tab ) ? ' is-active' : ''; ?>"
				data-sdi-panel="<?php echo esc_attr( $key ); ?>"
				role="tabpanel"
			>
				<?php include __DIR__ . '/dashboard-tabs/' . $key . '.php'; ?>
			</section>
		<?php endforeach; ?>
	</div>
</div>
