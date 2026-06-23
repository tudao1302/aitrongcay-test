<?php
require_once 'wp-load.php';
global $wpdb;

echo "<h2>=== ĐANG XÓA TOÀN BỘ DỮ LIỆU RACKS VÀ POTS (LÀM SẠCH HOÀN TOÀN) ===</h2>";

$wpdb->show_errors();

// 1. Tắt kiểm tra khóa ngoại cho toàn bộ phiên kết nối hiện tại
$wpdb->query("SET FOREIGN_KEY_CHECKS = 0;");

// 2. Danh sách các bảng cần làm sạch (Xóa tất cả Rack và Slot, Orders)
$tables_to_truncate = [
    $wpdb->prefix . 'aitr_garden_rack_assignments',
    $wpdb->prefix . 'aitr_garden_rack_slots',
    $wpdb->prefix . 'aitr_garden_rack_inventory_events',
    $wpdb->prefix . 'aitr_garden_racks',
    $wpdb->prefix . 'aitr_garden_pots', // Xóa sạch cả Pot của Admin để trắng tinh Dashboard
    $wpdb->prefix . 'aitr_orders', // Xóa sạch đơn hàng để test luồng từ đầu
];

foreach ($tables_to_truncate as $table) {
    // Truncate giúp reset ID về 1 và xóa nhanh hơn Delete
    $result = $wpdb->query("TRUNCATE TABLE {$table}");
    if ($result === false) {
        echo "<p style='color:red;'>❌ Lỗi TRUNCATE bảng {$table}: " . $wpdb->last_error . "</p>";
        // Fallback to DELETE if TRUNCATE fails (e.g., due to permissions)
        $wpdb->query("DELETE FROM {$table}");
        echo "<p style='color:orange;'>⚠️ Đã dùng lệnh DELETE FROM thay thế cho {$table}.</p>";
    } else {
        echo "<p style='color:green;'>✅ Đã làm trống hoàn toàn bảng: {$table}</p>";
    }
}

// 3. Bật lại kiểm tra khóa ngoại
$wpdb->query("SET FOREIGN_KEY_CHECKS = 1;");

// KHÔNG XÓA WP_OPTIONS NỮA ĐỂ GIỮ LẠI TIMELAPSE CỦA ADMIN
echo "<p style='color:green;'>✅ Đã giữ lại cấu hình Camera EZVIZ và Timelapse trong cài đặt hệ thống (wp_options).</p>";

echo "<h3>HOÀN TẤT XÓA DỮ LIỆU!</h3>";
echo "<b>Bây giờ bạn hãy F5 lại trang quản lý kho Rack và trang Dashboard. Tất cả đã trắng tinh và biến mất hoàn toàn!</b>";
