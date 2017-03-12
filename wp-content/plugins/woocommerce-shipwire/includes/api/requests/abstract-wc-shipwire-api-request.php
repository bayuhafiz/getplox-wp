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
 * The base Shipwire API request object.
 *
 * @since 2.0.0
 */
abstract class WC_Shipwire_API_Request extends SV_WC_API_JSON_Request {


	/**
	 * Gets the request URL.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_path() {

		$path   = $this->path;
		$params = $this->get_params();

		if ( 'GET' === $this->get_method() && ! empty( $params ) ) {

			$path .= '?' . http_build_query( $this->get_params(), '', '&' );
		}

		return $path;
	}


	/**
	 * Converts the request parameters to a string.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function to_string() {

		if ( 'GET' === $this->get_method() ) {
			$string = array();
		} else {
			$string = parent::to_string();
		}

		return $string;
	}


}
