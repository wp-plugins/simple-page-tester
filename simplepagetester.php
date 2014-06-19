<?php
/*
* Plugin Name: Simple Page Tester
*
* Description: Simple Page Tester is a plugin for doing simple split testing between variations of pages.
*
* Author: Simple Page Tester
* Author URI: http://www.simplepagetester.com
* Plugin URI: http://simplepagetester.com
* Version: 1.2.2
*/

/*******************************************************************************
** sptRegisterPostType
** Register the post type
** @since 1.0
*******************************************************************************/
function sptRegisterPostType() {
	register_post_type(
		'spt',
		array(
			'labels' => array(
				'name' => __('Split Tests'),
				'singular_name' => __('Split Test'),
				'add_new_item' => __('Add New Split Test'),
				'edit_item' => __('Edit Split Test'),
				'view_item' => __('View Split Test'),
				'search_items' =>  __('Search Split Tests'),
				'not_found' => __('No Split Tests are currently running!'),
				'not_found_in_trash' => __('No Split Tests found in trash'),
				'menu_name' => __('Split Tests'),
				'all_items' => __('All Split Tests')
			),
			'description' => 'Simple Page Tester - WordPress page split testing',
			'public' => true,
			'menu_position' => 20,
			'hierarchical' => true,
			'supports' => array(
				'title' => false,
				'editor' => false,
				'author' => false,
				'thumbnail' => false,
				'excerpt' => false,
				'trackbacks' => false,
				'comments' => false,
				'revisions' => false,
				'page-attributes' => false,
				'post-formats' => false
			),
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'can_export' => true,
			'rewrite' => true,
			'menu_icon' => plugins_url('simple-page-tester/images/icon-spt.png')
		)
	);

	if (get_option('spt_flush') == 'true') {
        flush_rewrite_rules();
        delete_option('spt_flush');
    }
}

/*******************************************************************************
** sptMetaBoxes
** Register the various meta boxes used throughout the admin
** @since 1.0
*******************************************************************************/
function sptMetaBoxes() {
	/* Add SPT meta boxes */

	add_meta_box(
		'spt-name-meta',
		'Split Test Name',
		'sptNameMeta',
		'spt',
		'normal',
		'high'
	);

	add_meta_box(
		'spt-details-meta',
		'Split Test Details',
		'sptDetailsMeta',
		'spt',
		'normal',
		'high'
	);

	/* Save boxes on SPT type */

	remove_meta_box( 'submitdiv', 'spt', 'side' );

	add_meta_box(
		'spt-save-link-side-meta',
		'Save Split Test',
		'sptSaveBoxMeta',
		'spt',
		'side',
		'high'
	);

	add_meta_box(
		'spt-save-link-bottom-meta',
		'Save Split Test',
		'sptSaveBoxMeta',
		'spt',
		'normal',
		'low'
	);

	add_meta_box(
		'spt-side-options-meta',
		'Split Test Options',
		'sptSideOptionsMeta',
		'spt',
		'side',
		'low'
	);

	/* Premium upsell */
	add_meta_box(
		'spt-side-premium-upsell-meta',
		'Split Test Analysis',
		'sptSidePremiumUpsellMetaBox',
		'spt',
		'side',
		'low'
	);

	/* Add new split test box to pages */

	add_meta_box(
		'spt-page-sidebar-meta',
		'Split Test',
		'sptPageSidebarBoxMeta',
		'page',
		'side',
		'low'
	);

	add_meta_box(
		'spt-page-sidebar-meta',
		'Split Test',
		'sptPageSidebarBoxMeta',
		'post',
		'side',
		'low'
	);
}

/*******************************************************************************
** sptSidePremiumUpsellMetaBox
** Setup upsell meta box
** @since 1.0
*******************************************************************************/
function sptSidePremiumUpsellMetaBox() {
	wp_nonce_field( plugin_basename(__FILE__), 'spt_noncename' );
	global $post;

	/* Make sure we only do this for regular saves and we have permission */
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		!current_user_can( 'edit_page', $post->ID ) ) {
		return $post->ID;
	}

	echo '<a href="http://simplepagetester.com/premium/?utm_source=Free%20Plugin&utm_medium=Sidebar&utm_campaign=Upgrade%20To%20Premium" target="_blank"><img id="sptPremiumUpsell" src="' . plugins_url('simple-page-tester/images/premium.jpg') . '" alt="SPT Premium Version" /></a>';
}

/*******************************************************************************
** sptNameMeta
** Show the name meta box on the spt edit screen
** @since 1.0
*******************************************************************************/
function sptNameMeta() {
	wp_nonce_field( plugin_basename(__FILE__), 'spt_noncename' );

	/* Make sure we only do this for regular saves and we have permission */
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		!current_user_can( 'edit_page', $post->ID ) ) {
		return $post->ID;
	}

	global $post;
	$sptData = unserialize(get_post_meta($post->ID, 'sptData', true));
	echo '<p><label class="infolabel" for="post_title">Split Test Name:</label></p>';
	echo '<p><input id="test_name" name="post_title" value="' . $post->post_title . '" size="50" type="text" /><br />
	<em>This is a convenience name so you can recall what your split test is about later.</em></p>';
}

/*******************************************************************************
** sptDetailsMeta
** Show main details meta box on the spt edit screen
** @since 1.0
*******************************************************************************/
function sptDetailsMeta() {
	global $post;
	wp_nonce_field( plugin_basename(__FILE__), 'spt_noncename' );

	/* Make sure we only do this for regular saves and we have permission */
	if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		!current_user_can( 'edit_page', $post->ID ) ) {
		return $post->ID;
	}
	$sptData = array();
	$sptData = unserialize(get_post_meta($post->ID, 'sptData', true));

	// Check the split test type
	if (isset($sptData['splitTestType']) && !empty($sptData['splitTestType']) && $sptData['splitTestType'] != 'page') {
		do_action('spt_alternate_split_test_type_details');
		return;
	}

	$masterPost = get_post($sptData['master_id']);
	$slavePost = get_post($sptData['slave_id']);

	echo '
	<div class="sptVariationContainer">
		<table id="sptMaster" width="100%">
			<tr>
				<td colspan="3"><div id="sptChart" style="height: 350px;" class="sptGoogleChart"><img id="sptChartLoader" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" /></div></td>
			</tr>
		</table>
	</div>

	<div class="sptVariationContainer">
		<table id="sptMaster" width="100%">
			<tr>
				<th colspan="3"><h3>Master</h3></th>
			</tr>
				<th scope="row">Page Name:</th>
				<td>' . $masterPost->post_title . '</td>
				<td style="text-align: right;">
					<a class="button" href="' . admin_url('/post.php?post=' . $masterPost->ID . '&action=edit') . '">Edit Page</a> <a class="button" href="' . get_permalink($masterPost->ID) . '">View Page</a>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Page Slug:</th>
				<td colspan="1">/' . $masterPost->post_name . '</td>
				<td colspan="1" style="text-align: right;">
					<div class="sptDeclareButtonContainer">
						<a id="' . $masterPost->ID . '" class="button-primary spt_declare">Declare Winner</a><br /><img class="winner_loader" style="display: none;" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" />
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Adjust Percentage Of Views:</th>
				<td colspan="2">
					<select name="sptData[master_weight]" id="master_weight">';

	for ($i = 90; $i > 0; $i = $i - 10) {
		echo '<option value="' . $i . '"' .
		($sptData['master_weight'] == $i ? ' selected="selected"' : '') .
		'>' . $i . '</option>';
	}
	echo '			</select> %
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Total Unique Visits:</th>
				<td colspan="2">' . count($sptData[$sptData['master_id'] . '_visits']) . '</td>
			</tr>';

	do_action('spt_master_table_content_end', $post->ID, $sptData['master_id']);

	echo '
		</table>
	</div><!-- /.sptVariationContainer -->

	<div class="sptVariationContainer">
		<table id="sptVariation" width="100%">
			<tr>
				<th colspan="3"><h3>Variation</h3></th>
			</tr>
				<th scope="row">Page Name:</th>
				<td>' . $slavePost->post_title . '</td>
				<td style="text-align: right;">
					<a class="button" href="' . admin_url('/post.php?post=' . $slavePost->ID . '&action=edit') . '">Edit Page</a> <a class="button" href="' . get_permalink($slavePost->ID) . '">View Page</a>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Page Slug:</th>
				<td colspan="1">/' . $slavePost->post_name . '</td>
				<td colspan="1" style="text-align: right;">
					<div class="sptDeclareButtonContainer">
						<a id="' . $slavePost->ID . '" class="button-primary spt_declare">Declare Winner</a><br /><img class="winner_loader" style="display: none;" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" />
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Adjust Percentage Of Views:</th>
				<td colspan="2">
					<select name="sptData[slave_weight]" id="slave_weight">';

	for ($i = 90; $i > 0; $i = $i - 10) {
		echo '<option value="' . $i . '"' .
		($sptData['slave_weight'] == $i ? ' selected="selected"' : '') .
		'>' . $i . '</option>';
	}

	echo '			</select> %
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="1">Total Unique Visits:</th>
				<td colspan="2">' . count($sptData[$sptData['slave_id'] . '_visits']) . '</td>
			</tr>';

	do_action('spt_variation_table_content_end', $post->ID, $sptData['slave_id']);

	echo '
		</table>
	</div><!-- /.sptVariationContainer -->';

	// Sort out the Google Chart for the Master and Variation

	echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	google.load("visualization", "1", {packages:["corechart"]});
	// Global container for the returned JSON data for the charts so we don\'t have to
	// query the DB again
	var sptChartJSON = [];

	jQuery(document).ready(function() {
		sptLoadChart();

		// Take care of resizing the chart when the window is resized dynamically
		jQuery(window).resize(function() {
			jQuery("#sptMasterChart").html("");
			sptDrawChart(sptChartJSON, "sptChart");
		});
	});

	function sptLoadChart() {
		jQuery("#sptChart").html("<img id=\"sptChartLoader\" src=\"' . plugins_url('simple-page-tester/images/spt-loader.gif') . '\" />");
		jQuery.post(
			ajaxurl,
			{
				action: "sptAjaxGetChartData",
				splitTestID: ' . $post->ID . '
			},
			function(results) {
				var jsonResults = jQuery.parseJSON(results);
				if (jsonResults == null)
					jsonResults = Array();

				// Set global JSON storage
				sptChartJSON = jsonResults;

				sptDrawChart(sptChartJSON, "sptChart");
				jQuery("#sptChartLoader").hide();
			}
		);
	}
	</script>';

}

/*******************************************************************************
** sptSaveBoxMeta
** Show the save button meta box
** @since 1.0
*******************************************************************************/
function sptSaveBoxMeta() {
	global $post;

	echo '<input name="original_publish" type="hidden" id="original_publish" value="Save" />';
	echo '<input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="Save Split Test">';

	if (current_user_can("delete_post", $post->ID)) {
		echo '&nbsp;&nbsp;<a class="submitdelete deletion" href="' . get_delete_post_link($post->ID) . '">Cancel Split Test</a>';
	}
}

/*******************************************************************************
** sptSideOptionsMeta
** Show the options meta box in the sidebar
** @since 1.1
*******************************************************************************/
function sptSideOptionsMeta() {
	global $post;

	$sptData = unserialize(get_post_meta($post->ID, 'sptData', true));

	if (isset($sptData['force_same']) && $sptData['force_same'] == 'on') $sptData['force_same'] = ' checked="checked"';

	// Core options
	echo '<input type="checkbox" name="sptData[force_same]" id="sptForceSame"' .  $sptData['force_same'] . ' /> <label for="sptForceSame">Force Users To View The Same Variation During A Browsing Session</label>';

	// Hook so others can add their own options
	do_action('spt_after_side_options');

}

/*******************************************************************************
** sptPageSidebarBoxMeta
** Show the appropriate sidebar box depending on if this page is in a split test
** or not
** @since 1.0
*******************************************************************************/
function sptPageSidebarBoxMeta() {
	wp_nonce_field( plugin_basename(__FILE__), 'spt_noncename' );
	global $post;

	if ($post->post_status != 'publish') {
		echo '<p>Looks like this page hasn\'t been published yet.
		The page must be published in order to run a split test.</p>';
		return;
	}

	$isBeingTested = false;
	$isMaster = false;
	$isSlave = false;

	$sptID = 0;
	$sptData = array();

	$sptID = get_post_meta($post->ID, 'sptID', true);
	if ($sptID != null && !empty($sptID) && is_numeric($sptID) && $sptID != '0') {
		$sptData = unserialize(get_post_meta($sptID, 'sptData', true));

		if ($sptData['master_id'] == $post->ID) $isMaster = true;
		if ($sptData['slave_id'] == $post->ID) $isSlave = true;

		if ($isMaster || $isSlave)
			$isBeingTested = true;
	}

	if ($isBeingTested) {
		if ($isMaster) {
			echo '<p>This page is the master page in a split test.</p>
			<p><a href="' . admin_url('post.php?post=' . $sptID . '&action=edit') .
			'" class="button">View Split Test Details</a></p>';
			echo '<p><a id="' . $sptData['master_id'] . '" class="button-primary spt_declare">Declare Winner</a><img class="winner_loader" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" /></p>';
			return;
		} else if ($isSlave) {
			echo '<p>This page is the secondary page in a split test.</p>
			<p><a href="' . admin_url('post.php?post=' . $sptID . '&action=edit') .
			'" class="button">View Split Test Details</a></p>';
			echo '<p><a id="' . $sptData['slave_id'] . '" class="button-primary spt_declare">Declare Winner</a><img class="winner_loader" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" /></p>';
			return;
		}
	}

	echo '<p>To use this page as the master page in a split test simply click the setup button below:</p>';
	echo '<p><span title="Simple Page Tester: New split test" id="sptSetupSplitTest" class="button-secondary">Setup New Split Test</span></p>';

	echo '<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#sptSetupSplitTest").click(function() {
			tb_show("Setup New Split Test", ajaxurl + "?action=sptGetThickboxContent&post_id=' . $post->ID . '&height=640&width=640&TB_iframe=true");
		});
	});
	</script>';
}

/*******************************************************************************
** sptSavePost
** Save the spt post and all the relevant meta info
** @since 1.0
*******************************************************************************/
function sptSavePost($post_id) {
	if (empty($_POST['post_type']) ||
		!empty($_POST['post_type']) && $_POST['post_type'] != 'spt') {
		return $post_id;
	}

	if (!wp_verify_nonce( $_POST['spt_noncename'], plugin_basename(__FILE__) ) ||
		(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
		!current_user_can( 'edit_page', $post_id ) ) {
		return $post_id;
	}

	$sptDataOrig = array();
	$sptDataOrig = unserialize(get_post_meta($post_id, 'sptData', true));

	$sptDataNew = array();
	$sptDataNew = sptFilterData($_POST['sptData']);

	$sptData = $sptDataNew;

	$sptData['master_id'] = $sptDataOrig['master_id'];
	$sptData['slave_id'] = $sptDataOrig['slave_id'];

	$sptData[$sptDataOrig['master_id'] . '_visits'] = $sptDataOrig[$sptDataOrig['master_id'] . '_visits'];
	$sptData[$sptDataOrig['slave_id'] . '_visits'] = $sptDataOrig[$sptDataOrig['slave_id'] . '_visits'];

	// 1.1.2 (jkohlbach) - adding after save hook
	$sptData = apply_filters('spt_after_data_save', $sptData, $sptDataOrig);

	/* Update the link data */
	update_post_meta($post_id, 'sptData', serialize($sptData));

}

/*******************************************************************************
** sptRecordVisit
** Record the visit to the page if required. Uses global post data to determine
** the page to record.
** @param $sptID - the ID of the split test
** @param $sptData - the data of the split test
** @since 1.1.3
*******************************************************************************/
function sptRecordVisit($sptID, $sptData) {
	session_start();
	global $post;

	// If in doubt, record the visit, next we'll test some conditions where it shouldn't recorded
	$recordTheVisit = true;

	// TODO: Detect if logged in users should be tracked or not
	/*if (global option says not to record logged in users and user is logged in) {
		$recordTheVisit = false;
	}*/

	// Check if we should record the visit if we're forcing visitors to view the same page
	if (isset($sptData['force_same']) && $sptData['force_same'] == 'on' &&
		isset($_SESSION['spt_force_same_' . $sptID]) && !empty($_SESSION['spt_force_same_' . $sptID])) {
		$recordTheVisit = false;
	}

	// Record the visit, but only if all preconditions are satisfied
	if ($recordTheVisit) {

		if ($sptData['master_id'] == $post->ID) $masterOrSlave = $sptData['master_id'] . '_visits';
		if ($sptData['slave_id'] == $post->ID) $masterOrSlave = $sptData['slave_id'] . '_visits';

		$sptData[$masterOrSlave][] = array(
			'timestamp' => current_time('timestamp'),
			'ip' => getenv(REMOTE_ADDR)
		);

		// If we are forcing users to view the same page, record the page so we know
		// not to record future visits
		if (isset($sptData['force_same']) && $sptData['force_same'] == 'on') {
			$_SESSION['spt_force_same_' . $sptID] = $post->ID;
		}

		update_post_meta($sptID, 'sptData', serialize($sptData));
	}
}

/*******************************************************************************
** sptRedirect
** Redirect the page for the given split amount for the test to the page
** @since 1.0
*******************************************************************************/
function sptRedirect() {
	session_start();
	global $post;

	// Check if this is the first redirect and if so record the visit
	if (isset($_SESSION['spt_redirect']) && $_SESSION['spt_redirect'] == true) {
		// Unset the session variable because we might end up visiting again
		unset($_SESSION['spt_redirect']);

		// Get the split test ID and exit if it's not valid
		$sptID = get_post_meta($post->ID, 'sptID', true);
		if ($sptID == null || empty($sptID) || !is_numeric($sptID) || $sptID == '0')
			return;

		// Retrieve the split test data and exit if it's not a valid test
		$sptData = unserialize(get_post_meta($sptID, 'sptData', true));
		if ($sptData == null || empty($sptData))
			return;

		sptRecordVisit($sptID, $sptData);

		do_action('spt_after_redirect', $sptID);

		// Exit here so we don't end up redirecting again
		return;
	} else {
		// Get the split test ID and exit if it's not valid
		$sptID = get_post_meta($post->ID, 'sptID', true);
		if ($sptID == null || empty($sptID) || !is_numeric($sptID) || $sptID == '0')
			return;

		// Retrieve the split test data and exit if it's not a valid test
		$sptData = unserialize(get_post_meta($sptID, 'sptData', true));
		if ($sptData == null || empty($sptData))
			return;

		// Determine which page to redirect to
		list($usec, $sec) = explode(' ', microtime());
		mt_srand((float)$sec + ((float)$usec * 100000));
		$randNum = mt_rand(1, 100);

		if ($randNum <= $sptData['master_weight'])
			$pageID = $sptData['master_id'];
		else
			$pageID = $sptData['slave_id'];

		// Set the session variable to indicate that they user is being redirected
		// so we don't end up redirecting them in an infinite loop!
		$_SESSION['spt_redirect'] = true;

		// Set the session variable to record which variation this user saw
		if (!isset($_SESSION['spt_page' . $sptID])) {
			$_SESSION['spt_page' . $sptID] = $pageID;
		} else {
			// Check if the global setting for forcing user to see the same variation again is off
			if (isset($sptData['force_same']) && $sptData['force_same'] == 'on') {
				$pageID = $_SESSION['spt_page' . $sptID];
			}
		}

		// Check if we should bother redirecting, if we're forcing visitors to view the same page there is no need
		if (isset($sptData['force_same']) && $sptData['force_same'] == 'on' &&
			isset($_SESSION['spt_force_same_' . $sptID]) && !empty($_SESSION['spt_force_same_' . $sptID])) {
			sptRecordVisit($sptID, $sptData);

			// 1.2.1: Add hook for after action has been taken (in this case, no redirect)
			do_action('spt_after_force_same_no_redirect', $sptID);
			return;
		}

		// Get the URL for the selected landing page
		$redirectURL = get_permalink($pageID);

		// Get query vars from the current URL and pass them to the selected landing page
		$redirectURL = add_query_arg($_GET, $redirectURL);

		// Perform the redirect to the desired split page
		wp_redirect($redirectURL, 302);

		// Stop any PHP from executing after the redirect
		exit();
	}
}

/*******************************************************************************
** sptTestDeleted
** Some housekeeping once tests are deleted
** @since 1.0
*******************************************************************************/
function sptTestDeleted() {
	global $post;

	if ($post && $post->post_type == 'spt') {
		$sptData = unserialize(get_post_meta($post->ID, 'sptData', true));

		// Delete the key for this test from the pages that were being tested
		delete_post_meta($sptData['master_id'], 'sptID');
		delete_post_meta($sptData['slave_id'], 'sptID');

		wp_delete_post($post->ID, true);
	}
}

/*******************************************************************************
** sptRemoveItemsFromEditList
** Remove the view etc buttons from the edit list
** @since 1.0
*******************************************************************************/
function sptRemoveItemsFromEditList($actions, $post) {
	global $current_screen;

	if ($post->post_type != 'spt')
		return $actions;

	unset($actions['view']);
	unset($actions['inline hide-if-no-js']);

	return $actions;
}

/*******************************************************************************
** sptHideAddNewFromEditPage
** Remove the add new edit page
** @since 1.0
*******************************************************************************/
function sptHideAddNewFromEditPage() {
	global $current_screen;

    if ($current_screen && $current_screen->post_type == 'spt') {
    	echo '<style type="text/css">
    	#favorite-actions {display:none;}
    	.add-new-h2{display:none;}
    	.tablenav{display:none;}
    	</style>';
    }

    // Now also remove split test from the admin bar
    echo '<style type="text/css">

    </style>';
}

/*******************************************************************************
** sptHideAddNewFromAdminBar
** Remove the add new from admin bar
** @since 1.0
*******************************************************************************/
function sptHideAddNewFromAdminBar() {
    global $wp_admin_bar;

    // Remove New SPT from the admin menu bar
    $wp_admin_bar->remove_menu('new-spt');
}

/*******************************************************************************
** sptRemoveAddNew
** Remove the add new from admin list page
** @since 1.0
*******************************************************************************/
function sptRemoveAddNew() {
    global $submenu;
    unset($submenu['edit.php?post_type=spt'][10]); // Removes 'Add New'.
}

/*******************************************************************************
** sptAdminHeader
** Include stuff in the admin dashboard header
** @since 1.0
*******************************************************************************/
function sptAdminHeader() {
	global $post;

	if ($post)
		$sptID = get_post_meta($post->ID, 'sptID', true);

	if (($post && $post->post_type == 'spt')  ||
		(!empty($sptID) && is_numeric($sptID) && $sptID != '0')) {
		wp_enqueue_script('jquery-ui');
		wp_enqueue_script('sptHelper', plugins_url('simple-page-tester/js/sptHelper.js'), true);
		wp_enqueue_style('sptStylesheet', plugins_url('simple-page-tester/css/spt.css'));

		echo '<script type="text/javascript">
		var sptPluginDir = \'' . plugins_url('simple-page-tester/') . '\';
		var sptAdminUrl = \'' . admin_url() . '\';
		</script>';
	}

	wp_enqueue_script('thickbox', true);
	wp_enqueue_style('thickbox');
}

/*******************************************************************************
** sptSetupCanonicalTag
** if secondary page, add the canonical tag
** @since 1.0.2
*******************************************************************************/
function sptSetupCanonicalTag() {
	global $post;

	$sptID = get_post_meta($post->ID, 'sptID', true);

	if ($sptID == null || empty($sptID) || !is_numeric($sptID) || $sptID == '0')
		return;

	$sptData = unserialize(get_post_meta($sptID, 'sptData', true));
	if ($sptData == null || empty($sptData))
		return;

	if ($sptData['slave_id'] == $post->ID)
		echo '<link rel="canonical" href="' . get_permalink($sptData['master_id']) . '" />';
}

/*******************************************************************************
** sptGetThickboxContent
** Get the link picker thickbox content
** @since 1.1
*******************************************************************************/
function sptGetThickboxContent() {
	?>

<html>
<head>

<title>Simple Page Tester: New split test</title>

<?php
wp_enqueue_style( 'colors' );
wp_enqueue_style( 'ie' );
wp_enqueue_style('buttons');
wp_enqueue_script('utils');
wp_enqueue_script('editor');
wp_enqueue_style( 'sptStylesheet', plugins_url('simple-page-tester/css/spt.css'));

do_action('admin_print_styles');
do_action('admin_print_scripts');
do_action('admin_head');

?>

<script type="text/javascript" src="<?php echo plugins_url('simple-page-tester/'); ?>js/sptSetupSplitTest.js"></script>

</head>
<body>
	<div class="spt_logo_center">
		<img id="spt_logo" src="<?php echo plugins_url('simple-page-tester/images/spt-logo.png'); ?>" alt="Simple Page Tester" />
	</div>
	<div id="spt_container" class="wp-core-ui">
		<script type="text/javascript">
			/* Pass any required info like the post id */
			var post_id = <?php echo sptFilterData($_GET['post_id']); ?>;
			var sptPluginDir = "<?php echo plugins_url('simple-page-tester/'); ?>";
			var sptAdminUrl = "<?php echo admin_url(); ?>";
			var sptAjaxUrl = "<?php echo admin_url(); ?>admin-ajax.php";
		</script>

		<div id="options_1" class="spt_center">
			<h2>Creating A New Split Test</h2>
			<p>You've chosen the <em><u>Master</u></em> page,
				now we need figure out what to do for the <em><u>Variation</u></em>.</p>
			<p>There's three options, what would you like to do?</p>

			<p style="margin-top: 20px;"><a id="duplicate_page" class="button" href="#">Duplicate Master Page</a>&nbsp;&nbsp;
			<a id="choose_existing" class="button" href="#">Choose An Existing Page</a>&nbsp;&nbsp;
			<a id="create_blank" class="button" href="#">Create A New Blank Page</a></p>

		</div>

		<div id="options_duplicate_page" class="spt_center" style="display: none;">
			<p class="action">Duplicating current page...</p>
			<p class="action">Setting up new split test...</p>
			<p class="action">Redirecting...</p>
			<p class="back_button"><span class="spt_back button">&laquo; Back</span></p>
		</div>

		<div id="options_choose_existing" class="spt_center" style="display: none;">
			<h2>Choose An Existing Page</h2>
			<div id="spt_search_panel">
				<div class="spt_search_wrapper">
					<label>
						<span style="width: 20%;">Search By Name:&nbsp;&nbsp;&nbsp;</span>
						<input type="text" id="spt_search_field" autocomplete="off" value="" />
					</label>
				</div>
				<div id="spt_search_results">
					<ul><li>No search term specified.</li></ul>
				</div>
			</div>

			<p class="action">Setting up new split test...</p>
			<p class="action">Redirecting...</p>

			<p class="back_button"><span class="spt_back button">&laquo; Back</span></p>
		</div>

		<div id="options_create_blank" class="spt_center" style="display: none;">

			<p class="action">Creating new blank page...</p>
			<p class="action">Setting up new split test...</p>
			<p class="action">Redirecting...</p>
			<p class="back_button"><span class="spt_back button">&laquo; Back</span></p>
		</div>

		<div id="sptBackupNotice">
			<p>We suggest backing up your website prior to starting a split test. For more information on why, please see <a href="http://simplepagetester.com/articles/backup-your-site-before-split-test/?utm_source=Free%20Plugin&utm_medium=New%20Test%20Dialog&utm_campaign=Backup%20Article" target="_blank">this article on our website</a>.
		</div>

	</div>
</body>
</html>
<?php
	exit();
}

/*******************************************************************************
** sptDuplicateCurrentPost
** Ajax function to include duplication functionality
** @since 1.0.3
*******************************************************************************/
function sptDuplicateCurrentPost() {
	include('DuplicateCurrentPost.php');
	exit();
}

/*******************************************************************************
** sptCreateNewPage
** Ajax function to include new page creation functionality
** @since 1.0.3
*******************************************************************************/
function sptCreateNewPage() {
	include('CreateNewPage.php');
	exit();
}

/*******************************************************************************
** sptReplaceSearchResults
** Ajax function to include search results functionality
** @since 1.0.3
*******************************************************************************/
function sptReplaceSearchResults() {
	include('ExistingSearch.php');
	exit();
}

/*******************************************************************************
** sptCreateSptPost
** Ajax function to include Split Test post creation functionality
** @since 1.0.3
*******************************************************************************/
function sptCreateSptPost() {
	include('CreateSptPost.php');
	exit();
}

/*******************************************************************************
** sptDeclareWinner
** Ajax function to include delaration functionality
** @since 1.0.3
*******************************************************************************/
function sptDeclareWinner() {
	include('DeclareWinner.php');
	exit();
}

/*******************************************************************************
** sptFilterData
** Filter all the data for nasty surprises
** @since 1.1.1
*******************************************************************************/
function sptFilterData($data) {
	if (is_array($data)) {
		foreach ($data as $key => $elem) {
			$data[$key] = sptFilterData($elem);
		}
	} else {
		if (empty($data))
			return $data;

		$data = nl2br(trim(htmlspecialchars(wp_kses_post($data), ENT_COMPAT)));
		$breaks = array("\r\n", "\n", "\r");
		$data = str_replace($breaks, "", $data);

		if (get_magic_quotes_gpc())
			$data = stripslashes($data);
		$data = esc_sql($data);
	}
    return $data;
}

/*******************************************************************************
** sptSetupAddShortcodeTestPage
** Add SPT premium settings page to settings menu
** @since 1.2
*******************************************************************************/
function sptSetupAddNewMenuItems() {
	add_submenu_page(
		'edit.php?post_type=spt',
		'Add Page Test',
		'Add Page Test',
		'manage_options',
		'sptNewPageSplitTest',
		'sptNewPageSplitTest'
	);
}

/*******************************************************************************
** sptNewPageSplitTest
** Add new page test screen
** @since 1.2
*******************************************************************************/
function sptNewPageSplitTest() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have suffifient permissions to access this page.') );
	}

	echo '<div class="wrap">';

	echo '<h2>Page Split Test Setup</h2>';

	echo '<p>1. To setup a new page split test, you need to navigate to the edit
	screen for your master page and click on the Setup Split Test button which you will
	find on the bottom right of the page.</p>';

	echo '<img src="' . plugins_url('simple-page-tester/images/add-new-1.jpg') . '" alt="Setup Split Test" />';

	echo '<p>2. Once you have clicked the button you will be presented with a dialog box
	to determine what to do for the Variation. Choose the option you would like and
	Simple Page Tester will add the Variation for the test.</p>';

	echo '<img src="' . plugins_url('simple-page-tester/images/add-new-2.jpg') . '" alt="Variation Dialog Box" />';

	echo '<p>3. After you have picked your Variation option and the Variation has been added
	to the test you need to edit the Variation. Note that the test is now active.</p>';

	echo '<p>Goto the listing screen for the post type you wish to test: <br /><br />';
	echo '<a href="' . admin_url('edit.php?post_type=page') . '" class="button-primary">Goto Pages Listing &rarr;</a>&nbsp;';
	echo '<a href="' . admin_url('edit.php?post_type=post') . '" class="button-primary">Goto Posts Listing &rarr;</a>&nbsp;';

	do_action('spt_after_new_page_test_description');

	echo '</div><!-- .wrap -->';
}

/*******************************************************************************
** sptAddStatsResetButtons
** Present a button to reset the stats on the options sidebar meta
** @since 1.2
*******************************************************************************/
function sptAddStatsResetButtons() {
	echo '<p>Reset the test statistics:<br />
	<input type="button" class="button-secondary" id="sptResetAllStats" value="Reset All Stats" />
	<img id="sptResetLoader" style="display: none;" src="' . plugins_url('simple-page-tester/images/spt-loader.gif') . '" /><br />
	<em>Note: this can\'t be undone.</em></p>';
}

/*******************************************************************************
** sptAddCustomColumns
** Add custom columns to the list page (type, stats, etc)
** @since 1.2
*******************************************************************************/
function sptAddCustomColumns($columns) {
	echo '<pre>DEBUG: ' . print_r($columns, true) . '</pre>';
    unset($columns['date']);
    $columns['title'] = 'Test Name';
    $columns['sptTestType'] = 'Test Type';
    $columns['sptTestStats'] = 'Statistics';
    $columns['date'] = 'Created Date';
    return $columns;
}

/*******************************************************************************
** sptAddCustomColumnsContent
** Add custom columns content to the list page (type, stats, etc)
** @since 1.2
*******************************************************************************/
function sptAddCustomColumnsContent($column, $post_id) {
	$sptData = unserialize(get_post_meta($post_id, 'sptData', true));
	if (isset($sptData) && !empty($sptData)) {
		switch ($column) {
			case 'sptTestType':
				if (isset($sptData['splitTestType']) && !empty($sptData['splitTestType'])) {
					echo ucwords($sptData['splitTestType']);
				} else {
					echo 'Page';
				}
			break;
			case 'sptTestStats':
				$html = '';

				$master = array(
					'visits' => count($sptData[$sptData['master_id'] . '_visits'])
				);

				$variation = array(
					'visits' => count($sptData[$sptData['slave_id'] . '_visits'])
				);

				if ($master['visits'] > 0) {
					$html .= '<strong>Master:</strong> ' . count($sptData[$sptData['master_id'] . '_visits']) . ' visits';
					$html .= '<br />';
				} else {
					$html .= '<strong>Master:</strong> No stats yet!<br />';
				}

				if ($variation['visits'] > 0) {
					$html .= '<strong>Variation:</strong> ' . count($sptData[$sptData['slave_id'] . '_visits']) . ' visits';
					$html .= '<br />';
				} else {
					$html .= '<strong>Variation:</strong> No stats yet!<br />';
				}

				$html = apply_filters('spt_list_column_test_stats', $html, $sptData);

				echo $html;
			break;
		}
	}
}

/*******************************************************************************
** sptActivation
** On activation add flush flag which gets removed after flushing the rules once
** @since 1.0
*******************************************************************************/
function sptActivation() {
    add_option('spt_flush', 'true');
}

/*******************************************************************************
** sptDeactivation
** On deactivation remove flush flag
** @since 1.0
*******************************************************************************/
function sptDeactivation() {
    delete_option('spt_flush');
}

/*******************************************************************************
** sptInit
** Plugin initialization
** @since 1.0
*******************************************************************************/
function sptInit() {
	/* Register the SPT post type */
	sptRegisterPostType();

	/* Add meta boxes to edit screen */
	add_action('add_meta_boxes', 'sptMetaBoxes');

	/* Save meta information for test */
	add_action('save_post', 'sptSavePost');

	/* Control redirection */
	add_action('template_redirect', 'sptRedirect');

	/* Setup hook to catch split tests before they are deleted */
	add_action('trashed_post', 'sptTestDeleted');

	/* Setup canonical tag for secondary page */
	add_action('wp_head', 'sptSetupCanonicalTag');

	/* Register AJAX calls */
	add_action('wp_ajax_sptGetThickboxContent', 'sptGetThickboxContent');
	add_action('wp_ajax_sptDuplicateCurrentPost', 'sptDuplicateCurrentPost');
	add_action('wp_ajax_sptCreateNewPage', 'sptCreateNewPage');
	add_action('wp_ajax_sptReplaceSearchResults', 'sptReplaceSearchResults');
	add_action('wp_ajax_sptCreateSptPost', 'sptCreateSptPost');
	add_action('wp_ajax_sptDeclareWinner', 'sptDeclareWinner');

	// Add stats reset buttons to options, right at the bottom
	add_action('spt_after_side_options', 'sptAddStatsResetButtons', 100);

	// Add stuff to list page
	add_filter('manage_spt_posts_columns', 'sptAddCustomColumns', 10, 1);
	add_action('manage_spt_posts_custom_column', 'sptAddCustomColumnsContent', 10, 2);

}

/*******************************************************************************
** sptAdminInit
** Plugin admin initialization
** @since 1.0
*******************************************************************************/
function sptAdminInit() {
	/* Add necessary javascript for the admin page */
	add_action('admin_head', 'sptAdminHeader');

	/* Remove view from quick edit options on spt post type */
	add_filter('page_row_actions', 'sptRemoveItemsFromEditList', 10, 2);

	/* Get rid of add new action at top of edit screens for SPT post type */
	add_action('admin_head', 'sptHideAddNewFromEditPage');
}

/* Initialize the plugin */
register_activation_hook(__FILE__, 'sptActivation');
register_deactivation_hook(__FILE__, 'sptDeactivation');

add_action('init', 'sptInit');
add_action('admin_init', 'sptAdminInit');

/* Remove submenus from SPT menu and add custom ones */
add_action('admin_menu', 'sptRemoveAddNew');
add_action('admin_menu', 'sptSetupAddNewMenuItems', 10);

/* Remove SPT from the add new menu on the admin bar */
add_action('wp_before_admin_bar_render', 'sptHideAddNewFromAdminBar');

/* Include the stats helper functions */
require_once('StatsHelperFunctions.php');
