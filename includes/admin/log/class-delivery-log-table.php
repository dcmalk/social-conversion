<?php
/**
 * Delivery Log Table Class
 *
 * @package     RSM
 * @subpackage  Admin/Log
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
 * RSM_Delivery_Log_Table Class
 *
 * Renders the Log table using RSM_WP_List_Table.
 *
 * @since 1.0
 */
class RSM_Delivery_Log_Table extends RSM_WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 10;

	/**
	 * Number of sent notifications
	 *
	 * @var string
	 * @since 1.0
	 */
	public $sent_count;

	/**
	 * Number of notifications not sent
	 *
	 * @var string
	 * @since 1.0
	 */
	public $not_sent_count;

    /**
     * Number of notification errors
     *
     * @var string
     * @since 1.0
     */
    public $sent_error_count;

    /**
     * Total number of notifications
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
	public function __construct( ) {
		parent::__construct( array(
            'singular' => 'notification',     // Singular name of the listed records
            'plural'   => 'notifications',    // Plural name of the listed records
            'ajax'     => false               // Does this table support ajax?
		) );

        $this->get_notification_count();
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base             = admin_url( 'admin.php?page=social-conversion-log' );

        $current          = isset( $_GET['status'] )      ? $_GET['status']      : null;
        $list_id          = isset( $_GET['list-id'] )     ? $_GET['list-id']     : null;
        $campaign_id      = isset( $_GET['campaign-id'] ) ? $_GET['campaign-id'] : null;
		$segment_id       = isset( $_GET['segment-id'] )  ? $_GET['segment-id']  : null;
        $search           = isset( $_GET['s'] )           ? $_GET['s']           : null;
        $type             = isset( $_GET['type'] )        ? $_GET['type']        : null;
        $start_date       = isset( $_GET['start-date'] )  ? $_GET['start-date']  : null;
        $end_date         = isset( $_GET['end-date'] )    ? $_GET['end-date']    : null;

		$total_count      = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$sent_count       = '&nbsp;<span class="count">(' . $this->sent_count . ')</span>';
		$not_sent_count   = '&nbsp;<span class="count">(' . $this->not_sent_count  . ')</span>';
        $sent_error_count = '&nbsp;<span class="count">(' . $this->sent_error_count . ')</span>';

		$views = array(
            'all'        => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => false, 'list-id' => $list_id, 'segment-id' => $segment_id, 'campaign-id' => $campaign_id, 'type' => $type, 's' => $search, 'start-date' => $start_date, 'end-date' => $end_date ), $base ), 'all' === $current || '' == $current ? ' class="current"' : '', 'All' . $total_count ),
            'sent'       => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'sent', 'list-id' => $list_id, 'segment-id' => $segment_id, 'campaign-id' => $campaign_id, 'type' => $type, 's' => $search, 'start-date' => $start_date, 'end-date' => $end_date ), $base ), 'sent' === $current ? ' class="current"' : '', 'Sent' . $sent_count ),
            'not_sent'   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'not_sent', 'list-id' => $list_id, 'segment-id' => $segment_id, 'campaign-id' => $campaign_id, 'type' => $type, 's' => $search, 'start-date' => $start_date, 'end-date' => $end_date ), $base ), 'not_sent' === $current ? ' class="current"' : '', 'Not Sent' . $not_sent_count ),
            'sent_error' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'sent_error', 'list-id' => $list_id, 'segment-id' => $segment_id, 'campaign-id' => $campaign_id, 'type' => $type, 's' => $search, 'start-date' => $start_date, 'end-date' => $end_date ), $base ), 'sent_error' === $current ? ' class="current"' : '', 'Errors' . $sent_error_count )
		);

		return $views;
	}

    /**
     * Renders the start and end date fields.
     *
     * @since 1.0
     * @return void
     */
    public function date_box() {
        $start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
        $end_date   = isset( $_GET['end-date'] )   ? sanitize_text_field( $_GET['end-date'] )   : null;
        ?>
        <div class ="alignright rsm-date-filters">
            <input type="text" id="start-date" name="start-date" class="form-control input-sm rsm-datepicker" style="width:100px;" value="<?php echo $start_date; ?>" placeholder="Start Date"/>
            <input type="text" id="end-date" name="end-date" class="form-control input-sm rsm-datepicker" style="width:100px;" value="<?php echo $end_date; ?>" placeholder="End Date"/>
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
                        $list_id = isset( $_GET['list-id'] ) ? (int) $_GET['list-id'] : 0;
                        $lists   = stripslashes_deep( db_get_list_data() );
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
                <select name="campaign-id" id="campaign-id" class="rsm-max-width">
                    <?php
                        $campaign_id = isset( $_GET['campaign-id'] ) ? (int) $_GET['campaign-id'] : 0;
                        $campaigns = db_get_campaign_data( array(
                            'list-id' => $list_id,
                            'type'    => isset( $_GET['type'] )    ? $_GET['type']    : 'A',
                            'orderby' => 'campaign_name',
                            'order'   => 'asc',
                            'status'  => 'all'
                        ), false, false );
                        $campaigns = stripslashes_deep( $campaigns );
                        echo '<option value="0"' . ( 0 == $campaign_id  ? ' selected="selected"' : '' ) . '>All Campaigns</option>';
                        if( $campaigns ) {
                            foreach( $campaigns as $campaign ) {
                                echo '<option value="' . esc_attr( $campaign['campaign_id'] ) . '"' .  ( $campaign['campaign_id'] == $campaign_id  ? ' selected="selected"' : '' ) . '>' . esc_attr( $campaign['campaign_name'] ) . '</option>';
                            }
                        }
                    ?>
                </select>

                <?php if ( rsm_feature_check( 2 ) ) : ?>
                <select name="type" id="type">
                    <?php
                        $type = isset( $_GET['type'] ) ? $_GET['type'] : 'A' ;
                        echo '<option value="A"' . ( 'A' == $type ? ' selected="selected"' : '' ) . '>All Types</option>';
                        echo '<option value="I"' . ( 'I' == $type ? ' selected="selected"' : '' ) . '>Regular</option>';
                        echo '<option value="L"' . ( 'L' == $type ? ' selected="selected"' : '' ) . '>Scheduled</option>';
                        echo '<option value="S"' . ( 'S' == $type ? ' selected="selected"' : '' ) . '>Sequence</option>';
                        echo '<option value="W"' . ( 'W' == $type ? ' selected="selected"' : '' ) . '>Welcome</option>';
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
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
            'cb'            => '<input type="checkbox" />',  // Render checkbox instead of text
            'app_name'      => 'FB List',
            'campaign_name' => 'Campaign Name',
            'full_name'     => 'Subscriber',
            'message'       => 'Message',
            'type'          => 'Type',
            'status'        => 'Status',
            'send_date'     => 'Delivery Date',
            'action'        => 'Action'
		);

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
            'app_name'      => array( 'app_name', false ),
            'campaign_name' => array( 'campaign_name', false ),
            'full_name'     => array( 'full_name', false ),
            'message'       => array( 'message', false ),
            'type'          => array( 'type', false ),
            'status'        => array( 'status', false ),
            'send_date'     => array( 'send_date', true )  // true means it's already sorted
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

            case 'status':
                switch ( $item['status'] ) {
                    case 'S':
                        return '<span class="label bg-rsm-light-green">Sent</span>';    // vs bg-rsm-slate
                        break;
                    case 'N':
                        return '<span class="label bg-rsm-gray">Not Sent</span>';
                        break;
                    case 'E':
                        return '<span class="label bg-rsm-red">Error</span>';
                        break;
                }
                break;

            case 'send_date':
                switch ( $item['status'] ) {
                    case 'N':
                        //return '<span style="color:#7a868f;">' . $item['send_date'] . '</span>';
                        list( $date, $time ) = explode( ' ', $item[ 'send_date' ] );
                        break;

                    default:
                        //return $item['sent_date'];
                        list( $date, $time ) = explode( ' ', $item[ 'sent_date' ] );
                        break;
                }

                $time = rsm_format_datetime( $time, RSM_TIME_OUTPUT );
                return '<span class="rsm-nowrap">' . esc_attr( $date ) . '</span><br /><span class="rsm-nowrap">' . esc_attr( $time ) . '</span>';
                break;

            case 'action':
                $delete_row = '<a class="btn default btn-xs bg-rsm-gray flat rsm-delete-notification" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-message'     => false,
                        'rsm-action'      => 'delete_notification',
                        'notification-id' => $item['notification_id']
                    ) ), 'rsm_log_nonce' ) . '"><i class="fa fa-1-2x fa-trash-o"></i>&nbsp;&nbsp;<small>DELETE</small></a>';

                return ( 'S' != $item['status'] ) ? $delete_row : '';
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
            /*$1%s*/ esc_attr( $this->_args['singular'] ),      // Repurpose the table's singular label
            /*$2%s*/ esc_attr( $item['notification_id'] )       // The value of the checkbox should be the record's id
        );
    }

	/**
	 * Message to be displayed when there are no items.
	 *
     * @since 1.0
	 * @return void
	 */
	public function no_items() {
		echo 'No notifications found.';
	}

	/**
	 * Retrieve the bulk actions.
	 *
	 * @since 1.0
	 * @return array Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
            'subscribers_notifications_delete' => 'Delete'
		);
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['notification'] ) ? $_GET['notification'] : false;
		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'subscribers_notifications_delete' === $this->current_action() ) {
				//return ( 'S' != $item['status'] ) ? $delete_row : '';
				if ( 'S' != db_get_notification_status ( $id ) )
					db_delete_notification( $id );
			}
		}
	}

	/**
	 * Retrieve the notification count.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_notification_count() {
        $notification_count = $this->get_notification_data( true );

        $this->sent_count       = $notification_count['sent'];
        $this->not_sent_count   = $notification_count['not_sent'];
        $this->sent_error_count = $notification_count['sent_error'];
        $this->total_count      = $notification_count['sent'] + $notification_count['not_sent'] + $notification_count['sent_error'];
	}

	/**
	 * Retrieve the notification data.
	 *
	 * @since 1.0
	 * @return array Array of all the notification data
	 */
	public function notification_data() {
        return $this->get_notification_data( false );
	}

    /**
     * Retrieve the notification data from the database.
     *
     * @since 1.0
     * @param $count (optional) Indicates whether to return counts
     * @return array Array of all the notification data
     */
    private function get_notification_data( $count = false ) {
        return db_get_notification_data( array(
                'per-page'    => $this->per_page,
                'paged'       => isset( $_GET['paged'] )       ? $_GET['paged']       : 1,
                'list-id'     => isset( $_GET['list-id'] )     ? $_GET['list-id']     : 0,
                'segment-id'  => isset( $_GET['segment-id'] )  ? $_GET['segment-id']  : 0,
                'campaign-id' => isset( $_GET['campaign-id'] ) ? $_GET['campaign-id'] : 0,
                'type'        => isset( $_GET['type'] )        ? $_GET['type']        : 'A',
                'orderby'     => isset( $_GET['orderby'] )     ? $_GET['orderby']     : 'send_date',
                'order'       => isset( $_GET['order'] )       ? $_GET['order']       : 'desc',
                'status'      => isset( $_GET['status'] )      ? $_GET['status']      : 'all',
                'search'      => !empty( $_GET['s'] )          ? $_GET['s']           : null,
                'start-date'  => !empty( $_GET['start-date'] ) ? get_gmt_from_date( $_GET['start-date'] . '00:00:00' ) : null,
                'end-date'    => !empty( $_GET['end-date'] )   ? get_gmt_from_date( $_GET['end-date'] . '23:59:59' )   : null
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

		// Setup headers, specifying columns, hidden and sortable
        $columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Process bulk actions
        $this->process_bulk_action();

		// Get the data
        $data = stripslashes_deep( $this->notification_data() );

		// Update counts for pagination
        $status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
		switch( $status ) {
			case 'sent':
				$total_items = $this->sent_count;
				break;
			case 'not_sent':
				$total_items = $this->not_sent_count;
				break;
            case 'sent_error':
                $total_items = $this->sent_error_count;
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
