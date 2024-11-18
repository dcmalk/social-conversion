<?php
/**
 * The autoresponder class definition for Benchmark.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_Benchmark if not loaded
if ( ! class_exists( 'IXR_Client' ) ) {
    require_once( ABSPATH . WPINC . '/class-IXR.php' );
}

/**
 * RSM_AR_Benchmark Class
 *
 * @since 1.0
 */
class RSM_AR_Benchmark extends RSM_AR_Base {

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
        $this->ar = new IXR_Client( 'https://api.benchmarkemail.com/1.0/', false, 443 );
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

        $ar->query( 'listGet', $this->config['api_key'], '', 1, 100, 'name', 'asc' );  // apiKey, filter, pageNumber, pageSize, orderBy, sortOrder
        $result = $ar->getResponse();
        if ( $result ) {
            foreach ( $result as $list ) {
                $lists[ $list['id'] ] = $list['listname'];
            };
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

        // http://www.benchmarkemail.com/API/Doc/listAddContactsOptin
        $ar->query( 'listAddContactsOptin', $this->config['api_key'], $list_value, array(
            array(
                'email'     => $user_data['email'],
                'firstname' => $user_data['first_name'],
                'lastname'  => $user_data['last_name']
            )
        ), ( "T" == $this->options['double_optin'] ? '1' : '0' ) );

        //), $this->options['double_optin'] );
        $response = $ar->getResponse();

        $output = ob_get_clean();

        return $response;
    }
}
