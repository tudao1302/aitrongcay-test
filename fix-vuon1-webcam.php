<?php
/**
 * fix-vuon1-webcam.php
 * Khôi phục webcam_url của vuon1 vào WP option
 * Truy cập MỘT LẦN: http://localhost/aitrongcay/fix-vuon1-webcam.php
 * Sau đó XÓA file này ngay lập tức
 */
require_once __DIR__ . '/wp-load.php';
header('Content-Type: text/plain; charset=utf-8');

// Garden option key cần sửa
$option_name = 'aitrongcay_rack_cfg_garden:a5bb69bedc403485a0f61f7ea22dd505';

// Webcam URL của vuon1 (đọc từ go2rtc đang chạy)
$vuon1_webcam_url = 'http://127.0.0.1:1984/stream.html?src=vuon1';

$current = get_option($option_name);
if (!is_array($current)) {
    echo "ERROR: Option '$option_name' không tồn tại hoặc không phải array\n";
    exit;
}

echo "=== TRƯỚC KHI SỬA ===\n";
foreach ($current as $rack_idx => $rack) {
    foreach ((array)($rack['trays'] ?? []) as $ti => $tray) {
        $w = trim((string)($tray['webcam_url'] ?? ''));
        echo "rack[$rack_idx] tray[$ti]: webcam_url=" . ($w ?: '(empty)') . "\n";
    }
}

// Xác định rack nào chứa vuon1 (tray[0] của rack đầu tiên, dựa vào cấu hình)
// Rack số 2 (rack_idx=1) là rack chứa nhiều tray - thường là rack hydro lớn
// Cần đặt webcam cho rack[0] tray[0] nếu đó là khoang 1
// Từ log cũ: vuon1 thuộc rack[0] tray[0] của vườn này

// Kiểm tra: option 'aitrongcay_rack_monitor_configs' có vuon1 ở rack[0] tray[0]
$monitor = get_option('aitrongcay_rack_monitor_configs');
echo "\n=== aitrongcay_rack_monitor_configs (global) ===\n";
if (is_array($monitor)) {
    foreach ($monitor as $ri => $rack) {
        foreach ((array)($rack['trays'] ?? []) as $ti => $tray) {
            echo "rack[$ri] tray[$ti]: webcam=" . ($tray['webcam_url'] ?? '(empty)') . "\n";
        }
    }
}

// SET webcam_url cho tất cả rack/tray đang trống mà match với vuon1
// Theo ảnh screenshot: Khoang 1 là camera vuon1
// Tìm tray đầu tiên có tên Khoang 1 hoặc tray[0] của rack[0] (rack số 1)
$modified = false;
// rack[0] là "Rack số 1", tray[0] = Khoang 1 → đặt vuon1
if (isset($current[0]['trays'][0])) {
    $before = $current[0]['trays'][0]['webcam_url'] ?? '';
    if (trim($before) === '') {
        $current[0]['trays'][0]['webcam_url'] = $vuon1_webcam_url;
        $modified = true;
        echo "\n→ Đặt rack[0] tray[0] webcam_url = $vuon1_webcam_url\n";
    } else {
        echo "\nrack[0] tray[0] đã có webcam_url='$before', bỏ qua\n";
    }
}

if ($modified) {
    $result = update_option($option_name, $current, false);
    if ($result) {
        echo "\n✅ Đã cập nhật option '$option_name' thành công!\n";
    } else {
        echo "\n⚠️ update_option() trả về false (có thể không thay đổi gì hoặc bị lỗi)\n";
    }
} else {
    echo "\n⚠️ Không có thay đổi nào được thực hiện\n";
}

// Verify
echo "\n=== SAU KHI SỬA ===\n";
$verify = get_option($option_name);
if (is_array($verify)) {
    foreach ($verify as $rack_idx => $rack) {
        foreach ((array)($rack['trays'] ?? []) as $ti => $tray) {
            $w = trim((string)($tray['webcam_url'] ?? ''));
            echo "rack[$rack_idx] tray[$ti]: webcam_url=" . ($w ?: '(empty)') . "\n";
        }
    }
}

echo "\nXONG. Hãy xóa file này ngay!\n";
