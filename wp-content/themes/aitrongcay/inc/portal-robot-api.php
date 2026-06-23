<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function () {
    register_rest_route('aitrongcay/v1', '/robot/capture', [
        'methods' => 'POST',
        'callback' => 'aitrongcay_robot_capture_callback',
        'permission_callback' => '__return_true', // You should add a token check here in production
    ]);
});

function aitrongcay_robot_capture_callback(WP_REST_Request $request)
{
    $command = sanitize_text_field($request->get_param('command') ?: '');
    $garden_key = sanitize_text_field($request->get_param('garden_key'));
    $pot_code = sanitize_text_field($request->get_param('pot_code'));

    // BƯỚC MỚI: NỘI SUY TỌA ĐỘ VẬT LÝ SANG LOGIC (MAPPING)
    // Nếu có truyền lệnh command (ví dụ N01H0), hệ thống sẽ tự tìm Chủ Vườn
    if (preg_match('/^N(\d+)H(\d+)$/i', $command, $matches)) {
        $target_node = (int)$matches[1]; // VD: 1 từ N01
        $target_tier = (int)$matches[2]; // VD: 0 từ H0

        global $wpdb;
        // Lấy tất cả rack để test, không phân biệt assigned hay inventory
        $assigned_racks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aitr_garden_racks ORDER BY rack_code ASC, id ASC", ARRAY_A);
        
        $current_node = 0;
        foreach ($assigned_racks as $rack) {
            $slot_count = (int)($rack['slot_count'] ?? 0);
            $compartments = max(1, (int)ceil($slot_count / 2));
            
            $rack_start_node = $current_node;
            $rack_end_node = $current_node + $compartments - 1;
            
            if ($target_node >= $rack_start_node && $target_node <= $rack_end_node) {
                $resolved_garden = $rack['garden_key'];
                $compartment_inside_rack = $target_node - $rack_start_node + 1;
                $tray_position = ($target_tier === 0) ? 1 : 2;
                $target_slot_index = ($compartment_inside_rack - 1) * 2 + $tray_position;
                
                // Tìm pot_code
                if (function_exists('aitrongcay_get_rack_slots')) {
                    $slots = aitrongcay_get_rack_slots($resolved_garden);
                    foreach ($slots as $s) {
                        if (($s['slot_index'] ?? 0) == $target_slot_index) {
                            $garden_key = $resolved_garden; // Nạp đè garden_key chuẩn
                            $pot_code = $s['pot_code'];     // Nạp đè pot_code chuẩn
                            break 2;
                        }
                    }
                }
            }
            $current_node += $compartments;
        }
    }

    if ($garden_key === '' || $pot_code === '') {
        $debug_msg = "Lỗi Mapping: Lệnh {$command}. ";
        if (empty($assigned_racks)) {
            $debug_msg .= "Không tìm thấy rack nào có status='assigned' trong Database. ";
        } else {
            $debug_msg .= "Tìm thấy " . count($assigned_racks) . " racks, nhưng tọa độ Node {$target_node} không nằm trong rack nào. ";
        }
        return new WP_Error('missing_params', $debug_msg, ['status' => 400]);
    }

    // 1. Tìm stream_url của khoang này
    $stream_url = '';
    if (function_exists('aitrongcay_get_rack_slot_camera_stream_url')) {
        $stream_url = aitrongcay_get_rack_slot_camera_stream_url($garden_key, $pot_code);
    }

    // Nếu rỗng, thử tìm trong rack_configs (giống logic ở dashboard)
    if ($stream_url === '' && function_exists('aitrongcay_get_rack_slots')) {
        // Find slot index
        $slots = aitrongcay_get_rack_slots($garden_key);
        $slot_index = 0;
        foreach ($slots as $s) {
            if (($s['pot_code'] ?? '') === $pot_code) {
                $slot_index = (int) ($s['slot_index'] ?? 0);
                break;
            }
        }
        if ($slot_index > 0) {
            global $wpdb;
            $table = $wpdb->prefix . 'aitr_rack_configs';
            $racks = $wpdb->get_results($wpdb->prepare("SELECT config_json FROM {$table} WHERE garden_key = %s ORDER BY rack_index ASC", $garden_key), ARRAY_A);
            $flat_trays = [];
            foreach ($racks as $row) {
                $config = json_decode($row['config_json'], true);
                if (is_array($config) && !empty($config['trays'])) {
                    foreach ($config['trays'] as $t) {
                        $flat_trays[] = trim((string) ($t['webcam_url'] ?? ''));
                    }
                }
            }
            if (isset($flat_trays[$slot_index - 1]) && $flat_trays[$slot_index - 1] !== '') {
                $stream_url = $flat_trays[$slot_index - 1];
            }
        }
    }

    if ($stream_url === '') {
        return new WP_Error('no_camera', 'Không tìm thấy cấu hình camera cho khoang này', ['status' => 404]);
    }

    // 2. Chuyển đổi webrtc url thành api/frame.jpeg
    if (strpos($stream_url, 'src=') === false) {
        return new WP_Error('invalid_camera', 'URL camera không hỗ trợ snapshot (cần chuẩn go2rtc)', ['status' => 400]);
    }

    $parsed = parse_url($stream_url);
    $base = ($parsed['scheme'] ?? 'http') . '://' . ($parsed['host'] ?? 'localhost');
    if (!empty($parsed['port'])) {
        $base .= ':' . $parsed['port'];
    }
    parse_str($parsed['query'] ?? '', $qp);
    $slug = sanitize_key($qp['src'] ?? '');

    if ($slug === '') {
        return new WP_Error('invalid_src', 'Không tìm thấy src trong URL', ['status' => 400]);
    }

    $snapshot_url = $base . '/api/frame.jpeg?src=' . rawurlencode($slug);

    // 3. Tải ảnh từ go2rtc
    $ch = curl_init($snapshot_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $image_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200 || empty($image_data)) {
        return new WP_Error('capture_failed', 'Không thể chụp ảnh từ camera: ' . $http_code, ['status' => 500]);
    }

    // 4. Lưu ảnh
    $fs_gk = sanitize_key($garden_key);
    $date_folder = gmdate('Y-m-d');
    $time_str = gmdate('H-i-s');

    $upload_dir = wp_upload_dir();
    $base_tl_dir = $upload_dir['basedir'] . '/timelapse';
    if (!is_dir($base_tl_dir . '/' . $fs_gk . '/' . $slug)) {
        if (is_dir($base_tl_dir . '/global/' . $slug)) {
            $fs_gk = 'global';
        }
    }
    $base_dir = $base_tl_dir . '/' . $fs_gk . '/' . $slug . '/' . $date_folder;

    if (!wp_mkdir_p($base_dir)) {
        return new WP_Error('mkdir_failed', 'Không thể tạo thư mục lưu ảnh', ['status' => 500]);
    }

    $filename = $time_str . '.jpg';
    $filepath = $base_dir . '/' . $filename;

    if (file_put_contents($filepath, $image_data) === false) {
        return new WP_Error('save_failed', 'Lỗi ghi file ảnh', ['status' => 500]);
    }

    $file_url = $upload_dir['baseurl'] . '/timelapse/' . $fs_gk . '/' . $slug . '/' . $date_folder . '/' . $filename;
    $relative_url = wp_make_link_relative($file_url);

    // 5. Cập nhật Database
    global $wpdb;
    $table = $wpdb->prefix . 'aitr_garden_pots';
    $pot = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s AND pot_code = %s", $garden_key, $pot_code), ARRAY_A);

    if ($pot) {
        $update_data = [
            'latest_photo_at' => current_time('mysql', 1)
        ];

        // Nếu chưa có ảnh đại diện tĩnh, hoặc khách không có camera (chỉ hiện ảnh chụp), thì update luôn
        // Ở đây ta update luôn vào cột image
        $update_data['image'] = $relative_url;

        $wpdb->update(
            $table,
            $update_data,
            ['garden_key' => $garden_key, 'code' => $pot_code],
            ['%s', '%s'],
            ['%s', '%s']
        );

        // 6. Xếp hàng phân tích AI (Action Scheduler)
        if (function_exists('as_enqueue_async_action')) {
            as_enqueue_async_action('aitrongcay_robot_analyze_pot', [
                'garden_key' => $garden_key,
                'pot_code' => $pot_code,
                'image_path' => $filepath
            ]);
        } else {
            // Fallback nếu không có Action Scheduler
            wp_schedule_single_event(time() + 5, 'aitrongcay_robot_analyze_pot', [$garden_key, $pot_code, $filepath]);
        }
    }

    return rest_ensure_response([
        'status' => 'success',
        'message' => 'Đã chụp và lưu ảnh thành công',
        'url' => $relative_url,
        'snapshot_url' => $snapshot_url // debug
    ]);
}

// 7. Background Worker xử lý AI
add_action('aitrongcay_robot_analyze_pot', 'aitrongcay_robot_analyze_pot_worker', 10, 3);
function aitrongcay_robot_analyze_pot_worker($garden_key, $pot_code, $image_path)
{
    if (!file_exists($image_path)) {
        return;
    }

    // Gọi hàm phân tích (tương tự như aitrongcay_analyze_timelapse_gemini_ajax)
    if (function_exists('aitrongcay_call_gemini_vision_raw') && function_exists('aitrongcay_build_gemini_analysis_prompt')) {

        global $wpdb;
        $table = $wpdb->prefix . 'aitr_garden_pots';
        $pot = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s AND code = %s", $garden_key, $pot_code), ARRAY_A);

        if (!$pot)
            return;

        $mime_type = 'image/jpeg';
        $prompt = aitrongcay_build_gemini_analysis_prompt($pot);
        $file_data = file_get_contents($image_path);

        $api_key = function_exists('aitrongcay_get_gemini_api_key') ? aitrongcay_get_gemini_api_key() : '';
        if ($api_key === '')
            return;

        $response = aitrongcay_call_gemini_vision_raw($api_key, 'gemini-1.5-flash', $prompt, $file_data, $mime_type);

        if (is_wp_error($response)) {
            return;
        }

        $json_str = trim((string) ($response['text'] ?? ''));
        $json_str = preg_replace('/^```json\s*|\s*```$/i', '', $json_str);
        $json_str = trim($json_str);

        $analysis = json_decode($json_str, true);
        if (!is_array($analysis))
            return;

        // Cập nhật kết quả vào DB
        $level = (int) ($analysis['level'] ?? 2);
        $label = trim((string) ($analysis['label'] ?? ''));
        $summary = trim((string) ($analysis['summary'] ?? ''));
        $actions = is_array($analysis['actions'] ?? null) ? $analysis['actions'] : [];
        $recommendation = trim((string) ($analysis['recommendation'] ?? ''));
        $current_stage = trim((string) ($analysis['current_stage'] ?? ''));

        $update_data = [
            'latest_analysis_json' => wp_json_encode($analysis, JSON_UNESCAPED_UNICODE),
            'latest_analysis_level' => $level,
            'latest_analysis_label' => $label,
            'latest_analysis_summary' => $summary,
            'latest_analysis_actions' => empty($actions) ? null : wp_json_encode($actions, JSON_UNESCAPED_UNICODE),
            'latest_analysis_recommendation' => $recommendation,
            'latest_analysis_current_stage' => $current_stage,
            'latest_analysis_updated_at' => current_time('mysql', 1),
        ];

        $wpdb->update(
            $table,
            $update_data,
            ['garden_key' => $garden_key, 'code' => $pot_code],
            null,
            ['%s', '%s']
        );

        // --- HỆ THỐNG CẢNH BÁO CHỦ ĐỘNG (PUSH ALERT) ---
        if ($level >= 1) {
            $garden_record = function_exists('aitrongcay_get_garden_record') ? aitrongcay_get_garden_record($garden_key) : null;
            $owner_id = $garden_record['user_id'] ?? $garden_record['owner_id'] ?? 0;

            if ($owner_id > 0) {
                $owner = get_userdata($owner_id);
                if ($owner && is_email($owner->user_email)) {
                    $pot_name = $pot['pot_name'] ?? $pot['name'] ?? $pot_code;
                    $subject = "[AiTrongCay] Cảnh báo hệ thống: Khoang {$pot_name} cần chú ý!";
                    $message = "Xin chào {$owner->display_name},\n\n";
                    $message .= "Hệ thống AI vừa quét tự động khu vườn của bạn lúc " . current_time('H:i d/m/Y') . ".\n";
                    $message .= "🚨 CHÚ Ý: Tại Khoang {$pot_name}, AI phát hiện dấu hiệu bất thường (Mức độ cảnh báo: Cấp {$level}).\n\n";

                    $message .= "📊 KẾT QUẢ CHẨN ĐOÁN:\n";
                    $message .= "- Vấn đề: {$label}\n";
                    $message .= "- Tóm tắt: {$summary}\n\n";

                    $message .= "💡 KHUYẾN NGHỊ XỬ LÝ:\n";
                    $message .= "{$recommendation}\n\n";

                    if (!empty($actions)) {
                        $message .= "🛠 HÀNH ĐỘNG CẦN LÀM NGAY:\n";
                        foreach ($actions as $action) {
                            $message .= "- {$action}\n";
                        }
                        $message .= "\n";
                    }

                    $message .= "Vui lòng đăng nhập vào hệ thống AiTrongCay.com để xem hình ảnh trực tiếp và thực hiện các biện pháp khắc phục.\n\n";
                    $message .= "Trân trọng,\nTrợ lý ảo Nông nghiệp - AiTrongCay";

                    wp_mail($owner->user_email, $subject, $message);
                }
            }
        }
    }
}
