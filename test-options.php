<?php
require_once __DIR__ . '/wp-config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
    
    echo "<h3>wp_options: aitrongcay_rack_cfg...</h3>";
    $stmt = $pdo->prepare("SELECT option_value FROM {$table_prefix}options WHERE option_name = ?");
    $stmt->execute(['aitrongcay_rack_cfg_' . $garden_key]);
    $res = $stmt->fetchColumn();
    echo "<pre>"; print_r(unserialize($res)); echo "</pre>";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
