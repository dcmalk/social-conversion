<?php
/**
 * Admin Pages
 *
 * @package     RSM
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *  Determines whether the current admin page is an RSM admin page.
 *
 *  @since 1.0
 *  @return bool True if is an RSM admin page
 */
function rsm_is_admin_page() {
    global $rsm_dashboard_page, $rsm_campaigns_page, $rsm_subscribers_page, $rsm_segmenting_page,$rsm_log_page, $rsm_settings_page, $rsm_help_page;

    if ( ! is_admin() ) {
        return false;
    }

    $rsm_admin_pages = array( $rsm_dashboard_page, $rsm_campaigns_page, $rsm_subscribers_page, $rsm_segmenting_page, $rsm_log_page, $rsm_settings_page, $rsm_help_page );

    $screen = get_current_screen();
    if ( in_array( $screen->id, $rsm_admin_pages ) ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Creates the admin submenu pages under the main menu and assigns their links
 * to global variables
 *
 * @since 1.0
 * @global $rsm_dashboard_page
 * @global $rsm_subscribers_page
 * @global $rsm_segmenting_page
 * @global $rsm_log_page
 * @global $rsm_settings_page
 * @global $rsm_help_page
 * @return void
 */
function rsm_add_menu() {
	global $rsm_dashboard_page, $rsm_campaigns_page, $rsm_subscribers_page, $rsm_segmenting_page, $rsm_log_page, $rsm_settings_page, $rsm_help_page;

    $menu_title = '<span style="letter-spacing: -.028em;" class="rsm-spacing-fix">' . RSM_PLUGIN_NAME . '</span>';

    // Verify license is active
    if ( !rsm_transient_state() ) {
        add_menu_page( RSM_PLUGIN_NAME, $menu_title, 'manage_options', RSM_PLUGIN_SLUG . '-license', 'rsm_sl_page', RSM_PLUGIN_URL . 'assets/img/rsm_wp_icon.png' );
        if ( isset( $_GET['page'] ) && ( 0 === strpos( $_GET['page'], RSM_PLUGIN_SLUG ) ) && ( RSM_PLUGIN_SLUG . '-license' <> $_GET['page'] ) ) {
            wp_redirect( 'admin.php?page=social-conversion-license' );
            exit;
        }
        return;
    }

    // Create main menu
    add_menu_page( RSM_PLUGIN_NAME, $menu_title, 'manage_options', RSM_PLUGIN_SLUG . '-dashboard', 'rsm_dashboard_page', RSM_PLUGIN_URL . 'assets/img/rsm_wp_icon.png' );

    // Create submenu pages
    $rsm_dashboard_page    = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Dashboard', 'Dashboard', 'manage_options', RSM_PLUGIN_SLUG . '-dashboard', 'rsm_dashboard_page' );
    $rsm_campaigns_page    = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Campaigns', 'Campaigns', 'manage_options', RSM_PLUGIN_SLUG . '-campaigns', 'rsm_campaigns_page' );
	$rsm_new_campaign_page = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'New Campaign', 'New Campaign', 'manage_options', RSM_PLUGIN_SLUG . '-campaigns&rsm-action=add_campaign', 'rsm_campaigns_page' );
    $rsm_subscribers_page  = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Subscribers', 'Subscribers', 'manage_options', RSM_PLUGIN_SLUG . '-subscribers', 'rsm_subscribers_page' );
	$rsm_segmenting_page   = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Segmenting', 'Segmenting', 'manage_options', RSM_PLUGIN_SLUG . '-segmenting', 'rsm_segmenting_page' );
    $rsm_log_page          = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Delivery Log', 'Delivery Log', 'manage_options', RSM_PLUGIN_SLUG . '-log', 'rsm_log_page' );
    $rsm_settings_page     = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Settings', 'Settings', 'manage_options', RSM_PLUGIN_SLUG . '-settings', 'rsm_settings_page' );
    $rsm_help_page         = add_submenu_page( RSM_PLUGIN_SLUG . '-dashboard', 'Help', 'Help', 'manage_options', RSM_PLUGIN_SLUG . '-help', 'rsm_help_page' );
}
add_action( 'admin_menu', 'rsm_add_menu', 10 );

/**
 * Renders the main header on admin pages
 *
 * @since 1.0
 * @return void
 */
function rsm_admin_main_header() {
    ?>
    <header class="main-header">

        <!-- Header Navbar -->
        <nav role="navigation" class="navbar navbar-static-top" style="text-align:center;">

            <!-- Logo -->
            <div class="rsm-logo">
                <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-dashboard"">
                    <img src="<?php echo RSM_PLUGIN_URL . 'assets/img/rsm_header.svg' ?>" alt="Social Conversion" width="309" height="36" >
                </a>
            </div>

            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">

	                <!--<li class="notifications-menu">
		                <a href="#" target="_blank" style="color:#777;"><i class="fa fa-flask"></i> beta testers</a>
	                </li>-->

                    <li class="messages-menu">
                        <a href="<?php echo RSM_USER_GUIDE_URL; ?>" target="_blank" style="color:#777;"><i class="fa fa-book"></i> user guide</a>
                    </li>


                    <li class="dropdown messages-menu" style="margin-bottom:0;">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#" style="color: #777;"><i class="fa fa-bars"></i> menu</a>

                        <ul class="dropdown-menu dropdown-dark">
                            <li class="control-sidebar-heading" style="text-align: center;">Navigation Menu</li>
                            <li>
                                <ul class="control-sidebar-menu menu">
                                    <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-dashboard">
                                            <i class="menu-icon fa fa-dashboard bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Dashboard</h4>
                                                <p>reports & system details</p>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-campaigns">
                                            <i class="menu-icon fa fa-bullhorn bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Campaigns</h4>
                                                <p>management & performance</p>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-subscribers">
                                            <i class="menu-icon fa fa-user bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Subscribers</h4>
                                                <p><?php echo ( rsm_feature_check( 3 ) ?  'details & import/export' : 'subscriber opt-in details' ); ?></p>
                                            </div>
                                        </a>
                                    </li>

	                                <li>
		                                <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-segmenting">
			                                <i class="menu-icon fa fa-braille bg-rsm-slate"></i>
			                                <div class="menu-info">
				                                <h4 class="control-sidebar-subheading">Segmenting</h4>
				                                <p>subscriber segment setup</p>
			                                </div>
		                                </a>
	                                </li>

	                                <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-log">
                                            <i class="menu-icon fa fa-book bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Delivery Log</h4>
                                                <p>scheduled & sent notifications</p>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-settings">
                                            <i class="menu-icon fa fa-cogs bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Settings</h4>
                                                <p>system configuration</p>
                                            </div>
                                        </a>
                                    </li>

                                    <li>
                                        <a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-help">
                                            <i class="menu-icon fa fa-question-circle bg-rsm-slate"></i>
                                            <div class="menu-info">
                                                <h4 class="control-sidebar-subheading">Help</h4>
                                                <p>tutorials, faq & support</p>
                                            </div>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                        </ul>
                    </li>


                </ul>
            </div>


        </nav>
    </header>
    <?php
}

/**
 * Renders the content header on admin pages
 *
 * @since 1.0
 * @param string $current Current tab id
 * @return void
 */
function rsm_admin_content_header( $current = null ) {
    if ( $current == null ) {
        $current = 'dashboard';
    }

    $header_btn = false;
    switch ( $current ) {
        case 'dashboard':
            $subtitle = 'reports & system details';
            break;
        case 'campaigns-procn':
            $current = 'campaigns';
            $subtitle = 'process notifications';
            break;
        case 'campaigns-table':
            $current = 'campaigns';
            $subtitle = 'management & performance';
            $header_btn = '<a class="page-title-action rsm-add-new" href="admin.php?page=' . RSM_PLUGIN_SLUG . '-campaigns&rsm-action=add_campaign"><i class="fa fa-plus"></i> Add New</a>';
            break;
        case 'campaigns-add':
            $current = 'campaigns';
            $subtitle = 'new campaign setup';
            break;
        case 'campaigns-edit':
            $current = 'campaigns';
            $subtitle = 'edit campaign';
            break;
        case 'campaigns-edit-seq':
            $current = 'campaigns';
            $subtitle = 'edit sequence';
            break;
        case 'subscribers':
            $subtitle = ( rsm_feature_check( 3 ) ? 'details & import/export' : 'opt-in details' );
            break;
	    case 'segmenting':
		    $subtitle = 'subscriber segment setup';
		    break;
        case 'log':
            $subtitle = 'scheduled & sent notifications';
            break;
        case 'settings':
            $subtitle = 'system configuration';
            break;
        case 'help':
            $subtitle = 'tutorials, faq & support';
            break;
    }
    $page = ucfirst( $current );

    ?>
    <section class="content-header">
        <h1>
            <?php echo $page; ?>
            <small><?php echo $subtitle; ?></small>
            <?php if( $header_btn ) echo $header_btn; ?>
        </h1>
        <ul class="breadcrumb">
            <li><a href="?page=<?php echo RSM_PLUGIN_SLUG; ?>-dashboard"><i class="fa fa-home"></i> Home<?php /*echo RSM_PLUGIN_NAME;*/ ?></a></li>
            <li class="active"><?php echo $page; ?></li>
        </ul>
    </section>
    <?php
}

/**
 * Renders the pre-footer content.
 *
 * @since 1.0
 * @return void
 */
function rsm_admin_pre_footer() {
    ?>
    <p class="rsm-pre-footer">NOTE: Notification delivery can be delayed up to 10 minutes, depending on your cron settings.<strong><span id="pre-footer-refresh" class="rsm-footer-refresh" data-toggle="tooltip" title="Click to force processing now"><i class="fa fa-refresh" style="margin:0 4px 2px 10px;"></i>Process now</span></strong>
    <span id="rsm-pre-footer-refresh-loading" style="display:none;"><i class="fa fa-spinner fa-pulse fa-1-5x" style="vertical-align:top; "></i></span>
    </p>
    <?php
}
