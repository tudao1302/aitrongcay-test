<?php
require_once 'wp-load.php';
global $wpdb;
$tables = $wpdb->get_col("SHOW TABLES LIKE '%aitr%'");
file_put_contents('test-tables-out.txt', print_r($tables, true));
echo "Done";
