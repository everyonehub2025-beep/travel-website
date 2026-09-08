<?php
/**
 * Search form.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form role="search" method="get" class="sdi-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="display:flex; gap:0.5em;">
	<label class="screen-reader-text" for="sdi-search-field"><?php esc_html_e( 'Search for:', 'sdi-travel' ); ?></label>
	<input type="search" id="sdi-search-field" class="sdi-form-input" placeholder="<?php esc_attr_e( 'Search…', 'sdi-travel' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" style="flex:1; padding:0.75em 1em; border:1px solid var(--sdi-border); border-radius:6px;" />
	<button type="submit" class="sdi-btn sdi-btn--primary"><?php esc_html_e( 'Search', 'sdi-travel' ); ?></button>
</form>
