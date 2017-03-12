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
 * The order webhook class.
 *
 * @since 2.0.0
 */
class WC_Shipwire_Order_Webhook extends WC_Shipwire_Webhook {


	/**
	 * Construct the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		parent::__construct( 'order' );
	}


	/**
	 * Determine if this webhook is enabled.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function enabled() {
		return (bool) apply_filters( 'wc_shipwire_auto_update_orders', 'yes' === get_option( 'wc_shipwire_auto_update_tracking' ) );
	}


	/**
	 * Process the request after validation.
	 *
	 * @since 2.0.0
	 */
	protected function process_request() {

		$data = $this->get_request_body()->resource;

		// build the API order data
		$order_data = new WC_Shipwire_API_Order( $data );

		// get the WC order object
		$order = wc_get_order( $order_data->get_wc_id() );

		if ( ! $order ) {
			throw new SV_WC_Plugin_Exception( sprintf( __( 'Order %s not found.', 'woocommerce-shipwire' ), $order_data->get_wc_id() ) );
		}

		$order = new WC_Shipwire_Order( $order );

		if ( $order_data->get_id() !== $order->wc_shipwire_order_id ) {
			throw new SV_WC_Plugin_Exception( __( 'Invalid order.', 'woocommerce-shipwire' ) );
		}

		$order->update_shipwire_status( $order_data->get_status() );

		// save any order holds
		$order->update_shipwire_holds( $order_data->get_holds() );

		$order->update_order_meta( 'shipper_full_name', $order_data->get_shipping_company() );

		$order->update_order_meta( 'expected_delivery_date', $order_data->get_delivery_date() );

		$order->update_order_meta( 'tracking_packages', $order_data->get_packages() );

		// add any shipwire item meta
		$order->update_shipwire_items( $order_data->get_items() );
	}


}
