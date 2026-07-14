<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// ─── Register 15-minute cron interval ────────────────────────────────────────
add_filter('cron_schedules', static function (array $schedules): array {
    if (! isset($schedules['aitr_15min'])) {
        $schedules['aitr_15min'] = [
            'interval' => 900,
            'display'  => 'Every 15 minutes',
        ];
    }
    return $schedules;
});

add_action('wp', static function (): void {
    if (! wp_next_scheduled('aitrongcay_timelapse_capture')) {
        wp_schedule_event(time(), 'aitr_15min', 'aitrongcay_timelapse_capture');
    }
});

add_action('aitrongcay_timelapse_capture', 'aitrongcay_do_timelapse_capture');

/**
 * Capture a JPEG snapshot from each configured go2rtc stream.
 * Called by WP-Cron every 15 minutes.
 * Derives the frame API URL from the webcam URL in rack config:
 *   http://HOST:PORT/stream.html?src=SLUG  →  http://HOST:PORT/api/frame.jpeg?src=SLUG
 */
function aitrongcay_do_timelapse_capture(): void {
    global $wpdb;
    
    // Prevent script from terminating prematurely when cameras are slow
    set_time_limit(180);

    $upload_info = wp_upload_dir();
    $base_dir    = $upload_info['basedir'] . '/timelapse';

    $rows = $wpdb->get_results(
        "SELECT option_name, option_value
           FROM {$wpdb->options}
          WHERE option_name LIKE 'aitrongcay_rack_cfg_%' OR option_name = 'aitrongcay_rack_monitor_configs'",
        ARRAY_A
    );

    // ─── Build garden→streams map from WP options (legacy + global config) ───
    $garden_streams_map = []; // [ garden_key => [ slug => [ 'frame_url'=>..., 'tray'=>... ] ] ]

    foreach ((array) $rows as $row) {
        if ($row['option_name'] === 'aitrongcay_rack_monitor_configs') {
            $garden_key = 'global';
        } else {
            $garden_key = substr((string) $row['option_name'], strlen('aitrongcay_rack_cfg_'));
        }
        $garden_key = urldecode($garden_key);

        if ($garden_key === '') {
            continue;
        }
        $configs = maybe_unserialize($row['option_value']);
        if (! is_array($configs)) {
            continue;
        }

        if (! isset($garden_streams_map[$garden_key])) {
            $garden_streams_map[$garden_key] = [];
        }

        foreach ($configs as $rack) {
            foreach ((array) ($rack['trays'] ?? []) as $tray) {
                $webcam_url = trim((string) ($tray['webcam_url'] ?? ''));
                if ($webcam_url === '' || ! str_contains($webcam_url, 'src=')) {
                    continue;
                }
                $parsed   = parse_url($webcam_url);
                $scheme   = $parsed['scheme'] ?? 'http';
                $host     = $parsed['host'] ?? '';
                if ($host === '') {
                    continue;
                }
                $port_str = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                parse_str($parsed['query'] ?? '', $qp);
                $slug = sanitize_key($qp['src'] ?? '');
                if ($slug === '' || isset($garden_streams_map[$garden_key][$slug])) {
                    continue;
                }
                $snap_slug = $slug;
                $garden_streams_map[$garden_key][$slug] = [
                    'frame_url' => $scheme . '://' . $host . $port_str . '/api/frame.jpeg?src=' . rawurlencode($snap_slug),
                    'tray'      => array_merge(function_exists('aitrongcay_tray_defaults') ? aitrongcay_tray_defaults() : [], (array) $tray),
                ];
            }
        }
    }

    // ─── Also pull webcam URLs from DB (Admin Beta stores them in wp_aitr_rack_slots) ───
    $racks_table = $wpdb->prefix . 'aitr_garden_racks';
    $slots_table = $wpdb->prefix . 'aitr_rack_slots';
    $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$racks_table}'") === $racks_table
        && $wpdb->get_var("SHOW TABLES LIKE '{$slots_table}'") === $slots_table;

    if ($tables_exist) {
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $db_slots = $wpdb->get_results(
            "SELECT r.garden_key, s.camera_stream_url, s.slot_index, s.slot_name, s.pot_code
               FROM {$racks_table} r
               JOIN {$slots_table} s ON s.rack_id = r.id
              WHERE s.camera_stream_url != '' AND s.camera_stream_url IS NOT NULL",
            ARRAY_A
        );
        error_log("[TIMELAPSE CRON] Tìm thấy " . count($db_slots) . " luồng camera từ DB (aitr_rack_slots).");
        foreach ((array) $db_slots as $db_slot) {
            $gk         = trim((string) ($db_slot['garden_key'] ?? ''));
            $gk         = urldecode($gk);
            $webcam_url = trim((string) ($db_slot['camera_stream_url'] ?? ''));
            
            if ($gk === '' || $webcam_url === '' || ! str_contains($webcam_url, 'src=')) {
                continue;
            }
            $parsed   = parse_url($webcam_url);
            $scheme   = $parsed['scheme'] ?? 'http';
            $host     = $parsed['host'] ?? '';
            if ($host === '') {
                continue;
            }
            $port_str = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            parse_str($parsed['query'] ?? '', $qp);
            $slug = sanitize_key($qp['src'] ?? '');
            if ($slug === '') {
                continue;
            }
            // Use sanitize_key for filesystem-safe garden key
            $safe_gk = sanitize_key($gk);
            if (! isset($garden_streams_map[$safe_gk])) {
                $garden_streams_map[$safe_gk] = [];
            }
            $snap_slug = $slug;
            $tray_defaults = function_exists('aitrongcay_tray_defaults') ? aitrongcay_tray_defaults() : [];
            $garden_streams_map[$safe_gk][$slug] = [
                'frame_url' => $scheme . '://' . $host . $port_str . '/api/frame.jpeg?src=' . rawurlencode($snap_slug),
                'tray'      => array_merge($tray_defaults, [
                    'webcam_url' => $webcam_url,
                    'name'       => trim((string) ($db_slot['slot_name'] ?? '')),
                ]),
                'garden_key_raw' => $gk, // preserve original for logging
            ];
            error_log("[TIMELAPSE CRON] Chuẩn bị lấy ảnh cho: {$safe_gk} / {$slug} tại URL: " . $garden_streams_map[$safe_gk][$slug]['frame_url']);
        }
    }

    // ─── Process each garden / stream ───────────────────────────────────────
    error_log("[TIMELAPSE CRON] Bắt đầu duyệt qua " . count($garden_streams_map) . " khu vườn có cấu hình camera.");
    foreach ($garden_streams_map as $garden_key => $streams) {
        if (empty($streams)) {
            continue;
        }

        $date = wp_date('Y-m-d');
        $time = wp_date('H-i');

        foreach ($streams as $slug => $stream) {
            $frame_url = $stream['frame_url'];
            error_log("[TIMELAPSE CRON] Đang tải ảnh từ: {$frame_url}");
            $body = aitrongcay_timelapse_fetch_frame($frame_url);
            if ($body === null) {
                error_log("[TIMELAPSE CRON] Lỗi lấy ảnh từ API go2rtc, thử qua ISAPI fallback...");
                $parsed_base = parse_url($frame_url);
                $origin      = ($parsed_base['scheme'] ?? 'http') . '://' . ($parsed_base['host'] ?? '') . (isset($parsed_base['port']) ? ':' . $parsed_base['port'] : '');
                $rtsp_url    = aitrongcay_timelapse_get_rtsp_url($origin, $slug);
                $body        = aitrongcay_timelapse_fetch_frame_isapi($rtsp_url);
            }
            if ($body === null) {
                error_log("[TIMELAPSE CRON] ❌ Thất bại hoàn toàn khi lấy ảnh cho {$garden_key} / {$slug}. Bỏ qua.");
                continue;
            }
            error_log("[TIMELAPSE CRON] ✅ Tải ảnh thành công (" . strlen($body) . " bytes). Bắt đầu lưu file...");
            $safe_gk = sanitize_key($garden_key);
            $safe_sl = sanitize_key($slug);
            $dir = $base_dir . '/' . $safe_gk . '/' . $safe_sl . '/' . $date;
            wp_mkdir_p($dir);
            $file_path = $dir . '/' . $time . '.jpg';
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
            $bytes_written = file_put_contents($file_path, $body);
            error_log("[TIMELAPSE CRON] Đã ghi " . ($bytes_written !== false ? $bytes_written : "LỖI") . " bytes vào file: {$file_path}");
            // Save sensor snapshot alongside the photo
            if (function_exists('aitrongcay_tray_read_sensors')) {
                $sensors = aitrongcay_tray_read_sensors($stream['tray']);
                if (! isset($sensors['error']) && ! empty($sensors)) {
                    $sensors['captured_at'] = wp_date('c');
                    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
                    file_put_contents($dir . '/' . $time . '.json', wp_json_encode($sensors));
                }
            }
        }
    }

    aitrongcay_timelapse_cleanup($base_dir);
}

/**
 * Try go2rtc /api/frame.jpeg. Only retries if non-500 (lazy stream).
 * H.265 cameras return 500 immediately — no sleep needed, skip straight to ISAPI fallback.
 */
function aitrongcay_timelapse_fetch_frame(string $frame_url): ?string {
    $args     = [
        'timeout'   => 15,
        'sslverify' => false,
        'headers'   => [
            'ngrok-skip-browser-warning' => 'true'
        ]
    ];
    $response = wp_remote_get($frame_url, $args);
    $code     = is_wp_error($response) ? 0 : wp_remote_retrieve_response_code($response);

    if ($code === 200) {
        $body = wp_remote_retrieve_body($response);
        return strlen($body) >= 1000 ? $body : null;
    }

    return null;
}

/**
 * Lấy RTSP URL của stream từ go2rtc /api/streams.
 * Returns first RTSP URL string found for the slug, or empty string.
 */
function aitrongcay_timelapse_get_rtsp_url(string $go2rtc_origin, string $slug): string {
    $resp = wp_remote_get($go2rtc_origin . '/api/streams', ['timeout' => 4, 'sslverify' => false]);
    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        return '';
    }
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    foreach ((array) ($data[$slug]['producers'] ?? []) as $producer) {
        $url = (string) ($producer['url'] ?? '');
        if (str_starts_with($url, 'rtsp://')) {
            return $url;
        }
    }
    return '';
}

/**
 * Chụp ảnh trực tiếp từ camera Hikvision/EZVIZ qua ISAPI HTTP snapshot.
 * Dùng khi go2rtc /api/frame.jpeg không hỗ trợ H.265.
 *
 * Hikvision ISAPI endpoint: /ISAPI/Streaming/channels/{CHANNEL}/picture
 * Channel mapping: /h264/ch1/... → channel 101, /h264/ch4/... → channel 401
 */
function aitrongcay_timelapse_fetch_frame_isapi(string $rtsp_url): ?string {
    if ($rtsp_url === '') {
        return null;
    }
    $parsed = parse_url($rtsp_url);
    $host   = $parsed['host'] ?? '';
    $user   = rawurldecode($parsed['user'] ?? '');
    $pass   = rawurldecode($parsed['pass'] ?? '');
    $path   = $parsed['path'] ?? '';

    if ($host === '' || $user === '') {
        return null;
    }

    preg_match('/\/ch(\d+)\//i', $path, $m);
    $ch = (int) ($m[1] ?? 1);

    // Try channel formats in order: NVR-style (401), simplified (4), single-cam fallback (101)
    $candidates = array_unique([$ch * 100 + 1, $ch, 101]);
    $auth       = 'Basic ' . base64_encode("{$user}:{$pass}");
    $args       = ['timeout' => 2, 'sslverify' => false, 'headers' => ['Authorization' => $auth]];

    foreach ($candidates as $isapi_ch) {
        $snapshot_url = "http://{$host}/ISAPI/Streaming/channels/{$isapi_ch}/picture";
        $response     = wp_remote_get($snapshot_url, $args);
        if (is_wp_error($response)) {
            continue;
        }
        if (wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            return strlen($body) >= 1000 ? $body : null;
        }
    }
    return null;
}

/**
 * Delete snapshot folders older than 31 days.
 */
function aitrongcay_timelapse_cleanup(string $base_dir = ''): void {
    if ($base_dir === '') {
        $upload_info = wp_upload_dir();
        $base_dir    = $upload_info['basedir'] . '/timelapse';
    }
    if (! is_dir($base_dir)) {
        return;
    }
    $cutoff_date = wp_date('Y-m-d', strtotime('-31 days'));

    foreach (glob($base_dir . '/*/*', GLOB_ONLYDIR) ?: [] as $stream_dir) {
        foreach (glob($stream_dir . '/*', GLOB_ONLYDIR) ?: [] as $date_dir) {
            if (basename($date_dir) < $cutoff_date) {
                array_map('unlink', glob($date_dir . '/*.jpg') ?: []);
                @rmdir($date_dir);
            }
        }
    }
}

// ─── AJAX: admin manual capture (test / force snapshot now) ──────────────────
add_action('wp_ajax_aitrongcay_timelapse_capture_now', 'aitrongcay_ajax_timelapse_capture_now');

function aitrongcay_ajax_timelapse_capture_now(): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    $garden_key  = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if (! current_user_can('manage_options') && (! is_user_logged_in() || ! aitrongcay_user_can_control_garden($garden_key, get_current_user_id()))) {
        wp_send_json_error(['message' => 'Chỉ chủ vườn mới được phép chụp ảnh.']);
        return;
    }

    $garden_key  = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    $stream_slug = sanitize_key((string) wp_unslash($_POST['stream'] ?? ''));
    if ($garden_key === '' || $stream_slug === '') {
        wp_send_json_error(['message' => 'Thiếu garden_key hoặc stream.']);
        return;
    }

    // ── Step 1: DB-backed config (reflects current rack assignment) ──────────
    // Use aitrongcay_get_rack_monitor_configs() which reads from aitr_garden_racks +
    // aitr_rack_slots tables. This always reflects the CURRENT owner after rack reassignment,
    // unlike WP options which are per-garden-key and may be empty for new owners.
    $configs = function_exists('aitrongcay_get_rack_monitor_configs')
        ? aitrongcay_get_rack_monitor_configs($garden_key)
        : [];

    // ── Step 2: Fallback to WP option if DB returned nothing ─────────────────
    if (empty($configs)) {
        $configs = get_option('aitrongcay_rack_cfg_' . $garden_key, []);
    }
    if (empty($configs)) {
        // Last resort: global/legacy option (admin-owned streams)
        $configs = get_option('aitrongcay_rack_monitor_configs', []);
    }

    if (! is_array($configs) || empty($configs)) {
        wp_send_json_error(['message' => 'Không tìm thấy config của vườn này (và cấu hình chung cũng trống).']);
        return;
    }

    // Find webcam URL and tray config for this stream slug
    $frame_url    = '';
    $matched_tray = null;
    foreach ($configs as $rack) {
        foreach ((array) ($rack['trays'] ?? []) as $tray) {
            $webcam_url = trim((string) ($tray['webcam_url'] ?? ''));
            if ($webcam_url === '') {
                continue;
            }
            $parsed = parse_url($webcam_url);
            parse_str($parsed['query'] ?? '', $qp);
            if (sanitize_key($qp['src'] ?? '') === $stream_slug) {
                $scheme        = $parsed['scheme'] ?? 'http';
                $host          = $parsed['host'] ?? '';
                $port_str      = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $frame_url     = $scheme . '://' . $host . $port_str . '/api/frame.jpeg?src=' . rawurlencode($stream_slug);
                $matched_tray  = function_exists('aitrongcay_tray_defaults')
                    ? array_merge(aitrongcay_tray_defaults(), (array) $tray)
                    : (array) $tray;
                break 2;
            }
        }
    }

    // ── Step 3: Direct DB query fallback (query by garden_key then all) ──────
    if ($frame_url === '') {
        global $wpdb;
        $racks_table = $wpdb->prefix . 'aitr_garden_racks';
        $slots_table = $wpdb->prefix . 'aitr_rack_slots';
        $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$racks_table}'") === $racks_table
            && $wpdb->get_var("SHOW TABLES LIKE '{$slots_table}'") === $slots_table;

        if ($tables_exist) {
            // First: query only slots belonging to this garden's racks (most specific)
            $db_slots = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT r.garden_key, s.camera_stream_url, s.slot_name
                       FROM {$racks_table} r
                       JOIN {$slots_table} s ON s.rack_id = r.id
                      WHERE r.garden_key = %s AND s.camera_stream_url != ''",
                    $garden_key
                ),
                ARRAY_A
            );
            // Second: widen to all slots if nothing found yet
            if (empty($db_slots)) {
                $db_slots = $wpdb->get_results(
                    "SELECT r.garden_key, s.camera_stream_url, s.slot_name
                       FROM {$racks_table} r
                       JOIN {$slots_table} s ON s.rack_id = r.id
                      WHERE s.camera_stream_url != ''",
                    ARRAY_A
                );
            }
            foreach ((array) $db_slots as $db_slot) {
                $webcam_url = trim((string) ($db_slot['camera_stream_url'] ?? ''));
                $parsed = parse_url($webcam_url);
                parse_str($parsed['query'] ?? '', $qp);
                if (sanitize_key($qp['src'] ?? '') === $stream_slug) {
                    $scheme        = $parsed['scheme'] ?? 'http';
                    $host          = $parsed['host'] ?? '';
                    $port_str      = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                    $frame_url     = $scheme . '://' . $host . $port_str . '/api/frame.jpeg?src=' . rawurlencode($stream_slug);
                    $matched_tray  = function_exists('aitrongcay_tray_defaults')
                        ? array_merge(aitrongcay_tray_defaults(), ['webcam_url' => $webcam_url, 'name' => $db_slot['slot_name']])
                        : ['webcam_url' => $webcam_url, 'name' => $db_slot['slot_name']];
                    // Use the actual rack's garden_key to save the timelapse under correct folder
                    $garden_key    = $db_slot['garden_key'];
                    break;
                }
            }
        }
    }

    if ($frame_url === '') {
        wp_send_json_error(['message' => 'Không tìm thấy webcam URL cho stream "' . $stream_slug . '".']);
        return;
    }

    $streams_info = '';

    // Try go2rtc first, fall back to Hikvision ISAPI for H.265 cameras
    $capture_method = 'go2rtc';
    $body           = aitrongcay_timelapse_fetch_frame($frame_url);
    $isapi_debug    = [];
    if ($body === null) {
        $capture_method = 'isapi';
        $rtsp_url       = aitrongcay_timelapse_get_rtsp_url($base_origin, $stream_slug);

        if ($rtsp_url !== '') {
            $rp         = parse_url($rtsp_url);
            $isapi_user = rawurldecode($rp['user'] ?? '');
            
            $isapi_debug = [
                'rtsp_url' => preg_replace('/\/\/([^:]+):([^@]+)@/', '//\\1:***@', $rtsp_url),
                'has_user' => $isapi_user !== '',
            ];
        } else {
            $isapi_debug = ['rtsp_url' => '(không lấy được từ go2rtc /api/streams)'];
        }

        $body = aitrongcay_timelapse_fetch_frame_isapi($rtsp_url);
    }

    if ($body === null) {
        $test   = wp_remote_get($frame_url, ['timeout' => 4, 'sslverify' => false]);
        $code   = is_wp_error($test) ? ('err: ' . $test->get_error_message()) : ('HTTP ' . wp_remote_retrieve_response_code($test));
        $codec  = $streams_info !== '' ? ' | Codec: ' . rtrim($streams_info, ' |') : ' | (stream chưa active hoặc không đọc được codec)';
        wp_send_json_error([
            'message'      => 'go2rtc /api/frame.jpeg → ' . $code . $codec . ' | ISAPI fallback cũng thất bại',
            'streams_info' => $streams_info,
            'isapi_debug'  => $isapi_debug,
            'hint'         => 'Xem isapi_debug để biết RTSP URL và HTTP code từ camera',
        ]);
        return;
    }

    $upload_info    = wp_upload_dir();
    $date           = wp_date('Y-m-d');
    $time           = wp_date('H-i');
    $safe_gk        = sanitize_key($garden_key);
    $safe_sl        = sanitize_key($stream_slug);
    $dir            = $upload_info['basedir'] . '/timelapse/' . $safe_gk . '/' . $safe_sl . '/' . $date;
    wp_mkdir_p($dir);
    $filename = $time . '.jpg';
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
    file_put_contents($dir . '/' . $filename, $body);

    // Save sensor snapshot alongside the photo
    $sensors_saved = null;
    if ($matched_tray !== null && function_exists('aitrongcay_tray_read_sensors')) {
        $sensors = aitrongcay_tray_read_sensors($matched_tray);
        if (! isset($sensors['error']) && ! empty($sensors)) {
            $sensors['captured_at'] = wp_date('c');
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
            file_put_contents($dir . '/' . $time . '.json', wp_json_encode($sensors));
            $sensors_saved = $sensors;
        }
    }

    $saved_url = wp_make_link_relative($upload_info['baseurl']) . '/timelapse/' . $safe_gk . '/' . $safe_sl . '/' . $date . '/' . $filename;
    $method_label = $capture_method === 'isapi' ? 'ISAPI (H.265 fallback)' : 'go2rtc';
    wp_send_json_success([
        'message'   => 'Đã chụp thành công ' . strlen($body) . ' bytes qua ' . $method_label . '.',
        'url'       => $saved_url,
        'date'      => $date,
        'time'      => str_replace('-', ':', $time),
        'method'    => $capture_method,
        'sensors'   => $sensors_saved,
    ]);
}

// ─── AJAX: return frame list for a garden/stream/date-range ──────────────────
add_action('wp_ajax_aitrongcay_timelapse_list', 'aitrongcay_ajax_timelapse_list');
add_action('wp_ajax_nopriv_aitrongcay_timelapse_list', 'aitrongcay_ajax_timelapse_list');

function aitrongcay_ajax_timelapse_list(): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');

    global $wpdb;

    // Dùng sanitize_text_field để giữ nguyên dấu ":" trong legacy garden key (garden:hash)
    $garden_key    = trim(sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? '')));
    $stream_slug   = sanitize_key((string) wp_unslash($_POST['stream'] ?? ''));
    $legacy_stream = sanitize_key((string) wp_unslash($_POST['legacy_stream'] ?? ''));
    
    $days_raw = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '7';
    $days = $days_raw === 'all' ? 365 : max(1, min(365, (int) $days_raw));

    file_put_contents(WP_CONTENT_DIR . '/timelapse_debug.log', 
        date('Y-m-d H:i:s') . " | HOST: " . $_SERVER['HTTP_HOST'] . 
        " | garden: $garden_key | stream: $stream_slug | days: $days\n", 
        FILE_APPEND
    );

    if ($stream_slug === '') {
        wp_send_json_error(['message' => 'Thiếu tham số.']);
        return;
    }

    $user_id  = get_current_user_id();
    $is_admin = current_user_can('manage_options');

    $can_access = $is_admin;
    if (! $can_access && $garden_key !== '' && function_exists('aitrongcay_user_can_view_garden')) {
        $can_access = aitrongcay_user_can_view_garden($garden_key, $user_id);
    }
    // Fallback: tìm garden_key thực từ đơn hàng của user
    if (! $can_access && $user_id > 0) {
        $_ot = $wpdb->prefix . 'aitr_orders';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$_ot}'") === $_ot) {
            $cur_email = wp_get_current_user()->user_email;
            // Thử khớp chính xác garden_key đã truyền vào
            if ($garden_key !== '') {
                $can_access = (bool) $wpdb->get_var($wpdb->prepare(
                    "SELECT 1 FROM {$_ot} WHERE (user_id = %d OR customer_email = %s) AND garden_key = %s AND status = 'active' LIMIT 1",
                    $user_id, $cur_email, $garden_key
                ));
            }
            // Nếu không khớp, lấy garden_key từ bất kỳ đơn hàng active nào của user
            if (! $can_access) {
                $order_gk = $wpdb->get_var($wpdb->prepare(
                    "SELECT garden_key FROM {$_ot} WHERE (user_id = %d OR customer_email = %s) AND status = 'active' AND garden_key != '' ORDER BY updated_at DESC LIMIT 1",
                    $user_id, $cur_email
                ));
                if (is_string($order_gk) && trim($order_gk) !== '') {
                    $can_access = true;
                    $garden_key = trim($order_gk); // Dùng garden_key thực từ order để serve files
                }
            }
        }
    }
    if (! $can_access) {
        wp_send_json_error(['message' => 'Không có quyền truy cập.']);
        return;
    }

    // NOTE: Timelapse privacy checks have been removed. 
    // If a user has access to the garden (validated above), they can view all timelapse frames.

    // sanitize_key strips ":" → backward-compat với path đã lưu trước đây (gardenhash/ không có dấu :)
    $safe_stream  = $legacy_stream !== '' ? $legacy_stream : sanitize_key($stream_slug);

    $upload_info  = wp_upload_dir();
    $base_uploads = $upload_info['basedir'] . '/timelapse';

    // Resolve actual filesystem garden key:
    // Timelapse cron lưu files dùng suffix của option name (aitrongcay_rack_cfg_{key}),
    // không nhất thiết trùng với sanitize_key($garden_key) nếu admin đã lưu config với key khác.
    $fs_garden_key = sanitize_key($garden_key);
    if (! is_dir($base_uploads . '/' . $fs_garden_key . '/' . $safe_stream)) {
        if (is_dir($base_uploads . '/global/' . $safe_stream)) {
            $fs_garden_key = 'global';
        } else {
            $opt_rows = $wpdb->get_results(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'aitrongcay_rack_cfg_%'",
                ARRAY_A
            );
            foreach ($opt_rows ?: [] as $opt_row) {
                $cand = substr((string) ($opt_row['option_name'] ?? ''), strlen('aitrongcay_rack_cfg_'));
                if ($cand === '' || $cand === $fs_garden_key) {
                    continue;
                }
                if (is_dir($base_uploads . '/' . $cand . '/' . $safe_stream)) {
                    $fs_garden_key = $cand;
                    break;
                }
            }
        }
    }

    $base_dir = $base_uploads . '/' . $fs_garden_key . '/' . $safe_stream;
    $base_url = wp_make_link_relative($upload_info['baseurl']) . '/timelapse/' . $fs_garden_key . '/' . $safe_stream;

    $frames = [];
    for ($d = $days; $d >= 0; $d--) {
        $date = wp_date('Y-m-d', strtotime("-{$d} days"));
        $dir  = $base_dir . '/' . $date;
        if (! is_dir($dir)) {
            continue;
        }
        $files = glob($dir . '/*.jpg') ?: [];
        sort($files);
        foreach ($files as $file) {
            $t_str    = basename($file, '.jpg');
            $json_file = $dir . '/' . $t_str . '.json';
            $sensors   = null;
            if (file_exists($json_file)) {
                // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
                $sensors = json_decode(file_get_contents($json_file), true);
            }
            $ts = strtotime($date . ' ' . str_replace('-', ':', $t_str));
            
            $ts = strtotime($date . ' ' . str_replace('-', ':', $t_str));
            $frames[] = [
                'url'     => $base_url . '/' . $date . '/' . basename($file),
                'date'    => $date,
                'time'    => str_replace('-', ':', $t_str),
                'sensors' => $sensors,
                'ts'      => $ts,
            ];
        }
    }

    // Lấy thêm ảnh từ thư viện ảnh (Robot chụp)
    $library_args = [
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 500,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_mime_type' => 'image',
        'no_found_rows'  => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'meta_query'     => [
            [
                'key'   => '_aitrongcay_photo_garden_key',
                'value' => $garden_key,
            ],
            [
                'key'   => '_aitrongcay_pot_code',
                'value' => $stream_slug,
            ]
        ],
        'date_query' => [
            [
                'after' => "-{$days} days"
            ]
        ]
    ];

    if ($days_raw === 'all') {
        unset($library_args['date_query']);
    }

    $library_query = new WP_Query($library_args);

    if ($library_query->have_posts()) {
        foreach ($library_query->posts as $photo_post) {
            $date = wp_date('Y-m-d', strtotime($photo_post->post_date));
            $time = wp_date('H:i', strtotime($photo_post->post_date));
            $url = wp_get_attachment_image_url($photo_post->ID, 'large') ?: wp_get_attachment_url($photo_post->ID);
            $ts = strtotime($photo_post->post_date);
            
            if ($url) {
                $frames[] = [
                    'url'     => wp_make_link_relative((string) $url),
                    'date'    => $date,
                    'time'    => $time,
                    'sensors' => null, // Ảnh từ robot hoặc thư viện không có sensor
                    'ts'      => $ts,
                ];
            }
        }
    }

    // Sort all frames by timestamp ascending
    usort($frames, static function ($a, $b) {
        return $a['ts'] <=> $b['ts'];
    });

    // Remove ts from final output
    foreach ($frames as &$f) {
        unset($f['ts']);
    }
    unset($f);

    file_put_contents(WP_CONTENT_DIR . '/timelapse_debug.log', 
        date('Y-m-d H:i:s') . " | HOST: " . $_SERVER['HTTP_HOST'] . 
        " | RETURNED FRAMES: " . count($frames) . "\n", 
        FILE_APPEND
    );

    wp_send_json_success([
        'frames' => array_values($frames),
        'total'  => count($frames),
    ]);
}

// ─── TỰ ĐỘNG RENDER VIDEO TIMELAPSE KHI THU HOẠCH ────────────────────────
add_action('aitrongcay_render_harvest_video_cron', 'aitrongcay_execute_harvest_video_render', 10, 4);

function aitrongcay_execute_harvest_video_render($archive_dir, $email, $garden_key, $pot_code): void {
    if (!is_dir($archive_dir)) {
        return;
    }

    $delete_dir = static function ($dir) use (&$delete_dir) {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $delete_dir($path) : @unlink($path);
        }
        @rmdir($dir);
    };

    // 1. Thu thập tất cả file .jpg
    $jpgs = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archive_dir));
    foreach ($iter as $file) {
        if ($file->getExtension() === 'jpg') {
            $jpgs[] = $file->getPathname();
        }
    }

    if (empty($jpgs)) {
        $delete_dir($archive_dir);
        return;
    }

    sort($jpgs); // Đảm bảo đúng thứ tự thời gian

    // 2. Tạo file danh sách cho FFmpeg (concat demuxer)
    $concat_txt = $archive_dir . '/concat.txt';
    $fh = fopen($concat_txt, 'w');
    if ($fh !== false) {
        foreach ($jpgs as $jpg) {
            // Thay thế backslash thành slash trên Windows để FFmpeg không lỗi
            $path = str_replace('\\', '/', $jpg);
            // Escape nháy đơn
            $path = str_replace("'", "'\\''", $path);
            fwrite($fh, "file '" . $path . "'\n");
        }
        fclose($fh);
    }

    // 3. Chạy lệnh FFmpeg để render mp4
    $upload_info = wp_upload_dir();
    $out_dir = $upload_info['basedir'] . '/harvest_videos/' . sanitize_key($garden_key);
    wp_mkdir_p($out_dir);
    $out_file = $out_dir . '/' . sanitize_key($pot_code) . '_harvest_' . time() . '.mp4';

    // Đường dẫn FFmpeg (yêu cầu server cài sẵn FFmpeg trong PATH)
    $cmd = sprintf(
        'ffmpeg -y -r 15 -f concat -safe 0 -i %s -c:v libx264 -preset fast -crf 23 -pix_fmt yuv420p %s 2>&1',
        escapeshellarg($concat_txt),
        escapeshellarg($out_file)
    );

    exec($cmd, $output, $result_code);

    // 4. Nếu render thành công, gửi Email cho khách hàng
    if ($result_code === 0 && file_exists($out_file)) {
        $video_url = wp_make_link_relative($upload_info['baseurl']) . '/harvest_videos/' . sanitize_key($garden_key) . '/' . basename($out_file);
        
        $subject = '🎉 Chúc mừng thu hoạch thành công Khoang ' . $pot_code;
        $body = "Chào bạn,\n\n";
        $body .= "Hệ thống AI Trồng Cây đã dọn dẹp khoang " . $pot_code . " của bạn để chuẩn bị cho lứa mới.\n";
        $body .= "Dưới đây là Video Kỷ Niệm (Timelapse) lưu giữ toàn bộ hành trình sinh trưởng của cây:\n\n";
        $body .= home_url($video_url) . "\n\n";
        $body .= "Bạn có thể tải về và chia sẻ lên mạng xã hội cùng bạn bè!\n\n";
        $body .= "Trân trọng,\nĐội ngũ AI Trồng Cây.";
        
        wp_mail($email, $subject, $body);
    }

    // 5. Hoàn tất dọn dẹp ổ cứng (xoá thư mục archive chứa hàng ngàn ảnh gốc)
    $delete_dir($archive_dir);
}
