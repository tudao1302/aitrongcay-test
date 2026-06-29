<?php
require_once 'wp-load.php';
global $wpdb;

$assignments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aitr_rack_assignments");
file_put_contents('assignments_output.txt', print_r($assignments, true));
