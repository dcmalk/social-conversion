<?php
/**
 * The autoresponder class definition for GetResponse.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_GetResponse if not loaded
if ( ! class_exists( 'RSM_GetResponse' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/GetResponse/legacy/GetResponseAPI.class.php';
}

/**
 * RSM_AR_GetResponse Class
 *
 * @since 1.0
 */
class RSM_AR_GetResponse extends RSM_AR_Base {

    /**
     * Initialize the autoresponder class. If config doesn't contain an api key, retrieve it and corresponding
     * data from the db.
     *
     * @since 1.0
     * @param array $config The application configuration
     * @throws Exception
     */
    public function __construct( $config = array() ) {
        parent::__construct( $config );
        $this->ar = new RSM_GetResponse( $this->config['api_key'] );
    }

    /**
     * Checks whether the autoresponder key is valid.
     *
     * @since 1.0
     * @return bool True of the key is valid, otherwise false
     */
    public function is_valid() {
        $ar = $this->ar;

        ob_start();
        $response = $ar->ping();
        $output = ob_get_clean();

        return $response;
    }

    /**
     * Gets all lists associated with an autoresponder using an API call.
     *
     * @since 1.0
     * @return mixed Array of lists if succesful, otherwise false
     */
    public function get_lists_api() {
        $ar       = $this->ar;
        $lists    = array();
        $response = array();

        ob_start();
        $result = $ar->getCampaigns();
        if ( $result ) {
            foreach ( $result as $key => $campaign ) {
                $lists[ $key ] = $campaign->name;
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
     * @return mixed $response Response received from API call
     */
    public function subscribe( $list_value = '', $user_data = array() ) {
        $ar = $this->ar;

        ob_start();
        $response = $ar->addContact( $list_value, $user_data['full_name'], $user_data['email'] );
        /*if ( $response ) {
            $error = $response['message'];
            $success = ( 1 == $response['queued']);
        }*/
        $output = ob_get_clean();

        return $response;
    }
}
