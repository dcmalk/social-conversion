<?php
/**
 * Updates
 *
 * @package     RSM
 * @subpackage  Admin/Updates
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * If necessary, run incremental updates, one by one, for each version in the update path.
 *
 * @since 1.0
 * @return void
 */
function rsm_maybe_update() {
    // Exit if this plugin database doesn't need updating
    $cur_version = get_option( 'rsm_sn_version' );
    if ( $cur_version >= RSM_VERSION )
        return;

    // Check if currently updating
    if( rsm_get_updating_state() ) {
        return;
    } else {
        update_option( 'rsm_sn_updates_lastrun', rsm_get_datetime_gmt(), 'no' );
    }

    // No PHP timeout for running updates
    rsm_set_timeout( 0 );

    // Set version update path
    $update_path = array(
        '0.5.1',
        '0.5.2',
	    '0.6.0',
	    '0.7.0',
	    '0.7.5',
	    '0.8.0'
    );

    // Run update routines one by one until the current version number
    foreach ( $update_path as $update_ver ) {
        if ( version_compare( $cur_version, $update_ver, '<' ) ) {

            // Convert version into string and remove periods
            $update_ver_str = str_replace( ".", "", $update_ver );

            // Call separate update function for each version in update path
            $func = "rsm_updates_v{$update_ver_str}";
            if ( function_exists( $func ) ) {
                call_user_func( $func );
            }

            // Update the option in the db, so that this process can always pick up where it left off
            update_option( 'rsm_sn_version', $update_ver, 'no' );
        }
    }

    // Update versions
    rsm_update_option( 'rsm_sn_version_upgraded_from', $cur_version );
    update_option( 'rsm_sn_version', RSM_VERSION );
    update_option( 'rsm_sn_updates_lastrun', 0, 'no' );
}
add_action( 'admin_init', 'rsm_maybe_update', -1 );

/**
 * Determines if system is busy running updates. If busy, check
 * if the last update time exceeds 10 minutes; if so, reset the update state.
 * This is a preventative measure for the unlikely case of something
 * interrupting the updating and leaving future updates in a dead state.
 *
 * @since 1.0
 * @return bool True if busy updating, otherwise false
 */
function rsm_get_updating_state() {
    // Get notification processing state
    $updating = get_option( 'rsm_sn_updates_lastrun' );  // 0=false, otherwise date

    // Check if currently updating
    if ( $updating ) {

        // If updating, check if exceeds 1 hour and if so, unlock
        $lastrun_gmt  = $updating;
        $datetime_gmt = rsm_get_datetime_gmt();

        // If lastrun is less than 10 minutes, return true (busy updating)
        if ( ( strtotime( $lastrun_gmt ) + 600 ) > strtotime( $datetime_gmt ) ) {
            return true;
        }

        // If exceeds 10 minutes, update the option and return
        update_option( 'rsm_sn_updates_lastrun', 0, 'no' );  // false
        return false;

    } else {
        // If not updating, return false
        return false;
    }
}

/**
 * Update routine for version 0.5.1.
 *
 * @since 0.5.1
 * @return void
 */
function rsm_updates_v051() {
    /*$settings = rsm_get_settings();

    if ( ! isset( $settings['fb_website_url'] ) ) {
        $settings['fb_website_url'] = home_url();
        update_option( 'rsm_sn_settings', $settings );
    }*/
}

/**
 * Update routine for version 0.5.2.
 *
 * @since 0.5.2
 * @return void
 */
function rsm_updates_v052() {
    //delete_option( 'rsm_sn_settings' );
    //update_option( 'rsm_sn_settings', rsm_get_default_settings() );
    // if there were any opt-in links, we would need to update those too
}

/**
 * Update routine for version 0.6.0.
 *
 * @since 0.6.0
 * @return void
 */
function rsm_updates_v060() {
	global $wpdb;

	// 1.) Delete any duplicate clicks
	$sql = "DELETE n1
	          FROM " . RSM_CLICK_TABLE . " n1, " . RSM_CLICK_TABLE . " n2
	         WHERE n1.click_id > n2.click_id
	           AND n1.notification_id = n2.notification_id";

	// Execute the query
	$wpdb->query( $sql );

	// 2.) Add the UNIQUE index
	$sql = "ALTER TABLE " . RSM_CLICK_TABLE . "
              ADD UNIQUE INDEX uq_notification_id (notification_id)";

	// Execute the query
	$wpdb->query( $sql );
}

/**
 * Update routine for version 0.7.0.
 *
 * @since 0.7.0
 * @return void
 */
function rsm_updates_v070() {
	$settings = rsm_get_settings();

	if ( ! isset( $settings['float_status'] ) ) {
		rsm_update_option( array( 'float_status'       => 0,
		                          'float_list_id'      => 0,
		                          'float_text'         => 'Get Notifications',
		                          'float_color'        => '#FFFFFF',
		                          'float_button_color' => '#357CA5',
		                          'float_position'     => 'right' )
		);
	}
}

/**
 * Update routine for version 0.7.5.
 *
 * @since 0.8.0
 * @return void
 */
function rsm_updates_v075() {
	global $wpdb;

	// Update all table collations
	$sql = "ALTER TABLE " . RSM_LIST_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_CAMPAIGN_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_SUBSCRIBER_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_SUMMARY_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_NOTIFICATION_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_AUTORESPONDER_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_AUTORESPONDER_LIST_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_INTEGRATED_AR_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_CLICK_TABLE . " CHARACTER SET = utf8mb4, COLLATE = utf8mb4_unicode_ci";
	$wpdb->query( $sql );
	$sql = "ALTER TABLE " . RSM_LOG_TABLE . " CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );
}

/**
 * Update routine for version 0.8.0.
 *
 * @since 0.8.0
 * @return void
 */
function rsm_updates_v080() {
	global $wpdb;

	// 1.) Drop the click table UNIQUE index
	$sql = "ALTER TABLE " . RSM_CLICK_TABLE . "
              DROP INDEX uq_notification_id";

	// Execute the query
	$wpdb->query( $sql );
}

