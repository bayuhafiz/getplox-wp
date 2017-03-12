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
 * @package     WC-Shipwire/Shipping-Method
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;


/**
 * Shipwire Shipping Method Class
 *
 * Provides real-time shipping rates from Shipwire for any product that is listed as Shipwire-managed
 *
 * @since 1.0
 * @extends \WC_Shipping_Method
 */
class WC_Shipwire_Shipping_Method extends WC_Shipping_Method {


	/** @var string handling fee in either amount or % */
	public $handling_fee;

	/** @var string additional handling fee applied to each item in the order after the first */
	public $additional_handling_fee;

	/** @var string show delivery estimates with rates */
	public $show_delivery_estimate;

	/** @var string require provided rates to include delivery confirmation */
	public $require_delivery_confirmation;

	/** @var string required provided rates to include tracking information */
	public $require_tracking;

	/** @var string show actual carrier/service labels (e.g. UPS Ground) instead of generic label (e.g. Ground Shipping) */
	public $show_service;

	// these are used if $show_service = 'no'

	/** @var string label for shipwire ground service  */
	public $label_gd;

	/** @var string label for shipwire next day service */
	public $label_1d;

	/** @var string label for shipwire two day service */
	public $label_2d;

	/** @var string label for shipwire freight service */
	public $label_ft;

	/** @var string label for shipwire express international service */
	public $label_e_intl;

	/** @var string label for shipwire international service */
	public $label_intl;

	/** @var string label for shipwire international-pl service */
	public $label_pl_intl;

	/** @var string label for shipwire international-pm service */
	public $label_pm_intl;


	/**
	 * Loads shipping method and related settings
	 *
	 * @access public
	 * @since  1.0
	 * @return \WC_Shipwire_Shipping_Method
	 */
	public function __construct() {

		// method ID / title
		$this->id = 'shipwire';
		$this->method_title = __( 'Shipwire', 'woocommerce-shipwire' );

		// save settings hook
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		$this->admin_page_heading     = __( 'Shipwire', 'woocommerce-shipwire' );
		$this->admin_page_description = __( 'Provide real-time shipping rates from Shipwire to your customers.', 'woocommerce-shipwire' );

		// Load form fields
		$this->init_form_fields();

		// Load settings
		$this->init_settings();

		// Define user-set variables
		foreach ( $this->settings as $setting_key => $setting_value ) {
			$this->$setting_key = $setting_value;
		}
	}


	/**
	 * Calculate shipping by sending destination/items to Shipwire and parsing returned rates
	 *
	 * @since 1.0
	 * @param array $package
	 */
	public function calculate_shipping( $package = array() ) {

		// country required for all shipments
		if ( ! $package['destination']['country'] ) {
			return;
		}

		// postal code required for US shipments only
		if ( 'US' === $package['destination']['country'] && ! $package['destination']['postcode'] ) {
			return;
		}

		try {

			$response = wc_shipwire()->get_api()->get_shipping_rates( $package );

			// add rates
			$this->add_rates( $response->get_rates(), $package );

		} catch( Exception $e ) {

			wc_shipwire()->log( $e->getMessage() );
		}
	}


	/**
	 * Add rates provided by Shipwire along with delivery estimates if provided & enabled
	 *
	 * @since 1.0
	 * @param array $shipwire_rates
	 * @param array $shipment
	 */
	private function add_rates( $shipwire_rates, $package ) {

		if ( ! is_array( $shipwire_rates ) || empty( $shipwire_rates ) ) {
			return;
		}

		foreach ( $shipwire_rates as $shipwire_rate ) {

			// require delivery confirmation
			if ( 'yes' === $this->require_delivery_confirmation && false === $shipwire_rate['service_delivery_confirmation'] ) {
				continue;
			}

			// require trackable service
			if ( 'yes' === $this->require_tracking && false === $shipwire_rate['service_trackable'] ) {
				continue;
			}

			// use user-set labels for services instead of carrier-provided labels
			if ( 'no' === $this->show_service ) {
				$label = 'label_' . strtolower( str_replace( '-', '_', $shipwire_rate['id'] ) );
				$shipwire_rate['service_name'] = $this->$label;
			}

			// show delivery estimates if enabled
			if ( 'yes' === $this->show_delivery_estimate ) {

				$delivery_date = $this->format_delivery_date( $shipwire_rate['delivery_estimate_min'], $shipwire_rate['delivery_estimate_max'] );

				$shipwire_rate['service_name'] = apply_filters( 'wc_shipwire_shipping_method_delivery_date', $shipwire_rate['service_name'] . ' ' . $delivery_date );
			}

			// build rate
			$rate = array(
				'id'    => $this->id . '_' . $shipwire_rate['id'],
				'label' => $shipwire_rate['service_name'],
				'cost'  => $shipwire_rate['cost']
			);

			// add handling fee
			if ( $this->handling_fee > 0 ) {

				$rate['cost'] += $this->get_fee( $this->handling_fee, $package['contents_cost'] );

				// add additional handling fee if there's more than 1 item in the cart, only applied to each item past the first item
				if ( $this->additional_handling_fee > 0 && WC()->cart->cart_contents_count > 1 ) {
					$rate['cost'] += ( $this->additional_handling_fee * ( WC()->cart->cart_contents_count - 1 ) );
				}
			}

			// add as valid rate
			$this->add_rate( apply_filters( 'wc_shipwire_shipping_method_rate', $rate, $shipwire_rate, $package, $this ) );
		}
	}


	/**
	 * Format estimated delivery dates according to site date format
	 *
	 * @since 1.0
	 * @param string $min_time minimum time in transit for delivery (e.g. '2 days')
	 * @param string $max_time maximum time in transit for delivery (e.g. '4 days')
	 * @return string formatted datetime in "Estimated delivery on {date}" or "Estimated delivery between {date} and {date}"
	 */
	private function format_delivery_date( $min_time, $max_time ) {

		// shipping time estimates are business days
		$min_time = strtotime( str_replace( 'days', 'weekdays', $min_time ) );
		$max_time = strtotime( str_replace( 'days', 'weekdays', $max_time ) );

		// add a day when the delivery estimate is short so the customer has a reasonable expectation
		if ( $min_time == $max_time ) {
			$max_time += DAY_IN_SECONDS;
		}

		// pretty format
		$from_date = date_i18n( wc_date_format(), $min_time );
		$to_date   = date_i18n( wc_date_format(), $max_time );

		/* translators: Placeholders: %1$s - from date, %2$s - to date */
		return sprintf( __( '(Estimated delivery between %1$s and %2$s)', 'woocommerce-shipwire' ), $from_date, $to_date );
	}


	/**
	 * Checks if shipping is available by using these checks :
	 * 1) The destination country must be entered
	 * 2) If the destination country is the US, ZIP Code must be entered
	 *
	 * @since 1.0
	 * @param array $shipment
	 * @return bool
	 */
	public function is_available( $shipment = array() ) {

		// standard availability checks (is enabled, ship to is in admin-selected allowed countries, etc)
		$is_available = parent::is_available( $shipment );


		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_2_6() ) {

			// it seems that in WC 2.6+ we do need to check if the shipping method is enabled,
			// otherwise it will appear enabled straight after activation {TZ 2016-05-26}
			$is_available = $this->is_enabled() && $is_available;
		}

		return apply_filters( 'wc_shipwire_shipping_method_is_available', $is_available, $shipment, $this );
	}


	/**
	 * Initialize Shipping Settings Form Fields
	 *
	 * @since 1.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-shipwire' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this shipping method', 'woocommerce-shipwire' ),
				'default' => 'no',
			),

			'availability' => array(
				'title'   => __( 'Method availability', 'woocommerce-shipwire' ),
				'type'    => 'select',
				'default' => 'all',
				'class'   => 'availability',
				'options' => array(
					'all'      => __( 'All allowed countries', 'woocommerce-shipwire' ),
					'specific' => __( 'Specific Countries', 'woocommerce-shipwire' ),
				),
			),

			'countries' => array(
				'title'   => __( 'Specific Countries', 'woocommerce-shipwire' ),
				'type'    => 'multiselect',
				'class'   => 'wc-enhanced-select',
				'css'     => 'width: 450px;',
				'default' => '',
				'options' => WC()->countries->countries,
			),

			'tax_status' => array(
				'title'       => __( 'Tax Status', 'woocommerce-shipwire' ),
				'type'        => 'select',
				'description' => '',
				'default'     => 'taxable',
				'options'     => array(
					'taxable' => __( 'Taxable', 'woocommerce-shipwire' ),
					'none'    => __( 'None', 'woocommerce-shipwire' ),
				),
			),

			'handling_fee' => array(
				'title'       => __( 'Handling Fee', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Leave blank to disable.', 'woocommerce-shipwire' ),
				'default'     => '',
			),

			'additional_handling_fee' => array(
				'title'       => __( 'Additional Handling Fee', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Enter an additional handling fee, excluding tax, that is applied to each item in the order after the first item.', 'woocommerce-shipwire' ),
				'default'     => '',
			),

			'show_delivery_estimate' => array(
				'title'       => __( 'Show Delivery Estimates', 'woocommerce-shipwire' ),
				'type'        => 'checkbox',
				'description' => __( 'Show delivery estimates along with shipping services.', 'woocommerce-shipwire' ),
				'default'     => 'yes'
			),

			'require_delivery_confirmation' => array(
				'title'       => __( 'Require Delivery Confirmation', 'woocommerce-shipwire' ),
				'type'        => 'checkbox',
				'description' => __( 'Check this to only show rates for services that include delivery confirmation.', 'woocommerce-shipwire' ),
				'default'     => 'no'
			),

			'require_tracking' => array(
				'title'       => __( 'Require Tracking', 'woocommerce-shipwire' ),
				'type'        => 'checkbox',
				'description' => __( 'Check this to only show rates for services that include tracking information.', 'woocommerce-shipwire' ),
				'default'     => 'no'
			),

			'show_service' => array(
				'title'       => __( 'Show Carrier Name / Service Level', 'woocommerce-shipwire' ),
				'type'        => 'checkbox',
				'class'       => 'hide_service_labels_if_checked',
				'description' => __( 'Show actual carrier name and service level (e.g. UPS Ground) for methods instead of generic names (e.g. Ground Shipping).', 'woocommerce-shipwire' ),
				'default'     => 'yes'
			),

			'label_gd' => array(
				'title'       => __( 'GD Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for GD Service.', 'woocommerce-shipwire' ),
				'default'     => 'Ground Shipping'
			),

			'label_1d' => array(
				'title'       => __( '1D Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for 1D Service.', 'woocommerce-shipwire' ),
				'default'     => 'Next Day Shipping'
			),

			'label_2d' => array(
				'title'       => __( '2D Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for GD Service.', 'woocommerce-shipwire' ),
				'default'     => '2 Day Shipping'
			),

			'label_ft' => array(
				'title'       => __( 'FT Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for FT Service.', 'woocommerce-shipwire' ),
				'default'     => 'Standard Shipping'
			),

			'label_e_intl' => array(
				'title'       => __( 'E-INTL Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for E-INTL Service.', 'woocommerce-shipwire' ),
				'default'     => 'Express International Shipping'
			),

			'label_intl' => array(
				'title'       => __( 'INTL Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for INTL Service.', 'woocommerce-shipwire' ),
				'default'     => 'Standard International Shipping'
			),

			'label_pl_intl' => array(
				'title'       => __( 'PL-INTL Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for PL-INTL Service.', 'woocommerce-shipwire' ),
				'default'     => 'PL International Shipping'
			),

			'label_pm_intl' => array(
				'title'       => __( 'PM-INTL Service Label', 'woocommerce-shipwire' ),
				'type'        => 'text',
				'description' => __( 'Label for PM-INTL Service.', 'woocommerce-shipwire' ),
				'default'     => 'PM International Shipping'
			),

		);
	}


	/**
	 * Show admin settings page and add some JS to control field visibility
	 *
	 * @since 1.0
	 */
	public function admin_options() {

		parent::admin_options();

		ob_start();
		?>
		// Hide service labels if admin opts to show carrier service levels
		$( '.hide_service_labels_if_checked' ).change( function() {

				if ( $( this ).is( ':checked' ) ) {
					$( this ).closest( 'tr' ).nextUntil( 'p' ).hide();
				} else {
					$( this ).closest( 'tr' ).nextUntil( 'p' ).show();
				}
		} ).change();
		// Hide additional handling fee field if there's no base handling fee entered
		$( 'input[name="woocommerce_shipwire_handling_fee"]' ).change( function() {
			if ( '' === $( this ).val() ) {
				$( 'input[name="woocommerce_shipwire_additional_handling_fee"]' ).closest( 'tr' ).hide();
			} else {
				$( 'input[name="woocommerce_shipwire_additional_handling_fee"]' ).closest( 'tr' ).show();
			}
		} ).change();

		<?php
		wc_enqueue_js( ob_get_clean() );
	}


} //end \WC_Shipwire_Shipping_Method class
