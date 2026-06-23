<?php
// check-options.php
require_once __DIR__ . '/wp-load.php';
header('Content-Type: text/plain; charset=utf-8');

$garden_key = 'garden:a5bb69bedc403485a0f61f7ea22dd505';

echo "garden_key: $garden_key\n";
echo "sanitize_key(garden_key): " . sanitize_key($garden_key) . "\n\n";

$opt_with_colon = 'aitrongcay_rack_cfg_' . $garden_key;
$opt_without_colon = 'aitrongcay_rack_cfg_' . sanitize_key($garden_key);

echo "Option with colon ($opt_with_colon):\n";
$val1 = get_option($opt_with_colon);
echo "Type: " . gettype($val1) . "\n";
if (is_array($val1)) {
    echo "Count: " . count($val1) . "\n";
    print_r($val1);
} else {
    var_dump($val1);
}

echo "\nOption without colon ($opt_without_colon):\n";
$val2 = get_option($opt_without_colon);
echo "Type: " . gettype($val2) . "\n";
if (is_array($val2)) {
    echo "Count: " . count($val2) . "\n";
} else {
    var_dump($val2);
}
