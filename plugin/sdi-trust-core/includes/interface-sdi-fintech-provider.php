<?php
/**
 * Contract for a Phase 2 fintech (eligible-purchase) provider.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A fintech provider connects SDI Trust Core to a participating debit-card
 * issuer's eligible-purchase donation program. Nothing implements this
 * beyond SDI_Fintech_Null_Provider today — the real partner and API are
 * undefined. Drop a real implementation in via the
 * `sdi_fintech_provider` filter without touching any core file.
 */
interface SDI_Fintech_Provider {

	/**
	 * Human-readable provider name for display in Settings.
	 *
	 * @return string
	 */
	public function get_provider_name();

	/**
	 * Whether this provider currently has valid credentials/connection.
	 *
	 * @return bool
	 */
	public function is_connected();

	/**
	 * Pull eligible-purchase activity for one member since their last
	 * sync and return normalized transaction data. Implementations are
	 * responsible for calling SDI_Points::add_entry() themselves with
	 * reason 'fintech_purchase' for anything that should award points.
	 *
	 * @param int $user_id Member user ID.
	 * @return array<int,array<string,mixed>> Normalized transactions.
	 */
	public function sync_transactions( $user_id );
}
