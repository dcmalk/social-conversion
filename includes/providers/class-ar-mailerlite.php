<?php
/**
 * The autoresponder class definition for MailerLite.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load MailerLite API if not loaded
if ( ! class_exists( 'ML_Lists' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/mailerlite_rest/ML_Lists.php';
    require_once RSM_PLUGIN_DIR . 'includes/vendor/mailerlite_rest/ML_Subscribers.php';
}

/**
 * RSM_AR_MailerLite Class
 *
 * @since 1.0
 */
class RSM_AR_MailerLite extends RSM_AR_Base {

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
        //$this->ar = new ML_Lists( $this->config['api_key'] );
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

        $rest = new ML_Lists( $this->config['api_key'] );
	    if ( $rest ) {
		    $result = json_decode( $rest->getAll() );
		    if ( ! empty( $result ) ) {
			    foreach ( $result->Results as $list ) {
				    $lists[ $list->id ] = $list->name;
			    }
			    $this->lists       = $lists;
			    $response['lists'] = $lists;
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

        $rest = new ML_Subscribers( $this->config['api_key'] );
	    $response = $rest->setId( $list_value )->add( array(
            'email' => $user_data['email'],
            'name'  => $user_data['full_name']
        ) );

        $output = ob_get_clean();

        return $response;
    }
}
