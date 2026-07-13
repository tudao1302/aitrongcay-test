<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

$map = [
    'P-001' => 'R1-S01', 'P-002' => 'R1-S02', 'P-003' => 'R1-S03', 'P-004' => 'R1-S04',
    'P-005' => 'R2-S05', 'P-006' => 'R2-S06',
    'RACK_3-S01' => 'R3-S01', 'RACK_3-S02' => 'R3-S02', 'RACK_3-S03' => 'R3-S03', 'RACK_3-S04' => 'R3-S04'
];

$log = [];
foreach ($map as $old => $new) {
    $r = $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_pots SET pot_code = %s WHERE pot_code = %s", $new, $old));
    $log[] = "Updated wp_aitr_garden_pots pot_code $old -> $new: " . ($r !== false ? "$r rows" : "ERROR");
}

$wpdb->query("UPDATE {$wpdb->prefix}aitr_garden_racks SET rack_code = 'R3' WHERE rack_code = 'RACK_3'");

$opts = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_rack_cfg_%'", ARRAY_A);
foreach ($opts as $o) {
    $val = maybe_unserialize($o['option_value']);
    if (is_array($val)) {
        $val_json = json_encode($val);
        foreach ($map as $old => $new) {
            $val_json = str_replace('"' . $old . '"', '"' . $new . '"', $val_json);
        }
        $val_json = str_replace('"RACK_3"', '"R3"', $val_json);
        $new_val = json_decode($val_json, true);
        update_option($o['option_name'], $new_val);
        $log[] = "Updated option " . $o['option_name'];
    }
}

file_put_contents(ABSPATH . 'debug_rename.txt', implode("\n", $log));
echo "DONE";
