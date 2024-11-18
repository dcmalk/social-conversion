<?php
/**
 * Admin Plugins
 *
 * @package     RSM
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugins row action links.
 *
 * @since 1.0
 * @param array $links Already defined action links
 * @param string $file Plugin file path and name being processed
 * @return array $links
 */
function rsm_plugin_action_links( $links, $file ) {
	if ( RSM_PLUGIN_BASENAME == $file ) {
        // If valid license, show link to Settings otherwise License Activation link
		if ( rsm_transient_state() ) {
			$rsm_link = '<a href="' . admin_url( 'admin.php?page=' . RSM_PLUGIN_SLUG . '-settings' ) . '">' . esc_html( 'Settings' ) . '</a>';
		} else {
			$rsm_link      = '<a href="' . admin_url( 'admin.php?page=' . RSM_PLUGIN_SLUG . '-license' ) . '">' . esc_html( 'Activate License' ) . '</a>';
			$rsm_help_link = '<a href="' . RSM_USER_GUIDE_URL_RAW . '#heading=h.vh042n1bmpm2" target="_blank">' . esc_html( 'Help Activating' ) . '</a>';
			array_unshift( $links, $rsm_help_link );
		}

		// Add to links
		array_unshift( $links, $rsm_link );

		// Remove Edit link
        if ( isset( $links['edit'] ) ) unset( $links['edit']);
	}
	return $links;
}
add_filter( 'plugin_action_links', 'rsm_plugin_action_links', 10, 2 );

/**
 * Plugin row meta links.
 *
 * @since 1.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function rsm_plugin_row_meta( $input, $file ) {
    if ( RSM_PLUGIN_BASENAME == $file ) {
        // If valid license, show extra meta link rows
        if ( rsm_transient_state() ) {
            $links = array( '<a href="' . admin_url( 'admin.php?page=' . RSM_PLUGIN_SLUG . '-help#support' ) . '">' . esc_html( 'Get support' ) . '</a>' );
	        $links[] ='<a href="' . RSM_USER_GUIDE_URL . '" target="_blank">' . esc_html( 'User Guide' ) . '</a>';
            $input = array_merge( $input, $links );
        }
    }
    return $input;
}
add_filter( 'plugin_row_meta', 'rsm_plugin_row_meta', 10, 2 );

/**
 * Updates information on the "View version x.x details" page with custom data. This should be called after
 * EDD_SL_Plugin_Updater's plugins_api_filter() is applied.
 *
 * @param mixed $_data
 * @param string $_action
 * @param object $_args
 * @return object $_data
 */
function rsm_plugins_api_filter( $_data, $_action = '', $_args = null ) {
    if ( ( $_action != 'plugin_information' ) || ! isset( $_args->slug ) || ( $_args->slug != RSM_PLUGIN_SLUG ) ) {
        return $_data;
    }

    // Set custom plugin detail
    $_data->version = $_data->new_version;
    $_data->requires = '3.3';
    $_data->author = '<a href="http://newexpanse.com" target="_blank">Damon Malkiewicz</a>';
    $_data->banners = array();
    $_data->banners['low'] = 'https://s3.amazonaws.com/socialconversion/banner-772x250.png';

    return $_data;
}
add_filter( 'plugins_api', 'rsm_plugins_api_filter', 11, 3 );
