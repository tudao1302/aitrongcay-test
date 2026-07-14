<?php
require 'wp-load.php';
global $wpdb;

$table = 'wp_aitr_garden_pots';
$garden_key = 'garden:a5bb69bedc403485a0f61f7ea22dd505';
$pot_code = 'R2-S01';
$slot_plant_name = 'Rau muống';
$slot_camera = '';

$res = $wpdb->update(
    $table,
    [
        'plant_name' => $slot_plant_name,
        'video_url' => $slot_camera,
        'updated_at' => current_time('mysql')
    ],
    ['garden_key' => $garden_key, 'pot_code' => $pot_code]
);

$out = "Update result: " . var_export($res, true) . "\n";
$out .= "Last Query: " . $wpdb->last_query . "\n";
$out .= "Last Error: " . $wpdb->last_error . "\n";

$pots = $wpdb->get_results($wpdb->prepare("SELECT id, pot_code, plant_name, garden_key FROM wp_aitr_garden_pots WHERE garden_key = %s AND pot_code LIKE 'R2-%'", $garden_key), ARRAY_A);
$out .= "POTS:\n" . print_r($pots, true);

file_put_contents('d:\laragon\www\aitrongcay\debug_output.txt', $out);
echo "Done";
