<?php
/**
 * Main autoresponder wrapper class using the Simple Factory Pattern.
 *
 * @package     RSM
 * @subpackage  Autoresponder
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load base autoresponder classes
require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-base.php';

/**
 * The main RSM_Autoresponder Factory class
 *
 * @since 1.0
 */
class RSM_Autoresponder {
    /**
     * Get an instance of a specific autoresponder type.
     *
     * The configuration:
     * - ar_name: The autoresponder name (aweber, mailchimp, etc)
     * - api_key: The API key
     * - options: Autoresponder-specific options, parameters, credentials and/or other data
     *
     * @since 1.0
     * @param array $config The application configuration
     * @returns $obj Instance of the requested autoresponder type
     * @throws Exception
     */
    public static function get_instance( $config = array() ) {

        // If the config is invalid, throw an exception
        if ( empty( $config ) || ( ! isset( $config['ar_name'] ) ) ) {
            throw new Exception( 'Invalid autoresponder configuration.' );
        }

        // Create object based on specified autoresponder name
        switch ( strtolower( $config['ar_name'] ) ) {

            case 'activecampaign':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-activecampaign.php';
                $obj = new RSM_AR_ActiveCampaign( $config );
                break;

            case 'aweber':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-aweber.php';
                $obj = new RSM_AR_AWeber( $config );
                break;

            case 'benchmark':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-benchmark.php';
                $obj = new RSM_AR_Benchmark( $config );
                break;

            case 'campaignmonitor':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-campaignmonitor.php';
                $obj = new RSM_AR_CampaignMonitor( $config );
                break;

            case 'ctct':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-ctct.php';
                $obj = new RSM_AR_Ctct( $config );
                break;

	        case 'convertkit':
		        require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-convertkit.php';
		        $obj = new RSM_AR_ConvertKit( $config );
		        break;

            case 'getresponse':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-getresponse.php';
                $obj = new RSM_AR_GetResponse( $config );
                break;

            case 'icontact':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-icontact.php';
                $obj = new RSM_AR_iContact( $config );
                break;

            case 'infusionsoft':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-infusionsoft.php';
                $obj = new RSM_AR_Infusionsoft( $config );
                break;

            case 'mailchimp':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-mailchimp.php';
                $obj = new RSM_AR_MailChimp( $config );
                break;

	        case 'mailerlite':
		        require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-mailerlite.php';
		        $obj = new RSM_AR_MailerLite( $config );
		        break;

	        case 'sendinblue':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-sendinblue.php';
                $obj = new RSM_AR_SendinBlue( $config );
                break;

            case 'sendreach':
                require_once RSM_PLUGIN_DIR . 'includes/providers/class-ar-sendreach.php';
                $obj = new RSM_AR_SendReach( $config );
                break;

            default:
                throw new Exception( 'Unknown autoresponder specified.' );
                break;

        }
        return $obj;
    }
}
