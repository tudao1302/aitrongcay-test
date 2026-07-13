<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

echo "<h1>DB POTS</h1>";
$pots = $wpdb->get_results("SELECT id, garden_key, pot_code FROM {$wpdb->prefix}aitr_garden_pots", ARRAY_A);
echo "<pre>"; print_r($pots); echo "</pre>";

echo "<h1>WP OPTIONS</h1>";
$opts = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_rack_cfg_%'", ARRAY_A);
foreach ($opts as $o) {
    echo "<h3>" . $o['option_name'] . "</h3>";
    echo "<pre>"; print_r(maybe_unserialize($o['option_value'])); echo "</pre>";
}

echo "<h1>USER META</h1>";
$meta = $wpdb->get_results("SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE meta_key = '_aitrongcay_custom_pots_by_garden'", ARRAY_A);
foreach ($meta as $m) {
    echo "<h3>User ID: " . $m['user_id'] . "</h3>";
    echo "<pre>"; print_r(maybe_unserialize($m['meta_value'])); echo "</pre>";
}
