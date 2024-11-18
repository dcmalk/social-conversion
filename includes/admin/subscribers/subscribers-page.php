<?php
/**
 * Subscribers Page
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
 * Renders the Subscribers page
 *
 * @since 1.0
 * @return void
*/
function rsm_subscribers_page() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/subscribers/class-subscribers-table.php';

    // Create instance of our table class and prepare data
    $subscribers_table = new RSM_Subscribers_Table();
    $subscribers_table->prepare_items();
	?>

    <div class="wrap rsm-bs wrapper subscribers-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'subscribers' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <div class="box box-rsm-slate box-solid flat">
                            <div class="box-header with-border">
                                <i class="fa fa-user"></i>
                                <h3 class="box-title">Subscriber Data</h3>
                                <div class="box-tools pull-right">
                                    <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
                                </div>
                            </div>
                            <div class="box-body table-responsive">
                                <form id="rsm-subscribers-filter" class="form-inline" method="get" action="<?php echo admin_url( 'admin.php?page=social-conversion-subscribers' ); ?>">
                                    <?php
                                        $subscribers_table->search_box( 'Search', 'rsm-subscribers' );
                                        $subscribers_table->views();
                                        $subscribers_table->display();
                                    ?>
                                    <input type="hidden" name="page" value="social-conversion-subscribers" />
                                </form>
                            </div><!-- /.box-body-->
                        </div><!-- /.box -->
                    </div>
                </div>


	            <?php if ( rsm_feature_check( 3 ) ) : ?>
                <div class="row clearfix" id="segment-top">
                    <div class="col-md-12 column">
                        <div class="row clearfix">

	                        <div class="col-offset-md-6 col-md-6 column">
		                        <div class="box box-rsm-slate box-solid flat" <?php if ( isset( $_GET['step'] ) ) echo 'id="import-top"'; ?>>
			                        <div class="box-header with-border">
				                        <i class="fa fa-sign-in"></i>
				                        <h3 class="box-title">Import Subscribers</h3>
				                        <div class="box-tools pull-right">
					                        <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
				                        </div>
			                        </div>
			                        <div class="box-body">
				                        <?php
				                        $subscribers_import = new RSM_Subscribers_Import();
				                        $subscribers_import->display();
				                        ?>
			                        </div><!-- /.box-body-->
		                        </div><!-- /.box -->
	                        </div>

	                        <div class="col-md-6 column">
                                <div class="box box-rsm-slate box-solid flat">
                                    <div class="box-header with-border">
                                        <i class="fa fa-sign-out"></i>
                                        <h3 class="box-title">Export Subscribers</h3>
                                        <div class="box-tools pull-right">
                                            <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <p class="help-block">To export subscribers to a comma delimited CSV file, select the FB List and click the download button.</p>
                                        <p class="help-block">Note that Excel may incorrectly display User's ID in <em>scientific notation</em> due to its length. To fix this, change the User's ID column's format to a <em>number</em> with <em>0 decimal places</em>.</p>
                                        <p><strong>Select FB List:</strong></p>
                                        <form id="subscribers-export-form" class="form-inline" method="post">
                                            <div class="form-group">
                                                <select name="export-list-id" class="form-control flat">
                                                    <option value="0">All Subscribers</option>
                                                    <?php
                                                        $lists = stripslashes_deep( db_get_list_data() );
                                                        if( $lists ) {
                                                            foreach( $lists as $list ) {
                                                                echo '<option value="' . esc_attr( $list['list_id'] ) . '">' . esc_attr( $list['app_name'] ) . '</option>';
                                                            }
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn bg-rsm-slate btn-flat">
                                                <i class="fa fa-download"></i> Download CSV
                                                <input type="hidden" name="rsm-action" value="subscribers_export"/>
                                            </button>
                                        </form>

                                    </div><!-- /.box-body-->
                                </div><!-- /.box -->
                            </div>

                        </div>

                    </div>
                </div>
	            <?php endif; ?>


            </section>

            <?php rsm_admin_pre_footer(); ?>

        </div><!-- .content-wrapper -->
    </div><!-- .wrap .rsm-bs .wrapper -->

	<?php
}
