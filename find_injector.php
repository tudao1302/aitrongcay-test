<?php
/**
 * TÌM KIẾM TÁC NHÂN GÂY NHIỄM MÃ ĐỘC LẠI
 */
header('Content-Type: text/plain; charset=utf-8');
echo "=== TÌM TÁC NHÂN LÂY NHIỄM ===\n\n";

$dir = __DIR__;

$core_files = [
    'wp-config.php',
    'wp-settings.php',
    'wp-blog-header.php',
    'wp-load.php',
    'wp-login.php',
    'wp-includes/load.php',
    'wp-includes/default-constants.php',
    'wp-includes/plugin.php'
];

echo "1. KIỂM TRA ĐẦU FILE (Thường mã độc giấu ở ngay sau <?php)\n";
foreach ($core_files as $file) {
    $path = $dir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Cắt 200 ký tự đầu tiên
        $start = substr($content, 0, 200);
        
        // Kiểm tra dấu hiệu lạ (ví dụ: chuỗi base64 dài, các mảng lạ, @include)
        if (preg_match('/<\?php\s+(/\*.*?\*/|@?error_reporting|@?ini_set|@?include|\\$[a-zA-Z0-9_]+\s*=)/s', $start, $matches)) {
            // Loại trừ wp-config.php có thể có khai báo biến hợp lệ, nhưng thường malware sẽ có dòng rất dài
            $first_line = strtok($content, "\n");
            $second_line = strtok("\n");
            
            if (strlen($first_line) > 100 || strlen($second_line) > 100) {
                echo "⚠️ PHÁT HIỆN NGHI VẤN trong: $file\n";
                echo "   Dòng đầu quá dài hoặc chứa mã lạ:\n   " . substr($first_line . $second_line, 0, 100) . "...\n\n";
                
                // Tự động clean nếu thấy pattern của malware (<?php /*id*/ ... /*/id*/)
                if (preg_match('/^<\?php\s+\/\*[a-z0-9]+\*\/.*?\/\*[a-z0-9]+\*\//s', $content, $m)) {
                    $clean = str_replace($m[0], "<?php\n", $content);
                    file_put_contents($path, $clean);
                    echo "   ✅ Đã tự động cắt bỏ đoạn mã độc dài ở đầu file này!\n\n";
                }
            }
        }
    }
}

echo "2. QUÉT TÌM HÀM GHI FILE (file_put_contents vào index.php)\n";
// Chạy lệnh grep trên server nếu có thể
$output = [];
exec("grep -rnw '$dir' -e 'index.php' | grep 'file_put_contents'", $output);
if (!empty($output)) {
    foreach ($output as $line) {
        // bỏ qua các file log hoặc cache
        if (strpos($line, 'error_log') === false && strpos($line, 'clean_malware') === false && strpos($line, 'find_injector') === false) {
            echo "🚨 Phát hiện injector: $line\n";
        }
    }
} else {
    echo "Không tìm thấy injector bằng lệnh grep cơ bản.\n";
}

echo "\n3. KIỂM TRA LẠI index.php NGAY LÚC NÀY\n";
$index = file_get_contents($dir . '/index.php');
if (strpos($index, 'compress.zlib') !== false || strlen($index) > 500) {
    echo "❌ index.php LẠI BỊ NHIỄM SAU KHI VỪA CLEAN!\n";
    echo "   Dòng cuối của index.php hiện tại là:\n   ";
    $lines = explode("\n", trim($index));
    echo end($lines) . "\n";
} else {
    echo "✅ index.php hiện đang SẠCH (Chưa bị nhiễm lại vào lúc này).\n";
}

echo "\n=== HOÀN TẤT KIỂM TRA ===";
