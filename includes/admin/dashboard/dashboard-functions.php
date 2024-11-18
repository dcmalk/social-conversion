<?php
/**
 * Dashbaord Functions
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get notifications sent by date.
 *
 * @since 1.0
 * @param int $day Day number
 * @param int $month Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Number of notifications sent for this date
 */
function rsm_get_sent_by_date( $day = null, $month = null, $year = null, $hour = null ) {
    $date_args = array(
        'day'   => $day,
        'month' => $month,
        'year'  => $year,
        'hour'  => $hour
        );

    return db_get_sent_by_date( $date_args );
}

/**
 * Get number of clicks by date.
 *
 * @since 1.0
 * @param int $day Day number
 * @param int $month Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Number of clicks for this date
 */
function rsm_get_clicks_by_date( $day = null, $month = null, $year = null, $hour = null ) {
    $date_args = array(
        'day'   => $day,
        'month' => $month,
        'year'  => $year,
        'hour'  => $hour
        );

    return db_get_clicks_by_date( $date_args );
}

/**
 * Get number of optins by date.
 *
 * @since 1.0
 * @param int $day Day number
 * @param int $month Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Number of options for this date
 */
function rsm_get_optins_by_date( $day = null, $month = null, $year = null, $hour = null ) {
    $date_args = array(
        'day'   => $day,
        'month' => $month,
        'year'  => $year,
        'hour'  => $hour
        );

    return db_get_optins_by_date( $date_args );
}

/**
 * Checks the version status of the plugin.
 *
 * @since 1.0
 * @return array The update status and date of last check
 */
function rsm_get_update_status() {
    // Get update details from cache
    $update_cache = get_site_transient( 'update_plugins' );

    // If details found, there is probably an update available
    if ( isset( $update_cache->response[ RSM_PLUGIN_BASENAME ] ) ) {

        $new_version  = $update_cache->response[ RSM_PLUGIN_BASENAME ]->new_version;
        if ( version_compare( RSM_VERSION, $new_version, '<' ) ) {
            $update_status = '<a class="btn bg-rsm-dark-slate btn-flat" title="Click to go to updates" data-toggle="tooltip" href="' . admin_url( 'plugins.php?plugin_status=upgrade' ) . '"><strong><i class="fa fa-arrow-circle-right"></i>&nbsp; Update available <small>(' . $new_version . ')</small></strong></a>';
        } else {
            $update_status = 'Up to date';
        }

    // Otherwise, the plugin is up to date
    } else {
        $update_status = 'Up to date' ;
    }

    // Get the last checked date
    $last_checked = isset( $update_cache->last_checked ) ? $update_cache->last_checked : 'N/A';

    return array( $update_status, $last_checked );
}
