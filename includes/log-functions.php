<?php
/**
 * Logging Functions
 *
 * @package     RSM
 * @subpackage  Logging
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Saves a log entry.
 *
 * @since 1.0
 * @param string $type The type of log entry (default: event)
 * @param string $description The log description
 * @param array (optional) $meta Any additional meta data to log
 * @param string (optional) $backtrace The backtrace of an error/exception
 * @return mixed ID of the new log entry, otherwise false
 */
function rsm_insert_log( $type = 'event', $description, $meta = null, $backtrace = null ) {
    // Encode and serialize arrays
    $meta      = isset( $meta )      ? ( base64_encode( is_array( $meta )      ? serialize( $meta )      : $meta ) )      : null;
    $backtrace = isset( $backtrace ) ? ( base64_encode( is_array( $backtrace ) ? serialize( $backtrace ) : $backtrace ) ) : null;

    $log_data = array(
        'type'             => $type,
        'description'      => $description,
        'meta'             => $meta,
        'backtrace'        => $backtrace
    );

    // Insert log data into db
    return db_insert_log( $log_data );
}
