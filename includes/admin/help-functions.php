<?php
/**
 * Help Functions
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
 * Echoes the help link.
 *
 * @since 1.0
 * @return void
 */
function rsm_get_help_link( $color_override = '' ){
    $style = empty( $color_override ) ? '' : 'style="color:'. $color_override .' !important;" ';
    echo '<span class="rsm-help-link" ' . $style . 'data-toggle="tooltip" title="Click for help">[<i class="fa fa-question"></i>]&nbsp;</span>';
}

/**
 * Echos the help text for a specific section.
 *
 * @since 1.0
 * @param string $help_section The specific section to render help text for
 * @param bool (optional) display Controls whether help text is shown by default
 * @param string (optional) $style_class Extra styling for help section with default margins
 * @param string (optional) $rsm_mode Specifies add or edit mode for when rendering help text
 * @return void
 */
function rsm_get_help_text( $help_section, $display = false, $style_class = 'rsm-help-margin-default', $rsm_mode = 'add' ) {
    $style_class = empty( $style_class ) ? 'rsm-help-margin-default' : $style_class;
    echo '<div class="rsm-help-text ' . $style_class . '" style="display:' . ( $display ? 'block' : 'none' ) .';">';

    switch ( $help_section ) {

        /*----------------------------------------------------------------------------*
         * Settings / Facebook Apps
         *----------------------------------------------------------------------------*/
        case "settings_app_details":
            ?>
            <p>A Facebook app is required in order to send notifications. Each app can be thought of as a subscriber list (or FB List). After a user opts in and becomes your subscriber, you can then send them messages.</p>
            <p>If you haven't already registered your Facebook account as a developer account, go to the address in step (1), click <em>Register Now</em> (it's free) and follow the simple instructions. Afterwards, follow the steps below to setup your first Facebook app.</p>
            <ol>
                <li>Go to <a href="https://developers.facebook.com/apps/" target="_blank">developers.facebook.com/apps</a> and click <em>Add a New App</em>.</li>
				<li>Enter a Display Name, select a Category (any) and click <em>Create App ID</em>. Answer the security check.</li>
                <li>Click on <em>App Review</em> and set your app to be live/public. <strong>Do <em>NOT</em> start a submission.</strong></li>
                <li>Click on <em>Settings</em>. Copy the Display Name, App ID and App Secret into the fields below.</li>
                <li>Supply links to your Privacy Policy (see an <a href="http://www.inforbiro.com/blog-eng/facebook-app-privacy-policy-example/" target="_blank">example here</a>) and Terms of Service.<br />
                Note: We recommend also uploading an App Icon to personalize/brand your notifications.
                </li>
            </ol>

            <?php
            break;

        case "settings_optin_redirects":
            ?>
            <p>Optional but recommended. These URLs tell the system where to redirect a user after they answer the Facebook authentication prompt.</p>
            <p>If the user chooses to opt-in, the Okay URL is where they will be redirected afterwards. This could be the download page for the promised "lead magnet" you offered in exchange for their opt-in. If they decline FB permissions, you can still try to capture their lead by redirecting them to the Cancel URL.</p>
            <!--<p>Note: if you do not specify a URL, the user will remain on the same page after the FB authentication. This may be ideal when using opt-in button code, which opens FB authentication inside a popup window; however, if using the text link opt-in URL (e.g. inside an email, forum signature, etc) then we recommend entering these URLs otherwise the system won't know where to go after the user authenticates inside Facebook.</p>-->
            <?php
            break;

        case "settings_options":
            ?>
            <p><span class="rsm-help-text-header">Welcome Message</span><br />
            You can send a welcome notification (180 characters maximum) to a user that will be delivered after they opt-in. This is a good way to welcome new users and begin engagement by directing them to your Facebook page, etc. This feature is optional.</p>
            <p>Emojis (picture icons) can be used to make your notifications fun and engaging and help better connect with your audience. Click the smiley face in the top right corner of the Message Text window and select.</p>
            <p>Shortcodes may also be used as placeholders to personalize messages. For example, you can have the system insert a subscriber's first name into your message. A shortcode will use a maximum of 30 characters or less. Keep this value in mind when considering your total message text length.
                Available shortcodes include: <code>{{first_name}}</code>, <code>{{last_name}}</code>, <code>{{full_name}}</code>, <code>{{day_of_week}}</code> and <code>{{date}}</code></p>
            <p>When a user clicks your notification, they will be redirected to your specified URL.</p>

            <p style="margin-top:18px;"><span class="rsm-help-text-header">Email Integration</span><br />
            Capture real email addresses of subscribers and have them automatically added to your email lists. To use this feature, first go to the <a href="admin.php?page=social-conversion-settings&rsm-tab=ar">Email Integrations</a> settings tab and connect your email service. Afterwards, your lists will appear below. You can select one or more email lists for each FB List.</p>

            <?php
            break;

        /*----------------------------------------------------------------------------*
         * Settings / Buttons
         *----------------------------------------------------------------------------*/
        case "settings_floating_button":
            ?>
            <p>To use the floating opt-in widget, you should first turn it on by clicking <em>Enable</em> below.</p>
			<p>Next, select the FB List the floating opt-in widget is linked to. This tells the software which of your Facebook lists (apps) the user will be subscribing to when they click your floating opt-in widget.</p>
			<p>You can further customize the appearance of the floating opt-in widget. <em>Lock Position</em> is the side location where the button will appear (and lock to) on each webpage. <em>Button Text</em> is text that will appear on the button. <em>Text Color</em> and <em>Button Color</em> control the colors of your button.</p>
            <?php
            break;

        case "settings_current_button":
            $btn_url  = RSM_PLUGIN_URL . 'assets/img/buttons/';
            ?>
            <p><span class="rsm-help-text-header">Customization</span><br />
            By default, your opt-in button will appear as shown below. You can change the size of this button by adding <code>&lt;img&gt;</code> tag attributes, <code>height</code> and <code>width</code>, to the opt-in HTML code. For more information, see <a href="http://www.w3schools.com/tags/tag_img.asp" target="_blank">this link</a>.</p>

            <p style="margin-top:18px;"><span class="rsm-help-text-header">Advanced Customization - Hover Effect</span><br />
            It's generally best practice to use <a href="http://www.w3schools.com/cssref/sel_hover.asp" target="_blank">CSS</a> for controlling the appearance of your website; however, for those situations when CSS isn't an option, you can achieve the effect using inline Javascript instead. Consider the following "hover effect" example:</p>
            <div style="margin:10px 0 10px 30px;">
                <img src="<?php echo $btn_url; ?>a01_text01_def.png" onmouseover="this.src='<?php echo $btn_url; ?>a01_text01_gloss.png'" onmouseout="this.src='<?php echo $btn_url; ?>a01_text01_def.png'">
            </div>
            <p>To achieve this effect with your opt-in links, you will need to add a <code>onmouseover</code> and <code>onmouseout</code> event to your opt-in HTML <code>&lt;img&gt;</code> code:</p>
            <div style="margin:10px 0 10px 30px;">
                <code>onmouseover="this.src='<em>YOUR-HOVER-IMAGE-VERSION.png</em>'"</code><br />
                <code>onmouseout="this.src='<em>YOUR-REGULAR-IMAGE-VERSION.png</em>'"</code>
            </div>
            <p>Your final <code>&lt;img&gt;</code> code will look like this:</p>
            <div style="margin:10px 0 10px 30px;">
                <code>&lt;img src="<?php echo $btn_url; ?>a1_text1_def.png" onmouseover="this.src='<?php echo $btn_url; ?>a1_text1_gloss.png'" onmouseout="this.src='<?php echo $btn_url; ?>a1_text1_def.png'"&gt;</code>
            </div>
            <p>Note that this is a single line (no line breaks). For more information, see <a href="http://www.w3schools.com/jsref/event_onmouseover.asp" target="_blank">this link</a>.</p>
            <?php
            break;

        case "settings_available_buttons":
            ?>
            <p>To change change the current opt-in button, select from the list of available buttons and click Save.</p>
            <p>To add and use your own button, follow the steps below.</p>
            <ol>
                <li>Upload your button image(s) to <code><?php echo RSM_PLUGIN_DIR . 'assets/img/buttons/'; ?></code></li>
                <li>Refresh this page and your uploaded button image(s) will be available.</li>
                <li>Select your button image and click Save.</li>
            </ol>
            <p>Alternatively, you can click Select Image to upload a new image or use an existing image from your WordPress Media Library.</p>
            <?php
            break;

        /*----------------------------------------------------------------------------*
         * Settings / Autoresponders
         *----------------------------------------------------------------------------*/
        case "settings_activecampaign_api":
            ?>
            <p>To get your API URL and API Key, follow these steps:</p>
            <ol>
                <li>Log into your <a href="http://www.activecampaign.com/login/" target="_blank">Active Campaign account</a>.</li>
                <li>Go to <em>My Settings</em> and then the <em>Developer</em> tab. See <a href="http://www.activecampaign.com/help/using-the-api/" target="_blank">this link</a> for more information.</li>
                <li>Copy both the <em>API URL</em> and <em>API Key</em> and paste each into their field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, the Active Campaign API uses <strong>single opt-in</strong> (no confirmation required).
            </p>
            <?php   // Note: For double opt-in, first create a form and get the form ID; use that ID with the method for adding the contact
            break;

        case "settings_aweber_api":
            ?>
            <p>To get your Authorization Code, follow these steps:</p>
            <ol>
                <li>Go to the <a href="https://auth.aweber.com/1.0/oauth/authorize_app/<?php echo RSM_AWEBER_APPID; ?>" target="_blank">AWeber authorization page</a>.</li>
                <li>Copy your <em>authorization code</em> and paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">Regardless of your campaign settings, the AWeber API uses <strong>double opt-in</strong> (confirmation required). To enable single opt-in, you must put in a request with AWeber support. See <a href="https://help.aweber.com/hc/en-us/articles/204029206-Why-Was-A-Confirmation-Message-Sent-When-Confirmation-Was-Disabled-" target="_blank">this link</a> for more information.</p>
            <?php
            break;

        case "settings_benchmark_api":
            ?>
            <p>To get your Benchmark API Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://ui.benchmarkemail.com/EditSetting" target="_blank">Benchmark Account Settings page</a>.</li>
                <li>At the bottom of this page, click the <em>generate your API key</em> link.</li>
                <li>Copy <em>Your API key</em> and paste it into the field above.</li>
            </ol>
            <?php
            break;

        case "settings_campaignmonitor_api":
            ?>
            <p>To get your API Key, follow these steps:</p>
            <ol>
                <li>Log into your <a href="http://www.campaignmonitor.com/login/" target="_blank">Campaign Monitor account</a>.</li>
                <li>Go to <em>Manage Account</em> and then <em>API keys</em>. See <a href="http://help.campaignmonitor.com/topic.aspx?t=206" target="_blank">this link</a> for more information.</li>
                <li>Click <em>Show API Key</em> and then copy/paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, Campaign Monitor uses <strong>single opt-in</strong> (no confirmation required). This can be changed inside your campaign settings.</p>
            <?php
            break;

        case "settings_convertkit_api":
            ?>
            <p>To get your ConvertKit API Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://app.convertkit.com/account/edit" target="_blank">ConvertKit Account Info page</a>.</li>
                <li>Copy the <em>API Key</em> and paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, ConvertKit uses <strong>double opt-in</strong> (confirmation required). This can be changed inside the Incentive Email settings of your Form. For instructions, see <a href="http://help.convertkit.com/article/396-single-vs-double-opt-in" target="_blank">this link</a>.<br />
                Note: For consistency, your ConvertKit <em>Forms</em> will be shown inside Social Conversion as email lists.</p>
            <?php
            break;

        case "settings_ctct_api":
            ?>
            <p>To get your Access Token, follow these steps:</p>
            <ol>
                <li>Click to begin <a href="<?php echo rsm_get_ctct_auth_url(); //$oauth->getAuthorizationUrl(); ?>" id="ctct-connect-popup">the Constant Contact authentication process.</a></li>
                <li>After the popup window appears, log into your account.</li>
                <li>Click <em>Allow</em> to grant access to Social Conversion. Your Access Token will then be inserted for you.</li>
                <li>Afterwards, click the <em>Connect</em> button to integrate with Social Conversion.</li>
            </ol>
            <p class="rsm-ar-notes">By default, Constant Contact uses <strong>single opt-in</strong> (no confirmation required). This can be changed inside account settings. For more information, see <a href="http://img.constantcontact.com/docs/pdf/working-with-the-email-confirmed-opt-in-feature-constant-contact.pdf" target="_blank">this link</a>.</p>
            <?php
            break;

        case "settings_getresponse_api":
            ?>
            <p>To get your GetResponse API Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://app.getresponse.com/manage_api.html" target="_blank">GetResponse API Key page</a>.</li>
                <li>Copy <em>My secret API key</em> and paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, GetResponses uses <strong>double opt-in</strong> (confirmation required). This can be changed inside your campaign settings. For instructions, see <a href="http://support.getresponse.com/faq/how-i-edit-opt-in-settings" target="_blank">this link</a>.</p>
            <?php
            break;

        case "settings_icontact_api":
            ?>
            <p>To create an Application ID and Application Password, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://app.icontact.com/icp/core/registerapp/" target="_blank">iContact Generate Application ID page</a>.</li>
                <li>Enter any Application Name, Description and click <em>Get App ID</em>.</li>
                <li>After the App ID is generated, click the <em>enable this AppId for your account</em> link.</li>
                <li>Copy the <em>Application ID</em> and paste it into the field above.</li>
                <li>Create a Password and click the <em>Save</em> button. Enter this <em>Application Password</em> into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">The iContact API uses <strong>single opt-in</strong> only.</p>
            <?php
            break;

        case "settings_infusionsoft_api":
            ?>
            <p>To get your Account ID/Subdomain and API Key, follow these steps:</p>
            <ol>
                <li>Log into your <a href="https://signin.infusionsoft.com/login" target="_blank">Infusionsoft account</a>.</li>
                <li>Your Account ID/Subdomain is the portion of the URL that comes before ".infusionsoft.com". For example, if your URL is <code>https://hy282.infusionsoft.com</code>, then enter <code>hy282</code> is your Account ID/Subdomain.</li>
                <li>For your API Key, go to <em>Admin Settings</em> and then the <em>Application</em> tab.</li>
                <li>At the bottom, enter an <em>API Passphrase</em> (if one doesn't already exist) and click <em>Save</em>. After the page refreshes, copy your <em>Encrypted Key</em> and paste it into the <em>API Key</em> field above. See <a href="http://help.infusionsoft.com/userguides/get-started/tips-and-tricks/api-key" target="_blank">this link</a> for more information.</li>
            </ol>
            <p class="rsm-ar-notes">By default, the Infusionsoft uses <strong>single opt-in</strong> (no confirmation required). For information on setting up double opt-in inside your Infusionsoft account, see <a href="http://help.infusionsoft.com/userguides/campaigns-and-broadcasts/email-broadcast-automation-links/double-opt-in-link" target="_blank">this link</a>.<br />
                Note: For consistency, your Infusionsoft <em>Tags</em> will be shown inside Social Conversion as email lists.</p>
            <?php
            break;

        case "settings_mailchimp_api":
            ?>
            <p>To get your MailChimp API Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://admin.mailchimp.com/account/api/" target="_blank">MailChimp API Keys page</a>.</li>
                <li>Click the <em>Create A Key</em> button.</li>
                <li>Copy the <em>API Key</em> and paste it into the field above.</li>
            </ol>
            <?php
            break;

        case "settings_mailerlite_api":
            ?>
            <p>To get your MailerLite API Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://app.mailerlite.com/integrations/api/" target="_blank">MailerLite Developer API page</a>.</li>
                <li>Copy the <em>API Key</em> and paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, the MailerLite API uses <strong>single opt-in</strong> (no confirmation required).
            <?php
            break;

        case "settings_sendinblue_api":
            ?>
            <p>To get your SendinBlue Access Key, follow these steps:</p>
            <ol>
                <li>Go to your <a href="https://my.sendinblue.com/advanced/apikey" target="_blank">SendinBlue API Key page</a>.</li>
                <li>Copy the Version 2.0 <em>Access Key</em> and paste it into the field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, the SendinBlue API uses <strong>single opt-in</strong> (no confirmation required).
            <?php
            break;

        case "settings_sendreach_api":
            ?>
            <p>To get your SendReach Public and Private Keys, follow these steps:</p>
            <ol>
                <li>Go to your <a href="http://dashboard.sendreach.com/customer/index.php/api-keys/index" target="_blank">SendReach API Keys page</a>.</li>
                <li>Click the <em>Create New API Instance</em> button.</li>
                <li>Copy both the <em>Public key</em> and <em>Private key</em> and paste each into their field above.</li>
            </ol>
            <p class="rsm-ar-notes">By default, the SendReach API uses <strong>single opt-in</strong> (no confirmation required).
            <?php
            break;

        /*----------------------------------------------------------------------------*
         * Settings / Delivery
         *----------------------------------------------------------------------------*/
        case "settings_job_scheduling":
            ?>
            <p>Background tasks handle the processing and delivery of notifications to Facebook. These tasks will run automatically when configured to trigger by a cron job.</p>
            <p>WordPress has a built-in cron service that runs whenever someone visits your website. Low traffic websites may consider using a ping service to visit their website, which in turn triggers WP cron. By default, background tasks triggered by WP cron will never run more frequently than <strong>once every 10 minutes</strong>. Also, it should be noted that other WordPress plugins you have installed may sometimes interfer with WP cron, such as <a href="admin.php?page=social-conversion-help#faq-performance" target="_blank">caching plugins</a>. If you encounter any problems or want background tasks to run more frequently, consider setting up real cron.</p>
            <p>A real cron job is a scheduled task that runs on your server periodically, such as every 10 minutes. Most web hosts provide cron job scheduling.
            Please refer to our help section for <a href="#">setup examples</a> or contact your web host for assistance in setting this up. You may also refer to the documentation of popular providers for setup details: <a href="https://documentation.cpanel.net/display/ALD/Cron+Jobs" target="_blank"><strong>cPanel</strong></a>, <a href="http://download1.parallels.com/Plesk/PP10/10.3.1/Doc/en-US/online/plesk-administrator-guide/plesk-control-panel-user-guide/index.htm?fileName=65208.htm" target="_blank"><strong>Plesk</strong></a> or <a href="http://www.thegeekstuff.com/2011/07/php-cron-job/" target="_blank"><strong>crontab</strong></a>.</p>
            <p>If cron isn't available on your web host, you can use a free, 3rd party web cron service (for example, easycron.com, setcronjob.com, etc).</p>
            <?php
            break;

        /*----------------------------------------------------------------------------*
         * Subscribers
         *----------------------------------------------------------------------------*/

        case "segment_details":
            ?>
            <p>Segments are used to create audiences or subsets of highly targeted subscribers within an FB List. There is no limit to the number of segments or conditions you can add.</p>
            <p>A segment will update automatically with a new subscriber who meets the segment conditions. For example, if you've created a sequence for a segment that targets "<em>females who join before a certain date</em>", any new opt-ins who match these conditions will automatically begin receiving this sequence.</p>
            <p>Segments can also be created automatically by appending <code>&segment=YourSegmentName</code> to your opt-link URL.</p>
            <?php
            break;

        case "segment_criteria":
            ?>
            <p>When creating your segment, you can decide whether a subscriber must match <u><em>all</em></u> your criteria or <u><em>any</em></u> of it to be included.</p>
            <p>The following matching rules can be used:</p>
            <ol>
                <li><strong>is equal</strong> - The target must be an exact match.</li>
                <li><strong>contains</strong> - The value you supply must occur somewhere in the target. For example, "<em>First name contains don</em>" would match anyone named <em>Donna</em>, <em>Donald</em>, <em>McDonald</em>, etc.</li>
                <li><strong>is one of</strong> - Exact match one or more values. When listing multiple values, you must separate them using a comma.</li>
                <li><strong>never</strong> - Target subscribers who have never performed this action.</li>
                <li><strong>any campaign</strong> - Target subscribers who have performed this action at least once.</li>
                <li><strong>before</strong> - Target subscribers who joined before a certain date.</li>
                <li><strong>after</strong> - Target subscribers who joined after a certain date.</li>
            </ol>
            <p>Note that some rules have a "<em>not</em>" counterpart which basically reverses the rule.</p>
            <?php
            break;

        /*----------------------------------------------------------------------------*
         * Campaigns / Setup
         *----------------------------------------------------------------------------*/
        case "campaign_details":
            ?>
            <p>Each campaign should be given a name to track performance and help identify it throughout the system. A detailed description is optional.
            <?php if ( rsm_feature_check( 2 ) ) : ?>
            The following are the types of campaigns you can create:
            </p>
            <ol>
                <li><strong>Broadcast (Regular)</strong> - Your notification is sent immediately to your target subsubscribers.</li>
                <li><strong>Broadcast (Scheduled)</strong> - Your notification will be sent at a specified date/time.</li>
                <li><strong>Sequence</strong> - A series of prewritten notifications are scheduled to deliver at specific, delayed intervals in the future. In function, it is similar to an email autoresponder.</li>
            </ol>
            <?php else : ?>
            When sending an <strong>Broadcast (Regular)</strong> campaign, the notifications are sent immediately.</p>
            <?php endif; ?>

            <?php
            break;

        case "campaign_segmenting":
            ?>
            <p>Campaigns can be setup to target an entire FB List or just a segment of the list. Segments are defined in the <a href="admin.php?page=social-conversion-segmenting">Segmenting</a> section on the Subscribers page.</p>
            <p>As you define your targeting options, the number of targeted subscribers will update in real-time.</p>
            <p class="rsm-sequence">Note: Sequence targeting options cannot be changed after the campaign's been created.</p>

            <?php
            break;

        case "campaign_notifications":
            ?>
            <p><span class="rsm-help-text-header">Notifications</span><br />
            Each Facebook notification is limited to <strong>180 characters</strong>.</p>
            <p>Emojis (picture icons) can be used to make your notifications fun and engaging and help better connect with your audience. Click the smiley face in the top right corner of the Message Text window and select.</p>
            <p>Shortcodes may also be used as placeholders to personalize messages. For example, you can have the system insert a subscriber's first name into your message. A shortcode will use a maximum of 30 characters or less. Keep this value in mind when considering your total message text length.
            Available shortcodes include: <code>{{first_name}}</code>, <code>{{last_name}}</code>, <code>{{full_name}}</code>, <code>{{day_of_week}}</code> and <code>{{date}}</code></p>
            <p>When a user clicks your notification, they will be redirected to your specified URL.</p>
            <div class="rsm-sequence" style="margin-top:18px;">
                <p><span class="rsm-help-text-header">Sequence Notifications</span><br />
                For each sequence message, enter the number of days to wait before sending, relative to the previous message (or subscriber opt-in). Consider the following example:</p>
                <ul style="margin-left:2em;">
                    <li>Message #1, <strong>delay 2</strong> - This will be sent 2 days after a new subscriber opts-in.</li>
                    <li>Message #2, <strong>delay 1</strong> - This will be sent 1 day following the previous message (ie, 3 days after opt-in).</li>
                    <li>Message #3, <strong>delay 6</strong> - This will be sent 6 day following the previous message (ie, 9 days after opt-in). </li>
                </ul>
                <p>If you have existing subscribers (those who have already opted in), they can still be included in your new sequence. In this case, the <em>delay</em> values simply counts from the day the campaign is created.</p>
            </div>

            <?php
            break;

        case "campaign_submit":
            ?>
                <?php if( 'add' == $rsm_mode ) : ?>
                    <p class="rsm-instant">To send this notification immediately, click Send Now.</p>
                    <p class="rsm-scheduled">To schedule this notification for a later delivery date, specify the date, time and click the Schedule button.</p>
                <?php else : ?>
                    <p class="rsm-instant">To update this notification, click Update.</p>
                    <p class="rsm-scheduled">To update this scheduled notification, specify the date, time and click the Update button.</p>
                <?php endif; ?>

                <?php if( 'edit-seq' != $rsm_mode ) : ?>
                    <p class="rsm-sequence">To save this notification to the sequence and return to the Campaigns page, click Save & Exit. To save this notification and add more to the follow-up sequence, click Save & Add More.</p>
                <?php else : ?>
                    <p class="rsm-sequence">To update this notification and return to the Campaigns page, click Update & Exit. To update this notification and add more to the sequence, click Update & Add More.</p>
                <?php endif; ?>

                <?php
            break;
    }
    echo '</div>';
}
