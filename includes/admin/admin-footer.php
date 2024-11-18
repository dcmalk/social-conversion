<?php
/**
 * Admin Footer
 *
 * @package     RSM
 * @subpackage  Admin/Footer
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Custom footer for admin dashboard
 *
 * @since 1.0
 * @param string $default The existing footer text
 * @return string New footer text if RSM admin page, otherwise default footer
 */
function rsm_admin_footer( $default ) {
	if ( ! rsm_is_admin_page() ) {
		return $default;
	}

    $return = sprintf( '<span class="alignleft">Copyright © 2017 <a href="%s" style="color:#777;" target="_blank">New Expanse Media</a> | <a href="%s">Help</a> | <a href="%s" target="_blank">Support Desk</a> | <a href="%s" target="_blank">Affiliate Program</a></span>',
		'http://newexpanse.com',
        'admin.php?page=social-conversion-help',
		'http://support.newexpanse.com',
		'http://socialconversion.io/jv'
	);

	return $return . '<br />' . $default;
}
add_filter( 'admin_footer_text', 'rsm_admin_footer' );

/**
 * Custom footer for admin dashboard
 *
 * @since 1.0
 * @param string $default The existing footer text
 * @return string New footer text if RSM admin page, otherwise default footer
 */
function rsm_admin_footer_version( $default ) {
	if ( ! rsm_is_admin_page() ) {
		return $default;
	}

    $return = sprintf( '<span class="alignright">%s v%s',
        RSM_PLUGIN_NAME . ' ' . rsm_get_edition(),
        RSM_VERSION
    );

    if ( 4 != (int) rsm_get_option( 'price_id' ) ) {
        $return .= sprintf( ' - <a href="%s" target="_blank">Upgrade info</a></span>',
            'http://socialconversion.io/upgrade'
        );
    }

	return $return . '<br />' . '<span class="alignright">' . $default . '</span>';
}
add_filter( 'update_footer', 'rsm_admin_footer_version', 11 );
