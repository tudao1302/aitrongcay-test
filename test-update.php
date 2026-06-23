<?php
// test-update.php
require_once __DIR__ . '/wp-load.php';
header('Content-Type: text/plain; charset=utf-8');

global $wpdb;

$rack_id = 2; // RACK_2
$rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
if (!$rack) {
    echo "ERROR: Rack not found\n";
    exit;
}

$report = function_exists('aitrongcay_blynk_probe_rack') ? aitrongcay_blynk_probe_rack($rack) : [];
echo "Probe report:\n";
print_r($report);

echo "\nCalling aitrongcay_upsert_rack_record...\n";
$res = aitrongcay_upsert_rack_record((string)$rack['garden_key'], [
    'rack_code' => (string) ($rack['rack_code'] ?? ''),
    'rack_name' => (string) ($rack['rack_name'] ?? ''),
    'owner_user_id' => (int) ($rack['owner_user_id'] ?? 0),
    'status' => (string) ($rack['status'] ?? 'inventory'),
    'slot_count' => (int) ($rack['slot_count'] ?? 0),
    'controller_type' => (string) ($rack['controller_type'] ?? 'blynk'),
    'controller_label' => (string) ($rack['controller_label'] ?? ''),
    'blynk_auth_token' => (string) ($rack['blynk_auth_token'] ?? ''),
    'blynk_template_id' => (string) ($rack['blynk_template_id'] ?? ''),
    'blynk_template_name' => (string) ($rack['blynk_template_name'] ?? ''),
    'blynk_email' => (string) ($rack['blynk_email'] ?? ''),
    'connectivity_status' => (string) ($report['connectivity_status'] ?? 'unknown'),
    'last_seen_at' => $report['last_seen_at'] ?? ($rack['last_seen_at'] ?? null),
    'notes' => (string) ($rack['notes'] ?? ''),
]);

echo "Result: " . ($res ? "TRUE" : "FALSE") . "\n";
echo "Last Query:\n" . $wpdb->last_query . "\n";
echo "Last Error: " . $wpdb->last_error . "\n";

// Dump all racks again to see changes
$table = $wpdb->prefix . 'aitr_garden_racks';
$rows = $wpdb->get_results("SELECT id, rack_code, connectivity_status, last_seen_at FROM {$table}", ARRAY_A);
echo "\n=== ALL RACKS IN DB NOW ===\n";
print_r($rows);
