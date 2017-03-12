<?php
/**
 * Plugin Name: WooCommerce Shipwire
 * Plugin URI: http://www.woothemes.com/products/shipwire/
 * Description: A full-featured Shipwire integration for WooCommerce, including real-time shipping rates, automatic order fulfillment processing, and live inventory / tracking updates.
 * Author: WooThemes / SkyVerge
 * Author URI: http://www.woothemes.com
 * Version: 2.0.0
 * Text Domain: woocommerce-shipwire
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2016 SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package    WC-Shipwire
 * @author     SkyVerge
 * @category   Integration
 * @copyright  Copyright (c) 2013-2016 SkyVerge, Inc.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '55e71dd56b91ccc546c5ca79979111c9', '182769' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.4.1', __( 'WooCommerce Shipwire', 'woocommerce-shipwire' ), __FILE__, 'init_woocommerce_shipwire', array(
	'minimum_wc_version'   => '2.4.13',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4.0',
) );

function init_woocommerce_shipwire() {

/**
 * Main Shipwire Class
 *
 * @since 1.0
 */
class WC_Shipwire extends SV_WC_Plugin {


	/** @var string version number */
	const VERSION = '2.0.0';

	/** @var WC_Shipwire single instance of this plugin */
	protected static $instance;

	/** plugin id */
	const PLUGIN_ID = 'shipwire';

	/** plugin text domain, DEPRECATED as of 1.5.0 */
	const TEXT_DOMAIN = 'woocommerce-shipwire';

	/** @var \WC_Shipwire_API instance */
	private $api;

	/** @var \WC_Shipwire_Admin instance */
	protected $admin;

	/** @var \WC_Shipwire_Webhook_Handler the webhook handler instance **/
	protected $webhook_handler;

	/** @var \WC_Shipwire_Cron instance */
	protected $cron;


	/**
	 * Load functionality/admin classes and add auto order export hooks
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array( 'dependencies' => array( 'SimpleXML', 'xmlwriter' ) )
		);

		// load WC-dependent classes
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'includes' ), 11 );

		// register shipwire order status taxonomy
		add_action( 'woocommerce_register_taxonomy', array( $this, 'register_shipwire_order_status_taxonomy' ) );

		// auto-export actions
		if ( 'yes' === get_option( 'wc_shipwire_auto_export_orders' ) ) {
			$this->add_export_actions();
		}

		// generate a separate shipping package for Shipwire-managed items
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'set_shipping_packages' ) );

		// validate checkout billing/shipping fields
		add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_checkout_fields' ) );

		// export order to shipwire
		add_action( 'wp_ajax_wc_shipwire_export_order', array( $this, 'process_order_export' ) );

		// Add the tracking number(s) to the Order Completed email
		add_filter( 'woocommerce_email_order_meta', array( $this, 'add_completed_email_tracking' ), 10, 3 );

		// adds tracking button(s) to the View Order page
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'add_view_order_tracking' ) );
	}


	/**
	 * Loads classes which require WC classes to be available
	 *
	 * @since 1.0
	 */
	public function includes() {

		$this->webhook_handler = $this->load_class( '/includes/webhooks/class-wc-shipwire-webhook-handler.php', 'WC_Shipwire_Webhook_Handler' );

		// Order class extends WC_Order to provide shipwire-specific functionality
		require_once( $this->get_plugin_path() . '/includes/class-wc-shipwire-order.php' );

		// Product class handles inventory syncing with shipwire
		require_once( $this->get_plugin_path() . '/includes/class-wc-shipwire-product.php' );

		// Shipping rate method for shipwire
		require_once( $this->get_plugin_path() . '/includes/shipping/class-wc-shipwire-shipping-method.php' );

		// add to available shipping methods
		add_filter( 'woocommerce_shipping_methods', array( $this, 'load_shipping_method' ) );

		// Cron class handles scheduling auto inventory/tracking updates (at least until Shipwire offers webhooks)
		$this->cron = $this->load_class( '/includes/class-wc-shipwire-cron.php', 'WC_Shipwire_Cron' );

		// add order exception email to available emails
		add_filter( 'woocommerce_email_classes', array( $this, 'load_order_exception_email' ) );

		// add order exception email action
		add_action( 'woocommerce_email_actions', array( $this, 'add_order_exception_email_action' ) );

		if ( is_admin() && ! is_ajax() ) {

			$this->admin_includes();
		}
	}


	/**
	 * Loads admin classes
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		// Admin class for settings page and admin UI customizations
		$this->admin = $this->load_class( '/includes/admin/class-wc-shipwire-admin.php', 'WC_Shipwire_Admin' );

		// message handler
		$this->admin->message_handler = $this->get_message_handler();

		// Update log for displaying inventory/tracking updates
		require_once( $this->get_plugin_path() . '/includes/admin/class-wc-shipwire-log-list-table.php' );
	}


	/**
	 * Get the cron class instance.
	 *
	 * @since 1.6.0
	 * @return \WC_Shipwire_Cron
	 */
	public function get_cron_instance() {
		return $this->cron;
	}


	/**
	 * Get the admin class instance.
	 *
	 * @since 1.6.0
	 * @return \WC_Shipwire_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the webhook handler instance.
	 *
	 * @since 2.0.0
	 * @return \WC_Shipwire_Webhook_Handler
	 */
	public function get_webhook_handler() {
		return $this->webhook_handler;
	}


	/**
	 * Backwards compat for changing the visibility of some class instances.
	 *
	 * @TODO Remove this as part of WC 2.7 compat {CW 2016-05-24}
	 *
	 * @since 1.6.0
	 */
	public function __get( $name ) {

		switch ( $name ) {

			case 'cron':
				_deprecated_function( 'wc_shipwire()->cron', '1.6.0', 'wc_shipwire()->get_cron_instance()' );
				return $this->get_cron_instance();

			case 'admin':
				_deprecated_function( 'wc_shipwire()->admin', '1.6.0', 'wc_shipwire()->get_admin_instance()' );
				return $this->get_admin_instance();
		}

		// you're probably doing it wrong
		trigger_error( 'Call to undefined property ' . __CLASS__ . '::' . $name, E_USER_ERROR );

		return null;
	}


	/**
	 * Get deprecated/removed hooks.
	 *
	 * @since 2.0.0
	 * @see SV_WC_Plugin::get_deprecated_hooks()
	 * @return array
	 */
	protected function get_deprecated_hooks() {

		// hooks removed in 2.0.0
		$hooks = array(
			'wc_shipwire_default_ship_from_warehouse' => array(
				'version'     => '2.0.0',
				'removed'     => true,
			),
		);

		return $hooks;
	}


	/**
	 * Adds the Shipwire shipping method to the list of available shipping method
	 *
	 * @since 1.0
	 * @param array $methods
	 * @return array $methods
	 */
	public function load_shipping_method( $methods ) {

		$methods[] = 'WC_Shipwire_Shipping_Method';

		return $methods;
	}


	/**
	 * Adds the Shipwire order exception email class to the list of available WC emails
	 *
	 * @since 1.1
	 * @param array $email_classes
	 * @return array $email_classes
	 */
	public function load_order_exception_email( $email_classes ) {

		require_once( $this->get_plugin_path() . '/includes/emails/class-wc-shipwire-order-exception-email.php' );

		$email_classes['WC_Shipwire_Order_Exception_Email'] = new WC_Shipwire_Order_Exception_Email();

		return $email_classes;
	}


	/**
	 * Adds the order exception email action.
	 *
	 * @since 2.0.0
	 * @param array $actions the default email actions
	 * @return array
	 */
	public function add_order_exception_email_action( $actions ) {

		$actions[] = 'wc_shipwire_order_exception';

		return $actions;
	}


	/**
	 * Registers a shop order taxonomy for shipwire order status
	 *
	 * @since 1.0
	 */
	public function register_shipwire_order_status_taxonomy() {

		register_taxonomy( 'shipwire_order_status', array( 'shop_order' ),
			array(
				'hierarchical'          => false,
				'update_count_callback' => '_update_generic_term_count',
				'show_ui'               => false,
				'show_in_nav_menus'     => false,
				'query_var'             => ( is_admin() ),
				'rewrite'               => false,
			)
		);
	}


	/**
	 * Load plugin text domain.
	 *
	 * @since 1.0
	 * @see SV_WC_Plugin::load_translation()
	 */
	public function load_translation() {

		load_plugin_textdomain( 'woocommerce-shipwire', false, dirname( plugin_basename( $this->get_file() ) ) . '/i18n/languages' );
	}


	/** Admin Methods ******************************************************/

	/**
	 * Export order to Shipwire
	 *
	 * @since 1.1
	 */
	public function process_order_export() {

		if ( ! current_user_can( 'manage_woocommerce' ) || ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woocommerce-shipwire' ) );
		}

		if ( ! check_admin_referer( 'wc_shipwire_export_order' ) ) {
			wp_die( __( 'You have taken too long, please go back and try again.', 'woocommerce-shipwire' ) );
		}

		$order_id = ( isset( $_GET['order_id'] ) && is_numeric( $_GET['order_id'] ) ) ? (int) $_GET['order_id'] : '';

		if ( ! $order_id ) {
			die;
		}

		$order = new WC_Shipwire_Order( $order_id );

		$order->export();

		wp_safe_redirect( wp_get_referer() );
		exit;
	}


	/**
	 * Render a notice for the user to RTFM
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::add_admin_notices()
	 */
	public function add_admin_notices() {

		// show any dependency notices
		parent::add_admin_notices();

		$this->get_admin_notice_handler()->add_admin_notice(
			/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
			sprintf( __( 'Thanks for installing WooCommerce Shipwire! Before getting started, please %1$sread the documentation%2$s :) ', 'woocommerce-shipwire' ), '<a href="http://docs.woothemes.com/document/shipwire/" target="_blank">', '</a>' ),
			'read-the-docs-notice',
			array( 'always_show_on_settings' => false, 'notice_class' => 'updated' )
		);
	}


	/**
	 * Hooks into order status actions to auto-export an order to Shipwire
	 *
	 * @since 1.0
	 */
	private function add_export_actions() {

		// export when WC_Order::payment_complete() is called
		add_action( 'woocommerce_payment_complete', array( $this, 'export_order_on_payment' ) );

		// export for gateways that do not call WC_Order::payment_complete()
		add_action( 'woocommerce_order_status_on-hold_to_processing', array( $this, 'export_order_on_payment' ) );
		add_action( 'woocommerce_order_status_on-hold_to_completed',  array( $this, 'export_order_on_payment' ) );

		// export when payment has previously failed
		add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'export_order_on_payment' ) );
		add_action( 'woocommerce_order_status_failed_to_completed',  array( $this, 'export_order_on_payment' ) );
	}


	/**
	 * Exports order to Shipwire upon payment completion
	 *
	 * @since 1.0
	 * @param string $order_id
	 */
	public function export_order_on_payment( $order_id ) {

		// allow plugins to prevent automatic order export
		if ( apply_filters( 'wc_shipwire_auto_export_order', true, $order_id ) ) {

			$allowed_countries = get_option( 'wc_shipwire_auto_order_export_countries', array() );

			$order = new WC_Shipwire_Order( $order_id );

			// if the admin has enabled automatic order export for specific countries, return if the ship to country isn't in the selected list
			if ( ! empty( $allowed_countries ) && ! in_array( $order->shipping_country, $allowed_countries ) ) {
				return;
			}

			$order->export();
		}
	}


	/**
	 * Validate address/city fields at checkout to ensure the don't exceed Shipwire limits
	 * Address Line 1/2 and City must be less than 50 characters
	 *
	 * @since 1.0
	 * @param array $posted checkout fields posted and cleaned by \WC_Checkout already
	 */
	public function validate_checkout_fields( $posted ) {

		// validate shipping address
		if ( ( isset( $posted['shiptobilling'] ) && 0 == $posted['shiptobilling'] ) || ( isset( $posted['ship_to_different_address'] ) && 1 == $posted['ship_to_different_address'] ) ) {

			if ( strlen( $posted['shipping_address_1'] ) > 50 ) {
				wc_add_notice( __( 'Shipping Address 1 must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );
			}

			if ( strlen( $posted['shipping_address_2'] ) > 50 )
				wc_add_notice( __( 'Shipping Address 2 must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );

			if ( strlen( $posted['shipping_city'] ) > 50 ) {

				wc_add_notice( __( 'Shipping City must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );
			}

		// validate billing address
		} else {

			if ( strlen( $posted['billing_address_1'] ) > 50 ) {
				wc_add_notice( __( 'Billing Address 1 must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );
			}

			if ( strlen( $posted['billing_address_2'] ) > 50 ) {
				wc_add_notice( __( 'Billing Address 2 must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );
			}

			if ( strlen( $posted['billing_city'] ) > 50 ) {
				wc_add_notice( __( 'Billing City must be less than 50 characters.', 'woocommerce-shipwire' ), 'error' );
			}
		}
	}


	/**
	 * Generate a separate shipping package for Shipwire-managed items.
	 *
	 * @since 2.0.0
	 * @param array $packages the shipping packages
	 * @return array
	 */
	public function set_shipping_packages( $packages ) {

		$methods = WC()->shipping->load_shipping_methods();

		if ( ! isset( $methods['shipwire'] ) || 'yes' !== $methods['shipwire']->enabled ) {
			return $packages;
		}

		$new_packages = array();

		// Set a special package for Shipwire items
		$shipwire_package = array(
			'user'     => array(
				'ID' => get_current_user_id(),
			),
			'destination' => array(
				'address'   => WC()->customer->get_shipping_address(),
				'address_2' => WC()->customer->get_shipping_address_2(),
				'city'      => WC()->customer->get_shipping_city(),
				'state'     => WC()->customer->get_shipping_state(),
				'country'   => WC()->customer->get_shipping_country(),
				'postcode'  => WC()->customer->get_shipping_postcode(),
			),
			'contents'        => array(),
			'contents_cost'   => 0,
			'applied_coupons' => WC()->cart->applied_coupons,
		);

		// Check each package
		foreach ( $packages as $package_key => $package ) {

			// Loop through each of the package's items
			foreach ( $package['contents'] as $item_key => $item ) {

				// If the item is managed by Shipwire
				if ( 'yes' === $item['data']->wc_shipwire_manage_stock ) {

					// Add it to the special package
					$shipwire_package['contents'][] = $item;

					// Add to the total package cost
					$shipwire_package['contents_cost'] += $item['line_total'];

					// Remove it from its original package
					unset( $package['contents'][ $item_key ] );

					// Remove its cost from the original package's total
					$package['contents_cost'] -= $item['line_total'];
				}
			}

			// If the original package still has some items, preserve it
			if ( ! empty( $package['contents'] ) ) {
				$new_packages[ $package_key ] = $package;
			}
		}

		// If the secial package has items, add it too
		if ( ! empty( $shipwire_package['contents'] ) ) {
			$new_packages[] = $shipwire_package;
		}

		return $new_packages;
	}


	/**
	 * Add the tracking information to the Order Completed email.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 * @param bool $sent_to_admin whether this email was sent to the admin
	 * @return string
	 */
	public function add_completed_email_tracking( $order, $sent_to_admin = false, $plain_text = false ) {

		$order = new WC_Shipwire_Order( $order );

		$packages = $order->tracking_packages;

		$count = 1;

		foreach ( $packages as $package ) {

			if ( ! $package['url'] ) {
				continue;
			}

			$title = ( 1 < count( $packages ) ) ? sprintf( __( 'Track Shipment #%s', 'woocommerce-shipwire' ), $count ) : __( 'Track Shipment', 'woocommerce-shipwire' );

			if ( $plain_text ) {
				echo esc_html( $title ) . ': ' . esc_url( $package['url'] ) . "\n\n";
			} else {
				echo '<p style="margin: 16px 0 0;"><a href="' . esc_url( $package['url'] ) . '">' . esc_html( $title ) . '</a></p>';
			}

			$count++;
		}
	}


	/**
	 * Adds the tracking information to the View Order page.
	 *
	 * @since 2.0.0
	 * @param \WC_Order $order the order object
	 */
	public function add_view_order_tracking( $order ) {

		$order = new WC_Shipwire_Order( $order );

		$packages = $order->tracking_packages;

		$count = 1;

		foreach ( $packages as $package ) {

			if ( ! $package['url'] ) {
				continue;
			}

			$title = ( 1 < count( $packages ) ) ? sprintf( __( 'Track Shipment #%s', 'woocommerce-shipwire' ), $count ) : __( 'Track Shipment', 'woocommerce-shipwire' );

			echo '<p class="wc-shipwire-track-shipment">';
				echo '<a href="' . esc_url( $package['url'] ) . '" class="button" target="_blank">' . esc_html( $title ) . '</a>';
			echo '</p>';

			$count++;
		}
	}


	/** Helper Methods ******************************************************/


	/**
	 * Main Shipwire Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.3.0
	 * @see wc_shipwire()
	 * @return WC_Shipwire
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {

		return __( 'WooCommerce Shipwire', 'woocommerce-shipwire' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_file()
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {

		return __FILE__;
	}


	/**
	 * Gets the URL to the settings page
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @param string $_ unused
	 * @return string URL to the settings page
	 */
	public function get_settings_url( $_ = '' ) {

		return admin_url( 'admin.php?page=wc-settings&tab=shipwire' );
	}


	/**
	 * Returns true if on the plugin settings page
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::is_plugin_settings()
	 * @return boolean true if on the settings page
	 */
	public function is_plugin_settings() {

		return isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset( $_GET['tab'] ) && 'shipwire' == $_GET['tab'];
	}


	/**
	 * Determine if the plugin is fully configured.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_configured() {
		return get_option( 'wc_shipwire_api_username' ) && get_option( 'wc_shipwire_api_password' );
	}


	/**
	 * Saves errors or messages to WooCommerce Log (woocommerce/logs/shipwire-xxx.txt)
	 *
	 * @since 1.0.0
	 * @see SV_WC_Plugin::log()
	 * @param string $message the message to log
	 * @param string $_ unused
	 */
	public function log( $message, $_ = null ) {

		if ( $this->debug_log_enabled() ) {
			parent::log( $message );
		}
	}


	/**
	 * Determine if debug logging is enabled.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	private function debug_log_enabled() {

		/**
		 * Filter whether debug logging is enabled.
		 *
		 * @since 2.0.0
		 * @param bool $logging_enabled Whether debug logging is enabled.
		 */
		return apply_filters( 'wc_shipwire_debug_log_enabled', ( 'yes' === get_option( 'wc_shipwire_debug_mode' ) ) );
	}


	/**
	 * Initializes the and returns Shipwire API object
	 *
	 * @since 1.0
	 * @return \WC_Shipwire_API instance
	 */
	public function get_api() {

		// return API object if already instantiated
		if ( is_object( $this->api ) ) {
			return $this->api;
		}

		// load API class
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-shipwire-api.php' );
		require_once( $this->get_plugin_path() . '/includes/api/class-wc-shipwire-api-order.php' );

		// request classes
		require_once( $this->get_plugin_path() . '/includes/api/requests/abstract-wc-shipwire-api-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-shipwire-api-order-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-shipwire-api-rate-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-shipwire-api-stock-request.php' );
		require_once( $this->get_plugin_path() . '/includes/api/requests/class-wc-shipwire-api-webhook-request.php' );

		// response classes
		require_once( $this->get_plugin_path() . '/includes/api/responses/abstract-wc-shipwire-api-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-shipwire-api-order-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-shipwire-api-order-resource-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-shipwire-api-rate-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-shipwire-api-stock-response.php' );
		require_once( $this->get_plugin_path() . '/includes/api/responses/class-wc-shipwire-api-webhook-response.php' );

		// get API username/password
		$username = get_option( 'wc_shipwire_api_username' );
		$password = get_option( 'wc_shipwire_api_password' );

		// instantiate API
		return $this->api = new WC_Shipwire_API( $username, $password, get_option( 'wc_shipwire_environment', 'sandbox' ) );
	}


	/**
	 * Log inventory / tracking updates for display in the 'Update Log' list table in the settings section
	 *
	 * @since 1.0
	 * @param string $message update message to display
	 */
	public function log_update( $message ) {

		// don't log empty messages
		if ( ! $message ) {
			return;
		}

		// load existing logs
		$log = get_option( 'wc_shipwire_update_log', array() );

		// add timestamp and message
		$log[] = array( 'timestamp' => time(), 'action' => $message );

		// prune oldest log entry if over 50 items
		if ( count( $log ) > 50 ) {
			array_shift( $log );
		}

		// update log
		update_option( 'wc_shipwire_update_log', $log );
	}


	/**
	 * Gets the plugin documentation url
	 *
	 * @since 1.4.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string documentation URL
	 */
	public function get_documentation_url() {
		return 'http://docs.woothemes.com/document/shipwire/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.4.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'http://support.woothemes.com/';
	}


	/** Lifecycle Methods ******************************************************/


	/**
	 * Install default settings
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::install()
	 */
	protected function install() {

		require_once( $this->get_plugin_path() . '/includes/admin/class-wc-shipwire-admin.php' );

		foreach ( WC_Shipwire_Admin::get_settings( 'settings' ) as $setting ) {

			if ( isset( $setting['default'] ) ) {

				update_option( $setting['id'], $setting['default'] );
			}
		}

		$email_settings = array(
			'enabled'    => 'yes',
			'recipient'  => '',
			'subject'    => '[WooCommerce] Shipwire Order Exception',
			'heading'    => 'Order {order_number} : {shipwire_status}',
			'email_type' => 'html',
		);

		update_option( 'woocommerce_wc_shipwire_order_exception_settings', $email_settings );

		// create and save the webhook data
		// TODO: Temporarily disable webhook support Shipwire is ready to support them. {CW 2016-08-11}
		// if ( $this->is_configured() ) {
			// $this->get_webhook_handler()->set_webhooks();
		// }

		// install default terms on latest possible hook that runs during plugin activation
		add_action( 'shutdown', array( $this, 'delayed_install' ) );
	}


	/**
	 * Install default shipwire order status taxonomy terms
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::install()
	 */
	public function delayed_install() {

		$this->register_shipwire_order_status_taxonomy();

		// install default shipwire order status terms
		$terms = array(
			'wc_shipwire_held'                   => 'Held',
			'wc_shipwire_held_with_warnings'     => 'Held (!)',
			'wc_shipwire_failed'                 => 'Failed',
			'wc_shipwire_shipped'                => 'Shipped'
		);

		foreach ( $terms as $term_slug => $term_name ) {

			if ( ! get_term_by( 'slug', $term_slug, 'shipwire_order_status' ) ) {
				wp_insert_term( $term_name, 'shipwire_order_status', array( 'slug' => $term_slug ) );
			}
		}
	}


	/**
	 * Upgrade to installed version
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::install()
	 */
	protected function upgrade( $installed_version ) {

		// upgrade to version 1.1
		if ( version_compare( $installed_version, '1.1', '<' ) ) {

			delete_option( 'wc_shipwire_is_installed' );

			if ( 'no' == get_option( 'wc_shipwire_notify_admin_failed_order_export' ) ) {

				$email_settings = get_option( 'woocommerce_wc_shipwire_order_exception_settings' );

				$email_settings['enabled'] = 'no';

				update_option( 'woocommerce_wc_shipwire_order_exception_settings', $email_settings );

				delete_option( 'wc_shipwire_notify_admin_failed_order_export' );
			}
		}

		// upgrade to version 2.0.0
		if ( version_compare( $installed_version, '2.0.0', '<' ) ) {

			parent::log( 'Upgrading from ' . $installed_version . ' to 2.0.0' );

			parent::log( 'Updating existing order data' );

			$query_args = array(
				'fields'      => 'ids',
				'post_type'   => 'shop_order',
				'post_status' => 'any',
				'nopaging'    => true,
				'meta_query'  => array(
					array(
						'key'     => '_wc_shipwire_shipwire_id',
						'compare' => 'EXISTS',
					),
				),
			);

			$query = new WP_Query( $query_args );

			foreach ( $query->posts as $order_id ) {

				$order = wc_get_order( $order_id );

				// convert "shipwire IDs" to transaction IDs
				update_post_meta( $order_id, '_wc_shipwire_transaction_id', $order->wc_shipwire_shipwire_id );

				if ( ! $order->wc_shipwire_carrier && ! $order->wc_shipwire_tracking_number && ! $order->wc_shipwire_tracking_href ) {
					continue;
				}

				update_post_meta( $order_id, '_wc_shipwire_tracking_packages', array( array(
					'carrier'         => trim( $order->wc_shipwire_carrier ),
					'tracking_number' => trim( $order->wc_shipwire_tracking_number ),
					'url'             => trim( $order->wc_shipwire_tracking_href ),
				) ) );
			}

			// create and save the webhook data
			// TODO: Temporarily disable webhook support Shipwire is ready to support them. {CW 2016-08-11}
			// if ( $this->is_configured() ) {
				// $this->get_webhook_handler()->set_webhooks();
			// }

			parent::log( 'Finished upgrading to 2.0.0' );

		}
	}


} // end WC_Shipwire


/**
 * Returns the One True Instance of Shipwire
 *
 * @since 1.3.0
 * @return WC_Shipwire
 */
function wc_shipwire() {
	return WC_Shipwire::instance();
}


// fire it up!
wc_shipwire();


} // init_woocommerce_shipwire()
