<?php
/**
 * Install Function
 *
 * @package     RSM
 * @subpackage  Admin/Install
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Runs on plugin install to initialize options settings, create database tables and maybe apply updates.
 *
 * @since 1.0
 * @return void
 */
function rsm_install() {
    // Initialize options
    rsm_init_options();

    // Create database tables
    rsm_create_db();

    // Maybe run updates
    rsm_maybe_update();

	// First run items
	rsm_first_run();
}
register_activation_hook( RSM_PLUGIN_FILE, 'rsm_install' );

/**
 * Initialize WP options.
 *
 * Note: the following options don't autoload - rsm_sn_version, rsm_sn_updates_lastrun, rsm_sn_proc_state
 *
 * @since 1.0
 * @return void
 */
function rsm_init_options() {
    global $rsm_options;

    // Set defaults if no Options exist
    if ( ! $rsm_options ) {
        $rsm_options = rsm_get_settings_default();
        update_option( 'rsm_sn_settings', $rsm_options );
    }

    // Add/update options that shouldn't autoload
    update_option( 'rsm_sn_proc_state', 0, 'no' );  // 0=false

    // Update versions
    add_option( 'rsm_sn_version', RSM_VERSION, '', 'no' );
}

/**
 * Creates the Social Conversion database tables.
 *
 * @since 1.0
 * @global $wpdb Wordpress database object
 * @return void
 */
function rsm_create_db() {
    global $wpdb;

    // Create database tables
    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_LIST_TABLE . " (
			  list_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  app_name VARCHAR(200) NOT NULL DEFAULT '',
			  app_id VARCHAR(100) NOT NULL DEFAULT '',
			  app_secret VARCHAR(100) NOT NULL DEFAULT '',
			  okay_url VARCHAR(2083) NOT NULL DEFAULT '',
			  cancel_url VARCHAR(2083) NOT NULL DEFAULT '',
			  show_welcome CHAR(1) NOT NULL DEFAULT 'F',
			  welcome_msg VARCHAR(360) NOT NULL DEFAULT '',
			  welcome_url VARCHAR(2083) NOT NULL DEFAULT '',
			  redirect_type CHAR(1) NOT NULL DEFAULT 'O',
			  integrate_ar CHAR(1) NOT NULL DEFAULT 'F',
			  optin_url VARCHAR(2083) NOT NULL DEFAULT '',
			  created_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  created_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (list_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS " . RSM_CAMPAIGN_TABLE . " (
              campaign_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
              segment_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
              campaign_name VARCHAR(100) NOT NULL DEFAULT '',
              campaign_desc VARCHAR(1024) NOT NULL DEFAULT '',
              type CHAR(1) NOT NULL DEFAULT 'I',
              status CHAR(1) NOT NULL DEFAULT 'I',
              created_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
              created_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
              PRIMARY KEY (campaign_id),
              INDEX ix_list_id (list_id),
              INDEX ix_segment_id (segment_id),
              INDEX ix_type (type)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_SUBSCRIBER_TABLE . " (
			  subscriber_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  uid VARCHAR(32) NOT NULL DEFAULT '',
			  full_name VARCHAR(200) NOT NULL DEFAULT '',
			  first_name VARCHAR(100) NOT NULL DEFAULT '',
			  last_name VARCHAR(100) NOT NULL DEFAULT '',
			  email VARCHAR(255) NOT NULL DEFAULT '',
			  link VARCHAR(255) NOT NULL DEFAULT '',
			  gender VARCHAR(16) NOT NULL DEFAULT '',
			  locale VARCHAR(16) NOT NULL DEFAULT '',
			  timezone SMALLINT NOT NULL DEFAULT 0,
			  status CHAR(1) NOT NULL DEFAULT 'A',
			  created_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  created_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (subscriber_id),
			  INDEX ix_list_id (list_id),
			  INDEX ix_status (status),
			  INDEX ix_created_date (created_date),
			  INDEX ix_locale (locale),
			  UNIQUE INDEX uq_uid (uid)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_SUMMARY_TABLE . " (
			  summary_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  campaign_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  message VARCHAR(360) NOT NULL DEFAULT '',
			  redirect_url VARCHAR(2083) NOT NULL DEFAULT '',
			  redirect_type CHAR(1) NOT NULL DEFAULT 'O',
			  delay SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			  delay_offset INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  schedule_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  schedule_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (summary_id),
              INDEX ix_list_id (list_id),
              INDEX ix_campaign_id (campaign_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_NOTIFICATION_TABLE . " (
			  notification_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  campaign_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  summary_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  subscriber_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  message VARCHAR(360) NOT NULL DEFAULT '',
			  redirect_url VARCHAR(2083) NOT NULL DEFAULT '',
			  redirect_type CHAR(1) NOT NULL DEFAULT 'O',
			  type CHAR(1) NOT NULL DEFAULT 'B',
			  status CHAR(1) NOT NULL DEFAULT 'N',
			  created_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  created_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  send_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  send_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  sent_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  sent_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (notification_id),
			  INDEX ix_list_id (list_id),
			  INDEX ix_campaign_id (campaign_id),
			  INDEX ix_summary_id (summary_id),
			  INDEX ix_subscriber_id (subscriber_id),
			  INDEX ix_status_sent_date (status, sent_date),
			  INDEX ix_status (status),
			  UNIQUE INDEX uq_camp_summary_sub_id (campaign_id, summary_id, subscriber_id )
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_AUTORESPONDER_TABLE . " (
              ar_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              ar_name VARCHAR(100) NOT NULL DEFAULT '',
              api_key VARCHAR(512) NOT NULL DEFAULT '',
              options VARCHAR(4096) NOT NULL DEFAULT '',
              connected CHAR(1) NOT NULL DEFAULT 'F',
			  PRIMARY KEY (ar_id),
			  UNIQUE INDEX uq_ar_name (ar_name)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_AUTORESPONDER_LIST_TABLE . " (
              ar_list_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              ar_name VARCHAR(100) NOT NULL DEFAULT '',
              ar_list_name VARCHAR(255) NOT NULL DEFAULT '',
              ar_list_value VARCHAR(100) NOT NULL DEFAULT '',
			  PRIMARY KEY (ar_list_id),
			  INDEX ix_ar_list_value (ar_list_value)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_INTEGRATED_AR_TABLE . " (
              int_ar_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
              ar_name VARCHAR(100) NOT NULL DEFAULT '',
              ar_list_value VARCHAR(100) NOT NULL DEFAULT '',
			  PRIMARY KEY (int_ar_id),
			  INDEX ix_ar_list_value (ar_list_value)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_CLICK_TABLE . " (
			  click_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  subscriber_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  notification_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  click_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  click_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  PRIMARY KEY (click_id),
			  INDEX ix_click_date (click_date),
			  INDEX ix_subscriber_id (subscriber_id)
			  INDEX ix_notification_id (notification_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

    $sql = "CREATE TABLE IF NOT EXISTS " . RSM_LOG_TABLE . " (
			  log_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  type CHAR(1) NOT NULL DEFAULT 'E',
			  description VARCHAR(1024) NOT NULL DEFAULT '',
			  meta TEXT NULL,
			  backtrace TEXT NULL,
			  created_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			  created_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  			  PRIMARY KEY (log_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS " . RSM_SEGMENT_TABLE . " (
			  segment_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  segment_name VARCHAR(100) NOT NULL DEFAULT '',
			  list_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  match_type VARCHAR(3) NOT NULL DEFAULT 'any',
			  PRIMARY KEY (segment_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS " . RSM_SEGMENT_DETAIL_TABLE . " (
			  segment_detail_id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  segment_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
			  field VARCHAR(16) NOT NULL DEFAULT '',
			  rule VARCHAR(16) NOT NULL DEFAULT '',
			  value VARCHAR(4096) NOT NULL DEFAULT '',
			  PRIMARY KEY (segment_detail_id),
              INDEX ix_segment_id (segment_id)
			  ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$wpdb->query( $sql );

}

/**
 * On first run after activation, run certain tasks.
 *
 * @since 1.0
 * @return void
 */
function rsm_first_run() {
	// Check if any FB lists exist
	if (false == db_get_list_data()) {
		// Set transient for 5 days
		set_transient( 'rsm_sn_no_lists', true, 5 * DAY_IN_SECONDS );
	} else {
		// Otherwise, delete the transient
		delete_transient( 'rsm_sn_no_lists' );
	}
}
