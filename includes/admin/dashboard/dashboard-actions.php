<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from RSM.
 *
 * @package     RSM
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Export log data to a log file.
 *
 * @since 1.0
 * @return void
 */
function rsm_log_download() {
    require_once RSM_PLUGIN_DIR . 'includes/admin/dashboard/class-system-log-export.php';

    $log_export = new RSM_System_Log_Export();

    $log_export->export();
}
add_action( 'rsm_log_download', 'rsm_log_download' );
