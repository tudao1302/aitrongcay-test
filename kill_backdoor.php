<?php
/**
 * MÁY QUÉT VÀ TIÊU DIỆT BACKDOOR HẠNG NẶNG
 */
header('Content-Type: text/plain; charset=utf-8');
echo "=== ĐANG QUÉT TOÀN BỘ WEBSITE ĐỂ TÌM FILE MẸ ===\n\n";

$dir = __DIR__;

// 1. Phục hồi index.php ngay lập tức để web chạy lại
$index_path = $dir . '/index.php';
$clean_index = "<?php\n/**\n * Front to the WordPress application.\n *\n * @package WordPress\n */\ndefine( 'WP_USE_THEMES', true );\nrequire __DIR__ . '/wp-blog-header.php';\n";
file_put_contents($index_path, $clean_index);
echo "✅ Đã dọn dẹp lại index.php (Tạm thời cứu sống web).\n\n";

// 2. Định nghĩa các mẫu nhận diện mã độc nguy hiểm
$bad_patterns = [
    '/eval\s*\(\s*base64_decode\s*\(/i',
    '/eval\s*\(\s*gzinflate\s*\(/i',
    '/@?include\s*["\']\\\0/i', // Mã hóa đường dẫn kiểu \057home\...
    '/chmod\s*\(\s*[\'"].*?index\.php[\'"]\s*,\s*0?[67]/i', // Lệnh tự động unlock index.php
    '/file_put_contents\s*\(\s*.*index\.php/i', // Lệnh tự động ghi đè index.php
    '/str_rot13\s*\(\s*pack\s*\(/i',
    '/\$GLOBALS\[.*?\]\s*=\s*\$[a-zA-Z0-9_]+;/i' // Biến đổi globals (thường thấy ở file wp-settings.php bị nhiễm)
];

// Các file nghi ngờ nhất (WordPress Core)
$suspects = [
    'wp-config.php',
    'wp-settings.php',
    'wp-load.php',
    'wp-blog-header.php',
    'wp-includes/load.php',
    'wp-includes/plugin.php',
    'wp-includes/functions.php',
    'wp-includes/option.php',
    'wp-admin/index.php',
    'wp-login.php'
];

$found = false;

echo ">>> ĐANG QUÉT CÁC FILE LÕI...\n";
foreach ($suspects as $file) {
    $path = $dir . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Quét pattern
        foreach ($bad_patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                echo "🚨 [BÁO ĐỘNG ĐỎ] Phát hiện mã độc trong file: $file\n";
                echo "   -> Mã độc khớp với mẫu nhận diện nguy hiểm.\n";
                $found = true;
                
                // Đổi tên file để vô hiệu hóa nó ngay lập tức (nếu không phải file lõi quá quan trọng)
                if ($file !== 'wp-config.php' && $file !== 'wp-settings.php') {
                    rename($path, $path . '.infected');
                    echo "   -> Đã vô hiệu hóa file này thành .infected\n";
                } else {
                    echo "   -> (ĐÂY LÀ FILE QUAN TRỌNG: Cần dọn dẹp thủ công bằng tay!)\n";
                }
                break;
            }
        }
    }
}

// 3. Tìm các file PHP ẩn trong thư mục Uploads (Nơi hacker hay giấu backdoor nhất)
echo "\n>>> ĐANG QUÉT THƯ MỤC UPLOADS (NƠI KHÔNG ĐƯỢC PHÉP CHỨA FILE PHP)...\n";
$upload_dir = $dir . '/wp-content/uploads';
if (file_exists($upload_dir)) {
    $cmd = "find " . escapeshellarg($upload_dir) . " -name '*.php'";
    $upload_phps = shell_exec($cmd);
    if (!empty(trim((string)$upload_phps))) {
        echo "🚨 Phát hiện file PHP giấu trong thư mục ảnh (Chắc chắn 100% là backdoor):\n";
        echo $upload_phps;
        $lines = explode("\n", trim((string)$upload_phps));
        foreach ($lines as $l) {
            if (file_exists($l)) {
                rename($l, $l . '.infected');
                echo "   -> Đã vô hiệu hóa: $l\n";
                $found = true;
            }
        }
    } else {
        echo "✅ Thư mục Uploads sạch sẽ.\n";
    }
}

if (!$found) {
    echo "\n⚠️ Máy quét chưa tìm thấy file mẹ bằng các mẫu phổ biến. Mã độc có thể được giấu trong Theme hoặc Plugin.\n";
}

echo "\n=== QUÉT HOÀN TẤT ===\n";
