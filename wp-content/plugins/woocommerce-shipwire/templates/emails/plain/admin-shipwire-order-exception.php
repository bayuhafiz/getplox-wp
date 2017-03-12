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
 * Admin Shipwire Order Exception Plaintext Email
 *
 * @since 1.1
 * @version 1.1
 */

echo $email_heading . "\n\n";

/* translators: Placeholders: %1$s - order number, %2$s - shipwire status */
echo sprintf( __( 'Order %1$s encountered an exception during export to Shipwire, order status changed to %2$s.', 'woocommerce-shipwire' ), $order->get_order_number(), $order->get_shipwire_status_for_display() ) . "\n\n";

echo sprintf( __( 'View this order: %s', 'woocommerce-shipwire' ), get_edit_post_link( $order->id ) ) . "\n\n";

echo "****************************************************\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
