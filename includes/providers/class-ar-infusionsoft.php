<?php
/**
 * The autoresponder class definition for Infusionsoft.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_Infusionsoft if not loaded
if ( ! class_exists( 'iSDK' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/Infusionsoft/isdk.php';
}

/**
 * RSM_AR_Infusionsoft Class
 *
 * @since 1.0
 */
class RSM_AR_Infusionsoft extends RSM_AR_Base {

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

        $this->ar = new iSDK();
        $this->ar->cfgCon( $this->options['subdomain'], $this->config['api_key'], 'throw' );
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

        // Query Infusionsoft for available tags
        $page    = 0;
        $all_tags = array();
        while ( true ) {
            $tags = $ar->dsQuery( 'ContactGroup', 1000, $page, array( 'Id' => '%' ), array( 'Id', 'GroupName' ) );
            $all_tags = array_merge( $all_tags, $tags );
            if ( count( $tags ) < 1000 ) break;
            $page++;
        }

        // Build lists array from tags
        if ( $all_tags ) {
            foreach ( $all_tags as $list ) {
                $lists[ $list['Id'] ] = $list['GroupName'];
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
            'FirstName' => $user_data['first_name'],
            'LastName'  => $user_data['last_name'],
            'Email'     => $user_data['email']
        );

        $contact = $ar->findByEmail( $user_data['email'], array( 'Id' ) );

        if ( isset( $contact[0] ) && ( ! empty( $contact[0]['Id'] ) ) ) {
            $contact_id = $contact[0]['Id'];
            $ar->updateCon( $contact_id, $data );
            $response = $ar->grpAssign( $contact_id, $list_value );
        } else {
            $contact_id = $ar->addCon( $data );
            $response   = $ar->grpAssign( $contact_id, $list_value );
        }

        $output = ob_get_clean();

        return $response;
    }
}
