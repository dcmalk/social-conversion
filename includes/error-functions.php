<?php
/**
 * Error Handling Functions
 *
 * @package     RSM
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles the logging, reporting and flow of an error
 *
 * @since 1.0
 * @param string $message The error message
 * @param int (optional) $code The error code, defaults to 0
 * @param array (optional) $meta Array of meta data to log
 * @param bool (optional) $show_user_error Shows an error message to user (default: false)
 * @param bool (optional) $terminate Whether the script should end (default: false)
 * @return void
 */
function rsm_error_handler( $message, $meta = array(), $show_user_error = false, $terminate = false ) {
    // Capture backtrace
    $backtrace = debug_backtrace();

    // Log all details about error
    rsm_insert_log( 'error', $message, $meta, $backtrace );

    // Optionally show user error message
    if ( $show_user_error ) {
        rsm_show_user_error();
    }

    // Optionally terminate the current script
    if ( $terminate ) {
        exit;
    }
}

/**
 * Handles the logging, reporting and flow of an exception
 *
 * @since 1.0
 * @param exception $e The exception
 * @param bool (optional) $show_user_error Shows an error message to user (default: false)
 * @param bool (optional) $terminate Whether the script should end (default: false)
 * @return void
 */
function rsm_exception_handler( Exception $e, $show_user_error = false, $terminate = false ) {
    // Capture backtrace
    $backtrace = debug_backtrace();

    // Log all details about error
    rsm_insert_log( 'error', $e->getMessage(), null, $backtrace );

    // Optionally show user error message
    if ( $show_user_error ) {
        rsm_show_user_error();
    }

    // Optionally terminate the current script
    if ( $terminate ) {
        exit;
    }
}

/**
 * Displays an error message to user and optionally redirects
 *
 * @since 1.0
 * @param string (optional) $redirect_url If supplied, the URL to redirect to, defaults to null
 * @return void
 */
function rsm_show_user_error( $redirect_url = null ) {
    ?>
    <!DOCTYPE HTML>
    <html lang="en-US">
        <head>
            <meta charset="UTF-8">
            <?php if( $redirect_url ) {
                rsm_js_redirect( $redirect_url, 5000 );
            } ?>
            <title>Error</title>
        </head>
        <body>
            <h3>An Error Has Occurred</h3>
             <p>We're sorry for the inconvenience, but we cannot complete your request at this time.</p>

            <?php if( $redirect_url ) : ?>
            <p>If you are not redirected automatically, follow <a href='<?php echo $redirect_url; ?>'>this link</a>...</p>
            <?php endif; ?>
        </body>
    </html>
    <?php
}

/**
 * Processes notification errors and maybe extra notification_id(s) that were involved.
 *
 * @since 1.0
 * @param array $results Results data to process
 * @return array Array of notification_id(s), if any
 */
function rsm_process_notification_errors( $results ) {
    if ( ! isset( $results ) ) {
        return;
    }

    $err_ids = array();

    // Loop through results and try to extract details about each error
    foreach( $results as $result ) {
        // 1.) Get error message
        $err_msg = ( isset( $result['error']['message'] ) ) ? $result['error']['message'] : 'Facebook returned an undefinied OAuth error';

        // 2.) Try to get UID details
        if ( isset( $result['batch']['relative_url'] ) ) {
            $r_url = explode( '/', $result['batch']['relative_url'] );
            if ( ! empty( $r_url ) ) {
                $err_msg .= '; UID=' . $r_url[1];
            }
        }

        // 3.) Try to get notification_id
        if ( isset( $result['batch']['body'] ) ) {
            $body = explode( '&', urldecode( $result['batch']['body'] ) );
            if ( ! empty( $body ) ) {
                if ( ( $pos = strpos( $body[2], "=" ) ) !== false ) {
                    $notification_id = substr( $body[2], $pos + 1 );
                    $err_ids[] = $notification_id;
                    $err_msg .= '; notification_id=' . $notification_id;
                }
            }
        }

        // 4.) Log error
        rsm_insert_log( 'error', $err_msg );
    }

    return $err_ids;
}
