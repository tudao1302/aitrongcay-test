<?php
require_once __DIR__ . '/wp-load.php';

echo "<pre>";
echo "Testing Blynk Token Retrieval...\n";

// Get all gardens
$device_configs = get_option('aitrongcay_garden_device_configs', []);
$all_gardens = array_keys($device_configs);

echo "Found gardens: " . implode(', ', $all_gardens) . "\n\n";

foreach ($all_gardens as $gk) {
    echo "Garden: $gk\n";
    $blynk_token = '';
    
    if (function_exists('aitrongcay_blynk_config')) {
        $cfg = aitrongcay_blynk_config($gk);
        if (!empty($cfg['token'])) {
            $blynk_token = trim($cfg['token']);
            echo "  [Advanced Model] Token found: $blynk_token\n";
        }
    }
    
    if ($blynk_token === '' && function_exists('aitrongcay_get_rack_monitor_configs')) {
        $racks = aitrongcay_get_rack_monitor_configs($gk);
        foreach ($racks as $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $tray) {
                $tk = trim((string) ($tray['blynk_token'] ?? ''));
                if ($tk !== '') {
                    $blynk_token = $tk;
                    echo "  [Tray Model] Token found: $blynk_token\n";
                    break 2;
                }
            }
        }
    }

    if ($blynk_token !== '') {
        $url = "https://blynk.cloud/external/api/batch/update?token={$blynk_token}&V3=1&V17=11&V18=6";
        echo "  Testing URL: $url\n";
        
        $resp = wp_remote_get($url, ['timeout' => 10]);
        if (is_wp_error($resp)) {
            echo "  Error: " . $resp->get_error_message() . "\n";
        } else {
            echo "  Response Code: " . wp_remote_retrieve_response_code($resp) . "\n";
            echo "  Response Body: " . wp_remote_retrieve_body($resp) . "\n";
        }
    } else {
        echo "  No token found for this garden.\n";
    }
    echo "\n";
}
echo "</pre>";
