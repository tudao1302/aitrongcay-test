<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/wp-load.php';

$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';

echo "<h2>1. Data from wp_aitr_garden_pots table:</h2>";
global $wpdb;
$table = aitrongcay_garden_pots_table();
$pots = $wpdb->get_results($wpdb->prepare("SELECT pot_code, pot_name, plant_name FROM {$table} WHERE garden_key = %s", $garden_key), ARRAY_A);
echo "<pre>";
print_r($pots);
echo "</pre>";

echo "<h2>2. Data from wp_aitr_garden_rack_slots table:</h2>";
$slots_table = aitrongcay_garden_rack_slots_table();
$rack = function_exists('aitrongcay_get_rack_record') ? aitrongcay_get_rack_record($garden_key) : null;
if ($rack) {
    $rack_id = $rack['id'];
    $slots = $wpdb->get_results($wpdb->prepare("SELECT pot_code, slot_name, plant_name FROM {$slots_table} WHERE rack_id = %d", $rack_id), ARRAY_A);
    echo "<pre>";
    print_r($slots);
    echo "</pre>";
} else {
    echo "No rack found for garden_key: $garden_key";
}

echo "<h2>3. Output of aitrongcay_portal_dataset_for_garden():</h2>";
$dataset = aitrongcay_portal_dataset_for_garden($garden_key);
echo "<pre>";
print_r($dataset['pots']);
echo "</pre>";
