<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$guest_start_url = wp_login_url(home_url('/portal/flower-bio/'));
$garden_key = $is_logged_in ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$requested_garden = isset($_GET['garden']) ? sanitize_text_field((string) wp_unslash($_GET['garden'])) : '';
if ($requested_garden !== '') {
    $garden_key = $requested_garden;
}
$pots = $is_logged_in && function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
$active_pot_code = isset($_GET['pot']) ? sanitize_text_field((string) wp_unslash($_GET['pot'])) : '';
$active_pot = $pots[0] ?? [];
if ($active_pot_code !== '') {
    foreach ($pots as $candidate_pot) {
        if ((string) ($candidate_pot['code'] ?? '') === $active_pot_code) {
            $active_pot = $candidate_pot;
            break;
        }
    }
}

$plant_id = (int) ($active_pot['plant_id'] ?? 0);
$selected_plant_id = isset($_GET['selected_plant_id']) ? absint($_GET['selected_plant_id']) : 0;
if ($selected_plant_id > 0) {
    $plant_id = $selected_plant_id;
}
$plant_search_query = trim((string) ($_GET['plant_search'] ?? ''));
$plant_search_results = [];
if ($plant_search_query !== '' && $selected_plant_id <= 0 && function_exists('aitrongcay_plants_latest')) {
    $plant_search_results = aitrongcay_plants_latest(8, $plant_search_query);
}
if ($plant_id <= 0 && ! $is_logged_in && function_exists('aitrongcay_plants_latest')) {
    $guest_candidate_plants = aitrongcay_plants_latest(48);
    if ($guest_candidate_plants !== []) {
        $preferred_guest_plants = array_values(array_filter(
            $guest_candidate_plants,
            static fn(array $candidate): bool => in_array((string) ($candidate['status'] ?? ''), ['public', 'active'], true)
        ));
        $guest_pool = $preferred_guest_plants !== [] ? $preferred_guest_plants : $guest_candidate_plants;
        $guest_pick = $guest_pool[array_rand($guest_pool)] ?? null;
        $plant_id = (int) ($guest_pick['id'] ?? 0);
    }
}
$plant = $plant_id > 0 && function_exists('aitrongcay_onboarding_plant_record') ? (aitrongcay_onboarding_plant_record($plant_id) ?: []) : [];
$public = $plant_id > 0 && function_exists('aitrongcay_plant_public_content') ? (aitrongcay_plant_public_content($plant_id) ?: []) : [];
$environment = $plant_id > 0 && function_exists('aitrongcay_plant_environment_profile') ? (aitrongcay_plant_environment_profile($plant_id) ?: []) : [];
$growth_stages = $plant_id > 0 && function_exists('aitrongcay_plant_growth_stages') ? aitrongcay_plant_growth_stages($plant_id) : [];
$nutrition = $plant_id > 0 && function_exists('aitrongcay_plant_nutrition_profile') ? (aitrongcay_plant_nutrition_profile($plant_id) ?: []) : [];
$checklists = $plant_id > 0 && function_exists('aitrongcay_plant_checklists') ? aitrongcay_plant_checklists($plant_id) : [];
$plant_health_issues = $plant_id > 0 && function_exists('aitrongcay_plant_health_issues') ? aitrongcay_plant_health_issues($plant_id) : [];
$plant_supplies = $plant_id > 0 && function_exists('aitrongcay_plant_supplies') ? aitrongcay_plant_supplies($plant_id) : [];
$plant_supply_roles = function_exists('aitrongcay_plant_supply_usage_roles') ? aitrongcay_plant_supply_usage_roles() : [];
$robot_care_guide_text = $plant_id > 0 && function_exists('aitrongcay_plant_longtext_pack') ? trim((string) aitrongcay_plant_longtext_pack($plant_id, 'plant_robot_tasks')) : '';
if ($robot_care_guide_text === '' && $plant_id > 0 && function_exists('aitrongcay_plant_sop_steps')) {
    $robot_sop_steps = aitrongcay_plant_sop_steps($plant_id);
    if (count($robot_sop_steps) === 1) {
        $single_step = $robot_sop_steps[0];
        $single_operator_tasks = trim((string) ($single_step['operator_tasks'] ?? ''));
        $single_has_extra_meta = trim((string) ($single_step['light_level'] ?? '')) !== ''
            || trim((string) ($single_step['watering_rule'] ?? '')) !== ''
            || trim((string) ($single_step['expected_state'] ?? '')) !== ''
            || trim((string) ($single_step['alert_conditions'] ?? '')) !== ''
            || trim((string) ($single_step['notes'] ?? '')) !== ''
            || (int) ($single_step['day_from'] ?? 0) > 0
            || (int) ($single_step['day_to'] ?? 0) > 0;
        if ($single_operator_tasks !== '' && ! $single_has_extra_meta) {
            $robot_care_guide_text = $single_operator_tasks;
        }
    }
}

$raw_public_name = trim((string) ($plant['public_name'] ?? ''));
$raw_pot_plant_name = trim((string) ($active_pot['plant_name'] ?? ''));
$raw_pot_name = trim((string) ($active_pot['name'] ?? ''));
$variety_name = trim((string) ($plant['variety_name'] ?? ''));
$scientific_name = trim((string) ($plant['scientific_name'] ?? ''));

$is_generic_catalog_name = static function (string $name): bool {
    $normalized = trim((string) preg_replace('/\s+/u', ' ', mb_strtolower($name)));
    if ($normalized === '') {
        return false;
    }

    foreach ([
        'rau trồng thủy canh',
        'rau trong thuy canh',
        'cây trồng thủy canh',
        'cay trong thuy canh',
        'rau ăn lá thủy canh',
        'rau an la thuy canh',
    ] as $generic_name) {
        if ($normalized === $generic_name) {
            return true;
        }
    }

    return false;
};

$common_name_from_scientific = static function (string $scientific): string {
    $normalized = trim((string) preg_replace('/\s+/u', ' ', mb_strtolower($scientific)));
    if ($normalized === '') {
        return '';
    }

    $map = [
        'nasturtium officinale' => 'Cải xoong',
        'brassica oleracea var. italica' => 'Bông cải xanh',
        'lactuca sativa' => 'Xà lách',
    ];

    return $map[$normalized] ?? '';
};

$normalize_main_plant_name = static function (string $candidate, string $variety, string $scientific): string {
    $candidate = trim(preg_replace('/\s+/u', ' ', $candidate) ?? $candidate);
    if ($candidate === '') {
        return '';
    }

    $parts = preg_split('/\s*\/\s*/u', $candidate) ?: [$candidate];
    $main = trim((string) ($parts[0] ?? $candidate));

    if ($variety !== '') {
        $variety_pattern = '/' . preg_quote($variety, '/') . '/iu';
        $main = trim((string) preg_replace($variety_pattern, '', $main));
    }
    if ($scientific !== '') {
        $scientific_pattern = '/' . preg_quote($scientific, '/') . '/iu';
        $main = trim((string) preg_replace($scientific_pattern, '', $main));
    }

    $main = trim((string) preg_replace('/\s{2,}/u', ' ', $main));
    if ($main === '') {
        $main = trim((string) ($parts[0] ?? $candidate));
    }

    if (preg_match('/^(.+?)\s+([A-Z][A-Za-z0-9\-]*(?:\s+[A-Z][A-Za-z0-9\-]*)+)$/u', $main, $matches)) {
        $prefix = trim((string) ($matches[1] ?? ''));
        $tail = trim((string) ($matches[2] ?? ''));
        if ($prefix !== '' && $tail !== '' && mb_stripos($prefix, $tail) === false) {
            $main = $prefix;
        }
    }

    return trim($main);
};

$extract_english_plant_name = static function (string $candidate, string $main_name, string $variety, string $scientific): string {
    $candidate = trim(preg_replace('/\s+/u', ' ', $candidate) ?? $candidate);
    if ($candidate === '') {
        return '';
    }

    $parts = preg_split('/\s*\/\s*/u', $candidate) ?: [];
    $english = '';
    foreach ($parts as $index => $part) {
        $part = trim((string) $part);
        if ($part === '') {
            continue;
        }
        if ($index > 0) {
            $english = $part;
            break;
        }
    }

    if ($english === '' && preg_match('/([A-Z][A-Za-z0-9\-]*(?:\s+[A-Za-z][A-Za-z0-9\-]*)+)$/u', $candidate, $matches)) {
        $tail = trim((string) ($matches[1] ?? ''));
        if ($tail !== '' && mb_strtolower($tail) !== mb_strtolower($main_name) && mb_strtolower($tail) !== mb_strtolower($variety) && mb_strtolower($tail) !== mb_strtolower($scientific)) {
            $english = $tail;
        }
    }

    if ($english !== '' && $variety !== '' && mb_strtolower($english) === mb_strtolower($variety)) {
        $english = '';
    }

    return trim($english);
};

$source_plant_name = $raw_public_name !== '' ? $raw_public_name : ($raw_pot_plant_name !== '' ? $raw_pot_plant_name : $raw_pot_name);
if ($is_generic_catalog_name($source_plant_name)) {
    if ($variety_name !== '') {
        $source_plant_name = $variety_name;
    } elseif ($raw_pot_plant_name !== '') {
        $source_plant_name = $raw_pot_plant_name;
    } elseif ($raw_pot_name !== '') {
        $source_plant_name = $raw_pot_name;
    }
}

if ($raw_public_name !== '' && ! $is_generic_catalog_name($raw_public_name)) {
    $plant_name = $raw_public_name;
} else {
    $plant_name = $normalize_main_plant_name($source_plant_name, $variety_name, $scientific_name);
    if (($plant_name === '' || $is_generic_catalog_name($plant_name)) && $variety_name !== '') {
        $plant_name = $variety_name;
    }
    if (($plant_name === '' || $is_generic_catalog_name($plant_name)) && $scientific_name !== '') {
        $common_name = $common_name_from_scientific($scientific_name);
        if ($common_name !== '') {
            $plant_name = $common_name;
        }
    }
}
if ($plant_name === '') {
    $plant_name = 'Cây chưa xác định';
}
$english_name = $extract_english_plant_name($source_plant_name, $plant_name, $variety_name, $scientific_name);
$sidebar_name = $plant_name;
$subtitle_parts = array_values(array_filter([
    $scientific_name,
    $english_name !== '' ? ('English: ' . $english_name) : '',
    $variety_name !== '' ? ('Dòng: ' . $variety_name) : '',
]));
$subtitle = trim(implode(' — ', $subtitle_parts));
if ($subtitle === '') {
    $subtitle = 'Hồ sơ cây từ dữ liệu onboarding hiện tại';
}

$hero_image = trim((string) ($plant['cover_image_url'] ?? ''));
if ($hero_image === '') {
    $hero_image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuD0D6kzxjKulSjlP-1cXiusGYkZKWol7Kus5wKYapZvswCmM8wREBoZ-ivq60cbYyfXAzLNorywoqwxzwEcNiMFcJFslqXBCCy_CJiwJ1k3-s7IztgY3EDC_Ey_V8m_Rz_-x_woP0o-Ed_IKedd6QKdz0R-UrvU14pXJDzrCpdWiM03DlCvifIWZhFLbvwtpMICSi82xbkYKioCwFniIiS8bWsnKPKmyXMm1KtmOz8fv5JVjgD_9xUtHK95ck5kMWy-N-OX4F35vFnB';
}
$avatar_url = get_avatar_url($current_user instanceof WP_User ? $current_user->ID : 0, ['size' => 96]);
if ($avatar_url === '') {
    $avatar_url = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDfDJ1HZqMaWHxQNVsNZDdXcZ3yRx3XIU-cYfhyXbnwiwNfDl4idIYt_SLS21s2RaGBNYDo3HlszZORGKRU_fQKUkY9_XeglN4Jx30MEbaui8UiXLBHniKAZwiXLVNjR9XdyqVyDmDewy-z6LNHUX5WMv2lzh-iGDP53uoV9lYrD66PwmDyOnrP-Us6j3xvfFRqM4iDOo0pJbWPls7NTAu0uJvm7jAB6hcb66X3Hl_804xhG2l3FMqk2lv0bHDkAuCjFq4QNXgP6Gbb';
}

$overview = trim((string) ($public['short_description'] ?? ''));
if ($overview === '') {
    $overview = trim((string) ($plant['short_description'] ?? ''));
}
if ($overview === '') {
    $overview = 'Hồ sơ thực vật này đang được nối trực tiếp từ dữ liệu onboarding của cây trong khoang hiện tại.';
}
$nutrition_components_text = trim((string) ($plant['nutrition_components'] ?? ''));
if ($nutrition_components_text === '') {
    $nutrition_components_text = 'Đang cập nhật từ hồ sơ cây.';
}
$special_nutrition_components_text = trim((string) ($plant['special_nutrition_components'] ?? ''));
if ($special_nutrition_components_text === '') {
    $special_nutrition_components_text = 'Đang cập nhật từ hồ sơ cây.';
}
$checklist_daily_text = '';
foreach ($checklists as $checklist_item) {
    if ((string) ($checklist_item['checklist_type'] ?? '') === 'daily' && trim((string) ($checklist_item['item_text'] ?? '')) !== '') {
        $checklist_daily_text .= ($checklist_daily_text !== '' ? "\n" : '') . trim((string) $checklist_item['item_text']);
    }
}
if ($checklist_daily_text === '') {
    $checklist_daily_text = 'Chưa có checklist chăm sóc hằng ngày trong hồ sơ onboarding của cây này.';
}
$health_issues_text = '';
foreach ($plant_health_issues as $health_issue) {
    $block = trim((string) ($health_issue['symptom_title'] ?? ''));
    $detail = trim((string) ($health_issue['symptom_detail'] ?? ''));
    if ($detail !== '') {
        $block .= ($block !== '' ? "\n" : '') . $detail;
    }
    if ($block !== '') {
        $health_issues_text .= ($health_issues_text !== '' ? "\n\n" : '') . $block;
    }
}
if ($health_issues_text === '') {
    $health_issues_text = 'Chưa có dữ liệu sâu bệnh và bất thường thường gặp trong hồ sơ onboarding của cây này.';
}
$overview = preg_replace('/\s+/u', ' ', $overview) ?: $overview;
$special_nutrition_components_text = preg_replace('/\s+/u', ' ', $special_nutrition_components_text) ?: $special_nutrition_components_text;

$analysis_summary = trim((string) ($active_pot['latest_analysis_summary'] ?? $active_pot['status_summary'] ?? 'AI đang phân tích tình trạng hiện tại của khoang này.'));
$analysis_actions = ! empty($active_pot['latest_analysis_actions']) && is_array($active_pot['latest_analysis_actions']) ? array_values($active_pot['latest_analysis_actions']) : [];
$analysis_label = trim((string) ($active_pot['latest_analysis_label'] ?? 'Rất Sức Sống'));
$analysis_label = $analysis_label !== '' ? $analysis_label : 'Rất Sức Sống';
$plant_lookup_label = trim((string) ($plant_name !== '' ? $plant_name : $analysis_label));
$plant_lookup_subtitle = trim((string) $scientific_name);
$analysis_level = max(1, min(5, (int) ($active_pot['latest_analysis_level'] ?? 2)));
$health_score = max(58, min(96, 100 - ($analysis_level * 4)));
$score_offset = max(8, 125 - (int) round(($health_score / 100) * 125));

$ai_box_1 = trim((string) ($analysis_summary !== '' ? $analysis_summary : 'Phân tích hình ảnh và tình trạng lá đang ở mức ổn định.'));
$ai_box_2 = trim((string) (($analysis_actions[0] ?? '') !== '' ? $analysis_actions[0] : 'Hệ rễ đang vận hành ổn định theo hồ sơ onboarding và các chỉ số gần nhất.'));
$accuracy = $plant_id > 0 ? '99.8%' : '0%';

$cycle_days = max(24, (int) ($plant['default_cycle_days'] ?? 24));
$germination_days = max(3, (int) ($plant['germination_days'] ?? 3));
$harvest_start = max(11, (int) ($plant['harvest_start_day'] ?? 21));
$harvest_end = max($harvest_start, (int) ($plant['harvest_end_day'] ?? 24));
$progress_width = min(100, max(12.5, round((max(11, $harvest_start - 1) / max(1, $harvest_end)) * 100, 1)));
$current_stage = $analysis_level >= 4 ? 'Cần chú ý' : 'Phát triển';

$growth_stage_items = [];
foreach ($growth_stages as $stage_row) {
    $stage_name = trim((string) ($stage_row['stage_name'] ?? ''));
    if ($stage_name === '') {
        continue;
    }
    $growth_stage_items[] = [
        'name' => $stage_name,
        'index' => (int) ($stage_row['stage_index'] ?? 0),
    ];
}
if ($growth_stage_items === []) {
    $growth_stage_items = [
        ['name' => 'Gieo hạt', 'index' => 1],
        ['name' => 'Nảy mầm', 'index' => 2],
        ['name' => 'Phát triển', 'index' => 3],
        ['name' => 'Thu hoạch', 'index' => 4],
    ];
}
$growth_stage_total = count($growth_stage_items);
$active_stage_position = $growth_stage_total >= 4 ? min($growth_stage_total - 1, max(1, $growth_stage_total - 2)) : min($growth_stage_total, max(1, (int) ceil($growth_stage_total * 0.67)));
$current_stage = (string) ($growth_stage_items[$active_stage_position - 1]['name'] ?? $current_stage);
$progress_width = $growth_stage_total > 1 ? round((($active_stage_position - 1) / ($growth_stage_total - 1)) * 100, 1) : 100;

$light_meta = trim((string) ($active_pot['light'] ?? '850 lux'));
$humidity_meta = trim((string) ($environment['watering_notes'] ?? 'Chu kỳ: Mỗi 12h'));
$nutrition_meta = trim((string) ($nutrition['nutrition_summary'] ?? 'PPM: 1200 (AB Mix)'));
$ph_value = trim((string) ($active_pot['ph'] ?? '6.2'));
$light_score = '80%';
$humidity_score = trim((string) preg_replace('/[^0-9]/', '', (string) ($active_pot['humidity'] ?? '42')));
$humidity_score = ($humidity_score !== '' ? $humidity_score : '42') . '%';
$nutrition_score = '90%';

$sop_rows = [];
$robot_care_lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $robot_care_guide_text))));
foreach (array_slice($robot_care_lines, 0, 6) as $idx => $line) {
    $normalized_line = ltrim($line, "-• ");
    if ($normalized_line === '') {
        continue;
    }
    $parts = array_values(array_filter(array_map('trim', preg_split('/\s*[\|\-–:]\s*/u', $normalized_line))));
    $task = $parts[0] ?? $normalized_line;
    $freq = $parts[1] ?? 'Theo hướng dẫn robot';
    $owner = $parts[2] ?? ($idx % 2 === 0 ? 'Robot chăm sóc' : 'AI Agent');
    $status = $parts[3] ?? 'Đang áp dụng';
    $sop_rows[] = [
        'task' => $task,
        'freq' => $freq,
        'owner' => $owner,
        'status' => $status,
        'dot' => $idx % 2 === 0 ? 'bg-primary' : 'bg-secondary-fixed',
        'chip' => $idx % 2 === 0 ? 'bg-primary/10' : 'bg-secondary-fixed/10',
        'chipText' => $idx % 2 === 0 ? 'text-primary' : 'text-secondary-fixed',
        'icon' => stripos($owner, 'robot') !== false ? 'precision_manufacturing' : (stripos($owner, 'ai') !== false ? 'smart_toy' : 'person'),
        'statusClass' => $idx % 2 === 0 ? 'text-primary font-bold' : 'text-secondary-fixed font-bold opacity-90',
    ];
}
if ($sop_rows === []) {
    foreach (array_slice($checklists, 0, 4) as $idx => $item) {
        if (! is_array($item)) continue;
        $task = trim((string) ($item['item_text'] ?? ''));
        if ($task === '') continue;
        $type = trim((string) ($item['checklist_type'] ?? 'daily'));
        $sop_rows[] = [
            'task' => $task,
            'freq' => $type === 'daily' ? 'Hàng ngày' : ($type === 'weekly' ? 'Hàng tuần' : 'Liên tục'),
            'owner' => $idx === 1 ? 'Chủ vườn' : ($idx === 3 ? 'Camera 4K' : ($idx === 2 ? 'Hệ thống' : 'AI Agent')),
            'status' => $idx === 1 ? 'Chờ xử lý' : ($idx === 0 ? ('Ổn định (' . $ph_value . ')') : ($idx === 3 ? 'Đã đồng bộ' : 'Hoạt động')),
            'dot' => $idx === 1 ? 'bg-secondary-fixed' : 'bg-primary',
            'chip' => $idx === 1 ? 'bg-secondary-fixed/10' : 'bg-white/5',
            'chipText' => $idx === 1 ? 'text-secondary-fixed' : 'text-on-surface-variant',
            'icon' => $idx === 1 ? 'person' : ($idx === 3 ? 'videocam' : ($idx === 2 ? 'settings_input_component' : 'smart_toy')),
            'statusClass' => $idx === 1 ? 'text-secondary-fixed font-bold opacity-70' : 'text-primary font-bold' . ($idx === 0 ? ' text-glow' : ''),
        ];
    }
}
if ($sop_rows === []) {
    $sop_rows = [
        ['task' => 'Cân bằng pH tự động', 'freq' => '4 giờ/lần', 'owner' => 'AI Agent', 'status' => 'Ổn định (' . $ph_value . ')', 'dot' => 'bg-primary', 'chip' => 'bg-primary/10', 'chipText' => 'text-primary', 'icon' => 'smart_toy', 'statusClass' => 'text-primary font-bold text-glow'],
        ['task' => 'Tỉa cành phụ', 'freq' => 'Hàng ngày', 'owner' => 'Chủ vườn', 'status' => 'Chờ xử lý', 'dot' => 'bg-secondary-fixed', 'chip' => 'bg-secondary-fixed/10', 'chipText' => 'text-secondary-fixed', 'icon' => 'person', 'statusClass' => 'text-secondary-fixed font-bold opacity-70'],
        ['task' => 'Bổ sung Vi lượng', 'freq' => 'Liên tục', 'owner' => 'Hệ thống', 'status' => 'Hoạt động', 'dot' => 'bg-primary', 'chip' => 'bg-white/5', 'chipText' => 'text-on-surface-variant', 'icon' => 'settings_input_component', 'statusClass' => 'text-primary font-bold'],
        ['task' => 'Chụp phổ quang nhiệt', 'freq' => '1 giờ/lần', 'owner' => 'Camera 4K', 'status' => 'Đã đồng bộ', 'dot' => 'bg-primary', 'chip' => 'bg-white/5', 'chipText' => 'text-on-surface-variant', 'icon' => 'videocam', 'statusClass' => 'text-primary font-bold'],
    ];
}

$market_price_text = 'Đang cập nhật';
$yield_text = 'Đang cập nhật';
$projected_result_text = 'Đang cập nhật';
$currency_or_placeholder = static function (float $value, string $fallback) : string {
    return $value > 0 ? number_format($value, 0, ',', '.') . 'đ' : $fallback;
};
$input_cost_items = [];
$input_cost_total = 0.0;
foreach ($plant_supplies as $supply_row) {
    $type_label = trim((string) ($supply_row['type'] ?? ''));
    $quantity_text = trim((string) ($supply_row['quantity_text'] ?? ''));
    $spec_text = trim((string) ($supply_row['spec'] ?? ''));
    $unit_text = trim((string) ($supply_row['unit'] ?? ''));
    $cost_value = (float) ($supply_row['cost_price'] ?? 0);
    $value_text = $cost_value > 0 ? number_format($cost_value, 0, ',', '.') . 'đ' . ($unit_text !== '' ? '/' . $unit_text : '') : 'Chưa nhập giá';
    $image_url = esc_url_raw((string) ($supply_row['image_url'] ?? ''));
    $note_parts = array_values(array_filter([
        $type_label !== '' ? $type_label : '',
        $spec_text,
        $quantity_text,
    ]));
    if ($cost_value > 0) {
        $input_cost_total += $cost_value;
    }
    $input_cost_items[] = [
        'label' => trim((string) ($supply_row['name'] ?? 'Vật tư')),
        'value' => $value_text,
        'note' => $note_parts !== [] ? implode(' • ', $note_parts) : 'Vật tư đã khai báo trong hồ sơ onboarding',
        'image_url' => $image_url,
    ];
}
if ($input_cost_items === []) {
    $input_cost_items = [[
        'label' => 'Vật tư đầu vào',
        'value' => 'Đang cập nhật',
        'note' => 'Chưa có danh sách vật tư onboarding cho cây này',
        'image_url' => '',
    ]];
}
$direct_cost_text = $input_cost_total > 0 ? number_format($input_cost_total, 0, ',', '.') . 'đ' : 'Đang cập nhật';
$back_url = add_query_arg(array_filter(['garden' => $garden_key, 'pot' => (string) ($active_pot['code'] ?? '')]), home_url('/portal/dashboard-2/'));
$account_display_name = '';
if ($current_user instanceof WP_User) {
    $account_display_name = trim((string) ($current_user->display_name ?: $current_user->user_login));
}
if ($account_display_name === '') {
    $account_display_name = 'AI trồng cây';
}
$account_initial = mb_strtoupper(mb_substr($account_display_name, 0, 1));
$account_primary_url = $is_logged_in ? home_url('/tai-khoan/') : wp_login_url(home_url('/portal/flower-bio/'));
$account_primary_label = $is_logged_in ? 'Quản lý tài khoản' : 'Đăng nhập';
$shared_top_links = [
    ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
    ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
    ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
];
foreach ($shared_top_links as &$shared_top_link) {
    if ($garden_key !== '' && in_array($shared_top_link['key'], ['kho-nong-cu', 'hang-xom'], true)) {
        $shared_top_link['url'] = add_query_arg('garden', $garden_key, $shared_top_link['url']);
    }
}
unset($shared_top_link);

// Cart payload for flower-bio → kho-nong-cu-2 redirect
$flower_bio_cart_payload = [];
foreach ($plant_supplies as $fb_supply) {
    $fb_name = trim((string) ($fb_supply['name'] ?? ''));
    if ($fb_name === '') {
        continue;
    }
    $fb_price = (float) ($fb_supply['cost_price'] ?? $fb_supply['sale_price'] ?? 0);
    $flower_bio_cart_payload[] = [
        'name'     => $fb_name,
        'price'    => (int) round(max(0, $fb_price)),
        'image'    => (string) ($fb_supply['image_url'] ?? ''),
        'category' => trim((string) ($fb_supply['type'] ?? 'Vật tư')),
        'qty'      => 1,
    ];
}
$flower_bio_warehouse_url = add_query_arg(
    array_filter(['garden' => $garden_key]),
    home_url('/portal/kho-nong-cu-2/')
);
?>
<!DOCTYPE html>
<html class="dark" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Hồ sơ Thực vật - AI Trồng Cây</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400;1,700&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-primary": "#003824",
                        "on-tertiary-fixed": "#321200",
                        "surface": "#121411",
                        "secondary-fixed": "#ffe16d",
                        "inverse-primary": "#006c49",
                        "secondary": "#fff9ef",
                        "on-background": "#e3e3de",
                        "error": "#ffb4ab",
                        "surface-container-low": "#1a1c19",
                        "on-error": "#690005",
                        "tertiary-container": "#d07b46",
                        "tertiary": "#ffb68c",
                        "primary-container": "#31a375",
                        "surface-tint": "#6fdba8",
                        "outline-variant": "#3e4942",
                        "outline": "#87948b",
                        "on-error-container": "#ffdad6",
                        "inverse-on-surface": "#2f312e",
                        "on-tertiary": "#532200",
                        "surface-container": "#1e201d",
                        "primary-fixed-dim": "#6fdba8",
                        "primary-fixed": "#8bf8c3",
                        "surface-dim": "#121411",
                        "on-secondary-fixed-variant": "#544600",
                        "surface-container-highest": "#333532",
                        "on-secondary": "#3a3000",
                        "on-primary-fixed-variant": "#005236",
                        "secondary-container": "#ffdb3c",
                        "surface-container-high": "#292b27",
                        "tertiary-fixed-dim": "#ffb68c",
                        "on-primary-container": "#00311f",
                        "on-secondary-container": "#725f00",
                        "on-primary-fixed": "#002113",
                        "error-container": "#93000a",
                        "on-secondary-fixed": "#221b00",
                        "on-tertiary-fixed-variant": "#753401",
                        "on-tertiary-container": "#481d00",
                        "inverse-surface": "#e3e3de",
                        "background": "#121411",
                        "surface-container-lowest": "#0d0f0c",
                        "surface-bright": "#383a36",
                        "tertiary-fixed": "#ffdbc9",
                        "primary": "#6fdba8",
                        "on-surface": "#e3e3de",
                        "surface-variant": "#333532",
                        "on-surface-variant": "#bdcac0"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Noto Serif"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    "animation": {
                        "pulse-slow": "pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                        "float": "float 6s ease-in-out infinite",
                        "glow": "glow 3s ease-in-out infinite",
                        "scan": "scan 2.5s linear infinite"
                    },
                    "keyframes": {
                        "float": {
                            "0%, 100%": { transform: "translateY(0)" },
                            "50%": { transform: "translateY(-10px)" }
                        },
                        "glow": {
                            "0%, 100%": { "box-shadow": "0 0 5px rgba(111, 219, 168, 0.2), 0 0 10px rgba(111, 219, 168, 0.1)" },
                            "50%": { "box-shadow": "0 0 20px rgba(111, 219, 168, 0.6), 0 0 30px rgba(111, 219, 168, 0.3)" }
                        },
                        "scan": {
                            "0%": { top: "-10%" },
                            "100%": { top: "110%" }
                        }
                    }
                }
            }
        }
    </script>
<style>
        body { font-family: 'Manrope', sans-serif; }
        .font-noto-serif { font-family: 'Noto Serif', serif; }
        .font-manrope { font-family: 'Manrope', sans-serif; }
        .glass-panel {
            background: rgba(30, 32, 29, 0.6);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .glass-panel:hover {
            border-color: rgba(111, 219, 168, 0.3);
            box-shadow: 0 0 20px rgba(111, 219, 168, 0.1);
        }
        .growth-gradient {
            background: linear-gradient(135deg, #31A375, #6FDBA8, #8BF8C3);
        }
        .hologram-glow {
            box-shadow: 0 0 30px rgba(111, 219, 168, 0.2) inset;
            border: 1px solid rgba(111, 219, 168, 0.3);
            background: linear-gradient(180deg, rgba(111, 219, 168, 0.05) 0%, rgba(111, 219, 168, 0.1) 100%);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .flower-scroll-hidden {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .flower-scroll-hidden::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
        .text-glow {
            text-shadow: 0 0 10px rgba(111, 219, 168, 0.5);
        }
        .chart-path {
            stroke-dasharray: 175;
            stroke-dashoffset: 175;
            animation: chart-draw 2s ease-out forwards;
        }
        @keyframes chart-draw {
            to { stroke-dashoffset: var(--offset); }
        }
        html, body {
            background: #0d0f0c !important;
        }
        .site-header, .site-footer, .footer, .floating-ai-chat {
            display: none !important;
        }
        main > .section,
        main > .section > .container,
        main article.page,
        main article.page > .entry-content {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
        }
        .flower-eco-top{position:sticky;top:0;z-index:50;display:flex;justify-content:space-between;align-items:center;gap:18px;padding:18px 28px;background:linear-gradient(180deg, rgba(7,33,24,.82), rgba(7,33,24,.34));backdrop-filter:blur(24px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
        .flower-eco-top-left{display:flex;align-items:center;gap:22px;min-width:0;flex:1}
        .flower-eco-top-title{font-family:'Noto Serif',serif;font-size:34px;font-style:italic;color:var(--primary);letter-spacing:-.03em;white-space:nowrap}
        .flower-eco-top-title a{color:inherit}
        .flower-eco-top-title-brand{display:inline-flex;align-items:baseline;gap:8px;font-style:normal;letter-spacing:-.03em}
        .flower-eco-top-title-brand .brand-ai{color:#6fdba8;font-weight:900}.flower-eco-top-title-brand .brand-rest{color:#ffffff;font-weight:700}
        .flower-eco-top-links{display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin-left:auto}.flower-eco-top-links a{color:#e3e3de;transition:color .18s ease}.flower-eco-top-links a:hover{color:#6fdba8}
        .flower-eco-top-right{display:flex;gap:14px;align-items:center;position:relative}.flower-eco-top-icon,.flower-eco-top-avatar{width:42px;height:42px;border-radius:999px;display:grid;place-items:center}.flower-eco-top-icon{background:rgba(41,43,39,.56);color:var(--primary)}.flower-eco-top-avatar{border:2px solid rgba(111,219,168,.3);background:#1a1c19}
        .flower-account-menu{position:relative}
        .flower-account-menu summary{list-style:none;cursor:pointer}
        .flower-account-menu summary::-webkit-details-marker{display:none}
        .flower-account-panel{position:absolute;top:54px;right:0;min-width:220px;padding:10px;border-radius:20px;background:rgba(26,28,25,.96);border:1px solid rgba(255,255,255,.06);box-shadow:0 24px 52px rgba(0,0,0,.28);z-index:80}
        .flower-account-link{display:block;padding:12px 14px;border-radius:14px;color:#e3e3de;text-decoration:none}
        .flower-account-link:hover{background:rgba(51,53,50,.56)}
        .flower-account-link.danger{color:#ffb4ab}
        @media (max-width:820px){.flower-eco-top{padding:14px 16px;flex-wrap:wrap}.flower-eco-top-left{width:100%;flex-wrap:wrap}.flower-eco-top-title{font-size:28px}.flower-eco-top-links{width:100%;overflow:auto;padding-bottom:4px;flex-wrap:nowrap}}
    </style>
</head>
<body class="bg-[#0d0f0c] text-on-surface min-h-screen overflow-x-hidden">
<script>
    document.documentElement.classList.add('dark');
    document.body.classList.add('bg-[#0d0f0c]', 'text-on-surface', 'min-h-screen', 'overflow-x-hidden');
    document.body.style.background = '#0d0f0c';
    document.body.style.margin = '0';
</script>
<div class="bg-[#0d0f0c] text-on-surface min-h-screen overflow-x-hidden">
<header class="flower-eco-top">
<div class="flower-eco-top-left">
<div class="flower-eco-top-title"><a href="<?php echo esc_url($back_url); ?>"><span class="flower-eco-top-title-brand"><span class="brand-ai">AI</span><span class="brand-rest">trồng cây</span></span></a></div>
<nav class="flower-eco-top-links" aria-label="Điều hướng nhanh">
<?php foreach ($shared_top_links as $top_link) : ?>
<a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
<?php endforeach; ?>
</nav>
</div>
<div class="flower-eco-top-right">
<div class="flower-eco-top-icon" aria-label="Thông báo">🔔</div>
<div class="flower-eco-top-icon" aria-label="Cài đặt">⚙️</div>
<details class="flower-account-menu">
<summary class="flower-eco-top-avatar" aria-haspopup="menu" aria-label="Tài khoản">
<?php echo esc_html($account_initial); ?>
</summary>
<div class="flower-account-panel" role="menu">
<a class="flower-account-link" href="<?php echo esc_url($account_primary_url); ?>"><?php echo esc_html($account_primary_label); ?></a>
<?php if ($is_logged_in) : ?>
<a class="flower-account-link danger" href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a>
<?php endif; ?>
</div>
</details>
</div>
</header>
<main class="pt-8 px-5 pb-12 lg:px-8 xl:px-10">
<div class="max-w-[1480px] mx-auto grid grid-cols-12 gap-6 xl:gap-7">
<div class="col-span-12 lg:col-span-8 space-y-4">
<form class="glass-panel rounded-2xl p-4 flex items-center gap-4 animate-glow" method="get" action="<?php echo esc_url(home_url('/portal/flower-bio/')); ?>">
<?php if ($garden_key !== '') : ?>
<input type="hidden" name="garden" value="<?php echo esc_attr($garden_key); ?>"/>
<?php endif; ?>
<?php if ($active_pot_code !== '') : ?>
<input type="hidden" name="pot" value="<?php echo esc_attr($active_pot_code); ?>"/>
<?php endif; ?>
<div class="w-12 h-12 rounded-full border-4 border-primary/30 flex items-center justify-center relative shrink-0">
<svg class="absolute inset-0 w-full h-full -rotate-90">
<circle class="opacity-80" cx="24" cy="24" fill="transparent" r="20" stroke="#6FDBA8" stroke-dasharray="125" stroke-dashoffset="<?php echo esc_attr((string) $score_offset); ?>" stroke-width="4"></circle>
</svg>
<span class="material-symbols-outlined text-primary text-xl">search</span>
</div>
<div class="min-w-0 flex-1">
<p class="text-[10px] uppercase tracking-wider text-primary/60 font-bold">Tìm cây</p>
<input class="mt-1 w-full bg-transparent text-lg font-bold leading-none text-white placeholder:text-primary/40 outline-none" type="text" name="plant_search" value="<?php echo esc_attr($plant_search_query); ?>" placeholder="Nhập tên cây cần tìm..."/>
<?php if ($selected_plant_id > 0 && $plant_lookup_subtitle !== '') : ?>
<p class="text-xs text-secondary-fixed font-medium italic opacity-80 truncate mt-1"><?php echo esc_html($plant_lookup_subtitle); ?></p>
<?php endif; ?>
</div>
<button class="shrink-0 px-4 py-2 rounded-xl bg-primary/15 border border-primary/25 text-primary font-bold hover:bg-primary/20 transition-colors" type="submit">Tìm</button>
</form>
<?php if ($plant_search_query !== '' && $selected_plant_id <= 0) : ?>
<div class="glass-panel rounded-[1.5rem] p-3 border border-white/8 bg-surface-container-high/40">
<?php if ($plant_search_results !== []) : ?>
<div class="space-y-2">
<?php foreach ($plant_search_results as $search_item) : ?>
<?php $search_item_id = (int) ($search_item['id'] ?? 0); ?>
<a class="flex items-center justify-between gap-4 rounded-2xl px-4 py-3 bg-white/5 hover:bg-primary/10 border border-white/6 transition-colors" href="<?php echo esc_url(add_query_arg(array_filter(['garden' => $garden_key, 'pot' => $active_pot_code, 'selected_plant_id' => $search_item_id]), home_url('/portal/flower-bio/'))); ?>">
<div class="min-w-0">
<p class="text-base font-bold text-white truncate"><?php echo esc_html((string) ($search_item['public_name'] ?? 'Cây')); ?></p>
<p class="text-xs text-secondary-fixed italic truncate"><?php echo esc_html((string) ($search_item['scientific_name'] ?? '')); ?></p>
</div>
<span class="text-xs font-bold text-primary">Chọn</span>
</a>
<?php endforeach; ?>
</div>
<?php else : ?>
<div class="px-4 py-3 text-sm text-on-surface-variant">Không tìm thấy cây nào trong danh sách onboarding.</div>
<?php endif; ?>
</div>
<?php endif; ?>
</div>
<section class="col-span-12 lg:col-span-8 glass-panel rounded-[2.5rem] p-7 lg:p-8 flex flex-col md:flex-row items-center gap-8 lg:gap-10 relative overflow-hidden group">
<div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/20 blur-[120px] rounded-full group-hover:bg-primary/30 transition-colors duration-700"></div>
<div class="w-full md:w-5/12 relative flex items-center">
<div class="aspect-square rounded-3xl overflow-hidden bg-gradient-to-b from-surface-container-high to-surface-container shadow-2xl animate-float">
<img alt="<?php echo esc_attr($plant_name); ?>" class="w-full h-full object-cover scale-110 group-hover:scale-125 transition-transform duration-1000" src="<?php echo esc_url($hero_image); ?>"/>
</div>
</div>
<div class="w-full md:w-7/12 flex flex-col justify-center">
<div class="flex flex-wrap gap-3 mb-6">
<span class="px-4 py-1 rounded-full bg-secondary-container/20 text-secondary-fixed text-xs font-bold border border-secondary-container/30">Hạng Thương gia</span>
<span class="px-4 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold border border-primary/20">Mức độ: <?php echo esc_html((string) ($plant['difficulty_level'] ?? 'Đang cập nhật')); ?></span>
<span class="px-4 py-1 rounded-full bg-white/5 text-on-surface-variant text-xs font-bold">Thu hoạch: <?php echo esc_html((string) $harvest_end); ?> Ngày</span>
</div>
<h3 class="text-5xl font-noto-serif font-bold mb-2 tracking-tight text-on-surface"><?php echo esc_html($plant_name); ?></h3>
<p class="text-secondary-fixed font-medium italic opacity-80 mb-6"><?php echo esc_html($subtitle); ?></p>
<p class="text-on-surface-variant leading-relaxed mb-8 text-lg">
                    <?php echo esc_html($overview); ?>
                </p>
</div>
</section>
<div class="col-span-12 lg:col-span-4 space-y-5 lg:space-y-6 self-start">
<?php if ($is_logged_in) : ?>
<button type="button" onclick="aitrStartGrowing()" class="w-full py-3 bg-primary-container text-on-primary-container rounded-xl font-bold hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/10">
<span class="material-symbols-outlined text-sm">add</span> Bắt đầu trồng
</button>
<?php else : ?>
<a href="<?php echo esc_url($guest_start_url); ?>" class="w-full py-3 bg-primary-container text-on-primary-container rounded-xl font-bold hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/10">
<span class="material-symbols-outlined text-sm">add</span> Đăng nhập để bắt đầu trồng
</a>
<p class="mt-3 text-sm text-on-surface-variant text-center">Đăng nhập để lưu cây đã chọn và bắt đầu hành trình trồng.</p>
<?php endif; ?>
<aside class="glass-panel rounded-[2.5rem] p-8 hologram-glow relative overflow-hidden group">
<div class="absolute left-0 w-full h-1 bg-primary/40 blur-[2px] animate-scan z-10 pointer-events-none"></div>
<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(#6fdba8 1px, transparent 1px), linear-gradient(90deg, #6fdba8 1px, transparent 1px); background-size: 20px 20px;"></div>
<div class="flex items-center gap-4 mb-8 relative z-20">
<div class="w-14 h-14 rounded-2xl bg-primary/20 flex items-center justify-center border border-primary/40 animate-pulse">
<span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">psychology</span>
</div>
<div>
<h4 class="font-bold text-xl text-glow">Phân tích từ AI</h4>
<p class="text-xs text-primary/60 font-mono tracking-widest uppercase">Live Analysis Active</p>
</div>
</div>
<div class="space-y-5 relative z-20">
<div class="bg-primary/5 p-5 rounded-[1.5rem] border border-primary/20 group-hover:bg-primary/10 transition-colors">
<div class="flex justify-between items-center mb-2">
<p class="text-sm font-bold text-primary">Dinh dưỡng trong 100gr</p>
<span class="text-[10px] bg-primary/20 px-2 py-0.5 rounded text-primary font-mono">OPTIMAL</span>
</div>
<div class="flower-scroll-hidden text-xs text-on-surface-variant leading-relaxed italic whitespace-pre-line overflow-y-auto pr-1" style="max-height:calc(1.6em * 10);"><?php echo esc_html($nutrition_components_text); ?></div>
</div>
<div class="bg-white/5 p-5 rounded-[1.5rem] border border-white/10 group-hover:bg-white/10 transition-colors">
<div class="flex justify-between items-center mb-2">
<p class="text-sm font-bold text-secondary-fixed">Đặc biệt</p>
<span class="text-[10px] bg-secondary-fixed/20 px-2 py-0.5 rounded text-secondary-fixed font-mono">ACTIVE</span>
</div>
<p class="text-xs text-on-surface-variant leading-relaxed italic"><?php echo esc_html($special_nutrition_components_text); ?></p>
</div>
<div class="pt-4">
<div class="flex justify-between text-xs font-bold mb-3 px-1">
<span class="text-primary/70 uppercase tracking-tighter">Độ chính xác của AI</span>
<span class="text-primary text-glow"><?php echo esc_html($accuracy); ?></span>
</div>
<div class="w-full bg-surface-container-highest h-2 rounded-full overflow-hidden">
<div class="bg-primary h-full rounded-full shadow-[0_0_10px_#6FDBA8] transition-all duration-1000" style="width: <?php echo esc_attr($accuracy); ?>"></div>
</div>
</div>
</div>
</aside>
</div>
<section class="col-span-12 glass-panel rounded-[2.5rem] p-7 lg:p-10 relative overflow-hidden">
<div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-4">
<h3 class="text-3xl font-noto-serif font-bold text-glow">Hành trình Tăng trưởng</h3>
<div class="flex items-center gap-3 bg-primary/10 px-5 py-2 rounded-full border border-primary/20">
<div class="w-2 h-2 rounded-full bg-primary animate-ping"></div>
<span class="text-sm font-bold">Giai đoạn hiện tại: <span class="text-primary text-glow"><?php echo esc_html($current_stage); ?></span></span>
</div>
</div>
<div class="relative flex justify-between items-start mt-8">
<div class="absolute top-7 left-0 w-full h-1 bg-surface-container-highest -z-10 rounded-full"></div>
<div class="absolute top-7 left-0 h-1 growth-gradient -z-10 rounded-full shadow-[0_0_15px_rgba(111,219,168,0.5)] transition-all duration-1000" style="width: <?php echo esc_attr((string) $progress_width); ?>%"></div>
<?php foreach ($growth_stage_items as $stage_loop_index => $stage_item) : ?>
<?php
  $position = $stage_loop_index + 1;
  $is_active_stage = $position === $active_stage_position;
  $is_completed_stage = $position < $active_stage_position;
  $is_future_stage = $position > $active_stage_position;
  $item_width = 'width:' . number_format(100 / max(1, $growth_stage_total), 4, '.', '') . '%';
  $icon_name = $position === 1 ? 'grain' : ($position === $growth_stage_total ? 'nutrition' : ($is_active_stage ? 'nature' : 'potted_plant'));
  $wrapper_class = $is_future_stage ? 'flex flex-col items-center text-center group cursor-pointer opacity-50 grayscale hover:grayscale-0 hover:opacity-100 transition-all' : 'flex flex-col items-center text-center group cursor-pointer';
  $circle_class = $is_active_stage
    ? 'w-14 h-14 rounded-full bg-[#1e201d] flex items-center justify-center mb-4 ring-8 ring-[#0d0f0c] shadow-[0_0_25px_rgba(111,219,168,0.6)] border-2 border-primary group-hover:scale-110 transition-transform relative'
    : (($is_completed_stage || $position === 1) ? 'w-14 h-14 rounded-full bg-primary flex items-center justify-center mb-4 ring-8 ring-[#0d0f0c] shadow-lg group-hover:scale-110 transition-transform' : 'w-14 h-14 rounded-full bg-surface-container-highest flex items-center justify-center mb-4 ring-8 ring-[#0d0f0c] group-hover:scale-110 transition-transform');
  $icon_class = $is_active_stage ? 'material-symbols-outlined text-primary text-2xl' : (($is_completed_stage || $position === 1) ? 'material-symbols-outlined text-on-primary text-2xl' : 'material-symbols-outlined text-on-surface-variant text-2xl');
  $title_class = $is_active_stage ? 'font-bold text-sm mb-1 text-primary text-glow' : 'font-bold text-sm mb-1';
  $meta_class = $is_active_stage ? 'text-[11px] text-primary/70 font-bold' : 'text-[11px] text-on-surface-variant font-medium';
?>
<div class="<?php echo esc_attr($wrapper_class); ?>" style="<?php echo esc_attr($item_width); ?>">
<div class="<?php echo esc_attr($circle_class); ?>">
<span class="<?php echo esc_attr($icon_class); ?>" style="font-variation-settings: 'FILL' 1;"><?php echo esc_html($icon_name); ?></span>
<?php if ($is_active_stage) : ?><div class="absolute -inset-2 rounded-full border border-primary/30 animate-ping"></div><?php endif; ?>
</div>
<p class="<?php echo esc_attr($title_class); ?>"><?php echo esc_html((string) $stage_item['name']); ?></p>
<p class="<?php echo esc_attr($meta_class); ?>">Giai đoạn <?php echo esc_html((string) $position); ?>/<?php echo esc_html((string) $growth_stage_total); ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<section class="col-span-12 lg:col-span-4 glass-panel rounded-[2.5rem] p-7 lg:p-8 self-start">
<h3 class="text-2xl font-noto-serif font-bold mb-8 flex items-center gap-3">
                Chỉ số Sinh mệnh 
                <span class="text-[10px] font-mono text-primary animate-pulse">● LIVE</span>
</h3>
<div class="space-y-10">
<div class="flex items-center gap-6 group">
<div class="relative w-20 h-20 flex items-center justify-center">
<svg class="w-full h-full transform -rotate-90">
<circle class="text-surface-container-highest" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-width="4"></circle>
<circle class="text-secondary-fixed chart-path" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-linecap="round" stroke-width="7" style="--offset: 35;"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span class="text-sm font-black text-secondary-fixed"><?php echo esc_html($light_score); ?></span>
</div>
</div>
<div class="flex-1">
<p class="font-bold text-lg mb-0.5 group-hover:text-secondary-fixed transition-colors">Ánh sáng</p>
<p class="text-xs text-on-surface-variant font-medium"><?php echo esc_html($light_meta); ?> <span class="text-secondary-fixed/70 ml-1">(Tối ưu)</span></p>
</div>
<span class="material-symbols-outlined text-secondary-fixed text-3xl group-hover:rotate-45 transition-transform">light_mode</span>
</div>
<div class="flex items-center gap-6 group">
<div class="relative w-20 h-20 flex items-center justify-center">
<svg class="w-full h-full transform -rotate-90">
<circle class="text-surface-container-highest" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-width="4"></circle>
<circle class="text-primary chart-path" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-linecap="round" stroke-width="7" style="--offset: 101;"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span class="text-sm font-black text-primary"><?php echo esc_html($humidity_score); ?></span>
</div>
</div>
<div class="flex-1">
<p class="font-bold text-lg mb-0.5 group-hover:text-primary transition-colors">Độ ẩm rễ</p>
<p class="text-xs text-on-surface-variant font-medium"><?php echo esc_html($humidity_meta); ?></p>
</div>
<span class="material-symbols-outlined text-primary text-3xl animate-bounce">water_drop</span>
</div>
<div class="flex items-center gap-6 group">
<div class="relative w-20 h-20 flex items-center justify-center">
<svg class="w-full h-full transform -rotate-90">
<circle class="text-surface-container-highest" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-width="4"></circle>
<circle class="text-tertiary-container chart-path" cx="40" cy="40" fill="transparent" r="34" stroke="currentColor" stroke-linecap="round" stroke-width="7" style="--offset: 17;"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span class="text-sm font-black text-tertiary-container"><?php echo esc_html($nutrition_score); ?></span>
</div>
</div>
<div class="flex-1">
<p class="font-bold text-lg mb-0.5 group-hover:text-tertiary-container transition-colors">Dinh dưỡng</p>
<p class="text-xs text-on-surface-variant font-medium"><?php echo esc_html($nutrition_meta); ?></p>
</div>
<span class="material-symbols-outlined text-tertiary-container text-3xl group-hover:scale-125 transition-transform">science</span>
</div>
</div>
</section>
<section class="col-span-12 lg:col-span-8 glass-panel rounded-[2.5rem] p-7 lg:p-8 hologram-glow relative overflow-hidden group">
<div class="absolute left-0 w-full h-1 bg-primary/40 blur-[2px] animate-scan z-10 pointer-events-none"></div>
<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(#6fdba8 1px, transparent 1px), linear-gradient(90deg, #6fdba8 1px, transparent 1px); background-size: 20px 20px;"></div>
<div class="flex justify-between items-center mb-8 relative z-20">
<h3 class="text-2xl font-noto-serif font-bold">Hướng dẫn trồng và chăm sóc cây</h3>
<span class="text-xs bg-primary/20 text-primary border border-primary/30 px-4 py-1.5 rounded-full font-bold uppercase tracking-widest">Robot Care Guide</span>
</div>
<div class="rounded-[2rem] bg-white/5 border border-white/8 p-6 md:p-8 relative z-20 group-hover:bg-white/10 transition-colors">
<?php if ($robot_care_guide_text !== '') : ?>
<div class="text-on-surface-variant leading-8 text-[15px] whitespace-pre-line"><?php echo esc_html($robot_care_guide_text); ?></div>
<?php else : ?>
<div class="text-on-surface-variant leading-8 text-[15px]">Chưa có nội dung hướng dẫn trồng và chăm sóc cây dành cho robot trong hồ sơ onboarding của cây này.</div>
<?php endif; ?>
</div>
</section>
<section class="col-span-12 glass-panel rounded-[2.5rem] p-7 lg:p-10 bg-gradient-to-br from-surface-container-low via-surface-container-highest to-surface-container-low relative overflow-hidden">
<div class="absolute inset-0 bg-primary/5 opacity-40 pointer-events-none"></div>
<h3 class="text-2xl font-noto-serif font-bold mb-10 relative z-10">Dự kiến thu hoạch</h3>
<div class="relative z-10 space-y-8">
<div class="rounded-[2rem] border border-white/8 bg-surface-container-lowest/45 backdrop-blur-md p-8 shadow-xl">
<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
<div>
<p class="text-xs font-bold uppercase tracking-[0.28em] text-primary/80 mb-2">Chi phí đầu vào</p>
</div>
<p class="text-sm text-on-surface-variant md:text-right">Tổng chi phí trực tiếp / khoang: <span class="text-white font-extrabold"><?php echo esc_html($direct_cost_text); ?></span></p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
<?php foreach ($input_cost_items as $item) : ?>
<div class="rounded-3xl border border-white/6 bg-surface-container-high/45 p-6">
<div class="w-full aspect-[4/3] rounded-2xl overflow-hidden bg-surface-container mb-4">
<?php if (! empty($item['image_url'])) : ?>
<img class="w-full h-full object-cover" src="<?php echo esc_url($item['image_url']); ?>" alt="<?php echo esc_attr($item['label']); ?>" loading="lazy"/>
<?php else : ?>
<div class="w-full h-full flex items-center justify-center text-center px-4 text-sm text-on-surface-variant">Chưa có ảnh vật tư</div>
<?php endif; ?>
</div>
<p class="text-sm font-bold uppercase tracking-widest text-on-surface-variant mb-2"><?php echo esc_html($item['label']); ?></p>
<p class="text-2xl font-black text-white mb-2"><?php echo esc_html($item['value']); ?></p>
<p class="text-sm text-on-surface-variant leading-relaxed"><?php echo esc_html($item['note']); ?></p>
</div>
<?php endforeach; ?>
</div>
</div>
<div class="rounded-[2rem] border border-white/8 bg-surface-container-lowest/45 backdrop-blur-md p-8 shadow-xl">
<div class="mb-6">
<p class="text-xs font-bold uppercase tracking-[0.28em] text-secondary-fixed/80 mb-2">Thu hoạch</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-10">
<div class="flex flex-col items-center p-8 bg-surface-container-lowest/50 backdrop-blur-md rounded-3xl border border-white/5 hover:-translate-y-2 transition-transform shadow-xl">
<div class="w-20 h-20 rounded-2xl bg-secondary-fixed/10 flex items-center justify-center mb-6 border border-secondary-fixed/20">
<span class="material-symbols-outlined text-secondary-fixed text-5xl">payments</span>
</div>
<p class="text-on-surface-variant text-sm font-bold uppercase tracking-widest mb-2">Giá thị trường</p>
<p class="text-4xl font-black text-white"><?php echo esc_html($market_price_text); ?><span class="text-sm font-normal text-on-surface-variant ml-1">/kg</span></p>
</div>
<div class="flex flex-col items-center p-8 bg-surface-container-lowest/50 backdrop-blur-md rounded-3xl border border-white/5 hover:-translate-y-2 transition-transform shadow-xl">
<div class="w-20 h-20 rounded-2xl bg-primary/10 flex items-center justify-center mb-6 border border-primary/20">
<span class="material-symbols-outlined text-primary text-5xl">inventory_2</span>
</div>
<p class="text-on-surface-variant text-sm font-bold uppercase tracking-widest mb-2">Sản lượng khoang</p>
<p class="text-4xl font-black text-white"><?php echo esc_html($yield_text); ?><span class="text-sm font-normal text-on-surface-variant ml-1">kg</span></p>
</div>
<div class="flex flex-col items-center p-8 bg-surface-container-lowest/50 backdrop-blur-md rounded-3xl border border-white/5 hover:-translate-y-2 transition-transform shadow-xl">
<div class="w-20 h-20 rounded-2xl bg-tertiary-container/10 flex items-center justify-center mb-6 border border-tertiary-container/20">
<span class="material-symbols-outlined text-tertiary-container text-5xl">trending_up</span>
</div>
<p class="text-on-surface-variant text-sm font-bold uppercase tracking-widest mb-2">Dự kiến kết quả</p>
<p class="text-4xl font-black text-tertiary-container text-glow"><?php echo esc_html($projected_result_text); ?></p>
</div>
</div>
</div>
</div>
</section>
<section class="col-span-12 grid grid-cols-1 gap-5 lg:gap-6">
<div class="glass-panel rounded-[2.5rem] p-8 hologram-glow relative overflow-hidden group">
<div class="absolute left-0 w-full h-1 bg-primary/40 blur-[2px] animate-scan z-10 pointer-events-none"></div>
<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(#6fdba8 1px, transparent 1px), linear-gradient(90deg, #6fdba8 1px, transparent 1px); background-size: 20px 20px;"></div>
<div class="relative z-20">
<div class="flex justify-between items-center mb-6">
<h3 class="text-2xl font-noto-serif font-bold">Checklist chăm sóc hằng ngày</h3>
<span class="text-xs bg-primary/20 text-primary border border-primary/30 px-4 py-1.5 rounded-full font-bold uppercase tracking-widest">Daily checklist</span>
</div>
<div class="rounded-[2rem] bg-white/5 border border-white/8 p-6 md:p-8 group-hover:bg-white/10 transition-colors">
<div class="text-on-surface-variant leading-8 text-[15px] whitespace-pre-line"><?php echo esc_html($checklist_daily_text); ?></div>
</div>
</div>
</div>
<div class="glass-panel rounded-[2.5rem] p-8 hologram-glow relative overflow-hidden group">
<div class="absolute left-0 w-full h-1 bg-primary/40 blur-[2px] animate-scan z-10 pointer-events-none"></div>
<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: linear-gradient(#6fdba8 1px, transparent 1px), linear-gradient(90deg, #6fdba8 1px, transparent 1px); background-size: 20px 20px;"></div>
<div class="relative z-20">
<div class="flex justify-between items-center mb-6">
<h3 class="text-2xl font-noto-serif font-bold">Sâu bệnh bất thường thường gặp</h3>
<span class="text-xs bg-primary/20 text-primary border border-primary/30 px-4 py-1.5 rounded-full font-bold uppercase tracking-widest">Health issues</span>
</div>
<div class="rounded-[2rem] bg-white/5 border border-white/8 p-6 md:p-8 group-hover:bg-white/10 transition-colors">
<div class="text-on-surface-variant leading-8 text-[15px] whitespace-pre-line"><?php echo esc_html($health_issues_text); ?></div>
</div>
<?php if ($is_logged_in) : ?>
<button type="button" onclick="aitrStartGrowing()" class="mt-6 w-full py-3 bg-primary-container text-on-primary-container rounded-xl font-bold hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/10">
<span class="material-symbols-outlined text-sm">shopping_cart</span> Thêm vào giỏ &amp; bắt đầu trồng
</button>
<?php else : ?>
<a href="<?php echo esc_url($guest_start_url); ?>" class="mt-6 w-full py-3 bg-primary-container text-on-primary-container rounded-xl font-bold hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/10">
<span class="material-symbols-outlined text-sm">add</span> Đăng nhập để bắt đầu trồng
</a>
<p class="mt-3 text-sm text-on-surface-variant text-center">Anh có thể xem flower-bio ở chế độ khách, nhưng cần đăng nhập khi muốn bắt đầu trồng thật.</p>
<?php endif; ?>
</div>
</div>
</section>
</div>
<footer class="col-span-12 mt-14 lg:mt-18 pt-10 border-t border-outline-variant/10 flex flex-col md:flex-row justify-between items-center gap-8 px-2 lg:px-4">
<div class="flex flex-col gap-3">
<h1 class="text-3xl font-serif italic text-primary">AI Trồng Cây</h1>
<p class="text-sm text-on-surface-variant font-medium">Nền tảng nông nghiệp kỹ thuật số đỉnh cao, tối ưu bởi trí tuệ nhân tạo.</p>
</div>
<div class="flex flex-wrap justify-center gap-8 text-sm font-bold text-on-surface-variant/80">
<a class="hover:text-primary transition-colors" href="#">Về chúng tôi</a>
<a class="hover:text-primary transition-colors" href="#">Công nghệ AI</a>
<a class="hover:text-primary transition-colors" href="#">Đối tác</a>
<a class="hover:text-primary transition-colors" href="#">Chính sách</a>
</div>
<div class="flex gap-4">
<div class="w-12 h-12 rounded-2xl bg-surface-container-high border border-white/5 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary/50 cursor-pointer transition-all hover:scale-110">
<span class="material-symbols-outlined text-xl">share</span>
</div>
<div class="w-12 h-12 rounded-2xl bg-surface-container-high border border-white/5 flex items-center justify-center text-on-surface-variant hover:text-primary hover:border-primary/50 cursor-pointer transition-all hover:scale-110">
<span class="material-symbols-outlined text-xl">mail</span>
</div>
</div>
</footer>
</main>
</div>
<script>
(function () {
    var payload = <?php echo wp_json_encode([
        'items'    => $flower_bio_cart_payload,
        'plant'    => $plant_name,
        'plant_id' => $plant_id,
    ]); ?>;
    var warehouseUrl = <?php echo wp_json_encode($flower_bio_warehouse_url); ?>;

    window.aitrStartGrowing = function () {
        try {
            var merged = {};
            try {
                var oldRaw = localStorage.getItem('aitr_cart');
                if (oldRaw) {
                    var oldData = JSON.parse(oldRaw);
                    if (oldData && Array.isArray(oldData.items)) {
                        oldData.items.forEach(function (it) { if (it.name) { merged[it.name] = it; } });
                    }
                }
            } catch (e2) {}
            payload.items.forEach(function (it) {
                if (!it.name) { return; }
                if (merged[it.name]) {
                    merged[it.name].qty = 1;
                } else {
                    merged[it.name] = { name: it.name, price: it.price, qty: 1, image: it.image || '', category: it.category || 'Vat tu' };
                }
            });
            var mergedArr = [];
            var keys = Object.keys(merged);
            for (var i = 0; i < keys.length; i++) { mergedArr.push(merged[keys[i]]); }
            localStorage.setItem('aitr_cart', JSON.stringify({ items: mergedArr, plant: payload.plant, plant_id: payload.plant_id, ts: Date.now() }));
        } catch (e) {}
        window.location.href = warehouseUrl;
    };
}());
</script>
</body></html>
