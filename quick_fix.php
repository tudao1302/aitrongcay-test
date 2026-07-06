<?php
/**
 * THUỐC GIẢI CẤP TỐC (QUICK FIX)
 * Chạy file này để khôi phục web lập tức nếu bị mã độc làm trắng trang.
 */
header('Content-Type: text/plain; charset=utf-8');
echo "=== ĐANG KHÔI PHỤC WEBSITE CẤP TỐC ===\n\n";

$dir = __DIR__;

// 1. Phục hồi index.php về nguyên trạng gốc
$index_path = $dir . '/index.php';
$clean_index = "<?php\n/**\n * Front to the WordPress application.\n *\n * @package WordPress\n */\ndefine( 'WP_USE_THEMES', true );\nrequire __DIR__ . '/wp-blog-header.php';\n";
file_put_contents($index_path, $clean_index);
echo "✅ Đã phục hồi và làm sạch: index.php\n";

// 2. Dọn dẹp mã độc chèn lén vào wp-settings.php
$settings_path = $dir . '/wp-settings.php';
if (file_exists($settings_path)) {
    $content = file_get_contents($settings_path);
    $original = $content;
    
    // Tự động cắt bỏ các đoạn mã độc dạng eval(), include() hay zlib thường bị chèn ở ngay sau <?php
    $content = preg_replace('/<\?php\s+(eval|@include|require_once|require)\s*\([^;]+;\s*/i', "<?php\n", $content);
    $content = preg_replace('/(eval|@include|require_once|require)\s*\([^;]+compress\.zlib[^;]+;\s*/i', "", $content);
    
    if ($content !== $original) {
        file_put_contents($settings_path, $content);
        echo "✅ Đã phẫu thuật cắt bỏ mã độc khỏi: wp-settings.php\n";
    } else {
        echo "✅ File wp-settings.php hiện không bị nhiễm.\n";
    }
}

// 3. Xóa các file .gz nén rác (nếu mã độc có sinh ra)
$gz_files = glob($dir . '/*.gz');
if (is_array($gz_files) && count($gz_files) > 0) {
    foreach ($gz_files as $gz) {
        unlink($gz);
        echo "✅ Đã dọn rác: " . basename($gz) . "\n";
    }
}

echo "\n=== HOÀN TẤT! WEBSITE ĐÃ SỐNG LẠI. BẠN CÓ THỂ TẢI LẠI TRANG! ===";
