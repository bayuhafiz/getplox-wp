<?php
	if(!is_subcategory()):
		$current_cat = get_queried_object();
		$cat_id = $current_cat->term_id;
?>

	<?php if( have_rows('category_chosen', 'product_cat_' . $cat_id) ): ?>

		<div class="promo-products-wrapper">

		<?php
			while( have_rows('category_chosen', 'product_cat_' . $cat_id) ): the_row();

			$title =  get_sub_field('chosen_prod_title');
			$description =  get_sub_field('chosen_prod_description');
			$link =  get_sub_field('chosen_prod_url');
			$imgID = get_sub_field('chosen_prod_image');
			$img = wp_get_attachment_image($imgID, 'promo-item');
		?>

			<div class="single-item">
				<figure>
					<?php echo $img; ?>
				</figure>
				<h3><?php echo $title; ?></h3>
				<p><?php echo $description; ?></p>
				<a href="<?php echo $link; ?>" class="main-btn"><?php _e('Shop', 'plox');?></a>
			</div>

		<?php endwhile; ?>

		</div>

	<?php endif; ?>

<?php endif; ?>
