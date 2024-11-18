<?php
/**
 * The autoresponder class definition for AWeber.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load AWeberAPI if not loaded
if ( ! class_exists( 'AWeberAPI' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/aweber_api/aweber_api.php';
}

/**
 * RSM_AR_AWeber Class
 *
 * @since 1.0
 */
class RSM_AR_AWeber extends RSM_AR_Base {

    /**
     * Initialize the autoresponder class. If config doesn't contain an api key, retrieve it and corresponding
     * data from the db.
     *
     * AWeber scenarios:
     *   1. first time connecting [no db values, api_key submitted, get credentials]
     *   2. updating connection [db values, api_key submitted; compare keys for deciding to get credentials]
     *   3. using a method, eg get_lists_html() or subscribe() [db values, no api_key submitted, no credentials]
     *
     * @since 1.0
     * @param array $config The application configuration
     * @throws Exception
     */
    public function __construct( $config = array() ) {

        // Remember any API code submitted with request
        $prev_key = isset( $config['api_key'] ) ? $config['api_key'] : null;

        // Get db values; if data exists, it means AWeber has connected in the past
        $ar_data = db_get_ar_data( 'aweber' );
        if ( $ar_data ) {
            // Store db values and unserialize options
            $this->config = $ar_data;
            if ( isset( $ar_data['options'] ) ) {
                $this->options = unserialize( $ar_data['options'] );
            }
        }

        // Handle the various AWeber scenarios
        if ( ( ! $ar_data ) && $prev_key ) {
            // 1. First time connecting [no db values, api_key submitted, get credentials]
            $get_credentials = true;

            // Verify an auth code was sent with request
            if ( isset( $config['api_key'] ) ) {
                $this->config = $config;
            } else {
                throw new Exception( 'Error with AWeber Authorization Code.' );
            }

        } elseif ( $ar_data && $prev_key ) {
            // 2. Updating connection [db values, api_key submitted; compare keys for deciding to get credentials]
            $get_credentials = ( $prev_key != $this->config['api_key'] );
            $this->config['api_key'] = $prev_key;

        } elseif ( $ar_data && ( ! $prev_key ) ) {
            // 3. Using a method, eg get_lists_html() or subscribe() [db values, no api_key submitted, no credentials]
            $get_credentials = false;

        } else {
            throw new Exception( 'Error processing AWeber request.' );
        }

        // Maybe get credentials
        if ( $get_credentials ) {
            list( $consumer_key, $consumer_secret, $access_key, $access_secret ) = AWeberAPI::getDataFromAweberID( $this->config['api_key'] );
            if ( $access_secret ) {
                $secrets = array(
                    'consumer_key'    => $consumer_key,
                    'consumer_secret' => $consumer_secret,
                    'access_key'      => $access_key,
                    'access_secret'   => $access_secret
                );
                $this->options = $secrets;
                $this->config['options'] = serialize( $this->options );
            } else {
                throw new Exception( 'Erorr connecting to AWeber. Please verify your Authorization Code or get a new one and try again.' );
            }
        }

        $this->ar = new AWeberAPI( $this->options['consumer_key'], $this->options['consumer_secret'] );
    }

    /**
     * Checks whether the autoresponder key is valid.
     *
     * @since 1.0
     * @return bool True of the key is valid, otherwise false
     */
    public function is_valid() {
        return isset( $this->options['access_secret'] );
    }

    /**
     * Gets all lists associated with an autoresponder using an API call.
     *
     * @since 1.0
     * @return mixed Array of lists if succesful, otherwise false
     */
    public function get_lists_api() {
        $lists    = array();
        $response = array();

        ob_start();
        $result = $this->ar->getAccount( $this->options['access_key'], $this->options['access_secret'] );

        if( $result ) {
            foreach ( $result->lists as $list ) {
                $lists[$list->id] = $list->name;
            }
            $this->lists       = $lists;
            $response['lists'] = $lists;
        }
        $output = ob_get_clean();

        return $response;
    }

    /**
     * Adds a user as a contact to an autoresponder list.
     *
     * @since 1.0
     * @param string $list_value The internal identifier of a list
     * @param array $user_data The user's details (email, full_name, first_name, last_name)
     * @return mixed $new_subscriber Response received from API call
     */
    public function subscribe( $list_value = '', $user_data = array() ) {
        ob_start();

        // Setup
        $result  = $this->ar->getAccount( $this->options['access_key'], $this->options['access_secret'] );
        $listURL = "/accounts/{$result->id}/lists/{$list_value}";
        $list    = $result->loadFromUrl( $listURL );

        // Create a subscriber
        $params         = array( 'email' => $user_data['email'], 'name' => $user_data['full_name'] );
        $subscribers    = $list->subscribers;
        $new_subscriber = $subscribers->create( $params );

        $output = ob_get_clean();

        return $new_subscriber;
    }
}
