<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_virtual_pages(): array
{
    return [
        'cach-hoat-dong' => ['title' => 'Giới thiệu', 'template' => 'template-parts/virtual/cach-hoat-dong.php'],
        'cho-que' => ['title' => 'Chợ quê', 'template' => 'template-parts/virtual/cho-que.php'],
        'faq' => ['title' => 'Câu hỏi thường gặp', 'template' => 'template-parts/virtual/faq.php'],
        'dang-ky-tu-van' => ['title' => 'Đăng ký tư vấn', 'template' => 'template-parts/virtual/dang-ky-tu-van.php'],
        'onboarding' => ['title' => 'Onboarding', 'template' => 'template-parts/virtual/onboarding.php'],
        'portal/onboarding-cay-moi' => ['title' => 'Onboarding cây mới', 'template' => 'template-parts/virtual/onboarding-cay-moi.php'],
        'dang-nhap' => ['title' => 'Đăng nhập', 'template' => 'template-parts/virtual/dang-nhap.php'],
        'dang-xuat' => ['title' => 'Đăng xuất', 'template' => 'template-parts/virtual/dang-nhap.php'],
        'tai-khoan' => ['title' => 'Tài khoản', 'template' => 'template-parts/virtual/tai-khoan.php'],
        'nang-cap-goi' => ['title' => 'Nâng cấp gói', 'template' => 'template-parts/virtual/nang-cap-goi.php'],
        'portal' => ['title' => 'Portal', 'template' => 'template-parts/virtual/portal.php'],
        'portal/dashboard' => ['title' => 'Tổng quan khu vườn', 'template' => 'template-parts/virtual/portal.php'],
        'portal/dashboard-2' => ['title' => 'Tổng quan khu vườn 2', 'template' => 'template-parts/virtual/dashboard-2.php'],
        'portal/hydration' => ['title' => 'Hydration Center', 'template' => 'template-parts/virtual/hydration.php'],
        'portal/soil-health' => ['title' => 'Soil Health', 'template' => 'template-parts/virtual/soil-health.php'],
        'portal/flower-bio' => ['title' => 'Flower Bio', 'template' => 'template-parts/virtual/flower-bio.php'],
        'portal/webcam' => ['title' => 'Live webcam 24/7', 'template' => 'template-parts/virtual/portal.php'],
        'portal/tinh-trang-vuon' => ['title' => 'Tình trạng khu vườn', 'template' => 'template-parts/virtual/portal.php'],
        'portal/nhat-ky-cham-soc' => ['title' => 'Nhật ký chăm sóc', 'template' => 'template-parts/virtual/portal.php'],
        'portal/chat-luong-an-toan' => ['title' => 'Hồ sơ chất lượng', 'template' => 'template-parts/virtual/portal.php'],
        'portal/tro-ly-ai' => ['title' => 'AI gardener', 'template' => 'template-parts/virtual/portal.php'],
        'portal/kho-nong-cu' => ['title' => 'Kho nông cụ', 'template' => 'template-parts/virtual/portal.php'],
        'portal/kho-nong-cu-2' => ['title' => 'Kho nông cụ 2', 'template' => 'template-parts/virtual/kho-nong-cu-2.php'],
        'portal/vat-tu-thiet-bi-moi' => ['title' => 'Vật tư / thiết bị mới', 'template' => 'template-parts/virtual/vat-tu-thiet-bi-moi.php'],
        'portal/giam-sat-khoang' => ['title' => 'Rack Monitor – 3 khoang trồng', 'template' => 'template-parts/virtual/giam-sat-khoang.php'],
        'portal/hang-xom' => ['title' => 'Hàng xóm', 'template' => 'template-parts/virtual/portal.php'],
        'portal/ban-be' => ['title' => 'Hàng xóm (cũ)', 'template' => 'template-parts/virtual/portal.php'],
        'portal/doi-diem'  => ['title' => 'Cửa hàng đổi điểm', 'template' => 'template-parts/virtual/doi-diem.php'],
        'portal/chia-se-khu-vuon' => ['title' => 'Chia sẻ khu vườn', 'template' => 'template-parts/virtual/portal.php'],
        'portal/lich-su-giao-dich' => ['title' => 'Sổ giao dịch & Hợp đồng', 'template' => 'template-parts/virtual/lich-su-giao-dich.php'],
    ];
}

function aitrongcay_portal_nav_items(): array
{
    return [
        ['slug' => 'portal/kho-nong-cu', 'label' => 'Kho nông cụ'],
        ['slug' => 'portal/tro-ly-ai', 'label' => 'Trợ lý AI'],
        ['slug' => 'portal/hang-xom', 'label' => 'Hàng xóm'],
        ['slug' => 'portal/chia-se-khu-vuon', 'label' => 'Chia sẻ khu vườn'],
    ];
}

function aitrongcay_register_virtual_page_rules(): void
{
    add_rewrite_tag('%aitrongcay_page%', '(.+)');

    foreach (array_keys(aitrongcay_virtual_pages()) as $slug) {
        add_rewrite_rule('^' . preg_quote($slug, '#') . '/?$', 'index.php?aitrongcay_page=' . $slug, 'top');
    }
}
add_action('init', 'aitrongcay_register_virtual_page_rules');

function aitrongcay_maybe_flush_virtual_rules(): void
{
    $version = (string) wp_get_theme()->get('Version');
    $option_key = 'aitrongcay_virtual_rules_version';
    if (get_option($option_key) === $version) {
        return;
    }

    aitrongcay_register_virtual_page_rules();
    flush_rewrite_rules(false);
    update_option($option_key, $version, false);
}
add_action('init', 'aitrongcay_maybe_flush_virtual_rules', 20);

function aitrongcay_force_flush_virtual_rules_once(): void
{
    $token = isset($_GET['aitr_flush_routes']) ? (string) $_GET['aitr_flush_routes'] : '';
    if ($token !== '20260403-banbe-share') {
        return;
    }

    aitrongcay_register_virtual_page_rules();
    flush_rewrite_rules(false);
    update_option('aitrongcay_virtual_rules_version', (string) wp_get_theme()->get('Version'), false);
}
add_action('init', 'aitrongcay_force_flush_virtual_rules_once', 99);

function aitrongcay_current_virtual_page(): ?array
{
    $slug = get_query_var('aitrongcay_page');
    $pages = aitrongcay_virtual_pages();

    if (! is_string($slug) || $slug === '' || ! isset($pages[$slug])) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        if ($request_uri !== '') {
            $path = (string) wp_parse_url($request_uri, PHP_URL_PATH);
            $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
            if ($home_path !== '' && $home_path !== '/' && str_starts_with($path, rtrim($home_path, '/'))) {
                $path = substr($path, strlen(rtrim($home_path, '/')));
            }
            $path = trim($path, '/');
            if ($path !== '' && isset($pages[$path])) {
                $slug = $path;
            }
        }
    }

    if (! is_string($slug) || $slug === '' || ! isset($pages[$slug])) {
        return null;
    }

    $native_page = get_page_by_path($slug);
    if ($native_page instanceof WP_Post) {
        return null;
    }

    return $pages[$slug] + ['slug' => $slug];
}

function aitrongcay_redirect_legacy_dashboard_to_dashboard_2(): void
{
    $page = aitrongcay_current_virtual_page();
    if (! is_array($page) || ($page['slug'] ?? '') !== 'portal/dashboard') {
        return;
    }

    $target = home_url('/portal/dashboard-2/');
    $query = isset($_SERVER['QUERY_STRING']) ? trim((string) $_SERVER['QUERY_STRING']) : '';
    if ($query !== '') {
        $target .= '?' . $query;
    }

    wp_safe_redirect($target, 301);
    exit;
}
add_action('template_redirect', 'aitrongcay_redirect_legacy_dashboard_to_dashboard_2', 1);

function aitrongcay_redirect_legacy_ban_be_to_hang_xom(): void
{
    $page = aitrongcay_current_virtual_page();
    if (! is_array($page) || ($page['slug'] ?? '') !== 'portal/ban-be') {
        return;
    }

    $target = home_url('/portal/hang-xom/');
    $query = isset($_SERVER['QUERY_STRING']) ? trim((string) $_SERVER['QUERY_STRING']) : '';
    if ($query !== '') {
        $target .= '?' . $query;
    }

    wp_safe_redirect($target, 301);
    exit;
}
add_action('template_redirect', 'aitrongcay_redirect_legacy_ban_be_to_hang_xom', 2);

function aitrongcay_redirect_removed_public_pages(): void
{
    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    if (! in_array($path, ['an-toan-thuc-pham'], true)) {
        return;
    }

    wp_safe_redirect(home_url('/'), 301);
    exit;
}
add_action('template_redirect', 'aitrongcay_redirect_removed_public_pages', 3);

function aitrongcay_logout_url(): string
{
    return home_url('/dang-xuat/');
}

function aitrongcay_handle_frontend_logout(): void
{
    $page = aitrongcay_current_virtual_page();
    if (! is_array($page) || ($page['slug'] ?? '') !== 'dang-xuat') {
        return;
    }

    if (is_user_logged_in()) {
        wp_logout();
    }

    wp_safe_redirect(home_url('/dang-nhap/?auth_status=logged-out'));
    exit;
}
add_action('template_redirect', 'aitrongcay_handle_frontend_logout', 4);

function aitrongcay_mark_virtual_request_ok(): void
{
    global $wp_query, $post;

    status_header(200);

    if ($wp_query instanceof WP_Query) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
    }

    // body_class() reads $post->ID, post_type, post_parent — stub it to prevent warnings.
    if (! $post instanceof WP_Post) {
        $stub = new stdClass();
        $stub->ID = 0;
        $stub->post_author = '0';
        $stub->post_date = '0000-00-00 00:00:00';
        $stub->post_date_gmt = '0000-00-00 00:00:00';
        $stub->post_content = '';
        $stub->post_title = '';
        $stub->post_excerpt = '';
        $stub->post_status = 'publish';
        $stub->comment_status = 'closed';
        $stub->ping_status = 'closed';
        $stub->post_password = '';
        $stub->post_name = '';
        $stub->to_ping = '';
        $stub->pinged = '';
        $stub->post_modified = '0000-00-00 00:00:00';
        $stub->post_modified_gmt = '0000-00-00 00:00:00';
        $stub->post_content_filtered = '';
        $stub->post_parent = 0;
        $stub->guid = '';
        $stub->menu_order = 0;
        $stub->post_type = 'page';
        $stub->post_mime_type = '';
        $stub->comment_count = '0';
        $stub->filter = 'raw';
        $post = new WP_Post($stub);
        if ($wp_query instanceof WP_Query) {
            $wp_query->post = $post;
        }
    }
}

function aitrongcay_prevent_virtual_page_404(bool $preempt, WP_Query $query): bool
{
    if (is_admin() || ! $query->is_main_query()) {
        return $preempt;
    }

    if (! aitrongcay_current_virtual_page()) {
        return $preempt;
    }

    aitrongcay_mark_virtual_request_ok();
    return true;
}
add_filter('pre_handle_404', 'aitrongcay_prevent_virtual_page_404', 10, 2);

function aitrongcay_template_include_virtual_page(string $template): string
{
    $page = aitrongcay_current_virtual_page();
    if ($page) {
        aitrongcay_mark_virtual_request_ok();
        return get_template_directory() . '/templates/virtual-page.php';
    }

    if (is_page()) {
        $native_slug = get_post_field('post_name', get_queried_object_id());
        $native_overrides = [
            'cach-hoat-dong' => 'template-parts/virtual/cach-hoat-dong.php',
            'cho-que' => 'template-parts/virtual/cho-que.php',
            'tai-khoan' => 'template-parts/virtual/tai-khoan.php',
        ];
        if (is_string($native_slug) && isset($native_overrides[$native_slug])) {
            return get_template_directory() . '/templates/virtual-page.php';
        }
    }

    return $template;
}
add_filter('template_include', 'aitrongcay_template_include_virtual_page');

function aitrongcay_virtual_page_title(string $title): string
{
    $page = aitrongcay_current_virtual_page();
    if (! $page) {
        return $title;
    }

    return $page['title'] . ' – ' . aitrongcay_company_profile()['brand'];
}
add_filter('pre_get_document_title', 'aitrongcay_virtual_page_title');

function aitrongcay_virtual_body_classes(array $classes): array
{
    $page = aitrongcay_current_virtual_page();
    if (! $page) {
        return $classes;
    }

    $classes[] = 'virtual-page';
    $classes[] = 'virtual-page-' . sanitize_html_class(str_replace('/', '-', $page['slug']));
    return $classes;
}
add_filter('body_class', 'aitrongcay_virtual_body_classes');
