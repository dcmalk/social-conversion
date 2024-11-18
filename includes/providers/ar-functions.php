<?php
/**
 * Autoresponder Functions
 *
 * @package     RSM
 * @subpackage  Autoresponder/Miscellaneous
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gets the URL where the user can authenticate and authorize the requesting application. Note that this is
 * functionally the same as CtctOAuth2/getAuthorizationUrl() but without the needs to load the entire API.
 *
 * @since 1.0
 * @return string The url to send a user to for granting access to their account
 */
function rsm_get_ctct_auth_url() {
    //$redirect  = urlencode( admin_url( 'admin-post.php?action=rsm_ctctauth' ) );
    $redirect  = urlencode( home_url( '?rsm-action=ctctauth' ) );
    $oauth_url = ( RSM_CTCT_OAUTH_URL . 'ctctauth.php?redirect=' . $redirect );
    $ctct_url  = 'https://oauth2.constantcontact.com/oauth2/oauth/siteowner/authorize';

    $params = array(
        'response_type' => 'code',
        'client_id'     => RSM_CTCT_API_KEY,
        'redirect_uri'  => $oauth_url
    );

    return $ctct_url . '?' . http_build_query( $params, '', '&' );
}

/**
 * Listens for a redirect callback from the Ctct OAuth2 flow. When called, insert the access token and close
 * the popup window using Javascript.
 *
 * eg, http://apps.newexpanse.com/ctct/ctctauth.php?redirect=http%3A%2F%2Fwww.wplocal.com%2Fwp-admin%2Fadmin-post.php%3Faction%3Drsm_ctctauth&code=lrWHWTa5fadCqUevbsN52KRiif6&username=damon%40newexpanse.com
 *
 * @since 1.0
 * @param array $request HTTP Request variables
 * @return void
 */
function rsm_process_ctctauth( $request = array() ) {
    // Load Ctct if not loaded; this is needed for creating the OAuth2 help link
    if ( ! class_exists( 'Ctct\SplClassLoader' ) ) {
        require_once RSM_PLUGIN_DIR . 'includes/vendor/Ctct/autoload.php';
    }

    // Setup our OAuth object
    $redirect  = urlencode( home_url( '?rsm-action=ctctauth' ) );
    $oauth_url = ( RSM_CTCT_OAUTH_URL . 'ctctauth.php?redirect=' . $redirect );
    $oauth     = new \Ctct\Auth\CtctOAuth2( RSM_CTCT_API_KEY, RSM_CTCT_SECRET, $oauth_url );

    // Get access token
    try {
        $response = $oauth->getAccessToken( $request['code'] );
        $access_token = isset( $response['access_token'] ) ? $response['access_token'] : '';
    } catch ( Exception $e ) {
        rsm_exception_handler( $e, true, true );
    }

    ?>
    <script type="text/javascript">
        var opener = window.opener;
        if (opener) {
            <?php if ( empty( $access_token ) ) : ?>
            var elem = opener.document.getElementById("ctct-results");
            elem.innerHTML = '<div class="alert bg-rsm-red alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>OAuth2 error getting access token.</div>';
            <?php else: ?>
            opener.document.getElementById("ctct-api-key").value = "<?php echo $access_token; ?>";
            opener.document.getElementById("ctct-results").style.display = 'none';
            <?php endif; ?>
        }
        window.close();
    </script>
    <?php
}
