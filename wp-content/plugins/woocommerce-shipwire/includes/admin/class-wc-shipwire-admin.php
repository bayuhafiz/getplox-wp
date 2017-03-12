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
 * @package     WC-Shipwire/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * Shipwire Admin Class
 *
 * Loads / saves the admin settings page, Adds Order / Product admin page customizations
 *
 * @since 1.0
 */
class WC_Shipwire_Admin {


	/** @var string settings tab id */
	public $tab_id = 'shipwire';

	/** @var array settings sections */
	public $sections;

	/** @var \SV_WP_Admin_Message_Handler instance */
	public $message_handler;


	/**
	 * Add various admin hooks/filters
	 *
	 * @since  1.0
	 */
	public function __construct() {

		/** General Hooks */

		$this->sections = array(
			'settings' => __( 'Settings', 'woocommerce-shipwire' ),
			'log'      => __( 'Update', 'woocommerce-shipwire' ),
		);

		// load necessary admin styles / scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );

		// add 'Shipwire' tab
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 100 );

		// render settings
		add_action( 'woocommerce_settings_' . $this->tab_id, array( $this, 'render_settings' ) );

		// save settings
		add_action( 'woocommerce_update_options_' . $this->tab_id, array( $this, 'save_settings' ) );

		// handle any settings page actions (update tracking/inventory, clear logs)
		add_action( 'admin_init', array( $this, 'handle_settings_actions' ) );

		// show messages for settings page actions (update tracking/inventory, clear logs)
		add_action( 'woocommerce_settings_start', array( $this, 'render_settings_actions_messages' ) );

		/** Order Hooks */

		// add 'Export to Shipwire' action on 'Orders' page
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'add_order_actions' ) );

		// add 'Shipwire Status' column on 'Orders' page
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_status_column_header' ), 20 );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'add_order_status_column' ) );

		// add bulk order filter for shipwire order status
		add_action( 'restrict_manage_posts', array( $this, 'filter_orders_by_shipwire_status') , 20 );

		// add bulk action to export multiple orders to shipwire
		add_action( 'admin_footer-edit.php', array( $this, 'add_order_bulk_actions' ) );
		add_action( 'load-edit.php',         array( $this, 'process_order_bulk_actions' ) );

		// add 'Export to Shipwire' and 'Update Tracking' order meta box order actions
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_meta_box_actions' ) );

		// process order meta box order actions
		add_action( 'woocommerce_order_action_wc_shipwire_update_tracking', array( $this, 'process_order_meta_box_actions' ) );
		add_action( 'woocommerce_order_action_wc_shipwire_export_order',    array( $this, 'process_order_meta_box_actions' ) );

		// add 'Shipwire Information' order meta box
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );

		// display serial numbers for order items
		add_action( 'woocommerce_before_order_itemmeta', array( $this, 'add_order_item_serial_numbers' ), 10, 3 );

		/** Product Hooks */

		// add 'Shipwire Managed' select to Product 'General' tab
		add_action( 'woocommerce_product_options_sku', array( $this, 'add_shipwire_managed_field' ) );

		// save 'Shipwire Managed' select to Product 'General' tab
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_shipwire_managed_field' ) );

		// add Products 'Shipwire Managed' bulk edit field
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'add_shipwire_managed_field_bulk_edit' ) );

		// save Products 'Shipwire Managed' bulk edit field
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_shipwire_managed_field_bulk_edit' ) );

		// ensure a product is set as stock managed when also being shipwire managed
		add_action( 'save_post', array( $this, 'force_stock_management' ), 20, 2 );
	}


	/**
	 * Load admin CSS
	 *
	 * @since 1.0
	 * @param string $hook_suffix
	 */
	public function load_styles_scripts( $hook_suffix ) {

		// Load admin CSS only load on settings / order / product pages
		if ( 'edit.php' == $hook_suffix || 'post.php' == $hook_suffix || 'post-new.php' == $hook_suffix ) {
			wp_enqueue_style( 'woocommerce_shipwire_admin', wc_shipwire()->get_plugin_url() . '/assets/css/admin/wc-shipwire-admin.min.css', array( 'woocommerce_admin_styles' ), WC_Shipwire::VERSION );
		}
	}


	/**
	 * Add 'Shipwire' tab to WooCommerce Settings
	 *
	 * @since 1.1
	 * @param array $settings_tabs tabs array sans 'Shipwire' tab
	 * @return array $settings_tabs now with 100% more 'Shipwire' tab!
	 */
	public function add_settings_tab( $settings_tabs ) {

		$settings_tabs[ $this->tab_id ] = __( 'Shipwire', 'woocommerce-shipwire' );

		return $settings_tabs;
	}


	/**
	 * Show Shipwire settings
	 *
	 * @since 1.1
	 */
	public function render_settings() {

		$current_section = ( empty( $_REQUEST['section'] ) ) ? '' : sanitize_text_field( urldecode( $_REQUEST['section'] ) );
		$links = array();

		foreach ( $this->sections as $section_key => $section_title ) {

			$links[] = sprintf(
				'<a href="%s"%s>%s</a>',
				esc_url( add_query_arg( array( 'section' => $section_key ), admin_url( 'admin.php?page=wc-settings&tab=shipwire' ) ) ),
				( $current_section == $section_key || ( ! $current_section && 'settings' == $section_key ) ) ? ' class="current"' : '',
				esc_html( $section_title )
			);
		}

		?><ul class="subsubsub"><li><?php echo implode( ' | </li><li>', $links ); ?></li></ul><br class="clear" /><?php

		// show update log
		if ( 'log' == $current_section ) {

			$this->render_log_section();

		} else {

			// show settings
			woocommerce_admin_fields( $this->get_settings() );
		}

		// tidy up settings
		wc_enqueue_js( "
			// Hide update intervals if auto-updates are disabled on Shipwire settings page
			$( '.show_options_if_checked [type=checkbox]' ).change( function () {
				if ( $( this ).is( ':checked' ) ) {
					$( this ).closest( 'tr' ).nextUntil( 'table' ).show();
				} else {
					$( this ).closest( 'tr' ).nextUntil( 'table' ).hide();
				}
			}).change();
			// Hide allowed countries for automatic order export if auto-exports are disabled
			$( 'input[name=wc_shipwire_auto_export_orders]' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( 'select[name^=wc_shipwire_auto_order_export_countries]' ).closest( 'tr' ).show();
				} else {
					$( 'select[name^=wc_shipwire_auto_order_export_countries]' ).closest( 'tr' ).hide();
				}
			} ).change();
		");
	}


	/**
	 * Save settings page
	 *
	 * @since 1.0
	 */
	public function save_settings() {

		$current_section = ( empty( $_REQUEST['section'] ) ) ? '' : sanitize_text_field( urldecode( $_REQUEST['section'] ) );

		// bail if on the log section as it has no settings to save :)
		if ( 'log' === $current_section ) {
			return;
		}

		woocommerce_update_options( $this->get_settings() );

		/* TODO: Temporarily disable webhook support Shipwire is ready to support them. {CW 2016-08-11}

		$webhook_ids = get_option( 'wc_shipwire_webhook_ids', array() );

		// if we have API credentials but haven't generated webhooks yet, create them
		if ( wc_shipwire()->is_configured() && empty( $webhook_ids ) ) {
			wc_shipwire()->get_webhook_handler()->reset_webhooks();
		}

		*/

		// clear scheduled events in case schedule intervals were changed
		wp_clear_scheduled_hook( 'wc_shipwire_inventory_update' );
		wp_clear_scheduled_hook( 'wc_shipwire_tracking_update' );
	}


	/**
	 * Display the 'Update' page which shows automatic inventory / tracking updates which have occurred
	 *
	 * @since 1.0
	 */
	private function render_log_section() {

		// Hide the save button
		$GLOBALS['hide_save_button'] = true;

		?>
			<h2><?php _e( 'Update', 'woocommerce-shipwire' ); ?></h2>

			<p><?php printf( __( 'Next inventory update at : %s', 'woocommerce-shipwire' ), $this->get_formatted_datetime( wp_next_scheduled( 'wc_shipwire_inventory_update' ) ) ); ?></p>
			<p><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'update_inventory' ) ) ); ?>" class="button" id="wc_shipwire_update_inventory"><?php _e( 'Update Inventory', 'woocommerce-shipwire' ); ?></a></p>

			<p><?php printf( __( 'Next tracking update at : %s', 'woocommerce-shipwire' ), $this->get_formatted_datetime( wp_next_scheduled( 'wc_shipwire_tracking_update' ) ) ); ?></p>
			<p><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'update_tracking' ) ) ); ?>" class="button" id="wc_shipwire_update_tracking"><?php _e( 'Update Tracking', 'woocommerce-shipwire' ); ?></a></p>
		<?php

		// get update log
		$data = get_option( 'wc_shipwire_update_log', array() );

		// build array in the format required for WP_List_Table use, starting with the most recent log entry
		foreach ( array_reverse( $data ) as $log_id => $log_data ) {

			$log[] = array(
				'ID'       => $log_id,
				'datetime' => self::get_formatted_datetime( $log_data['timestamp'] ),
				'action'   => $log_data['action']
			);
		}

		// instantiate extended list table, use empty array if no logs are available
		$log_table = new WC_Shipwire_List_Table( ( empty( $log ) ? array() : $log ) );

		// prepare and display the list table
		$log_table->prepare_items();
		$log_table->display();

		// clear history
		?><p><a href="<?php echo esc_url( add_query_arg( array( 'action' => 'clear_history' ) ) ); ?>" class="button" id="wc_shipwire_clear_history"><?php _e( 'Clear History', 'woocommerce-shipwire' ); ?></a></p><?php
	}


	/**
	 * Handles any actions from the settings/log page
	 *
	 * @since 1.1
	 */
	public function handle_settings_actions() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$current_action = ( empty( $_REQUEST['action'] ) )  ? null : sanitize_text_field( urldecode( $_REQUEST['action'] ) );

		if ( wc_shipwire()->is_plugin_settings() && $current_action ) {

			switch ( $current_action ) {

				case 'update_inventory' :
					$updated_count = WC_Shipwire_Product::update_all_inventory();
					$message = sprintf( _n( 'Updated inventory for %d product.', 'Updated inventory for %d products.', $updated_count, 'woocommerce-shipwire' ), $updated_count );
					break;

				case 'update_tracking' :
					$updated_count = WC_Shipwire_Order::update_all_tracking();
					$message = sprintf( _n( 'Updated tracking information for %d order.', 'Updated tracking information for %d orders.', $updated_count, 'woocommerce-shipwire' ), $updated_count );
					break;

				case 'clear_history' :
					delete_option( 'wc_shipwire_update_log' );
					$message = __( 'History deleted.', 'woocommerce-shipwire' );
					break;

				default :
					$message = __( 'Nothing to do.', 'woocommerce-shipwire' );
					break;
			}

			// success message
			$this->message_handler->add_message( $message );

			$redirect_url = remove_query_arg( array( 'action' ), stripslashes( $_SERVER['REQUEST_URI'] ) );
			wp_redirect( esc_url_raw( $redirect_url ) );
			exit;
		}
	}


	/**
	 * Render any messages set after handling settings actions
	 *
	 * @since 1.2.1
	 */
	public function render_settings_actions_messages() {

		if ( wc_shipwire()->is_plugin_settings() ) {

			$this->message_handler->show_messages();
		}
	}


	/**
	 * Format a given timestamp in the site's timezone and date / time format
	 * Try/Catch blocks are required as DateTime class throws exceptions
	 *
	 * @since 1.0
	 * @param int $timestamp
	 * @return string formatted datetime
	 */
	private function get_formatted_datetime( $timestamp ) {

		if ( ! $timestamp ) {
			return __( 'N/A', 'woocommerce-shipwire' );
		}

		try {

			// get datetime object from unix timestamp
			$datetime = new DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );

			// change timezone to site timezone
			$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );

			// return datetime formatted in site date/time format & localized to locale
			return date_i18n( wc_date_format() . ' ' . wc_time_format(), $timestamp + $datetime->getOffset() );

		} catch ( Exception $e ) {

			return __( 'N/A', 'woocommerce-shipwire' );
		}
	}


	/**
	 * Add 'Shipwire Information' meta-box to 'Edit Order' page
	 *
	 * @since 1.0
	 */
	public function add_order_meta_box() {

		add_meta_box(
			'wc_shipwire_order_meta_box',
			__( 'Shipwire Order Information', 'woocommerce-shipwire' ),
			array( $this, 'render_order_meta_box'),
			'shop_order',
			'side',
			'high'
		);
	}


	/**
	 * Display the 'Shipwire Information' meta-box on the 'Edit Order' page
	 *
	 * @since 1.0
	 */
	public function render_order_meta_box() {
		global $post;

		$order = new WC_Shipwire_Order( $post->ID );

		// don't show any information for new orders
		if ( 'new' == $order->shipwire_status ) {
			echo "<p>" . __( 'Shipwire order information will be displayed here once this order is exported.', 'woocommerce-shipwire' ) . "</p>";
			return;
		}
		?>
		<table id="wc_shipwire_order_meta_box">

			<tr>
				<th><strong><?php esc_html_e( 'Order ID', 'woocommerce-shipwire' ) ?> : </strong></th>
				<td><?php echo esc_html( ( empty( $order->wc_shipwire_order_id ) ) ? __( 'N/A', 'woocommerce-shipwire' ) : $order->wc_shipwire_order_id ); ?></td>
			</tr>
			<tr>
				<th><strong><?php esc_html_e( 'Transaction ID', 'woocommerce-shipwire' ) ?> : </strong></th>
				<td><?php echo esc_html( ( empty( $order->wc_shipwire_transaction_id ) ) ? __( 'N/A', 'woocommerce-shipwire' ) : $order->wc_shipwire_transaction_id ); ?></td>
			</tr>
			<tr>
				<th><strong><?php esc_html_e( 'Status', 'woocommerce-shipwire' ) ?> : </strong></th>
				<td class="shipwire_status">
					<mark class="<?php echo esc_attr( $order->shipwire_status ); ?>"><?php echo esc_html( $order->get_shipwire_status_for_display() ); ?></mark>
				</td>
			</tr>

			<?php if ( 'held' === $order->shipwire_status && ! empty( $order->holds ) ) : ?>

				<tr>
					<th><strong><?php esc_html_e( 'Holds', 'woocommerce-shipwire' ) ?> : </strong></th>
					<td>
						<?php foreach ( $order->holds as $hold ) : ?>
							<?php echo esc_html( $hold ); ?><br />
						<?php endforeach; ?>
					</td>
				</tr>

			<?php elseif ( 'completed' === $order->shipwire_status || 'shipped' === $order->shipwire_status ) : ?>

				<tr>
					<th><strong><?php esc_html_e( 'Warehouse', 'woocommerce-shipwire' ) ?> : </strong></th>
					<td><?php echo esc_html( ( empty( $order->ship_from_warehouse ) ) ? __( 'N/A', 'woocommerce-shipwire' ) : $order->ship_from_warehouse ); ?></td>
				</tr>

				<tr>
					<th><strong><?php esc_html_e( 'Ship via', 'woocommerce-shipwire' ) ?> : </strong></th>
					<td><?php echo esc_html( ( empty( $order->shipper_full_name ) ) ? __( 'N/A', 'woocommerce-shipwire' ) : $order->shipper_full_name ); ?></td>
				</tr>

				<tr>
					<th><strong><?php esc_html_e( 'Expected Delivery', 'woocommerce-shipwire' ) ?> : </strong></th>
					<td><?php echo esc_html( empty( $order->expected_delivery_date ) ) ? __( 'N/A', 'woocommerce-shipwire' ) : date_i18n( wc_date_format(), strtotime( $order->expected_delivery_date ) ); ?></td>
				</tr>

				<?php $package_count = 1; ?>

				<?php foreach ( $order->tracking_packages as $id => $package ) : ?>

					<?php if ( 1 < count( $order->tracking_packages ) ) : ?>
						<tr>
							<th><strong><?php printf( __( 'Package %s', 'woocommerce-shipwire' ), $package_count + 1 ); ?></strong></th>
							<td>&nbsp;</td>
						</tr>
					<?php endif; ?>

					<?php $package_count++; ?>

					<tr>
						<th><strong><?php esc_html_e( 'Carrier', 'woocommerce-shipwire' ) ?> : </strong></th>
						<td><?php echo esc_html( $package['carrier'] ); ?></td>
					</tr>
					<tr>
						<th><strong><?php esc_html_e( 'Tracking Number', 'woocommerce-shipwire' ) ?> : </strong></th>

						<?php $tracking_number = esc_html( $package['tracking_number'] ); ?>

						<?php $tracking_number = ( $package['url'] ) ? '<a href="' . esc_url( $package['url'] ). '" target="_blank">' . $tracking_number . '</a>' : $tracking_number; ?>

						<td><?php echo $tracking_number; ?></td>
					</tr>

				<?php endforeach; ?>

			<?php endif; ?>

		</table><?php
	}


	/**
	 * Add 'Export to Shipwire' order action button to the 'Order Actions' column on the 'Edit Orders' page
	 *
	 * @since 1.0
	 * @param WC_Order $order
	 */
	public function add_order_actions( $order ) {

		$order = new WC_Shipwire_Order( $order->id );

		if ( ! $order->is_exported ) {

			$url = wp_nonce_url( admin_url( 'admin-ajax.php?action=wc_shipwire_export_order&order_id=' . $order->id ), 'wc_shipwire_export_order' );
			$name = __( 'Export to Shipwire', 'woocommerce-shipwire' );

			printf( '<a class="button tips export_to_shipwire" href="%1$s" data-tip="%2$s">%2$s</a>', esc_url( $url ), $name );
		}
	}


	/**
	 * Add 'Shipwire Status' column header to the 'Edit Orders' page
	 *
	 * @since 1.0
	 * @param array $column_headers
	 * @return array new columns
	 */
	public function add_order_status_column_header( $column_headers ) {

		$new_column_headers = array();

		foreach ( $column_headers as $column_id => $column_info ) {

			$new_column_headers[ $column_id ] = $column_info;

			if ( 'order_status' == $column_id ) {
				$new_column_headers['shipwire_status'] = __( 'Shipwire Status', 'woocommerce-shipwire' );
			}
		}

		return $new_column_headers;
	}


	/**
	 * Add 'Shipwire Status' column content to the 'Edit Orders' page
	 *
	 * @since 1.0
	 * @param array $column
	 */
	public function add_order_status_column( $column ) {
		global $post;

		if ( 'shipwire_status' == $column ) {

			$order = new WC_Shipwire_Order( $post->ID );

			printf( '<mark class="%1$s">%2$s</mark>', esc_attr( $order->shipwire_status ), strtolower( $order->get_shipwire_status_for_display() ) );
		}
	}


	/**
	 * Add bulk filter for Shipwire order status
	 *
	 * @since 1.0
	 */
	public function filter_orders_by_shipwire_status() {
		global $typenow, $wp_query;

		if ( 'shop_order' != $typenow ) {
			return;
		}

		$terms = get_terms( 'shipwire_order_status' );

		?>
		<select name="shipwire_order_status" id="dropdown_shipwire_order_status" class="wc-enhanced-select" data-placeholder="<?php _e( 'Show all Shipwire orders', 'woocommerce-shipwire' ); ?>" style="min-width: 190px;">
			<option value=""></option>
			<?php foreach ( $terms as $term ) : ?>
				<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $term->slug, ( isset( $wp_query->query['shipwire_order_status'] ) ) ? $wp_query->query['shipwire_order_status'] : '' ); ?>>
					<?php printf( '%1$s (%2$s)', $term->name, absint( $term->count ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}


	/**
	 * Add "Export to Shipwire" custom bulk action to the 'Orders' page bulk action drop-down
	 *
	 * @since 1.0
	 */
	public function add_order_bulk_actions() {
		global $post_type, $post_status;

		if ( $post_type == 'shop_order' && $post_status != 'trash' ) {
			?>
				<script type="text/javascript">
					jQuery( document ).ready( function ( $ ) {
						$( 'select[name^=action]' ).append(
							$( '<option>' ).val( 'export_to_shipwire' ).text( '<?php _e( 'Export to Shipwire', 'woocommerce-shipwire' ); ?>' ),
							$( '<option>' ).val( 'update_tracking' ).text( '<?php _e( 'Update Tracking', 'woocommerce-shipwire' ); ?>' )
						);
					});
				</script>
			<?php
		}
	}


	/**
	 * Processes the "Export to Shipwire" & "Update Tracking" custom bulk actions on the 'Orders' page bulk action drop-down
	 *
	 * @since  1.0
	 */
	public function process_order_bulk_actions() {
		global $typenow;

		if ( 'shop_order' == $typenow ) {

			// get the action
			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			// return if not processing our actions
			if ( ! in_array( $action, array( 'export_to_shipwire', 'update_tracking' ) ) ) {
				return;
			}

			// security check
			check_admin_referer( 'bulk-posts' );

			// make sure order IDs are submitted
			if ( isset( $_REQUEST['post'] ) ) {
				$order_ids = array_map( 'absint', $_REQUEST['post'] );
			}

			// return if there are no orders to export
			if ( empty( $order_ids ) ) {
				return;
			}

			// give ourselves an unlimited timeout if possible
			@set_time_limit( 0 );

			foreach ( $order_ids as $order_id ) {

				$order = new WC_Shipwire_Order( $order_id );

				if ( 'export_to_shipwire' === $action ) {
					$order->export();
				} else {
					$order->update_tracking();
				}
			}
		}
	}


	/**
	 * Add 'Export to Shipwire' and 'Update Tracking'' order actions to the 'Edit Order' page
	 *
	 * @since 1.0
	 * @param array $actions
	 * @return array
	 */
	public function add_order_meta_box_actions( $actions ) {
		global $theorder;

		$order = new WC_Shipwire_Order( $theorder->id );

		// add update tracking action
		$actions['wc_shipwire_update_tracking'] = __( 'Update Shipwire tracking info', 'woocommerce-shipwire' );

		// add export order action if not already exported
		if ( ! $order->is_exported ) {
			$actions['wc_shipwire_export_order'] = __( 'Export order to Shipwire', 'woocommerce-shipwire' );
		}

		return $actions;
	}


	/**
	 * Handle actions from the 'Edit Order' order action select box
	 *
	 * @since 1.0
	 * @param object $order WC_Order object
	 */
	public function process_order_meta_box_actions( $order ) {

		$order = new WC_Shipwire_Order( $order->id );

		if ( 'woocommerce_order_action_wc_shipwire_export_order' === current_filter() ) {
			$order->export();
		} else {
			$order->update_tracking();
		}
	}


	/**
	 * Display serial numbers for order items.
	 *
	 * @since 2.0.0
	 * @param int $item_id the order ID
	 * @param array $item the order item and its meta
	 * @param \WC_Product $product the product object
	 */
	public function add_order_item_serial_numbers( $item_id, $item, $product ) {

		$serial_numbers = isset( $item['wc_shipwire_serial_numbers'] ) ? maybe_unserialize( $item['wc_shipwire_serial_numbers'] ) : array();

		if ( ! empty( $serial_numbers ) ) {
			echo '<div class="wc-shipwire-order-item-serial-numbers"><strong>' . _n( 'Serial Number:', 'Serial Numbers:', count( $serial_numbers ), 'woocommerce-shipwire' ) . '</strong> ' . esc_html( implode( ', ', $serial_numbers ) ) . '</div>';
		}
	}


	/** Product Admin Methods ******************************************************/


	/**
	 * Add field to indicate the product exists in Shipwire and stock/shipping can be managed
	 *
	 * @since 1.1
	 */
	public function add_shipwire_managed_field() {

		woocommerce_wp_checkbox(
			array(
				'id'          => '_wc_shipwire_manage_stock',
				'label'       => __( 'Shipwire Managed?', 'woocommerce-shipwire' ),
				'description' => __( 'Enable this if this product is listed in Shipwire. This will sync inventory and provide shipping rates for this product from Shipwire.', 'woocommerce-shipwire' ),
			)
		);
	}

	/**
	 * Add meta to product (parent product for variations) to indicate the product exists in Shipwire and stock/shipping can be managed
	 *
	 * @since 1.0
	 * @param int $post_id
	 */
	public function save_shipwire_managed_field( $post_id ) {

		update_post_meta( $post_id, '_wc_shipwire_manage_stock', ( isset( $_POST['_wc_shipwire_manage_stock'] ) ) ? 'yes' : 'no' );
	}


	/**
	 * Add a "Shipwire Managed?" select box to the product bulk edit area
	 *
	 * @since 1.1
	 */
	public function add_shipwire_managed_field_bulk_edit() {

		?>
			<div class="inline-edit-group">
				<label class="alignleft">
					<span class="title"><?php _e( 'Shipwire Managed?', 'woocommerce-shipwire' ); ?></span>
						<span class="input-text-wrap">
							<select class="change_wc_shipwire_managed change_to" name="change_wc_shipwire_managed">
								<?php
								$options = array(
									''    => __( '— No Change —', 'woocommerce-shipwire' ),
									'yes' => __( 'Yes', 'woocommerce-shipwire' ),
									'no'  => __( 'No', 'woocommerce-shipwire' )
								);
								foreach ( $options as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</span>
				</label>
			</div>
		<?php
	}


	/**
	 * Process the product bulk edit to mark product(s) as shipwire managed
	 *
	 * @since 1.0
	 * @param WC_Product $product
	 */
	public function save_shipwire_managed_field_bulk_edit( $product ) {

		// update option
		if ( ! empty( $_REQUEST['change_wc_shipwire_managed'] ) ) {
			update_post_meta( $product->id, '_wc_shipwire_manage_stock', ( 'yes' === $_REQUEST['change_wc_shipwire_managed'] ) ? 'yes' : 'no' );
		}
	}


	/**
	 * Force stock management for a product if it's shipwire managed
	 *
	 * @since 1.1
	 * @param int $product_id
	 * @param WP_Post $post
	 */
	public function force_stock_management( $product_id, $post ) {

		if ( 'product' !== $post->post_type ) {
			return;
		}

		$shipwire_managed = ( 'yes' === get_post_meta( $product_id, '_wc_shipwire_manage_stock', true ) );

		if ( $shipwire_managed ) {

			update_post_meta( $product_id, '_manage_stock', 'yes' );
		}
	}


	/**
	 * Get settings array
	 *
	 * @since 1.0
	 * @return array
	 */
	public static function get_settings() {

		return apply_filters( 'wc_shipwire_settings',

			array(

				array( 'name' => __( 'General', 'woocommerce-shipwire' ), 'type' => 'title' ),

				array(
					'id'       => 'wc_shipwire_api_username',
					'name'     => __( 'API Username', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Log into your Shipwire Account to find your API username and password.', 'woocommerce-shipwire' ),
					'type'     => 'text'
				),

				array(
					'id'       => 'wc_shipwire_api_password',
					'name'     => __( 'API Password', 'woocommerce-shipwire' ),
					'type'     => 'password',
				),

				array(
					'id'       => 'wc_shipwire_environment',
					'name'     => __( 'Environment', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Environment to use for API requests. Use Sandbox only if you have created a sandbox account.', 'woocommerce-shipwire' ),
					'default'  => 'production',
					'type'     => 'select',
					'options'  => array(
						'production' => __( 'Production', 'woocommerce-shipwire' ),
						'sandbox'    => __( 'Sandbox', 'woocommerce-shipwire' )
					)
				),

				array(
					'id'       => 'wc_shipwire_debug_mode',
					'name'     => __( 'Debug Mode', 'woocommerce-shipwire' ),
					'desc'     => __( 'Enable debug mode. ', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Log API requests/responses and errors to the WooCommerce log. Only enable if you are having issues.', 'woocommerce-shipwire' ),
					'default'  => 'no',
					'type'     => 'checkbox',
				),

				array( 'type' => 'sectionend' ),

				array( 'name' => __( 'Fulfillment', 'woocommerce-shipwire' ), 'type' => 'title' ),

				array(
					'id'      => 'wc_shipwire_auto_export_orders',
					'name'    => __( 'Automatically Export Orders', 'woocommerce-shipwire' ),
					'desc'    => __( 'Enable to automatically submit orders to Shipwire upon successful payment.', 'woocommerce-shipwire' ),
					'default' => 'no',
					'type'    => 'checkbox'
				),

				array(
					'title'    => __( 'Automatically Export Orders Only for These Countries', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Orders shipping to these countries will be automatically exported to Shipwire, all others will need to be exported manually. Leave blank to automatically export all orders.', 'woocommerce-shipwire' ),
					'id'       => 'wc_shipwire_auto_order_export_countries',
					'default'  => '',
					'type'     => 'multi_select_countries'
				),

				array(
					'id'      => 'wc_shipwire_auto_complete_shipped_orders',
					'name'    => __( 'Automatically Complete Shipped Orders', 'woocommerce-shipwire' ),
					'desc'    => __( 'Enable to automatically mark orders as "completed" when Shipwire indicates they have been shipped.', 'woocommerce-shipwire' ),
					'default' => 'yes',
					'type'    => 'checkbox'
				),

				array( 'type' => 'sectionend' ),

				array( 'name' => __( 'Inventory', 'woocommerce-shipwire' ), 'type' => 'title' ),

				array(
					'id'              => 'wc_shipwire_auto_update_inventory',
					'name'            => __( 'Automatically Update Inventory', 'woocommerce-shipwire' ),
					'desc'            => __( 'Enable to automatically update inventory stock.', 'woocommerce-shipwire' ),
					'default'         => 'no',
					'type'            => 'checkbox',
					'show_if_checked' => 'option'
				),

				array(
					'id'       => 'wc_shipwire_auto_update_inventory_interval',
					'name'     => __( 'Update Interval (in minutes)', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'All inventory for Shipwire managed products will update on this interval.', 'woocommerce-shipwire' ),
					'default'  => '15',
					'type'     => 'text',
					'css'      => 'max-width: 50px;',
				),

				array(
					'id'      => 'wc_shipwire_include_pending_inventory',
					'name'    => __( 'Include Pending Inventory in Stock', 'woocommerce-shipwire' ),
					'desc'    => __( 'Enable to include inventory listed as "Pending" in Shipwire as valid inventory in WooCommerce.', 'woocommerce-shipwire' ),
					'default' => 'no',
					'type'    => 'checkbox'
				),

				array(
					'id'       => 'wc_shipwire_inventory_continents',
					'name'     => __( 'Include Inventory from Warehouse Locations', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Select the warehouse continents that inventory will be included from. Leave set as "Worldwide" to include inventory from all warehouse locations.', 'woocommerce-shipwire' ),
					'default'  => array( 'NORTH_AMERICA', 'SOUTH_AMERICA', 'EUROPE', 'ASIA', 'AFRICA', 'AUSTRALIA', 'ANTARCTICA' ),
					'type'     => 'multiselect',
					'class'    => 'wc-enhanced-select',
					'options'  => array(
						'NORTH_AMERICA' => __( 'North America', 'woocommerce-shipwire' ),
						'SOUTH_AMERICA' => __( 'South America', 'woocommerce-shipwire' ),
						'EUROPE'        => __( 'Europe', 'woocommerce-shipwire' ),
						'ASIA'          => __( 'Asia', 'woocommerce-shipwire' ),
						'AFRICA'        => __( 'Africa', 'woocommerce-shipwire' ),
						'AUSTRALIA'     => __( 'Australia', 'woocommerce-shipwire' ),
						'ANTARCTICA'    => __( 'Antarctica', 'woocommerce-shipwire' ),
					),
				),

				array( 'type' => 'sectionend' ),

				array( 'name' => __( 'Tracking', 'woocommerce-shipwire' ), 'type' => 'title' ),

				array(
					'id'              => 'wc_shipwire_auto_update_tracking',
					'name'            => __( 'Automatically Update Tracking', 'woocommerce-shipwire' ),
					'desc'            => __( 'Enable to automatically update tracking information for all orders.', 'woocommerce-shipwire' ),
					'default'         => 'no',
					'type'            => 'checkbox',
					'show_if_checked' => 'option'
				),

				array(
					'id'       => 'wc_shipwire_auto_update_tracking_interval',
					'name'     => __( 'Update Interval (in minutes)', 'woocommerce-shipwire' ),
					'desc_tip' => __( 'Tracking information for all un-shipped orders will update on this interval.', 'woocommerce-shipwire' ),
					'default'  => '15',
					'type'     => 'text',
					'css'      => 'max-width: 50px;'
				),

				array( 'type' => 'sectionend' ),
			)
		);
	}


}
