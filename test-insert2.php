<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$data = [
    'pot_code' => 'P-001',
    'pot_name' => 'Khoang 1',
    'plant_name' => 'Cây cà chua',
];
$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
$now = current_time('mysql');
$merged = array_merge(['garden_key' => $garden_key, 'pot_code' => 'P-001'], $data, ['created_at' => $now]);
var_dump(array_keys($merged));

$res = aitrongcay_upsert_db_pot($garden_key, $data);
var_dump($res);
echo $wpdb->last_error;
