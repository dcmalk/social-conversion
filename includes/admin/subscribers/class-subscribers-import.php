<?php
/**
 * Subscribers Data Import Class
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
 * RSM_Subscribers_Import Class
 *
 * @since 1.0
 */
class RSM_Subscribers_Import {

    /**
     * Get things started.
     *
     * @since 1.0
     * @return void
     */
    public function __construct() {
        require_once RSM_PLUGIN_DIR . 'includes/admin/legacy/parsecsv.lib.php';
    }

    /**
     * Reset/delete transients.
     *
     * @since 1.0
     * @return void
     */
    private function reset() {
        if ( get_transient( 'rsm_sn_csv_headers' ) )  delete_transient( 'rsm_sn_csv_headers' );
        if ( get_transient( 'rsm_sn_csv_file' ) )     delete_transient( 'rsm_sn_csv_file' );
        if ( get_transient( 'rsm_sn_csv_map' ) )      delete_transient( 'rsm_sn_csv_map' );
    }

    /**
     * Ensure the uploaded file is a valid CSV.
     *
     * @since 1.0
     * @param string $file the filename of a specified upload
     * @return bool
     */
    private function is_valid_csv( $file ) {
        // Array of allowed extensions
        $allowed = array( 'csv' );

        // Determine the extension for the uploaded file
        $ext = pathinfo( $file, PATHINFO_EXTENSION );

        // Check if $ext is allowed
        if ( in_array( $ext, $allowed ) ) {
            return true;
        }
        return false;
    }

    /**
     * Get dropdown list of available fields.
     *
     * @since 1.0
     * @param string $parent the name of a particular select element
     * @return string
     */
    public function get_fields( $parent ) {
        // List of available fields
        $fields = array(
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
        asort( $fields );

        // Create HTML dropdown options for each field
        $return = '<option value="">' . 'Unmapped' . '</option>';
        foreach( $fields as $field_name => $field_title ) {
            $return .= '<option value="' . $field_name . '"' . ( $parent == $field_title ? ' selected' : '' ) . '>' . $field_title . '</option>';
        }

        return $return;
    }

    /**
     * Check a given map for duplicates.
     *
     * @since 1.0
     * @param array $fields an array of mapped fields
     * @return bool
     */
    function map_has_duplicates( $fields ) {
        $duplicates = false;

        foreach( $fields as $csv => $db ) {
            if( !empty( $db ) ) {
                if( !isset( $value_{$db} ) ) {
                    $value_{$db} = true;
                } else {
                    $duplicates = true;
                    break;
                }
            }
        }

        return $duplicates;
    }

    /**
     * Begins the import process by uploading the CSV file and extracting headers.
     *
     * @since 1.0
     * @return void
     */
    public function upload() {
        $csv = new RSM_parseCSV();

        $import_file = $_FILES['import_file']['tmp_name'];

        // Make sure we have a valid CSV
        if ( empty( $import_file ) || ! $this->is_valid_csv( $_FILES['import_file']['name'] ) ) {
            wp_redirect( 'admin.php?page=social-conversion-subscribers&rsm-message=subscriber_invalid_csv' );
            exit;
        }

        // Detect delimiter
        $csv->auto( $import_file );

        // Duplicate the temp file so it doesn't disappear on us
        $destination = trailingslashit( WP_CONTENT_DIR ) . basename( $import_file );
        move_uploaded_file( $import_file, $destination );

        // Clear any previous transients
        $this->reset();

        set_transient( 'rsm_sn_csv_headers', $csv->titles );
        set_transient( 'rsm_sn_csv_file', basename( $import_file ) );

        wp_safe_redirect( 'admin.php?page=social-conversion-subscribers&step=2' );
        exit;
    }

    /**
     * Handle mapping of CSV fields to RSM fields.
     *
     * @since 1.0
     * @return void
     */
    public function map_csv() {
        // Check for duplicates
        if( $this->map_has_duplicates( $_POST['csv_fields'] ) ) {
            wp_redirect( 'admin.php?page=social-conversion-subscribers&step=2&rsm-message=subscriber_duplicate_csv' );
            exit;
        }

        // Invert the array
        $fields = array_flip( $_POST['csv_fields'] );

        // Save CSV fields and begin import
        set_transient( 'rsm_sn_csv_fields', serialize( $fields ) );
        $this->process_import();
    }

    /**
     * Import the mapped data to RSM.
     *
     * @since 1.0
     * @return void
     */
    private function process_import() {
        // Check for valid FB List
        $list_id = absint( $_POST['import-list-id'] );
        if ( empty( $list_id ) ) {
            wp_redirect( 'admin.php?page=social-conversion-subscribers&step=2&rsm-message=subscriber_invalid_fb_list' );
            exit;
        }

        // Defaults values to be included even if unmapped
        $defaults = array(
            'uid'          => '',
            'full_name'    => '',
            'first_name'   => '',
            'last_name'    => '',
            'email'        => '',
            'link'         => '',
            'gender'       => '',
            'locale'       => '',
            'timezone'     => '',
            'status'       => '',
            'created_date' => ''
        );

        // Get CSV fields and headers
        $csv_fields = maybe_unserialize( get_transient( 'rsm_sn_csv_fields' ) );
        $csv_fields = wp_parse_args( $csv_fields, $defaults );
        $headers    = get_transient( 'rsm_sn_csv_headers' );

        // Prep for file import
        $filename    = get_transient( 'rsm_sn_csv_file' );
        $import_file = trailingslashit( WP_CONTENT_DIR ) . $filename;

        $csv = new RSM_parseCSV();

        // Detect delimiter
        $csv->auto( $import_file );

        // Map headers to fields
        $uid_key          = array_search( $csv_fields['uid'], $headers );
        $full_name_key    = array_search( $csv_fields['full_name'], $headers );
        $first_name_key   = array_search( $csv_fields['first_name'], $headers );
        $last_name_key    = array_search( $csv_fields['last_name'], $headers );
        $email_key        = array_search( $csv_fields['email'], $headers );
        $link_key         = array_search( $csv_fields['link'], $headers );
        $gender_key       = array_search( $csv_fields['gender'], $headers );
        $locale_key       = array_search( $csv_fields['locale'], $headers );
        $timezone_key     = array_search( $csv_fields['timezone'], $headers );
        $status_key       = array_search( $csv_fields['status'], $headers );
        $created_date_key = array_search( $csv_fields['created_date'], $headers );

        $errors = false;

        // Loop through each row and insert new subscriber
        foreach( $csv->data as $key => $row ) {
            $new_row = array();
            $i = 0;
            foreach ( $row as $column ) {
                $new_row[ $i ] = $column;
                $i ++;
            }

            // Get the column keys
            $subscriber_data = array(
                'list_id'      => $list_id,
                'id'           => $new_row[ $uid_key ],
                'name'         => $new_row[ $full_name_key ],
                'first_name'   => $new_row[ $first_name_key ],
                'last_name'    => $new_row[ $last_name_key ],
                'email'        => $new_row[ $email_key ],
                'link'         => $new_row[ $link_key ],
                'gender'       => $new_row[ $gender_key ],
                'locale'       => $new_row[ $locale_key ],
                'timezone'     => $new_row[ $timezone_key ],
                'status'       => $new_row[ $status_key ],
                'created_date' => $new_row[ $created_date_key ]
            );

            $subscriber_id = db_insert_subscriber( $subscriber_data );
            if ( ! $subscriber_id ) {
                $errors = true;
                rsm_error_handler( 'Error encountered during import.', $subscriber_data );
            }
        }

        // If error encountered, redirect with error notice
        if( $errors ) {
            wp_redirect( 'admin.php?page=social-conversion-subscribers&rsm-message=subscriber_import_error' );
            exit;
        }

        // Import completed successfully
        wp_redirect( 'admin.php?page=social-conversion-subscribers&rsm-message=subscriber_import_success' );
        exit;
    }

    /**
     * Renders the HTML display depending on import step.
     *
     * @since 1.0
     * @return void
     */
    public function display() {
        // Import Step 1
        echo '<form id="subscribers-import-form" class="form-inline" method="post" enctype="multipart/form-data" action="' . admin_url( 'admin.php?page=social-conversion-subscribers' ) . '">';
        if ( ! isset( $_GET['step'] ) ) {
            ?>
            <!--<p class="help-block">To import subscribers, first setup your FB App in the <a href="?page=social-conversion-settings">Settings</a> section. Your FB App <em>must</em> use the same App ID and App Secret used when these subscribers first authenticated your previous Facebook app.</p>-->
            <!--<p class="help-block">To import subscribers, first setup your FB App in the <a href="?page=social-conversion-settings">Settings</a> section. Your FB App <em>must</em> use the same App ID and App Secret used by the previous Facebook app these subscribers originally authenticated.</p>-->
            <!--<p class="help-block">Next, select your CSV file and click the upload button to begin mapping your fields. The CSV file must begin with a header row and cannot include any blank rows. To see an example, review a subscriber export CSV file.</p>-->

	        <p class="help-block">There are several options for importing subscribers depending on your situation.</p>
	        <p class="help-block">The most common scenario is if you have an email-only list of subscribers and want to bring them into Social Conversion. In this case, we recommend emailing your list an incentivized opt-in to your Social Conversion FB list. "Peeling" email subscribers off in this manner is easy and helps ensure responsiveness of your new FB list.</p>
	        <p class="help-block rsm-more-link">For other less common scenarios, <a class="rsm-link">see below</a>.</p>

			<div class="rsm-help-text" style="display:none">
				<span class="rsm-help-text-header">Import Scenarios</span>
		        <ol>
		        <li><p class="help-block">Importing subscribers from another Facebook app into Social Conversion is possible but the actual method depends on a few conditions.</p>
		            <p class="help-block">If you already have all your subscriber data (Name, UID, Email, etc) you can import it using the upload option below. If you don't have this data, please open a <a href="https://support.newexpanse.com" target="_blank">support ticket</a> so we can discuss a custom solution.</p></li>
		        <li><p class="help-block">If you are importing subscribers from a previous Social Conversion backup (export), you can simply use the upload option below.</p></li>
		        </ol>
		        <span class="rsm-help-text-header">Upload Option</span>
		        <p class="help-block">Before using the upload option below, first make sure your FB App is set up in the <a href="?page=social-conversion-settings">Settings</a> section. Your FB App <em>must</em> use the same App ID and App Secret used by the previous Facebook app these subscribers originally authenticated.</p>

		        <p class="help-block">Next, select your CSV file and click the upload button to begin mapping your fields. The CSV file must begin with a header row and cannot include any blank rows. To see an example, download and review a subscribers CSV file (see Export Subscribers window to the right).</p>

		        <p><strong>Select file:</strong></p>
	            <div class="form-group">
	                <div class="input-group">
	                <span class="input-group-btn">
	                    <span class="btn bg-rsm-slate btn-file flat">
	                        <small>Browse...</small> <input type="file" name="import_file"/>
	                    </span>
	                </span>
	                    <input type="text" readonly="" class="form-control flat">
	                </div>
	                <button type="submit" class="btn bg-rsm-slate btn-flat">
	                    <i class="fa fa-upload"></i> Upload CSV
	                    <input type="hidden" name="_wpnonce"
	                           value="<?php echo wp_create_nonce( 'rsm_import_nonce' ); ?>"/>
	                    <input type="hidden" name="rsm-action" value="subscribers_upload"/>
	                </button>
	            </div>
            </div>

            <?php

        // Import Step 2
        } elseif ( isset( $_GET['step'] ) && ( 2 == $_GET['step'] ) ) {
            $fields = get_transient( 'rsm_sn_csv_headers' );
            ?>
            <p class="help-block">To continue, select the FB List to import your subscribers into. As a reminder, this app <em>must</em> use the same App ID and App Secret used by the previous Facebook app these subscribers originally authenticated.</p>
            <p class="help-block">Afterwards, map your CSV fields to their corresponding Subscriber fields. This helps the system understand the contents of your CSV file. When complete, click the Import CSV button to process the import.</p>

            <p><strong>Import into FB List:</strong></p>
            <select name="import-list-id" class="form-control flat" style="margin-bottom:20px;">
                <?php
                $lists = stripslashes_deep( db_get_list_data() );
                if ( $lists ) {
                    foreach ( $lists as $list ) {
                        echo '<option value="' . esc_attr( $list['list_id'] ) . '">' . esc_attr( $list['app_name'] ) . '</option>';
                    }
                }
                ?>
            </select>

            <div class="row clearfix">
                <div class="col-md-12">
                    <div class="row rsm-static-info">
                        <div class="col-md-6"><strong>CSV Field:</strong></div>
                        <div class="col-md-6"><strong>Subscriber Field:</strong></div>
                    </div>

                    <div class="row rsm-static-info">
                        <?php
                        $excludes = array( 'fb list', 'fb_list' );
                        foreach ( $fields as $id => $field ) {
                            if ( ! in_array( strtolower( $field ), $excludes ) ) {
                                $field_label = $field;
                                $field_id    = $field;
                                echo '<div class="row" style="margin-bottom:5px;">';
                                echo '<div class="col-md-6" style="padding-left:32px;">' . $field_label . '</div>';
                                echo '<div class="col-md-6"><select class="form-control flat" name="csv_fields[' . $field_id . ']" >' . $this->get_fields( $field_label ) . '</select></div>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>

                    <button type="submit" class="btn bg-rsm-gray btn-flat">
                        <i class="fa fa-cog"></i> Import CSV
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_import_nonce' ); ?>"/>
                        <input type="hidden" name="rsm-action" value="subscribers_map_csv"/>
                    </button>

                </div>
            </div>

            <?php
        }

     echo '</form>';
    }
}
