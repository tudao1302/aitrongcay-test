<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_menu_items(string $location, array $fallback): array
{
    $locations = get_nav_menu_locations();
    $menu_id = $locations[$location] ?? null;

    if (! $menu_id) {
        return $fallback;
    }

    $items = wp_get_nav_menu_items($menu_id);
    if (! is_array($items) || $items === []) {
        return $fallback;
    }

    $normalized = [];
    foreach ($items as $item) {
        if (! isset($item->title, $item->url)) {
            continue;
        }

        $normalized[] = [
            'label' => wp_strip_all_tags((string) $item->title),
            'url' => (string) $item->url,
        ];
    }

    return $normalized !== [] ? $normalized : $fallback;
}

function aitrongcay_primary_nav_items(): array
{
    return aitrongcay_menu_items('primary', [
        ['label' => 'Giới thiệu', 'url' => home_url('/cach-hoat-dong/')],
        ['label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
    ]);
}

function aitrongcay_footer_groups(): array
{
    return [
        'explore' => [
            'title' => 'Khám phá',
            'items' => aitrongcay_menu_items('footer_public', [
                ['label' => 'Giới thiệu', 'url' => home_url('/cach-hoat-dong/')],
                ['label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
            ]),
        ],
        'trust' => [
            'title' => 'Niềm tin',
            'items' => aitrongcay_menu_items('footer_trust', []),
        ],
        'start' => [
            'title' => 'Bắt đầu',
            'items' => aitrongcay_menu_items('footer_start', [
                ['label' => 'Đăng ký tư vấn', 'url' => home_url('/dang-ky-tu-van/')],
                ['label' => 'Onboarding', 'url' => home_url('/onboarding/')],
                ['label' => 'Đăng nhập', 'url' => home_url('/dang-nhap/')],
            ]),
        ],
    ];
}

function aitrongcay_company_profile(): array
{
    return [
        'brand' => 'Ai trồng cây',
        'tagline' => 'Một khu vườn số cho gia đình, đủ rõ để theo dõi và đủ yên để gắn bó mỗi ngày.',
        'company' => 'CÔNG TY CỔ PHẦN NGHIÊN CỨU GIẢI PHÁP VÀ PHÁT TRIỂN CÔNG NGHỆ XANH',
        'address' => 'Số 180A, đường Âu Cơ, Phường Tứ Liên, Quận Tây Hồ, Thành phố Hà Nội, Việt Nam',
        'phone' => '0983.660.988 – 0876.666.114',
        'description' => 'Mô hình vườn thuê số hóa cho gia đình muốn theo dõi khu vườn của mình bằng webcam, care log, dữ liệu môi trường và hồ sơ theo lô.',
        'meta_description' => 'Ai trồng cây là mô hình vườn thuê số hóa cho gia đình: có webcam, care log, dữ liệu môi trường và hồ sơ theo lô để việc theo dõi trở nên rõ ràng và đáng tin hơn.',
        'og_image' => home_url('/wp-content/themes/aitrongcay/assets/images/hero-greenhouse.svg'),
        'favicon' => home_url('/wp-content/themes/aitrongcay/assets/images/favicon.svg'),
    ];
}

function aitrongcay_meta_context(): array
{
    $company = aitrongcay_company_profile();
    $meta = [
        'description' => $company['meta_description'],
        'og_image' => $company['og_image'],
    ];

    $slug = '';
    if (function_exists('get_query_var')) {
        $slug = (string) get_query_var('aitrongcay_page');
    }
    if ($slug === '' && is_page()) {
        $slug = (string) get_post_field('post_name', get_queried_object_id());
    }

    if ($slug === 'cach-hoat-dong') {
        $meta['description'] = 'Giới thiệu Ai trồng cây: mô hình vườn thuê số hóa cho gia đình với webcam, care log, dữ liệu môi trường và hồ sơ theo lô để việc theo dõi thực phẩm trở nên rõ ràng, truyền cảm hứng và đáng tin hơn.';
        $meta['og_image'] = home_url('/wp-content/themes/aitrongcay/assets/images/story-morning.svg');
    }

    return $meta;
}
