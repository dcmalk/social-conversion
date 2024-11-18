<?php
/**
 * Admin Actions
 *
 * @package     RSM
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all RSM actions sent via POST and GET by looking for the 'rsm-action'
 * request and running do_action() to call the function
 *
 * @since 1.0
 * @return void
 */
function rsm_process_actions() {
	if ( isset( $_POST['rsm-action'] ) ) {
		do_action( 'rsm_' . $_POST['rsm-action'], $_POST );
	}

	if ( isset( $_GET['rsm-action'] ) ) {
		do_action( 'rsm_' . $_GET['rsm-action'], $_GET );
	}
}
add_action( 'admin_init', 'rsm_process_actions' );
