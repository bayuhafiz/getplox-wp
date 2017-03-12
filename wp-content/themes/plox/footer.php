<?php
/**
 * The footer for theme.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */
?>

		</section> <!-- /.main -->
		<footer class="main-footer">
			<div class="container-fluid">

			<?php

				if ( have_rows('footer_social_links', 'options') ):

			?>

				<div class="socials-wrapper">

				<?php

					while ( have_rows('footer_social_links', 'options') ) : the_row();

						$bg = get_sub_field('f_social_bg');
						$txt = get_sub_field('f_social_txt');
						$url = get_sub_field('f_social_url');
						$imgID = get_sub_field('f_social_ico');
						$img = wp_get_attachment_image($imgID, 'footer-social');

				?>

					<a target="_blank" href="<?php echo $url; ?>" style="background-color:<?php echo $bg; ?>;">
						<div class="social-content">

							<figure>
								<?php echo $img; ?>
							</figure>

							<span class="caption">
								<?php echo $txt; ?>
							</span>

						</div>
					</a>

				<?php endwhile; ?>

				</div>

			<?php endif; ?>

				<div class="footer-bottom">

						<nav class="footer-nav">
							<?php wp_nav_menu(array("theme_location" => 'footer', 'container' => false)); ?>
						</nav>

						<div class="copyright">
							<p><?php the_field('f_copyright', 'options');?></p>
						</div>

				</div>
			</div>
		</footer>
	</div> <!-- /#page -->
<?php wp_footer(); ?>
</body>
</html>
