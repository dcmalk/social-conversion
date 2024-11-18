<?php
/**
 * Dashboard
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders the Dashboard page
 *
 * @since 1.0
 * @return void
*/
function rsm_dashboard_page() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/class-top-perf-table.php';
    require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/class-all-time-perf-table.php';

    // Create instance of our table class and prepare data
    $top_perf_table = new RSM_Top_Perf_table();
    $top_perf_table->prepare_items();

    // Create instance of our table class and prepare data
    $all_time_perf_table = new RSM_All_Time_Perf_Table();
    $all_time_perf_table->prepare_items();
    ?>

	<div class="wrap rsm-bs wrapper dashboard-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'dashboard' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <!-- Quick stat boxes -->
                <div class="row clearfix">
                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-rsm-slate flat">
                            <div class="inner rsm-quick-stats">
                                <table class="wp-list-table fixed">
                                    <thead><tr><th width="55%"><span class="rsm-quick-header">Opt-ins</span></th><th width="45%">&nbsp;</th></tr></thead>
                                    <tbody>
                                    <?php
                                        $stats = db_get_quick_stats();
    if( $stats ) {
        echo '<tr>';
        echo '<td class="text-right">Today</td>';
        echo '<td class="text-center"><span class="rsm-quick-today">' . esc_attr( $stats['day_sub_count'] ) . '</span></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="text-right rsm-nowrap">This Week</td>';
        echo '<td class="text-center">' . esc_attr( $stats['week_sub_count'] ) . '</td>';
        echo '<tr>';
        echo '</tr>';
        echo '<td class="text-right rsm-nowrap">This Month</td>';
        echo '<td class="text-center">' . esc_attr( $stats['month_sub_count'] ) . '</td>';
        echo '</tr>';
    }
    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="icon">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <a class="small-box-footer" href="admin.php?page=social-conversion-subscribers"><span title="Click to view subscriber data" data-toggle="tooltip">Subscribers <i class="fa fa-arrow-circle-right"></i></span></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-rsm-fountain flat">
                            <div class="inner rsm-quick-stats">
                                <table class="wp-list-table fixed">
                                    <thead><tr><th width="55%"><span class="rsm-quick-header">Sent</span></th><th width="45%">&nbsp;</th></tr></thead>
                                    <tbody>
                                    <?php
    if( $stats ) {
        echo '<tr>';
        echo '<td class="text-right">Today</td>';
        echo '<td class="text-center"><span class="rsm-quick-today">' . esc_attr( $stats['day_sent_count'] ) . '</span></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="text-right rsm-nowrap">This Week</td>';
        echo '<td class="text-center">' . esc_attr( $stats['week_sent_count'] ) . '</td>';
        echo '<tr>';
        echo '</tr>';
        echo '<td class="text-right rsm-nowrap">This Month</td>';
        echo '<td class="text-center">' . esc_attr( $stats['month_sent_count'] ) . '</td>';
        echo '</tr>';
    }
    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="icon">
                                <i class="fa fa-envelope"></i>
                            </div>
                            <a class="small-box-footer" href="admin.php?page=social-conversion-log"><span title="Click to view delivery log" data-toggle="tooltip">Delivery Log <i class="fa fa-arrow-circle-right"></i></span></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-rsm-nepal flat">
                            <div class="inner rsm-quick-stats">
                                <table class="wp-list-table fixed">
                                    <thead><tr><th width="55%"><span class="rsm-quick-header">Clicks</span></th><th width="45%">&nbsp;</th></tr></thead>
                                    <tbody>
                                    <?php
    if( $stats ) {
        echo '<tr>';
        echo '<td class="text-right">Today</td>';
        echo '<td class="text-center"><span class="rsm-quick-today">' . esc_attr( $stats['day_click_count'] ) . '</span></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="text-right rsm-nowrap">This Week</td>';
        echo '<td class="text-center">' . esc_attr( $stats['week_click_count'] ) . '</td>';
        echo '<tr>';
        echo '</tr>';
        echo '<td class="text-right rsm-nowrap">This Month</td>';
        echo '<td class="text-center">' . esc_attr( $stats['month_click_count'] ) . '</td>';
        echo '</tr>';
    }
    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="icon" style="margin-top:10px;">
                                <i class="fa fa-mouse-pointer"></i>
                            </div>
                            <a class="small-box-footer" href="admin.php?page=social-conversion-campaigns"><span title="Click to view campaign data" data-toggle="tooltip">Campaigns <i class="fa fa-arrow-circle-right"></i></span></a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-xs-6">
                        <div class="small-box bg-rsm-dark-gray flat">
                            <div class="inner rsm-quick-stats">
                                <table class="wp-list-table fixed">
                                    <thead><tr><th width="50%"><span class="rsm-quick-header">CTR</span></th><th width="50%">&nbsp;</th></tr></thead>
                                    <tbody>
                                    <?php
    if( $stats ) {
        echo '<tr>';
        echo '<td class="text-right">Today</td>';
        echo '<td class="text-center"><span class="rsm-quick-today">' . esc_attr( $stats['day_ctr'] ) . '</span><span class="ordinal">%</span></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td class="text-right rsm-nowrap">This Week</td>';
        echo '<td class="text-center">' . esc_attr( $stats['week_ctr'] ) . '%</td>';
        echo '<tr>';
        echo '</tr>';
        echo '<td class="text-right rsm-nowrap">This Month</td>';
        echo '<td class="text-center">' . esc_attr( $stats['month_ctr'] ) . '%</td>';
        echo '</tr>';
    }
    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="icon" style="margin-top:5px;">
                                <i class="fa fa-bar-chart"></i>
                            </div>
                            <a class="small-box-footer" href="admin.php?page=social-conversion-dashboard#performance-top"><span title="Click to view performance data" data-toggle="tooltip">Performance <i class="fa fa-arrow-circle-right"></i></span></a>
                        </div>
                    </div>
                </div><!-- /.row (Quick stat boxes) -->

                <div class="row clearfix">
                    <div class="col-md-8">

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="info-box flat">
                                <!-- <div class="box box-solid flat"> -->
                                    <div class="box-header" style="padding-bottom: 0;">
                                        <i class="fa fa-area-chart"></i>
                                        <h3 class="box-title">Performance Overview</h3>
                                    </div>
                                    <div class="box-body" style="padding-top: 0px;">
                                        <?php
        // Display Performance Overview graph
        rsm_dashboard_graph();
    ?>
                                    </div><!-- /.box-body-->
                                </div><!-- /.box -->
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-rsm-slate box-solid flat" id="performance-top">
                                    <div class="box-header with-border">
                                        <i class="fa fa-line-chart"></i>
                                        <h3 class="box-title">Top Performing Notifications</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body table-responsive no-padding no-tablenav">
                                        <?php $top_perf_table->display(); ?>
                                    </div><!-- /.box-body-->
                                </div><!-- /.box -->
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-rsm-fountain box-solid flat">
                                    <div class="box-header with-border">
                                        <i class="fa fa-flag-o"></i>
                                        <h3 class="box-title">All Time Performance</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-fountain btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body table-responsive no-padding no-tablenav">
                                        <form id="rsm-dashboard-filter" method="get" action="<?php echo admin_url( 'admin.php?page=social-conversion-dashboard' ); ?>">
                                            <?php $all_time_perf_table->display(); ?>
                                            <input type="hidden" name="page" value="social-conversion-dashboard" />
                                        </form>
                                    </div><!-- /.box-body-->
                                </div><!-- /.box -->
                            </div>
                        </div>


                    </div>

                    <div class="col-md-4">

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-rsm-fountain box-solid flat">
                                    <div class="box-header">
                                        <i class="fa fa-link"></i>
                                        <h3 class="box-title">Quick Opt-in Links</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-fountain btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body table-responsive no-padding no-tablenav">
                                        <?php
    $lists = stripslashes_deep( db_get_list_data() );
    if( $lists ) {
        echo '<table class="table no-border quick-optin-links" width="100%" style="margin-bottom:10px !important;">';
        echo '<thead><tr><th>FB&nbsp;List</th><th style="padding-left:0!important;">Opt-in&nbsp;Link</th></tr></thead>';
        echo '<tbody>';

        foreach( $lists as $list ) {
            echo '<tr>';
            echo '<td style="vertical-align: middle;width:33%;">' . esc_attr( $list['app_name'] ) . '</td>';
            echo '<td style="padding-left:0 !important;">
                                                          <div class="input-group" style="margin: 5px 5px 10px 0;">
                                                               <input class="form-control rsm-select-all rsm-copy-text" type="text" readonly value="'. esc_html( rsm_get_optin_html( $list['optin_url'] ) ) . '">
                                                               <span class="input-group-btn">
                                                                   <button class="btn bg-rsm-fountain btn-flat rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                                                       <i class="fa fa-files-o"></i>
                                                                   </button>
                                                               <span>
                                                          </div>
                                                      </td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<div style="margin:10px;"><p>No Opt-in Links found.</p><p>Go to <a href="?page=social-conversion-settings"><strong>Settings</strong></a> to configure your first Facebook App.</p></div>';
    }
    ?>
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>

                        <?php if ( rsm_feature_check( 2 ) ) : ?>
                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-solid bg-rsm-dark-gray flat">
                                    <div class="box-header">
                                        <i class="fa fa-server"></i>
                                        <h3 class="box-title">System Status</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-dark-gray btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body border-radius-none">
                                        <div class="row rsm-static-info rsm-vertical-align">
                                            <div class="col-xs-5 col-md-5">Delivery system:</div>
                                            <div class="col-md-7">
                                            <?php
            echo ( 1 == rsm_get_option( 'autorun' ) ? '<span class="label label-lg bg-rsm-gray">ENABLED</span>' : '<span class="label label-lg bg-rsm-red">DISABLED</span>' );
                            ?>
                                            </div>
                                        </div>
                                        <div class="row rsm-static-info rsm-vertical-align">
                                            <div class="col-xs-5 col-md-5">Cron status:</div>
                                            <div class="col-md-7">
                                            <!-- empty(last_run) ? never ran : is working; if last_run + 1hr < now(), "cron has been stopped ...") -->
                                            <?php
                                echo ( 0 == get_option( 'rsm_sn_proc_state' ) ? '<span class="label label-lg bg-rsm-gray">IDLE</span>' : '<span class="label label-lg bg-rsm-light-green">RUNNING</span>' );
                            ?>
                                            </div>
                                        </div>
                                        <div class="row rsm-static-info rsm-vertical-align" style="padding-top:10px;">
                                            <div class="col-xs-5 col-md-5">Background tasks:</div>
                                            <div class="col-md-7">
                                                <?php echo ( 'wp' == rsm_get_option( 'cron_type' ) ? 'WordPress Cron' : 'Real Cron' ); ?> Service
                                            </div>
                                        </div>
                                        <div class="row rsm-static-info rsm-vertical-align">
                                            <div class="col-xs-5 col-md-5">First execution:</div>
                                            <div class="col-md-7">
                                                <strong>
                                                <?php
                                    $firstrun = rsm_get_option( 'proc_firstrun' );
                            echo( '0000-00-00 00:00:00' == $firstrun ? 'Hasn\'t run yet'  : rsm_format_datetime_wp_nowrap( get_date_from_gmt( $firstrun ) ) );
                            ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="row rsm-static-info rsm-vertical-align">
                                            <div class="col-xs-5 col-md-5">Last execution:</div>
                                            <div class="col-md-7">
                                                <strong>
                                                <?php
                                $lastrun = rsm_get_option( 'proc_lastrun' );
                            echo( '0000-00-00 00:00:00' == $lastrun ? 'Hasn\'t run yet' : rsm_format_datetime_wp_nowrap( get_date_from_gmt( $lastrun ) ) );
                            ?>
                                                </strong>
                                            </div>
                                        </div>
                                        <div class="row rsm-static-info rsm-vertical-align" style="padding-top:10px;">
                                            <div class="col-xs-5 col-md-5" style="margin-bottom:5px;">Total executions:</div>
                                            <div class="col-md-7 rsm-nowrap">
                                                <span class="rsm-quick-today"><?php echo rsm_get_option( 'proc_totalrun' ); ?></span>
                                                <span class="ordinal"><small>times</small></span>
                                            </div>
                                        </div>
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-solid bg-rsm-slate flat">
                                    <div class="box-header">
                                        <i class="fa fa-th"></i>
                                        <h3 class="box-title">About</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body border-radius-none">
                                        <div class="row rsm-static-info">
                                            <div class="col-md-12">
                                                <span class="rsm-about-header"><?php echo RSM_PLUGIN_NAME . ' <strong>' . rsm_get_edition() . '</strong>'; ?></span><span style="font-size:12px;"> <sub>EDITION</sub></span>
                                            </div>
                                        </div>

	                                    <div class="row rsm-static-info rsm-vertical-align" style="padding-left:10px;">
                                            <div class="col-xs-5 col-md-4">License:</div>
                                            <div class="col-md-7"><?php echo rsm_get_license(); ?></div>
                                        </div>

                                        <div class="row rsm-static-info rsm-vertical-align" style="padding-left:10px;">
                                            <div class="col-xs-5 col-md-4">Version:</div>
                                            <div class="col-md-7"><?php echo RSM_VERSION; ?></div>
                                        </div>

                                        <?php
                                            list( $update_status, $last_checked ) = rsm_get_update_status();
    if ( 'N/A' != $last_checked ) {
        $gmdate       = gmdate( RSM_DATETIME_MYSQL, $last_checked );
        $last_checked = get_date_from_gmt( $gmdate );
    }
    ?>
                                        <div class="row rsm-static-info rsm-vertical-align" style="padding-left:10px;align-items:initial;">
                                            <div class="col-xs-5 col-md-4">Updates:</div>
                                            <div class="col-xs-5 col-md-7"><?php echo $update_status; ?><br/><span style="display:block;font-size:10px;line-height:1.5em;width:215px;">Last checked: <?php echo rsm_format_datetime_wp_nowrap( $last_checked ); ?></span></div>
                                        </div>

                                        <div class="row rsm-static-info rsm-vertical-align" style="padding-left:10px;">
                                            <div class="col-xs-5 col-md-4">Domain:</div>
                                            <div class="col-md-7"><?php echo rsm_get_option( 'fb_app_domain' ); ?></div>
                                        </div>

                                        <form id="deactivate-form" method="post">
                                            <input type="hidden" name="rsm_deactivate_sl"/>
                                            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_sl_nonce' ); ?>"/>
                                            <p style="margin-top:20px;">Note: If you need to release this license key for use on a different site, please <a class="rsm-box-link" href="javascript:void(0);" id="rsm-deactivate-license" title="Click to release license" data-toggle="tooltip">click here</a>.</p>
                                        </form>

                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix">
                            <div class="col-md-12">
                                <div class="box box-solid bg-rsm-nepal flat">
                                    <div class="box-header">
                                        <i class="fa fa-life-ring"></i>
                                        <h3 class="box-title">Contact Support</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-nepal btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body border-radius-none">
                                        <p>Despite our best efforts, sometimes things go wrong. If you are in need of support, kindly open a ticket at our <a class="rsm-box-link" href="http://support.newexpanse.com" title="Click to open support desk. Please attach the system log for any technical issues." data-toggle="tooltip" target="_blank">support desk</a>.</p>
                                        <p>Please attach the system log (below) to your ticket when contacting us. We're here to help!</p>
                                        <form id="log-download-form" method="post">
                                            <input type="hidden" name="rsm-action" value="log_download"/>
                                            <button type="submit" class="btn bg-rsm-dark-nepal btn-flat" title="Click to download" data-toggle="tooltip">
                                                <i class="fa fa-download"></i> Download System Log
                                            </button>
                                        </form>
                                    </div><!-- /.box-body -->
                                </div>
                            </div>
                        </div>


                    </div>
                </div>

            </section>

            <?php rsm_admin_pre_footer(); ?>

        </div><!-- .content-wrapper -->
    </div><!-- .wrap .rsm-bs .wrapper -->

    <?php
}