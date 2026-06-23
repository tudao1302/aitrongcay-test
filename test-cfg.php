<?php
require_once dirname(__DIR__, 4) . '/wp-load.php';

$global_cfg = get_option('aitrongcay_rack_monitor_configs', null);
$garden_key = $_GET['garden'] ?? 'garden:c652d782acc145d690eb690043e62ddf';
$garden_cfg = get_option('aitrongcay_rack_cfg_' . sanitize_key($garden_key), null);

echo "Global CFG:\n";
print_r($global_cfg);
echo "\nGarden CFG:\n";
print_r($garden_cfg);

$merged = function_exists('aitrongcay_get_rack_monitor_configs') ? aitrongcay_get_rack_monitor_configs($garden_key) : [];
echo "\nMerged CFG:\n";
print_r($merged);
