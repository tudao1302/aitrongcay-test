<?php
$pdo = new PDO('mysql:host=localhost;dbname=nhaitpx1_wp01;charset=utf8mb4', 'root', '');

echo "=== All aitrongcay_rack_cfg_* options ===\n";
$rows = $pdo->query("SELECT option_name, option_value FROM wp_options WHERE option_name LIKE 'aitrongcay_rack_cfg_%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "\n[{$r['option_name']}]\n";
    // Extract webcam_url values from serialized PHP
    preg_match_all('/"webcam_url";s:\d+:"([^"]+)"/', $r['option_value'], $matches);
    if ($matches[1]) {
        foreach ($matches[1] as $url) {
            echo "  webcam_url: $url\n";
        }
    } else {
        echo "  (no webcam_url found, raw: " . substr($r['option_value'], 0, 300) . ")\n";
    }
}
echo $rows ? '' : "  not found\n";

echo "\n=== camera_stream_url in rack_slots ===\n";
$rows2 = $pdo->query("SELECT id, slot_code, camera_stream_url FROM wp_aitr_garden_rack_slots WHERE camera_stream_url IS NOT NULL AND camera_stream_url != ''")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) {
    echo "slot {$r['id']} ({$r['slot_code']}): {$r['camera_stream_url']}\n";
}
echo $rows2 ? '' : "  no camera_stream_url set in any slot\n";

echo "\n=== global aitrongcay_rack_monitor_configs ===\n";
$r = $pdo->query("SELECT option_value FROM wp_options WHERE option_name='aitrongcay_rack_monitor_configs'")->fetch();
if ($r) {
    preg_match_all('/"webcam_url";s:\d+:"([^"]+)"/', $r['option_value'], $m);
    echo $m[1] ? implode("\n", array_map(fn($u) => "  webcam_url: $u", $m[1])) . "\n" : "  (no webcam_url)\n";
} else {
    echo "  not set\n";
}
