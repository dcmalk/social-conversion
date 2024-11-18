<?php
/**
 * AJAX Callback Functions
 *
 * @package     RSM
 * @subpackage  Admin/AJAX
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handle the AJAX call to force processing of notifications manually.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_force_process() {
    // Security check
    check_ajax_referer( 'rsm_ajax_nonce', 'security' );

    // Process notifications manually
    $result = rsm_process_notifications( false, 'manual' );
    $response = ( false !== $result && $result >= 0 );

    // Return response
    $response = array( 'success' => true,
                       'data'    => $response
    );
    wp_send_json( $response );
}
add_action( 'wp_ajax_rsm_ajax_force_process', 'rsm_ajax_force_process' );

/**
 * Handle the AJAX call to calculate a segment count in real-time.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_segment_count() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );

	$data = isset( $_POST['data'] ) ? $_POST['data'] : null;

	// Get segment count
    $count = $data ? stripslashes_deep( db_get_segment_data( $data, true ) ) : 0;

    // Return response
    $response = array( 'success' => true,
                       'data'    => strval( ( int ) $count )
    );
    wp_send_json( $response );
}
add_action( 'wp_ajax_rsm_ajax_segment_count', 'rsm_ajax_segment_count' );

/**
 * Handle the AJAX call to get a segment's details.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_get_segment_detail() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );
	$segment_id = isset( $_POST['segment-id'] ) ? $_POST['segment-id'] : null;
	$list_id    = isset( $_POST['list-id'] )    ? $_POST['list-id']    : null;

	if ( $segment_id ) $detail = db_get_segment_detail( $segment_id );
	if ( $list_id ) $detail = db_get_list_segment( $list_id );

	if ( $detail ) {
		// Return response
		$response = array( 'success' => true,
		                   'data'    => $detail
		);
		wp_send_json( $response );

	} else {
		wp_send_json_error( "There was an error retrieving segment details." );
	}
}
add_action( 'wp_ajax_rsm_ajax_get_segment_detail', 'rsm_ajax_get_segment_detail' );

/**
 * Handle the AJAX call to get a segment's details.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_delete_segment() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );
	$segment_id = isset( $_POST['segment-id'] ) ? $_POST['segment-id'] : null;

	$results = db_delete_segment( $segment_id );

	if ( false !== $results ) {
		wp_send_json_success( "The segment was successfully deleted." );
	} else {
		wp_send_json_error( "There was an error deleting the segment." );
	}
}
add_action( 'wp_ajax_rsm_ajax_delete_segment', 'rsm_ajax_delete_segment' );

/**
 * Handle the AJAX call to connect an autoresponder.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_ar_connect() {
    // Security check
    check_ajax_referer( 'rsm_ajax_nonce', 'security' );

    try {
        // Create our autoresponder object
         $ar = RSM_Autoresponder::get_instance( array(
            'ar_name' => isset( $_POST['ar_name'] ) ? $_POST['ar_name']         : null,
            'api_key' => isset( $_POST['api_key'] ) ? trim( $_POST['api_key'] ) : null,
            'options' => isset( $_POST['options'] ) ? $_POST['options']         : null
        ) );

        // Validate key
        if ( ! $ar->is_valid() ) wp_send_json_error( "Error validating API key." );
        if ( ! $ar->save_autoresponder_db() ) wp_send_json_error( "Error saving autoresponder data." );

        // Get lists
        if ( ! $ar->get_lists_api() ) wp_send_json_error( "Error retrieving autoresponder lists. Please verify that you have one or more lists setup." );
        if ( ! $ar->save_lists_db() ) wp_send_json_error( "Error saving list data." );

        $response = array( 'success' => true,
                           'data'    => "Autoresponder connected and settings successfully saved.",
                           'html'    => $ar->get_lists_html()
                    );
        unset( $ar );
        wp_send_json( $response );

    } catch ( Exception $e ) {
        rsm_exception_handler( $e );
        wp_send_json_error( "Error connecting autoresponder. Please verify your settings." );
    }
}
add_action( 'wp_ajax_rsm_ajax_ar_connect', 'rsm_ajax_ar_connect' );

/**
 * Handle the AJAX call to disconnect an autoresponder.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_ar_disconnect() {
    // Security check
    check_ajax_referer( 'rsm_ajax_nonce', 'security' );
    $ar_name = isset( $_POST['ar_name'] ) ? $_POST['ar_name'] : null;

    // Disconnect
    if ( db_update_ar_disconnect( $ar_name ) ) {
        wp_send_json_success( "Autoresponder successfully disconnected." );
    } else {
        wp_send_json_error( "There was an error disconnecting the autoresponder." );
    }
}
add_action( 'wp_ajax_rsm_ajax_ar_disconnect', 'rsm_ajax_ar_disconnect' );

/**
 * Handle the AJAX call to get lists from a connected autoresponder.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_ar_get_lists() {
    // Security check
    check_ajax_referer( 'rsm_ajax_nonce', 'security' );

    try {
        // Create our autoresponder object
	    $ar = RSM_Autoresponder::get_instance( array(
		    'ar_name' => isset( $_POST['ar_name'] ) ? $_POST['ar_name'] : null
	    ) );

        // Get lists
        if ( ! $ar->get_lists_api() ) wp_send_json_error( "Error retrieving autoresponder lists." );
        if ( ! $ar->save_lists_db() ) wp_send_json_error( "Error saving list data." );

        // Get lists in html
        $response = array( 'success' => true,
                           'data'    => "List updated successfully.",
                           'html'    => $ar->get_lists_html()
        );
        unset( $ar );
        wp_send_json( $response );

    } catch ( Exception $e ) {
        rsm_exception_handler( $e );
        wp_send_json_error( "Errors have occurred and the system cannot continue." );
    }
}
add_action( 'wp_ajax_rsm_ajax_ar_get_lists', 'rsm_ajax_ar_get_lists' );

/**
 * Handle the AJAX call to fetch the campaign view for a specific campaign ID.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_get_campaign_view() {
    // Security check
    check_ajax_referer( 'rsm_ajax_nonce', 'security' );

    // Get campaign data
    $campaign_id = absint( $_REQUEST['campaign-id'] );
    $campaign    = stripslashes_deep( db_get_campaign_row( $campaign_id ) );

    // Render campaign data, if available
    if( $campaign ) {

        // Prepare data
        $list_id     = isset( $campaign['list_id'] )    ? $campaign['list_id']    : 0;
	    $segment_id  = isset( $campaign['segment_id'] ) ? $campaign['segment_id'] : 0;
        $type        = isset( $campaign['type'] )       ? $campaign['type']       : '';

        $list          = stripslashes_deep( db_get_list_row( $list_id ) );
        $summary       = stripslashes_deep( db_get_summary_data( $campaign_id ) );
	    $segment       = stripslashes_deep( db_get_segment_detail( $segment_id ) );

        $campaign_type = ( 'I' == $type ) ? 'Broadcast - Instant' : ( ( 'L' == $type ) ? 'Broadcast - Scheduled' : ( ( 'S' == $type ) ? 'Sequence' : '' ) );
        $segment_label = ( 0 == (int)$segment_id ) ? 'Send to all subscribers:' : 'Send to a segment:';
        $schedule_date = ( $summary && 'L' == $type ) ? rsm_format_datetime( $summary[0]['schedule_date'], RSM_DATE_OUTPUT ) : '';
        $schedule_time = ( $summary && 'L' == $type ) ? rsm_format_datetime( $summary[0]['schedule_date'], RSM_TIME_OUTPUT ) : '';

        ob_start();
        ?>
        <table class="wp-list-table rsm-tb-table">
	        <thead>
	        <tr>
		        <th style="width:25%;"></th>
		        <th></th>
	        </tr>
	        </thead>
            <tbody>
            <tr>
                <td colspan="2"><h3>Campaign Details</h3></td>
            </tr>
            <tr>
	            <td class="rsm-tb-label">Campaign type</td>
	            <td><input type="text" class="rsm-tb-field" <?php if ( isset( $campaign_type ) ) echo 'value="' . esc_attr( $campaign_type ) . '"'; ?> readonly /></td>
            </tr>
            <tr>
                <td class="rsm-tb-label">Campaign name</td>
                <td><input type="text" class="rsm-tb-field" <?php if ( isset( $campaign['campaign_name'] ) ) echo 'value="' . esc_attr( $campaign['campaign_name'] ) . '"'; ?> readonly /></td>
            </tr>
            <tr>
                <td class="rsm-tb-label" valign="top">Campaign description</td>
                <td><textarea class="rsm-tb-field" rows="1" readonly><?php if ( isset( $campaign['campaign_desc'] ) ) echo esc_textarea( $campaign['campaign_desc'] ); ?></textarea></td>
            </tr>
            </tbody>
        </table>

        <table class="wp-list-table rsm-tb-table">
	        <thead>
	        <tr>
		        <th style="width:25%;"></th>
		        <th></th>
	        </tr>
	        </thead>
            <tbody>
            <tr>
                <td colspan="2"><h3>Targeting</h3></td>
            </tr>
            <tr>
                <td class="rsm-tb-label" colspan="2"><?php echo $segment_label; ?></td>
            </tr>
            <tr>
	            <td class="rsm-tb-label">FB List</td>
	            <td><input type="text" class="rsm-tb-field" <?php if ( isset( $list['app_name'] ) ) echo 'value="' . esc_attr( $list['app_name'] ) . '"'; ?> readonly /></td>
            </tr>

            <?php if ( 0 < (int)$segment_id ) { ?>
                <tr>
                    <td class="rsm-tb-label">Segment</td>
                    <td><input type="text" class="rsm-tb-field" <?php if ( isset( $segment[0]['segment_name'] ) ) echo 'value="' . esc_attr( $segment[0]['segment_name'] ) . '"'; ?> readonly /></td>
                </tr>

            <?php } // end if statement ?>

            <?php
            // Render summary data of batch (instant/scheduled) campaigns
            if ( isset( $summary ) && ( 'I' == $type || 'L' == $type ) ) {
                ?>
                <table class="wp-list-table rsm-tb-table">
                    <thead><tr><th style="width:25%;"></th><th></th></tr></thead>
                    <tbody>
                    <tr>
                        <td colspan="2"><h3>Facebook Notification</h3></td>
                    </tr>
                    <tr>
                        <td class="rsm-tb-label" valign="top">Message text</td>
                        <td><textarea class="rsm-tb-field" rows="2" readonly><?php if ( isset( $summary[0]['message'] ) ) echo esc_textarea( $summary[0]['message'] ); ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="rsm-tb-label">Redirect URL</td>
                        <td><input type="text" class="rsm-tb-field" <?php if ( isset( $summary[0]['redirect_url'] ) ) echo 'value="' . esc_url( $summary[0]['redirect_url'] ) . '"'; ?> readonly /></td>
                    </tr>
                    <?php
                    if ( 'L' == $type ) {
                        ?>
                        <tr>
                            <td class="rsm-tb-label">Schedule date</td>
                            <td><input type="text" class="rsm-tb-field" <?php if ( isset( $schedule_date ) && isset( $schedule_time ) ) echo 'value="' . esc_attr( $schedule_date ) . ' '. esc_attr( $schedule_time ) . '"'; ?> readonly /></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                    </tbody>
                </table>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php

        // Render any follow-up sequence summary records
        if ( isset( $summary ) && ( 'S' == $campaign['type'] ) ) {
            echo '<table class="wp-list-table rsm-tb-table"><tbody><tr><td><h3>Sequence Summary</h3></td></tr></tbody></table>';
            echo '<table class="wp-list-table widefat table-hover">';
            echo '<thead><tr><th>No.</th><th>Message</th><th>URL</th><th>Delay</th></tr></thead>';
            echo '<tbody>';

            $msg_no = 0;
            $alt = false;
            foreach ( $summary as $row ) {
                echo '<tr' . ( ( $alt = ! $alt ) ? ' class="alternate"' : '' ) . '>';
                echo '<td align="center" valign="middle">' . esc_attr( $msg_no + 1 ) . '</td>';
                echo '<td>' . esc_attr( $row['message'] ) . '</td>';
                echo '<td>' . esc_url( $row['redirect_url'] ) . '</td>';
                echo '<td>' . esc_attr( $row['delay'] ) . '</td>';
                echo '</tr>';
                $msg_no++;
            }
            echo '</tbody></table><br />';
        }

        $html = ob_get_clean();

    } else {
        $html = '<p>No campaign details found.</p>';
    }

    die( $html );
}
add_action( 'wp_ajax_rsm_ajax_get_campaign_view', 'rsm_ajax_get_campaign_view' );

/**
 * Handle the AJAX call to fetch the campaign view for a specific campaign ID.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_get_subscriber_view() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );

	// Get subscriber data
	$subscriber_id = absint( $_REQUEST['subscriber-id'] );
	$subscriber    = stripslashes_deep( db_get_subscriber_row( $subscriber_id ) );

	// Render subscriber data, if available
	if( $subscriber ) {

		// Prepare data
		$app_name     = isset( $subscriber['app_name'] )     ? $subscriber['app_name']     : '';
		$uid          = isset( $subscriber['uid'] )          ? $subscriber['uid']          : '';
		$full_name    = isset( $subscriber['full_name'] )    ? $subscriber['full_name']    : '';
		$first_name   = isset( $subscriber['first_name'] )   ? $subscriber['first_name']   : '';
		$email        = isset( $subscriber['email'] )        ? $subscriber['email']        : '';
		$link         = isset( $subscriber['link'] )         ? $subscriber['link']         : '';
		$gender       = isset( $subscriber['gender'] )       ? $subscriber['gender']       : '';
		$locale       = isset( $subscriber['locale'] )       ? $subscriber['locale']       : '';
		$timezone     = isset( $subscriber['timezone'] )     ? $subscriber['timezone']     : '';
		$status       = isset( $subscriber['status'] )       ? $subscriber['status']       : '';
		$created_date = isset( $subscriber['created_date'] ) ? $subscriber['created_date'] : '';

		$clean_date   = rsm_valid_date( $created_date )      ? rsm_format_datetime( $created_date, RSM_DATE_OUTPUT_PRETTY ) : '';
		$img_url      = esc_url( 'https://graph.facebook.com/' . $uid . '/picture?type=large' );
		$profile_url  = empty( $link ) ? '#' : esc_url( $link );

		ob_start();
		?>
		<div class="rsm-bs">
			<div class="row clearfix">
				<div class="col-md-12">

					<div class="box-body" style="padding-bottom:0;">
						<img class="rsm-profile-img" src="<?php echo $img_url; ?>" alt="<?php echo esc_attr( $full_name ); ?>">
						<h3 class="text-center"><?php echo esc_attr( $full_name ); ?></h3>
						<p class="text-muted text-center rsm-profile-sub"><i class="fa fa-envelope"></i> &nbsp;<?php echo $email; ?></p>
						<p class="text-muted text-center rsm-profile-sub"><i class="fa fa-clock-o"></i> &nbsp;<?php echo 'Subscriber since ' . esc_attr( $clean_date ); ?></p>
						<p class="text-muted text-center" style="margin:0;"><a href="<?php echo esc_url_raw( $profile_url ); ?>" target="_blank"><span title="Click to view Facebook Profile" data-toggle="tooltip">Facebook profile &nbsp;<i class="fa fa-external-link"></i></span></a></p>
					</div>

					<div class="box box-secondary flat no-border">
						<div class="row clearfix" style="margin-bottom:20px;">
							<div class="col-md-12" style="margin-bottom:10px;">
								<h4>Statistics</h4>
								<?php
									$sub_stats = db_get_subscriber_stats( $subscriber_id );
									$delivered = isset( $sub_stats['delivered'] ) ? $sub_stats['delivered'] : 'N/A';
									$clicked   = isset( $sub_stats['clicked'] )   ? $sub_stats['clicked']   : 'N/A';
									$ctr       = isset( $sub_stats['ctr'] )       ? $sub_stats['ctr']       : 'N/A';
								?>
								<div class="col-md-4">
									<div class="info-box bg-rsm-fountain">
										<span class="info-box-icon"><i class="fa fa-envelope-o"></i></span>
										<div class="rsm-profile-stat-content">
											<div class="rsm-profile-stat-header">Delivered</div>
											<div class="rsm-profile-stat"><?php echo esc_attr( $delivered ); ?></div>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="info-box bg-rsm-fountain">
										<span class="info-box-icon"><i class="fa fa-mouse-pointer"></i></span>
										<div class="rsm-profile-stat-content">
											<div class="rsm-profile-stat-header rsm-nowrap">Unique Clicks</div>
											<div class="rsm-profile-stat"><?php echo esc_attr( $clicked ); ?></div>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="info-box bg-rsm-fountain">
										<span class="info-box-icon"><i class="fa fa-bar-chart"></i></span>
										<div class="rsm-profile-stat-content">
											<div class="rsm-profile-stat-header">CTR</div>
											<div class="rsm-profile-stat"><?php echo esc_attr( $ctr ); ?>%</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>


					<div class="box box-secondary flat no-border">
						<h4>About <?php echo esc_attr( $first_name ); ?></h4>
						<div class="rsm-profile-group">
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">Status</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo ( 'A' == $status ) ? 'Active' : ( ( 'I' == $status ) ? 'Inactive' : 'N/A' );  ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">FB List</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo esc_attr( $app_name ); ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">Gender</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo ( 'male' == $gender ) ? 'Male' : ( ( 'female' == $gender ) ? 'Female' : 'N/A' ); ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">Locale</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo esc_attr( $locale ); ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">Timezone</button>
								<button type="button" disabled="" class="btn btn-default">UTC <?php echo esc_attr( $timezone ); ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">Opt-in Date</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo esc_attr( $created_date ); ?></button>
							</div>
							<div class="btn-group rsm-profile-btn">
								<button type="button" disabled="" class="btn btn-primary">UID</button>
								<button type="button" disabled="" class="btn btn-default"><?php echo esc_attr( $uid ); ?></button>
							</div>
						</div>
					</div>

					<div class="box box-secondary flat no-border">
						<h4>Segments</h4>
						<div class="rsm-profile-group">
							<?php
								$segments = stripslashes_deep( db_get_subscriber_segments( $subscriber_id ) );
								if ( $segments ) {
									foreach ( $segments as $segment ) {
										echo '<span class="label bg-rsm-nepal" style="white-space:normal;">' . $segment['segment_name'] . '</span>&nbsp;';
									}
								}
							?>

						</div>
					</div>

				</div>
			</div>

			<div class="row clearfix">
				<div class="col-md-12">
					<h4>Activity Timeline</h4>

					<!-- The time line -->
					<ul class="timeline" style="margin-top:20px;">

						<!-- timeline item -->
						<li>
							<i class="fa fa-user-plus bg-rsm-fountain"></i>
							<div class="timeline-item">
								<span class="time"><i class="fa fa-clock-o"></i> <?php echo esc_attr( $created_date ); ?></span>
								<h3 class="timeline-header no-border"><?php echo esc_attr( $first_name ) . ' subscribed to FB List: <strong>' . esc_attr( $app_name ) . '</strong>'; ?></h3>
							</div>
						</li>
						<!-- END timeline item -->

						<?php
							$activity = stripslashes_deep( db_get_subscriber_activity( $subscriber_id ) );

							if( $activity ) {
								foreach( $activity as $act ) {
									// Click event
									if ( isset( $act['act_type'] ) ) {
										if ( 'C' == $act['act_type'] ) {
											?>
											<li>
												<i class="fa fa-mouse-pointer bg-rsm-light-green"></i>
												<div class="timeline-item">
													<span class="time"><i class="fa fa-clock-o"></i> <?php echo $act['act_date']; ?></span>
													<h3 class="timeline-header"><?php echo esc_attr( $first_name ) . ' <strong>clicked</strong> ' . esc_attr( $act['act_campaign'] ) . ' <i class="fa fa-long-arrow-right"></i> Notification ID ' . esc_attr( $act['act_id'] ); ?></h3>
												</div>
											</li>
											<?php

										} else {
										// Notification events
											switch ( $act['act_type'] ) {
												case 'I':
													$type = 'Regular broadcast';
													break;
												case 'L':
													$type = 'Scheduled broadcast';
													break;
												case 'S':
													$type = 'Sequence';
													break;
												case 'W':
													$type = 'Welcome';
													break;
											}
											?>
											<li id="act-event-<?php echo esc_attr( $act['act_id'] ); ?>">
												<i class="fa fa-envelope bg-rsm-slate"></i>
												<div class="timeline-item">
													<span class="time"><i class="fa fa-clock-o"></i> <?php echo esc_attr( $act['act_date'] ); ?></span>
													<h3 class="timeline-header">Delivered <strong><?php echo $type; ?></strong> notification</h3>
													<div class="timeline-body"><?php echo esc_attr( $act['act_message'] ); ?>
														<br />
														<a href="<?php echo esc_url_raw( $act['act_url'] ); ?>" target="_blank"><span title="Click to open in a new window" data-toggle="tooltip"><?php echo esc_url( $act['act_url'] ); ?> &nbsp;<i class="fa fa-external-link"></i></span></a>
													</div>
													<div class="timeline-footer">
														<span class="label bg-rsm-fountain"><?php echo esc_attr( $act['act_campaign'] ); ?></span>
														<span class="label bg-rsm-slate">Notification ID <?php echo esc_attr( $act['act_id'] ); ?></span>
													</div>
												</div>
											</li>
											<?php
										}
									}
								}
							}
						?>

					</ul>
				</div>
				<!-- /.col -->
			</div>

		</div>

		<?php
		$html = ob_get_clean();

	} else {
		$html = '<p>No subscriber details found.</p>';
	}

	die( $html );
}
add_action( 'wp_ajax_rsm_ajax_get_subscriber_view', 'rsm_ajax_get_subscriber_view' );

/**
 * Handle the AJAX call to fetch all locales.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_get_locale() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );

	// Get locale data
	$locales = stripslashes_deep( db_get_locale() );

	// Return response
	$response = array( 'data' => $locales );
	wp_send_json( $response );
}
add_action( 'wp_ajax_rsm_ajax_get_locale', 'rsm_ajax_get_locale' );

/**
 * Handle the AJAX call to fetch all timezones.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_get_timezone() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );

	// Get timezone data
	$timezones = stripslashes_deep( db_get_timezone() );

	// Return response
	$response = array( 'data' => $timezones );
	wp_send_json( $response );
}
add_action( 'wp_ajax_rsm_ajax_get_timezone', 'rsm_ajax_get_timezone' );

/**
 * Handle the AJAX call for saving/updating segment.
 *
 * @since 1.0
 * @return void
 */
function rsm_ajax_save_segment() {
	// Security check
	check_ajax_referer( 'rsm_ajax_nonce', 'security' );

	$mode = isset( $_POST['mode'] ) ? trim( $_POST['mode'] )              : null;
	$data = isset( $_POST['data'] ) ? stripslashes_deep( $_POST['data'] ) : null;

	// Insert or update, depending on mode; otherwise, report error
	if ( $mode && $data ) {
		if ( "add" == $mode ) {
			$segment_id = db_insert_segment( $data );
			if ( false == $segment_id ) wp_send_json_error( "There was an error saving the segment." );

		} elseif ( "update" == $mode ) {
			$segment_id = db_update_segment( $data );
			if ( false == $segment_id ) wp_send_json_error( "There was an error updating the segment." );
		}

		$response = array( 'success'   => true,
						   'segmentId' => $segment_id,
		                   'data'      => "The segment was successfully saved." );
		wp_send_json( $response );

	} else {
		wp_send_json_error( "Invalid data. There was an error saving the segment." );
	}
}
add_action( 'wp_ajax_rsm_ajax_save_segment', 'rsm_ajax_save_segment' );


