<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$rows = $wpdb->get_results("SELECT pot_code, pot_name, plant_name, status FROM {$table}", ARRAY_A);
file_put_contents(__DIR__ . '/dump-pots.txt', print_r($rows, true));
echo "Dumped pots";
