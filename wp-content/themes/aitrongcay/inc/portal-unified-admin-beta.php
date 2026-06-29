<?php
/**
 * Portal Unified Admin Beta - AI Trồng Cây
 * Parallel testing dashboard to consolidate admin functions.
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// Register Unified Admin Beta Menu
add_action('admin_menu', 'aitrongcay_register_unified_admin_beta_menu');
function aitrongcay_register_unified_admin_beta_menu(): void {
    add_menu_page(
        '🌿 AI Trồng Cây (BETA)',
        '🌿 AI Trồng Cây (BETA)',
        'manage_options',
        'aitrongcay-unified-admin-beta',
        'aitrongcay_render_unified_admin_beta_page',
        'dashicons-superhero',
        30
    );
}

// Handle Form Posts for Beta Dashboard Actions
add_action('admin_init', 'aitrongcay_handle_unified_admin_beta_actions');
function aitrongcay_handle_unified_admin_beta_actions(): void {
    if (! is_admin() || ! current_user_can('manage_options')) {
        return;
    }

    $action = sanitize_key((string) ($_POST['beta_action'] ?? ''));
    if ($action === '') {
        return;
    }

    // Verify Nonce
    check_admin_referer('aitrongcay_beta_action_nonce');

    global $wpdb;
    $redirect = admin_url('admin.php?page=aitrongcay-unified-admin-beta');

    // Action 1: Assign Rack to Garden
    if ($action === 'assign_rack') {
        $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
        $rack_id = absint($_POST['rack_id'] ?? 0);

        if ($garden_key === '' || $rack_id <= 0) {
            wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Thiếu thông tin garden_key hoặc rack_id.'), $redirect));
            exit;
        }

        // Get owner user from membership or dataset
        $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
        $owner_user_id = $owner instanceof WP_User ? (int) $owner->ID : 0;
        $is_admin_garden = $owner instanceof WP_User && user_can($owner, 'manage_options');

        if (function_exists('aitrongcay_get_rack_by_id') && function_exists('aitrongcay_garden_racks_table') && function_exists('aitrongcay_garden_rack_assignments_table')) {
            $rack = aitrongcay_get_rack_by_id($rack_id);
            if (! is_array($rack)) {
                wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Rack không tồn tại.'), $redirect));
                exit;
            }

            if ($is_admin_garden) {
                // Admin Clone Mode
                $cloned_racks = get_option('aitrongcay_cloned_racks_' . $garden_key, []);
                if (!in_array($rack_id, $cloned_racks, true)) {
                    $cloned_racks[] = $rack_id;
                    update_option('aitrongcay_cloned_racks_' . $garden_key, $cloned_racks, false);
                }
                wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
                exit;
            } else {
                // Normal Customer Mode
                if (($rack['status'] ?? '') !== 'inventory') {
                    wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Rack này không khả dụng hoặc đã được giao.'), $redirect));
                    exit;
                }

                $from_gk = (string) ($rack['garden_key'] ?? '');
                $now = current_time('mysql');

                // Update Rack status
                $updated = $wpdb->update(
                    aitrongcay_garden_racks_table(),
                    [
                        'garden_key' => $garden_key,
                        'owner_user_id' => $owner_user_id,
                        'status' => 'assigned',
                        'updated_at' => $now
                    ],
                    ['id' => $rack_id],
                    ['%s', '%d', '%s', '%s'],
                    ['%d']
                );

                if ($updated === false) {
                    wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Lỗi hệ thống: Không thể gán Rack vào CSDL.'), $redirect));
                    exit;
                }

                // Insert Assignment Record
                $wpdb->insert(aitrongcay_garden_rack_assignments_table(), [
                    'rack_id' => $rack_id,
                    'user_id' => $owner_user_id,
                    'garden_key' => $garden_key,
                    'household_key' => $garden_key,
                    'assigned_at' => $now,
                    'status' => 'active',
                    'notes' => 'Giao rack qua trang Unified Admin Beta',
                ], ['%d', '%d', '%s', '%s', '%s', '%s', '%s']);

                if (function_exists('aitrongcay_move_blynk_config_key')) {
                    aitrongcay_move_blynk_config_key($from_gk, $garden_key);
                }

                if (function_exists('aitrongcay_log_rack_inventory_event')) {
                    aitrongcay_log_rack_inventory_event($rack_id, 'assign', 'inventory', 'assigned', $owner_user_id, 'Giao vườn qua trang quản lý hợp nhất BETA', get_current_user_id());
                }

                // Add notification to garden
                $rack_name = $rack['rack_name'] ?: $rack['rack_code'];
                $notices = get_option('aitr_garden_notices_' . $garden_key, []);
                $notices[] = [
                    'id' => uniqid(),
                    'message' => "Bạn đã được gán thêm Rack <strong>{$rack_name}</strong> từ chủ khu vườn.",
                    'type' => 'success',
                    'time' => current_time('mysql')
                ];
                update_option('aitr_garden_notices_' . $garden_key, $notices);

                wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
                exit;
            }
        }
    }

    // Action 2: Release Rack
    if ($action === 'release_rack') {
        $rack_id = absint($_POST['rack_id'] ?? 0);
        $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));

        if ($rack_id <= 0) {
            wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Thiếu mã rack_id.'), $redirect));
            exit;
        }

        // Check if this is an admin cloned rack
        $cloned_racks = get_option('aitrongcay_cloned_racks_' . $garden_key, []);
        $key = array_search($rack_id, $cloned_racks, true);
        if ($key !== false) {
            // Just remove the clone
            unset($cloned_racks[$key]);
            update_option('aitrongcay_cloned_racks_' . $garden_key, array_values($cloned_racks), false);
            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
            exit;
        }

        if (function_exists('aitrongcay_release_rack_to_inventory')) {
            $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : [];
            $rack_name = ($rack['rack_name'] ?? '') ?: ($rack['rack_code'] ?? 'Rack');

            $result = aitrongcay_release_rack_to_inventory($rack_id);
            if (! empty($result['error'])) {
                wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Lỗi thu hồi: ' . (string) $result['error']), $redirect));
                exit;
            }

            // Add notification to garden
            $notices = get_option('aitr_garden_notices_' . $garden_key, []);
            $notices[] = [
                'id' => uniqid(),
                'message' => "Rack <strong>{$rack_name}</strong> đã được thu hồi bởi chủ khu vườn.",
                'type' => 'warning',
                'time' => current_time('mysql')
            ];
            update_option('aitr_garden_notices_' . $garden_key, $notices);

            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
            exit;
        } else {
            wp_safe_redirect(add_query_arg('beta_error', rawurlencode('Hàm thu hồi rack (aitrongcay_release_rack_to_inventory) không tồn tại trên hệ thống!'), $redirect));
            exit;
        }
    }

    // Action 3: Save Auto-Pump Config
    if ($action === 'save_pump_rules') {
        $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
        if ($garden_key !== '' && function_exists('aitrongcay_save_pump_rules')) {
            aitrongcay_save_pump_rules($garden_key, [
                'enabled'            => ! empty($_POST['enabled']),
                'soil_threshold_low' => (int) ($_POST['soil_threshold_low'] ?? 40),
                'pump_duration_sec'  => (int) ($_POST['pump_duration_sec']  ?? 30),
                'cooldown_min'       => (int) ($_POST['cooldown_min']       ?? 15),
                'time_start'         => sanitize_text_field((string) wp_unslash($_POST['time_start'] ?? '06:00')),
                'time_end'           => sanitize_text_field((string) wp_unslash($_POST['time_end']   ?? '22:00')),
                'days'               => array_map('intval', (array) ($_POST['days'] ?? [])),
            ]);
            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
            exit;
        }
    }

    // Action 3.5: Save Camera URLs
    if ($action === 'save_camera_urls') {
        $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
        $slot_ids = array_map('intval', (array) ($_POST['slot_ids'] ?? []));
        $camera_urls = (array) ($_POST['camera_urls'] ?? []);

        if (function_exists('aitrongcay_garden_rack_slots_table')) {
            $table = aitrongcay_garden_rack_slots_table();
            foreach ($slot_ids as $slot_id) {
                if ($slot_id <= 0) continue;
                $url = sanitize_text_field((string) ($camera_urls[$slot_id] ?? ''));
                $wpdb->update($table, ['camera_stream_url' => $url, 'updated_at' => current_time('mysql')], ['id' => $slot_id]);
            }
            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'gardens', 'selected_garden' => $garden_key], $redirect));
            exit;
        }
    }

    // Action 3.8: Save Single Rack Settings (Modal)
    if ($action === 'save_single_rack_settings') {
        $rack_id = absint($_POST['rack_id'] ?? 0);
        $blynk_token = sanitize_text_field((string) wp_unslash($_POST['rack_blynk_token'] ?? ''));
        if ($rack_id > 0) {
            $rack_table = aitrongcay_garden_racks_table();
            $old_token = $wpdb->get_var($wpdb->prepare("SELECT blynk_auth_token FROM {$rack_table} WHERE id = %d", $rack_id));
            
            $update_data = [
                'blynk_auth_token' => $blynk_token, 
                'controller_label' => $blynk_token,
                'updated_at' => current_time('mysql')
            ];
            
            // Only reset status to unknown if the token was changed
            if ($old_token !== $blynk_token) {
                $update_data['connectivity_status'] = 'unknown';
            }

            // Update rack blynk token, controller_label
            $wpdb->update(
                $rack_table, 
                $update_data, 
                ['id' => $rack_id]
            );

            // Update slot configurations
            if (isset($_POST['trays']) && is_array($_POST['trays'])) {
                $slots_table = aitrongcay_garden_rack_slots_table();
                foreach ($_POST['trays'] as $slot_key => $tray_data) {
                    $slot_id = absint($slot_key);
                    $slot_name = sanitize_text_field((string) ($tray_data['name'] ?? ''));
                    $raw_plant_val = sanitize_text_field((string) ($tray_data['plant_name'] ?? ''));
                    $plant_id = 0;
                    $plant_name = $raw_plant_val;
                    if (strpos($raw_plant_val, '|') !== false) {
                        $parts = explode('|', $raw_plant_val, 2);
                        $plant_id = (int) $parts[0];
                        $plant_name = trim($parts[1]);
                    }
                    $camera_url = esc_url_raw((string) ($tray_data['webcam_url'] ?? ''));
                    $pot_code = '';
                    
                    if ($slot_id > 0) {
                        $wpdb->update($slots_table, [
                            'slot_name' => $slot_name,
                            'plant_name' => $plant_name,
                            'camera_stream_url' => $camera_url,
                            'updated_at' => current_time('mysql')
                        ], ['id' => $slot_id]);
                        $pot_code = $wpdb->get_var($wpdb->prepare("SELECT pot_code FROM {$slots_table} WHERE id = %d", $slot_id));
                    } else if (strpos((string) $slot_key, 'new_') === 0) {
                        // Create missing slot in database
                        $slot_index = (int) str_replace('new_', '', (string) $slot_key);
                        if ($slot_index > 0) {
                            $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
                            $rack_code = $rack ? (string) ($rack['rack_code'] ?? 'RACK') : 'RACK';
                            $pot_code = sprintf('P-%03d', $slot_index);
                            $wpdb->insert($slots_table, [
                                'rack_id' => $rack_id,
                                'slot_index' => $slot_index,
                                'slot_code' => sprintf('%s-S%02d', $rack_code, $slot_index),
                                'slot_name' => $slot_name,
                                'plant_name' => $plant_name,
                                'pot_code' => $pot_code,
                                'camera_stream_url' => $camera_url,
                                'control_channel' => 'light' . $slot_index,
                                'created_at' => current_time('mysql'),
                                'updated_at' => current_time('mysql'),
                            ]);
                        }
                    }

                    // Also sync to active garden pot if it exists
                    $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
                    $garden_key = $rack ? (string) ($rack['garden_key'] ?? '') : '';

                    if ($garden_key !== '' && $pot_code !== '' && function_exists('aitrongcay_garden_pots_table')) {
                        $pots_table = aitrongcay_garden_pots_table();
                        // Resolve plant_id for update
                        $plant_id = 0;
                        if ($plant_name !== '' && $plant_name !== 'Cây chưa xác định') {
                            $onboarding_table = $wpdb->prefix . 'aitr_onboarding_plants';
                            $found_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$onboarding_table} WHERE public_name = %s LIMIT 1", $plant_name));
                            if ($found_id) {
                                $plant_id = (int)$found_id;
                            }
                        }

                        $update_data = [
                            'pot_name' => $slot_name,
                            'plant_name' => $plant_name === '' ? 'Cây chưa xác định' : $plant_name,
                            'plant_id' => $plant_id,
                            'video_url' => $camera_url,
                            'updated_at' => current_time('mysql')
                        ];

                        $updated = $wpdb->update($pots_table, $update_data, ['garden_key' => $garden_key, 'pot_code' => $pot_code]);

                        if ($updated === 0) {
                            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$pots_table} WHERE garden_key = %s AND pot_code = %s", $garden_key, $pot_code));
                            if (!$exists) {
                                // Try to find the plant_id if we have a plant_name
                                $plant_id = 0;
                                if ($plant_name !== '' && $plant_name !== 'Cây chưa xác định') {
                                    $onboarding_table = $wpdb->prefix . 'aitr_onboarding_plants';
                                    $found_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$onboarding_table} WHERE public_name = %s LIMIT 1", $plant_name));
                                    if ($found_id) {
                                        $plant_id = (int)$found_id;
                                    }
                                }

                                if (function_exists('aitrongcay_upsert_db_pot')) {
                                    aitrongcay_upsert_db_pot($garden_key, [
                                        'pot_code' => $pot_code,
                                        'pot_name' => $slot_name,
                                        'plant_name' => $plant_name === '' ? 'Cây chưa xác định' : $plant_name,
                                        'plant_id' => $plant_id,
                                        'video_url' => $camera_url,
                                        'status' => 'Đang theo dõi',
                                        'status_summary' => 'Khoang vừa được cấu hình.',
                                    ]);
                                } else {
                                    $wpdb->insert($pots_table, [
                                        'garden_key' => $garden_key,
                                        'pot_code' => $pot_code,
                                        'pot_name' => $slot_name,
                                        'plant_name' => $plant_name === '' ? 'Cây chưa xác định' : $plant_name,
                                        'plant_id' => $plant_id,
                                        'status' => 'Đang theo dõi',
                                        'status_summary' => '',
                                        'ph' => '',
                                        'temperature' => '',
                                        'humidity' => '',
                                        'light_label' => '',
                                        'light_device' => '',
                                        'pump_label' => '',
                                        'irrigation' => '',
                                        'video_url' => $camera_url,
                                        'image_url' => '',
                                        'ai_note' => '',
                                        'harvest_eta' => '',
                                        'latest_analysis_color' => '',
                                        'latest_analysis_label' => '',
                                        'latest_analysis_current_stage' => '',
                                        'created_at' => current_time('mysql'),
                                        'updated_at' => current_time('mysql')
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
            
            // Re-sync global rack config to wp_options (aitrongcay_rack_cfg_...)
            if (function_exists('aitrongcay_get_rack_by_id')) {
                $rack = aitrongcay_get_rack_by_id($rack_id);
                if ($rack) {
                    $garden_key = (string) ($rack['garden_key'] ?? '');
                    if ($garden_key !== '') {
                        $configs = get_option('aitrongcay_rack_cfg_' . $garden_key, []);
                        if (!is_array($configs)) $configs = [];
                        
                        // Find the rack index by matching rack_id if possible, or by sequence.
                        // For simplicity, we just rebuild the options array from DB to be safe.
                        $fresh_racks = $wpdb->get_results($wpdb->prepare("SELECT id, rack_name, blynk_auth_token FROM " . aitrongcay_garden_racks_table() . " WHERE garden_key = %s ORDER BY id ASC", $garden_key), ARRAY_A);
                        $new_configs = [];
                        foreach ($fresh_racks as $fr) {
                            $fr_id = (int) $fr['id'];
                            $trays = [];
                            $fresh_slots = function_exists('aitrongcay_get_rack_slots_by_rack_id') ? aitrongcay_get_rack_slots_by_rack_id($fr_id) : [];
                            // If this is the rack being saved, we merge POST data
                            $posted_trays_list = $fr_id === $rack_id ? array_values($_POST['trays'] ?? []) : null;
                            foreach ($fresh_slots as $slot) {
                                $s_idx = (int) ($slot['slot_index'] ?? 0);
                                if ($s_idx > 0) {
                                    $t_idx = $s_idx - 1;
                                    $tray_info = [
                                        'webcam_url' => trim((string) ($slot['camera_stream_url'] ?? '')),
                                        'name' => trim((string) ($slot['slot_name'] ?? ''))
                                    ];
                                    if ($fr_id === $rack_id && $posted_trays_list && isset($posted_trays_list[$t_idx])) {
                                        $posted_tray = $posted_trays_list[$t_idx];
                                        foreach (['temp', 'hum', 'soil', 'ph', 'ec', 'light', 'pump'] as $v) {
                                            if (isset($posted_tray['vpin_'.$v])) {
                                                $tray_info['vpin_'.$v] = sanitize_text_field((string)$posted_tray['vpin_'.$v]);
                                            }
                                        }
                                    } else {
                                        // Preserve existing vpins from old config if not currently posting
                                        foreach ($configs as $old_cfg) {
                                            if (isset($old_cfg['blynk_auth_token']) && $old_cfg['blynk_auth_token'] === $fr['blynk_auth_token'] && isset($old_cfg['trays'][$t_idx])) {
                                                foreach (['temp', 'hum', 'soil', 'ph', 'ec', 'light', 'pump'] as $v) {
                                                    if (isset($old_cfg['trays'][$t_idx]['vpin_'.$v])) {
                                                        $tray_info['vpin_'.$v] = $old_cfg['trays'][$t_idx]['vpin_'.$v];
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                    $trays[$t_idx] = $tray_info;
                                }
                            }
                            ksort($trays);
                            $new_configs[] = [
                                'rack_id' => $fr_id,
                                'rack_name' => (string) ($fr['rack_name'] ?? 'Rack'),
                                'blynk_auth_token' => (string) ($fr['blynk_auth_token'] ?? ''),
                                'trays' => $trays
                            ];
                        }
                        update_option('aitrongcay_rack_cfg_' . $garden_key, $new_configs);
                        
                        // Clear the sensor cache for this rack's trays so new token takes effect immediately
                        foreach ($configs as $idx => $cfg) {
                            if (isset($cfg['rack_id']) && (int) $cfg['rack_id'] === $rack_id) {
                                $rack_index = $idx;
                                break;
                            }
                        }
                        // We must clear cache based on the rack's index in the array
                        foreach ($new_configs as $idx => $cfg) {
                            if ((int) $cfg['rack_id'] === $rack_id) {
                                foreach ($cfg['trays'] as $ti => $t) {
                                    delete_transient('aitr_t_' . $garden_key . '_r' . $idx . '_t' . $ti);
                                }
                            }
                        }
                    }
                }
            }
            
            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => sanitize_text_field($_POST['return_tab'] ?? 'racks')], $redirect));
            exit;
        }
    }

    // Action 3.9: Save Hydration Settings (Modal)
    if ($action === 'save_hydration_settings') {
        $rack_id = absint($_POST['rack_id'] ?? 0);
        if ($rack_id > 0) {
            if (isset($_POST['tanks']) && is_array($_POST['tanks'])) {
                $hydration_data = [];
                foreach ($_POST['tanks'] as $tank_id => $tank_data) {
                    $tank_id = absint($tank_id);
                    if ($tank_id <= 0) continue;
                    
                    $hydration_data[$tank_id] = [
                        'vpin_water_level' => sanitize_text_field((string) ($tank_data['vpin_water_level'] ?? '')),
                        'vpin_ph' => sanitize_text_field((string) ($tank_data['vpin_ph'] ?? '')),
                        'vpin_tds' => sanitize_text_field((string) ($tank_data['vpin_tds'] ?? '')),
                        'vpin_cool_pump' => sanitize_text_field((string) ($tank_data['vpin_cool_pump'] ?? '')),
                    ];
                }
                update_option("aitrongcay_hydration_config_{$rack_id}", $hydration_data);
            }
            
            wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => sanitize_text_field($_POST['return_tab'] ?? 'hydration')], $redirect));
            exit;
        }
    }

    // Action 4: Save System Settings
    if ($action === 'save_system_settings') {
        $gemini_key = sanitize_text_field((string) wp_unslash($_POST['gemini_api_key'] ?? ''));
        if ($gemini_key !== '') {
            update_option('aitrongcay_gemini_api_key', $gemini_key);
        }

        // Save AI Agent
        if (function_exists('aitrongcay_ai_agent_option_name')) {
            $ai_agent_config = [
                'enabled' => empty($_POST['ai_enabled']) ? 0 : 1,
                'mode' => sanitize_key((string) wp_unslash($_POST['ai_mode'] ?? 'adapter-ready')),
                'endpoint_url' => esc_url_raw((string) wp_unslash($_POST['ai_endpoint_url'] ?? '')),
                'bearer_token' => trim((string) wp_unslash($_POST['ai_bearer_token'] ?? '')),
                'model' => sanitize_text_field((string) wp_unslash($_POST['ai_model'] ?? 'openclaw')),
                'timeout_seconds' => max(5, min(90, (int) ($_POST['ai_timeout_seconds'] ?? 90))),
            ];
            update_option(aitrongcay_ai_agent_option_name(), $ai_agent_config, false);
        }

        // Save Payments
        $payment_rules = [
            'bank_bin'      => sanitize_text_field((string) wp_unslash($_POST['bank_bin'] ?? '')),
            'bank_acc'      => sanitize_text_field((string) wp_unslash($_POST['bank_acc'] ?? '')),
            'bank_name'     => sanitize_text_field((string) wp_unslash($_POST['bank_name'] ?? '')),
            'qr_template'   => sanitize_text_field((string) wp_unslash($_POST['qr_template'] ?? 'qr_only')),
            'price_per_day' => max(0, (int) ($_POST['price_per_day'] ?? 20000)),
        ];
        update_option('aitrongcay_payment_rules', $payment_rules, false);

        wp_safe_redirect(add_query_arg(['beta_success' => '1', 'tab' => 'settings'], $redirect));
        exit;
    }
}

// Render Page Content with Premium UI
function aitrongcay_render_unified_admin_beta_page(): void {
    if (! current_user_can('manage_options')) {
        return;
    }

    global $wpdb;

    // Load libraries variables
    $racks = function_exists('aitrongcay_list_racks') ? aitrongcay_list_racks() : [];
    $gardens = function_exists('aitrongcay_known_gardens_for_device_admin') ? aitrongcay_known_gardens_for_device_admin() : [];
    $available_racks = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aitr_garden_racks WHERE status = 'inventory' ORDER BY id ASC", ARRAY_A) ?: [];

    // Counters
    $active_gardens_count = 0;
    foreach ($racks as $r) {
        if (($r['status'] ?? '') === 'assigned') {
            $active_gardens_count++;
        }
    }
    $total_racks_count = count($racks);
    $inventory_racks_count = count($available_racks);

    // Active tab
    $active_tab = sanitize_key($_GET['tab'] ?? 'overview');
    $selected_garden_key = sanitize_text_field($_GET['selected_garden'] ?? '');

    // Notifications
    $success_msg = isset($_GET['beta_success']) ? 'Thao tác thực hiện thành công!' : '';
    $error_msg = isset($_GET['beta_error']) ? sanitize_text_field(wp_unslash($_GET['beta_error'])) : '';

    // Load configs for settings
    $gemini_key = function_exists('aitrongcay_get_gemini_api_key') ? aitrongcay_get_gemini_api_key() : '';
    $gemini_masked = $gemini_key !== '' ? substr($gemini_key, 0, 6) . '...' . substr($gemini_key, -4) : '';
    $ai_config = function_exists('aitrongcay_ai_agent_config') ? aitrongcay_ai_agent_config() : [];
    $payment_rules = get_option('aitrongcay_payment_rules', [
        'bank_bin'      => '',
        'bank_acc'      => '',
        'bank_name'     => '',
        'qr_template'   => 'qr_only',
        'price_per_day' => 20000,
    ]);

    ?>
    <!-- Google Fonts Outfit & Font-Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Modern Premium Dark-Green Dashboard CSS styling */
        .aitr-beta-wrapper {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            padding: 24px;
            margin-right: 20px;
            margin-top: 15px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            min-height: 80vh;
        }

        .aitr-beta-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #1e293b;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .aitr-beta-title h1 {
            color: #10b981;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.2);
        }

        .aitr-beta-title p {
            color: #94a3b8;
            margin: 5px 0 0;
            font-size: 14px;
        }

        .aitr-beta-badge-live {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            padding: 6px 14px;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid rgba(16, 185, 129, 0.3);
            animation: pulse-border 2s infinite;
        }

        /* Stats Grid */
        .aitr-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .aitr-stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .aitr-stat-card:hover {
            transform: translateY(-3px);
            border-color: #10b981;
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.1);
        }

        .aitr-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .aitr-stat-info h3 {
            margin: 0;
            font-size: 12px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
        }

        .aitr-stat-info .num {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-top: 4px;
        }

        /* Nav Tabs */
        .aitr-beta-tabs {
            display: flex;
            gap: 8px;
            background: #1e293b;
            padding: 6px;
            border-radius: 10px;
            margin-bottom: 28px;
            border: 1px solid #334155;
        }

        .aitr-beta-tab-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none !important;
        }

        .aitr-beta-tab-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }

        .aitr-beta-tab-btn.active {
            background: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        /* Forms, Tables & Panels */
        .aitr-panel {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 24px;
        }

        .aitr-panel-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .aitr-panel-title span {
            color: #10b981;
        }

        /* Custom alert styles */
        .aitr-alert-beta {
            padding: 14px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .aitr-alert-beta-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #34d399;
        }

        .aitr-alert-beta-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }

        /* Two Column Layout for Gardens Tab */
        .aitr-two-col {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 24px;
        }

        .aitr-list-sidebar {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            max-height: 600px;
            overflow-y: auto;
        }

        .aitr-list-item {
            padding: 14px 18px;
            border-bottom: 1px solid #334155;
            cursor: pointer;
            transition: all 0.2s ease;
            display: block;
            text-decoration: none !important;
            color: #94a3b8;
        }

        .aitr-list-item:last-child {
            border-bottom: none;
        }

        .aitr-list-item:hover {
            background: rgba(255, 255, 255, 0.02);
            color: #f1f5f9;
        }

        .aitr-list-item.active {
            background: rgba(16, 185, 129, 0.1);
            border-left: 4px solid #10b981;
            color: #10b981;
            font-weight: 600;
        }

        .aitr-list-item-title {
            font-size: 14px;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .aitr-list-item-sub {
            font-size: 11px;
            color: #64748b;
        }

        /* Custom Table Styling */
        .aitr-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .aitr-table th {
            text-align: left;
            padding: 12px 16px;
            background: #0f172a;
            color: #94a3b8;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #334155;
        }

        .aitr-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #334155;
            color: #cbd5e1;
            font-size: 13px;
        }

        .aitr-table tr:hover td {
            background: rgba(255, 255, 255, 0.01);
            color: #ffffff;
        }

        /* Badge design */
        .aitr-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
        }

        .aitr-badge-online { background: rgba(16, 185, 129, 0.1); color: #34d399; }
        .aitr-badge-offline { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .aitr-badge-warn { background: rgba(245, 158, 11, 0.1); color: #fbbf24; }

        /* Form Controls */
        .aitr-form-group {
            margin-bottom: 20px;
        }

        .aitr-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #e2e8f0;
            font-size: 13px;
        }

        .aitr-form-control {
            width: 100%;
            background: #0f172a !important;
            border: 1px solid #475569 !important;
            border-radius: 8px;
            padding: 10px 14px;
            color: #ffffff !important;
            font-size: 14px;
            font-family: inherit;
        }

        .aitr-form-control::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        .aitr-form-control:focus {
            border-color: #10b981 !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }

        select.aitr-form-control {
            color: #ffffff !important;
        }

        select.aitr-form-control option {
            background: #1e293b !important;
            color: #ffffff !important;
            padding: 10px;
        }

        .aitr-btn {
            background: #10b981;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .aitr-btn:hover {
            background: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .aitr-btn-secondary {
            background: #475569;
        }

        .aitr-btn-secondary:hover {
            background: #334155;
            box-shadow: none;
        }

        .aitr-btn-danger {
            background: #ef4444;
        }

        .aitr-btn-danger:hover {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Detail boxes inside Gardens */
        .aitr-garden-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .aitr-garden-meta-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 18px;
        }

        .aitr-garden-meta-title {
            font-size: 14px;
            font-weight: 700;
            color: #10b981;
            margin: 0 0 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Grid for details inside Tab 2 */
        .aitr-meta-rows {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        .aitr-meta-row-single {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .aitr-meta-row-single span.lbl {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
        }

        .aitr-meta-row-single span.val {
            font-size: 14px;
            color: #e2e8f0;
            font-weight: 500;
        }
    </style>

    <div class="aitr-beta-wrapper">
        <!-- Header -->
        <div class="aitr-beta-header">
            <div class="aitr-beta-title">
                <h1><i class="fa-solid fa-leaf"></i> AI Trồng Cây — Bảng điều khiển tích hợp</h1>
                <p>Môi trường thử nghiệm song song (BETA) - Kết nối toàn bộ tính năng quản trị vào một luồng duy nhất</p>
            </div>
            <div class="aitr-beta-badge-live">
                <i class="fa-solid fa-circle" style="font-size:8px;vertical-align:middle;margin-right:4px"></i> Beta Live Mode
            </div>
        </div>

        <!-- Alert messages -->
        <?php if ($success_msg !== ''): ?>
            <div class="aitr-alert-beta aitr-alert-beta-success">
                <i class="fa-solid fa-circle-check" style="font-size:16px"></i>
                <div><?php echo esc_html($success_msg); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error_msg !== ''): ?>
            <div class="aitr-alert-beta aitr-alert-beta-error">
                <i class="fa-solid fa-circle-exclamation" style="font-size:16px"></i>
                <div><?php echo esc_html($error_msg); ?></div>
            </div>
        <?php endif; ?>

        <?php
        $check_result = get_transient('aitrongcay_rack_check_' . get_current_user_id());
        if ($check_result) {
            delete_transient('aitrongcay_rack_check_' . get_current_user_id());
        }
        if (is_array($check_result) && ! empty($check_result['report'])) :
            $report = (array) $check_result['report'];
        ?>
            <div class="aitr-alert-beta" style="background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; flex-direction: column; align-items: flex-start; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px; font-weight: 600;">
                    <i class="fa-solid fa-square-rss" style="font-size:16px; color: #60a5fa;"></i>
                    <span>Check Blynk rack #<?php echo esc_html((string) ($check_result['rack_id'] ?? '')); ?>: <?php echo esc_html((string) ($report['summary'] ?? '')); ?></span>
                </div>
                <?php if (! empty($report['ok'])) : ?>
                    <div style="font-size: 13px; margin-left: 26px; white-space: pre-line;">
                        <strong style="color: #34d399;">OK:</strong><br>
                        <?php echo esc_html(implode("\n", array_map('strval', (array) $report['ok']))); ?>
                    </div>
                <?php endif; ?>
                <?php if (! empty($report['errors'])) : ?>
                    <div style="font-size: 13px; margin-left: 26px; white-space: pre-line;">
                        <strong style="color: #f87171;">Lỗi / chưa kết nối:</strong><br>
                        <?php echo esc_html(implode("\n", array_map('strval', (array) $report['errors']))); ?>
                    </div>
                <?php endif; ?>
                <?php if (! empty($report['controls'])) : ?>
                    <div style="font-size: 13px; margin-left: 26px; white-space: pre-line;">
                        <strong style="color: #fbbf24;">Điều khiển:</strong><br>
                        <?php echo esc_html(implode("\n", array_map('strval', (array) $report['controls']))); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="aitr-stats-grid">
            <div class="aitr-stat-card">
                <div class="aitr-stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="aitr-stat-info">
                    <h3>Vườn hoạt động</h3>
                    <div class="num"><?php echo esc_html((string) $active_gardens_count); ?></div>
                </div>
            </div>
            <div class="aitr-stat-card">
                <div class="aitr-stat-icon"><i class="fa-solid fa-warehouse"></i></div>
                <div class="aitr-stat-info">
                    <h3>Rack trong kho</h3>
                    <div class="num"><?php echo esc_html((string) $inventory_racks_count); ?></div>
                </div>
            </div>
            <div class="aitr-stat-card">
                <div class="aitr-stat-icon"><i class="fa-solid fa-server"></i></div>
                <div class="aitr-stat-info">
                    <h3>Tổng số Racks</h3>
                    <div class="num"><?php echo esc_html((string) $total_racks_count); ?></div>
                </div>
            </div>
            <div class="aitr-stat-card">
                <div class="aitr-stat-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="aitr-stat-info">
                    <h3>Tổng đơn hàng</h3>
                    <div class="num">
                        <?php
                            $orders_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}aitr_orders");
                            echo esc_html((string) ($orders_count ?: 0));
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="aitr-beta-tabs">
            <a href="?page=aitrongcay-unified-admin-beta&tab=overview" class="aitr-beta-tab-btn <?php echo $active_tab === 'overview' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Tổng quan hệ thống
            </a>
            <a href="?page=aitrongcay-unified-admin-beta&tab=gardens" class="aitr-beta-tab-btn <?php echo $active_tab === 'gardens' ? 'active' : ''; ?>">
                <i class="fa-solid fa-seedling"></i> Quản lý Vườn khách hàng
            </a>
            <a href="?page=aitrongcay-unified-admin-beta&tab=racks" class="aitr-beta-tab-btn <?php echo $active_tab === 'racks' ? 'active' : ''; ?>">
                <i class="fa-solid fa-cubes"></i> Quản lý Kho Racks
            </a>
            <a href="?page=aitrongcay-unified-admin-beta&tab=settings" class="aitr-beta-tab-btn <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
                <i class="fa-solid fa-sliders"></i> Cấu hình Hệ thống
            </a>
            <a href="?page=aitrongcay-unified-admin-beta&tab=hydration" class="aitr-beta-tab-btn <?php echo $active_tab === 'hydration' ? 'active' : ''; ?>">
                <i class="fa-solid fa-droplet"></i> Cấu hình Hydration
            </a>
        </div>

        <!-- Tab 1: Overview Panel -->
        <?php if ($active_tab === 'overview'): ?>
            <div class="aitr-panel">
                <div class="aitr-panel-title">
                    <span><i class="fa-solid fa-chart-pie"></i> Tổng quan Vận hành & Giám sát nhanh</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                    <!-- Left: Status Active Devices -->
                    <div>
                        <h3 style="margin-top:0;color:#10b981;font-size:16px"><i class="fa-solid fa-circle-nodes"></i> Trạng thái các Rack hoạt động</h3>
                        <table class="aitr-table">
                            <thead>
                                <tr>
                                    <th>Tên thiết bị</th>
                                    <th>Khu vườn gán</th>
                                    <th>Kết nối</th>
                                    <th>Cập nhật cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $active_racks_list = array_filter($racks, static fn(array $rack): bool => (string) ($rack['status'] ?? '') === 'assigned');
                                if (empty($active_racks_list)):
                                ?>
                                    <tr><td colspan="4" style="text-align:center;color:#64748b">Chưa có thiết bị nào được gán cho vườn.</td></tr>
                                <?php
                                else:
                                    foreach ($active_racks_list as $ar):
                                        $conn = (string) ($ar['connectivity_status'] ?? 'unknown');
                                        $badge_class = 'aitr-badge-warn';
                                        if ($conn === 'online') { $badge_class = 'aitr-badge-online'; }
                                        elseif ($conn === 'offline') { $badge_class = 'aitr-badge-offline'; }
                                ?>
                                        <tr>
                                            <td><strong><?php echo esc_html((string) ($ar['rack_name'] ?? $ar['rack_code'])); ?></strong></td>
                                            <td><code><?php echo esc_html(substr((string) ($ar['garden_key'] ?? ''), 0, 15) . '...'); ?></code></td>
                                            <td><span class="aitr-badge <?php echo $badge_class; ?>"><?php echo esc_html($conn); ?></span></td>
                                            <td><?php echo ! empty($ar['last_seen_at']) ? esc_html((string) $ar['last_seen_at']) : '—'; ?></td>
                                        </tr>
                                <?php
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Right: Quick logs and tools info -->
                    <div>
                        <h3 style="margin-top:0;color:#10b981;font-size:16px"><i class="fa-solid fa-clock-rotate-left"></i> Sự kiện Log gần đây</h3>
                        <div style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:15px;max-height:300px;overflow-y:auto">
                            <?php
                            $logs = function_exists('aitrongcay_get_rack_inventory_events') ? aitrongcay_get_rack_inventory_events(15) : [];
                            if (empty($logs)):
                                echo '<p style="color:#64748b;font-size:13px;text-align:center;margin:20px 0">Chưa có log sự kiện nào.</p>';
                            else:
                                foreach ($logs as $log):
                                    $time = ! empty($log['created_at']) ? mysql2date('d/m H:i', (string) $log['created_at']) : '';
                            ?>
                                    <div style="font-size:12px;border-bottom:1px solid #1e293b;padding:8px 0;line-height:1.4">
                                        <span style="color:#64748b"><?php echo esc_html($time); ?></span> | 
                                        <span style="color:#38bdf8">[<?php echo esc_html((string) ($log['event_type'] ?? '')); ?>]</span>: 
                                        <span style="color:#e2e8f0"><?php echo esc_html((string) ($log['description'] ?? '')); ?></span>
                                    </div>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Tab 2: Gardens Panel (Unified details) -->
        <?php elseif ($active_tab === 'gardens'): ?>
            <div class="aitr-panel">
                <div class="aitr-panel-title">
                    <span><i class="fa-solid fa-seedling"></i> Quản lý & Cấu hình chi tiết Khu vườn của Khách</span>
                </div>

                <div class="aitr-two-col">
                    <!-- Left Sidebar List of Gardens -->
                    <div class="aitr-list-sidebar">
                        <div style="padding: 12px; border-bottom: 1px solid #334155; position: sticky; top: 0; background: #1e293b; z-index: 10; border-radius: 12px 12px 0 0;">
                            <input type="text" id="aitrGardenSearch" class="aitr-form-control" placeholder="🔍 Tìm tên khách, email..." onkeyup="aitrFilterGardens()" style="font-size: 13px; padding: 8px 12px; background: #0f172a; border-radius: 6px;">
                        </div>
                        <script>
                        function aitrFilterGardens() {
                            var filter = document.getElementById('aitrGardenSearch').value.toLowerCase();
                            var nodes = document.querySelectorAll('.aitr-list-sidebar .aitr-list-item');
                            nodes.forEach(function(node) {
                                var text = node.innerText.toLowerCase();
                                node.style.display = text.includes(filter) ? '' : 'none';
                            });
                        }
                        </script>
                        <?php
                        if (empty($gardens)):
                            echo '<p style="padding:20px;text-align:center;color:#64748b">Không tìm thấy khu vườn nào.</p>';
                        else:
                            foreach ($gardens as $g):
                                $g_key = (string) $g['garden_key'];
                                $is_selected = $selected_garden_key === $g_key;
                                $g_label = (string) $g['label'];
                                if (empty($g_label)) { $g_label = $g_key; }
                        ?>
                                <a href="?page=aitrongcay-unified-admin-beta&tab=gardens&selected_garden=<?php echo rawurlencode($g_key); ?>" class="aitr-list-item <?php echo $is_selected ? 'active' : ''; ?>">
                                    <div class="aitr-list-item-title"><?php echo esc_html($g_label); ?></div>
                                    <div class="aitr-list-item-sub"><?php echo esc_html((string) $g['owner_email']); ?></div>
                                </a>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>

                    <!-- Right detail window -->
                    <div>
                        <?php if ($selected_garden_key === ''): ?>
                            <div style="text-align:center;padding:80px 20px;color:#64748b">
                                <i class="fa-solid fa-circle-info" style="font-size:32px;margin-bottom:12px;color:#475569"></i>
                                <p>Vui lòng chọn một khu vườn ở danh sách bên trái để cấu hình chi tiết.</p>
                            </div>
                        <?php else:
                            // Fetch detail for selected garden
                            $cur_garden = null;
                            foreach ($gardens as $g) {
                                if ((string) $g['garden_key'] === $selected_garden_key) {
                                    $cur_garden = $g;
                                    break;
                                }
                            }
                            $g_label = $cur_garden ? ($cur_garden['label'] ?: $selected_garden_key) : $selected_garden_key;
                            $g_email = $cur_garden ? $cur_garden['owner_email'] : 'Chưa cập nhật';

                            // Get mapped racks of this garden
                            $mapped_racks = [];
                            $cloned_rack_ids = get_option('aitrongcay_cloned_racks_' . $selected_garden_key, []);
                            
                            foreach ($racks as $r) {
                                $r_id = (int) ($r['id'] ?? 0);
                                if ((string) ($r['garden_key'] ?? '') === $selected_garden_key) {
                                    $mapped_racks[] = $r;
                                } elseif (in_array($r_id, $cloned_rack_ids, true)) {
                                    $r['_is_clone'] = true;
                                    $mapped_racks[] = $r;
                                }
                            }
                            
                            $selected_is_admin = false;
                            $owner_user = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($selected_garden_key) : null;
                            if ($owner_user instanceof WP_User && user_can($owner_user, 'manage_options')) {
                                $selected_is_admin = true;
                            }
                            $assignable_racks = $selected_is_admin ? $wpdb->get_results("SELECT * FROM {$wpdb->prefix}aitr_garden_racks ORDER BY id ASC", ARRAY_A) ?: [] : $available_racks;

                            // Get auto pump settings
                            $pump_rules = function_exists('aitrongcay_get_pump_rules') ? aitrongcay_get_pump_rules($selected_garden_key) : [];
                        ?>
                            <div class="aitr-garden-details">
                                <!-- Box 1: Owner Details -->
                                <div class="aitr-garden-meta-box">
                                    <h3 class="aitr-garden-meta-title"><i class="fa-solid fa-circle-user"></i> Thông tin sở hữu</h3>
                                    <div class="aitr-meta-rows">
                                        <div class="aitr-meta-row-single">
                                            <span class="lbl">Tên Vườn</span>
                                            <span class="val"><?php echo esc_html($g_label); ?></span>
                                        </div>
                                        <div class="aitr-meta-row-single">
                                            <span class="lbl">Mã Khu Vườn (Garden Key)</span>
                                            <span class="val" style="font-family:monospace;font-size:12px;color:#10b981"><?php echo esc_html($selected_garden_key); ?></span>
                                        </div>
                                        <div class="aitr-meta-row-single" style="margin-top:10px">
                                            <span class="lbl">Email Khách hàng</span>
                                            <span class="val"><?php echo esc_html($g_email); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box 2: Racks Device Assignment -->
                                <div class="aitr-garden-meta-box">
                                    <h3 class="aitr-garden-meta-title"><i class="fa-solid fa-cube"></i> Thiết bị Rack được gán</h3>
                                    <?php if (empty($mapped_racks)): ?>
                                        <div style="background:rgba(239,68,68,0.05);border:1px dashed #ef4444;color:#fca5a5;padding:12px;border-radius:6px;font-size:13px;margin-bottom:15px">
                                            ⚠️ Hiện chưa có Rack vật lý nào gán vào khu vườn này! Dashboard khách hàng sẽ dùng các giá trị ảo mặc định (Placeholder).
                                        </div>
                                    <?php else: ?>
                                        <table class="aitr-table" style="margin-bottom:15px">
                                            <thead>
                                                <tr>
                                                    <th>Mã thiết bị</th>
                                                    <th>Tên hiển thị</th>
                                                    <th>Kết nối Blynk</th>
                                                    <th>Hành động</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mapped_racks as $mr): ?>
                                                    <tr>
                                                        <td><code><?php echo esc_html((string) ($mr['rack_code'] ?? '')); ?></code></td>
                                                        <td><strong><?php echo esc_html((string) ($mr['rack_name'] ?? '')); ?></strong> <?php if (!empty($mr['_is_clone'])) echo '<span style="color:#fbbf24;font-size:11px;margin-left:4px">[Bản sao]</span>'; ?></td>
                                                        <td>
                                                            <?php
                                                                $conn = (string) ($mr['connectivity_status'] ?? 'unknown');
                                                                $bclass = 'aitr-badge-warn';
                                                                if ($conn === 'online') { $bclass = 'aitr-badge-online'; }
                                                                elseif ($conn === 'offline') { $bclass = 'aitr-badge-offline'; }
                                                            ?>
                                                            <span class="aitr-badge <?php echo $bclass; ?>"><?php echo esc_html($conn); ?></span>
                                                        </td>
                                                        <td>
                                                            <div style="display:flex;gap:8px">
                                                                <button type="button" class="aitr-btn aitr-btn-secondary" style="padding:4px 8px;font-size:11px" onclick="openAitrRackSettingsModal(<?php echo (int) ($mr['id'] ?? 0); ?>)">
                                                                    <i class="fa-solid fa-gear"></i> Cài đặt
                                                                </button>
                                                                <form method="post" style="display:inline" onsubmit="event.preventDefault(); aitrConfirmRevoke(this);">
                                                                    <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                                                    <input type="hidden" name="beta_action" value="release_rack">
                                                                    <input type="hidden" name="rack_id" value="<?php echo (int) ($mr['id'] ?? 0); ?>">
                                                                    <input type="hidden" name="garden_key" value="<?php echo esc_attr($selected_garden_key); ?>">
                                                                    <button type="submit" class="aitr-btn aitr-btn-danger" style="padding:4px 8px;font-size:11px"><i class="fa-solid fa-arrow-right-left"></i> Thu hồi về kho</button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>

                                    <!-- Assign a Rack Form -->
                                    <form method="post" style="background:#1e293b;border:1px solid #334155;border-radius:8px;padding:15px;display:flex;align-items:flex-end;gap:15px">
                                        <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                        <input type="hidden" name="beta_action" value="assign_rack">
                                        <input type="hidden" name="garden_key" value="<?php echo esc_attr($selected_garden_key); ?>">
                                        <div style="flex:1">
                                            <label style="display:block;margin-bottom:6px;font-size:12px;font-weight:600;color:#94a3b8">Giao thêm Rack từ kho thiết bị:</label>
                                            <select name="rack_id" class="aitr-form-control" style="background:#0f172a;height:40px" required>
                                                <option value="">-- Chọn Rack trong kho --</option>
                                                <?php foreach ($assignable_racks as $avail): ?>
                                                    <?php $status_label = (($avail['status'] ?? 'inventory') !== 'inventory') ? ' [Đang cho thuê]' : ''; ?>
                                                    <option value="<?php echo (int) ($avail['id'] ?? 0); ?>">
                                                        <?php echo esc_html((string) ($avail['rack_name'] ?? $avail['rack_code']) . $status_label); ?> (<?php echo (int) ($avail['slot_count'] ?? 3); ?> khoang)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <button type="submit" class="aitr-btn" style="height:40px"><i class="fa-solid fa-plus"></i> Giao Rack</button>
                                    </form>
                                </div>

                                <!-- Box 3: Auto Pump Configuration -->
                                <?php if (! empty($pump_rules)): ?>
                                    <div class="aitr-garden-meta-box">
                                        <h3 class="aitr-garden-meta-title"><i class="fa-solid fa-faucet-drip"></i> Cài đặt tự động tưới nước (Auto Pump)</h3>
                                        <form method="post">
                                            <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                            <input type="hidden" name="beta_action" value="save_pump_rules">
                                            <input type="hidden" name="garden_key" value="<?php echo esc_attr($selected_garden_key); ?>">

                                            <div class="aitr-meta-rows">
                                                <div class="aitr-form-group">
                                                    <label><input type="checkbox" name="enabled" value="1" <?php checked((bool) $pump_rules['enabled']); ?>> Kích hoạt tự động tưới</label>
                                                </div>
                                                <div class="aitr-form-group">
                                                    <label>Ngưỡng độ ẩm bật bơm (%)</label>
                                                    <input type="number" name="soil_threshold_low" class="aitr-form-control" value="<?php echo esc_attr((string) $pump_rules['soil_threshold_low']); ?>" min="0" max="100">
                                                </div>
                                                <div class="aitr-form-group">
                                                    <label>Thời lượng bơm mỗi chu kỳ (giây)</label>
                                                    <input type="number" name="pump_duration_sec" class="aitr-form-control" value="<?php echo esc_attr((string) $pump_rules['pump_duration_sec']); ?>" min="5" max="300">
                                                </div>
                                                <div class="aitr-form-group">
                                                    <label>Thời gian nghỉ cooldown tối thiểu (phút)</label>
                                                    <input type="number" name="cooldown_min" class="aitr-form-control" value="<?php echo esc_attr((string) $pump_rules['cooldown_min']); ?>" min="1" max="1440">
                                                </div>
                                                <div class="aitr-form-group">
                                                    <label>Khung giờ hoạt động</label>
                                                    <div style="display:flex;align-items:center;gap:10px">
                                                        <input type="time" name="time_start" class="aitr-form-control" value="<?php echo esc_attr((string) $pump_rules['time_start']); ?>">
                                                        <span>đến</span>
                                                        <input type="time" name="time_end" class="aitr-form-control" value="<?php echo esc_attr((string) $pump_rules['time_end']); ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <div style="margin-top:15px;text-align:right">
                                                <button type="submit" class="aitr-btn"><i class="fa-solid fa-save"></i> Lưu cấu hình tưới</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Box 4: Camera Setup -->
                                <?php if (! empty($mapped_racks) && function_exists('aitrongcay_get_rack_slots_by_rack_id')): ?>
                                    <div class="aitr-garden-meta-box">
                                        <h3 class="aitr-garden-meta-title"><i class="fa-solid fa-video"></i> Cài đặt luồng Camera các khoang (Streams)</h3>
                                        <form method="post">
                                            <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                            <input type="hidden" name="beta_action" value="save_camera_urls">
                                            <input type="hidden" name="garden_key" value="<?php echo esc_attr($selected_garden_key); ?>">

                                            <?php foreach ($mapped_racks as $mr): ?>
                                                <?php 
                                                    $slots = aitrongcay_get_rack_slots_by_rack_id((int) $mr['id']);
                                                    // Auto-populate slots if missing
                                                    if (empty($slots)) {
                                                        $slot_count = max(1, (int) ($mr['slot_count'] ?? 3));
                                                        if (function_exists('aitrongcay_garden_rack_slots_table')) {
                                                            $slots_table = aitrongcay_garden_rack_slots_table();
                                                            for ($i = 1; $i <= $slot_count; $i++) {
                                                                $wpdb->insert($slots_table, [
                                                                    'rack_id' => $mr['id'],
                                                                    'slot_index' => $i,
                                                                    'slot_code' => sprintf('%s-S%02d', $mr['rack_code'], $i),
                                                                    'slot_name' => 'Khoang ' . $i,
                                                                    'pot_code' => sprintf('P-%03d', $i),
                                                                    'created_at' => current_time('mysql'),
                                                                    'updated_at' => current_time('mysql'),
                                                                ]);
                                                            }
                                                            $slots = aitrongcay_get_rack_slots_by_rack_id((int) $mr['id']);
                                                        }
                                                    }

                                                    if (! empty($slots)):
                                                ?>
                                                    <div style="background:#0f172a; border: 1px solid #334155; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                                        <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #f1f5f9;"><?php echo esc_html($mr['rack_name'] ?: $mr['rack_code']); ?></h4>
                                                        <?php foreach ($slots as $slot): ?>
                                                            <div class="aitr-form-group" style="margin-bottom: 12px; display: flex; align-items: center; gap: 15px;">
                                                                <label style="width: 150px; margin: 0; font-weight: 500; font-size: 13px;">
                                                                    Khoang <?php echo (int) $slot['slot_index']; ?>
                                                                    <?php if (!empty($slot['slot_name'])): ?><br><span style="color:#64748b; font-size: 11px;"><?php echo esc_html($slot['slot_name']); ?></span><?php endif; ?>
                                                                </label>
                                                                <input type="hidden" name="slot_ids[]" value="<?php echo (int) $slot['id']; ?>">
                                                                <input type="url" name="camera_urls[<?php echo (int) $slot['id']; ?>]" class="aitr-form-control" style="flex:1" placeholder="Nhập link stream m3u8 hoặc luồng MJPEG..." value="<?php echo esc_attr((string) $slot['camera_stream_url']); ?>">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>

                                            <div style="margin-top:15px;text-align:right">
                                                <button type="submit" class="aitr-btn aitr-btn-secondary"><i class="fa-solid fa-save"></i> Lưu luồng Camera</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- Tab 3: Racks Inventory Panel -->
        <?php elseif ($active_tab === 'racks'): ?>
            <div class="aitr-panel">
                <div class="aitr-panel-title">
                    <span><i class="fa-solid fa-cubes"></i> Quản lý Kho Thiết Bị Racks vật lý</span>
                </div>

                <!-- Add new warehouse rack -->
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#0f172a;border:1px solid #334155;border-radius:10px;padding:20px;margin-bottom:24px">
                    <?php wp_nonce_field('aitrongcay_add_inventory_rack'); ?>
                    <input type="hidden" name="action" value="aitrongcay_add_inventory_rack">
                    <h3 style="margin-top:0;color:#10b981;font-size:15px"><i class="fa-solid fa-plus-circle"></i> Đăng ký thêm thiết bị mới vào kho</h3>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px,1fr));gap:15px;align-items:flex-end">
                        <?php
                            $total_racks = count($racks);
                            $next_rack_index = $total_racks + 1;
                            $next_rack_code = "RACK_" . $next_rack_index;
                            $next_rack_name = "Rack số " . $next_rack_index;
                        ?>
                        <div class="aitr-form-group" style="margin:0">
                            <label>Mã code của Rack:</label>
                            <input type="text" name="rack_code" class="aitr-form-control" value="<?php echo esc_attr($next_rack_code); ?>" readonly style="background: rgba(255,255,255,0.05); color: #94a3b8;">
                        </div>
                        <div class="aitr-form-group" style="margin:0">
                            <label>Tên định danh thiết bị:</label>
                            <input type="text" name="rack_name" class="aitr-form-control" value="<?php echo esc_attr($next_rack_name); ?>" readonly style="background: rgba(255,255,255,0.05); color: #94a3b8;">
                        </div>

                        <div class="aitr-form-group" style="margin:0">
                            <label>Số lượng khoang:</label>
                            <select name="slot_count" class="aitr-form-control">
                                <option value="2">2 khoang</option>
                                <option value="3" selected>3 khoang (Mặc định)</option>
                                <option value="4">4 khoang</option>
                                <option value="6">6 khoang</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="aitr-btn" style="width:100%;height:40px;justify-content:center"><i class="fa-solid fa-save"></i> Đăng ký Rack</button>
                        </div>
                    </div>
                </form>

                <!-- Inventory List -->
                <table class="aitr-table">
                    <thead>
                        <tr>
                            <th>Mã thiết bị (Code)</th>
                            <th>Tên thiết bị</th>
                            <th>Trạng thái</th>
                            <th>Blynk connection</th>
                            <th>Hành động nhanh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($racks)): ?>
                            <tr><td colspan="5" style="text-align:center;color:#64748b">Chưa có thiết bị nào được đăng ký.</td></tr>
                        <?php else:
                            foreach ($racks as $rk):
                                $status = (string) ($rk['status'] ?? 'inventory');
                                $connectivity = (string) ($rk['connectivity_status'] ?? 'unknown');
                                $is_inventory = $status === 'inventory';
                        ?>
                                <tr>
                                    <td><code><?php echo esc_html((string) ($rk['rack_code'] ?? '')); ?></code></td>
                                    <td><strong><?php echo esc_html((string) ($rk['rack_name'] ?? '')); ?></strong></td>
                                    <td>
                                        <span style="padding:3px 8px;border-radius:4px;font-size:11px;background:<?php echo $is_inventory ? '#1e293b' : 'rgba(16,185,129,0.1)'; ?>;color:<?php echo $is_inventory ? '#94a3b8' : '#34d399'; ?>">
                                            <?php echo esc_html($status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $bclass = 'aitr-badge-warn';
                                            if ($connectivity === 'online') { $bclass = 'aitr-badge-online'; }
                                            elseif ($connectivity === 'offline') { $bclass = 'aitr-badge-offline'; }
                                        ?>
                                        <span class="aitr-badge <?php echo $bclass; ?>"><?php echo esc_html($connectivity); ?></span>
                                    </td>
                                    <td>
                                        <div style="display:flex;gap:8px">
                                            <!-- Settings -->
                                            <button type="button" class="aitr-btn aitr-btn-secondary" style="padding:4px 8px;font-size:11px" onclick="openAitrRackSettingsModal(<?php echo (int) ($rk['id'] ?? 0); ?>)">
                                                <i class="fa-solid fa-gear"></i> Cài đặt
                                            </button>

                                            <!-- Check Blynk connection -->
                                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                                <?php wp_nonce_field('aitrongcay_check_rack_blynk'); ?>
                                                <input type="hidden" name="action" value="aitrongcay_check_rack_blynk">
                                                <input type="hidden" name="rack_id" value="<?php echo (int) ($rk['id'] ?? 0); ?>">
                                                <button type="submit" class="aitr-btn aitr-btn-secondary" style="padding:4px 8px;font-size:11px"><i class="fa-solid fa-wifi"></i> Check Blynk</button>
                                            </form>

                                            <?php if ($is_inventory): ?>
                                                <!-- Delete Rack -->
                                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Bạn có thực sự muốn xóa thiết bị này khỏi kho?')">
                                                    <?php wp_nonce_field('aitrongcay_delete_inventory_rack'); ?>
                                                    <input type="hidden" name="action" value="aitrongcay_delete_inventory_rack">
                                                    <input type="hidden" name="rack_id" value="<?php echo (int) ($rk['id'] ?? 0); ?>">
                                                    <button type="submit" class="aitr-btn aitr-btn-danger" style="padding:4px 8px;font-size:11px"><i class="fa-solid fa-trash"></i> Xóa</button>
                                                </form>
                                            <?php else: ?>
                                                <!-- Revoke Rack -->
                                                <form method="post" style="display:inline" onsubmit="event.preventDefault(); aitrConfirmRevoke(this);">
                                                    <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                                    <input type="hidden" name="beta_action" value="release_rack">
                                                    <input type="hidden" name="rack_id" value="<?php echo (int) ($rk['id'] ?? 0); ?>">
                                                    <input type="hidden" name="garden_key" value="<?php echo esc_attr((string) ($rk['garden_key'] ?? '')); ?>">
                                                    <button type="submit" class="aitr-btn aitr-btn-danger" style="padding:4px 8px;font-size:11px"><i class="fa-solid fa-arrow-right-left"></i> Thu hồi</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>

        <!-- Tab 4: System Settings Panel -->
        <?php elseif ($active_tab === 'settings'): ?>
            <div class="aitr-panel">
                <div class="aitr-panel-title">
                    <span><i class="fa-solid fa-sliders"></i> Cấu hình Hệ thống, AI & Thanh toán (VietQR)</span>
                </div>

                <form method="post">
                    <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                    <input type="hidden" name="beta_action" value="save_system_settings">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
                        <!-- Left: Gemini API & AI settings -->
                        <div>
                            <h3 style="color:#10b981;font-size:15px;margin-top:0"><i class="fa-solid fa-brain"></i> Trí tuệ nhân tạo AI & API Keys</h3>
                            <div class="aitr-form-group">
                                <label>Google Gemini API Key:</label>
                                <input type="text" name="gemini_api_key" class="aitr-form-control" autocomplete="off" placeholder="<?php echo $gemini_masked !== '' ? '✓ Key đã được cài đặt: ' . esc_attr($gemini_masked) : 'Nhập mã API Key (AIza...)'; ?>">
                                <span style="font-size:11px;color:#64748b">Lấy key miễn phí tại <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#10b981">Google AI Studio</a>. Dùng phím tắt Ctrl+V để dán.</span>
                            </div>

                            <hr style="border:0;border-bottom:1px solid #334155;margin:20px 0">

                            <h3 style="color:#10b981;font-size:15px"><i class="fa-solid fa-robot"></i> Trợ lý AI Cindy (Remote Agent)</h3>
                            <div class="aitr-form-group">
                                <label><input type="checkbox" name="ai_enabled" value="1" <?php checked(! empty($ai_config['enabled'])); ?>> Cho phép gọi Cindy AI Agent bên ngoài</label>
                            </div>
                            <div class="aitr-form-group">
                                <label>Chế độ kết nối:</label>
                                <select name="ai_mode" class="aitr-form-control" id="aitr-ai-mode-select" onchange="aitrToggleAiFields()">
                                    <option value="adapter-ready" <?php selected($ai_config['mode'] ?? '', 'adapter-ready'); ?>>Adapter-ready (Phân tích nội bộ)</option>
                                    <option value="remote-http" <?php selected($ai_config['mode'] ?? '', 'remote-http'); ?>>Remote HTTP API</option>
                                    <option value="openai-chat" <?php selected($ai_config['mode'] ?? '', 'openai-chat'); ?>>OpenClaw Chat API</option>
                                    <option value="gemini-chat" <?php selected($ai_config['mode'] ?? '', 'gemini-chat'); ?>>Google Gemini (dùng API Key ở trên)</option>
                                </select>
                            </div>
                            <div class="aitr-form-group" id="aitr-model-row">
                                <label>Model AI:</label>
                                <select name="ai_model" class="aitr-form-control">
                                    <optgroup label="OpenClaw Models">
                                        <option value="openclaw" <?php selected($ai_config['model'] ?? 'openclaw', 'openclaw'); ?>>openclaw (Mặc định OpenClaw)</option>
                                    </optgroup>
                                    <optgroup label="Google Gemini Models">
                                        <option value="gemini-1.5-flash" <?php selected($ai_config['model'] ?? '', 'gemini-1.5-flash'); ?>>gemini-1.5-flash (Nhanh, miễn phí)</option>
                                        <option value="gemini-1.5-pro" <?php selected($ai_config['model'] ?? '', 'gemini-1.5-pro'); ?>>gemini-1.5-pro (Mạnh hơn)</option>
                                        <option value="gemini-2.0-flash" <?php selected($ai_config['model'] ?? '', 'gemini-2.0-flash'); ?>>gemini-2.0-flash (Thế hệ mới)</option>
                                        <option value="gemini-2.5-flash" <?php selected($ai_config['model'] ?? '', 'gemini-2.5-flash'); ?>>gemini-2.5-flash (Mới nhất)</option>
                                    </optgroup>
                                </select>
                                <span style="font-size:11px;color:#64748b;margin-top:4px;display:block">Nếu chọn Gemini, hãy chọn model Gemini. Nếu chọn OpenClaw, hãy chọn openclaw.</span>
                            </div>
                            <div class="aitr-form-group" id="aitr-endpoint-row">
                                <label>Endpoint URL (chỉ cần cho Remote HTTP / OpenClaw):</label>
                                <input type="url" name="ai_endpoint_url" class="aitr-form-control" value="<?php echo esc_attr((string) ($ai_config['endpoint_url'] ?? '')); ?>" placeholder="https://...">
                            </div>
                            <div class="aitr-form-group" id="aitr-token-row">
                                <label>Bearer Token bảo mật (chỉ cần cho Remote HTTP / OpenClaw):</label>
                                <input type="text" name="ai_bearer_token" class="aitr-form-control" value="<?php echo esc_attr((string) ($ai_config['bearer_token'] ?? '')); ?>" placeholder="Nếu cần">
                            </div>
                            <script>
                            function aitrToggleAiFields() {
                                const mode = document.getElementById('aitr-ai-mode-select').value;
                                const isGemini = mode === 'gemini-chat';
                                const isRemote = mode === 'remote-http' || mode === 'openai-chat';
                                document.getElementById('aitr-endpoint-row').style.display = isGemini ? 'none' : '';
                                document.getElementById('aitr-token-row').style.display = isGemini ? 'none' : '';
                            }
                            aitrToggleAiFields();
                            </script>
                        </div>

                        <!-- Right: VietQR payment settings -->
                        <div>
                            <h3 style="color:#10b981;font-size:15px;margin-top:0"><i class="fa-solid fa-credit-card"></i> Cấu hình VietQR & Thanh toán</h3>
                            <div class="aitr-form-group">
                                <label>Mã ngân hàng (BIN ngân hàng):</label>
                                <input type="text" name="bank_bin" class="aitr-form-control" value="<?php echo esc_attr((string) $payment_rules['bank_bin']); ?>" placeholder="Ví dụ: 970415 (Vietinbank)">
                            </div>
                            <div class="aitr-form-group">
                                <label>Số tài khoản thụ hưởng:</label>
                                <input type="text" name="bank_acc" class="aitr-form-control" value="<?php echo esc_attr((string) $payment_rules['bank_acc']); ?>" placeholder="Ví dụ: 10987182">
                            </div>
                            <div class="aitr-form-group">
                                <label>Tên tài khoản (Không dấu):</label>
                                <input type="text" name="bank_name" class="aitr-form-control" value="<?php echo esc_attr((string) $payment_rules['bank_name']); ?>" placeholder="Ví dụ: NGUYEN VAN A">
                            </div>
                            <div class="aitr-form-group">
                                <label>Template tạo QR:</label>
                                <select name="qr_template" class="aitr-form-control">
                                    <option value="qr_only" <?php selected($payment_rules['qr_template'], 'qr_only'); ?>>Chỉ QR Code</option>
                                    <option value="compact" <?php selected($payment_rules['qr_template'], 'compact'); ?>>Gọn (Compact)</option>
                                    <option value="print" <?php selected($payment_rules['qr_template'], 'print'); ?>>Bản in (Print layout)</option>
                                </select>
                            </div>
                            <div class="aitr-form-group">
                                <label>Đơn giá thuê vườn (VNĐ / Ngày):</label>
                                <input type="number" name="price_per_day" class="aitr-form-control" value="<?php echo esc_attr((string) $payment_rules['price_per_day']); ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:20px;border-top:1px solid #334155;padding-top:20px;text-align:right">
                        <button type="submit" class="aitr-btn"><i class="fa-solid fa-save"></i> Lưu toàn bộ cấu hình</button>
                    </div>
                </form>
            </div>

        <!-- Tab 5: Hydration Panel -->
        <?php elseif ($active_tab === 'hydration'): ?>
            <div class="aitr-panel">
                <div class="aitr-panel-title">
                    <span><i class="fa-solid fa-droplet"></i> Quản lý & Cấu hình Hydration (Bơm, Dinh dưỡng, Mực nước)</span>
                </div>
                
                <table class="aitr-table">
                    <thead>
                        <tr>
                            <th>Mã thiết bị (Rack)</th>
                            <th>Tên hiển thị</th>
                            <th>Khu vườn gán</th>
                            <th>Kết nối Blynk</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($racks)): ?>
                            <tr><td colspan="5" style="text-align:center;color:#64748b">Chưa có thiết bị nào.</td></tr>
                        <?php else:
                            foreach ($racks as $rk):
                                $connectivity = (string) ($rk['connectivity_status'] ?? 'unknown');
                                $garden = (string) ($rk['garden_key'] ?? '');
                                $is_assigned = $garden !== '';
                        ?>
                                <tr>
                                    <td><code><?php echo esc_html((string) ($rk['rack_code'] ?? '')); ?></code></td>
                                    <td><strong><?php echo esc_html((string) ($rk['rack_name'] ?? '')); ?></strong></td>
                                    <td><?php echo $is_assigned ? '<code>' . esc_html(substr($garden, 0, 15) . '...') . '</code>' : '<span style="color:#64748b">Chưa gán</span>'; ?></td>
                                    <td>
                                        <?php
                                            $bclass = 'aitr-badge-warn';
                                            if ($connectivity === 'online') { $bclass = 'aitr-badge-online'; }
                                            elseif ($connectivity === 'offline') { $bclass = 'aitr-badge-offline'; }
                                        ?>
                                        <span class="aitr-badge <?php echo $bclass; ?>"><?php echo esc_html($connectivity); ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="aitr-btn" style="padding:6px 12px;font-size:12px;background:#3b82f6" onclick="openAitrHydrationModal(<?php echo (int) ($rk['id'] ?? 0); ?>)">
                                            <i class="fa-solid fa-faucet-drip"></i> Cấu hình Hydration
                                        </button>
                                    </td>
                                </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <!-- Hydration Settings Modal (Hidden) -->
    <div id="aitr-hydration-settings-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:99999; backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div style="background:#1e293b; border:1px solid #334155; border-radius:12px; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); display:flex; flex-direction:column;">
            <div style="padding:16px 20px; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#1e293b; z-index:10;">
                <h3 style="margin:0; font-size:16px; color:#f8fafc;"><i class="fa-solid fa-droplet" style="color:#3b82f6"></i> Cấu hình Hydration: <span id="aitr-hydration-modal-rack-name"></span></h3>
                <button type="button" onclick="document.getElementById('aitr-hydration-settings-overlay').style.display='none'" style="background:transparent; border:none; color:#94a3b8; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="padding:20px;">
                <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                <input type="hidden" name="beta_action" value="save_hydration_settings">
                <input type="hidden" name="action" value="aitrongcay_beta_action">
                <input type="hidden" name="rack_id" id="aitr-hydration-modal-rack-id" value="">
                <input type="hidden" name="return_tab" value="<?php echo esc_attr($active_tab); ?>">

                <div id="aitr-hydration-modal-trays-container" style="display:flex; flex-direction:column; gap:16px;">
                    <!-- Hydration Trays will be injected here -->
                </div>

                <div style="margin-top:24px; text-align:right;">
                    <button type="button" class="aitr-btn" style="background:#475569; margin-right:8px;" onclick="document.getElementById('aitr-hydration-settings-overlay').style.display='none'">Hủy</button>
                    <button type="submit" class="aitr-btn" style="background:#3b82f6"><i class="fa-solid fa-save"></i> Lưu cấu hình</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirm Modal (Hidden) -->
    <div id="aitr-confirm-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:99999; backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div style="background:#1e293b; border:1px solid #334155; border-radius:12px; width:100%; max-width:400px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); display:flex; flex-direction:column; overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center; background:#1e293b;">
                <h3 style="margin:0; font-size:16px; color:#f8fafc;"><i class="fa-solid fa-triangle-exclamation" style="color:#eab308"></i> Xác nhận thu hồi</h3>
                <button type="button" onclick="aitrConfirmCancel()" style="background:transparent; border:none; color:#94a3b8; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <div style="padding:24px 20px; color:#cbd5e1; font-size:14px; line-height:1.5;">
                Bạn có chắc chắn muốn thu hồi Rack này về kho không? Thao tác này sẽ gỡ bỏ Rack khỏi vườn hiện tại.
            </div>
            <div style="padding:16px 20px; border-top:1px solid #334155; display:flex; justify-content:flex-end; gap:12px; background:#0f172a;">
                <button type="button" class="aitr-btn" style="background:#475569;" onclick="aitrConfirmCancel()">Hủy bỏ</button>
                <button type="button" class="aitr-btn aitr-btn-danger" onclick="aitrConfirmAccept()">Đồng ý thu hồi</button>
            </div>
        </div>
    </div>

    <!-- Rack Settings Modal (Hidden) -->
    <div id="aitr-rack-settings-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:99999; backdrop-filter:blur(4px); align-items:center; justify-content:center;">
        <div style="background:#1e293b; border:1px solid #334155; border-radius:12px; width:100%; max-width:600px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); display:flex; flex-direction:column;">
            <div style="padding:16px 20px; border-bottom:1px solid #334155; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#1e293b; z-index:10;">
                <h3 style="margin:0; font-size:16px; color:#f8fafc;"><i class="fa-solid fa-gear"></i> Cài đặt Rack: <span id="aitr-modal-rack-name"></span></h3>
                <button type="button" onclick="document.getElementById('aitr-rack-settings-overlay').style.display='none'" style="background:transparent; border:none; color:#94a3b8; font-size:20px; cursor:pointer;">&times;</button>
            </div>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="padding:20px;">
                <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                <input type="hidden" name="beta_action" value="save_single_rack_settings">
                <input type="hidden" name="action" value="aitrongcay_beta_action">
                <input type="hidden" name="rack_id" id="aitr-modal-rack-id" value="">
                <input type="hidden" name="return_tab" value="<?php echo esc_attr($active_tab); ?>">

                <div class="aitr-form-group" style="margin-bottom:20px">
                    <label>Blynk Auth Token (Chung cho cả Rack):</label>
                    <input type="text" name="rack_blynk_token" id="aitr-modal-blynk-token" class="aitr-form-control" placeholder="Auth Token chung...">
                </div>

                <div id="aitr-modal-trays-container" style="display:flex; flex-direction:column; gap:16px;">
                    <!-- Trays will be injected here -->
                </div>

                <div style="margin-top:24px; text-align:right;">
                    <button type="button" class="aitr-btn" style="background:#475569; margin-right:8px;" onclick="document.getElementById('aitr-rack-settings-overlay').style.display='none'">Hủy</button>
                    <button type="submit" class="aitr-btn"><i class="fa-solid fa-save"></i> Lưu cài đặt</button>
                </div>
            </form>
        </div>
    </div>

    <?php
    // Prepare data for JS
    $catalog_options_html = '<option value="">Cây chưa xác định</option>';
    if (function_exists('aitrongcay_onboarding_plant_catalog')) {
        $catalog = aitrongcay_onboarding_plant_catalog();
        foreach ($catalog as $plant) {
            $pid = (int) ($plant['id'] ?? 0);
            $pname = esc_attr((string) ($plant['public_name'] ?? ''));
            if ($pid > 0 && $pname !== '') {
                $catalog_options_html .= sprintf('<option value="%d|%s">%s</option>', $pid, $pname, $pname);
            }
        }
    }
    
    $rack_settings_data = [];
    foreach ($racks as $r) {
        $r_id = (int) $r['id'];
        $slots = function_exists('aitrongcay_get_rack_slots_by_rack_id') ? aitrongcay_get_rack_slots_by_rack_id($r_id) : [];
        if (empty($slots) && function_exists('aitrongcay_garden_rack_slots_table')) {
             $slot_count = max(1, (int) ($r['slot_count'] ?? 3));
             for ($i=1; $i<=$slot_count; $i++) {
                 $slots[] = [
                     'id' => 0, 'slot_index' => $i, 'slot_name' => 'Khoang '.$i, 'plant_name' => '', 'camera_stream_url' => ''
                 ];
             }
        }
        $hydration_config = get_option("aitrongcay_hydration_config_{$r_id}", []);
        $rack_settings_data[$r_id] = [
            'rack_name' => $r['rack_name'] ?: $r['rack_code'],
            'blynk_auth_token' => $r['blynk_auth_token'] ?? '',
            'slots' => $slots,
            'hydration_config' => is_array($hydration_config) ? $hydration_config : []
        ];
    }
    ?>
    <script>
    let aitrFormToSubmit = null;
    function aitrConfirmRevoke(form) {
        aitrFormToSubmit = form;
        document.getElementById('aitr-confirm-overlay').style.display = 'flex';
    }
    function aitrConfirmAccept() {
        if (aitrFormToSubmit) {
            aitrFormToSubmit.removeAttribute('onsubmit');
            let tempBtn = document.createElement('button');
            tempBtn.type = 'submit';
            tempBtn.style.display = 'none';
            aitrFormToSubmit.appendChild(tempBtn);
            tempBtn.click();
        }
        document.getElementById('aitr-confirm-overlay').style.display = 'none';
    }
    function aitrConfirmCancel() {
        aitrFormToSubmit = null;
        document.getElementById('aitr-confirm-overlay').style.display = 'none';
    }

    const aitrRackData = <?php echo wp_json_encode($rack_settings_data); ?>;
    const aitrCatalogOptionsHtml = <?php echo wp_json_encode($catalog_options_html); ?>;
    
    function openAitrHydrationModal(rackId) {
        if (!aitrRackData[rackId]) return;
        const data = aitrRackData[rackId];
        
        document.getElementById('aitr-hydration-modal-rack-name').innerText = data.rack_name;
        document.getElementById('aitr-hydration-modal-rack-id').value = rackId;
        
        const container = document.getElementById('aitr-hydration-modal-trays-container');
        container.innerHTML = '';
        
        // Render 2 tanks (Main and Optional Sub) per Rack
        const tankNames = ['Bồn Nước Chính (Tank 1)', 'Bồn Nước Phụ (Tank 2)'];
        for (let i = 1; i <= 2; i++) {
            // Hydration config from data if available, otherwise default
            const hydro = data.hydration_config ? data.hydration_config[i] : null;
            const vWater = hydro ? (hydro.vpin_water_level || '') : '';
            const vPh = hydro ? (hydro.vpin_ph || '') : '';
            const vTds = hydro ? (hydro.vpin_tds || '') : '';
            const vCool = hydro ? (hydro.vpin_cool_pump || '') : '';
            
            const html = `
            <div style="background:#0f172a; border:1px solid #334155; border-radius:8px; padding:16px;">
                <h4 style="margin:0 0 12px 0; color:#3b82f6; font-size:14px;"><i class="fa-solid fa-droplet"></i> Hydration - ${tankNames[i-1]}</h4>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(120px, 1fr)); gap:12px; align-items:end;">
                    <div>
                        <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;">Cảm biến Mực nước</label>
                        <input type="text" name="tanks[${i}][vpin_water_level]" placeholder="Ví dụ: V12" value="${vWater}" class="aitr-form-control" style="padding:6px; font-size:13px; text-align:center;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;">Cảm biến độ pH</label>
                        <input type="text" name="tanks[${i}][vpin_ph]" placeholder="Ví dụ: V13" value="${vPh}" class="aitr-form-control" style="padding:6px; font-size:13px; text-align:center;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;">Cảm biến dinh dưỡng (TDS)</label>
                        <input type="text" name="tanks[${i}][vpin_tds]" placeholder="Ví dụ: V14" value="${vTds}" class="aitr-form-control" style="padding:6px; font-size:13px; text-align:center;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; color:#94a3b8; margin-bottom:4px;">Bơm làm mát/tuần hoàn</label>
                        <input type="text" name="tanks[${i}][vpin_cool_pump]" placeholder="Ví dụ: V15" value="${vCool}" class="aitr-form-control" style="padding:6px; font-size:13px; text-align:center;">
                    </div>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
        }
        
        const overlay = document.getElementById('aitr-hydration-settings-overlay');
        overlay.style.display = 'flex';
    }

    function openAitrRackSettingsModal(rackId) {
        if (!aitrRackData[rackId]) return;
        const data = aitrRackData[rackId];
        
        document.getElementById('aitr-modal-rack-name').innerText = data.rack_name;
        document.getElementById('aitr-modal-rack-id').value = rackId;
        document.getElementById('aitr-modal-blynk-token').value = data.blynk_auth_token || '';
        
        const container = document.getElementById('aitr-modal-trays-container');
        container.innerHTML = '';
        
        if (data.slots && data.slots.length > 0) {
            data.slots.forEach(slot => {
                const sId = slot.id || ('new_'+slot.slot_index);
                const sName = slot.slot_name || ('Khoang ' + slot.slot_index);
                const sUrl = slot.camera_stream_url || '';
                
                const html = `
                <div style="background:#0f172a; border:1px solid #334155; border-radius:8px; padding:16px;">
                    <h4 style="margin:0 0 12px 0; color:#10b981; font-size:14px;"><i class="fa-solid fa-leaf"></i> ${sName}</h4>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                        <div>
                            <label style="display:block; font-size:12px; color:#94a3b8; margin-bottom:4px;">Tên khoang</label>
                            <input type="text" name="trays[${sId}][name]" value="${sName}" class="aitr-form-control" style="padding:8px; font-size:13px;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; color:#94a3b8; margin-bottom:4px;">Giống cây trồng</label>
                            <select name="trays[${sId}][plant_name]" class="aitr-form-control" style="padding:8px; font-size:13px;" data-selected="${slot.plant_name || ''}">
                                ${aitrCatalogOptionsHtml}
                            </select>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr; gap:12px;">
                        <div>
                            <label style="display:block; font-size:12px; color:#94a3b8; margin-bottom:4px;">Link Camera Stream (m3u8/mjpeg)</label>
                            <input type="url" name="trays[${sId}][webcam_url]" value="${sUrl}" class="aitr-form-control" style="padding:8px; font-size:13px;" placeholder="https://...">
                        </div>
                    </div>
                    <div style="margin-top:12px; display:grid; grid-template-columns:repeat(auto-fit, minmax(70px, 1fr)); gap:8px;">
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">🌡 Temp</label>
                            <input type="text" name="trays[${sId}][vpin_temp]" value="V0" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">💧 Hum</label>
                            <input type="text" name="trays[${sId}][vpin_hum]" value="V1" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">🌿 Đất</label>
                            <input type="text" name="trays[${sId}][vpin_soil]" value="V2" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">⚗️ pH</label>
                            <input type="text" name="trays[${sId}][vpin_ph]" value="V3" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">🌱 EC</label>
                            <input type="text" name="trays[${sId}][vpin_ec]" value="V4" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">💡 Đèn</label>
                            <input type="text" name="trays[${sId}][vpin_light]" value="V6" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                        <div>
                            <label style="display:block; font-size:11px; color:#64748b; margin-bottom:2px;">🔄 Bơm</label>
                            <input type="text" name="trays[${sId}][vpin_pump]" value="V5" class="aitr-form-control" style="padding:4px; font-size:12px; text-align:center;">
                        </div>
                    </div>
                </div>`;
                container.insertAdjacentHTML('beforeend', html);
            });
            
            setTimeout(() => {
                document.querySelectorAll('#aitr-modal-trays-container select[name^="trays["]').forEach(sel => {
                    const selectedText = sel.getAttribute('data-selected');
                    if (selectedText) {
                        for (let i = 0; i < sel.options.length; i++) {
                            if (sel.options[i].value.endsWith('|' + selectedText) || sel.options[i].text === selectedText || sel.options[i].value === selectedText) {
                                sel.selectedIndex = i;
                                break;
                            }
                        }
                    }
                });
            }, 50);
        } else {
            container.innerHTML = '<div style="color:#64748b; font-size:13px; text-align:center;">Chưa có thông tin khoang.</div>';
        }
        
        const overlay = document.getElementById('aitr-rack-settings-overlay');
        overlay.style.display = 'flex';
    }
    </script>
    <?php
}
