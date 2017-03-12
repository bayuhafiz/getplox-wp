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
 * Handle a Shipwire API shipping rate request.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Rate_Request extends WC_Shipwire_API_Request {


	/**
	 * Process a WooCommerce package for the API.
	 *
	 * @since 2.0.0
	 * @param array $package
	 * @return
	 */
	public function process_package( $package ) {

		$params = array(
			'options' => array(
				'currency' => get_woocommerce_currency(),
			),
			'order' => array(
				'shipTo' => array(),
				'items'  => array(),
			),
		);

		$params['order']['shipTo'] = array(
			'address1'   => $package['destination']['address'],
			'address2'   => $package['destination']['address_2'],
			'city'       => $package['destination']['city'],
			'postalCode' => $package['destination']['postcode'],
			'region'     => $package['destination']['state'],
			'country'    => $package['destination']['country'],
		);

		foreach( $package['contents'] as $item ) {

			// skip virtual products
			if ( ! $item['data']->needs_shipping() ) {
				continue;
			}

			$params['order']['items'][] = array(
				'sku'      => $item['data']->get_sku(),
				'quantity' => $item['quantity']
			);
		}

		// TODO: back compat only - remove in a future version {CW 2016-06-14}
		$params['order'] = (array) apply_filters( 'wc_shipwire_shipping_method_shipment', (object) $params['order'], $package );

		/**
		 * Filter the Shipwire API rate package.
		 *
		 * @since 2.0.0
		 * @param array $package
		 * @param array $wc_package
		 */
		$params['order'] = apply_filters( 'wc_shipwire_shipping_rate_request_order', $params['order'], $package );

		$this->path   = '/rate';
		$this->params = $params;
	}


}
