<?php
/**
 * The autoresponder class definition for SendinBlue.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_SendinBlue if not loaded
if ( ! class_exists( 'Sendinblue\Mailin' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/Sendinblue/Mailin.php';
}

/**
 * RSM_AR_SendinBlue Class
 *
 * @since 1.0
 */
class RSM_AR_SendinBlue extends RSM_AR_Base {

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
        $this->ar = new \Sendinblue\Mailin( 'https://api.sendinblue.com/v2.0', $this->config['api_key'] );
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

        $result = $ar->get_lists( array( "page" => 1, "page_limit" => 50 ) );
        if ( 'success' == $result['code'] ) {
            foreach ( $result['data']['lists'] as $list ) {
                $lists[ $list['id'] ] = $list['name'];
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

        $data = array(
            "email"       => $user_data['email'],
            "attributes"  => array( "NAME" => $user_data['full_name'] ),
            "listid"      => array( $list_value ),
            "blacklisted" => 0,
            "blacklisted_sms" => 0
        );

        $response = $ar->create_update_user($data);
        $output = ob_get_clean();

        return $response;
    }
}
