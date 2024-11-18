<?php
/**
 * Front End Functions
 *
 * @package     RSM
 * @subpackage  Front end
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Listens for and processes front-end requests.
 *
 * @since 1.0
 * @return void
 */
function rsm_process_query() {
    // Only process RSM requests
    if ( ! isset( $_REQUEST['rsm-action'] ) ) return;

    // Validate the query type
    $query_mode = rsm_get_query_mode( $_REQUEST['rsm-action'] );
    if ( ! $query_mode ) {
        // Invalid query type, log error and terminate
        rsm_error_handler( 'Invalid request: query type not valid. Please double-check and test your link.', $_REQUEST, true, true );
    }

    // Process the request
    switch ( $query_mode ) {
        case 'rcron':
            rsm_process_query_rcron( $_REQUEST );
            break;

        case 'ctctauth':
            rsm_process_ctctauth( $_REQUEST );
            break;

        case 'opt':
        case 'fbauth':
        case 'iframe':
            rsm_process_query_fb( $_REQUEST, $query_mode );
            break;
    }
    exit;
}
add_action( 'parse_request', 'rsm_process_query' );

/**
 * Determines the type and validity of query.
 *
 * @since 1.0
 * @param string $action The RSM query variable passed to the front-end
 * @return string $action Query mode, otherwise false
 */
function rsm_get_query_mode( $action ) {
    // Whitelist our query options
    $whitelist = array( 'opt', 'fbauth', 'iframe', 'rcron', 'ctctauth' );

    // Make sure our query is valid
    if ( ! in_array( $action, $whitelist ) ) {
        return false;
    }
    return $action;
}

/**
 * Processes a remote cron front-end request.
 *
 * @since 1.0
 * @param array $request HTTP Request variables
 * @return void
 */
function rsm_process_query_rcron( $request = array() ) {
    // Get cron token
    $settings   = rsm_get_settings();
    $cron_token = $settings['cron_token'];

    // Verify request  [?rsm-action=rcron&tk=abc123]
    if ( $cron_token && isset( $request['tk'] ) && ( $cron_token == $request['tk'] ) ) {
        rsm_process_notifications( false, 'real' );
    } else {
        // Invalid cron token, log error and terminate; do not show user error msg in case of brute force attack
        rsm_error_handler( 'Invalid request: cron token not valid. Please double-check your cron command or URL.', $request, false, true );
    }
    exit;
}

/**
 * Processes a Facebook front-end request.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @param array $request HTTP Request variables
 * @param string $query_mode The query mode of the request
 * @return void
 */
function rsm_process_query_fb( $request = array(), $query_mode ) {
    // For all other requests, get list data and setup OAuth object
    $list_id      = isset( $request['id'] )      ? $request['id']      : null;
	$segment_id   = isset( $request['sid'] )     ? $request['sid']     : null;
	$segment_name = isset( $request['segment'] ) ? $request['segment'] : null;

	if ( ! $list_id ) {
        // No query ID, log error and terminate
        rsm_error_handler( 'Invalid request: no query ID in request. Please double-check and test your optin link.', $request, true, true );
    }

    // Get all data for optin link's list
    $data = db_get_list_row( $list_id );
    if ( ! $data ) {
        // Invalid query ID, log error and terminate
        rsm_error_handler( 'Invalid request: query ID not valid. Please double-check and test your optin link.', $request, true, true );
    }

    try {
        // Create our FB OAuth object
        $rsm_fb = new RSM_Facebook( array(
            'app_id'     => $data['app_id'],
            'app_secret' => $data['app_secret']
        ) );
    } catch ( Exception $e ) {
        rsm_exception_handler( $e, true, true );
    }

	// If segment_name is used, it must be encoded for FB compatibility
	if ( $segment_name && 'opt' == $query_mode ) {
		$segment_name = rsm_base64url_encode( $segment_name );
	}

	// Set our redirect_url (must be the same URL for getting code and access_token)
    $redirect_url = home_url( '?rsm-action=fbauth&id=' . $list_id );
	$redirect_url .= empty( $segment_id )   ? '' : '&sid=' . $segment_id;
	$redirect_url .= empty( $segment_name ) ? '' : '&segment=' . $segment_name;

    // Process different query modes
    switch( $query_mode ) :

        /*----------------------------------------------------------------------------*
         * Optin requests  [?rsm-action=opt&id=1]
         *----------------------------------------------------------------------------*/
        case 'opt' :
            // Get login URL for authentication
            $display = isset( $request['display'] ) ? $request['display'] : '';
            $login_url = $rsm_fb->get_login_url( 'public_profile,email', $redirect_url, $display );

            // Log event
            //rsm_insert_log( 'event', 'Optin request received for list: ' . $data['app_name'] );

            // Redirect to login URL
            if ( $login_url ) {
                wp_redirect( $login_url );
            }
            exit;

            break;

        /*----------------------------------------------------------------------------*
         * FB authentication  [?rsm-action=fbauth&id=1]
         *----------------------------------------------------------------------------*/
        case 'fbauth' :
            // Verify state tokens match
            $state = $rsm_fb->get_state_token();
            if ( ! ( $state && isset( $request['state'] ) && ( $state === $request['state'] ) ) ) {
                // Invalid state token, log error and terminate
                rsm_error_handler( 'Facebook authentication error. Invalid state token.', array_merge( array( 'rsm_state' => $state ), $request ), true, true ) ;
            }

            // Check for Graph API return errors
            if ( isset( $request['error'] ) || isset( $request['error_code'] ) ) {

                // Check whether user cancelled authentication and if so, redirect to Cancel URL
                if ( ( isset( $request['error'] ) && stripos( $request['error'], 'access_denied' ) !== false ) ||
                     ( isset( $request['error_reason'] ) && stripos( $request['error_reason'], 'user_denied' ) !== false ) )
                {
                    // Log cancelled authentication
                    //rsm_insert_log( 'event', 'Facebook access denied. The user cancelled at the Facebook authentication prompt.' );
                    //rsm_error_handler( 'Facebook access denied. The user cancelled at the Facebook authentication prompt.', $request );

                    // Redirect to Cancel URL and close popup window
                    $cancel_url = isset( $data['cancel_url'] ) ? $data['cancel_url'] : '';
                    rsm_redirect_maybe_close_popup( $cancel_url );
                    exit;

                } else {
                    // Capture error, log it and terminate
                    rsm_error_handler( 'Facebook error encountered during authentication.', $request, true, true );
                }
            }

            // Confirm identity by getting user access token from code
            if ( ! isset( $request['code'] ) ) {
                // No API code, log error and terminate
                rsm_error_handler( 'Facebook authentication error. No access token returned.', $request, true, true );
            }

            // Get user access token
            try {
                $response = $rsm_fb->get_user_access_token( array (
                        'code'         => $request['code'],
                        'redirect_uri' => $redirect_url
                    )
                );
            } catch ( Exception $e ) {
                rsm_exception_handler( $e, true, true );
            }

            // Parse results and store user access token (for graph API v2.3)
            if ( ( isset( $response['code'] ) && '200' == $response['code'] ) && isset( $response['result']['access_token'] ) ) {
                $rsm_fb->set_user_access_token( $response['result']['access_token'] );
            } else {
                // Capture error, log it and terminate
                rsm_error_handler( 'Facebook authentication error. Invalid user access token.', $response, true, true );
            }

            // Get user data
            try {
                $response = $rsm_fb->get_user();
            } catch ( Exception $e ) {
                rsm_exception_handler( $e, true, true );
            }

            // Verify that user data retrieved and log event
            if ( ( isset( $response['code'] ) && '200' == $response['code'] ) ) {
                // Log event
                //rsm_insert_log( 'event', 'User successfully opted in to list: ' . $data['app_name'] );
            } else {
                // Capture error, log it and terminate
                rsm_error_handler( 'Facebook authentication error. No user data returned.', $response, true, true );
            }

            // Save optin user details to db
            $subscriber_id = db_insert_subscriber( array_merge( array( 'list_id' => $list_id ), $response['result'] ) );
            if ( false == $subscriber_id ) {
                // Optin user didn't save, log event
                rsm_error_handler( 'Optin user was not saved. This usually means they have already subscribed.', $response );

            } else {
                // If optin user saved, then queue welcome notification (if exists)
                if ( false === db_insert_welcome_notification( $list_id, $subscriber_id ) ) {
                    // Error queuing welcome notifications, log event
                    rsm_error_handler( 'Error queuing welcome msg for new optin user: ' . $response['result']['id'], $response['result']['id'] );
                }

	            // Add subscriber to any specified segments
	            if ( $segment_id || $segment_name ) {
		            $segment_name = rsm_base64url_decode( $segment_name );
		            if ( false === db_insert_subscriber_segment( $list_id, $segment_id, $segment_name, $response['result']['email'] ) ) {
			            // Error adding optin to segment, log event
			            rsm_error_handler( 'Error adding new optin user to segment: segment=' . $segment_name . ', sid=' . $segment_id, $response['result']['id'] );
		            }
	            }

	            // Get new subscriber's segments
	            $segments = db_get_subscriber_segments( $subscriber_id );

	            // Queue any scheduled & sequence notifications
	            if ( false === db_insert_optin_notifications( $list_id, $segments, $subscriber_id ) ) {
		            // Error queuing sequence notifications, log event
		            rsm_error_handler( 'Error queuing notifications for new optin user: ' . $response['result']['id'], $response['result']['id'] );
	            }

                // Add email address to autoresponder(s) if integrated
                if ( isset( $data['integrate_ar'] ) && ( "T" == $data['integrate_ar'] ) && isset( $response['result'] ) ) {
                    $user_data = $response['result'];
                    try {
                        rsm_ar_subscribe( $list_id, $user_data );
                    } catch ( Exception $e ) {
                        rsm_exception_handler( $e );
                    }
                }
            }

            // User optin successful; redirect to Okay URL and close popup window
            $okay_url = isset( $data['okay_url'] ) ? $data['okay_url'] : '';
            rsm_redirect_maybe_close_popup( $okay_url );
            exit;

            break;

        /*----------------------------------------------------------------------------*
         * FB Canvas request  [?rsm-action=iframe&id=1&n=111&rtype=O]
         * ?rsm-action=iframe&pid=66&rid=0123456789abcdef&id=1&n=2&rtype=O&fb_source=notification&ref=notif&notif_t=app_notification
         *----------------------------------------------------------------------------*/
        case 'iframe' :
            // Record statistics
	        $notification_id = isset( $request['n'] ) ? $request['n'] : null;
            $subscriber_id   = db_insert_click( $notification_id );

	        if ( $subscriber_id ) {
				// Auto update subscriber for qualifying segments that include 'Clicked' criteria
				db_update_subscriber_click_segment( $list_id, $subscriber_id );
			}

            // Redirect to URL; must handle whether using default or self-host secure canvas
            $results = db_get_notification_redirect( $notification_id );
            $url     = isset( $results['redirect_url'] ) ? esc_url_raw( $results['redirect_url'] )  : null;
            $type    = isset( $results['redirect_type'] ) ? strtoupper( $results['redirect_type'] ) : null;

            if ( $url ) rsm_js_redirect( $url, 0, ( 'O' == $type ) );

            exit;
            break;

    endswitch;
}

/**
 * Subscribes a user to any integrated autoresponder lists for a specified Facebook app.
 *
 * @since 1.0
 * @param int $list_id List ID to be deleted
 * @param array $user_data Array containing subscriber's Facebook user data
 * @return void
 */
function rsm_ar_subscribe( $list_id = 0, $user_data = array() ) {
    // List ID must be positive integer
    $list_id = absint( $list_id );
    if ( empty( $list_id ) ) return;

    // Get all ar lists for this list_id
    $ar_lists = db_get_ar_lists( $list_id );

    // Submit subscribe call for each selected ar list
    if ( $ar_lists ) {

        $prev_ar_name = '';
        foreach ( $ar_lists as $list ) {
            if ( ( isset( $list['ar_name'] ) && isset( $list['selected'] ) ) && ( 'T' == $list['selected'] ) ) {

                try {
                    // If ar name is same as previous, no need to recreate the ar object
                    if ( $list['ar_name'] != $prev_ar_name ) {
                        // Create new ar object
                        $ar = RSM_Autoresponder::get_instance( array( 'ar_name' => $list['ar_name'] ) );
                    }

                    // Subscribe the user to this ar list
                    $result = $ar->subscribe( $list['ar_list_value'], array( 'first_name' => $user_data['first_name'],
                                                                             'last_name'  => $user_data['last_name'],
                                                                             'full_name'  => $user_data['name'],
                                                                             'email'      => $user_data['email']
                    ) );

                    // Log event
                    $desc = sprintf( ' %s ( %s ) added to autoresponder list: %s', //; result = %s',
                        $user_data['name'],
                        $user_data['email'],
                        $list['ar_list_name']/*,
                        $result*/
                    );
                    //rsm_insert_log( 'event', $desc  );

                } catch ( Exception $e ) {
                    rsm_exception_handler( $e );
                }

                // Remember this ar name
                $prev_ar_name = $list['ar_name'];
            }
        }
    }
}

