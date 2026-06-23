<?php
$pdo = new PDO('mysql:host=localhost;dbname=nhaitpx1_wp01;charset=utf8mb4', 'root', '');

echo "=== Active plugins ===\n";
$row = $pdo->query("SELECT option_value FROM wp_options WHERE option_name = 'active_plugins' LIMIT 1")->fetch();
echo $row['option_value'] . "\n\n";

echo "=== All aitrongcay_rack* options ===\n";
$rows = $pdo->query("SELECT option_name, LENGTH(option_value) as len, LEFT(option_value,120) as preview FROM wp_options WHERE option_name LIKE 'aitrongcay_rack%'")->fetchAll();
foreach ($rows as $r) { echo "[{$r['option_name']}] {$r['len']} bytes: {$r['preview']}\n"; }

echo "\n=== aitrongcay_social_schema_version ===\n";
$row2 = $pdo->query("SELECT option_value FROM wp_options WHERE option_name = 'aitrongcay_social_schema_version'")->fetch();
echo ($row2 ? $row2['option_value'] : 'not found') . "\n";

echo "\n=== aitrongcay_db_seed_version ===\n";
$row3 = $pdo->query("SELECT option_value FROM wp_options WHERE option_name = 'aitrongcay_db_seed_version'")->fetch();
echo ($row3 ? $row3['option_value'] : 'not found') . "\n";

echo "\n=== Large aitrongcay options (>1KB) ===\n";
$rows4 = $pdo->query("SELECT option_name, LENGTH(option_value) as len FROM wp_options WHERE option_name LIKE 'aitrongcay%' AND LENGTH(option_value) > 1024 ORDER BY len DESC LIMIT 10")->fetchAll();
foreach ($rows4 as $r) { echo "[{$r['option_name']}] {$r['len']} bytes\n"; }

echo "\n=== Better search replace options ===\n";
$rows5 = $pdo->query("SELECT option_name, LENGTH(option_value) as len FROM wp_options WHERE option_name LIKE '%search_replace%' OR option_name LIKE '%bsr%'")->fetchAll();
foreach ($rows5 as $r) { echo "[{$r['option_name']}] {$r['len']} bytes\n"; }
