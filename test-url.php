<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = $wpdb->prefix . 'aitr_garden_plant_pots';
$pots = $wpdb->get_results("SELECT pot_code, image_url FROM {$table} LIMIT 10", ARRAY_A);
print_r($pots);
