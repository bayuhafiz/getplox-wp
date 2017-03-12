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
 * @package     WC-Shipwire/Cron
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Shipwire Cron Class
 *
 * Adds custom update schedule and schedules inventory / tracking update events
 *
 * @since 1.0
 */
class WC_Shipwire_Cron {


	/**
	 * Adds hooks and filters
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Add custom schedule, e.g. every 10 minutes
		add_filter( 'cron_schedules', array( $this, 'add_update_schedules' ) );

		// Schedule auto-update events if they don't exist, run in both frontend and backend so events are still scheduled when an admin reactivates the plugin
		add_action( 'init', array( $this, 'add_scheduled_updates' ) );

		// trigger inventory updates
		add_action( 'wc_shipwire_inventory_update', 'WC_Shipwire_Product::update_all_inventory' );

		// trigger tracking updates
		add_action( 'wc_shipwire_tracking_update', 'WC_Shipwire_Order::update_all_tracking' );

		$this->inventory_updates_enabled = ( 'yes' == get_option( 'wc_shipwire_auto_update_inventory' ) );
		$this->inventory_interval = $this->inventory_updates_enabled ? (int) get_option( 'wc_shipwire_auto_update_inventory_interval' ) : 0;

		$this->tracking_updates_enabled = ( 'yes' == get_option( 'wc_shipwire_auto_update_tracking' ) );
		$this->tracking_interval = $this->tracking_updates_enabled ? (int) get_option( 'wc_shipwire_auto_update_tracking_interval' ) : 0;
	}


	/**
	 * If updates are enabled, add the custom interval (e.g. every 15 minutes) set on the admin settings page
	 *
	 * @since 1.0
	 * @param array $schedules existing WP recurring schedules
	 * @return array
	 */
	public function add_update_schedules( $schedules ) {

		if ( $this->inventory_interval ) {
			$schedules['wc_shipwire_inventory'] = array(
				'interval' => $this->inventory_interval * 60,
				'display'  => sprintf( __( 'Every %d minutes', 'woocommerce-shipwire' ), $this->inventory_interval )
			);
		}


		if ( $this->tracking_interval ) {
			$schedules['wc_shipwire_tracking' ] = array(
				'interval' => $this->tracking_interval * 60,
				'display'  => sprintf( __( 'Every %s minutes', 'woocommerce-shipwire' ), $this->tracking_interval )
			);
		}

		return $schedules;
	}


	/**
	 * Add scheduled events to wp-cron if not already added
	 *
	 * @since 1.0
	 * @return array
	 */
	public function add_scheduled_updates() {

		// Schedule inventory update if enabled and not already scheduled
		if ( $this->inventory_updates_enabled && ! wp_next_scheduled( 'wc_shipwire_inventory_update' ) ) {

			// set next immediate execution time using interval
			wp_schedule_event( strtotime( "{$this->inventory_interval} minutes"), 'wc_shipwire_inventory', 'wc_shipwire_inventory_update' );
		}

		// Schedule tracking update if enabled and not already scheduled
		if ( $this->tracking_updates_enabled && ! wp_next_scheduled( 'wc_shipwire_tracking_update' ) ) {

			// set next immediate execution time using interval
			wp_schedule_event( strtotime( "{$this->tracking_interval} minutes" ), 'wc_shipwire_tracking', 'wc_shipwire_tracking_update' );
		}
	}


}
