<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!wp_verify_nonce($_REQUEST['nonce'], 'sptTourClose')) {
    wp_send_json_error(array(
        'message' => __( 'Invalid request' )
    ));
}

update_option('sptTourStatus', 'close');

wp_send_json_success();