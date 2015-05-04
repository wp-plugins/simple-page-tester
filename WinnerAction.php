<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!wp_verify_nonce($_REQUEST['nonce'], 'spt-winner-action')) {
    wp_send_json_error(array(
        'message' => __( 'Invalid request' )
    ));
}

if (empty($_POST['value']) || empty($_POST['post'])) {
    wp_send_json_error(array(
        'message' => __( 'Missing parameters' )
    ));
}

$value = strtolower($_POST['value']);

if (!in_array($value, array('delete', 'archive'))) {
    wp_send_json_error(array(
        'message' => __( 'Invalid parameter' )
    ));
}

$post_id = absint($_POST['post']);

$data = unserialize(get_post_meta($post_id, 'sptData', true));

$data['winner_action'] = $value;

update_post_meta($post_id, 'sptData', serialize($data));

//
// Create the JSON result.
//

wp_send_json_success(array(
    'action' => $value
));