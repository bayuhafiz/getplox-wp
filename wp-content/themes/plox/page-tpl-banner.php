<?php
/**
 * Template Name: Banner Page
 * The statict page template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */

get_header(); the_post(); ?>

	<main class="banner-page-content">

	<?php
		$show = get_field('show_banner_tpl');
		$alt = get_field('banner_tpl_alt');
		$btn = get_field('banner_tpl_btn');
		$url = get_field('banner_tpl_url');
		if($show === true):
	?>

		<section class="page-caption">
			<div class="container-fluid">
				<?php woocommerce_breadcrumb(); ?>

				<h1><?php if (!empty($alt)): echo $alt; else: the_title(); endif; ?></h1>

			<?php if (!empty($btn)): ?>
				<a href="<?php echo $url; ?>" class="main-btn long-btn"><?php echo $btn; ?></a>
			<?php endif; ?>

			</div>

		</section>

	<?php endif; ?>


	<?php

		if ( have_rows('content_block_row') ):

	?>
		<section class="page-banner">

			<?php the_post_thumbnail(); ?>

		<?php

			while ( have_rows('content_block_row') ) : the_row();

				$content = get_sub_field('block_content');
				$light = get_sub_field('block_light_txt');
				$width = get_sub_field('block_width');
				$pos_l = get_sub_field('block_pos_l');
				$pos_r = get_sub_field('block_pos_r');
				$pos_t = get_sub_field('block_pos_t');
				$pos_b = get_sub_field('block_pos_b');
				$full_img = get_sub_field('full_width_images');

		?>

			<div class="content-block <?php if ( $light === true): echo 'light-block'; endif; if ( $full_img === true): echo 'full-width-images'; endif;?>" style="
				<?php if(!empty($pos_t)): ?>
				top:<?php echo $pos_t; ?>%;
				<?php endif; ?>
				<?php if(!empty($pos_l)): ?>
				left:<?php echo $pos_l; ?>%;
				<?php endif; ?>
				<?php if(!empty($pos_r)): ?>
				right:<?php echo $pos_r; ?>%;
				<?php endif; ?>
				<?php if(!empty($pos_b)): ?>
				bottom:<?php echo $pos_b; ?>%;
				<?php endif; ?>
				<?php if(!empty($width)): ?>
				width:<?php echo $width; ?>%;
				<?php endif; ?>">

				<?php echo $content; ?>
			</div>

		<?php endwhile; ?>

		</section>

	<?php endif; ?>

	<?php if (get_the_content()): ?>

		<section class="default-content light-section">

			<div class="content">
				<?php the_content(); ?>
			</div>

		</section>

	<?php endif; ?>

	</main>

<?php get_footer(); ?>
