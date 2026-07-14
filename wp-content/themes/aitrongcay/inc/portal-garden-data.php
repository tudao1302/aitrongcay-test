<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_portal_sample_profiles(): array
{
    return [];
}

function aitrongcay_portal_profile_for_user(?WP_User $user = null): array
{
    static $cache = [];

    $user = $user instanceof WP_User ? $user : wp_get_current_user();
    $user_id = (int) ($user->ID ?? 0);
    if ($user_id > 0 && isset($cache[$user_id])) {
        return $cache[$user_id];
    }

    $display_name = trim((string) ($user->display_name ?: $user->first_name ?: $user->user_login));
    $display_name = $display_name !== '' ? $display_name : 'Anh/chị';
    $garden_key = function_exists('aitrongcay_primary_garden_key_for_user') ? aitrongcay_primary_garden_key_for_user($user) : (function_exists('aitrongcay_current_garden_key') ? aitrongcay_current_garden_key($user) : '');
    $garden_name = function_exists('aitrongcay_build_default_garden_name') ? aitrongcay_build_default_garden_name($garden_key, $user) : ('Khu vườn của ' . $display_name);

    $profile = [
        'name' => $display_name,
        'garden_code' => $garden_key !== '' ? strtoupper(substr(md5($garden_key), 0, 6)) : 'GARDEN',
        'garden_name' => $garden_name,
        'summary' => 'Khu vườn này sẽ dần hiện rõ hơn khi có khoang, ảnh, dữ liệu cảm biến và nhật ký chăm sóc được cập nhật đúng.',
        'status' => 'Sẵn sàng theo dõi',
        'market_title' => $display_name . ' • Chia sẻ khu vườn của mình',
        'market_body' => 'Khu vườn đang được theo dõi theo từng khoang để việc chăm sóc, quan sát và đối chiếu trở nên rõ ràng hơn mỗi ngày.',
        'market_meta' => ['Hình thức: chia sẻ từ khu vườn', 'Có thể gắn ảnh, video và ghi chú theo khoang'],
        'facebook_caption' => 'Hôm nay em mở khu vườn của mình và tiếp tục cập nhật các mốc chăm sóc để mọi thứ rõ ràng hơn từng ngày.',
    ];

    if ($user_id > 0) {
        $cache[$user_id] = $profile;
    }

    return $profile;
}

function aitrongcay_portal_dataset_library(): array
{
    return [];
}

function aitrongcay_portal_default_dataset(): array
{
    return aitrongcay_portal_empty_dataset(wp_get_current_user());
}

function aitrongcay_portal_normalize_plant_match_text(string $value): string
{
    $value = strtolower(remove_accents(trim($value)));
    $value = preg_replace('/[^a-z0-9\s-]+/', ' ', $value) ?? '';
    $value = preg_replace('/\b(khoang|chau|pot|cay|rau|giong|hat)\b/u', ' ', $value) ?? '';
    $value = preg_replace('/\s+/', ' ', $value) ?? '';
    return trim($value);
}

function aitrongcay_portal_match_source_to_alias(string $source, string $alias): bool
{
    $source = trim($source);
    $alias = trim($alias);
    if ($source === '' || $alias === '') {
        return false;
    }

    if ($source === $alias) {
        return true;
    }

    if (str_contains($source, $alias) || str_contains($alias, $source)) {
        return true;
    }

    $source_tokens = array_values(array_filter(explode(' ', $source)));
    $alias_tokens = array_values(array_filter(explode(' ', $alias)));
    if ($source_tokens === [] || $alias_tokens === []) {
        return false;
    }

    $shared_tokens = array_intersect($source_tokens, $alias_tokens);
    return count($shared_tokens) >= min(count($source_tokens), count($alias_tokens));
}

function aitrongcay_onboarding_plant_catalog(): array
{
    static $catalog = null;
    if ($catalog !== null) {
        return $catalog;
    }
    if (! function_exists('aitrongcay_onboarding_tables')) {
        $catalog = [];
        return $catalog;
    }

    global $wpdb;
    $tables = aitrongcay_onboarding_tables();
    $sql = "SELECT id, slug, public_name, internal_name, scientific_name, variety_name, status FROM {$tables['plants']} ORDER BY updated_at DESC, id DESC";
    $rows = $wpdb->get_results($sql, ARRAY_A) ?: [];
    $catalog = [];
    foreach ($rows as $row) {
        $aliases = [];
        foreach (['public_name', 'internal_name', 'slug', 'scientific_name', 'variety_name'] as $field) {
            $normalized = aitrongcay_portal_normalize_plant_match_text((string) ($row[$field] ?? ''));
            if ($normalized !== '') {
                $aliases[$normalized] = true;
            }
        }
        $catalog[] = [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'public_name' => (string) ($row['public_name'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'aliases' => array_keys($aliases),
        ];
    }

    return $catalog;
}

function aitrongcay_resolve_onboarding_plant_for_pot(array $pot): array
{
    $unknown = [
        'plant_id' => 0,
        'plant_slug' => '',
        'plant_name' => 'Cây chưa xác định',
        'onboarding_status' => 'unknown',
        'has_onboarding' => false,
    ];

    if (!empty($pot['plant_id'])) {
        $pot_plant_id = (int) $pot['plant_id'];
        foreach (aitrongcay_onboarding_plant_catalog() as $plant) {
            if ((int) ($plant['id'] ?? 0) === $pot_plant_id) {
                return [
                    'plant_id' => $pot_plant_id,
                    'plant_slug' => (string) ($plant['slug'] ?? ''),
                    'plant_name' => (string) ($plant['public_name'] ?? 'Cây chưa xác định'),
                    'onboarding_status' => 'matched',
                    'has_onboarding' => true,
                ];
            }
        }
    }

    if (trim((string) ($pot['plant_name'] ?? '')) === 'Cây chưa xác định') {
        return $unknown;
    }

    $match_sources_to_catalog = static function (array $sources) use ($unknown): array {
        $normalized_sources = [];
        foreach ($sources as $source) {
            $normalized = aitrongcay_portal_normalize_plant_match_text((string) $source);
            if ($normalized !== '') {
                $normalized_sources[] = $normalized;
            }
        }
        if ($normalized_sources === []) {
            return $unknown;
        }

        foreach (aitrongcay_onboarding_plant_catalog() as $plant) {
            foreach ((array) ($plant['aliases'] ?? []) as $alias) {
                if ($alias === '') {
                    continue;
                }
                foreach ($normalized_sources as $source) {
                    if (aitrongcay_portal_match_source_to_alias($source, $alias)) {
                        return [
                            'plant_id' => (int) ($plant['id'] ?? 0),
                            'plant_slug' => (string) ($plant['slug'] ?? ''),
                            'plant_name' => (string) ($plant['public_name'] ?? 'Cây chưa xác định'),
                            'onboarding_status' => 'matched',
                            'has_onboarding' => true,
                        ];
                    }
                }
            }
        }

        return $unknown;
    };

    $primary_match = $match_sources_to_catalog([
        (string) ($pot['plant_name'] ?? ''),
        (string) ($pot['pot_name'] ?? $pot['name'] ?? ''),
        (string) ($pot['status'] ?? ''),
    ]);
    if (! empty($primary_match['has_onboarding'])) {
        return $primary_match;
    }

    $is_analysis_text = static function (string $text): bool {
        $text = trim($text);
        if ($text === '') {
            return false;
        }

        return preg_match('/cindy đã đối chiếu|hồ sơ onboarding|đối chiếu ảnh|không phải|cần xác minh lại loại cây/iu', $text) === 1;
    };

    $secondary_sources = [];
    $status_summary = (string) ($pot['status_summary'] ?? '');
    $ai_note = (string) ($pot['ai_note'] ?? '');
    if (! $is_analysis_text($status_summary)) {
        $secondary_sources[] = $status_summary;
    }
    if (! $is_analysis_text($ai_note)) {
        $secondary_sources[] = $ai_note;
    }

    return $match_sources_to_catalog($secondary_sources);
}

function aitrongcay_portal_enrich_pot_with_onboarding(array $pot): array
{
    $resolved = aitrongcay_resolve_onboarding_plant_for_pot($pot);
    $pot['plant_id'] = (int) ($resolved['plant_id'] ?? 0);
    $pot['plant_slug'] = (string) ($resolved['plant_slug'] ?? '');
    $existing_name = trim((string) ($pot['plant_name'] ?? ''));
    if ($existing_name !== '') {
        $pot['plant_name'] = $existing_name;
    } else {
        $pot['plant_name'] = (string) ($resolved['plant_name'] ?? 'Cây chưa xác định');
    }
    $pot['onboarding_status'] = (string) ($resolved['onboarding_status'] ?? 'unknown');
    $pot['has_onboarding'] = ! empty($resolved['has_onboarding']);
    return $pot;
}

function aitrongcay_portal_empty_dataset(?WP_User $user = null): array
{
    $profile = aitrongcay_portal_profile_for_user($user);
    $name = is_array($profile) ? (string) ($profile['name'] ?? 'Anh/chị') : 'Anh/chị';

    return [
        'match_emails' => [],
        'ai' => [
            'name' => 'AI Agent của ' . $name,
            'summary' => 'Khu vườn này còn đang chờ những khoang đầu tiên và các dữ liệu thực tế đầu tiên được đưa vào. Khi bắt đầu có dữ liệu, mọi thứ ở đây sẽ hiện dần theo đúng nhịp chăm sóc của khu vườn.',
            'tips' => [
                'Bắt đầu bằng một khoang trồng cây đầu tiên để khu vườn có mốc theo dõi rõ ràng.',
                'Khi đã có khoang, ảnh, chỉ số và ghi chú chăm sóc sẽ dần hiện ra ngay trong khu vườn này.',
                'Nếu đã có khoang ngoài thực tế mà ở đây chưa thấy, chỉ cần gắn khoang đó đúng vào khu vườn là được.',
            ],
            'starter_prompts' => [
                'Hướng dẫn em thêm khoang đầu tiên.',
                'Khu vườn này cần chuẩn bị gì để bắt đầu có dữ liệu thật?',
                'Khi nào dashboard bắt đầu hiện dữ liệu theo từng khoang?',
            ],
        ],
        'pots' => [],
        'tool_shelf' => [],
    ];
}

function aitrongcay_portal_dataset_for_garden(string $garden_key = '', ?WP_User $viewer = null): array
{
    static $cache = [];
    $viewer_id = (int) (($viewer instanceof WP_User ? $viewer->ID : 0));
    $cache_key = $garden_key . '|' . $viewer_id;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $context_user = $owner instanceof WP_User ? $owner : ($viewer instanceof WP_User ? $viewer : wp_get_current_user());

    $record = function_exists('aitrongcay_get_garden_record') ? aitrongcay_get_garden_record($garden_key) : null;
    $db_pots = function_exists('aitrongcay_get_db_pots') ? aitrongcay_get_db_pots($garden_key) : [];
    $db_tools = function_exists('aitrongcay_get_db_tools') ? aitrongcay_get_db_tools($garden_key) : [];

    if (is_array($record) || ! empty($db_pots) || ! empty($db_tools)) {
        $profile = aitrongcay_portal_profile_for_user($context_user instanceof WP_User ? $context_user : null);
        $garden_name = trim((string) ($record['garden_name'] ?? ($profile['garden_name'] ?? '')));
        $garden_code = trim((string) ($record['garden_code'] ?? ($profile['garden_code'] ?? '')));
        $summary = trim((string) ($record['summary'] ?? ''));
        $status_line = trim((string) ($record['status_line'] ?? ''));

        $pots = array_map(static function (array $pot): array {
            $temp = (string) ($pot['temperature'] ?? '');
            $hum = (string) ($pot['humidity'] ?? '');
            $soil_moisture = (string) ($pot['soil_moisture'] ?? '');

            return aitrongcay_portal_enrich_pot_with_onboarding([
                'code' => (string) ($pot['pot_code'] ?? ''),
                'name' => (string) ($pot['pot_name'] ?? ''),
                'plant_name' => (string) ($pot['plant_name'] ?? ''),
                'plant_id' => (int) ($pot['plant_id'] ?? 0),
                'status' => (string) ($pot['status'] ?? ''),
                'ph' => (string) ($pot['ph'] ?? ''),
                'temperature' => $temp,
                'humidity' => $hum,
                'light' => (string) ($pot['light_label'] ?? ''),
                'light_device' => (string) ($pot['light_device'] ?? ''),
                'pump' => (string) ($pot['pump_label'] ?? ''),
                'irrigation' => (string) ($pot['irrigation'] ?? ''),
                'video' => (string) ($pot['video_url'] ?? ''),
                'image' => wp_make_link_relative((string) ($pot['image_url'] ?? '')),
                'latest_photo_id' => (int) ($pot['latest_photo_id'] ?? 0),
                'latest_photo_at' => (string) ($pot['latest_photo_at'] ?? ''),
                'ai_note' => (string) ($pot['ai_note'] ?? ''),
                'status_summary' => (string) ($pot['status_summary'] ?? ''),
                'harvest_eta' => (string) ($pot['harvest_eta'] ?? ''),
                'created_at' => (string) ($pot['created_at'] ?? ''),
                'soil_moisture' => $soil_moisture !== '' ? $soil_moisture : (rand(50, 85) . '%'),
                'soil_ec' => (string) ($pot['soil_ec'] ?? (rand(800, 1500) . ' µS/cm')),
                'soil_temp' => (string) ($pot['soil_temp'] ?? rand(24, 28) . '°C'),
                'trays' => [(string) ($pot['pot_name'] ?? '')],
            ]);
        }, $db_pots);

        $tools = array_map(static function (array $item): array {
            return [
                'name' => (string) ($item['name'] ?? ''),
                'type' => (string) ($item['type'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
                'owned' => (int) ($item['owned'] ?? 0),
                'qty' => (int) ($item['qty'] ?? 0),
                'image' => (string) ($item['image'] ?? ''),
            ];
        }, $db_tools);

        if ($status_line === '' && ! empty($pots)) {
            $status_line = count($pots) . ' khoang • đang theo dõi';
        }
        if ($garden_name === '' && $context_user instanceof WP_User) {
            $garden_name = 'Khu vườn của ' . trim((string) ($context_user->display_name ?: $context_user->user_login));
        }

        $cache[$cache_key] = [
            'match_emails' => [],
            'ai' => [
                'name' => 'AI Agent của ' . ($garden_name !== '' ? $garden_name : (($profile['name'] ?? 'khu vườn này'))),
                'summary' => '',
                'tips' => [],
                'starter_prompts' => ['Hôm nay khoang nào cần chú ý nhất?', 'Tóm tắt nhanh tình trạng vườn này giúp em.', 'Gợi ý việc nên làm tiếp theo cho từng khoang.'],
            ],
            'pots' => $pots,
            'tool_shelf' => $tools,
            'garden_meta' => [
                'garden_name' => $garden_name,
                'garden_code' => $garden_code,
                'status' => $status_line,
            ],
        ];

        return $cache[$cache_key];
    }

    $cache[$cache_key] = aitrongcay_portal_empty_dataset($context_user instanceof WP_User ? $context_user : null);
    return $cache[$cache_key];
}

function aitrongcay_portal_garden_ai(string $garden_key = '', ?WP_User $viewer = null): array
{
    return (array) (aitrongcay_portal_dataset_for_garden($garden_key, $viewer)['ai'] ?? []);
}

function aitrongcay_portal_pots(string $garden_key = '', ?WP_User $viewer = null): array
{
    $viewer = $viewer instanceof WP_User ? $viewer : wp_get_current_user();
    
    $fetch_pots_for_garden = function($gk) use ($viewer) {
        $pts = (array) (aitrongcay_portal_dataset_for_garden($gk, $viewer)['pots'] ?? []);
        if (function_exists('aitrongcay_get_custom_pots')) {
            $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($gk) : null;
            $target_user = $owner instanceof WP_User ? $owner : $viewer;
            $target_user_id = (int) ($target_user->ID ?? 0);
            if ($target_user_id > 0) {
                $db_or_custom_pots = aitrongcay_get_custom_pots($gk, $target_user_id);
                if (! empty($db_or_custom_pots)) {
                    $pts = $db_or_custom_pots;
                }
            }
        }
        return $pts;
    };

    $pots = $fetch_pots_for_garden($garden_key);

    $cloned_rack_ids = get_option('aitrongcay_cloned_racks_' . $garden_key, []);
    $cloned_rack_ids = is_array($cloned_rack_ids) ? array_values($cloned_rack_ids) : (is_string($cloned_rack_ids) && $cloned_rack_ids !== '' ? explode(',', $cloned_rack_ids) : array_values((array) $cloned_rack_ids));
    if (!empty($cloned_rack_ids) && function_exists('aitrongcay_garden_racks_table')) {
        global $wpdb;
        $racks_table = aitrongcay_garden_racks_table();
        $ids_placeholder = implode(',', array_fill(0, count($cloned_rack_ids), '%d'));
        $cloned_gks = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT garden_key FROM {$racks_table} WHERE id IN ($ids_placeholder) AND garden_key != ''", ...$cloned_rack_ids));
        
        foreach ($cloned_gks as $cgk) {
            if ($cgk !== $garden_key) {
                $pots = array_merge($pots, $fetch_pots_for_garden((string) $cgk));
            }
        }
        
        $unique_pots = [];
        foreach ($pots as $p) {
            $c = $p['code'] ?? '';
            if ($c !== '') {
                $unique_pots[$c] = $p;
            } else {
                $unique_pots[] = $p;
            }
        }
        $pots = array_values($unique_pots);
    }

    if (function_exists('aitrongcay_portal_apply_garden_device_mapping')) {
        $pots = aitrongcay_portal_apply_garden_device_mapping($pots, $garden_key);
    }

    if (function_exists('aitrongcay_get_pot_name_overrides')) {
        $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
        $target_user = $owner instanceof WP_User ? $owner : $viewer;
        $target_user_id = (int) ($target_user->ID ?? 0);
        $name_overrides = $target_user_id > 0 ? aitrongcay_get_pot_name_overrides($garden_key, $target_user_id) : [];
        if (is_array($name_overrides) && ! empty($name_overrides)) {
            foreach ($pots as &$pot) {
                $pot_code = (string) ($pot['code'] ?? '');
                if ($pot_code !== '' && isset($name_overrides[$pot_code]) && trim((string) $name_overrides[$pot_code]) !== '') {
                    $pot['name'] = trim((string) $name_overrides[$pot_code]);
                }
            }
            unset($pot);
        }
    }

    foreach ($pots as &$pot) {
        if (! is_array($pot)) {
            continue;
        }
        if (! empty($pot['plant_id']) && $pot['plant_id'] > 0) {
            continue;
        }
        $pot = aitrongcay_portal_enrich_pot_with_onboarding($pot);
    }
    unset($pot);

    return $pots;
}

function aitrongcay_portal_sample_tool_shelf(): array
{
    $items = [
        ['name' => 'Hạt giống cà chua bi', 'type' => 'Hạt giống', 'description' => 'Bộ hạt giống dùng cho các khoang cà chua bi trong vườn.', 'owned' => 6, 'qty' => 6, 'image' => 'tool-seed-tomato-real.png'],
        ['name' => 'Hạt giống cải cúc', 'type' => 'Hạt giống', 'description' => 'Hạt giống rau ăn lá cho các lứa gieo mới.', 'owned' => 8, 'qty' => 8, 'image' => 'tool-seed-chrysanthemum-real.png'],
        ['name' => 'Hạt giống cải xoong', 'type' => 'Hạt giống', 'description' => 'Hạt giống cải xoong cho khoang gieo mới và theo dõi nảy mầm.', 'owned' => 5, 'qty' => 5, 'image' => 'tool-seed-watercress-real.png'],
        ['name' => 'Cây giống ớt', 'type' => 'Cây giống', 'description' => 'Cây giống ớt ăn trái để đưa vào khoang mới.', 'owned' => 4, 'qty' => 4, 'image' => 'tool-seedling-chili-real2.png'],
        ['name' => 'Cây giống sâm ngọc linh', 'type' => 'Cây giống', 'description' => 'Cây giống dùng cho giai đoạn cây con sớm.', 'owned' => 3, 'qty' => 3, 'image' => 'tool-seedling-sam-real2.png'],
        ['name' => 'Dung dịch dinh dưỡng A', 'type' => 'Dinh dưỡng thủy canh', 'description' => 'Thành phần A cho hệ dinh dưỡng thủy canh.', 'owned' => 2, 'qty' => 2, 'image' => 'tool-nutrient-ab-real.png'],
        ['name' => 'Dung dịch dinh dưỡng B', 'type' => 'Dinh dưỡng thủy canh', 'description' => 'Thành phần B dùng cùng bộ dinh dưỡng thủy canh.', 'owned' => 2, 'qty' => 2, 'image' => 'tool-nutrient-ab-real.png'],
        ['name' => 'Phân bón lá hữu cơ', 'type' => 'Phân bón', 'description' => 'Phân bón hỗ trợ giai đoạn sinh trưởng thân lá.', 'owned' => 3, 'qty' => 3, 'image' => 'tool-fertilizer-organic-real.png'],
        ['name' => 'Khoang trồng cây', 'type' => 'Khoang & vật tư', 'description' => 'Khoang trồng cây dùng cho từng loại rau và cây giống.', 'owned' => 12, 'qty' => 12, 'image' => 'tool-tray-real.png'],
        ['name' => 'Rọ thủy canh', 'type' => 'Khoang & vật tư', 'description' => 'Rọ trồng dùng cho cây con và rau ăn lá.', 'owned' => 24, 'qty' => 24, 'image' => 'tool-net-pot.svg'],
        ['name' => 'Máy bơm mini', 'type' => 'Thiết bị', 'description' => 'Máy bơm tuần hoàn cho hệ thủy canh quy mô nhỏ.', 'owned' => 2, 'qty' => 2, 'image' => 'tool-pump-real.png'],
        ['name' => 'Đèn chiếu sáng', 'type' => 'Thiết bị', 'description' => 'Đèn hỗ trợ chiếu sáng cho từng khoang trồng.', 'owned' => 4, 'qty' => 4, 'image' => 'tool-grow-light-real.png'],
    ];

    return $items;
}

function aitrongcay_portal_default_tool_image(array $item): string
{
    $image = trim((string) ($item['image'] ?? ''));
    if ($image !== '' && $image !== 'tools-shed.svg') {
        return $image;
    }

    $name = strtolower(remove_accents(trim((string) ($item['name'] ?? ''))));
    $type = strtolower(remove_accents(trim((string) ($item['type'] ?? ''))));
    $haystack = trim($name . ' ' . $type);

    $map = [
        'ca chua' => 'tool-seed-tomato-real.png',
        'tomato' => 'tool-seed-tomato-real.png',
        'cai cuc' => 'tool-seed-chrysanthemum-real.png',
        'tan o' => 'tool-seed-chrysanthemum-real.png',
        'cai xoong' => 'tool-seed-watercress-real.png',
        'watercress' => 'tool-seed-watercress-real.png',
        'cay giong ot' => 'tool-seedling-chili-real2.png',
        'seedling chili' => 'tool-seedling-chili-real2.png',
        'ot' => 'tool-seed-chili-real.png',
        'chili' => 'tool-seed-chili-real.png',
        'cay giong sam ngoc linh' => 'tool-seedling-sam-real2.png',
        'seedling sam' => 'tool-seedling-sam-real2.png',
        'sam ngoc linh' => 'tool-seed-sam-real.png',
        'dinh duong a' => 'tool-nutrient-ab-real.png',
        'nutrient a' => 'tool-nutrient-ab-real.png',
        'dinh duong b' => 'tool-nutrient-ab-real.png',
        'nutrient b' => 'tool-nutrient-ab-real.png',
        'phan bon' => 'tool-fertilizer-organic-real.png',
        'fertilizer' => 'tool-fertilizer-organic-real.png',
        'khoang' => 'tool-tray-real.png',
        'tray' => 'tool-tray-real.png',
        'ro' => 'tool-net-pot.svg',
        'net pot' => 'tool-net-pot.svg',
        'rọ' => 'tool-net-pot.svg',
        'bom' => 'tool-pump-real.png',
        'pump' => 'tool-pump-real.png',
        'den' => 'tool-grow-light-real.png',
        'đèn' => 'tool-grow-light-real.png',
        'light' => 'tool-grow-light-real.png',
        'hat giong' => 'tool-seed-pouch.svg',
        'seed' => 'tool-seed-pouch.svg',
        'cay giong' => 'tool-seedling-generic.svg',
        'seedling' => 'tool-seedling-generic.svg',
    ];

    foreach ($map as $needle => $filename) {
        if ($needle !== '' && strpos($haystack, $needle) !== false) {
            return $filename;
        }
    }

    return 'tools-shed.svg';
}

function aitrongcay_portal_tool_shelf(string $garden_key = '', ?WP_User $viewer = null): array
{
    $items = (array) (aitrongcay_portal_dataset_for_garden($garden_key, $viewer)['tool_shelf'] ?? []);
    if ($items === []) {
        $items = aitrongcay_portal_sample_tool_shelf();
    }
    return $items;
}
