<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

echo "<h3>BƯỚC 1: ĐỒNG BỘ CƠ SỞ DỮ LIỆU</h3>";
$pots_table = aitrongcay_garden_pots_table();
$slots_table = aitrongcay_garden_rack_slots_table();

function add_column_if_not_exists($table, $column, $definition) {
    global $wpdb;
    $row = $wpdb->get_row("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
    if (empty($row)) {
        $wpdb->query("ALTER TABLE {$table} ADD COLUMN {$column} {$definition}");
        echo "<p style='color:green'>+ Đã thêm cột {$column} vào bảng {$table}</p>";
    } else {
        echo "<p style='color:gray'>- Cột {$column} đã tồn tại trong bảng {$table}</p>";
    }
}

add_column_if_not_exists($pots_table, 'plant_name', "varchar(255) NOT NULL DEFAULT ''");
add_column_if_not_exists($pots_table, 'latest_analysis_recommendation', "text NULL");
add_column_if_not_exists($slots_table, 'plant_name', "varchar(255) NOT NULL DEFAULT ''");

echo "<h3>BƯỚC 2: XÓA CACHE DASHBOARD</h3>";
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aitr_%' OR option_name LIKE '_transient_timeout_aitr_%'");
echo "<p style='color:green'>+ Đã xóa thành công toàn bộ cache tạm thời (transient) của vườn và rack.</p>";

echo "<h3>HOÀN TẤT!</h3>";
echo "<p>Bây giờ bạn hãy vào lại trang <strong>Cài đặt Rack</strong>, lưu tên cây là <em>Cây cà chua</em>, rồi tải lại trang Dashboard để kiểm tra nhé!</p>";
