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
 * @package     WC-Shipwire/Product
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Shipwire Product Class
 *
 * Handles inventory sync with Shipwire
 *
 * @since 1.0
 */
class WC_Shipwire_Product {


	/**
	 * Updates inventory counts from Shipwire for products
	 *
	 * @since 1.0
	 * @param array|int $product_ids product post ID or array of product post IDs
	 * @return bool true if inventory updates were successful, false otherwise
	 */
	public static function update_inventory( $product_ids ) {

		// handle single product ID
		if ( ! is_array( $product_ids ) ) {
			$product_ids = array( $product_ids );
		}

		// get skus for each product
		$skus = self::get_skus( $product_ids );

		try {

			// get shipwire inventory given array of SKUs
			$inventory = wc_shipwire()->get_api()->get_product_inventory( $skus );

			// iterate through SKUs and set stock
			foreach ( $inventory as $sku => $stock ) {

				if ( ! $sku || empty( $stock ) ) {
					throw new Exception( __( 'Inventory Update: item SKU or item inventory is empty.', 'woocommerce-shipwire' ) );
				}

				// get product for SKU
				$product = self::get_product_by_sku( $sku );

				// skip SKUs provided by shipwire that don't exist in WC
				if ( ! is_object( $product ) ) {
					continue;
				}

				// update the product's stock values
				self::update_stock( $product, $stock );

				do_action( 'wc_shipwire_inventory_updated', $product, $stock );
			}

		} catch ( Exception $e ) {

			wc_shipwire()->log( $e->getMessage() );

			return false;
		}

		return true;
	}


	/**
	 * Update a product's stock values.
	 *
	 * @since 2.0.0
	 * @param \WC_Product $product the product object
	 * @param array $stock {
	 *     the updated stock values
	 *
	 *     @type int $good the amount of available stock
	 *     @type int $pending the amount of stock pending shipping
	 * }
	 */
	public static function update_stock( WC_Product $product, $stock ) {

		$stock = wp_parse_args( $stock, array(
			'good'    => 0,
			'pending' => 0,
		) );

		/**
		 * Fire before a product's stock has been updated.
		 *
		 * @since 2.0.0
		 * @param \WC_Product $product the product object
		 * @param array $stock {
		 *     the updated stock
		 *
		 *     @type int $good the amount of available stock
		 *     @type int $pending the amount of stock currently in transit and will soon be available to ship
		 * }
		 */
		do_action( 'wc_shipwire_before_product_stock_updated', $product, $stock );

		// if a variation has never had stock assigned, this ensures using $product->set_stock() will work correctly
		if ( $product->is_type( 'variation' ) ) {
			$product->variation_has_stock = true;
		}

		// 'good' inventory in shipwire is considered to be ready to ship immediately
		$stock_amount = (int) $stock['good'];

		// the admin can optionally set 'pending' inventory to be considered as valid stock in WC
		// 'pending' inventory in shipwire means inventory which will arrive shortly (e.g. currently in transit) and will then be available to ship
		if ( 'yes' === get_option( 'wc_shipwire_include_pending_inventory' ) ) {
			$stock_amount += (int) $stock['pending'];
		}

		// set the product's stock
		$product->set_stock( $stock_amount );

		// update shipwire inventory meta
		update_post_meta( $product->id, '_wc_shipwire_inventory', $stock_amount );

		/**
		 * Fire after a product's stock has been updated.
		 *
		 * @since 2.0.0
		 * @param \WC_Product $product the product object
		 * @param array $stock {
		 *     the updated stock
		 *
		 *     @type int $good the amount of available stock
		 *     @type int $pending the amount of stock pending shipping
		 * }
		 */
		do_action( 'wc_shipwire_after_product_stock_updated', $product, $stock );
	}


	/**
	 * Get the SKUs associated with a product
	 *
	 * @since 1.0
	 * @param array $product_ids array of product IDs to return SKUs for
	 * @return array
	 */
	private static function get_skus( $product_ids ) {

		$skus = array();

		foreach ( $product_ids as $product_id ) {

			$product = wc_get_product( $product_id );

			if ( $product->is_type( 'variable' ) ) {

				foreach ( $product->get_children() as $child_id ) {

					$variation = $product->get_child( $child_id );

					$skus[] = $variation->get_sku();
				}

			} else {

				$skus[] = $product->get_sku();
			}
		}

		return $skus;
	}


	/**
	 * Returns the product object for a given SKU
	 *
	 * @since 1.0
	 * @param string $sku the desired product SKU
	 * @return \WC_Product|false
	 */
	private static function get_product_by_sku( $sku ) {

		if ( ! $sku ) {
			return null;
		}

		$product_id = wc_get_product_id_by_sku( $sku );

		return wc_get_product( $product_id );
	}


	/**
	 * Determine if a product's inventory is managed by Shipwire
	 *
	 * @since 1.0
	 * @param object|int $product the WC_Product object or product post ID
	 * @return bool true if is managed, false if not managed
	 */
	public static function is_shipwire_managed( $product ) {

		if ( ! is_object( $product ) ) {
			$product = wc_get_product( $product );
		}

		return ( 'yes' === $product->wc_shipwire_manage_stock );
	}


	/**
	 * Updates inventory for all products that have inventory managed by Shipwire
	 *
	 * @since 1.0
	 */
	public static function update_all_inventory() {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => 'product',
			'nopaging'    => true,
			'meta_key'    => '_wc_shipwire_manage_stock',
			'meta_value'  => 'yes',
		);

		// get the SKUs for product managed by shipwire
		$query = new WP_Query( $query_args );

		if ( $query->post_count ) {

			$success = self::update_inventory( $query->posts );

			if ( $success ) {

				wc_shipwire()->log_update( sprintf( _n( 'Updated inventory for %d product.', 'Updated inventory for %d products.', $query->post_count, 'woocommerce-shipwire' ), $query->post_count ) );

				do_action( 'wc_shipwire_all_inventory_updated', $query->posts );
			}
		}

		return $query->post_count;
	}


} // end \WC_Shipwire_Product class
