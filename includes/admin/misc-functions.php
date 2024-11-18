<?php
/**
 * Misc Functions
 *
 * @package     RSM
 * @subpackage  Admin/Functions
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Build URL query based on array.
 *
 * @since 1.0
 * @param array $data URL key/pair
 * @return string URL encoded string
 */
function rsm_build_query( $data ) {
    return build_query( array(
		    'list-id'     => isset( $data['list-id'] )     ? $data['list-id']     : null,
		    'segment-id'  => isset( $data['segment-id'] )  ? $data['segment-id']  : null,
		    'campaign-id' => isset( $data['campaign-id'] ) ? $data['campaign-id'] : null,
		    'type'        => isset( $data['type'] )        ? $data['type']        : null,
		    'paged'       => isset( $data['paged'] )       ? $data['paged']       : null,
            'orderby'     => isset( $data['orderby'] )     ? $data['orderby']     : null,
            'order'       => isset( $data['order'] )       ? $data['order']       : null,
            'status'      => isset( $data['status'] )      ? $data['status']      : null,
            's'           => isset( $data['s'] )           ? $data['s']           : null
        )
    );
}

/**
 * Generate an opt-in link.
 *
 * @since 1.0
 * @param int $list_id List ID tied to optin link
 * @return string Opt-in URL
 */
function rsm_generate_optin_link( $list_id ) {
    return home_url( '?rsm-action=opt&id=' . abs( $list_id ), 'http' );
}

/**
 * Gets the opt-in button HTML code.
 *
 * @since 1.0
 * @param string $url The opt-in link URL
 * @return string Opt-in HTML code
 */
function rsm_get_optin_html( $url ) {
    $url   = add_query_arg( 'display', 'popup', $url );
    $btn   = rsm_get_option( 'optin_btn' );
    $html  = '<a href="javascript:void(0);" onclick="window.open(\'' . esc_url( $url ) . '\',\'fbConnect\',\'top=\'+((screen.height/2)-300)+\',left=\'+((screen.width/2)-298)+\',width=596,height=300\');return false;">';
    $html .= '<img src="' . esc_url( $btn ) . '"></a>';

    return $html;
}

/**
 * Generate information about Wordpress install and the system environment.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @return string Information about WP and environment
 */
function rsm_get_sysinfo() {
    global $wpdb;

    // Get a reference to the SysInfo instance
    $sysinfo = RSM_SysInfo::get_instance();

    // Get information from the environment
    $theme                  = wp_get_theme();
    $browser                = $sysinfo->get_browser();
    $plugins                = $sysinfo->get_all_plugins();
    $active_plugins         = $sysinfo->get_active_plugins();
    $memory_limit           = ini_get( 'memory_limit' );
    $memory_usage           = $sysinfo->get_memory_usage();
    $all_options            = $sysinfo->get_all_options();
    $all_options_serialized = serialize( $all_options );
    $all_options_bytes      = round( mb_strlen( $all_options_serialized, '8bit' ) / 1024, 2 );
    $all_options_transients = $sysinfo->get_transients_in_options( $all_options );

    // Begin collecting system information
    $info = '### Begin System Info ###' . "\n\n";

    $info .= 'WordPress Version: ' . "\t" . get_bloginfo( 'version' ) . "\n";
    $info .= 'PHP Version: ' . "\t\t" . PHP_VERSION . "\n";
    $info .= 'MySQL Version: ' . "\t\t" . $wpdb->db_version() . "\n";
    $info .= 'Web Server: ' . "\t\t" . $_SERVER['SERVER_SOFTWARE'] . "\n\n";

    $info .= 'WordPress URL: ' . "\t\t" . get_bloginfo( 'wpurl' ) . "\n";
    $info .= 'Home URL: ' . "\t\t" . get_bloginfo( 'url' ) . "\n\n";

    $info .= 'Content Directory: ' . "\t" . WP_CONTENT_DIR . "\n";
    $info .= 'Content URL: ' . "\t\t" . WP_CONTENT_URL . "\n";
    $info .= 'Plugins Directory: ' . "\t" . WP_PLUGIN_DIR . "\n";
    $info .= 'Plugins URL: ' . "\t\t" . WP_PLUGIN_URL . "\n";
    $info .= 'Uploads Directory: ' . "\t" . ( defined( 'UPLOADS' ) ? UPLOADS : WP_CONTENT_DIR . '/uploads' ) . "\n\n";

    $info .= 'Cookie Domain: ' . "\t\t" . ( defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN ? COOKIE_DOMAIN : 'Disabled' : 'Not set' ) . "\n";
    $info .= 'Multi-Site Active: ' . "\t" . ( is_multisite() ? 'Yes' : 'No' ) . "\n\n";

    $info .= 'PHP cURL Support: ' . "\t" . ( function_exists( 'curl_init' ) ? 'Yes' : 'No' ) . "\n";
    $info .= 'PHP GD Support: ' . "\t" . ( function_exists( 'gd_info' ) ? 'Yes' : 'No' ) . "\n";
    $info .= 'PHP Memory Limit: ' . "\t" . $memory_limit . "\n";
    $info .= 'PHP Memory Usage: ' . "\t" . $memory_usage . "M (" . round( $memory_usage / $memory_limit * 100, 0 ) . "%)\n";
    $info .= 'PHP Post Max Size: ' . "\t" . ini_get( 'post_max_size' ) . "\n";
    $info .= 'PHP Upload Max Size: ' . "\t" . ini_get( 'upload_max_filesize' ) . "\n\n";

    $info .= 'WP Options Count: ' . "\t" . count( $all_options ) . "\n";
    $info .= 'WP Options Size: ' . "\t" . $all_options_bytes . "kb\n";
    $info .= 'WP Options Transients: ' . "\t" . count( $all_options_transients ) . "\n\n";

    $info .= 'WP_DEBUG: ' . "\t\t" . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
    $info .= 'SCRIPT_DEBUG: ' . "\t\t" . ( defined( 'SCRIPT_DEBUG' ) ? SCRIPT_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
    $info .= 'SAVEQUERIES: ' . "\t\t" . ( defined( 'SAVEQUERIES' ) ? SAVEQUERIES ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
    $info .= 'AUTOSAVE_INTERVAL: ' . "\t" . ( defined( 'AUTOSAVE_INTERVAL' ) ? AUTOSAVE_INTERVAL ? AUTOSAVE_INTERVAL : 'Disabled' : 'Not set' ) . "\n";
    $info .= 'WP_POST_REVISIONS: ' . "\t" . ( defined( 'WP_POST_REVISIONS' ) ? WP_POST_REVISIONS ? WP_POST_REVISIONS : 'Disabled' : 'Not set' ) . "\n\n";

    $info .= 'Operating System: ' . "\t" . $browser['platform'] . "\n";
    $info .= 'Browser: ' . "\t\t" . $browser['name'] . ' ' . $browser['version'] . "\n";
    $info .= 'User Agent: ' . "\t\t" . $browser['user_agent'] . "\n\n";

    $info .= 'Active Theme:' . "\n\n";
    $info .= "- " . $theme->get( 'Name' ) . " " . $theme->get( 'Version' ) . "\n";
    $info .= $theme->get( 'ThemeURI' ) . "\n\n";

    $info .= 'Active Plugins:' . "\n\n";
    foreach ( $plugins as $plugin_path => $plugin ) {

        // Only show active plugins
        if ( in_array( $plugin_path, $active_plugins ) ) {
            $info .= '- ' . $plugin['Name'] . ' ' . $plugin['Version'] . "\n";

            if ( isset( $plugin['PluginURI'] ) ) {
                $info .= '  ' . $plugin['PluginURI'] . "\n";
            } // end if

            $info .= "\n";
        } // end if
    } // end foreach

    $info .= '### End System Info ###' . "\n\n";
    return $info;
}

/**
 * Gets the product edition.
 *
 * @since 1.0
 * @return string Edition of product
 */
function rsm_get_edition() {
    $id = rsm_get_option( 'price_id' );

    switch( $id ) {
        case 1:
            $edition = 'Lite';
            break;
        case 2:
            $edition = 'Basic';
            break;
        case 3:
            $edition = 'Professional';
            break;
	    case 4:
		    $edition = 'Ultimate';
		    break;
    }
    return isset( $edition ) ? $edition : '';
}

/**
 * Gets the product license.
 *
 * @since 1.0
 * @return string License of product
 */
function rsm_get_license() {
//	$limit = (int) rsm_get_option( 'limit' );
//	$license = ( 1 == $limit ? 'Single site' : ( 0 == $limit ? 'Agency' : 'Multisite' ) ); // single site


	$id = rsm_get_option( 'price_id' );

	switch( $id ) {
		case 1:
		case 2:
		case 3:
			$license = 'Personal';
			break;
		case 4:
			$license = 'Agency';
			break;
	}
	return isset( $license ) ? $license : '';
}

/**
 * Checks whether a feature is available for this edition.
 *
 * @since 1.0
 * @param int $min The minimum required level for this feature
 * @return bool True if this edition is greater than/equal to the min required, otherwise false
 */
function rsm_feature_check( $min ) {
    return ( (int) $min <= (int) rsm_get_option( 'price_id' ) );
}

/**
 * Outputs a message.
 *
 * @since 1.0
 * @param string $message The message to display
 * @param bool (optional) $flush Indicates whether to flush the message (default: true)
 * @return void
 */
function rsm_show_message( $message = '', $flush = true ) {
    echo $message;

    if ( $flush ) {
        ob_flush();
        flush();
    }
}

/**
 * Gets a file's extension.
 *
 * @since 1.0
 * @param string $path The path to the file
 * @return string The file extension, if present
 */
function rsm_get_file_ext( $path ) {
    $ext = pathinfo( $path, PATHINFO_EXTENSION );
    return $ext;
}
