<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// ─── Defaults ────────────────────────────────────────────────────────────────

function aitrongcay_tray_defaults(): array
{
    return [
        'name'        => '',
        'webcam_url'  => '',
        'blynk_token' => '',
        'blynk_base'  => 'https://blynk.cloud/external/api',
        'vpin_temp'   => 'V0',
        'vpin_hum'    => 'V1',
        'vpin_soil'   => 'V2',
        'vpin_ph'     => 'V3',
        'vpin_ec'     => 'V4',
        'vpin_light'  => 'V6',
        'vpin_pump'   => 'V5',
    ];
}

// ─── Data: multi-rack config ─────────────────────────────────────────────────
// Structure: [ ['rack_name'=>'Rack 1', 'trays'=>[...tray configs...]], ... ]
// Stored in WP option 'aitrongcay_rack_monitor_configs'.
// Migrates automatically from old flat 'aitrongcay_tray_rack_configs' option.

function aitrongcay_get_rack_monitor_configs(string $garden_key = ''): array
{
    $garden_key = trim($garden_key);

    // Dynamic database-driven mapping if slots are assigned to this garden
    if ($garden_key !== '' && function_exists('aitrongcay_get_rack_slots')) {
        global $wpdb;
        $racks_table = function_exists('aitrongcay_garden_racks_table') ? aitrongcay_garden_racks_table() : $wpdb->prefix . 'aitr_garden_racks';
        $assigned_racks = $wpdb->get_results($wpdb->prepare("SELECT id, rack_name, rack_code, slot_count, blynk_auth_token FROM {$racks_table} WHERE garden_key = %s ORDER BY id ASC", $garden_key), ARRAY_A);
        
        if (!empty($assigned_racks)) {
            $slots = aitrongcay_get_rack_slots($garden_key);
            $racks_grouped = [];
            foreach ($assigned_racks as $r) {
                $racks_grouped[(int) $r['id']] = [];
            }
            if (!empty($slots)) {
                foreach ($slots as $slot) {
                    $rack_id = (int) ($slot['rack_id'] ?? 1);
                    if (isset($racks_grouped[$rack_id])) {
                        $racks_grouped[$rack_id][] = $slot;
                    }
                }
            }

            $racks = [];
            $rack_idx = 0;
            $saved_options = [];
            if ($garden_key === 'tung-01') {
                $saved_options = (array) get_option('aitrongcay_rack_monitor_configs', []);
            }
            if (empty($saved_options)) {
                $saved_options = get_option('aitrongcay_rack_cfg_' . $garden_key, []);
                if (empty($saved_options)) {
                    $saved_options = get_option('aitrongcay_rack_cfg_' . sanitize_key($garden_key), []);
                }
            }
            if (! is_array($saved_options)) {
                $saved_options = [];
            }

            foreach ($assigned_racks as $rack_info) {
                $rack_id = (int) $rack_info['id'];
                $rack_slots = $racks_grouped[$rack_id] ?? [];
                $rack_name = trim((string) ($rack_info['rack_name'] ?? '')) ?: (trim((string) ($rack_info['rack_code'] ?? '')) ?: ('Rack ' . $rack_id));
                $slot_count = (int) ($rack_info['slot_count'] ?? 3);
                $slot_count = max(1, $slot_count);

                $trays = [];
                // Sort any existing slots by index
                usort($rack_slots, static function($a, $b) {
                    return (int)($a['slot_index'] ?? 0) <=> (int)($b['slot_index'] ?? 0);
                });

                // Convert DB slots into an indexed map (0-based)
                $slot_map = [];
                foreach ($rack_slots as $slot) {
                    $idx = max(0, (int)($slot['slot_index'] ?? 1) - 1);
                    $slot_map[$idx] = $slot;
                }

                for ($si = 0; $si < $slot_count; $si++) {
                    $blynk_token = '';
                    $blynk_base = 'https://blynk.cloud/external/api';
                    $slot = $slot_map[$si] ?? [];
                    $pot_code = trim((string) ($slot['pot_code'] ?? ''));

                    if ($pot_code !== '') {
                        if (function_exists('aitrongcay_blynk_config')) {
                            $blynk_cfg = aitrongcay_blynk_config($garden_key);
                            if (! empty($blynk_cfg['base_url'])) {
                                $blynk_base = trim((string) $blynk_cfg['base_url']);
                            }
                        }
                    }
                    
                    // Prioritize the Rack's own blynk token
                    $rack_token = trim((string) ($rack_info['blynk_auth_token'] ?? ''));
                    if ($rack_token !== '') {
                        $blynk_token = $rack_token;
                    } elseif (isset($blynk_cfg)) {
                        // Fallback to old global system if rack token is missing
                        $raw_token = trim((string) ($blynk_cfg['pot_tokens'][$pot_code] ?? ''));
                        if ($raw_token !== '') {
                            if (function_exists('aitrongcay_blynk_effective_token')) {
                                $blynk_token = aitrongcay_blynk_effective_token($garden_key, $raw_token);
                            } else {
                                $blynk_token = $raw_token;
                            }
                        } else {
                            $blynk_token = trim((string) ($blynk_cfg['token'] ?? ''));
                        }
                    }

                    $saved_tray = $saved_options[$rack_idx]['trays'][$si] ?? [];
                    $tray_data = array_merge(aitrongcay_tray_defaults(), [
                        'name'        => trim((string) ($slot['slot_name'] ?? '')) ?: ('Khoang ' . ($si + 1)),
                        'webcam_url'  => trim((string) ($slot['camera_stream_url'] ?? '')),
                        'blynk_token' => $blynk_token,
                        'blynk_base'  => $blynk_base,
                    ]);

                    foreach (['name', 'webcam_url', 'vpin_temp', 'vpin_hum', 'vpin_soil', 'vpin_ph', 'vpin_ec', 'vpin_light', 'vpin_pump'] as $vp) {
                        if (!empty($saved_tray[$vp])) {
                            $tray_data[$vp] = $saved_tray[$vp];
                        }
                    }
                    // Only fallback to saved tray token if the rack has no token
                    if (empty($tray_data['blynk_token']) && !empty($saved_tray['blynk_token'])) {
                        $tray_data['blynk_token'] = $saved_tray['blynk_token'];
                    }
                    if (empty($tray_data['blynk_base']) && !empty($saved_tray['blynk_base'])) {
                        $tray_data['blynk_base'] = $saved_tray['blynk_base'];
                    }
                    $trays[] = $tray_data;
                }
                $racks[] = [
                    'rack_id'   => $rack_id,
                    'rack_name' => $rack_name,
                    'blynk_auth_token' => trim((string) ($rack_info['blynk_auth_token'] ?? '')),
                    'trays'     => $trays,
                ];
                $rack_idx++;
            }
            return $racks;
        }
    }

    $option_key = $garden_key !== ''
        ? 'aitrongcay_rack_cfg_' . sanitize_key($garden_key)
        : 'aitrongcay_rack_monitor_configs';

    $saved = get_option($option_key, null);

    // If a per-garden option doesn't exist yet, fall back to the global config.
    // Only fallback for the main demo garden to prevent leaking admin streams to new users.
    if (($saved === null || ! is_array($saved) || empty($saved)) && $garden_key !== '') {
        if ($garden_key === 'tung-01') {
            $saved = get_option('aitrongcay_rack_monitor_configs', null);
        }
    }

    if ($saved === null || ! is_array($saved) || empty($saved)) {
        // Migrate old flat 3-tray option → single rack
        $old = (array) get_option('aitrongcay_tray_rack_configs', []);
        if (! empty($old)) {
            $trays = [];
            foreach ($old as $ti => $t) {
                $trays[] = array_merge(
                    aitrongcay_tray_defaults(),
                    ['name' => 'Khoang ' . ($ti + 1)],
                    (array) $t
                );
            }
            return [['rack_name' => 'Rack 1', 'trays' => $trays]];
        }
        // Default: 1 rack × 3 empty trays
        return [[
            'rack_name' => 'Rack 1',
            'trays'     => [
                array_merge(aitrongcay_tray_defaults(), ['name' => 'Khoang 1']),
                array_merge(aitrongcay_tray_defaults(), ['name' => 'Khoang 2']),
                array_merge(aitrongcay_tray_defaults(), ['name' => 'Khoang 3']),
            ],
        ]];
    }

    $racks = [];
    foreach ($saved as $ri => $rack) {
        $rack  = (array) $rack;
        $trays = [];
        foreach ((array) ($rack['trays'] ?? []) as $ti => $t) {
            $trays[] = array_merge(
                aitrongcay_tray_defaults(),
                ['name' => 'Khoang ' . ($ti + 1)],
                (array) $t
            );
        }
        if (empty($trays)) {
            for ($j = 0; $j < 3; $j++) {
                $trays[] = array_merge(aitrongcay_tray_defaults(), ['name' => 'Khoang ' . ($j + 1)]);
            }
        }
        $racks[] = [
            'rack_name' => (string) ($rack['rack_name'] ?? ('Rack ' . ($ri + 1))),
            'trays'     => $trays,
        ];
    }
    return $racks;
}

// Backward compat shim — returns flat tray array for Rack 1.
function aitrongcay_get_tray_configs(string $garden_key = ''): array
{
    $racks = aitrongcay_get_rack_monitor_configs($garden_key);
    return $racks[0]['trays'] ?? [];
}

// ─── Sensor read / write (single tray) ───────────────────────────────────────

function aitrongcay_tray_read_sensors(array $tray): array
{
    $token = trim((string) ($tray['blynk_token'] ?? ''));
    $base  = trim((string) ($tray['blynk_base']  ?? 'https://blynk.cloud/external/api'));
    if ($token === '') {
        return ['error' => 'no_token'];
    }

    $vpin_map = [
        'temp'  => strtoupper(trim((string) ($tray['vpin_temp']  ?? ''))),
        'hum'   => strtoupper(trim((string) ($tray['vpin_hum']   ?? ''))),
        'soil'  => strtoupper(trim((string) ($tray['vpin_soil']  ?? ''))),
        'ph'    => strtoupper(trim((string) ($tray['vpin_ph']    ?? ''))),
        'ec'    => strtoupper(trim((string) ($tray['vpin_ec']    ?? ''))),
        'light' => strtoupper(trim((string) ($tray['vpin_light'] ?? ''))),
        'pump'  => strtoupper(trim((string) ($tray['vpin_pump']  ?? ''))),
    ];
    $vpin_map = array_filter($vpin_map);
    if (empty($vpin_map)) {
        return ['error' => 'no_vpins'];
    }

    $raw    = aitrongcay_blynk_read_values($token, array_values($vpin_map), $base);
    $result = [];
    foreach ($vpin_map as $key => $vpin) {
        $value        = $raw[$vpin] ?? null;
        $result[$key] = in_array($key, ['light', 'pump'], true)
            ? ($value !== null ? (int) $value : null)
            : ($value !== null ? (float) $value : null);
    }
    return $result;
}

function aitrongcay_tray_write_device(array $tray, string $vpin, int $value): bool
{
    $token = trim((string) ($tray['blynk_token'] ?? ''));
    $base  = trim((string) ($tray['blynk_base']  ?? 'https://blynk.cloud/external/api'));
    if ($token === '' || $vpin === '') {
        return false;
    }
    $url  = add_query_arg(
        ['token' => $token, $vpin => $value],
        untrailingslashit($base) . '/update'
    );
    $resp = wp_remote_get($url, ['timeout' => 8]);
    return ! is_wp_error($resp) && (int) wp_remote_retrieve_response_code($resp) === 200;
}

// ─── AJAX: read sensors (rack_index + tray_index) ────────────────────────────

function aitrongcay_tray_sensors_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        $garden_key = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key(wp_get_current_user()) : '';
    }
    $ri         = absint($_POST['rack_index'] ?? 0);
    $ti         = absint($_POST['tray_index'] ?? 0);

    if (
        ! current_user_can('manage_options')
        && (! is_user_logged_in() || ! aitrongcay_user_can_view_garden($garden_key, get_current_user_id()))
    ) {
        wp_send_json_error(['message' => 'Access denied.'], 403);
        return;
    }

    $racks = aitrongcay_get_rack_monitor_configs($garden_key);
    if (! isset($racks[$ri]['trays'][$ti])) {
        wp_send_json_error(['message' => 'Invalid rack/tray index.'], 400);
        return;
    }

    $data = aitrongcay_tray_read_sensors($racks[$ri]['trays'][$ti]);
    wp_send_json_success($data);
}
add_action('wp_ajax_aitrongcay_tray_sensors', 'aitrongcay_tray_sensors_ajax');

// ─── AJAX: control device (rack_index + tray_index) ──────────────────────────

function aitrongcay_tray_control_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        $garden_key = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key(wp_get_current_user()) : '';
    }
    $ri         = absint($_POST['rack_index'] ?? 0);
    $ti         = absint($_POST['tray_index'] ?? 0);
    $device     = sanitize_key((string) wp_unslash($_POST['device'] ?? ''));
    $value      = (int) ($_POST['value'] ?? 0);

    if (
        ! current_user_can('manage_options')
        && (! is_user_logged_in() || ! aitrongcay_user_can_view_garden($garden_key, get_current_user_id()))
    ) {
        wp_send_json_error(['message' => 'Access denied.'], 403);
        return;
    }

    if (! in_array($device, ['light', 'pump'], true) || ! in_array($value, [0, 1], true)) {
        wp_send_json_error(['message' => 'Invalid parameters.'], 400);
        return;
    }

    $racks = aitrongcay_get_rack_monitor_configs($garden_key);
    if (! isset($racks[$ri]['trays'][$ti])) {
        wp_send_json_error(['message' => 'Invalid rack/tray index.'], 400);
        return;
    }

    $tray = $racks[$ri]['trays'][$ti];
    $vpin = strtoupper(trim((string) ($tray['vpin_' . $device] ?? '')));
    if ($vpin === '') {
        wp_send_json_error(['message' => 'VPin not configured for ' . esc_html($device) . '.'], 400);
        return;
    }

    $ok = aitrongcay_tray_write_device($tray, $vpin, $value);
    if ($ok) {
        delete_transient('aitr_t_' . $garden_key . '_r' . $ri . '_t' . $ti);
        wp_send_json_success(['device' => $device, 'value' => $value]);
    } else {
        wp_send_json_error(['message' => 'Blynk command failed. Check token and network.']);
    }
}
add_action('wp_ajax_aitrongcay_tray_control', 'aitrongcay_tray_control_ajax');

// ─── AJAX: save full rack config from inline modal ───────────────────────────

function aitrongcay_tray_config_ajax_save(): void
{
    aitrongcay_require_portal_nonce();

    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.'], 403);
        return;
    }

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        $garden_key = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key(wp_get_current_user()) : '';
    }

    $raw        = isset($_POST['racks']) ? (string) wp_unslash($_POST['racks']) : '';
    $data       = $raw !== '' ? json_decode($raw, true) : null;

    if (! is_array($data) || empty($data)) {
        wp_send_json_error(['message' => 'Invalid data.'], 400);
        return;
    }

    $racks = [];
    foreach ($data as $ri => $rack) {
        $rack  = (array) $rack;
        $trays = [];
        foreach ((array) ($rack['trays'] ?? []) as $ti => $t) {
            $t       = (array) $t;
            $trays[] = [
                'name'        => sanitize_text_field($t['name']        ?? ('Khoang ' . ($ti + 1))),
                'webcam_url'  => esc_url_raw($t['webcam_url']          ?? ''),
                'blynk_token' => sanitize_text_field($t['blynk_token'] ?? ''),
                'blynk_base'  => esc_url_raw($t['blynk_base']         ?? 'https://blynk.cloud/external/api'),
                'vpin_temp'   => strtoupper(sanitize_text_field($t['vpin_temp']  ?? 'V0')),
                'vpin_hum'    => strtoupper(sanitize_text_field($t['vpin_hum']   ?? 'V1')),
                'vpin_ph'     => strtoupper(sanitize_text_field($t['vpin_ph']    ?? 'V2')),
                'vpin_ec'     => strtoupper(sanitize_text_field($t['vpin_ec']    ?? 'V3')),
                'vpin_light'  => strtoupper(sanitize_text_field($t['vpin_light'] ?? 'V4')),
                'vpin_pump'   => strtoupper(sanitize_text_field($t['vpin_pump']  ?? 'V5')),
            ];
        }
        $racks[] = [
            'rack_name' => sanitize_text_field($rack['rack_name'] ?? ('Rack ' . ($ri + 1))),
            'trays'     => $trays,
        ];
    }

    $save_option = 'aitrongcay_rack_monitor_configs';
    update_option($save_option, $racks, false);

    foreach ($racks as $ri => $r) {
        foreach ($r['trays'] as $ti => $_) {
            delete_transient('aitr_t_' . $garden_key . '_r' . $ri . '_t' . $ti);
        }
    }

    wp_send_json_success(['message' => 'Saved.', 'rack_count' => count($racks)]);
}
add_action('wp_ajax_aitrongcay_tray_config_save', 'aitrongcay_tray_config_ajax_save');

// ─── Admin page (WP Admin → Appearance) ──────────────────────────────────────

function aitrongcay_tray_config_admin_menu(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Cấu hình Rack Monitor',
        '🌿 Rack Monitor',
        'manage_options',
        'aitrongcay-tray-config',
        'aitrongcay_render_tray_config_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_tray_config_admin_menu', 100);

function aitrongcay_handle_tray_config_save(): void
{
    if (
        ! isset($_POST['aitrongcay_tray_config_nonce'])
        || ! wp_verify_nonce((string) $_POST['aitrongcay_tray_config_nonce'], 'aitrongcay_tray_config_save')
        || ! current_user_can('manage_options')
    ) {
        return;
    }

    $raw_racks = (array) ($_POST['rack'] ?? []);
    $racks     = [];
    foreach ($raw_racks as $ri => $rack) {
        $rack  = (array) $rack;
        $trays = [];
        foreach ((array) ($rack['trays'] ?? []) as $ti => $t) {
            $t       = (array) $t;
            $trays[] = [
                'name'        => sanitize_text_field($t['name']        ?? ('Khoang ' . ($ti + 1))),
                'webcam_url'  => esc_url_raw($t['webcam_url']          ?? ''),
                'blynk_token' => sanitize_text_field($t['blynk_token'] ?? ''),
                'blynk_base'  => esc_url_raw($t['blynk_base']         ?? 'https://blynk.cloud/external/api'),
                'vpin_temp'   => strtoupper(sanitize_text_field($t['vpin_temp']  ?? 'V0')),
                'vpin_hum'    => strtoupper(sanitize_text_field($t['vpin_hum']   ?? 'V1')),
                'vpin_ph'     => strtoupper(sanitize_text_field($t['vpin_ph']    ?? 'V2')),
                'vpin_ec'     => strtoupper(sanitize_text_field($t['vpin_ec']    ?? 'V3')),
                'vpin_light'  => strtoupper(sanitize_text_field($t['vpin_light'] ?? 'V4')),
                'vpin_pump'   => strtoupper(sanitize_text_field($t['vpin_pump']  ?? 'V5')),
            ];
        }
        $racks[] = [
            'rack_name' => sanitize_text_field($rack['rack_name'] ?? ('Rack ' . ($ri + 1))),
            'trays'     => $trays,
        ];
    }

    update_option('aitrongcay_rack_monitor_configs', $racks, false);

    foreach ($racks as $ri => $r) {
        foreach ($r['trays'] as $ti => $_) {
            delete_transient('aitr_t__r' . $ri . '_t' . $ti);
        }
    }

    wp_safe_redirect(add_query_arg('saved', '1', admin_url('admin.php?page=aitrongcay-tray-config')));
    exit;
}
add_action('admin_post_aitrongcay_tray_config_save', 'aitrongcay_handle_tray_config_save');

function aitrongcay_render_tray_config_admin_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    $racks    = aitrongcay_get_rack_monitor_configs();
    $live_url = home_url('/portal/dashboard-2/');
    ?>
    <div class="wrap">
    <h1>🌿 Rack Monitor – Cấu hình thiết bị theo rack</h1>
    <?php if (isset($_GET['saved'])) : ?>
    <div class="notice notice-success is-dismissible"><p><strong>Đã lưu!</strong></p></div>
    <?php endif; ?>
    <p>Cấu hình trực tiếp trên trang khu vườn: <a href="<?php echo esc_url($live_url); ?>" target="_blank">nhấn nút ⚙️ góc trên phải</a>.</p>
    <p style="background:#fff3cd;border:1px solid #ffc107;padding:10px 14px;border-radius:4px;margin-bottom:20px">
        <strong>Hướng dẫn:</strong> Mỗi rack có thể có nhiều khoang. Mỗi khoang cấu hình độc lập: Blynk token + VPin cảm biến + URL webcam.
    </p>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('aitrongcay_tray_config_save', 'aitrongcay_tray_config_nonce'); ?>
    <input type="hidden" name="action" value="aitrongcay_tray_config_save">
    <style>
    .rm-rack{background:#fff;border:1px solid #c3c4c7;border-radius:6px;padding:20px;margin-bottom:24px}
    .rm-rack-title{font-size:17px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:2px solid #2271b1}
    .rm-tray-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;margin-bottom:12px}
    .rm-tray-card{background:#f6f7f7;border:1px solid #dcdcde;border-radius:6px;padding:14px}
    .rm-tray-card h4{margin:0 0 10px;font-size:14px}
    .rm-row{display:grid;grid-template-columns:100px 1fr;gap:4px;align-items:center;margin-bottom:6px}
    .rm-row label{font-size:12px;color:#50575e;font-weight:500}
    .rm-row input{padding:5px 7px;border:1px solid #8c8f94;border-radius:4px;font-size:12px;width:100%}
    .rm-vpins{display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-top:8px}
    .rm-vp{display:flex;flex-direction:column;gap:2px}
    .rm-vp label{font-size:11px;color:#8c8f94}
    .rm-vp input{padding:4px 6px;border:1px solid #8c8f94;border-radius:4px;font-size:12px;text-align:center}
    </style>
    <?php foreach ($racks as $ri => $rack) : ?>
    <div class="rm-rack">
        <div class="rm-row" style="margin-bottom:14px">
            <label>Tên rack</label>
            <input type="text" name="rack[<?php echo $ri; ?>][rack_name]" value="<?php echo esc_attr($rack['rack_name']); ?>" placeholder="Rack <?php echo $ri + 1; ?>">
        </div>
        <div class="rm-rack-title"><?php echo esc_html($rack['rack_name']); ?></div>
        <div class="rm-tray-grid">
        <?php foreach ($rack['trays'] as $ti => $tray) : ?>
        <div class="rm-tray-card">
            <h4>🌿 <?php echo esc_html($tray['name'] ?: ('Khoang ' . ($ti + 1))); ?></h4>
            <div class="rm-row"><label>Tên khoang</label><input type="text" name="rack[<?php echo $ri; ?>][trays][<?php echo $ti; ?>][name]" value="<?php echo esc_attr($tray['name']); ?>"></div>
            <div class="rm-row"><label>Webcam URL</label><input type="text" name="rack[<?php echo $ri; ?>][trays][<?php echo $ti; ?>][webcam_url]" value="<?php echo esc_attr($tray['webcam_url']); ?>" placeholder="http://..."></div>
            <div class="rm-row"><label>Blynk Token</label><input type="text" name="rack[<?php echo $ri; ?>][trays][<?php echo $ti; ?>][blynk_token]" value="<?php echo esc_attr($tray['blynk_token']); ?>"></div>
            <div class="rm-row"><label>Base URL</label><input type="text" name="rack[<?php echo $ri; ?>][trays][<?php echo $ti; ?>][blynk_base]" value="<?php echo esc_attr($tray['blynk_base']); ?>"></div>
            <div class="rm-vpins">
                <?php foreach (['vpin_temp'=>'🌡 Temp','vpin_hum'=>'💧 Hum','vpin_ph'=>'⚗️ pH','vpin_ec'=>'🌱 EC','vpin_light'=>'💡 Đèn','vpin_pump'=>'🔄 Bơm'] as $vk => $vl) : ?>
                <div class="rm-vp"><label><?php echo $vl; ?></label><input type="text" name="rack[<?php echo $ri; ?>][trays][<?php echo $ti; ?>][<?php echo $vk; ?>]" value="<?php echo esc_attr($tray[$vk] ?? ''); ?>" placeholder="V0"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php submit_button('💾 Lưu cấu hình'); ?>
    </form>
    </div>
    <?php
}
