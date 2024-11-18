<?php
/**
 * Notification Functions
 *
 * @package     RSM
 * @subpackage  Functions/Notifications
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sends all queued notifications and processes the results
 *
 * @since 1.0
 * @param bool $show_progress If true, outputs a progress message
 * @param string $trigger_type Indicates whether routine is triggered by WP cron, real cron, manual or duplicate delayed
 * @return mixed Int containing number of sent notifications, -1 if autorun disabled, -2 if processor busy, otherwise false
 */
function rsm_process_notifications( $show_progress = false, $trigger_type = null ) {
    // Check if autorun is enabled or disbabled
    $autorun = rsm_get_option( 'autorun' );
    if ( ! $autorun ) {
        rsm_insert_log( 'event', 'The system tried to process notifications and autorun mode is disabled.' );
        return -1;  // autorun disabled
    }

    // Check if system is busy processing notifications
    $processing = rsm_get_processing_state();
    if ( $processing ) {
        rsm_insert_log( 'event', 'The system tried to process notifications and system currently in a busy state.' );
        return -2;  // processor busy
    }

    // If this wasn't called manually or dup delayed, then check cron type against trigger type parameter
	if ( ( 'manual' != $trigger_type ) && ( 'delayed' != $trigger_type ) ) {
        $cron_type = rsm_get_option( 'cron_type' );
        if ( $cron_type != $trigger_type ) {
            rsm_insert_log( 'event', 'The system tried to process notifications but was triggered by the wrong type of cron service.' );
            return -3;  // cron trigger mismatch
        }
    }

    // Update processing data
    $datetime_gmt = rsm_get_datetime_gmt();
    if ( '0000-00-00 00:00:00' == rsm_get_option( 'proc_firstrun' ) ) {
        rsm_update_option( 'proc_firstrun', $datetime_gmt );
    }
    update_option( 'rsm_sn_proc_state', 1, 'no' );
    rsm_update_option( 'proc_lastrun', $datetime_gmt );
    rsm_update_option( 'proc_totalrun', ( rsm_get_option( 'proc_totalrun' ) + 1 ) );

    // Get queued notifications
    $queued_items = stripslashes_deep( db_get_queued_notifications( $datetime_gmt ) );
    $sent_items   = 0;
    $batch_no     = 0;

	// Facebook sometimes has trouble processing batch notifications to the same user, so if duplicates are found,
	// we should remove them and trigger the notification processer to re-run in 30 seconds
	/*$dups = false;
	$temp_array = array();
	foreach ( $queued_items as &$v ) {
		if ( ! isset( $temp_array[ $v['subscriber_id'] ] ) ) {
			$temp_array[ $v['subscriber_id'] ] =& $v;
		} else {
			$dups = true;
		}
	}
	$queued_items = $temp_array;*/

    if ( $queued_items ) {
        // Build an organized array by list ID (as each list has its own app id/secret)
        $batches = array();
        foreach ( $queued_items as $item ) {
            if ( ! array_key_exists( $item['list_id'], $batches ) ) {
                $batches[ $item['list_id'] ] = array( 'list_id'         => $item['list_id'],
                                                      'app_name'        => $item['app_name'],
                                                      'app_id'          => $item['app_id'],
                                                      'app_secret'      => $item['app_secret'],
                                                      'notification_id' => $item['notification_id'],
                                                      'redirect_type'   => $item['redirect_type'] );
            }
        }

        if ( $show_progress ) {
            $message= 'Preparing <strong>' . count( $queued_items ) . '</strong> total notifications for delivery...';
            ?>
            <li>
                <i class="fa fa-paper-plane-o bg-rsm-slate"></i>
                <div class="timeline-item">
                    <span class="time"><i class="fa fa-clock-o"></i> <?php echo rsm_get_datetime(); ?></span>
                    <h3 class="timeline-header no-border"><?php echo $message; ?></h3>
                </div>
            </li>
            <?php
        }

        // For each unique list ID, queue those of same list ID and send that group of notifications
        foreach ( $batches as $batch ) {
            // Queue each notification in this batch
            $queue    = array();
            $sent_ids = array();
            $camp_ids = array();
            foreach ( $queued_items as $item ) {
                if ( $batch['list_id'] == $item['list_id'] ) {
                    $queue[] = array( 'uid'      => $item['uid'],
                                      'href'     => '?id=' . $item['list_id'] . '&n=' . $item['notification_id'] . '&rtype=' . $item['redirect_type'],
                                      'template' =>  $item['message'] );
                    $sent_ids[] = $item['notification_id'];
                    $sent_items++;

                    // Only record Instant and Scheduled campaign types for updating
                    if ( 'I' == $item['type'] || 'L' == $item['type'] ) $camp_ids[] = $item['campaign_id'];
                }
            }

            $batch_no++;
            if ( $show_progress ) {
                $message = 'Processing batch #' . $batch_no . ' - <strong>' . $batch['app_name'] . '</strong> [' . count( $queue ) . ' notifications]';
                ?>
                <li>
                    <i class="fa fa-cog fa-spin bg-rsm-slate"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> <?php echo rsm_get_datetime(); ?></span>
                        <h3 class="timeline-header no-border"><?php echo $message; ?></h3>
                        <div class="timeline-body">
                <?php
            }

            // Send queued notifications
            try {
                $rsm_fb = new RSM_Facebook( array( 'app_id' => $batch['app_id'], 'app_secret' => $batch['app_secret'], 'state' => 1 ) );
                $results = $rsm_fb->send_batch_notifications( $queue, 40, $show_progress );
            } catch ( Exception $e ) {
                update_option( 'rsm_sn_proc_state', 0, 'no' );  // false
                rsm_exception_handler( $e );
            }

            // Complete the timeline-body and issue javascript for changing the spinning-cog into a check mark
            if ( $show_progress ) {
                rsm_show_message( '... done!' );
                ?>
                        </div>
                    </div>
                </li>
                <script type="text/javascript">
                    var elems = document.querySelectorAll('.fa-spin'), i;
                    for (i = 0; i < elems.length; i++) {
                        elems[i].className = '';
                    }
                </script>
                <?php
            }

            // If batch process encountered errors, log them
            if ( $results !== true ) {
                $err_ids = rsm_process_notification_errors( $results );
                db_update_notification_status( $err_ids, 'E' );
            }

            // Update notification and campaign status
            $not_ids = isset( $err_ids ) ? array_diff( $sent_ids, $err_ids ) : $sent_ids;
            db_update_notification_status( $not_ids, 'S' );
            db_update_campaign_status( array_unique( $camp_ids ), 'F' );

            // Cleanup
            unset( $rsm_fb );
        }
    }

    // Update processing state
    update_option( 'rsm_sn_proc_state', 0, 'no' );  // false

	// If duplicates were found, reschedule cron to re-run in 30 seconds
	//if ( $dups ) rsm_schedule_dup_delay_cron();

    // Return number of notifications sent
    return $sent_items;
}

/**
 * Determines if system is busy processing notifications. If busy, check
 * if the lastrun time exceeds 30 mins; if so, reset the processing state.
 * This is a preventative measure for the unlikely case of something
 * interrupting the processing and leaving the system in a dead state.
 *
 * @since 1.0
 * @return bool True if busy processing, otherwise false
 */
function rsm_get_processing_state() {
    // Get notification processing state
    $processing = get_option( 'rsm_sn_proc_state' ); // 0=false, 1=true

    // Check if currently processing
    if ( $processing ) {

        // If processing, check if exceeds 30 mins and if so, unlock
        $lastrun_gmt  = rsm_get_option( 'proc_lastrun' );
        $datetime_gmt = rsm_get_datetime_gmt();

        // If lastrun is less than 30 mins, return true (busy processing)
        if ( $lastrun_gmt && ( ( strtotime( $lastrun_gmt ) + 1800 ) > strtotime( $datetime_gmt ) ) ) {
            return true;
        }

        // If no lastrun or has exceed 30 mins, update the options and return
        update_option( 'rsm_sn_proc_state', 0, 'no' );  // false
        rsm_update_option( 'proc_lastrun', $datetime_gmt );
        return false;

    } else {
        // If not processing, return false
        return false;
    }
}
