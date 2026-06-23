<?php
require_once 'wp-load.php';
global $wpdb;

$garden_key = 'garden:c652d782acc145d690eb690043e62ddf';
$table = $wpdb->prefix . 'aitr_rack_configs';

$racks = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s ORDER BY rack_index ASC", $garden_key), ARRAY_A);

echo "=== THÔNG TIN CAMERA CỦA VƯỜN: $garden_key ===\n";
if (empty($racks)) {
    echo "Khu vườn này CHƯA CÓ bất kỳ cấu hình Rack/Camera nào trong database!\n";
} else {
    foreach ($racks as $row) {
        echo "- Rack " . ($row['rack_index'] + 1) . " (" . $row['rack_name'] . "):\n";
        $config = json_decode($row['config_json'], true);
        if (!empty($config['trays'])) {
            foreach ($config['trays'] as $i => $tray) {
                $cam = $tray['webcam_url'] ?? '';
                echo "  + Khay " . ($i + 1) . ": " . ($cam === '' ? "[Trống]" : $cam) . "\n";
            }
        }
    }
}
echo "\n=== DANH SÁCH KHAY (POTS) ===\n";
$slots = aitrongcay_get_rack_slots($garden_key);
foreach ($slots as $s) {
    echo "Mã khay: " . $s['pot_code'] . " -> Nằm ở Slot: " . $s['slot_index'] . "\n";
}
