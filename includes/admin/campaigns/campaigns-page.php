<?php
/**
 * Campaigns Page
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
 * Handles processing and rendering of the campaigns page.
 *
 * @since 1.0
 * @return void
 */
function rsm_campaigns_page() {
    // If processing notifications, show progress
    if ( isset( $_GET['procn'] ) && ( 1 == $_GET['procn'] ) ) {
        rsm_campaigns_process_notifications();

    // Render the appropriate campaigns page
    } elseif ( isset( $_GET['rsm-action'] ) && ( 'add_campaign' == $_GET['rsm-action'] || 'edit_campaign' == $_GET['rsm-action'] || 'edit_sequence' == $_GET['rsm-action'] ) ) {
        $rsm_mode = ( 'add_campaign' == $_GET['rsm-action'] ) ? 'add' : ( ( 'edit_campaign' == $_GET['rsm-action'] ) ? 'edit' : 'edit-seq' );
        rsm_campaigns_config( $rsm_mode );

    // Render the campaigns table
    } else {
        rsm_campaigns_table();
    }
}

/**
 * Renders the notification processing page.
 *
 * @since 1.0
 * @return void
 */
function rsm_campaigns_process_notifications() {
    ?>
    <div class="wrap rsm-bs wrapper">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'campaigns-procn' ); ?>

            <section class="content">
                <form class="form-horizontal">
                    <ul class="timeline">

                    <?php
                        // Process all queued notifications
                        $result  = rsm_process_notifications( true, 'manual' );
                        $success = false;
                        if ( false === $result ) {
                            $result = 'notification_sent_error';
                        } elseif ( -1 === $result ) {
                            $result = 'notification_autorun_off';
                        } elseif ( -2 === $result ) {
                            $result = 'notification_proc_busy';
                        } else {
                            $result  = 'notification_sent';
                            $success = true;
                        }

                        if ( $success ) {
                            $message = '<a href="admin.php?page=social-conversion-campaigns&rsm-message=' . $result . '" style="text-decoration:underline;">Return to Campaigns page</a>';
                            ?>
                            <li>
                                <i class="fa fa-check bg-rsm-slate"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fa fa-clock-o"></i> <?php echo rsm_get_datetime(); ?></span>
                                    <h3 class="timeline-header no-border">Notification delivery complete!</h3>
                                    <div class="timeline-body">
                                        <p><?php echo $message; ?></p>
                                    </div>
                                </div>
                            </li>
                            <?php
                        }
                    ?>

                    </ul>
                </form>
            </section>

        </div><!-- .content-wrapper -->
    </div><!-- .wrap .rsm-bs .wrapper -->

    <?php

    // If not success, redirect immediately with result message displayed
    if ( ! $success ) {
        rsm_js_redirect( admin_url( 'admin.php?page=social-conversion-campaigns&rsm-message=' . $result ) );    // must JS redirect since headers already sent
        exit;
    }
}

/**
 * Renders the campaigns table page.
 *
 * @since 1.0
 * @return void
 */
function rsm_campaigns_table() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/campaigns/class-campaigns-table.php';

    // Create instance of our table class and prepare data
    $campaigns_list_table = new RSM_Campaigns_Table();
    $campaigns_list_table->prepare_items();
    ?>

    <div class="wrap rsm-bs wrapper campaigns-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'campaigns-table' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <div class="row clearfix">
                    <div class="col-md-12">
                        <div class="box box-rsm-slate box-solid flat">
                            <div class="box-header with-border">
                                <i class="fa fa-bullhorn"></i>
                                <h3 class="box-title">Campaign Data</h3>
                                <!--<div class="box-tools pull-right">
                                    <button data-widget="collapse" class="btn bg-rsm-aqua btn-sm"><i class="fa fa-chevron-down"></i></button>
                                </div>-->
                            </div>
                            <div class="box-body table-responsive">
                                <form id="rsm-campaigns-filter" class="form-inline" method="get" action="<?php echo admin_url( 'admin.php?page=social-conversion-campaigns' ); ?>">
                                    <?php
                                    $campaigns_list_table->search_box( 'Search', 'rsm-campaigns' );
                                    $campaigns_list_table->views();
                                    $campaigns_list_table->display();
                                    ?>
                                    <input type="hidden" name="page" value="social-conversion-campaigns" />
                                </form>
                            </div><!-- /.box-body-->
                        </div><!-- /.box -->
                    </div>
                </div>

            </section>

            <?php rsm_admin_pre_footer(); ?>

        </div><!-- .content-wrapper -->
    </div><!-- .wrap .rsm-bs .wrapper -->

<?php
}

/**
 * Renders the campaigns configuration page.
 *
 * @since 1.0
 * @param string $rsm_mode Specifies add or edit mode
 * @return void
 */
function rsm_campaigns_config( $rsm_mode ) {
    require_once RSM_PLUGIN_DIR . 'includes/admin/campaigns/class-sequence-table.php';

    // Get campaign values
    $values = stripslashes_deep( rsm_get_campaign_values( $rsm_mode ) );
    $campaign_id  = isset( $values['campaign-id'] ) ? $values['campaign-id'] : null;

    // Create instance of our table class and prepare data
    $sequence_table = new RSM_Sequence_Table( $campaign_id );
    $sequence_table->prepare_items();
    ?>

    <div class="wrap rsm-bs wrapper campaigns-config">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'campaigns-' . $rsm_mode ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <form class="form-horizontal" method="post" id="campaigns-form" action="">
                <ul class="timeline">

                    <!-- timeline item -->
                    <li>
                        <i class="fa fa-file-text-o bg-rsm-slate"></i>
                        <div class="timeline-item flat">
                            <h3 class="timeline-header no-border rsm-help-header"><strong>Step 1</strong> - Campaign Details</h3>
                            <?php rsm_get_help_link(); ?>

                            <div class="timeline-body">
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <?php rsm_get_help_text( 'campaign_details' ) ?>

	                                    <div class="form-group rsm-group">
		                                    <label for="campaign-type" class="col-md-3 control-label rsm-label">Campaign type</label>
		                                    <div class="col-md-8 col-lg-7">
			                                    <select class="form-control" name="campaign-type" id="campaign-type" <?php if( 'add' != $rsm_mode ) echo 'disabled'; ?>>
				                                    <?php $campaign_type = isset( $values['campaign-type'] ) ? $values['campaign-type'] : 'I'; ?>
				                                    <option value="I" <?php if( 'I' == $campaign_type ) echo 'selected="selected"'; ?>>Broadcast - Regular</option>
				                                    <option value="L" <?php if( 'L' == $campaign_type ) echo 'selected="selected"'; ?>>Broadcast - Scheduled</option>
				                                    <option value="S" <?php if( 'S' == $campaign_type ) echo 'selected="selected"'; ?>>Sequence</option>
			                                    </select>
			                                    <?php
			                                    if ( 'add' != $rsm_mode ) {
				                                    echo '<input type="hidden" name="campaign-type" value="' . $campaign_type . '"/>';
			                                    }
			                                    ?>
		                                    </div>
	                                    </div>
	                                    <div class="form-group rsm-group">
                                            <label for="campaign-name" class="col-md-3 control-label rsm-label">Campaign name</label>
                                            <div class="col-md-8 col-lg-7">
                                                <input type="text" class="form-control" id="campaign-name" name="campaign-name" <?php if ( isset( $values['campaign-name'] ) ) echo 'value="' . esc_attr( $values['campaign-name'] ) . '"'; ?> />
                                            </div>
                                        </div>
                                        <div class="form-group rsm-group">
                                            <label for="campaign-desc" class="col-md-3 control-label rsm-label">Campaign description</label>
                                            <div class="col-md-8 col-lg-7">
                                                <textarea class="form-control" name="campaign-desc" id="campaign-desc" cols="60" rows="3" maxlength="1024"><?php if ( isset( $values['campaign-desc'] ) ) echo esc_textarea( $values['campaign-desc'] ); ?></textarea>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>
                    </li>
                    <!-- END timeline item -->

                    <!-- timeline item -->
                    <li>
                        <i class="fa fa-bullseye bg-rsm-slate"></i>
                        <div class="timeline-item flat">
                            <h3 class="timeline-header no-border rsm-help-header"><strong>Step 2</strong> - Targeting</h3>
                            <?php rsm_get_help_link(); ?>

                            <div class="timeline-body">
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <?php rsm_get_help_text( 'campaign_segmenting' ) ?>

	                                    <div class="form-group rsm-group">
		                                    <label for="list-id" class="col-md-3 control-label rsm-label">FB List</label>
		                                    <div class="col-md-8 col-lg-7">
			                                    <select class="form-control" name="list-id" id="list-id" style="display:inline-block;" <?php if( 'add' != $rsm_mode && 'S' == $campaign_type ) echo 'disabled'; ?>>
				                                    <?php
				                                    echo '<option hidden>Select FB List...</option>';
				                                    $list_id = $values['list-id'];
				                                    $lists = stripslashes_deep( db_get_list_data() );
				                                    if( $lists ) {
					                                    foreach( $lists as $list ) {
						                                    echo '<option value="' . esc_attr( $list['list_id'] ) . '"' .  ( $list['list_id'] == $list_id  ? ' selected="selected"' : '' ) . '>' . esc_attr( $list['app_name'] ) . '</option>';
					                                    }
				                                    }
				                                    ?>
			                                    </select>
			                                    <span id="seg-loading" style="display:none;"><i class="fa fa-spinner fa-pulse fa-2x"></i></span>
		                                    </div>
	                                    </div>

	                                    <div class="form-group rsm-group">
		                                    <label for="segment-id" class="col-md-3 control-label rsm-label">Segment</label>
		                                    <div class="col-md-8 col-lg-7">
			                                    <select class="form-control" name="segment-id" id="segment-id" <?php if( 'add' != $rsm_mode && 'S' == $campaign_type ) echo 'disabled'; ?>>
				                                    <?php
				                                    echo '<option value="0">All subscribers</option>';
				                                    if ( 'add' != $rsm_mode ) {
					                                    $segment_id = $values['segment-id'];
					                                    $segments   = ( 0 == $list_id ) ? db_get_segment_detail() : db_get_list_segment( $list_id );
					                                    if( $segments ) {
						                                    foreach( $segments as $segment ) {
							                                    echo '<option value="' . esc_attr( $segment['segment_id'] ) . '"' . ( $segment['segment_id'] == $segment_id ? ' selected="selected"' : '' ) . '>' . esc_attr( $segment['segment_name'] ) . '</option>';
						                                    }
					                                    }
				                                    }
				                                    ?>
			                                    </select>
		                                    </div>
	                                    </div>

	                                    <?php
	                                    if ( 'add' != $rsm_mode ) {
		                                    if( 'S' == $campaign_type ) {
			                                    echo '<input type="hidden" name="list-id" value="' . $list_id . '"/>';
			                                    echo '<input type="hidden" name="segment-id" value="' . $segment_id . '"/>';
		                                    }
		                                    echo '<input type="hidden" name="campaign-id" value="' . $campaign_id . '"/>';
	                                    }
	                                    ?>

	                                    <div class="form-group" style="margin-top:20px;">
                                            <label class="col-md-3 control-label">&nbsp;</label>
                                            <div class="col-md-8 col-lg-7">
                                                <label class="rsm-segment-results"><span id="rsm-recipient-count">0</span> subscribers are targeted by this criteria.</label>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </li>
                    <!-- END timeline item -->

                    <!-- timeline item -->
                    <li <?php if ( 'edit-seq' == $rsm_mode && 'S' == $campaign_type ) echo 'id="campaigns-top"'; ?>>
                        <i class="fa fa-pencil bg-rsm-slate"></i>
                        <div class="timeline-item flat">
                            <h3 class="timeline-header no-border rsm-help-header"><strong>Step 3</strong> - Facebook Notification</h3>
                            <?php rsm_get_help_link(); ?>

                            <div class="timeline-body">
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <?php rsm_get_help_text( 'campaign_notifications' ) ?>

                                        <div class="form-group rsm-group">
                                            <label for="message-text" class="col-md-3 control-label rsm-label">Message text</label>
                                            <div class="col-md-8 col-lg-7">
                                                <textarea class="form-control rsm-emoji" name="message-text" id="message-text" cols="60" rows="3" maxlength="180"><?php if ( isset( $values['message-text'] ) ) echo esc_textarea( $values['message-text'] ); ?></textarea>
	                                            <div id="emoji-text-error" style="display:none;">Message text cannot exceed 180 characters.</div>
	                                            <div class="rsm-field-details pull-left rsm-nowrap" style="margin-top:3px;"><em>Shortcodes:&nbsp;</em>
		                                            <a class="btn btn-xs bg-rsm-slate btn-flat rsm-shortcode" data-id="{{first_name}}"><i class="fa fa-sort-asc"></i> <i class="fa fa-1-2x fa-user"></i>&nbsp;&nbsp;<small>FIRST NAME</small></a>
		                                            <a class="btn btn-xs bg-rsm-slate btn-flat rsm-shortcode" data-id="{{last_name}}"><i class="fa fa-sort-desc"></i> <i class="fa fa-1-2x fa-user"></i>&nbsp;&nbsp;<small>LAST NAME</small></a>
		                                            <a class="btn btn-xs bg-rsm-slate btn-flat rsm-shortcode" data-id="{{full_name}}"><i class="fa fa-1-2x fa-user"></i>&nbsp;&nbsp;<small>FULL NAME</small></a>
		                                            <a class="btn btn-xs bg-rsm-slate btn-flat rsm-shortcode" data-id="{{day_of_week}}"><i class="fa fa-1-2x fa-calendar-o"></i>&nbsp;&nbsp;<small>DAY OF WEEK</small></a>
		                                            <a class="btn btn-xs bg-rsm-slate btn-flat rsm-shortcode" data-id="{{date}}"><i class="fa fa-1-2x fa-calendar"></i>&nbsp;&nbsp;<small>DATE</small></a>
	                                            </div>
	                                            <div class="rsm-char-count pull-right"><span id="char-count">180</span> character(s) left</div>
                                            </div>
                                        </div>
                                        <div class="form-group rsm-group">
                                            <label for="redirect-url" class="col-md-3 control-label rsm-label">Redirect URL</label>
                                            <div class="col-md-8 col-lg-7">
                                                <div class="input-group">
                                                    <span class="input-group-addon" title="URL requires http:// or https://" data-toggle="tooltip"><i class="fa fa-link"></i></span>
                                                    <input class="form-control" type="url" id="redirect-url" name="redirect-url" maxlength="2083" <?php if ( isset( $values['redirect-url'] ) ) echo 'value="' . esc_url( $values['redirect-url'] ) . '"'; ?> />
                                                </div>
                                                <div class="rsm-field-details pull-left" id="rsm-redirect-policy" style="margin-top:4px;display:none;"><i class="fa fa-exclamation-triangle" style="color:#e15554;"></i> Note: While this software is capable of redirecting to a website outside Facebook, we don't recommend it because (1) it's against Facebook policy, (2) it creates a poor user experience and (3) it can affect conversions. <a href="admin.php?page=social-conversion-help#faq-redirect" target="_blank">Click here to read more.</a></div>
                                                <div class="rsm-field-details pull-left" id="rsm-canvas-redirect" style="margin-top:4px;display:none;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Note: Redirecting inside Facebook canvas requires an SSL certificate installed on your website. Additionally, you must specify the URL of a page within this website: <code>https://<?php echo rsm_get_option( 'fb_app_domain' ); ?></code></div>
                                            </div>
                                        </div>
                                        <div class="form-group rsm-group rsm-sequence">
                                            <label for="seq-delay" class="col-md-3 control-label rsm-label">Delay</label>
                                            <div class="col-md-8 col-lg-7">
                                                <div class="input-group" style="margin-top:4px;">
                                                    <label class="inline-label" style="font-weight: 400;">Send <input class="rsm-spinner" type="number" name="seq-delay" value="<?php echo ( isset( $values['seq-delay'] ) ? esc_attr( $values['seq-delay'] ) : 2 ); ?>" min="0" step="1" maxlength="5" /> day(s) after the previous message.</label>
                                                </div>

                                            </div>
                                        </div>

                                    </div>


                                </div>

                            </div>

                        </div>
                    </li>
                    <!-- END timeline item -->

                    <!-- timeline item -->
                    <li>
                        <i class="fa fa-paper-plane-o bg-rsm-slate"></i>
                        <div class="timeline-item flat">
                            <h3 class="timeline-header no-border rsm-help-header"><strong>Step 4</strong> - Submit</h3>
                            <?php rsm_get_help_link(); ?>

                            <div class="timeline-body">
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <?php rsm_get_help_text( 'campaign_submit', false, '', $rsm_mode ) ?>

                                        <div class="form-group">
                                            <div class="col-xs-2 col-sm-2 col-md-3" style="text-align:center;">
                                                <button class="btn bg-rsm-slate btn-flat" id="rsm-btn-submit" name="rsm-btn-submit" type="submit" style="margin-bottom:5px;">
                                                    Send Now
                                                </button>
                                                <input type="hidden" name="rsm-mode" value="<?php echo $rsm_mode; ?>"/>
                                                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_campaign_nonce' ); ?>"/>
                                                <input type="hidden" value="campaign_submit" name="rsm-action" />
                                            </div>
                                            <div class="col-xs-12 col-sm-6 col-md-9">
                                                <div class="row rsm-date-filters rsm-scheduled">
                                                    <div class="col-md-5 col-lg-4 rsm-group">
                                                        <div class="input-group">
                                                            <span class="input-group-addon" title="Date format is YYYY-MM-DD" data-toggle="tooltip"><i class="fa fa-calendar"></i></span>
                                                            <input type="text" name="schedule-date" class="form-control rsm-datepicker" placeholder="YYYY-MM-DD" <?php if ( isset( $values['schedule-date'] ) ) echo 'value="' . esc_attr( $values['schedule-date'] ) . '"'; ?> />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 col-lg-4 rsm-group">
                                                        <div class="input-group">
                                                            <span class="input-group-addon" title="Time format is HH:MM AM/PM" data-toggle="tooltip"><i class="fa fa-clock-o"></i></span>
                                                            <input type="text" name="schedule-time" class="form-control rsm-timepicker" placeholder="HH:MM AM/PM" <?php if ( isset( $values['schedule-time'] ) ) echo 'value="' . esc_attr( $values['schedule-time'] ) . '"'; ?> />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row rsm-date-filters rsm-sequence">
                                                    <div class="col-sm-2 col-md-3" style="text-align:left;">
                                                        <button class="btn bg-rsm-slate btn-flat" id="rsm-btn-add-more" name="rsm-btn-add-more" type="submit">
                                                            Save & Add More
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="rsm-current-time">Current local time: <code><?php echo /*'June 30, 2016 11:28 am'; */ rsm_get_datetime( rsm_datetime_wp_format() ); ?></code></div>

                                            </div>

                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </li>
                    <!-- END timeline item -->
                </ul>
                </form>

                <form method="get" id="rsm-sequence-filter" class="rsm-sequence" action="<?php echo admin_url( 'admin.php?page=social-conversion-campaigns' ); ?>">
                    <ul class="timeline">
                    <li>
                        <i class="fa fa-list-ol bg-rsm-gray"></i>
                        <div class="timeline-item flat">
                            <h3 class="timeline-header no-border bg-rsm-gray">Sequence Summary</h3>
                            <div class="timeline-body no-tablenav">
                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <!--<p>Each list's follow-up sequence is inactive by default. Upon activation, the prewritten notifications will be queued for current and future subscribers. Once active, follow-up sequences cannot be set inactive; however, they can be deleted, edited and added to. Changing the order of an active follow-up sequence (by deleting or editing) will only affect future subscribers.</p>-->
                                        <?php $sequence_table->display(); ?>
                                        <input type="hidden" name="page" value="social-conversion-campaigns" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                </form>

            </section>

        </div><!-- .content-wrapper -->
    </div><!-- .wrap .rsm-bs .wrapper -->

<?php
}

/**
 * Gets campaign and summary values from either http variables or database.
 *
 * @since 1.0
 * @param string $rsm_mode Specifies add or edit mode
 * @return array $values Array of campaign and summary values
 */
function rsm_get_campaign_values( $rsm_mode ){
    // Gets values based on rsm-action
    if ( 'add' == $rsm_mode ) {
        $values = array(
            'list-id'             => isset( $_POST['list-id'] )             ? $_POST['list-id']             : null,
            'campaign-name'       => isset( $_POST['campaign-name'] )       ? $_POST['campaign-name']       : null,
            'campaign-desc'       => isset( $_POST['campaign-desc'] )       ? $_POST['campaign-desc']       : null,
            'campaign-type'       => isset( $_POST['campaign-type'] )       ? $_POST['campaign-type']       : null,
            'message-text'        => isset( $_POST['message-text'] )        ? $_POST['message-text']        : null,
            'redirect-url'        => isset( $_POST['redirect-url'] )        ? $_POST['redirect-url']        : null,
            'redirect-type'       => isset( $_POST['redirect-type'] )       ? $_POST['redirect-type']       : null,
            'schedule-date'       => isset( $_POST['schedule-date'] )       ? $_POST['schedule-date']       : null,
            'schedule-time'       => isset( $_POST['schedule-time'] )       ? $_POST['schedule-time']       : null,
            'seq-delay'           => isset( $_POST['seq-delay'] )           ? $_POST['seq-delay']           : null,
            'segmented'           => isset( $_POST['segmented'] )           ? $_POST['segmented']           : null,
            'segment-gender'      => isset( $_POST['segment-gender'] )      ? $_POST['segment-gender']      : null,
            'segment-locale'      => isset( $_POST['segment-locale'] )      ? $_POST['segment-locale']      : null,
            'segment-action'      => isset( $_POST['segment-action'] )      ? $_POST['segment-action']      : null,
            'segment-uid'         => isset( $_POST['segment-uid'] )         ? $_POST['segment-uid']         : null,
            'segment-before-date' => isset( $_POST['segment-before-date'] ) ? $_POST['segment-before-date'] : null,
            'segment-after-date'  => isset( $_POST['segment-after-date'] )  ? $_POST['segment-after-date']  : null
        );

    } elseif ( 'edit' == $rsm_mode || 'edit-seq' == $rsm_mode ) {
        // If edit action, verify campaign ID
        if ( ! isset( $_GET['campaign-id'] ) || ! is_numeric( $_GET['campaign-id'] ) ) {
            wp_die( 'Edit campaign error: No campaign ID.', 'Error' );
        }

        // Get campaign values from db
        $campaign_id = (int) $_GET['campaign-id'];
        $campaign    = db_get_campaign_row( $campaign_id );
        if ( false == $campaign ) {
            wp_die( 'Edit campaign error: No campaign data.', 'Error' );
        }
        $values = array(
            'list-id'             => $campaign['list_id'],
            'segment-id'          => $campaign['segment_id'],
            'campaign-id'         => $campaign['campaign_id'],
            'campaign-name'       => $campaign['campaign_name'],
            'campaign-desc'       => $campaign['campaign_desc'],
            'campaign-type'       => $campaign['type']
        );

        // Process batch campaign edits
        if ( ( 'I' == $campaign['type'] ) || ( 'L' == $campaign['type'] ) ) {

            // Get batch summary data
            $summary = db_get_summary_data( $campaign_id );
            if ( false == $summary ) {
                wp_die( 'Edit campaign error: No summary data.', 'Error' );
            }

            $values['summary-id']    = $summary[0]['summary_id'];
            $values['message-text']  = $summary[0]['message'];
            $values['redirect-url']  = $summary[0]['redirect_url'];
            $values['redirect-type'] = $summary[0]['redirect_type'];
            $values['schedule-date'] = rsm_format_datetime( $summary[0]['schedule_date'], RSM_DATE_OUTPUT );
            $values['schedule-time'] = rsm_format_datetime( $summary[0]['schedule_date'], RSM_TIME_OUTPUT );

        // Process follow-up sequence campaign edits
        } elseif ( 'S' == $campaign['type'] && ( 'edit-seq' == $rsm_mode ) ) {
            // If edit sequence action, verify summary ID
            if ( ! isset( $_GET['summary-id'] ) || ! is_numeric( $_GET['summary-id'] ) ) {
                wp_die( 'Edit campaign error: No summary ID.', 'Error' );
            }

            // Get sequence data
            $summary = db_get_summary_row( $_GET['summary-id'] );
            if ( false == $summary ) {
                wp_die( 'Edit campaign error: No sequence data.', 'Error' );
            }

            $values['summary-id']    = $summary['summary_id'];
            $values['message-text']  = $summary['message'];
            $values['redirect-url']  = $summary['redirect_url'];
            $values['redirect-type'] = $summary['redirect_type'];
            $values['seq-delay']     = $summary['delay'];
        }
    }
    return $values;
}
