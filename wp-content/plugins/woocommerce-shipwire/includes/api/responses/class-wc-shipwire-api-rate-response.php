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
 * Handle a Shipwire API shipping rate response.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Rate_Response extends WC_Shipwire_API_Response {


	/**
	 * Get the calculated rates for a package
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_rates() {

		$rate_options = isset( $this->resource->rates[0]->serviceOptions ) && ! empty( $this->resource->rates[0]->serviceOptions ) ? $this->resource->rates[0]->serviceOptions : array();

		$rates = array();

		foreach( $rate_options as $option ) {

			$shipment = $option->shipments[0];

			$rates[] = array(
				'id'                            => $option->serviceLevelCode,
				'warehouse'                     => $shipment->warehouseName,
				'service_delivery_confirmation' => ( isset( $shipment->carrier->properties['deliveryConfirmation'] ) ),
				'service_trackable'             => ( isset( $shipment->carrier->properties['trackable'] ) ),
				'service_signature_required'    => ( isset( $shipment->carrier->properties['signatureRequired'] ) ),
				'service_name'                  => $shipment->carrier->description,
				'service_code'                  => $shipment->carrier->code,
				'cost'                          => $shipment->cost->amount,
				'delivery_estimate_min'         => $shipment->expectedDeliveryMinDate,
				'delivery_estimate_max'         => $shipment->expectedDeliveryMaxDate,
			);
		}

		return $rates;
	}


}
