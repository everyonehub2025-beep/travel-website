<?php
/**
 * Comments template for News & Resources posts.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
?>
<div id="comments" class="sdi-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="sdi-panel__subtitle">
			<?php
			printf(
				/* translators: %s: comment count */
				esc_html( _n( '%s Comment', '%s Comments', get_comments_number(), 'sdi-travel' ) ),
				esc_html( number_format_i18n( get_comments_number() ) )
			);
			?>
		</h2>
		<ol class="sdi-comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'short_ping' => true ) ); ?>
		</ol>
		<?php the_comments_pagination(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="sdi-empty"><?php esc_html_e( 'Comments are closed.', 'sdi-travel' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</div>
