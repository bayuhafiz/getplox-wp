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
 * @package     WC-Shipwire/Emails
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Custom email for Shipwire Order Exceptions
 *
 * @since 1.1
 * @extends \WC_Email
 */
class WC_Shipwire_Order_Exception_Email extends WC_Email {


	/**
	 * Set email defaults
	 *
	 * @since 1.1
	 */
	public function __construct() {

		// defaults
		$this->id          = 'wc_shipwire_order_exception';
		$this->title       = __( 'Shipwire Order Exception', 'woocommerce-shipwire' );
		$this->description = __( 'Shipwire order exception emails are sent when an order encounters an issue when exported to Shipwire, such as an invalid address.' );

		/* translators: Placeholders: %1$s - order number, %2$s - shipwire status */
		$this->heading     = sprintf( __( 'Order %1$s : %2$s', 'woocommerce-shipwire' ), '{order_number}', '{shipwire_status}' );
		$this->subject     = sprintf( __( '[%s] Shipwire Order Exception', 'woocommerce-shipwire' ), get_bloginfo( 'name' ) );

		$this->template_base = wc_shipwire()->get_plugin_path() . '/templates/';
		$this->template_html  = 'emails/admin-shipwire-order-exception.php';
		$this->template_plain = 'emails/plain/admin-shipwire-order-exception.php';

		// trigger on held/failed statuses
		add_action( 'wc_shipwire_order_exception_notification', array( $this, 'trigger' ) );

		// set any non-specified defaults
		parent::__construct();

		// other settings
		$this->recipient = $this->get_option( 'recipient' );

		// if none was entered, just use the WP admin email as a fallback
		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}


	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 1.1
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {

		// bail if no order ID is present
		if ( ! $order_id ) {
			return;
		}

		// setup order object
		$this->object = new WC_Shipwire_Order( $order_id );

		// replace variables in the subject/headings
		$this->find[] = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

		$this->find[] = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		$this->find[] = '{shipwire_status}';
		$this->replace[] = $this->object->get_shipwire_status_for_display();

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}


	/**
	 * Gets the email HTML content
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}


	/**
	 * Gets the email plaintext content
	 *
	 * @since 1.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading()
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}


	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 1.1
	 */
	public function init_form_fields() {

		$this->form_fields = array(

			'enabled'    => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),

			'recipient'  => array(
				'title'       => 'Recipient(s)',
				'type'        => 'text',
				'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => ''
			),

			'subject'    => array(
				'title'       => 'Subject',
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),

			'heading'    => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			),

			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type',
				'options' => array(
					'plain'     => __( 'Plain text', 'woocommerce' ),
					'html'      => __( 'HTML', 'woocommerce' ),
					'multipart' => __( 'Multipart', 'woocommerce' ),
				)
			),
		);
	}


} // end \WC_Shipwire_Order_Exception_Email class
