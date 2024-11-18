<?php
/**
 * Subscribers Table Class
 *
 * @package     RSM
 * @subpackage  Admin/Subscribers
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
 * RSM_Subscribers_Table Class
 *
 * Renders the Subscribers table using RSM_WP_List_Table.
 *
 * @since 1.0
 */
class RSM_Subscribers_Table extends RSM_WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 10;

	/**
	 * Number of active subscribers
	 *
	 * @var string
	 * @since 1.0
	 */
	public $active_count;

	/**
	 * Number of inactive subscribers
	 *
	 * @var string
	 * @since 1.0
	 */
	public $inactive_count;

    /**
     * Total number of subscribers
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
            'singular' => 'subscriber',     // Singular name of the listed records
            'plural'   => 'subscribers',    // Plural name of the listed records
            'ajax'     => false             // Does this table support ajax?
		) );

        $this->get_subscriber_count();
	}

	/**
	 * Retrieve the view types.
	 *
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url( 'admin.php?page=social-conversion-subscribers' );

		$current        = isset( $_GET['status'] )     ? $_GET['status']     : null;
        $list_id        = isset( $_GET['list-id'] )    ? $_GET['list-id']    : null;
		$segment_id     = isset( $_GET['segment-id'] ) ? $_GET['segment-id'] : null;
        $search         = !empty( $_GET['s'] )         ? $_GET['s']          : null;

		$total_count    = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
            'all'       => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => false, 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search ) , $base ), 'all' === $current || '' == $current ? ' class="current"' : '', 'All' . $total_count ),//
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'active', 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search ), $base ), 'active' === $current ? ' class="current"' : '', 'Active' . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'rsm-message' => false, 'status' => 'inactive', 'list-id' => $list_id, 'segment-id' => $segment_id, 's' => $search ), $base ), 'inactive' === $current ? ' class="current"' : '', 'Inactive' . $inactive_count ),
		);

		return $views;
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
            'cb'           => '<input type="checkbox" />',  // Render checkbox instead of text
            'full_name'    => 'Name',
            'uid'          => 'User ID',
            'app_name'     => 'FB&nbsp;List',
            'email'        => 'Email',
            'created_date' => 'Opt-in&nbsp;Date',
            'status'       => 'Status',
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
        return array(
            'full_name'    => array( 'full_name', true ),     // true means it's already sorted
            'uid'          => array( 'uid', false ),
            'app_name'     => array( 'app_name', false ),
            'email'        => array( 'email', false ),
            'created_date' => array( 'created_date', false ),
            'status'       => array( 'status', false )
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
            case 'full_name':
	            $img_url     = esc_url( 'https://graph.facebook.com/' . $item['uid'] . '/picture?type=square' );
	            $img         = '<img src="' . $img_url . '" title="Click to view Facebook profile" />';
	            $profile_url = empty( $item['link'] ) ? '#' : esc_url( $item['link'] );
	            $profile     = '<a href="' . $profile_url . '" target="_blank">' . $img . '</a>';
	            ?>
	            <ul class="products-list product-list-in-box">
		            <li class="item" style="background:none !important; padding: 2px 0 6px 0 !important;">
			            <div class="product-img"><?php echo $profile; ?></div>
			            <div class="product-info">
				            <span class="product-title"><?php echo esc_attr( $item['full_name'] ); ?></span>
			            </div>
		            </li>
	            </ul>
	            <?php
                break;

            case 'email':
                return sprintf( '%1$s', empty( $item['email'] ) ? '[ Did not authorize ]' : esc_attr( $item['email'] ) );
                break;

            case 'created_date':
                list( $date, $time ) = explode( ' ', $item[ $column_name ] );
                $time = rsm_format_datetime( $time, RSM_TIME_OUTPUT );
                return '<span class="rsm-nowrap">' . esc_attr( $date ) . '</span> <span class="rsm-nowrap">' . esc_attr( $time ) . '</span>';
                break;

            case 'status':
                return ( 'A' == $item['status'] ) ? '<span class="label bg-rsm-light-green">Active</span>' : '<span class="label bg-rsm-gray">Inactive</span>';
                break;

            case 'action':
	            $view_row = sprintf( '<span class="view_subscriber">
                                  <a href="#TB_inline=true&width=800&height=600&inlineId=subscriber_id_%1$s" class="btn default btn-xs bg-rsm-gray flat thickbox" data-subscriber-id="%1$s" title="Subscriber Profile">
                                      <i class="fa fa-1-2x fa-search"></i>&nbsp;&nbsp;<small>VIEW</small>
                                  </a>
                              </span>
                              <div id="subscriber_id_%1$s" style="display: none;"><p>Loading details...</p></div>',
		            esc_attr( $item['sub_id'] ) );

	            $activate_row = '<a class="btn default btn-xs bg-rsm-gray flat" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-message' => false,
                        'rsm-action'  => 'activate_subscriber',
                        'sub-id'      => $item['sub_id']
                    ) ), 'rsm_subscriber_nonce' ) . '"><i class="fa fa-1-2x fa-check-circle-o"></i>&nbsp;&nbsp;<small>ACTIVATE</small></a>';
                $delete_row   = '<a class="btn default btn-xs bg-rsm-gray flat rsm-delete-subscriber" href="' . wp_nonce_url( add_query_arg( array(
                        'rsm-message' => false,
                        'rsm-action'  => 'delete_subscriber',
                        'sub-id'      => $item['sub_id']
                    ) ), 'rsm_subscriber_nonce' ) . '"><i class="fa fa-1-2x fa-trash-o"></i>&nbsp;&nbsp;<small>DELETE</small></a>';

                $row = 'A' == $item['status']  ? $delete_row : $activate_row . '<br />' . $delete_row;
	            return $view_row . '<br />' . $row;
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
            /*$2%s*/ esc_attr( $item['sub_id'] )                 // The value of the checkbox should be the record's id
        );
    }

	/**
	 * Message to be displayed when there are no items.
	 *
     * @since 1.0
	 * @return void
	 */
	public function no_items() {
		echo 'No subscribers found.';
	}

	/**
	 * Retrieve the bulk actions.
	 *
	 * @since 1.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
            'subscribers_bulk_activate' => 'Activate',
            'subscribers_bulk_delete'   => 'Delete'
		);
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['subscriber'] ) ? $_GET['subscriber'] : false;
		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
            if ( 'subscribers_bulk_activate' === $this->current_action() ) {
                db_update_subscriber_status( $id, true );
            }

			if ( 'subscribers_bulk_delete' === $this->current_action() ) {
				db_delete_subscriber( $id );
			}
		}
	}

	/**
	 * Retrieve the subscriber count.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function get_subscriber_count() {
        $subscriber_count = $this->get_subscriber_data( true );

        $this->active_count   = $subscriber_count['active'];
        $this->inactive_count = $subscriber_count['inactive'];
        $this->total_count    = $subscriber_count['active'] + $subscriber_count['inactive'];
	}

	/**
	 * Retrieve the subscriber data.
	 *
	 * @since 1.0
	 * @return array $subscriber_data Array of all the subscriber data
	 */
	public function subscriber_data() {
        return $this->get_subscriber_data( false );
	}

    /**
     * Retrieve the subscriber data from the database.
     *
     * @since 1.0
     * @param $count (optional) Indicates whether to return counts
     * @return array Array of all the subscriber data
     */
    private function get_subscriber_data( $count = false ) {
        return db_get_subscriber_data( array(
                'per-page'  => $this->per_page,
                'list-id'   => isset( $_GET['list-id'] )    ? $_GET['list-id']    : 0,
                'segment-id'=> isset( $_GET['segment-id'] ) ? $_GET['segment-id'] : 0,
                'paged'     => isset( $_GET['paged'] )      ? $_GET['paged']      : 1,
                'orderby'   => isset( $_GET['orderby'] )    ? $_GET['orderby']    : 'full_name',
                'order'     => isset( $_GET['order'] )      ? $_GET['order']      : 'asc',
                'status'    => isset( $_GET['status'] )     ? $_GET['status']     : 'all',
                'search'    => !empty( $_GET['s'] )         ? $_GET['s']          : null
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
        $data = stripslashes_deep( $this->subscriber_data() );

		// Update counts for pagination
        $status = isset( $_GET['status'] ) ? $_GET['status'] : 'all';
		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
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
