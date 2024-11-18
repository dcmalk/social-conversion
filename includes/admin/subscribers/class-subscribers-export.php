<?php
/**
 * Subscribers Data Export Class
 *
 * This is the class for handling all subscriber export methods.
 *
 * @package     RSM
 * @subpackage  Admin/Subscribers
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RSM_Subscribers_Export Class
 *
 * @since 1.0
 */
class RSM_Subscribers_Export {
	/**
	 * Set the export headers.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function headers() {
        rsm_set_timeout( 0 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=social-conv-subscribers-' . date( 'Y-m-d' ) . '.csv' );
		header( 'Expires: 0' );
	}

	/**
	 * Set the CSV columns.
	 *
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
            'app_name'     => 'FB List',
            'uid'          => 'User ID',
            'full_name'    => 'Full Name',
            'first_name'   => 'First Name',
            'last_name'    => 'Last Name',
            'email'        => 'Email',
            'link'         => 'Profile Link',
            'gender'       => 'Gender',
            'locale'       => 'Locale',
            'timezone'     => 'UTC',
            'status'       => 'Status',
            'created_date' => 'Opt-in Date'
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
		foreach ( $data as $row ) {
			$i = 1;
			foreach ( $row as $col_id => $column ) {
				// Make sure the column is valid
				if ( array_key_exists( $col_id, $cols ) ) {
					echo '"' . $column . '"';
					echo count( $cols ) + 1 == $i ? '' : ',';
				}

				$i++;
			}
			echo "\r\n";
		}
	}

	/**
	 * Get the data being exported.
	 *
	 * @since 1.0
	 * @return array $data Data for export
	 */
	public function get_data() {
        return db_get_subscriber_data( array( 'list-id' => $_POST['export-list-id'] ), false, true );
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

		// Output CSV columns (headers)
		$this->csv_cols_out();

		// Output CSV rows
		$this->csv_rows_out();

		exit;
	}
}
