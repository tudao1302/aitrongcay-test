<?php
require_once __DIR__ . '/wp-config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
    
    echo "<h3>wp_usermeta</h3>";
    $stmt = $pdo->prepare("SELECT meta_value FROM {$table_prefix}usermeta WHERE meta_key = 'aitr_custom_pots'");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = unserialize($row['meta_value']);
        if (isset($data[$garden_key])) {
            echo "Found in aitr_custom_pots:<br>";
            echo "<pre>"; print_r($data[$garden_key]); echo "</pre>";
        }
    }
    
    echo "<h3>Searching entire DB for Rau kinh giới</h3>";
    $tables = ['wp_options', 'wp_usermeta', 'wp_postmeta'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("SELECT * FROM {$t} WHERE " . ($t === 'wp_options' ? 'option_value' : 'meta_value') . " LIKE '%Rau kinh giới%'");
        if ($stmt->rowCount() > 0) {
            echo "Found in {$t}!<br>";
            echo "<pre>"; print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); echo "</pre>";
        }
    }
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
