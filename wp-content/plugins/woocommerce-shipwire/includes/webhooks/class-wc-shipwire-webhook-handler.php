<?php

class WC_Shipwire_Webhook_Handler {

	/** @var string the Webhooks API version **/
	protected $api_version;

	/** @var array the registered webhooks **/
	protected $webhooks;


	/**
	 * Construct the handler.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->api_version = 'v1';

		$this->init_webhooks();
	}


	/**
	 * Set the plugin webhooks.
	 *
	 * @since 2.0.0
	 */
	protected function init_webhooks() {

		require_once( wc_shipwire()->get_plugin_path() . '/includes/webhooks/abstract-wc-shipwire-webhook.php' );
		require_once( wc_shipwire()->get_plugin_path() . '/includes/webhooks/class-wc-shipwire-order-webhook.php' );
		require_once( wc_shipwire()->get_plugin_path() . '/includes/webhooks/class-wc-shipwire-tracking-webhook.php' );
		require_once( wc_shipwire()->get_plugin_path() . '/includes/webhooks/class-wc-shipwire-stock-webhook.php' );

		$classes = array(
			'WC_Shipwire_Order_Webhook',
			'WC_Shipwire_Tracking_Webhook',
			'WC_Shipwire_Stock_Webhook',
		);

		foreach ( $classes as $class ) {
			$this->webhooks[] = new $class;
		}
	}


	/**
	 * Generate and save the webhooks data.
	 *
	 * @since 2.0.0
	 */
	public function set_webhooks() {

		$this->generate_secret();
		$this->create_remote_webhooks();
	}


	/**
	 * Remove the webhooks data.
	 *
	 * @since 2.0.0
	 */
	public function remove_webhooks() {

		$this->delete_secrets();
		$this->delete_remote_webhooks();
	}


	/**
	 * Reset the webhook data.
	 *
	 * @since 2.0.0
	 */
	public function reset_webhooks() {

		$this->remove_webhooks();
		$this->set_webhooks();
	}


	/**
	 * Create new webhooks via the Shipwire API.
	 *
	 * @since 2.0.0
	 */
	public function create_remote_webhooks() {

		$new_webhooks = array();

		$webhooks = array(
			'order' => array(
				'updated',
				'canceled',
				'completed',
			),
			'order.hold' => array(
				'added',
				'cleared',
			),
			'tracking' => array(
				'created',
				'delivered',
				'updated',
			),
			'stock' => array(
				'transition',
			),
		);

		$base_url = add_query_arg( 'wc-api', 'wc_shipwire_', home_url() );

		foreach ( $webhooks as $resource => $topics ) {

			if ( strpos( $resource, '.' ) ) {
				list( $url_resource, $subresource ) = explode( '.', $resource );
			} else {
				$url_resource = $resource;
			}

			foreach ( $topics as $topic ) {

				try {

					$url = $base_url . $url_resource;

					$webhooks = wc_shipwire()->get_api()->create_webhook( $resource, $topic, $url, $this->api_version );

					foreach ( $webhooks as $webhook ) {
						$new_webhooks[] = $webhook->id;
					}

				} catch ( SV_WC_API_Exception $e ) {

					wc_shipwire()->log( $e->getMessage() );
				}
			}
		}

		update_option( 'wc_shipwire_webhook_ids', $new_webhooks );
	}


	/**
	 * Delete existing webhooks from the Shipwire API.
	 *
	 * @since 2.0.0
	 */
	public function delete_remote_webhooks() {

		$existing_webhooks = get_option( 'wc_shipwire_webhook_ids', array() );

		foreach ( $existing_webhooks as $id ) {

			try {

				wc_shipwire()->get_api()->delete_webhook( $id );

				unset( $existing_webhooks[ $id ] );

			} catch ( SV_WC_API_Exception $e ) {

				wc_shipwire()->log( $e->getMessage() );
			}
		}

		update_option( 'wc_shipwire_webhook_ids', $existing_webhooks );
	}


	/**
	 * Generate a new webhook secret.
	 *
	 * @since 2.0.0
	 */
	public function generate_secret() {

		$existing_secrets = get_option( 'wc_shipwire_secrets', array() );

		try {

			$response = wc_shipwire()->get_api()->create_secret();

			if ( $secret = $response->get_secret() ) {
				$existing_secrets[ $secret->id ] = $secret->secret;
			}

		} catch ( SV_WC_API_Exception $e ) {

			wc_shipwire()->log( $e->getMessage() );
		}

		update_option( 'wc_shipwire_secrets', $existing_secrets );
	}


	/**
	 * Delete existing webhook secrets from the Shipwire API.
	 *
	 * @since 2.0.0
	 */
	public function delete_secrets() {

		$existing_secrets  = get_option( 'wc_shipwire_secrets', array() );

		foreach ( $existing_secrets as $id => $secret ) {

			try {

				wc_shipwire()->get_api()->delete_secret( $id );

				unset( $existing_secrets[ $id ] );

			} catch ( SV_WC_API_Exception $e ) {

				wc_shipwire()->log( $e->getMessage() );
			}
		}

		update_option( 'wc_shipwire_secrets', $existing_secrets );
	}
}
