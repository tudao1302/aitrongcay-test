<?php
// print-rack-status.php
require_once __DIR__ . '/wp-load.php';
header('Content-Type: text/plain; charset=utf-8');

global $wpdb;
$table = $wpdb->prefix . 'aitr_garden_racks';

echo "=== ALL RACKS IN DB ===\n";
$rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
print_r($rows);
