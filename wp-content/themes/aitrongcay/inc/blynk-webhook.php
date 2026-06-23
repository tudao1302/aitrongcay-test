<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Tạo bảng Database lưu lịch sử IoT từ Blynk (nếu chưa có)
 */
function aitrongcay_blynk_create_log_table(): void
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_device_logs';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        device_token varchar(255) NOT NULL,
        pin varchar(50) NOT NULL,
        value varchar(255) NOT NULL,
        logged_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY device_token (device_token),
        KEY pin (pin),
        KEY logged_at (logged_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Bảng 2: Bảng Thống kê Tiêu hao hàng ngày (Task 1.2)
    $reports_table = $wpdb->prefix . 'aitr_garden_reports';
    $sql_reports = "CREATE TABLE $reports_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        device_token varchar(255) NOT NULL,
        report_date date NOT NULL,
        total_pump_minutes int(11) DEFAULT 0 NOT NULL,
        water_consumed_liters float DEFAULT 0 NOT NULL,
        power_consumed_kwh float DEFAULT 0 NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY token_date (device_token, report_date)
    ) $charset_collate;";
    
    dbDelta($sql_reports);
}
// Chạy hàm tạo bảng mỗi khi khởi tạo (Hoặc có thể chạy 1 lần khi kích hoạt theme)
add_action('init', 'aitrongcay_blynk_create_log_table');

/**
 * Đăng ký đường dẫn Webhook API
 */
function aitrongcay_blynk_register_webhook_route(): void
{
    register_rest_route('aitrongcay/v1', '/webhook/blynk', [
        'methods'             => ['GET', 'POST'],
        'callback'            => 'aitrongcay_blynk_webhook_handler',
        'permission_callback' => '__return_true', // Webhook mở public để Blynk bắn vào
    ]);
}
add_action('rest_api_init', 'aitrongcay_blynk_register_webhook_route');

/**
 * Xử lý dữ liệu Blynk bắn về
 */
function aitrongcay_blynk_webhook_handler(WP_REST_Request $request): WP_REST_Response
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_device_logs';

    // Lấy params (hỗ trợ cả GET và POST từ Blynk Webhook)
    $token = sanitize_text_field((string) $request->get_param('token'));
    $pin   = sanitize_text_field((string) $request->get_param('pin'));
    $value = sanitize_text_field((string) $request->get_param('value'));

    if ($token === '' || $pin === '') {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Missing token or pin'], 400);
    }

    // Task 1.1: Bảo mật cổng Webhook bằng Secret Key
    $provided_secret = sanitize_text_field((string) $request->get_param('secret'));
    $expected_secret = get_option('aitrongcay_blynk_webhook_secret', 'aitrongcay_secured_2026'); // Mã bảo mật mặc định
    
    if ($provided_secret !== $expected_secret) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Unauthorized: Invalid or missing secret key'], 401);
    }

    // Ghi log vào Database
    $inserted = $wpdb->insert(
        $table_name,
        [
            'device_token' => $token,
            'pin'          => $pin,
            'value'        => $value,
            'logged_at'    => current_time('mysql', true)
        ],
        ['%s', '%s', '%s', '%s']
    );

    if (!$inserted) {
        return new WP_REST_Response(['status' => 'error', 'message' => 'Database error'], 500);
    }

    return new WP_REST_Response([
        'status' => 'success',
        'message' => 'Log saved successfully',
        'data' => [
            'pin' => $pin,
            'value' => $value
        ]
    ], 200);
}

/**
 * Hàm hỗ trợ: Lấy lịch sử bơm gần đây của một thiết bị (Token)
 */
function aitrongcay_blynk_get_pump_history(string $token, string $pin = 'V1', int $limit = 50): array
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'aitr_device_logs';
    
    // Nếu bảng chưa tồn tại thì trả về rỗng
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return [];
    }

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE device_token = %s AND pin = %s ORDER BY logged_at DESC LIMIT %d",
            $token,
            $pin,
            $limit
        ),
        ARRAY_A
    );

    return $results ?? [];
}

/**
 * Task 1.3: Viết Cronjob tính toán lượng tiêu thụ ngầm lúc nửa đêm
 */
// Lên lịch cronjob chạy hàng ngày lúc 23:55 (hoặc 11:55 PM)
if (!wp_next_scheduled('aitrongcay_daily_pump_report_cron')) {
    // Chạy vào lúc gần nửa đêm giờ địa phương
    $local_midnight = strtotime('tomorrow -5 minutes', current_time('timestamp'));
    wp_schedule_event($local_midnight, 'daily', 'aitrongcay_daily_pump_report_cron');
}

add_action('aitrongcay_daily_pump_report_cron', 'aitrongcay_generate_daily_pump_report');

function aitrongcay_generate_daily_pump_report(): void
{
    global $wpdb;
    $log_table = $wpdb->prefix . 'aitr_device_logs';
    $report_table = $wpdb->prefix . 'aitr_garden_reports';

    // Tính cho ngày hôm nay
    $today = current_time('Y-m-d');

    // Lấy danh sách các token có log trong ngày hôm nay cho chân bơm V1
    $tokens = $wpdb->get_col(
        $wpdb->prepare("SELECT DISTINCT device_token FROM $log_table WHERE DATE(logged_at) = %s AND pin = 'V1'", $today)
    );

    foreach ($tokens as $token) {
        $logs = $wpdb->get_results(
            $wpdb->prepare("SELECT value, logged_at FROM $log_table WHERE device_token = %s AND pin = 'V1' AND DATE(logged_at) = %s ORDER BY logged_at ASC", $token, $today),
            ARRAY_A
        );

        $total_seconds = 0;
        $last_on_time = null;

        foreach ($logs as $log) {
            $val = strtolower((string)$log['value']);
            $is_on = ($val === '1' || $val === 'on');
            $time = strtotime($log['logged_at']);

            if ($is_on) {
                if ($last_on_time === null) {
                    $last_on_time = $time; // Bắt đầu tính giờ
                }
            } else {
                if ($last_on_time !== null) {
                    $total_seconds += ($time - $last_on_time); // Cộng dồn thời gian chạy
                    $last_on_time = null;
                }
            }
        }
        
        // Nếu bơm đang bật vắt ngang qua lúc tính toán
        if ($last_on_time !== null) {
             $total_seconds += (current_time('timestamp') - $last_on_time);
        }

        $total_minutes = (int) round($total_seconds / 60);

        // Công suất giả định: Bơm 2 lít/phút, tốn 0.005 kWh/phút (ví dụ máy bơm 300W)
        // Lưu ý: Sau này có thể lấy từ cấu hình Rack
        $liters_per_min = 2.0;
        $kwh_per_min = 300 / 60 / 1000; // 300W

        $water = $total_minutes * $liters_per_min;
        $power = $total_minutes * $kwh_per_min;

        $wpdb->replace(
            $report_table,
            [
                'device_token' => $token,
                'report_date' => $today,
                'total_pump_minutes' => $total_minutes,
                'water_consumed_liters' => $water,
                'power_consumed_kwh' => $power,
            ],
            ['%s', '%s', '%d', '%f', '%f']
        );
    }
}

/**
 * API Lấy lịch sử Real-time cho Frontend UI
 */
add_action('rest_api_init', function () {
    register_rest_route('aitrongcay/v1', '/pump-history', [
        'methods' => 'GET',
        'callback' => function (WP_REST_Request $request) {
            $token = sanitize_text_field((string) $request->get_param('token'));
            $pin = sanitize_text_field((string) ($request->get_param('pin') ?? 'V1'));
            if (empty($token)) {
                return new WP_REST_Response(['error' => 'Missing token'], 400);
            }
            $logs = aitrongcay_blynk_get_pump_history($token, $pin, 20);
            return rest_ensure_response($logs);
        },
        'permission_callback' => '__return_true' // Cho phép Frontend gọi (có thể thêm check nonce nếu cần bảo mật cao hơn)
    ]);
});
