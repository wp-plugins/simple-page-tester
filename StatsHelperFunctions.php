<?php

/*******************************************************************************
** sptGetChartData
** Gets the link data for the chart and returns an array ready for json encoding
** @param from_date - starting date to collect link data for
** @param to_date - ending date to collect link data for
** @param link_slug - limit the search to this link slug
** @param cat_slug - limit the search to this category slug
** @return array - organised link data
** @since 1.1
*******************************************************************************/
function sptGetChartData($splitTestID, $pageID, $fromDate = null, $toDate = null) {
	$dataArray = array();
	
	// First, sanitize the inputs
	if ($fromDate == null || empty($fromDate))
		$fromDate = get_the_time('Y-m-d', $splitTestID);

	if ($toDate == null || empty($toDate))
		$toDate = date('Y-m-d', current_time('timestamp'));

	// For each link, find the number of clicks for each given date
	$dates = sptGetDays($fromDate, $toDate);
	
	foreach ($dates as $date) {
		$viewsForDate = 0;
		
		$unixStartOfDate = mktime(00, 00, 00, date("m", strtotime($date)), date("d", strtotime($date)), date("Y", strtotime($date)));
		$unixEndOfDate = mktime(23, 59, 59, date("m", strtotime($date)), date("d", strtotime($date)), date("Y", strtotime($date)));

		// Get this day's views on this page
		$viewsForDate = count(sptViewsForPeriod($splitTestID, $pageID, $unixStartOfDate, $unixEndOfDate));
		
		// Add this day's views to the array
		$dataArray[] = array($date, (!empty($viewsForDate) ? $viewsForDate : 0));
	}
	
	// Allow filtering of the chart data before return
	$dataArray = apply_filters('spt_chart_data', $dataArray, $splitTestID, $pageID);

	return $dataArray;
}

/*******************************************************************************
** sptGetDays
** Gets the link data for the chart and returns an array ready for json encoding
** Credit must goto Ed Rackham for this code, adapted for use here
** (http://edrackham.com/php/get-days-between-two-dates-using-php/)
** @param sStartDate - starting date
** @param sEndDate - ending date
** @return array - dates in an array
** @since 1.1
*******************************************************************************/
function sptGetDays($sStartDate, $sEndDate){
	// Firstly, format the provided dates.
	// This function works best with YYYY-MM-DD
	// but other date formats will work thanks
	// to strtotime().
	
	$sStartDate = gmdate("Y-m-d", strtotime($sStartDate));
	$sEndDate = gmdate("Y-m-d", strtotime($sEndDate));
	
	// Start the variable off with the start date
	$aDays[] = $sStartDate;
	
	// Set a 'temp' variable, sCurrentDate, with
	// the start date - before beginning the loop
	$sCurrentDate = $sStartDate;
	
	// While the current date is less than the end date
	while ($sCurrentDate < $sEndDate) {
		// Add a day to the current date
		$sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));
		
		// Add this new day to the aDays array
		$aDays[] = $sCurrentDate;
	}
	
	// Once the loop has finished, return the
	// array of days.
	return $aDays;
}

/******************************************************************************* 
** sptViewsForPeriod
** Return all the views within the given period as an array
** @since 1.1
*******************************************************************************/
function sptViewsForPeriod($splitTestID, $pageID, $startDate, $endDate) {
	
	$sptData = unserialize(get_post_meta($splitTestID, 'sptData', true));
	
	if (!$sptData)
		return;
	
	$viewsForPeriod = array();

	if (!empty($sptData) && !empty($sptData[$pageID . '_visits'])) {
		foreach ($sptData[$pageID . '_visits'] as $view) {
			if ($view['timestamp'] > $startDate && 
				$view['timestamp'] <= $endDate) {
			
				// View was within date parameters
				$viewsForPeriod[] = $view;
			}
		}
	}
	
	usort($viewsForPeriod, 'sptStatsSortFunction'); 
	return $viewsForPeriod;
}

/******************************************************************************* 
** sptStatsSortFunction
** Return all the clicks so far today as an array
** @since 1.1
*******************************************************************************/
function sptStatsSortFunction($dateA, $dateB) { 
	if ($dateA['timestamp'] == $dateB['timestamp']) 
		return 0; 
	else 
		return ($dateA['timestamp'] < $dateB['timestamp']) ? -1 : 1; 
}

/******************************************************************************* 
** sptClicksSoFarToday
** Return all the clicks so far today as an array
** @since 1.1
*******************************************************************************/
function sptClicksSoFarToday($link_id) {
	$ct = current_time('timestamp', 0);
	$startoftoday  = mktime(00, 00, 00, date("m", $ct), date("d", $ct), date("Y", $ct));
	
	return sptClicksForPeriod($link_id, 
		$startoftoday,
		$ct
	);
}

/******************************************************************************* 
** sptClicksSoFarThisWeek
** Return all the clicks so far this week as an array
** @since 1.1
*******************************************************************************/
function sptClicksSoFarThisWeek($link_id) {
	$ct = current_time('timestamp', 0);
	$startofthisweek  = mktime(23, 59, 59, date("m", $ct)  , date("d", $ct) - date('N', $ct), date("Y", $ct));
	$endofthisweek  = mktime(23, 59, 59, date("m", $ct)  , date("d", $ct) + (7 - date('N', $ct)), date("Y", $ct));
	
	return sptClicksForPeriod($link_id, 
		$startofthisweek,
		$endofthisweek
	);
}

/******************************************************************************* 
** sptClicksLastWeek
** Return all the clicks last week as an array
** @since 1.1
*******************************************************************************/
function sptClicksLastWeek($link_id) {
	$ct = current_time('timestamp', 0);
	$startoflastweek  = mktime(23, 59, 59, date("m", $ct)  , date("d", $ct) - (7 + date('N', $ct)), date("Y", $ct));
	$endoflastweek  = mktime(23, 59, 59, date("m", $ct)  , date("d", $ct) - (date('N', $ct)), date("Y", $ct));
	
	return sptClicksForPeriod($link_id, 
		$startoflastweek,
		$endoflastweek
	);
	
}

/******************************************************************************* 
** sptClicksSoFarThisMonth
** Return all the clicks so far this month as an array
** @since 1.1
*******************************************************************************/
function sptClicksSoFarThisMonth($link_id) {
	$ct = current_time('timestamp', 0);
	$startofthismonth  = mktime(00, 00, 00, date("m", $ct), 1, date("Y", $ct));
	$endofthismonth  = mktime(23, 59, 59, date("m", $ct), date("t", $ct), date("Y", $ct));
	
	return sptClicksForPeriod($link_id, 
		$startofthismonth,
		$endofthismonth
	);
	
}

/******************************************************************************* 
** sptClicksLastMonth
** Return all the clicks last month as an array
** @since 1.1
*******************************************************************************/
function sptClicksLastMonth($link_id) {
	$ct = current_time('timestamp', 0);
	$startoflastmonth  = mktime(00, 00, 00, date('m', $ct) - 1, 1, date("Y", $ct));
	$endoflastmonth  = mktime(23, 59, 59, date('m', $ct), 0, date("Y", $ct));
	
	return sptClicksForPeriod($link_id, 
		$startoflastmonth,
		$endoflastmonth
	);
	
}

/******************************************************************************* 
** sptClicksAllTime
** Return all the clicks, ever, on this link as an array
** @since 1.1
*******************************************************************************/
function sptClicksAllTime($link_id) {
	$linkData = unserialize(get_post_meta($link_id, 'sptData', true));
	return (!empty($linkData['linkclicks']) ? $linkData['linkclicks'] : 0);
}

/******************************************************************************* 
** sptResetStatsForLink
** Reset the stats on a particular link
** @since 1.1
*******************************************************************************/
function sptResetStatsForLink($link_id) {
	$linkData = unserialize(get_post_meta($link_id, 'sptData', true));
	$linkData['linkclicks'] = array();
	
	update_post_meta($link_id, 'sptData', serialize($linkData));
}

/******************************************************************************* 
** sptResetStatsForAllLinks
** Reset the stats for all links
** @since 1.1
*******************************************************************************/
function sptResetStatsForAllVariations($sptID) {
	
	$sptData = unserialize(get_post_meta($sptID, 'sptData', true));
	$sptData[$sptData['master_id'] . '_visits'] = array();
	$sptData[$sptData['slave_id'] . '_visits'] = array();
	$sptData[$sptData['master_id'] . '_conversions'] = array();
	$sptData[$sptData['slave_id'] . '_conversions'] = array();
		
	update_post_meta($sptID, 'sptData', serialize($sptData));

}

/******************************************************************************* 
** sptAjaxGetChartData
** Ajax wrapper function for sptGetChartData
** @since 1.1
*******************************************************************************/
function sptAjaxGetChartData() {
	$splitTestID = (!empty($_POST['splitTestID']) ? $_POST['splitTestID'] : '');
	$pageID = (!empty($_POST['pageID']) ? $_POST['pageID'] : '');
	$fromDate = (!empty($_POST['fromDate']) ? $_POST['fromDate'] : '');
	$toDate = (!empty($_POST['toDate']) ? $_POST['toDate'] : '');
	
	echo json_encode(sptGetChartData($splitTestID, $pageID, $fromDate, $toDate));
	
	die();
}

/******************************************************************************* 
** sptAjaxResetStats
** Ajax wrapper function for resetting the stats
** @since 1.1
*******************************************************************************/
function sptAjaxResetStats() {
	$sptID = (!empty($_POST['sptID']) ? $_POST['sptID'] : '');
	
	if (empty($sptID))
		die();
	
	sptResetStatsForAllLinks($sptID);
	
	die();
}

/* Register ajax calls */
add_action('wp_ajax_sptAjaxGetChartData', 'sptAjaxGetChartData');
add_action('wp_ajax_sptAjaxResetStats', 'sptAjaxResetStats');

?>
