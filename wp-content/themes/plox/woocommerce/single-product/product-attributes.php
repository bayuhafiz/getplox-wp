<?php
/**
 * Product attributes
 *
 * Used by list_attributes() in the products class.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-attributes.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$has_row    = false;
$alt        = 1;
$attributes = $product->get_attributes();

ob_start();

?>
<table class="shop_attributes">
	<?php if ( !empty($product->sku) ): ?>
		<tr class="info-sku">
			<th><?php _e( 'SKU', 'plox' ) ?></th>
			<td class="product_sku"><?php echo $product->sku; ?></td>
		</tr>
	<?php endif; ?>

	<?php foreach ( $attributes as $attribute ) :
		if ( empty( $attribute['is_visible'] ) || ( $attribute['is_taxonomy'] && ! taxonomy_exists( $attribute['name'] ) ) ) {
			continue;
		} else {
			$has_row = true;
		}
		?>
		<tr class="<?php if ( ( $alt = $alt * -1 ) == 1 ) echo 'alt'; ?>">
			<th><?php echo wc_attribute_label( $attribute['name'] ); ?></th>
			<td><?php
				if ( $attribute['is_taxonomy'] ) {

					$values = wc_get_product_terms( $product->id, $attribute['name'], array( 'fields' => 'names' ) );
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

				} else {

					// Convert pipes to commas and display values
					$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
					echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );

				}
			?></td>
		</tr>
	<?php endforeach; ?>

	<?php if ( $product->enable_dimensions_display() ) : ?>

		<?php //variables
			$length= get_post_meta($product->id, '_length');
			$width = get_post_meta($product->id, '_width');
			$height = get_post_meta($product->id, '_height');
			$dim_unit = get_option( 'woocommerce_dimension_unit' )
		?>
		<?php if ( $product->has_dimensions() ) : $has_row = true; ?>
			<tr class="<?php if ( ( $alt = $alt * -1 ) === 1 ) echo 'alt'; ?>">
				<th><?php _e( 'Dimensions', 'plox' ) ?></th>
				<td class="product_dimensions"><?php echo $length[0] . ' ' . $dim_unit . ' x ' . $width[0] . ' ' . $dim_unit . ' x ' . $height[0] . ' ' . $dim_unit; ?></td>
			</tr>
		<?php endif; ?>

		<?php
		if(get_option( 'woocommerce_weight_unit' ) === 'g'){
 			$output = 'grams';
		} else{
			$output = get_option( 'woocommerce_weight_unit' );
		}

		if ( $product->has_weight() ) : $has_row = true; ?>
			<tr class="<?php if ( ( $alt = $alt * -1 ) === 1 ) echo 'alt'; ?>">
				<th><?php _e( 'Weight', 'plox' ) ?></th>
				<td class="product_weight"><?php echo wc_format_localized_decimal( $product->get_weight() ) . ' ' . esc_attr( $output ); ?></td>
			</tr>
		<?php endif; ?>

	<?php endif; ?>

</table>
<?php
if ( $has_row ) {
	echo ob_get_clean();
} else {
	ob_end_clean();
}
