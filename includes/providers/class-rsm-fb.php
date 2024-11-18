<?php
/**
 * Wrapper class of OAuth2 for managing Facebook API interactions
 *
 * @package     RSM
 * @subpackage  Classes/Facebook
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Include OAuth2 libraries
require_once RSM_PLUGIN_DIR . 'includes/vendor/OAuth2/Client.php';
require_once RSM_PLUGIN_DIR . 'includes/vendor/OAuth2/GrantType/IGrantType.php';
require_once RSM_PLUGIN_DIR . 'includes/vendor/OAuth2/GrantType/AuthorizationCode.php';

/**
 * RSM_Facebook Class
 *
 * @since 1.0
 */
class RSM_Facebook {
    /**
     * Graph API version
     *
     * @since 1.0
     */
    const API_VER = 'v2.9';

    /**
     * Session state cookie name prefix
     *
     * @since 1.0
     */
    const COOKIE_PREFIX = 'rsm_sn';

    /**
     * Main Facebook app object
     *
     * @var object
     * @since 1.0
     */
    private $fb;

    /**
    * Map of aliases to Facebook endpoints
    *
    * @var array
    */
    private $endpoint_map = array();

    /**
    * The app ID
    *
    * @var string
    * @since 1.0
    */
    private $app_id;

    /**
    * The app secret
    *
    * @var string
    * @since 1.0
    */
    private $app_secret;

    /**
    * List of extended permissions
    *
    * @var string
    * @since 1.0
    */
    private $scope;

    /**
    * State to prevent CSRF
    *
    * @var string
    * @since 1.0
    */
    private $state;

    /**
     * Initialize the RSM Facebook class.
     *
     * The configuration:
     * - app_id: The application ID
     * - app_secret: The application secret
     * - state : Optional state token
     *
     * @since 1.0
     * @param array $config The application configuration
     */
    public function __construct( $config = array() ) {
        // Set our app configuration
        $this->app_id     = $config['app_id'];
        $this->app_secret = $config['app_secret'];
        $this->state      = empty( $config['state'] ) ? $this->set_state_token() : $config['state'];

        // Set default endpoints
        $this->endpoint_map['auth']  = 'https://www.facebook.com/'   . self::API_VER . '/dialog/oauth';
        $this->endpoint_map['token'] = 'https://graph.facebook.com/' . self::API_VER . '/oauth/access_token';
        $this->endpoint_map['me']    = 'https://graph.facebook.com/' . self::API_VER . '/me';
        $this->endpoint_map['graph'] = 'https://graph.facebook.com/' . self::API_VER;

        // Create our app instance
        $this->fb = new OAuth2\Client( $this->app_id, $this->app_secret,
                                       OAuth2\Client::AUTH_TYPE_URI,
                                       RSM_PLUGIN_DIR . 'includes/vendor/' . 'OAuth2/DigiCertHighAssuranceEVRootCA.pem' );
    }

    /**
     * Creates a state token to prevent CSRF attacks and store it.
     *
     * @since 1.0
     * @return string CSRF state token
     */
    private function set_state_token() {
      $state = md5( uniqid( mt_rand(), true ) );
      $this->set_session_cookie( $state );

      return $state;
    }

    /**
     * Returns the CSRF state token from stored cookie.
     *
     * @since 1.0
     * @return string CSRF state token, otherwise false
     */
    public function get_state_token() {
      $cookie_name = $this->get_cookie_name();

      return isset( $_COOKIE[$cookie_name] ) ? $_COOKIE[$cookie_name] : false;
    }

    /**
     * Stores the given value in a cookie.
     *
     * @since 1.0
     * @param string $value The value to store
     * @return void
     */
    private function set_session_cookie( $value ) {
        $cookie_name = $this->get_cookie_name();

        if ( ! headers_sent() ) {
            setcookie( $cookie_name, $value, "0", "/" );
        } else {
            rsm_error_handler( 'Notice: Headers already sent.' );
        }
    }

    /**
     * Returns the session's cookie name.
     *
     * @since 1.0
     * @return string The cookie name
     */
    private function get_cookie_name() {
        return self::COOKIE_PREFIX . '_' . $this->app_id;
    }

    /**
     * Returns the Facebook login url for authenticating user.
     *
     * @since 1.0
     * @param string $scope Extended permissions
     * @param string $redirect_url The redirect url to handle login results
     * @param string $display Whether the login will appear normal or condensed for a popup display
     * @return string The URL for the login flow
     */
    public function get_login_url( $scope, $redirect_url, $display = '' ) {
        $fb          = $this->fb;
        $this->scope = $scope;
        $params      = array( 'scope' => $scope, 'state' => $this->state );

        if ( ! empty( $display ) ) $params['display'] = $display;

        return $fb->getAuthenticationUrl(
            $this->endpoint_map['auth'],
            $redirect_url,
            $params
        );
    }

    /**
     * Gets a user access token.
     *
     * The parameters:
     * - code: The authentication code return by the login flow
     * - redirect_uri: The redirect url to handle login results
     *
     * @since 1.0
     * @param array $params Array of parameters required exchanging a code for an access token
     * @return string Results string containing access_token and expires
     * @throws RSM_FB_Exception
     */
    public function get_user_access_token( $params = array() ) {
        $fb = $this->fb;

        $result = $fb->getAccessToken(
            $this->endpoint_map['token'],
            'authorization_code',
            $params
        );

        // Check results and throw error if found
        if ( is_array( $result ) && isset( $result['result']['error'] ) ) {
            throw new Exception( 'Graph API error: ' . $result['result']['error']['message'] );
        }

        return $result;
    }

    /**
     * Sets a user access token.
     *
     * @since 1.0
     * @param string $token Set the access token
     * @return void
     */
    public function set_user_access_token( $token ) {
        $fb = $this->fb;
        $fb->setAccessToken( $token);
    }

    /**
     * Returns the app access token.
     *
     * @since 1.0
     * @return string An app access token
     */
    public function get_app_access_token() {
        return $this->app_id . '|' . $this->app_secret;
    }

    /**
     * Returns the details of Facebook user currently logged in.
     *
     * @since 1.0
     * @return string Results string containing details
     * @throws RSM_FB_Exception
     */
    public function get_user() {
        $fb = $this->fb;

        // Get the listed fields
	    $fields = array( 'id', 'name', 'first_name', 'last_name', 'email', 'age_range', 'link', 'gender', 'locale', 'timezone' );
        $result = $fb->fetch( $this->endpoint_map['me'], array( 'fields' => implode( ',', $fields ) ) );

        // Check results and throw error if found
        if ( is_array( $result ) && isset( $result['result']['error'] ) ) {
            throw new Exception( 'Graph API error: ' . $result['result']['error']['message'] );
        }

        return $result;
    }

    /**
     * Sends Facebook notifications to a batch of users.
     *
     * <http://garyrafferty.com/2013/02/03/Send-batch-requests-using-facebook-php-sdk.html>
     *
     * The parameters for each array item:
     * - uid: Facebook app user ID
     * - href: Query args to pass when user clicks on the notification
     * - template: The message to be displayed within the notification
     *
     * @since 1.0
     * @param array $params Notification message parameters
     * @param int (optional) $chunk_size The max number of API calls per batch (default: 40)
     * @param bool (optional) $show_progress If true, outputs a progress message (default: false)
     * @return mixed True if entire batch completed successfully, otherwise array containing errors
     */
    public function send_batch_notifications( $params = array(), $chunk_size = 40, $show_progress = false ) {
        $fb = $this->fb;

        // Increase timeout time limits
        rsm_set_timeout( 600 );

        // Set our application access token
        $fb_access_token = $this->get_app_access_token();
        $fb->setAccessToken( $fb_access_token );

        // Build batch API calls
        $batch = array();
        foreach( $params as $param ) {
            $batch[] = array( 'method'          => 'POST',
                              'relative_url'    => '/' . $param['uid'] . '/notifications',
                              'include_headers' => false,
                              'body'            => http_build_query( array( 'access_token' => $fb_access_token,
                                                                            'href'         => $param['href'],
                                                                            'template'     => $param['template']
                                                                          )
                                                                   )
            );
        }

        // Break batch down into smaller chunks (to satisfy FB API batch limit)
        $retval   = array();
        $results  = array();
        $chunks   = array_chunk( $batch, $chunk_size );

        // Progress related variables
        $total    = count( $params );
        $progress = 0;

        // Batch send each chunk of notifications
        foreach ( $chunks as $chunk ) {
            // Send batch FB user notifications
            $result = $fb->fetch( $this->endpoint_map['graph'] . '/',
                                  array( 'batch' => json_encode( $chunk ) ),
                                  OAuth2\Client::HTTP_METHOD_POST
                                );
            $results = array_merge( $results, $result );

            if ( $show_progress ) {
                $progress = $progress + count( $chunk );
                $message  = '<p>Sent <strong>' . $progress . '</strong> out of <strong>' . $total . '</strong> notifications...</p>';
                rsm_show_message( $message );
            }
        }

        // Parse results
        if ( $results ) {
            foreach( $results['result'] as $key=>$result ) {
                if ( isset( $result['code'] ) && $result['code'] !== 200 ) {
                    $retval[] = array_merge( json_decode( $result['body'], true ), array( 'batch' => $batch[ $key ] ) );
                }
            }
        }

        // Return results if error, otherwise true
        return $retval ? $retval : true;
    }

	/**
	 * Returns the details of a Facebook Application.
	 *
	 * @since 1.0
	 * @return array Result array containing any available values for fields list
	 * @throws RSM_FB_Exception
	 */
	public function get_app_settings() {
		$fb = $this->fb;

		// Set our application access token
		$fb_access_token = $this->get_app_access_token();
		$fb->setAccessToken( $fb_access_token );

		// Get the listed fields
		$fields = array( 'app_name', 'app_domains', 'supported_platforms', 'canvas_url', 'secure_canvas_url', 'website_url', 'icon_url', 'logo_url' );
		$result = $fb->fetch( $this->endpoint_map['graph'] . '/' . $fb->getClientId(),
							  array( 'fields' => implode( ',', $fields ) )
							);

		// Check results and throw error if found
		if ( is_array( $result ) && isset( $result['result']['error'] ) ) {
			throw new Exception( 'Graph API error: ' . $result['result']['error']['message'] );
		}
		return $result;
	}

	/**
	 * Sets the details of a Facebook Application.
	 *
	 * @since 1.0
	 * @param array $params Array of Graph API App parameters to set
	 * @return array Result array indicating success
	 * @throws RSM_FB_Exception
	 */
	public function set_app_settings( $params = array() ) {
		$fb = $this->fb;

		// Set our application access token
		$fb_access_token = $this->get_app_access_token();
		$fb->setAccessToken( $fb_access_token );

		// Update the supplied fields
		$result = $fb->fetch( $this->endpoint_map['graph'] . '/' . $fb->getClientId(),
							  $params,
							  OAuth2\Client::HTTP_METHOD_POST
							);

		// Check results and throw error if found
		if ( is_array( $result ) && isset( $result['result']['error'] ) ) {
			throw new Exception( 'Graph API error: ' . $result['result']['error']['message'] );
		}
		return $result;
	}
}
