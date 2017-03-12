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
 * Handle a Shipwire API webhook request.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Webhook_Request extends WC_Shipwire_API_Request {


	/**
	 * Construct the request.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->path = '/webhooks';
	}


	/**
	 * Get all existing webhooks.
	 *
	 * @since 2.0.0
	 */
	public function get_webhooks() {

		$this->method = 'GET';
	}


	/**
	 * Create a new webhook.
	 *
	 * @since 2.0.0
	 * @param string $resource the resource that will trigger this webhook
	 * @param string $topic the topic or event
	 * @param string $url the webhook URL
	 * @param string $version Optional. The webhook API version
	 */
	public function create_webhook( $resource, $topic, $url, $version = '' ) {

		$topic = $resource . '.' . $topic;

		if ( $version ) {
			$topic = $version . '.' . $topic;
		}

		$this->params = array(
			'topic' => $topic,
			'url'   => $url,
		);
	}


	/**
	 * Delete an existing webhook.
	 *
	 * @since 2.0.0
	 * @param int $id the webhook ID
	 */
	public function delete_webhook( $id ) {

		$this->method = 'DELETE';

		$this->path .= '/' . (int) $id;
	}


	/**
	 * Get all existing webhook secrets.
	 *
	 * @since 2.0.0
	 */
	public function get_secrets() {

		$this->method = 'GET';
		$this->path   = '/secret';
	}


	/**
	 * Create a new webhook secret.
	 *
	 * @since 2.0.0
	 */
	public function create_secret() {

		$this->path = '/secret';
	}


	/**
	 * Delete an existing webhook secret.
	 *
	 * @since 2.0.0
	 * @param int $id the secret ID
	 */
	public function delete_secret( $id ) {

		$this->method = 'DELETE';

		$this->path = '/secret/' . (int) $id;
	}


}
