<?php
/**
 * Abstract autoresponder wrapper class.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The abstract RSM_AR_Base class
 *
 * @since 1.0
 */
abstract class RSM_AR_Base {
    /**
     * Main autoresponder app object
     *
     * @var object
     * @since 1.0
     */
    public $ar;

    /**
     * Autoresponder config
     *
     * @var array
     * @since 1.0
     */
    protected $config = array();

    /**
     * Autoresponder options
     *
     * @var array
     * @since 1.0
     */
    protected $options = array();

    /**
     * Autoresponder lists
     *
     * @var array
     * @since 1.0
     */
    protected $lists = array();

    /**
     * Initialize the autoresponder class. If config doesn't contain an api key, retrieve it and corresponding
     * data from the db.
     *
     * @since 1.0
     * @param array $config The application configuration
     * @throws Exception
     */
    public function __construct( $config = array() ) {

        // If API key doesn't exist in config, retrieve ar data from db
        if ( isset( $config['api_key'] ) ) {

            // Parse querystring, serialize options inside config object, and set config object
            if ( isset( $config['options'] ) ) {
                parse_str( $config['options'], $this->options );
                $config['options'] = serialize( $this->options );
            }
            $this->config = $config;

        } else {
            $ar_data = db_get_ar_data( $config['ar_name'] );
            if ( $ar_data ) {
                $this->config = $ar_data;
                if ( isset( $ar_data['options'] ) ) {
                    $this->options = unserialize( $ar_data['options'] );
                }
            } else {
                throw new Exception( 'Error retrieving autoresponder data.' );
            }
        }
    }

    /**
     * Checks whether the autoresponder key is valid.
     *
     * @since 1.0
     * @return bool True of the key is valid, otherwise false
     */
    public function is_valid() {
        return ( isset( $this->config ) && ( ! empty( $this->config['api_key'] ) ) );
    }

    /**
     * Gets all lists associated with an autoresponder using an API call.
     *
     * @since 1.0
     * @return mixed Array of lists if succesful, otherwise false
     */
    abstract public function get_lists_api();

    /**
     * Gets all stored lists associated with an autoresponder from the database.
     *
     * @since 1.0
     * @return string $html HTML formatted string of options values
     */
    public function get_lists_html() {
        $lists = db_get_ar_lists( 0, $this->config['ar_name'] );

        if ( $lists ) {
            $html = '';
            foreach ( $lists as $list ) {
                $html .= '<option value="' . $list['ar_list_value'] . '">' . $list['ar_list_name'] . '</option>';
            }
        } else {
            $html = '<option value="0" disabled>No lists found</option>';
        }

        return $html;
    }

    /**
     * Adds a user as a contact to an autoresponder list.
     *
     * @since 1.0
     * @param string $list_value The internal identifier of a list
     * @param array $user_data The user's details (email, full_name, first_name, last_name)
     * @return mixed $response Response received from API call
     */
    abstract public function subscribe( $list_value = '', $user_data = array() );

    /**
     * Saves the autoresponder configuration to the database.
     *
     * @since 1.0
     * @return mixed ID of inserted record, otherwise false
     */
    public function save_autoresponder_db() {
        return db_insert_autoresponder( $this->config );
    }

    /**
     * Saves all list associated with the autoresponder to the database.
     *
     * @since 1.0
     * @return bool True if successful, otherwise false
     */
    public function save_lists_db() {
        return db_insert_ar_lists( $this->config['ar_name'], $this->lists );
    }
}
