<?php
/**
 * Fintech module manager — resolves the active provider.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves and exposes the active SDI_Fintech_Provider implementation.
 */
class SDI_Fintech {

	/**
	 * Cached resolved provider instance.
	 *
	 * @var SDI_Fintech_Provider|null
	 */
	private static $provider = null;

	/**
	 * Get the active fintech provider.
	 *
	 * A real Phase 2 integration drops in by hooking the
	 * `sdi_fintech_provider` filter and returning an object implementing
	 * SDI_Fintech_Provider — no core file needs to change.
	 *
	 * @return SDI_Fintech_Provider
	 */
	public static function get_provider() {
		if ( null !== self::$provider ) {
			return self::$provider;
		}

		/**
		 * Filters the active fintech provider implementation.
		 *
		 * @param SDI_Fintech_Provider $provider Defaults to the null provider.
		 */
		$provider = apply_filters( 'sdi_fintech_provider', new SDI_Fintech_Null_Provider() );

		if ( ! ( $provider instanceof SDI_Fintech_Provider ) ) {
			$provider = new SDI_Fintech_Null_Provider();
		}

		self::$provider = $provider;

		return self::$provider;
	}

	/**
	 * Whether the fintech module is enabled in Settings at all. Kept
	 * separate from is_connected() so an admin can toggle the whole
	 * module's UI off without needing a real provider first.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$modules = get_option( 'sdi_enabled_modules', array() );

		return ! empty( $modules['fintech'] );
	}
}
