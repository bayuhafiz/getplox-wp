<?php
	global $product;
	$terms = get_the_terms( $post->ID , sanitize_title( 'pa_colours' ) );
	if(!empty($terms) && $product->is_type( 'variable' ) ):
?>

<ul class="colours-variables">
	<?php
		foreach($terms as $term):
		$term_id =  $term->term_id;
		$thumbnail_id = get_woocommerce_term_meta( $term_id,'', 'phoen_color', true );
	?>
	<li class="<?php echo 'color-' . $term->slug; ?>" style="background-color:<?php echo $thumbnail_id['pa_colours_swatches_id_phoen_color'][0]; ?>;"><?php echo $term->name; ?></li>
	<?php
		endforeach;
	?>
</ul>

<?php endif; ?>
