<?php
/**
 * Campaigns Table Class
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
 * RSM_Campaigns_Table Class
 *
 * Renders the Campaigns table using RSM_WP_List_Table.
 *
 * @since 1.0
 */
class RSM_Campaigns_Table extends RSM_WP_List_Table {
    /**
     * Number of results to show per page
     *
     * @var string
     * @since 1.0
     */
    public $per_page = 10;

    /**
     * Number of active campaigns
     *
     * @var string
     * @since 1.0
     */
    public $active_count;

    /**
     * Number of inactive campaigns
     *
     * @var string
     * @since 1.0
     */
    public $inactive_count;

    /**
     * Number of finished campaigns
     *
     * @var string
     * @since 1.0
     */
    public $finished_count;

    /**
     * Total number of campaigns
     *
     * @var string
     * @since 1.0
     */
    public $total_count;

    /**
     * Get things started.
     *
     * @since 1.0
     * @see WP_List_Table::__construct()
     */
    public function __construct() {
        parent::__construct( array(
            'singular' => 'campaign',     // Singular name of the listed records
            'plural'   => 'campaigns',    // Plural name of the listed records
            'ajax'     => false           // Does this table support ajax?
        ) );

        $this->get_campaign_count();
    }

    /**
     * Retrieve the view types.
     *
     * @since 1.0
     * @return array $views All the views available
     */
    public function get_views() {
        $base            = admin_url( 'admin.php?page=social-conversion-campaigns' );

        $current         = isset( $_GET['status'] )      ? $_GET['status']      : null;
        $list_id         = isset( $_GET['list-id'] )     ? $_GET['list-id']     : null;
	    $segment_id      = isset( $_GET['segment-id'] )  ? $_GET['segment-id']  : null;
        $type            = isset( $_GET['type'] )        ? $_GET['type']        : null;
        $search          = !empty( $_GET['s'] )          ? $_GET['s']           : null;

        $total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
        $active_count    = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
        $inactive_count  = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';
        $finished_count  = '&nbsp;<span class="count">(' . $this->finished_count  . ')</span>';

        $views = array(
            'all'        => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => false, 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search, 'type' => $type ) , $base ), 'all' === $current || '' == $current ? ' class="current"' : '', 'All' . $total_count ),
            'active'	 => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'active', 'list-id' => $list_id,  'segment-id' => $segment_id,'s' => $search, 'type' => $type ), $base ), 'active' === $current ? ' class="current"' : '', 'Active' . $active_count ),
            //'inactive'	 => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'inactive', 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search, 'type' => $type ), $base ), 'inactive' === $current ? ' class="current"' : '', 'Inactive' . $inactive_count ),
            'finished'	 => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'finished', 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search, 'type' => $type ), $base ), 'finished' === $current ? ' class="current"' : '', 'Finished' . $finished_count )
        );

        return $views;
    }

    /**
     * Generate the table navigation above or below the table.
     *
     * @since 3.1.0
      * @return void
     */
    public function display_tablenav( $which ) {
        if ( 'top' == $which )
            wp_nonce_field( 'bulk-' . $this->_args['plural'] );
        ?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions( $which ); ?>
            </div>
            <?php
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
    <?php
    }

    /**
     * Extra controls to be displayed between bulk actions and pagination.
     *
     * @since 1.0
     * @param string $which Indicates to add markup before (top) or after (bottom) the list
     * @return void
     */
    public function extra_tablenav( $which ) {
        if ( "top" == $which ){
            ?>
            <div class ="alignleft rsm-tablenav">
                <select name="list-id" id="list-id" class="rsm-max-width">
                    <?php
                    $list_id = isset( $_GET['list-id'] ) ? (int) $_GET['list-id'] : 0 ;
                    $lists = db_get_list_data();
                    echo '<option value="0"' . ( 0 == $list_id  ? ' selected="selected"' : '' ) . '>All FB Lists</option>';
                    if( $lists ) {
                        foreach( $lists as $list ) {
                            echo '<option value="' . esc_attr( $list['list_id'] ) . '"' .  ( $list['list_id'] == $list_id  ? ' selected="selected"' : '' ) . '>' . esc_attr( $list['app_name'] ) . '</option>';
                        }
                    }
                    ?>
                </select>

	            <select name="segment-id" id="segment-id" class="rsm-max-width">
		            <?php
		            $segment_id = isset( $_GET['segment-id'] ) ? (int) $_GET['segment-id'] : 0;
		            $segments   = ( 0 == $list_id ) ? db_get_segment_detail() : db_get_list_segment( $list_id );
		            $segments   = stripslashes_deep( $segments );
		            echo '<option value="0"' . ( 0 == $segment_id  ? ' selected="selected"' : '' ) . '>All Segments</option>';
		            if( $segments ) {
			            foreach( $segments as $segment ) {
				            echo '<option value="' . esc_attr( $segment['segment_id'] ) . '"' .  ( $segment['segment_id'] == $segment_id  ? ' selected="selected"' : '' ) . '>' . esc_attr( $segment['segment_name'] ) . '</option>';
			            }
		            }
		            ?>
	            </select>

                <?php if ( rsm_feature_check( 2 ) ) : ?>
                <select name="type" id="type">
                    <?php
                    $current = isset( $_GET['type'] ) ? $_GET['type'] : 'A' ;
                    echo '<option value="A"' . ( 'A' == $current  ? ' selected="selected"' : '' ) . '>All Types</option>';
                    echo '<option value="I"' . ( 'I' == $current  ? ' selected="selected"' : '' ) . '>Regular</option>';
                    echo '<option value="L"' . ( 'L' == $current  ? ' selected="selected"' : '' ) . '>Scheduled</option>';
                    echo '<option value="S"' . ( 'S' == $current  ? ' selected="selected"' : '' ) . '>Sequence</option>';
                    //echo '<option value="W"' . ( 'W' == $current  ? ' selected="selected"' : '' ) . '>Welcome</option>';
                    ?>
                </select>
                <?php endif; ?>

                <button type="submit" class="button bg-rsm-slate flat no-border rsm-tablenav-btn">
                    <i class="fa fa-filter"></i> Filter
                </button>
            </div>
        <?php
        }
        if ( "bottom" == $which ){
        }
    }

    /**
     * Retrieve the table columns.
     *
     * @since 1.0
     * @return array Array of all the list table columns
     */
    public function get_columns() {
        return array(
            'cb'            => '<input type="checkbox" />',  // Render checkbox instead of text
            'campaign_name' => 'Campaign',
            'app_name'      => 'FB List',
            'sent_count'    => 'Sent',
            'click_count'   => 'Clicks',
            'ctr'           => 'CTR %',
            'created_date'  => 'Created',
            'status'        => 'Status',
            'action'        => 'Action'
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
            'campaign_name' => array( 'campaign_name', false ),     // true means it's already sorted
            'app_name'      => array( 'app_name', false ),
            'sent_count'    => array( 'sent_count', false ),
            'click_count'   => array( 'click_count', false ),
            'ctr'           => array( 'ctr', false ),
            'created_date'  => array( 'created_date', false ),
            'status'        => array( 'status', true )
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
            case 'created_date':
                list( $date, $time ) = explode( ' ', $item[ $column_name ] );
                $time = rsm_format_datetime( $time, RSM_TIME_OUTPUT );
                return '<span class="rsm-nowrap">' . esc_attr( $date ) . '</span><br /><span class="rsm-nowrap">' . esc_attr( $time ) . '</span>';
                break;

            default:
                return esc_attr( $item[ $column_name ] );
        }
    }

    /**
     * Render the checkbox column.
     *
     * @since 1.0
     * @param array $item Contains all the data for the checkbox column
     * @return string Displays a checkbox
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ esc_attr( $this->_args['singular'] ),       // Repurpose the table's singular label ("subscriber")
            /*$2%s*/ esc_attr( $item['campaign_id'] )            // The value of the checkbox should be the record's id
        );
    }

    /**
     * Render the campaign column.
     *
     * @since 1.0
     * @param array $item Contains all the data for the status column
     * @return string Displays the discount status
     */
    public function column_campaign_name( $item ) {
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
            default:
                $icon = 'question-circle';
        }
        ?>
        <ul class="products-list product-list-in-box">
            <li class="item" style="background:none !important; padding: 2px 0 6px 0 !important;">
                <div class="product-img" style="opacity: .65;">
                    <i class="fa fa-<?php echo $icon; ?> fa-3x"></i>
                </div>
                <div class="product-info">
                    <span class="product-title"><?php echo $item['campaign_name']; ?></span>
                    <span class="product-description" style="white-space: normal !important;"><?php echo $item['campaign_desc']; ?></span>
                    <span class="label bg-rsm-fountain" style="display:inline-block;margin-top:8px;"><?php echo $type; ?></span>
                    <?php
                        if ( 0 < (int) $item['segment_id']) {
                            echo '<span class="label bg-rsm-nepal" style="display:inline-block;margin-top:8px;">Segmented</span>';
                        }
                    ?>

                </div>
            </li>
        </ul>
        <?php
    }

    /**
     * Render the action column.
     *
     * @since 1.0
     * @param array $item Contains all the data of the subscriber
     * @return string Data shown in the Action column
     */
    public function column_action( $item ) {
        $view_row = sprintf( '<span class="view_campaign">
                                  <a href="#TB_inline=true&width=800&height=600&inlineId=campaign_id_%1$s" class="btn default btn-xs bg-rsm-gray flat thickbox" data-campaign-id="%1$s" title="Campaign Overview">
                                      <i class="fa fa-1-2x fa-search"></i>&nbsp;&nbsp;<small>VIEW</small>
                                  </a>
                              </span>
                              <div id="campaign_id_%1$s" style="display: none;"><p>Loading details...</p></div>',
                            esc_attr( $item['campaign_id'] ) );
        $edit_row   = '<a class="btn default btn-xs bg-rsm-gray flat" href="' . wp_nonce_url( add_query_arg( array(
                'rsm-message' => false,
                'action'      => false,
                'rsm-action'  => 'edit_campaign',
                'campaign-id' => $item['campaign_id']
            ) ), 'rsm_campaign_nonce' ) . '"><i class="fa fa-1-2x fa-pencil-square-o"></i>&nbsp;&nbsp;<small>EDIT</small></a>';
        $delete_row = '<a class="btn default btn-xs bg-rsm-gray flat rsm-delete-campaign" href="' . wp_nonce_url( add_query_arg( array(
                'rsm-message' => false,
                'action'      => false,
                'rsm-action'  => 'delete_campaign',
                'campaign-id' => $item['campaign_id']
            ) ), 'rsm_campaign_nonce' ) . '"><i class="fa fa-1-2x fa-trash-o"></i>&nbsp;&nbsp;<small>DELETE</small></a>';

        $default_row = $view_row . '<br />' . $delete_row;
        switch ( $item['status'] ) {
            case 'A':   // Active
            case 'I':   // Inactive
                $row = $edit_row . '<br />' . $default_row;
                break;
            case 'F':   // Finished
                $row = $default_row;
                break;
        }

        return $row;
    }

    /**
     * Render the status column.
     *
     * @since 1.0
     * @param array $item Contains all the data for the status column
     * @return string Displays the discount status
     */
    public function column_status( $item ) {
        switch ( $item['status'] ) {
            case 'A':
                //$status = ( 'L' == $item['type'] ) ? 'Scheduled' : 'Active';
                $status = 'Active';
                $label = 'bg-rsm-light-green';
                break;
            case 'I':
                $status = 'Inactive';
                $label = 'bg-rsm-gray';
                break;
            case 'F':
                $status = 'Finished';
                $label = 'bg-rsm-slate';
                break;
            default:
                $status = 'Unknown';
                $label = 'label-danger';
        }

        return '<span class="label ' . $label . '">'. $status . '</span>';
    }

    /**
     * Message to be displayed when there are no items.
     *
     * @since 1.0
     * @return void
     */
    public function no_items() {
        echo 'No campaigns found.';
    }

    /**
     * Retrieve the bulk actions.
     *
     * @since 1.0
     * @return array Array of the bulk actions
     */
    public function get_bulk_actions() {
        return array(
            'campaigns_bulk_delete' => 'Delete'
        );
    }

    /**
     * Process the bulk actions.
     *
     * @since 1.0
     * @return void
     */
    public function process_bulk_action() {
        $ids = isset( $_GET['campaign'] ) ? $_GET['campaign'] : false;
        if ( ! is_array( $ids ) )
            $ids = array( $ids );

        foreach ( $ids as $id ) {
            if ( 'campaigns_bulk_delete' === $this->current_action() ) {
                db_delete_campaign( $id );
            }
        }
    }

    /**
     * Retrieve the campaign count.
     *
     * @since 1.0
     * @return void
     */
    public function get_campaign_count() {
        $campaign_count = $this->get_campaign_data( true );

        $this->active_count    = $campaign_count['active'];
        $this->inactive_count  = $campaign_count['inactive'];
        $this->finished_count  = $campaign_count['finished'];
        $this->total_count     = $campaign_count['active'] + $campaign_count['inactive'] + $campaign_count['finished'];
    }

    /**
     * Retrieve the campaign data.
     *
     * @since 1.0
     * @return array Array of all the campaign data
     */
    public function campaign_data() {
        return $this->get_campaign_data( false );
    }

    /**
     * Retrieve the campaign data from the database.
     *
     * @since 1.0
     * @param $count (optional) Indicates whether to return counts
     * @return array Array of all the notification data
     */
    private function get_campaign_data( $count = false ) {
        return db_get_campaign_data( array(
                'per-page'   => $this->per_page,
                'paged'      => isset( $_GET['paged'] )      ? $_GET['paged']      : 1,
                'list-id'    => isset( $_GET['list-id'] )    ? $_GET['list-id']    : 0,
                'segment-id' => isset( $_GET['segment-id'] ) ? $_GET['segment-id'] : 0,
                'type'       => isset( $_GET['type'] )       ? $_GET['type']       : 'A',
                'orderby'    => isset( $_GET['orderby'] )    ? $_GET['orderby']    : 'status',
                'order'      => isset( $_GET['order'] )      ? $_GET['order']      : 'asc',
                'status'     => isset( $_GET['status'] )     ? $_GET['status']     : 'all',
                'search'     => !empty( $_GET['s'] )         ? $_GET['s']          : null
            ),
            $count
        );
    }

    /**
     * Setup the final data for the table.
     *
     * @since 1.0
     * @return void
     */
    public function prepare_items() {
        // Records per page to show
        $per_page = $this->per_page;

        // Thickbox is required for popups
        add_thickbox();

        // Setup headers, specifying columns, hidden and sortable
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        // Process bulk actions
        $this->process_bulk_action();

        // Get the data
        $data = stripslashes_deep( $this->campaign_data() );

        // Update counts for pagination
        $status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
        switch( $status ) {
            case 'active':
                $total_items = $this->active_count;
                break;
            case 'inactive':
                $total_items = $this->inactive_count;
                break;
            case 'finished':
                $total_items = $this->finished_count;
                break;
            default:
                $total_items = $this->total_count;
                break;
        }

        // Plug our sorted data into the rest of the class
        $this->items = $data;

        // Register our pagination options and calculations
        $this->set_pagination_args( array(
                'total_items' => $total_items,                      // Calculate the total number of items
                'per_page'    => $per_page,                         // Determine how many items to show on a page
                'total_pages' => ceil( $total_items / $per_page )   // Calculate the total number of pages
            )
        );
    }
}
