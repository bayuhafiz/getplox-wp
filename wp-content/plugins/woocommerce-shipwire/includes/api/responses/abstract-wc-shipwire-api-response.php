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
 * The base Shipwire API response object.
 *
 * @since 2.0.0
 */
abstract class WC_Shipwire_API_Response extends SV_WC_API_JSON_Response {


	/**
	 * Gets the API errors, if any.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function get_errors() {

		if ( $this->errors ) {
			$errors = $this->errors;
		} elseif ( $this->error ) {
			$errors = array( $this->error );
		} else {
			$errors = array();
		}

		return $errors;
	}


	/**
	 * Get the status code for this response.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_status() {

		return $this->status;
	}


	/**
	 * Get the status message for this response.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_message() {

		return $this->message;
	}


	/**
	 * Get any warnings associated with this response.
	 *
	 * @since 2.0.0
	 * @return array of \WP_Error object for each warning
	 */
	public function get_warnings() {

		if ( ! $this->warnings ) {

			return '';
		}

		foreach ( $this->warnings as $warning ) {

			$warnings[] = $warning->message;
		}

		return implode( '. ', $warnings );
	}


	/**
	 * Get the actual resource data associated with this response.
	 *
	 * @since 2.0.0
	 * @return object
	 */
	protected function get_resource() {

		return $this->resource;
	}


}
