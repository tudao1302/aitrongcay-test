<?php
$pdo = new PDO('mysql:host=localhost;dbname=nhaitpx1_wp01;charset=utf8mb4', 'root', '');

echo "=== Before fix ===\n";
$rows = $pdo->query("SELECT id, slot_code, camera_stream_url FROM wp_aitr_garden_rack_slots WHERE camera_stream_url IS NOT NULL AND camera_stream_url != ''")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "slot {$r['id']} ({$r['slot_code']}): {$r['camera_stream_url']}\n";
}

echo "\n=== Clearing old ngrok URL from slot 41 ===\n";
$stmt = $pdo->prepare("UPDATE wp_aitr_garden_rack_slots SET camera_stream_url = NULL WHERE id = 41");
$stmt->execute();
echo "Rows affected: " . $stmt->rowCount() . "\n";

echo "\n=== After fix ===\n";
$rows2 = $pdo->query("SELECT id, slot_code, camera_stream_url FROM wp_aitr_garden_rack_slots WHERE camera_stream_url IS NOT NULL AND camera_stream_url != ''")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) {
    echo "slot {$r['id']} ({$r['slot_code']}): {$r['camera_stream_url']}\n";
}
echo $rows2 ? '' : "  (no camera_stream_url in any slot - fallback to rack_cfg will be used)\n";
