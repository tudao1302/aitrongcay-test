<?php
require_once dirname(__FILE__) . '/wp-load.php';

global $wpdb;
$racks_table = $wpdb->prefix . 'aitr_garden_racks';
$slots_table = $wpdb->prefix . 'aitr_rack_slots';
$pots_table  = $wpdb->prefix . 'aitr_garden_pots';

echo "<h2>Tiến hành đồng bộ chuẩn tên mã Khoang Toàn Diện</h2>";

// 1. Cập nhật RACK_3 -> R3
$wpdb->update($racks_table, ['rack_code' => 'R3'], ['rack_code' => 'RACK_3']);
$wpdb->query("UPDATE {$slots_table} SET pot_code = REPLACE(pot_code, 'RACK_3-', 'R3-') WHERE pot_code LIKE 'RACK_3-%'");
$wpdb->query("UPDATE {$slots_table} SET slot_code = REPLACE(slot_code, 'RACK_3-', 'R3-') WHERE slot_code LIKE 'RACK_3-%'");
if ($wpdb->get_var("SHOW TABLES LIKE '{$pots_table}'") === $pots_table) {
    $wpdb->query("UPDATE {$pots_table} SET pot_code = REPLACE(pot_code, 'RACK_3-', 'R3-') WHERE pot_code LIKE 'RACK_3-%'");
}
echo "<p>✅ Đã cập nhật Rack 3 (RACK_3 -> R3).</p>";

// 2. Cập nhật P-00x -> R1-S0x
// Quét từ P-001 đến P-010
for ($i = 1; $i <= 10; $i++) {
    $old_code = sprintf('P-%03d', $i);
    $new_code = sprintf('R1-S%02d', $i);
    
    $wpdb->update($slots_table, ['pot_code' => $new_code, 'slot_code' => $new_code], ['pot_code' => $old_code]);
    if ($wpdb->get_var("SHOW TABLES LIKE '{$pots_table}'") === $pots_table) {
        $wpdb->update($pots_table, ['pot_code' => $new_code], ['pot_code' => $old_code]);
    }
    // Cập nhật thẻ meta của Media Library
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_key = '_aitrongcay_pot_code' AND meta_value = %s", $new_code, $old_code));
}
echo "<p>✅ Đã cập nhật Rack 1 (P-00x -> R1-S0x) trong cơ sở dữ liệu và Thư viện ảnh.</p>";

// 3. Cập nhật trong Cấu hình (wp_options) - Tránh lỗi Unserialize
$options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_rack_cfg_%'", ARRAY_A);
foreach ($options as $opt) {
    $val = maybe_unserialize($opt['option_value']);
    if (is_array($val)) {
        $changed = false;
        foreach ($val as &$rack_cfg) {
            // Đổi rack_code nếu có
            if (isset($rack_cfg['rack_code']) && $rack_cfg['rack_code'] === 'RACK_3') {
                $rack_cfg['rack_code'] = 'R3';
                $changed = true;
            }
            if (isset($rack_cfg['trays']) && is_array($rack_cfg['trays'])) {
                foreach ($rack_cfg['trays'] as &$tray_cfg) {
                    $pot = $tray_cfg['pot_code'] ?? '';
                    if (strpos($pot, 'RACK_3-') === 0) {
                        $tray_cfg['pot_code'] = str_replace('RACK_3-', 'R3-', $pot);
                        $changed = true;
                    }
                    if (preg_match('/^P-00(\d)$/', $pot, $m)) {
                        $tray_cfg['pot_code'] = sprintf('R1-S%02d', $m[1]);
                        $changed = true;
                    }
                }
            }
        }
        if ($changed) {
            update_option($opt['option_name'], $val);
        }
    }
}
echo "<p>✅ Đã làm sạch các cấu hình Legacy ẩn trong wp_options.</p>";

// Xóa cache
delete_transient('aitrongcay_garden_pots_cache');
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aitr_blynk_status_%'");

echo "<hr><p><b>Hoàn tất!</b> Bạn có thể xóa file này và quay lại trang Dashboard (F5) để xem kết quả tuyệt đối.</p>";
