<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$winnerID = sptFilterData($_POST['winner_id']);
if (!is_numeric($winnerID))
	die();

$sptID = get_post_meta($winnerID, 'sptID', true);

$sptData = unserialize(get_post_meta($sptID, 'sptData', true));

// Determine if the winner was the master or the slave
$isMaster = false;
if ($sptData['master_id'] == $winnerID) $isMaster = true;
if ($sptData['slave_id'] == $winnerID) $isMaster = false;

// if winner is master then delete slave page
// if winner is slave, then delete master page and set slug on slave to master's slug

if ($isMaster) {
	
	// Delete test
	delete_post_meta($sptData['master_id'], 'sptID');
	delete_post_meta($sptData['slave_id'], 'sptID');
	wp_delete_post($sptID, true);
	
	// Delete slave page
	wp_delete_post($sptData['slave_id'], true);
	
	echo $winnerID;
	
} else {
	
	// Retain master info to reset into slave page
	$masterPost = get_post($sptData['master_id']);
	$masterSlug = $masterPost->post_name;
	$masterStatus = $masterPost->post_status;
	$masterPostType = $masterPost->post_type;
	$masterParent = $masterPost->post_parent;
	
	// Delete test
	delete_post_meta($sptData['master_id'], 'sptID');
	delete_post_meta($sptData['slave_id'], 'sptID');
	wp_delete_post($sptID, true);
	
	// Delete master page
	wp_delete_post($sptData['master_id'], true);
	
	// Set slave slug to master slug
	$slavePost = array();
	$slavePost['ID'] = $sptData['slave_id'];
	$slavePost['post_name'] = wp_unique_post_slug($masterSlug, $sptData['slave_id'], $masterStatus, $masterPostType, $masterParent);
	wp_update_post($slavePost);
	
	echo $winnerID;
	
}

?>
