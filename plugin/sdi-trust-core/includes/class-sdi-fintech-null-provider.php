<?php
/**
 * No-op fintech provider — Phase 2 is not built.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default provider used until a real fintech partner integration exists.
 * Deliberately does nothing beyond recording that a sync was attempted —
 * no fake data, no mock transactions, no UI implying the integration is
 * live.
 */
class SDI_Fintech_Null_Provider implements SDI_Fintech_Provider {

	/**
	 * {@inheritDoc}
	 */
	public function get_provider_name() {
		return __( 'Not connected — integration pending (Phase 2)', 'sdi-trust-core' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_connected() {
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function sync_transactions( $user_id ) {
		update_option( 'sdi_fintech_last_sync_attempt', current_time( 'mysql', true ) );

		return array();
	}
}
