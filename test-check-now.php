<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
$pots = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s", $garden_key), ARRAY_A);
echo "Pots currently in DB for Khach 4:\n";
print_r($pots);
