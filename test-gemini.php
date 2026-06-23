<?php
require_once 'wp-load.php';
global $wpdb;

$out = "=== RACK CONFIGS ===\n";
$opts = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name LIKE 'aitrongcay_rack_cfg_%'");
foreach ($opts as $o) {
    $out .= "Option: " . $o->option_name . "\n";
    $out .= print_r(unserialize($o->option_value), true);
    $out .= "\n";
}

$out .= "=== BLYNK CONFIGS ===\n";
$opts = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->prefix}options WHERE option_name = 'aitrongcay_blynk_configs'");
foreach ($opts as $o) {
    $out .= "Option: " . $o->option_name . "\n";
    $out .= print_r(unserialize($o->option_value), true);
    $out .= "\n";
}

file_put_contents(ABSPATH . 'test-gemini-output.txt', $out);
echo "Done";

