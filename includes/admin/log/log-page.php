<?php
/**
 * Log Page
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
 * Renders the notification log page.
 *
 * @since 1.0
 * @return void
 */
function rsm_log_page() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/log/class-delivery-log-table.php';

    // Create instance of our table class and prepare data
    $log_list_table = new RSM_Delivery_Log_Table();
    $log_list_table->prepare_items();
    ?>

    <div class="wrap rsm-bs wrapper log-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'log' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <div class="box box-rsm-slate box-solid flat">
                            <div class="box-header with-border">
                                <i class="fa fa-book"></i>
                                <h3 class="box-title">Delivery Log</h3>
                            </div>
                            <div class="box-body table-responsive">
                                <form id="rsm-log-filter" class="form-inline" method="get" action="<?php echo admin_url( 'admin.php?page=social-conversion-log' ); ?>">
                                    <?php
                                        $log_list_table->search_box( 'Search', 'rsm-log' );
    $log_list_table->date_box();
    $log_list_table->views();
    $log_list_table->display();
    ?>
                                    <input type="hidden" name="page" value="social-conversion-log" />
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
