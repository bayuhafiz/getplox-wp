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
 * @package     WC-Shipwire/Order
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Shipwire Order Class
 *
 * Extends the WooCommerce Order class to add additional information and functionality specific to Shipwire
 *
 * @since 1.0
 * @extends \WC_Order
 */
class WC_Shipwire_Order extends WC_Order {


	/** @var string shipwire status for order */
	public $shipwire_status;

	/** @var string shipwire order ID */
	public $shipwire_id;

	/** @var string warehouse code where order will be fulfilled / shipped from */
	public $warehouse;

	/** @var string address type provided to shipwire during order submission */
	public $address_type;

	/** @var string shipwire shipping code - will be used during order submission if provided */
	public $shipping_code;

	/** @var string full shipping name */
	public $shipping_full_name;

	/** @var string location name of warehouse (e.g. Philadelphia) order will ship from */
	public $ship_from_warehouse;

	/** @var string full name of shipper and service (e.g. USPS Priority Mail + Delivery Confirmation) */
	public $shipper_full_name;

	/** @var string expected delivery date provided after order submission */
	public $expected_delivery_date;

	/** @var string link to shipwire portal where customer can view order stauts */
	public $manage_href;

	// this information is provided during tracking updates

	/** @var string shipping carrier name */
	public $carrier;

	/** @var string carrier tracking number */
	public $tracking_number;

	/** @var string URL to track shipment on carrier's website */
	public $tracking_href;


	/**
	 * Load standard order data and custom order data
	 *
	 * @access public
	 * @since 1.0
	 * @param int $order_id
	 * @return \WC_Shipwire_Order
	 */
	public function __construct( $order_id ) {

		// load default order data
		parent::__construct( $order_id );

		// get shipwire order status
		$terms = wp_get_object_terms( $this->id, 'shipwire_order_status', array( 'fields' => 'slugs' ) );
		$this->shipwire_status = ( isset( $terms[0] ) ) ? str_replace( 'wc_shipwire_', '', $terms[0] ) : 'new'; // remove term prefix


		/**
		 * Set order defaults & allow plugins to modify the default Shipwire info associated with the order
		 * This can be used to change the default shipping code to `E-INTL` for example
		 *
		 * @since 1.0.2
		 * @param array $custom_fields the Shipwire custom data {
		 *     @type string $warehouse the Shipwire warehouse to pull the order from
		 *     @type string $address_type the address type provided to Shipwire
		 *     @type string $shipping_code the shipping method that Shipwire should use to ship the order
		 *     @type string $shipping_full_name the full name of the person/company to ship to
		 *     @type string $ship_from_warehouse the warehouse code to ship from
		 *     @type string $shipwire_id the Shipwire order ID
		 *     @type string $shipper_full_name the full name of the shipping carrier
		 *     @type string $expected_delivery_date the expected delivery date for the order
		 *     @type string $manage_href the URL to view the order in Shipwire's portal
		 *     @type string $carrier the shipping carrier who is delivering the order
		 *     @type string $tracking_number the tracking number for the carrier
		 *     @type string $tracking_href the URL to view the order on the carrier's website
		 * }
		 * @param int $order_id the order ID
		 */
		$custom_fields = apply_filters( 'wc_shipwire_order_defaults', array(
			'warehouse'              => '00',
			'address_type'           => 'ship',
			'shipping_code'          => 'GD',
			'shipping_full_name'     => '',
			'ship_from_warehouse'    => '',
			'shipwire_id'            => '',
			'shipper_full_name'      => '',
			'expected_delivery_date' => '',
			'holds'                  => array(),
			'tracking_packages'      => array(),
		), $order_id );

		// set shipwire-specific order data
		foreach ( $custom_fields as $key => $default ) {

			$value = get_post_meta( $this->id, "_wc_shipwire_{$key}", true );

			$this->$key = ( ! empty( $value ) ) ? $value : $default;
		}

		// set this after shipping first/last name are available
		if ( empty( $this->shipping_full_name ) ) {
			$this->shipping_full_name = apply_filters( 'wc_shipwire_shipping_name', $this->get_formatted_shipping_full_name(), $this );
		}

		// if shipwire shipping method is used, set the shipping code for order fulfillment to what was selected by the customer during checkout
		foreach ( $this->get_shipping_methods() as $method ) {

			if ( false !== strpos( $method['method_id'], 'shipwire' ) ) {
				$this->shipping_code = str_replace( 'shipwire_', '', $method['method_id'] );
				break;
			}
		}

		// orders are considered 'exported' if they have any shipwire status other than 'new' or 'failed'
		$this->is_exported = ( 'new' == $this->shipwire_status || 'failed' == $this->shipwire_status ) ? false : true;
	}


	/**
	 * Replace items array with format required by Shipwire
	 *
	 * @since 1.0
	 */
	private function convert_items_to_shipwire_items() {

		$this->items = array();

		// setup new items array
		foreach ( $this->get_items() as $item_key => $item ) {

			$product = $this->get_product_from_item( $item );

			if ( ! $product->needs_shipping() ) {
				continue;
			}

			if ( 'yes' !== $product->wc_shipwire_manage_stock || ! $product->get_sku() ) {
				continue;
			}

			$this->items[ $item_key ] = new StdClass();

			// only SKU and Quantity are required
			$this->items[ $item_key ]->code     = $product->get_sku();
			$this->items[ $item_key ]->quantity = (int) $item['qty'];
		}
	}


	/**
	 * Export order to Shipwire
	 *
	 * @since 1.0
	 */
	public function export() {

		// don't export already exported orders
		if ( $this->is_exported ) {
			return;
		}

		// don't export orders with invalid statuses
		if ( ! in_array( $this->status, apply_filters( 'wc_shipwire_valid_order_statuses_for_export', array( 'on-hold', 'processing' ) ) ) ) {
			return;
		}


		// shipwire requires minimal information for items, so replace the default WC order items array with a custom format
		$this->convert_items_to_shipwire_items();

		// don't export order with no shippable items
		if ( empty( $this->items ) ) {
			return;
		}

		try {

			// export orders and parse response
			$response = wc_shipwire()->get_api()->export_order( $this );

			$warnings = $response->get_warnings();
			$order    = $response->get_single_order();
			$status   = $order->get_status();

			$message = '';

			// save the order holds for the new order
			$this->update_shipwire_holds( $order->get_holds(), true );

			if ( ! empty( $warnings ) ) {
				$status  .= '_with_warnings';
				$message .= sprintf( '<p class="wc_shipwire_note"><strong>' . __( 'Warnings', 'woocommerce-shipwire' ) . '</strong>: %s</p>', $warnings );
			}

			$this->update_shipwire_status( $status, $message );

			// set as exported and add shipwire ID
			$this->is_exported = true;
			$this->update_order_meta( 'transaction_id', $order->get_transaction_id() );
			$this->update_order_meta( 'order_id', $order->get_id() );

			do_action( 'wc_shipwire_order_exported', $this, $response );

		} catch ( Exception $e ) {

			wc_shipwire()->log( $e->getMessage() );

			$this->update_shipwire_status( 'failed', sprintf( '<p class="wc_shipwire_note"><strong>' . __( 'API/HTTP Error', 'woocommerce-shipwire' ) . ':</strong> %s</p>', $e->getMessage() ) );
		}
	}


	/**
	 * Update tracking info for order
	 *
	 * @since 1.0.0
	 */
	public function update_tracking() {

		// don't update tracking info for new orders or if order is missing shipwire/order ID
		if ( ! $this->is_exported || ! $this->id || ! $this->wc_shipwire_transaction_id ) {
			return;
		}

		try {

			// get updated order ID data for legacy orders
			if ( ! $this->wc_shipwire_order_id ) {
				$order = $this->get_legacy_order();
			} else {
				$order = wc_shipwire()->get_api()->get_order( $this->id );
			}

			// save any order holds
			$this->update_shipwire_holds( $order->get_holds() );

			$this->update_order_meta( 'shipper_full_name', $order->get_shipping_company() );

			$this->update_order_meta( 'expected_delivery_date', $order->get_delivery_date() );

			$this->update_order_meta( 'tracking_packages', $order->get_packages() );

			// add any shipwire item meta
			$this->update_shipwire_items( $order->get_items() );

			// update status
			$this->update_shipwire_status( $order->get_status() );

			/**
			 * Trigger action when the tracking is updated for an order
			 *
			 * @since 1.0.2
			 * @param \WC_Shipwire_Order $order the shipwire order object
			 */
			do_action( 'wc_shipwire_tracking_updated', $this );

		} catch ( Exception $e ) {

			wc_shipwire()->log( $e->getMessage() );
		}
	}


	/**
	 * Gets the order's Shipwire data from 1.6.1 and below.
	 *
	 * @since 2.0.0
	 * @return \WC_Shipwire_API_Order
	 * @throws \SV_WC_API_Exception
	 */
	protected function get_legacy_order() {

		$orders = wc_shipwire()->get_api()->get_orders( array(
			'transaction_id' => $this->wc_shipwire_transaction_id,
		) );

		if ( empty( $orders ) ) {
			throw new SV_WC_API_Exception( 'Order not found' );
		}

		$order = current( $orders );

		if ( $order instanceof WC_Shipwire_API_Order ) {
			$this->update_order_meta( 'order_id', $order->get_id() );
		} else {
			throw new SV_WC_API_Exception( 'Invalid order' );
		}

		return $order;
	}


	/**
	 * Update tracking information for all exported orders that are not shipped
	 *
	 * @since 1.0
	 */
	public static function update_all_tracking() {

		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => 'shop_order',
			'post_status' => 'any',
			'nopaging'    => true,
			'meta_query'  => array(
				'relation' => 'OR',
				array(
					'key'     => '_wc_shipwire_order_id',
					'compare' => 'EXISTS',
				),
				// transaction ID for back compat
				array(
					'key'     => '_wc_shipwire_transaction_id',
					'compare' => 'EXISTS',
				),
			),
			'tax_query'   => array(
				array(
					'taxonomy' => 'shipwire_order_status',
					'field'    => 'slug',
					'terms'    => array( 'wc_shipwire_shipped' ),
					'operator' => 'NOT IN',
				),
			),
		);

		// get unshipped order IDs
		$query = new WP_Query( $query_args );

		foreach ( $query->posts as $order_id ) {

			$order = new WC_Shipwire_Order( $order_id );

			$order->update_tracking();
		}

		if ( $query->post_count ) {

			wc_shipwire()->log_update( sprintf( _n( 'Updated tracking information for %d order.', 'Updated tracking information for %d orders.', $query->post_count, 'woocommerce-shipwire' ), $query->post_count ) );

			return $query->post_count;

		} else {

			return 0;
		}
	}


	/**
	 * Helper to update custom order meta
	 *
	 * @since 1.0
	 * @param string $meta_key
	 * @param mixed $meta_value
	 */
	public function update_order_meta( $meta_key, $meta_value ) {

		if ( ! $meta_key || ! $meta_value ) {
			return;
		}

		update_post_meta( $this->id, '_wc_shipwire_'. $meta_key, $meta_value );

		$this->$meta_key = $meta_value;
	}


	/**
	 * Update the Shipwire custom order status
	 *
	 * 'new' - un-exported order
	 * 'held' - exported but requires manual review in Shipwire admin before shipment (out of stock errors, etc)
	 * 'held_with_warnings' - same as 'held', but order also has warnings (wrong address classification, etc - not serious errors)
	 * 'failed' - could not be exported due to API/HTTP error
	 * 'accepted' - exported to shipwire and accepted for fulfillment and shipment
	 * 'accepted_with_warnings' - same as excepted, but order also has warnings (wrong address classification, etc - not serious errors)
	 * 'shipped' - order has been fulfilled and shipped
	 *
	 * Note that the shipwire order status term slugs are stored with a 'wc_shipwire_' prefix to ensure they are always unique and not
	 * accidentally deleted or modified
	 *
	 * @since 1.0
	 * @param string $new_status the new shipwire status to change the order to
	 * @param string $note an optional message to include in the order note
	 */
	public function update_shipwire_status( $new_status, $note = '' ) {

		if ( ! $new_status ) {
			return;
		}

		// get old status term
		$old_status_term = get_term_by( 'slug', sanitize_title( 'wc_shipwire_' . $this->shipwire_status ), 'shipwire_order_status' );

		// get new status term
		$new_status_term = get_term_by( 'slug', sanitize_title( 'wc_shipwire_' . $new_status ), 'shipwire_order_status' );

		// new orders will not have a shipwire order status term assigned yet, so set to 'new'
		if ( ! $old_status_term ) {

			$old_status_term = new stdClass();
			$old_status_term->slug = 'new';
			$old_status_term->name = 'New';
		}

		// If the new status doesn't already exist as a taxonomy term, add it
		if ( ! $new_status_term ) {

			$term = wp_insert_term( ucwords( $new_status ), 'shipwire_order_status', array( 'slug' => 'wc_shipwire_' . $new_status ) );

			if ( is_array( $term ) ) {
				$new_status_term = get_term( $term['term_id'], 'shipwire_order_status' );
			}
		}

		if ( $new_status_term ) {

			// set new status on order
			wp_set_object_terms( $this->id, array( $new_status_term->slug ), 'shipwire_order_status', false );

			// remove prefix from slugs
			$old_status_term->slug = str_replace( 'wc_shipwire_', '', $old_status_term->slug );
			$new_status_term->slug = str_replace( 'wc_shipwire_', '', $new_status_term->slug );

			if ( $old_status_term->slug != $new_status_term->slug ) {

				// Status was changed
				do_action( 'wc_shipwire_order_status_' . $new_status_term->slug, $this->id );
				do_action( 'wc_shipwire_order_status_changed', $this->id, $old_status_term->slug, $new_status_term->slug );
				do_action( "wc_shipwire_order_status_{$old_status_term->slug}_to_{$new_status_term->slug}", $this->id );

				// add order note
				/* translators: Placeholders: %1$s - old Shipwire order status, %2$s - new shipwire order status, %3$s - additional order note */
				$this->add_order_note( sprintf( __( 'Shipwire order status changed from %1$s to %2$s. %3$s', 'woocommerce-shipwire' ), $old_status_term->name, $new_status_term->name, $note ) );

				// Change the order status to completed if shipwire has shipped the order and admin has enabled option
				if ( 'completed' === $new_status_term->slug && 'yes' === get_option( 'wc_shipwire_auto_complete_shipped_orders' ) ) {
					$this->update_status( 'completed' );
				}

				$this->shipwire_status = $new_status_term->slug;
			}
		}
	}


	/**
	 * Updates the Shipwire hold data.
	 *
	 * @since 2.0.0
	 * @param array $holds the new hold data
	 * @param bool $new whether this is a new Shipwire order
	 */
	public function update_shipwire_holds( $holds = array(), $new = false ) {

		$existing_holds = $this->holds;

		if ( $holds !== $existing_holds ) {

			$this->update_order_meta( 'holds', $holds );

			// if there are new holds, trigger an order exception
			if ( ! empty( $holds ) && ! $new ) {

				/**
				 * Fires when there is an order hold that requires action.
				 *
				 * @since 2.0.0
				 * @param int $order_id the held order ID
				 */
				do_action( 'wc_shipwire_order_exception', $this->id );
			}
		}

		/**
		 * Fires after an order's Shipwire hold data is updated.
		 *
		 * @since 2.0.0
		 * @param int $order_id the held order ID
		 */
		do_action( 'wc_shipwire_order_holds_updated', $this->id, $holds );
	}


	/**
	 * Update WC order items with Shipwire data.
	 *
	 * @since 2.0.0
	 * @param array $item_data the Shipwire items
	 */
	public function update_shipwire_items( $item_data ) {

		foreach ( $this->get_items() as $item_id => $item ) {

			$product = $this->get_product_from_item( $item );

			if ( isset( $item_data[ $product->get_sku() ] ) ) {

				// add the serial numbers
				wc_update_order_item_meta( $item_id, 'wc_shipwire_serial_numbers', $item_data[ $product->get_sku() ]['serial_numbers'] );
			}
		}
	}


	/**
	 * Helper to return the Shipwire order status term name
	 *
	 * @since 1.0
	 * @param string $status
	 * @return string
	 */
	public function get_shipwire_status_for_display( $status = '' ) {

		if ( ! $status ) {
			$status = $this->shipwire_status;
		}

		$status = get_term_by( 'slug', sanitize_title( 'wc_shipwire_' . $status ), 'shipwire_order_status' );

		if ( $status ) {
			return $status->name;
		} else {
			return 'New';
		}
	}


} //end \WC_Shipwire_Order class
