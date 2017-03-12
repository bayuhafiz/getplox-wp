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
 * Handle a Shipwire API order request.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Order_Request extends WC_Shipwire_API_Request {


	/**
	 * Construct the request.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->path = '/orders';
	}


	/**
	 * Get a list of orders from Shipwire.
	 *
	 * @since 2.0.0
	 * @param array $args {
	 *     Optional. The orders request args.
	 *
	 *     @see WC_Shipwire_API::get_orders for values.
	 * }
	 */
	public function get_orders( $args = array() ) {

		$args = wp_parse_args( $args, array(
			'transaction_id' => '',
			'limit'          => 20,
			'expand'         => 'all',
		) );

		$this->params = array(
			'limit'  => $args['limit'],
			'expand' => $args['expand'],
		);

		if ( $args['transaction_id'] ) {
			$this->params['transactionId'] = $args['transaction_id'];
		}

		$this->method = 'GET';
	}


	/**
	 * Get an order from Shipwire.
	 *
	 * @since 2.0.0
	 * @param int $order_id the WooCommerce order ID
	 */
	public function get_order( $order_id ) {

		if ( $shipwire_id = get_post_meta( $order_id, '_wc_shipwire_order_id', true ) ) {
			$order_id = $shipwire_id;
		} else {
			$order_id = 'E' . $order_id;
		}

		$this->params['expand'] = 'all';

		$this->method = 'GET';
		$this->path .= '/' . urlencode( $order_id );
	}


	/**
	 * Gets the holds associated with an order.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 */
	public function get_holds( $order_id ) {
		$this->get_resource( $order_id, 'holds' );
	}


	/**
	 * Gets the items associated with an order.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 */
	public function get_items( $order_id ) {
		$this->get_resource( $order_id, 'items' );
	}


	/**
	 * Gets the packages associated with an order.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 */
	public function get_packages( $order_id ) {
		$this->get_resource( $order_id, 'trackings' );
	}


	/**
	 * Gets a resource associated with an order.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 * @param string $resource the desired resource
	 */
	protected function get_resource( $order_id, $resource ) {

		$this->method = 'GET';
		$this->path .= '/' . urlencode( $order_id ) . '/' . $resource;
	}


	/**
	 * Processes the WC order data to send to Shipwire.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 */
	public function process_new_order( WC_Order $order ) {

		if ( ! $order instanceof WC_Shipwire_Order ) {
			throw new SV_WC_API_Exception( __( 'Order must be a valid Shipwire order object', 'woocommerce-shipwire' ) );
		}

		$params = array(
			'orderNo'    => $order->get_order_number(),
			'externalId' => $order->id,
			'items'      => array(),
			'options'    => array(
				'currency'         => $order->get_order_currency(),
				'serviceLevelCode' => $order->shipping_code,
				'hold'             => 0,
			),
			'shipTo' => array(
				'email'      => $order->billing_email,
				'name'       => $order->shipping_full_name,
				'address1'   => $order->shipping_address_1,
				'address2'   => $order->shipping_address_2,
				'city'       => $order->shipping_city,
				'state'      => $order->shipping_state,
				'postalCode' => $order->shipping_postcode,
				'country'    => $order->shipping_country,
				'phone'      => $order->billing_phone,
			),
		);

		// Order Items
		foreach ( $order->items as $item ) {

			$params['items'][] = array(
				'sku'      => $item->code,
				'quantity' => $item->quantity,
			);
		}

		$this->params['expand'] = 'all';

		$this->path   = '/orders';
		$this->params = $params;
	}


}
