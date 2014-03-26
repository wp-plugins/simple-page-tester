<?php
$post_id = $_POST['post_id'];
if (!empty($post_id) && is_numeric($post_id)) {
	$post = get_post($post_id);
	
	if (!empty($post)) {
		echo sptDuplicatePost($post);
	} else {
		echo 'Sorry, invalid post id provided.';
		die();
	}
}

function sptDuplicatePost($post) {
	global $wpdb;
	
	$new_post_author = wp_get_current_user();
	
	$new_post = array(
		'menu_order' => $post->menu_order,
		'comment_status' => $post->comment_status,
		'ping_status' => $post->ping_status,
		'pinged' => $post->pinged,
		'post_author' => $new_post_author->ID,
		'post_content' => $post->post_content,
		'post_date' => current_time('mysql'),
		'post_date_gmt' => get_gmt_from_date(current_time('mysql')),
		'post_excerpt' => $post->post_excerpt,
		'post_parent' => $post->post_parent,
		'post_password' => $post->post_password,
		'post_status' => 'publish',
		'post_title' => $post->post_title,
		'post_type' => $post->post_type,
		'page_template' => $post->page_template,
		'to_ping' => $post->to_ping 
	);

	$new_post_id = wp_insert_post($new_post, false);
	
	// Check if successfully duplicated
	if ($new_post_id != 0) {
		
		$post_name = wp_unique_post_slug($post->post_name, $new_post_id, 'publish', $post->post_type, $post->post_parent);
	
		$new_post = array();
		$new_post['ID'] = $new_post_id;
		$new_post['post_name'] = $post_name;
	
		// Update the post into the database
		wp_update_post($new_post);
	
	}
	
	return $new_post_id;
}

?>
