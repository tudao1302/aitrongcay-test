<?php
require_once __DIR__ . '/wp-config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
    
    echo "<h3>1. wp_aitr_garden_pots</h3>";
    $stmt = $pdo->prepare("SELECT pot_code, pot_name, plant_name FROM {$table_prefix}aitr_garden_pots WHERE garden_key = ?");
    $stmt->execute([$garden_key]);
    echo "<pre>"; print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); echo "</pre>";

    echo "<h3>2. wp_aitr_garden_rack_slots</h3>";
    $stmt = $pdo->prepare("SELECT rack_id, slot_code, pot_code, slot_name, plant_name FROM {$table_prefix}aitr_garden_rack_slots");
    $stmt->execute();
    echo "<pre>"; print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); echo "</pre>";

    echo "<h3>3. wp_aitr_garden_racks</h3>";
    $stmt = $pdo->prepare("SELECT id, garden_key, rack_code, rack_name FROM {$table_prefix}aitr_garden_racks WHERE garden_key = ?");
    $stmt->execute([$garden_key]);
    echo "<pre>"; print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); echo "</pre>";
    
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}
