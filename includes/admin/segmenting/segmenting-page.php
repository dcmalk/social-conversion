<?php
/**
 * Segmenting Page
 *
 * @package     RSM
 * @subpackage  Admin/Help
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the Segmenting page
 *
 * @since 1.0
 * @return void
 */
function rsm_segmenting_page() {
    ?>
    <div class="wrap rsm-bs wrapper segmenting-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'segmenting' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

	            <div class="row clearfix" id="segment-top">
		            <div class="col-md-12 column">
			            <div class="row clearfix">

				            <div class="col-md-12 column">
					            <div class="box box-rsm-slate box-solid flat">
						            <div class="box-header with-border">
							            <i class="fa fa-braille"></i>
							            <h3 class="box-title">Manage Segments</h3>
							            <div class="box-tools pull-right">
								            <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
							            </div>
						            </div>

						            <div class="box-body">

							            <p class="help-block">Segmenting allows you to create a target audience based on specific criteria. Each segment can contain one or more filtering conditions. As you define your segment, the number subscribers who match your criteria will update and display in real-time.</p>
							            <p><strong>Select Segment:</strong></p>

							            <div class="form-inline">
								            <div class="form-group">
									            <select name="segment-list" id="segment-list" class="form-control flat">
										            <?php
										            echo '<option hidden>Select segment...</option>';
										            $segments = stripslashes_deep( db_get_segment_detail( 0 , true ) );
										            if( $segments ) {
											            foreach( $segments as $segment ) {
												            echo '<option value="' . esc_attr( $segment['segment_id'] ) . '" data-name="' . esc_attr( $segment['segment_name'] ) . '" data-match="' . esc_attr( $segment['match_type'] ) . '">' . esc_attr( $segment['app_name'] ) . ' >> ' . esc_attr( $segment['segment_name'] ) . '</option>';
											            }
										            }
										            ?>
									            </select>
								            </div>
								            <button class="page-title-action rsm-add-new" id="edit-segment">
									            <!--<i class="fa fa-pencil-square-o"></i> Edit-->
									            <i class="fa fa-search"></i> View
								            </button>
								            <button class="page-title-action rsm-add-new" id="delete-segment">
									            <i class="fa fa-trash-o"></i> Delete
								            </button>
								            <button class="page-title-action rsm-add-new" id="add-new-segment">
									            <i class="fa fa-plus"></i> Add New
								            </button>
								            <span id="seg-loading" style="display:none;margin-left:10px;"><i class="fa fa-spinner fa-pulse fa-2x"></i></span>
							            </div>

							            <hr>

							            <form class="form-horizontal" method="post" id="segment-form" action="">
								            <div>
									            <h5 class="rsm-help-header" style="margin:0 0 20px 16px;">Segment Details</h5>
									            <?php rsm_get_help_link(); ?>

									            <div>
										            <div class="row clearfix">
											            <div class="col-xs-12">
												            <?php rsm_get_help_text( 'segment_details' ) ?>

												            <div class="form-group rsm-group">
													            <label for="segment-name" class="col-xs-2 col-sm-offset-1 col-lg-offset-0 control-label rsm-label">Segment Name</label>
													            <div class="col-xs-4">
														            <input type="text" class="form-control" id="segment-name" name="segment-name" maxlength="100" />
													            </div>
												            </div>

												            <div class="form-group rsm-group">
													            <label for="segment-list-id" class="col-xs-2 col-sm-offset-1 col-lg-offset-0 control-label rsm-label">FB List</label>
													            <div class="col-xs-4">
														            <select class="form-control" id="segment-list-id" name="segment-list-id">
															            <?php
															            echo '<option hidden>Select FB List...</option>';
															            $lists = stripslashes_deep( db_get_list_data() );
															            if( $lists ) {
																            foreach( $lists as $list ) {
																	            echo '<option value="' . esc_attr( $list['list_id'] ) . '">' . esc_attr( $list['app_name'] ) . '</option>';
																            }
															            }
															            ?>
														            </select>
													            </div>
												            </div>
											            </div>
										            </div>
									            </div>
								            </div>

								            <div>
									            <h5 class="rsm-help-header" style="margin:16px 0 16px 16px;">Matching Criteria</h5>
									            <?php rsm_get_help_link(); ?>
									            <div>
										            <div class="row clearfix">
											            <div class="col-xs-12">
											                <?php rsm_get_help_text( 'segment_criteria' ) ?>
											            </div>
										            </div>
									            </div>
								            </div>

								            <div class="row clearfix">
									            <div class="col-xs-12">
										            <div class="form-group" style="margin:0 0 30px 20px;">
											            <div class="radio">
												            <input type="radio" value="any" name="opt-match" id="opt-match-any" checked="checked">
												            <label class="rsm-label" for="opt-match-any">
													            <strong>Match Any</strong> - Subscribers who match <em>any</em> of the critera will be included
												            </label>
											            </div>
											            <div class="radio">
												            <input type="radio" value="all" name="opt-match" id="opt-match-all">
												            <label class="rsm-label" for="opt-match-all">
													            <strong>Match All</strong> - Subscribers must match <em>all</em> the criteria to be included
												            </label>
											            </div>
										            </div>
									            </div>
								            </div>

								            <div class="row clearfix">
									            <div class="col-xs-12">
										            <div class="col-xs-3 rsm-center">
											            <strong>Field</strong>
										            </div>
										            <div class="col-xs-3 rsm-center">
											            <strong>Rule</strong>
										            </div>
										            <div class="col-xs-4 rsm-center">
											            <strong>Value</strong>
										            </div>
										            <div class="col-xs-2">
										            </div>
									            </div>
								            </div>

								            <div id="seg-controls">
									            <div class="row clearfix entry">
										            <div class="col-xs-12">
											            <div class="rsm-group">
												            <div class="col-xs-3 rsm-center">
													            <select name="fields[0]" class="form-control rsm-segment-field">
														            <option hidden>Select field...</option>
														            <option value="email">Email</option>
														            <option value="first_name">First name</option>
														            <option value="last_name">Last name</option>
														            <option value="gender">Gender</option>
														            <option value="locale">Locale</option>
														            <option value="timezone">Timezone</option>
														            <option value="clicked">Clicked</option>
														            <option value="optin_date">Opt-in date</option>
														            <option value="uid">User ID</option>
													            </select>
												            </div>
											            </div>
											            <div class="rsm-group">
												            <div class="col-xs-3 rsm-center">
													            <select name="rules[0]" class="form-control rsm-segment-rule">
														            <option hidden>Select rule...</option>
														            <option value="is_equal">is equal</option>
														            <option value="is_not_equal">is not equal</option>
														            <option value="contains">contains</option>
														            <option value="does_not_contain">does not contain</option>
														            <option value="is_one_of">is one of</option>
														            <option value="is_not_one_of">is not one of</option>
													            </select>
												            </div>
											            </div>
											            <div class="rsm-group">
												            <div class="col-xs-4 rsm-center">
													            <input name='input-values[0]' class="form-control rsm-segment-input" type="text" maxlength="4096">
													            <select name="combo-values[0]" class="form-control rsm-segment-combo" style="display:none;"></select>
													            <input name="date-values[0]" class="form-control rsm-datepicker rsm-segment-datepicker" placeholder="YYYY-MM-DD" type="text" style="display:none;">
												            </div>
											            </div>
											            <div class="col-xs-2">
												            <div class="btn-group rsm-nowrap">
										                        <span>
										                            <button type="button" class="btn btn-default rsm-delete-rule" disabled><i class="fa fa-trash-o"></i></button>
										                            <button type="button" class="btn btn-default rsm-add-rule"><i class="fa fa-plus"></i></button>
										                        </span>
												            </div>
											            </div>
										            </div>
									            </div>
								            </div>

								            <div class="row clearfix">
									            <div class="center-block" style="width:360px;margin-top:20px;margin-bottom:20px;">
										            <label class="rsm-segment-results"><span id="rsm-recipient-count">0</span> subscribers are targeted by this criteria.</label>
									            </div>
								            </div>

								            <div class="row clearfix">
									            <div class="col-xs-12">
										            <div class="rsm-btn-wrapper">
											            <button id="submit-segment" class="btn bg-rsm-slate btn-flat">
												            Save Segment
												            <!--<input type="hidden" name="rsm-action" value="save_segment"/>-->
											            </button>
										            </div>
									            </div>
								            </div>

								            <div class="row clearfix">
									            <div class="col-xs-10">
										            <div id="segment-results"></div>
									            </div>
								            </div>

							            </form>

						            </div><!-- /.box-body-->
					            </div><!-- /.box -->
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
