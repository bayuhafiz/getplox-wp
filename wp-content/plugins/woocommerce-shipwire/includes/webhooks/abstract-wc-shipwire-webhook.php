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
 * The base webhook class.
 *
 * @since 2.0.0
 */
abstract class WC_Shipwire_Webhook {


	/** @var string webhook resource **/
	protected $resource;

	/** @var string raw request data **/
	protected $raw_request_data;

	/** @var string request data, decoded **/
	protected $request_data;


	/**
	 * Construct the class.
	 *
	 * @since 2.0.0
	 */
	public function __construct( $resource ) {

		$this->resource = $resource;

		add_action( 'woocommerce_api_wc_shipwire_' . $this->resource, array( $this, 'handle_request' ) );
	}


	/**
	 * Determine if this webhook is enabled.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function enabled() {
		return true;
	}


	/**
	 * Handle the request.
	 *
	 * @since 2.0.0
	 */
	public function handle_request() {

		try {

			if ( ! $this->enabled() ) {
				throw new SV_WC_Plugin_Exception( 'The ' . $this->resource . ' webhook is disabled.' );
			}

			// log the request data
			$this->log_request();

			$this->validate_request();

			$this->process_request();

		} catch ( SV_WC_Plugin_Exception $e ) {

			wc_shipwire()->log( '[Webhook Error] ' . $e->getMessage() );
		}

		status_header( 200 );
		die();
	}


	/**
	 * Validate the webhook request.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	protected function validate_request() {

		if ( ! $this->get_request_signature() ) {
			throw new SV_WC_Plugin_Exception( __( 'Missing signature.', 'woocommerce-shipwire' ) );
		}

		$hash = base64_encode( hash_hmac( 'sha256', $this->get_raw_request_data(), $this->get_request_secret(), true ) );

		if ( $hash !== $this->get_request_hash() ) {
			throw new SV_WC_Plugin_Exception( __( 'Invalid hash.', 'woocommerce-shipwire' ) );
		}

		$topic = explode( '.', $this->get_request_topic() );

		// If not a request of this type, bail
		if ( $this->resource !== $topic[0] ) {
			throw new SV_WC_Plugin_Exception( __( 'Invalid resource.', 'woocommerce-shipwire' ) );
		}

		if ( ! $this->get_request_body() ) {
			throw new SV_WC_Plugin_Exception( __( 'Invalid data.', 'woocommerce-shipwire' ) );
		}
	}


	/**
	 * Process the request after validation.
	 *
	 * @since 2.0.0
	 */
	protected function process_request() {}


	/**
	 * Get the request signature.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_signature() {

		return isset( $_SERVER['HTTP_X_SHIPWIRE_SIGNATURE'] ) ? $_SERVER['HTTP_X_SHIPWIRE_SIGNATURE'] : '';
	}


	/**
	 * Get the request hash.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_hash() {

		$signature = $this->parse_signature( $this->get_request_signature() );

		return $signature['hash'];
	}


	/**
	 * Get the request secret.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_secret() {

		$signature = $this->parse_signature( $this->get_request_signature() );

		return $this->get_stored_secret( $signature['secret_id'] );
	}


	/**
	 * Get the raw request body.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_raw_request_data() {

		if ( is_null( $this->raw_request_data ) ) {
			$this->raw_request_data = file_get_contents( 'php://input' );
		}

		return $this->raw_request_data;
	}


	/**
	 * Get the request data, decoded.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_data() {

		return json_decode( $this->get_raw_request_data() );
	}


	/**
	 * Get the request topic.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_topic() {

		$data = $this->get_request_data();

		return isset( $data->topic ) ? $data->topic : '';
	}


	/**
	 * Get the request body.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	protected function get_request_body() {

		$data = $this->get_request_data();

		return isset( $data->body ) ? $data->body : '';
	}


	/**
	 * Get the stored secret for hash validation.
	 *
	 * @since 2.0.0
	 * @param int $id the secret ID
	 * @return string
	 */
	protected function get_stored_secret( $id ) {

		$secrets = get_option( 'wc_shipwire_secrets', array() );

		return isset( $secrets[ $id ] ) ? $secrets[ $id ] : '';
	}


	/**
	 * Parse out the request hash and secret ID from the signature.
	 *
	 * @since 2.0.0
	 * @param string $signature the request signature
	 * @return array
	 */
	protected function parse_signature( $signature ) {

		// signature starts as `abc123;secret-id=2`
		$signature = explode( ';', $signature );

		$parsed_signature['hash'] = $signature[0];

		$secret = wp_parse_args( $signature[1], array(
			'secret-id' => 0,
		) );

		$parsed_signature['secret_id'] = $secret['secret-id'];

		return $parsed_signature;
	}


	/**
	 * Log the webhook request.
	 *
	 * @since 2.0.0
	 */
	protected function log_request() {

		wc_shipwire()->log( "Webhook Request\ntopic: " . $this->get_request_topic() . "\nbody: " . $this->get_raw_request_data() );
	}


}
