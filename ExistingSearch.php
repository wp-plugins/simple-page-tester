<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$search_query = sptFilterData(isset($_POST['search_query']) ? mysql_escape_string($_POST['search_query']) : '');
$search_offset = sptFilterData(isset($_POST['search_offset']) ? $_POST['search_offset'] : 0);
$current_post_id = sptFilterData(isset($_POST['current_post_id']) ? $_POST['current_post_id'] : '');

global $wpdb;
$querystr = "SELECT * FROM $wpdb->posts	
WHERE id != $current_post_id 
AND (post_type = 'page' OR post_type = 'post') 
AND post_status = 'publish' 
AND id NOT IN (
	SELECT post_id
	FROM $wpdb->postmeta
	WHERE meta_key = 'sptID'
	AND meta_value != ''
	AND meta_value IS NOT NULL
)
";

if (!empty($search_query))
	$querystr .= " AND LOWER(post_title) like '%" . strtolower($search_query) . "%' ";

$querystr .= " ORDER BY post_date DESC";

if (empty($search_query)) {
	$querystr .= " LIMIT 500";
	
	if (!empty($search_offset)) {
		$querystr .= " OFFSET " . $search_offset;
	}
}


$searchResult = $wpdb->get_results($querystr, OBJECT);

if (!empty($searchResult)) {

	$html = '';
	$i = 0;
	foreach ($searchResult as $result) {
		$html .= '<li class="post ' . ($i % 2 == 0 ? 'odd' : 'even') . '" id="' . $result->ID . '">' . $result->post_title . ' <span class="slug"> - /' . $result->post_name . '</span> <span class="spt_select_existing button">Select</span></li>';
		$i++;
	}
	
	echo $html;
	
} else {
	die('<li>Sorry, no posts or pages found.</li>');
}
?>
