<?php
require_once 'wp-load.php';
global $wpdb;
$racks = $wpdb->get_results("SELECT id, rack_code, rack_name, garden_key, status FROM wp_aitr_garden_racks", ARRAY_A);
print_r($racks);
