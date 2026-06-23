<?php
require 'wp-load.php';

header('Content-Type: text/plain');

$garden_key = 'garden:3aa5bb69bedc403485a0f61f7ea22dd505';
$racks = aitrongcay_get_rack_monitor_configs($garden_key);

echo "RACK 0:\n";
echo "Token: " . ($racks[0]['blynk_auth_token'] ?? 'none') . "\n";
print_r(aitrongcay_tray_read_sensors($racks[0]['trays'][0]));

echo "\n\nRACK 1:\n";
echo "Token: " . ($racks[1]['blynk_auth_token'] ?? 'none') . "\n";
print_r(aitrongcay_tray_read_sensors($racks[1]['trays'][0]));
