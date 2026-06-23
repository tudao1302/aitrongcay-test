<?php
define('WP_USE_THEMES', false);
require_once 'd:\laragon\www\aitrongcay\wp-load.php';

$_POST['garden_key'] = 'garden:a5bb69bedc403485a0f61f7ea22dd505';

function mock_wp_send_json_success($data) {
    echo "SUCCESS:\n";
    print_r($data);
    exit;
}
function mock_wp_send_json_error($data) {
    echo "ERROR:\n";
    print_r($data);
    exit;
}
// Override wp_send_json functions
if (!function_exists('wp_send_json_success_mock')) {
    function wp_send_json_success($data = null, $status_code = null, $options = 0) {
        mock_wp_send_json_success($data);
    }
    function wp_send_json_error($data = null, $status_code = null, $options = 0) {
        mock_wp_send_json_error($data);
    }
}

// remove nonce check
remove_action('wp_ajax_aitrongcay_blynk_get_status', 'aitrongcay_require_portal_nonce');
function aitrongcay_require_portal_nonce() {}

// Call function
aitrongcay_blynk_get_status_ajax();
