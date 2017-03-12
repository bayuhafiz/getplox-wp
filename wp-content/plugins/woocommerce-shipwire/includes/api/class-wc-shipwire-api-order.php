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
 * A returned Shipwire order from an API response.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Order {

	/** @var object $data the order's API response data */
	protected $data;

	/** @var array the order holds **/
	protected $holds;

	/** @var array the order items **/
	protected $items;

	/** @var array the order packages **/
	protected $packages;


	/**
	 * Constructs the order object.
	 *
	 * @since 2.0.0
	 * @param object $data the Shipwire API order object
	 */
	public function __construct( $data ) {

		$this->data = $data;
	}


	/**
	 * Magic accessor for order data attributes
	 *
	 * @since 2.0.0
	 * @param string $name The attribute name to get.
	 * @return mixed The attribute value
	 */
	public function __get( $name ) {

		return isset( $this->data->$name ) ? $this->data->$name : null;
	}


	/**
	 * Get the Shipwire order ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}


	/**
	 * Get the transaction ID.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_transaction_id() {
		return $this->transactionId;
	}


	/**
	 * Get the order number.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_order_number() {
		return $this->orderNo;
	}


	/**
	 * Get the WooCommerce order ID.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_wc_id() {
		return $this->externalId;
	}


	/**
	 * Get the fulfillment status.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_status() {
		return $this->status;
	}


	/**
	 * Get the warehouse.
	 *
	 * @since 2.0.0
	 * @return int
	 */
	public function get_warehouse() {

		$warehouse = $this->options->resource;

		return $warehouse->warehouseId;
	}


	/**
	 * Get the company name that this will be shipped from.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_shipping_company() {

		$ship_to = $this->shipFrom->resource;

		return isset( $ship_to->company ) ? $ship_to->company : '';
	}


	/**
	 * Get the shipping event dates.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_event_dates() {

		return $this->events->resource;
	}


	/**
	 * Get the expected delivery date.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_delivery_date() {

		$dates = $this->get_event_dates();

		return isset( $dates->expectedDate ) ? $dates->expectedDate : '';
	}


	/**
	 * Get the hold information, if any.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_holds() {

		if ( is_array( $this->holds ) ) {
			return $this->holds;
		}

		$holds = $this->get_resource( 'holds' );

		$this->holds = array();

		foreach ( $holds as $item ) {

			$data = $item->resource;

			$this->holds[] = $data->description;
		}

		return $this->holds;
	}


	/**
	 * Get the order items.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_items() {

		if ( is_array( $this->items ) ) {
			return $this->items;
		}

		$items = $this->get_resource( 'items' );

		$this->items = array();

		foreach ( $items as $item ) {

			$data = $item->resource;

			$this->items[ $data->sku ] = array(
				'sku'            => $data->sku,
				'quantity'       => $data->quantity,
				'serial_numbers' => $this->get_item_serial_numbers( $data ),
			);
		}

		return $this->items;
	}


	/**
	 * Get the shipped package information, if any.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_packages() {

		if ( is_array( $this->packages ) ) {
			return $this->packages;
		}

		$packages = $this->get_resource( 'packages' );

		$this->packages = array();

		foreach ( $packages as $item ) {

			$data = $item->resource;

			$this->packages[ $data->id ] = array(
				'carrier'         => $data->carrier,
				'tracking_number' => $data->tracking,
				'url'             => $data->url,
			);
		}

		return $this->packages;
	}


	/**
	 * Get any serial numbers returned for an item.
	 *
	 * @since 2.0.0
	 * @param object $item_data the item info
	 * @return array
	 */
	protected function get_item_serial_numbers( $item_data ) {

		$serial_numbers = array();

		if ( isset( $item_data->serialNumbers->resource->items ) ) {

			foreach ( $item_data->serialNumbers->resource->items as $serial_number ) {

				$serial_numbers[] = $serial_number->resource->serialNumber;
			}
		}

		return $serial_numbers;
	}


	/**
	 * Get a resource for this order.
	 *
	 * Things like items and holds can be either included in the order data or retreived via the API.
	 *
	 * @since 2.0.0
	 * @param string $resource the resource name
	 * @return array
	 */
	protected function get_resource( $resource ) {

		$items = array();

		// if this resource already exists in the order data, use it
		if ( isset( $this->data->$resource->resource->items ) ) {

			$items = $this->data->$resource->resource->items;

		// otherwise, call the API
		} else if ( is_callable( array( wc_shipwire()->get_api(), 'get_order_' . $resource ) ) ) {

			try {

				$items = wc_shipwire()->get_api()->{ 'get_order_' . $resource }( $this->get_id() );

			} catch ( Exception $e ) {

				wc_shipwire()->log( $e->getMessage() );
			}

		}

		return $items;
	}


}
