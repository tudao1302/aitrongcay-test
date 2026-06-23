<?php
require_once __DIR__ . '/wp-load.php';

$garden_key = 'garden:c652d782acc145d690eb690043e62ddf';
$rack_configs = aitrongcay_get_rack_monitor_configs($garden_key);
$slots = aitrongcay_get_rack_slots($garden_key);

$global_cfg = get_option('aitrongcay_rack_monitor_configs', null);
$garden_cfg = get_option('aitrongcay_rack_cfg_' . sanitize_key($garden_key), null);

$debug = [
    'global_cfg' => $global_cfg,
    'garden_cfg' => $garden_cfg,
    'rack_configs' => $rack_configs,
    'slots' => $slots,
];

file_put_contents(dirname(__FILE__) . '/debug-rack.json', json_encode($debug, JSON_PRETTY_PRINT));
echo "DONE";
