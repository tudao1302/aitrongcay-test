<?php
/**
 * KHÓA BẢO MẬT & QUÉT SÂU WORDPRESS CRON
 */
header('Content-Type: text/plain; charset=utf-8');
echo "=== CHỐNG TÁI NHIỄM MÃ ĐỘC ===\n\n";

$dir = __DIR__;
require_once $dir . '/wp-config.php';

// 1. KHÓA CHẾT INDEX.PHP (Chặn quyền ghi)
$index_path = $dir . '/index.php';
if (file_exists($index_path)) {
    // Chuyển file về Read-Only (444) để malware không thể sửa
    if (chmod($index_path, 0444)) {
        echo "✅ ĐÃ KHÓA index.php (Quyền 444) - Malware không thể ghi đè nữa!\n";
    } else {
        echo "⚠️ Không thể tự động khóa index.php. Hãy vào cPanel đổi Permission file index.php thành 444.\n";
    }
}

// 2. KIỂM TRA WORDPRESS CRON (Mã độc thường hẹn giờ chạy lại)
echo "\n=== QUÉT WORDPRESS CRON ===\n";
$crons = get_option('cron');
if (is_array($crons)) {
    $found_suspicious = false;
    foreach ($crons as $timestamp => $cronhooks) {
        if (!is_numeric($timestamp)) continue;
        foreach ($cronhooks as $hook => $keys) {
            // Danh sách hook an toàn cơ bản
            $safe_hooks = ['wp_version_check', 'wp_update_plugins', 'wp_update_themes', 'wp_scheduled_delete', 'wp_scheduled_auto_draft_delete', 'recovery_mode_clean_expired_keys', 'wp_site_health_scheduled_check'];
            
            if (!in_array($hook, $safe_hooks) && !strpos($hook, 'woocommerce') && !strpos($hook, 'elementor')) {
                echo "Nghi vấn Cron Hook lạ: $hook\n";
                // Xóa thử nếu hook trông quá khả nghi (chuỗi ngẫu nhiên, v.v.)
                if (preg_match('/^[a-z0-9]{10,}$/i', $hook) || strpos($hook, 'eval') !== false || strpos($hook, 'shell') !== false) {
                    echo "   🚨 ĐÂY RẤT CÓ THỂ LÀ CRON CỦA MÃ ĐỘC! Đang xóa...\n";
                    wp_clear_scheduled_hook($hook);
                    $found_suspicious = true;
                }
            }
        }
    }
    if (!$found_suspicious) {
        echo "✅ Không tìm thấy Cron lạ tự động kích hoạt.\n";
    }
}

// 3. TÌM FILE PHP BỊ SỬA TRONG 3 NGÀY GẦN NHẤT
echo "\n=== CÁC FILE BỊ SỬA GẦN ĐÂY (Nơi ẩn nấp của mã độc) ===\n";
$cmd = "find " . escapeshellarg($dir) . " -name '*.php' -mtime -3 -not -path '*/.git/*' -not -name 'clean_malware.php' -not -name 'deep_scan.php' -not -name 'debug_live.php' | head -n 20";
$recent_files = shell_exec($cmd);
if ($recent_files) {
    echo $recent_files;
    echo "\n(Nếu thấy file nào tên lạ hoặc nằm trong wp-includes/ mà bị sửa gần đây, đó chính là ổ dịch!)\n";
} else {
    echo "Không tìm thấy file PHP nào bị sửa gần đây (Hoặc lệnh find bị vô hiệu hóa).\n";
}

echo "\n=== HOÀN TẤT BẢO VỆ ===";
