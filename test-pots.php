<?php
require_once 'wp-load.php';

global $wpdb;
$pots = $wpdb->get_results("SELECT garden_key, pot_code, pot_name, temperature, humidity, soil_moisture, ph, soil_ec, status_summary FROM wp_aitr_pots WHERE garden_key IN ('garden:a5bb69bedc403485a0f61f7ea22dd505', 'garden:3ac652d782acc145d690eb690043e62ddf')", ARRAY_A);

print_r($pots);
