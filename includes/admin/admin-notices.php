<?php
/**
 * Admin Notices
 *
 * @package     RSM
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles the display of admin notices.
 *
 * @since 1.0
 * @return void
 */
function rsm_admin_messages() {
    // Check for rsm-message notices
    if ( isset( $_GET['rsm-message'] ) ) {

        // Only display admin notices on RSM pages
        if ( ! rsm_is_admin_page() ) return;

        /*----------------------------------------------------------------------------*
         * Settings notices
         *----------------------------------------------------------------------------*/
        if ( 'list_inserted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-inserted', 'The list has been successfully created.', 'updated' );
        }
        if ( 'list_updated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-updated', 'The list has been successfully updated.', 'updated' );
        }
        if ( 'list_deleted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-deleted', 'The list has been successfully deleted.', 'updated' );
        }
        if ( 'auto_updated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-auto-updated', 'Automation settings have been successfully updated.', 'updated' );
        }
	    if ( 'list_app_error' == $_GET['rsm-message'] ) {
		    add_settings_error( 'rsm-notices', 'rsm-list-app-error', 'There was an error setting up the Facebook app. Please ensure you\'ve created the initial Facebook app following the User Guide instructions (link above) and then try again.' , 'error' );
	    }
        if ( 'list_insert_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-insert-error', 'There was an error creating the list.', 'error' );
        }
        if ( 'list_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-update-error', 'There was an error updating the list.', 'error' );
        }
        if ( 'list_delete_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-list-delete-error', 'There was an error deleting the list.', 'error' );
        }
        if ( 'welcome_insert_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-welcome-insert-error', 'There was an error creating the welcome message.', 'error' );
        }
        if ( 'welcome_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-welcome-update-error', 'There was an error updating the welcome message.', 'error' );
        }
        if ( 'integrated_insert_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-integrated-insert-error', 'There was an error integrating an autoresponder.', 'error' );
        }
        if ( 'integrated_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-integrated-update-error', 'There was an error updating the autoresponder integration.', 'error' );
        }
        if ( 'button_updated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-button-updated', 'The button has been successfully updated.', 'updated' );
        }
        if ( 'button_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-button-update-error', 'There was an error updating the button.', 'error' );
        }


        /*----------------------------------------------------------------------------*
         * Campaign notices
         *----------------------------------------------------------------------------*/
        if ( 'campaign_inserted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-inserted', 'The campaign has been successfully created.', 'updated' );
        }
        if ( 'campaign_updated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-updated', 'The campaign has been successfully updated.', 'updated' );
        }
        if ( 'campaign_deleted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-deleted', 'The campaign has been successfully deleted.', 'updated' );
        }
        if ( 'campaign_insert_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-insert-error', 'There was an error creating the campaign.', 'error' );
        }
        if ( 'campaign_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-update-error', 'There was an error updating the campaign.', 'error' );
        }
        if ( 'campaign_delete_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaign-delete-error', 'There was an error deleting the campaign.', 'error' );
        }

        /*----------------------------------------------------------------------------*
         * Follow-up sequence notices
         *----------------------------------------------------------------------------*/
        if ( 'sequence_inserted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-inserted', 'The sequence message has been successfully added.', 'updated' );
        }
        if ( 'sequence_updated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-updated', 'The sequence message has been successfully updated.', 'updated' );
        }
        if ( 'sequence_deleted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-deleted', 'The sequence message has been successfully deleted.', 'updated' );
        }
        if ( 'sequence_insert_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-insert-error', 'There was an error adding the sequence message.', 'error' );
        }
        if ( 'sequence_update_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-update-error', 'There was an error updating the sequence message.', 'error' );
        }
        if ( 'sequence_delete_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-sequence-delete-error', 'There was an error deleting the sequence message.', 'error' );
        }

        /*----------------------------------------------------------------------------*
         * Notification notices
         *----------------------------------------------------------------------------*/
        if ( 'notification_sent' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-sent', 'The notifications have been successfully sent.', 'updated' );
        }
        if ( 'notification_sent_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-sent-error', 'There was an error sending the notifications.', 'error' );
        }
        if ( 'notification_autorun_off' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-autorun-off', 'System delivery is disabled (see Settings). Your broadcast notifications have been queued and will be sent once delivery is enabled.', 'updated' );
        }
        if ( 'notification_proc_busy' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-proc-busy', 'Server currently busy processing other notifications. Your broadcast notifications have been queued and will be sent soon.', 'updated' );
        }

        /*----------------------------------------------------------------------------*
         * Subscriber notices
         *----------------------------------------------------------------------------*/
        if ( 'subscriber_deleted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-deleted', 'The subscriber has been successfully deleted.', 'updated' );
        }
        if ( 'subscriber_activated' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-activated', 'The subscriber has been successfully activated.', 'updated' );
        }
        if ( 'subscriber_delete_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-delete-error', 'There was an error deleting the subscriber.', 'error' );
        }
        if ( 'subscriber_activate_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-activate-error', 'There was an error activating the subscriber.', 'error' );
        }
        if ( 'subscriber_import_success' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-import-success', 'The import has successfully completed.', 'updated' );
        }
        if ( 'subscriber_invalid_csv' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-invalid-csv', 'Import error. You must specify a valid CSV file to import.', 'error' );
        }
        if ( 'subscriber_invalid_fb_list' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-invalid-fb-list', 'Import error. You must specify a valid FB List to import into.', 'error' );
        }
        if ( 'subscriber_duplicate_csv' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-duplicate-csv', 'Import error. You cannot assign multiple CSV fields to the same subscriber field.', 'error' );
        }
        if ( 'subscriber_import_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscriber-import-error', 'Import error. One or more records failed to import.', 'error' );
        }

        /*----------------------------------------------------------------------------*
         * Log notices
         *----------------------------------------------------------------------------*/
        if ( isset( $_GET['rsm-message'] ) && 'notification_deleted' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-deleted', 'The notification has been deleted.', 'updated' );
        }

        if ( isset( $_GET['rsm-message'] ) && 'notification_delete_error' == $_GET['rsm-message'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notification-delete-error', 'There was an error deleting the notification.', 'error' );
        }
    }

    // Check for action notices
    if ( isset( $_GET['action'] ) ) {

        // Only display admin notices on RSM pages
        if ( ! rsm_is_admin_page() ) return;

        if ( 'campaigns_bulk_delete' == $_GET['action'] ) {
            add_settings_error( 'rsm-notices', 'rsm-campaigns-deleted', 'The campaign(s) have been successfully deleted.', 'updated' );
        }
        if ( 'subscribers_bulk_delete' == $_GET['action'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscribers-deleted', 'The subscriber(s) have been successfully deleted.', 'updated' );
        }
        if ( 'subscribers_bulk_activate' == $_GET['action'] ) {
            add_settings_error( 'rsm-notices', 'rsm-subscribers-activated', 'The subscriber(s) have been successfully activated.', 'updated' );
        }
        if ( 'subscribers_notifications_delete' == $_GET['action'] ) {
            add_settings_error( 'rsm-notices', 'rsm-notifications-delete', 'The notification(s) have been successfully deleted.', 'updated' );
        }
        //bulk-delete-log
    }

    settings_errors( 'rsm-notices' );
}
add_action( 'admin_notices', 'rsm_admin_messages' );

/**
 * Handles the display of getting started "welcome" notice.
 *
 * @since 1.0
 * @return void
 */
function rsm_getting_started_notice() {
	// If no FB lists exist, provide user instructions on how to get started
	if( get_transient( 'rsm_sn_no_lists' ) ){
		?>
		<div class="callout callout-info flat rsm-welcome-notice">
			<h4>Welcome!</h4>
			<p>To get started using Social Conversion, please:</p>
			<ol>
				<li><a href="admin.php?page=social-conversion-settings&rsm-tab=ar">Configure your email autoresponder</a>&nbsp;&nbsp;( <i class="fa fa-long-arrow-right"></i> <a href="<?php echo RSM_USER_GUIDE_URL_RAW; ?>#heading=h.4ei8cqfb9cw9" target="_blank">help</a> )</li>
				<li><a href="admin.php?page=social-conversion-settings&rsm-tab=apps">Setup your first Facebook list</a>&nbsp;&nbsp;( <i class="fa fa-long-arrow-right"></i> <a href="<?php echo RSM_USER_GUIDE_URL_RAW; ?>#heading=h.cd0c0lmpzyhr" target="_blank">help</a> )</li>
			</ol>
			<p>Instructions can be found by clicking a help link above. If you need further assistance, please open a <a href="http://support.newexpanse.com" target="_blank">support ticket</a>. <i class="fa fa-smile-o"></i></p>
		</div>
		<?php
	}
}
