<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$pots_table = aitrongcay_garden_pots_table();
$slots_table = aitrongcay_garden_rack_slots_table();

function add_column_if_not_exists($table, $column, $definition) {
    global $wpdb;
    $row = $wpdb->get_row("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
    if (empty($row)) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        echo "Added {$column} to {$table}<br>";
    } else {
        echo "Column {$column} already exists in {$table}<br>";
    }
}

add_column_if_not_exists($pots_table, 'plant_name', "varchar(255) NOT NULL DEFAULT ''");
add_column_if_not_exists($pots_table, 'latest_analysis_recommendation', "text NULL");
add_column_if_not_exists($slots_table, 'plant_name', "varchar(255) NOT NULL DEFAULT ''");

echo "Schema sync complete.";
