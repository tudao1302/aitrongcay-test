<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$garden_key = sanitize_text_field((string) ($_GET['garden'] ?? ''));
if ($garden_key === '' && function_exists('aitrongcay_market_context_garden_key')) {
    $garden_key = aitrongcay_market_context_garden_key();
}

$profile = function_exists('aitrongcay_portal_profile_for_garden_context') ? aitrongcay_portal_profile_for_garden_context($garden_key, wp_get_current_user()) : [];
$garden_name = trim((string) ($profile['garden_name'] ?? 'The Observatory'));
$dashboard_url = add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/dashboard-2/'));
$market_url = add_query_arg(array_filter(['garden' => $garden_key]), home_url('/cho-que/'));
$warehouse_url = add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/kho-nong-cu-2/'));
$friends_url = add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/hang-xom/'));
$shared_top_links = [
    ['key' => 'doi-diem', 'label' => 'Đổi điểm', 'url' => home_url('/portal/doi-diem/')],
    ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
    ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
    ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
    ['key' => 'dashboard-2', 'label' => 'Vào khu vườn của tôi', 'url' => home_url('/portal/dashboard-2/')],
];
$can_manage_catalog = function_exists('aitrongcay_can_manage_onboarding_catalog') ? aitrongcay_can_manage_onboarding_catalog(wp_get_current_user()) : current_user_can('manage_options');
$catalog_access_state = isset($_GET['catalog_access']) ? sanitize_key((string) ($_GET['catalog_access'] ?? '')) : '';
$new_plant_url = home_url('/portal/onboarding-cay-moi/');
$new_supply_url = home_url('/portal/vat-tu-thiet-bi-moi/');
$search = sanitize_text_field((string) ($_GET['tool_search'] ?? ''));
$active_category = sanitize_key((string) ($_GET['tool_category'] ?? 'all'));
$theme_image_base = trailingslashit(get_template_directory_uri() . '/assets/images');

$raw_tools = function_exists('aitrongcay_get_db_tools') ? aitrongcay_get_db_tools($garden_key) : [];
$onboarding_supplies = function_exists('aitrongcay_supplies_latest') ? aitrongcay_supplies_latest(200, '') : [];
if ($onboarding_supplies !== []) {
    $existing_names = [];
    foreach ($raw_tools as $index => $tool) {
        $name_key = function_exists('remove_accents') ? strtolower(remove_accents(trim((string) ($tool['name'] ?? '')))) : strtolower(trim((string) ($tool['name'] ?? '')));
        if ($name_key !== '') {
            $existing_names[$name_key] = $index;
        }
    }
    foreach ($onboarding_supplies as $supply) {
        if (! is_array($supply)) {
            continue;
        }
        $name = trim((string) ($supply['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $name_key = function_exists('remove_accents') ? strtolower(remove_accents($name)) : strtolower($name);
        $sale_price = isset($supply['sale_price']) ? (float) $supply['sale_price'] : 0.0;
        $cost_price = isset($supply['cost_price']) ? (float) $supply['cost_price'] : 0.0;
        $normalized_price = (int) round(max(0, $sale_price));
        $stock = (int) ($supply['stock_quantity'] ?? 0);
        if (isset($existing_names[$name_key])) {
            $existing_index = (int) $existing_names[$name_key];
            $raw_tools[$existing_index]['price'] = $normalized_price;
            $raw_tools[$existing_index]['sale_price'] = $normalized_price;
            $raw_tools[$existing_index]['cost_price'] = (int) round(max(0, $cost_price));
            $raw_tools[$existing_index]['stock_quantity'] = $stock;
            $raw_tools[$existing_index]['image'] = (string) (($supply['image_url'] ?? '') !== '' ? $supply['image_url'] : ($raw_tools[$existing_index]['image'] ?? ''));
            $raw_tools[$existing_index]['description'] = trim((string) (($supply['description'] ?? '') !== '' ? $supply['description'] : ($raw_tools[$existing_index]['description'] ?? '')));
            $raw_tools[$existing_index]['spec'] = (string) (($supply['spec'] ?? '') !== '' ? $supply['spec'] : ($raw_tools[$existing_index]['spec'] ?? ''));
            $raw_tools[$existing_index]['supplier_name'] = (string) (($supply['supplier_name'] ?? '') !== '' ? $supply['supplier_name'] : ($raw_tools[$existing_index]['supplier_name'] ?? ''));
            continue;
        }
        $raw_tools[] = [
            'tool_key' => 'supply_' . (int) ($supply['id'] ?? 0),
            'name' => $name,
            'type' => (string) ($supply['type'] ?? 'Vật tư'),
            'description' => trim((string) ($supply['description'] ?? '')),
            'owned' => 0,
            'qty' => 0,
            'stock_quantity' => $stock,
            'image' => (string) ($supply['image_url'] ?? ''),
            'spec' => (string) ($supply['spec'] ?? ''),
            'supplier_name' => (string) ($supply['supplier_name'] ?? ''),
            'price' => $normalized_price,
            'sale_price' => $normalized_price,
            'cost_price' => (int) round(max(0, $cost_price)),
        ];
        $existing_names[$name_key] = array_key_last($raw_tools);
    }
}
$raw_tools = array_values(array_filter($raw_tools, static fn($tool) => is_array($tool) && trim((string) ($tool['name'] ?? '')) !== ''));

$category_labels = [
    'all' => 'Tất cả',
    'seeds' => 'Hạt giống',
    'nutrients' => 'Dinh dưỡng',
    'devices' => 'Thiết bị',
    'trays' => 'Khoang vườn',
    'other' => 'Khác',
];

$resolve_tool_category = static function (array $tool): string {
    $explicit_type = mb_strtolower(trim((string) ($tool['type'] ?? '')));
    
    if (str_contains($explicit_type, 'hạt') || str_contains($explicit_type, 'giống') || str_contains($explicit_type, 'seed')) return 'seeds';
    if (str_contains($explicit_type, 'dinh dưỡng') || str_contains($explicit_type, 'phân')) return 'nutrients';
    if (str_contains($explicit_type, 'thiết bị') || str_contains($explicit_type, 'đèn') || str_contains($explicit_type, 'bơm')) return 'devices';
    if (str_contains($explicit_type, 'khoang') || str_contains($explicit_type, 'chậu') || str_contains($explicit_type, 'rọ')) return 'trays';
    if (str_contains($explicit_type, 'giá thể') || str_contains($explicit_type, 'viên nén') || str_contains($explicit_type, 'đất')) return 'other';

    // 1. Quét trong tên sản phẩm trước (chính xác nhất)
    $name_haystack = mb_strtolower(trim((string) ($tool['name'] ?? '')));
    
    foreach (['đèn', 'light', 'pump', 'bơm', 'sensor', 'cảm biến', 'thiết bị'] as $word) {
        if (str_contains($name_haystack, $word)) return 'devices';
    }
    foreach (['khoang', 'tray', 'rọ', 'pot', 'net', 'chậu'] as $word) {
        if (str_contains($name_haystack, $word)) return 'trays';
    }
    foreach (['dinh dưỡng', 'nutrient', 'phân', 'fertilizer', 'ab'] as $word) {
        if (str_contains($name_haystack, $word)) return 'nutrients';
    }
    foreach (['giá thể', 'xơ dừa', 'đất', 'sỏi', 'viên nén'] as $word) {
        if (str_contains($name_haystack, $word)) return 'other';
    }
    foreach (['hạt', 'seed', 'giống', 'ươm'] as $word) {
        if (str_contains($name_haystack, $word)) return 'seeds';
    }

    // 2. Nếu tên không có, mới quét trong mô tả
    $haystack = mb_strtolower(trim(implode(' ', [
        (string) ($tool['description'] ?? ''),
        (string) ($tool['tool_key'] ?? ''),
    ])));

    foreach (['đèn', 'light', 'pump', 'bơm', 'sensor', 'cảm biến', 'thiết bị'] as $word) {
        if (str_contains($haystack, $word)) return 'devices';
    }
    foreach (['khoang', 'tray', 'rọ', 'pot', 'net', 'chậu'] as $word) {
        if (str_contains($haystack, $word)) return 'trays';
    }
    foreach (['dinh dưỡng', 'nutrient', 'phân', 'fertilizer', 'ab'] as $word) {
        if (str_contains($haystack, $word)) return 'nutrients';
    }
    foreach (['giá thể', 'xơ dừa', 'đất nung', 'sỏi'] as $word) {
        if (str_contains($haystack, $word)) return 'other';
    }
    foreach (['hạt', 'seed', 'giống', 'ươm'] as $word) {
        if (str_contains($haystack, $word)) return 'seeds';
    }
    
    return 'other';
};

$resolve_tool_image = static function (array $tool, string $category, string $theme_image_base): string {
    $image = trim((string) ($tool['image'] ?? ''));
    if ($image !== '') {
        return $image;
    }

    $haystack = mb_strtolower(trim(implode(' ', [
        (string) ($tool['type'] ?? ''),
        (string) ($tool['name'] ?? ''),
        (string) ($tool['description'] ?? ''),
        (string) ($tool['tool_key'] ?? ''),
    ])));

    if ($category === 'seeds') {
        if (str_contains($haystack, 'cải') || str_contains($haystack, 'sam')) {
            return $theme_image_base . 'tool-seed-sam-real.png';
        }
        if (str_contains($haystack, 'rau muống') || str_contains($haystack, 'watercress')) {
            return $theme_image_base . 'tool-seed-watercress-real.png';
        }
        if (str_contains($haystack, 'cúc') || str_contains($haystack, 'chrysanthemum')) {
            return $theme_image_base . 'tool-seed-chrysanthemum-real.png';
        }
        if (str_contains($haystack, 'ớt') || str_contains($haystack, 'chili')) {
            return $theme_image_base . 'tool-seed-chili-real.png';
        }
        return $theme_image_base . 'tool-seed-tomato-real.png';
    }

    if ($category === 'nutrients') {
        if (str_contains($haystack, 'organic') || str_contains($haystack, 'hữu cơ')) {
            return $theme_image_base . 'tool-fertilizer-organic-real.png';
        }
        return $theme_image_base . 'tool-nutrient-ab-real.png';
    }

    if ($category === 'devices') {
        if (str_contains($haystack, 'đèn') || str_contains($haystack, 'light')) {
            return $theme_image_base . 'tool-grow-light-real.png';
        }
        return $theme_image_base . 'tool-pump-real.png';
    }

    if ($category === 'trays') {
        return $theme_image_base . 'tool-tray-real.png';
    }

    return $theme_image_base . 'tools-shed.svg';
};

$resolve_tool_badge = static function (array $tool, string $category): string {
    if ((int) ($tool['owned'] ?? 0) > 0) {
        return 'In stock';
    }
    return match ($category) {
        'seeds' => 'Seed bank',
        'nutrients' => 'Grow boost',
        'devices' => 'Precision',
        'trays' => 'Core setup',
        default => 'Utility',
    };
};

$resolve_tool_meta = static function (array $tool, string $category, array $category_labels): string {
    $description = trim((string) ($tool['description'] ?? ''));
    if ($description !== '') {
        return $description;
    }
    return $category_labels[$category] ?? 'Kho vật tư';
};

$resolve_tool_price = static function (array $tool, string $category): int {
    $price_fields = ['sale_price', 'price', 'amount', 'input_price', 'cost_price'];
    foreach ($price_fields as $field) {
        if (array_key_exists($field, $tool)) {
            return max(0, (int) round((float) $tool[$field]));
        }
    }

    $looks_like_supply = str_starts_with((string) ($tool['tool_key'] ?? ''), 'supply_')
        || array_key_exists('supplier_name', $tool)
        || array_key_exists('spec', $tool);

    if ($looks_like_supply) {
        return 0;
    }

    return 0;
};

$items = [];
foreach ($raw_tools as $tool) {
    $category = $resolve_tool_category($tool);
    $item = [
        'code' => trim((string) ($tool['code'] ?? '')),
        'name' => trim((string) ($tool['name'] ?? '')),
        'category' => $category,
        'category_label' => $category_labels[$category] ?? 'Khác',
        'meta' => $resolve_tool_meta($tool, $category, $category_labels),
        'price' => $resolve_tool_price($tool, $category),
        'unit' => 'đơn vị',
        'badge' => $resolve_tool_badge($tool, $category),
        'spec_1' => trim((string) ($tool['type'] ?? 'Chuẩn vườn số')),
        'spec_2' => (string) max(0, (int) ($tool['qty'] ?? 0)),
        'image' => $resolve_tool_image($tool, $category, $theme_image_base),
        'owned' => (int) ($tool['owned'] ?? 0),
        'qty' => (int) ($tool['qty'] ?? 0),
        'stock_quantity' => (int) ($tool['stock_quantity'] ?? 0),
        'description' => trim((string) ($tool['description'] ?? '')),
    ];

    if ($search !== '') {
        $search_haystack = mb_strtolower(implode(' ', [$item['name'], $item['category_label'], $item['spec_1'], $item['spec_2'], $item['description']]));
        if (! str_contains($search_haystack, mb_strtolower($search))) {
            continue;
        }
    }

    if ($active_category !== 'all' && $category !== $active_category) {
        continue;
    }

    $items[] = $item;
}

$inventory_counts = array_fill_keys(array_keys($category_labels), 0);
foreach ($raw_tools as $tool) {
    $inventory_counts['all'] += 1;
    $inventory_counts[$resolve_tool_category($tool)] += 1;
}

// Dữ liệu mock đã được gỡ bỏ để đảm bảo Production an toàn

$total_price = 0;
$result_count = count($items);
$show_result_message = $search !== '';
$result_message = $result_count > 0
    ? sprintf('Đã tìm thấy %d kết quả phù hợp.', $result_count)
    : 'Không tìm thấy vật phẩm phù hợp.';

$eco_nav_items = array_map(static function (array $item) use ($garden_key) {
    $key = (string) ($item['key'] ?? '');
    $url = (string) ($item['url'] ?? '#');
    if (in_array($key, ['kho-nong-cu', 'dashboard'], true)) {
        $url = add_query_arg(array_filter(['garden' => $garden_key]), $url);
    }
    return ['key' => $key, 'label' => (string) ($item['label'] ?? ''), 'url' => $url];
}, aitrongcay_eco_nav_items());
$is_logged_in = is_user_logged_in();
$header_avatar_html = '👤';
if ($is_logged_in) {
    $current_user_header = wp_get_current_user();
    $header_avatar_id = (int) get_user_meta($current_user_header->ID, 'aitrongcay_avatar_id', true);
    $header_avatar_url = $header_avatar_id ? (wp_get_attachment_image_url($header_avatar_id, 'thumbnail') ?: wp_get_attachment_url($header_avatar_id)) : '';
    if ($header_avatar_url) {
        $header_avatar_html = '<img src="' . esc_url($header_avatar_url) . '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
    } else {
        $header_avatar_html = esc_html(mb_strtoupper(mb_substr($current_user_header->display_name ?: $current_user_header->user_login, 0, 1)));
    }
}

set_query_var('aitr_eco_shell', [
    'title' => $garden_name !== '' ? $garden_name : 'Khu vườn của bạn',
    'active' => 'kho-nong-cu',
    'side_title' => $garden_name !== '' ? $garden_name : 'The Observatory',
    'side_subtitle' => 'Marketplace hub',
    'side_badge' => '🌱',
    'top_icons' => [],
    'nav' => $eco_nav_items,
]);
?>
<section class="section-tight" style="background:#121411;min-height:100vh;padding:0">
  <style>
    .site-header,.account-menu{display:none !important}
    .eco-warehouse-shell{min-height:100vh;background:#121411;color:#e3e3de;font-family:'Manrope',sans-serif}
    .eco-warehouse-topbar{position:sticky;top:0;z-index:40;display:grid;grid-template-columns:minmax(260px,1fr) auto auto;align-items:center;gap:18px;padding:16px 28px;background:rgba(18,20,17,.72);backdrop-filter:blur(24px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
    .eco-warehouse-garden-name{font-family:'Noto Serif',serif;font-size:34px;line-height:1.05;color:var(--primary);font-weight:800}.eco-warehouse-garden-name a{color:inherit;text-decoration:none}
    .eco-warehouse-top-links{display:flex;gap:18px;justify-content:center;align-items:center;flex-wrap:wrap}.eco-warehouse-top-links a{color:rgba(227,227,222,.64);font-weight:700;transition:color .18s ease}.eco-warehouse-top-links a:hover,.eco-warehouse-top-links a.active{color:#6fdba8}
    .eco-warehouse-search{display:flex;align-items:center;gap:10px;padding:10px 16px;border-radius:999px;background:rgba(6,25,18,.44);border:1px solid rgba(111,219,168,.12);min-width:280px;color:rgba(227,227,222,.7)}
    .eco-warehouse-search input{background:transparent;border:none;outline:none;color:inherit;width:100%}
    .eco-warehouse-top-right{display:flex;gap:14px;align-items:center;color:var(--primary);justify-content:flex-end;flex-wrap:wrap}
    .eco-warehouse-admin-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
    .eco-warehouse-admin-link{display:inline-flex;align-items:center;justify-content:center;padding:10px 14px;border-radius:999px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-size:12px;font-weight:900;letter-spacing:.03em;box-shadow:0 12px 28px rgba(111,219,168,.18)}
    .eco-warehouse-admin-link.is-secondary{background:rgba(51,53,50,.78);color:#e3e3de;border:1px solid rgba(111,219,168,.12);box-shadow:none}
    .eco-warehouse-avatar{width:42px;height:42px;border-radius:999px;border:2px solid var(--primary);display:grid;place-items:center;background:#1a1c19;overflow:hidden;padding:0;box-sizing:border-box;color:#fff;font-weight:bold}
    .eco-warehouse-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:24px;padding:24px}
    .eco-warehouse-cart{align-self:start;position:static;border-radius:32px;background:rgba(7,33,24,.55);backdrop-filter:blur(24px);padding:20px;border:1px solid rgba(111,219,168,.08);box-shadow:0 20px 44px rgba(0,0,0,.18);margin-top:24px;width:100%}
    .eco-warehouse-cart.is-empty{display:none}
    .eco-shared-side{position:sticky;top:100px;align-self:start;background:rgba(7,33,24,.58);backdrop-filter:blur(24px);border-radius:30px;padding:24px 0;box-shadow:10px 0 30px rgba(0,0,0,.2)}
    .eco-shared-side-head{padding:0 24px 22px;display:flex;align-items:center;gap:12px}.eco-shared-side-badge{width:48px;height:48px;border-radius:18px;background:linear-gradient(135deg,#31a375,#6fdba8);display:grid;place-items:center;color:#062013}
    .eco-shared-side-head h3{margin:0;font-size:14px;color:#6FDBA8;font-weight:800}.eco-shared-side-head p{margin:4px 0 0;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:rgba(227,227,222,.58)}
    .eco-shared-side nav{display:flex;flex-direction:column;gap:2px;margin-top:18px}.eco-shared-side nav a{display:flex;align-items:center;gap:14px;padding:14px 24px;color:rgba(227,227,222,.62);transition:.2s}.eco-side-link-icon{flex:0 0 auto;font-size:16px;line-height:1}.eco-side-link-short{display:none}.eco-shared-side nav a.active{background:linear-gradient(90deg,#31a375,#6fdba8);color:#062013;border-radius:0 999px 999px 0;font-weight:900}.eco-shared-side nav a:not(.active):hover{transform:translateX(6px);color:#6FDBA8}
    .eco-warehouse-main{min-width:0;padding-top:8px}.eco-warehouse-hero{position:relative;margin-bottom:24px}.eco-warehouse-hero p{max-width:860px;font-size:10px;line-height:1.45;color:#6fdba8 !important;font-weight:700;letter-spacing:.02em}
    .eco-warehouse-hero-search{display:flex;align-items:center;gap:12px;padding:16px 20px;border-radius:24px;background:rgba(41,43,39,.56);border:1px solid rgba(111,219,168,.12);width:100%;box-shadow:0 20px 44px rgba(0,0,0,.14)}
    .eco-warehouse-hero-search input{background:transparent;border:none;outline:none;color:var(--text);width:100%;font-size:18px}
    .eco-warehouse-chips{display:flex;flex-wrap:wrap;gap:12px;margin:24px 0 16px}.eco-warehouse-chip{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:999px;background:rgba(51,53,50,.78);color:rgba(227,227,222,.72);font-size:14px;font-weight:700}.eco-warehouse-chip.active{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013}
    .eco-warehouse-chip-icon{font-size:16px;line-height:1}
    .eco-warehouse-chip small{opacity:.75}
    .eco-warehouse-slider{position:relative;padding:0 74px}
    .eco-warehouse-slider::before,.eco-warehouse-slider::after{content:'';position:absolute;top:0;bottom:14px;width:92px;z-index:1;pointer-events:none}
    .eco-warehouse-slider::before{left:0;background:linear-gradient(90deg,#121411 0%, rgba(18,20,17,.92) 36%, rgba(18,20,17,0) 100%)}
    .eco-warehouse-slider::after{right:0;background:linear-gradient(270deg,#121411 0%, rgba(18,20,17,.92) 36%, rgba(18,20,17,0) 100%)}
    .eco-warehouse-slider-nav{position:absolute;top:50%;transform:translateY(-50%);width:58px;height:58px;border:none;border-radius:999px;background:linear-gradient(135deg,rgba(26,34,28,.96),rgba(10,16,13,.96));color:var(--primary);font-size:30px;font-weight:900;display:grid;place-items:center;box-shadow:0 18px 40px rgba(0,0,0,.3), inset 0 0 0 1px rgba(111,219,168,.14);z-index:3;cursor:pointer;transition:transform .18s ease, box-shadow .18s ease, opacity .18s ease}
    .eco-warehouse-slider-nav:hover{transform:translateY(-50%) scale(1.04);box-shadow:0 22px 44px rgba(0,0,0,.34), inset 0 0 0 1px rgba(111,219,168,.2)}
    .eco-warehouse-slider-nav[disabled]{opacity:.3;cursor:default;transform:translateY(-50%) scale(.98)}
    .eco-warehouse-slider-nav.is-prev{left:0}
    .eco-warehouse-slider-nav.is-next{right:0}
    .eco-warehouse-slider-track{display:flex;gap:22px;overflow-x:auto;overflow-y:hidden;padding:14px 4px 18px;scroll-snap-type:x mandatory;scroll-padding-inline:calc(50% - 160px);scroll-behavior:smooth;scrollbar-width:none;-ms-overflow-style:none;cursor:grab;-webkit-overflow-scrolling:touch}
    .eco-warehouse-slider-track::-webkit-scrollbar{display:none}
    .eco-warehouse-slider-indicators{display:flex;align-items:center;justify-content:center;gap:10px;margin-top:14px;position:relative;z-index:3}
    .eco-warehouse-slider-dot{width:10px;height:10px;border:none;border-radius:999px;background:rgba(227,227,222,.22);box-shadow:inset 0 0 0 1px rgba(111,219,168,.08);padding:0;transition:transform .18s ease, background .18s ease, box-shadow .18s ease;cursor:pointer}
    .eco-warehouse-slider-dot.is-active{background:var(--primary);transform:scale(1.25);box-shadow:0 0 0 4px rgba(111,219,168,.12)}
    .eco-warehouse-slider-track.is-dragging{cursor:grabbing;scroll-snap-type:none;user-select:none}
    .eco-warehouse-slider-track > *{flex:0 0 calc((100% - 44px) / 3);min-width:280px;scroll-snap-align:center;scroll-snap-stop:always;transition:transform .24s ease, opacity .24s ease, filter .24s ease}
    .eco-warehouse-slider-track > *.is-featured{transform:translateY(-10px) scale(1.02);filter:brightness(1.05)}
    .eco-warehouse-slider-track > *.is-dimmed{opacity:.76;filter:saturate(.88)}
    .eco-warehouse-card{position:relative;border-radius:34px;padding:22px;background:rgba(41,43,39,.54);backdrop-filter:blur(24px);border:1px solid rgba(111,219,168,.08);box-shadow:0 20px 44px rgba(0,0,0,.18);overflow:hidden;height:100%;transition:box-shadow .24s ease,border-color .24s ease,background .24s ease}.eco-warehouse-slider-track > *.is-featured .eco-warehouse-card{background:rgba(47,50,45,.68);border-color:rgba(111,219,168,.22);box-shadow:0 28px 58px rgba(0,0,0,.24),0 0 0 1px rgba(111,219,168,.08),0 0 28px rgba(111,219,168,.12)}.eco-warehouse-slider-track > *.is-featured .eco-warehouse-card::after{content:'';position:absolute;inset:-8px;border-radius:40px;pointer-events:none;box-shadow:0 0 36px rgba(111,219,168,.12)}.eco-warehouse-card-media{height:230px;display:grid;place-items:center;margin-bottom:18px;position:relative}.eco-warehouse-card-media::before{content:'';position:absolute;inset:22px;border-radius:999px;background:rgba(111,219,168,.08);filter:blur(30px)}.eco-warehouse-card-img-wrap{width:160px;height:160px;border-radius:22px;overflow:hidden;position:relative;box-shadow:0 20px 30px rgba(111,219,168,.22)}
    .eco-warehouse-card-media img{width:100%;height:100%;object-fit:cover;background:transparent}
    .eco-warehouse-badge{position:absolute;top:18px;right:18px;padding:7px 10px;border-radius:999px;background:rgba(255,182,140,.12);border:1px solid rgba(255,182,140,.24);font-size:10px;font-weight:900;letter-spacing:.08em;color:#ffd6c0;text-transform:uppercase}
    .eco-warehouse-card h3{color:#fdfcf6;margin:0 0 8px;font-family:'Noto Serif',serif;font-size:24px;line-height:1.12;min-height:54px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.eco-warehouse-meta{display:inline-block;color:#6fdba8;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin:0 0 6px;padding:3px 8px;border-radius:999px;background:rgba(111,219,168,.1)}
    .eco-warehouse-desc{color:rgba(226,183,143,.9);font-size:13px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;min-height:60px;margin:0 0 10px}.eco-warehouse-price-row{display:flex;justify-content:space-between;align-items:flex-end;margin:14px 0 18px}.eco-warehouse-price{font-size:24px;font-weight:900;color:#6fdba8;line-height:1}
    .eco-warehouse-specs,.eco-warehouse-spec{display:none !important}
    .eco-warehouse-buy-row{display:flex;justify-content:center;align-items:center;margin-top:8px}
    .eco-warehouse-cta{width:min(180px,100%);height:42px;padding:0 20px;border:none;border-radius:14px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-size:15px;font-weight:800;line-height:1;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform 0.2s cubic-bezier(0.34,1.56,0.64,1),box-shadow 0.2s ease,filter 0.2s ease;box-shadow:0 8px 16px rgba(49,163,117,0.2)}
    .eco-warehouse-cta:hover{transform:translateY(-3px) scale(1.02);box-shadow:0 12px 24px rgba(49,163,117,0.35);filter:brightness(1.1)}
    .eco-warehouse-cta:active{transform:translateY(1px) scale(0.98);box-shadow:0 4px 8px rgba(49,163,117,0.2)}
    .eco-warehouse-profile-trigger{cursor:pointer}.eco-warehouse-profile-popup{position:absolute;top:72px;right:28px;min-width:240px;background:rgba(26,28,25,.96);border:1px solid rgba(255,255,255,.06);border-radius:22px;padding:10px;box-shadow:0 24px 52px rgba(0,0,0,.28);z-index:70}.eco-warehouse-profile-popup[hidden]{display:none}.eco-warehouse-profile-popup a{display:block;padding:12px 14px;border-radius:14px;color:#e3e3de}.eco-warehouse-profile-popup a:hover{background:rgba(51,53,50,.56)}
    .eco-warehouse-feature{display:grid;grid-template-columns:1.4fr .8fr;gap:20px;margin-top:34px;padding:26px;border-radius:36px;background:rgba(41,43,39,.46);border:1px solid rgba(111,219,168,.08);box-shadow:0 20px 44px rgba(0,0,0,.16)}
    .eco-warehouse-feature h2{margin:10px 0 12px;font-family:'Noto Serif',serif;font-size:54px;line-height:1}.eco-warehouse-feature p{max-width:580px;color:rgba(227,227,222,.72);font-size:19px;line-height:1.6}.eco-warehouse-feature-actions{display:flex;gap:14px;margin-top:22px}.eco-warehouse-feature-actions a{display:inline-flex;align-items:center;justify-content:center;padding:16px 26px;border-radius:18px;font-weight:800}.eco-warehouse-feature-actions a.primary{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013}.eco-warehouse-feature-actions a.secondary{background:transparent;border:1px solid rgba(255,255,255,.14);color:#fff}
    .eco-warehouse-feature-visual{display:grid;place-items:center}.eco-warehouse-feature-visual div{width:190px;height:190px;border-radius:34px;background:radial-gradient(circle at 30% 30%, rgba(111,219,168,.18), rgba(7,33,24,.92));box-shadow:inset 0 0 0 1px rgba(111,219,168,.08),0 20px 44px rgba(0,0,0,.22)}
    .eco-warehouse-cart h3,.eco-warehouse-cart h4{margin:0}.eco-warehouse-cart-balance{padding:20px;border-radius:24px;background:rgba(51,53,50,.54);margin:10px 0 24px}.eco-warehouse-cart-balance .big{font-size:54px;font-weight:900;color:var(--primary);line-height:1}.eco-warehouse-cart-list{display:grid;gap:16px;margin:18px 0 28px}.eco-warehouse-cart-item{display:grid;grid-template-columns:54px 1fr auto;gap:12px;align-items:center}.eco-warehouse-cart-item img{width:54px;height:54px;border-radius:14px;object-fit:contain;background:rgba(18,20,17,.22)}.eco-warehouse-cart-item .price{font-weight:900;color:var(--primary)}.eco-warehouse-cart-total{display:flex;justify-content:space-between;align-items:center;margin:22px 0 16px;font-weight:800}.eco-warehouse-cart-checkout{width:100%;padding:18px 18px;border:none;border-radius:22px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-size:28px;font-weight:900}
    .eco-warehouse-empty{padding:28px;border-radius:28px;background:rgba(41,43,39,.42);border:1px solid rgba(111,219,168,.08);color:rgba(227,227,222,.7)}
    @media (max-width:1400px){.eco-warehouse-layout{grid-template-columns:240px minmax(0,1fr)}.eco-warehouse-cart{grid-column:1 / -1;position:static}.eco-warehouse-slider-track > *{flex-basis:calc((100% - 22px) / 2)}}
    .eco-warehouse-hint-banner{display:none;align-items:center;gap:12px;margin:10px 0 12px;padding:12px 16px;border-radius:16px;background:rgba(111,219,168,.07);border:1px solid rgba(111,219,168,.18);font-size:13px;flex-wrap:wrap}
    .eco-warehouse-hint-text{flex:1;color:rgba(227,227,222,.8);line-height:1.4}
    .eco-warehouse-hint-btn{padding:7px 14px;border-radius:10px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-weight:800;font-size:12px;white-space:nowrap;text-decoration:none;flex-shrink:0}
    .eco-warehouse-hint-close{background:none;border:none;color:rgba(227,227,222,.35);cursor:pointer;font-size:16px;padding:2px 6px;flex-shrink:0}
    .aitr-qty-row{display:flex;align-items:center;gap:6px;margin-top:4px}
    .aitr-qty-btn{width:26px;height:26px;border-radius:8px;border:1px solid rgba(111,219,168,.3);background:rgba(111,219,168,.08);color:#6fdba8;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;padding:0;line-height:1}
    .aitr-qty-btn:hover{background:rgba(111,219,168,.2)}
    .aitr-qty-display{min-width:22px;text-align:center;font-weight:800;font-size:13px}
    .eco-warehouse-checkout-overlay{position:fixed;inset:0;background:rgba(0,0,0,.72);backdrop-filter:blur(8px);z-index:200;display:grid;place-items:center;padding:16px}
    .eco-warehouse-checkout-overlay[hidden]{display:none}
    .eco-warehouse-checkout-modal{width:100%;max-width:480px;background:#1a1c19;border-radius:28px;border:1px solid rgba(111,219,168,.14);box-shadow:0 30px 60px rgba(0,0,0,.45);overflow:hidden}
    .eco-warehouse-checkout-head{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.06);font-size:17px;font-weight:800}
    .eco-warehouse-checkout-head button{background:none;border:none;color:rgba(227,227,222,.45);cursor:pointer;font-size:20px;padding:4px 8px;line-height:1}
    .eco-warehouse-checkout-body{padding:22px 24px;max-height:65vh;overflow-y:auto}
    .eco-warehouse-checkout-summary{border-radius:14px;background:rgba(41,43,39,.7);padding:14px 16px;margin-bottom:12px}
    .eco-warehouse-checkout-total-row{display:flex;justify-content:space-between;align-items:center;font-weight:900;font-size:17px;padding:10px 0;border-top:1px solid rgba(255,255,255,.06);margin-bottom:16px}
    [data-aitr-checkout-total]{color:#6fdba8}
    .eco-warehouse-checkout-form{display:grid;gap:10px}
    .eco-warehouse-checkout-form label{display:flex;flex-direction:column;gap:5px;font-size:12px;font-weight:700;color:rgba(227,227,222,.65)}
    .eco-warehouse-checkout-form input,.eco-warehouse-checkout-form textarea{background:rgba(41,43,39,.9);border:1px solid rgba(255,255,255,.08);border-radius:12px;color:#e3e3de;padding:10px 14px;font-size:14px;outline:none;width:100%;font-family:inherit;transition:border-color .18s}
    .eco-warehouse-checkout-form input:focus,.eco-warehouse-checkout-form textarea:focus{border-color:rgba(111,219,168,.4)}
    .eco-warehouse-checkout-form textarea{min-height:72px;resize:vertical}
    [data-aitr-checkout-msg]{margin-top:10px;font-size:13px;font-weight:700;border-radius:10px;padding:9px 12px;background:rgba(41,43,39,.7)}
    .eco-warehouse-checkout-foot{padding:14px 24px;border-top:1px solid rgba(255,255,255,.06)}
    .eco-warehouse-checkout-submit-btn{width:100%;padding:15px;border:none;border-radius:16px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-size:17px;font-weight:900;cursor:pointer;transition:opacity .18s}
    .eco-warehouse-checkout-submit-btn:disabled{opacity:.55;cursor:not-allowed}
    @media (max-width:980px){.eco-warehouse-shell{padding-bottom:calc(104px + env(safe-area-inset-bottom,0px))}.eco-warehouse-topbar{display:flex;flex-wrap:wrap;justify-content:space-between;padding:14px 16px;gap:12px;grid-template-columns:none}.eco-warehouse-garden-name{font-size:28px;flex:1 1 0;min-width:0;order:1}.eco-warehouse-top-right{justify-content:flex-end;gap:14px;order:2;flex:0 0 auto}.eco-warehouse-top-links{justify-content:flex-start;overflow:auto;padding-bottom:4px;order:3;flex:0 0 100%;margin-top:4px}.eco-warehouse-search{min-width:0;width:100%}.eco-warehouse-layout{grid-template-columns:1fr;padding:16px 16px 14px}.eco-shared-side{position:fixed;left:12px;right:12px;bottom:calc(16px + env(safe-area-inset-bottom,0px));top:auto;z-index:65;padding:11px 12px calc(11px + env(safe-area-inset-bottom,0px));border-radius:26px;background:rgba(7,33,24,.88);backdrop-filter:blur(26px);-webkit-backdrop-filter:blur(26px);box-shadow:0 20px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.06)}.eco-shared-side-head{display:none}.eco-shared-side nav{margin-top:0;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.eco-shared-side nav a{flex-direction:column;justify-content:center;text-align:center;padding:10px 8px;border-radius:18px;font-size:11px;line-height:1.15;gap:5px;font-weight:700;color:rgba(227,227,222,.74)}.eco-shared-side nav a.is-desktop-only,.eco-side-link-label{display:none}.eco-side-link-short{display:block}.eco-side-link-icon{font-size:20px}.eco-shared-side nav a.active{border-radius:18px;background:linear-gradient(180deg,rgba(111,219,168,.24),rgba(49,163,117,.92));color:#f7fff9;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 10px 22px rgba(49,163,117,.22)}.eco-warehouse-hero h1{font-size:44px}.eco-warehouse-hero p{font-size:10px;color:#6fdba8 !important}.eco-warehouse-slider{padding:0 54px}.eco-warehouse-slider::before,.eco-warehouse-slider::after{width:58px}.eco-warehouse-slider-track{gap:16px;padding-bottom:10px;scroll-padding-inline:calc(50% - 140px)}.eco-warehouse-slider-track > *{flex-basis:calc(100% - 8px);min-width:0}.eco-warehouse-slider-track > *.is-featured{transform:translateY(-4px) scale(1.01)}.eco-warehouse-card h3{min-height:54px}.eco-warehouse-desc{min-height:66px}.eco-warehouse-slider-nav{width:46px;height:46px;font-size:24px}.eco-warehouse-feature{grid-template-columns:1fr}.eco-warehouse-feature h2{font-size:40px}}
  </style>
  <div class="eco-warehouse-shell">
    <?php if ($catalog_access_state === 'denied') : ?>
      <div style="max-width:1320px;margin:0 auto;padding:18px 24px 0;">
        <div style="background:rgba(255,180,171,.12);border:1px solid rgba(255,180,171,.28);color:#ffd9d2;border-radius:20px;padding:14px 16px;font-size:14px;">
          Tài khoản này chưa có quyền creator để vào page onboarding cây mới hoặc tạo vật phẩm mới.
        </div>
      </div>
    <?php endif; ?>
    <header class="eco-warehouse-topbar">
      <div class="eco-warehouse-garden-name"><a href="<?php echo esc_url($dashboard_url); ?>"><span style="color:#6fdba8;font-weight:900">AI</span> <span style="color:#ffffff;font-weight:700">trồng cây</span></a></div>
      <nav class="eco-warehouse-top-links">
        <?php foreach ($shared_top_links as $top_link) : ?>
          <?php if ($top_link['key'] === 'kho-nong-cu' || ($top_link['key'] === 'dashboard-2' && $garden_key === '')) { continue; } ?>
          <a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="eco-warehouse-top-right">
        <?php if ($can_manage_catalog) : ?>
          <div class="eco-warehouse-admin-actions">
            <a class="eco-warehouse-admin-link" href="<?php echo esc_url($new_plant_url); ?>">+ cây mới</a>
            <a class="eco-warehouse-admin-link is-secondary" href="<?php echo esc_url($new_supply_url); ?>">+ vật tư mới</a>
          </div>
        <?php endif; ?>
        <?php get_template_part('template-parts/site/eco-notification-bell'); ?>
        <div class="eco-warehouse-avatar" aria-hidden="true">⚙️</div>
        <button class="eco-warehouse-avatar eco-warehouse-profile-trigger" type="button" data-eco-warehouse-profile-trigger aria-expanded="false"><?php echo $header_avatar_html; ?></button>
      </div>
      <div class="eco-warehouse-profile-popup" data-eco-warehouse-profile-popup hidden>
        <a href="<?php echo esc_url(home_url('/tai-khoan/')); ?>">Quản lý tài khoản</a>
        <a href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a>
      </div>
    </header>

    <div class="eco-warehouse-layout">
      <aside class="eco-shared-side">
        <div class="eco-shared-side-head">
          <div class="eco-shared-side-badge">🌿</div>
          <div><h3>Ai trồng cây</h3><p>Portal navigation</p></div>
        </div>
        <nav>
          <?php foreach ($eco_nav_items as $nav_item) : ?>
            <a class="<?php echo (($nav_item['key'] ?? '') === 'kho-nong-cu') ? 'active' : ''; ?><?php echo (($nav_item['key'] ?? '') === 'gioi-thieu') ? ' is-desktop-only' : ''; ?>" href="<?php echo esc_url((string) ($nav_item['url'] ?? '#')); ?>">
              <span class="eco-side-link-icon" aria-hidden="true"><?php echo esc_html((string) ($nav_item['icon'] ?? '🍃')); ?></span>
              <span class="eco-side-link-label"><?php echo esc_html((string) ($nav_item['label'] ?? '')); ?></span>
              <span class="eco-side-link-short"><?php echo esc_html((string) ($nav_item['short_label'] ?? ($nav_item['label'] ?? ''))); ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
      </aside>

      <main class="eco-warehouse-main">
        <header class="eco-warehouse-hero">
          <form class="eco-warehouse-hero-search" method="get" action="<?php echo esc_url($warehouse_url); ?>">
          <?php if ($garden_key !== '') : ?><input type="hidden" name="garden" value="<?php echo esc_attr($garden_key); ?>"><?php endif; ?>
          <?php if ($active_category !== 'all') : ?><input type="hidden" name="tool_category" value="<?php echo esc_attr($active_category); ?>"><?php endif; ?>
          <span>🔎</span><input type="search" name="tool_search" value="<?php echo esc_attr($search); ?>" placeholder="Tìm hạt giống, dinh dưỡng, thiết bị...">
        </form>
          <?php if ($show_result_message) : ?>
            <p><?php echo esc_html($result_message); ?></p>
          <?php endif; ?>
        </header>

        <div class="eco-warehouse-chips">
          <?php $category_icons = ['all' => '🧺', 'seeds' => '🌱', 'nutrients' => '💧', 'devices' => '🛠️', 'trays' => '🪴', 'other' => '📦']; ?>
          <?php foreach ($category_labels as $key => $label) : ?>
            <?php $tab_url = add_query_arg(array_filter(['garden' => $garden_key, 'tool_category' => $key !== 'all' ? $key : null, 'tool_search' => $search !== '' ? $search : null]), $warehouse_url); ?>
            <a class="eco-warehouse-chip<?php echo $active_category === $key ? ' active' : ''; ?>" href="<?php echo esc_url($tab_url); ?>"><span class="eco-warehouse-chip-icon"><?php echo esc_html($category_icons[$key] ?? '📦'); ?></span><?php echo esc_html($label); ?><small><?php echo esc_html((string) ($inventory_counts[$key] ?? 0)); ?></small></a>
          <?php endforeach; ?>
        </div>

        <?php if ($items) : ?>
          <div class="eco-warehouse-slider">
            <button class="eco-warehouse-slider-nav is-prev" type="button" data-eco-warehouse-slider-prev aria-label="Xem vật phẩm trước">‹</button>
            <div class="eco-warehouse-slider-track" data-eco-warehouse-slider>
              <?php foreach ($items as $index => $item) : ?>
                <div data-eco-warehouse-slide data-slide-index="<?php echo esc_attr((string) $index); ?>">
                  <?php set_query_var('aitr_product_card', $item); get_template_part('template-parts/site/eco-product-card'); ?>
                </div>
              <?php endforeach; ?>
            </div>
            <button class="eco-warehouse-slider-nav is-next" type="button" data-eco-warehouse-slider-next aria-label="Xem vật phẩm tiếp theo">›</button>
            <div class="eco-warehouse-slider-indicators" data-eco-warehouse-slider-indicators>
              <?php foreach ($items as $index => $item) : ?>
                <button class="eco-warehouse-slider-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-eco-warehouse-slider-dot data-slide-target="<?php echo esc_attr((string) $index); ?>" aria-label="Xem vật phẩm <?php echo esc_attr((string) ($index + 1)); ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else : ?>
          <div class="eco-warehouse-empty">Không có vật tư nào khớp với bộ lọc hiện tại. Anh có thể đổi tab hoặc xóa từ khóa tìm kiếm để xem lại toàn bộ kho.</div>
        <?php endif; ?>

        <section class="eco-warehouse-cart">
          <h3 style="font-size:18px;margin-bottom:6px">Giỏ hiện tại (<span data-eco-warehouse-cart-count>0</span>)</h3>
          <div style="display:flex;justify-content:space-between;color:rgba(227,227,222,.56);font-size:12px"><span>Tạm tính</span><button type="button" data-eco-warehouse-cart-clear style="background:none;border:none;padding:0;color:var(--primary);cursor:pointer">Clear</button></div>
          <div class="eco-warehouse-cart-list" data-eco-warehouse-cart-list></div>
          <div class="eco-warehouse-cart-total"><span></span><span style="font-size:38px;color:#fff" data-eco-warehouse-cart-total>0đ</span></div>
          <div class="eco-warehouse-hint-banner" id="aitr-new-user-hint">
            <span class="eco-warehouse-hint-text">Chưa biết trồng rau gì? Xem <strong>Bách thảo</strong> để chọn cây và biết vật tư cần mua.</span>
            <a class="eco-warehouse-hint-btn" href="<?php echo esc_url(add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/flower-bio/'))); ?>">Khám phá Bách thảo &rarr;</a>
            <button type="button" class="eco-warehouse-hint-close" data-aitr-close-hint aria-label="Đóng">&times;</button>
          </div>
          <button class="eco-warehouse-cart-checkout" type="button">Tiến hành đặt hàng</button>
        </section>
      </main>

      <!-- Checkout overlay -->
      <div class="eco-warehouse-checkout-overlay" data-aitr-checkout-overlay hidden>
        <div class="eco-warehouse-checkout-modal">
          <div class="eco-warehouse-checkout-head">
            <span>Xác nhận đặt dịch vụ</span>
            <button type="button" data-aitr-checkout-close aria-label="Đóng">&times;</button>
          </div>
          <div class="eco-warehouse-checkout-body">
            <!-- Form panel -->
            <div data-aitr-checkout-panel="form">
              <div class="eco-warehouse-checkout-summary" data-aitr-checkout-summary></div>
              <div class="eco-warehouse-checkout-total-row">
                <span>Tổng cộng:</span>
                <span data-aitr-checkout-total>0đ</span>
              </div>
              <div class="eco-warehouse-checkout-form">
                <label>Họ và tên <input type="text" placeholder="Nguyễn Văn A" data-aitr-order-name autocomplete="name"></label>
                <label>Số điện thoại <input type="tel" placeholder="0912 345 678" data-aitr-order-phone autocomplete="tel"></label>
                <label>Email <small style="font-weight:400;color:rgba(227,227,222,.5)">(nhận xác nhận đơn)</small> <input type="email" placeholder="email@example.com" data-aitr-order-email autocomplete="email"></label>
                <label>Ghi chú <small style="font-weight:400;color:rgba(227,227,222,.5)">(tuỳ chọn)</small> <textarea placeholder="Cây muốn trồng, yêu cầu đặc biệt..." data-aitr-order-note></textarea></label>
              </div>
              <p data-aitr-checkout-msg style="display:none"></p>
            </div>
            <!-- Payment panel — shown after successful order -->
            <div data-aitr-checkout-panel="payment" style="display:none"></div>
          </div>
          <div class="eco-warehouse-checkout-foot">
            <button type="button" class="eco-warehouse-checkout-submit-btn" data-aitr-checkout-submit>Đặt dịch vụ ngay</button>
          </div>
        </div>
      </div>
    </div>
    <script>
      function ecoAlert(msg) {
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:30px;right:30px;background:#18181b;color:#fff;padding:14px 20px;border-radius:12px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.3);z-index:999999;font-size:14px;border:1px solid #27272a;border-left:4px solid #10b981;opacity:0;transform:translateY(-20px);transition:all 0.3s ease;font-weight:500;';
        toast.innerHTML = '<span style="margin-right:8px">ℹ️</span>' + msg;
        document.body.appendChild(toast);
        toast.offsetHeight;
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        setTimeout(function() {
          toast.style.opacity = '0';
          toast.style.transform = 'translateY(-20px)';
          setTimeout(function() { toast.remove(); }, 300);
        }, 3500);
      }

      (function(){
        var cartList = document.querySelector('[data-eco-warehouse-cart-list]');
        var cartCount = document.querySelector('[data-eco-warehouse-cart-count]');
        var cartTotal = document.querySelector('[data-eco-warehouse-cart-total]');
        var cartClear = document.querySelector('[data-eco-warehouse-cart-clear]');
        function formatNumber(n){ return new Intl.NumberFormat('vi-VN').format(n); }
        function recalc(){
          if (!cartList) return;
          var items = cartList.querySelectorAll('[data-cart-item]');
          var total = 0;
          var totalQty = 0;
          items.forEach(function(item){
            total += Number(item.getAttribute('data-cart-total') || '0');
            totalQty += Number(item.getAttribute('data-cart-qty') || '0');
          });
          if (cartCount) cartCount.textContent = String(totalQty);
          if (cartTotal) cartTotal.textContent = formatNumber(total) + 'đ';
          var cartBox = cartList.closest('.eco-warehouse-cart');
          if (cartBox) cartBox.classList.toggle('is-empty', items.length === 0);
        }
        if (cartClear && cartList) {
          cartClear.addEventListener('click', function(){
            cartList.innerHTML = '';
            recalc();
          });
        }
        document.querySelectorAll('[data-eco-warehouse-buy-row]').forEach(function(row){
          var buyBtn = row.querySelector('[data-eco-warehouse-buy]');
          if (buyBtn) buyBtn.addEventListener('click', function(){
            if (!cartList) return;
            var name = row.getAttribute('data-tool-name') || 'Vật tư';
            var price = Number(row.getAttribute('data-tool-price') || '0');
            var stock = Number(row.getAttribute('data-tool-stock') || '0');
            var image = row.getAttribute('data-tool-image') || '';
            var category = row.getAttribute('data-tool-category') || 'Kho';
            var qty = 1;
            var total = price * qty;
            if (qty > stock) { ecoAlert('Sản phẩm này đã hết hàng trong kho.'); return; }
            var existingItem = cartList.querySelector('[data-cart-key="' + CSS.escape(name) + '"]');
            if (existingItem) {
              var currentQty = Number(existingItem.getAttribute('data-cart-qty') || '0');
              var newQty = currentQty + qty;
              if (newQty > stock) { ecoAlert('Sản phẩm này trong kho chỉ còn ' + stock + ' đơn vị.'); return; }
              var newTotal = price * newQty;
              existingItem.setAttribute('data-cart-qty', String(newQty));
              existingItem.setAttribute('data-cart-total', String(newTotal));
              var qdEl = existingItem.querySelector('[data-cart-qty-display]');
              var priceEl = existingItem.querySelector('[data-cart-price]');
              if (qdEl) qdEl.textContent = String(newQty);
              if (priceEl) priceEl.textContent = formatNumber(newTotal) + 'đ';
            } else {
              var item = document.createElement('div');
              item.className = 'eco-warehouse-cart-item';
              item.setAttribute('data-cart-item',     '1');
              item.setAttribute('data-cart-key',      name);
              item.setAttribute('data-cart-qty',      String(qty));
              item.setAttribute('data-cart-total',    String(total));
              item.setAttribute('data-cart-category', category);
              item.setAttribute('data-cart-stock',    String(stock));
              item.innerHTML = '<img src="' + image + '" alt="' + name.replace(/"/g, '&quot;') + '">'
                + '<div><div style="font-weight:800;font-size:14px">' + name + '</div>'
                + '<div style="font-size:11px;color:rgba(227,227,222,.5);margin-bottom:3px">' + category + '</div>'
                + '<div class="aitr-qty-row">'
                + '<button type="button" class="aitr-qty-btn" data-cart-dec>&minus;</button>'
                + '<span class="aitr-qty-display" data-cart-qty-display>' + qty + '</span>'
                + '<button type="button" class="aitr-qty-btn" data-cart-inc>+</button>'
                + '<span class="price" data-cart-price style="margin-left:6px">' + formatNumber(total) + 'đ</span>'
                + '</div></div>'
                + '<button type="button" data-cart-remove style="background:none;border:none;color:rgba(227,227,222,.5);cursor:pointer;align-self:flex-start;font-size:16px">🗑</button>';
              var removeBtn = item.querySelector('[data-cart-remove]');
              if (removeBtn) removeBtn.addEventListener('click', function(){ item.remove(); recalc(); });
              var decBtn = item.querySelector('[data-cart-dec]');
              var incBtn = item.querySelector('[data-cart-inc]');
              if (decBtn) decBtn.addEventListener('click', function(){
                var q = Number(item.getAttribute('data-cart-qty') || '1');
                if (q <= 1) { item.remove(); recalc(); return; }
                q--;
                item.setAttribute('data-cart-qty', String(q));
                item.setAttribute('data-cart-total', String(price * q));
                var qd = item.querySelector('[data-cart-qty-display]'); if (qd) qd.textContent = String(q);
                var pd = item.querySelector('[data-cart-price]'); if (pd) pd.textContent = formatNumber(price * q) + 'đ';
                recalc();
              });
              if (incBtn) incBtn.addEventListener('click', function(){
                var q = Number(item.getAttribute('data-cart-qty') || '1');
                var s = Number(item.getAttribute('data-cart-stock') || '0');
                if (q + 1 > s) { ecoAlert('Sản phẩm này trong kho chỉ còn ' + s + ' đơn vị.'); return; }
                q++;
                item.setAttribute('data-cart-qty', String(q));
                item.setAttribute('data-cart-total', String(price * q));
                var qd = item.querySelector('[data-cart-qty-display]'); if (qd) qd.textContent = String(q);
                var pd = item.querySelector('[data-cart-price]'); if (pd) pd.textContent = formatNumber(price * q) + 'đ';
                recalc();
              });
              cartList.prepend(item);
            }
            recalc();
          });
        });

        var urlParams = new URLSearchParams(window.location.search);
        var autoAddCode = urlParams.get('add_to_cart');
        if (autoAddCode) {
          var targetRow = document.querySelector('[data-eco-warehouse-buy-row][data-tool-code="' + CSS.escape(autoAddCode) + '"]');
          if (!targetRow) targetRow = document.querySelector('[data-eco-warehouse-buy-row][data-tool-name="' + CSS.escape(autoAddCode) + '"]');
          if (targetRow) {
            var autoAddBtn = targetRow.querySelector('[data-eco-warehouse-buy]');
            if (autoAddBtn) {
              setTimeout(function() {
                autoAddBtn.click();
                var newUrl = window.location.pathname;
                if (urlParams.has('garden')) newUrl += '?garden=' + encodeURIComponent(urlParams.get('garden'));
                window.history.replaceState({}, '', newUrl);
                ecoAlert('Đã thêm ' + autoAddCode + ' vào giỏ theo đề xuất của AI.');
              }, 400);
            }
          }
        }

        var profileTrigger=document.querySelector('[data-eco-warehouse-profile-trigger]');
        var profilePopup=document.querySelector('[data-eco-warehouse-profile-popup]');
        if(profileTrigger&&profilePopup){
          function closeProfile(){ profilePopup.hidden=true; profileTrigger.setAttribute('aria-expanded','false'); }
          profileTrigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var willOpen=profilePopup.hidden; profilePopup.hidden=!willOpen; profileTrigger.setAttribute('aria-expanded', willOpen?'true':'false'); });
          document.addEventListener('click', function(e){ if(!profilePopup.hidden && !profilePopup.contains(e.target) && e.target!==profileTrigger){ closeProfile(); } });
          document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeProfile(); });
        }
        document.querySelectorAll('.eco-warehouse-card').forEach(function(card){
          card.querySelectorAll('.eco-warehouse-specs, .eco-warehouse-spec').forEach(function(el){ el.remove(); });
          var priceRow = card.querySelector('.eco-warehouse-price-row');
          var buyRow = card.querySelector('.eco-warehouse-buy-row');
          if (priceRow && buyRow) {
            var node = priceRow.nextElementSibling;
            while (node && node !== buyRow) {
              var next = node.nextElementSibling;
              node.remove();
              node = next;
            }
          }
        });
        document.querySelectorAll('[data-eco-warehouse-slider]').forEach(function(track){
          var slider = track.parentElement;
          var prevBtn = slider ? slider.querySelector('[data-eco-warehouse-slider-prev]') : null;
          var nextBtn = slider ? slider.querySelector('[data-eco-warehouse-slider-next]') : null;
          var indicators = slider ? Array.prototype.slice.call(slider.querySelectorAll('[data-eco-warehouse-slider-dot]')) : [];
          var slides = Array.prototype.slice.call(track.querySelectorAll('[data-eco-warehouse-slide]'));
          var autoplayTimer = null;
          var suspendAutoplayUntil = 0;
          var isDown = false;
          var startX = 0;
          var startScrollLeft = 0;
          var scrollTimer = null;
          function gapSize(){
            try { return parseFloat(window.getComputedStyle(track).gap || '22') || 22; } catch (e) { return 22; }
          }
          function cardStep(){
            var firstCard = track.firstElementChild;
            if (!firstCard) return Math.max(280, Math.round(track.clientWidth * 0.85));
            return firstCard.getBoundingClientRect().width + gapSize();
          }
          function closestSlide(){
            var trackCenter = track.scrollLeft + (track.clientWidth / 2);
            var closest = null;
            var closestDistance = Infinity;
            slides.forEach(function(card){
              var cardCenter = card.offsetLeft + (card.offsetWidth / 2);
              var distance = Math.abs(trackCenter - cardCenter);
              if (distance < closestDistance) {
                closestDistance = distance;
                closest = card;
              }
            });
            return closest;
          }
          function updateUi(){
            var active = closestSlide();
            var activeIndex = active ? Number(active.getAttribute('data-slide-index') || '0') : 0;
            slides.forEach(function(card){
              var featured = card === active;
              card.classList.toggle('is-featured', featured);
              card.classList.toggle('is-dimmed', !featured && slides.length > 1);
            });
            indicators.forEach(function(dot, index){ dot.classList.toggle('is-active', index === activeIndex); });
          }
          function settleToNearest(){
            var active = closestSlide();
            if (!active) return;
            var left = active.offsetLeft - Math.max(0, (track.clientWidth - active.offsetWidth) / 2);
            track.scrollTo({ left: left, behavior: 'smooth' });
            updateUi();
          }
          function requestUpdate(){
            if (scrollTimer) window.clearTimeout(scrollTimer);
            scrollTimer = window.setTimeout(settleToNearest, 120);
            updateUi();
          }
          function pauseAutoplay(ms){ suspendAutoplayUntil = Date.now() + (ms || 5000); }
          function stopAutoplay(){ if (autoplayTimer) { window.clearInterval(autoplayTimer); autoplayTimer = null; } }
          function startAutoplay(){
            stopAutoplay();
            if (slides.length < 2) return;
            autoplayTimer = window.setInterval(function(){
              if (Date.now() < suspendAutoplayUntil || isDown || document.hidden) return;
              var active = closestSlide();
              var current = slides.indexOf(active);
              var next = slides[(current + 1) % slides.length] || slides[0];
              if (!next) return;
              var left = next.offsetLeft - Math.max(0, (track.clientWidth - next.offsetWidth) / 2);
              track.scrollTo({ left: left, behavior: 'smooth' });
            }, 5200);
          }
          function goToSlide(index){
            var target = slides[index];
            if (!target) return;
            pauseAutoplay(7000);
            var left = target.offsetLeft - Math.max(0, (track.clientWidth - target.offsetWidth) / 2);
            track.scrollTo({ left: left, behavior: 'smooth' });
          }
          if (prevBtn) prevBtn.addEventListener('click', function(){
            pauseAutoplay(7000);
            var active = closestSlide();
            var current = slides.indexOf(active);
            goToSlide((current - 1 + slides.length) % slides.length);
          });
          if (nextBtn) nextBtn.addEventListener('click', function(){
            pauseAutoplay(7000);
            var active = closestSlide();
            var current = slides.indexOf(active);
            goToSlide((current + 1) % slides.length);
          });
          indicators.forEach(function(dot){
            dot.addEventListener('click', function(){ goToSlide(Number(dot.getAttribute('data-slide-target') || '0')); });
          });
          track.addEventListener('scroll', requestUpdate, { passive:true });
          track.addEventListener('mouseenter', function(){ pauseAutoplay(100000); });
          track.addEventListener('mouseleave', function(){ suspendAutoplayUntil = Date.now() + 1400; });
          track.addEventListener('touchstart', function(){ pauseAutoplay(8000); }, { passive:true });
          track.addEventListener('mousedown', function(e){ if (e.button !== 0) return; isDown = true; startX = e.pageX; startScrollLeft = track.scrollLeft; track.classList.add('is-dragging'); pauseAutoplay(8000); });
          window.addEventListener('mousemove', function(e){ if (!isDown) return; e.preventDefault(); track.scrollLeft = startScrollLeft - (e.pageX - startX); updateUi(); });
          window.addEventListener('mouseup', function(){ if (!isDown) return; isDown = false; track.classList.remove('is-dragging'); requestUpdate(); });
          window.addEventListener('resize', function(){ requestUpdate(); });
          requestUpdate();
          startAutoplay();
        });
        recalc();
      })();
    </script>
    <script>
    (function () {
      var CART_KEY = 'aitr_cart';
      var AJAX_URL = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
      var NONCE    = <?php echo wp_json_encode(wp_create_nonce('aitrongcay_portal_actions')); ?>;
      var GARDEN   = <?php echo wp_json_encode($garden_key); ?>;
      <?php
      $_cu = wp_get_current_user();
      $_phone_meta = '';
      if ($_cu->ID) {
        foreach (['aitrongcay_phone', 'billing_phone', 'phone', 'mobile'] as $_mk) {
          $_v = trim((string) get_user_meta($_cu->ID, $_mk, true));
          if ($_v !== '') { $_phone_meta = $_v; break; }
        }
      }
      $_cu_name = '';
      if ($_cu->ID) {
        $_cu_name = trim((string) $_cu->first_name);
        if ($_cu_name === '') { $_cu_name = trim((string) $_cu->display_name); }
      }
      $_aitr_prefill = [
        'name'  => $_cu_name,
        'email' => $_cu->ID ? trim((string) $_cu->user_email) : '',
        'phone' => $_phone_meta,
      ];
      ?>
      var AITR_PREFILL = <?php echo wp_json_encode($_aitr_prefill, JSON_UNESCAPED_UNICODE); ?>;
      var AITR_PREFILL_KEY = 'aitr_checkout_prefill';

      var cartListEl  = document.querySelector('[data-eco-warehouse-cart-list]');
      var cartCountEl = document.querySelector('[data-eco-warehouse-cart-count]');
      var cartTotalEl = document.querySelector('[data-eco-warehouse-cart-total]');

      function fmtNum(n) { return new Intl.NumberFormat('vi-VN').format(n); }

      function getCartItems() {
        var items = [];
        if (!cartListEl) { return items; }
        cartListEl.querySelectorAll('[data-cart-item]').forEach(function (el) {
          var qty   = Number(el.getAttribute('data-cart-qty')   || '1');
          var total = Number(el.getAttribute('data-cart-total') || '0');
          var imgEl = el.querySelector('img');
          items.push({
            name:     el.getAttribute('data-cart-key') || '',
            price:    qty > 0 ? Math.round(total / qty) : 0,
            qty:      qty,
            image:    imgEl ? imgEl.src : '',
            category: el.getAttribute('data-cart-category') || 'Vat tu',
          });
        });
        return items;
      }

      function saveCartToStorage() {
        try { localStorage.setItem(CART_KEY, JSON.stringify({ items: getCartItems(), ts: Date.now() })); } catch (e) {}
      }

      function recalcCart() {
        if (!cartListEl) { return; }
        var its = cartListEl.querySelectorAll('[data-cart-item]');
        var total = 0, qty = 0;
        its.forEach(function (it) {
          total += Number(it.getAttribute('data-cart-total') || '0');
          qty   += Number(it.getAttribute('data-cart-qty')   || '0');
        });
        if (cartCountEl) { cartCountEl.textContent = String(qty); }
        if (cartTotalEl) { cartTotalEl.textContent = fmtNum(total) + 'd'; }
        var box = cartListEl.closest('.eco-warehouse-cart');
        if (box) { box.classList.toggle('is-empty', its.length === 0); }
      }

      function wireQtyBtns(item, price) {
        var decBtn = item.querySelector('[data-cart-dec]');
        var incBtn = item.querySelector('[data-cart-inc]');
        var rmBtn2 = item.querySelector('[data-cart-remove-2]');
        if (decBtn) decBtn.addEventListener('click', function () {
          var q = Number(item.getAttribute('data-cart-qty') || '1');
          if (q <= 1) { item.remove(); recalcCart(); saveCartToStorage(); updateHint(); return; }
          q--;
          item.setAttribute('data-cart-qty',   String(q));
          item.setAttribute('data-cart-total', String(price * q));
          var qd = item.querySelector('[data-cart-qty-display]'); if (qd) qd.textContent = String(q);
          var pd = item.querySelector('[data-cart-price]'); if (pd) pd.textContent = fmtNum(price * q) + 'đ';
          recalcCart();
        });
        if (incBtn) incBtn.addEventListener('click', function () {
          var q = Number(item.getAttribute('data-cart-qty') || '1');
          var s = Number(item.getAttribute('data-cart-stock') || '0');
          if (q + 1 > s) { ecoAlert('Sản phẩm này trong kho chỉ còn ' + s + ' đơn vị.'); return; }
          q++;
          item.setAttribute('data-cart-qty',   String(q));
          item.setAttribute('data-cart-total', String(price * q));
          var qd = item.querySelector('[data-cart-qty-display]'); if (qd) qd.textContent = String(q);
          var pd = item.querySelector('[data-cart-price]'); if (pd) pd.textContent = fmtNum(price * q) + 'đ';
          recalcCart();
        });
        if (rmBtn2) rmBtn2.addEventListener('click', function () { item.remove(); recalcCart(); saveCartToStorage(); updateHint(); });
      }

      function addCartItem(name, price, image, category, qty, stock) {
        if (!cartListEl) { return; }
        stock = stock || 0;
        if (qty > stock) { qty = stock; }
        if (qty <= 0) { return; }
        var existing = cartListEl.querySelector('[data-cart-key="' + CSS.escape(name) + '"]');
        if (existing) {
          var curQty   = Number(existing.getAttribute('data-cart-qty')   || '0');
          var newQty   = curQty + qty;
          if (newQty > stock) { newQty = stock; }
          var newTotal = price * newQty;
          existing.setAttribute('data-cart-qty',   String(newQty));
          existing.setAttribute('data-cart-total', String(newTotal));
          var qd = existing.querySelector('[data-cart-qty-display]'); if (qd) qd.textContent = String(newQty);
          var pd = existing.querySelector('[data-cart-price]'); if (pd) pd.textContent = fmtNum(newTotal) + 'đ';
        } else {
          var item = document.createElement('div');
          item.className = 'eco-warehouse-cart-item';
          item.setAttribute('data-cart-item',     '1');
          item.setAttribute('data-cart-key',      name);
          item.setAttribute('data-cart-qty',      String(qty));
          item.setAttribute('data-cart-total',    String(price * qty));
          item.setAttribute('data-cart-category', category || 'Vat tu');
          item.setAttribute('data-cart-stock',    String(stock));
          item.innerHTML = '<img src="' + (image || '') + '" alt="' + name.replace(/"/g, '&quot;') + '">'
            + '<div><div style="font-weight:800;font-size:14px">' + name + '</div>'
            + '<div style="font-size:11px;color:rgba(227,227,222,.5);margin-bottom:3px">' + (category || 'Vat tu') + '</div>'
            + '<div class="aitr-qty-row">'
            + '<button type="button" class="aitr-qty-btn" data-cart-dec>&minus;</button>'
            + '<span class="aitr-qty-display" data-cart-qty-display>' + qty + '</span>'
            + '<button type="button" class="aitr-qty-btn" data-cart-inc>+</button>'
            + '<span class="price" data-cart-price style="margin-left:6px">' + fmtNum(price * qty) + 'đ</span>'
            + '</div></div>'
            + '<button type="button" data-cart-remove-2 style="background:none;border:none;color:rgba(227,227,222,.5);cursor:pointer;align-self:flex-start;font-size:16px">&#128465;</button>';
          wireQtyBtns(item, price);
          cartListEl.prepend(item);
        }
        recalcCart();
      }

      function loadCartFromStorage() {
        try {
          var raw = localStorage.getItem(CART_KEY);
          if (!raw) { return; }
          var data = JSON.parse(raw);
          if (!data || !Array.isArray(data.items) || (Date.now() - (data.ts || 0)) > 86400000) {
            localStorage.removeItem(CART_KEY);
            return;
          }
          data.items.forEach(function (it) {
            if (!it.name) { return; }
            // Note: the stock check inside addCartItem might fail if stock info isn't saved in localStorage.
            // Wait, we need to pass stock from localStorage if it exists, or fetch it dynamically.
            // Since localStorage might not have stock, we just pass an artificially large stock for old items, 
            // OR we fetch actual stock from the DOM elements because the products are rendered on the page.
            var row = document.querySelector('[data-eco-warehouse-buy-row][data-tool-name="' + CSS.escape(it.name) + '"]');
            var realStock = row ? Number(row.getAttribute('data-tool-stock') || '0') : 0;
            addCartItem(it.name, it.price || 0, it.image || '', it.category || 'Vat tu', it.qty || 1, realStock);
          });
        } catch (e) {}
      }

      function updateHint() {
        var hint = document.getElementById('aitr-new-user-hint');
        if (!hint) { return; }
        try { if (localStorage.getItem('aitr_hint_dismissed') === '1') { hint.style.display = 'none'; return; } } catch (e) {}
        hint.style.display = 'flex';
      }

      // Observe cart DOM changes to auto-save to localStorage
      if (cartListEl) {
        var saveTimer;
        new MutationObserver(function () {
          clearTimeout(saveTimer);
          saveTimer = setTimeout(function () { saveCartToStorage(); updateHint(); }, 100);
        }).observe(cartListEl, { childList: true, subtree: true, attributes: true,
          attributeFilter: ['data-cart-qty', 'data-cart-total'] });
      }

      // Patch clear button
      var clearBtn = document.querySelector('[data-eco-warehouse-cart-clear]');
      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          try { localStorage.removeItem(CART_KEY); } catch (e) {}
          setTimeout(updateHint, 60);
        });
      }

      // Hint close
      var hintCloseBtn = document.querySelector('[data-aitr-close-hint]');
      if (hintCloseBtn) {
        hintCloseBtn.addEventListener('click', function () {
          try { localStorage.setItem('aitr_hint_dismissed', '1'); } catch (e) {}
          var hint = document.getElementById('aitr-new-user-hint');
          if (hint) { hint.style.display = 'none'; }
        });
      }

      // Checkout modal
      var overlay       = document.querySelector('[data-aitr-checkout-overlay]');
      var closeBtn      = document.querySelector('[data-aitr-checkout-close]');
      var submitBtn     = document.querySelector('[data-aitr-checkout-submit]');
      var summaryEl     = document.querySelector('[data-aitr-checkout-summary]');
      var checkoutTotal = document.querySelector('[data-aitr-checkout-total]');
      var msgEl         = document.querySelector('[data-aitr-checkout-msg]');
      var orderDone     = false;

      function resetCheckoutModal() {
        orderDone = false;
        var formPanel    = overlay ? overlay.querySelector('[data-aitr-checkout-panel="form"]') : null;
        var paymentPanel = overlay ? overlay.querySelector('[data-aitr-checkout-panel="payment"]') : null;
        if (formPanel)    { formPanel.style.display    = ''; }
        if (paymentPanel) { paymentPanel.style.display = 'none'; paymentPanel.innerHTML = ''; }
        if (submitBtn)    { submitBtn.disabled = false; submitBtn.textContent = 'Đặt dịch vụ ngay'; }
        if (msgEl)        { msgEl.style.display = 'none'; }
      }

      function openCheckout() {
        if (!overlay) { return; }
        var items = getCartItems();
        if (!items.length) { return; }
        resetCheckoutModal();
        var html = '<ul style="list-style:none;padding:0;margin:0;display:grid;gap:6px">';
        var tot  = 0;
        items.forEach(function (it) {
          var lt = it.price * it.qty;
          tot += lt;
          html += '<li style="display:flex;justify-content:space-between;font-size:13px">'
            + '<span>' + it.name + (it.qty > 1 ? ' <span style="color:rgba(227,227,222,.5)">x' + it.qty + '</span>' : '') + '</span>'
            + '<span style="color:#6fdba8;font-weight:700">' + fmtNum(lt) + 'đ</span></li>';
        });
        html += '</ul>';
        if (summaryEl)     { summaryEl.innerHTML = html; }
        if (checkoutTotal) { checkoutTotal.textContent = fmtNum(tot) + 'đ'; }
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
        // Auto-fill: WP account → localStorage → để trống
        var _nameEl  = overlay.querySelector('[data-aitr-order-name]');
        var _phoneEl = overlay.querySelector('[data-aitr-order-phone]');
        var _emailEl = overlay.querySelector('[data-aitr-order-email]');
        var _ls = {};
        try { _ls = JSON.parse(localStorage.getItem(AITR_PREFILL_KEY) || '{}'); } catch (_e) {}
        if (_nameEl  && !_nameEl.value)  _nameEl.value  = AITR_PREFILL.name  || _ls.name  || '';
        if (_phoneEl && !_phoneEl.value) _phoneEl.value = AITR_PREFILL.phone || _ls.phone || '';
        if (_emailEl && !_emailEl.value) _emailEl.value = AITR_PREFILL.email || _ls.email || '';
      }

      function closeCheckout() {
        if (!overlay) { return; }
        overlay.hidden = true;
        document.body.style.overflow = '';
      }

      if (closeBtn) { closeBtn.addEventListener('click', closeCheckout); }
      if (overlay)  { overlay.addEventListener('click', function (e) { if (e.target === overlay) { closeCheckout(); } }); }
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && overlay && !overlay.hidden) { closeCheckout(); } });

      var legacyBtn = document.querySelector('.eco-warehouse-cart-checkout');
      if (legacyBtn) { legacyBtn.addEventListener('click', openCheckout); }

      if (submitBtn) {
        submitBtn.addEventListener('click', function () {
          if (orderDone) { closeCheckout(); return; }

          var nameEl  = overlay.querySelector('[data-aitr-order-name]');
          var phoneEl = overlay.querySelector('[data-aitr-order-phone]');
          var emailEl = overlay.querySelector('[data-aitr-order-email]');
          var noteEl  = overlay.querySelector('[data-aitr-order-note]');
          var name    = nameEl  ? nameEl.value.trim()  : '';
          var phone   = phoneEl ? phoneEl.value.trim() : '';
          var email   = emailEl ? emailEl.value.trim() : '';
          var note    = noteEl  ? noteEl.value.trim()  : '';

          if (!name || !phone) {
            if (msgEl) { msgEl.style.display = 'block'; msgEl.style.color = '#ffa4a4'; msgEl.textContent = 'Vui lòng nhập họ tên và số điện thoại.'; }
            return;
          }

          var items   = getCartItems();
          if (!items.length) { return; }
          var total   = items.reduce(function (s, it) { return s + it.price * it.qty; }, 0);
          var plantNm = '';
          try { var cd = JSON.parse(localStorage.getItem(CART_KEY) || '{}'); plantNm = cd.plant || ''; } catch (e2) {}

          submitBtn.disabled = true; submitBtn.textContent = 'Đang xử lý...';
          if (msgEl) { msgEl.style.display = 'none'; }

          var fd = new FormData();
          fd.append('action',          'aitrongcay_place_order');
          fd.append('nonce',           NONCE);
          fd.append('items',           JSON.stringify(items));
          fd.append('plant_name',      plantNm);
          fd.append('customer_name',   name);
          fd.append('customer_phone',  phone);
          fd.append('customer_email',  email);
          fd.append('note',            note);
          fd.append('total',           String(total));
          // garden_key omitted — assigned by admin after payment confirmed

          fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
              if (res.success) {
                var d = res.data || {};
                // Lưu thông tin khách để gợi ý lần sau
                try { localStorage.setItem(AITR_PREFILL_KEY, JSON.stringify({ name: name, phone: phone, email: email })); } catch (_e2) {}
                // Clear cart
                try { localStorage.removeItem(CART_KEY); } catch (e3) {}
                if (cartListEl) { cartListEl.innerHTML = ''; }
                recalcCart();
                updateHint();

                // Build bank transfer info panel
                var qrHtml = (d.qr_url && d.account_number)
                  ? '<div style="text-align:center;margin:14px 0">'
                    + '<img src="' + d.qr_url + '" alt="QR chuyển khoản" width="180" height="180" style="border-radius:12px;display:block;margin:0 auto">'
                    + '<p style="font-size:11px;color:rgba(227,227,222,.45);margin:5px 0 0">Quét QR bằng app ngân hàng</p></div>'
                  : '';
                var emailNote = d.has_email
                  ? '<p style="font-size:11px;color:rgba(227,227,222,.4);text-align:center;margin:8px 0 0">📧 Email xác nhận và thông tin CK đã gửi tới hộp thư của bạn.</p>'
                  : '';

                var paymentHtml = '<div style="text-align:center;padding-bottom:8px">'
                  + '<div style="font-size:44px;margin-bottom:4px">✅</div>'
                  + '<h3 style="margin:0 0 3px;color:#6fdba8;font-size:18px">Đặt dịch vụ thành công!</h3>'
                  + '<p style="color:rgba(227,227,222,.55);font-size:12px;margin:0">Mã đơn hàng của bạn</p>'
                  + '<p style="font-size:22px;font-weight:900;color:#6fdba8;margin:5px 0 0;letter-spacing:.5px">' + (d.order_id || '') + '</p>'
                  + '</div>'
                  + '<div style="background:rgba(41,43,39,.8);border-radius:12px;padding:14px 16px;margin:14px 0 10px">'
                  + '<p style="font-size:11px;font-weight:700;color:rgba(227,227,222,.4);margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px">Thông tin chuyển khoản</p>'
                  + '<table style="width:100%;font-size:13px;border-collapse:collapse">'
                  + '<tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Ngân hàng</td><td style="font-weight:700;text-align:right">' + (d.bank_name || '—') + '</td></tr>'
                  + '<tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Số tài khoản</td><td style="font-weight:700;font-size:15px;letter-spacing:.5px;text-align:right">' + (d.account_number || '—') + '</td></tr>'
                  + '<tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Chủ TK</td><td style="font-weight:700;text-align:right">' + (d.account_name || '—') + '</td></tr>'
                  + '<tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Số tiền</td><td style="font-weight:900;color:#f87171;font-size:16px;text-align:right">' + (d.total_fmt || '0') + 'đ</td></tr>'
                  + '<tr style="background:rgba(251,191,36,.08)"><td style="padding:6px 0;color:rgba(227,227,222,.55)">Nội dung CK ⚠️</td><td style="font-weight:900;color:#fbbf24;font-size:15px;text-align:right">' + (d.order_id || '') + '</td></tr>'
                  + '</table></div>'
                  + qrHtml
                  + '<div style="background:rgba(249,115,22,.1);border-left:3px solid #f97316;padding:9px 12px;border-radius:0 8px 8px 0;font-size:12px;color:#fdba74;margin-bottom:4px">'
                  + 'Vui lòng ghi đúng nội dung chuyển khoản để chúng tôi xác nhận đơn nhanh nhất.'
                  + '</div>'
                  + emailNote;

                var formPanel    = overlay.querySelector('[data-aitr-checkout-panel="form"]');
                var paymentPanel = overlay.querySelector('[data-aitr-checkout-panel="payment"]');
                if (formPanel)    { formPanel.style.display    = 'none'; }
                if (paymentPanel) { paymentPanel.style.display = ''; paymentPanel.innerHTML = paymentHtml; }

                orderDone = true;
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Đã hiểu, tôi sẽ chuyển khoản →';
              } else {
                if (msgEl) {
                  msgEl.style.display = 'block';
                  msgEl.style.color   = '#ffa4a4';
                  msgEl.textContent   = (res.data && res.data.message) ? res.data.message : 'Có lỗi xảy ra, vui lòng thử lại.';
                }
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Đặt dịch vụ ngay';
              }
            })
            .catch(function () {
              if (msgEl) { msgEl.style.display = 'block'; msgEl.style.color = '#ffa4a4'; msgEl.textContent = 'Lỗi kết nối, vui lòng thử lại.'; }
              submitBtn.disabled    = false;
              submitBtn.textContent = 'Đặt dịch vụ ngay';
            });
        });
      }

      // Init
      loadCartFromStorage();
      updateHint();
    }());
    </script>
  </div>
</section>
