<?php
/**
 * Log Actions
 *
 * @package     RSM
 * @subpackage  Admin/Log
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Listens for when a notification delete button is clicked and deletes the notification.
 *
 * @since 1.0
 * @param array $data Notification data
 * @return void
 */
function rsm_delete_notification( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_log_nonce' ) ) {
        wp_die( 'Delete Notification: Nonce is invalid', 'Error' );
    }

    $results = db_delete_notification( $data['notification-id'] ) ? 'notification_deleted' : 'notification_delete_error';

    $args = rsm_build_query( $data );
    wp_redirect( 'admin.php?page=social-conversion-log&rsm-message=' . $results . ( ! empty( $args ) ? '&'. $args : '' ) );
    exit;
}
add_action( 'rsm_delete_notification', 'rsm_delete_notification' );
