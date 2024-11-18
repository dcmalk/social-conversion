<?php
/**
 * Settings
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
 * Get all plugin settings.
 *
 * @since 1.0
 * @return array RSM settings
 */
function rsm_get_settings() {
    return get_option( 'rsm_sn_settings' );
}

/**
 * Get default Options values.
 *
 * @since 1.0
 * @return array RSM settings
 */
function rsm_get_settings_default() {
    // Get domains and URLs
    $home_parts   = parse_url( home_url() );

    // Cron settings
    $cron_token   = substr( md5( uniqid( mt_rand(), true ) ), 0, 12 );
    $cron_url     = home_url( '?rsm-action=rcron&tk=' . $cron_token );
    $cron_command = '/usr/bin/env curl -s "' . $cron_url . '" >/dev/null 2>&1';
    $cron_alt_cmd = 'lynx -dump "' . $cron_url . '" >/dev/null 2>&1';

	// Return settings values (static)
	$options = array(
		'fb_app_domain'      => $home_parts['host'],
		'fb_app_domain2'     => 'newexpanse.com',
		'fb_website_url'     => home_url( '', 'http' ),
		'fb_app_https'       => home_url( '?rsm-action=iframe', 'https' ),
		'fb_app_alt_https'   => rsm_get_fb_app_alt_https(),
		'optin_btn'          => RSM_PLUGIN_URL . 'assets/img/buttons/a1_text1_def.png',
		'autorun'            => 1,
		'cron_type'          => 'wp',
		'cron_token'         => $cron_token,
		'cron_url'           => $cron_url,
		'cron_command'       => $cron_command,
		'cron_alt_cmd'       => $cron_alt_cmd,
		'proc_firstrun'      => '0000-00-00 00:00:00',
		'proc_lastrun'       => '0000-00-00 00:00:00',
		'proc_totalrun'      => 0,
		'float_status'       => 0,
		'float_list_id'      => 0,
		'float_segment_id'   => 0,
		'float_text'         => 'Get Notifications',
		'float_color'        => '#FFFFFF',
		'float_button_color' => '#357CA5',
		'float_position'     => 'right'
    );

    return $options;
}

/**
 * Returns a specified setting if exists or default if not.
 *
 * @since 1.0
 * @param string $key The key to get
 * @param mixed $default (optional)
 * @return mixed
 */
function rsm_get_option( $key = '', $default = false ) {
    global $rsm_options;
    return ( ! empty( $rsm_options[ $key ] ) ) ? $rsm_options[ $key ] : $default;
}

/**
 * Updates an RSM setting value in both the db and the global variable.
 *
 * @since 1.0
 * @param string|array $arg The key or array of keys to update
 * @param mixed $value The value to set the key to
 * @return bool True if updated, false if not
 */
function rsm_update_option( $arg = '', $value = false ) {
    // If no arguments, exit
    if ( empty( $arg ) )
        return false;

    // First get the current settings
    $options = rsm_get_settings(); //get_option( 'rsm_sn_settings' );

    // Next try to update the value
    if ( ! is_array( $arg) ) {
        $options[ $arg ] = $value;
    } else {
        foreach ( $arg as $key => $value ) {
            $options[ $key ] = $value;
        }
    }
    $updated = update_option( 'rsm_sn_settings', $options );

    // If it updated, then update the global variable
    if ( $updated ){
        global $rsm_options;
        $rsm_options = $options;
    }

    return $updated;
}

/**
 * Removes an RSM setting value in both the db and the global variable.
 *
 * @since 1.0
 * @param string|array $arg The key or array of keys to delete
 * @return bool True if updated, false if not.
 */
function rsm_delete_option( $arg = '' ) {
    // If no key, exit
    if ( empty( $arg ) )
        return false;

    // First get the current settings
    $options = get_option( 'rsm_sn_settings' );

    // Next let's try to update the value
    if ( ! is_array( $arg) ) {
        if( isset( $options[ $arg ] ) ) {
            unset( $options[ $arg ] );
        }
    } else {
        foreach ( $arg as $key ) {
            if( isset( $options[ $key ] ) ) {
                unset( $options[ $key ] );
            }
        }
    }
    $updated = update_option( 'rsm_sn_settings', $options );

    // If it updated, then update the global variable
    if ( $updated ){
        global $rsm_options;
        $rsm_options = $options;
    }

    return $updated;
}

/**
 * Gets the license key and status.
 *
 * @since 1.0
 * @return array Array containing license key and status
 */
function rsm_get_settings_sl() {
    return array( trim( (string) rsm_get_option( 'sl_tk' ) ), trim( (string) rsm_get_option( 'sl_enum' ) ) );
}

/**
 * Gets the FB alternative Secure Canvas URL.
 *
 * @since 1.0
 * @return string FB alt Secure Canvas URL, including license payment_id and hex-encoded redirect URL.
 */
function rsm_get_fb_app_alt_https() {
    return 'https://apps.newexpanse.com/fbsc.php?rsm-action=iframe&pid=' . rsm_get_option( 'payment_id' ) . '&rid='  . bin2hex( home_url() );
}
