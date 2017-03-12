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
 * @package     WC-Shipwire/API
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Handle a Shipwire API stock request.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Stock_Request extends WC_Shipwire_API_Request {


	/**
	 * Constructs the stock request.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->method = 'GET';

		$this->params['warehouseArea'] = implode( ',', get_option( 'wc_shipwire_inventory_continents', array() ) );
	}


	/**
	 * Get a product's Shipwire stock.
	 *
	 * @since 2.0.0
	 * @param array $args {
	 *     the product to get. $id will override $sku if set.
	 *
	 *     @type int    $id the product's Shipwire ID
	 *     @type string $sku the product's SKU
	 * }
	 */
	public function get_product_stock( $args ) {

		if ( isset( $args['id'] ) ) {
			$this->params['productId'] = $args['id'];
		} else if ( isset( $args['sku'] ) ) {
			$this->params['sku'] = $args['sku'];
		}

		$this->method = 'GET';
		$this->path   = '/stock';
	}


	/**
	 * Get stock values for a set of products.
	 *
	 * @since 2.0.0
	 * @param array $skus the product SKUs to update
	 */
	public function get_inventory( $skus ) {

		$skus = (array) $skus;

		$this->params['sku'] = implode( ',', $skus );

		$this->method = 'GET';
		$this->path   = '/stock';
	}


}
