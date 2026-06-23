<?php
require 'wp-load.php';

$garden_key = 'garden:3aa5bb69bedc403485a0f61f7ea22dd505';
$racks = aitrongcay_get_rack_monitor_configs($garden_key);

echo "Rack 1:\n";
$token1 = $racks[0]['blynk_auth_token'] ?? 'none';
echo "Token: $token1\n";
print_r(aitrongcay_tray_read_sensors($racks[0]['trays'][0]));

echo "\nRack 2:\n";
$token2 = $racks[1]['blynk_auth_token'] ?? 'none';
echo "Token: $token2\n";
print_r(aitrongcay_tray_read_sensors($racks[1]['trays'][0]));
