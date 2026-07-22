<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_blynk_shared_token_markers(): array
{
    return ['__shared__', 'shared', 'shared-token', 'same-as-shared'];
}

function aitrongcay_blynk_is_shared_token_marker(string $token): bool
{
    return in_array(strtolower(trim($token)), aitrongcay_blynk_shared_token_markers(), true);
}

function aitrongcay_rack_max_compartments(): int
{
    return 6;
}

function aitrongcay_rack_max_slots(): int
{
    return aitrongcay_rack_max_compartments() * 2;
}

function aitrongcay_normalize_rack_slot_count(int $slot_count): int
{
    return max(2, min(aitrongcay_rack_max_slots(), $slot_count));
}

function aitrongcay_slot_to_compartment(int $slot_index): array
{
    $slot_index = max(1, min(aitrongcay_rack_max_slots(), $slot_index));
    $compartment_index = (int) ceil($slot_index / 2);
    $tray_position = $slot_index % 2 === 1 ? 1 : 2;

    return [
        'slot_index' => $slot_index,
        'compartment_index' => $compartment_index,
        'tray_position' => $tray_position,
        'tray_suffix' => $tray_position === 1 ? 'A' : 'B',
        'slot_code' => sprintf('C%02d-T%d', $compartment_index, $tray_position),
        'slot_label' => 'Khoang ' . $compartment_index . ' · Khoang ' . ($tray_position === 1 ? 'A' : 'B'),
        'short_label' => 'K' . $compartment_index . ' · ' . ($tray_position === 1 ? 'A' : 'B'),
        'light_device' => 'light' . $slot_index,
        'inlet_device' => 'inlet' . $compartment_index,
        'drain_device' => 'drain' . $compartment_index,
    ];
}

function aitrongcay_compartment_count_from_slots(int $slot_count): int
{
    return max(1, (int) ceil(aitrongcay_normalize_rack_slot_count($slot_count) / 2));
}

function aitrongcay_rack_compartment_summary(int $slot_count): string
{
    $slot_count = aitrongcay_normalize_rack_slot_count($slot_count);
    return aitrongcay_compartment_count_from_slots($slot_count) . ' khoang · ' . $slot_count . ' khoang';
}

function aitrongcay_blynk_device_schema(): array
{
    $vpins = [
        'temp' => ['label' => 'VPin nhiệt độ', 'default' => 'V0'],
        'hum' => ['label' => 'VPin độ ẩm không khí', 'default' => 'V1'],
        'soil' => ['label' => 'VPin độ ẩm đất', 'default' => 'V11'],
        'pump' => ['label' => 'VPin bơm', 'default' => 'V2'],
    ];
    $devices = [
        'pump' => ['label' => 'Khóa thiết bị bơm', 'default' => 'pump'],
    ];
    $pots = [];
    $pot_tokens = [];
    for ($compartment = 1; $compartment <= aitrongcay_rack_max_compartments(); $compartment++) {
        $vpins['inlet' . $compartment] = ['label' => 'VPin van cấp nước khoang ' . $compartment, 'default' => ''];
        $vpins['drain' . $compartment] = ['label' => 'VPin van thoát nước khoang ' . $compartment, 'default' => ''];
        $devices['inlet' . $compartment] = ['label' => 'Khóa van cấp nước khoang ' . $compartment, 'default' => 'inlet' . $compartment];
        $devices['drain' . $compartment] = ['label' => 'Khóa van thoát nước khoang ' . $compartment, 'default' => 'drain' . $compartment];
    }
    for ($i = 1; $i <= aitrongcay_rack_max_slots(); $i++) {
        $slot_meta = aitrongcay_slot_to_compartment($i);
        $light_key = 'light' . $i;
        $pot_code = sprintf('P-%03d', $i);
        $vpins[$light_key] = ['label' => 'VPin đèn ' . $i . ' (' . $slot_meta['slot_label'] . ')', 'default' => 'V' . (4 + $i)];
        $devices[$light_key] = ['label' => 'Khóa thiết bị đèn ' . $i . ' (' . $slot_meta['slot_label'] . ')', 'default' => $light_key];
        $pots[$pot_code] = ['label' => $slot_meta['slot_label'] . ' / Pot ' . $pot_code, 'default' => $light_key];
        $pot_tokens[$pot_code] = ['label' => 'Token ' . $slot_meta['slot_label'] . ' / ' . $pot_code, 'default' => '__shared__'];
    }

    return [
        'base' => ['label' => 'Blynk base URL', 'type' => 'url', 'default' => 'https://blynk.cloud/external/api'],
        'token' => ['label' => 'Blynk token chung (sensor + pump fallback)', 'type' => 'text', 'default' => ''],
        'vpins' => $vpins,
        'devices' => $devices,
        'pots' => $pots,
        'pot_tokens' => $pot_tokens,
    ];
}

function aitrongcay_blynk_config_option_name(): string
{
    return 'aitrongcay_garden_device_configs';
}

function aitrongcay_blynk_default_config(): array
{
    $schema = aitrongcay_blynk_device_schema();

    $config = [
        'base' => (string) ($schema['base']['default'] ?? ''),
        'token' => (string) ($schema['token']['default'] ?? ''),
        'vpins' => [],
        'devices' => [],
        'pots' => [],
        'pot_tokens' => [],
    ];

    foreach ((array) ($schema['vpins'] ?? []) as $key => $field) {
        $config['vpins'][$key] = (string) ($field['default'] ?? '');
    }
    foreach ((array) ($schema['devices'] ?? []) as $key => $field) {
        $config['devices'][$key] = (string) ($field['default'] ?? '');
    }
    foreach ((array) ($schema['pots'] ?? []) as $key => $field) {
        $config['pots'][$key] = (string) ($field['default'] ?? '');
    }
    foreach ((array) ($schema['pot_tokens'] ?? []) as $key => $field) {
        $config['pot_tokens'][$key] = trim((string) ($field['default'] ?? ''));
    }

    return $config;
}

function aitrongcay_blynk_builtin_configs(): array
{
    $default = aitrongcay_blynk_default_config();
    $primary = $default;
    $primary['token'] = '8lriQRJG5nyKCEUyBM6fiUPKdaAtx9iX';
    $primary['vpins'] = [
        'temp' => 'V0',
        'hum' => 'V1',
        'soil' => 'V11',
        'pump' => 'V2',
        'light1' => 'V5',
        'light2' => 'V6',
        'light3' => 'V7',
        'light4' => 'V8',
    ];
    $primary['pots'] = [
        'P-001' => 'light1',
        'P-002' => 'light2',
        'P-003' => 'light3',
        'P-004' => 'light4',
    ];
    $primary['pot_tokens'] = [
        'P-001' => '__shared__',
        'P-002' => '__shared__',
        'P-003' => '__shared__',
        'P-004' => '__shared__',
    ];

    return [
        'primary-live' => $primary,
    ];
}

function aitrongcay_seed_real_blynk_configs(): void
{
    $saved = aitrongcay_get_saved_blynk_configs();
    $builtins = aitrongcay_blynk_builtin_configs();
    $seed_aliases = [
        'primary-live' => 'primary-live',
    ];

    $dirty = false;
    foreach ($seed_aliases as $target_key => $source_key) {
        if (! isset($builtins[$source_key])) {
            continue;
        }

        $existing = isset($saved[$target_key]) && is_array($saved[$target_key])
            ? aitrongcay_normalize_blynk_config($saved[$target_key])
            : [];
        $token = trim((string) ($existing['token'] ?? ''));
        $needs_seed = $existing === [] || $token === '' || $token === 'shared-token-placeholder';
        if (! $needs_seed) {
            continue;
        }

        $saved[$target_key] = aitrongcay_normalize_blynk_config(array_replace_recursive(
            aitrongcay_blynk_default_config(),
            $builtins[$source_key],
            $existing
        ));
        $saved[$target_key]['token'] = (string) ($builtins[$source_key]['token'] ?? '');
        $dirty = true;
    }

    if ($dirty) {
        aitrongcay_save_blynk_configs($saved);
    }
}
add_action('init', 'aitrongcay_seed_real_blynk_configs', 35);

function aitrongcay_normalize_blynk_config(array $config): array
{
    $default = aitrongcay_blynk_default_config();

    $normalized = [
        'base' => esc_url_raw((string) ($config['base'] ?? $default['base'])),
        'token' => trim((string) ($config['token'] ?? $default['token'])),
        'vpins' => [],
        'devices' => [],
        'pots' => [],
        'pot_tokens' => [],
    ];

    foreach ($default['vpins'] as $key => $value) {
        $normalized['vpins'][$key] = strtoupper(trim((string) (($config['vpins'][$key] ?? $value))));
    }
    foreach ($default['devices'] as $key => $value) {
        $normalized['devices'][$key] = sanitize_key((string) (($config['devices'][$key] ?? $value)));
    }
    foreach ($default['pots'] as $key => $value) {
        $normalized['pots'][$key] = sanitize_key((string) (($config['pots'][$key] ?? $value)));
    }
    foreach ($default['pot_tokens'] as $key => $value) {
        $normalized['pot_tokens'][$key] = trim((string) (($config['pot_tokens'][$key] ?? $value)));
    }

    if ($normalized['base'] === '') {
        $normalized['base'] = $default['base'];
    }

    return $normalized;
}

function aitrongcay_get_saved_blynk_configs(): array
{
    $saved = get_option(aitrongcay_blynk_config_option_name(), []);
    if (! is_array($saved)) {
        return [];
    }

    $normalized = [];
    foreach ($saved as $garden_key => $config) {
        $garden_key = sanitize_text_field((string) $garden_key);
        if ($garden_key === '' || ! is_array($config)) {
            continue;
        }
        $normalized[$garden_key] = aitrongcay_normalize_blynk_config($config);
    }

    return $normalized;
}

function aitrongcay_save_blynk_configs(array $configs): void
{
    $normalized = [];
    foreach ($configs as $garden_key => $config) {
        $garden_key = sanitize_text_field((string) $garden_key);
        if ($garden_key === '' || ! is_array($config)) {
            continue;
        }
        $normalized[$garden_key] = aitrongcay_normalize_blynk_config($config);
    }

    update_option(aitrongcay_blynk_config_option_name(), $normalized, false);
}

function aitrongcay_resolve_blynk_aliases_for_garden(string $garden_key = ''): array
{
    $aliases = [];
    $garden_key = trim($garden_key);
    if ($garden_key !== '') {
        $aliases[] = $garden_key;
    }

    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    if ($owner instanceof WP_User) {
        $aliases[] = strtolower(trim((string) $owner->user_email));
    }

    if (function_exists('aitrongcay_portal_dataset_library')) {
        foreach ((array) aitrongcay_portal_dataset_library() as $dataset_key => $dataset) {
            $matched = false;
            foreach ((array) ($dataset['match_emails'] ?? []) as $email) {
                $email = strtolower(trim((string) $email));
                if ($email !== '' && in_array($email, $aliases, true)) {
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                $aliases[] = (string) $dataset_key;
            }
        }
    }

    return array_values(array_unique(array_filter($aliases)));
}

function aitrongcay_blynk_config(string $garden_key = ''): array
{
    $default = aitrongcay_blynk_default_config();
    $builtins = aitrongcay_blynk_builtin_configs();
    $saved = aitrongcay_get_saved_blynk_configs();

    foreach (aitrongcay_resolve_blynk_aliases_for_garden($garden_key) as $alias) {
        if (isset($saved[$alias]) && is_array($saved[$alias])) {
            return aitrongcay_normalize_blynk_config(array_replace_recursive($default, $saved[$alias]));
        }
        if (isset($builtins[$alias]) && is_array($builtins[$alias])) {
            return aitrongcay_normalize_blynk_config(array_replace_recursive($default, $builtins[$alias]));
        }
    }

    return $default;
}

function aitrongcay_blynk_effective_token(string $garden_key, string $raw_token = ''): string
{
    $config = aitrongcay_blynk_config($garden_key);
    $shared_token = trim((string) ($config['token'] ?? ''));
    $raw_token = trim($raw_token);

    if ($raw_token === '' || aitrongcay_blynk_is_shared_token_marker($raw_token)) {
        return $shared_token;
    }

    return $raw_token;
}

function aitrongcay_blynk_pot_token_for_code(string $garden_key, string $pot_code): string
{
    $config = aitrongcay_blynk_config($garden_key);
    $raw_token = trim((string) (($config['pot_tokens'][$pot_code] ?? '')));
    return aitrongcay_blynk_effective_token($garden_key, $raw_token);
}

function aitrongcay_blynk_pot_token_for_device(string $garden_key, string $device): string
{
    $device = sanitize_key($device);
    if ($device === '' || $device === 'pump') {
        return '';
    }

    $config = aitrongcay_blynk_config($garden_key);
    foreach ((array) ($config['pots'] ?? []) as $pot_code => $mapped_device) {
        if (sanitize_key((string) $mapped_device) !== $device) {
            continue;
        }

        return aitrongcay_blynk_pot_token_for_code($garden_key, (string) $pot_code);
    }

    return '';
}

function aitrongcay_blynk_remote_get(array $query_args, string $base, string $endpoint = '/get')
{
    $url = add_query_arg($query_args, untrailingslashit($base) . $endpoint);
    return wp_remote_get($url, ['timeout' => 3]); // Giảm timeout từ 10s xuống 3s để tránh chết PHP-FPM
}

function aitrongcay_blynk_read_values(string $token, array $vpins, string $base): array
{
    $token = trim($token);
    if ($token === '' || $vpins === []) {
        return [];
    }

    $request_args = ['token' => $token];
    foreach ($vpins as $vpin) {
        $vpin = strtoupper(trim((string) $vpin));
        if ($vpin === '') {
            continue;
        }
        $request_args[$vpin] = '';
    }

    $response = aitrongcay_blynk_remote_get($request_args, $base, '/get');
    if (is_wp_error($response)) {
        return [];
    }

    $body = (string) wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if (is_array($data)) {
        if (isset($data['error'])) {
            return [];
        }
        return $data;
    }

    if (count($vpins) === 1) {
        $single_vpin = strtoupper(trim((string) ($vpins[0] ?? '')));
        if ($single_vpin !== '' && $body !== '') {
            if (is_numeric($body)) {
                return [$single_vpin => 0 + $body];
            }
            if ($body === 'true' || $body === 'false') {
                return [$single_vpin => $body === 'true'];
            }
            return [$single_vpin => trim($body, '"')];
        }
    }

    return [];
}

function aitrongcay_portal_apply_garden_device_mapping(array $pots, string $garden_key = ''): array
{
    $config = aitrongcay_blynk_config($garden_key);
    $pot_map = (array) ($config['pots'] ?? []);
    $device_map = (array) ($config['devices'] ?? []);
    $pump_device = (string) ($device_map['pump'] ?? 'pump');

    foreach ($pots as $index => $pot) {
        if (! is_array($pot)) {
            continue;
        }

        $pot_code = (string) ($pot['code'] ?? '');
        $mapped_device = (string) ($pot_map[$pot_code] ?? ($pot['light_device'] ?? ''));
        if ($mapped_device !== '') {
            $pots[$index]['light_device'] = $mapped_device;
        }

        $raw_pot_token = trim((string) ($config['pot_tokens'][$pot_code] ?? ''));
        $effective_pot_token = aitrongcay_blynk_effective_token($garden_key, $raw_pot_token);
        $pots[$index]['blynk_token'] = $effective_pot_token;
        $pots[$index]['blynk_token_raw'] = $raw_pot_token;
        $pots[$index]['uses_shared_blynk_token'] = $raw_pot_token === '' || aitrongcay_blynk_is_shared_token_marker($raw_pot_token);
        $pots[$index]['has_device'] = $effective_pot_token !== '';

        if (! empty($pots[$index]['pump'])) {
            $pots[$index]['pump_device'] = $pump_device;
        }
    }

    return $pots;
}

function aitrongcay_known_gardens_for_device_admin(): array
{
    $gardens = [];

    if (function_exists('aitrongcay_portal_dataset_library')) {
        foreach ((array) aitrongcay_portal_dataset_library() as $dataset_key => $dataset) {
            $email = strtolower(trim((string) (($dataset['match_emails'][0] ?? ''))));
            $user = $email !== '' ? get_user_by('email', $email) : null;
            $garden_key = $user instanceof WP_User && function_exists('aitrongcay_primary_garden_key_for_user')
                ? aitrongcay_primary_garden_key_for_user($user)
                : (string) $dataset_key;
            $profile = $user instanceof WP_User && function_exists('aitrongcay_portal_profile_for_user')
                ? aitrongcay_portal_profile_for_user($user)
                : null;
            $gardens[$garden_key] = [
                'garden_key' => $garden_key,
                'label' => (string) (($profile['garden_name'] ?? $dataset_key)),
                'owner_email' => $email,
                'dataset_key' => (string) $dataset_key,
            ];
        }
    }

    global $wpdb;
    if (function_exists('aitrongcay_garden_members_table') && function_exists('aitrongcay_gardens_table')) {
        $members_table = aitrongcay_garden_members_table();
        $gardens_table = aitrongcay_gardens_table();
        
        // Tối ưu hóa: Dùng 1 câu truy vấn JOIN duy nhất thay vì lặp qua từng khu vườn
        $sql = "
            SELECT 
                m.garden_key,
                m.user_id as owner_id,
                g.garden_name,
                u.user_email,
                u.display_name
            FROM {$members_table} m
            LEFT JOIN {$gardens_table} g ON m.garden_key = g.garden_key
            LEFT JOIN {$wpdb->users} u ON m.user_id = u.ID
            WHERE m.role = 'owner' AND m.status = 'active'
        ";
        $rows = $wpdb->get_results($sql, ARRAY_A) ?: [];
        
        foreach ($rows as $row) {
            $garden_key = sanitize_text_field((string) ($row['garden_key'] ?? ''));
            if ($garden_key === '') {
                continue;
            }
            if (! isset($gardens[$garden_key])) {
                $garden_name = trim((string) ($row['garden_name'] ?? ''));
                if ($garden_name === '') {
                    $display_name = trim((string) ($row['display_name'] ?? 'Khách hàng'));
                    $garden_name = 'Vườn của ' . $display_name;
                }
                $gardens[$garden_key] = [
                    'garden_key' => $garden_key,
                    'label' => $garden_name,
                    'owner_email' => strtolower(trim((string) ($row['user_email'] ?? ''))),
                    'dataset_key' => '',
                ];
            }
        }
    }

    foreach (array_keys(aitrongcay_get_saved_blynk_configs()) as $saved_key) {
        if (! isset($gardens[$saved_key])) {
            $gardens[$saved_key] = [
                'garden_key' => $saved_key,
                'label' => $saved_key,
                'owner_email' => '',
                'dataset_key' => '',
            ];
        }
    }

    return array_values($gardens);
}

function aitrongcay_device_mapping_admin_menu(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Mapping thiết bị khu vườn',
        'Mapping thiết bị khu vườn',
        'edit_theme_options',
        'aitrongcay-garden-device-mapping',
        'aitrongcay_render_device_mapping_admin_page'
    );

    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Hồ sơ & vật tư khu vườn',
        'Hồ sơ & vật tư khu vườn',
        'edit_theme_options',
        'aitrongcay-garden-profile-tools',
        'aitrongcay_render_garden_profile_tools_admin_page'
    );

    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Kho rack',
        'Kho rack',
        'edit_theme_options',
        'aitrongcay-rack-inventory',
        'aitrongcay_render_rack_inventory_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_device_mapping_admin_menu', 100);

function aitrongcay_handle_device_mapping_admin_save(): void
{
    if (! is_admin() || ! current_user_can('edit_theme_options')) {
        return;
    }

    if (($_POST['action'] ?? '') !== 'aitrongcay_save_device_mapping') {
        return;
    }

    check_admin_referer('aitrongcay_save_device_mapping');

    $submitted = $_POST['garden_configs'] ?? [];
    $submitted = is_array($submitted) ? $submitted : [];

    $configs = [];
    foreach ($submitted as $garden_key => $config) {
        $garden_key = sanitize_text_field((string) $garden_key);
        if ($garden_key === '' || ! is_array($config)) {
            continue;
        }
        $configs[$garden_key] = aitrongcay_normalize_blynk_config($config);
    }

    aitrongcay_save_blynk_configs($configs);
    if (function_exists('aitrongcay_sync_rack_from_blynk_config')) {
        foreach ($configs as $garden_key => $config) {
            aitrongcay_sync_rack_from_blynk_config($garden_key, $config, [
                'status' => 'inventory',
                'owner_user_id' => 0,
                'notes' => 'Đồng bộ từ trang mapping thiết bị để đưa rack vào kho sẵn sàng cấp phát.',
            ]);
        }
    }

    $redirect = add_query_arg([
        'page' => 'aitrongcay-garden-device-mapping',
        'updated' => 'true',
    ], admin_url('admin.php'));
    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_init', 'aitrongcay_handle_device_mapping_admin_save');

function aitrongcay_handle_garden_profile_tools_admin_save(): void
{
    if (! is_admin() || ! current_user_can('edit_theme_options')) {
        return;
    }
    if (($_POST['action'] ?? '') !== 'aitrongcay_save_garden_profile_tools') {
        return;
    }

    check_admin_referer('aitrongcay_save_garden_profile_tools');

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        return;
    }

    $record = function_exists('aitrongcay_get_garden_record') ? (aitrongcay_get_garden_record($garden_key) ?: []) : [];
    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $owner_user_id = (int) ($record['owner_user_id'] ?? ($owner instanceof WP_User ? $owner->ID : 0));
    if ($owner_user_id <= 0 && $owner instanceof WP_User) {
        $owner_user_id = (int) $owner->ID;
    }
    if ($owner_user_id <= 0) {
        $owner_user_id = get_current_user_id();
    }

    if (function_exists('aitrongcay_upsert_garden_record')) {
        aitrongcay_upsert_garden_record($garden_key, $owner_user_id, [
            'garden_code' => sanitize_text_field((string) wp_unslash($_POST['garden_code'] ?? '')),
            'garden_name' => sanitize_text_field((string) wp_unslash($_POST['garden_name'] ?? '')),
            'summary' => sanitize_textarea_field((string) wp_unslash($_POST['summary'] ?? '')),
            'status_line' => sanitize_text_field((string) wp_unslash($_POST['status_line'] ?? '')),
        ]);
    }

    $submitted_tools = $_POST['tools'] ?? [];
    $submitted_tools = is_array($submitted_tools) ? $submitted_tools : [];
    $tools = [];
    foreach ($submitted_tools as $tool) {
        if (! is_array($tool)) {
            continue;
        }
        $name = sanitize_text_field((string) wp_unslash($tool['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $tools[] = [
            'tool_key' => sanitize_key((string) wp_unslash($tool['tool_key'] ?? $name)),
            'name' => $name,
            'type' => sanitize_text_field((string) wp_unslash($tool['type'] ?? '')),
            'description' => sanitize_textarea_field((string) wp_unslash($tool['description'] ?? '')),
            'owned' => max(0, (int) ($tool['owned'] ?? 0)),
            'qty' => max(0, (int) ($tool['qty'] ?? 0)),
            'image' => sanitize_text_field((string) wp_unslash($tool['image'] ?? '')),
        ];
    }
    if (function_exists('aitrongcay_replace_garden_tools')) {
        aitrongcay_replace_garden_tools($garden_key, $tools);
    }

    $submitted_pots = $_POST['pots'] ?? [];
    $submitted_pots = is_array($submitted_pots) ? $submitted_pots : [];
    $pots = [];
    foreach ($submitted_pots as $pot) {
        if (! is_array($pot)) {
            continue;
        }
        $pot_code = sanitize_text_field((string) wp_unslash($pot['pot_code'] ?? ''));
        $pot_name = sanitize_text_field((string) wp_unslash($pot['pot_name'] ?? ''));
        if ($pot_code === '' || $pot_name === '') {
            continue;
        }
        $pots[] = [
            'pot_code' => $pot_code,
            'pot_name' => $pot_name,
            'status' => sanitize_text_field((string) wp_unslash($pot['status'] ?? '')),
            'status_summary' => sanitize_textarea_field((string) wp_unslash($pot['status_summary'] ?? '')),
            'ph' => sanitize_text_field((string) wp_unslash($pot['ph'] ?? '')),
            'temperature' => sanitize_text_field((string) wp_unslash($pot['temperature'] ?? '')),
            'humidity' => sanitize_text_field((string) wp_unslash($pot['humidity'] ?? '')),
            'light_label' => sanitize_text_field((string) wp_unslash($pot['light_label'] ?? '')),
            'light_device' => sanitize_key((string) wp_unslash($pot['light_device'] ?? '')),
            'pump_label' => sanitize_text_field((string) wp_unslash($pot['pump_label'] ?? '')),
            'irrigation' => sanitize_text_field((string) wp_unslash($pot['irrigation'] ?? '')),
            'image_url' => esc_url_raw((string) wp_unslash($pot['image_url'] ?? '')),
            'video_url' => esc_url_raw((string) wp_unslash($pot['video_url'] ?? '')),
            'ai_note' => sanitize_textarea_field((string) wp_unslash($pot['ai_note'] ?? '')),
            'harvest_eta' => sanitize_text_field((string) wp_unslash($pot['harvest_eta'] ?? '')),
        ];
    }
    if (function_exists('aitrongcay_replace_garden_pots')) {
        aitrongcay_replace_garden_pots($garden_key, $pots);
    }

    $redirect = add_query_arg([
        'page' => 'aitrongcay-garden-profile-tools',
        'garden_key' => rawurlencode($garden_key),
        'updated' => 'true',
    ], admin_url('admin.php'));
    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_init', 'aitrongcay_handle_garden_profile_tools_admin_save');

function aitrongcay_handle_rack_inventory_admin_actions(): void
{
    if (! is_admin() || ! current_user_can('edit_theme_options')) {
        return;
    }

    $action = sanitize_key((string) ($_POST['action'] ?? ''));
    if (! in_array($action, ['aitrongcay_add_inventory_rack', 'aitrongcay_delete_inventory_rack', 'aitrongcay_release_inventory_rack', 'aitrongcay_update_rack_token', 'aitrongcay_check_rack_blynk', 'aitrongcay_update_rack_slot_cameras'], true)) {
        return;
    }

    check_admin_referer($action);
    $is_beta = false;
    $referer = wp_get_referer();
    if ($referer && strpos($referer, 'page=aitrongcay-unified-admin-beta') !== false) {
        $is_beta = true;
        $redirect = add_query_arg(['page' => 'aitrongcay-unified-admin-beta', 'tab' => 'racks'], admin_url('admin.php'));
    } else {
        $redirect = add_query_arg(['page' => 'aitrongcay-rack-inventory'], admin_url('admin.php'));
    }

    if ($action === 'aitrongcay_add_inventory_rack') {
        $result = function_exists('aitrongcay_create_inventory_rack') ? aitrongcay_create_inventory_rack([
            'rack_code' => sanitize_text_field((string) wp_unslash($_POST['rack_code'] ?? '')),
            'rack_name' => sanitize_text_field((string) wp_unslash($_POST['rack_name'] ?? '')),
            'slot_count' => max(2, min(12, (int) ($_POST['slot_count'] ?? 2))),
            'blynk_auth_token' => sanitize_text_field((string) wp_unslash($_POST['blynk_auth_token'] ?? '')),
            'notes' => sanitize_textarea_field((string) wp_unslash($_POST['notes'] ?? '')),
        ]) : ['error' => 'Thiếu hàm tạo rack.'];
        
        if ($is_beta) {
            $redirect = add_query_arg($result && empty($result['error']) ? ['beta_success' => rawurlencode('Đã thêm rack vào kho.')] : ['beta_error' => rawurlencode((string) ($result['error'] ?? 'Không tạo được rack.'))], $redirect);
        } else {
            $redirect = add_query_arg($result && empty($result['error']) ? ['rack_saved' => '1'] : ['rack_error' => rawurlencode((string) ($result['error'] ?? 'Không tạo được rack.'))], $redirect);
        }
        
        wp_safe_redirect($redirect);
        exit;
    }

    $rack_id = absint($_POST['rack_id'] ?? 0);
    if ($rack_id <= 0) {
        wp_safe_redirect(add_query_arg(['rack_error' => rawurlencode('Thiếu rack_id.')], $redirect));
        exit;
    }

    if ($action === 'aitrongcay_update_rack_token') {
        $token = sanitize_text_field((string) wp_unslash($_POST['blynk_auth_token'] ?? ''));
        $slot_count = max(2, min(12, (int) ($_POST['slot_count'] ?? 2)));
        if (! function_exists('aitrongcay_update_rack_hardware')) {
            wp_safe_redirect(add_query_arg(['rack_error' => rawurlencode('Thiếu hàm cập nhật cấu hình rack.')], $redirect));
            exit;
        }
        $result = aitrongcay_update_rack_hardware($rack_id, [
            'blynk_auth_token' => $token,
            'slot_count' => $slot_count,
        ]);
        wp_safe_redirect(add_query_arg(empty($result['error']) ? ['rack_saved' => '1'] : ['rack_error' => rawurlencode((string) $result['error'])], $redirect));
        exit;
    }

    if ($action === 'aitrongcay_update_rack_slot_cameras') {
        $raw_slot_cameras = wp_unslash($_POST['slot_cameras'] ?? []);
        $slot_cameras = [];
        if (is_array($raw_slot_cameras)) {
            foreach ($raw_slot_cameras as $slot_index => $row) {
                $slot_index = (int) $slot_index;
                if ($slot_index <= 0 || ! is_array($row)) {
                    continue;
                }
                $slot_cameras[$slot_index] = [
                    'camera_label' => sanitize_text_field((string) ($row['camera_label'] ?? '')),
                    'camera_stream_url' => esc_url_raw((string) ($row['camera_stream_url'] ?? '')),
                ];
            }
        }
        $test_slot_index = max(0, (int) ($_POST['test_slot_index'] ?? 0));
        if ($test_slot_index > 0) {
            $slot_row = is_array($slot_cameras[$test_slot_index] ?? null) ? $slot_cameras[$test_slot_index] : [];
            if (! function_exists('aitrongcay_probe_camera_stream_url')) {
                wp_safe_redirect(add_query_arg(['rack_error' => rawurlencode('Thiếu hàm test camera.')], $redirect));
                exit;
            }
            $report = aitrongcay_probe_camera_stream_url((string) ($slot_row['camera_stream_url'] ?? ''));
            if (function_exists('aitrongcay_log_rack_inventory_event')) {
                $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
                if ($rack) {
                    $slot_text = 'Test camera khoang ' . $test_slot_index;
                    if (! empty($slot_row['camera_label'])) {
                        $slot_text .= ' (' . (string) $slot_row['camera_label'] . ')';
                    }
                    $slot_text .= ': ' . (string) ($report['summary'] ?? 'Không có kết quả');
                    aitrongcay_log_rack_inventory_event($rack_id, 'test_camera', (string) ($rack['status'] ?? ''), (string) ($rack['status'] ?? ''), 0, $slot_text, get_current_user_id());
                }
            }
            set_transient('aitrongcay_rack_camera_test_' . get_current_user_id(), [
                'rack_id' => $rack_id,
                'slot_index' => $test_slot_index,
                'camera_label' => (string) ($slot_row['camera_label'] ?? ''),
                'stream_url' => (string) ($slot_row['camera_stream_url'] ?? ''),
                'report' => $report,
            ], 300);
            wp_safe_redirect(add_query_arg(['rack_camera_tested' => $rack_id, 'slot_index' => $test_slot_index], $redirect));
            exit;
        }
        if (! function_exists('aitrongcay_update_rack_slot_cameras')) {
            wp_safe_redirect(add_query_arg(['rack_error' => rawurlencode('Thiếu hàm cập nhật camera theo khoang.')], $redirect));
            exit;
        }
        $result = aitrongcay_update_rack_slot_cameras($rack_id, $slot_cameras);
        wp_safe_redirect(add_query_arg(empty($result['error']) ? ['rack_saved' => '1'] : ['rack_error' => rawurlencode((string) $result['error'])], $redirect));
        exit;
    }

    if ($action === 'aitrongcay_check_rack_blynk') {
        $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
        $report = ($rack && function_exists('aitrongcay_blynk_probe_rack')) ? aitrongcay_blynk_probe_rack($rack) : ['summary' => 'Không kiểm tra được rack.', 'ok' => [], 'errors' => ['Không tìm thấy rack.'], 'controls' => []];
        if ($rack && function_exists('aitrongcay_upsert_rack_record')) {
            aitrongcay_upsert_rack_record((string) ($rack['garden_key'] ?? ''), [
                'id' => (int) ($rack['id'] ?? 0),
                'rack_code' => (string) ($rack['rack_code'] ?? ''),
                'rack_name' => (string) ($rack['rack_name'] ?? ''),
                'owner_user_id' => (int) ($rack['owner_user_id'] ?? 0),
                'status' => (string) ($rack['status'] ?? 'inventory'),
                'slot_count' => (int) ($rack['slot_count'] ?? 0),
                'controller_type' => (string) ($rack['controller_type'] ?? 'blynk'),
                'controller_label' => (string) ($rack['controller_label'] ?? ''),
                'blynk_auth_token' => (string) ($rack['blynk_auth_token'] ?? ''),
                'blynk_template_id' => (string) ($rack['blynk_template_id'] ?? ''),
                'blynk_template_name' => (string) ($rack['blynk_template_name'] ?? ''),
                'blynk_email' => (string) ($rack['blynk_email'] ?? ''),
                'connectivity_status' => (string) ($report['connectivity_status'] ?? 'unknown'),
                'last_seen_at' => $report['last_seen_at'] ?? ($rack['last_seen_at'] ?? null),
                'notes' => (string) ($rack['notes'] ?? ''),
            ]);
        }
        if ($rack && function_exists('aitrongcay_log_rack_inventory_event')) {
            $note_parts = [(string) ($report['summary'] ?? '')];
            if (! empty($report['ok'])) {
                $note_parts[] = 'OK: ' . implode(' | ', array_map('strval', (array) $report['ok']));
            }
            if (! empty($report['errors'])) {
                $note_parts[] = 'Lỗi: ' . implode(' | ', array_map('strval', (array) $report['errors']));
            }
            if (! empty($report['controls'])) {
                $note_parts[] = 'Điều khiển: ' . implode(' | ', array_map('strval', (array) $report['controls']));
            }
            aitrongcay_log_rack_inventory_event(
                (int) ($rack['id'] ?? 0),
                'check_blynk',
                (string) ($rack['status'] ?? ''),
                (string) ($rack['status'] ?? ''),
                0,
                implode("\n", array_filter($note_parts)),
                get_current_user_id()
            );
        }
        set_transient('aitrongcay_rack_check_' . get_current_user_id(), [
            'rack_id' => $rack_id,
            'report' => $report,
        ], 300);
        wp_safe_redirect(add_query_arg(['rack_checked' => $rack_id], $redirect));
        exit;
    }

    if ($action === 'aitrongcay_delete_inventory_rack') {
        $ok = function_exists('aitrongcay_delete_rack') ? aitrongcay_delete_rack($rack_id) : false;
        wp_safe_redirect(add_query_arg($ok ? ['rack_deleted' => '1'] : ['rack_error' => rawurlencode('Không xóa được rack.')], $redirect));
        exit;
    }

    $result = function_exists('aitrongcay_release_rack_to_inventory') ? aitrongcay_release_rack_to_inventory($rack_id) : ['error' => 'Thiếu hàm thu hồi rack.'];
    wp_safe_redirect(add_query_arg(empty($result['error']) ? ['rack_released' => '1'] : ['rack_error' => rawurlencode((string) $result['error'])], $redirect));
    exit;
}
add_action('admin_init', 'aitrongcay_handle_rack_inventory_admin_actions');

function aitrongcay_render_rack_inventory_admin_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die('Không đủ quyền.');
    }

    $racks = function_exists('aitrongcay_list_racks') ? aitrongcay_list_racks() : [];
    $events = function_exists('aitrongcay_get_rack_inventory_events') ? aitrongcay_get_rack_inventory_events(80) : [];
    $rack_defaults = function_exists('aitrongcay_inventory_rack_defaults') ? aitrongcay_inventory_rack_defaults() : ['rack_code' => 'R1', 'rack_name' => 'Rack số 1'];
    $status_filter = sanitize_key((string) ($_GET['rack_status'] ?? ''));
    if ($status_filter !== '') {
        $racks = array_values(array_filter($racks, static fn(array $rack): bool => (string) ($rack['status'] ?? '') === $status_filter));
    }
    $inventory_count = count(array_filter($racks, static fn(array $rack): bool => (string) ($rack['status'] ?? '') === 'inventory'));
    $check_result = get_transient('aitrongcay_rack_check_' . get_current_user_id());
    if ($check_result) {
        delete_transient('aitrongcay_rack_check_' . get_current_user_id());
    }
    $camera_test_result = get_transient('aitrongcay_rack_camera_test_' . get_current_user_id());
    if ($camera_test_result) {
        delete_transient('aitrongcay_rack_camera_test_' . get_current_user_id());
    }
    $camera_notice_style = static function (array $report): string {
        if (! empty($report['ok']) && ! empty($report['looks_like_stream'])) {
            return 'background:#e8f7ee;border-left:4px solid #16a34a;color:#166534;';
        }
        if (! empty($report['ok'])) {
            return 'background:#fff7e6;border-left:4px solid #d97706;color:#92400e;';
        }
        return 'background:#fef2f2;border-left:4px solid #dc2626;color:#991b1b;';
    };
    $camera_card_style = static function (array $slot_item, ?array $camera_test_result): string {
        $base = 'border:1px solid #e5e7eb;border-radius:10px;padding:8px;background:#fff';
        if (! $camera_test_result || (int) ($camera_test_result['slot_index'] ?? 0) !== (int) ($slot_item['slot_index'] ?? 0)) {
            return $base;
        }
        $report = is_array($camera_test_result['report'] ?? null) ? (array) $camera_test_result['report'] : [];
        if (! empty($report['ok']) && ! empty($report['looks_like_stream'])) {
            return $base . ';border-color:#16a34a;background:#f0fdf4;box-shadow:inset 0 0 0 1px rgba(22,163,74,.08)';
        }
        if (! empty($report['ok'])) {
            return $base . ';border-color:#f59e0b;background:#fffbeb;box-shadow:inset 0 0 0 1px rgba(245,158,11,.08)';
        }
        return $base . ';border-color:#ef4444;background:#fef2f2;box-shadow:inset 0 0 0 1px rgba(239,68,68,.08)';
    };
    ?>
    <div class="wrap">
        <style>
            .aitr-rack-admin{max-width:1400px}
            .aitr-rack-topbar{display:grid;grid-template-columns:minmax(280px,380px) minmax(0,1fr);gap:20px;align-items:start;margin:18px 0 22px}
            .aitr-rack-box{background:#fff;border:1px solid #dcdcde;border-radius:16px;padding:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
            .aitr-rack-stats{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
            .aitr-rack-stat{min-width:140px;padding:12px 14px;border:1px solid #e5e7eb;border-radius:14px;background:#f9fafb}
            .aitr-rack-stat strong{display:block;font-size:20px;line-height:1.1}
            .aitr-rack-filter{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
            .aitr-rack-list{display:grid;gap:16px}
            .aitr-rack-card{background:#fff;border:1px solid #dcdcde;border-radius:18px;padding:18px;box-shadow:0 1px 2px rgba(16,24,40,.04)}
            .aitr-rack-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;margin-bottom:14px}
            .aitr-rack-title{display:flex;flex-direction:column;gap:6px}
            .aitr-rack-title h3{margin:0;font-size:18px}
            .aitr-rack-code{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:#4b5563}
            .aitr-rack-badges{display:flex;gap:8px;flex-wrap:wrap}
            .aitr-rack-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
            .aitr-span-3{grid-column:span 3}.aitr-span-4{grid-column:span 4}.aitr-span-5{grid-column:span 5}.aitr-span-6{grid-column:span 6}.aitr-span-7{grid-column:span 7}.aitr-span-8{grid-column:span 8}.aitr-span-12{grid-column:span 12}
            .aitr-panel{border:1px solid #e5e7eb;border-radius:14px;padding:14px;background:#fcfcfd;min-width:0}
            .aitr-panel h4{margin:0 0 10px;font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280}
            .aitr-meta{display:grid;gap:8px}
            .aitr-meta-row{display:grid;grid-template-columns:120px minmax(0,1fr);gap:10px;font-size:13px}
            .aitr-meta-row code,.aitr-inline-code{word-break:break-all}
            .aitr-slot-summary{white-space:pre-wrap;font-size:13px;line-height:1.55;color:#374151}
            .aitr-camera-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}
            .aitr-camera-actions,.aitr-action-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
            .aitr-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
            .aitr-form-grid .aitr-full{grid-column:1 / -1}
            .aitr-log-wrap{overflow:auto}
            .aitr-log-table td{vertical-align:top}
            .aitr-check-lines{white-space:pre-line;line-height:1.55}
            @media (max-width: 1180px){
                .aitr-rack-topbar,.aitr-rack-grid{grid-template-columns:1fr}
                .aitr-span-3,.aitr-span-4,.aitr-span-5,.aitr-span-6,.aitr-span-7,.aitr-span-8,.aitr-span-12{grid-column:auto}
            }
            @media (max-width: 782px){
                .aitr-form-grid,.aitr-meta-row{grid-template-columns:1fr}
                .aitr-rack-card,.aitr-rack-box{padding:14px}
            }
        </style>
        <div class="aitr-rack-admin">
            <h1>Kho rack</h1>
            <p>Quản lý rack theo model mới: 1 rack tối đa 6 khoang, mỗi khoang tối đa 2 khoang, mỗi khoang có 2 đèn + 1 van cấp + 1 van thoát, toàn rack có 1 bơm và bộ cảm biến chung.</p>

            <?php if (! empty($_GET['rack_saved'])) : ?><div class="notice notice-success"><p>Đã thêm rack vào kho.</p></div><?php endif; ?>
            <?php if (! empty($_GET['rack_deleted'])) : ?><div class="notice notice-success"><p>Đã xóa rack.</p></div><?php endif; ?>
            <?php if (! empty($_GET['rack_released'])) : ?><div class="notice notice-success"><p>Đã thu hồi rack về kho.</p></div><?php endif; ?>
            <?php if (! empty($_GET['rack_error'])) : ?><div class="notice notice-error"><p><?php echo esc_html((string) wp_unslash($_GET['rack_error'])); ?></p></div><?php endif; ?>
            <?php if (is_array($check_result) && ! empty($check_result['report'])) : $report = (array) $check_result['report']; ?>
                <div class="notice notice-info" style="padding:12px 16px">
                    <p><strong>Check Blynk rack #<?php echo esc_html((string) ($check_result['rack_id'] ?? '')); ?>:</strong> <?php echo esc_html((string) ($report['summary'] ?? '')); ?></p>
                    <?php if (! empty($report['ok'])) : ?><p class="aitr-check-lines"><strong>OK:</strong>
<?php echo esc_html(implode("\n", array_map('strval', (array) $report['ok']))); ?></p><?php endif; ?>
                    <?php if (! empty($report['errors'])) : ?><p class="aitr-check-lines"><strong>Lỗi / chưa kết nối:</strong>
<?php echo esc_html(implode("\n", array_map('strval', (array) $report['errors']))); ?></p><?php endif; ?>
                    <?php if (! empty($report['controls'])) : ?><p class="aitr-check-lines"><strong>Điều khiển:</strong>
<?php echo esc_html(implode("\n", array_map('strval', (array) $report['controls']))); ?></p><?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (is_array($camera_test_result) && ! empty($camera_test_result['report'])) : $report = (array) $camera_test_result['report']; ?>
                <div class="notice" style="padding:12px 16px;<?php echo esc_attr($camera_notice_style($report)); ?>">
                    <?php $camera_slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment((int) ($camera_test_result['slot_index'] ?? 1)) : ['slot_label' => 'Khoang ' . (int) ($camera_test_result['slot_index'] ?? 0)]; ?>
                    <p><strong>Test camera rack #<?php echo esc_html((string) ($camera_test_result['rack_id'] ?? '')); ?>, <?php echo esc_html((string) ($camera_slot_meta['slot_label'] ?? 'Khoang')); ?><?php if (! empty($camera_test_result['camera_label'])) : ?> (<?php echo esc_html((string) $camera_test_result['camera_label']); ?>)<?php endif; ?>:</strong> <?php echo esc_html((string) ($report['summary'] ?? '')); ?></p>
                    <?php if (! empty($camera_test_result['stream_url'])) : ?><p><code><?php echo esc_html((string) $camera_test_result['stream_url']); ?></code></p><?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="aitr-rack-topbar">
                <div class="aitr-rack-box">
                    <h2 style="margin-top:0">Thêm rack vào kho</h2>
                    <form method="post">
                        <?php wp_nonce_field('aitrongcay_add_inventory_rack'); ?>
                        <input type="hidden" name="action" value="aitrongcay_add_inventory_rack">
                        <div class="aitr-form-grid">
                            <div><label for="rack_code"><strong>Mã rack</strong></label><input class="regular-text" style="width:100%" type="text" name="rack_code" id="rack_code" value="<?php echo esc_attr((string) ($rack_defaults['rack_code'] ?? 'R1')); ?>" required></div>
                            <div><label for="rack_name"><strong>Tên rack</strong></label><input class="regular-text" style="width:100%" type="text" name="rack_name" id="rack_name" value="<?php echo esc_attr((string) ($rack_defaults['rack_name'] ?? 'Rack số 1')); ?>"></div>
                            <div><label for="slot_count"><strong>Tổng số khoang</strong></label><input class="small-text" type="number" min="2" max="12" step="2" name="slot_count" id="slot_count" value="4" required><p class="description" style="margin:4px 0 0">2 khoang = 1 khoang, tối đa 6 khoang.</p></div>
                            <div><label for="blynk_auth_token"><strong>Auth token Blynk</strong></label><input class="regular-text" style="width:100%" type="text" name="blynk_auth_token" id="blynk_auth_token" placeholder="Nhập auth token"></div>
                            <div class="aitr-full"><label for="rack_notes"><strong>Ghi chú</strong></label><textarea class="large-text" rows="3" name="notes" id="rack_notes" placeholder="Ví dụ: bộ mới nhập kho, test đủ đèn"></textarea></div>
                        </div>
                        <?php submit_button('Thêm rack', 'primary', '', false, ['style' => 'margin-top:12px']); ?>
                    </form>
                </div>

                <div class="aitr-rack-box">
                    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap">
                        <div>
                            <h2 style="margin:0">Tổng quan nhanh</h2>
                            <div class="aitr-rack-stats">
                                <div class="aitr-rack-stat"><span>Trong kho</span><strong><?php echo esc_html((string) $inventory_count); ?></strong></div>
                                <div class="aitr-rack-stat"><span>Tổng rack</span><strong><?php echo esc_html((string) count($racks)); ?></strong></div>
                                <div class="aitr-rack-stat"><span>Đang lọc</span><strong><?php echo esc_html($status_filter !== '' ? $status_filter : 'tất cả'); ?></strong></div>
                            </div>
                        </div>
                        <form method="get" class="aitr-rack-filter">
                            <input type="hidden" name="page" value="aitrongcay-rack-inventory">
                            <select name="rack_status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="inventory" <?php selected($status_filter, 'inventory'); ?>>inventory</option>
                                <option value="assigned" <?php selected($status_filter, 'assigned'); ?>>assigned</option>
                                <option value="draft" <?php selected($status_filter, 'draft'); ?>>draft</option>
                            </select>
                            <button type="submit" class="button">Lọc</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="aitr-rack-list">
                <?php if (! $racks) : ?>
                    <div class="aitr-rack-box"><p style="margin:0">Chưa có rack nào.</p></div>
                <?php else : ?>
                    <?php foreach ($racks as $rack) : ?>
                        <?php
                        $rack_id = (int) ($rack['id'] ?? 0);
                        $latest_check = function_exists('aitrongcay_get_latest_rack_event') ? aitrongcay_get_latest_rack_event($rack_id, 'check_blynk') : null;
                        $active_assignment = function_exists('aitrongcay_get_active_rack_assignment') ? aitrongcay_get_active_rack_assignment($rack_id) : null;
                        $rack_slots = function_exists('aitrongcay_get_rack_slots_by_rack_id') ? aitrongcay_get_rack_slots_by_rack_id($rack_id) : [];
                        $slot_summary = function_exists('aitrongcay_format_rack_slot_summary') ? aitrongcay_format_rack_slot_summary($rack_slots) : '';
                        $connectivity_status = trim((string) ($rack['connectivity_status'] ?? 'unknown'));
                        $holder = function_exists('aitrongcay_describe_rack_holder') ? aitrongcay_describe_rack_holder($rack) : '';
                        ?>
                        <div class="aitr-rack-card">
                            <div class="aitr-rack-head">
                                <div class="aitr-rack-title">
                                    <h3><?php echo esc_html((string) ($rack['rack_name'] ?? '')); ?></h3>
                                    <div class="aitr-rack-code"><?php echo esc_html((string) ($rack['rack_code'] ?? '')); ?> · ID <?php echo esc_html((string) $rack_id); ?></div>
                                </div>
                                <div class="aitr-rack-badges">
                                    <span style="<?php echo esc_attr(function_exists('aitrongcay_status_badge_style') ? aitrongcay_status_badge_style((string) ($rack['status'] ?? '')) : ''); ?>"><?php echo esc_html((string) ($rack['status'] ?? '')); ?></span>
                                    <span style="<?php echo esc_attr(function_exists('aitrongcay_status_badge_style') ? aitrongcay_status_badge_style($connectivity_status === 'online' ? 'inventory' : ($connectivity_status === 'degraded' ? 'draft' : ($connectivity_status === 'offline' || $connectivity_status === 'missing_token' ? 'assigned' : '')) ) : ''); ?>"><?php echo esc_html($connectivity_status !== '' ? $connectivity_status : 'unknown'); ?></span>
                                </div>
                            </div>

                            <div class="aitr-rack-grid">
                                <div class="aitr-panel aitr-span-4">
                                    <h4>Thông tin rack</h4>
                                    <div class="aitr-meta">
                                        <div class="aitr-meta-row"><strong>Cấu hình rack</strong><span><?php echo esc_html(function_exists('aitrongcay_rack_compartment_summary') ? aitrongcay_rack_compartment_summary((int) ($rack['slot_count'] ?? 0)) : (string) ($rack['slot_count'] ?? '0')); ?></span></div>
                                        <div class="aitr-meta-row"><strong>Garden key</strong><code class="aitr-inline-code"><?php echo esc_html((string) ($rack['garden_key'] ?? '')); ?></code></div>
                                        <div class="aitr-meta-row"><strong>Người giữ</strong><span><?php echo esc_html($holder); ?></span></div>
                                        <?php if (! empty($rack['last_seen_at'])) : ?><div class="aitr-meta-row"><strong>Last seen</strong><span><?php echo esc_html((string) $rack['last_seen_at']); ?></span></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="aitr-panel aitr-span-4">
                                    <h4>Assignment & check</h4>
                                    <div class="aitr-meta">
                                        <div class="aitr-meta-row"><strong>Lần cấp gần nhất</strong><span><?php echo $active_assignment ? esc_html((string) ($active_assignment['assigned_at'] ?? '')) : 'Chưa cấp'; ?></span></div>
                                        <div class="aitr-meta-row"><strong>Garden active</strong><span><?php echo $active_assignment ? esc_html((string) ($active_assignment['garden_key'] ?? '')) : '—'; ?></span></div>
                                        <div class="aitr-meta-row"><strong>Check gần nhất</strong><span><?php echo $latest_check ? esc_html((string) ($latest_check['created_at'] ?? '')) : 'Chưa có'; ?></span></div>
                                        <?php if ($latest_check && ! empty($latest_check['notes'])) : ?><div class="aitr-meta-row"><strong>Ghi chú check</strong><span class="aitr-check-lines"><?php echo esc_html(str_replace([' | ', '|'], "\n", (string) ($latest_check['notes'] ?? ''))); ?></span></div><?php endif; ?>
                                    </div>
                                </div>

                                <div class="aitr-panel aitr-span-4">
                                    <h4>Token & cấu hình rack</h4>
                                    <form method="post" style="display:grid;gap:10px">
                                        <?php wp_nonce_field('aitrongcay_update_rack_token'); ?>
                                        <input type="hidden" name="action" value="aitrongcay_update_rack_token">
                                        <input type="hidden" name="rack_id" value="<?php echo esc_attr((string) $rack_id); ?>">
                                        <input type="text" name="blynk_auth_token" value="<?php echo esc_attr((string) ($rack['blynk_auth_token'] ?? '')); ?>" class="regular-text" style="width:100%" placeholder="Auth token Blynk">
                                        <div class="aitr-action-row">
                                            <label style="display:flex;align-items:center;gap:8px"><span>Tổng số khoang</span><input type="number" min="2" max="12" step="2" name="slot_count" value="<?php echo esc_attr((string) ($rack['slot_count'] ?? 2)); ?>" class="small-text"></label>
                                            <button type="submit" class="button">Lưu</button>
                                        </div>
                                        <div style="font-size:12px;color:#6b7280">Mỗi 2 khoang tạo thành 1 khoang. Đèn được ghép theo thứ tự 2 đèn cho mỗi khoang.</div>
                                        <?php if ((string) ($rack['status'] ?? '') !== 'inventory') : ?><div style="font-size:12px;color:#6b7280">Được giảm số khoang nếu các khoang bị cắt bớt đang trống.</div><?php endif; ?>
                                    </form>
                                </div>

                                <div class="aitr-panel aitr-span-5">
                                    <h4>Tóm tắt khoang / khoang</h4>
                                    <div class="aitr-slot-summary"><?php echo esc_html($slot_summary !== '' ? $slot_summary : 'Chưa có dữ liệu khoang / khoang.'); ?></div>
                                </div>

                                <div class="aitr-panel aitr-span-7">
                                    <h4>Camera theo khoang trong từng khoang</h4>
                                    <?php if ($rack_slots) : ?>
                                        <form method="post" style="display:grid;gap:10px">
                                            <?php wp_nonce_field('aitrongcay_update_rack_slot_cameras'); ?>
                                            <input type="hidden" name="action" value="aitrongcay_update_rack_slot_cameras">
                                            <input type="hidden" name="rack_id" value="<?php echo esc_attr((string) $rack_id); ?>">
                                            <div class="aitr-camera-grid">
                                                <?php foreach ($rack_slots as $slot_item) : ?>
                                                    <?php $slot_index = (int) ($slot_item['slot_index'] ?? 0); $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($slot_index > 0 ? $slot_index : 1) : ['slot_label' => (string) ($slot_item['slot_name'] ?? ('Khoang ' . $slot_index)), 'inlet_device' => '', 'drain_device' => '']; ?>
                                                    <div style="<?php echo esc_attr($camera_card_style($slot_item, is_array($camera_test_result) ? $camera_test_result : null)); ?>">
                                                        <div style="font-weight:600;margin-bottom:6px"><?php echo esc_html((string) ($slot_item['slot_name'] ?? ($slot_meta['slot_label'] ?? ('Khoang ' . $slot_index)))); ?><?php if (! empty($slot_item['pot_code'])) : ?> · <code><?php echo esc_html((string) $slot_item['pot_code']); ?></code><?php endif; ?></div>
                                                        <div style="font-size:12px;color:#6b7280;margin:-2px 0 6px">Đèn: <?php echo esc_html((string) ($slot_item['control_channel'] ?? ($slot_meta['light_device'] ?? ''))); ?> · Cấp: <?php echo esc_html((string) ($slot_meta['inlet_device'] ?? '')); ?> · Thoát: <?php echo esc_html((string) ($slot_meta['drain_device'] ?? '')); ?></div>
                                                        <input type="text" name="slot_cameras[<?php echo esc_attr((string) $slot_index); ?>][camera_label]" value="<?php echo esc_attr((string) ($slot_item['camera_label'] ?? '')); ?>" class="regular-text" placeholder="Tên camera" style="margin-bottom:6px;width:100%">
                                                        <input type="url" name="slot_cameras[<?php echo esc_attr((string) $slot_index); ?>][camera_stream_url]" value="<?php echo esc_attr((string) ($slot_item['camera_stream_url'] ?? '')); ?>" class="regular-text code" placeholder="https://.../index.m3u8" style="width:100%">
                                                        <div style="margin-top:8px"><button type="submit" class="button button-small" name="test_slot_index" value="<?php echo esc_attr((string) $slot_index); ?>">Test camera</button></div>
                                                        <?php if (is_array($camera_test_result) && (int) ($camera_test_result['slot_index'] ?? 0) === $slot_index && is_array($camera_test_result['report'] ?? null)) : $slot_report = (array) $camera_test_result['report']; ?>
                                                            <div style="margin-top:8px;font-size:12px;font-weight:600;<?php echo esc_attr(! empty($slot_report['ok']) && ! empty($slot_report['looks_like_stream']) ? 'color:#166534;' : (! empty($slot_report['ok']) ? 'color:#92400e;' : 'color:#991b1b;')); ?>"><?php echo esc_html((string) ($slot_report['summary'] ?? '')); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="aitr-camera-actions"><button type="submit" class="button">Lưu camera theo khoang</button></div>
                                        </form>
                                    <?php else : ?>
                                        <p style="margin:0">Chưa có khoang để gắn camera.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="aitr-panel aitr-span-12">
                                    <h4>Thao tác nhanh</h4>
                                    <div class="aitr-action-row">
                                        <form method="post" style="display:inline">
                                            <?php wp_nonce_field('aitrongcay_check_rack_blynk'); ?>
                                            <input type="hidden" name="action" value="aitrongcay_check_rack_blynk">
                                            <input type="hidden" name="rack_id" value="<?php echo esc_attr((string) $rack_id); ?>">
                                            <button type="submit" class="button">Check</button>
                                        </form>
                                        <?php if ((string) ($rack['status'] ?? '') !== 'inventory') : ?>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Thu hồi rack này về kho? Rack đang gắn với user/garden sẽ bị chuyển về trạng thái inventory.');">
                                                <?php wp_nonce_field('aitrongcay_release_inventory_rack'); ?>
                                                <input type="hidden" name="action" value="aitrongcay_release_inventory_rack">
                                                <input type="hidden" name="rack_id" value="<?php echo esc_attr((string) $rack_id); ?>">
                                                <button type="submit" class="button">Thu hồi về kho</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Xóa rack này khỏi hệ thống?');">
                                            <?php wp_nonce_field('aitrongcay_delete_inventory_rack'); ?>
                                            <input type="hidden" name="action" value="aitrongcay_delete_inventory_rack">
                                            <input type="hidden" name="rack_id" value="<?php echo esc_attr((string) $rack_id); ?>">
                                            <button type="submit" class="button button-link-delete">Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="aitr-rack-box" style="margin-top:18px">
                <h2 style="margin-top:0">Log kho rack</h2>
                <div class="aitr-log-wrap">
                    <table class="widefat striped aitr-log-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Event</th>
                                <th>Rack</th>
                                <th>Từ</th>
                                <th>Sang</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (! $events) : ?>
                                <tr><td colspan="6">Chưa có log.</td></tr>
                            <?php else : ?>
                                <?php foreach ($events as $event) : ?>
                                    <tr>
                                        <td><?php echo esc_html((string) ($event['created_at'] ?? '')); ?></td>
                                        <td><?php echo esc_html((string) ($event['event_type'] ?? '')); ?></td>
                                        <td><?php echo esc_html((string) (($event['rack_code'] ?? '') !== '' ? $event['rack_code'] : '#0')); ?></td>
                                        <td><?php echo esc_html((string) ($event['from_status'] ?? '')); ?></td>
                                        <td><?php echo esc_html((string) ($event['to_status'] ?? '')); ?></td>
                                        <td><?php echo esc_html((string) ($event['notes'] ?? '')); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function aitrongcay_render_garden_profile_tools_admin_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die('Không đủ quyền.');
    }

    $gardens = aitrongcay_known_gardens_for_device_admin();
    $selected_key = sanitize_text_field((string) ($_GET['garden_key'] ?? ''));
    if ($selected_key === '' && ! empty($gardens[0]['garden_key'])) {
        $selected_key = (string) $gardens[0]['garden_key'];
    }

    $record = $selected_key !== '' && function_exists('aitrongcay_get_garden_record') ? (aitrongcay_get_garden_record($selected_key) ?: []) : [];
    $tools = $selected_key !== '' && function_exists('aitrongcay_get_db_tools') ? aitrongcay_get_db_tools($selected_key) : [];
    $pots = $selected_key !== '' && function_exists('aitrongcay_get_db_pots') ? aitrongcay_get_db_pots($selected_key) : [];
    $display_name = $selected_key !== '' && function_exists('aitrongcay_get_garden_display_name') ? aitrongcay_get_garden_display_name($selected_key) : '';
    if ($record === []) {
        $record = [
            'garden_code' => '',
            'garden_name' => $display_name,
            'summary' => '',
            'status_line' => '',
        ];
    }
    while (count($tools) < 6) {
        $tools[] = [
            'tool_key' => '',
            'name' => '',
            'type' => '',
            'description' => '',
            'owned' => 0,
            'qty' => 0,
            'image' => '',
        ];
    }
    while (count($pots) < 8) {
        $pots[] = [
            'pot_code' => '',
            'pot_name' => '',
            'status' => '',
            'status_summary' => '',
            'ph' => '',
            'temperature' => '',
            'humidity' => '',
            'light_label' => '',
            'light_device' => '',
            'pump_label' => '',
            'irrigation' => '',
            'image_url' => '',
            'video_url' => '',
            'ai_note' => '',
            'harvest_eta' => '',
        ];
    }
    ?>
    <div class="wrap">
        <h1>Hồ sơ & vật tư khu vườn</h1>
        <p>Trang này giúp nhập nhanh dữ liệu thật cho từng khu vườn: tên vườn, mã vườn, mô tả ngắn, trạng thái hiển thị và danh sách vật tư đang dùng.</p>
        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p>Đã lưu hồ sơ và vật tư khu vườn.</p></div>
        <?php endif; ?>

        <form method="get" style="margin:16px 0 24px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
            <input type="hidden" name="page" value="aitrongcay-garden-profile-tools">
            <label for="garden_key_picker"><strong>Chọn khu vườn</strong></label>
            <select id="garden_key_picker" name="garden_key" style="min-width:360px;">
                <?php foreach ($gardens as $garden) : $garden_key = (string) ($garden['garden_key'] ?? ''); ?>
                    <option value="<?php echo esc_attr($garden_key); ?>" <?php selected($selected_key, $garden_key); ?>>
                        <?php echo esc_html((string) ($garden['label'] ?? $garden_key)); ?> — <?php echo esc_html($garden_key); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button button-secondary" type="submit">Mở khu vườn</button>
        </form>

        <?php if ($selected_key === '') : ?>
            <div class="notice notice-warning"><p>Chưa có khu vườn nào để chỉnh.</p></div>
        <?php else : ?>
            <form method="post">
                <?php wp_nonce_field('aitrongcay_save_garden_profile_tools'); ?>
                <input type="hidden" name="action" value="aitrongcay_save_garden_profile_tools">
                <input type="hidden" name="garden_key" value="<?php echo esc_attr($selected_key); ?>">

                <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:0 0 20px;max-width:1100px;">
                    <h2 style="margin-top:0">Hồ sơ khu vườn</h2>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="garden_code">Mã vườn</label></th>
                                <td><input class="regular-text" id="garden_code" name="garden_code" value="<?php echo esc_attr((string) ($record['garden_code'] ?? '')); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="garden_name">Tên vườn</label></th>
                                <td><input class="regular-text" id="garden_name" name="garden_name" value="<?php echo esc_attr((string) ($record['garden_name'] ?? '')); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="status_line">Dòng trạng thái</label></th>
                                <td><input class="regular-text" id="status_line" name="status_line" value="<?php echo esc_attr((string) ($record['status_line'] ?? '')); ?>"><p class="description">Ví dụ: 4 khoang • đang theo dõi</p></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="summary">Mô tả ngắn</label></th>
                                <td><textarea id="summary" name="summary" rows="4" class="large-text"><?php echo esc_textarea((string) ($record['summary'] ?? '')); ?></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:0 0 20px;max-width:1100px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0">Vật tư khu vườn</h2>
                            <p style="margin:8px 0 0">Điền mỗi dòng là một vật tư. Bỏ trống tên thì hệ thống sẽ bỏ qua dòng đó khi lưu.</p>
                        </div>
                        <button class="button button-secondary" type="button" data-add-tool-row>+ Thêm dòng vật tư</button>
                    </div>
                    <table class="widefat striped" data-tools-table>
                        <thead>
                            <tr>
                                <th style="width:120px">Mã</th>
                                <th style="width:180px">Tên vật tư</th>
                                <th style="width:140px">Loại</th>
                                <th>Mô tả</th>
                                <th style="width:90px">Sở hữu</th>
                                <th style="width:90px">SL</th>
                                <th style="width:140px">Ảnh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tools as $index => $tool) : ?>
                                <tr data-tool-row>
                                    <td><input class="regular-text code" name="tools[<?php echo esc_attr((string) $index); ?>][tool_key]" value="<?php echo esc_attr((string) ($tool['tool_key'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="tools[<?php echo esc_attr((string) $index); ?>][name]" value="<?php echo esc_attr((string) ($tool['name'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="tools[<?php echo esc_attr((string) $index); ?>][type]" value="<?php echo esc_attr((string) ($tool['type'] ?? '')); ?>"></td>
                                    <td><textarea name="tools[<?php echo esc_attr((string) $index); ?>][description]" rows="2" class="large-text"><?php echo esc_textarea((string) ($tool['description'] ?? '')); ?></textarea></td>
                                    <td><input type="number" min="0" step="1" name="tools[<?php echo esc_attr((string) $index); ?>][owned]" value="<?php echo esc_attr((string) ((int) ($tool['owned'] ?? 0))); ?>"></td>
                                    <td><input type="number" min="0" step="1" name="tools[<?php echo esc_attr((string) $index); ?>][qty]" value="<?php echo esc_attr((string) ((int) ($tool['qty'] ?? 0))); ?>"></td>
                                    <td><div style="display:flex;gap:8px;align-items:flex-start"><input class="regular-text" name="tools[<?php echo esc_attr((string) $index); ?>][image]" value="<?php echo esc_attr((string) ($tool['image'] ?? '')); ?>"><button class="button-link-delete" type="button" data-remove-row>Xóa</button></div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:0 0 20px;max-width:1280px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0">Khoang / Pots của khu vườn</h2>
                            <p style="margin:8px 0 0">Điền mỗi dòng là một khoang thật. Bỏ trống mã khoang hoặc tên khoang thì dòng đó sẽ bị bỏ qua khi lưu.</p>
                        </div>
                        <button class="button button-secondary" type="button" data-add-pot-row>+ Thêm dòng khoang</button>
                    </div>
                    <table class="widefat striped" data-pots-table>
                        <thead>
                            <tr>
                                <th style="width:90px">Mã khoang</th>
                                <th style="width:140px">Tên khoang</th>
                                <th style="width:120px">Trạng thái</th>
                                <th>Mô tả ngắn</th>
                                <th style="width:70px">pH</th>
                                <th style="width:90px">Nhiệt độ</th>
                                <th style="width:80px">Độ ẩm</th>
                                <th style="width:120px">Đèn</th>
                                <th style="width:90px">light_device</th>
                                <th style="width:120px">Bơm</th>
                                <th style="width:120px">Tưới</th>
                                <th style="width:130px">Harvest ETA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pots as $index => $pot) : ?>
                                <tr data-pot-row-main>
                                    <td><input class="regular-text code" name="pots[<?php echo esc_attr((string) $index); ?>][pot_code]" value="<?php echo esc_attr((string) ($pot['pot_code'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][pot_name]" value="<?php echo esc_attr((string) ($pot['pot_name'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][status]" value="<?php echo esc_attr((string) ($pot['status'] ?? '')); ?>"></td>
                                    <td><textarea name="pots[<?php echo esc_attr((string) $index); ?>][status_summary]" rows="2" class="large-text"><?php echo esc_textarea((string) ($pot['status_summary'] ?? '')); ?></textarea></td>
                                    <td><input class="small-text" name="pots[<?php echo esc_attr((string) $index); ?>][ph]" value="<?php echo esc_attr((string) ($pot['ph'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][temperature]" value="<?php echo esc_attr((string) ($pot['temperature'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][humidity]" value="<?php echo esc_attr((string) ($pot['humidity'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][light_label]" value="<?php echo esc_attr((string) ($pot['light_label'] ?? '')); ?>"></td>
                                    <td><input class="regular-text code" name="pots[<?php echo esc_attr((string) $index); ?>][light_device]" value="<?php echo esc_attr((string) ($pot['light_device'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][pump_label]" value="<?php echo esc_attr((string) ($pot['pump_label'] ?? '')); ?>"></td>
                                    <td><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][irrigation]" value="<?php echo esc_attr((string) ($pot['irrigation'] ?? '')); ?>"></td>
                                    <td><div style="display:flex;gap:8px;align-items:flex-start"><input class="regular-text" name="pots[<?php echo esc_attr((string) $index); ?>][harvest_eta]" value="<?php echo esc_attr((string) ($pot['harvest_eta'] ?? '')); ?>"><button class="button-link-delete" type="button" data-remove-pot-row>Xóa</button></div></td>
                                </tr>
                                <tr data-pot-row-extra>
                                    <td colspan="6"><label><strong>AI note</strong><br><textarea name="pots[<?php echo esc_attr((string) $index); ?>][ai_note]" rows="2" class="large-text"><?php echo esc_textarea((string) ($pot['ai_note'] ?? '')); ?></textarea></label></td>
                                    <td colspan="3"><label><strong>Image URL</strong><br><input class="large-text" name="pots[<?php echo esc_attr((string) $index); ?>][image_url]" value="<?php echo esc_attr((string) ($pot['image_url'] ?? '')); ?>"></label></td>
                                    <td colspan="3"><label><strong>Video URL</strong><br><input class="large-text" name="pots[<?php echo esc_attr((string) $index); ?>][video_url]" value="<?php echo esc_attr((string) ($pot['video_url'] ?? '')); ?>"></label></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php submit_button('Lưu hồ sơ, vật tư & khoang khu vườn'); ?>
            </form>

            <script>
            (function () {
              const toolsTable = document.querySelector('[data-tools-table] tbody');
              const potsTable = document.querySelector('[data-pots-table] tbody');
              const addToolBtn = document.querySelector('[data-add-tool-row]');
              const addPotBtn = document.querySelector('[data-add-pot-row]');

              function reindexTools() {
                if (!toolsTable) return;
                Array.from(toolsTable.querySelectorAll('[data-tool-row]')).forEach((row, index) => {
                  row.querySelectorAll('input, textarea').forEach((field) => {
                    field.name = field.name.replace(/tools\[\d+\]/, `tools[${index}]`);
                  });
                });
              }

              function reindexPots() {
                if (!potsTable) return;
                const mains = Array.from(potsTable.querySelectorAll('[data-pot-row-main]'));
                mains.forEach((mainRow, index) => {
                  const extraRow = mainRow.nextElementSibling;
                  [mainRow, extraRow].forEach((row) => {
                    if (!row) return;
                    row.querySelectorAll('input, textarea').forEach((field) => {
                      field.name = field.name.replace(/pots\[\d+\]/, `pots[${index}]`);
                    });
                  });
                });
              }

              if (addToolBtn && toolsTable) {
                addToolBtn.addEventListener('click', () => {
                  const index = toolsTable.querySelectorAll('[data-tool-row]').length;
                  const tr = document.createElement('tr');
                  tr.setAttribute('data-tool-row', '1');
                  tr.innerHTML = `
                    <td><input class="regular-text code" name="tools[${index}][tool_key]"></td>
                    <td><input class="regular-text" name="tools[${index}][name]"></td>
                    <td><input class="regular-text" name="tools[${index}][type]"></td>
                    <td><textarea name="tools[${index}][description]" rows="2" class="large-text"></textarea></td>
                    <td><input type="number" min="0" step="1" name="tools[${index}][owned]" value="0"></td>
                    <td><input type="number" min="0" step="1" name="tools[${index}][qty]" value="0"></td>
                    <td><div style="display:flex;gap:8px;align-items:flex-start"><input class="regular-text" name="tools[${index}][image]"><button class="button-link-delete" type="button" data-remove-row>Xóa</button></div></td>`;
                  toolsTable.appendChild(tr);
                });

                toolsTable.addEventListener('click', (event) => {
                  const btn = event.target.closest('[data-remove-row]');
                  if (!btn) return;
                  const row = btn.closest('[data-tool-row]');
                  if (row) row.remove();
                  reindexTools();
                });
              }

              if (addPotBtn && potsTable) {
                addPotBtn.addEventListener('click', () => {
                  const index = potsTable.querySelectorAll('[data-pot-row-main]').length;
                  const main = document.createElement('tr');
                  main.setAttribute('data-pot-row-main', '1');
                  main.innerHTML = `
                    <td><input class="regular-text code" name="pots[${index}][pot_code]"></td>
                    <td><input class="regular-text" name="pots[${index}][pot_name]"></td>
                    <td><input class="regular-text" name="pots[${index}][status]"></td>
                    <td><textarea name="pots[${index}][status_summary]" rows="2" class="large-text"></textarea></td>
                    <td><input class="small-text" name="pots[${index}][ph]"></td>
                    <td><input class="regular-text" name="pots[${index}][temperature]"></td>
                    <td><input class="regular-text" name="pots[${index}][humidity]"></td>
                    <td><input class="regular-text" name="pots[${index}][light_label]"></td>
                    <td><input class="regular-text code" name="pots[${index}][light_device]"></td>
                    <td><input class="regular-text" name="pots[${index}][pump_label]"></td>
                    <td><input class="regular-text" name="pots[${index}][irrigation]"></td>
                    <td><div style="display:flex;gap:8px;align-items:flex-start"><input class="regular-text" name="pots[${index}][harvest_eta]"><button class="button-link-delete" type="button" data-remove-pot-row>Xóa</button></div></td>`;
                  const extra = document.createElement('tr');
                  extra.setAttribute('data-pot-row-extra', '1');
                  extra.innerHTML = `
                    <td colspan="6"><label><strong>AI note</strong><br><textarea name="pots[${index}][ai_note]" rows="2" class="large-text"></textarea></label></td>
                    <td colspan="3"><label><strong>Image URL</strong><br><input class="large-text" name="pots[${index}][image_url]"></label></td>
                    <td colspan="3"><label><strong>Video URL</strong><br><input class="large-text" name="pots[${index}][video_url]"></label></td>`;
                  potsTable.appendChild(main);
                  potsTable.appendChild(extra);
                });

                potsTable.addEventListener('click', (event) => {
                  const btn = event.target.closest('[data-remove-pot-row]');
                  if (!btn) return;
                  const main = btn.closest('[data-pot-row-main]');
                  const extra = main ? main.nextElementSibling : null;
                  if (main) main.remove();
                  if (extra && extra.matches('[data-pot-row-extra]')) extra.remove();
                  reindexPots();
                });
              }
            })();
            </script>
        <?php endif; ?>
    </div>
    <?php
}

function aitrongcay_render_device_mapping_admin_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die('Không đủ quyền.');
    }

    $schema = aitrongcay_blynk_device_schema();
    $saved = aitrongcay_get_saved_blynk_configs();
    $builtins = aitrongcay_blynk_builtin_configs();
    $gardens = aitrongcay_known_gardens_for_device_admin();
    ?>
    <div class="wrap">
        <h1>Mapping thiết bị theo khu vườn</h1>
        <p>Trang này dùng để khai báo token thật, VPin và mapping thiết bị theo từng <code>garden_key</code>. Model hiện tại là: 1 rack tối đa 6 khoang, mỗi khoang 2 khoang, 2 đèn, 1 van cấp, 1 van thoát; toàn rack dùng cảm biến chung + bơm chung.</p>
        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p>Đã lưu mapping thiết bị khu vườn.</p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('aitrongcay_save_device_mapping'); ?>
            <input type="hidden" name="action" value="aitrongcay_save_device_mapping">
            <?php foreach ($gardens as $garden) :
                $garden_key = (string) ($garden['garden_key'] ?? '');
                $dataset_key = (string) ($garden['dataset_key'] ?? '');
                $config = $saved[$garden_key] ?? ($builtins[$dataset_key] ?? aitrongcay_blynk_default_config());
                $token_mask = trim((string) ($config['token'] ?? '')) !== '' ? str_repeat('•', max(8, strlen((string) $config['token']))) : 'Chưa khai báo';
                ?>
                <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:0 0 20px;max-width:1280px;">
                    <h2 style="margin-top:0"><?php echo esc_html((string) ($garden['label'] ?? $garden_key)); ?></h2>
                    <p style="margin-top:0;color:#50575e">
                        <strong>garden_key:</strong> <code><?php echo esc_html($garden_key); ?></code>
                        <?php if (! empty($garden['owner_email'])) : ?> · <strong>owner:</strong> <?php echo esc_html((string) $garden['owner_email']); ?><?php endif; ?>
                        <?php if ($dataset_key !== '') : ?> · <strong>dataset:</strong> <code><?php echo esc_html($dataset_key); ?></code><?php endif; ?>
                    </p>
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="base-<?php echo esc_attr($garden_key); ?>"><?php echo esc_html((string) $schema['base']['label']); ?></label></th>
                                <td><input class="regular-text code" id="base-<?php echo esc_attr($garden_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][base]" value="<?php echo esc_attr((string) ($config['base'] ?? '')); ?>"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="token-<?php echo esc_attr($garden_key); ?>"><?php echo esc_html((string) $schema['token']['label']); ?></label></th>
                                <td>
                                    <input class="regular-text code" id="token-<?php echo esc_attr($garden_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][token]" value="<?php echo esc_attr((string) ($config['token'] ?? '')); ?>" autocomplete="off" spellcheck="false">
                                    <p class="description">Hiện tại: <?php echo esc_html($token_mask); ?>. Token này dùng cho cảm biến chung + bơm và có thể dùng luôn cho đèn, van cấp, van thoát của các khoang. Ở phần token theo khoang, có thể để trống hoặc nhập <code>__shared__</code>/<code>shared-token</code> để dùng lại token chung.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
                        <div>
                            <h3>VPin cảm biến & điều khiển</h3>
                            <?php foreach ((array) ($schema['vpins'] ?? []) as $field_key => $field) : ?>
                                <p>
                                    <label for="vpin-<?php echo esc_attr($garden_key . '-' . $field_key); ?>"><strong><?php echo esc_html((string) ($field['label'] ?? $field_key)); ?></strong></label><br>
                                    <input class="regular-text code" id="vpin-<?php echo esc_attr($garden_key . '-' . $field_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][vpins][<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr((string) (($config['vpins'][$field_key] ?? ''))); ?>">
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <h3>Khóa thiết bị logic</h3>
                            <?php foreach ((array) ($schema['devices'] ?? []) as $field_key => $field) : ?>
                                <p>
                                    <label for="device-<?php echo esc_attr($garden_key . '-' . $field_key); ?>"><strong><?php echo esc_html((string) ($field['label'] ?? $field_key)); ?></strong></label><br>
                                    <input class="regular-text code" id="device-<?php echo esc_attr($garden_key . '-' . $field_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][devices][<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr((string) (($config['devices'][$field_key] ?? ''))); ?>">
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <h3>Map khoang → thiết bị đèn</h3>
                            <?php foreach ((array) ($schema['pots'] ?? []) as $field_key => $field) : ?>
                                <p>
                                    <label for="pot-<?php echo esc_attr($garden_key . '-' . $field_key); ?>"><strong><?php echo esc_html((string) ($field['label'] ?? $field_key)); ?></strong></label><br>
                                    <input class="regular-text code" id="pot-<?php echo esc_attr($garden_key . '-' . $field_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][pots][<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr((string) (($config['pots'][$field_key] ?? ''))); ?>">
                                </p>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <h3>Token riêng theo khoang</h3>
                            <?php foreach ((array) ($schema['pot_tokens'] ?? []) as $field_key => $field) :
                                $masked = trim((string) ($config['pot_tokens'][$field_key] ?? '')) !== '' ? str_repeat('•', max(8, strlen((string) $config['pot_tokens'][$field_key]))) : 'Chưa khai báo';
                                ?>
                                <p>
                                    <label for="pot-token-<?php echo esc_attr($garden_key . '-' . $field_key); ?>"><strong><?php echo esc_html((string) ($field['label'] ?? $field_key)); ?></strong></label><br>
                                    <input class="regular-text code" id="pot-token-<?php echo esc_attr($garden_key . '-' . $field_key); ?>" name="garden_configs[<?php echo esc_attr($garden_key); ?>][pot_tokens][<?php echo esc_attr($field_key); ?>]" value="<?php echo esc_attr((string) (($config['pot_tokens'][$field_key] ?? ''))); ?>" autocomplete="off" spellcheck="false">
                                    <span class="description" style="display:block">Hiện tại: <?php echo esc_html($masked); ?>. Để trống hoặc nhập <code>__shared__</code> nếu khoang này dùng cùng token chung.</span>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php submit_button('Lưu mapping thiết bị'); ?>
        </form>
    </div>
    <?php
}
