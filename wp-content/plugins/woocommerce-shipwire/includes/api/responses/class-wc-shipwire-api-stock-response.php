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
 * Handle a Shipwire API stock response.
 *
 * @since 2.0.0
 */
class WC_Shipwire_API_Stock_Response extends WC_Shipwire_API_Response {


	/**
	 * Get the returned product stock information.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_inventory() {

		$inventory = array();

		foreach ( $this->resource->items as $item ) {

			$data = $item->resource;

			$inventory[ $data->sku ] = (array) $data;
		}

		return $inventory;
	}


	/**
	 * Get the first product's stock.
	 *
	 * @since 2.0.0
	 * @return array|null
	 */
	public function get_single_stock() {

		$inventory = $this->get_inventory();

		return ! empty( $inventory ) ? current( $inventory ) : null;
	}


}
