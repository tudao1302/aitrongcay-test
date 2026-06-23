<?php
require_once 'wp-load.php';

$api_key = get_option('aitrongcay_gemini_api_key', '');
if (empty($api_key)) {
    die("No API Key found.");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . rawurlencode($api_key);
$response = wp_remote_get($url, ['timeout' => 15, 'sslverify' => false]);

if (is_wp_error($response)) {
    die("Error: " . $response->get_error_message());
}

$body = wp_remote_retrieve_body($response);
echo "<pre>";
$data = json_decode($body, true);
if (isset($data['models'])) {
    foreach ($data['models'] as $model) {
        if (str_contains($model['name'], 'gemini') && in_array('generateContent', $model['supportedGenerationMethods'] ?? [])) {
            echo $model['name'] . "\n";
        }
    }
} else {
    echo "Raw response:\n" . $body;
}
echo "</pre>";
