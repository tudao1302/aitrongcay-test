<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;
$table = aitrongcay_garden_pots_table();
$now = current_time('mysql');
$updated = $wpdb->query("UPDATE {$table} SET pot_name = REPLACE(pot_name, ' trống', ''), status = 'Đang theo dõi', status_summary = 'Khoang vừa được kích hoạt, đang bắt đầu theo dõi Ngày 1.', ai_note = 'Khu vườn bắt đầu ghi nhận dữ liệu sinh trưởng của khoang mới.', created_at = '{$now}', plant_name = 'Cây chưa xác định' WHERE status = 'Khoang trống'");
echo "Updated $updated pots.";
