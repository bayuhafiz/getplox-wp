<?php
	global $prod_cat_id;
	global $prod_cat_name;
	global $prod_cat_description;
	$title = get_field('cat_full_title', 'product_cat_' . $prod_cat_id);
	$subtitle = get_field('cat_sub_title', 'product_cat_' . $prod_cat_id);
	$btn = get_field('cat_btn_text', 'product_cat_' . $prod_cat_id);
	$url = get_field('cat_btn_url', 'product_cat_' . $prod_cat_id);
	$description = $prod_cat_description;
	$name = $prod_cat_name;

?>
<div class="subcategory-content">
	<h2><?php if(!empty($title)): echo $title; else: echo $name; endif; ?></h2>
	<h3><?php echo $subtitle; ?></h3>
	<p><?php echo $description; ?></p>
	<a href="<?php if(!empty($url)): echo $url; else: echo get_term_link( $prod_cat_id ,'product_cat'); endif; ?>" class="main-btn">
		<?php
			if(empty($btn)){
				_e('Shop ', 'plox');
				echo $prod_cat_name;
			} else {
				echo $btn;
			}
		?>
	</a>
</div>
