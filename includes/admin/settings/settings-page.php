<?php
/**
 * Settings Page
 *
 * @package     RSM
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the Lists page.
 *
 * @since 1.0
 * @return void
*/
function rsm_settings_page() {
    // Get RSM tab and settings
    $rsm_tab  = ( isset( $_GET['rsm-tab'] ) && ( in_array( $_GET['rsm-tab'], array( 'apps', 'ar', 'btn', 'auto' ) ) ) ) ? $_GET['rsm-tab'] : 'apps' ;
    $settings = rsm_get_settings();

    // Get autoresponder data and remap array
    $data    = stripslashes_deep( db_get_ar_data() );
    $ar_data = array();
    if ( $data ) {
        foreach ( $data as $ar_name ) {
            $options = unserialize( $ar_name['options'] );
            if ( $options ) {
                $ar_data[ $ar_name['ar_name'] ] = array_merge( $ar_name, $options );
            } else {
                $ar_data[ $ar_name['ar_name'] ] = $ar_name;
            }
        }
    }
    ?>
	<div class="wrap rsm-bs wrapper settings-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'settings' );	?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <div class="box box-rsm-gray box-solid flat">
                    <div class="box-header with-border">
                        <i class="fa fa-cogs"></i>
                        <h3 class="box-title">Manage Settings</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <div class="row clearfix">
                            <div class="col-md-3 col-lg-2">

                                <div class="box box-solid flat" style="box-shadow:0 0 1px rgba(0, 0, 0, 0.1);">
                                    <div class="box-body no-padding">
                                        <ul class="nav nav-pills nav-stacked rsm-pills">
                                            <li <?php echo 'apps'  == $rsm_tab ? 'class="active"' : ''; ?>><a data-toggle="tab" href="#rsm-apps">Facebook Apps</a></li>
                                            <li <?php echo 'ar'    == $rsm_tab ? 'class="active"' : ''; ?>><a data-toggle="tab" href="#rsm-ar">Email Integrations</a></li>
	                                        <li <?php echo 'btn'   == $rsm_tab ? 'class="active"' : ''; ?>><a data-toggle="tab" href="#rsm-btn">Widget & Buttons</a></li>
                                            <?php if ( rsm_feature_check( 2 ) ) : ?>
                                            <li <?php echo 'auto'   == $rsm_tab ? 'class="active"' : ''; ?>><a data-toggle="tab" href="#rsm-auto">Delivery</a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div><!-- /.box-body -->
                                </div><!-- /. box -->
                            </div>

                            <div class="col-md-9 col-lg-10">
                                <div class="tab-content">
                                    <div id="rsm-apps" class="tab-pane <?php echo 'apps' == $rsm_tab ? 'active' : ''; ?>"><?php rsm_settings_apps_tab( $settings, $ar_data ); ?></div>
                                    <div id="rsm-ar" class="tab-pane <?php echo 'ar' == $rsm_tab     ? 'active' : ''; ?>"><?php rsm_settings_ar_tab( $ar_data ); ?></div>
	                                <div id="rsm-btn" class="tab-pane <?php echo 'btn' == $rsm_tab   ? 'active' : ''; ?>"><?php rsm_settings_buttons_tab( $settings ); ?></div>
                                    <?php if ( rsm_feature_check( 2 ) ) : ?>
                                    <div id="rsm-auto" class="tab-pane <?php echo 'auto' == $rsm_tab ? 'active' : ''; ?>"><?php rsm_settings_automation_tab( $settings ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div><!-- /.box-body -->
                </div>

            </section>

	        <?php rsm_admin_pre_footer(); ?>

        </div><!-- .content-wrapper -->
	</div><!-- .wrap .rsm-bs .wrapper -->

	<?php
}

/**
 * Renders the FB Apps settings tab.
 *
 * @since 1.0
 * @return void
 */
function rsm_settings_apps_tab( $settings, $ar_data ) {
    require_once RSM_PLUGIN_DIR . 'includes/admin/settings/class-lists-table.php';

    // Create instance of our table class and prepare data
    $lists_table   = new RSM_Lists_Table();
    $fb_list_count = (int) $lists_table->prepare_items();

    // Check whether maximum number of FB Lists has been reached
    $id  = (int) rsm_get_option( 'price_id' );
    $max = ( 1 == $id && 1 <= $fb_list_count ) || ( 2 == $id && 10 <= $fb_list_count );

    // Get RSM Mode, values and ar lists
    $rsm_mode = ( isset( $_GET['rsm-action'] ) && ( 'edit_list' == $_GET['rsm-action'] ) ) ? 'edit' : 'add' ;
    $values   = stripslashes_deep( rsm_get_settings_apps_values( $rsm_mode ) );
    $ar_lists = ( 'add' == $rsm_mode ) ? db_get_ar_lists() : db_get_ar_lists( $values['list-id'] );
	$ar_lists = stripslashes_deep( $ar_lists );

    // Determine which autoresponders are connected
    $activecampaign_connected  = ( isset( $ar_data['activecampaign'] )  && ( 'T' == $ar_data['activecampaign']['connected'] ) );
    $aweber_connected          = ( isset( $ar_data['aweber'] )          && ( 'T' == $ar_data['aweber']['connected'] ) );
    $benchmark_connected       = ( isset( $ar_data['benchmark'] )       && ( 'T' == $ar_data['benchmark']['connected'] ) );
    $campaignmonitor_connected = ( isset( $ar_data['campaignmonitor'] ) && ( 'T' == $ar_data['campaignmonitor']['connected'] ) );
    $ctct_connected            = ( isset( $ar_data['ctct'] )            && ( 'T' == $ar_data['ctct']['connected'] ) );
	$convertkit_connected      = ( isset( $ar_data['convertkit'] )      && ( 'T' == $ar_data['convertkit']['connected'] ) );
    $getresponse_connected     = ( isset( $ar_data['getresponse'] )     && ( 'T' == $ar_data['getresponse']['connected'] ) );
    $icontact_connected        = ( isset( $ar_data['icontact'] )        && ( 'T' == $ar_data['icontact']['connected'] ) );
    $infusionsoft_connected    = ( isset( $ar_data['infusionsoft'] )    && ( 'T' == $ar_data['infusionsoft']['connected'] ) );
    $mailchimp_connected       = ( isset( $ar_data['mailchimp'] )       && ( 'T' == $ar_data['mailchimp']['connected'] ) );
	$mailerlite_connected      = ( isset( $ar_data['mailerlite'] )      && ( 'T' == $ar_data['mailerlite']['connected'] ) );
    $sendinblue_connected      = ( isset( $ar_data['sendinblue'] )      && ( 'T' == $ar_data['sendinblue']['connected'] ) );
    $sendreach_connected       = ( isset( $ar_data['sendreach'] )       && ( 'T' == $ar_data['sendreach']['connected'] ) );
    ?>

    <div class="box box-rsm-slate box-solid flat">
        <div class="box-header with-border">
            <i class="fa fa-facebook-official"></i>
            <h3 class="box-title">Configured Facebook Apps</h3>
            <div class="box-tools pull-right">
                <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
            </div>
        </div><!-- /.box-header -->

        <div class="box-body table-responsive">
            <form class="no-tablenav" method="get" action="<?php echo admin_url( 'admin.php?page=social-conversion-settings' ); ?>">
                <?php $lists_table->display(); ?>
                <input type="hidden" name="page" value="social-conversion-settings" />
            </form>
        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="box box-rsm-slate box-solid flat">
        <div class="box-header with-border">
            <i class="fa fa-facebook-official"></i>
            <h3 class="box-title">Facebook App Settings</h3>
            <div class="box-tools pull-right">
                <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
            </div>
        </div><!-- /.box-header -->

        <div class="box-body">
            <form class="form-horizontal" method="post" id="settings-form" action="">
            <ul class="timeline">

                <!-- timeline item -->
                <li <?php if ( 'add' != $rsm_mode) echo 'id="settings-top"'; ?>>
                    <i class="fa fa-file-text-o bg-rsm-slate"></i>
                    <div class="timeline-item flat">
                        <h3 class="timeline-header no-border rsm-help-header"><strong>Step 1</strong> - App Setup</h3>
                        <?php rsm_get_help_link(); ?>

                        <div class="timeline-body">
                            <div class="row clearfix">
                                <div class="col-md-12">
                                    <?php rsm_get_help_text( 'settings_app_details' ) ?>

                                    <div class="form-group rsm-group">
                                        <label for="app-name" class="col-md-3 control-label rsm-label">Display Name</label>
                                        <div class="col-md-8 col-lg-7">
                                            <input type="text" class="form-control" id="app-name" name="app-name" <?php if ( isset( $values['app-name'] ) ) echo 'value="' . esc_attr( $values['app-name'] ) . '"'; ?> maxlength="32" />
                                        </div>
                                    </div>

                                    <div class="form-group rsm-group">
                                        <label for="app-id" class="col-md-3 control-label rsm-label">App ID</label>
                                        <div class="col-md-8 col-lg-7">
                                            <input type="text" class="form-control" id="app-id" name="app-id" <?php if ( isset( $values['app-id'] ) ) echo 'value="' . esc_attr( $values['app-id'] ) . '"'; ?> maxlength="50" />
                                        </div>
                                    </div>

                                    <div class="form-group rsm-group">
                                        <label for="app-secret" class="col-md-3 control-label rsm-label">App Secret</label>
                                        <div class="col-md-8 col-lg-7">
                                            <input type="text" class="form-control" id="app-secret" name="app-secret" <?php if ( isset( $values['app-secret'] ) ) echo 'value="' . esc_attr( $values['app-secret'] ) . '"'; ?> maxlength="50" />
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
                    <i class="fa fa-external-link bg-rsm-slate"></i>
                    <div class="timeline-item flat">
                        <h3 class="timeline-header no-border rsm-help-header"><strong>Step 2</strong> - Opt-in Redirects</h3>
                        <?php rsm_get_help_link(); ?>

                        <div class="timeline-body">
                            <div class="row clearfix">
                                <div class="col-md-12">
                                    <?php rsm_get_help_text( 'settings_optin_redirects' ) ?>

                                    <div class="form-group rsm-group">
                                        <label for="okay-url" class="col-md-3 control-label rsm-label">Okay URL</label>
                                        <div class="col-md-8 col-lg-7">
                                            <div class="input-group">
                                                <span class="input-group-addon" title="URL requires http:// or https://" data-toggle="tooltip"><i class="fa fa-link"></i></span>
                                                <input class="form-control" type="url" name="okay-url" maxlength="2083" <?php if ( isset( $values['okay-url'] ) ) echo 'value="' . esc_attr( $values['okay-url'] ) . '"'; ?> />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group rsm-group">
                                        <label for="cancel-url" class="col-md-3 control-label rsm-label">Cancel URL</label>
                                        <div class="col-md-8 col-lg-7">
                                            <div class="input-group">
                                                <span class="input-group-addon" title="URL requires http:// or https://" data-toggle="tooltip"><i class="fa fa-link"></i></span>
                                                <input class="form-control" type="url" name="cancel-url" maxlength="2083" <?php if ( isset( $values['cancel-url'] ) ) echo 'value="' . esc_attr( $values['cancel-url'] ) . '"'; ?> />
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
                    <i class="fa fa-cog bg-rsm-slate"></i>
                    <div class="timeline-item flat">
                        <h3 class="timeline-header no-border rsm-help-header"><strong>Step 3</strong> - Options</h3>
                        <?php rsm_get_help_link(); ?>

                        <div class="timeline-body">
                            <div class="row clearfix">
                                <div class="col-md-12">
                                    <?php rsm_get_help_text( 'settings_options' ) ?>

                                    <h4 class="rsm-help-header" style="margin-top:20px;">Welcome Message</h4>
                                    <div class="form-group">
                                        <div class="col-md-offset-1 col-md-11">
                                            <div class="radio">
                                                <input type="radio" value="F" name="opt-welcome" id="opt-welcome-no" <?php echo ( isset( $values['show-welcome'] ) && 'T' == $values['show-welcome'] ) ? '' : 'checked="checked"'; ?>>
                                                <label class="rsm-label" for="opt-welcome-no">
                                                    Do not send a welcome notification
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <input type="radio" value="T" name="opt-welcome" id="opt-welcome-yes" <?php echo ( isset( $values['show-welcome'] ) && 'T' == $values['show-welcome'] ) ? 'checked="checked"' : ''; ?>>
                                                <label class="rsm-label" for="opt-welcome-yes">
                                                    Send a welcome notification
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rsm-welcome" style="display:none;">
                                        <div class="form-group rsm-group">
                                            <label for="welcome-text" class="col-md-3 control-label rsm-label">Message text</label>
                                            <div class="col-md-8 col-lg-7">
                                                <textarea class="form-control rsm-emoji" name="welcome-text" id="welcome-text" cols="60" rows="3" maxlength="180"><?php if ( isset( $values['welcome-text'] ) ) echo esc_textarea( $values['welcome-text'] ); ?></textarea>
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
                                            <label for="welcome-url" class="col-md-3 control-label">Redirect URL</label>
                                            <div class="col-md-8 col-lg-7">
                                                <div class="input-group">
                                                    <span class="input-group-addon" title="URL requires http:// or https://" data-toggle="tooltip"><i class="fa fa-link"></i></span>
                                                    <input class="form-control" type="url" id="welcome-url" name="welcome-url" maxlength="2083" <?php if ( isset( $values['welcome-url'] ) ) echo 'value="' . esc_attr( $values['welcome-url'] ) . '"'; ?> />
                                                </div>
                                                <div class="rsm-field-details pull-left" id="rsm-redirect-policy" style="margin-top:4px;display:none;"><i class="fa fa-exclamation-triangle" style="color:#e15554;"></i> Note: While this software is capable of redirecting to a website outside Facebook, we don't recommend it because (1) it's against Facebook policy, (2) it creates a poor user experience and (3) it can affect conversions. <a href="admin.php?page=social-conversion-help#faq-redirect" target="_blank">Click here to read more.</a></div>
                                                <div class="rsm-field-details pull-left" id="rsm-canvas-redirect" style="margin-top:4px;display:none;"><i class="fa fa-info-circle" style="color:#3c8dbc;"></i> Note: Redirecting inside Facebook canvas requires an SSL certificate installed on your website. Additionally, you must specify the URL of a page within this website: <code>https://<?php echo rsm_get_option( 'fb_app_domain' ); ?></code></div>

                                            </div>
                                        </div>

                                    </div>

                                    <h4 style="margin-top:20px;">Email Integration</h4>
                                    <div class="form-group rsm-group">
                                        <div class="col-md-offset-1 col-md-11">
                                            <div class="radio">
                                                <input type="radio" value="F" name="opt-ar" id="opt-ar-no" <?php echo ( isset( $values['integrate-ar'] ) && 'T' == $values['integrate-ar'] ) ? '' : 'checked="checked"'; ?>>
                                                <label class="rsm-label" for="opt-ar-no">
                                                    Do not integrate with an email list
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <input type="radio" value="T" name="opt-ar" id="opt-ar-yes" <?php echo ( isset( $values['integrate-ar'] ) && 'T' == $values['integrate-ar'] ) ? 'checked="checked"' : ''; ?>>
                                                <label class="rsm-label" for="opt-ar-yes">
                                                    Integrate with an email list
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="rsm-ar-lists" style="display:none;">
                                        <div class="form-group" id="opt-ar-error" style="display:none;">
                                            <div class="col-md-offset-2 col-md-9 col-lg-8 rsm-error-outline">
                                                If integrating with your email service, please select one or more lists.
                                            </div>
                                        </div>

                                        <div class="form-group" id="integrate-none" <?php if ( $activecampaign_connected || $aweber_connected || $benchmark_connected || $campaignmonitor_connected || $ctct_connected || $getresponse_connected || $icontact_connected || $infusionsoft_connected || $mailchimp_connected || $mailerlite_connected || $sendinblue_connected || $sendreach_connected ) echo 'style="display:none;"'; ?>>
                                            <div class="col-md-offset-2 col-md-9 col-lg-8">
                                                <div class="alert bg-rsm-red">
                                                    No email services are connected. You must first go to the <a href="admin.php?page=social-conversion-settings&rsm-tab=ar" target="_blank"><em>Email Integrations</em></a> tab (in <em>Settings</em>) to connect your email service.
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                            rsm_get_ar_checklist_html( $ar_lists, $activecampaign_connected, 'activecampaign', 'Active Campaign' );
                                            rsm_get_ar_checklist_html( $ar_lists, $aweber_connected, 'aweber', 'AWeber' );
                                            rsm_get_ar_checklist_html( $ar_lists, $benchmark_connected, 'benchmark', 'Benchmark' );
                                            rsm_get_ar_checklist_html( $ar_lists, $campaignmonitor_connected, 'campaignmonitor', 'Campaign Monitor' );
                                            rsm_get_ar_checklist_html( $ar_lists, $ctct_connected, 'ctct', 'Constant Contact' );
                                            rsm_get_ar_checklist_html( $ar_lists, $convertkit_connected, 'convertkit', 'ConvertKit' );
                                            rsm_get_ar_checklist_html( $ar_lists, $getresponse_connected, 'getresponse', 'GetResponse' );
                                            rsm_get_ar_checklist_html( $ar_lists, $icontact_connected, 'icontact', 'iContact' );
                                            rsm_get_ar_checklist_html( $ar_lists, $infusionsoft_connected, 'infusionsoft', 'Infusionsoft' );
                                            rsm_get_ar_checklist_html( $ar_lists, $mailchimp_connected, 'mailchimp', 'MailChimp' );
                                            rsm_get_ar_checklist_html( $ar_lists, $mailerlite_connected, 'mailerlite', 'MailerLite' );
                                            rsm_get_ar_checklist_html( $ar_lists, $sendinblue_connected, 'sendinblue', 'SendinBlue' );
                                            rsm_get_ar_checklist_html( $ar_lists, $sendreach_connected, 'sendreach', 'SendReach' );
                                        ?>
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
                        <h3 class="timeline-header no-border"><strong>Step 4</strong> - Submit</h3>
                        <div class="timeline-body">
                            <div class="row clearfix">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <div class="col-xs-2 col-sm-2 col-md-3" style="text-align:center;">
                                            <button class="btn bg-rsm-slate btn-flat" id="rsm-btn-submit" name="rsm-btn-submit" type="submit" style="margin-bottom:5px;width:100px;"<?php echo ( 'add' == $rsm_mode && $max ) ? 'disabled' : ''; ?>>
                                                <?php echo ( 'add' == $rsm_mode ) ? 'Save' : 'Update'; ?>
                                            </button>
                                            <?php if ( 'add' != $rsm_mode ) echo '<input type="hidden" name="list-id" value="' . $values['list-id'] . '"/>'; ?>
                                            <input type="hidden" name="rsm-mode" value="<?php echo $rsm_mode; ?>"/>
                                            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_settings_nonce' ); ?>"/>
                                            <input type="hidden" value="list_submit" name="rsm-action" />
                                        </div>
                                        <div class="col-xs-offset-2 col-sm-offset-1 col-xs-8 col-md-7 col-lg-pull-1 col-lg-8"<?php echo ( 'add' == $rsm_mode && $max ) ? '' : ' style="display:none;"'; ?>>
                                            Note: You have reached the maximum number of FB Lists for this edition. If you need to add more, please <a href="http://socialconversion.io/upgrade" target="_blank">upgrade</a>.
                                        </div>


                                    </div>


                                </div>
                            </div>

                    </div>
                </li>
                <!-- END timeline item -->

            </ul>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Renders the Autoresponder settings tab.
 *
 * @since 1.0
 * @return void
 */
function rsm_settings_ar_tab( $ar_data ) {
	// Determine which autoresponders are connected
	$activecampaign_connected  = ( isset( $ar_data['activecampaign'] )  && ( 'T' == $ar_data['activecampaign']['connected'] ) );
	$aweber_connected          = ( isset( $ar_data['aweber'] )          && ( 'T' == $ar_data['aweber']['connected'] ) );
	$benchmark_connected       = ( isset( $ar_data['benchmark'] )       && ( 'T' == $ar_data['benchmark']['connected'] ) );
	$campaignmonitor_connected = ( isset( $ar_data['campaignmonitor'] ) && ( 'T' == $ar_data['campaignmonitor']['connected'] ) );
	$ctct_connected            = ( isset( $ar_data['ctct'] )            && ( 'T' == $ar_data['ctct']['connected'] ) );
	$convertkit_connected      = ( isset( $ar_data['convertkit'] )      && ( 'T' == $ar_data['convertkit']['connected'] ) );
	$getresponse_connected     = ( isset( $ar_data['getresponse'] )     && ( 'T' == $ar_data['getresponse']['connected'] ) );
	$icontact_connected        = ( isset( $ar_data['icontact'] )        && ( 'T' == $ar_data['icontact']['connected'] ) );
	$infusionsoft_connected    = ( isset( $ar_data['infusionsoft'] )    && ( 'T' == $ar_data['infusionsoft']['connected'] ) );
	$mailchimp_connected       = ( isset( $ar_data['mailchimp'] )       && ( 'T' == $ar_data['mailchimp']['connected'] ) );
	$mailerlite_connected      = ( isset( $ar_data['mailerlite'] )      && ( 'T' == $ar_data['mailerlite']['connected'] ) );
	$sendinblue_connected      = ( isset( $ar_data['sendinblue'] )      && ( 'T' == $ar_data['sendinblue']['connected'] ) );
	$sendreach_connected       = ( isset( $ar_data['sendreach'] )       && ( 'T' == $ar_data['sendreach']['connected'] ) );
	?>

	<div class="box box-rsm-slate box-solid flat">
		<div class="box-header with-border">
			<i class="fa fa-envelope"></i>
			<h3 class="box-title">Email Provider Connections</h3>
			<div class="box-tools pull-right">
				<button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
			</div>
		</div><!-- /.box-header -->

		<div class="box-body">
			Capture email addresses automatically into your email list when integrating with <?php echo RSM_PLUGIN_NAME; ?>. Each Facebook app can integrate with one or more email lists. To setup, you must first connect your email service provider by clicking below and supplying the API details. Afterwards, go to the <a href="admin.php?page=social-conversion-settings&rsm-tab=apps">Facebook Apps</a> settings tab and select which email lists to integrate with which Facebook apps.
			<form id="ar-form">
				<ul class="products-list product-list-in-box">

					<?php
					// Active Campaign
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_url',
							'field_display' => 'API URL'
						),
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $activecampaign_connected, 'activecampaign', 'Active Campaign', $ar_fields );

					// AWeber Campaign
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'Authorization Code'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $aweber_connected, 'aweber', 'AWeber', $ar_fields );

					// Benchmark Campaign
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						),
						array(
							'field_type'    => 'checkbox',
							'field_name'    => 'double_optin',
							'field_display' => '<strong>Double Opt-in</strong> - Send a confirmation email after a user signs up'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $benchmark_connected, 'benchmark', 'Benchmark', $ar_fields );

					// Campaign Monitor
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $campaignmonitor_connected, 'campaignmonitor', 'Campaign Monitor', $ar_fields );

					// Constant Contact
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'Access Token'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $ctct_connected, 'ctct', 'Constant Contact', $ar_fields );

					// ConvertKit
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $convertkit_connected, 'convertkit', 'ConvertKit', $ar_fields );

					// GetResponse
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $getresponse_connected, 'getresponse', 'GetResponse', $ar_fields );

					// iContact
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'username',
							'field_display' => 'Account Username'
						),
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'Application ID'
						),
						array(
							'field_type'    => 'text',
							'field_name'    => 'password',
							'field_display' => 'Application Password <span style="color:#999;font-size:12px;">(<em>NOT</em> your account password; see below)</span>'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $icontact_connected, 'icontact', 'iContact', $ar_fields );

					// Infusionsoft
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'subdomain',
							'field_display' => 'Account ID/Subdomain <span style="color:#999;font-size:12px;">(e.g. ab123)</span>'
						),
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $infusionsoft_connected, 'infusionsoft', 'Infusionsoft', $ar_fields );

					// MailChimp Campaign
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						),
						array(
							'field_type'    => 'checkbox',
							'field_name'    => 'double_optin',
							'field_display' => '<strong>Double Opt-in</strong> - Send a confirmation email after a user signs up'
						),
						array(
							'field_type'    => 'checkbox',
							'field_name'    => 'welcome_email',
							'field_display' => '<strong>Welcome Email</strong> - Send the default welcome message after a user signs up'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $mailchimp_connected, 'mailchimp', 'MailChimp', $ar_fields );

					// MailerLite
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $mailerlite_connected, 'mailerlite', 'MailerLite', $ar_fields );

					// SendinBlue
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'API Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $sendinblue_connected, 'sendinblue', 'SendinBlue', $ar_fields );

					// SendReach
					$ar_fields = array(
						array(
							'field_type'    => 'text',
							'field_name'    => 'api_key',
							'field_display' => 'Public Key'
						),
						array(
							'field_type'    => 'text',
							'field_name'    => 'private_key',
							'field_display' => 'Private Key'
						)
					);
					rsm_get_ar_connect_html( $ar_data, $sendreach_connected, 'sendreach', 'SendReach', $ar_fields );
					?>

				</ul>
			</form>
			<p class="rsm-ar-footnote">Note: If you do not see your email provider listed above and would like us to integrate with it, <a href="http://support.newexpanse.com" target="_blank">drop us a message</a>.</p>
		</div><!-- /.box-body -->
	</div>

	<?php
}


/**
 * Renders the Opt-in button settings tab.
 *
 * @since 1.0
 * @return void
 */
function rsm_settings_buttons_tab( $settings ) {
    $btn_dir  = RSM_PLUGIN_DIR . 'assets/img/buttons/';
    $btn_url  = RSM_PLUGIN_URL . 'assets/img/buttons/';
    $img_type = array( 'apng', 'bmp', 'cur', 'dib', 'gif', 'ico', 'jfi', 'jfif', 'jif', 'jpe', 'jpeg', 'jpg', 'png', 'svg', 'webp', 'xbm' );
    $images   = array();

    // Get all files in button directory
    if ( is_dir( $btn_dir ) ) {
        if ( $dh = opendir( $btn_dir ) ) {
            while ( ( $file = readdir( $dh ) ) !== false ) {
                $ext = rsm_get_file_ext( $file );
                if ( in_array( $ext, $img_type ) ) {
                    $images[] = $btn_url . $file;
                }
            }
            closedir( $dh );
            asort( $images );
        }
    }

    // Get Option settings
	$optin_btn          = $settings['optin_btn'];
	$float_status       = $settings['float_status'];
	$float_list_id      = $settings['float_list_id'];
	$float_segment_id   = $settings['float_segment_id'];
	$float_text         = $settings['float_text'];
	$float_color        = $settings['float_color'];
	$float_button_color = $settings['float_button_color'];
	$float_position     = $settings['float_position'];
    ?>

    <div class="box box-rsm-slate box-solid flat">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title">Opt-in Widget Settings</h3>
            <div class="box-tools pull-right">
                <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
            </div>
        </div><!-- /.box-header -->

        <div class="box-body">
            <p>The floating opt-in widget displays on your website and works just like your standard opt-in button. When a user visits your website, the floating widget appears and if clicked, the user is taken through the opt-in steps (FB authentication, optional redirecting, etc). The floating button will display on every webpage on your site when enabled. </p>
            <form class="form-horizontal" method="post" id="settings-form-float-btn" action="">

	            <div class="row clearfix">
		            <div class="col-md-12">
			            <h4 class="rsm-help-header">Configuration</h4>
			            <?php rsm_get_help_link(); ?>

			            <div class="form-group">
				            <div class="row clearfix">
					            <div class="col-md-11">
						            <?php rsm_get_help_text( 'settings_floating_button', false, 'rsm-help-margin-auto' ) ?>

						            <div class="col-xs-offset-1 radio">
							            <input type="radio" value="0" id="opt-float-button-disabled" name="opt-float-button" <?php echo ( 1 == $float_status ) ? '' : 'checked="checked"'; ?>>
							            <label class="rsm-label" for="opt-float-button-disabled">
								            <strong>Disable</strong> - Hide floating opt-in widget
							            </label>
						            </div>

						            <div class="col-xs-offset-1 radio">
							            <input type="radio" value="1" id="opt-float-button-enabled" name="opt-float-button" <?php echo ( 1 == $float_status ) ? 'checked="checked"' : ''; ?>>
							            <label class="rsm-label" for="opt-float-enabled">
								            <strong>Enable</strong> - Show floating opt-in widget
							            </label>
						            </div>

					            </div>
				            </div>
			            </div>

			            <div id="rsm-float-config" style="display:none;">

				            <div class="form-group rsm-group">
					            <label for="float-list-id" class="col-md-3 control-label rsm-label">FB List</label>
					            <div class="col-md-8 col-lg-7">
						            <select class="form-control" name="float-list-id" id="float-list-id" style="display:inline-block;">
							            <?php
							            $current = isset( $float_list_id ) ? (int) $float_list_id : 0 ;
							            $lists = db_get_list_data();
							            echo '<option hidden>Select FB List...</option>';
							            if( $lists ) {
								            foreach( $lists as $list ) {
									            echo '<option value="' . esc_attr( $list['list_id'] ) . '"' .  ( $list['list_id'] == $current  ? ' selected="selected"' : '' ) . '>' . esc_attr( $list['app_name'] ) . '</option>';
								            }
							            }
							            ?>
						            </select>
						            <span id="seg-loading" style="display:none;"><i class="fa fa-spinner fa-pulse fa-2x"></i></span>
					            </div>
				            </div>

				            <div class="form-group rsm-group">
					            <label for="float-segment-id" class="col-md-3 control-label rsm-label">Segment</label>
					            <div class="col-md-8 col-lg-7">
						            <select class="form-control" name="float-segment-id" id="float-segment-id">
							            <?php
							            echo '<option value="0">All subscribers</option>';
							            $current = isset( $float_segment_id ) ? (int) $float_segment_id : 0 ;
							            $segments   = ( 0 == $float_list_id ) ? db_get_segment_detail() : db_get_list_segment( $float_list_id );
							            if( $segments ) {
								            foreach( $segments as $segment ) {
									            echo '<option value="' . esc_attr( $segment['segment_id'] ) . '"' . ( $segment['segment_id'] == $current ? ' selected="selected"' : '' ) . '>' . esc_attr( $segment['segment_name'] ) . '</option>';
								            }
							            }
							            ?>
						            </select>
					            </div>
				            </div>
							<hr style="width:75%;">
				            <div class="form-group">
					            <label for="float-position" class="col-md-3 control-label rsm-label">Lock Position</label>
					            <div class="col-md-8 col-lg-7">
						            <select class="form-control" name="float-position" id="float-position">
							            <?php $float_position = isset( $float_position ) ? $float_position : 'right'; ?>
							            <option value="left" <?php if( 'left' == $float_position ) echo 'selected="selected"'; ?>>Left side</option>
							            <option value="right" <?php if( 'right' == $float_position ) echo 'selected="selected"'; ?>>Right side</option>
							            <option value="top-left" <?php if( 'top-left' == $float_position ) echo 'selected="selected"'; ?>>Top left</option>
							            <option value="bottom-right" <?php if( 'bottom-right' == $float_position ) echo 'selected="selected"'; ?>>Bottom right</option>
						            </select>
					            </div>
				            </div>

				            <div class="form-group rsm-group">
					            <label for="float-text" class="col-md-3 control-label rsm-label">Button Text</label>
					            <div class="col-md-6 col-lg-5">
						            <input type="text" class="form-control" id="float-text" name="float-text" <?php if ( isset( $float_text ) ) echo 'value="' . esc_attr( $float_text ) . '"'; ?> maxlength="128" />
					            </div>
				            </div>

				            <div class="form-group rsm-group">
					            <label for="float-color" class="col-md-3 control-label rsm-label">Text Color</label>
					            <div class="col-md-6 col-lg-5">
						            <input type="text" class="rsm-color-field" id="float-color" name="float-color" <?php if ( isset( $float_color ) ) echo 'value="' . esc_attr( $float_color ) . '"'; ?> maxlength="8" />
					            </div>
				            </div>

				            <div class="form-group rsm-group">
					            <label for="float-button-color" class="col-md-3 control-label rsm-label">Button Color</label>
					            <div class="col-md-6 col-lg-5">
						            <input type="text" class="rsm-color-field" id="float-button-color" name="float-button-color" <?php if ( isset( $float_button_color ) ) echo 'value="' . esc_attr( $float_button_color ) . '"'; ?> maxlength="8" />
					            </div>
				            </div>

			            </div>

			            <div class="form-group">
				            <div class="col-xs-1 col-sm-1 col-md-2" style="text-align:center;">
					            <button class="btn bg-rsm-slate btn-flat" id="rsm-float-btn-submit" name="rsm-float-btn-submit" type="submit" style="margin:20px 0 5px 0;width:100px;">
						            Save
					            </button>
					            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_settings_nonce' ); ?>"/>
					            <input type="hidden" value="float_button_submit" name="rsm-action" />
				            </div>
			            </div>

		            </div>
	            </div>

            </form>

        </div><!-- /.box-body -->
    </div><!-- /.box -->


	<div class="box box-rsm-slate box-solid flat">
		<div class="box-header with-border">
			<i class="fa fa-caret-square-o-right"></i>
			<h3 class="box-title">Opt-in Button Settings</h3>
			<div class="box-tools pull-right">
				<button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
			</div>
		</div><!-- /.box-header -->

		<div class="box-body">
			<p>Using a graphical opt-in button will help your links get noticed and increase your opt-in rate. The opt-in button shown below (current) will be used by default in your opt-in HTML code. To use a different button, select from your Available Buttons below and click Save. Afterwards, go to the <a href="admin.php?page=social-conversion-settings&rsm-tab=apps">Facebook Apps</a> settings tab and copy the updated opt-in HTML code for any of your Configured Apps.</p>
			<form class="form-horizontal" method="post" id="settings-form-btn" action="">

				<div class="row clearfix">
					<div class="col-md-12">
						<h4 class="rsm-help-header">Current Opt-in Button</h4>
						<?php rsm_get_help_link(); ?>

						<div class="form-group">
							<div class="row clearfix">
								<div class="col-md-11">
									<?php rsm_get_help_text( 'settings_current_button', false, 'rsm-help-margin-btn' ) ?>

									<div class="col-xs-offset-1">
										<?php echo '<img style="margin:10px;" src="' . esc_url( $optin_btn ) . '">' ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row clearfix">
					<div class="col-md-12">
						<h4 class="rsm-help-header">Available Buttons</h4>
						<?php rsm_get_help_link(); ?>

						<div class="form-group">
							<div class="row clearfix">
								<div class="col-md-11">
									<?php rsm_get_help_text( 'settings_available_buttons', false, 'rsm-help-margin-btn' ) ?>

									<div class="row radio">
										<div class="col-sm-offset-1 col-md-11">
											<?php
											if ( $images ) {
												$media_btn = true;
												foreach( $images as $image ) {
													?>
													<div class="col-xs-11 col-sm-6 col-md-6 col-lg-4">
														<label class="rsm-label">
															<input type="radio" value="<?php echo $image;?>" name="opt-button"
																<?php
																if ( esc_url_raw( $image ) == $optin_btn ) {
																	$media_btn = false;
																	echo 'checked="checked"'; }
																?>
																>
															<?php echo '<img class="rsm-opt-button rsm-no-select" src="' . esc_url( $image ) . '">'; ?>
														</label>
													</div>
													<?php
												}
											}
											?>
										</div>

										<div class="col-sm-offset-1 col-md-11">
											<div class="col-sm-11" style="margin-top: 20px;">
												<h4>Media Library Button</h4>
												<label class="rsm-label rsm-group">
													<input type="radio" value="<?php if( $media_btn ) echo $optin_btn; ?>" name="opt-button" id="rsm-media-upload-opt" <?php echo $media_btn ? 'checked="checked"' : ''; ?>>
													<input type="text" value="<?php if( $media_btn ) echo $optin_btn; ?>" class="rsm-media-url" id="rsm-media-url" name="rsm-media-url">
													<button id="rsm-media-upload" class="btn btn-sm bg-rsm-gray" value="Upload" type="submit" style="padding: 7px 10px;"><i class="fa fa-upload"></i> Select Image</button>
												</label>
											</div>
										</div>

									</div>

								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-xs-1 col-sm-1 col-md-2" style="text-align:center;">
								<button class="btn bg-rsm-slate btn-flat" id="rsm-btn-submit" name="rsm-btn-submit" type="submit" style="margin:20px 0 5px 0;width:100px;">
									Save
								</button>
								<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_settings_nonce' ); ?>"/>
								<input type="hidden" value="button_submit" name="rsm-action" />
							</div>
						</div>


					</div>

				</div>
			</form>

		</div><!-- /.box-body -->
	</div><!-- /.box -->


	<?php
}

/**
 * Renders the Automation settings tab.
 *
 * @since 1.0
 * @return void
 */
function rsm_settings_automation_tab( $settings ) {
    // Get Option settings
	$rsm_cron_type = $settings['cron_type'];   // wp or real
    ?>

    <div class="box box-rsm-slate box-solid flat">
        <div class="box-header with-border">
            <i class="fa fa-rocket"></i>
            <h3 class="box-title">Delivery Settings</h3>
            <div class="box-tools pull-right">
                <button data-widget="collapse" class="btn bg-rsm-slate btn-sm"><i class="fa fa-chevron-down"></i></button>
            </div>
        </div><!-- /.box-header -->

        <div class="box-body">
            <p>These settings control how notification delivery is handled on the backend. Certain features, such as Scheduled and Sequence notifications, require a scheduled task (cron job) to run properly. If you would like help setting this up, please open a <a href="https://support.newexpanse.com" target="_blank">support ticket</a>.</p>
            <form class="form-horizontal" method="post" id="settings-form" action="">

                <div class="row clearfix">
                    <div class="col-md-12">
                        <h4 class="rsm-help-header">Background Tasks</h4>
                        <?php rsm_get_help_link(); ?>

                        <div class="form-group">
                            <div class="row clearfix">
                                <div class="col-md-11">
                                    <?php rsm_get_help_text( 'settings_job_scheduling', false, 'rsm-help-margin-auto' ) ?>

                                    <div class="col-xs-offset-1 radio">
                                        <input type="radio" value="wp" name="opt-cron" id="opt-wp-cron" <?php echo ( 'wp' == $rsm_cron_type ) ? 'checked="checked"' : ''; ?>>
                                        <label class="rsm-label" for="opt-wp-cron">
                                            <strong>WordPress cron</strong> - Use the cron service built into WordPress
                                        </label>
                                        <p class="help-block col-md-offset-1">WordPress's cron service triggers whenever someone visits this website. If you have a low traffic website, consider using a ping service to visit this website or setting up a real cron (see below).</p>
                                    </div>
                                    <div class="col-xs-offset-1 radio">
                                        <input type="radio" value="real" name="opt-cron" id="opt-real-cron" <?php echo ( 'wp' == $rsm_cron_type ) ? '' : 'checked="checked"'; ?>>
                                        <label class="rsm-label" for="opt-real-cron">
                                            <strong>Real cron</strong> - Use your web host cron or a 3rd party web cron service
                                        </label>
                                        <p class="help-block col-md-offset-1">A real cron job will trigger and run background tasks at a fixed interval. It requires one-time configuration on your web host or web cron service provider.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rsm-cron-service" style="display:none;">

                            <div class="form-group">
                                <label for="cron-command" class="col-md-3 control-label rsm-label">Your Web Host Cron<br/><span id="rsm-cron-alt-link" class="rsm-advanced-link" data-toggle="tooltip" title="If having trouble with cron, click to show an alternate cron command" style="font-weight:500;"><small>[Show alternative]</small></span></label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control input-sm rsm-select-all rsm-copy-text" id="cron-command" value="<?php echo esc_attr( $settings['cron_command'] ); ?>" readonly />
                                        <span class="input-group-btn">
                                           <button class="btn btn-sm bg-rsm-slate btn-flat rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                               <i class="fa fa-files-o"></i>
                                           </button>
                                        <span>
                                    </div>
                                    <p class="help-block">Use this command when configuring a cron job on your <em>web host</em> (e.g. cPanel).</p>
                                </div>
                            </div>

                            <div id="rsm-cron-alt" class="form-group" style="display:none;">
                                <label for="cron-alt-cmd" class="col-md-3 control-label rsm-label">Your Web Host Cron<br/>Alternative</label>

                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control input-sm rsm-select-all rsm-copy-text" id="cron-alt-cmd" value="<?php echo esc_attr( $settings['cron_alt_cmd'] ); ?>" readonly />
                                        <span class="input-group-btn">
                                           <button class="btn btn-sm bg-rsm-slate btn-flat rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                               <i class="fa fa-files-o"></i>
                                           </button>
                                        <span>
                                    </div>
                                    <p class="help-block">Each web host environment is different and so if the command above didn't work, try using this command instead.</p>
                                </div>
                            </div>


                            <div class="form-group">
                                <label for="cron-url" class="col-md-3 control-label rsm-label">3rd Party Web Cron</label>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control input-sm rsm-select-all rsm-copy-text" id="cron-url" value="<?php echo esc_attr( $settings['cron_url'] ); ?>" readonly />
                                        <span class="input-group-btn">
                                           <button class="btn btn-sm bg-rsm-slate btn-flat rsm-copy-btn" title="Click to copy" data-toggle="tooltip">
                                               <i class="fa fa-files-o"></i>
                                           </button>
                                        <span>
                                    </div>
                                    <p class="help-block">Use this URL when configuring a <em>web cron</em> job with a 3rd party service.</p>
                                    <p class="help-block">Set your cron job to run every 10-30 minutes.</p>
                                </div>
                            </div>

                        </div>

                        <div class="form-group">
                            <div class="col-xs-1 col-sm-1 col-md-2" style="text-align:center;">
                                <button class="btn bg-rsm-slate btn-flat" id="rsm-btn-submit" name="rsm-btn-submit" type="submit" style="margin-bottom:5px;width:100px;">
                                    Save
                                </button>
                                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_settings_nonce' ); ?>"/>
                                <input type="hidden" value="auto_submit" name="rsm-action" />
                            </div>
                        </div>


                    </div>

                </div>
            </form>

        </div><!-- /.box-body -->
    </div><!-- /.box -->

    <div class="box box-rsm-gray box-solid flat">
        <div class="box-header with-border">
            <i class="fa fa-clock-o"></i>
            <h3 class="box-title">Date & Time Settings</h3>
            <div class="box-tools pull-right">
                <button data-widget="collapse" class="btn bg-rsm-gray btn-sm"><i class="fa fa-chevron-down"></i></button>
            </div>
        </div><!-- /.box-header -->

        <div class="box-body">
            <div class="row clearfix">
                <div class="col-md-12">
                    <p>The local time will be used when processing notifications. Your local timezone is set in Wordpress's <a href="<?php echo admin_url('options-general.php#timezone_string'); ?>" target="_blank">General Settings</a> section.</p>
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="rsm-timezone" class="col-md-3 col-lg-3 control-label rsm-label">Local timezone</label>
                            <div class="col-md-6 col-lg-5">
                                <input type="text" class="form-control" id="rsm-timezone" value="<?php echo rsm_get_timezone_string( true ); ?>" readonly />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-offset-3 col-lg-offset-3 col-md-9">
                                <span class="rsm-local-time">Local time: <code><?php echo rsm_get_datetime( rsm_datetime_wp_format() ); ?></code></span><br/>
                                <span class="rsm-utc-time">UTC time: <code><?php echo rsm_get_datetime_gmt( rsm_datetime_wp_format() ); ?></code></span>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

        </div><!-- /.box-body -->
    </div><!-- /.box -->
<?php
/*
<span id="utc-time"><abbr title="Coordinated Universal Time">UTC</abbr> time: <code><?php echo rsm_get_datetime_gmt( rsm_datetime_wp_format() ); ?></code></span>
<span id="local-time">Local time: <code><?php echo rsm_get_datetime( rsm_datetime_wp_format() ); ?></code></span>
*/
}

/**
 * Gets settings values from either http variables or database.
 *
 * @since 1.0
 * @param string $rsm_mode Specifies add or edit mode
 * @return array $values Array of system settings values
 */
function rsm_get_settings_apps_values( $rsm_mode ) {
    if ( 'add' == $rsm_mode ) {
        $values = array (
            'app-name'      => isset( $_POST['app-name'] )      ? $_POST['app-name']      : null,
            'app-id'        => isset( $_POST['app-id'] )        ? $_POST['app-id']        : null,
            'app-secret'    => isset( $_POST['app-secret'] )    ? $_POST['app-secret']    : null,
            'okay-url'      => isset( $_POST['okay-url'] )      ? $_POST['okay-url']      : null,
            'cancel-url'    => isset( $_POST['cancel-url'] )    ? $_POST['cancel-url']    : null,
            'show-welcome'  => isset( $_POST['opt-welcome'] )   ? $_POST['opt-welcome']   : null,
            'welcome-text'  => isset( $_POST['welcome-text'] )  ? $_POST['welcome-text']  : null,
            'welcome-url'   => isset( $_POST['welcome-url'] )   ? $_POST['welcome-url']   : null,
            'redirect-type' => isset( $_POST['redirect-type'] ) ? $_POST['redirect-type'] : null,
            'integrate-ar'  => isset( $_POST['integrate-ar'] )  ? $_POST['integrate-ar']  : null
        );

    } elseif ( 'edit' == $rsm_mode ) {
        // If edit action, verify List ID
        if ( ! isset( $_GET['list-id'] ) || ! is_numeric( $_GET['list-id'] ) ) {
            wp_die( 'Edit list error: No list ID.', 'Error' );
        }
        // Get list values from db
        $list_id = (int) $_GET['list-id'];
        $list    = db_get_list_row( $list_id );
        if ( false == $list ) {
            wp_die( 'Edit list error: No list data.', 'Error' );
        }
        $values = array(
            'list-id'       => $list['list_id'],
            'app-name'      => $list['app_name'],
            'app-id'        => $list['app_id'],
            'app-secret'    => $list['app_secret'],
            'okay-url'      => $list['okay_url'],
            'cancel-url'    => $list['cancel_url'],
            'show-welcome'  => $list['show_welcome'],
            'welcome-text'  => $list['welcome_msg'],
            'welcome-url'   => $list['welcome_url'],
            'redirect-type' => $list['redirect_type'],
            'integrate-ar'  => $list['integrate_ar']
        );
    }
    return $values;
}

/**
 * Renders the autoresponder connection pane in html.
 *
 * @since 1.0
 * @param array $ar_data The autoresponder data
 * @param bool $connected Indicates whether this autoresponder is connected or not
 * @param string $ar_name The autoresponder name
 * @param string $ar_display_name The autoresponder name to display
 * @param array $ar_fields The fields that define the autoresponder connection pane
 * @return string $html The autoresponder's connection pane in html
 */
function rsm_get_ar_connect_html( $ar_data = array(), $connected = false, $ar_name = '', $ar_display_name = '', $ar_fields = array() ) {
    ?>
    <li class="item <?php echo $ar_name; ?>">
        <div class="row clearfix">
            <div class="col-md-12">
                <div class="rsm-ar-toggle">
                    <i class="fa fa-chevron-right rsm-ar-panel"></i>
                    <div class="rsm-ar-img rsm-no-select <?php echo $ar_name; ?>-img <?php echo $connected ? 'active' : 'inactive'; ?>"></div>
                    <div class="product-info">
                        <span class="product-title"><?php echo $ar_display_name; ?></span>
                        <span class="rsm-ar-label pull-right" <?php if( ! $connected ) echo 'style="display:none;" ';?>id="<?php echo $ar_name; ?>-connected"><i class="fa fa-check"></i> Connected</span>
                        <span class="product-description">Integrate <?php echo RSM_PLUGIN_NAME; ?> opt-ins with <?php echo $ar_display_name; ?>.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row clearfix rsm-ar-panel-detail" style="display:none;">
            <div class="col-lg-offset-1 col-md-12 col-lg-11">
                <?php
                    if ( $ar_fields ) {
                        $index = 0;
                        foreach ( $ar_fields as $field ) {
                            if ( 'text' == $field['field_type'] ) {
                                $field_html = $ar_name . '-' . str_replace( '_', '-', $field['field_name'] );
                                ?>
                                <div class="form-group rsm-group">
                                    <label for="<?php echo $field_html; ?>" class="col-md-8 col-lg-7 rsm-label" <?php if ( $index > 0 ) echo 'style="margin-top:10px !important;"'; ?>><?php echo $field['field_display']; ?></label>
                                    <div class="col-md-8 col-lg-7">
                                        <input type="text" class="form-control" id="<?php echo $field_html; ?>" name="<?php echo $field_html; ?>" <?php if ( $connected ) echo 'value="' . esc_attr( $ar_data[ $ar_name ][ $field['field_name'] ] ) . '"'; ?> />
                                    </div>
                                </div>
                                <?php
                                $index++;
                            }
                        }
                    }
                ?>

                <div class="col-md-12 col-lg-11">
                    <?php
                        if ( $ar_fields ) {
                            foreach ( $ar_fields as $field ) {
                                if ( 'checkbox' == $field['field_type'] ) {
                                    $field_html = $ar_name . '-' . str_replace( '_', '-', $field['field_name'] );
                                    ?>
                                    <div class="checkbox rsm-checkbox">
                                        <input type="checkbox" id="<?php echo $field_html; ?>" <?php echo ( $connected && 'T' == $ar_data[ $ar_name ][ $field['field_name'] ] ) ? 'checked="checked"' : ''; ?>>
                                        <label for="<?php echo $field_html; ?>">
                                            <?php echo $field['field_display']; ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                            }
                        }
                    ?>
                    <div class="rsm-btn-wrapper">
                        <button class="btn bg-rsm-slate btn-flat" id="<?php echo $ar_name; ?>-connect" value="<?php echo $ar_name; ?>">
                            <?php echo ( $connected ) ? 'Update' : 'Connect'; ?>
                        </button>
                        <button class="btn bg-rsm-offwhite btn-flat" id="<?php echo $ar_name; ?>-disconnect" value="<?php echo $ar_name; ?>" <?php if( ! $connected ) echo 'style="display:none;"'; ?>>
                            Disconnect
                        </button>
                        <span id="<?php echo $ar_name; ?>-loading" style="display:none;"><i class="fa fa-spinner fa-pulse fa-2x"></i></span>
                    </div>
                    <div id="<?php echo $ar_name; ?>-results"></div>
                    <?php rsm_get_help_text( 'settings_' . $ar_name . '_api', true, 'rsm-help-margin-ar' ) ?>
                </div>

            </div>
        </div>
    </li><!-- /.item -->
    <?php
}

/**
 * Renders the autoresponder checklist in html.
 *
 * @since 1.0
 * @param array $ar_lists The integrated ar list
 * @param bool $connected Indicates whether this autoresponder is connected or not
 * @param string $ar_name The autoresponder name
 * @param string $ar_display_name The autoresponder name to display
 * @return string $html The select option list in html
 */
function rsm_get_ar_checklist_html( $ar_lists = array(), $connected = false, $ar_name = '', $ar_display_name = '' ) {
    ?>
    <div class="form-group" id="integrate-<?php echo $ar_name; ?>" <?php if( ! $connected ) echo 'style="display:none;"'; ?>>
    <div class="col-md-offset-2 col-sm-5 col-md-4 col-lg-3">
        <div class="checkbox rsm-checkbox rsm-label">
            <input type="checkbox" id="ar-use-<?php echo $ar_name; ?>" value="<?php echo $ar_name; ?>" <?php echo ( rsm_is_used( $ar_name, $ar_lists ) ) ? 'checked="checked"' : ''; ?>>
            <label for="ar-use-<?php echo $ar_name; ?>"><?php echo $ar_display_name; ?></label>
        </div>
    </div>
    <div class="col-md-offset-3 col-lg-offset-0 col-md-7 col-lg-6">
        <div class="input-group rsm-nowrap">
            <span class="input-group-addon" id="<?php echo $ar_name; ?>-addon" title="Select one or more lists to integrate with this Facebook app" data-toggle="tooltip"><i class="fa fa-file-text-o"></i></span>
            <select id="<?php echo $ar_name; ?>-multiselect" name="<?php echo $ar_name; ?>-multiselect[]" multiple="multiple">
                <optgroup label="<?php echo $ar_display_name; ?> Lists">
                    <?php if( $connected ) echo rsm_get_lists_html( $ar_name, $ar_lists ); ?>
                </optgroup>
            </select>
            <span id="<?php echo $ar_name; ?>-refresh" class="rsm-refresh" data-toggle="tooltip" title="Click to refresh"><i class="fa fa-refresh"></i></span>
            <span id="<?php echo $ar_name; ?>-refresh-loading" style="display:none;"><i class="fa fa-spinner fa-pulse fa-2x"></i></span>
        </div>
    </div>
    </div>
<?php
}

/**
 * Returns the integrated ar list in html, including those that are selected.
 *
 * @since 1.0
 * @param string $ar_name The autoresponder name
 * @param array $ar_lists The integrated ar list
 * @return string $html The select option list in html
 */
function rsm_get_lists_html( $ar_name = '', $ar_lists  ) {
    $html = '';
    if ( is_array( $ar_lists ) ) {
        foreach ( $ar_lists as $list ) {
            if( isset( $list['ar_name'] ) && ( $ar_name == $list['ar_name'] )  ) {
                $selected = ( isset( $list['selected'] ) && ( 'T' == $list['selected'] ) ) ? ' selected' : '';
                $html .= '<option value="' . $list['ar_list_value'] . '"' . $selected . '>' . $list['ar_list_name'] . '</option>';
            }
        }
    }

    if ( empty( $html ) ) {
        $html = '<option value="0" disabled>No lists found</option>';
    }

    return $html;
}

/**
 * Detects whether any ar list is being used for the specifier autoresponder.
 *
 * @since 1.0
 * @param array $ar_lists The integrated ar list
 * @param string $ar_name The autoresponder name
 * @return bool True if in use, otherwise false
 */
function rsm_is_used( $ar_name = '', $ar_lists ) {
    if ( is_array( $ar_lists ) ) {
        foreach ( $ar_lists as $list ) {
            if ( ( isset( $list['ar_name'] ) && isset( $list['selected'] ) ) && ( $ar_name == $list['ar_name'] ) && ( 'T' == $list['selected'] ) ) {
                return true;
            }
        }
    }
    return false;
}
