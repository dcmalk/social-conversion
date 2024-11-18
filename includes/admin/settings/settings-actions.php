<?php
/**
 * Settings Actions
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
 * Listens for when a save list button is clicked and adds/updates the list.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_list_submit( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_settings_nonce' ) ) {
        wp_die( 'Submit list: Nonce is invalid', 'Error' );
    }

    // Validate form
    if ( rsm_validate_list( $data ) ) {

	    // Set parameters for update Facebook App
	    $settings = rsm_get_settings();
	    $params   = array(
		    'app_name'             => $data['app-name'],
		    'app_domains'          => json_encode( array( $settings['fb_app_domain'], $settings['fb_app_domain2'] ) ),
		    'supported_platforms ' => json_encode( array( 'WEB', 'CANVAS' ) ),
		    'secure_canvas_url'    => $settings['fb_app_alt_https'],
		    'website_url'          => $settings['fb_website_url']
	    );

	    // Update the Facebook App via Graph API
	    try {
		    $rsm_fb = new RSM_Facebook( array( 'app_id' => $data['app-id'], 'app_secret' => $data['app-secret'] ) );
		    $response = $rsm_fb->set_app_settings( $params );
	    } catch ( Exception $e ) {
		    rsm_exception_handler( $e );
	    }

	    // If App successfully created, continue
	    if ( ( isset( $response['code'] ) && '200' == $response['code'] ) ) {

		    // Insert or update list based on rsm-mode; queue welcome message and save integrated ar lists
		    if ( 'add' == $data['rsm-mode'] ) {
			    $list_id = db_insert_list( $data );
			    $result = $list_id ? 'list_inserted' : 'list_insert_error';
			    if ( $list_id ) {
				    $data['list-id'] = $list_id;
				    delete_transient( 'rsm_sn_no_lists' );
				    if ( db_insert_welcome( $data ) ) {
					    if ( 'T' == $data['opt-ar'] ) {
						    $result  = db_insert_integrated_ar( rsm_get_ar_data( $data ) ) ? $result : 'integrated_insert_error';
					    }
				    } else {
					    $result = 'welcome_insert_error';
				    }
			    }
		    } else {
			    if ( db_update_list( $data ) ) {
				    if ( db_update_welcome( $data ) ) {
					    if ( db_update_integrated_ar( $data['list-id'], rsm_get_ar_data( $data ) ) ) {
						    $result = 'list_updated';
						    delete_transient( 'rsm_sn_no_lists' );
					    } else {
						    $result = 'integrated_update_error';
					    }
				    } else {
					    $result = 'welcome_update_error';
				    }
			    } else {
				    $result = 'list_update_error';
			    }
		    }

	    } else {
		    $result = 'list_app_error';
	    }

        wp_redirect( 'admin.php?page=social-conversion-settings&rsm-message=' . $result );
        exit;
    }
}
add_action( 'rsm_list_submit', 'rsm_list_submit' );

/**
 * Listens for when a delete list button is clicked and deletes the list.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_delete_list( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_settings_nonce' ) ) {
        wp_die( 'Delete list: Nonce is invalid', 'Error' );
    }

    // Delete list
    $result = db_delete_list( $data['list-id'] ) ? 'list_deleted' : 'list_delete_error';

    wp_redirect( 'admin.php?page=social-conversion-settings&rsm-message=' . $result );
    exit;
}
add_action( 'rsm_delete_list', 'rsm_delete_list' );

/**
 * Listens for when the floating opt-in widget page's save button is clicked.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_float_button_submit( $data ) {
	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_settings_nonce' ) ) {
		wp_die( 'Submit floating button: Nonce is invalid', 'Error' );
	}

	$float_status = isset( $data['opt-float-button'] ) ? (int) $data['opt-float-button'] : null;
	if ( null !== $float_status ) {

		if ( $float_status ) {
			rsm_update_option( array(
					'float_status'       => 1,
					'float_list_id'      => isset( $data['float-list-id'] )      ? (int) $data['float-list-id']                       : 0,
					'float_segment_id'   => isset( $data['float-segment-id'] )   ? (int) $data['float-segment-id']                    : 0,
					'float_text'         => isset( $data['float-text'] )         ? sanitize_text_field( $data['float-text'] )         : "",
					'float_color'        => isset( $data['float-color'] )        ? sanitize_text_field( $data['float-color'] )        : "",
					'float_button_color' => isset( $data['float-button-color'] ) ? sanitize_text_field( $data['float-button-color'] ) : "",
					'float_position'     => isset( $data['float-position'] )     ? (string) $data['float-position']                   : "right"
				)
			);
		} else {
			rsm_update_option( 'float_status', 0 );
		}
		$result = 'button_updated';

	} else {
		$result = 'button_update_error';
	}

	wp_redirect( 'admin.php?page=social-conversion-settings&rsm-tab=btn&rsm-message=' . $result );
	exit;
}
add_action( 'rsm_float_button_submit', 'rsm_float_button_submit' );

/**
 * Listens for when the opt-in button page's save button is clicked.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_button_submit( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_settings_nonce' ) ) {
        wp_die( 'Submit button: Nonce is invalid', 'Error' );
    }

    $rsm_optin_btn = isset( $data['opt-button'] ) ? esc_url_raw( $data['opt-button'] ) : null;
    if ( $rsm_optin_btn ) {
        rsm_update_option( 'optin_btn', $rsm_optin_btn );
        $result = 'button_updated';
    } else {
        $result = 'button_update_error';
    }

    wp_redirect( 'admin.php?page=social-conversion-settings&rsm-tab=btn&rsm-message=' . $result );
    exit;
}
add_action( 'rsm_button_submit', 'rsm_button_submit' );

/**
 * Listens for when the automation save button is clicked.
 *
 * @since 1.0
 * @param array $data List data
 * @return void
 */
function rsm_auto_submit( $data ) {
    if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rsm_settings_nonce' ) ) {
        wp_die( 'Submit automation: Nonce is invalid', 'Error' );
    }

    // Get values
    $rsm_cron_type = ( isset( $data['opt-cron'] ) && 'wp' == $data['opt-cron'] ) ? 'wp' : 'real';  // wp or real

    // Trigger cron scheduling
    if ( 'wp' == $rsm_cron_type ) {
        rsm_schedule_wp_cron();
    } else {
        rsm_remove_wp_cron();
    }

    // Update automation options
    rsm_update_option( 'cron_type', $rsm_cron_type );

    wp_redirect( 'admin.php?page=social-conversion-settings&rsm-tab=auto&rsm-message=auto_updated' );
    exit;
}
add_action( 'rsm_auto_submit', 'rsm_auto_submit' );

/**
 * Supplemental (backup) validation of a list form.
 *
 * @since 1.0
 * @param array $data List data
 * @return bool True if successfully validated, otherwise false
 */
function rsm_validate_list( $data ){
     $result = true;

    if ( isset( $data['app-name'] ) && 0 === mb_strlen( trim( $data['app-name'] ) ) ) {
        add_settings_error( 'rsm-notices', 'app-name', 'Facebook Display Name is required.', 'error' );
        $result = false;
    }

    if ( isset( $data['app-id'] ) && 0 === mb_strlen( trim( $data['app-id'] ) ) ) {
        add_settings_error( 'rsm-notices', 'app-id', 'Facebook App ID is required.', 'error' );
        $result = false;
    }

    if ( isset( $data['app-secret'] ) && 0 === mb_strlen( trim( $data['app-secret'] ) ) ) {
        add_settings_error( 'rsm-notices', 'app-secret', 'Facebook App Secret is required.', 'error' );
        $result = false;
    }

    if ( ! empty( $data['okay-url'] ) ) {
        $arrURL = @parse_url( strtolower( $data['okay-url'] ) );
        if ( ! in_array( $arrURL['scheme'], array( 'http', 'https' ) ) ) {
            add_settings_error( 'rsm-notices', 'welcome-url', 'Please enter a valid Okay URL, including http:// or https://', 'error' );
            $result = false;
        }
    }

    if ( ! empty( $data['cancel-url'] ) ) {
        $arrURL = @parse_url( strtolower( $data['cancel-url'] ) );
        if ( ! in_array( $arrURL['scheme'], array( 'http', 'https' ) ) ) {
            add_settings_error( 'rsm-notices', 'welcome-url', 'Please enter a valid Cancel URL, including http:// or https://', 'error' );
            $result = false;
        }
    }

    // If sending welcome notification, validate
    if ( isset( $data['opt-welcome'] ) && "T" == $data['opt-welcome'] ) {
        if ( isset( $data['welcome-text'] ) && 0 === mb_strlen( trim( $data['welcome-text'] ) ) ) {
            add_settings_error( 'rsm-notices', 'welcome-text', 'If sending a welcome notification, Welcome text is required.', 'error' );
            $result = false;
        }

        if ( isset( $data['welcome-url'] ) && 0 === mb_strlen( trim( $data['welcome-url'] ) ) ) {
            add_settings_error( 'rsm-notices', 'welcome-url', 'If sending a welcome notification, Redirect URL is required.', 'error' );
            $result = false;
        } else if ( isset( $data['welcome-url'] ) ) {
            $arrURL = @parse_url( strtolower( $data['welcome-url'] ) );
            if ( ! in_array( $arrURL['scheme'], array( 'http', 'https' ) ) ) {
                add_settings_error( 'rsm-notices', 'welcome-url', 'Please enter a valid Redirect URL, including http:// or https://', 'error' );
                $result = false;
            }
        }
    }

    // If integrating with an autoresponder, make sure at least one item is selected
    if ( isset( $data['opt-ar'] ) && 'T' == $data['opt-ar'] ) {
        $ar_data = rsm_get_ar_data( $data );
        if ( empty( $ar_data ) ) {
            add_settings_error( 'rsm-notices', 'opt-ar', 'If integrating with an autoresponder, you must select at least one list.', 'error' );
            $result = false;
        }
    }

    return $result;
}

/**
 * Gets integrated autoresponder data from list data.
 *
 * @since 1.0
 * @param array $data List data
 * @return array Array of integrated ar data (list_id, ar_name, ar_value)
 */
function rsm_get_ar_data( $data ) {
    // Build array of ar data for inserting
    $ar_data = array();
    foreach ( $data as $key => $val ) {
        if ( is_array( $data[ $key ] ) && ( strpos( $key, "-multiselect" ) > 0 ) ) {
            $ar_name = str_replace( "-multiselect", "", $key );
            foreach ( $data[ $key ] as $ar_value ) {
                $ar_data[] = array( 'list_id'  => $data['list-id'],
                                    'ar_name'  => $ar_name,
                                    'ar_value' => $ar_value
                );
            }
        }
    }
    return $ar_data;
}
