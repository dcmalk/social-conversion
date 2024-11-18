<?php
/**
 * Uninstall RSM
 *
 * @package     RSM
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if uninstall constant isn't defined
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

global $wpdb;

// Delete option from options table
delete_option( 'rsm_sn_settings' );
delete_option( 'rsm_sn_version' );
delete_option( 'rsm_sn_updates_lastrun' );
delete_option( 'rsm_sn_proc_state' );
delete_transient( 'rsm_sn_state' );

// Remove all database tables
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_list' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_campaign' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_subscriber' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_summary' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_notification' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_autoresponder' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_autoresponder_list' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_integrated_ar' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_click' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_log' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_segment' );
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'rsm_sn_segment_detail' );

// Cleanup cron events
wp_clear_scheduled_hook( 'rsm_cron_event' );
