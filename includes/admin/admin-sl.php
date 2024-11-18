<?php
/**
 * License Functions
 *
 * @package     RSM
 * @subpackage  Admin/License
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load the EDD Sofware Licensing plugin updater
if( !class_exists( 'RSM_SL_Plugin_Updater' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/admin/legacy/rsm_sl_plugin_updater.php';
}

/**
 * Checks if a license key is valid.
 *
 * @since 1.0
 * @return bool True if valid license, otherwise false
 */
function rsm_transient_state() {
    // Get license state from transient
    $t_state = get_transient( 'rsm_sn_state' );
    $p8_hash = pow( 0x02, 0x03 );

    // If false, check and set
    if ( (int) $p8_hash << 1 && ! $t_state ) {
        list( $s, $p ) = rsm_get_settings_sl();
        $t_state = ( false == $s || 'valid' != $p ) ? 0 : 8;
        set_transient( 'rsm_sn_state', $t_state, 86400 );
    }

    // Return transient state
    return ( ( 0xFF << (int) $p8_hash == 65280 ) && $t_state );
}

/**
 * Activates a license key and increases the site count.
 *
 * @since 1.0
 * @return void
 */
function rsm_activate_sl() {
    // listen for our activate button to be clicked
    if( isset( $_POST['rsm_activate_sl'] ) ) {

        // run a quick security check
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'rsm_sl_nonce' ) )
            wp_die( 'Activation: Nonce is invalid', 'Error' );

        // retrieve the license from the database
        $y = isset( $_POST['rsm_sl_tk'] ) ? trim( $_POST['rsm_sl_tk'] ) : "";

        // data to send in our API request
        $api_params = array(
            'edd_action' => 'activate_license',
            'license'    => $y,
            'item_name'  => urlencode( RSM_PLUGIN_NAME ), // the name of our product in EDD
            'url'        => home_url()
        );

        // Call the custom API
        $response = wp_remote_post( RSM_PRODUCT_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) ) {
            $z = 'api_response_error';
        } else {
            // decode the license data
            $sl_data = json_decode( wp_remote_retrieve_body( $response ) );
            $z = ( $sl_data->success ) ? (string) $sl_data->license : (string) $sl_data->error;
            rsm_update_option( rsm_sl_data_sanitize( $sl_data ) );
        }

        // Update license and status and reset (delete) state transient
        rsm_update_option( array( 'sl_tk' => $y, 'sl_enum' => $z, 'fb_app_alt_https' => rsm_get_fb_app_alt_https() ) );

        delete_transient( 'rsm_sn_state' );

        // then we redirect to dashboard
        if ( 'valid' == $z ) {
            wp_redirect( 'admin.php?page=social-conversion-dashboard' );
            exit;
        }
    }
}
add_action( 'admin_init', 'rsm_activate_sl' );

/**
 * Deactivate a license key and decreases the site count.
 *
 * @since 1.0
 * @return void
 */
function rsm_deactivate_sl() {
    // listen for our activate button to be clicked
    if( isset( $_POST['rsm_deactivate_sl'] ) ) {

        // run a quick security check
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'rsm_sl_nonce' ) )
            wp_die( 'Deactivation: Nonce is invalid', 'Error' );

        // retrieve the license from the database
        $x = trim( rsm_get_option( 'sl_tk' ) );

        // data to send in our API request
        $api_params = array(
            'edd_action'=> 'deactivate_license',
            'license' 	=> $x,
            'item_name' => urlencode( RSM_PLUGIN_NAME ), // the name of our product in EDD
            'url'       => home_url()
        );

        // Call the custom API
        $response = wp_remote_post( RSM_PRODUCT_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

        // make sure the response came back okay
        if ( is_wp_error( $response ) )
            return false;

        // decode the license data
        $sl_data = json_decode( wp_remote_retrieve_body( $response ) );

        // $sl_data->license will be either "deactivated" or "failed"
        if( $sl_data->license == 'deactivated' ) {
            rsm_delete_option( array( 'sl_enum', 'email', 'payment_id', 'price_id', 'tier', 'expires', 'limit' ) );
            delete_transient( 'rsm_sn_state' );
            wp_redirect( 'admin.php?page=social-conversion-license' );
            exit;
        }
    }
}
add_action( 'admin_init', 'rsm_deactivate_sl' );

/**
 * Sanitizes the software license activation response object.
 *
 * @since 1.0
 * @return array $result Sanitized activation response values
 */
function rsm_sl_data_sanitize( $data ) {
    $result = array();

    if ( isset( $data->success ) && $data->success ) {
        $result['email']      = isset( $data->customer_email ) ? $data->customer_email : null;
        $result['payment_id'] = isset( $data->payment_id )     ? $data->payment_id     : null;
        $result['price_id']   = isset( $data->price_id )       ? $data->price_id       : null;
        $result['tier']       = isset( $data->price_name )     ? $data->price_name     : null;
        $result['expires']    = isset( $data->expires )        ? $data->expires        : null;
        $result['limit']      = isset( $data->license_limit )  ? $data->license_limit  : null;
    }
    return $result;
}

/**
 * Renders the License page.
 *
 * @since 1.0
 * @return void
 */
function rsm_sl_page() {
    list( $t, $e ) = rsm_get_settings_sl();
    ?>
    <div class="wrap">
    <h2>Plugin License Activation</h2>
    <form method="post" action="">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" valign="top">License Key</th>
                <td>
                    <input name="rsm_sl_tk" type="text" class="regular-text" value="<?php esc_attr_e( $t ); ?>" />
                    <p class="description">Enter your license key</p>
                </td>
            </tr>
            <?php
            if ( $e ) {
                $msg = rsm_get_sl_msg( $e );
                ?>
                <tr valign="top">
                    <th scope="row" valign="top">License Status</th>
                    <td>
                        <input type="text" class="regular-text" value="<?php esc_attr_e( $msg ); ?>" readonly />
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <p class="submit">
            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'rsm_sl_nonce' ); ?>"/>
            <input type="submit" class="button-primary" name="rsm_activate_sl" value="Activate License"/>
        </p>
    </form>
    <?php
}

/**
 * Get a license status message based on its status code.
 *
 * @since 1.0
 * @param string $val The license status code
 * @return string
 */
function rsm_get_sl_msg( $val ) {
    switch ( $val ) {
        case 'api_response_error':
            $msg = 'API response error';
            break;
        case 'missing':
            $msg = 'Invalid license key';
            break;
        case 'license_not_activable':
            $msg = 'License key cannot be activated';
            break;
        case 'revoked':
            $msg = 'License key revoked';
            break;
        case 'no_activations_left':
            $msg = 'No activations left';
            break;
        case 'expired':
            $msg = 'License key expired';
            break;
        case 'key_mismatch':
            $msg = 'Key mismatch error';
            break;
        case 'invalid_item_id':
            $msg = 'Invalid item ID';
            break;
        case 'item_name_mismatch':
            $msg = 'Item name mismatch error';
            break;
        default:
            $msg = (string) $val;
            break;
    }
    return $msg;
}

/**
 * Initializes the SL updater.
 *
 * @since 1.0
 * @return void
 */
function rsm_plugin_updater() {
    // Setup the updater
    $rsm_updater = new RSM_SL_Plugin_Updater( RSM_PRODUCT_URL, RSM_PLUGIN_FILE, array(
            'version'   => RSM_VERSION,               // current version number
            'license'   => rsm_get_option( 'sl_tk' ), // license key
            'item_name' => RSM_PLUGIN_NAME,           // name of this plugin
            'author'    => 'Damon Malkiewicz'         // author of this plugin
        )
    );
}
add_action( 'admin_init', 'rsm_plugin_updater', 0 );
