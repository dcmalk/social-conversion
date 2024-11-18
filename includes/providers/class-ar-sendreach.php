<?php
/**
 * The autoresponder class definition for SendReach.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_SendReachApi if not loaded
if ( ! class_exists( 'MailWizzApi_Autoloader' ) ) {
    require_once RSM_PLUGIN_DIR . '/includes/vendor/MailWizzApi/Autoloader.php';
}

/**
 * RSM_AR_SendReach Class
 *
 * @since 1.0
 */
class RSM_AR_SendReach extends RSM_AR_Base {

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

        // Register autoloader
        MailWizzApi_Autoloader::register();

        // Setup config
        $config = new MailWizzApi_Config( array(
            'apiUrl'     => 'http://dashboard.sendreach.com/api/index.php',
            'publicKey'  => $this->config['api_key'],
            'privateKey' => $this->options['private_key']
        ) );
        MailWizzApi_Base::setConfig($config);
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

        // Create the lists endpoint
        $endpoint = new MailWizzApi_Endpoint_Lists();

        // Get lists
        $result = $endpoint->getLists($pageNumber = 1, $perPage = 100);
        if ( 'success' == $result->body['status'] ) {
            foreach ( $result->body['data']['records'] as $list ) {
                $lists[ $list['general']['list_uid'] ] = $list['general']['name'];
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
        ob_start();

        // Create the subscribers endpoint
        $endpoint = new MailWizzApi_Endpoint_ListSubscribers();

        // Add the contact
        $response = $endpoint->create( $list_value, array(
            'EMAIL'    => $user_data['email'],
            'FNAME'    => $user_data['first_name'],
            'LNAME'    => $user_data['last_name']
        ));

        $output = ob_get_clean();
        return $response;
    }
}
