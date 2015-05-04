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
	
	switch ($sptData['winner_action']) {
		case 'delete':
			// Delete slave page
			wp_delete_post($sptData['slave_id'], true);
			break;
		
		case 'archive':
			$slave = get_post($sptData['slave_id']);

			// Put a suffix to the post name to indicate it's been archived.
			$name = sprintf('%s-archived-variation', $slave->post_name);

			// Change the post status of the slave page.
			wp_update_post(array(
				'ID'          => $slave->ID,
				'post_name'   => $name,
				'post_status' => 'trash'
			));
			break;
	}
	
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
	
	switch ($sptData['winner_action']) {
		case 'delete':
			// Delete master page
			wp_delete_post($sptData['master_id'], true);
			break;
		
		case 'archive':
			// Put a suffix to the post name to indicate it's been archived.
			$name = sprintf('%s-archived-variation', $masterSlug);

			// Change the post status of the master page.
			$result = wp_update_post(array(
				'ID'          => $masterPost->ID,
				'post_name'   => $name,
				'post_status' => 'trash'
			));
			break;
	}
	
	// Set slave slug to master slug
	$slavePost = array();
	$slavePost['ID'] = $sptData['slave_id'];
	$slavePost['post_name'] = wp_unique_post_slug($masterSlug, $sptData['slave_id'], $masterStatus, $masterPostType, $masterParent);
	wp_update_post($slavePost);
	
	echo $winnerID;
	
}

?>
