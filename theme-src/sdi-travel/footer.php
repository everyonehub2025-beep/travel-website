<?php
/**
 * Site footer: 4 columns — org info + logo, quick links, programs,
 * legal links + newsletter signup.
 *
 * @package SDI_Travel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main>

<footer class="sdi-footer">
	<div class="sdi-container">
		<div class="sdi-footer__grid">
			<div class="sdi-footer__col sdi-footer__col--org">
				<div class="sdi-footer__logo">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<strong><?php bloginfo( 'name' ); ?></strong>
					<?php endif; ?>
				</div>
				<p class="sdi-footer__blurb"><?php echo esc_html( get_theme_mod( 'sdi_footer_blurb', __( 'SDI Travel Trust connects individuals and families with curated travel resources while generating support for academic scholarships and community programs.', 'sdi-travel' ) ) ); ?></p>
				<div class="sdi-footer__social">
					<?php
					$sdi_socials = array(
						'facebook'  => get_theme_mod( 'sdi_social_facebook', '' ),
						'instagram' => get_theme_mod( 'sdi_social_instagram', '' ),
						'linkedin'  => get_theme_mod( 'sdi_social_linkedin', '' ),
						'x'         => get_theme_mod( 'sdi_social_x', '' ),
					);
					foreach ( $sdi_socials as $sdi_network => $sdi_url ) :
						if ( ! $sdi_url ) {
							continue;
						}
						?>
						<a href="<?php echo esc_url( $sdi_url ); ?>" aria-label="<?php echo esc_attr( ucfirst( $sdi_network ) ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( strtoupper( substr( $sdi_network, 0, 1 ) ) ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="sdi-footer__col">
				<h3 class="sdi-footer__heading"><?php esc_html_e( 'Quick Links', 'sdi-travel' ); ?></h3>
				<?php sdi_footer_menu( 'footer-quick-links' ); ?>
			</div>

			<div class="sdi-footer__col">
				<h3 class="sdi-footer__heading"><?php esc_html_e( 'Programs', 'sdi-travel' ); ?></h3>
				<?php sdi_footer_menu( 'footer-programs' ); ?>
			</div>

			<div class="sdi-footer__col">
				<h3 class="sdi-footer__heading"><?php esc_html_e( 'Legal', 'sdi-travel' ); ?></h3>
				<?php sdi_footer_menu( 'footer-legal' ); ?>

				<div class="sdi-footer__newsletter">
					<h3 class="sdi-footer__heading"><?php esc_html_e( 'Stay Informed', 'sdi-travel' ); ?></h3>
					<?php echo do_shortcode( '[sdi_newsletter_form]' ); ?>
				</div>
			</div>
		</div>

		<div class="sdi-footer__bottom">
			<span>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'sdi-travel' ); ?>
			</span>
			<span>
				<?php
				$sdi_email = get_theme_mod( 'sdi_contact_email', '' );
				$sdi_phone = get_theme_mod( 'sdi_contact_phone', '' );
				echo esc_html( trim( $sdi_email . ( $sdi_phone ? '  ·  ' . $sdi_phone : '' ) ) );
				?>
			</span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
