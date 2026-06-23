<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$data = [
    'garden_key' => 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b',
    'pot_code' => 'P-001',
    'pot_name' => 'Khoang 1',
    'plant_name' => 'Cây cà chua',
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql'),
];
$res = $wpdb->insert($table, $data);
var_dump($res);
echo $wpdb->last_error;
