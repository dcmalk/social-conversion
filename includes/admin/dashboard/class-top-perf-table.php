<?php
/**
 * Dashboard Top Performing Table Class
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
 * RSM_Top_Perf_Table Class
 *
 * Renders the Lists table using RSM_WP_List_Table
 *
 * @since 1.0
 */
class RSM_Top_Perf_Table extends RSM_WP_List_Table {
    /**
     * Top count no. (incrementing 1-5)
     *
     * @var int
     * @since 1.0
     */
    private $top_count = 0;

	/**
	 * Get things started.
	 *
     * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
            'singular' => 'top-perf-stat',     // Singular name of the listed records
            'plural'   => 'top-perf-stats',    // Plural name of the listed records
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
            'perf_no'     => 'No.',
            'app_name'    => 'FB List',
            'message'     => 'Message',
            'type'        => 'Type',
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
        return array();
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
            case 'perf_no':
                return ++$this->top_count;
                break;

            case 'app_name':
            case 'message':
                return esc_attr( $item[ $column_name ] );
                break;

            case 'type':
                switch ( $item['type'] ) {
                    case 'I':
                        $type = 'Regular';
                        $icon = 'podcast';
                        break;
                    case 'L':
                        $type = 'Scheduled';
                        $icon = 'clock-o';
                        break;
                    case 'S':
                        $type = 'Sequence';
                        $icon = 'list-ol';
                        break;
                    case 'W':
                        $type = 'Welcome';
                        $icon = 'smile-o';
                        break;
                }
                return '<span class="label bg-rsm-fountain"><i class="fa fa-1-2x fa-' . $icon . '"></i>&nbsp;&nbsp;' . $type . '</span>';
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
        return db_get_top_performing( 5 );
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
