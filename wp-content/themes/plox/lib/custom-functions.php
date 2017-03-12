<?php

function localize_scripts() {
	$params = array(
		'siteUrl' => home_url(),
		'templateUrl' => get_template_directory_uri(),
		'stylesheetUrl' => get_stylesheet_directory_uri(),
		'ajaxUrl' => site_url() . '/wp-admin/admin-ajax.php'
	);

	$params =  apply_filters('sitevars', $params);
	wp_localize_script('script', 'SiteVars', $params);
}


// GOOGLE API KEY

function my_acf_init() {
	$key = get_field('google_maps_key', 'options');
	acf_update_setting('google_api_key', $key);
}

add_action('acf/init', 'my_acf_init');


function is_subcategory($cat_id = null) {
    if (is_tax('product_cat')) {

        if (empty($cat_id)){
            $cat_id = get_queried_object_id();
        }

        $cat = get_term(get_queried_object_id(), 'product_cat');
        if ( empty($cat->parent) ){
            return false;
        }else{
            return true;
        }
    }
    return false;
}

// change default mobile breakpoint
add_filter('woocommerce_style_smallscreen_breakpoint','woo_custom_breakpoint');

function woo_custom_breakpoint($px) {
  $px = '767px';
  return $px;
}

// ADD WOOCOMMERCE THEME SUPPORT
add_theme_support('woocommerce');

// GET CART TOTAL
function shop_get_cart_total() {

    global $woocommerce;

    return $woocommerce->cart->cart_contents_count;

}

// GET CART URL
function shop_get_cart_url() {

    global $woocommerce;

    echo $woocommerce->cart->get_cart_url();

}

// Remove shop from breadcrumbs trail
function my_remove_shop_from_breadcrumbs( $trail ) {
    unset( $trail['shop'] );
    return $trail;
}
add_filter( 'wpex_breadcrumbs_trail', 'my_remove_shop_from_breadcrumbs', 20 );

// Change Breadcrumb Url
add_filter( 'woocommerce_breadcrumb_defaults', 'jk_change_breadcrumb_delimiter' );
function jk_change_breadcrumb_delimiter( $defaults ) {
	// Change the breadcrumb delimeter from '/' to '>'
	$defaults['delimiter'] = ' <span class="separator">/</span> ';
	return $defaults;
}

//Remove Sidebar
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar');


//Remove Related Products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

// CHANGE Meta Position
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 6);

// CHANGE Short description Position
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 40);

// CHANGE Single add to cart Position
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 15);


// REMOVE FROM TABS
add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );

function woo_remove_product_tabs( $tabs ) {
	unset( $tabs['description'] );        // Remove the description tab
	unset( $tabs['additional_information'] );      // Remove the additional information tab
unset($tabs['reviews']);

  return $tabs;

}

// Move Additional info
function woocommerce_template_product_additional() {
woocommerce_get_template( 'single-product/tabs/additional-information.php' );
}
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_product_additional', 50 );

// Add social share
function woocommerce_template_social_popup() {
woocommerce_get_template( 'custom-social-popup.php' );
}
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_social_popup', 35 );

// Move Description
function woocommerce_template_product_description() {
woocommerce_get_template( 'single-product/tabs/description.php' );
}
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_product_description', 25 );

// Insert Product cart meta (color swatches plugin debug)
function action_woocommerce_before_add_to_cart_button(  ) {
	echo get_template_part( 'woocommerce/single-product/add-to-cart/cart', 'meta' );
};
add_action( 'woocommerce_before_add_to_cart_button', 'action_woocommerce_before_add_to_cart_button', 10, 0 );

//Remove Subcategory List after title description
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10);

//Remove Subcategory List Title
remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10);

//Add description to subcategories list
function action_subcategory_description(  ) {
	woocommerce_get_template( 'subcategory-description.php' );
};
add_action( 'woocommerce_before_subcategory', 'action_subcategory_description', 25 );

//Remove Subcategory List default link
remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10);
remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10);

//Add manually choosen items on the top of Parent Category Page
function action_chosen_items(  ) {
	woocommerce_get_template( 'chosen-items.php' );
};
add_action( 'woocommerce_before_shop_loop', 'action_chosen_items', 5 );

//Add Category Thumbnail wrapper
function action_thumb_wrap_open(  ) {
	echo '<div class="cat-img-wrapper">';
};
add_action( 'woocommerce_before_subcategory_title', 'action_thumb_wrap_open', 5 );

function action_thumb_wrap_close(  ) {
	echo '</div>';
};
add_action( 'woocommerce_before_subcategory_title', 'action_thumb_wrap_close', 15 );

//Remove Archive Results Count
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20);

//Add product filtering navigation
function action_shop_filters(  ) {
	woocommerce_get_template( 'shop-filters.php' );
};
add_action( 'woocommerce_before_shop_loop', 'action_shop_filters', 20);

//Custom Button types text
add_filter( 'woocommerce_product_add_to_cart_text' , 'custom_woocommerce_product_add_to_cart_text' );
function custom_woocommerce_product_add_to_cart_text() {
	global $product;

	$product_type = $product->product_type;

	switch ( $product_type ) {
		case 'external':
			return __( 'Buy product', 'woocommerce' );
		break;
		case 'grouped':
			return __( 'View products', 'woocommerce' );
		break;
		case 'simple':
			return __( 'Add to cart', 'woocommerce' );
		break;
		case 'variable':
			return __( 'Add to cart', 'woocommerce' );
		break;
		default:
			return __( 'Read more', 'woocommerce' );
	}

}

//* Change the Add To Cart Link
add_filter( 'woocommerce_loop_add_to_cart_link', 'custom_add_product_link' );
function custom_add_product_link( $link ) {
 global $product;
 $product_id = $product->id;
 $product_sku = $product->get_sku();
 $link = '<a href="'.get_permalink().'" rel="nofollow" data-product_id="'.$product_id.'" data-product_sku="'.$product_sku.'" data-quantity="1" class="button add_to_cart_button product_type_variable">'.custom_woocommerce_product_add_to_cart_text().'</a>';
 return $link;
}

// Modify Product meta to archive loop
function action_woocommerce_archive_meta(  ) {
	echo get_template_part( 'woocommerce/single-product/add-to-cart/cart', 'meta' );
};
add_action( 'woocommerce_after_shop_loop_item_title', 'action_woocommerce_archive_meta', 10);

function action_woocommerce_colours_variables(  ) {
	woocommerce_get_template( 'color-variables.php' );
};
add_action( 'woocommerce_after_shop_loop_item_title', 'action_woocommerce_colours_variables', 5);

function action_woocommerce_sku_loop(  ) {
	global $product;

	if(!empty($product->sku)){
		echo '<h6 class="product-sku">' . $product->sku . '</h6>';
	}
};
add_action( 'woocommerce_after_shop_loop_item_title', 'action_woocommerce_sku_loop', 4);

remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);


// Modify loop product thumb
function action_add_product_img_size(){
	echo '<figure class="product-thumb">' . woocommerce_get_product_thumbnail('list-item') . '</figure>';
};
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
add_action( 'woocommerce_before_shop_loop_item_title', 'action_add_product_img_size', 10);


// Move order summary from checkout order review
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );

add_action( 'custom_order_review', 'woocommerce_order_review', 10);


// remove specificate checkout fields in Woocommerce
add_filter( 'woocommerce_checkout_fields' , 'alter_woocommerce_checkout_fields' );
function alter_woocommerce_checkout_fields( $fields ) {
     unset($fields['order']['order_comments']);
     unset($fields['billing']['billing_company']); // remove the option to enter in a company
     return $fields;
}

// Change order billing fields

add_filter("woocommerce_checkout_fields", "order_fields");

function order_fields($fields) {

    $order = array(
    	"billing_country",
        "billing_first_name",
        "billing_last_name",
        "billing_address_1",
        "billing_address_2",
        "billing_postcode",
        "billing_city",
        "billing_state",

        "billing_phone",
        "billing_email"

    );
    foreach($order as $field)
    {
        $ordered_fields[$field] = $fields["billing"][$field];
    }

    $fields["billing"] = $ordered_fields;
    return $fields;

}

// Change order shipping fields

add_filter("woocommerce_checkout_fields", "order_shipping_fields");

function order_shipping_fields($fields) {

    $order = array(
    	"shipping_country",
        "shipping_first_name",
        "shipping_last_name",
        "shipping_address_1",
        "shipping_address_2",
        "shipping_postcode",
        "shipping_city",
        "shipping_state",

    );
    foreach($order as $field)
    {
        $ordered_fields[$field] = $fields["shipping"][$field];
    }

    $fields["shipping"] = $ordered_fields;
    return $fields;

}

function sv_unrequire_wc_phone_field( $fields ) {
    $fields['billing_postcode']['required'] = true;
    return $fields;
}
add_filter( 'woocommerce_billing_fields', 'sv_unrequire_wc_phone_field', 1, 1 );

//make shipping fields not required in checkout
add_filter( 'woocommerce_shipping_fields', 'wc_npr_filter_shipping_fields', 10, 1 );
function wc_npr_filter_shipping_fields( $address_fields ) {
    $address_fields['shipping_country']['required'] = false;
    $address_fields['shipping_first_name']['required'] = false;
    $address_fields['shipping_last_name']['required'] = false;
    $address_fields['shipping_address_1']['required'] = false;
    $address_fields['shipping_address_2']['required'] = false;
    $address_fields['shipping_postcode']['required'] = false;
    $address_fields['shipping_city']['required'] = false;
    $address_fields['shipping_state']['required'] = false;
        return $address_fields;
}

// Change field labels

add_filter( 'woocommerce_default_address_fields' , 'override_default_address_fields' );
function override_default_address_fields( $address_fields ) {


    $address_fields['city']['label'] = __('City', 'woocommerce');

    $address_fields['phone']['label'] = __('Telephone Number', 'woocommerce');
    $address_fields['phone']['required'] = true;

    $address_fields['address_1']['label'] = __('Address Line 1', 'woocommerce');
    $address_fields['address_2']['label'] = __('Address Line 2', 'woocommerce');
    $address_fields['address_2']['required'] = false;

    $address_fields['postcode']['label'] = __('Zip/Postal Code', 'woocommerce');


    return $address_fields;
}

// Add privacy policy to checkout

add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

function custom_override_checkout_fields( $fields ) {

	$descr = get_field('privacy_page_descr', 'options');
	$link_txt = get_field('privacy_page_txt', 'options');
	$link_url = get_field('privacy_page_url', 'options');

     $fields['billing']['billing_policy'] = array(
		'type'          => 'checkbox',
		'label'     => __($descr . '<a href="' . $link_url . '" target="_blank">' . $link_txt . '</a>', 'woocommerce'),
		'required'  => false,
		'class'     => array('input-checkbox'),
		'clear'     => false
     );

     return $fields;
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Privacy Policy From Checkout Form').':</strong> ' . get_post_meta( $order->id, '_billing_policy', true ) . '</p>';
}

