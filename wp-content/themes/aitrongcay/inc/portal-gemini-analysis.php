<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// ── Gemini API key ───────────────────────────────────────────────────────────

function aitrongcay_get_gemini_api_key(): string
{
    return trim((string) get_option('aitrongcay_gemini_api_key', ''));
}

// ── Map pot_code → webcam info (slug + go2rtc base URL) ──────────────────────

/**
 * Returns ['slug' => 'vuon1', 'base_url' => 'http://127.0.0.1:1984'] or [].
 */
function aitrongcay_resolve_pot_webcam_info(string $garden_key, string $pot_code): array
{
    $pot_code = strtoupper(trim($pot_code));
    if ($garden_key === '' || $pot_code === '') {
        return [];
    }

    $rack_configs = function_exists('aitrongcay_get_rack_monitor_configs')
        ? aitrongcay_get_rack_monitor_configs($garden_key)
        : [];
    if (empty($rack_configs)) {
        return [];
    }

    $flat_trays = [];
    foreach ($rack_configs as $rc) {
        foreach ((array) ($rc['trays'] ?? []) as $rt) {
            $flat_trays[] = trim((string) ($rt['webcam_url'] ?? ''));
        }
    }

    $extract = static function (string $webcam_url): array {
        if ($webcam_url === '' || !str_contains($webcam_url, 'src=')) {
            return [];
        }
        $parsed = parse_url($webcam_url);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '127.0.0.1';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $base = $scheme . '://' . $host . $port;
        parse_str((string) ($parsed['query'] ?? ''), $qp);
        $slug = sanitize_key($qp['src'] ?? '');
        return $slug !== '' ? ['slug' => $slug, 'base_url' => $base] : [];
    };

    // Method 0: Check DB directly for slot-specific camera
    if (function_exists('aitrongcay_get_rack_slot_camera_stream_url')) {
        $cam_url = aitrongcay_get_rack_slot_camera_stream_url($garden_key, $pot_code);
        if ($cam_url !== '') {
            $info = $extract($cam_url);
            if ($info !== []) {
                return $info;
            }
        }
    }

    // Method 1: slot_index-based
    if (function_exists('aitrongcay_get_rack_slots')) {
        foreach ((array) aitrongcay_get_rack_slots($garden_key) as $slot) {
            $slot_index = (int) ($slot['slot_index'] ?? 0);
            $slot_pot = strtoupper(trim((string) ($slot['pot_code'] ?? '')));
            if ($slot_pot !== $pot_code || $slot_index < 1) {
                continue;
            }
            $info = $extract($flat_trays[$slot_index - 1] ?? '');
            if ($info !== []) {
                return $info;
            }
        }
    }

    // Method 2: positional fallback
    if (function_exists('aitrongcay_portal_pots')) {
        foreach (aitrongcay_portal_pots($garden_key, null) as $i => $pot) {
            if (strtoupper(trim((string) ($pot['code'] ?? ''))) !== $pot_code) {
                continue;
            }
            $info = $extract($flat_trays[$i] ?? '');
            if ($info !== []) {
                return $info;
            }
        }
    }

    return [];
}

// ── Download live frame from go2rtc ──────────────────────────────────────────

/**
 * Returns raw JPEG bytes or null on failure.
 */
function aitrongcay_fetch_live_frame(string $base_url, string $slug): ?string
{
    if ($base_url === '' || $slug === '') {
        return null;
    }
    $url = rtrim($base_url, '/') . '/api/frame.jpeg?src=' . rawurlencode($slug) . '&_t=' . time();
    $response = wp_remote_get($url, ['timeout' => 5, 'sslverify' => false]);
    if (is_wp_error($response)) {
        return null;
    }
    if ((int) wp_remote_retrieve_response_code($response) !== 200) {
        return null;
    }
    $body = wp_remote_retrieve_body($response);
    // Basic JPEG signature check
    return (strlen($body) > 1024 && substr($body, 0, 2) === "\xFF\xD8") ? $body : null;
}

// ── Get latest DAYTIME timelapse photo from filesystem ───────────────────────

/**
 * Returns ['path', 'url', 'date', 'time', 'sensors'] or null.
 * Prefers daytime photos (06:00-20:00); falls back to any if none found.
 */
function aitrongcay_get_latest_timelapse_for_pot(string $garden_key, string $stream_slug): ?array
{
    if ($garden_key === '' || $stream_slug === '') {
        return null;
    }

    $fs_garden_key = sanitize_key($garden_key);
    $base_uploads = WP_CONTENT_DIR . '/uploads/timelapse';
    $base_dir = $base_uploads . '/' . $fs_garden_key . '/' . $stream_slug . '/';
    if (!is_dir($base_dir)) {
        if (is_dir($base_uploads . '/global/' . $stream_slug . '/')) {
            $fs_garden_key = 'global';
            $base_dir = $base_uploads . '/global/' . $stream_slug . '/';
        } else {
            return null;
        }
    }

    $date_dirs = glob($base_dir . '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', GLOB_ONLYDIR);
    if (empty($date_dirs)) {
        return null;
    }
    rsort($date_dirs);

    // Search up to last 3 days for a daytime photo
    $chosen_path = null;
    $chosen_date_dir = null;
    foreach (array_slice($date_dirs, 0, 3) as $date_dir) {
        $photos = glob($date_dir . '/*.jpg');
        if (empty($photos)) {
            continue;
        }
        rsort($photos);

        // Prefer daytime: hour 06-20
        foreach ($photos as $p) {
            $hour = (int) substr(basename($p, '.jpg'), 0, 2);
            if ($hour >= 6 && $hour <= 20) {
                $chosen_path = $p;
                $chosen_date_dir = $date_dir;
                break 2;
            }
        }

        // No daytime found in this day — keep as fallback candidate
        if ($chosen_path === null) {
            $chosen_path = $photos[0];
            $chosen_date_dir = $date_dir;
        }
    }

    if ($chosen_path === null || $chosen_date_dir === null) {
        return null;
    }

    $photo_url = content_url(
        'uploads/timelapse/' . $fs_garden_key . '/' . $stream_slug . '/'
        . basename($chosen_date_dir) . '/' . basename($chosen_path)
    );

    $sensors = [];
    $json_path = substr($chosen_path, 0, -4) . '.json';
    if (file_exists($json_path)) {
        $raw = @file_get_contents($json_path);
        if ($raw !== false) {
            $d = json_decode($raw, true);
            if (is_array($d)) {
                $sensors = $d;
            }
        }
    }

    return [
        'path' => $chosen_path,
        'url' => $photo_url,
        'date' => basename($chosen_date_dir),
        'time' => basename($chosen_path, '.jpg'),
        'sensors' => $sensors,
    ];
}


// ── Build Gemini analysis prompt ─────────────────────────────────────────────

function aitrongcay_build_gemini_analysis_prompt(array $pot_record, array $photo_meta): string
{
    $plant_name = trim((string) ($pot_record['plant_name'] ?? $pot_record['name'] ?? 'rau thủy canh'));
    $pot_name = trim((string) ($pot_record['name'] ?? $pot_record['code'] ?? 'khoang'));
    $created_at = trim((string) ($pot_record['created_at'] ?? ''));
    $days_growing = 0;
    if ($created_at !== '') {
        $ts = strtotime($created_at);
        if ($ts) {
            $days_growing = max(0, (int) round((time() - $ts) / 86400));
        }
    }

    $sensor_info = '';
    $sensors = $photo_meta['sensors'] ?? [];
    if (!empty($sensors)) {
        $parts = [];
        if (isset($sensors['temp']))
            $parts[] = 'nhiệt độ ' . $sensors['temp'] . '°C';
        if (isset($sensors['hum']))
            $parts[] = 'độ ẩm ' . $sensors['hum'] . '%';
        if (isset($sensors['soil']))
            $parts[] = 'độ ẩm giá thể ' . $sensors['soil'] . '%';
        if (isset($sensors['ph']))
            $parts[] = 'pH ' . $sensors['ph'];
        if (isset($sensors['ec']))
            $parts[] = 'EC ' . $sensors['ec'] . ' mS/cm';
        if (!empty($parts)) {
            $sensor_info = "\n- Cảm biến: " . implode(', ', $parts);
        }
    }

    $date_label = isset($photo_meta['date'])
        ? $photo_meta['date'] . ' ' . str_replace('-', ':', (string) ($photo_meta['time'] ?? ''))
        : 'vừa chụp';
    $source_label = ($photo_meta['source'] ?? 'timelapse') === 'live' ? 'ảnh live camera' : 'ảnh timelapse';

    global $wpdb;
    $supplies_table = $wpdb->prefix . 'aitr_supplies';
    // Lấy các vật tư Đang dùng, ưu tiên loại phổ biến (Hạt giống, Dinh dưỡng)
    $db_supplies = $wpdb->get_results("SELECT code, name, type FROM {$supplies_table} WHERE status = 'active' ORDER BY updated_at DESC LIMIT 30", ARRAY_A);
    $supplies_text = "Không có vật tư nào khả dụng.";
    if (!empty($db_supplies)) {
        $parts = [];
        foreach ($db_supplies as $s) {
            $type = empty($s['type']) ? 'Khác' : $s['type'];
            $parts[] = "- Phân loại: " . esc_html($type) . " | Mã: " . esc_html($s['code']) . " | Tên: " . esc_html($s['name']);
        }
        $supplies_text = implode("\n", $parts);
    }

    $plant_knowledge = '';
    $plant_name_lc = mb_strtolower($plant_name . ' ' . $pot_name, 'UTF-8');
    if (str_contains($plant_name_lc, 'cà chua') || str_contains($plant_name_lc, 'ca chua') || str_contains($plant_name_lc, 'tomato')) {
        $plant_knowledge = "\n\nKIẾN THỨC CỤ THỂ CHO CÀ CHUA BI (CHERRY TOMATO):
- Môi trường lý tưởng: pH 5.5 - 6.5, EC 1.5 - 2.5 mS/cm.
- Nảy mầm: Thường mất 3-7 ngày. Nếu < 7 ngày mà chưa nảy mầm thì vẫn bình thường, hãy tư vấn người dùng kiên nhẫn.
- Độ ẩm giá thể: Nên giữ ở mức 60% - 70%. Nếu dưới 50% là quá khô (cần bật bơm), trên 80% rễ dễ bị úng.
- Nếu số ngày trồng > 14 ngày mà vẫn ở giai đoạn Nảy Mầm, hãy cảnh báo sinh trưởng chậm.
- Đánh giá màu lá theo 3 tầng (ngọn, giữa, gốc). Lá gốc già vàng đi là bình thường khi cây lớn, chỉ đáng ngại nếu ngọn héo hoặc vàng.";
    }

    $prompt = "Bạn là một hệ thống AI Chuyên gia Nông nghiệp Thông minh chuẩn quốc tế, tích hợp công nghệ Thị giác Máy tính (Computer Vision). Nhiệm vụ của bạn là phân tích ảnh chụp hệ thống cây trồng (thủy canh, thổ canh, trong nhà, ngoài trời...) và đưa ra chẩn đoán chính xác tuyệt đối.

THÔNG TIN KHOANG:
- Tên khoang: {$pot_name}
- Loại cây: {$plant_name}
- Ngày bắt đầu: {$created_at} ({$days_growing} ngày trồng)
- Ảnh chụp: {$date_label}{$sensor_info}{$plant_knowledge}

DANH MỤC VẬT TƯ (DÙNG ĐỂ GỢI Ý MUA HÀNG NẾU CẦN):
{SUPPLIES_INJECTION}

QUY TRÌNH PHÂN TÍCH (BẮT BUỘC):
1. KIỂM TRA SỰ CỐ CƠ HỌC & NGOẠI LỰC: Xem giá thể/đất có bị đào bới không (chuột, mèo, côn trùng), thân cây có bị gãy gập, đổ rạp, hoặc bật gốc do tác động vật lý không.
2. KIỂM TRA SỨC KHỎE THÂN/LÁ: Phân tích màu sắc lá (vàng, cháy mép, đốm đen, bạc lá), hình thái thân (vống quá cao, teo gốc, lở cổ rễ) và ngọn (xoăn, héo rũ).
3. KIỂM TRA MÔI TRƯỜNG & DINH DƯỠNG: Đánh giá độ ẩm bề mặt (sũng nước, núng nước gây úng hay khô khốc), ánh sáng (thiếu sáng gây vống hay thừa sáng gây cháy lá).
4. KIỂM TRA GIAI ĐOẠN PHÁT TRIỂN: Nhận diện cây đang ở giai đoạn nào (hạt nảy mầm, đội mũ vỏ hạt, cây con, trưởng thành, ra hoa, đậu quả). Chỉ chọn \"Gieo hạt\" khi HOÀN TOÀN trống, không thấy mầm.

TRẢ VỀ JSON THUẦN (không markdown) THEO CẤU TRÚC ĐẦY ĐỦ SAU:
{
  \"analytics_status\": \"success\",
  \"anomalies_detected\": <true/false>,
  \"primary_issue\": \"Tên vấn đề lớn nhất phát hiện được (Ví dụ: Pest Damage, Root Rot, Light Stretch, Normal...)\",
  \"detailed_analysis\": {
    \"substrate_status\": \"Trạng thái đất/giá thể (Ví dụ: Bị cào bới, Quá ẩm sũng, Khô hạn, Bình thường)\",
    \"plant_morphology\": \"Tình trạng thân/lá/mầm (Ví dụ: Thân gãy gập, Lá vàng úng, Khỏe mạnh)\",
    \"environmental_match\": \"Đánh giá môi trường qua ảnh (Ví dụ: Thiếu sáng, Thừa nhiệt, Ổn định)\"
  },
  \"confidence_score\": <0.0 đến 1.0>,
  \"level\": <1-5; 1=xuất sắc, 5=nghiêm trọng>,
  \"color\": <\"xanh-non\"|\"xanh\"|\"xam\"|\"vang\"|\"cam\"|\"do\">,
  \"label\": <nhãn ngắn ≤6 từ cho UI>,
  \"current_stage\": <\"Gieo hạt\"|\"Nảy mầm\"|\"Cây con\"|\"Phát triển sinh dưỡng\"|\"Ra hoa & thu phấn\"|\"Đậu quả & phát triển quả\"|\"Chín & thu hoạch\">,
  \"summary\": <Gộp chung detailed_analysis thành 2-3 câu mô tả dễ hiểu cho người trồng, phân tích rõ nguyên nhân>,
  \"recommendation\": <1-2 câu lời khuyên. LƯU Ý TỐI QUAN TRỌNG: Nếu khoang trống, BẮT BUỘC chèn thêm chuỗi \"[UPSELL] MÃ_HẠT_GIỐNG\" vào cuối câu. Nếu cây bị vàng lá/thiếu chất, BẮT BUỘC chèn \"[UPSELL] MÃ_DINH_DƯỠNG\" vào cuối câu.>,
  \"actions\": [
    \"Hành động khẩn cấp 1 cần người trồng làm ngay\",
    \"Hành động khắc phục 2\",
    \"Hành động phòng ngừa dài hạn\"
  ]
}";

    return str_replace('{SUPPLIES_INJECTION}', $supplies_text, $prompt);
}

// ── Call Gemini Vision API (accepts raw image bytes) ─────────────────────────

/**
 * Try each model in order, stop on first success or non-429 error.
 */
function aitrongcay_call_gemini_vision_raw(string $image_data, string $prompt, string $api_key): array
{
    // Update to latest Gemini 2.5 models as seen in Google AI Studio
    $models = [
        ['id' => 'gemini-2.5-flash', 'api' => 'v1beta'],
        ['id' => 'gemini-2.5-pro', 'api' => 'v1beta'],
    ];

    $contents = [
        [
            'parts' => [
                ['inlineData' => ['mimeType' => 'image/jpeg', 'data' => base64_encode($image_data)]],
                ['text' => $prompt],
            ],
        ]
    ];

    $gen_config = ['temperature' => 0.2, 'maxOutputTokens' => 4096];

    $attempt_log = [];

    foreach ($models as $model_cfg) {
        $model = $model_cfg['id'];
        $api_ver = $model_cfg['api'];

        // Disable thinking for Flash to free all tokens for JSON output
        $request_body = ['contents' => $contents, 'generationConfig' => $gen_config];
        if ($model === 'gemini-2.5-flash') {
            $request_body['generationConfig']['thinkingConfig'] = ['thinkingBudget' => 0];
        }

        $response = wp_remote_post(
            'https://generativelanguage.googleapis.com/' . $api_ver . '/models/' . $model . ':generateContent?key=' . rawurlencode($api_key),
            [
                'timeout' => 45,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => wp_json_encode($request_body),
            ]
        );

        if (is_wp_error($response)) {
            $attempt_log[] = $model . '/' . $api_ver . ': WP_Error(' . $response->get_error_message() . ')';
            continue;
        }

        $http_code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);
        $api_msg = is_array($decoded) ? ($decoded['error']['message'] ?? '') : substr($body, 0, 120);

        if ($http_code === 429) {
            $attempt_log[] = $model . ':quota_exceeded';
            continue;
        }

        if ($http_code === 404) {
            $attempt_log[] = $model . ':not_found';
            continue;
        }

        if ($http_code !== 200) {
            $attempt_log[] = $model . '/' . $api_ver . ':' . $http_code . ($api_msg ? '(' . $api_msg . ')' : '');
            break;
        }

        if (!is_array($decoded)) {
            $attempt_log[] = $model . '/' . $api_ver . ': invalid response';
            continue;
        }

        $text = (string) ($decoded['candidates'][0]['content']['parts'][0]['text'] ?? '');
        if ($text === '') {
            $attempt_log[] = $model . '/' . $api_ver . ': empty result';
            continue;
        }

        $result = json_decode($text, true);
        if (!is_array($result)) {
            $cleaned = preg_replace('/^```(?:json)?\s*/m', '', $text);
            $cleaned = preg_replace('/```\s*$/m', '', (string) $cleaned);
            $result = json_decode(trim((string) $cleaned), true);
        }
        // Last resort: extract the first {...} block (handles preamble/postamble text)
        if (!is_array($result)) {
            $start = strpos($text, '{');
            $end = strrpos($text, '}');
            if ($start !== false && $end !== false && $end > $start) {
                $result = json_decode(substr($text, $start, $end - $start + 1), true);
            }
        }

        if (is_array($result)) {
            $result['_model'] = $model;
            return $result;
        }

        // Log first 200 chars of raw text to help diagnose format issues
        $attempt_log[] = $model . '/' . $api_ver . ': JSON parse failed (raw: ' . substr(str_replace("\n", ' ', $text), 0, 200) . ')';
        continue; // try next model instead of giving up
    }

    $all_quota = count($attempt_log) > 0 && count(array_filter($attempt_log, fn($e) => str_contains($e, 'quota_exceeded'))) === count($attempt_log);
    if ($all_quota) {
        return ['error' => 'Gemini: Đã hết quota API. Vào https://aistudio.google.com tạo API key mới hoặc bật billing để tiếp tục.'];
    }

    return ['error' => 'Gemini thất bại — ' . implode(' | ', $attempt_log)];
}

// Keep old signature for backward compat
function aitrongcay_call_gemini_vision(string $image_path, string $prompt, string $api_key): array
{
    if (!file_exists($image_path)) {
        return ['error' => 'Không tìm thấy file ảnh: ' . basename($image_path)];
    }
    $data = @file_get_contents($image_path);
    if ($data === false) {
        return ['error' => 'Không thể đọc file ảnh.'];
    }
    return aitrongcay_call_gemini_vision_raw($data, $prompt, $api_key);
}

// ── AJAX handler ─────────────────────────────────────────────────────────────

function aitrongcay_analyze_timelapse_gemini_ajax(): void
{
    aitrongcay_require_portal_nonce();

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Cần đăng nhập.'], 403);
    }

    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    $pot_code = strtoupper(trim(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''))));

    if ($garden_key === '' || $pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu garden_key hoặc pot_code.'], 400);
    }
    if (!aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Không có quyền xem vườn này.'], 403);
    }

    // Cache check: return DB result if analysis < 2 hours old and not forced
    $force_refresh = !empty($_POST['force']);
    if (!$force_refresh && function_exists('aitrongcay_get_db_pots')) {
        foreach (aitrongcay_get_db_pots($garden_key) as $cached_pot) {
            if (strtoupper(trim((string) ($cached_pot['pot_code'] ?? ''))) !== $pot_code) {
                continue;
            }
            $cached_at = trim((string) ($cached_pot['latest_analysis_updated_at'] ?? ''));
            $cached_lvl = (int) ($cached_pot['latest_analysis_level'] ?? 0);
            $cached_sum = trim((string) ($cached_pot['latest_analysis_summary'] ?? ''));
            if ($cached_at !== '' && $cached_lvl > 0 && $cached_sum !== '') {
                $age_seconds = time() - (int) strtotime($cached_at);
                if ($age_seconds >= 0 && $age_seconds < 7200) {
                    $cached_actions = [];
                    if (!empty($cached_pot['latest_analysis_actions'])) {
                        $decoded = json_decode((string) $cached_pot['latest_analysis_actions'], true);
                        if (is_array($decoded)) {
                            $cached_actions = array_values($decoded);
                        }
                    }
                    wp_send_json_success([
                        'pot_code' => $pot_code,
                        'analysis' => [
                            'level' => $cached_lvl,
                            'color' => (string) ($cached_pot['latest_analysis_color'] ?? 'xam'),
                            'label' => (string) ($cached_pot['latest_analysis_label'] ?? 'Ổn định'),
                            'current_stage' => (string) ($cached_pot['latest_analysis_current_stage'] ?? ''),
                            'summary' => $cached_sum,
                            'recommendation' => (string) ($cached_pot['latest_analysis_recommendation'] ?? ''),
                            'actions' => $cached_actions,
                            'escalate_if' => [],
                            'updated_at' => $cached_at,
                            'updated_at_formatted' => wp_date('H:i d/m/Y', strtotime($cached_at . ' UTC'), new DateTimeZone('Asia/Ho_Chi_Minh')),
                        ],
                        'photo_source' => 'cache',
                        'photo_date' => substr($cached_at, 0, 10),
                        'cached' => true,
                    ]);
                }
            }
            break;
        }
    }

    $api_key = aitrongcay_get_gemini_api_key();
    if ($api_key === '') {
        wp_send_json_error(['message' => 'Chưa cấu hình Gemini API key. Vào WP Admin → Appearance → Gemini API Key.'], 503);
    }

    // Get pot record for context and latest image
    $pot_record = ['name' => $pot_code, 'code' => $pot_code];
    if (function_exists('aitrongcay_get_db_pots')) {
        foreach (aitrongcay_get_db_pots($garden_key) as $pot) {
            if (strtoupper(trim((string) ($pot['pot_code'] ?? ''))) === $pot_code) {
                $pot_record = $pot;
                break;
            }
        }
    }

    $image_url = trim((string) ($pot_record['image_url'] ?? ''));
    if ($image_url === '' || $image_url === '0') {
        // Fallback to latest timelapse if available
        $stream_slug = trim((string) ($pot_record['video_url'] ?? $pot_record['video'] ?? ''));
        if ($stream_slug !== '') {
            $latest = aitrongcay_get_latest_timelapse_for_pot($garden_key, $stream_slug);
            if ($latest) {
                $image_url = $latest['url'];
            }
        }

        if ($image_url === '' || $image_url === '0') {
            wp_send_json_error(['message' => 'Khoang này chưa có ảnh nào để phân tích. Hãy chờ hệ thống chụp ảnh hoặc tự upload một ảnh mới nhé.'], 400);
        }
    }

    // Robustly find the local path for the image to avoid wp_remote_get loopback issues on localhost
    $image_data = null;
    $upload_dir = wp_upload_dir();
    $base_url = $upload_dir['baseurl'];
    $base_dir = $upload_dir['basedir'];

    // Convert relative URLs or mismatched scheme URLs to local paths
    $local_path = '';

    // Handle case where image_url is stored as an absolute path starting with site url or upload url
    if (str_contains($image_url, '/wp-content/uploads/')) {
        $parts = explode('/wp-content/uploads/', $image_url);
        $relative_path = end($parts);
        // URL decode to handle spaces and special characters in filenames
        $relative_path = urldecode($relative_path);
        $local_path = trailingslashit($base_dir) . ltrim($relative_path, '/');
    }

    if ($local_path !== '' && file_exists($local_path)) {
        $image_data = @file_get_contents($local_path);
    }

    // Fallback 1: HTTP fetch if it's an external URL or loopback works
    if ($image_data === null || $image_data === false) {
        // Only try HTTP if it looks like a full URL
        if (str_starts_with($image_url, 'http')) {
            $response = wp_remote_get($image_url, ['timeout' => 10, 'sslverify' => false]);
            if (!is_wp_error($response)) {
                $image_data = wp_remote_retrieve_body($response);
            }
        }
    }

    if ($image_data === null || $image_data === '' || $image_data === false) {
        wp_send_json_error(['message' => 'Không thể đọc được file ảnh của khoang (' . esc_html($image_url) . '). Vui lòng thử upload lại.'], 500);
    }

    $photo_meta = ['source' => 'gallery', 'date' => wp_date('Y-m-d'), 'time' => wp_date('H-i'), 'sensors' => []];

    $prompt = aitrongcay_build_gemini_analysis_prompt($pot_record, $photo_meta);
    $result = aitrongcay_call_gemini_vision_raw((string) $image_data, $prompt, $api_key);

    if (isset($result['error'])) {
        wp_send_json_error(['message' => 'Gemini: ' . $result['error']]);
    }

    $analysis = [
        'level' => max(1, min(5, (int) ($result['level'] ?? 2))),
        'color' => (string) ($result['color'] ?? 'xam'),
        'label' => (string) ($result['label'] ?? 'Đang theo dõi'),
        'current_stage' => (string) ($result['current_stage'] ?? ''),
        'summary' => (string) ($result['summary'] ?? ''),
        'recommendation' => (string) ($result['recommendation'] ?? ''),
        'actions' => array_values((array) ($result['actions'] ?? [])),
        'analytics_status' => (string) ($result['analytics_status'] ?? 'success'),
        'anomalies_detected' => (bool) ($result['anomalies_detected'] ?? false),
        'primary_issue' => (string) ($result['primary_issue'] ?? ''),
        'detailed_analysis' => (array) ($result['detailed_analysis'] ?? []),
        'confidence_score' => (float) ($result['confidence_score'] ?? 0.0),
        'escalate_if' => [],
        'updated_at' => current_time('mysql'),
        'updated_at_formatted' => wp_date('H:i d/m/Y', strtotime(current_time('mysql') . ' UTC'), new DateTimeZone('Asia/Ho_Chi_Minh')),
    ];

    if (function_exists('aitrongcay_store_pot_analysis')) {
        $old_stage = trim((string) ($pot_record['latest_analysis_current_stage'] ?? ''));
        $new_stage = trim((string) ($analysis['current_stage'] ?? ''));

        aitrongcay_store_pot_analysis($garden_key, $pot_code, $analysis);

        if ($old_stage !== '' && $new_stage !== '' && strcasecmp($old_stage, $new_stage) !== 0) {
            $plant_name = trim((string) ($pot_record['pot_name'] ?? $pot_record['name'] ?? $pot_code));
            if (function_exists('aitrongcay_notify_plant_stage_change')) {
                aitrongcay_notify_plant_stage_change($garden_key, $pot_code, $plant_name, $old_stage, $new_stage);
            }
        }
    }

    wp_send_json_success([
        'pot_code' => $pot_code,
        'analysis' => $analysis,
        'photo_source' => $photo_meta['source'],
        'photo_date' => $photo_meta['date'] ?? '',
    ]);
}
add_action('wp_ajax_aitrongcay_analyze_timelapse_gemini', 'aitrongcay_analyze_timelapse_gemini_ajax');

// ── Admin settings: Gemini API key ───────────────────────────────────────────

function aitrongcay_gemini_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    if (isset($_POST['aitrongcay_gemini_save']) && check_admin_referer('aitrongcay_gemini_settings')) {
        $key = sanitize_text_field((string) wp_unslash($_POST['aitrongcay_gemini_api_key'] ?? ''));
        if ($key !== '') {
            update_option('aitrongcay_gemini_api_key', $key);
        }
        echo '<div class="notice notice-success"><p>Đã lưu Gemini API key.</p></div>';
    }
    $current_key = aitrongcay_get_gemini_api_key();
    $masked = $current_key !== ''
        ? substr($current_key, 0, 6) . str_repeat('*', max(0, strlen($current_key) - 10)) . substr($current_key, -4)
        : '';
    ?>
    <div class="wrap">
        <h1>Gemini API Key</h1>
        <p>Dùng để phân tích ảnh live camera / timelapse bằng Google Gemini Vision. <a
                href="https://aistudio.google.com/app/apikey" target="_blank">Lấy API key miễn phí tại Google AI Studio</a>.
        </p>
        <form method="post">
            <?php wp_nonce_field('aitrongcay_gemini_settings'); ?>
            <table class="form-table">
                <tr>
                    <th>Gemini API Key</th>
                    <td>
                        <input type="text" autocomplete="off" name="aitrongcay_gemini_api_key" class="regular-text"
                            placeholder="<?php echo esc_attr($masked !== '' ? $masked : 'AIza...'); ?>" value="">
                        <?php if ($current_key !== ''): ?>
                            <p class="description" style="color:#2a9d5c">✓ Key hiện tại: <?php echo esc_html($masked); ?></p>
                        <?php else: ?>
                            <p class="description" style="color:#c0392b">Chưa có key — AI phân tích sẽ không hoạt động.</p>
                        <?php endif; ?>
                        <p class="description">Để trống nếu không muốn thay đổi key đã lưu.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Lưu API Key', 'primary', 'aitrongcay_gemini_save'); ?>
        </form>
    </div>
    <?php
}

add_action('admin_menu', static function (): void {
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Gemini API Key',
        'Gemini API Key',
        'manage_options',
        'aitrongcay-gemini-key',
        'aitrongcay_gemini_settings_page'
    );
}, 100);

// ── TASK 3.4: Reset vụ mùa (Bắt đầu vụ trồng mới) ─────────────────────────
function aitrongcay_reset_pot_crop_ajax(): void
{
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
    }

    $garden_key = sanitize_text_field(wp_unslash($_POST['garden_key'] ?? ''));
    $pot_code = sanitize_text_field(wp_unslash($_POST['pot_code'] ?? ''));

    if ($garden_key === '' || $pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu tham số khoang.']);
    }

    // Kiểm tra quyền
    $current_user = wp_get_current_user();
    if (!function_exists('aitrongcay_user_can_control_garden') || !aitrongcay_user_can_control_garden($garden_key, $current_user->ID)) {
        wp_send_json_error(['message' => 'Bạn không có quyền dọn khoang này.']);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'aitr_garden_pots';
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
        wp_send_json_error(['message' => 'Cơ sở dữ liệu chưa sẵn sàng.']);
    }

    // 1. Xoá tất cả attachments hình ảnh được gắn với khoang này
    $args = [
        'post_type' => 'attachment',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_aitrongcay_photo_garden_key',
                'value' => $garden_key,
            ],
            [
                'key' => '_aitrongcay_pot_code',
                'value' => $pot_code,
            ],
        ],
        'fields' => 'ids',
    ];
    $attachments = get_posts($args);
    foreach ($attachments as $attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }

    // 2. Xoá tất cả ảnh/video timelapse của khoang này trong filesystem
    if (function_exists('aitrongcay_resolve_pot_webcam_info')) {
        $cam_info = aitrongcay_resolve_pot_webcam_info($garden_key, $pot_code);
        $stream_slug = $cam_info['slug'] ?? '';
        if ($stream_slug !== '') {
            $upload_info = wp_upload_dir();
            $fs_garden_key = sanitize_key($garden_key);
            $base_uploads = $upload_info['basedir'] . '/timelapse';
            if (!is_dir($base_uploads . '/' . $fs_garden_key . '/' . sanitize_key($stream_slug))) {
                if (is_dir($base_uploads . '/global/' . sanitize_key($stream_slug))) {
                    $fs_garden_key = 'global';
                }
            }
            $stream_dir = $base_uploads . '/' . $fs_garden_key . '/' . sanitize_key($stream_slug);

            $archive_dir = $upload_info['basedir'] . '/timelapse_archive/' . $fs_garden_key . '_' . sanitize_key($stream_slug) . '_' . time();
            wp_mkdir_p(dirname($archive_dir));
            rename($stream_dir, $archive_dir);

            // Chuyển việc xoá thư mục rác sang cronjob (sau khi render video xong)
            $current_user = wp_get_current_user();
            wp_schedule_single_event(time(), 'aitrongcay_render_harvest_video_cron', [
                $archive_dir,
                $current_user->user_email,
                $garden_key,
                $pot_code
            ]);
        }
    }

    $now = current_time('mysql');
    $updated = $wpdb->update(
        $table,
        [
            'created_at' => $now,
            'image_url' => '',
            'latest_photo_id' => 0,
            'latest_photo_at' => null,
            'latest_analysis_level' => 0,
            'latest_analysis_color' => '',
            'latest_analysis_label' => '',
            'latest_analysis_current_stage' => '',
            'latest_analysis_summary' => 'Vừa dọn dẹp và bắt đầu lứa mới. Đang chờ phân tích AI đầu tiên...',
            'latest_analysis_actions' => '',
            'latest_analysis_escalate' => '',
            'latest_analysis_updated_at' => $now,
            'updated_at' => $now
        ],
        [
            'garden_key' => $garden_key,
            'pot_code' => $pot_code
        ]
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Lỗi lưu database.']);
    }

    wp_send_json_success(['message' => 'Đã reset vụ mùa thành công!']);
}
add_action('wp_ajax_aitrongcay_reset_pot_crop', 'aitrongcay_reset_pot_crop_ajax');
