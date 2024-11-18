<?php
/**
 * Plugin Name: Social Conversion
 * Plugin URI: http://socialconversion.io
 * Description: Social Conversion is a Facebook autoresponder and notification tool for businesses and marketers to capture real leads, boost subscriber engagement and continue reselling to their customers.
 * Version: 0.7.5
 * Author: Damon Malkiewicz
 * Author URI: http://newexpanse.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 
 *
 * @package RSM
 * @category Core
 * @author Damon Malkiewicz
 * @version 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'RSM_Social_Conversion' ) ) :

    /**
     * Main RSM_Social_Conversion Class
     *
     * @since 1.0
     */
    final class RSM_Social_Conversion {
        /** Singleton *********************************************************************/

        /**
         * @var RSM_Social_Conversion The main instance of the RSM_Social_Conversion class
         * @since 1.0
         */
        private static $instance;

        /**
         * The floating button instance variable.
         *
         * @var RSM_Floating_Button The floating opt-in widget class
         * @since 1.0
         */
        public $floating_button;

        /**
         * Main RSM_Social_Conversion Instance
         *
         * Insures that only one instance of RSM_Social_Conversion exists in memory at any one time. Also prevents needing
         * to define too many globals.
         *
         * @since 1.0
         * @static
         * @staticvar array $instance
         * @uses RSM_Social_Conversion::check_requirements() Check minimum requirements
         * @uses RSM_Social_Conversion::setup_constants() Setup the constants needed
         * @uses RSM_Social_Conversion::includes() Include the required files
         * @return object The main RSM_Social_Conversion instance
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RSM_Social_Conversion ) ) {
                self::$instance = new RSM_Social_Conversion;
                self::$instance->check_requirements();
                self::$instance->setup_constants();
                self::$instance->includes();

                add_action( 'plugins_loaded', [ self::$instance, 'setup_objects' ] );
            }
            return self::$instance;
        }

        /**
         * Throw error on object clone. The whole idea of the singleton design pattern is that there is a single object
         * therefore, we don't want the object to be cloned.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, 'Cloning forbidden', '1.0' );
        }

        /**
         * Disable unserializing of the class.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden
            _doing_it_wrong( __FUNCTION__, 'Unserializing forbidden', '1.0' );
        }

        /**
        * Check system requirements and displays notices if not met.
        *
        * @access private
        * @since 1.0
        * @global $wp_version
        * @return void
        */
        private function check_requirements() {
            global $wp_version;

            if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
                add_action( 'admin_notices', [ 'RSM_Social_Conversion', 'php_version_notice' ] );
            }

            if ( version_compare( $wp_version, '3.3', '<' ) ) {
                add_action( 'admin_notices', [ 'RSM_Social_Conversion', 'wp_version_notice' ] );
            }

            if ( is_multisite() ) {
                add_action( 'admin_notices', [ 'RSM_Social_Conversion', 'multisite_notice' ] );
            }
        }

        /**
         * Show a warning to sites running PHP version less than 5.3.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function php_version_notice() {
            if ( isset( $_GET['page'] ) && ( 0 === strpos( $_GET['page'], RSM_PLUGIN_SLUG ) ) ) {
                echo '<div class="error"><p>' . 'Your version of PHP is below the minimum version of PHP required by ' . RSM_PLUGIN_NAME . '. Some features may not work properly. Please contact your host and request that your version be upgraded to 5.3 or later.' . '</p></div>';
            }
        }

        /**
         * Show a warning to sites running WordPress version less than 3.3.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function wp_version_notice() {
            if ( isset( $_GET['page'] ) && ( 0 === strpos( $_GET['page'], RSM_PLUGIN_SLUG ) ) ) {
                echo '<div class="error"><p>' . 'Your version of WordPress is below the minimum version of WordPress required by ' . RSM_PLUGIN_NAME . '. Some features may not work properly. Please upgrade to version 3.3 or later.' . '</p></div>';
            }
        }

        /**
         * Show a warning to sites running WordPress Multisite.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function multisite_notice() {
            if ( isset( $_GET['page'] ) && ( 0 === strpos( $_GET['page'], RSM_PLUGIN_SLUG ) ) ) {
                echo '<div class="error"><p>' . RSM_PLUGIN_NAME . ' does not currently support WordPress Multisite. Some features will not work properly. Please install on standard (single site) WordPress.' . '</p></div>';
            }
        }

        /**
         * Setup constants.
         *
         * @access private
         * @since 1.0
         * @global $wpdb
         * @return void
         */
        private function setup_constants() {
            global $wpdb;

            /*----------------------------------------------------------------------------*
            * Plugin constants
            *----------------------------------------------------------------------------*/

            // Plugin version
            if ( ! defined( 'RSM_VERSION' ) ) {
                define( 'RSM_VERSION', '0.7.5' );
            }

            // Plugin name
            if ( ! defined( 'RSM_PLUGIN_NAME' ) ) {
                define( 'RSM_PLUGIN_NAME', 'Social Conversion' );
            }

            // Plugin slug
            if ( ! defined( 'RSM_PLUGIN_SLUG' ) ) {
                define( 'RSM_PLUGIN_SLUG', 'social-conversion' );
            }

            // Plugin folder path
            if ( ! defined( 'RSM_PLUGIN_DIR' ) ) {
                define( 'RSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
            }

            // Plugin folder URL
            if ( ! defined( 'RSM_PLUGIN_URL' ) ) {
                define( 'RSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            }

            // Plugin root file
            if ( ! defined( 'RSM_PLUGIN_FILE' ) ) {
                define( 'RSM_PLUGIN_FILE', __FILE__ );
            }

            // Plugin basename
            if ( ! defined( 'RSM_PLUGIN_BASENAME' ) ) {
                define( 'RSM_PLUGIN_BASENAME', plugin_basename( RSM_PLUGIN_FILE ) );
            }

            // Plugin product URL
            if ( ! defined( 'RSM_PRODUCT_URL' ) ) {
                define( 'RSM_PRODUCT_URL', 'http://socialconversion.io' );
            }

            // User guide URL
            if ( ! defined( 'RSM_USER_GUIDE_URL' ) ) {
                define( 'RSM_USER_GUIDE_URL', 'http://bit.ly/SocialConvGuide' );
            }

            // User guide raw URL
            if ( ! defined( 'RSM_USER_GUIDE_URL_RAW' ) ) {
                define( 'RSM_USER_GUIDE_URL_RAW', 'https://docs.google.com/document/d/18kdotUx3faMm0emXDjv4Jb70JbYpehRRuF6HGVBpawc/edit' );
            }

            /*----------------------------------------------------------------------------*
            * Database constants
            * Note: if renaming a table, rename it inside uninstall.php too
            *----------------------------------------------------------------------------*/

            // FB List table name
            if ( ! defined( 'RSM_LIST_TABLE' ) ) {
                define( 'RSM_LIST_TABLE', $wpdb->prefix . 'rsm_sn_list' );
            }

            // Campaign table name
            if ( ! defined( 'RSM_CAMPAIGN_TABLE' ) ) {
                define( 'RSM_CAMPAIGN_TABLE', $wpdb->prefix . 'rsm_sn_campaign' );
            }

            // Subscriber table name
            if ( ! defined( 'RSM_SUBSCRIBER_TABLE' ) ) {
                define( 'RSM_SUBSCRIBER_TABLE', $wpdb->prefix . 'rsm_sn_subscriber' );
            }

            // Summary table name
            if ( ! defined( 'RSM_SUMMARY_TABLE' ) ) {
                define( 'RSM_SUMMARY_TABLE', $wpdb->prefix . 'rsm_sn_summary' );
            }

            // Notification table name
            if ( ! defined( 'RSM_NOTIFICATION_TABLE' ) ) {
                define( 'RSM_NOTIFICATION_TABLE', $wpdb->prefix . 'rsm_sn_notification' );
            }

            // Autoresponder table name
            if ( ! defined( 'RSM_AUTORESPONDER_TABLE' ) ) {
                define( 'RSM_AUTORESPONDER_TABLE', $wpdb->prefix . 'rsm_sn_autoresponder' );
            }

            // Autoresponder/List table name
            if ( ! defined( 'RSM_AUTORESPONDER_LIST_TABLE' ) ) {
                define( 'RSM_AUTORESPONDER_LIST_TABLE', $wpdb->prefix . 'rsm_sn_autoresponder_list' );
            }

            // Integrated AR/List table name
            if ( ! defined( 'RSM_INTEGRATED_AR_TABLE' ) ) {
                define( 'RSM_INTEGRATED_AR_TABLE', $wpdb->prefix . 'rsm_sn_integrated_ar' );
            }

            // Click table name
            if ( ! defined( 'RSM_CLICK_TABLE' ) ) {
                define( 'RSM_CLICK_TABLE', $wpdb->prefix . 'rsm_sn_click' );
            }

            // Logging table name
            if ( ! defined( 'RSM_LOG_TABLE' ) ) {
                define( 'RSM_LOG_TABLE', $wpdb->prefix . 'rsm_sn_log' );
            }

            // Segment table name
            if ( ! defined( 'RSM_SEGMENT_TABLE' ) ) {
                define( 'RSM_SEGMENT_TABLE', $wpdb->prefix . 'rsm_sn_segment' );
            }

            // Segment detail table name
            if ( ! defined( 'RSM_SEGMENT_DETAIL_TABLE' ) ) {
                define( 'RSM_SEGMENT_DETAIL_TABLE', $wpdb->prefix . 'rsm_sn_segment_detail' );
            }

            /*----------------------------------------------------------------------------*
             * Autoresponder constants
             *----------------------------------------------------------------------------*/

            // AWeber Application ID
            if ( ! defined( 'RSM_AWEBER_APPID' ) ) {
                define( 'RSM_AWEBER_APPID', 'ca65c0ff' );
            }

            // Constant Contact API Key
            if ( ! defined( 'RSM_CTCT_API_KEY' ) ) {
                define( 'RSM_CTCT_API_KEY', '5dq3z4nmsezrzwfyadtt7223' );
            }

            // Constant Contact Secret
            if ( ! defined( 'RSM_CTCT_SECRET' ) ) {
                define( 'RSM_CTCT_SECRET', 'b7R7UxkxTkZ3XP2cK3fbTDDA' );
            }

            // Constant Contact OAuth URL
            if ( ! defined( 'RSM_CTCT_OAUTH_URL' ) ) {
                define( 'RSM_CTCT_OAUTH_URL', 'http://apps.newexpanse.com/ctct/' );
            }

            /*----------------------------------------------------------------------------*
             * Date/time format constants
             *----------------------------------------------------------------------------*/

            // MySql date/time format
            if ( ! defined( 'RSM_DATETIME_MYSQL' ) ) {
                define( 'RSM_DATETIME_MYSQL', 'Y-m-d H:i:s' );
            }

            // Date display format
            if ( ! defined( 'RSM_DATE_OUTPUT' ) ) {
                define( 'RSM_DATE_OUTPUT', 'Y-m-d' );
            }

            // Date display pretty format
            if ( ! defined( 'RSM_DATE_OUTPUT_PRETTY' ) ) {
                define( 'RSM_DATE_OUTPUT_PRETTY', 'F d, Y' );
            }

            // Time display format
            if ( ! defined( 'RSM_TIME_OUTPUT' ) ) {
                define( 'RSM_TIME_OUTPUT', 'g:i A' );
            }
        }

        /**
         * Include required files.
         *
         * @access private
         * @since 1.0
         * @return void
         */
        private function includes() {
            global $rsm_options;

            require_once RSM_PLUGIN_DIR . 'includes/admin/settings.php';
            $rsm_options = rsm_get_settings();

            require_once RSM_PLUGIN_DIR . 'includes/admin/install.php';
            require_once RSM_PLUGIN_DIR . 'includes/class-floating-button.php';
            require_once RSM_PLUGIN_DIR . 'includes/date-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/db-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/error-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/front-end.php';
            require_once RSM_PLUGIN_DIR . 'includes/log-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/misc-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/notification-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/wp-cron.php';
            require_once RSM_PLUGIN_DIR . 'includes/providers/ar-functions.php';
            require_once RSM_PLUGIN_DIR . 'includes/providers/class-rsm-ar.php';
            require_once RSM_PLUGIN_DIR . 'includes/providers/class-rsm-fb.php';

            if ( is_admin() ) {
                require_once RSM_PLUGIN_DIR . 'includes/admin/ajax-functions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/admin-footer.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/admin-pages.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/admin-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/admin-notices.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/help-functions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/misc-functions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/admin-sl.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/plugins.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/scripts.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-functions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/class-rsm-graph.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-graph.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/dashboard-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/campaigns/campaigns-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/campaigns/campaigns-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/settings/settings-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/settings/settings-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/subscribers/subscribers-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/subscribers/subscribers-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/subscribers/class-subscribers-import.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/segmenting/segmenting-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/log/log-actions.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/log/log-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/help/help-page.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/legacy/sysinfo.php';
                require_once RSM_PLUGIN_DIR . 'includes/admin/updates/updates.php';
            }
        }

        /**
         * Setup all objects.
         *
         * @access public
         * @since 1.0
         * @return void
         */
        public function setup_objects() {
            self::$instance->floating_button = new RSM_Floating_Button();
        }
    }

endif; // End if class_exists check

/**
 * The main function responsible for returning the main RSM_Social_Conversion instance to functions everywhere.
 *
 * @since 1.0
 * @return object The main RSM_Social_Conversion instance
 */
function RSM() {
    return RSM_Social_Conversion::instance();
}

// Initiate RSM
RSM();
