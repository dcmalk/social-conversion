<?php
/**
 * The autoresponder class definition for CampaignMonitor.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load Campaign Monitor API if not loaded
if ( ! class_exists( 'CS_REST_General' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/CampaignMonitor/csrest_general.php';
    require_once RSM_PLUGIN_DIR . 'includes/vendor/CampaignMonitor/csrest_clients.php';
    require_once RSM_PLUGIN_DIR . 'includes/vendor/CampaignMonitor/csrest_subscribers.php';
}

/**
 * RSM_AR_CampaignMonitor Class
 *
 * @since 1.0
 */
class RSM_AR_CampaignMonitor extends RSM_AR_Base {

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
        $this->ar = new CS_REST_General( array( 'api_key' => $this->config['api_key'] ) );
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

        $client_id = $ar->get_clients()->response[0];
        if ( $client_id ) {
            $resources = new CS_REST_Clients( $client_id->ClientID, array( 'api_key' => $this->config['api_key'] ) );
            if ( $resources ) {
                $result = $resources->get_lists();
                if ( $result->response ) {
                    foreach ( $result->response as $list ) {
                        $lists[ $list->ListID ] = $list->Name;
                    };
                    $this->lists       = $lists;
                    $response['lists'] = $lists;
                }
            }
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

        $subscriber = new CS_REST_Subscribers( $list_value, array( 'api_key' => $this->config['api_key'] ) );
        $response = $subscriber->add( array(
            'EmailAddress' => $user_data['email'],
            'Name'         => $user_data['full_name']
        ) );

        $output = ob_get_clean();

        return $response;
    }
}
