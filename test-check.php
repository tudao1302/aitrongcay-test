<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
$pots = $wpdb->get_results($wpdb->prepare("SELECT pot_code, pot_name, plant_name, status FROM {$table} WHERE garden_key = %s", $garden_key), ARRAY_A);
echo "Pots for Khách hàng 4:\n";
print_r($pots);
$slots_table = aitrongcay_garden_rack_slots_table();
$slots = $wpdb->get_results("SELECT id, rack_id, slot_code, slot_name, plant_name, pot_code FROM {$slots_table} WHERE rack_id = 3", ARRAY_A);
echo "Slots for Rack 3:\n";
print_r($slots);

$columns = $wpdb->get_results("SHOW COLUMNS FROM " . aitrongcay_garden_pots_table(), ARRAY_A);
echo "Columns:\n";
print_r($columns);


