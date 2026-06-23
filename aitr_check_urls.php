<?php
$pdo = new PDO('mysql:host=localhost;dbname=nhaitpx1_wp01;charset=utf8mb4', 'root', '');
echo "=== 0.0.0.0 in options ===\n";
$rows = $pdo->query("SELECT option_name, LENGTH(option_value) as len FROM wp_options WHERE option_value LIKE '%0.0.0.0%'")->fetchAll();
foreach ($rows as $r) { echo "[{$r['option_name']}] {$r['len']} bytes\n"; }
echo $rows ? '' : "  not found in options\n";

echo "\n=== 0.0.0.0 in pots ===\n";
$rows2 = $pdo->query("SELECT id, pot_code FROM wp_aitr_garden_pots WHERE video_url LIKE '%0.0.0.0%' OR image_url LIKE '%0.0.0.0%'")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows2 as $r) { echo "pot {$r['id']} ({$r['pot_code']})\n"; }
echo $rows2 ? '' : "  not in pots\n";

echo "\n=== aitrongcay_rack_monitor_configs (global) ===\n";
$r = $pdo->query("SELECT option_value FROM wp_options WHERE option_name='aitrongcay_rack_monitor_configs'")->fetch();
echo ($r ? substr($r['option_value'], 0, 400) : "  not set") . "\n";
