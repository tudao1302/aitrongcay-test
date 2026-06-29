<?php
require_once 'wp-load.php';
global $wpdb;
$plants = $wpdb->get_results("SELECT id, public_name FROM {$wpdb->prefix}aitr_onboarding_plants WHERE public_name LIKE '%Rau cải xoong%' OR public_name LIKE '%rau cai xoong%'", ARRAY_A);
echo json_encode($plants, JSON_UNESCAPED_UNICODE);
