<?php
/**
 * Lists Table Class
 *
 * @package     RSM
 * @subpackage  Admin/Settings
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
 * RSM_Lists_Table Class
 *
 * Renders the Lists table using RSM_WP_List_Table.
 *
 * @since 1.0
 */
class RSM_Lists_Table extends RSM_WP_List_Table {
	/**
	 * Get things started.
	 *
     * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
            'singular' => 'list',     // Singular name of the listed records
            'plural'   => 'lists',    // Plural name of the listed records
            'ajax'     => false       // Does this table support ajax?
		) );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.0
	 * @return array Array of all the list table columns
	 */
	public function get_columns() {
        $columns = array(
            'app_name'     => 'FB List',
            'show_welcome' => 'Welcome',
            'integrate_ar' => 'A/R',
	        'segments'     => 'Segments',
            'optin_url'    => 'Opt-in Links',
            'action'       => 'Action'
		);
        /*if ( ! rsm_feature_check( 2 ) ) unset( $columns['integrate_ar'] );*/
        return $columns;
	}

    /**
     * Retrieve the table's sortable columns.
     *
     * @since 1.0
     * @return array Array of all the sortable columns
     */
    public function get_sortable_columns() {
        return array(
            'app_name'     => array( 'app_name', true ),     // true means it's already sorted
            'show_welcome' => array( 'show_welcome', false ),
            'integrate_ar' => array( 'integrate_ar', false )
        );
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
            case 'app_name':
	            $app_id = esc_attr( $item[ 'app_id' ] );
                return esc_attr( $item['app_name'] ) . '<br /><span class="rsm-nowrap" style="font-size:85%;color:#999;">App ID: <a href="https://www.facebook.com/analytics/' . $app_id . '" target="_blank" title="Click to view Facebook Analytics" data-toggle="tooltip">' . $app_id . '</a></span>';
                break;

            case 'show_welcome':
                return ( 'T' == $item['show_welcome'] ) ? 'Yes' : 'No';
                break;

            case 'integrate_ar':
                return ( 'T' == $item['integrate_ar'] ) ? 'Yes' : 'No';
                break;

			case 'segments':
				echo '<div class="input-group" style="margin:0 5px 5px 0;">
					  <span class="input-group-addon" title="Select a segment to have your opt-in links (on the right) update as segmented links" data-toggle="tooltip"><i class="fa fa-braille"></i></span>
						<select class="form-control flat rsm-app-segment" style="width:100% !important;">
					      <option value="0">All subscribers</option>';

				$segments = db_get_list_segment( $item['list_id'] );
				if( $segments ) {
					foreach( $segments as $segment ) {
						echo '<option value="' . esc_attr( $segment['segment_id'] ) . '">' . esc_attr( $segment['segment_name'] ) . '</option>';
					}
				}

				echo '	</select>
					  </div>';
				break;

            case 'optin_url':
                echo '<div class="input-group" style="margin:0 5px 5px 0;">
                          <span class="input-group-addon" style="background-color:#eee;" title="Text opt-in link URL" data-toggle="tooltip"><i class="fa fa-link"></i></span>
                          <input class="form-control input-sm rsm-select-all rsm-copy-text rsm-optin-text" type="text" readonly value="'. esc_url( $item['optin_url'] ) . '" data-optin-url="'. esc_url( $item['optin_url'] ) . '">
                          <span class="input-group-btn">
                              <button class="btn bg-rsm-slate btn-flat btn-sm rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                  <i class="fa fa-files-o"></i>
                              </button>
                          <span>
                      </div>
                      <div class="input-group" style="margin:0 5px 0 0;">
                          <span class="input-group-addon" style="background-color:#eee;" title="Graphical opt-in button HTML (opens a popup window)" data-toggle="tooltip"><i class="fa fa-code"></i></span>
                          <input class="form-control input-sm rsm-select-all rsm-copy-text rsm-optin-html" type="text" readonly value="'. esc_html( rsm_get_optin_html( $item['optin_url'] ) ) . '" data-optin-url="'. esc_html( rsm_get_optin_html( $item['optin_url'] ) ) . '">
                          <span class="input-group-btn">
                              <button class="btn bg-rsm-slate btn-flat btn-sm rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                  <i class="fa fa-files-o"></i>
                              </button>
                          <span>
                      </div>';
                break;

            case 'action':
                $edit_row   = '<a class="btn default btn-xs bg-rsm-gray flat" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-tab'     => false,
                        'rsm-message' => false,
                        'action'      => false,
                        'rsm-action'  => 'edit_list',
                        'list-id'     => $item['list_id']
                    ) ), 'rsm_settings_nonce' ) . '"><i class="fa fa-1-2x fa-pencil-square-o"></i>&nbsp;&nbsp;<small>EDIT</small></a>';
                $delete_row = '<a class="btn default btn-xs bg-rsm-gray flat rsm-delete-list" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-tab'     => false,
                        'rsm-message' => false,
                        'action'      => false,
                        'rsm-action'  => 'delete_list',
                        'list-id'     => $item['list_id']
                    ) ), 'rsm_settings_nonce' ) . '"><i class="fa fa-1-2x fa-trash-o"></i>&nbsp;&nbsp;<small>DELETE</small></a>';

                return $edit_row . '<br />' . $delete_row;
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
		echo 'No FB apps found.';
	}

	/**
	 * Retrieve the list data.
	 *
	 * @since 1.0
	 * @return array $list_data Array of all the list data
	 */
	public function list_data() {
        return db_get_list_data( array(
            'orderby'  => isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'app_name',
            'order'    => isset( $_GET['order'] )   ? $_GET['order']   : 'asc',
            )
        );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.0
	 * @return int Number of FB Lists
	 */
	public function prepare_items() {
		// Setup headers, specifying columns, hidden and sortable
        $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get the data
        $data = stripslashes_deep( $this->list_data() );

		// Plug our sorted data into the rest of the class
        $this->items = $data;

        // Return the number of FB Lists
        return $data ? count( $data ) : 0;
	}
}
