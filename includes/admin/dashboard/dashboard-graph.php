<?php
/**
 * Dashboard Graphing Functions
 *
 * @package     RSM
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2017, Damon Malkiewicz
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Returns the list of graphing periods.
 *
 * @since 1.0
 * @return array Array of valid graphing periods
 */
function rsm_get_graph_periods(){
    return array(
    	'today',
    	'yesterday',
    	'this_week',
    	'last_week',
    	'this_month',
    	'last_month',
    	'this_year',
    	'last_year'
    );
}

/**
 * Show dashboard graph.
 *
 * @since 1.0
 * @return void
*/
function rsm_dashboard_graph() {
	// Retrieve the queried dates
	$dates = rsm_get_graph_dates();

	// Determine graph options
	switch ( $dates['range'] ):
		case 'today':
		case 'yesterday':
			$day_by_day	= true;
			$min_tick_size = '[1, "hour"]';
			$time_format = '%I:%M%p';
			break;
		case 'this_year':
		case 'last_year':
			$day_by_day = false;
			$min_tick_size = '[1, "month"]';
			$time_format = '%b';
			break;
		default:
            $day_by_day = true;
			$min_tick_size = '[1, "day"]';
			$time_format = '%b %e';
			break;
	endswitch;

	$sent_totals  = 0;	// Total notifications sent for time period shown
	$click_totals = 0;  // Total clicks for time period shown
	$optin_totals = 0;	// Total opt-ins for time period shown

	$sent_data  = array();
	$click_data = array();
	$optin_data = array();

	if( $dates['range'] == 'today' || $dates['range'] == 'yesterday' ) {
		// Hour by hour
		$hour  = 1;
		$month = $dates['m_start'];
		while ( $hour <= 23 ) :
			$sent   = rsm_get_sent_by_date( $dates['day'], $month, $dates['year'], $hour );
			$clicks = rsm_get_clicks_by_date( $dates['day'], $month, $dates['year'], $hour );
			$optins = rsm_get_optins_by_date( $dates['day'], $month, $dates['year'], $hour );

			$sent_totals  += $sent;
			$click_totals += $clicks;
			$optin_totals += $optins;

			$date         = mktime( $hour, 0, 0, $month, $dates['day'], $dates['year'] ) * 1000;
			$sent_data[]  = array( $date, $sent );
			$click_data[] = array( $date, $clicks );
			$optin_data[] = array( $date, $optins );

			$hour++;
		endwhile;

	} elseif( $dates['range'] == 'this_week' || $dates['range'] == 'last_week' ) {
        // Day by day
        $num_of_days  = cal_days_in_month( CAL_GREGORIAN, $dates['m_start'], $dates['year'] );
        $report_dates = array();
        $i            = 0;
        while ( $i <= 6 ) {
            if ( ( $dates['day'] + $i ) <= $num_of_days ) {
                $report_dates[ $i ] = array(
                    'day'   => (string) ( $dates['day'] + $i ),
                    'month' => $dates['m_start'],
                    'year'  => $dates['year'],
                );
            } else {
                $report_dates[ $i ] = array(
                    'day'   => (string) $i,
                    'month' => $dates['m_end'],
                    'year'  => $dates['year_end'],
                );
            }
            $i++;
        }

        foreach ( $report_dates as $report_date ) {
            $sent   = rsm_get_sent_by_date( $report_date['day'], $report_date['month'], $report_date['year'] );
            $clicks = rsm_get_clicks_by_date( $report_date['day'], $report_date['month'], $report_date['year'] );
            $optins = rsm_get_optins_by_date( $report_date['day'], $report_date['month'], $report_date['year'] );

            $sent_totals  += $sent;
            $click_totals += $clicks;
            $optin_totals += $optins;

            $date         = mktime( 0, 0, 0, $report_date['month'], $report_date['day'], $report_date['year'] ) * 1000;
            $sent_data[]  = array( $date, $sent );
            $click_data[] = array( $date, $clicks );
            $optin_data[] = array( $date, $optins );
        }

	} else {
		$y = $dates['year'];
		while( $y <= $dates['year_end'] ) {
            $last_year = false;

            if ( $dates['year'] == $dates['year_end'] ) {
                $month_start = $dates['m_start'];
                $month_end   = $dates['m_end'];
                $last_year   = true;
            } elseif ( $y == $dates['year'] ) {
                $month_start = $dates['m_start'];
                $month_end   = 12;
            } elseif ( $y == $dates['year_end'] ) {
                $month_start = 1;
                $month_end   = $dates['m_end'];
            } else {
                $month_start = 1;
                $month_end   = 12;
            }

            $i = $month_start;
            while ( $i <= $month_end ) {
                if ( $day_by_day ) {
                    $d = $dates['day'];
                    if ( $i == $month_end ) {
                        $num_of_days = $dates['day_end'];
                        if ( $month_start < $month_end ) {
                            $d = 1;
                        }
                    } else {
                        $num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
                    }
                    while ( $d <= $num_of_days ) {
                        $sent   = rsm_get_sent_by_date( $d, $i, $y );
                        $clicks = rsm_get_clicks_by_date( $d, $i, $y );
                        $optins = rsm_get_optins_by_date( $d, $i, $y );

                        $sent_totals  += $sent;
                        $click_totals += $clicks;
                        $optin_totals += $optins;

                        $date         = mktime( 0, 0, 0, $i, $d, $y ) * 1000;
                        $sent_data[]  = array( $date, $sent );
                        $click_data[] = array( $date, $clicks );
                        $optin_data[] = array( $date, $optins );

                        $d ++;
                    }
                } else {
                    $sent   = rsm_get_sent_by_date( null, $i, $y );
                    $clicks = rsm_get_clicks_by_date( null, $i, $y );
                    $optins = rsm_get_optins_by_date( null, $i, $y );

                    $sent_totals  += $sent;
                    $click_totals += $clicks;
                    $optin_totals += $optins;

                    if ( $i == $month_end && $last_year ) {
                        $num_of_days = cal_days_in_month( CAL_GREGORIAN, $i, $y );
                    } else {
                        $num_of_days = 1;
                    }

                    $date         = mktime( 0, 0, 0, $i, $num_of_days, $y ) * 1000;
                    $sent_data[]  = array( $date, $sent );
                    $click_data[] = array( $date, $clicks );
                    $optin_data[] = array( $date, $optins );
                }
                $i ++;
            }
            $y ++;
        }

	}

/*
$sent_data = array( array(1406851200000,1),array(1406937600000,0),array(1407024000000,0),array(1407110400000,0),array(1407196800000,0),array(1407283200000,2),array(1407369600000,0),array(1407456000000,0),array(1407542400000,0),array(1407628800000,0),array(1407715200000,0),array(1407801600000,0),array(1407888000000,0),array(1407974400000,0),array(1408060800000,0),array(1408147200000,0),array(1408233600000,0),array(1408320000000,0),array(1408406400000,0),array(1408492800000,0),array(1408579200000,0),array(1408665600000,0),array(1408752000000,0),array(1408838400000,0),array(1408924800000,0),array(1409011200000,0),array(1409097600000,0),array(1409184000000,0),array(1409270400000,0),array(1409356800000,0),array(1409443200000,10) );
$click_data = array( array(1406851200000,2),array(1406937600000,0),array(1407024000000,0),array(1407110400000,0),array(1407196800000,0),array(1407283200000,10),array(1407369600000,0),array(1407456000000,0),array(1407542400000,0),array(1407628800000,0),array(1407715200000,0),array(1407801600000,0),array(1407888000000,0),array(1407974400000,0),array(1408060800000,0),array(1408147200000,0),array(1408233600000,0),array(1408320000000,0),array(1408406400000,0),array(1408492800000,0),array(1408579200000,0),array(1408665600000,0),array(1408752000000,0),array(1408838400000,0),array(1408924800000,0),array(1409011200000,0),array(1409097600000,0),array(1409184000000,0),array(1409270400000,0),array(1409356800000,0),array(1409443200000,20) );
$optin_data = array( array(1406851200000,3),array(1406937600000,0),array(1407024000000,0),array(1407110400000,0),array(1407196800000,0),array(1407283200000,20),array(1407369600000,0),array(1407456000000,0),array(1407542400000,0),array(1407628800000,0),array(1407715200000,0),array(1407801600000,0),array(1407888000000,0),array(1407974400000,0),array(1408060800000,0),array(1408147200000,0),array(1408233600000,0),array(1408320000000,0),array(1408406400000,0),array(1408492800000,0),array(1408579200000,0),array(1408665600000,0),array(1408752000000,0),array(1408838400000,0),array(1408924800000,0),array(1409011200000,0),array(1409097600000,0),array(1409184000000,0),array(1409270400000,0),array(1409356800000,0),array(1409443200000,30) );
*/

	$data = array(
        'Opt-ins' => $optin_data,
        'Sent'   => $sent_data,
		'Clicks'  => $click_data

	);

    rsm_graph_periods( $dates['range'] );
    $graph = new rsm_Graph( $data );
    $graph->set( 'range', $dates['range'] );
    $graph->set( 'y_label', 'Amount' );
    $graph->set( 'x_label', ucwords( str_replace( '_', ' ', $dates['range'] ) ) );
    $graph->set( 'min_tick_size', $min_tick_size );
    $graph->set( 'time_format', $time_format );
    $graph->display();
    $colors = $graph->get('line_colors');

    echo sprintf('<p class="rsm-graph-totals">Total opt-ins for period shown: <span style="color: %s;"><strong>%d</strong></span></p>', $colors[0], $optin_totals );
    echo sprintf('<p class="rsm-graph-totals">Total sent for period shown: <span style="color: %s;"><strong>%d</strong></span></p>', $colors[1], $sent_totals );
    echo sprintf('<p class="rsm-graph-totals">Total clicks for period shown: <span style="color: %s;"><strong>%d</strong></span></p>', $colors[2], $click_totals );


	echo ob_get_clean();
}

/**
 * Show dashboard graphing periods.
 *
 * @since 1.0
 * @param string $current Selected graphing period
 * @return void
*/
function rsm_graph_periods( $current ) {
	$periods = rsm_get_graph_periods();
	?>
	    <ul class="rsm-graph-period">
	        <li>Select Period:</li>
	     	<?php
			    foreach ( $periods as $period ) {
			        echo sprintf('<li><a %s href="?page=social-conversion-dashboard&range=%s">%s</a></li>', $period == $current ? 'class="current"' : '', $period, ucwords( str_replace( '_', ' ', $period ) ) );
			    }
    		?>
	    </ul>
	<?php
}

/**
 * Sets up the dates used to filter graph data. Date sent via $_GET is read first
 * and then modified (if needed) to match the selected date-range (if any).
 *
 * @since 1.0
 * @return array Date values
*/
function rsm_get_graph_dates() {
	$dates = array();

	// Get the current time using WP timezone
	$current_time  = current_time( 'timestamp' );
	$start_of_week = ( 0 == get_option( 'start_of_week' ) ? 0 : 1 );

    $dates['range']    = isset( $_GET['range'] )   ? $_GET['range']   : 'this_month';
    $dates['year']     = isset( $_GET['year'] )    ? $_GET['year']    : date( 'Y', $current_time );
    $dates['year_end'] = isset( $_GET['year_end'] )? $_GET['year_end']: date( 'Y', $current_time );
	$dates['m_start']  = isset( $_GET['m_start'] ) ? $_GET['m_start'] : 1;
	$dates['m_end']    = isset( $_GET['m_end'] )   ? $_GET['m_end']   : 12;
    $dates['day']      = isset( $_GET['day'] )     ? $_GET['day']     : 1;
    $dates['day_end']    = isset( $_GET['day_end'] ) ? $_GET['day_end'] : cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );

	// Modify dates based on predefined ranges
	switch ( $dates['range'] ) :

		case 'this_month' :
			$dates['m_start'] = date( 'n', $current_time );
			$dates['m_end']   = date( 'n', $current_time );
            $dates['day']      = 1;
            $dates['day_end']  = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
            $dates['year']     = date( 'Y', $current_time );
            $dates['year_end'] = date( 'Y', $current_time );
		break;

		case 'last_month' :
			if( date( 'n' ) == 1 ) {
				$dates['m_start'] = 12;
				$dates['m_end']	  = 12;
				$dates['year']    = date( 'Y', $current_time ) - 1;
				$dates['year_end']= date( 'Y', $current_time ) - 1;
			} else {
				$dates['m_start'] = date( 'n', $current_time ) - 1;
				$dates['m_end']	  = date( 'n', $current_time ) - 1;
				$dates['year_end']= $dates['year'];
			}
            $dates['day_end'] = cal_days_in_month( CAL_GREGORIAN, $dates['m_end'], $dates['year'] );
		break;

		case 'today' :
			$dates['day']		= date( 'd', $current_time );
			$dates['m_start'] 	= date( 'n', $current_time );
			$dates['m_end']		= date( 'n', $current_time );
			$dates['year']		= date( 'Y', $current_time );
		break;

		case 'yesterday' :
            $year  = date( 'Y', $current_time );
            $month = date( 'n', $current_time );
            $day   = date( 'd', $current_time );

            if ( $month == 1 && $day == 1 ) {
                $year  -= 1;
                $month = 12;
                $day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );
            } elseif ( $month > 1 && $day == 1 ) {
                $month -= 1;
                $day   = cal_days_in_month( CAL_GREGORIAN, $month, $year );
            } else {
                $day -= 1;
            }

            $dates['day']       = $day;
            $dates['m_start']   = $month;
            $dates['m_end']     = $month;
            $dates['year']      = $year;
            $dates['year_end']  = $year;

		break;

        case 'this_week' :
        case 'last_week' :
            $base_time = $dates['range'] === 'this_week' ? current_time( 'mysql' ) : date( 'Y-m-d h:i:s', $current_time - WEEK_IN_SECONDS );
            $start_end = get_weekstartend( $base_time, $start_of_week );

            $dates['day']      = date( 'd', $start_end['start'] );
            $dates['m_start']  = date( 'n', $start_end['start'] );
            $dates['year']     = date( 'Y', $start_end['start'] );

            $dates['day_end']  = date( 'd', $start_end['end'] );
            $dates['m_end']    = date( 'n', $start_end['end'] );
            $dates['year_end'] = date( 'Y', $start_end['end'] );
            break;

		case 'this_year' :
			$dates['m_start'] 	= 1;
			$dates['m_end']		= 12;
			$dates['year']		= date( 'Y', $current_time );
		break;

		case 'last_year' :
			$dates['m_start'] 	= 1;
			$dates['m_end']		= 12;
			$dates['year']		= date( 'Y', $current_time ) - 1;
			$dates['year_end']  = date( 'Y', $current_time ) - 1;
		break;

	endswitch;

	return $dates;
}
