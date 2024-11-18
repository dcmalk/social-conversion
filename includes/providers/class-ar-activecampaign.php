<?php
/**
 * The autoresponder class definition for Active Campaign.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load RSM_ActiveCampaign if not loaded
if ( ! class_exists( 'ActiveCampaign' ) ) {
    require_once RSM_PLUGIN_DIR . 'includes/vendor/ActiveCampaign/ActiveCampaign.class.php';
}

/**
 * RSM_AR_ActiveCampaign Class
 *
 * @since 1.0
 */
class RSM_AR_ActiveCampaign extends RSM_AR_Base {

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

        $this->ar = new ActiveCampaign($this->options['api_url'], $this->config['api_key'] );
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
        $response = $ar->credentials_test();
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
        $result = $ar->api( 'list/list', array( 'ids' => 'all' ) );
        if ( $result->success ) {
            foreach ( $result as $list ) {
                if ( isset( $list->id ) ) {
                    $lists[ $list->id ] = $list->name;
                }
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
        $contact  = array(
            "email"                 => $user_data['email'],
            "first_name"            => $user_data['first_name'],
            "last_name"             => $user_data['last_name'],
            "p[{$list_value}]"      => $list_value,
            "status[{$list_value}]" => 1     // 'Active' status
        );
        $response = $ar->api( 'contact/sync', $contact );
        $output = ob_get_clean();

        return $response;
    }
}
