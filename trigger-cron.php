<?php
require_once __DIR__ . '/wp-load.php';

echo "<h1>Đang chạy Bot tính toán tiêu hao ngầm (Giả lập nửa đêm)...</h1>";

if (function_exists('aitrongcay_generate_daily_pump_report')) {
    aitrongcay_generate_daily_pump_report();
    echo "<p style='color:green;'>✅ Đã tính toán xong và lưu vào Database (aitr_garden_reports)!</p>";
    echo "<p>Bây giờ bạn hãy quay lại trang Hydration và F5 để xem kết quả Biểu đồ nhé!</p>";
} else {
    echo "<p style='color:red;'>❌ Lỗi: Không tìm thấy hàm aitrongcay_generate_daily_pump_report</p>";
}
