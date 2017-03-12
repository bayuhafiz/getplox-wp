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
 * Handle a Shipwire API order response.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Order_Response extends WC_Shipwire_API_Response {


	/**
	 * Get the returned order information.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_orders() {

		$orders = array();

		if ( isset( $this->resource->orderNo ) ) {

			$orders[] = new WC_Shipwire_API_Order( $this->resource );

		} else {

			foreach ( $this->resource->items as $item ) {

				$data = $item->resource;

				$orders[] = new WC_Shipwire_API_Order( $data );
			}
		}

		return $orders;
	}


	/**
	 * Get the newly created order.
	 *
	 * @since 2.0.0
	 * @return \WC_Shipwire_API_Order the API order object
	 */
	public function get_single_order() {

		$orders = $this->get_orders();

		return ! empty( $orders ) ? $orders[0] : null;
	}


}
