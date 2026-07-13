<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

$tables = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}aitr_%'");
echo "<pre>"; print_r($tables); echo "</pre>";
