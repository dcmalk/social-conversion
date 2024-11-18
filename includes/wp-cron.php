<?php
/**
 * WordPress Cron Functions
 *
 * @package     RSM
 * @subpackage  Cron
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add custom WP cron intervals.
 *
 * @since 1.0
 * @param array $schedules Stores all recurrence schedules within WordPress
 * @return array Return our newly added schedule to be merged into the others
 */
function rsm_add_wp_cron_intervals( $schedules ) {
    // WP Cron schedule for running the notification processor
	$schedules['rsm_ten_minutes'] = array(
        'interval'  => 600,  // Number of seconds, 600 seconds = 10 minutes
        'display'   => 'Once Every 10 Minutes'
    );

    return (array)$schedules;
}
add_filter( 'cron_schedules', 'rsm_add_wp_cron_intervals' );

/**
 * Schedule the core cron notification processing event.
 *
 * @since 1.0
 * @return void
 */
function rsm_schedule_wp_cron() {
    // Make sure this event hasn't been scheduled
    if ( ! wp_next_scheduled( 'rsm_wp_cron_event' ) ) {
        // Schedule the event
        wp_schedule_event( time(), 'rsm_ten_minutes', 'rsm_wp_cron_event' );
    }
}
register_activation_hook( RSM_PLUGIN_FILE, 'rsm_schedule_wp_cron' );

/**
 * Executes the notification processor when WP cron triggers.
 *
 * @since 1.0
 * @return void
 */
function rsm_wp_cron_event() {
    // Get cron type setting
    $cron_type = rsm_get_option( 'cron_type' );

    // If using WP cron, schedule; otherwise, remove cron schedule
    if ( 'wp' == $cron_type ) {
        rsm_process_notifications( false, 'wp' );
    } else {
        rsm_remove_wp_cron();
    }
}
// Hook the notification processor to an action for WP cron scheduling
add_action( 'rsm_wp_cron_event', 'rsm_wp_cron_event' );

/**
 * Remove scheduled cron event.
 *
 * @since 1.0
 * @return void
 */
function rsm_remove_wp_cron(){
	wp_clear_scheduled_hook( 'rsm_wp_cron_event' );
}
register_deactivation_hook( RSM_PLUGIN_FILE, 'rsm_remove_wp_cron' );

/**
 * Schedule the duplicate delay cron notification processing event.
 *
 * @since 1.0
 * @return void
 */
/*function rsm_schedule_dup_delay_cron() {
	// Make sure this event hasn't been scheduled
	wp_schedule_single_event( time() + 30, 'rsm_wp_dup_delay_event' );
}*/

/**
 * Executes the notification processor when WP cron triggers.
 *
 * @since 1.0
 * @return void
 */
/*function rsm_wp_dup_delay_event() {
	rsm_process_notifications( false, 'delayed' );
}
// Hook the duplicate delay event to an action for WP cron scheduling
add_action( 'rsm_wp_dup_delay_event', 'rsm_wp_dup_delay_event' );*/

