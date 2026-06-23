<?php
require_once 'wp-load.php';
global $wpdb;

$assigned_racks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aitr_garden_racks ORDER BY rack_code ASC, id ASC", ARRAY_A);

echo "=== BẢN ĐỒ TỌA ĐỘ VẬT LÝ ===\n";
$current_node = 0;
foreach ($assigned_racks as $rack) {
    $slot_count = (int)($rack['slot_count'] ?? 0);
    $compartments = max(1, (int)ceil($slot_count / 2));
    
    $rack_start_node = $current_node;
    $rack_end_node = $current_node + $compartments - 1;
    
    echo "Tọa độ: N" . sprintf('%02d', $rack_start_node) . " đến N" . sprintf('%02d', $rack_end_node) . "\n";
    echo "  -> Thuộc về Khu vườn: " . $rack['garden_key'] . "\n";
    echo "  -> Mã Rack: " . $rack['rack_code'] . "\n";
    
    $current_node += $compartments;
}
echo "=============================\n";
