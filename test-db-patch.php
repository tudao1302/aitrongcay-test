<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$slots_table = aitrongcay_garden_rack_slots_table();

$result = $wpdb->query("ALTER TABLE {$slots_table} ADD COLUMN plant_name varchar(255) NOT NULL DEFAULT '' AFTER slot_name");
if ($result !== false) {
    echo "Added plant_name to {$slots_table} successfully.<br>";
} else {
    echo "Failed to add column to slots, maybe it already exists: " . $wpdb->last_error . "<br>";
}

$pots_table = aitrongcay_garden_pots_table();
$result2 = $wpdb->query("ALTER TABLE {$pots_table} ADD COLUMN plant_name varchar(255) NOT NULL DEFAULT '' AFTER pot_name");
if ($result2 !== false) {
    echo "Added plant_name to {$pots_table} successfully.<br>";
} else {
    echo "Failed to add column to pots, maybe it already exists: " . $wpdb->last_error . "<br>";
}

$pots = $wpdb->get_results("SELECT * FROM {$pots_table} ORDER BY id DESC LIMIT 5", ARRAY_A);
echo "Recent pots:<br><pre>";
print_r($pots);
echo "</pre>";
