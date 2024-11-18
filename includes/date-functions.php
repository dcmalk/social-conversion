<?php
/**
 * Date/Time Functions
 *
 * @package     RSM
 * @subpackage  Date/Time
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the Wordpress defined date/time format
 *
 * @since 1.0
 * @return string Wordpress date/time format string
 */
function rsm_datetime_wp_format() {
    $date_format = get_option('date_format');
    $time_format = get_option('time_format');

    return ( empty( $date_format ) || empty( $time_format ) ) ? RSM_DATETIME_MYSQL : $date_format . ' ' . $time_format;
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset.
 * Optionally returns a UTC+- zone for display purposes only.
 *
 * @since 1.0
 * @param bool (optional) $for_display Return a UTC+- zone for display only
 * @return string A timezone string
 */
function rsm_get_timezone_string( $for_display = false ) {
    // If site timezone string exists, return it
    if ( $timezone = get_option( 'timezone_string' ) )
        return $timezone;

    // Get UTC offset
    $utc_offset = get_option( 'gmt_offset', 0 );

    // If no timezone string and this is for display, create a UTC+- zone
    // UTC+- zone is not a supported timezone, hence for display only
    if ( empty( $timezone ) && $for_display ) {
        if ( 0 == $utc_offset )
            $timezone = 'UTC+0';
        elseif ($utc_offset < 0)
            $timezone = 'UTC' . $utc_offset;
        else
            $timezone = 'UTC+' . $utc_offset;
        return $timezone;
    }

    // If UTC offset isn't set, return UTC
    if ( ! ( $utc_offset = 3600 * $utc_offset ) )
        return 'UTC';

    // Wordpress sets default timezone to UTC, so we must change it to determine DST
    date_default_timezone_set( 'America/New_York' );
    $is_dst = date( 'I' );
    date_default_timezone_set('UTC');

    // Attempt to guess the timezone string from the UTC offset
    if ( $timezone = timezone_name_from_abbr( '', $utc_offset, $is_dst ) )
        return $timezone;

    // Last try, guess timezone string manually
    $is_dst = date( 'I' );
    foreach ( timezone_abbreviations_list() as $abbr ) {
        foreach ( $abbr as $city ) {
            if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                return $city['timezone_id'];
        }
    }

    // Fallback
    return 'UTC';
}

/**
 * Wrapper function that returns the current date/time in gmt format
 *
 * @since 1.0
 * @param string (optional) $format The date/time format (default: RSM_DATETIME_MYSQL)
 * @return string The current date/time in gmt format
 */
function rsm_get_datetime_gmt( $format = RSM_DATETIME_MYSQL ) {
    return date_i18n( $format, false, true );
}

/**
 * Wrapper function that returns the current local date/time
 *
 * @since 1.0
 * @param string (optional) $format The date/time format (default: RSM_DATETIME_MYSQL)
 * @return string The current local date/time
 */
function rsm_get_datetime( $format = RSM_DATETIME_MYSQL ) {
    return date_i18n( $format, false, false );
}

/**
 * Formats a date/time string into a specified date/time format
 *
 * @since 1.0
 * @param string $datetime The date/time to be formatted
 * @param string (optional) $format The date/time format to use (default: RSM_DATETIME_MYSQL)
 * @return string A formatted date/time string
 */
function rsm_format_datetime( $datetime, $format = RSM_DATETIME_MYSQL ) {
	return date( $format, strtotime( $datetime ) );
}

/**
 * Formats a date/time string to use nowrap styling around date and time parts
 *
 * @since 1.0
 * @param string $datetime The date/time to be formatted
 * @return string A formatted date/time string with nowrap styling
 */
function rsm_format_datetime_wp_nowrap( $datetime ) {
	$date_format = get_option('date_format');
	$time_format = get_option('time_format');

	$date_format = empty( $date_format )  ? RSM_DATE_OUTPUT : $date_format;
	$time_format = empty ( $time_format ) ? RSM_TIME_OUTPUT : $time_format;

	$datetime = strtotime( $datetime );
	$date_part = date( $date_format, $datetime);
	$time_part = date( $time_format, $datetime);

	return '<span class="rsm-nowrap">' . $date_part . '</span> <span class="rsm-nowrap">' . $time_part . '</span>';
}
/**
 * Checks whether a date and/or time is valid
 *
 * @since 1.0
 * @param string $datetime A date and/or time to test
 * @return bool True if valid date and/or, otherwise false
 */
function rsm_valid_date( $datetime ){
    return ( strtotime( $datetime ) ) ? true : false;
}
