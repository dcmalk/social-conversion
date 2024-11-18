<?php
/**
 * Campaigns Actions
 *
 * @package     RSM
 * @subpackage  Admin/Campaigns
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Listens for when a notification button is clicked and processes.
 * the appropriate action.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_campaign_submit( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_campaign_nonce' ) ) {
        wp_die( 'Submit campaign: Nonce is invalid', 'Error' );
    }

    if ( isset( $data['campaign-type'] ) ) {

        // Validate campaign
        if ( rsm_validate_campaign( $data ) ) {

            // Submit data based on campaign type
            switch ( $data['campaign-type'] ) {
                case 'I':   // Instant or
                case 'L':   // Scheduled

                    // Insert or update campaign based on rsm-mode
                    if ( 'add' == $data['rsm-mode'] ) {
                        $db_result = db_insert_batch_campaign( $data );
                        $result    = $db_result ? 'campaign_inserted' : 'campaign_insert_error';
                    } else {
                        $db_result = db_update_batch_summary( $data );
                        $result    = $db_result ? 'campaign_updated' : 'campaign_update_error';
                    }

                    // If Instant campaign without errors, proceed to processing queued notifications
                    if ( 'I' == $data['campaign-type'] && $db_result ) {
                        wp_redirect( 'admin.php?page=social-conversion-campaigns&procn=1' );
                        exit;
                    }
                    wp_redirect( 'admin.php?page=social-conversion-campaigns&rsm-message=' . $result );
                    exit;

                    break;

                case 'S':   // Follow-up sequence
                    // Insert or update campaign based on rsm-mode
                    if ( 'add' == $data['rsm-mode'] ) {
                        $campaign_id = db_insert_sequence_campaign( $data );
                        $result      = $campaign_id ? 'campaign_inserted' : 'campaign_insert_error';
                    } else {
                        // If summary_id is set, we are editing a summary; otherwise, adding new sequence to existing campaign
                        if ( isset( $_GET['summary-id'] ) ) {
                            $campaign_id        = (int) $data['campaign-id'];
                            $data['summary-id'] = (int) $_GET['summary-id'];
                            $result             = db_update_sequence_summary( $data ) ? 'sequence_updated' : 'sequence_update_error';
                        } else {
                            $campaign_id = db_insert_sequence_campaign( $data );
                            $result      = $campaign_id ? 'sequence_inserted' : 'sequence_insert_error';
                        }
                    }

                    if ( isset( $data['rsm-btn-submit'] ) ) {
                        // Save & Exit
                        wp_redirect( 'admin.php?page=social-conversion-campaigns&rsm-message=' . $result );
                        exit;

                    } else if ( isset( $data['rsm-btn-add-more'] ) ) {
                        // Save & Add More
                        wp_redirect( 'admin.php?page=social-conversion-campaigns&rsm-action=edit_campaign&campaign-id=' . $campaign_id . '&rsm-message=' . $result );
                        exit;
                    }
                    break;
            }
        }
    }
    return;
}
add_action( 'rsm_campaign_submit', 'rsm_campaign_submit' );

/**
 * Listens for when a delete campaign button is clicked and deletes the campaign.
 *
 * @since 1.0
 * @param array $data Campaign data
 * @return void
 */
function rsm_delete_campaign( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_campaign_nonce' ) ) {
        wp_die( 'Delete campaign: Nonce is invalid', 'Error' );
    }

    $result = db_delete_campaign( $data['campaign-id'] ) ? 'campaign_deleted' : 'campaign_delete_error';
    wp_redirect( 'admin.php?page=social-conversion-campaigns&rsm-message=' . $result );
    exit;
}
add_action( 'rsm_delete_campaign', 'rsm_delete_campaign' );

/**
 * Listens for when a delete sequence button is clicked and deletes the sequence.
 *
 * @since 1.0
 * @param array $data Subscriber data
 * @return void
 */
function rsm_delete_sequence( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_campaign_nonce' ) ) {
        wp_die( 'Delete sequence: Nonce is invalid', 'Error' );
    }

    // Delete sequence
    $result = db_delete_sequence( $data['summary-id'] ) ? 'sequence_deleted' : 'sequence_delete_error';
    wp_redirect( 'admin.php?page=social-conversion-campaigns&rsm-action=edit_campaign&campaign-id=' . $data['campaign-id'] . '&rsm-message=' . $result );
    exit;
}
add_action( 'rsm_delete_sequence', 'rsm_delete_sequence' );

/**
 * Supplemental (backup) validation of a submitted campaign.
 *
 * @since 1.0
 * @param array $data Campaign data
 * @return bool True if successfully validated, otherwise false
 */
function rsm_validate_campaign( $data ){
    $result = true;

    if ( ! isset( $data['list-id'] ) ) {
        add_settings_error( 'rsm-notices', 'list-id', 'No FB lists found. Please go to Settings to setup your first FB list.', 'error' );
        $result = false;
    }

    if ( isset( $data['list-id'] ) && 0 === mb_strlen( trim( $data['list-id'] ) ) ) {
        add_settings_error( 'rsm-notices', 'list-id', 'FB List is required.', 'error' );
        $result = false;
    }

    if ( isset( $data['campaign-name'] ) && 0 === mb_strlen( trim( $data['campaign-name'] ) ) ) {
        add_settings_error( 'rsm-notices', 'campaign-name', 'Campaign name is required.', 'error' );
        $result = false;
    }

    if ( isset( $data['message-text'] ) && 0 === mb_strlen( trim( $data['message-text'] ) ) ) {
        add_settings_error( 'rsm-notices', 'message-text', 'Message text is required.', 'error' );
        $result = false;
    }

    if ( isset( $data['redirect-url'] ) && 0 === mb_strlen( trim( $data['redirect-url'] ) ) ) {
        add_settings_error( 'rsm-notices', 'redirect-url', 'Redirect URL is required.', 'error' );
        $result = false;
    } else if ( isset( $data['redirect-url'] ) ) {
        $arrURL = @parse_url( strtolower( $data['redirect-url'] ) );
        if ( ! in_array( $arrURL['scheme'], array( 'http', 'https' ) ) ) {
            add_settings_error( 'rsm-notices', 'redirect-url', 'Please enter a valid Redirect URL, including http:// or https://', 'error' );
            $result = false;
        }
    }

    // Campaign-specific validation
    switch ( $data['campaign-type'] ) {

        case 'L':   // Scheduled
            if ( isset( $data['schedule-date'] ) && 0 === mb_strlen( trim( $data['schedule-date'] ) ) ) {
                add_settings_error( 'rsm-notices', 'schedule-date', 'Schedule date is required.', 'error' );
                $result = false;
            } else if ( isset( $data['schedule-date'] ) && rsm_valid_date( $data['schedule-date'] ) == false ) {
                add_settings_error( 'rsm-notices', 'schedule-date', 'Schedule date is invalid.', 'error' );
                $result = false;
            }

            if ( isset( $data['schedule-time'] ) && 0 === mb_strlen( trim( $data['schedule-time'] ) ) ) {
                add_settings_error( 'rsm-notices', 'schedule-time', 'Schedule time is required.', 'error' );
                $result = false;
            } else if ( isset( $data['schedule-time'] ) && rsm_valid_date( $data['schedule-time'] ) == false ) {
                add_settings_error( 'rsm-notices', 'schedule-time', 'Schedule time is invalid.', 'error' );
                $result = false;
            }
            break;

        case 'S':   // Sequence
            if ( isset( $data['seq-delay'] ) && 0 === mb_strlen( trim( $data['seq-delay'] ) ) ) {
                add_settings_error( 'rsm-notices', 'seq-delay', 'Delay is required.', 'error' );
                $result = false;
            } else if ( isset( $data['seq-delay'] ) && 0 > (int) $data['seq-delay'] ) {
                add_settings_error( 'rsm-notices', 'seq-delay', 'Delay must be 0 or greater.', 'error' );
                $result = false;
            } else if ( isset( $data['seq-delay'] ) && 65535 < (int) $data['seq-delay'] ) {
                    add_settings_error( 'rsm-notices', 'seq-delay', 'Delay cannot be greater than 65535.', 'error' );
                    $result = false;
            }
            break;
    }
    return $result;
}
