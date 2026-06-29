<?php
require_once __DIR__ . '/wp-load.php';

$url = 'https://camera.camera-vuon-nha.online/api/frame.jpeg?src=vuon1';
echo "<h1>Testing Timelapse Fetch</h1>";
echo "Fetching URL: <b>$url</b><br><br>";

$args = array(
    'timeout'     => 15,
    'redirection' => 5,
    'httpversion' => '1.0',
    'blocking'    => true,
    'headers'     => array(
        'ngrok-skip-browser-warning' => 'true'
    )
);

$response = wp_remote_get($url, $args);

if (is_wp_error($response)) {
    echo "<h2 style='color:red;'>ERROR:</h2>";
    echo "<pre>" . print_r($response->get_error_message(), true) . "</pre>";
} else {
    $code = wp_remote_retrieve_response_code($response);
    echo "<h2>HTTP STATUS CODE: <span style='color:" . ($code == 200 ? "green" : "red") . "'>$code</span></h2>";
    
    $body = wp_remote_retrieve_body($response);
    $content_type = wp_remote_retrieve_header($response, 'content-type');
    
    echo "<b>Content-Type:</b> $content_type <br>";
    echo "<b>Body Length:</b> " . strlen($body) . " bytes<br><br>";
    
    if (strpos($content_type, 'image') !== false) {
        echo "<h3 style='color:green'>SUCCESS! We received an image!</h3>";
        echo '<img src="data:image/jpeg;base64,' . base64_encode($body) . '" style="max-width:500px; border: 2px solid green;"/>';
    } else {
        echo "<h3 style='color:red'>FAILED! Cloudflare is blocking it with an HTML page:</h3>";
        echo "<div style='border:1px solid #ccc; padding:10px; background:#f9f9f9; overflow:auto; max-height:400px;'>";
        echo htmlspecialchars($body);
        echo "</div>";
    }
}
