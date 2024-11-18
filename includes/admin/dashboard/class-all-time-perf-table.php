<?php
/**
 * Dashboard Performance Table Class
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_WP_List_Table if not loaded
if ( ! class_exists( 'RSM_WP_List_Table' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/admin/legacy/class-rsm-list-table.php';
}

/**
 * RSM_All_Time_Perf_Table Class
 *
 * Renders the Lists table using RSM_WP_List_Table
 *
 * @since 1.0
 */
class RSM_All_Time_Perf_Table extends RSM_WP_List_Table {
	/**
	 * Get things started.
	 *
     * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
			parent::__construct( array(
							'singular' => 'all-time-stat',     // Singular name of the listed records
							'plural'   => 'all-time-stats',    // Plural name of the listed records
							'ajax'     => false             // Does this table support ajax?
			) );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0
	 * @return array Array of all the list table columns
	 */
	public function get_columns() {
			return array(
							'app_name'    => 'FB List',
							'sub_count'   => 'Subscribers',
							'sent_count'  => 'Sent',
							'click_count' => 'Clicks',
							'ctr'         => 'CTR %'
			);
	}

    /**
     * Retrieve the table's sortable columns.
     *
     * @since 1.0
     * @return array Array of all the sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'app_name'    => array( 'app_name', true ),     // true means it's already sorted
            'sub_count'   => array( 'sub_count', false ),
            'sent_count'  => array( 'sent_count', false ),
            'click_count' => array( 'click_count', false ),
            'ctr'         => array( 'ctr', false )
        );
    }

	/**
	 * This function renders most of the columns in the performance table.
	 *
	 * @since 1.0
	 * @param array $item Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
			switch( $column_name ){
							case 'app_name':
									return esc_attr( $item[ $column_name ] );
									break;

							case 'ctr':
									//return '<span class="badge bg-light-blue">' . esc_attr( $item[ $column_name ] ) . '%</span>';
									return esc_attr( $item[ $column_name ] );
									break;

				default:
					return esc_attr( $item[ $column_name ] );
			}
	}

	/**
	 * Message to be displayed when there are no items.
	 *
     * @since 1.0
	 * @return void
	 */
	public function no_items() {
			echo 'No performance data available.';
	}

	/**
	 * Retrieve the performance data.
	 *
	 * @since 1.0
	 * @return array $perf_data Array of all the performance data
	 */
	public function perf_data() {
        return db_get_list_performance( array(
            'orderby'  => isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'app_name',
            'order'    => isset( $_GET['order'] )   ? $_GET['order']   : 'asc',
            )
        );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function prepare_items() {
		// Setup headers, specifying columns, hidden and sortable
    $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get the data
    $data = stripslashes_deep( $this->perf_data() );

		// Plug our sorted data into the rest of the class
    $this->items = $data;
	}
}
