<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

$map = [
    'RACK_1' => 'R1',
    'RACK_2' => 'R2',
    'RACK_4' => 'R4',
    'RACK_5' => 'R5'
];

foreach ($map as $old => $new) {
    // Rename in wp_aitr_garden_racks
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aitr_garden_racks SET rack_code = %s WHERE rack_code = %s", $new, $old));
}

// Update wp_options
$opts = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_rack_cfg_%'", ARRAY_A);
foreach ($opts as $o) {
    $val = maybe_unserialize($o['option_value']);
    if (is_array($val)) {
        $val_json = json_encode($val);
        foreach ($map as $old => $new) {
            $val_json = str_replace('"' . $old . '"', '"' . $new . '"', $val_json);
        }
        $new_val = json_decode($val_json, true);
        update_option($o['option_name'], $new_val);
    }
}

// Update transient and user meta caches just in case
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_aitrongcay_custom_pots_by_garden'");
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_aitrongcay_pot_name_overrides_by_garden'");
delete_transient('aitrongcay_garden_pots_cache');
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aitr_blynk_status_%'");

echo "<h1>RACKS RENAMED PERFECTLY!</h1>";
