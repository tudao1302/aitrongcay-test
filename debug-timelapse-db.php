<?php
// debug-timelapse-db.php - Kiểm tra nguồn dữ liệu timelapse
// Truy cập: http://localhost/aitrongcay/debug-timelapse-db.php

define('ABSPATH_FOUND', true);
require_once __DIR__ . '/wp-load.php';

global $wpdb;
header('Content-Type: text/plain; charset=utf-8');

// 1. WP Options
echo "=== WP Options (aitrongcay_rack_cfg_*) ===\n";
$rows = $wpdb->get_results(
    "SELECT option_name, CHAR_LENGTH(option_value) as len FROM {$wpdb->options}
     WHERE option_name LIKE 'aitrongcay_rack_cfg_%'
        OR option_name = 'aitrongcay_rack_monitor_configs'",
    ARRAY_A
);
foreach ($rows as $r) {
    echo $r['option_name'] . ' (len=' . $r['len'] . ")\n";
    $cfg = maybe_unserialize($wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name=%s", $r['option_name'])));
    if (is_array($cfg)) {
        foreach ($cfg as $rack_idx => $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $ti => $tray) {
                $wurl = trim((string)($tray['webcam_url'] ?? ''));
                echo "  rack[$rack_idx] tray[$ti]: webcam_url=" . ($wurl ?: '(empty)') . "\n";
            }
        }
    }
}

// 2. DB racks
echo "\n=== wp_aitr_garden_racks ===\n";
$racks_table = $wpdb->prefix . 'aitr_garden_racks';
$slots_table = $wpdb->prefix . 'aitr_rack_slots';

$racks = $wpdb->get_results("SELECT id, garden_key, rack_name FROM {$racks_table}", ARRAY_A);
if (empty($racks)) {
    echo "(no rows - table may not exist)\n";
} else {
    foreach ($racks as $r) {
        echo 'ID=' . $r['id'] . ' key=' . $r['garden_key'] . ' sanitize=' . sanitize_key($r['garden_key']) . ' name=' . $r['rack_name'] . "\n";
    }
}

// 3. DB slots with camera URLs
echo "\n=== wp_aitr_rack_slots (camera_stream_url) ===\n";
$slots = $wpdb->get_results(
    "SELECT r.garden_key, s.slot_name, s.slot_index, s.camera_stream_url
       FROM {$racks_table} r
       JOIN {$slots_table} s ON s.rack_id = r.id",
    ARRAY_A
);
if (empty($slots)) {
    echo "(no rows)\n";
} else {
    foreach ($slots as $s) {
        $cam = trim((string)($s['camera_stream_url'] ?? ''));
        echo sanitize_key($s['garden_key']) . ' | ' . $s['slot_name'] . ' (idx=' . $s['slot_index'] . ')'
            . ' | cam=' . ($cam ?: '(empty)') . "\n";
    }
}

// 4. What the timelapse cron would SEE for garden_key matching
echo "\n=== Folder name comparison ===\n";
echo "Existing folder 'gardena5bb69bedc403485a0f61f7ea22dd505'\n";
echo "sanitize_key of DB garden_key examples:\n";
foreach ($racks as $r) {
    echo '  DB key: ' . $r['garden_key'] . ' → sanitize: ' . sanitize_key($r['garden_key']) . "\n";
}
