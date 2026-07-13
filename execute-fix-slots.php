<?php
require_once dirname(__FILE__) . '/wp-load.php';
global $wpdb;

$slots_table = $wpdb->prefix . 'aitr_garden_rack_slots';
$racks_table = $wpdb->prefix . 'aitr_garden_racks';
$pots_table = $wpdb->prefix . 'aitr_garden_pots';

// 1. Lấy toàn bộ Racks
$racks = $wpdb->get_results("SELECT id, rack_code, garden_key FROM {$racks_table}", ARRAY_A);
$rack_map = [];
foreach ($racks as $r) {
    $rack_map[$r['id']] = $r;
}

// 2. Sửa lại mã Pot Code trong bảng Slots cho đúng chuẩn theo Rack Code
$slots = $wpdb->get_results("SELECT id, rack_id, slot_index, pot_code FROM {$slots_table}", ARRAY_A);
foreach ($slots as $slot) {
    $rack_id = (int) $slot['rack_id'];
    if (isset($rack_map[$rack_id])) {
        $rack_code = $rack_map[$rack_id]['rack_code']; // Vd: R4
        $slot_idx = (int) $slot['slot_index'];
        
        $correct_code = sprintf('%s-S%02d', $rack_code, $slot_idx);
        
        if ($slot['pot_code'] !== $correct_code) {
            $wpdb->update($slots_table, [
                'pot_code' => $correct_code,
                'slot_code' => $correct_code
            ], ['id' => $slot['id']]);
        }
    }
}

// 3. Xây dựng lại wp_options aitrongcay_rack_cfg_... 
// (Bởi vì file trước đó str_replace làm hỏng data của R4, R5)
$gardens = $wpdb->get_col("SELECT DISTINCT garden_key FROM {$racks_table} WHERE garden_key != '' AND status = 'assigned'");
foreach ($gardens as $gk) {
    if (function_exists('aitrongcay_handoff_rebuild_wp_option')) {
        aitrongcay_handoff_rebuild_wp_option($gk);
    }
}

// 4. Dọn dẹp Pots cache
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_aitrongcay_custom_pots_by_garden'");
$wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key = '_aitrongcay_pot_name_overrides_by_garden'");
delete_transient('aitrongcay_garden_pots_cache');
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aitr_t_%'");

echo "<h1>SLOTS AND RACKS FIXED!</h1>";
