<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$post_id = sptFilterData($_POST['post_id']);

if (!empty($post_id) && is_numeric($post_id)) {
	$post = get_post($post_id);
	
	if (!empty($post)) {
		echo sptCreateNewPageForSplitTest($post);
	} else {
		echo 'Sorry, invalid post id provided.';
		die();
	}
}

function sptCreateNewPageForSplitTest($post) {
	global $wpdb;
	
	$new_post_author = wp_get_current_user();
	
	$new_post = array(
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'post_author' => $new_post_author->ID,
		'post_content' => '',
		'post_date' => current_time('mysql'),
		'post_date_gmt' => current_time('mysql', true),
		'post_excerpt' => '',
		'post_parent' => '',
		'post_password' => '',
		'post_status' => 'publish',
		'post_title' => $post->post_title,
		'post_type' => $post->post_type
	);


	$new_post_id = wp_insert_post($new_post, false);
	return $new_post_id;
}
?>
