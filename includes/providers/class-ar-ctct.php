<?php
/**
 * The autoresponder class definition for Constant Contact.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load Ctct if not loaded
if ( ! class_exists( 'Ctct\SplClassLoader' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/Ctct/autoload.php';
}

/**
 * RSM_AR_Ctct Class
 *
 * @since 1.0
 */
class RSM_AR_Ctct extends RSM_AR_Base {

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
        $this->ar = new \Ctct\ConstantContact( RSM_CTCT_API_KEY );
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

        $result = $ar->getLists( $this->config['api_key'] );    // Note: api_key = access_token
        if ( $result ) {
            foreach ( $result as $list ) {
                $lists[ $list->id ] = $list->name;
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
        $access_token = $this->config['api_key'];

        ob_start();

        // Check to see if a contact with the email address already exists in the account
        $exists = $ar->getContacts( $access_token, array( "email" => $user_data['email'] ) );

        // Create a new contact if one does not exist
        if ( empty( $exists->results ) ) {
            $contact = new \Ctct\Components\Contacts\Contact();
            $contact->addEmail( $user_data['email'] );
            $contact->addList( $list_value );
            $contact->first_name = $user_data['first_name'];
            $contact->last_name  = $user_data['last_name'];

            $response = $ar->addContact( $access_token, $contact, true );

        } else {
            // If contact already exists, update instead
            $contact = $exists->results[0];

            if ( $contact instanceof \Ctct\Components\Contacts\Contact ) {
                $contact->addList( $list_value );
                $contact->first_name = $user_data['first_name'];
                $contact->last_name  = $user_data['last_name'];

                $response = $ar->updateContact( $access_token, $contact, true );

            } else {
                rsm_error_handler( 'Ctct Contact type not returned', array_merge( array( 'list_value' => $list_value ), $user_data ) );
            }
        }

        $output = ob_get_clean();

        return $response;
    }
}
