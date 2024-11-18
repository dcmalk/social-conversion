<?php
/**
 * Scripts
 *
 * @package     RSM
 * @subpackage  Admin/Scripts
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load admin scripts and styles
 *
 * Enqueues the required admin scripts and styles.
 *
 * @since 1.0
 * @param string $hook Page hook
 * @global $rsm_dashboard_page
 * @global $rsm_subscribers_page
 * @global $rsm_segmenting_page
 * @global $rsm_log_page
 * @global $rsm_settings_page
 * @global $rsm_help_page
 * @global $ie_IE
 * @return void
 */
function rsm_load_admin_scripts( $hook ) {
	global $rsm_dashboard_page, $rsm_campaigns_page, $rsm_subscribers_page, $rsm_segmenting_page, $rsm_log_page, $rsm_settings_page, $rsm_help_page;
    global $is_IE, $is_safari, $is_chrome;

    $slug    = RSM_PLUGIN_SLUG;
    $js_dir  = RSM_PLUGIN_URL . 'assets/js/';
    $css_dir = RSM_PLUGIN_URL . 'assets/css/';

    // Scripts to be loaded for all plugin pages
    if ( $is_chrome || $is_safari || $is_IE )
        wp_enqueue_style( $slug . '-rsm-menu-fix', $css_dir . 'rsm-menu.css' );

	// Only load scripts and styles for plugin pages
    if ( ( $hook != $rsm_dashboard_page ) && ( $hook != $rsm_campaigns_page ) && ( $hook != $rsm_subscribers_page ) && ( $hook != $rsm_segmenting_page )&& ( $hook != $rsm_log_page ) && ( $hook != $rsm_settings_page ) && ( $hook != $rsm_help_page ) )
        return;

    // Enqueue admin styles
	wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( $slug . '-rsm-style', $css_dir . 'rsm.min.css' );
	wp_enqueue_style( $slug . '-icheck-blue-style', $css_dir . 'blue.min.css' );
    wp_enqueue_style( $slug . '-rsm-bs-style', $css_dir . 'rsm-bs.min.css' );
    wp_enqueue_style( $slug . '-rsm-alte-style', $css_dir . 'rsm-alte.min.css' );
    wp_enqueue_style( $slug . '-font-awesome-style', $css_dir . 'font-awesome.min.css' );
	wp_enqueue_style( $slug . '-emojionearea-style', $css_dir . 'emojionearea.min.css' );
    wp_enqueue_style( $slug . '-jquery-datetimepicker-style', $css_dir . 'jquery.datetimepicker.min.css' );
    wp_enqueue_style( $slug . '-bs-multiselect-style', $css_dir . 'bootstrap-multiselect.min.css' );
	if ( $is_IE ) wp_enqueue_style( $slug . '-rsm-ie-style', $css_dir . 'rsm-ie.css' );

    // Enqueue admin scripts    // https://pippinsplugins.com/uncached-script-and-style-updates/
	wp_enqueue_media();
	wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script( $slug . '-rsm-admin-script', $js_dir . 'rsm-admin.js', array( 'jquery' ), RSM_VERSION, true );
    wp_localize_script( $slug . '-rsm-admin-script', 'wp_vars', array( 'ajaxurl'    => admin_url( 'admin-ajax.php' ),
                                                                       'ajaxnonce'  => wp_create_nonce( 'rsm_ajax_nonce' ),
                                                                       'app_domain' => rsm_get_option( 'fb_app_domain' ) ) );
    wp_enqueue_script( $slug . '-bootstrap', $js_dir . 'bootstrap.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-rsm-app-script', $js_dir . 'lte-app.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-datetimepicker', $js_dir . 'jquery.datetimepicker.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-flot', $js_dir . 'jquery.flot.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-flot-resize', $js_dir . 'jquery.flot.resize.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-flot-time', $js_dir . 'jquery.flot.time.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-flot-axislabels', $js_dir . 'jquery.flot.axislabels.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-jquery-validate', $js_dir . 'jquery.validate.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-icheck-script', $js_dir . 'icheck.min.js', array( 'jquery' ), RSM_VERSION, true );
	wp_enqueue_script( $slug . '-clipboard-script', $js_dir . 'clipboard.min.js', array( 'jquery' ), RSM_VERSION, true );
	wp_enqueue_script( $slug . '-emojionearea-script', $js_dir . 'emojionearea.min.js', array( 'jquery' ), RSM_VERSION, true );
    wp_enqueue_script( $slug . '-bs-multiselect', $js_dir . 'bootstrap-multiselect.js', array( 'jquery' ), RSM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'rsm_load_admin_scripts', 100 );
