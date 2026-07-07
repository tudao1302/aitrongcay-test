<?php
/**
 * Rack Data Handoff — AI Trồng Cây
 *
 * Khi thu hồi rack (release): đóng gói snapshot dữ liệu của khách hàng cũ.
 * Khi giao rack (assign): xoá cache cũ, chuyển luồng dữ liệu camera/timelapse
 * sang garden_key của khách hàng mới.
 *
 * Hook vào:
 *   - aitrongcay_release_rack_to_inventory()  → action 'aitrongcay_before_rack_release'
 *   - assign_rack action (unified admin beta)  → action 'aitrongcay_after_rack_assign'
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// ─── Hook: TRƯỚC khi thu hồi rack ────────────────────────────────────────────
// Đóng gói snapshot dữ liệu của khách hàng hiện tại (KH cũ).
add_action('aitrongcay_before_rack_release', 'aitrongcay_handoff_archive_old_customer', 10, 2);

/**
 * Đóng gói dữ liệu của KH cũ trước khi rack rời khỏi họ.
 *
 * @param int    $rack_id         ID của rack đang được thu hồi.
 * @param string $from_garden_key Garden key của KH cũ (đang giữ rack).
 */
function aitrongcay_handoff_archive_old_customer(int $rack_id, string $from_garden_key): void {
    if ($rack_id <= 0 || $from_garden_key === '') {
        return;
    }

    $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
    if (! $rack) {
        return;
    }

    $rack_code = sanitize_key((string) ($rack['rack_code'] ?? 'rack_' . $rack_id));
    $archived_at = current_time('mysql');

    // 1. Lưu snapshot cấu hình thiết bị của KH cũ vào option có timestamp
    $old_cfg = function_exists('aitrongcay_get_rack_monitor_configs')
        ? aitrongcay_get_rack_monitor_configs($from_garden_key)
        : [];

    if (! empty($old_cfg)) {
        $archive_key = 'aitrongcay_rack_archive_' . sanitize_key($from_garden_key) . '_' . $rack_code;
        $existing_archives = (array) get_option($archive_key, []);
        $existing_archives[] = [
            'rack_id'       => $rack_id,
            'rack_code'     => $rack_code,
            'garden_key'    => $from_garden_key,
            'archived_at'   => $archived_at,
            'rack_configs'  => $old_cfg,
        ];
        // Giữ tối đa 5 snapshot gần nhất
        if (count($existing_archives) > 5) {
            $existing_archives = array_slice($existing_archives, -5);
        }
        update_option($archive_key, $existing_archives, false);
    }

    // 2. Copy (không xoá) thư mục timelapse của KH cũ sang thư mục archive
    aitrongcay_handoff_archive_timelapse_folder($from_garden_key, $rack_id, $rack_code);

    // 3. Xoá transient cache sensor của rack này cho KH cũ
    aitrongcay_handoff_flush_sensor_cache($from_garden_key, $rack_id);

    // 3.5 Xoá dữ liệu các khoang (pots) thuộc rack này khỏi garden của KH cũ
    global $wpdb;
    $slots_table = $wpdb->prefix . 'aitr_rack_slots';
    $pots_table  = function_exists('aitrongcay_garden_pots_table') ? aitrongcay_garden_pots_table() : ($wpdb->prefix . 'aitr_garden_pots');
    
    $pot_codes = $wpdb->get_col($wpdb->prepare(
        "SELECT pot_code FROM {$slots_table} WHERE rack_id = %d AND pot_code != ''",
        $rack_id
    ));
    
    if (!empty($pot_codes)) {
        $placeholders = implode(',', array_fill(0, count($pot_codes), '%s'));
        $delete_sql = "DELETE FROM {$pots_table} WHERE garden_key = %s AND pot_code IN ($placeholders)";
        $delete_args = array_merge([$from_garden_key], $pot_codes);
        $wpdb->query($wpdb->prepare($delete_sql, ...$delete_args));
        error_log("[RACK HANDOFF] 🗑️ Đã xoá " . count($pot_codes) . " dữ liệu khoang trồng (pots) khỏi database của KH cũ.");
    }

    // 4. Log sự kiện
    error_log("[RACK HANDOFF] ✅ Đã đóng gói dữ liệu KH cũ: garden={$from_garden_key}, rack_id={$rack_id} ({$rack_code}) lúc {$archived_at}.");
}

// ─── Hook: SAU KHI giao rack cho KH mới ──────────────────────────────────────
add_action('aitrongcay_after_rack_assign', 'aitrongcay_handoff_prepare_new_customer', 10, 3);

/**
 * Chuẩn bị luồng dữ liệu sạch cho KH mới sau khi rack được giao.
 *
 * @param int    $rack_id       ID rack vừa được giao.
 * @param string $to_garden_key Garden key của KH mới.
 * @param string $from_garden_key Garden key cũ (inventory key hoặc KH trước).
 */
function aitrongcay_handoff_prepare_new_customer(int $rack_id, string $to_garden_key, string $from_garden_key): void {
    if ($rack_id <= 0 || $to_garden_key === '') {
        return;
    }

    // 1. Đồng bộ WP option rack config từ DB cho KH mới
    //    (xây lại aitrongcay_rack_cfg_{to_garden_key} từ DB, không kế thừa data cũ)
    aitrongcay_handoff_rebuild_wp_option($to_garden_key);

    // 2. Xoá WP option config cũ gắn với inventory_key (nếu khác to_garden_key)
    if ($from_garden_key !== '' && $from_garden_key !== $to_garden_key) {
        delete_option('aitrongcay_rack_cfg_' . sanitize_key($from_garden_key));
    }

    // 3. Chuyển thư mục timelapse camera: tạo thư mục mới cho KH mới
    //    Các ảnh cũ (của KH trước) không bị xoá — chỉ tạo thư mục mới sạch.
    aitrongcay_handoff_init_timelapse_folder($to_garden_key, $rack_id);

    // 4. Xoá transient cache sensor cho KH mới (để load tươi từ thiết bị)
    aitrongcay_handoff_flush_sensor_cache($to_garden_key, $rack_id);

    // 5. Cập nhật wp_options 'aitrongcay_rack_cfg_{to_garden_key}' từ data DB mới nhất
    error_log("[RACK HANDOFF] ✅ Luồng dữ liệu đã sẵn sàng cho KH mới: garden={$to_garden_key}, rack_id={$rack_id}.");
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Xây lại WP option 'aitrongcay_rack_cfg_{garden_key}' từ DB.
 * Đây là source-of-truth sau khi rack được giao cho KH mới.
 */
function aitrongcay_handoff_rebuild_wp_option(string $garden_key): void {
    global $wpdb;

    if ($garden_key === '') {
        return;
    }

    $racks_table = $wpdb->prefix . 'aitr_garden_racks';
    $slots_table = $wpdb->prefix . 'aitr_rack_slots';

    $racks = $wpdb->get_results($wpdb->prepare(
        "SELECT id, rack_name, rack_code, blynk_auth_token, slot_count
           FROM {$racks_table}
          WHERE garden_key = %s AND status = 'assigned'
          ORDER BY id ASC",
        $garden_key
    ), ARRAY_A);

    if (empty($racks)) {
        // Không có rack nào trong DB → xoá option để tránh data cũ gây nhầm
        delete_option('aitrongcay_rack_cfg_' . sanitize_key($garden_key));
        return;
    }

    $new_configs = [];
    foreach ($racks as $rack) {
        $rack_id = (int) ($rack['id'] ?? 0);

        $slots = $wpdb->get_results($wpdb->prepare(
            "SELECT slot_index, slot_name, camera_stream_url, pot_code
               FROM {$slots_table}
              WHERE rack_id = %d
              ORDER BY slot_index ASC",
            $rack_id
        ), ARRAY_A);

        $trays = [];
        foreach ($slots as $slot) {
            $idx = max(0, (int) ($slot['slot_index'] ?? 1) - 1);
            $trays[$idx] = [
                'name'       => (string) ($slot['slot_name'] ?? ''),
                'webcam_url' => (string) ($slot['camera_stream_url'] ?? ''),
            ];
        }
        ksort($trays);

        $new_configs[] = [
            'rack_id'          => $rack_id,
            'rack_name'        => (string) ($rack['rack_name'] ?? 'Rack'),
            'blynk_auth_token' => (string) ($rack['blynk_auth_token'] ?? ''),
            'trays'            => $trays,
        ];
    }

    update_option('aitrongcay_rack_cfg_' . sanitize_key($garden_key), $new_configs, false);
}

/**
 * Lưu snapshot thư mục timelapse (đổi tên folder) để KH cũ có dữ liệu lịch sử.
 * Không xoá — chỉ đổi tên thư mục thành {garden_key}_archived_{timestamp}.
 */
function aitrongcay_handoff_archive_timelapse_folder(string $from_garden_key, int $rack_id, string $rack_code): void {
    $upload_info = wp_upload_dir();
    $tl_base     = $upload_info['basedir'] . '/timelapse';
    $safe_gk     = sanitize_key($from_garden_key);
    $src_dir     = $tl_base . '/' . $safe_gk;

    if (! is_dir($src_dir)) {
        return; // Chưa có gì để archive
    }

    $archive_name = $safe_gk . '_archived_' . date('Ymd_His') . '_rack' . $rack_id;
    $dst_dir      = $tl_base . '/_archives/' . $archive_name;

    wp_mkdir_p($tl_base . '/_archives');

    // Đổi tên thư mục (move, không copy) để tốn ít IO
    if (@rename($src_dir, $dst_dir)) {
        error_log("[RACK HANDOFF] 📦 Đã archive timelapse: {$src_dir} → {$dst_dir}");
    } else {
        error_log("[RACK HANDOFF] ⚠️ Không thể archive timelapse folder: {$src_dir}");
    }
}

/**
 * Tạo thư mục timelapse trống cho KH mới (đảm bảo đường dẫn tồn tại).
 */
function aitrongcay_handoff_init_timelapse_folder(string $to_garden_key, int $rack_id): void {
    $upload_info = wp_upload_dir();
    $tl_base     = $upload_info['basedir'] . '/timelapse';
    $safe_gk     = sanitize_key($to_garden_key);
    $new_dir     = $tl_base . '/' . $safe_gk;

    wp_mkdir_p($new_dir);
    error_log("[RACK HANDOFF] 📁 Đã chuẩn bị thư mục timelapse mới: {$new_dir}");
}

/**
 * Xoá tất cả transient cache cảm biến liên quan đến garden_key + rack_id.
 */
function aitrongcay_handoff_flush_sensor_cache(string $garden_key, int $rack_id): void {
    global $wpdb;

    // Xoá transient theo pattern: aitr_t_{garden_key}_r{ri}_t{ti}
    $safe_gk = sanitize_key($garden_key);
    $pattern = '_transient_aitr_t_' . $safe_gk . '_%';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $pattern
    ));

    // Cũng xoá object cache nếu đang dùng
    wp_cache_flush_group('aitr_sensors');

    error_log("[RACK HANDOFF] 🗑️ Đã flush sensor cache cho garden={$garden_key}, rack_id={$rack_id}");
}
