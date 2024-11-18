<?php
/**
 * Misc Functions
 *
 * @package     RSM
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Attempts a javascript redirect with optional delay and breaking
 * out of an iframe. A meta refresh redirect is staged 1 second
 * later in case javascript is disabled. Note that remove_fb_hash()
 * removes the Facebook appended hash (bug), as described:
 * http://stackoverflow.com/questions/7131909/facebook-callback-appends-to-return-url
 *
 * @since 1.0
 * @param string $url The URL to redirect to
 * @param int (optional) $delay Number of milliseconds to delay before redirecting
 * @param bool (optional) $break_iframe Whether to break out of an iframe
 * @return void
 */
function rsm_js_redirect( $url, $delay = 0, $break_iframe = false ) {
    $delay = max( (int) $delay, 0 );
    ?>
    <meta http-equiv="refresh" content="<?php echo empty( $delay ) ? 1 : ( round( $delay / 1000 ) + 1 ) ?>;url=<?php echo $url; ?>">
    <script type="text/javascript">
        var url = "<?php echo $url; ?>";
        window.onload = function () {
            do_redirect();
        }
        function do_redirect() {
            setTimeout(goto_url, <?php echo $delay; ?>);
        }
        function remove_fb_hash() {
            if (window.location.hash && window.location.hash == '#_=_')
                window.location.hash = '';
        }
        function goto_url() {
            remove_fb_hash();
            <?php if ( $break_iframe ) : ?>
            top.location.replace(url);
            <?php else: ?>
            window.location.replace(url);
            <?php endif; ?>
        }
    </script>
    <?php
}

/**
 * Issues a redirect via Javascript and closes the popup window (if open).
 *
 * @since 1.0
 * @param string $url The URL to redirect to
 * @return void
 */
function rsm_redirect_maybe_close_popup( $url ) {
    //<!DOCTYPE html>
    ?>
    <script type="text/javascript">
	    /*if ( !window.opener ) window.opener = window.open( '', 'fbConnect' );*/
        var opener = window.opener;
        if (opener) {
            <?php if ( ! empty( $url ) ) : ?>
            opener.location.href="<?php echo $url; ?>";
            <?php endif; ?>
            window.close();
        } else {
            <?php if ( ! empty( $url ) ) : ?>
            window.location.href="<?php echo $url; ?>";
            <?php endif; ?>
        }
    </script>
    <?php
}

/**
 * Removes control characters (first 32 ascii and \x7F ) from a string.
 *
 * @since 1.0
 * @param string $val The string to remove control characters from
 * @return string Supplied string without control characters
 */
function rsm_strip_control( $val ) {
    return preg_replace( '/[\x00-\x1F\x7F]/', '', $val );
}

/**
 * Sets the timeout period for when a script executes, including whether a user can abort.
 *
 * @since 1.0
 * @param int $seconds The number of seconds before timing out
 * @param bool (optional) $ignore_abort Whether the user can abort
 * @return void
 */
function rsm_set_timeout( $seconds = 0, $ignore_abort = true  ) {
    ignore_user_abort( $ignore_abort );

    if ( ! rsm_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
        @set_time_limit( $seconds );
        //@ini_set( 'max_execution_time', $seconds );
    }
}

/**
 * Checks whether function is disabled.
 *
 * @since 1.0
 * @param string $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function rsm_is_func_disabled( $function ) {
    $disabled = explode( ',',  ini_get( 'disable_functions' ) );

    return in_array( $function, $disabled );
}

/**
 * Base64 encoding that is URL safe.
 *
 * @since 1.0
 * @param string $data The string to encode.
 * @return string Base64 encode string.
 */
function rsm_base64url_encode( $data ) {
	return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
}

/**
 * Base64 decoding of URL safe strings.
 *
 * @since 1.0
 * @param string $data The string to decode.
 * @return string Base64 decoded string.
 */
function rsm_base64url_decode( $data ) {
	return base64_decode( strtr( $data, '-_', '+/' ) );
}

/**
 * Prints formatted information about a variable.
 *
 * @since 1.0
 * @param mixed $val The expression to be printed
 * @return string Formatted information about specified variable
 */
function print_r2( $val ) {
    echo '<pre>';
    print_r( $val );
    echo '</pre><br />';
}
