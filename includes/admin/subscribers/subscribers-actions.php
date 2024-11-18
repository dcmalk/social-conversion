<?php
/**
 * Subscriber Actions
 *
 * @package     RSM
 * @subpackage  Admin/Subscribers
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Listens for when an activate subscriber button is clicked and activates the subscriber.
 *
 * @since 1.0
 * @param array $data Subscriber data
 * @return void
 */
function rsm_activate_subscriber( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_subscriber_nonce' ) ) {
        wp_die( 'Activate Subscriber: Nonce is invalid', 'Error' );
    }

    $results = db_update_subscriber_status( $data['sub-id'], true ) ? 'subscriber_activated' : 'subscriber_activate_error';

    $args = rsm_build_query( $data );
    wp_redirect( 'admin.php?page=social-conversion-subscribers&rsm-message=' . $results . ( ! empty( $args ) ? '&'. $args : '' ) );
    exit;
}
add_action( 'rsm_activate_subscriber', 'rsm_activate_subscriber' );

/**
 * Listens for when a delete subscriber button is clicked and deletes the subscriber.
 *
 * @since 1.0
 * @param array $data Subscriber data
 * @return void
 */
function rsm_delete_subscriber( $data ) {
   if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_subscriber_nonce' ) ) {
		wp_die( 'Delete Subscriber: Nonce is invalid', 'Error' );
    }

    $results = db_delete_subscriber( $data['sub-id'] ) ? 'subscriber_deleted' : 'subscriber_delete_error';

    $args = rsm_build_query( $data );
    wp_redirect( 'admin.php?page=social-conversion-subscribers&rsm-message=' . $results . ( ! empty( $args ) ? '&'. $args : '' ) );
    exit;
}
add_action( 'rsm_delete_subscriber', 'rsm_delete_subscriber' );

/**
 * Import subscribers to a CSV file.
 *
 * @since 1.0
 * @return void
 */
function rsm_subscribers_import( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_import_nonce' ) ) {
        wp_die( 'Subscriber Import: Nonce is invalid', 'Error' );
    }

    $subscribers_import = new RSM_Subscribers_Import();

    if ( isset( $data['rsm-action'] ) ) {

        switch ( $data['rsm-action'] ) {
            case 'subscribers_upload':
                $subscribers_import->upload();
                break;

            case 'subscribers_map_csv':
                $subscribers_import->map_csv();
                break;
        }
    }
}
add_action( 'rsm_subscribers_upload', 'rsm_subscribers_import' );
add_action( 'rsm_subscribers_map_csv', 'rsm_subscribers_import' );

/**
 * Export subscribers to a CSV file.
 *
 * @since 1.0
 * @return void
 */
function rsm_subscribers_export() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/subscribers/class-subscribers-export.php';

    $subscribers_export = new RSM_Subscribers_Export();

    $subscribers_export->export();
}
add_action( 'rsm_subscribers_export', 'rsm_subscribers_export' );
