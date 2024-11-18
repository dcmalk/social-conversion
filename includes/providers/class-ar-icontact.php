<?php
/**
 * The autoresponder class definition for iContact.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_iContactApi if not loaded
if ( ! class_exists( 'RSM_iContactApi' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/icontact/legacy/iContactApi.php';
}

/**
 * RSM_AR_iContact Class
 *
 * @since 1.0
 */
class RSM_AR_iContact extends RSM_AR_Base {

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
        RSM_iContactApi::getInstance()->setConfig(array(
            'appId'       => $this->config['api_key'],
            'apiPassword' => $this->options['password'],
            'apiUsername' => $this->options['username']
        ));
        $this->ar = RSM_iContactApi::getInstance();
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
        $result = $ar->getLists();
        if ( $result ) {
            foreach ( $result as $list ) {
                $lists[ $list->listId ] = $list->name;
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
        $response = $ar->addContact( $user_data['email'], 'normal', null, $user_data['first_name'], $user_data['last_name'] );
        if ( $response && isset( $response->contactId ) ) {
            $response = $ar->subscribeContactToList( $response->contactId, $list_value, 'normal' );
        }
        $output = ob_get_clean();

        return $response;
    }
}
