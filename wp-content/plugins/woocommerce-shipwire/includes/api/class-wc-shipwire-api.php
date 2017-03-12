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
 * The base Shipwire API.
 *
 * @since 1.0.0
 */
class WC_Shipwire_API extends SV_WC_API_Base {

	/** @var string request URI */
	protected $request_uri;


	/**
	 * Construct the API base.
	 *
	 * @since 1.0.0
	 * @param string $username the API username
	 * @param string $password the API password
	 * @param string $environment the API environment
	 */
	public function __construct( $username, $password, $environment ) {

		$this->set_request_content_type_header( 'application/json' );
		$this->set_request_accept_header( 'application/json' );

		$this->set_http_basic_auth( $username, $password );

		$this->request_uri = ( 'production' === $environment ) ? 'https://api.shipwire.com/api/v3' : 'https://api.beta.shipwire.com/api/v3';
	}


	/**
	 * Get a list of orders from Shipwire.
	 *
	 * @since 2.0.0
	 * @param array $args {
	 *     Optional. The orders request args.
	 *
	 *     @type string $transaction_id the desired order transaction ID
	 *     @type int    $limit          the number of orders to get. Defaults to 20.
	 *     @type string $expand         the order resource to expand. Accepts `holds`, `trackings`, `items`, or `all`
	 * }
	 * @return array
	 * @throws \SV_WC_API_Exception
	 */
	public function get_orders( $args = array() ) {

		$request = $this->get_new_request( 'order' );

		$request->get_orders( $args );

		$response = $this->perform_request( $request );

		return $response->get_orders();
	}


	/**
	 * Get an order from Shipwire.
	 *
	 * @since 2.0.0
	 * @param int $order_id the WooCommerce order ID
	 * @return \WC_Shipwire_API_Order
	 * @throws \SV_WC_API_Exception
	 */
	public function get_order( $order_id ) {

		$request = $this->get_new_request( 'order' );

		$request->get_order( $order_id );

		$response = $this->perform_request( $request );

		return $response->get_single_order();
	}


	/**
	 * Get an order's hold info from Shipwire.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 * @return array
	 * @throws \SV_WC_API_Exception
	 */
	public function get_order_holds( $order_id ) {

		$request = $this->get_new_request( 'order_holds' );

		$request->get_holds( $order_id );

		$response = $this->perform_request( $request );

		return $response->get_items();
	}


	/**
	 * Get an order's package info from Shipwire.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 * @return \WC_Shipwire_API_Order_Package_Response
	 * @throws \SV_WC_API_Exception
	 */
	public function get_order_packages( $order_id ) {

		$request = $this->get_new_request( 'order_packages' );

		$request->get_packages( $order_id );

		$response = $this->perform_request( $request );

		return $response->get_items();
	}


	/**
	 * Get an order's item info from Shipwire.
	 *
	 * @since 2.0.0
	 * @param int $order_id the Shipwire order ID
	 * @return array
	 * @throws \SV_WC_API_Exception
	 */
	public function get_order_items( $order_id ) {

		$request = $this->get_new_request( 'order_items' );

		$request->get_items( $order_id );

		$response = $this->perform_request( $request );

		return $response->get_items();
	}


	/**
	 * Send an order to Shipwire.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 * @return \WC_Shipwire_API_Order_Response the response object
	 * @throws \SV_WC_API_Exception
	 */
	public function export_order( WC_Order $order ) {

		$request = $this->get_new_request( 'order' );

		$request->process_new_order( $order );

		return $this->perform_request( $request );
	}


	/**
	 * Get stock a product's stock values.
	 *
	 * @since 2.0.0
	 * @param array $args {
	 *     the products to get
	 *
	 *     @type int    $id the product's Shipwire ID
	 *     @type string $sku the product's SKU
	 * }
	 * @return object
	 * @throws \SV_WC_API_Exception
	 */
	public function get_product_stock( $args ) {

		$request = $this->get_new_request( 'stock' );

		$request->get_product_stock( $args );

		$response = $this->perform_request( $request );

		return $response->get_single_stock();
	}


	/**
	 * Get stock values for a set of products.
	 *
	 * @since 2.0.0
	 * @param array $skus the product SKUs to update
	 * @return array
	 * @throws \SV_WC_API_Exception
	 */
	public function get_product_inventory( $skus ) {

		$request = $this->get_new_request( 'stock' );

		$request->get_inventory( $skus );

		$response = $this->perform_request( $request );

		return $response->get_inventory();
	}


	/**
	 * Get shipping rates for a shipment package.
	 *
	 * @since 1.0.0
	 * @param array $package the package information
	 * @return \WC_Shipwire_API_Rate_Response the response object
	 * @throws \SV_WC_API_Exception
	 */
	public function get_shipping_rates( $package ) {

		$request = $this->get_new_request( 'rate' );

		$request->process_package( $package );

		return $this->perform_request( $request );
	}


	/**
	 * Get all existing webhooks.
	 *
	 * @since 2.0.0
	 * @throws \SV_WC_API_Exception
	 */
	public function get_webhooks() {

		$request = $this->get_new_request( 'webhook' );

		$request->get_webhooks();

		return $this->perform_request( $request );
	}


	/**
	 * Create a new webhook.
	 *
	 * @since 2.0.0
	 * @param string $resource the resource that will trigger this webhook
	 * @param string $topic the topic or event
	 * @param string $url the webhook URL
	 * @param string $version Optional. The webhook API version
	 * @throws \SV_WC_API_Exception
	 */
	public function create_webhook( $resource, $topic, $url, $version = '' ) {

		$request = $this->get_new_request( 'webhook' );

		$request->create_webhook( $resource, $topic, $url, $version );

		return $this->perform_request( $request );
	}


	/**
	 * Delete an existing webhook.
	 *
	 * @since 2.0.0
	 * @param int $id the webhook ID
	 * @throws \SV_WC_API_Exception
	 */
	public function delete_webhook( $id ) {

		$request = $this->get_new_request( 'webhook' );

		$request->delete_webhook( $id );

		return $this->perform_request( $request );
	}


	/**
	 * Get all existing webhook secrets.
	 *
	 * @since 2.0.0
	 * @throws \SV_WC_API_Exception
	 */
	public function get_secrets() {

		$request = $this->get_new_request( 'webhook' );

		$request->get_secrets();

		return $this->perform_request( $request );
	}


	/**
	 * Create a new webhook secret.
	 *
	 * @since 2.0.0
	 * @throws \SV_WC_API_Exception
	 */
	public function create_secret() {

		$request = $this->get_new_request( 'webhook' );

		$request->create_secret();

		return $this->perform_request( $request );
	}


	/**
	 * Delete an existing webhook secret.
	 *
	 * @since 2.0.0
	 * @param int $id the secret ID
	 * @throws \SV_WC_API_Exception
	 */
	public function delete_secret( $id ) {

		$request = $this->get_new_request( 'webhook' );

		$request->delete_secret( $id );

		return $this->perform_request( $request );
	}


	/**
	 * Build and return a new API request object.
	 *
	 * @since 2.0.0
	 * @see SV_WC_API_Base::get_new_request()
	 * @param string $type the desired request type
	 * @return \WC_Shipwire_API_Request the request object
	 * @throws \SV_WC_API_Exception
	 */
	protected function get_new_request( $type = '' ) {

		switch ( $type ) {

			// Orders
			case 'order':
				$this->set_response_handler( 'WC_Shipwire_API_Order_Response' );
				return new WC_Shipwire_API_Order_Request();
			break;

			case 'order_holds':
			case 'order_items':
			case 'order_packages':
				$this->set_response_handler( 'WC_Shipwire_API_Order_Resource_Response' );
				return new WC_Shipwire_API_Order_Request();
			break;

			// Product stock
			case 'stock':
				$this->set_response_handler( 'WC_Shipwire_API_Stock_Response' );
				return new WC_Shipwire_API_Stock_Request();
			break;

			// Shipping rates
			case 'rate':
				$this->set_response_handler( 'WC_Shipwire_API_Rate_Response' );
				return new WC_Shipwire_API_Rate_Request();
			break;

			// Webhooks
			case 'webhook':
				$this->set_response_handler( 'WC_Shipwire_API_Webhook_Response' );
				return new WC_Shipwire_API_Webhook_Request();
			break;

			// unknown request type
			default:
				throw new SV_WC_API_Exception( 'Invalid request type' );
		}
	}


	/**
	 * Validates the response after parsing.
	 *
	 * @since 2.0.0
	 * @throws \SV_WC_API_Exception
	 */
	protected function do_post_parse_response_validation() {

		$response = $this->get_response();
		$errors   = $response->get_errors();
		$message  = '';

		if ( ! empty( $errors ) ) {

			$errors = array();

			foreach ( $response->get_errors() as $error ) {
				$errors[] = isset( $error->message ) ? $error->message : $error;
			}

			$message = implode( '. ', $errors );

		} elseif ( 200 !== $response->get_status() ) {

			$message = $response->get_message();
		}

		if ( $message ) {
			throw new SV_WC_API_Exception( $message );
		}
	}


	/**
	 * Get the request arguments in the format required by wp_remote_request()
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_request_args() {

		$args = parent::get_request_args();

		/**
		 * Filter the API request timeout.
		 *
		 * @since 1.0.0
		 * @param int $timeout the timeout in seconds
		 */
		$args['timeout'] = apply_filters( 'wc_shipwire_api_timeout', $args['timeout'] );

		return $args;
	}


	/**
	 * Get the plugin class instance associated with this API.
	 *
	 * @since 2.0.0
	 * @return \WC_Shipwire
	 */
	protected function get_plugin() {

		return wc_shipwire();
	}
}
