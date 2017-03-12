<?php
/**
 * WooCommerce Shipwire
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Shipwire to newer
 * versions in the future. If you wish to customize WooCommerce Shipwire for your
 * needs please refer to http://docs.woothemes.com/document/shipwire/ for more information.
 *
 * @package     WC-Shipwire/Webhooks
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * The product webhook class.
 *
 * @since 2.0.0
 */
class WC_Shipwire_Stock_Webhook extends WC_Shipwire_Webhook {


	/**
	 * Construct the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct( 'stock' );
	}


	/**
	 * Determine if this webhook is enabled.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function enabled() {
		return (bool) apply_filters( 'wc_shipwire_auto_update_inventory', 'yes' === get_option( 'wc_shipwire_auto_update_inventory' ) );
	}


	/**
	 * Process the request after validation.
	 *
	 * @since 2.0.0
	 */
	protected function process_request() {

		$data = $this->get_request_data();

		$product_data = wc_shipwire()->get_api()->get_product_stock( array(
			'id' => $data->productId,
		) );

		$product_id = wc_get_product_id_by_sku( $product_data['sku'] );

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			throw new SV_WC_Plugin_Exception( sprintf( __( 'Product SKU %s not found.', 'woocommerce-shipwire' ), $product_data['sku'] ) );
		}

		WC_Shipwire_Product::update_stock( $product, $product_data );
	}


	/**
	 * Get the request topic.
	 *
	 * The 'stock' webhook doesn't return the topic, so we have to force it so this passes validation.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_topic() {
		return 'stock.transition';
	}


	/**
	 * Get the request body.
	 *
	 * The 'stock' webhook doesn't return a body object, so we have to force it so this passes validation.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function get_request_body() {
		return true;
	}


}
