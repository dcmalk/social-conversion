<?php
/**
 * Help Page
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
 * Renders the help page
 *
 * @since 1.0
 * @return void
 */
function rsm_help_page() {
    ?>
    <div class="wrap rsm-bs wrapper help-page">

        <?php rsm_admin_main_header(); ?>

        <div class="content-wrapper">

            <?php rsm_admin_content_header( 'help' ); ?>

            <section class="content">

	            <?php rsm_getting_started_notice(); ?>

                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <div class="box box-rsm-slate box-solid flat">
                            <div class="box-header with-border">
                                <i class="fa fa-play-circle"></i>
                                <h3 class="box-title">Tutorials</h3>
                            </div>
                            <div class="box-body">
                                <p>Coming soon!</p>


                            </div><!-- /.box-body-->
                        </div><!-- /.box -->
                    </div>
                </div>

                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <div class="box box-rsm-slate box-solid flat">
                            <div class="box-header with-border">
                                <i class="fa fa-question-circle"></i>
                                <h3 class="box-title">Frequently Asked Questions</h3>
                            </div>
                            <div class="box-body">

	                            <div class="panel-group" id="accordion">

                                    <div class="panel panel-default">
                                        <div class="panel-heading" id="best-practices">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">1. Best practices</a>
                                            </h4>
                                        </div>
                                        <div id="collapseOne" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <p>The following recommendations will help ensure a quality experience for your prospects while also keeping Facebook happy:</p>
                                                <ol>
                                                    <li>People don't differentiate notifications from the rest of their experience on Facebook, so each message has a lot of power. Do not spam or deceive in your notifications. Send only one or two notifications to your subscribers each day. If too many spam reports are received, Facebook may restrict or disable the app.</li>
	                                                <li>Facebook requires apps that send more than 50,000 notifications in a week to maintain at least a 17% weekly click-to-impression (CTI) ratio. Apply standard copywriting techniques to help with this. Writing a great notification is similar to writing PPC ads, email subject lines and sales letter headlines.</li>
                                                    <li>Engage new subscribers immediately with a welcome message and personalize your messages using shortcodes and emojis (picture icons).</li>
                                                    <li>Like other areas of marketing, best results will come from testing, tracking and optimizing.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
	                                <div class="panel panel-default">
		                                <div class="panel-heading" id="faq-performance">
			                                <h4 class="panel-title">
				                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">2. How do I find the direct link to a specific Facebook post?</a>
			                                </h4>
		                                </div>
		                                <div id="collapseTwo" class="panel-collapse collapse">
			                                <div class="panel-body">
				                                <p>Redirecting a Notification to a Facebook post is a great engagement strategy. To find the direct link of a post, click its timestamp (when the post was published) at the top-left. This will take you directly to the post. You can now copy/paste the URL showing in your browser window.</p>
				                                <img src="<?php echo RSM_PLUGIN_URL . 'assets/img/help_click_timestamp.jpg'; ?>">
			                                </div>
		                                </div>
	                                </div>

	                                <div class="panel panel-default">
                                        <div class="panel-heading" id="faq-performance">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#collapreThree">3. Slow performance & caching plugins</a>
                                            </h4>
                                        </div>
                                        <div id="collapreThree" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <p>Whenever faced with WordPress performance issues, the first place we advise looking is your active plugins. While many plugins are great, in reality, it only takes one bad plugin to impact your site's performance.</p>
                                                <p>Caching plugins can sometimes be the problem child affecting WordPress performance. You can determine rather quickly whether it's the culprit by temporarily deactivating it. If the caching plugin turns out to be the problem, look for an "object caching" setting and if found, make sure it's not enabled.</p>
                                                <p>W3 Total Cache, in particular, has some known issues that affect performance. If you use W3 Total Cache and are experiencing slow page loads or general sluggishness, disable <em>Object Cache</em> under the <em>General Settings</em> section. To read about the issue and/or to tweak your settings further, you can read more <a href="https://wordpress.org/support/topic/self-diagnosed-and-fixed-w3-total-cache-bug-in-faulty-object-caching" target="_blank">here</a> and <a href="https://wordpress.org/support/topic/suggested-changes-to-object-caching-and-transients" target="_blank">here</a>.</p>
                                                <p>For those with some technical prowess, we recommend using the <a href="https://wordpress.org/plugins/query-monitor/" target="_blank">Query Monitor</a> plugin to help pinpoint the cause of your performance issue. If you notice repeated <em>HTTP Requests</em> and/or <em>Transients Set</em> during page loads, there's probably a poorly coded plugin doing something funky or a caching plugin interfering  with WordPress. </p>
                                            </div>
                                        </div>
                                    </div>
	                                <div class="panel panel-default">
		                                <div class="panel-heading" id="faq-performance">
			                                <h4 class="panel-title">
				                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">4. Site errors or broken pages</a>
			                                </h4>
		                                </div>
		                                <div id="collapseFour" class="panel-collapse collapse">
			                                <div class="panel-body">
				                                <p>If you are seeing errors after activating this plugin, a possibility to consider - and a common one - is that there's a plugin compatibility issue.</p>
					                            <p>With over 40,000 WordPress plugins out in the wild, it's pretty well expected that some won't play nicely together. Social Conversion was programmed using industry standard practices and tested with literally hundreds of the most popular plugins. If our code is clashing with another, we ask that you first try disabling other plugins one-by-one to identify the culprit.</p>
				                                <p>If this doesn't help pinpoint the problem, please get in touch with <a title="Click to open support desk. Please attach the system log for any technical issues." data-toggle="tooltip" href="http://support.newexpanse.com" target="_blank">support</a>. We understand how frustrating it is when things don't work right and we'll do our best to straighten this out for you!</p>
			                                </div>
		                                </div>
	                                </div>
                                    <div class="panel panel-default">
                                        <div class="panel-heading" id="faq-safari-scrollbars">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion" href="#collapseFive">5. Missing data/scroll bars in OS X</a>
                                            </h4>
                                        </div>
                                        <div id="collapseFive" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <p>By default, OS X only displays scroll bars when scrolling. This can sometimes lead to confusion when viewing a table (eg, Subscriber data) that requires horizontal scrolling because without a visible scroll bar, there's no visual cue to scroll. This can be resolved by simply following these steps:</p>
                                                <ol>
                                                    <li>Inside OS X, go to <em>System Preferences</em> and click the <em>General</em> icon.</li>
                                                    <li>In the <em>Show scroll bars</em> section, select <strong>Always</strong>.</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
		                            <div class="panel panel-default">
			                            <div class="panel-heading" id="faq-missing-notification">
				                            <h4 class="panel-title">
					                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseSix">6. Missing notification</a>
				                            </h4>
			                            </div>
			                            <div id="collapseSix" class="panel-collapse collapse">
				                            <div class="panel-body">
					                            <p>If a notification isn't delivered as expected, we recommend following these troubleshooting steps:</p>
					                            <ol>
						                            <li>First, check the <a href="admin.php?page=social-conversion-log" target="_blank">Delivery Log</a>. This will quickly tell you whether the notification is queued, already sent and/or experienced an error.</li>
						                            <li>If the notification doesn't appear in the Delivery Log, it could mean that notifications haven't been processed yet. By default, the WordPress Cron system processes notifications every 10 minutes. You can force it to process NOW by clicking the <em>Process now</em> link at the bottom of this page.</li>
						                            <li>The next place to check is the <em>System Status</em> window on the <a href="admin.php?page=social-conversion-dashboard" target="_blank">Dashboard</a>. The Delivery system should report ENABLED and Cron status IDLE. In the unlikely case it shows something else, we kindly ask that you check again in 30 minutes. The system is designed to self-correct itself under most circumstances. If not, please open a <a title="Click to open support desk. Please attach the system log for any technical issues." data-toggle="tooltip" href="http://support.newexpanse.com" target="_blank">support ticket</a>.</li>
						                            <li>If notifications have never worked, we recommend going to your <a href="admin.php?page=social-conversion-settings&rsm-tab=apps" target="_blank">Facebook Apps</a> section and reviewing your Facebook App settings. Click EDIT next to your Facebook App and ensure the App ID and App Secret are correct. Click Save afterwards because the system can also correct configuration problems on Facebook's side.</li>
						                            <li>If notifications have worked in the past, then we recommend disabling <em>Auto Targeting for App Notifications</em> for your Facebook App. This is a relatively new feature Facebook released to improve deliverability by selectively delivering notifications to people who are more likely to engage with them. The feature, which is still in beta, is turned ON by default. To disable, please follow these steps:
							                            <ol style="padding-top:5px;">
								                            <li>Go to <a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a> and click your Facebook App.</li>
								                            <li>Click on <em>Settings</em> and scroll down to the <em>Facebook Canvas</em> section.</li>
								                            <li>Set <em>Auto Targeting for App Notifications</em> to NO.</li>
							                            </ol>
						                            </li>
					                            </ol>
					                            <p>We are genuinely sorry for the frustration this is causing. If the above steps haven't help, please open a <a title="Click to open support desk. Please attach the system log for any technical issues." data-toggle="tooltip" href="http://support.newexpanse.com" target="_blank">support ticket</a> and we will do our best to quickly resolve the issue.</p>
				                            </div>
			                            </div>
		                            </div>
                                </div>

                            </div><!-- /.box-body-->
                        </div><!-- /.box -->
                    </div>
                </div>



                <div class="row clearfix">
                    <div class="col-md-12 column">
                        <div class="box box-rsm-slate box-solid flat" id="support">
                            <div class="box-header with-border">
                                <i class="fa fa-life-ring"></i>
                                <h3 class="box-title">Support</h3>
                            </div>
                            <div class="box-body">
                                <p>If this software was the first ever to be completely glitch-free, that would be truly remarkable. <i class="fa fa-smile-o"></i></p>
                                <p>With that in mind - when something doesn't go as expected, please open a ticket at our <a title="Click to open support desk. Please attach the system log for any technical issues." data-toggle="tooltip" href="http://support.newexpanse.com" target="_blank">support desk</a>.
                                    We are grateful for every one of our customers. We extend that appreciation by making it our priority to take care of all questions, problems or feedback in a quick yet thorough manner.</p>
                                <p>Please attach the system log (below) to your ticket when contacting us. Thanks for giving us the chance to help!</p>
                                <form id="log-download-form" method="post">
                                    <input type="hidden" name="rsm-action" value="log_download"/>
                                    <button type="submit" class="btn bg-rsm-dark-slate btn-flat">
                                        <i class="fa fa-download"></i> Download System Log
                                    </button>
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
