<?php
/**
 * System Log Data Export Class
 *
 * This is the class for handling all log export methods.
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RSM_System_Log_Export Class
 *
 * @since 1.0
 */
class RSM_System_Log_Export {
	/**
	 * Set the export headers.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		nocache_headers();
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename=social-conv-log-' . date( 'Y-m-d' ) . '.txt' );
		header( "Expires: 0" );
	}

	/**
	 * Output the system information.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function sysinfo_out() {
		$data = rsm_get_sysinfo();
		echo $data . "\r\n";
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'log_id'           => 'log_id',
			'type'             => 'type',
			'description'      => 'description',
			'meta'             => 'meta',
			'backtrace'        => 'backtrace',
			'created_date'     => 'created_date',
			'created_date_gmt' => 'created_date_gmt'
		);

		return $cols;
	}

	/**
	 * Output the CSV columns.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function csv_cols_out() {
		$cols = $this->csv_cols();
		$i = 1;
		foreach( $cols as $col_id => $column ) {
			echo '"' . $column . '"';
			echo count( $cols ) == $i ? '' : ',';
			$i++;
		}
		echo "\r\n";
	}

	/**
	 * Output the CSV rows.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function csv_rows_out() {
		$data = stripslashes_deep( $this->get_data() );
		$cols = $this->csv_cols();

		// Output each row
        if ( $data ) {
            foreach ( $data as $row ) {
                $i = 1;
                foreach ( $row as $col_id => $column ) {
                    // Make sure the column is valid
                    if ( array_key_exists( $col_id, $cols ) ) {
                        echo '"' . $column . '"';
                        echo count( $cols ) + 1 == $i ? '' : ',';
                    }

                    $i ++;
                }
                echo "\r\n";
            }
        }
	}

	/**
	 * Get the data being exported.
	 *
	 * @since 1.0
	 * @return array $data Data for export
	 */
	public function get_data() {
		return db_get_log_for_export( 'error' );
	}

	/**
	 * Perform the export.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function export() {
		// Set headers
		$this->headers();

		// Output system information
		$this->sysinfo_out();

		// Output separator
		echo str_repeat("-", 50) . "\r\n";

		// Output CSV columns (headers)
		$this->csv_cols_out();

		// Output CSV rows
		$this->csv_rows_out();

		exit;
	}
}
