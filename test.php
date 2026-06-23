<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$table = $wpdb->prefix . 'aitr_garden_plant_pots';
$garden = 'garden:a5bb69bedc403485a0f61f7ea22dd505';

$pots = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s", $garden), ARRAY_A);

foreach ($pots as $pot) {
    echo "Updating pot " . $pot['pot_code'] . "\n";
    $wpdb->update($table, ['current_stage' => 'Nảy mầm'], ['id' => $pot['id']]);
}
echo "Done.";
