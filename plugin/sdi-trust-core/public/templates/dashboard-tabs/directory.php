<?php
/**
 * Dashboard tab: Directory — link into member-only Directorist listings.
 *
 * Deliberately does not touch Directorist's own templates or data; this is
 * a link-out only, per the integration rules in the project brief.
 *
 * @package SDI_Trust_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h2 class="sdi-panel__title"><?php esc_html_e( 'Travel Directory', 'sdi-trust-core' ); ?></h2>

<?php if ( ! SDI_Public::is_directorist_active() ) : ?>
	<p class="sdi-empty"><?php esc_html_e( 'The travel directory is not connected yet.', 'sdi-trust-core' ); ?></p>
<?php else : ?>
	<p><?php esc_html_e( 'Browse the full curated travel directory, including any member-only listings.', 'sdi-trust-core' ); ?></p>
	<p>
		<a class="sdi-btn sdi-btn--primary" href="<?php echo esc_url( SDI_Public::get_directory_url() ); ?>">
			<?php esc_html_e( 'Open the Travel Directory', 'sdi-trust-core' ); ?>
		</a>
	</p>
<?php endif; ?>
