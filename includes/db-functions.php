<?php
/**
 * Database Functions
 *
 * @package     RSM
 * @subpackage  Database
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*----------------------------------------------------------------------------*
 * Count functions
 *----------------------------------------------------------------------------*/

/**
 * Count number of notifications sent by date.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array (optional) $args Date parameters
 * @return mixed String containing number of notifications sent by date, otherwise false
 */
function db_get_sent_by_date( $args = array() ) {
    global $wpdb;

    // Build query
    $sql = "SELECT COUNT( notification_id ) AS count
              FROM " . RSM_NOTIFICATION_TABLE . "
             WHERE status = 'S'";

    // Filter by conditional arguments
    $sql .= isset( $args['day'] )   ? ( ' AND DAY( sent_date )   = ' . ( int ) $args['day'] )   : '';
    $sql .= isset( $args['month'] ) ? ( ' AND MONTH( sent_date ) = ' . ( int ) $args['month'] ) : '';
    $sql .= isset( $args['year'] )  ? ( ' AND YEAR( sent_date )  = ' . ( int ) $args['year'] )  : '';
    $sql .= isset( $args['hour'] )  ? ( ' AND HOUR( sent_date )  = ' . ( int ) $args['hour'] )  : '';

    // Get the data
    $results = $wpdb->get_var( $sql );

    return ( $results !== null ) ? $results : false;
}

/**
 * Count number of clicks by date.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array (optional) $args Date parameters
 * @return mixed String containing number of click by date, otherwise false
 */
function db_get_clicks_by_date( $args = array() ) {
    global $wpdb;

    // Build query
    $sql = "SELECT COUNT( DISTINCT notification_id ) AS count
              FROM " . RSM_CLICK_TABLE . "
             WHERE 1 = 1";

    // Filter by conditional arguments
    $sql .= isset( $args['day'] )   ? ( ' AND DAY( click_date )   = ' . ( int ) $args['day'] )   : '';
    $sql .= isset( $args['month'] ) ? ( ' AND MONTH( click_date ) = ' . ( int ) $args['month'] ) : '';
    $sql .= isset( $args['year'] )  ? ( ' AND YEAR( click_date )  = ' . ( int ) $args['year'] )  : '';
    $sql .= isset( $args['hour'] )  ? ( ' AND HOUR( click_date )  = ' . ( int ) $args['hour'] )  : '';

    // Get the data
    $results = $wpdb->get_var( $sql );

    return ( $results !== null ) ? $results : false;
}

/**
 * Count number of optins by date.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array (optional) $args Date parameters
 * @return mixed String containing number of optins by date, otherwise false
 */
function db_get_optins_by_date( $args = array() ) {
    global $wpdb;

    // Build query
    $sql = "SELECT COUNT( subscriber_id ) AS count
              FROM " . RSM_SUBSCRIBER_TABLE . "
             WHERE 1 = 1";

    // Filter by conditional arguments
    $sql .= isset( $args['day'] )   ? ( ' AND DAY( created_date )   = ' . ( int ) $args['day'] )   : '';
    $sql .= isset( $args['month'] ) ? ( ' AND MONTH( created_date ) = ' . ( int ) $args['month'] ) : '';
    $sql .= isset( $args['year'] )  ? ( ' AND YEAR( created_date )  = ' . ( int ) $args['year'] )  : '';
    $sql .= isset( $args['hour'] )  ? ( ' AND HOUR( created_date )  = ' . ( int ) $args['hour'] )  : '';

    // Get the data
    $results = $wpdb->get_var( $sql );

    return ( $results !== null ) ? $results : false;
}

/**
 * Count number of clicks by subscriber.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $subscriber_id Subscriber ID
 * @return mixed String containing number of clicks by a subscriber, otherwise false
 */
function db_get_clicks_by_subscriber( $subscriber_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $subscriber_id ) )
		return false;

	// Build query
	$sql = "SELECT COUNT( DISTINCT notification_id ) AS clicks
              FROM " . RSM_CLICK_TABLE . "
             WHERE subscriber_id = " . $subscriber_id;

	// Get the data
	$results = $wpdb->get_var( $sql );

	return ( $results !== null ) ? $results : false;
}


/*----------------------------------------------------------------------------*
 * Get data functions
 *----------------------------------------------------------------------------*/

/**
 * Gets all list data.
 *
 * The arguments:
 * - orderby: The column to order results by
 * - order: Order results ascending or descending
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array (optional) $args The query parameters
 * @return mixed Array containing all lists and values, otherwise false
 */
function db_get_list_data( $args = array() ) {
    global $wpdb;

    // Build query
    $sql = "SELECT list_id, app_name, app_id, app_secret, okay_url, cancel_url, show_welcome, integrate_ar, optin_url FROM " . RSM_LIST_TABLE;

    // Check for query parameters
    if ( ! empty( $args ) ){

        // Create whitelists and sanitize
        $allowed_orderby = array( 'app_name', 'app_id', 'app_secret', 'show_welcome', 'integrate_ar' );
        $allowed_order   = array( 'asc', 'desc' );

        // Validate params and if fail, use defaults
        $orderby = isset( $args['orderby'] ) && in_array( strtolower ( $args['orderby'] ), $allowed_orderby ) ? $args['orderby'] : 'app_name';
        $order   = isset( $args['order'] )   && in_array( strtolower ( $args['order'] ), $allowed_order )     ? $args['order']   : 'asc';

        $sql .= " ORDER BY ".  $orderby . ' ' . $order;
    } else {

        // Default sort order
        $sql .= " ORDER BY app_name asc";
    }

    // Execute query
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a list of all locales based on current subscribers.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @return mixed Array containing list of locales, otherwise false
 */
function db_get_locale() {
    global $wpdb;

    // Build query
    $sql = "SELECT DISTINCT locale FROM " . RSM_SUBSCRIBER_TABLE . " WHERE locale != '' ORDER BY locale ASC";

    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a list of all timezones based on current subscribers.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @return mixed Array containing list of timezones, otherwise false
 */
function db_get_timezone() {
	global $wpdb;

	// Build query
	$sql = "SELECT DISTINCT CONCAT( 'UTC ', timezone ) AS timezone FROM " . RSM_SUBSCRIBER_TABLE . " ORDER BY timezone ASC";

	$results = $wpdb->get_results( $sql, ARRAY_A );

	return $results ? $results : false;
}

/**
 * Returns the delay value for a sequence.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $summary_id The summary ID of the sequence
 * @return mixed The sequence's delay value, otherwise false
 */
function db_get_sequence_delay( $summary_id = 0 ) {
    global $wpdb;

    // Summary ID is required
    $summary_id = absint( $summary_id );
    if ( empty( $summary_id ) )
        return false;

    // Build query
    $sql = $sql = "SELECT delay FROM " . RSM_SUMMARY_TABLE . " WHERE summary_id = " . $summary_id;

    $results = $wpdb->get_var( $sql );

    // If the summary record isn't found (eg deleted), set delay_offset = 0
    return ( null === $results ) ? 0 : $results;
}

/**
 * Gets campaign data.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $args Query arguments
 * @param bool (optional) $count Returns just counts if true
 * @param bool (optional) $limit Whether to limit results (true when returning results to a paginated table)
 * @return mixed Array if campaigns exist, otherwise false
 */
function db_get_campaign_data( $args = array(), $count = false, $limit = true ) {
    global $wpdb;

    // Create whitelists and sanitize
    $allowed_orderby = array( 'campaign_name', 'app_name', 'type', 'sent_count', 'click_count', 'ctr', 'created_date', 'status' );
    $allowed_order   = array( 'asc', 'desc' );
    $allowed_type    = array( 'A', 'I', 'L', 'S' );
    $allowed_status  = array( 'active', 'inactive', 'finished' );
    $list_id         = isset( $args['list-id'] )    ? ( int ) $args['list-id']               : 0;
	$segment_id      = isset( $args['segment-id'] ) ? ( int ) $args['segment-id']            : 0;
    $per_page        = isset( $args['per-page'] )   ? ( int ) $args['per-page']              : 10;
    $paged           = isset( $args['paged'] )      ? ( int ) $args['paged']                 : 1;
    $search          = isset( $args['search'] )     ? sanitize_text_field( $args['search'] ) : null;

    // Validate params and if fail, use defaults
    $type    = isset( $args['type'] )    && in_array( strtoupper ( $args['type'] ), $allowed_type )       ? $args['type']    : 'A';
    $orderby = isset( $args['orderby'] ) && in_array( strtolower ( $args['orderby'] ), $allowed_orderby ) ? $args['orderby'] : 'status';
    $order   = isset( $args['order'] )   && in_array( strtolower ( $args['order'] ), $allowed_order )     ? $args['order']   : 'asc';
    $status  = isset( $args['status'] )  && in_array( strtolower ( $args['status'] ), $allowed_status )   ? $args['status']  : 'all';

    // Calculate offset to display only the current page's data
    $paged    = ( $paged < 1 )     ? 1  : $paged;
    $per_page = ( $per_page < 10 ) ? 10 : $per_page;
    $offset   = ( $paged - 1 ) * $per_page;

    // Build query
    if ( $count ) {
        $sql = "SELECT COUNT( NULLIF( c.status = 'A', 0 ) ) AS active, COUNT( NULLIF( c.status = 'I', 0 ) ) AS inactive, COUNT( NULLIF( c.status = 'F', 0 ) ) AS finished
                  FROM " . RSM_CAMPAIGN_TABLE . " AS c
                 INNER JOIN " . RSM_LIST_TABLE . " AS l ON c.list_id = l.list_id
                 WHERE 1 = 1";
    } else {
        $sql = "SELECT c.campaign_id, c.segment_id, c.campaign_name, c.campaign_desc, l.app_name, c.type,
                       IFNULL( n.count, 0 ) AS sent_count, IFNULL( k.count, 0 ) AS click_count, IFNULL( ROUND( ( IFNULL( k.count, 0 ) / NULLIF( n.count, 0 ) ) * 100 ), 0 ) AS ctr,
                       c.created_date, c.status, c.created_date, '' AS action
                  FROM " . RSM_CAMPAIGN_TABLE . " AS c
                 INNER JOIN " . RSM_LIST_TABLE . " AS l ON c.list_id = l.list_id
                  LEFT JOIN ( SELECT campaign_id, count( notification_id ) AS count
                                FROM " . RSM_NOTIFICATION_TABLE . "
                               WHERE status = 'S'
                               GROUP BY campaign_id ) AS n ON c.campaign_id = n.campaign_id
                  LEFT JOIN ( SELECT n1.campaign_id, count( DISTINCT k1.notification_id ) AS count
                                FROM " . RSM_CLICK_TABLE . " AS k1
                               INNER JOIN " . RSM_NOTIFICATION_TABLE . " AS n1 ON k1.notification_id = n1.notification_id
                               GROUP BY n1.campaign_id ) AS k ON c.campaign_id = k.campaign_id
                 WHERE 1 = 1";
    }

    // Filter by type
    $sql .= ( 'A' != $type ) ? " AND c.type = '" . $type . "'" : " AND c.type IN ( 'I', 'L', 'S' )";

    // Filter by list
    $sql .= ( 0 != $list_id ) ? " AND l.list_id = " . $list_id : "";

	// Filter by segment
	$sql .= ( 0 != $segment_id ) ? " AND c.segment_id = " . $segment_id : "";

    // Include any search paramters
    if ( ! empty( $search ) ) {
        $sql .= " AND ( c.campaign_name LIKE '%" . $search . "%'
                   OR c.campaign_desc LIKE '%" . $search . "%'
                   OR c.created_date LIKE '%" . $search . "%'
                   OR l.app_name LIKE '%" . $search . "%' )";
    }

    // Filter for non-count queries
    if ( ! $count ) {
        // Filter by status
        switch ( $status ) {
            case 'active':
                $sql .= " AND c.status = 'A'";
                break;
            case 'inactive':
                $sql .= " AND c.status = 'I'";
                break;
            case 'finished':
                $sql .= " AND c.status = 'F'";
                break;
        }

        // Order and optionally limit results
        $sql .= " ORDER BY " . $orderby . ' ' . $order . ", c.status asc, c.created_date desc";
        $sql .= ( $limit ) ? " LIMIT " . $offset . ',' . $per_page : '';
    }

    // Get the data
    $results = $count ? $wpdb->get_row( $sql, ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets the current delay_offset for a campaign.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $campaign_id Campaign ID of follow-up sequence
 * @param int delay Delay to use for calculating delay_offset
 * @return mixed Delay_offset if successful, otherwise false
 */
function db_get_delay_offset( $campaign_id = 0, $delay = 0 ) {
    global $wpdb;

    // List ID is required
    $campaign_id = absint( $campaign_id );
    if ( empty( $campaign_id ) )
        return false;

    // Build query
    $sql = "SELECT IFNULL( s2.delay_offset, 0 ) + %d AS delay_offset
              FROM " . RSM_SUMMARY_TABLE . " AS s
              LEFT JOIN ( SELECT campaign_id, SUM( delay ) AS delay_offset
                            FROM " . RSM_SUMMARY_TABLE . "
                           GROUP BY campaign_id ) AS s2 ON s.campaign_id = s2.campaign_id
             WHERE s.campaign_id = %d
             GROUP BY s.campaign_id";

    // Prepare query for execution
    $sql = $wpdb->prepare( $sql,
        $delay,
        $campaign_id
    );

    // Get the data
    $results = $wpdb->get_var( $sql );

    // If this is the first summary record, set delay_offset = delay
    return ( null === $results ) ? $delay : $results;
}

/**
 * Gets subscriber data.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $args Query arguments
 * @param bool (optional) $count Returns just counts if true
 * @param bool (optional) $export Returns data formatted for exporting
 * @return mixed Array if subscribers exist, otherwise false
 */
function db_get_subscriber_data( $args = array(), $count = false, $export = false ) {
    global $wpdb;

    // Create whitelists and sanitize
    $allowed_orderby = array( 'sub_id', 'full_name', 'uid', 'app_name', 'email', 'created_date', 'status' );
    $allowed_order   = array( 'asc', 'desc' );
    $allowed_status  = array( 'active', 'inactive' );
    $list_id         = isset( $args['list-id'] )    ? ( int ) $args['list-id']               : 0;
	$segment_id      = isset( $args['segment-id'] ) ? ( int ) $args['segment-id']            : 0;
    $per_page        = isset( $args['per-page'] )   ? ( int ) $args['per-page']              : 10;
    $paged           = isset( $args['paged'] )      ? ( int ) $args['paged']                 : 1;
    $search          = isset( $args['search'] )     ? sanitize_text_field( $args['search'] ) : null;

    // Validate params and if fail, use defaults
    $orderby = isset( $args['orderby'] ) && in_array( strtolower ( $args['orderby'] ), $allowed_orderby ) ? $args['orderby'] : 'full_name';
    $order   = isset( $args['order'] )   && in_array( strtolower ( $args['order'] ), $allowed_order )     ? $args['order']   : 'asc';
    $status  = isset( $args['status'] )  && in_array( strtolower ( $args['status'] ), $allowed_status )   ? $args['status']  : 'all';

    // Calculate offset to display only the current page's data
    $paged    = ( $paged < 1 )     ? 1  : $paged;
    $per_page = ( $per_page < 10 ) ? 10 : $per_page;
    $offset   = ( $paged - 1 ) * $per_page;

    // Build query
    if ( $count ) {
        $sql = "SELECT COUNT( NULLIF( s.status = 'A', 0 ) ) AS active, COUNT( NULLIF( s.status = 'I', 0 ) ) AS inactive";
    } elseif ( $export ) {
        $sql = "SELECT l.app_name, s.uid, s.full_name, s.first_name, s.last_name, s.email, s.link, s.gender, s.locale, s.timezone,
                       CASE WHEN s.status = 'A' THEN 'Active' ELSE 'Inactive' END AS status, s.created_date";
    } else {
        $sql = "SELECT s.subscriber_id AS sub_id, s.full_name, s.uid, l.app_name, s.email, s.link, s.created_date, s.status, '' AS action";
    }
    $sql .= " FROM " . RSM_SUBSCRIBER_TABLE . " AS s
             INNER JOIN " . RSM_LIST_TABLE . " AS l ON s.list_id = l.list_id
             WHERE 1 = 1";

    $sql .= ( 0 != $list_id ) ? " AND l.list_id = " . $list_id : "";

	// Filter using a segment
	if ( 0 != $segment_id ) {
		$id_list = db_get_segment_data( array( 'segment-id' => $segment_id ) );

		if ( $id_list ) {
			$prefix = $where_in = '';
			foreach ( $id_list as $uid ) {
				$where_in .= $prefix . $uid['subscriber_id'];
				$prefix = ', ';
			}
		} else {
			$where_in = 0;
		}
		$sql .=  " AND s.subscriber_id IN ( " . $where_in . " )";
	}

    // Include any search paramters
    if ( ! empty( $search ) ) {
        $sql .= " AND ( s.full_name LIKE '%" . $search . "%'
                    OR s.uid LIKE '%" . $search . "%'
                    OR l.app_name LIKE '%" . $search . "%'
                    OR s.email LIKE '%" . $search . "%'
                    OR s.created_date LIKE '%" . $search . "%' )";
    }

    // Filter for non-count queries
    if ( ! $count ) {
        // Filter by status
        switch( $status ) {
            case 'active':
                $sql .= " AND s.status = 'A'";
                break;
            case 'inactive':
                $sql .= " AND s.status = 'I'";
                break;
        }

        // Order and limit results
        if ( $export ) {
            $sql .=' ORDER BY l.app_name, full_name asc';
        } else {
            $sql .= " ORDER BY ".  $orderby . ' ' . $order . " LIMIT " . $offset . ',' . $per_page;
        }
    }

    // Get the data
    $results = $count ? $wpdb->get_row( $sql, ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a list of segments a specific subscriber belongs to.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $subscriber_id Subscriber ID to be used
 * @return mixed Array of segments subscriber matches, otherwise false
 */
function db_get_subscriber_segments( $subscriber_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $subscriber_id ) )
		return false;

	// Get all segment_id's
	$sql = "SELECT segment_id, segment_name FROM " . RSM_SEGMENT_TABLE;
	$results = $wpdb->get_results( $sql, ARRAY_A );

	// Find all segments that matches subscriber
	if ( $results ) {
		$sub_segments = array();
		$sub_segments[] = array( 'segment_id' => 0, 'segment_name' => 'All Subscribers' );

		foreach ( $results as $segment ) {
			$segment_id   = $segment['segment_id'];
			$segment_name = $segment['segment_name'];

			if ( db_get_segment_data( array( 'segment-id' => $segment_id, 'subscriber-id' => $subscriber_id ), true ) ) {
				$sub_segments[] = array( 'segment_id' => $segment_id, 'segment_name' => $segment_name );
			}
		}
	}

	// Return results
	return ( false === $results ) ? false : $sub_segments;
}

/**
 * Gets a subset of subscribers data (or count) based on segment criteria.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $args Query arguments
 * @param bool (optional) $count Return number of subscribers that match segment criteria
 * @return mixed Array of subscribers in segment (or count), otherwise 0
 */
function db_get_segment_data( $args = array(), $count = false ) {
    global $wpdb;

	// List ID or Segment ID is required
	$list_id       = absint( $args['list-id'] );
	$segment_id    = absint( $args['segment-id'] );
	$subscriber_id = absint( $args['subscriber-id'] );
	if ( empty( $list_id ) && empty ( $segment_id ) )
		return false;

	// Get match type if supplied, otherwise default to null
	$match_type = isset( $args['match-type'] ) ? $args['match-type'] : null;

	// If Segment ID is provided, get segment details otherwise use args provided
	if ( $segment_id ) {
		if ( empty( $match_type ) ) {
			$sql = "SELECT match_type FROM " . RSM_SEGMENT_TABLE . " WHERE segment_id = " . $segment_id;
			$match_type = $wpdb->get_var( $sql );
		}

		$sql = "SELECT field, rule, value FROM " . RSM_SEGMENT_DETAIL_TABLE . " WHERE segment_id = " . $segment_id;
		$criteria = $wpdb->get_results( $sql, ARRAY_N );

		// Build query
		$sql = "SELECT s.subscriber_id
	              FROM " . RSM_SUBSCRIBER_TABLE . " AS s
	             INNER JOIN " . RSM_SEGMENT_TABLE . " AS seg ON s.list_id = seg.list_id
	             WHERE seg.segment_id = " . $segment_id . "
	               AND s.status = 'A'";
	} else {
		// The match type will be unknown when only a list_id is supplied during campaign creation and
		// it's still undetermined which (if any) segment will be used, therefore we default to 'any'.
		if ( empty( $match_type ) ) $match_type = 'any';

		// Remap segment data array
		$criteria = array();
		for ( $i = 0; $i < count( $args['fields'] ); $i ++ ) {
			$criteria[] = array( $args['fields'][ $i ], $args['rules'][ $i ], $args['values'][ $i ] );
		}

		// Build query
		$sql = "SELECT s.subscriber_id
	              FROM " . RSM_SUBSCRIBER_TABLE . " AS s
	             WHERE s.list_id = " . $list_id . "
	               AND s.status = 'A'";
	}

	// Create whitelists
	$allowed_fields = array( 'email', 'first_name', 'last_name', 'gender', 'locale', 'timezone', 'clicked', 'optin_date', 'uid' );
	$allowed_rules  = array( 'is_equal_to', 'is_not_equal_to', 'contains', 'does_not_contain', 'is_one_of', 'is_not_one_of', 'never', 'any_campaign', 'before', 'after' );
	$allowed_gender = array( 'male', 'female' );

	// Filter by subscriber_id
	$sql .= ( 0 != $subscriber_id ) ? " AND s.subscriber_id = " . $subscriber_id : "";

	// For 'any' match type, use OR; for 'all' match type, use AND
	$op = ( 'any' == $match_type ) ? 'OR': 'AND';
	$where = "";

	// Loop through, sanitize and validate; add to new array if OK
	foreach ( $criteria as $i => $entry ) {
		$field = isset( $entry[0] ) && in_array( $entry[0], $allowed_fields ) ? $entry[0] : null;
		$rule  = isset( $entry[1] ) && in_array( $entry[1], $allowed_rules )  ? $entry[1] : null;
		$value = isset( $entry[2] ) ? $entry[2] : null;

		// If field and rule are valid, continue
		if ( $field && $rule ) {

			// Validate & sanitize the user inputted value column
			switch ( $field ) {
				case 'email':
				case 'first_name':
				case 'last_name':
				case 'uid':
					$value = sanitize_text_field( preg_replace( '/\s+/', '', $value ) );  // removes all spaces
					break;

				case 'gender':
					$value = in_array( strtolower( $value ), $allowed_gender ) ? strtolower( $value ) : null;
					break;

				case 'timezone':
					$value = str_replace( 'UTC ', '', $value );
					break;
			}

			if ( 'before' == $rule || 'after' == $rule ) {
				$value = rsm_valid_date( $value ) ? $value : null;
			}

			// If our value isn't null, continue
			if ( null !== $value ){
				switch ( $rule ) {
					case 'is_equal_to':
						$where .= " " . $op . " s." . $field . " = '" . $value . "'";
						break;

					case 'is_not_equal_to':
						$where .= " " . $op . " s." . $field . " <> '" . $value . "'";
						break;

					case 'contains':
						$where .= " " . $op . " s." . $field . " LIKE '%" . $value . "%'";
						break;

					case 'does_not_contain':
						$where .= " " . $op . " s." . $field . " NOT LIKE '%" . $value . "%'";
						break;

					case 'is_one_of':
						$where .= " " . $op . " s." . $field . " IN ('" . str_replace( ",", "','", $value ) . "')";
						break;

					case 'is_not_one_of':
						$where .= " " . $op . " s." . $field . " NOT IN ('" . str_replace( ",", "','", $value ) . "')";
						break;

					case 'never':
					case 'any_campaign':
						$not = ( 'never' == $rule ) ? 'NOT ': '';
						$where .= " " . $op . " " . $not . "EXISTS ( SELECT 1
							                                           FROM " . RSM_CLICK_TABLE . " AS c
							                                          WHERE s.subscriber_id = c.subscriber_id
							                                          GROUP BY c.subscriber_id )";
						break;

					case 'before':
					case 'after':
						$gt_lt = ( 'before' == $rule ) ? '<': '>';

						if ( 'optin_date' == $field ) {
							$where .= " " . $op . " date( s.created_date ) " . $gt_lt . " date( '" . $value . "' )";

						} elseif ( 'clicked' == $field ) {
							$where .= " " . $op . " EXISTS ( SELECT 1
					                                           FROM " . RSM_CLICK_TABLE . " AS c
					                                          WHERE s.subscriber_id = c.subscriber_id
					                                            AND date( c.click_date ) " . $gt_lt . " date( '" . $value . "' )
					                                          GROUP BY c.subscriber_id )";
						}
						break;
				}
			}
		}
	}

	$pos = strpos( $where, $op );
	if ( $pos !== false ) {
		$sql .= " AND (" . substr_replace( $where, "", $pos, strlen( $op ) ) . " )";
	}

    // Get the data
    $results = $wpdb->get_results( $sql, ARRAY_A );

    // Return results
    if ( $count ) {
        return ( false === $results ) ? false : count( $results );
    } else {
        return ( false === $results ) ? false : ( empty( $results) ? 0 : $results );
    }
}

/**
 * Gets the list of segments or their filtering criteria.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int (optional) $segment_id The segment_id whose detail is to be queried
 * @param bool (optional) $sort_by_app Determines whether to sort by app_name
 * @return mixed Array containing list of segments or criteria, otherwise false
 */
function db_get_segment_detail( $segment_id = 0, $sort_by_app = false ) {
	global $wpdb;

	// Segment ID must be a positive integer
	$segment_id = absint( $segment_id );

	// Check for segment_id
	if ( $segment_id ){

		// Build query
		$sql = "SELECT s.segment_id, s.list_id, s.segment_name, s.match_type, d.field, d.rule, d.value
			      FROM " . RSM_SEGMENT_TABLE . " AS s
			     INNER JOIN " . RSM_SEGMENT_DETAIL_TABLE . " AS d ON s.segment_id = d.segment_id
			     WHERE s.segment_id = " . $segment_id . "
			     ORDER BY d.segment_detail_id";
	} else {

		// Build query
		$sql = "SELECT s.segment_id, s.segment_name, l.app_name, s.list_id, s.match_type
				  FROM " . RSM_SEGMENT_TABLE. " AS s
				 INNER JOIN " . RSM_LIST_TABLE. " AS l ON s.list_id = l.list_id";

		$sql .= $sort_by_app ? " ORDER BY l.app_name ASC, s.segment_name ASC" : " ORDER BY s.segment_name ASC";
	}

	// Execute query
	$results = $wpdb->get_results( $sql, ARRAY_A );

	return $results ? $results : false;
}

/**
 * Gets all segments associated with a list.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id The summary ID of the sequence
 * @return mixed Array containing list of segments or criteria, otherwise false
 */
function db_get_list_segment( $list_id = 0 ) {
	global $wpdb;

	// List ID is required
	$list_id = absint( $list_id );
	if ( empty( $list_id ) )
		return false;

	// Build query
	$sql = "SELECT s.segment_id, s.segment_name, s.list_id, s.match_type
			  FROM " . RSM_SEGMENT_TABLE. " AS s
			 INNER JOIN " . RSM_LIST_TABLE. " AS l ON s.list_id = l.list_id
			 WHERE s.list_id = " . $list_id . "
			 ORDER BY s.segment_name ASC";

	// Execute query
	$results = $wpdb->get_results( $sql, ARRAY_A );

	return $results ? $results : false;
}

/**
 * Gets notification data.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $args Query arguments
 * @param bool (optional) $count Returns just counts if true
 * @return mixed Array if notification data exists, otherwise false
 */
function db_get_notification_data( $args = array(), $count = false ) {
    global $wpdb;

    // Create whitelists and sanitize
    $allowed_orderby = array( 'app_name', 'full_name', 'message', 'status', 'send_date' );
    $allowed_order   = array( 'asc', 'desc' );
    $allowed_type    = array( 'A', 'I', 'L', 'S', 'W' );
    $allowed_status  = array( 'sent', 'not_sent', 'sent_error' );
    $per_page        = isset( $args['per-page'] )            ? ( int ) $args['per-page']              : 10;
    $paged           = isset( $args['paged'] )               ? ( int ) $args['paged']                 : 1;
    $list_id         = isset( $args['list-id'] )             ? ( int ) $args['list-id']               : 0;
	$segment_id      = isset( $args['segment-id'] )          ? ( int ) $args['segment-id']            : 0;
    $campaign_id     = isset( $args['campaign-id'] )         ? ( int ) $args['campaign-id']           : 0;
    $search          = isset( $args['search'] )              ? sanitize_text_field( $args['search'] ) : null;
    $start_date      = rsm_valid_date( $args['start-date'] ) ? $args['start-date']                    : null;
    $end_date        = rsm_valid_date( $args['end-date'] )   ? $args['end-date']                      : null;

    // Validate params and if fail, use defaults
    $type    = isset( $args['type'] )    && in_array( strtoupper ( $args['type'] ), $allowed_type )       ? $args['type']    : 'A';
    $orderby = isset( $args['orderby'] ) && in_array( strtolower ( $args['orderby'] ), $allowed_orderby ) ? $args['orderby'] : 'send_date';
    $order   = isset( $args['order'] )   && in_array( strtolower ( $args['order'] ), $allowed_order )     ? $args['order']   : 'desc';
    $status  = isset( $args['status'] )  && in_array( strtolower ( $args['status'] ), $allowed_status )   ? $args['status']  : 'all';

    // Calcualte offset to display only the current page's data
    $paged    = ( $paged < 1 )     ? 1  : $paged;
    $per_page = ( $per_page < 10 ) ? 10 : $per_page;
    $offset   = ( $paged - 1 ) * $per_page;

    // Build query
    if ( $count ) {
        $sql = "SELECT COUNT( NULLIF( n.status = 'S', 0 ) ) AS sent, COUNT( NULLIF( n.status = 'N', 0 ) ) AS not_sent, COUNT( NULLIF( n.status = 'E', 0 ) ) AS sent_error";
    } else {
        $sql = "SELECT n.notification_id, l.app_name, s.full_name, c.campaign_name, n.message, n.type AS type, n.status, '' AS action, n.send_date, n.sent_date";
    }
    $sql .= " FROM " . RSM_NOTIFICATION_TABLE . " AS n
             INNER JOIN " . RSM_LIST_TABLE . " AS l ON n.list_id = l.list_id
              LEFT JOIN " . RSM_SUBSCRIBER_TABLE . " AS s ON n.subscriber_id = s.subscriber_id
              LEFT JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON n.campaign_id = c.campaign_id
             WHERE 1 = 1";

    // Include any search paramters
    if ( ! empty( $search ) ) {
        $sql .= " AND ( l.app_name LIKE '%" . $search . "%'
                   OR s.full_name LIKE '%" . $search . "%'
                   OR n.message LIKE '%" . $search . "%'
                   OR n.send_date LIKE '%" . $search . "%' )";
    }

    // Filter by type
    $sql .= ( 'A' != $type ) ? " AND n.type = '" . $type . "'" : "";

    // Filter by list
    $sql .= ( 0 != $list_id ) ? " AND l.list_id = " . $list_id : "";

	// Filter by segment
	$sql .= ( 0 != $segment_id ) ? " AND c.segment_id = " . $segment_id : "";

    // Filter by campaign
    $sql .= ( 0 != $campaign_id ) ? " AND n.campaign_id = " . $campaign_id : "";

    // Filter by dates
    $sql .= $start_date ? " AND ( ( n.status = 'N' AND n.send_date >= '" . $start_date . "' ) OR ( n.status <> 'N' AND n.sent_date >= '" . $start_date . "' ) ) " : "";
    $sql .= $end_date   ? " AND ( ( n.status = 'N' AND n.send_date <= '" . $end_date . "' ) OR ( n.status <> 'N' AND n.sent_date <= '" . $end_date . "' ) ) " : "";

    // Filter for non-count queries
    if ( ! $count ) {
        // Filter by status
        switch( $status ) {
            case 'sent':
                $sql .= " AND n.status = 'S'";
                break;
            case 'not_sent':
                $sql .= " AND n.status = 'N'";
                break;
            case 'sent_error':
                $sql .= " AND n.status = 'E'";
                break;
        }

        // Order and limit results
        $sql .= " ORDER BY ".  $orderby . ' ' . $order . " LIMIT " . $offset . ',' . $per_page;
    }

    // Get the datas
    $results = $count ? $wpdb->get_row( $sql, ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets all qualifying, queued notifications based on a supplied GMT date/time.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string $datetime_gmt The GMT date/time used for finding qualifying notifications
 * @return mixed Array if notifications exist, otherwise false
 */
function db_get_queued_notifications( $datetime_gmt = null ) {
    global $wpdb;

    // Date/time in GMT format required
    if ( ! rsm_valid_date( $datetime_gmt ) )
        return false;

    // Build query
    $sql = "SELECT n.notification_id, n.list_id, n.campaign_id, n.summary_id, n.type, l.app_name, l.app_id, l.app_secret, s.subscriber_id, s.uid, n.message, n.redirect_type
              FROM " . RSM_NOTIFICATION_TABLE . " AS n
             INNER JOIN " . RSM_LIST_TABLE . " AS l ON n.list_id = l.list_id
             INNER JOIN " . RSM_SUBSCRIBER_TABLE . " AS s ON n.subscriber_id = s.subscriber_id
             WHERE n.send_date_gmt <= '" . $datetime_gmt . "'
               AND n.status = 'N'
             ORDER BY n.list_id, n.campaign_id, n.summary_id";

    // Get the data
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets all summary data for a given campaign.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $campaign_id Campaign ID of summary data to retrieve
 * @return mixed Array containing all summary data including an autoincrement field, otherwise false
 */
function db_get_summary_data( $campaign_id = 0 ) {
    global $wpdb;

    // Campaign ID is required
    $campaign_id = absint( $campaign_id );
    if ( empty( $campaign_id ) )
        return false;

    // Build query
    $sql = "SELECT summary_id, list_id, campaign_id, message, redirect_url, redirect_type, delay, schedule_date, @rownum:=@rownum+1 AS msg_no
              FROM " . RSM_SUMMARY_TABLE . " AS s, (SELECT @rownum:=0) AS r
             WHERE campaign_id = " . $campaign_id . "
             ORDER BY summary_id ASC";

    // Get the data
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a single list row.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID to be deleted
 * @return mixed Array containing single list row, otherwise false
 */
function db_get_list_row( $list_id = 0 ) {
    global $wpdb;

    // List ID is required
    $list_id = absint( $list_id );
    if ( empty( $list_id ) )
        return false;

    // Build query
    $sql = "SELECT list_id, app_name, app_id, app_secret, okay_url, cancel_url, show_welcome, welcome_msg, welcome_url, redirect_type, integrate_ar, optin_url
              FROM " . RSM_LIST_TABLE . "
             WHERE list_id = ".  $list_id;

    $results = $wpdb->get_row( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a single subscriber row.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $subscriber_id Subscriber ID to be retrieved
 * @return mixed Array containing single subscriber row, otherwise false
 */
function db_get_subscriber_row( $subscriber_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $subscriber_id ) )
		return false;

	// Build query
	$sql = "SELECT s.subscriber_id, l.app_name, s.uid, s.full_name, s.first_name, s.last_name, s.email, s.link, s.gender, s.locale, s.timezone, s.status, s.created_date, s.created_date_gmt
              FROM " . RSM_SUBSCRIBER_TABLE . " AS s
             INNER JOIN " . RSM_LIST_TABLE . " AS l ON s.list_id = l.list_id
             WHERE s.subscriber_id = ".  $subscriber_id;

	$results = $wpdb->get_row( $sql, ARRAY_A );

	return $results ? $results : false;
}

/**
 * Gets a single campaign row.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $campaign_id Campaign ID to be retrieved
 * @return mixed Array containing single campaign row, otherwise false
 */
function db_get_campaign_row( $campaign_id = 0 ) {
    global $wpdb;

    // Campaign ID is required
    $campaign_id = absint( $campaign_id );
    if ( empty( $campaign_id ) )
        return false;

    // Build query
    $sql = "SELECT campaign_id, list_id, segment_id, campaign_name, campaign_desc, type, status
              FROM " . RSM_CAMPAIGN_TABLE . "
             WHERE campaign_id = ".  $campaign_id;

    $results = $wpdb->get_row( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets a single summary row.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $summary_id Summary ID to be retrieved
 * @return mixed Array containing single summary row, otherwise false
 */
function db_get_summary_row( $summary_id = 0 ) {
    global $wpdb;

    // Summary ID is required
    $summary_id = absint( $summary_id );
    if ( empty( $summary_id ) )
         return false;

    // Build query
    $sql = "SELECT summary_id, list_id, campaign_id, message, redirect_url, redirect_type, delay, schedule_date
              FROM " . RSM_SUMMARY_TABLE . "
             WHERE summary_id = ".  $summary_id;

    $results = $wpdb->get_row( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets the redirect URL and type for a given notification.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $notification_id Notification ID to retrieve data for
 * @return mixed Array containing redirect URL and type, otherwise false
 */
function db_get_notification_redirect( $notification_id = 0 ) {
    global $wpdb;

    // Notification ID is required
    $notification_id = absint( $notification_id );
    if ( empty( $notification_id ) )
        return false;

    // Build query
    $sql = "SELECT redirect_url, redirect_type
              FROM " . RSM_NOTIFICATION_TABLE . "
             WHERE notification_id = ".  $notification_id;

    $results = $wpdb->get_row( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets the redirect URL and type for a given notification.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $notification_id Notification ID to retrieve status for
 * @return mixed String containing notification status, otherwise false
 */
function db_get_notification_status( $notification_id = 0 ) {
	global $wpdb;

	// Notification ID is required
	$notification_id = absint( $notification_id );
	if ( empty( $notification_id ) )
		return false;

	// Build query
	$sql = "SELECT status FROM " . RSM_NOTIFICATION_TABLE . " WHERE notification_id = ".  $notification_id;
	$results = $wpdb->get_var( $sql );

	return ( $results !== null ) ? $results : false;
}

/**
 * Gets all autoresponder data, or just the data for a specified autoresponder.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string (optional) $ar_name The autoresponder name
 * @return mixed Array containing autoresponder data, otherwise false
 */
function db_get_ar_data( $ar_name = null ) {
    global $wpdb;

    // Build query
    $sql = "SELECT ar_name, api_key, options, connected FROM " . RSM_AUTORESPONDER_TABLE;
    if ( empty ( $ar_name ) ) {
        $results = $wpdb->get_results( $sql, ARRAY_A );
    } else {
        $sql .= " WHERE ar_name = %s";
        $sql = $wpdb->prepare( $sql, $ar_name );
        $results = $wpdb->get_row( $sql, ARRAY_A );
    }

    return $results ? $results : false;
}

/**
 * Gets autoresponder lists.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string (optional) $ar_name The autoresponder name
 * @return mixed Array containing all autoresponder data, otherwise false
 */
function db_get_ar_lists( $list_id = 0, $ar_name = null ) {
    global $wpdb;

    // List ID, if provided, must be positive integer
    $list_id = absint( $list_id );

    // Build query and conditionally constrain by list_id
    if ( empty( $list_id ) ) {
        $sql = "SELECT l.ar_name, l.ar_list_name, l.ar_list_value FROM " . RSM_AUTORESPONDER_LIST_TABLE . " AS l";
        if ( ! empty ( $ar_name ) ) {
            $sql .= " WHERE l.ar_name = %s";
            $sql = $wpdb->prepare( $sql, $ar_name );
        }
    } else {
        $sql = "SELECT l.ar_name, l.ar_list_name, l.ar_list_value,
                       CASE WHEN i.list_id IS NULL THEN 'F' ELSE 'T' END AS selected
                  FROM " . RSM_AUTORESPONDER_LIST_TABLE . " AS l
                  LEFT JOIN " . RSM_INTEGRATED_AR_TABLE . " AS i ON l.ar_list_value = i.ar_list_value AND i.list_id = " . $list_id;
    }
    $sql .= " ORDER BY l.ar_name, l.ar_list_name";

    // Execute query
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets an autoresponder's API key
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string $ar_name The autoresponder name
 * @return mixed API key if successful, otherwise false
 */
function db_get_ar_api_key( $ar_name = null ) {
    global $wpdb;

    // Build query
    $sql = "SELECT api_key FROM " . RSM_AUTORESPONDER_TABLE . " WHERE ar_name = %s";

    // Prepare query for execution
    $sql = $wpdb->prepare( $sql,
        $ar_name
    );

    // Get the data
    $results = $wpdb->get_var( $sql );

    return $results ? $results : false;
}

/**
 * Gets system log data for export.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string (optional) $type The type of log records to export
 * @return mixed Array containing all log data, otherwise false
 */
function db_get_log_for_export( $type = null ) {
    global $wpdb;

    // Build query
    $sql = "SELECT l.log_id, l.type, l.description, l.meta, l.backtrace, l.created_date, l.created_date_gmt
              FROM " . RSM_LOG_TABLE . " AS l
              WHERE l.created_date_gmt > DATE_SUB( '" . rsm_get_datetime_gmt() . "', INTERVAL 2 WEEK )";

    // Filter by type
    switch( $type ) {
        case 'event':
            $sql .= " AND l.type = 'V'";
            break;
        case 'error':
            $sql .= " AND l.type = 'E'";
            break;
    }

    $sql .= " ORDER BY l.log_id asc";

    // Get the data
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets top performing statistics.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $limit The number of rows to return
 * @return mixed Array containing top performing statistics, otherwise false
 */
function db_get_top_performing( $limit = 0 ) {
    global $wpdb;

    // Build query
    $sql = "SELECT l.app_name, s.message, p.type, IFNULL( n.count, 0 ) AS sent_count, IFNULL( c.count, 0 ) AS click_count, IFNULL( ROUND( ( IFNULL( c.count, 0 ) / NULLIF( n.count, 0 ) ) * 100 ), 0 ) AS ctr
              FROM " . RSM_LIST_TABLE . " AS l
             INNER JOIN " . RSM_SUMMARY_TABLE . " AS s ON l.list_id = s.list_id
             INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS p ON s.campaign_id = p.campaign_id
              LEFT JOIN ( SELECT summary_id, count( notification_id ) AS count
                            FROM " . RSM_NOTIFICATION_TABLE . "
                           WHERE status = 'S'
                           GROUP BY summary_id ) AS n ON s.summary_id = n.summary_id
              LEFT JOIN ( SELECT n1.summary_id, count( DISTINCT c1.notification_id ) AS count
                            FROM " . RSM_CLICK_TABLE . " AS c1
                           INNER JOIN " . RSM_NOTIFICATION_TABLE . " AS n1 ON c1.notification_id = n1.notification_id
                           GROUP BY n1.summary_id ) AS c ON s.summary_id = c.summary_id
             ORDER BY ctr desc, click_count desc, sent_count desc ";

    $limit = absint( $limit );
    if ( ! empty( $limit ) )
        $sql .= "LIMIT " . $limit;

    // Execute query
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets list performance statistics.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array (optional) $args The query parameters
 * @return mixed Array containing list performance statistics, otherwise false
 */
function db_get_list_performance( $args = array() ) {
    global $wpdb;

    // Build query
    $sql = "SELECT l.app_name, IFNULL( s.count, 0 ) AS sub_count, IFNULL( n.count, 0 ) AS sent_count, IFNULL( c.count, 0 ) AS click_count,  IFNULL( ROUND( ( IFNULL( c.count, 0 ) / NULLIF( n.count, 0 ) ) * 100 ), 0 ) AS ctr
              FROM " . RSM_LIST_TABLE . " AS l
              LEFT JOIN ( SELECT list_id, COUNT( subscriber_id ) AS count
                            FROM " . RSM_SUBSCRIBER_TABLE . "
                           GROUP BY list_id ) AS s ON l.list_id = s.list_id
              LEFT JOIN ( SELECT list_id, COUNT( notification_id ) AS count
                            FROM " . RSM_NOTIFICATION_TABLE . "
                           WHERE status = 'S'
                           GROUP BY list_id ) AS n ON l.list_id = n.list_id
              LEFT JOIN ( SELECT n1.list_id, count( DISTINCT c1.notification_id ) AS count
                            FROM " . RSM_CLICK_TABLE . " AS c1
                           INNER JOIN " . RSM_NOTIFICATION_TABLE . " AS n1 ON c1.notification_id = n1.notification_id
                           GROUP BY n1.list_id ) AS c ON l.list_id = c.list_id";

    // Check for query parameters
    if ( ! empty( $args ) ){
        // Create whitelists and sanitize
        $allowed_orderby = array( 'app_name', 'sub_count', 'sent_count', 'click_count', 'ctr' );
        $allowed_order   = array( 'asc', 'desc' );

        // Validate params and if fail, use defaults
        $orderby = isset( $args['orderby'] ) && in_array( strtolower ( $args['orderby'] ), $allowed_orderby ) ? $args['orderby'] : 'app_name';
        $order   = isset( $args['order'] )   && in_array( strtolower ( $args['order'] ), $allowed_order )     ? $args['order']   : 'asc';

        $sql .= " ORDER BY ".  $orderby . ' ' . $order;
    } else {
        // Default sort order
        $sql .= " ORDER BY app_name asc";
    }

    // Execute query
    $results = $wpdb->get_results( $sql, ARRAY_A );

    return $results ? $results : false;
}

/**
 * Gets quick stats for day, week and month.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @return mixed Array containing quick stats, otherwise false
 */
function db_get_quick_stats() {
    global $wpdb;

    // Get the current date using WP timezone; using WP tz because only data stored and worked with in UTC. We want to
    // otherwise display data consistent with the user's current WP settings.
    $cur_date = date( 'Y-m-d', current_time( 'timestamp' ) );
    $mode     = ( 0 == get_option( 'start_of_week' ) ? 0 : 1 );

    // Build query
    $sql = "SELECT IFNULL( s1.count, 0 ) AS day_sub_count, IFNULL( n1.count, 0 ) AS day_sent_count, IFNULL( c1.count, 0 ) AS day_click_count,  IFNULL( ROUND( ( IFNULL( c1.count, 0 ) / NULLIF( n1.count, 0 ) ) * 100 ), 0 ) AS day_ctr,
                   IFNULL( s2.count, 0 ) AS week_sub_count, IFNULL( n2.count, 0 ) AS week_sent_count, IFNULL( c2.count, 0 ) AS week_click_count,  IFNULL( ROUND( ( IFNULL( c2.count, 0 ) / NULLIF( n2.count, 0 ) ) * 100 ), 0 ) AS week_ctr,
                   IFNULL( s3.count, 0 ) AS month_sub_count, IFNULL( n3.count, 0 ) AS month_sent_count, IFNULL( c3.count, 0 ) AS month_click_count,  IFNULL( ROUND( ( IFNULL( c3.count, 0 ) / NULLIF( n3.count, 0 ) ) * 100 ), 0 ) AS month_ctr
              FROM ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE created_date >= '" . $cur_date . "' ) AS s1,
                   ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE YEARWEEK( created_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS s2,
                   ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE YEAR( created_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( created_date ) = MONTH( '" . $cur_date . "' ) ) AS s3,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND sent_date >= '" . $cur_date . "' ) AS n1,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND YEARWEEK( sent_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS n2,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND YEAR( sent_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( sent_date ) = MONTH( '" . $cur_date . "' ) ) AS n3,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE click_date >= '" . $cur_date . "' ) AS c1,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE YEARWEEK( click_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS c2,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE YEAR( click_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( click_date ) = MONTH( '" . $cur_date . "' ) ) AS c3";

     // Get the data
    $results = $wpdb->get_row( $sql, ARRAY_A );

    return $results ? $results : false;
}

function db_get_quick_stats2() {
    global $wpdb;

    // Get the current date using WP timezone; using WP tz because only data stored and worked with in UTC. We want to
    // otherwise display data consistent with the user's current WP settings.
    $cur_date = date( 'Y-m-d', current_time( 'timestamp' ) );
    $cur_date = '2016-6-30';
    $mode     = ( 0 == get_option( 'start_of_week' ) ? 0 : 1 );

    // Build query
    $sql = "SELECT IFNULL( s1.count, 0 ) AS day_sub_count, IFNULL( n1.count, 0 ) AS day_sent_count, IFNULL( c1.count, 0 ) AS day_click_count,  IFNULL( ROUND( ( IFNULL( c1.count, 0 ) / NULLIF( n1.count, 0 ) ) * 100 ), 0 ) AS day_ctr,
                   IFNULL( s2.count, 0 ) AS week_sub_count, IFNULL( n2.count, 0 ) AS week_sent_count, IFNULL( c2.count, 0 ) AS week_click_count,  IFNULL( ROUND( ( IFNULL( c2.count, 0 ) / NULLIF( n2.count, 0 ) ) * 100 ), 0 ) AS week_ctr,
                   IFNULL( s3.count, 0 ) AS month_sub_count, IFNULL( n3.count, 0 ) AS month_sent_count, IFNULL( c3.count, 0 ) AS month_click_count,  IFNULL( ROUND( ( IFNULL( c3.count, 0 ) / NULLIF( n3.count, 0 ) ) * 100 ), 0 ) AS month_ctr
              FROM ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE created_date >= '" . $cur_date . "' AND created_date < '2016-07-01' ) AS s1,
                   ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE YEARWEEK( created_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS s2,
                   ( SELECT COUNT( subscriber_id ) AS count
                       FROM " . RSM_SUBSCRIBER_TABLE . "
                      WHERE YEAR( created_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( created_date ) = MONTH( '" . $cur_date . "' ) ) AS s3,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND sent_date >= '" . $cur_date . "' AND sent_date < '2016-07-01' ) AS n1,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND YEARWEEK( sent_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS n2,
                   ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND YEAR( sent_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( sent_date ) = MONTH( '" . $cur_date . "' ) ) AS n3,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE click_date >= '" . $cur_date . "' AND click_date < '2016-07-01' ) AS c1,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE YEARWEEK( click_date, " . $mode . " ) = YEARWEEK( '" . $cur_date . "', " . $mode . " ) ) AS c2,
                   ( SELECT COUNT( DISTINCT notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . "
                      WHERE YEAR( click_date ) = YEAR( '" . $cur_date . "' )
                        AND MONTH( click_date ) = MONTH( '" . $cur_date . "' ) ) AS c3";

    // Get the data
    $results = $wpdb->get_row( $sql, ARRAY_A );

	// Hardcode demo data
	$results['week_sent_count'] = '677';
	$results['week_click_count'] = '433';
	$results['week_ctr'] = '64';

    return $results ? $results : false;
}

/**
 * Gets summary stats (delivered, unique clicks, CTR) for a subscriber.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int subscriber_id Subscriber ID to retrieve stats about
 * @return mixed Array containing subscriber stats, otherwise false
 */
function db_get_subscriber_stats( $subscriber_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $subscriber_id ) )
		return false;

	// Build query
	$sql = "SELECT IFNULL( n1.count, 0 ) AS delivered, IFNULL( c1.count, 0 ) AS clicked,  IFNULL( ROUND( ( IFNULL( c1.count, 0 ) / NULLIF( n1.count, 0 ) ) * 100 ), 0 ) AS ctr
              FROM ( SELECT COUNT( notification_id ) AS count
                       FROM " . RSM_NOTIFICATION_TABLE . "
                      WHERE status = 'S'
                        AND subscriber_id = '" . $subscriber_id . "' ) AS n1,
                   ( SELECT COUNT( DISTINCT n.notification_id ) AS count
                       FROM " . RSM_CLICK_TABLE . " AS c
                      INNER JOIN " . RSM_NOTIFICATION_TABLE . " AS n ON c.notification_id = n.notification_id
                      WHERE n.subscriber_id = '" . $subscriber_id . "' ) AS c1";

	// Get the data
	$results = $wpdb->get_row( $sql, ARRAY_A );

	return $results ? $results : false;
}

/**
 * Gets a subscriber's activity timeline.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int subscriber_id Subscriber ID to retrieve stats about
 * @return mixed Array of subscriber acitivity, otherwise false
 */
function db_get_subscriber_activity( $subscriber_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $subscriber_id ) )
		return false;

	// Build query
	$sql = "SELECT a.act_type, a.act_id, a.act_campaign, a.act_message, a.act_url, a.act_date
			  FROM ( SELECT n.type AS act_type, n.notification_id AS act_id, c.campaign_name AS act_campaign, n.message AS act_message, n.redirect_url AS act_url, n.created_date AS act_date
			           FROM " . RSM_NOTIFICATION_TABLE . " AS n
			          INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON n.campaign_id = c.campaign_id
				      WHERE n.status = 'S'
				            AND n.subscriber_id = '" . $subscriber_id . "'
					  UNION ALL
					 SELECT 'C' AS act_type, n.notification_id AS act_id, c.campaign_name AS act_campaign, '' AS act_message, '' AS act_url, k.click_date AS act_date
					   FROM " . RSM_CLICK_TABLE . " AS k
					  INNER JOIN " . RSM_NOTIFICATION_TABLE . " AS n ON k.notification_id = n.notification_id
			          INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON n.campaign_id = c.campaign_id
					  WHERE n.subscriber_id = '" . $subscriber_id . "' ) AS a
			  ORDER BY a.act_date ASC";

	// Get the data
	$results = $wpdb->get_results( $sql, ARRAY_A );

	return $results ? $results : false;
}

/*----------------------------------------------------------------------------*
 * Insert functions
 *----------------------------------------------------------------------------*/

/**
 * Inserts a list.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List values to be inserted
 * @return mixed ID of inserted record, otherwise false
 */
function db_insert_list( $data = array() ) {
    global $wpdb;

    // Check for list data
    if ( empty ( $data ) )
        return false;

    // Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // Insert our new list, excluding the optin_url
    $inserted = $wpdb->insert( RSM_LIST_TABLE,
        array(
            'app_name'         => $data['app-name'],
            'app_id'           => $data['app-id'],
            'app_secret'       => $data['app-secret'],
            'okay_url'         => $data['okay-url'],
            'cancel_url'       => $data['cancel-url'],
            'show_welcome'     => $data['opt-welcome'],
            'welcome_msg'      => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-text'] : '',
            'welcome_url'      => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-url']  : '',
            'redirect_type'    => 'O', //$data['redirect-type'],
            'integrate_ar'     => $data['opt-ar'],
            'created_date'     => $created_date,
            'created_date_gmt' => $created_date_gmt
        ),
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    // If new record inserted successfully, update it with a newly generated optin link
    if ( $inserted ) {
        $new_id  = $wpdb->insert_id;
        $updated = $wpdb->update( RSM_LIST_TABLE,
            array( 'optin_url' => rsm_generate_optin_link( $new_id ) ),
            array( 'list_id' => $new_id ),
            array( '%s' ),
            array( '%d' )
        );
    }

    // If both operations successful, return ID of inserted record
    return ( $inserted && ( $updated !== false ) ) ? $new_id : false;
}

/**
 * Inserts the welcome notification campaign/summary for a specified list immediately after the list is created. Since
 * the welcome notification can be enabled/disabled, we create the record here regardless for consistency of flow and
 * data. If disabled when created, the welcome campaign is set to inactive by default.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List values containing welcome notification details
 * @return mixed True if successful, otherwise false
 */
function db_insert_welcome( $data = array() ) {
    global $wpdb;

    // List ID is required
    $list_id = absint( $data['list-id'] );
    if ( empty( $list_id ) )
        return false;

    // 1.) Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // 2.) Insert campaign record
    $campaign_results = $wpdb->insert( RSM_CAMPAIGN_TABLE,
        array(
            'list_id'          => $list_id,
	        'segment_id'       => 0,
            'campaign_name'    => 'Welcome Campaign',
            'campaign_desc'    => 'System generated welcome campaign for list_id ' . $list_id,
            'type'             => 'W',
            'status'           => ( 'T' == $data['opt-welcome'] ? 'A' : 'I' ),  // Set to Inactive if initially disabled for this list
            'created_date'     => $created_date,
            'created_date_gmt' => $created_date_gmt
        ),
        array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    // If campaign created successfully, get campaign_id and continue
    if ( $campaign_results ) {
        $campaign_id = $wpdb->insert_id;

        // 3.) Insert summary record
        $summary_results = $wpdb->insert( RSM_SUMMARY_TABLE,
            array(
                'list_id'       => $list_id,
                'campaign_id'   => $campaign_id,
                'message'       => $data['welcome-text'],
                'redirect_url'  => $data['welcome-url'],
                'redirect_type' => 'O', //$data['redirect-type'],
                'delay'         => 0,
                'delay_offset'  => 0
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%d', '%d' )
        );
    }

    // Return false if either campaign or summary insert fail (0 || error), otherwise return true
    return ( false == $campaign_results || false == $summary_results ) ? false : true;
}

/**
 * Inserts a campaign.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Campaign values to be inserted
 * @return mixed ID of inserted record, otherwise false
 */
function db_insert_campaign( $data = array() ) {
    global $wpdb;

    // Check for campaign data
    if ( empty ( $data ) )
        return false;

    // Set defaults
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );
    $status           = 'A';    // all campaigns are Active by default

    // Insert the new campaign
    $inserted = $wpdb->insert( RSM_CAMPAIGN_TABLE,
        array(
            'list_id'          => $data['list-id'],
            'segment_id'       => $data['segment-id'],
            'campaign_name'    => $data['campaign-name'],
            'campaign_desc'    => $data['campaign-desc'],
            'type'             => $data['campaign-type'],
            'status'           => $status,
            'created_date'     => rsm_valid_date( $data['created-date'] ) ? $data['created-date'] : $created_date,
            'created_date_gmt' => rsm_valid_date( $data['created-date-gmt'] ) ? $data['created-date-gmt'] : $created_date_gmt
        ),
        array ( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
    );

    // If successful, return ID of inserted record
    return $inserted ? $wpdb->insert_id : false;
}

/**
 * Inserts a summary.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Summary values to be inserted
 * @return mixed ID of inserted record, otherwise false
 */
function db_insert_summary( $data = array() ) {
    global $wpdb;

    // Check for summary data
    if ( empty ( $data ) )
        return false;

    // Set defaults
    $delay             = 0;
    $delay_offset      = 0;
    $schedule_date     = '0000-00-00 00:00:00';
    $schedule_date_gmt = '0000-00-00 00:00:00';

    // Set values based on campaign-type
    $type = $data['campaign-type'];
    switch ( $type ) {
        case 'L':
            $schedule_date     = rsm_format_datetime( $data['schedule-date'] . ' ' . $data['schedule-time'] );
            $schedule_date_gmt = get_gmt_from_date( $schedule_date );
            break;
        case 'S':
            $delay = (int) $data['seq-delay'];
            $delay_offset = db_get_delay_offset( $data['campaign-id'], $delay );
            if ( false === $delay_offset ) return false;
            break;
    }

    // Insert our new summary record
    $inserted = $wpdb->insert( RSM_SUMMARY_TABLE,
        array(
            'list_id'           => $data['list-id'],
            'campaign_id'       => $data['campaign-id'],
            'message'           => $data['message-text'],
            'redirect_url'      => $data['redirect-url'],
            'redirect_type'     => 'O', //$data['redirect-type'],
            'delay'             => $delay,
            'delay_offset'      => $delay_offset,
            'schedule_date'     => $schedule_date,
            'schedule_date_gmt' => $schedule_date_gmt
        ),
        array( '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
    );

    // If successful, return ID of inserted record
    return $inserted ? $wpdb->insert_id : false;
}

/**
 * Inserts a new subscriber. If subscriber already exists, update their status to Active.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List_id and array of subscriber details
 * @return mixed ID of inserted/updated record, otherwise false
 */
function db_insert_subscriber( $data = array() ) {
    global $wpdb;

    // Check for subscriber data
    if ( empty ( $data ) )
        return false;

    // Sanitize values
    if ( isset( $data['created_date'] ) && rsm_valid_date( $data['created_date'] ) ) {
        $created_date     = rsm_format_datetime( $data['created_date'] );
        $created_date_gmt = get_gmt_from_date( $created_date );
    } else {
        $created_date     = rsm_get_datetime();
        $created_date_gmt = get_gmt_from_date( $created_date );
    }
    $status = isset( $data['status'] ) ? strtoupper( substr( $data['status'], 0, 1 ) ) : 'A';
    $gender = strtolower( $data['gender'] );

    // Insert subscriber if not already subscribed
    $sql = "INSERT INTO " . RSM_SUBSCRIBER_TABLE . " ( list_id, uid, full_name, first_name, last_name, email, link, gender, locale, timezone, status, created_date, created_date_gmt )
            VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %s )
            ON DUPLICATE KEY UPDATE status = 'A'";

    // Prepare query for execution
    $sql = $wpdb->prepare( $sql,
        $data['list_id'],
        $data['id'],
        $data['name'],
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['link'],
        $gender,
        $data['locale'],
        $data['timezone'],
        $status,
        $created_date,
        $created_date_gmt
    );

    // Execute our query
    $results = $wpdb->query( $sql );

    // Return false on error, otherwise return ID of inserted record (or fetch ID if duplicate)
    if ( false === $results ) {
        return false;
    } elseif ( 0 == $results ) {
        $sql = "SELECT subscriber_id FROM " . RSM_SUBSCRIBER_TABLE . " WHERE uid = '" . $data['id']. "'";
        return $wpdb->get_var( $sql );
    } else {
        return $wpdb->insert_id;
    }
}

/**
 * Inserts a new segment.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List values containing welcome notification details
 * @return mixed Segment_id if successful, otherwise false
 */
function db_insert_segment( $data = array() ) {
	global $wpdb;

	// List ID is required
	$list_id = absint( $data['list-id'] );
	if ( empty( $list_id ) )
		return false;

	// 1.) Insert segment master record
	$segment_results = $wpdb->insert( RSM_SEGMENT_TABLE,
		array(
			'segment_name' => $data['segment-name'],
			'list_id'      => $list_id,
			'match_type'   => $data['match-type']
		),
		array( '%s', '%d', '%s' )
	);

	// If segment created successfully, get segment_id and continue
	if ( $segment_results ) {
		$segment_id    = $wpdb->insert_id;
		$criteria      = array();
		$values        = array();
		$place_holders = array();

		// Remap segment data array
		for ( $i = 0; $i < count( $data['fields'] ); $i ++ ) {
			$criteria[] = array( $data['fields'][ $i ], $data['rules'][ $i ], $data['values'][ $i ] );
		}

		// 2.) Insert segment detail
		$sql = "INSERT INTO " . RSM_SEGMENT_DETAIL_TABLE . " (segment_id, field, rule, value) VALUES ";

		foreach ( $criteria as $entry ) {
			array_push( $values, $segment_id, $entry[0], $entry[1], $entry[2] );
			$place_holders[] = "( '%d', '%s', '%s', '%s' )";
		}

		$sql .= implode( ', ', $place_holders );
		$detail_results = $wpdb->query( $wpdb->prepare( $sql, $values ) );
	}

	// Return false if either segment or detail insert fail (0 || error), otherwise return the new segment_id
	return ( false == $segment_results || false == $detail_results ) ? false : $segment_id;
}

/**
 * Inserts a subscriber as part of a segment.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID segment is a part of
 * @param int $segment_id Segment ID to be updated
 * @param string $segment_name Segment name to be inserted into
 * @param string $email The subscriber email address to add to segment
 * @return bool True if successfully inserted, otherwise false
 */
function db_insert_subscriber_segment( $list_id = 0, $segment_id = 0, $segment_name = null, $email = null ) {
	global $wpdb;

	// List ID, segment ID/name and email required
	$list_id      = absint( $list_id );
	$segment_id   = absint( $segment_id );
	if ( empty( $list_id ) || ( empty( $segment_id ) && empty( $segment_name ) ) || empty ( $email ) )
		return false;

	// Get segment ID if only segment name supplied (also validates list ID)
	if ( empty( $segment_id ) ) {
		$sql = "SELECT segment_id FROM " . RSM_SEGMENT_TABLE . " WHERE list_id = %d AND segment_name = %s";
		$segment_id = $wpdb->get_var( $wpdb->prepare( $sql, $list_id, $segment_name ) );

		// If segment name not found, create it
		if ( empty( $segment_id ) ) {
			$inserted = $wpdb->insert( RSM_SEGMENT_TABLE,
				array(
					'segment_name' => $segment_name,
					'list_id'      => $list_id,
					'match_type'   => 'any'
				),
				array ( '%s', '%d', '%s' )
			);
			$segment_id = $inserted ? $wpdb->insert_id : false;
		}

	} else {
		// Validate List ID
		$sql = "SELECT segment_id FROM " . RSM_SEGMENT_TABLE . " WHERE list_id = %d AND segment_id = %d";
		$segment_id = $wpdb->get_var( $wpdb->prepare( $sql, $list_id, $segment_id ) );
	}

	// Validate segment ID
	if ( ! $segment_id ) return false;

	// Insert segment detail record if it doesn't already exist
	$sql = "INSERT INTO " . RSM_SEGMENT_DETAIL_TABLE . " ( segment_id, field, rule, value )
			SELECT %d, 'email', 'is_equal_to', %s
              FROM (SELECT 1) AS t
			 WHERE NOT EXISTS ( SELECT 1
                                  FROM " . RSM_SEGMENT_DETAIL_TABLE . "
							     WHERE segment_id = %d
				                   AND field = 'email'
						           AND rule = 'is_equal_to'
						           AND value = %s )";

	$inserted = $wpdb->query( $wpdb->prepare( $sql, $segment_id, $email, $segment_id, $email ) );

	// If successful, return ID of inserted record
	return ( false === $inserted ) ? false : $inserted;
}

/**
 * Inserts a new autoresponder record. If autoresponder already exists, we update the connected status.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of autoresponder details
 * @return mixed ID of inserted record, otherwise false
 */
function db_insert_autoresponder( $data = array() ) {
    global $wpdb;

    // Check for autoresponder data
    if ( empty ( $data ) )
        return false;

    // Insert subscriber if not already subscribed
    $sql = "INSERT INTO " . RSM_AUTORESPONDER_TABLE . " ( ar_name, api_key, options, connected )
            VALUES ( %s, %s, %s, %s )
            ON DUPLICATE KEY UPDATE api_key = %s, options = %s, connected = %s";

    // Prepare query for execution
    $sql = $wpdb->prepare( $sql,
        $data['ar_name'],
        $data['api_key'],
        $data['options'],
        'T',
        $data['api_key'],
        $data['options'],
        'T'
    );

    // Execute our query
    $results = $wpdb->query( $sql );

    // Return false on error, otherwise return ID of inserted record (or fetch ID if duplicate)
    if ( false === $results ) {
        return false;
    } elseif ( 0 == $results ) {
        $sql = "SELECT ar_id FROM " . RSM_AUTORESPONDER_TABLE . " WHERE ar_name = '" . $data['ar_name']. "'";
        return $wpdb->get_var( $sql );
    } else {
        return $wpdb->insert_id;
    }
}

/**
 * Inserts one or more autoresponder lists.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param string $ar_name Name of 3rd-party autoresponder
 * @param array $lists Array of list data (list_name and list_value)
 * @return bool True if successful, otherwise false
 */
function db_insert_ar_lists( $ar_name, $lists = array() ) {
    global $wpdb;

    // Check if any lists exist
    if ( empty ( $lists ) )
        return false;

    // Delete any previously saved lists for this autoresponder
    $deleted = $wpdb->delete( RSM_AUTORESPONDER_LIST_TABLE, array( 'ar_name' => $ar_name ), array( '%s' ) );
    if ( false === $deleted )
        return false;

    $values        = array();
    $place_holders = array();

    // Build insert query
    $sql = "INSERT INTO " . RSM_AUTORESPONDER_LIST_TABLE . " ( ar_name, ar_list_name, ar_list_value ) VALUES ";
    foreach ( $lists as $list_value => $list_name ) {
        array_push( $values, $ar_name, $list_name, $list_value );
        $place_holders[] = "( '%s', '%s', '%s' )";
    }
    $sql .= implode( ', ', $place_holders );
    $sql = $wpdb->prepare( "$sql ", $values );

    // Execute our query
    $results = $wpdb->query( $sql );

    // Return results
    return $results ? $results : false;
}

/**
 * Inserts one or more integrated autoresponder lists.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $ar_data Array of ar data to insert
 * @return bool True if successful, otherwise false
 */
function db_insert_integrated_ar( $ar_data = array() ) {
    global $wpdb;

    // Check for ar lists to integrate
    if ( empty ( $ar_data ) )
        return false;

    $values        = array();
    $place_holders = array();

    // Build insert query
    $sql = "INSERT INTO " . RSM_INTEGRATED_AR_TABLE . " ( list_id, ar_name, ar_list_value ) VALUES ";
    foreach ( $ar_data as $ar ) {
        array_push( $values, $ar['list_id'], $ar['ar_name'], $ar['ar_value'] );
        $place_holders[] = "( %d, '%s', '%s' )";
    }
    $sql .= implode( ', ', $place_holders );
    $sql = $wpdb->prepare( "$sql ", $values );

    // Execute our query
    $results = $wpdb->query( $sql );

    // Return results
    return $results ? $results : false;
}

/**
 * Queues a welcome notification for a specified subscriber. This will happen after a new user opts-in.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID of subscriber
 * @param int $list_id Subscriber ID to receive welcome msg
 * @return mixed Int containing number of queued records (which should be 1), otherwise false.
 *               Note that 0 means there are no welcome notifications, so check results using "==="
 */
function db_insert_welcome_notification( $list_id = 0, $sub_id = 0 ) {
    global $wpdb;

    // List ID and Subscriber ID are required
    $list_id = absint( $list_id );
    $sub_id  = absint( $sub_id );
    if ( empty( $list_id ) || empty( $sub_id ) )
        return false;

    // Check if there's a welcome message for this list
    $sql = "SELECT show_welcome FROM " . RSM_LIST_TABLE . " WHERE list_id = " . $list_id;
    if ( 'T' != $wpdb->get_var( $sql ) )
        return 0;

    // Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // Build query for inserting welcome notification
    $sql = "INSERT IGNORE INTO " . RSM_NOTIFICATION_TABLE . " ( list_id, campaign_id, summary_id, subscriber_id,
                   message, redirect_url, redirect_type,
                   type, status, created_date, created_date_gmt, send_date, send_date_gmt, sent_date, sent_date_gmt )
            SELECT s.list_id, s.campaign_id, s.summary_id, sub.subscriber_id,
                   REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( s.message, '{{first_name}}', LEFT( sub.first_name, 15 ) ), '{{last_name}}', LEFT( sub.last_name, 20 ) ), '{{full_name}}', LEFT( sub.full_name, 35 ) ), '{{day_of_week}}', DAYNAME( '" . $created_date . "' ) ), '{{date}}', DATE( '". $created_date . "' ) ), s.redirect_url, s.redirect_type,
                   %s, %s, %s, %s, %s, %s, %s, %s
              FROM " . RSM_SUMMARY_TABLE . " AS s
             INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON s.campaign_id = c.campaign_id AND c.type = 'W'
             INNER JOIN " . RSM_SUBSCRIBER_TABLE . " AS sub ON s.list_id = sub.list_id AND sub.status = 'A' AND sub.subscriber_id = %d
             WHERE c.list_id = %d";

    // Prepare query for execution
    $sql = $wpdb->prepare( $sql,
        'W',    // welcome notification
        'N',    // status = not sent
        $created_date,
        $created_date_gmt,
        $created_date,      // send_date
        $created_date_gmt,  // send_date_gmt
        '0000-00-00 00:00:00',
        '0000-00-00 00:00:00',
        $sub_id,
        $list_id
    );

    // Execute our query
    $results = $wpdb->query( $sql );

    // Return results
    return ( false === $results ) ? false : $results;
}

/**
 * Inserts a batch (instant/scheduled) notification campaign.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of campaign values to be inserted
 * @return mixed ID of inserted campaign record if successful, otherwise false
 */
function db_insert_batch_campaign( $data = array() ) {
    global $wpdb;

    // Check for batch data
    if ( empty ( $data ) )
        return false;

	// Set any supplied values
	$campaign_id      = isset( $data['campaign-id'] )      ? absint( $data['campaign-id'] ) : null;
	$summary_id       = isset( $data['summary-id'] )       ? absint( $data['summary-id'] )  : null;
	$created_date     = isset( $data['created-date'] )     ? $data['created-date']          : rsm_get_datetime();
	$created_date_gmt = isset( $data['created-date-gmt'] ) ? $data['created-date-gmt']      : get_gmt_from_date( $created_date );

    // 1.) Create dates
    if ( empty( $data['schedule-date'] ) && empty( $data['schedule-time'] ) ) {
        $send_date        = $created_date;
        $send_date_gmt    = $created_date_gmt;
    } else {
        $send_date        = rsm_format_datetime( $data['schedule-date'] . ' ' . $data['schedule-time'] );
        $send_date_gmt    = get_gmt_from_date( $send_date );
    }

    // 2.) Create new campaign
    $data['created-date']     = $created_date;
    $data['created-date-gmt'] = $created_date_gmt;
    if ( ! $campaign_id ) {
	    $campaign_results = db_insert_campaign( $data );
	    $campaign_id = $wpdb->insert_id;
    }

    // If campaign created, continue
    if ( $campaign_id ) {
	    $campaign_results = true;

        // 3.) Create summary record
        $data['campaign-id'] = $campaign_id;
        if (! $summary_id ) {
	        $summary_results = db_insert_summary( $data );
	        $summary_id = $wpdb->insert_id;
        }

        // If summary record created, continue
        if ( $summary_id ) {
	        $summary_results = true;

            // 4.) Get segment subscriber IDs
            if ( isset( $data['segment-id'] ) && 0 < (int) $data['segment-id'] ) {
                $id_list = db_get_segment_data( $data );

	            if ( $id_list ) {
		            $prefix = $where_in = '';
		            foreach ( $id_list as $uid ) {
			            $where_in .= $prefix . $uid['subscriber_id'];
			            $prefix = ', ';
		            }
	            } else {
		            $where_in = 0;
	            }
            }

            // 5.) Build query for inserting all notifications
            $sql = "INSERT INTO " . RSM_NOTIFICATION_TABLE . " ( list_id, campaign_id, summary_id, subscriber_id, message, redirect_url, redirect_type, type, status, created_date, created_date_gmt, send_date, send_date_gmt, sent_date, sent_date_gmt )
                    SELECT list_id, %d, %d, subscriber_id,
                           REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( %s, '{{first_name}}', LEFT( first_name, 15 ) ), '{{last_name}}', LEFT( last_name, 20 ) ), '{{full_name}}', LEFT( full_name, 35 ) ), '{{day_of_week}}', DAYNAME( '" . $send_date . "' ) ), '{{date}}', DATE( '". $send_date . "' ) ),
                           %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
                      FROM " . RSM_SUBSCRIBER_TABLE . "
                     WHERE list_id = %d AND status = 'A'";

            // Filter by subscriber segment
            $sql .= isset( $where_in ) ? " AND subscriber_id IN ( " . $where_in . " )" : '';

            // Prepare query for execution
            $sql = $wpdb->prepare( $sql,
                $campaign_id,
                $summary_id,
                $data['message-text'],
                $data['redirect-url'],
                'O', //$data['redirect-type'],
                $data['campaign-type'],
                'N',    // status = not sent
                $created_date,
                $created_date_gmt,
                $send_date,
                $send_date_gmt,
                '0000-00-00 00:00:00',
                '0000-00-00 00:00:00',
                $data['list-id']
            );

            // Execute our query
            $notification_results = $wpdb->query( $sql );
        }
    }

    // If successful, return ID of inserted summary record;
    // Note that campaign and summary results should return false on 0 results or error,
    // while notification results should return false only on error. This is because
    // a campaign and summary record should always be created, whereas a notification
    // record is only created if subscribers exist.
    return ( false == $campaign_results || false == $summary_results || false === $notification_results ) ? false : $campaign_id;
}

/**
 * Inserts a follow-up sequence record. If associated list is activated,
 * notifications will be queued for existing subscribers.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of campaign values to be inserted
 * @return mixed ID of campaign record if successful, otherwise false
 */
function db_insert_sequence_campaign( $data = array() ) {
    global $wpdb;

	// Check for data
	if ( empty ( $data ) )
		return false;

	// Set any supplied values
	$campaign_id = isset( $data['campaign-id'] ) ? absint( $data['campaign-id'] ) : null;

    // 1.) Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // 2.) Check campaign ID; if doesn't exist, create new campaign
    if ( empty( $campaign_id ) ) {
        // Create new campaign record
        $data['created-date'] = $created_date;
        $data['created-date-gmt'] = $created_date_gmt;
        $campaign_results = db_insert_campaign( $data );

        // If campaign created, continue
        if ( $campaign_results ) {
            $campaign_id = $wpdb->insert_id;
        } else {
            return false;
        }
    } else {
        // Otherwise update the campaign
	    $updated = $wpdb->update( RSM_CAMPAIGN_TABLE,
		    array(
			    'campaign_name' => $data['campaign-name'],
			    'campaign_desc' => $data['campaign-desc']
		    ),
		    array( 'campaign_id' => $campaign_id ),
		    array( '%s', '%s' ),
		    array( '%d' )
	    );
        $campaign_results = (false !== $updated );
    }

    // 3.) Create summary record
    $data['campaign-id'] = $campaign_id;
    $summary_results = db_insert_summary( $data );

    // If summary record created, continue and queue notifications
    if ( $summary_results ) {
        $summary_id = $wpdb->insert_id;

        // 4.) Get segment subscriber IDs
	    if ( isset( $data['segment-id'] ) && 0 < (int) $data['segment-id'] ) {
            $id_list = db_get_segment_data( $data );

            if ( $id_list ) {
	            $prefix = $where_in = '';
	            foreach ( $id_list as $uid ) {
		            $where_in .= $prefix . $uid['subscriber_id'];
		            $prefix = ', ';
	            }
            } else {
	            $where_in = 0;
            }
        }

        // 5.) Queue sequence for existing subscribers;
        // Must handle (1) those who already completed entire sequence -> queue: now + delay
        //             (2) those who are still somewhere in sequence   -> queue: max(send_date) + delay
        //
        $sql = "INSERT INTO " . RSM_NOTIFICATION_TABLE . " ( list_id, campaign_id, summary_id, subscriber_id, message, redirect_url, redirect_type,
                       type, status, created_date, created_date_gmt,
                       send_date, send_date_gmt, sent_date, sent_date_gmt )
                SELECT s.list_id, s.campaign_id, s.summary_id, sub.subscriber_id, REPLACE( REPLACE( REPLACE( s.message, '{{first_name}}', LEFT( sub.first_name, 15 ) ), '{{last_name}}', LEFT( sub.last_name, 20 ) ), '{{full_name}}', LEFT( sub.full_name, 35 ) ), s.redirect_url, s.redirect_type,
                       %s, %s, %s, %s,
                       IF( IFNULL( n.send_date, '0000-00-00 00:00:00' ) <= %s, DATE_ADD( %s, INTERVAL s.delay DAY ), DATE_ADD( n.send_date, INTERVAL s.delay DAY ) ),
                       IF( IFNULL( n.send_date_gmt, '0000-00-00 00:00:00' ) <= %s, DATE_ADD( %s, INTERVAL s.delay DAY ), DATE_ADD( n.send_date_gmt, INTERVAL s.delay DAY ) ),
                       %s, %s
                  FROM " . RSM_SUBSCRIBER_TABLE . " AS sub
                 INNER JOIN " . RSM_SUMMARY_TABLE . " AS s ON sub.list_id = s.list_id
                  LEFT JOIN ( SELECT n2.subscriber_id, MAX( n2.send_date ) AS send_date, MAX( n2.send_date_gmt ) AS send_date_gmt
                                FROM " . RSM_NOTIFICATION_TABLE . " AS n2
                               WHERE n2.type = 'S'
                                 AND n2.campaign_id = %d
                               GROUP BY n2.subscriber_id ) AS n ON sub.subscriber_id = n.subscriber_id
                 WHERE sub.status = 'A'
                   AND s.summary_id = %d";
                // Must constrain LEFT JOIN sub-query by campaign, otherwise max( send_date ) will consider send_dates for ALL campaigns this subscriber is a part of

        // Filter by subscriber segment
        $sql .= isset( $where_in ) ? " AND sub.subscriber_id IN ( " . $where_in . " )" : '';

        // Prepare query for execution
        $sql = $wpdb->prepare( $sql,
                'S',    // sequence notification
                'N',    // status = not sent
                $created_date,
                $created_date_gmt,
                $created_date,      // this date is used in IF comparison
                $created_date,      // IF true (finished entire sequence), queue: now + delay; IF false (somewhere in sequence), queue: max(send_date) + delay
                $created_date_gmt,  // this date is used in IF comparison
                $created_date_gmt,  // IF true (finished entire sequence), queue: now + delay; IF false (somewhere in sequence), queue: max(send_date_gmt) + delay
                '0000-00-00 00:00:00',
                '0000-00-00 00:00:00',
                $campaign_id,
                $summary_id
            );

        // Execute our query
        $notification_results = $wpdb->query( $sql );

	    if ( $notification_results !== false ) {
		    $sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
                       SET n.message = REPLACE( REPLACE( n.message, '{{day_of_week}}', DAYNAME( n.send_date ) ), '{{date}}', DATE( n.send_date ) )
                     WHERE n.status = 'N'
                       AND n.summary_id = " . $summary_id;

		    $notification_results = $wpdb->query( $sql );
	    }
    }

    // If successful, return ID of inserted summary record;
    // Note that sequence results should return false on 0 results or error,
    // while notification results should return false only on error.
    return ( false == $campaign_results || false == $summary_results || false === $notification_results ) ? false : $campaign_id;
}

/**
 * Queue all scheduled & sequence notifications for a specified subscriber. This will happen after a new user opts-in.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID of subscriber
 * @param array $segments Array of segments the subscriber belongs to
 * @param int $list_id Subscriber ID to receive queued notifications
 * @return mixed True or false depending on success.
 */
function db_insert_optin_notifications( $list_id = 0, $segments = array(), $sub_id = 0 ) {
	global $wpdb;

	// List ID and Subscriber ID are required
	$list_id = absint( $list_id );
	$sub_id  = absint( $sub_id );
	if ( empty( $list_id ) || empty( $sub_id ) || empty( $segments ) )
		return false;

	// 1.) Create dates
	$created_date     = rsm_get_datetime();
	$created_date_gmt = get_gmt_from_date( $created_date );

	// 2.) Build segmenting SQL clause
	$prefix = $where_in = '';
	foreach ( $segments as $segment ) {
		$where_in .= $prefix . $segment['segment_id'];
		$prefix = ', ';
	}

	// 3.) Build our query for Scheduled notifications; INSERT IGNORE due to index uq_camp_summary_sub_id
	$sql = "INSERT IGNORE INTO " . RSM_NOTIFICATION_TABLE . " ( list_id, campaign_id, summary_id, subscriber_id,
	               message, redirect_url, redirect_type,
                   type, status, created_date, created_date_gmt, send_date, send_date_gmt, sent_date, sent_date_gmt )
            SELECT s.list_id, s.campaign_id, s.summary_id, sub.subscriber_id,
                   REPLACE( REPLACE( REPLACE( REPLACE( REPLACE( s.message, '{{first_name}}', LEFT( sub.first_name, 15 ) ), '{{last_name}}', LEFT( sub.last_name, 20 ) ), '{{full_name}}', LEFT( sub.full_name, 35 ) ), '{{day_of_week}}', DAYNAME( s.schedule_date ) ), '{{date}}', DATE( s.schedule_date ) ), s.redirect_url, s.redirect_type,
                   'L', 'N', %s, %s, s.schedule_date, s.schedule_date_gmt, %s, %s
              FROM " . RSM_SUMMARY_TABLE . " AS s
             INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON s.campaign_id = c.campaign_id AND c.type = 'L' AND c.segment_id IN ( " . $where_in . " )
             INNER JOIN " . RSM_SUBSCRIBER_TABLE . " AS sub ON s.list_id = sub.list_id AND sub.status = 'A' AND sub.subscriber_id = %d
             WHERE c.list_id = %d";

	// Prepare query for execution
	$sql = $wpdb->prepare( $sql,
		$created_date,
		$created_date_gmt,
		'0000-00-00 00:00:00',
		'0000-00-00 00:00:00',
		$sub_id,
		$list_id
	);

	// Execute our query
	$scheduled_results = $wpdb->query( $sql );

	if ( false !== $scheduled_results ) {

		// 4.) Build our query for Sequence notifications; to calculate each send_date -> queue: now + delay_offset
		$sql = "INSERT IGNORE INTO " . RSM_NOTIFICATION_TABLE . " ( list_id, campaign_id, summary_id, subscriber_id, message, redirect_url, redirect_type,
                   type, status, created_date, created_date_gmt, send_date, send_date_gmt, sent_date, sent_date_gmt )
            SELECT s.list_id, s.campaign_id, s.summary_id, sub.subscriber_id, REPLACE( REPLACE( REPLACE( s.message, '{{first_name}}', LEFT( sub.first_name, 15 ) ), '{{last_name}}', LEFT( sub.last_name, 20 ) ), '{{full_name}}', LEFT( sub.full_name, 35 ) ), s.redirect_url, s.redirect_type,
                   'S', 'N', %s, %s, DATE_ADD( %s, INTERVAL s.delay_offset DAY ), DATE_ADD( %s, INTERVAL s.delay_offset DAY ), %s, %s
              FROM " . RSM_SUMMARY_TABLE . " AS s
             INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON s.campaign_id = c.campaign_id AND c.type = 'S' AND c.segment_id IN ( " . $where_in . " )
             INNER JOIN " . RSM_SUBSCRIBER_TABLE . " AS sub ON s.list_id = sub.list_id AND sub.status = 'A' AND sub.subscriber_id = %d
             WHERE c.list_id = %d";

		// Prepare query for execution
		$sql = $wpdb->prepare( $sql,
			$created_date,
			$created_date_gmt,
			$created_date,      // this date will have the delay_offset interval added
			$created_date_gmt,  // this date will have the delay_offset interval added
			'0000-00-00 00:00:00',
			'0000-00-00 00:00:00',
			$sub_id,
			$list_id
		);

		// Execute our query
		$sequence_results = $wpdb->query( $sql );

		if ( $sequence_results !== false ) {
			$sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
                       SET n.message = REPLACE( REPLACE( n.message, '{{day_of_week}}', DAYNAME( n.send_date ) ), '{{date}}', DATE( n.send_date ) )
                     WHERE n.status = 'N'
                       AND n.list_id = " . $list_id . "
                       AND n.subscriber_id = " . $sub_id;

			$sequence_results = $wpdb->query( $sql );
		}
	}

	// Return results
	return ( false === $scheduled_results || false === $sequence_results ) ? false : true;
}

/**
 * Inserts a new log record.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of logging details
 * @return mixed ID of inserted record, otherwise false
 */
function db_insert_log( $data = array() ) {
    global $wpdb;

    // Check for log data
    if ( empty ( $data ) )
        return false;

    // Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // Create whitelist and sanitize
    $allowed_type = array( 'event', 'error' );
    $type         = isset( $data['type'] ) && in_array( $data['type'], $allowed_type ) ? $data['type'] : 'event';
    switch ( $type ) {
        case 'event':
            $type = 'V';
            break;
        case 'error':
            $type = 'E';
            break;
    }

    // Inserts a record into the logging table
    $inserted = $wpdb->insert( RSM_LOG_TABLE,
         array(
            'type'             => $type,
            'description'      => $data['description'],
            'meta'             => $data['meta'],
            'backtrace'        => $data['backtrace'],
            'created_date'     => $created_date,
            'created_date_gmt' => $created_date_gmt
          ),
         array ( '%s', '%s', '%s', '%s', '%s', '%s' )
     );

    // If successful, return ID of inserted record
    return $inserted ? $wpdb->insert_id : false;
}

/**
 * Inserts a new entry in the click stats table.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $notification_id ID of click details
 * @return mixed ID of subscriber, otherwise false
 */
function db_insert_click( $notification_id = 0 ) {
    global $wpdb;

    // Notification ID is required
    $notification_id = absint( $notification_id );
    if ( empty( $notification_id ) )
        return false;

	// Get the notification's subscriber_id
	$sql = "SELECT subscriber_id
              FROM " . RSM_NOTIFICATION_TABLE . "
             WHERE notification_id = ". $notification_id . "
             LIMIT 1";

	// Get the subscriber ID
	$subscriber_id = $wpdb->get_var( $sql );
	if ( empty( $subscriber_id ) )
		return false;

    // Create dates
    $created_date     = rsm_get_datetime();
    $created_date_gmt = get_gmt_from_date( $created_date );

    // Inserts a record into the click stats table
    $inserted = $wpdb->insert( RSM_CLICK_TABLE,
         array(
	         'subscriber_id'   => $subscriber_id,
             'notification_id' => $notification_id,
             'click_date'      => $created_date,
             'click_date_gmt'  => $created_date_gmt
          ),
         array ( '%d', '%d', '%s', '%s' )
     );

    // If successful, return Subscriber ID
    return $inserted ? $subscriber_id : false;
}


/*----------------------------------------------------------------------------*
 * Update functions
 *----------------------------------------------------------------------------*/

/**
 * Updates a list.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List values to be updated
 * @return bool True if successfully updated, otherwise false
 */
function db_update_list( $data = array() ) {
    global $wpdb;

    // Check for list data
    if ( empty ( $data ) )
        return false;

    $updated = $wpdb->update( RSM_LIST_TABLE,
        array(
            'app_name'      => $data['app-name'],
            'app_id'        => $data['app-id'],
            'app_secret'    => $data['app-secret'],
            'okay_url'      => $data['okay-url'],
            'cancel_url'    => $data['cancel-url'],
            'show_welcome'  => $data['opt-welcome'],
            'welcome_msg'   => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-text'] : '',
            'welcome_url'   => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-url']  : '',
            'redirect_type' => 'O', //$data['redirect-type'],
            'integrate_ar'  => $data['opt-ar'],
        ),
        array( 'list_id' => $data['list-id'] ),
        array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
        array( '%d' )
    );

    return ( false === $updated ) ? false : true;
}

/**
 * Updates a welcome notification.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data List values to be updated
 * @return bool True if successfully updated, otherwise false
 */
function db_update_welcome( $data = array() ) {
    global $wpdb;

    // List ID is required
    $list_id = absint( $data['list-id'] );
    if ( empty( $list_id ) )
        return false;

    // 1.) Update campaign status
    $campaign_results = $wpdb->update( RSM_CAMPAIGN_TABLE,
        array(
            'status' => ( 'T' == $data['opt-welcome'] ? 'A' : 'I' )
        ),
        array(
            'list_id' => $list_id,
            'type'    => 'W'
        ),
        array( '%s' ),
        array( '%d', '%s' )
    );

    // If campaign update didn't fail, continue
    if ( false !== $campaign_results ) {

        // Get the welcome notification's campaign_id
        $sql = "SELECT campaign_id
                  FROM " . RSM_CAMPAIGN_TABLE . "
                 WHERE list_id = ". $list_id . "
                   AND type = 'W'";

        // Get the data
        $campaign_id = $wpdb->get_var( $sql );
        if ( empty( $campaign_id ) )
            return false;

        // 2.) Update summary values
        $summary_results = $wpdb->update( RSM_SUMMARY_TABLE,
            array(
                'message'       => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-text'] : '',
                'redirect_url'  => ( 'T' == $data['opt-welcome'] ) ? $data['welcome-url']  : '',
                'redirect_type' => 'O' //$data['redirect-type']
            ),
            array( 'campaign_id' => $campaign_id ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );
    }

    return ( false === $campaign_results || false === $summary_results ) ? false : true;
}
/**
 * Updates a batch (instant/scheduled) campaign summary and any queued notifications.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of summary values to use in updating
 * @return bool True if successfully updated, otherwise false
 */
function db_update_batch_summary( $data = array() ) {
    global $wpdb;

	// Campaign ID is required
	$campaign_id = absint( $data['campaign-id'] );
    if ( empty( $campaign_id ) )
        return false;

    // If Scheduled, set date and time
    if ( 'L' == $data['campaign-type'] ) {
        $schedule_date     = rsm_format_datetime( $data['schedule-date'] . ' ' . $data['schedule-time'] );
        $schedule_date_gmt = get_gmt_from_date( $schedule_date );
    } else {
        $schedule_date     = '0000-00-00 00:00:00';
        $schedule_date_gmt = '0000-00-00 00:00:00';
    }

	// 1.) Update campaign details
	$updated = $wpdb->update( RSM_CAMPAIGN_TABLE,
		array(
			'list_id'       => $data['list-id'],
			'segment_id'    => $data['segment-id'],
			'campaign_name' => $data['campaign-name'],
			'campaign_desc' => $data['campaign-desc']
		),
		array( 'campaign_id' => $campaign_id ),
		array( '%d', '%d', '%s', '%s' ),
		array( '%d' )
	);

    // 2.) If success, update summary details
	if ( $updated !== false ) {
		$updated = $wpdb->update( RSM_SUMMARY_TABLE,
			array(
				'list_id'           => $data['list-id'],
				'message'           => $data['message-text'],
				'redirect_url'      => $data['redirect-url'],
				'redirect_type'     => 'O', //$data['redirect-type'],
				'schedule_date'     => $schedule_date,
				'schedule_date_gmt' => $schedule_date_gmt
			),
			array( 'campaign_id' => $campaign_id ),
			array( '%d', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		// 3.) Afer success, delete/reinsert notifications (vs UPDATE, which cannot be done due to possible change in targeting/segmenting)
		if ( $updated !== false ) {
			$sql = "SELECT s.summary_id, c.created_date, c.created_date_gmt
					  FROM " . RSM_CAMPAIGN_TABLE . " AS c
					 INNER JOIN " . RSM_SUMMARY_TABLE . " AS s ON c.campaign_id = s.campaign_id
					 WHERE s.campaign_id = " . $campaign_id;
			$results = $wpdb->get_row( $sql, ARRAY_A );

			if ( $results ) {
				$deleted = $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'summary_id' => $results['summary_id'] ), array( '%d' ) );

				$data['summary-id']      = $results['summary_id'];
				$data['create-date']     = $results['create_date'];
				$data['create-date-gmt'] = $results['create_date_gmt'];
				$updated = db_insert_batch_campaign( $data );

			} else {
				$updated = false;
			}
		}
	}

    return ( false === $updated ) ? false : true;
}

/**
 * Updates a sequence campaign summary and any queued sequence notifications.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $data Array of summary values to use in updating
 * @return bool True if successfully updated, otherwise false
 */
function db_update_sequence_summary( $data = array() ) {
    global $wpdb;

    // Campaign and Summary ID are required
	$campaign_id = absint( $data['campaign-id'] );
    $summary_id  = absint( $data['summary-id'] );
    if ( empty( $campaign_id ) || empty( $summary_id ) )
        return false;

    // Get previous delay value
    $prev_delay = db_get_sequence_delay( $summary_id );

	// 1.) Update campaign details
	$updated = $wpdb->update( RSM_CAMPAIGN_TABLE,
		array(
			'campaign_name' => $data['campaign-name'],
			'campaign_desc' => $data['campaign-desc']
		),
		array( 'campaign_id' => $campaign_id ),
		array( '%s', '%s' ),
		array( '%d' )
	);

    // 2.) If successful, update Summary table with new values
	if ( false !== $updated ) {
		$updated = $wpdb->update( RSM_SUMMARY_TABLE,
			array(
				'message'       => $data['message-text'],
				'redirect_url'  => $data['redirect-url'],
				'redirect_type' => 'O', //$data['redirect-type'],
				'delay'         => $data['seq-delay']
			),
			array( 'summary_id' => $summary_id ),
			array( '%s', '%s', '%s', '%d' ),
			array( '%d' )
		);

		// 3.) If any records successfully updated, update queued notifications
		if ( false !== $updated ) {
			$updated = db_update_sequence_notifications( $summary_id );

			// 4.) If update didn't fail, update sequence delays
			if ( false !== $updated ) {
				$updated = db_update_sequence_delays( $campaign_id, $summary_id, $prev_delay );
			}
		}
	}

    return ( false === $updated ) ? false : true;
}

/**
 * Updates a subscriber's status to active or inactive.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $sub_id Subscriber ID to be updated
 * @param bool (optional) $set_active True to activate, false to set inactive
 * @param int (optional) $uid Subscriber UID to be updated
 * @return bool True if successfully updated, otherwise false
 */
function db_update_subscriber_status( $sub_id = 0, $set_active = true, $uid = null ) {
    global $wpdb;

    // Subscriber ID or UID required
    $sub_id = absint( $sub_id );
    if ( empty( $sub_id ) && empty( $uid ) )
         return false;

    $status  = $set_active ? 'A' : 'I';
    if ( $sub_id ) {
        $updated = $wpdb->update( RSM_SUBSCRIBER_TABLE, array( 'status' => $status ), array( 'subscriber_id' => $sub_id ), array( '%s'  ), array( '%d' ) );
    } else {
        $updated = $wpdb->update( RSM_SUBSCRIBER_TABLE, array( 'status' => $status ), array( 'uid' => $uid ), array( '%s'  ), array( '%s' ) );
    }

    return ( false === $updated ) ? false : true;
}

/**
 * Auto updates a subscriber's notifications after changing segments due to a click event.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID to be updated
 * @param int $subscriber_id Subscriber ID to be updated
 * @return bool True if successfully updated, otherwise false
 */
function db_update_subscriber_click_segment( $list_id = 0, $subscriber_id = 0 ) {
	global $wpdb;

	// List and Subscriber ID required
	$list_id       = absint( $list_id );
	$subscriber_id = absint( $subscriber_id );
	if ( empty( $list_id ) && empty( $subscriber_id ) )
		return false;

	// 1.) Check whether subscriber has any previous clicks; if so, it's not necessary to run this routine (and we save some overhead)
	$clicks = absint( db_get_clicks_by_subscriber( $subscriber_id ) );
	if ( $clicks > 1 )
		return true;

	// 2.) Create dates
	$created_date     = rsm_get_datetime();
	$created_date_gmt = get_gmt_from_date( $created_date );

	// 3.) Get all 'clicked' qualifying segments
	$sql = "SELECT s.segment_id, d.rule
              FROM " . RSM_SEGMENT_TABLE . " AS s
             INNER JOIN " . RSM_SEGMENT_DETAIL_TABLE . " AS d ON s.segment_id = d.segment_id
             WHERE s.list_id = " . $list_id . "
               AND d.field = 'clicked'
               AND ( d.rule = 'never'
	                OR d.rule = 'any_campaign'
	                OR ( d.rule = 'before' AND date( '" . $created_date . "' ) < date ( d.value ) )
	                OR ( d.rule = 'after' AND date( '" . $created_date . "' ) > date ( d.value ) )
		           )";

	$results = $wpdb->get_results( $sql, ARRAY_A );

	// Two-step process: (1) delete previously qualifying then (2) add new qualifying
	if ( $results ) {
		$seg_clicked_never = array();
		$seg_clicked       = array();

		// 4.) Separate results by segment rule ('never' vs other)
		foreach ( $results as $segment ) {
			if ( 'never' == $segment['rule'] ) {
				$seg_clicked_never[] = array( 'segment_id' => $segment['segment_id'] );
			} else {
				$seg_clicked[] = array( 'segment_id' => $segment['segment_id'] );
			}
		}

		// 5.) Delete the unsent, 'never clicked' notifications
		if ( count( $seg_clicked_never ) > 0 ) {
			$prefix = $segment_in = '';
			foreach ( $seg_clicked_never as $seg ) {
				$segment_in .= $prefix . $seg['segment_id'];
				$prefix = ',';
			}
			$sql = "DELETE n
					  FROM " . RSM_NOTIFICATION_TABLE . " AS n
					 INNER JOIN " . RSM_CAMPAIGN_TABLE . " AS c ON n.campaign_id = c.campaign_id
					 WHERE c.list_id = " . $list_id . "
					   AND c.segment_id IN (" . $segment_in . ")
					   AND n.subscriber_id = " . $subscriber_id . "
					   AND n.status = 'N'";

			$results = $wpdb->query( $sql );
		}

		// 6.) Queue any scheduled & sequence notifications
		if ( count( $seg_clicked ) > 0 ) {
			$results = db_insert_optin_notifications( $list_id, $seg_clicked, $subscriber_id );
		}
	}

	return ( false === $results ) ? false : true;
}

/**
 * Updates the status of campaigns.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $ids Array of notification_ids to update
 * @param string $status Status to set applicable campaigns
 * @return bool True if successfully updated, otherwise false
 */
function db_update_campaign_status( $ids = array(), $status ) {
    global $wpdb;

    // Whitelist our status options (Aactive, Inactive, Finished)
    $whitelist = array( 'A', 'I', 'F' );

    // Make sure the status is valid
    if ( ! in_array( $status, $whitelist ) )
        return false;

    // Check for IDs
    if ( empty( $ids ) )
        return false;

    $sql = "UPDATE " . RSM_CAMPAIGN_TABLE . " AS c
               SET c.status = '" . $status . "'
             WHERE c.campaign_id IN ( " . implode( ',', $ids ) . " )";

    // Execute our query
    $updated = $wpdb->query( $sql );

    return ( false === $updated ) ? false : true;
}

/**
 * Updates the saved integrated autoresponder lists.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID of ar data to update
 * @param array $ar_data Array of ar data to update
 * @return bool True if successful (even when no ar lists are supplied), otherwise false
 */
function db_update_integrated_ar( $list_id = 0, $ar_data = array() ) {
    global $wpdb;

    // List ID is required
    $list_id = absint( $list_id );
    if ( empty( $list_id ) )
        return false;

    // Delete any previously saved ar data
    $deleted = $wpdb->delete( RSM_INTEGRATED_AR_TABLE, array( 'list_id' => $list_id ), array( '%d' ) );
    if ( false === $deleted )
        return false;

    // Check for data
    if ( empty ( $ar_data ) )
        return true;

    return db_insert_integrated_ar( $ar_data );
}

/**
 * Updates the status of notifications.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $ids Array of notification_ids to update
 * @param string $status Status to set notifications
 * @return bool True if successfully updated, otherwise false
 */
function db_update_notification_status( $ids = array(), $status ) {
    global $wpdb;

    // Whitelist our status options (Not sent, Sent, Error)
    $whitelist = array( 'N', 'S', 'E' );

    // Make sure the status is valid
    if ( ! in_array( $status, $whitelist ) )
        return false;

    // Check for IDs
    if ( empty( $ids ) )
         return false;

    // Set or reset sent dates, depending on status
    $sent_date     = ( 'N' == $status ) ? '0000-00-00 00:00:00' : rsm_get_datetime();
    $sent_date_gmt = ( 'N' == $status ) ? '0000-00-00 00:00:00' : get_gmt_from_date( $sent_date );

    $sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
               SET n.status = '" . $status . "',
                   n.sent_date = '" . $sent_date . "',
                   n.sent_date_gmt = '" . $sent_date_gmt . "'
             WHERE n.notification_id IN ( " . implode( ',', $ids ) . " )";

    // Execute our query
    $updated = $wpdb->query( $sql );

    return ( false === $updated ) ? false : true;
}

/**
 * Recalculates and updates each follow-up sequence offset delay of a campaign after a delay value has changed
 * or a sequence has been deleted.
 *
 * Seq-delays can be modified two ways: (1) Edited and (2) Deleted. Whenever a seq-delay is modified:
 *   1.) Recalculate the offsets
 *   2.) Recalculate the send-dates
 *
 * - Recalculating offsets can be done without dependency at any time.
 * - Recalculating send-dates requires a previous delay value.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $campaign_id Campaign ID that owns the follow-up sequences needing to be updated
 * @param int $summary_id Summary ID of the sequence that triggered the update
 * @param int $prev_delay The previous sequence delay value; this value is used in calculating new send_dates
 * @return bool True if successfully updated, otherwise false
 */
function db_update_sequence_delays( $campaign_id = 0, $summary_id = 0, $prev_delay = 0 ) {
    global $wpdb;

    // List and Summary ID are required
    $campaign_id = absint( $campaign_id );
    $summary_id = absint( $summary_id );
    if ( empty( $campaign_id ) || empty( $summary_id ) )
         return false;

    // 1.) Update sequence table delay_offsets
    $sql = "UPDATE " . RSM_SUMMARY_TABLE . " AS s
             INNER JOIN  ( SELECT summary_id, delay_offset
                             FROM ( SELECT summary_id, @d_offset:=@d_offset+delay AS delay_offset
                                      FROM " . RSM_SUMMARY_TABLE . ", ( SELECT @d_offset:=0 ) AS d
                                     WHERE campaign_id = " . $campaign_id . "
                                     ORDER BY summary_id ASC ) AS s3
                         ) AS s2 ON s.summary_id = s2.summary_id
               SET s.delay_offset = s2.delay_offset";

    // Execute our query
    $updated = $wpdb->query( $sql );

    // 2.) Update notification table send dates
    if ( $updated !== false ) {

        // Get the current/new delay value; this will be 0 if this routine was called from db_delete_sequence()
        //   UPDATE operation: summary_id will exist  --> $new_delay will contain a value
        //   DELETE operation: summary_id won't exist --> $new_delay = 0
        $new_delay = db_get_sequence_delay( $summary_id );

        // Get the difference between previous and new delay;
        //   date_add( send_date, INTERVAL ($new_delay - $prev_delay) DAY )
        $interval  = $new_delay - $prev_delay;

        // Update qualifying notifications with new send_dates
        $sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
                 INNER JOIN ( SELECT n3.notification_id, date_add( n3.send_date, INTERVAL " . $interval . " DAY ) AS send_date, date_add( n3.send_date_gmt, INTERVAL " . $interval . " DAY ) AS send_date_gmt
                                FROM " . RSM_NOTIFICATION_TABLE . " AS n3
                               WHERE n3.status = 'N'
                                 AND n3.summary_id >= " . $summary_id . " ) AS n2 ON n.notification_id = n2.notification_id
                   SET n.send_date = n2.send_date,
                       n.send_date_gmt = n2.send_date_gmt";

        // Execute our query
        $updated = $wpdb->query( $sql );
    }

    return ( false === $updated ) ? false : true;

}

/**
 * Updates the message, redirect_url, and/or send_date of sequence notifications. This should take place after
 * its related summary record has been updated with new values.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $int Summary ID of queued notifications to update
 * @return bool True if successfully updated, otherwise false
 */
function db_update_sequence_notifications( $summary_id = 0 ) {
    global $wpdb;

    // Summary ID is required
    $summary_id  = absint( $summary_id );
    if ( empty( $summary_id ) )
        return false;

    // Build update query
    $sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
             INNER JOIN ( SELECT summary_id, subscriber_id, message, redirect_url, redirect_type, schedule_date, schedule_date_gmt
                            FROM ( SELECT s.summary_id, n.subscriber_id, REPLACE( REPLACE( REPLACE( s.message, '{{first_name}}', LEFT( sub.first_name, 15 ) ), '{{last_name}}', LEFT( sub.last_name, 20 ) ), '{{full_name}}', LEFT( sub.full_name, 35 ) ) AS message, s.redirect_url, s.redirect_type, s.schedule_date, s.schedule_date_gmt
                                     FROM " . RSM_NOTIFICATION_TABLE . " AS n
                                    INNER JOIN " . RSM_SUMMARY_TABLE . " AS s ON n.summary_id = s.summary_id
                                    INNER JOIN " . RSM_SUBSCRIBER_TABLE . " AS sub ON n.subscriber_id = sub.subscriber_id
                           WHERE s.summary_id = " . $summary_id . "
		                     AND n.status = 'N' ) AS n3
                        ) AS n2 ON n.summary_id = n2.summary_id AND n.subscriber_id = n2.subscriber_id
               SET n.message = n2.message,
                   n.redirect_url = n2.redirect_url,
                   n.redirect_type = n2.redirect_type";

    // Execute our query
    $updated = $wpdb->query( $sql );

	if ( $updated !== false ) {
		$sql = "UPDATE " . RSM_NOTIFICATION_TABLE . " AS n
                   SET n.message = REPLACE( REPLACE( n.message, '{{day_of_week}}', DAYNAME( n.send_date ) ), '{{date}}', DATE( n.send_date ) )
                 WHERE n.status = 'N'
                   AND n.summary_id = " . $summary_id;

		$updated = $wpdb->query( $sql );
	}

	return ( false === $updated ) ? false : true;
}

/**
 * Disconnects an autoresponder.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param $ar_name Autoresponder to be updated
 * @return bool True if successfully updated, otherwise false
 */
function db_update_ar_disconnect( $ar_name ) {
    global $wpdb;

    $updated = $wpdb->update( RSM_AUTORESPONDER_TABLE,
        array( 'connected' => 'F' ),
        array( 'ar_name' => $ar_name ),
        array( '%s' ),
        array( '%s' )
    );

    return ( false === $updated ) ? false : true;
}

/*----------------------------------------------------------------------------*
 * Delete functions
 *----------------------------------------------------------------------------*/

/**
 * Deletes a list.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $list_id List ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_list( $list_id = 0 ) {
    global $wpdb;

    // List ID is required
    $list_id = absint( $list_id );
    if ( empty( $list_id ) )
        return false;

    // Build delete statement
    $c_sql = 'DELETE c
                FROM ' . RSM_CLICK_TABLE . ' AS c
               INNER JOIN ' . RSM_NOTIFICATION_TABLE . ' AS n ON c.notification_id = n.notification_id
               WHERE n.list_id = ' . $list_id;

	$d_sql = 'DELETE d
                FROM ' . RSM_SEGMENT_DETAIL_TABLE . ' AS d
               INNER JOIN ' . RSM_SEGMENT_TABLE . ' AS s ON d.segment_id = s.segment_id
               WHERE s.list_id = ' . $list_id;

	// Delete from tables
    $deleted = 0;
    $deleted += absint( $wpdb->delete( RSM_LIST_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_SUBSCRIBER_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->query( $c_sql ) );   // delete from CLICK table before NOTIFICATION to ensure JOIN works
    $deleted += absint( $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
	$deleted += absint( $wpdb->query( $d_sql ) );   // delete from SEGMENT_DETAIL table before SEGMENT to ensure JOIN works
	$deleted += absint( $wpdb->delete( RSM_SEGMENT_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_SUMMARY_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_CAMPAIGN_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_INTEGRATED_AR_TABLE, array( 'list_id' => $list_id ), array( '%d' ) ) );

    return ( 0 == $deleted ? false : $deleted );
}

/**
 * Deletes a campaign.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $campaign_id Campaign ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_campaign( $campaign_id = 0 ) {
    global $wpdb;

    // List ID is required
    $campaign_id = absint( $campaign_id );
    if ( empty( $campaign_id ) )
        return false;

    $c_sql = 'DELETE c
                FROM ' . RSM_CLICK_TABLE . ' c
               INNER JOIN ' . RSM_NOTIFICATION_TABLE . ' n ON c.notification_id = n.notification_id
               WHERE n.campaign_id = ' . $campaign_id;

    // Delete from tables: campaign, summary, click, notification
    $deleted = 0;
    $deleted += absint( $wpdb->query( $c_sql ) );
    $deleted += absint( $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'campaign_id' => $campaign_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_SUMMARY_TABLE, array( 'campaign_id' => $campaign_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->delete( RSM_CAMPAIGN_TABLE, array( 'campaign_id' => $campaign_id ), array( '%d' ) ) );

    return ( 0 == $deleted ? false : $deleted );
}

/**
 * Deletes a follow-up sequence record.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $summary_id Summary ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_sequence( $summary_id = 0 ) {
    global $wpdb;

    // Summary ID is required
    $summary_id = absint( $summary_id );
    if ( empty( $summary_id ) )
        return false;

    // Get the sequence's campaign_id
    $sql = "SELECT campaign_id
                  FROM " . RSM_SUMMARY_TABLE . "
                 WHERE summary_id = ". $summary_id . "
                 LIMIT 1";

    // Get the campaign ID
    $campaign_id = $wpdb->get_var( $sql );
    if ( empty( $campaign_id ) )
        return false;

    // Get previous delay value
    $prev_delay = db_get_sequence_delay( $summary_id );

    // Build query for deleting notifications
    $c_sql = 'DELETE c
                FROM ' . RSM_CLICK_TABLE . ' c
               INNER JOIN ' . RSM_NOTIFICATION_TABLE . ' n ON c.notification_id = n.notification_id
               WHERE n.campaign_id = ' . $campaign_id;

    // Delete from tables: summary, click, notification
    $deleted = 0;
    $deleted += absint( $wpdb->query( $c_sql ) );
    $deleted += $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'summary_id' => $summary_id ), array( '%d' ) );
    $deleted += $wpdb->delete( RSM_SUMMARY_TABLE, array( 'summary_id' => $summary_id ), array( '%d' ) );

    // Afer successful deletion, recalculate sequence delays and corresponding send_dates
    $updated = $deleted ? db_update_sequence_delays( $campaign_id, $summary_id, $prev_delay ) : 0;

    return ( 0 == $deleted || false === $updated ? false : $deleted );
}

/**
 * Deletes a subscriber.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $subscriber_id Subscriber ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_subscriber( $subscriber_id = 0 ) {
    global $wpdb;

    // Subscriber ID is required
    $subscriber_id = absint( $subscriber_id );
    if ( empty( $subscriber_id ) )
         return false;

    $c_sql = 'DELETE c
                FROM ' . RSM_CLICK_TABLE . ' c
               INNER JOIN ' . RSM_NOTIFICATION_TABLE . ' n ON c.notification_id = n.notification_id
               WHERE n.subscriber_id = ' . $subscriber_id;

    // Delete from tables: subscriber, notification, click
    $deleted = 0;
    $deleted += absint( $wpdb->delete( RSM_SUBSCRIBER_TABLE, array( 'subscriber_id' => $subscriber_id ), array( '%d' ) ) );
    $deleted += absint( $wpdb->query( $c_sql ) );
    $deleted += absint( $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'subscriber_id' => $subscriber_id ), array( '%d' ) ) );

    return ( 0 == $deleted ? false : $deleted );
}

/**
 * Deletes a segment.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $segment_id Segment ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_segment( $segment_id = 0 ) {
	global $wpdb;

	// Subscriber ID is required
	$segment_id = absint( $segment_id );
	if ( empty( $segment_id ) )
		return false;

	$c_sql = 'DELETE c
                FROM ' . RSM_CLICK_TABLE . ' c
               INNER JOIN ' . RSM_NOTIFICATION_TABLE . ' n ON c.notification_id = n.notification_id
               WHERE n.segment_d = ' . $segment_id;

	// Delete from tables
	$deleted = 0;
	$deleted += $wpdb->delete( RSM_SEGMENT_DETAIL_TABLE, array( 'segment_id' => $segment_id ), array( '%d' ) );
	$deleted += $wpdb->delete( RSM_SEGMENT_TABLE, array( 'segment_id' => $segment_id ), array( '%d' ) );
	//$deleted += absint( $wpdb->query( $c_sql ) );
	//$deleted += $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'segment_id' => $segment_id ), array( '%d' ) );

	return ( 0 == $deleted ? false : $deleted );
}

/**
 * Deletes a notification. Note: whether to allow deleting of sent/not-sent is controlled by UI.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $notification_id Notification ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_notification( $notification_id = 0 ) {
    global $wpdb;

    // Notification ID is required
    $notification_id = absint( $notification_id );
    if ( empty( $notification_id ) )
         return false;

    // Delete from tables: notification, click
    $deleted = 0;
    $deleted += $wpdb->delete( RSM_CLICK_TABLE, array( 'notification_id' => $notification_id ), array( '%d' ) );
    $deleted += $wpdb->delete( RSM_NOTIFICATION_TABLE, array( 'notification_id' => $notification_id ), array( '%d' ) );

    return ( 0 == $deleted ? false : $deleted );
}

/**
 * Deletes a log entry.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param int $log_id Log ID to be deleted
 * @return mixed Number of rows deleted, otherwise false
 */
function db_delete_log( $log_id = 0 ) {
    global $wpdb;

    // Log ID is required
    $log_id = absint( $log_id );
    if ( empty( $log_id ) )
         return false;

    $deleted = $wpdb->delete( RSM_LOG_TABLE, array( 'log_id' => $log_id ), array( '%d' ) );

    return ( 0 == $deleted ? false : $deleted );
}
