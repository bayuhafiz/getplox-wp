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
 * @package     WC-Shipwire/Admin/List-Table
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2016, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Shipwire Update Log List Table Class
 *
 * Extends WP_List_Table to help display the Update Log
 *
 * @since 1.0
 * @extends \WP_List_Table
 */
class WC_Shipwire_List_Table extends WP_List_Table {


	/** @var array log data */
	public $data;


	/**
	 * Constructor - setup list table
	 *
	 * @access public
	 * @since 1.0
	 * @param array $data
	 * @return \WC_Shipwire_List_Table
	 */
	public function __construct( $data ) {

		$this->data = $data;

		parent::__construct( array(
			'singular' => __( 'Log', 'woocommerce-shipwire' ),
			'plural'   => __( 'Logs', 'woocommerce-shipwire' ),
			'ajax'     => false
		) );
	}


	/**
	 * Get column content
	 *
	 * @since 1.0
	 * @param string $item
	 * @param string $column_name
	 * @return array
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'datetime':
			case 'action':
				return $item[ $column_name ];
		}
	}


	/**
	 * Set column titles
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'datetime' => __( 'Date', 'woocommerce-shipwire' ),
			'action'   => __( 'Action Performed', 'woocommerce-shipwire' )
		);

		return $columns;
	}


	/**
	 * Prepare log entries for display
	 *
	 * @since 1.0
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items = $this->data;
	}


	/**
	 * The HTML to display when there are no update log entries
	 *
	 * @see WP_List_Table::no_items()
	 * @since 1.0
	 */
	public function no_items() {
		?>
		<p><?php _e( 'Updates will appear here once inventory or tracking is updated. Use the buttons above to update inventory or tracking immediately.', 'woocommerce-shipwire' ); ?></p>
		<p><?php
			/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
			printf( __( '%1$sLearn more about updating tracking or inventory%2$s', 'woocommerce-shipwire' ), '<a href="http://docs.woothemes.com/document/shipwire/" target="_blank">', ' &raquo;</a>' );
		?></p>
		<p><?php
			/* translators: Placeholders: %1$s - <a> tag, %2$s - </a> tag */
			printf( __( '%1$sSetup a product to be Shipwire-enabled%2$s', 'woocommerce-shipwire' ), '<a href="' . admin_url( 'post-new.php?post_type=product' ) . '">', ' &raquo;</a>' );
		?></p>
		<?php
	}


} // end \WC_Shipwire_List_Table class
