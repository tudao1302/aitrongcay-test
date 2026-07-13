<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

echo "<pre>";

$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_racks SET rack_code = 'R3' WHERE rack_code = 'RACK_3'");

$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R1-S01', 'P-001'));
$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R1-S02', 'P-002'));
$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R1-S03', 'P-003'));
$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R1-S04', 'P-004'));
$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R2-S05', 'P-005'));
$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = %s WHERE pot_code = %s", 'R2-S06', 'P-006'));

$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = 'R3-S01' WHERE pot_code = 'RACK_3-S01'");
$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = 'R3-S02' WHERE pot_code = 'RACK_3-S02'");
$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = 'R3-S03' WHERE pot_code = 'RACK_3-S03'");
$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_rack_slots SET pot_code = 'R3-S04' WHERE pot_code = 'RACK_3-S04'");

echo "ALL DONE PERFECTLY!";
echo "</pre>";