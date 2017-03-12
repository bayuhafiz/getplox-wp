<?php
/**
 * The static page template.
 *
 *
 * @package WordPress
 * @subpackage plox
 * @since plox 1.0
 */

get_header(); the_post(); ?>

	<main class="page-content">

	<?php
		$show = get_field('show_main_banner');
		$alt = get_field('alternative_banner_title');
		if($show === true):
	?>

		<section class="main-banner" style="background-image:url('<?php the_post_thumbnail_url(); ?>');">
			<div class="container-fluid">
				<?php woocommerce_breadcrumb(); ?>
				<h1><?php if (!empty($alt)): echo $alt; else: the_title(); endif; ?></h1>

			</div>
		</section>

	<?php endif; ?>

	<?php if (get_the_content()): ?>

		<section class="default-content light-section">
			<div class="container-fluid">
				<div class="content <?php if (get_field('short_content') === true): echo 'short-content'; endif; ?>">
					<?php the_content(); ?>
				</div>
			</div>
		</section>

	<?php endif; ?>

<?php if (have_rows('page_content')): ?>

	<?php while (have_rows('page_content')): the_row(); ?>

		<?php

			if (get_row_layout() === 'big_banner_section'):

				$pos = get_sub_field('big_banner_con_pos');
				$title = get_sub_field('big_banner_title');
				$small = get_sub_field('big_banner_s_t');
				$bg_imgID = get_sub_field('big_banner_bg');
				$bg = wp_get_attachment_image($bg_imgID, 'banner-bg');
				$dark_font = get_sub_field('big_banner_color');
				$capt_bool = get_sub_field('big_banner_cap_sh');
				$capt_txt = get_sub_field('big_banner_caption');

				$el_img_bool = get_sub_field('big_banner_show_img');
				$el_img_width = get_sub_field('big_banner_img_width');
				$el_imgID = get_sub_field('big_banner_img_el');
				$el_img = wp_get_attachment_image($el_imgID, 'full-width');

				$cap_l = get_sub_field('big_banner_cap_l');
				$cap_r = get_sub_field('big_banner_cap_r');
				$cap_t = get_sub_field('big_banner_cap_t');
				$cap_ali = get_sub_field('caption_text_aligment');
				$btn = get_sub_field('big_banner_btn');
				$url = get_sub_field('big_banner_url');
				$btn_color = get_sub_field('big_banner_btn_color');
				$btn_caption = get_sub_field('big_banner_btn_caption');
				$imgID = get_sub_field('big_banner_btn_img');
				$img = wp_get_attachment_image($imgID, 'banner-btn-img');


		?>
			<section class="big-banner content-<?php echo $pos; ?> <?php if ( $dark_font === true ): echo 'dark-text'; endif; ?>" >

					<?php echo $bg; ?>

					<div class="content <?php if ( $small === true ): echo 'smaller-title'; endif; ?>">

					<?php if (!empty($title)): ?>

						<h2><?php echo $title; ?></h2>

					<?php endif; ?>

					<?php if ($el_img_bool === true): ?>

						<figure class="after-title-img" <?php if(!empty($el_img_width)): ?> style="width:<?php echo $el_img_width; ?>%;" <?php endif; ?>>
							<?php echo $el_img; ?>
						</figure>

					<?php endif; ?>

						<div class="btn-wrapper">

						<?php

							if ($pos == 'right'):

								if (!empty($btn_caption)): echo '<p class="btn-wrapper-caption">' . $btn_caption .'</p>'; endif;

								if (!empty($img)): echo $img; endif;

							endif;

						?>

						<?php if (!empty($btn)): ?>

							<a class="main-btn btn-<?php echo $btn_color; ?>" href="<?php echo $url; ?>"><?php echo $btn; ?></a>

						<?php endif; ?>

						<?php

							if ($pos == 'left'):

								if (!empty($img)): echo $img; endif;

								if (!empty($btn_caption)): echo '<p class="btn-wrapper-caption">' . $btn_caption .'</p>'; endif;

							endif;

						?>

						</div>

					</div>

				<?php if ( $capt_bool === true ): ?>

					<div class="banner-caption text-<?php echo $cap_ali; ?>" style="
						<?php if(!empty($cap_t)): ?>
						top:<?php echo $cap_t; ?>%;
						<?php endif; ?>
						<?php if(!empty($cap_l)): ?>
						left:<?php echo $cap_l; ?>%;
						<?php endif; ?>
						<?php if(!empty($cap_r)): ?>
						right:<?php echo $cap_r; ?>%;
						<?php endif; ?>
					">
						<?php echo $capt_txt; ?>
					</div>

				<?php endif; ?>


			</section>

		<?php

			elseif ( get_row_layout() === 'half_boxes_section' ):

		?>

			<section class="half-boxes container-fluid">

			<?php

				if ( have_rows('half_box_row') ):

			?>

				<div class="row">

				<?php

					while ( have_rows('half_box_row') ) : the_row();

						$title = get_sub_field('half_box_title');
						$subtitle = get_sub_field('half_box_subtitle');
						$btn = get_sub_field('half_box_btn');
						$url = get_sub_field('half_box_url');
						$imgID = get_sub_field('half_box_img');
						$img = wp_get_attachment_image_src($imgID, 'full');

				?>

					<div class="half-box-single" style="background-image:url('<?php echo $img[0]; ?>');">

						<div class="content">

						<?php if (!empty($title)): ?>
							<h3><?php echo $title; ?>
							<?php if (!empty($subtitle)): ?>
								<span><?php echo $subtitle; ?></span>
							<?php endif; ?>
							</h3>
						<?php endif; ?>

						<?php if (!empty($btn)): ?>
							<a class="main-btn" href="<?php echo $url; ?>"><?php echo $btn; ?></a>
						<?php endif; ?>

						</div>

					</div>

				<?php endwhile; ?>

				</div>

			<?php endif; ?>

			</section>

		<?php

			elseif ( get_row_layout() === 'map_section' ):
				$content = get_sub_field('map_section_content');
				$content_b = get_sub_field('map_section_content_bottom');
				$location = get_sub_field('map_section_map');
		?>

			<section class="map-section light-section">

			<?php if( !empty($content) || !empty($content_b) ): ?>
				<div class="container-fluid">
					<div class="col-content">

					<?php if( !empty($content) ): ?>
						<div class="content-top">
							<?php echo $content; ?>
						</div>
					<?php endif; ?>

					<?php if( !empty($location) ): ?>

						<div class="col-map mobile-map">
							<div class="acf-map">
								<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
							</div>
						</div>

					<?php endif; ?>

					<?php if( !empty($content_b) ): ?>
						<div class="content-bottom">
							<?php echo $content_b; ?>
						</div>
					<?php endif; ?>
					</div>

				<?php endif; ?>

				<?php if( !empty($location) ): ?>

					<div class="col-map desktop-map">
						<div class="acf-map">
							<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>"></div>
						</div>
					</div>

				<?php endif; ?>
				</div>


			</section>

		<?php endif; ?>

	<?php endwhile; ?>

<?php endif; ?>

	</main>

<?php get_footer(); ?>
