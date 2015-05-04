<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!wp_verify_nonce($_REQUEST['nonce'], 'spt-pause-test')) {
    wp_send_json_error(array(
        'message' => __( 'Invalid request' )
    ));
}

if (empty($_POST['pause']) || empty($_POST['post'])) {
    wp_send_json_error(array(
        'message' => __( 'Missing parameters' )
    ));
}

$pause = strtolower($_POST['pause']);

if (!in_array($pause, array('on', 'off'))) {
    wp_send_json_error(array(
        'message' => __( 'Invalid parameter' )
    ));
}

$post_id = absint($_POST['post']);

$data = unserialize(get_post_meta($post_id, 'sptData', true));

$data['pause_test'] = $pause;

update_post_meta($post_id, 'sptData', serialize($data));

//
// Create the JSON result.
//

$result = array(
    'pause' => $pause
);

if ($pause === 'on') {
    $result = array_merge($result, array(
        'text' => __('Not Running')
    ));
} else {
    $result = array_merge($result, array(
        'text' => __('Active')
    ));
}

wp_send_json_success($result);