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
        $assigned_racks = $wpdb->get_results($wpdb->prepare("SELECT id, garden_key, rack_name, rack_code, slot_count, blynk_auth_token FROM {$racks_table} WHERE garden_key = %s ORDER BY id ASC", $garden_key), ARRAY_A);
        
        $cloned_rack_ids = get_option('aitrongcay_cloned_racks_' . $garden_key, []);
        $cloned_rack_ids = is_array($cloned_rack_ids) ? array_values($cloned_rack_ids) : (is_string($cloned_rack_ids) && $cloned_rack_ids !== '' ? explode(',', $cloned_rack_ids) : array_values((array) $cloned_rack_ids));
        if (!empty($cloned_rack_ids)) {
            $ids_placeholder = implode(',', array_fill(0, count($cloned_rack_ids), '%d'));
            $cloned_racks = $wpdb->get_results($wpdb->prepare("SELECT id, garden_key, rack_name, rack_code, slot_count, blynk_auth_token FROM {$racks_table} WHERE id IN ($ids_placeholder) ORDER BY id ASC", ...$cloned_rack_ids), ARRAY_A);
            if (!empty($cloned_racks)) {
                if (!is_array($assigned_racks)) $assigned_racks = [];
                foreach ($cloned_racks as $cr) {
                    $cr['_is_clone'] = true;
                    $assigned_racks[] = $cr;
                }
            }
        }
        
        if (!empty($assigned_racks)) {
            $racks_grouped = [];
            $unique_garden_keys = array_unique(array_column($assigned_racks, 'garden_key'));
            $unique_garden_keys[] = $garden_key;
            
            foreach ($unique_garden_keys as $gk) {
                if ($gk !== '') {
                    $slots = aitrongcay_get_rack_slots((string) $gk);
                    if (!empty($slots)) {
                        foreach ($slots as $slot) {
                            $rack_id = (int) ($slot['rack_id'] ?? 1);
                            if (!isset($racks_grouped[$rack_id])) {
                                $racks_grouped[$rack_id] = [];
                            }
                            $racks_grouped[$rack_id][] = $slot;
                        }
                    }
                }
            }

            $racks = [];
            $saved_options_map = [];

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

                    $true_gk = (string) ($rack_info['garden_key'] ?? $garden_key);
                    
                    if (!isset($saved_options_map[$true_gk])) {
                        $so = [];
                        if ($true_gk === 'tung-01') {
                            $so = (array) get_option('aitrongcay_rack_monitor_configs', []);
                        } else {
                            $so = get_option('aitrongcay_rack_cfg_' . $true_gk, []);
                            if (empty($so)) $so = get_option('aitrongcay_rack_cfg_' . sanitize_key($true_gk), []);
                        }
                        $saved_options_map[$true_gk] = is_array($so) ? $so : [];
                    }
                    
                    $true_saved_options = $saved_options_map[$true_gk];
                    $saved_rack = null;
                    foreach ($true_saved_options as $sro) {
                        if (isset($sro['rack_id']) && (int) $sro['rack_id'] === $rack_id) {
                            $saved_rack = $sro;
                            break;
                        }
                    }
                    
                    $saved_tray = $saved_rack ? ($saved_rack['trays'][$si] ?? []) : [];
                    $tray_data = array_merge(aitrongcay_tray_defaults(), [
                        'name'        => trim((string) ($slot['slot_name'] ?? '')) ?: ('Khoang ' . ($si + 1)),
                        'webcam_url'  => trim((string) ($slot['camera_stream_url'] ?? '')),
                        'blynk_token' => $blynk_token,
                        'blynk_base'  => $blynk_base,
                        'pot_code'    => $pot_code,
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
                    '_is_clone' => !empty($rack_info['_is_clone']),
                ];
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
    $resp = wp_remote_get($url, ['timeout' => 4]);
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

    $cache_key = 'aitr_tray_sensors_' . md5($garden_key . '_r' . $ri . '_t' . $ti);
    $cooldown_key = 'aitr_tray_sensors_cooldown_' . md5($garden_key . '_r' . $ri . '_t' . $ti);

    $cached = get_transient($cache_key);
    if (is_array($cached) && !empty($cached)) {
        wp_send_json_success($cached);
    }
    
    $cooldown = get_transient($cooldown_key);
    if (is_array($cooldown) && isset($cooldown['message'])) {
        wp_send_json_error($cooldown);
    }

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
    
    if (isset($data['error'])) {
        wp_send_json_error(['message' => 'Lỗi cấu hình khay: ' . $data['error']], 400);
    }

    $has_data = false;
    foreach ($data as $k => $v) {
        if ($v !== null) {
            $has_data = true;
            break;
        }
    }

    if (!$has_data) {
        set_transient($cooldown_key, ['message' => 'Blynk đang giới hạn quota hoặc chưa phản hồi, tạm ngưng gọi lại trong ít phút.'], 300);
        wp_send_json_error(['message' => 'Không đọc được dữ liệu Blynk.'], 502);
    }

    delete_transient($cooldown_key);
    set_transient($cache_key, $data, 45); // Cache for 45 seconds to reduce load
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
        && (! is_user_logged_in() || ! aitrongcay_user_can_control_garden($garden_key, get_current_user_id()))
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

