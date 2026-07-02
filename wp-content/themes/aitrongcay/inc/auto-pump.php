<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// ═══════════════════════════════════════════════════════════════════════════════
// DB TABLE: wp_aitr_pump_log
// ═══════════════════════════════════════════════════════════════════════════════

function aitrongcay_install_pump_log_table(): void
{
    global $wpdb;
    $t   = $wpdb->prefix . 'aitr_pump_log';
    $ch  = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$t} (
        id           bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        garden_key   varchar(100)        NOT NULL,
        triggered_by varchar(20)         NOT NULL DEFAULT 'auto',
        soil_before  float               DEFAULT NULL,
        soil_after   float               DEFAULT NULL,
        duration_sec int unsigned        DEFAULT NULL,
        pump_on_at   datetime            NOT NULL,
        pump_off_at  datetime            DEFAULT NULL,
        status       varchar(20)         NOT NULL DEFAULT 'started',
        note         text                DEFAULT NULL,
        PRIMARY KEY (id),
        KEY gk  (garden_key),
        KEY ton (pump_on_at)
    ) ENGINE=InnoDB {$ch};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
add_action('after_switch_theme', 'aitrongcay_install_pump_log_table');

// ═══════════════════════════════════════════════════════════════════════════════
// PUMP RULES (lưu trong WP option per garden_key)
// ═══════════════════════════════════════════════════════════════════════════════

function aitrongcay_pump_rules_default(): array
{
    return [
        'enabled'    => false,
        'time_on'    => 10,       // Phút TIME ON
        'time_off'   => 5,        // Phút TIME OFF
        'time_start' => '06:00',  // Khung giờ hoạt động
        'time_end'   => '22:00',
        'days'       => [0, 1, 2, 3, 4, 5, 6],
    ];
}

function aitrongcay_get_pump_rules(string $garden_key): array
{
    $raw = get_option('aitrongcay_pump_rules_' . sanitize_key($garden_key), []);
    return array_merge(aitrongcay_pump_rules_default(), is_array($raw) ? $raw : []);
}

function aitrongcay_save_pump_rules(string $garden_key, array $r): bool
{
    $d     = aitrongcay_pump_rules_default();
    $clean = [
        'enabled'    => (bool) ($r['enabled'] ?? false),
        'time_on'    => max(1, min(120,  (int)   ($r['time_on']  ?? $d['time_on']))),
        'time_off'   => max(1, min(1440, (int)   ($r['time_off'] ?? $d['time_off']))),
        'time_start' => sanitize_text_field((string) ($r['time_start'] ?? $d['time_start'])),
        'time_end'   => sanitize_text_field((string) ($r['time_end']   ?? $d['time_end'])),
        'days'       => array_values(array_unique(array_filter(
            array_map('intval', (array) ($r['days'] ?? $d['days'])),
            static fn(int $v) => $v >= 0 && $v <= 6
        ))),
    ];
    return update_option('aitrongcay_pump_rules_' . sanitize_key($garden_key), $clean, false);
}

// ═══════════════════════════════════════════════════════════════════════════════
// PUMP LOG CRUD
// ═══════════════════════════════════════════════════════════════════════════════

function aitrongcay_pump_log_start(string $garden_key, string $by, ?float $soil): int
{
    global $wpdb;
    $data  = [
        'garden_key'   => $garden_key,
        'triggered_by' => $by,
        'pump_on_at'   => current_time('mysql'),
        'status'       => 'started',
    ];
    $fmts  = ['%s', '%s', '%s', '%s'];
    if ($soil !== null) {
        $data['soil_before'] = $soil;
        $fmts[]              = '%f';
    }
    $wpdb->insert($wpdb->prefix . 'aitr_pump_log', $data, $fmts);
    return (int) $wpdb->insert_id;
}

function aitrongcay_pump_log_end(int $id, ?float $soil_after, int $duration_sec, string $status = 'completed', string $note = ''): void
{
    global $wpdb;
    $data  = [
        'duration_sec' => $duration_sec,
        'pump_off_at'  => current_time('mysql'),
        'status'       => $status,
    ];
    $fmts  = ['%d', '%s', '%s'];
    if ($soil_after !== null) {
        $data['soil_after'] = $soil_after;
        $fmts[]             = '%f';
    }
    if ($note !== '') {
        $data['note'] = $note;
        $fmts[]       = '%s';
    }
    $wpdb->update($wpdb->prefix . 'aitr_pump_log', $data, ['id' => $id], $fmts, ['%d']);
}

function aitrongcay_pump_get_logs(string $garden_key, int $limit = 30): array
{
    global $wpdb;
    return (array) $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}aitr_pump_log
             WHERE garden_key = %s ORDER BY pump_on_at DESC LIMIT %d",
            $garden_key,
            $limit
        ),
        ARRAY_A
    );
}

function aitrongcay_pump_last_completed_at(string $garden_key): ?string
{
    global $wpdb;
    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT pump_off_at FROM {$wpdb->prefix}aitr_pump_log
             WHERE garden_key = %s AND status = 'completed'
             ORDER BY pump_off_at DESC LIMIT 1",
            $garden_key
        )
    );
}

// ═══════════════════════════════════════════════════════════════════════════════
// SENSOR READ — thống nhất cho cả 2 model (advanced + tray)
// ═══════════════════════════════════════════════════════════════════════════════

function aitrongcay_pump_read_soil(string $garden_key): ?float
{
    // Advanced (device-mapping) model
    $cfg_fn = function_exists('aitrongcay_blynk_runtime_config')
        ? 'aitrongcay_blynk_runtime_config'
        : (function_exists('aitrongcay_blynk_config') ? 'aitrongcay_blynk_config' : null);

    if ($cfg_fn !== null && function_exists('aitrongcay_blynk_read_values')) {
        $cfg   = $cfg_fn($garden_key);
        $token = trim((string) ($cfg['token'] ?? ''));
        $vpin  = strtoupper(trim((string) ($cfg['vpins']['soil'] ?? '')));
        $base  = (string) ($cfg['base'] ?? 'https://blynk.cloud/external/api');
        if ($token !== '' && $vpin !== '') {
            $d = aitrongcay_blynk_read_values($token, [$vpin], $base);
            if (isset($d[$vpin]) && is_numeric($d[$vpin])) {
                return (float) $d[$vpin];
            }
        }
    }

    // Tray model fallback
    if (function_exists('aitrongcay_get_rack_monitor_configs') && function_exists('aitrongcay_blynk_read_values')) {
        foreach (aitrongcay_get_rack_monitor_configs($garden_key) as $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $tray) {
                $tk = trim((string) ($tray['blynk_token'] ?? ''));
                $vp = strtoupper(trim((string) ($tray['vpin_soil'] ?? 'V2')));
                $bk = trim((string) ($tray['blynk_base'] ?? 'https://blynk.cloud/external/api'));
                if ($tk === '') {
                    continue;
                }
                $d = aitrongcay_blynk_read_values($tk, [$vp], $bk);
                if (isset($d[$vp]) && is_numeric($d[$vp])) {
                    return (float) $d[$vp];
                }
            }
        }
    }

    return null;
}

// ═══════════════════════════════════════════════════════════════════════════════
// PUMP SWITCH — thống nhất cho cả 2 model
// ═══════════════════════════════════════════════════════════════════════════════

function aitrongcay_pump_do_switch(string $garden_key, int $state): bool
{
    $state = $state !== 0 ? 1 : 0;

    // Advanced model
    if (function_exists('aitrongcay_blynk_send_control')) {
        $r = aitrongcay_blynk_send_control('pump', $state, $garden_key);
        if ($r === true) {
            return true;
        }
    }

    // Tray model fallback
    if (function_exists('aitrongcay_get_rack_monitor_configs') && function_exists('aitrongcay_tray_write_device')) {
        foreach (aitrongcay_get_rack_monitor_configs($garden_key) as $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $tray) {
                $vp = strtoupper(trim((string) ($tray['vpin_pump'] ?? 'V5')));
                if (aitrongcay_tray_write_device($tray, $vp, $state)) {
                    return true;
                }
            }
        }
    }

    return false;
}

// ═══════════════════════════════════════════════════════════════════════════════
// AUTO-PUMP CORE LOGIC
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Kiểm tra và kích hoạt bơm tự động cho 1 vườn.
 * Gọi mỗi 5 phút bởi WP cron.
 *
 * Flow:
 *  1. Nếu bơm đang chạy → kiểm tra hết duration chưa → tắt nếu đủ giờ
 *  2. Nếu chưa bơm → kiểm tra cooldown + khung giờ + ngưỡng độ ẩm → bật bơm
 */
function aitrongcay_auto_pump_check(string $garden_key): void
{
    $rules = aitrongcay_get_pump_rules($garden_key);
    if (empty($rules['enabled'])) {
        return;
    }

    // Kiểm tra ngày trong tuần
    $dow = (int) wp_date('w');  // 0=CN … 6=T7
    if (! in_array($dow, (array) $rules['days'], true)) {
        return;
    }

    // Kiểm tra khung giờ
    $hm = (string) wp_date('H:i');
    if ($hm < ($rules['time_start'] ?? '06:00') || $hm >= ($rules['time_end'] ?? '22:00')) {
        return;
    }

    $run_key = 'aitr_pump_run_' . sanitize_key($garden_key);
    $running = get_transient($run_key);

    // ── Bơm đang chạy: kiểm tra hết thời gian chưa ──────────────────────────
    if (is_array($running)) {
        $elapsed  = time() - (int) ($running['started'] ?? 0);
        $duration = max(5, (int) $rules['pump_duration_sec']);
        if ($elapsed >= $duration) {
            aitrongcay_pump_do_switch($garden_key, 0);
            $soil_after = aitrongcay_pump_read_soil($garden_key);
            if ((int) ($running['log_id'] ?? 0) > 0) {
                aitrongcay_pump_log_end((int) $running['log_id'], $soil_after, $elapsed);
            }
            delete_transient($run_key);
        }
        // Dù đã tắt hay còn chạy đều không khởi chu kỳ mới trong lần tick này
        return;
    }

    // ── Kiểm tra cooldown ────────────────────────────────────────────────────
    $last = aitrongcay_pump_last_completed_at($garden_key);
    if ($last !== null) {
        $cooldown_sec = max(60, (int) $rules['cooldown_min'] * 60);
        if ((time() - (int) strtotime($last)) < $cooldown_sec) {
            return;
        }
    }

    // ── Đọc độ ẩm đất ───────────────────────────────────────────────────────
    $soil      = aitrongcay_pump_read_soil($garden_key);
    $threshold = (int) $rules['soil_threshold_low'];
    if ($soil === null || $soil >= $threshold) {
        return;
    }

    // ── Đủ điều kiện → BẬT BƠM ──────────────────────────────────────────────
    if (! aitrongcay_pump_do_switch($garden_key, 1)) {
        return;
    }

    $log_id = aitrongcay_pump_log_start($garden_key, 'auto', $soil);
    set_transient(
        $run_key,
        ['log_id' => $log_id, 'started' => time(), 'soil_before' => $soil],
        (int) $rules['pump_duration_sec'] + 300  // TTL = duration + 5 phút buffer
    );
}

/**
 * Cron tick: duyệt qua tất cả vườn có pump rules đã bật.
 */
function aitrongcay_auto_pump_cron_tick(): void
{
    global $wpdb;
    $rows = (array) $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options}
         WHERE option_name LIKE 'aitrongcay_pump_rules_%'"
    );
    foreach ($rows as $option_name) {
        $gk = str_replace('aitrongcay_pump_rules_', '', (string) $option_name);
        if ($gk !== '') {
            aitrongcay_auto_pump_check($gk);
        }
    }
}

// ═══════════════════════════════════════════════════════════════════════════════
// CRON SCHEDULE
// ═══════════════════════════════════════════════════════════════════════════════

add_filter('cron_schedules', static function (array $s): array {
    if (! isset($s['aitr_5min'])) {
        $s['aitr_5min'] = ['interval' => 300, 'display' => 'Mỗi 5 phút (AITR)'];
    }
    return $s;
});

add_action('aitrongcay_auto_pump_tick', 'aitrongcay_auto_pump_cron_tick');

add_action('init', static function (): void {
    if (! wp_next_scheduled('aitrongcay_auto_pump_tick')) {
        wp_schedule_event(time(), 'aitr_5min', 'aitrongcay_auto_pump_tick');
    }
});

// ═══════════════════════════════════════════════════════════════════════════════
// AJAX: LẤY LỊCH SỬ BƠM
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_aitrongcay_pump_log', static function (): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    $gk = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if (
        $gk === ''
        || ! function_exists('aitrongcay_user_can_view_garden')
        || ! aitrongcay_user_can_view_garden($gk, get_current_user_id())
    ) {
        wp_send_json_error(['message' => 'Không có quyền.'], 403);
    }
    $limit = max(1, min(100, (int) ($_POST['limit'] ?? 30)));
    wp_send_json_success(['logs' => aitrongcay_pump_get_logs($gk, $limit)]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// AJAX: LƯU CÀI ĐẶT BƠM (admin only)
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_aitrongcay_pump_rules_save', static function (): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Chỉ admin mới được cấu hình bơm tự động.'], 403);
    }
    $gk    = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    $rules = json_decode(stripslashes((string) ($_POST['rules'] ?? '{}')), true);
    if ($gk === '' || ! is_array($rules)) {
        wp_send_json_error(['message' => 'Dữ liệu không hợp lệ.'], 400);
    }
    aitrongcay_save_pump_rules($gk, $rules);
    
    // Đồng bộ ngay lập tức cấu hình Timer xuống mạch phần cứng thông qua Blynk API
    $blynk_token = '';
    $blynk_base  = 'https://blynk.cloud/external/api';
    
    $cfg_fn = function_exists('aitrongcay_blynk_runtime_config') ? 'aitrongcay_blynk_runtime_config' : (function_exists('aitrongcay_blynk_config') ? 'aitrongcay_blynk_config' : null);
    if ($cfg_fn !== null) {
        $cfg = $cfg_fn($gk);
        $blynk_token = trim((string) ($cfg['token'] ?? ''));
        if (!empty($cfg['base'])) {
            $blynk_base = rtrim(trim((string) $cfg['base']), '/');
        }
    }
    if ($blynk_token === '' && function_exists('aitrongcay_get_rack_monitor_configs')) {
        foreach (aitrongcay_get_rack_monitor_configs($gk) as $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $tray) {
                $tk = trim((string) ($tray['blynk_token'] ?? ''));
                if ($tk !== '') {
                    $blynk_token = $tk;
                    if (!empty($tray['blynk_base'])) {
                        $blynk_base = rtrim(trim((string) $tray['blynk_base']), '/');
                    }
                    break 2;
                }
            }
        }
    }

    $blynk_updated = false;
    $blynk_debug = [];
    if ($blynk_token !== '') {
        $v3  = !empty($rules['enabled']) ? 1 : 0;
        $v17 = (int) ($rules['time_on'] ?? 10);
        $v18 = (int) ($rules['time_off'] ?? 5);
        
        $urls = [
            'V3'  => "{$blynk_base}/update?token={$blynk_token}&V3={$v3}",
            'V17' => "{$blynk_base}/update?token={$blynk_token}&V17={$v17}",
            'V18' => "{$blynk_base}/update?token={$blynk_token}&V18={$v18}"
        ];
        
        $success_count = 0;
        foreach ($urls as $pin => $url) {
            $resp = wp_remote_get($url, ['timeout' => 3]);
            if (!is_wp_error($resp)) {
                $code = wp_remote_retrieve_response_code($resp);
                $body = wp_remote_retrieve_body($resp);
                $blynk_debug[$pin] = ['code' => $code, 'body' => $body];
                if ($code === 200) {
                    $success_count++;
                }
            } else {
                $blynk_debug[$pin] = ['error' => $resp->get_error_message()];
            }
        }
        
        if ($success_count > 0) {
            $blynk_updated = true;
        }
    }

    wp_send_json_success([
        'message'       => 'Đã lưu cấu hình bơm tự động.',
        'rules'         => aitrongcay_get_pump_rules($gk),
        'blynk_updated' => $blynk_updated,
        'blynk_debug'   => $blynk_debug
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════════
// AJAX: BẬT/TẮT BƠM THỦ CÔNG
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_aitrongcay_pump_manual', static function (): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    $gk    = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    $state = isset($_POST['state']) ? (int) $_POST['state'] : -1;

    if ($gk === '' || ! in_array($state, [0, 1], true)) {
        wp_send_json_error(['message' => 'Tham số không hợp lệ.'], 400);
    }
    if (
        ! function_exists('aitrongcay_user_can_control_garden')
        || ! aitrongcay_user_can_control_garden($gk, get_current_user_id())
    ) {
        wp_send_json_error(['message' => 'Không có quyền.'], 403);
    }

    $soil    = aitrongcay_pump_read_soil($gk);
    $ok      = aitrongcay_pump_do_switch($gk, $state);
    $run_key = 'aitr_pump_run_' . sanitize_key($gk);

    if (! $ok) {
        wp_send_json_error(['message' => 'Không thể gửi lệnh đến Blynk. Kiểm tra token.'], 500);
    }

    if ($state === 1) {
        $log_id = aitrongcay_pump_log_start($gk, 'manual', $soil);
        set_transient($run_key, ['log_id' => $log_id, 'started' => time(), 'soil_before' => $soil], 600);
        wp_send_json_success(['state' => 1, 'message' => 'Đã bật bơm.', 'soil' => $soil]);
    } else {
        $run_data = get_transient($run_key);
        if (is_array($run_data) && (int) ($run_data['log_id'] ?? 0) > 0) {
            $elapsed = max(0, time() - (int) ($run_data['started'] ?? time()));
            aitrongcay_pump_log_end((int) $run_data['log_id'], $soil, $elapsed);
        }
        delete_transient($run_key);
        wp_send_json_success(['state' => 0, 'message' => 'Đã tắt bơm.', 'soil' => $soil]);
    }
});

// ═══════════════════════════════════════════════════════════════════════════════
// AJAX: TRẠNG THÁI HIỆN TẠI (bơm đang chạy không + độ ẩm + cài đặt)
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_aitrongcay_pump_status', static function (): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    $gk = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if (
        $gk === ''
        || ! function_exists('aitrongcay_user_can_view_garden')
        || ! aitrongcay_user_can_view_garden($gk, get_current_user_id())
    ) {
        wp_send_json_error(['message' => 'Không có quyền.'], 403);
    }

    $running  = get_transient('aitr_pump_run_' . sanitize_key($gk));
    $is_admin = current_user_can('manage_options');

    $rules = aitrongcay_get_pump_rules($gk);

    $payload = [
        'is_running' => is_array($running),
        'soil'       => aitrongcay_pump_read_soil($gk),
        'last_pump'  => aitrongcay_pump_last_completed_at($gk),
        'soil_threshold_low' => (int) ($rules['soil_threshold_low'] ?? 40),
    ];
    if ($is_admin) {
        $payload['rules'] = $rules;
    }

    wp_send_json_success($payload);
});

// ═══════════════════════════════════════════════════════════════════════════════
// ADMIN PAGE: WP Admin → Appearance → Bơm tự động
// ═══════════════════════════════════════════════════════════════════════════════

add_action('admin_menu', static function (): void {
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Bơm tự động — Ai trồng cây',
        'Bơm tự động',
        'manage_options',
        'aitrongcay-auto-pump',
        'aitrongcay_auto_pump_admin_page'
    );
}, 100);

function aitrongcay_auto_pump_admin_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $notice   = '';
    $selected = sanitize_key((string) wp_unslash($_GET['garden_key'] ?? $_POST['garden_key'] ?? ''));

    // Xử lý form POST lưu rules
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ! empty($_POST['_wpnonce'])) {
        check_admin_referer('aitrongcay_pump_admin');
        $gk_post = sanitize_key((string) wp_unslash($_POST['garden_key'] ?? ''));
        if ($gk_post !== '') {
            aitrongcay_save_pump_rules($gk_post, [
                'enabled'            => ! empty($_POST['enabled']),
                'soil_threshold_low' => (int) ($_POST['soil_threshold_low'] ?? 40),
                'pump_duration_sec'  => (int) ($_POST['pump_duration_sec']  ?? 30),
                'cooldown_min'       => (int) ($_POST['cooldown_min']       ?? 15),
                'time_start'         => sanitize_text_field((string) wp_unslash($_POST['time_start'] ?? '06:00')),
                'time_end'           => sanitize_text_field((string) wp_unslash($_POST['time_end']   ?? '22:00')),
                'days'               => array_map('intval', (array) ($_POST['days'] ?? [])),
            ]);
            $notice   = 'Đã lưu cấu hình bơm tự động cho vườn <strong>' . esc_html($gk_post) . '</strong>.';
            $selected = $gk_post;
        }
    }

    // Thu thập danh sách garden keys
    global $wpdb;
    $rule_keys      = (array) $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_pump_rules_%'"
    );
    $rule_gardens   = array_map(static fn($n) => str_replace('aitrongcay_pump_rules_', '', $n), $rule_keys);
    $device_configs = (array) get_option('aitrongcay_garden_device_configs', []);
    $all_gardens    = array_values(array_unique(array_filter(array_merge(array_keys($device_configs), $rule_gardens))));
    sort($all_gardens);

    $rules = $selected !== '' ? aitrongcay_get_pump_rules($selected) : null;
    $logs  = ($selected !== '' && $rules !== null) ? aitrongcay_pump_get_logs($selected, 20) : [];
    $days  = ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'];

    // Kiểm tra cron đang được schedule chưa
    $next_cron = wp_next_scheduled('aitrongcay_auto_pump_tick');
    ?>
    <div class="wrap">
    <h1>⚡ Bơm tự động — Ai trồng cây</h1>

    <?php if ($notice !== ''): ?>
    <div class="notice notice-success is-dismissible"><p><?= $notice ?></p></div>
    <?php endif; ?>

    <!-- Cron status notice -->
    <div class="notice <?= $next_cron ? 'notice-info' : 'notice-warning' ?>">
    <p>
        <?php if ($next_cron): ?>
        ✅ WP Cron đã được lên lịch. Lần chạy kế tiếp: <strong><?= esc_html(wp_date('d/m/Y H:i:s', $next_cron)) ?></strong>
        <?php else: ?>
        ⚠️ WP Cron <strong>chưa được schedule</strong>. Nếu <code>DISABLE_WP_CRON=true</code>, hãy cấu hình cron hệ thống:
        <code>*/5 * * * * wget -q -O /dev/null "https://yourdomain.com/wp-cron.php?doing_wp_cron"</code>
        <?php endif; ?>
    </p>
    </div>

    <!-- Chọn vườn -->
    <form method="get" style="margin-bottom:20px">
        <input type="hidden" name="page" value="aitrongcay-auto-pump">
        <label><strong>Chọn vườn:</strong>
        <select name="garden_key" onchange="this.form.submit()" style="min-width:200px;margin-left:8px">
            <option value="">— Chọn vườn —</option>
            <?php foreach ($all_gardens as $gk): ?>
            <option value="<?= esc_attr($gk) ?>" <?= selected($selected, $gk, false) ?>>
                <?= esc_html($gk) ?>
            </option>
            <?php endforeach; ?>
        </select>
        </label>
    </form>

    <?php if ($rules !== null): ?>

    <!-- Form cấu hình rules -->
    <form method="post" style="max-width:640px">
    <?php wp_nonce_field('aitrongcay_pump_admin'); ?>
    <input type="hidden" name="garden_key" value="<?= esc_attr($selected) ?>">
    <h2 style="margin-top:0">Cấu hình bơm: <code><?= esc_html($selected) ?></code></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Kích hoạt bơm tự động</th>
            <td><label>
                <input type="checkbox" name="enabled" value="1" <?= checked((bool) $rules['enabled']) ?>>
                Bật tự động bơm theo độ ẩm
            </label></td>
        </tr>
        <tr>
            <th scope="row">Ngưỡng độ ẩm bật bơm (%)</th>
            <td>
                <input type="number" name="soil_threshold_low"
                       value="<?= esc_attr($rules['soil_threshold_low']) ?>"
                       min="0" max="100" class="small-text">
                <p class="description">Bơm BẬT khi độ ẩm đất <strong>dưới</strong> giá trị này.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Thời gian bơm mỗi chu kỳ (giây)</th>
            <td>
                <input type="number" name="pump_duration_sec"
                       value="<?= esc_attr($rules['pump_duration_sec']) ?>"
                       min="5" max="300" class="small-text">
                <p class="description">Tối thiểu 5s, tối đa 300s.</p>
            </td>
        </tr>
        <tr>
            <th scope="row">Nghỉ giữa 2 chu kỳ (phút)</th>
            <td>
                <input type="number" name="cooldown_min"
                       value="<?= esc_attr($rules['cooldown_min']) ?>"
                       min="1" max="1440" class="small-text">
            </td>
        </tr>
        <tr>
            <th scope="row">Khung giờ hoạt động</th>
            <td>
                <input type="time" name="time_start" value="<?= esc_attr($rules['time_start']) ?>">
                &nbsp;→&nbsp;
                <input type="time" name="time_end"   value="<?= esc_attr($rules['time_end']) ?>">
            </td>
        </tr>
        <tr>
            <th scope="row">Ngày trong tuần</th>
            <td>
                <?php foreach ($days as $i => $dn): ?>
                <label style="margin-right:10px">
                    <input type="checkbox" name="days[]" value="<?= $i ?>"
                        <?= in_array($i, (array) $rules['days'], true) ? 'checked' : '' ?>>
                    <?= esc_html($dn) ?>
                </label>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
    <?php submit_button('Lưu cấu hình'); ?>
    </form>

    <!-- Lịch sử bơm -->
    <h2>Lịch sử bơm gần đây</h2>
    <?php if (empty($logs)): ?>
    <p>Chưa có lịch sử bơm nào.</p>
    <?php else: ?>
    <table class="widefat striped" style="max-width:900px">
        <thead>
        <tr>
            <th>Bật lúc</th>
            <th>Tắt lúc</th>
            <th>Loại</th>
            <th>Độ ẩm trước</th>
            <th>Độ ẩm sau</th>
            <th>Thời gian (s)</th>
            <th>Trạng thái</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= esc_html($log['pump_on_at']  ?? '—') ?></td>
            <td><?= esc_html($log['pump_off_at'] ?? '—') ?></td>
            <td><?= esc_html($log['triggered_by'] ?? '—') ?></td>
            <td><?= $log['soil_before'] !== null ? esc_html(round((float) $log['soil_before'], 1)) . '%' : '—' ?></td>
            <td><?= $log['soil_after']  !== null ? esc_html(round((float) $log['soil_after'],  1)) . '%' : '—' ?></td>
            <td><?= $log['duration_sec'] !== null ? esc_html((string) $log['duration_sec']) : '—' ?></td>
            <td><span class="<?= $log['status'] === 'completed' ? 'aitr-ok' : 'aitr-warn' ?>">
                <?= esc_html($log['status'] ?? '—') ?>
            </span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <style>
        .aitr-ok   { color: #2e7d32; font-weight:600 }
        .aitr-warn { color: #b71c1c; font-weight:600 }
    </style>
    <?php endif; ?>

    <?php endif; // $rules !== null ?>
    </div>
    <?php
}
