<?php
/**
 * Sequence Table Class
 *
 * @package     RSM
 * @subpackage  Admin/Campaigns
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
 * RSM_Sequence_Table Class
 *
 * Renders the Follow-up Sequence table using RSM_WP_List_Table.
 *
 * @since 1.0
 */
class RSM_Sequence_Table extends RSM_WP_List_Table {
    /**
     * Campaign ID of the follow-up sequence
     *
     * @var int
     * @since 1.0
     */
    private $campaign_id = 0;

	/**
	 * Get things started.
	 *
     * @since 1.0
     * @param int $_campaign_id Campaign ID that sequence belongs to
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $_campaign_id ) {
		parent::__construct( array(
            'singular' => 'sequence',     // Singular name of the listed records
            'plural'   => 'sequences',    // Plural name of the listed records
            'ajax'     => false           // Does this table support ajax?
		) );

        $this->campaign_id = $_campaign_id;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0
	 * @return array Array of all the list table columns
	 */
	public function get_columns() {
		return array(
            'msg_no'       => 'No.',
            'message'      => 'Message',
            'redirect_url' => 'Redirect URL',
            'delay'        => 'Delay',
            'action'       => 'Action'
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
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.0
	 * @param array $item Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ){
            case 'message':
                return esc_textarea( $item['message'] );
                break;

            case 'redirect_url':
                return esc_url( $item['redirect_url'] );
                break;

            case 'action':
                $edit_row   = '<a class="btn default btn-xs bg-rsm-gray flat" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-message' => false,
                        'action'      => false,
                        'rsm-action'  => 'edit_sequence',
                        'summary-id'  => $item['summary_id'],
                        'campaign-id' => $this->campaign_id
                    ) ), 'rsm_campaign_nonce' ) . '"><i class="fa fa-1-2x fa-pencil-square-o"></i>&nbsp;&nbsp;<small>EDIT</small></a>';
                $delete_row = '<a class="btn default btn-xs bg-rsm-gray flat rsm-delete-sequence" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-message' => false,
                        'action'      => false,
                        'rsm-action'  => 'delete_sequence',
                        'summary-id'  => $item['summary_id']
                    ) ), 'rsm_campaign_nonce' ) . '"><i class="fa fa-1-2x fa-trash-o"></i>&nbsp;&nbsp;<small>DELETE</small></a>';

                return $edit_row . ' ' . $delete_row;
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
		echo 'No sequence messages found.';
	}

	/**
	 * Retrieve the follow-up sequence data.
	 *
	 * @since 1.0
	 * @return array Array of all the subscriber data
	 */
	public function sequence_data() {
        return db_get_summary_data( $this->campaign_id );
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
        $data = stripslashes_deep( $this->sequence_data() );

		// Plug our sorted data into the rest of the class
        $this->items = $data;
	}
}
