<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/site-config.php';
require_once get_template_directory() . '/inc/virtual-pages.php';
require_once get_template_directory() . '/inc/homepage-settings.php';
require_once get_template_directory() . '/inc/page-content-settings.php';
require_once get_template_directory() . '/inc/portal-garden-data.php';
require_once get_template_directory() . '/inc/portal-device-mapping-admin.php';
require_once get_template_directory() . '/inc/plant-onboarding-db.php';
require_once get_template_directory() . '/inc/portal-ai-agent.php';
require_once get_template_directory() . '/inc/portal-gemini-analysis.php';
require_once get_template_directory() . '/inc/tray-config.php';
require_once get_template_directory() . '/inc/timelapse.php';
require_once get_template_directory() . '/inc/auto-pump.php';
require_once get_template_directory() . '/inc/api-settings.php';
require_once get_template_directory() . '/inc/orders.php';
require_once get_template_directory() . '/inc/supplies-admin.php';
require_once get_template_directory() . '/inc/portal-unified-admin-beta.php';
require_once get_template_directory() . '/inc/blynk-webhook.php';
require_once get_template_directory() . '/inc/portal-robot-api.php';
require_once get_template_directory() . '/inc/notifications.php';
require_once get_template_directory() . '/inc/rack-handoff.php';
function aitrongcay_theme_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');

    register_nav_menus([
        'primary' => __('Primary Menu', 'aitrongcay'),
        'footer_public' => __('Footer Public Menu', 'aitrongcay'),
        'footer_trust' => __('Footer Trust Menu', 'aitrongcay'),
        'footer_start' => __('Footer Start Menu', 'aitrongcay'),
    ]);
}
add_action('after_setup_theme', 'aitrongcay_theme_setup');

function aitrongcay_eco_nav_items(): array
{
    return [
        ['key' => 'gioi-thieu', 'label' => 'Giới thiệu', 'short_label' => 'Giới thiệu', 'icon' => '✨', 'url' => home_url('/cach-hoat-dong/')],
        ['key' => 'cho-que', 'label' => 'Chợ quê', 'short_label' => 'Chợ', 'icon' => '🏪', 'url' => home_url('/cho-que/')],
        ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'short_label' => 'Hàng xóm', 'icon' => '👥', 'url' => home_url('/portal/hang-xom/')],
        ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'short_label' => 'Kho', 'icon' => '🗄️', 'url' => home_url('/portal/kho-nong-cu-2/')],
        ['key' => 'dashboard', 'label' => 'Khu vườn', 'short_label' => 'Vườn', 'icon' => '🪴', 'url' => home_url('/portal/dashboard-2/')],
    ];
}

function aitrongcay_mobile_bottom_nav_items(string $garden_key = ''): array
{
    $items = [
        ['key' => 'dashboard', 'label' => 'Khu vườn', 'short_label' => 'Vườn', 'icon' => '🪴', 'url' => home_url('/portal/dashboard-2/')],
        ['key' => 'cho-que', 'label' => 'Chợ quê', 'short_label' => 'Chợ', 'icon' => '🏪', 'url' => home_url('/cho-que/')],
        ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'short_label' => 'Hàng xóm', 'icon' => '👥', 'url' => home_url('/portal/hang-xom/')],
        ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'short_label' => 'Kho', 'icon' => '🗄️', 'url' => home_url('/portal/kho-nong-cu-2/')],
    ];

    if ($garden_key === '') {
        return $items;
    }

    foreach ($items as &$item) {
        if (in_array($item['key'], ['dashboard', 'hang-xom', 'kho-nong-cu'], true)) {
            $item['url'] = add_query_arg('garden', rawurlencode($garden_key), $item['url']);
        }
    }
    unset($item);

    return $items;
}

function aitrongcay_register_post_types(): void
{
    register_post_type('aitr_consultation', [
        'labels' => [
            'name' => __('Đăng ký tư vấn', 'aitrongcay'),
            'singular_name' => __('Đăng ký tư vấn', 'aitrongcay'),
            'menu_name' => __('Leads tư vấn', 'aitrongcay'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-sprout',
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    register_post_type('aitr_market_post', [
        'labels' => [
            'name' => __('Tin Chợ quê', 'aitrongcay'),
            'singular_name' => __('Tin Chợ quê', 'aitrongcay'),
            'menu_name' => __('Tin Chợ quê', 'aitrongcay'),
        ],
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-store',
        'supports' => ['title', 'editor', 'thumbnail', 'author', 'custom-fields'],
        'has_archive' => false,
        'rewrite' => false,
        'query_var' => true,
        'show_in_rest' => true,
    ]);

    register_post_type('aitr_food_safety_scan', [
        'labels' => [
            'name' => __('Phân tích chất lượng vườn', 'aitrongcay'),
            'singular_name' => __('Phân tích chất lượng vườn', 'aitrongcay'),
            'menu_name' => __('Ảnh phân tích vườn', 'aitrongcay'),
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-microscope',
        'supports' => ['title', 'editor', 'author', 'custom-fields'],
    ]);
}
add_action('init', 'aitrongcay_register_post_types');

function aitrongcay_register_market_admin_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=aitr_market_post',
        __('Quản trị tin người dùng', 'aitrongcay'),
        __('Quản trị tin người dùng', 'aitrongcay'),
        'edit_posts',
        'aitr-market-admin',
        'aitrongcay_render_market_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_register_market_admin_menu');

function aitrongcay_market_status_label(string $status): string
{
    return match ($status) {
        'publish' => 'Đang hiển thị',
        'pending' => 'Chờ duyệt',
        'draft' => 'Đang ẩn',
        'trash' => 'Thùng rác',
        default => ucfirst($status),
    };
}

function aitrongcay_render_market_admin_page(): void
{
    if (! current_user_can('edit_posts')) {
        wp_die(esc_html__('Bạn không có quyền truy cập mục này.', 'aitrongcay'));
    }

    wp_safe_redirect(admin_url('edit.php?post_type=aitr_market_post'));
    exit;
}

function aitrongcay_market_admin_columns(array $columns): array
{
    $date = $columns['date'] ?? 'Ngày';

    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox" />',
        'title' => 'Tiêu đề',
        'market_thumb' => 'Ảnh',
        'market_author' => 'Người đăng',
        'market_type' => 'Loại tin',
        'market_contact' => 'Liên hệ',
        'market_status' => 'Trạng thái',
        'date' => $date,
    ];
}
add_filter('manage_aitr_market_post_posts_columns', 'aitrongcay_market_admin_columns');

function aitrongcay_render_market_admin_column(string $column, int $post_id): void
{
    if (get_post_type($post_id) !== 'aitr_market_post') {
        return;
    }

    $structured = aitrongcay_get_market_structured_data($post_id);
    $author = get_user_by('id', (int) get_post_field('post_author', $post_id));

    switch ($column) {
        case 'market_thumb':
            $thumb = get_the_post_thumbnail($post_id, [72, 72], ['style' => 'width:72px;height:72px;object-fit:cover;border-radius:12px;']);
            if ($thumb !== '') {
                echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } else {
                echo '<span style="display:inline-flex;width:72px;height:72px;border-radius:12px;background:#f1f3f2;align-items:center;justify-content:center;color:#68806d">—</span>';
            }
            break;

        case 'market_author':
            if ($author instanceof WP_User) {
                echo '<strong>' . esc_html($author->display_name ?: $author->user_login) . '</strong><br>';
                echo '<span style="color:#666">' . esc_html($author->user_email) . '</span>';
                $phone = (string) get_user_meta($author->ID, 'aitrongcay_phone', true);
                if ($phone !== '') {
                    echo '<br><span style="color:#666">' . esc_html($phone) . '</span>';
                }
            } else {
                echo '—';
            }
            break;

        case 'market_type':
            $parts = array_filter([
                (string) ($structured['category'] ?? ''),
                (string) ($structured['offer_type'] ?? ''),
                (string) ($structured['quantity'] ?? ''),
            ]);
            echo esc_html($parts ? implode(' • ', $parts) : '—');
            break;

        case 'market_contact':
            $parts = array_filter([
                (string) ($structured['contact_text'] ?? ''),
                (string) ($structured['availability'] ?? ''),
                (string) ($structured['area'] ?? ''),
            ]);
            echo esc_html($parts ? implode(' • ', $parts) : '—');
            break;

        case 'market_status':
            $status = (string) get_post_status($post_id);
            echo '<strong>' . esc_html(aitrongcay_market_status_label($status)) . '</strong>';
            echo '<div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">';

            if ($status !== 'publish') {
                $approve_url = wp_nonce_url(admin_url('admin-post.php?action=aitrongcay_market_admin_set_status&post_id=' . $post_id . '&status=publish'), 'aitr_market_admin_status_' . $post_id);
                echo '<a class="button button-small button-primary" href="' . esc_url($approve_url) . '">Duyệt</a>';
            }

            if ($status !== 'draft') {
                $hide_url = wp_nonce_url(admin_url('admin-post.php?action=aitrongcay_market_admin_set_status&post_id=' . $post_id . '&status=draft'), 'aitr_market_admin_status_' . $post_id);
                echo '<a class="button button-small" href="' . esc_url($hide_url) . '">Ẩn</a>';
            }

            $pending_url = wp_nonce_url(admin_url('admin-post.php?action=aitrongcay_market_admin_set_status&post_id=' . $post_id . '&status=pending'), 'aitr_market_admin_status_' . $post_id);
            echo '<a class="button button-small" href="' . esc_url($pending_url) . '">Chờ duyệt</a>';
            echo '</div>';
            break;
    }
}
add_action('manage_aitr_market_post_posts_custom_column', 'aitrongcay_render_market_admin_column', 10, 2);

function aitrongcay_market_admin_filters(): void
{
    global $typenow;
    if ($typenow !== 'aitr_market_post') {
        return;
    }

    $selected_author = absint($_GET['market_author_id'] ?? 0);
    $selected_status = sanitize_key((string) ($_GET['market_admin_status'] ?? ''));

    $authors = get_users([
        'has_published_posts' => ['aitr_market_post'],
        'orderby' => 'display_name',
        'order' => 'ASC',
    ]);

    echo '<select name="market_author_id">';
    echo '<option value="0">Tất cả người đăng</option>';
    foreach ($authors as $author) {
        echo '<option value="' . esc_attr((string) $author->ID) . '"' . selected($selected_author, (int) $author->ID, false) . '>' . esc_html($author->display_name ?: $author->user_login) . '</option>';
    }
    echo '</select>';

    echo '<select name="market_admin_status">';
    echo '<option value="">Mọi trạng thái</option>';
    foreach (['publish', 'pending', 'draft'] as $status) {
        echo '<option value="' . esc_attr($status) . '"' . selected($selected_status, $status, false) . '>' . esc_html(aitrongcay_market_status_label($status)) . '</option>';
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'aitrongcay_market_admin_filters');

function aitrongcay_market_admin_query_filter(WP_Query $query): void
{
    if (! is_admin() || ! $query->is_main_query()) {
        return;
    }

    if (($query->get('post_type') ?? '') !== 'aitr_market_post') {
        return;
    }

    $author_id = absint($_GET['market_author_id'] ?? 0);
    if ($author_id > 0) {
        $query->set('author', $author_id);
    }

    $status = sanitize_key((string) ($_GET['market_admin_status'] ?? ''));
    if (in_array($status, ['publish', 'pending', 'draft'], true)) {
        $query->set('post_status', $status);
    }
}
add_action('pre_get_posts', 'aitrongcay_market_admin_query_filter');

function aitrongcay_market_admin_set_status(): void
{
    if (! current_user_can('edit_posts')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'aitrongcay'));
    }

    $post_id = absint($_GET['post_id'] ?? 0);
    $status = sanitize_key((string) ($_GET['status'] ?? ''));
    if ($post_id <= 0 || ! in_array($status, ['publish', 'pending', 'draft'], true)) {
        wp_safe_redirect(admin_url('edit.php?post_type=aitr_market_post'));
        exit;
    }

    check_admin_referer('aitr_market_admin_status_' . $post_id);

    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post') {
        wp_safe_redirect(admin_url('edit.php?post_type=aitr_market_post'));
        exit;
    }

    wp_update_post([
        'ID' => $post_id,
        'post_status' => $status,
    ]);

    $redirect = add_query_arg([
        'post_type' => 'aitr_market_post',
        'market_updated' => '1',
    ], admin_url('edit.php'));
    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_aitrongcay_market_admin_set_status', 'aitrongcay_market_admin_set_status');

function aitrongcay_market_admin_notice(): void
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (! $screen || $screen->post_type !== 'aitr_market_post' || ! isset($_GET['market_updated'])) {
        return;
    }

    echo '<div class="notice notice-success is-dismissible"><p>Đã cập nhật trạng thái tin đăng.</p></div>';
}
add_action('admin_notices', 'aitrongcay_market_admin_notice');

function aitrongcay_enqueue_assets(): void
{
    $theme = wp_get_theme();
    $css_rel_path = '/assets/css/styles.css';
    $public_js_rel_path = '/assets/js/public.js';
    $portal_js_rel_path = '/assets/js/main.js';
    $css_abs_path = get_template_directory() . $css_rel_path;
    $public_js_abs_path = get_template_directory() . $public_js_rel_path;
    $portal_js_abs_path = get_template_directory() . $portal_js_rel_path;
    $css_version = file_exists($css_abs_path) ? (string) filemtime($css_abs_path) : $theme->get('Version');
    $public_js_version = file_exists($public_js_abs_path) ? (string) filemtime($public_js_abs_path) : $theme->get('Version');
    $portal_js_version = file_exists($portal_js_abs_path) ? (string) filemtime($portal_js_abs_path) : $theme->get('Version');

    $page = function_exists('aitrongcay_current_virtual_page') ? aitrongcay_current_virtual_page() : null;
    $slug = $page['slug'] ?? '';
    
    if ($slug !== '') {
        $is_portal_request = $slug === 'portal'
            || str_starts_with($slug, 'portal/')
            || $slug === 'cho-que'
            || str_ends_with($slug, '/cho-que');
    } else {
        $request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
        $home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');
        if ($home_path !== '' && str_starts_with($request_path, $home_path)) {
            $request_path = trim(substr($request_path, strlen($home_path)), '/');
        }

        $is_portal_request = $request_path === 'portal'
            || str_starts_with($request_path, 'portal/')
            || $request_path === 'cho-que'
            || str_ends_with($request_path, '/cho-que');
    }

    wp_enqueue_style(
        'aitrongcay-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Serif:wght@400;600;700;800&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'aitrongcay-theme',
        get_template_directory_uri() . $css_rel_path,
        ['aitrongcay-fonts'],
        $css_version
    );

    $script_handle = $is_portal_request ? 'aitrongcay-theme-portal' : 'aitrongcay-theme-public';
    $script_rel_path = $is_portal_request ? $portal_js_rel_path : $public_js_rel_path;
    $script_version = $is_portal_request ? $portal_js_version : $public_js_version;

    wp_enqueue_script(
        $script_handle,
        get_template_directory_uri() . $script_rel_path,
        [],
        $script_version,
        true
    );

    wp_localize_script($script_handle, 'aitrongcayTheme', [
        'rootUrl' => home_url('/'),
        'portalUrl' => home_url('/portal/'),
        'authUrl' => home_url('/dang-nhap/'),
        'signupUrl' => home_url('/dang-ky-tu-van/'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'ajaxNonce' => wp_create_nonce('aitrongcay_portal_actions'),
        'gardenAssistantEnabled' => true,
        'gardenAssistantMode' => function_exists('aitrongcay_ai_agent_config') ? ((string) (aitrongcay_ai_agent_config()['mode'] ?? 'adapter-ready')) : 'adapter-ready',
        'gardenKey' => function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key() : '',
        'nav' => array_map(
            static fn(array $item): array => [
                'label' => $item['label'],
                'url' => $item['url'],
            ],
            aitrongcay_primary_nav_items()
        ),
    ]);
}
add_action('wp_enqueue_scripts', 'aitrongcay_enqueue_assets');

function aitrongcay_resource_hints(array $urls, string $relation_type): array
{
    if ($relation_type === 'preconnect') {
        $urls[] = 'https://fonts.googleapis.com';
        $urls[] = ['href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous'];
    }

    return $urls;
}
add_filter('wp_resource_hints', 'aitrongcay_resource_hints', 10, 2);

function aitrongcay_body_classes(array $classes): array
{
    if (is_page_template('templates/page-portal.php')) {
        $classes[] = 'page-portal';
    }

    return $classes;
}
add_filter('body_class', 'aitrongcay_body_classes');

function aitrongcay_consultation_action_url(): string
{
    return esc_url(admin_url('admin-post.php'));
}

function aitrongcay_hls_stream_map(): array
{
    return apply_filters('aitrongcay_hls_stream_map', []);
}

function aitrongcay_hls_stream_url(string $garden_key, string $pot_code): string
{
    $garden_key = trim($garden_key);
    $pot_code = trim($pot_code);
    if ($garden_key === '' || $pot_code === '') {
        return '';
    }

    if (function_exists('aitrongcay_get_rack_slot_camera_stream_url')) {
        $slot_stream_url = aitrongcay_get_rack_slot_camera_stream_url($garden_key, $pot_code);
        if ($slot_stream_url !== '') {
            return $slot_stream_url;
        }
    }

    $map = aitrongcay_hls_stream_map();
    return trim((string) ($map[$garden_key][$pot_code] ?? ''));
}

function aitrongcay_consultation_notice(): ?array
{
    $status = isset($_GET['consultation_status']) ? sanitize_key((string) $_GET['consultation_status']) : '';
    if ($status === '') {
        return null;
    }

    return match ($status) {
        'success' => [
            'class' => 'notice success',
            'title' => 'Đã ghi nhận đăng ký tư vấn.',
            'body' => 'Bước tiếp theo là đội ngũ liên hệ ngắn để hiểu nhu cầu thật và chốt nhịp bắt đầu phù hợp.',
        ],
        'invalid' => [
            'class' => 'notice error',
            'title' => 'Thiếu thông tin cần thiết.',
            'body' => 'Anh/chị vui lòng để lại ít nhất họ tên và số điện thoại để bên em liên hệ tư vấn.',
        ],
        default => null,
    };
}

function aitrongcay_render_consultation_notice(): void
{
    $notice = aitrongcay_consultation_notice();
    if (! is_array($notice)) {
        return;
    }

    printf(
        '<div class="%1$s" style="margin-bottom:16px"><strong style="display:block;margin-bottom:6px">%2$s</strong><span>%3$s</span></div>',
        esc_attr($notice['class']),
        esc_html($notice['title']),
        esc_html($notice['body'])
    );
}

function aitrongcay_consultation_notification_email(): string
{
    $saved = trim((string) get_option('aitrongcay_notification_email', ''));
    return $saved !== '' ? $saved : (string) get_option('admin_email', '');
}

function aitrongcay_send_consultation_notification(array $payload, int $post_id = 0): void
{
    $admin_email = aitrongcay_consultation_notification_email();
    if (! is_email($admin_email)) {
        return;
    }

    $subject = sprintf('[Ai trồng cây] Lead tư vấn mới: %s — %s', $payload['full_name'], $payload['phone']);
    $lines = [
        'Có một đăng ký tư vấn mới từ website Ai trồng cây.',
        '',
        'Họ tên: ' . $payload['full_name'],
        'Số điện thoại: ' . $payload['phone'],
        'Email: ' . ($payload['email'] !== '' ? $payload['email'] : 'Không có'),
        'Mục tiêu đầu tiên: ' . ($payload['goal'] !== '' ? $payload['goal'] : 'Chưa ghi rõ'),
        'Mốc bắt đầu mong muốn: ' . ($payload['start_window'] !== '' ? $payload['start_window'] : 'Chưa ghi rõ'),
        'Ghi chú thêm: ' . ($payload['focus'] !== '' ? $payload['focus'] : 'Không có'),
        'Funnel stage: ' . $payload['funnel_stage'],
        'Funnel source: ' . $payload['funnel_source'],
        'Thời gian (giờ Việt Nam): ' . wp_date('c'),
    ];

    if ($post_id > 0) {
        $lines[] = 'Lead admin: ' . admin_url('post.php?post=' . $post_id . '&action=edit');
    }

    wp_mail($admin_email, $subject, implode("\n", $lines));
}

function aitrongcay_handle_consultation_submission(): void
{
    if (! isset($_POST['aitrongcay_consultation_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitrongcay_consultation_nonce'])), 'aitrongcay_consultation_submit')) {
        wp_safe_redirect(add_query_arg('consultation_status', 'invalid', wp_get_referer() ?: home_url('/dang-ky-tu-van/')));
        exit;
    }

    $full_name = sanitize_text_field(wp_unslash($_POST['fullName'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $goal = sanitize_text_field(wp_unslash($_POST['goal'] ?? ''));
    $start_window = sanitize_text_field(wp_unslash($_POST['startWindow'] ?? ''));
    $focus = sanitize_textarea_field(wp_unslash($_POST['focus'] ?? ''));
    $funnel_stage = sanitize_text_field(wp_unslash($_POST['funnelStage'] ?? 'consultation'));
    $funnel_source = sanitize_text_field(wp_unslash($_POST['funnelSource'] ?? 'website'));
    $redirect_to = esc_url_raw(wp_unslash($_POST['redirect_to'] ?? home_url('/dang-ky-tu-van/')));

    if ($full_name === '' || $phone === '') {
        wp_safe_redirect(add_query_arg('consultation_status', 'invalid', $redirect_to));
        exit;
    }

    $payload = [
        'full_name' => $full_name,
        'phone' => $phone,
        'email' => $email,
        'goal' => $goal,
        'start_window' => $start_window,
        'focus' => $focus,
        'funnel_stage' => $funnel_stage,
        'funnel_source' => $funnel_source,
    ];

    $post_id = wp_insert_post([
        'post_type' => 'aitr_consultation',
        'post_status' => 'publish',
        'post_title' => sprintf('%s — %s', $full_name, $phone),
        'post_content' => implode("\n\n", array_filter([
            'Mục tiêu đầu tiên: ' . ($goal !== '' ? $goal : 'Chưa ghi rõ'),
            'Mốc bắt đầu mong muốn: ' . ($start_window !== '' ? $start_window : 'Chưa ghi rõ'),
            'Ghi chú thêm: ' . ($focus !== '' ? $focus : 'Không có'),
            'Funnel stage: ' . $funnel_stage,
            'Funnel source: ' . $funnel_source,
        ])),
    ], true);

    if (! is_wp_error($post_id) && is_int($post_id)) {
        update_post_meta($post_id, 'full_name', $full_name);
        update_post_meta($post_id, 'phone', $phone);
        update_post_meta($post_id, 'email', $email);
        update_post_meta($post_id, 'goal', $goal);
        update_post_meta($post_id, 'start_window', $start_window);
        update_post_meta($post_id, 'focus', $focus);
        update_post_meta($post_id, 'funnel_stage', $funnel_stage);
        update_post_meta($post_id, 'funnel_source', $funnel_source);
        update_post_meta($post_id, 'submitted_at_gmt', wp_date('c'));
        aitrongcay_send_consultation_notification($payload, $post_id);
    }

    wp_safe_redirect(add_query_arg('consultation_status', 'success', $redirect_to));
    exit;
}
add_action('admin_post_nopriv_aitrongcay_consultation_submit', 'aitrongcay_handle_consultation_submission');
add_action('admin_post_aitrongcay_consultation_submit', 'aitrongcay_handle_consultation_submission');

function aitrongcay_auth_notice(): ?array
{
    $status = isset($_GET['auth_status']) ? sanitize_key((string) $_GET['auth_status']) : '';
    if ($status === '') {
        return null;
    }

    return match ($status) {
        'login-required' => [
            'class' => 'notice error',
            'title' => 'Anh/chị vui lòng đăng nhập trước.',
            'body' => 'Khu vực này dành cho thành viên đã có tài khoản.',
        ],
        'login-error' => [
            'class' => 'notice error',
            'title' => 'Thông tin đăng nhập chưa đúng.',
            'body' => 'Anh/chị kiểm tra lại email và mật khẩu giúp em.',
        ],
        'register-invalid' => [
            'class' => 'notice error',
            'title' => 'Thiếu thông tin cần thiết.',
            'body' => 'Anh/chị vui lòng điền họ tên, email và mật khẩu để tạo tài khoản.',
        ],
        'register-exists' => [
            'class' => 'notice error',
            'title' => 'Email này đã có tài khoản.',
            'body' => 'Anh/chị có thể đăng nhập luôn bằng email đó.',
        ],
        'register-success' => [
            'class' => 'notice success',
            'title' => 'Tài khoản đã được tạo.',
            'body' => 'Anh/chị đã được đăng nhập ngay sau khi tạo tài khoản và có thể vào khu vườn của mình bây giờ.',
        ],
        'logged-out' => null,
        default => null,
    };
}

function aitrongcay_render_auth_notice(): void
{
    $notice = aitrongcay_auth_notice();
    if (! is_array($notice)) {
        return;
    }

    printf(
        '<div class="%1$s" style="margin-bottom:16px"><strong style="display:block;margin-bottom:6px">%2$s</strong><span>%3$s</span></div>',
        esc_attr($notice['class']),
        esc_html($notice['title']),
        esc_html($notice['body'])
    );
}

function aitrongcay_login_action_url(): string
{
    return esc_url(admin_url('admin-post.php'));
}

function aitrongcay_account_notice(): ?array
{
    $status = isset($_GET['account_status']) ? sanitize_key((string) $_GET['account_status']) : '';
    if ($status === '') {
        return null;
    }

    return match ($status) {
        'updated' => [
            'class' => 'notice success',
            'title' => 'Đã cập nhật tài khoản.',
            'body' => 'Thông tin cơ bản của anh/chị đã được lưu.',
        ],
        'password-updated' => [
            'class' => 'notice success',
            'title' => 'Đã đổi mật khẩu.',
            'body' => 'Mật khẩu mới đã có hiệu lực cho lần đăng nhập tiếp theo.',
        ],
        'avatar-updated' => [
            'class' => 'notice success',
            'title' => 'Đã cập nhật ảnh đại diện.',
            'body' => 'Ảnh đại diện mới của anh/chị đã được lưu.',
        ],
        'password-mismatch' => [
            'class' => 'notice error',
            'title' => 'Mật khẩu xác nhận chưa khớp.',
            'body' => 'Anh/chị nhập lại giúp em để tránh sai sót.',
        ],
        'invalid-email' => [
            'class' => 'notice error',
            'title' => 'Email chưa hợp lệ.',
            'body' => 'Anh/chị kiểm tra lại email trước khi lưu.',
        ],
        'email-pending' => [
            'class' => 'notice success',
            'title' => 'Đã ghi nhận yêu cầu đổi email.',
            'body' => 'Anh/chị kiểm tra email mới và bấm link xác nhận để hoàn tất.',
        ],
        'email-confirmed' => [
            'class' => 'notice success',
            'title' => 'Email đã được xác nhận.',
            'body' => 'Địa chỉ email mới đã có hiệu lực cho tài khoản này.',
        ],
        'reset-sent' => [
            'class' => 'notice success',
            'title' => 'Đã gửi email đặt lại mật khẩu.',
            'body' => 'Anh/chị kiểm tra hộp thư để tiếp tục.',
        ],
        'avatar-removed' => [
            'class' => 'notice success',
            'title' => 'Đã xóa ảnh đại diện.',
            'body' => 'Tài khoản đã quay về avatar mặc định.',
        ],
        default => null,
    };
}

function aitrongcay_render_account_notice(): void
{
    $notice = aitrongcay_account_notice();
    if (! is_array($notice)) {
        return;
    }

    printf(
        '<div class="%1$s" style="margin-bottom:16px"><strong style="display:block;margin-bottom:6px">%2$s</strong><span>%3$s</span></div>',
        esc_attr($notice['class']),
        esc_html($notice['title']),
        esc_html($notice['body'])
    );
}

function aitrongcay_admin_display_page_title($title, $post_id = 0)
{
    if (! is_admin()) {
        return $title;
    }

    if ((int) $post_id > 0) {
        $post = get_post($post_id);
        if ($post instanceof WP_Post && $post->post_type === 'page' && $post->post_name === 'cach-hoat-dong') {
            return 'Giới thiệu';
        }
    }

    return $title;
}
add_filter('the_title', 'aitrongcay_admin_display_page_title', 10, 2);

function aitrongcay_generate_username_from_email(string $email): string
{
    $base = sanitize_user((string) strstr($email, '@', true), true);
    if ($base === '') {
        $base = 'vuon';
    }

    $username = $base;
    $i = 1;
    while (username_exists($username)) {
        $username = $base . $i;
        $i++;
    }

    return $username;
}

function aitrongcay_resolve_login_identity(string $identity): string
{
    if (is_email($identity)) {
        $user = get_user_by('email', $identity);
        if ($user instanceof WP_User) {
            return $user->user_login;
        }
    }

    return $identity;
}

function aitrongcay_handle_register_submission(): void
{
    $redirect_to = esc_url_raw(wp_unslash($_POST['redirect_to'] ?? home_url('/onboarding/')));

    if (! isset($_POST['aitrongcay_register_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitrongcay_register_nonce'])), 'aitrongcay_register_submit')) {
        wp_safe_redirect(add_query_arg('auth_status', 'register-invalid', $redirect_to));
        exit;
    }

    $full_name = sanitize_text_field(wp_unslash($_POST['full_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $password = (string) wp_unslash($_POST['password'] ?? '');
    $salutation = sanitize_text_field(wp_unslash($_POST['salutation'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));

    if ($full_name === '' || ! is_email($email) || $password === '' || ! in_array($salutation, ['anh', 'chị'], true) || $phone === '') {
        wp_safe_redirect(add_query_arg('auth_status', 'register-invalid', $redirect_to));
        exit;
    }

    if (email_exists($email)) {
        wp_safe_redirect(add_query_arg('auth_status', 'register-exists', home_url('/dang-nhap/')));
        exit;
    }

    $username = aitrongcay_generate_username_from_email($email);
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        wp_safe_redirect(add_query_arg('auth_status', 'register-invalid', $redirect_to));
        exit;
    }

    wp_update_user([
        'ID' => $user_id,
        'display_name' => $full_name,
        'first_name' => $full_name,
        'nickname' => trim($salutation . ' ' . $full_name),
    ]);

    update_user_meta($user_id, 'aitrongcay_salutation', $salutation);
    update_user_meta($user_id, 'aitrongcay_phone', $phone);

    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    wp_safe_redirect(add_query_arg('auth_status', 'register-success', home_url('/portal/dashboard-2/')));
    exit;
}
add_action('admin_post_nopriv_aitrongcay_register_submit', 'aitrongcay_handle_register_submission');
add_action('admin_post_aitrongcay_register_submit', 'aitrongcay_handle_register_submission');

function aitrongcay_handle_login_submission(): void
{
    $redirect_to = esc_url_raw(wp_unslash($_POST['redirect_to'] ?? home_url('/portal/dashboard-2/')));

    if (! isset($_POST['aitrongcay_login_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aitrongcay_login_nonce'])), 'aitrongcay_login_submit')) {
        wp_safe_redirect(add_query_arg('auth_status', 'login-error', home_url('/dang-nhap/')));
        exit;
    }

    $identity = sanitize_text_field(wp_unslash($_POST['identity'] ?? ''));
    $password = (string) wp_unslash($_POST['password'] ?? '');
    $remember = isset($_POST['remember']) && wp_unslash($_POST['remember']) === '1';

    if ($identity === '' || $password === '') {
        wp_safe_redirect(add_query_arg('auth_status', 'login-error', home_url('/dang-nhap/')));
        exit;
    }

    $user = wp_signon([
        'user_login' => aitrongcay_resolve_login_identity($identity),
        'user_password' => $password,
        'remember' => $remember,
    ], is_ssl());

    if (is_wp_error($user)) {
        wp_safe_redirect(add_query_arg('auth_status', 'login-error', home_url('/dang-nhap/')));
        exit;
    }

    wp_safe_redirect($redirect_to);
    exit;
}
add_action('admin_post_nopriv_aitrongcay_login_submit', 'aitrongcay_handle_login_submission');
add_action('admin_post_aitrongcay_login_submit', 'aitrongcay_handle_login_submission');

function aitrongcay_is_backend_admin_user($user = null): bool
{
    if ($user instanceof WP_User) {
        return $user->has_cap('edit_theme_options') || $user->has_cap('manage_options');
    }

    return current_user_can('edit_theme_options') || current_user_can('manage_options');
}

function aitrongcay_normalize_user_identity(string $value): string
{
    $value = trim(wp_strip_all_tags($value));
    if ($value === '') {
        return '';
    }

    if (function_exists('remove_accents')) {
        $value = remove_accents($value);
    }

    $value = strtolower($value);
    $value = preg_replace('/\s+/u', ' ', $value);

    return is_string($value) ? trim($value) : '';
}

function aitrongcay_creator_default_entries(): array
{
    return [
        ['label' => 'Nguyễn Hà'],
        ['label' => 'Phí Ngọc Tùng'],
        ['label' => 'Tú nihe'],
        ['label' => 'Hiep Duong Le'],
    ];
}

function aitrongcay_creator_user_entries(): array
{
    $stored_entries = get_option('aitrongcay_creator_user_list', null);
    $raw_entries = is_array($stored_entries) && $stored_entries !== [] ? $stored_entries : aitrongcay_creator_default_entries();

    $normalized = [];
    foreach ($raw_entries as $entry) {
        if (is_array($entry)) {
            $entry_id = absint($entry['id'] ?? 0);
            $entry_label = sanitize_text_field((string) ($entry['label'] ?? ''));
        } else {
            $entry_id = 0;
            $entry_label = sanitize_text_field((string) $entry);
        }

        if ($entry_id <= 0 && $entry_label === '') {
            continue;
        }

        $normalized[] = [
            'id' => $entry_id,
            'label' => $entry_label,
        ];
    }

    return array_values(array_unique($normalized, SORT_REGULAR));
}

function aitrongcay_creator_user_list(): array
{
    $labels = [];
    foreach (aitrongcay_creator_user_entries() as $entry) {
        $label = sanitize_text_field((string) ($entry['label'] ?? ''));
        if ($label !== '') {
            $labels[] = $label;
        }
    }

    return array_values(array_unique($labels));
}

function aitrongcay_is_creator_user($user = null): bool
{
    if (! ($user instanceof WP_User)) {
        $user = wp_get_current_user();
    }

    if (! ($user instanceof WP_User) || (int) $user->ID <= 0) {
        return false;
    }

    $allowed_lookup = [];
    $allowed_ids = [];
    foreach (aitrongcay_creator_user_entries() as $entry) {
        $entry_id = absint($entry['id'] ?? 0);
        if ($entry_id > 0) {
            $allowed_ids[$entry_id] = true;
        }

        $normalized = aitrongcay_normalize_user_identity((string) ($entry['label'] ?? ''));
        if ($normalized !== '') {
            $allowed_lookup[$normalized] = true;
        }
    }

    if ($allowed_lookup === [] && $allowed_ids === []) {
        return false;
    }

    if (isset($allowed_ids[(int) $user->ID])) {
        return true;
    }

    $candidates = [
        (string) $user->display_name,
        (string) $user->user_login,
        (string) $user->user_nicename,
        (string) $user->user_email,
    ];

    foreach ($candidates as $candidate) {
        $normalized = aitrongcay_normalize_user_identity($candidate);
        if ($normalized !== '' && isset($allowed_lookup[$normalized])) {
            return true;
        }
    }

    return false;
}

function aitrongcay_can_manage_onboarding_catalog($user = null): bool
{
    return aitrongcay_is_backend_admin_user($user) || aitrongcay_is_creator_user($user);
}

function aitrongcay_creator_admin_menu(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Danh sách creator',
        'Danh sách creator',
        'edit_theme_options',
        'aitrongcay-creator-list',
        'aitrongcay_render_creator_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_creator_admin_menu', 100);

function aitrongcay_handle_creator_admin_save(): void
{
    if (! is_admin() || ! current_user_can('edit_theme_options')) {
        return;
    }

    if (($_POST['action'] ?? '') !== 'aitrongcay_save_creator_list') {
        return;
    }

    check_admin_referer('aitrongcay_save_creator_list');

    $selected_ids = isset($_POST['creator_user_ids']) && is_array($_POST['creator_user_ids'])
        ? array_values(array_unique(array_filter(array_map('absint', wp_unslash($_POST['creator_user_ids'])))))
        : [];

    $entries = [];
    foreach ($selected_ids as $user_id) {
        $user = get_user_by('id', $user_id);
        if (! ($user instanceof WP_User)) {
            continue;
        }

        $entries[] = [
            'id' => (int) $user->ID,
            'label' => (string) $user->display_name,
        ];
    }

    update_option('aitrongcay_creator_user_list', $entries, false);

    wp_safe_redirect(add_query_arg([
        'page' => 'aitrongcay-creator-list',
        'updated' => 'true',
    ], admin_url('admin.php')));
    exit;
}
add_action('admin_init', 'aitrongcay_handle_creator_admin_save');

function aitrongcay_render_creator_admin_page(): void
{
    if (! current_user_can('edit_theme_options')) {
        wp_die('Bạn không có quyền truy cập mục này.');
    }

    $users = get_users([
        'orderby' => 'display_name',
        'order' => 'ASC',
        'fields' => ['ID', 'display_name', 'user_login', 'user_email', 'roles'],
    ]);
    $selected_lookup = [];
    foreach (aitrongcay_creator_user_entries() as $entry) {
        $entry_id = absint($entry['id'] ?? 0);
        if ($entry_id > 0) {
            $selected_lookup[$entry_id] = true;
        }
    }
    ?>
    <div class="wrap">
      <h1>Danh sách creator</h1>
      <p>Chọn những user được phép nhìn thấy và truy cập khu <strong>onboarding cây mới</strong> và <strong>tạo vật phẩm mới</strong>.</p>
      <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true') : ?>
        <div class="notice notice-success is-dismissible"><p>Đã cập nhật danh sách creator.</p></div>
      <?php endif; ?>
      <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=aitrongcay-creator-list')); ?>">
        <?php wp_nonce_field('aitrongcay_save_creator_list'); ?>
        <input type="hidden" name="action" value="aitrongcay_save_creator_list">
        <table class="widefat striped" style="max-width:980px">
          <thead>
            <tr>
              <th style="width:90px">Chọn</th>
              <th>Tên hiển thị</th>
              <th>Login</th>
              <th>Email</th>
              <th>Role</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user) : ?>
              <?php $user_id = (int) $user->ID; ?>
              <tr>
                <td><label><input type="checkbox" name="creator_user_ids[]" value="<?php echo esc_attr((string) $user_id); ?>" <?php checked(isset($selected_lookup[$user_id])); ?>> chọn</label></td>
                <td><strong><?php echo esc_html((string) $user->display_name); ?></strong></td>
                <td><?php echo esc_html((string) $user->user_login); ?></td>
                <td><?php echo esc_html((string) $user->user_email); ?></td>
                <td><?php echo esc_html(implode(', ', array_map('strval', (array) $user->roles))); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p style="margin-top:16px">
          <button type="submit" class="button button-primary">Lưu danh sách creator</button>
        </p>
      </form>
    </div>
    <?php
}

function aitrongcay_login_redirect(string $redirect_to, string $requested_redirect_to, $user): string
{
    if (! ($user instanceof WP_User)) {
        return $redirect_to;
    }

    if (aitrongcay_is_backend_admin_user($user)) {
        return $redirect_to;
    }

    $requested = trim($requested_redirect_to);
    if ($requested !== '') {
        $requested_path = wp_parse_url($requested, PHP_URL_PATH);
        if (is_string($requested_path) && $requested_path !== '' && strpos($requested_path, '/wp-admin') !== 0) {
            return $requested;
        }
    }

    return home_url('/portal/dashboard-2/');
}
add_filter('login_redirect', 'aitrongcay_login_redirect', 10, 3);

function aitrongcay_block_wp_admin_for_frontend_users(): void
{
    if (! is_user_logged_in() || aitrongcay_is_backend_admin_user()) {
        return;
    }

    if (wp_doing_ajax()) {
        return;
    }

    $script = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    if (in_array($script, ['admin-post.php', 'async-upload.php'], true)) {
        return;
    }

    if (is_admin()) {
        wp_safe_redirect(home_url('/tai-khoan/'));
        exit;
    }
}
add_action('admin_init', 'aitrongcay_block_wp_admin_for_frontend_users');

function aitrongcay_show_admin_bar_for_backend_users(bool $show): bool
{
    if (aitrongcay_is_backend_admin_user()) {
        return $show;
    }

    return false;
}
add_filter('show_admin_bar', 'aitrongcay_show_admin_bar_for_backend_users');

function aitrongcay_handle_account_update(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    check_admin_referer('aitrongcay_account_update_submit', 'aitrongcay_account_update_nonce');

    $user_id = get_current_user_id();
    $display_name = sanitize_text_field(wp_unslash($_POST['display_name'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $city = sanitize_text_field(wp_unslash($_POST['city'] ?? ''));
    $household = sanitize_text_field(wp_unslash($_POST['household'] ?? ''));
    $address_line = sanitize_text_field(wp_unslash($_POST['address_line'] ?? ''));
    $ward = sanitize_text_field(wp_unslash($_POST['ward'] ?? ''));
    $district = sanitize_text_field(wp_unslash($_POST['district'] ?? ''));
    $note = sanitize_textarea_field(wp_unslash($_POST['account_note'] ?? ''));
    $notify_email = isset($_POST['notify_email']) ? '1' : '0';
    $notify_sms = isset($_POST['notify_sms']) ? '1' : '0';
    $notify_zalo = isset($_POST['notify_zalo']) ? '1' : '0';
    $notify_harvest = isset($_POST['notify_harvest']) ? '1' : '0';

    if ($email !== '' && ! is_email($email)) {
        wp_safe_redirect(add_query_arg('account_status', 'invalid-email', home_url('/tai-khoan/')));
        exit;
    }

    $current = wp_get_current_user();
    wp_update_user([
        'ID' => $user_id,
        'display_name' => $display_name !== '' ? $display_name : $current->display_name,
        'first_name' => $display_name !== '' ? $display_name : $current->first_name,
    ]);

    if ($email !== '' && $email !== $current->user_email) {
        $token = wp_generate_password(32, false, false);
        update_user_meta($user_id, 'aitrongcay_pending_email', $email);
        update_user_meta($user_id, 'aitrongcay_pending_email_token', $token);
        $confirm_url = add_query_arg([
            'ait_confirm_email' => '1',
            'uid' => $user_id,
            'token' => $token,
        ], home_url('/tai-khoan/'));
        wp_mail($email, '[Ai trồng cây] Xác nhận email mới', "Anh/chị hãy bấm link sau để xác nhận email mới cho tài khoản:\n\n" . $confirm_url);
    }

    update_user_meta($user_id, 'aitrongcay_phone', $phone);
    update_user_meta($user_id, 'aitrongcay_city', $city);
    update_user_meta($user_id, 'aitrongcay_household', $household);
    update_user_meta($user_id, 'aitrongcay_address_line', $address_line);
    update_user_meta($user_id, 'aitrongcay_ward', $ward);
    update_user_meta($user_id, 'aitrongcay_district', $district);
    update_user_meta($user_id, 'aitrongcay_account_note', $note);
    update_user_meta($user_id, 'aitrongcay_notify_email', $notify_email);
    update_user_meta($user_id, 'aitrongcay_notify_sms', $notify_sms);
    update_user_meta($user_id, 'aitrongcay_notify_zalo', $notify_zalo);
    update_user_meta($user_id, 'aitrongcay_notify_harvest', $notify_harvest);

    $status = ($email !== '' && $email !== $current->user_email) ? 'email-pending' : 'updated';
    wp_safe_redirect(add_query_arg('account_status', $status, home_url('/tai-khoan/')));
    exit;
}
add_action('admin_post_aitrongcay_account_update', 'aitrongcay_handle_account_update');

function aitrongcay_handle_email_confirmation(): void
{
    if (! isset($_GET['ait_confirm_email'], $_GET['uid'], $_GET['token'])) {
        return;
    }
    $user_id = absint($_GET['uid']);
    $token = sanitize_text_field((string) $_GET['token']);
    $saved_token = (string) get_user_meta($user_id, 'aitrongcay_pending_email_token', true);
    $pending_email = (string) get_user_meta($user_id, 'aitrongcay_pending_email', true);
    if ($user_id > 0 && $token !== '' && hash_equals($saved_token, $token) && is_email($pending_email)) {
        wp_update_user(['ID' => $user_id, 'user_email' => $pending_email]);
        delete_user_meta($user_id, 'aitrongcay_pending_email');
        delete_user_meta($user_id, 'aitrongcay_pending_email_token');
        wp_safe_redirect(add_query_arg('account_status', 'email-confirmed', home_url('/tai-khoan/')));
        exit;
    }
}
add_action('template_redirect', 'aitrongcay_handle_email_confirmation');

function aitrongcay_handle_account_password_update(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    check_admin_referer('aitrongcay_account_password_submit', 'aitrongcay_account_password_nonce');

    $password = (string) wp_unslash($_POST['new_password'] ?? '');
    $confirm = (string) wp_unslash($_POST['confirm_password'] ?? '');

    if ($password === '' || $password !== $confirm) {
        wp_safe_redirect(add_query_arg('account_status', 'password-mismatch', home_url('/tai-khoan/#doi-mat-khau')));
        exit;
    }

    wp_set_password($password, get_current_user_id());
    wp_set_auth_cookie(get_current_user_id(), true);
    wp_safe_redirect(add_query_arg('account_status', 'password-updated', home_url('/tai-khoan/#doi-mat-khau')));
    exit;
}
add_action('admin_post_aitrongcay_account_password_update', 'aitrongcay_handle_account_password_update');

function aitrongcay_handle_account_avatar_update(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    check_admin_referer('aitrongcay_account_avatar_submit', 'aitrongcay_account_avatar_nonce');

    if (empty($_FILES['avatar']) || ! is_array($_FILES['avatar']) || (int) ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        wp_safe_redirect(add_query_arg('account_status', 'updated', home_url('/tai-khoan/')));
        exit;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload('avatar', 0);
    if (! is_wp_error($attachment_id) && $attachment_id) {
        update_user_meta(get_current_user_id(), 'aitrongcay_avatar_id', (int) $attachment_id);
        update_post_meta($attachment_id, '_aitrongcay_avatar_owner', get_current_user_id());
        wp_safe_redirect(add_query_arg('account_status', 'avatar-updated', home_url('/tai-khoan/')));
        exit;
    }

    wp_safe_redirect(add_query_arg('account_status', 'updated', home_url('/tai-khoan/')));
    exit;
}
add_action('admin_post_aitrongcay_account_avatar_update', 'aitrongcay_handle_account_avatar_update');

function aitrongcay_handle_account_avatar_remove(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }
    check_admin_referer('aitrongcay_account_avatar_remove_submit', 'aitrongcay_account_avatar_remove_nonce');
    delete_user_meta(get_current_user_id(), 'aitrongcay_avatar_id');
    wp_safe_redirect(add_query_arg('account_status', 'avatar-removed', home_url('/tai-khoan/')));
    exit;
}
add_action('admin_post_aitrongcay_account_avatar_remove', 'aitrongcay_handle_account_avatar_remove');

function aitrongcay_handle_password_reset_request(): void
{
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if ($email !== '' && is_email($email)) {
        $user = get_user_by('email', $email);
        if ($user instanceof WP_User) {
            retrieve_password($user->user_login);
        }
    }
    wp_safe_redirect(add_query_arg('account_status', 'reset-sent', home_url('/dang-nhap/')));
    exit;
}
add_action('admin_post_nopriv_aitrongcay_password_reset_request', 'aitrongcay_handle_password_reset_request');
add_action('admin_post_aitrongcay_password_reset_request', 'aitrongcay_handle_password_reset_request');

function aitrongcay_consultation_form_shortcode(): string
{
    ob_start();
    include get_template_directory() . '/template-parts/virtual/dang-ky-tu-van.php';
    return (string) ob_get_clean();
}
add_shortcode('aitrongcay_consultation_form', 'aitrongcay_consultation_form_shortcode');

function aitrongcay_login_form_shortcode(): string
{
    ob_start();
    include get_template_directory() . '/template-parts/virtual/dang-nhap.php';
    return (string) ob_get_clean();
}
add_shortcode('aitrongcay_login_form', 'aitrongcay_login_form_shortcode');

function aitrongcay_register_form_shortcode(): string
{
    ob_start();
    include get_template_directory() . '/template-parts/virtual/onboarding.php';
    return (string) ob_get_clean();
}
add_shortcode('aitrongcay_register_form', 'aitrongcay_register_form_shortcode');

function aitrongcay_require_portal_nonce(): void
{
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập trước.'], 401);
    }
}

function aitrongcay_friendships_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_friendships';
}

function aitrongcay_garden_members_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_members';
}

function aitrongcay_garden_notes_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_notes';
}

function aitrongcay_gardens_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_gardens';
}

function aitrongcay_garden_pots_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_pots';
}

function aitrongcay_garden_tools_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_tools';
}

function aitrongcay_garden_racks_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_racks';
}

function aitrongcay_garden_rack_slots_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_rack_slots';
}

function aitrongcay_garden_rack_inventory_events_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_rack_inventory_events';
}

function aitrongcay_garden_rack_assignments_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'aitr_garden_rack_assignments';
}

function aitrongcay_install_social_tables(): void
{
    if (get_option('aitrongcay_social_schema_version', '') === '5') {
        return;
    }

    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset = $wpdb->get_charset_collate();
    $friendships = aitrongcay_friendships_table();
    $members = aitrongcay_garden_members_table();
    $notes = aitrongcay_garden_notes_table();
    $gardens = aitrongcay_gardens_table();
    $pots = aitrongcay_garden_pots_table();
    $tools = aitrongcay_garden_tools_table();
    $racks = aitrongcay_garden_racks_table();
    $rack_slots = aitrongcay_garden_rack_slots_table();
    $rack_events = aitrongcay_garden_rack_inventory_events_table();
    $rack_assignments = aitrongcay_garden_rack_assignments_table();

    dbDelta("CREATE TABLE {$friendships} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        requester_user_id bigint unsigned NOT NULL,
        addressee_user_id bigint unsigned NOT NULL,
        unique_pair_key varchar(64) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime NOT NULL,
        responded_at datetime NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_pair_key (unique_pair_key),
        KEY requester_user_id (requester_user_id),
        KEY addressee_user_id (addressee_user_id),
        KEY status (status)
    ) {$charset};");

    dbDelta("CREATE TABLE {$members} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        garden_key varchar(64) NOT NULL,
        user_id bigint unsigned NOT NULL,
        role varchar(20) NOT NULL DEFAULT 'viewer',
        status varchar(20) NOT NULL DEFAULT 'invited',
        invited_by_user_id bigint unsigned NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY garden_user (garden_key, user_id),
        KEY garden_key (garden_key),
        KEY user_id (user_id),
        KEY status (status)
    ) {$charset};");

    dbDelta("CREATE TABLE {$notes} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        garden_key varchar(64) NOT NULL,
        pot_code varchar(64) NOT NULL,
        note_text longtext NOT NULL,
        updated_by_user_id bigint unsigned NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY garden_pot (garden_key, pot_code),
        KEY garden_key (garden_key),
        KEY updated_at (updated_at)
    ) {$charset};");

    dbDelta("CREATE TABLE {$gardens} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        garden_key varchar(64) NOT NULL,
        owner_user_id bigint unsigned NOT NULL,
        garden_code varchar(64) NOT NULL DEFAULT '',
        garden_name varchar(255) NOT NULL DEFAULT '',
        summary text NULL,
        status_line varchar(255) NOT NULL DEFAULT '',
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY garden_key (garden_key),
        KEY owner_user_id (owner_user_id)
    ) {$charset};");

    dbDelta("CREATE TABLE {$pots} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        garden_key varchar(64) NOT NULL,
        pot_code varchar(64) NOT NULL,
        pot_name varchar(255) NOT NULL DEFAULT '',
        plant_name varchar(255) NOT NULL DEFAULT '',
        status varchar(255) NOT NULL DEFAULT '',
        status_summary text NULL,
        ph varchar(64) NOT NULL DEFAULT '',
        temperature varchar(64) NOT NULL DEFAULT '',
        humidity varchar(64) NOT NULL DEFAULT '',
        light_label varchar(128) NOT NULL DEFAULT '',
        light_device varchar(64) NOT NULL DEFAULT '',
        pump_label varchar(128) NOT NULL DEFAULT '',
        irrigation varchar(255) NOT NULL DEFAULT '',
        video_url text NULL,
        image_url text NULL,
        latest_photo_id bigint unsigned NOT NULL DEFAULT 0,
        latest_photo_at datetime NULL,
        latest_analysis_level int NOT NULL DEFAULT 0,
        latest_analysis_color varchar(32) NOT NULL DEFAULT '',
        latest_analysis_label varchar(255) NOT NULL DEFAULT '',
        latest_analysis_current_stage varchar(255) NOT NULL DEFAULT '',
        latest_analysis_recommendation text NULL,
        latest_analysis_summary text NULL,
        latest_analysis_actions text NULL,
        latest_analysis_escalate text NULL,
        latest_analysis_upsell text NULL,
        latest_analysis_updated_at datetime NULL,
        ai_note text NULL,
        harvest_eta varchar(255) NOT NULL DEFAULT '',
        sort_order int NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY garden_pot (garden_key, pot_code),
        KEY garden_key (garden_key),
        KEY sort_order (sort_order)
    ) {$charset};");

    dbDelta("CREATE TABLE {$tools} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        garden_key varchar(64) NOT NULL,
        tool_key varchar(64) NOT NULL,
        name varchar(255) NOT NULL DEFAULT '',
        type varchar(128) NOT NULL DEFAULT '',
        description text NULL,
        owned int NOT NULL DEFAULT 0,
        qty int NOT NULL DEFAULT 0,
        image varchar(255) NOT NULL DEFAULT '',
        sort_order int NOT NULL DEFAULT 0,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY garden_tool (garden_key, tool_key),
        KEY garden_key (garden_key),
        KEY sort_order (sort_order)
    ) {$charset};");

    $wpdb->suppress_errors(true);
    $wpdb->query("ALTER TABLE {$racks} DROP INDEX garden_key");
    $wpdb->suppress_errors(false);

    dbDelta("CREATE TABLE {$racks} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        rack_code varchar(64) NOT NULL,
        rack_name varchar(255) NOT NULL DEFAULT '',
        garden_key varchar(64) NOT NULL DEFAULT '',
        owner_user_id bigint unsigned NULL,
        status varchar(32) NOT NULL DEFAULT 'draft',
        slot_count int NOT NULL DEFAULT 0,
        controller_type varchar(64) NOT NULL DEFAULT 'blynk',
        controller_label varchar(255) NOT NULL DEFAULT '',
        blynk_template_id varchar(128) NOT NULL DEFAULT '',
        blynk_template_name varchar(128) NOT NULL DEFAULT '',
        blynk_auth_token text NULL,
        blynk_email varchar(190) NOT NULL DEFAULT '',
        connectivity_status varchar(32) NOT NULL DEFAULT 'unknown',
        last_seen_at datetime NULL,
        notes text NULL,
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY rack_code (rack_code),
        KEY garden_key (garden_key),
        KEY status (status),
        KEY owner_user_id (owner_user_id)
    ) {$charset};");

    dbDelta("CREATE TABLE {$rack_slots} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        rack_id bigint unsigned NOT NULL,
        slot_index int NOT NULL,
        slot_code varchar(64) NOT NULL,
        slot_name varchar(255) NOT NULL DEFAULT '',
        plant_name varchar(255) NOT NULL DEFAULT '',
        pot_code varchar(64) NOT NULL DEFAULT '',
        camera_label varchar(255) NOT NULL DEFAULT '',
        camera_stream_url text NULL,
        control_channel varchar(64) NOT NULL DEFAULT '',
        control_vpin varchar(32) NOT NULL DEFAULT '',
        is_enabled tinyint(1) NOT NULL DEFAULT 1,
        crop_id bigint unsigned NULL,
        crop_cycle_id bigint unsigned NULL,
        status varchar(32) NOT NULL DEFAULT 'empty',
        created_at datetime NOT NULL,
        updated_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY rack_slot (rack_id, slot_index),
        UNIQUE KEY slot_code (slot_code),
        KEY rack_id (rack_id),
        KEY pot_code (pot_code),
        KEY status (status)
    ) {$charset};");

    dbDelta("CREATE TABLE {$rack_events} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        rack_id bigint unsigned NOT NULL,
        event_type varchar(64) NOT NULL,
        from_status varchar(32) NOT NULL DEFAULT '',
        to_status varchar(32) NOT NULL DEFAULT '',
        target_user_id bigint unsigned NULL,
        notes text NULL,
        created_by_user_id bigint unsigned NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY rack_id (rack_id),
        KEY event_type (event_type),
        KEY created_at (created_at)
    ) {$charset};");

    dbDelta("CREATE TABLE {$rack_assignments} (
        id bigint unsigned NOT NULL AUTO_INCREMENT,
        rack_id bigint unsigned NOT NULL,
        user_id bigint unsigned NOT NULL,
        garden_key varchar(64) NOT NULL DEFAULT '',
        household_key varchar(64) NOT NULL DEFAULT '',
        assigned_at datetime NOT NULL,
        released_at datetime NULL,
        status varchar(32) NOT NULL DEFAULT 'active',
        notes text NULL,
        PRIMARY KEY  (id),
        KEY rack_id (rack_id),
        KEY user_id (user_id),
        KEY garden_key (garden_key),
        KEY status (status)
    ) {$charset};");

    update_option('aitrongcay_social_schema_version', '5', false);
}
add_action('init', 'aitrongcay_install_social_tables', 30);

function aitrongcay_seed_db_from_legacy_datasets(): void
{
    if ((string) get_option('aitrongcay_db_seed_version', '') === '1') {
        return;
    }
    if (! function_exists('aitrongcay_portal_dataset_library')) {
        return;
    }

    $library = aitrongcay_portal_dataset_library();
    foreach ($library as $dataset) {
        $emails = array_values(array_filter((array) ($dataset['match_emails'] ?? [])));
        if (! $emails) {
            continue;
        }
        $owner = get_user_by('email', (string) $emails[0]);
        if (! $owner instanceof WP_User) {
            continue;
        }
        $garden_key = aitrongcay_primary_garden_key_for_user($owner);
        aitrongcay_upsert_garden_record($garden_key, (int) $owner->ID, [
            'garden_code' => (string) (($dataset['garden_code'] ?? '')),
            'garden_name' => (string) (($dataset['garden_name'] ?? '')),
            'summary' => (string) (($dataset['ai']['summary'] ?? ($dataset['summary'] ?? ''))),
            'status_line' => (string) (($dataset['status'] ?? '')),
        ]);

        foreach ((array) ($dataset['pots'] ?? []) as $index => $pot) {
            if (! is_array($pot)) continue;
            $pot['sort_order'] = $index + 1;
            aitrongcay_upsert_db_pot($garden_key, $pot);
        }

        foreach ((array) ($dataset['tool_shelf'] ?? []) as $index => $tool) {
            if (! is_array($tool)) continue;
            global $wpdb;
            $table = aitrongcay_garden_tools_table();
            $tool_key = sanitize_key((string) ($tool['name'] ?? ('tool_' . ($index + 1))));
            $now = current_time('mysql');
            $existing_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE garden_key = %s AND tool_key = %s LIMIT 1", $garden_key, $tool_key));
            $payload = [
                'name' => (string) ($tool['name'] ?? ''),
                'type' => (string) ($tool['type'] ?? ''),
                'description' => (string) ($tool['description'] ?? ''),
                'owned' => (int) ($tool['owned'] ?? 0),
                'qty' => (int) ($tool['qty'] ?? 0),
                'image' => (string) ($tool['image'] ?? ''),
                'sort_order' => $index + 1,
                'updated_at' => $now,
            ];
            if ($existing_id > 0) {
                $wpdb->update($table, $payload, ['id' => $existing_id], ['%s','%s','%s','%d','%d','%s','%d','%s'], ['%d']);
            } else {
                $wpdb->insert($table, array_merge(['garden_key' => $garden_key, 'tool_key' => $tool_key], $payload, ['created_at' => $now]), ['%s','%s','%s','%s','%s','%d','%d','%s','%d','%s','%s']);
            }
        }
    }

    update_option('aitrongcay_db_seed_version', '1', false);
}
add_action('init', 'aitrongcay_seed_db_from_legacy_datasets', 35);

function aitrongcay_current_garden_key(?WP_User $user = null): string
{
    $user = $user instanceof WP_User ? $user : wp_get_current_user();
    $email = strtolower(trim((string) ($user->user_email ?? 'guest@example.com')));
    return 'garden:' . md5($email);
}

function aitrongcay_legacy_garden_key_for_user(?WP_User $user = null): string
{
    return aitrongcay_current_garden_key($user);
}

function aitrongcay_primary_garden_key_for_user(?WP_User $user = null): string
{
    return aitrongcay_preferred_garden_key_for_user($user);
}

function aitrongcay_selected_garden_user_meta_key(): string
{
    return '_aitrongcay_selected_garden_key';
}

function aitrongcay_garden_name_overrides_meta_key(): string
{
    return '_aitrongcay_garden_name_overrides';
}

function aitrongcay_get_garden_name_override(string $garden_key, int $user_id): string
{
    if ($garden_key === '' || $user_id <= 0) {
        return '';
    }

    $bucket = get_user_meta($user_id, aitrongcay_garden_name_overrides_meta_key(), true);
    if (! is_array($bucket)) {
        return '';
    }

    return trim((string) ($bucket[$garden_key] ?? ''));
}

function aitrongcay_store_garden_name_override(string $garden_key, int $user_id, string $garden_name): void
{
    if ($garden_key === '' || $user_id <= 0) {
        return;
    }

    $bucket = get_user_meta($user_id, aitrongcay_garden_name_overrides_meta_key(), true);
    if (! is_array($bucket)) {
        $bucket = [];
    }

    $garden_name = trim(preg_replace('/\s+/u', ' ', $garden_name));
    if ($garden_name === '') {
        unset($bucket[$garden_key]);
    } else {
        $bucket[$garden_key] = $garden_name;
    }

    update_user_meta($user_id, aitrongcay_garden_name_overrides_meta_key(), $bucket);
}

function aitrongcay_format_person_name_list(array $names): string
{
    $names = array_values(array_filter(array_map(static function ($name): string {
        return trim((string) $name);
    }, $names)));

    $count = count($names);
    if ($count === 0) {
        return '';
    }
    if ($count === 1) {
        return $names[0];
    }
    if ($count === 2) {
        return $names[0] . ' và ' . $names[1];
    }

    $last = array_pop($names);
    return implode(', ', $names) . ' và ' . $last;
}

function aitrongcay_build_default_garden_name(string $garden_key, ?WP_User $viewer = null): string
{
    $garden_key = trim($garden_key);
    $members = $garden_key !== '' ? aitrongcay_get_garden_members($garden_key) : [];
    $owner_names = [];

    foreach ($members as $member) {
        $role = (string) ($member['role'] ?? 'viewer');
        $status = (string) ($member['status'] ?? '');
        if ($status !== 'active' || $role !== 'owner') {
            continue;
        }

        $member_user = get_user_by('id', (int) ($member['user_id'] ?? 0));
        if (! $member_user instanceof WP_User) {
            continue;
        }

        $display_name = trim((string) ($member_user->display_name ?: $member_user->first_name ?: $member_user->user_login));
        if ($display_name !== '' && ! in_array($display_name, $owner_names, true)) {
            $owner_names[] = $display_name;
        }
    }

    if ($owner_names !== []) {
        return 'Vườn của ' . aitrongcay_format_person_name_list($owner_names);
    }

    $viewer_user = $viewer instanceof WP_User ? $viewer : wp_get_current_user();
    $fallback_name = trim((string) ($viewer_user->display_name ?: $viewer_user->first_name ?: $viewer_user->user_login));
    return 'Vườn của ' . ($fallback_name !== '' ? $fallback_name : 'bạn');
}

function aitrongcay_get_garden_display_name(string $garden_key, ?WP_User $viewer = null): string
{
    $record = function_exists('aitrongcay_get_garden_record') ? aitrongcay_get_garden_record($garden_key) : null;
    $db_name = trim((string) ($record['garden_name'] ?? ''));
    if ($db_name !== '') {
        return $db_name;
    }

    $owner = aitrongcay_get_garden_owner_user($garden_key);
    $owner_id = (int) ($owner->ID ?? 0);
    $override = $owner_id > 0 ? aitrongcay_get_garden_name_override($garden_key, $owner_id) : '';
    if ($override !== '') {
        return $override;
    }

    $viewer = $viewer instanceof WP_User ? $viewer : wp_get_current_user();
    $viewer_id = (int) ($viewer->ID ?? 0);
    $viewer_override = $viewer_id > 0 ? aitrongcay_get_garden_name_override($garden_key, $viewer_id) : '';
    if ($viewer_override !== '') {
        return $viewer_override;
    }

    return aitrongcay_build_default_garden_name($garden_key, $viewer);
}

function aitrongcay_remember_selected_garden_key(int $user_id, string $garden_key): void
{
    $garden_key = trim($garden_key);
    if ($user_id <= 0 || $garden_key === '') {
        return;
    }

    update_user_meta($user_id, aitrongcay_selected_garden_user_meta_key(), $garden_key);
}

function aitrongcay_get_remembered_garden_key(int $user_id): string
{
    if ($user_id <= 0) {
        return '';
    }

    return trim((string) get_user_meta($user_id, aitrongcay_selected_garden_user_meta_key(), true));
}

function aitrongcay_resolve_garden_owner_id(string $garden_key, ?WP_User $viewer = null): int
{
    $owner = aitrongcay_get_garden_owner_user($garden_key);
    if ($owner instanceof WP_User) {
        return (int) $owner->ID;
    }

    $viewer = $viewer instanceof WP_User ? $viewer : wp_get_current_user();
    return $viewer instanceof WP_User ? (int) $viewer->ID : 0;
}

function aitrongcay_friend_pair_key(int $user_a, int $user_b): string
{
    $pair = [$user_a, $user_b];
    sort($pair, SORT_NUMERIC);
    return $pair[0] . ':' . $pair[1];
}

function aitrongcay_get_user_friends(int $user_id): array
{
    global $wpdb;
    $table = aitrongcay_friendships_table();
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE (requester_user_id = %d OR addressee_user_id = %d) AND status = 'accepted' ORDER BY id DESC",
        $user_id,
        $user_id
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_friend_ids(int $user_id): array
{
    $friends = aitrongcay_get_user_friends($user_id);
    if ($friends === []) {
        return [];
    }

    $friend_ids = [];
    foreach ($friends as $friendship) {
        $requester_id = (int) ($friendship['requester_user_id'] ?? 0);
        $addressee_id = (int) ($friendship['addressee_user_id'] ?? 0);
        $friend_id = $requester_id === $user_id ? $addressee_id : $requester_id;
        if ($friend_id > 0) {
            $friend_ids[] = $friend_id;
        }
    }

    return array_values(array_unique($friend_ids));
}

function aitrongcay_is_friend_with_garden_owner(string $garden_key, int $user_id): bool
{
    if ($garden_key === '' || $user_id <= 0) {
        return false;
    }

    $owner = aitrongcay_get_garden_owner_user($garden_key);
    if (! $owner instanceof WP_User) {
        return false;
    }

    if ((int) $owner->ID === $user_id) {
        return true;
    }

    return in_array((int) $owner->ID, aitrongcay_get_friend_ids($user_id), true);
}

function aitrongcay_get_friend_invites_received(int $user_id): array
{
    global $wpdb;
    $table = aitrongcay_friendships_table();
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE addressee_user_id = %d AND status = 'pending' ORDER BY id DESC",
        $user_id
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_active_garden_owners(int $viewer_user_id, string $search = ''): array
{
    global $wpdb;

    $members_table = aitrongcay_garden_members_table();
    $friendships_table = aitrongcay_friendships_table();
    $search = trim($search);

    $sql = "SELECT m.user_id, m.garden_key, u.display_name, u.user_login
        FROM {$members_table} m
        INNER JOIN {$wpdb->users} u ON u.ID = m.user_id
        WHERE m.role = 'owner'
          AND m.status = 'active'
          AND m.user_id <> %d";
    $params = [$viewer_user_id];

    if ($search !== '') {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $sql .= " AND (u.display_name LIKE %s OR u.user_login LIKE %s)";
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY CASE WHEN u.display_name = '' OR u.display_name IS NULL THEN u.user_login ELSE u.display_name END ASC, m.id ASC";

    $rows = $wpdb->get_results($wpdb->prepare($sql, ...$params), ARRAY_A) ?: [];
    if ($rows === []) {
        return [];
    }

    $owner_map = [];
    foreach ($rows as $row) {
        $owner_id = (int) ($row['user_id'] ?? 0);
        if ($owner_id <= 0 || isset($owner_map[$owner_id])) {
            continue;
        }
        $owner_map[$owner_id] = $row;
    }

    $owner_ids = array_keys($owner_map);
    if ($owner_ids === []) {
        return [];
    }

    $friendship_status_map = [];
    $placeholders = implode(',', array_fill(0, count($owner_ids), '%d'));
    $friendship_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT requester_user_id, addressee_user_id, status
         FROM {$friendships_table}
         WHERE ((requester_user_id = %d AND addressee_user_id IN ({$placeholders}))
             OR (addressee_user_id = %d AND requester_user_id IN ({$placeholders})))",
        ...array_merge([$viewer_user_id], $owner_ids, [$viewer_user_id], $owner_ids)
    ), ARRAY_A) ?: [];

    foreach ($friendship_rows as $row) {
        $requester_id = (int) ($row['requester_user_id'] ?? 0);
        $addressee_id = (int) ($row['addressee_user_id'] ?? 0);
        $other_id = $requester_id === $viewer_user_id ? $addressee_id : $requester_id;
        if ($other_id <= 0) {
            continue;
        }

        $status = (string) ($row['status'] ?? '');
        $friendship_status_map[$other_id] = [
            'status' => $status,
            'direction' => $status === 'pending'
                ? ($requester_id === $viewer_user_id ? 'outgoing' : 'incoming')
                : 'none',
        ];
    }

    $results = [];
    foreach ($owner_map as $owner_id => $row) {
        $owner = get_user_by('id', $owner_id);
        if (! $owner instanceof WP_User) {
            continue;
        }

        $garden_key = (string) ($row['garden_key'] ?? '');
        $profile = $garden_key !== '' ? aitrongcay_portal_profile_for_garden_context($garden_key, $owner) : aitrongcay_portal_profile_for_user($owner);
        $friendship = $friendship_status_map[$owner_id] ?? ['status' => 'none', 'direction' => 'none'];

        $results[] = [
            'user_id' => $owner_id,
            'garden_key' => $garden_key,
            'display_name' => trim((string) ($owner->display_name ?: $owner->first_name ?: $owner->user_login)),
            'user_login' => (string) $owner->user_login,
            'friendship_status' => (string) ($friendship['status'] ?? 'none'),
            'friendship_direction' => (string) ($friendship['direction'] ?? 'none'),
            'profile' => is_array($profile) ? $profile : null,
        ];
    }

    return $results;
}

function aitrongcay_sync_friend_memberships_for_garden(string $garden_key): void
{
    global $wpdb;

    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return;
    }

    $owner = aitrongcay_get_garden_owner_user($garden_key);
    if (! $owner instanceof WP_User) {
        return;
    }

    $owner_id = (int) $owner->ID;
    if ($owner_id <= 0) {
        return;
    }

    $friends = aitrongcay_get_user_friends($owner_id);
    if (! is_array($friends) || $friends === []) {
        return;
    }

    $table = aitrongcay_garden_members_table();
    foreach ($friends as $friendship) {
        $friend_id = (int) (($friendship['requester_user_id'] ?? 0) === $owner_id
            ? ($friendship['addressee_user_id'] ?? 0)
            : ($friendship['requester_user_id'] ?? 0));
        if ($friend_id <= 0 || $friend_id === $owner_id) {
            continue;
        }

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id, role, status FROM {$table} WHERE garden_key = %s AND user_id = %d ORDER BY id DESC LIMIT 1",
            $garden_key,
            $friend_id
        ), ARRAY_A);

        if (is_array($existing) && ! empty($existing['id'])) {
            $patch = [];
            if (($existing['status'] ?? '') !== 'active') {
                $patch['status'] = 'active';
            }
            if (($existing['role'] ?? '') === '' || ($existing['role'] ?? '') === 'owner') {
                $patch['role'] = 'viewer';
            }
            if ($patch !== []) {
                $patch['updated_at'] = current_time('mysql');
                $wpdb->update($table, $patch, ['id' => (int) $existing['id']]);
            }
            continue;
        }

        $wpdb->insert($table, [
            'garden_key' => $garden_key,
            'user_id' => $friend_id,
            'role' => 'viewer',
            'status' => 'active',
            'invited_by_user_id' => $owner_id,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
    }
}

function aitrongcay_get_garden_members(string $garden_key): array
{
    global $wpdb;
    $table = aitrongcay_garden_members_table();
    aitrongcay_sync_friend_memberships_for_garden($garden_key);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE garden_key = %s AND status IN ('active','invited') ORDER BY FIELD(role,'owner','co_owner','viewer'), id ASC",
        $garden_key
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_user_garden_memberships(int $user_id, array $statuses = ['active', 'invited']): array
{
    global $wpdb;
    $table = aitrongcay_garden_members_table();
    $statuses = array_values(array_filter(array_map('sanitize_key', $statuses)));
    if ($statuses === []) {
        $statuses = ['active', 'invited'];
    }
    $placeholders = implode(',', array_fill(0, count($statuses), '%s'));
    $params = array_merge([$user_id], $statuses);
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE user_id = %d AND status IN ({$placeholders}) ORDER BY updated_at DESC, id DESC",
        ...$params
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_garden_invites_received(int $user_id): array
{
    return aitrongcay_get_user_garden_memberships($user_id, ['invited']);
}

function aitrongcay_get_garden_owner_user(string $garden_key): ?WP_User
{
    global $wpdb;
    static $cache = [];

    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return null;
    }
    if (array_key_exists($garden_key, $cache)) {
        return $cache[$garden_key];
    }

    $table = aitrongcay_garden_members_table();
    $owner_id = $wpdb->get_var($wpdb->prepare(
        "SELECT user_id FROM {$table} WHERE garden_key = %s AND role = 'owner' AND status = 'active' ORDER BY id ASC LIMIT 1",
        $garden_key
    ));
    if (! $owner_id) {
        $cache[$garden_key] = null;
        return null;
    }

    $owner = get_user_by('id', (int) $owner_id);
    $cache[$garden_key] = $owner instanceof WP_User ? $owner : null;
    return $cache[$garden_key];
}

function aitrongcay_resolve_active_garden_key(?WP_User $user = null): string
{
    $user = $user instanceof WP_User ? $user : wp_get_current_user();
    $default_garden_key = aitrongcay_preferred_garden_key_for_user($user);
    if (! $user instanceof WP_User || ! $user->exists()) {
        return $default_garden_key;
    }

    $user_id = (int) $user->ID;
    $requested_garden_key = isset($_GET['garden']) ? sanitize_text_field((string) wp_unslash($_GET['garden'])) : '';
    if ($requested_garden_key !== '' && aitrongcay_user_can_view_garden($requested_garden_key, $user_id)) {
        aitrongcay_remember_selected_garden_key($user_id, $requested_garden_key);
        return $requested_garden_key;
    }

    $remembered_garden_key = aitrongcay_get_remembered_garden_key($user_id);
    if ($remembered_garden_key !== '' && aitrongcay_user_can_view_garden($remembered_garden_key, $user_id)) {
        return $remembered_garden_key;
    }

    $active_memberships = aitrongcay_get_user_garden_memberships($user_id, ['active']);
    foreach ($active_memberships as $membership) {
        if (($membership['role'] ?? '') === 'owner' && ! empty($membership['garden_key'])) {
            $garden_key = (string) $membership['garden_key'];
            aitrongcay_remember_selected_garden_key($user_id, $garden_key);
            return $garden_key;
        }
    }

    foreach ($active_memberships as $membership) {
        if (! empty($membership['garden_key'])) {
            $garden_key = (string) $membership['garden_key'];
            aitrongcay_remember_selected_garden_key($user_id, $garden_key);
            return $garden_key;
        }
    }

    aitrongcay_remember_selected_garden_key($user_id, $default_garden_key);
    return $default_garden_key;
}

function aitrongcay_get_role_label(string $role): string
{
    return match ($role) {
        'owner' => 'Chủ vườn',
        'co_owner' => 'Đồng sở hữu',
        'viewer' => 'Chỉ xem',
        default => $role,
    };
}

function aitrongcay_get_role_badge_class(string $role): string
{
    return match ($role) {
        'owner' => 'is-forest',
        'co_owner' => 'is-gold',
        'viewer' => 'is-sky',
        default => 'is-sand',
    };
}

function aitrongcay_get_member_status_label(string $status): string
{
    return match ($status) {
        'active' => 'Đang tham gia',
        'invited' => 'Đang chờ phản hồi',
        'declined' => 'Đã từ chối',
        'removed' => 'Đã gỡ',
        default => $status,
    };
}

function aitrongcay_get_viewable_gardens_for_user(?WP_User $user = null): array
{
    $user = $user instanceof WP_User ? $user : wp_get_current_user();
    if (! $user instanceof WP_User || ! $user->exists()) {
        return [];
    }

    $user_id = (int) $user->ID;
    $memberships = aitrongcay_get_user_garden_memberships($user_id, ['active']);
    $gardens = [];
    $append_garden = static function (string $garden_key, string $role) use (&$gardens, $user): void {
        if ($garden_key === '' || isset($gardens[$garden_key])) {
            return;
        }

        $profile = aitrongcay_portal_profile_for_garden_context($garden_key, $user);
        if (! is_array($profile)) {
            return;
        }

        $owner = aitrongcay_get_garden_owner_user($garden_key);
        $gardens[$garden_key] = [
            'garden_key' => $garden_key,
            'role' => $role,
            'profile' => $profile,
            'owner' => $owner,
            'member_count' => count(array_filter(
                aitrongcay_get_garden_members($garden_key),
                static fn(array $member): bool => ($member['status'] ?? '') === 'active'
            )),
        ];
    };

    foreach ($memberships as $membership) {
        $append_garden((string) ($membership['garden_key'] ?? ''), (string) ($membership['role'] ?? 'viewer'));
    }

    foreach (aitrongcay_get_friend_ids($user_id) as $friend_id) {
        $friend = get_user_by('id', $friend_id);
        if (! $friend instanceof WP_User) {
            continue;
        }

        $friend_garden_key = aitrongcay_preferred_garden_key_for_user($friend);
        $append_garden($friend_garden_key, 'viewer');
    }

    return $gardens;
}

function aitrongcay_portal_profile_for_garden_context(string $garden_key, ?WP_User $viewer = null): ?array
{
    $owner = aitrongcay_get_garden_owner_user($garden_key);
    $base_user = $owner instanceof WP_User ? $owner : ($viewer instanceof WP_User ? $viewer : wp_get_current_user());
    $viewer = $viewer instanceof WP_User ? $viewer : wp_get_current_user();

    if ($garden_key !== '' && function_exists('aitrongcay_get_garden_record') && function_exists('aitrongcay_upsert_garden_record')) {
        $record_probe = aitrongcay_get_garden_record($garden_key);
        if (! is_array($record_probe) || trim((string) ($record_probe['garden_name'] ?? '')) === '') {
            $owner_id = (int) ($owner->ID ?? 0);
            $viewer_id = (int) ($viewer->ID ?? 0);
            $override_name = $owner_id > 0 ? aitrongcay_get_garden_name_override($garden_key, $owner_id) : '';
            if ($override_name === '' && $viewer_id > 0) {
                $override_name = aitrongcay_get_garden_name_override($garden_key, $viewer_id);
            }
            if ($override_name !== '') {
                $sync_owner_id = $owner_id > 0 ? $owner_id : max(0, $viewer_id);
                if ($sync_owner_id > 0) {
                    aitrongcay_upsert_garden_record($garden_key, $sync_owner_id, [
                        'garden_name' => $override_name,
                        'garden_code' => (string) ($record_probe['garden_code'] ?? strtoupper(substr(md5($garden_key), 0, 6))),
                        'summary' => (string) ($record_probe['summary'] ?? ''),
                        'status_line' => (string) ($record_probe['status_line'] ?? ''),
                    ]);
                }
            }
        }
    }

    $profile = aitrongcay_portal_profile_for_user($base_user instanceof WP_User ? $base_user : null);

    $record = function_exists('aitrongcay_get_garden_record') ? aitrongcay_get_garden_record($garden_key) : null;
    if (is_array($record)) {
        $db_name = trim((string) ($record['garden_name'] ?? ''));
        $db_code = trim((string) ($record['garden_code'] ?? ''));
        $db_summary = trim((string) ($record['summary'] ?? ''));
        $db_status = trim((string) ($record['status_line'] ?? ''));
        if ($db_name !== '') {
            $profile['garden_name'] = $db_name;
        }
        if ($db_code !== '') {
            $profile['garden_code'] = $db_code;
        }
        if ($db_summary !== '') {
            $profile['summary'] = $db_summary;
        }
        if ($db_status !== '') {
            $profile['status'] = $db_status;
        }
    }

    if (! empty($garden_key)) {
        $profile['garden_name'] = aitrongcay_get_garden_display_name($garden_key, $base_user instanceof WP_User ? $base_user : null);
    }

    return $profile;
}

function aitrongcay_user_garden_role(string $garden_key, int $user_id): ?string
{
    global $wpdb;
    $table = aitrongcay_garden_members_table();
    $role = $wpdb->get_var($wpdb->prepare(
        "SELECT role FROM {$table} WHERE garden_key = %s AND user_id = %d AND status = 'active' LIMIT 1",
        $garden_key,
        $user_id
    ));

    if (is_string($role) && $role !== '') {
        return $role;
    }

    if (aitrongcay_is_friend_with_garden_owner($garden_key, $user_id)) {
        return 'viewer';
    }

    if ($user_id > 0 && $garden_key !== '') {
        $legacy_key = trim((string) get_user_meta($user_id, 'aitrongcay_garden_key', true));
        if ($legacy_key === $garden_key) {
            return 'owner';
        }
    }

    // Fallback: user có đơn hàng active gắn với garden_key này → coi như owner
    if ($user_id > 0 && $garden_key !== '') {
        $_ot = $wpdb->prefix . 'aitr_orders';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$_ot}'") === $_ot) {
            $user_email = (string) get_userdata($user_id)?->user_email;
            $has_order  = (bool) $wpdb->get_var($wpdb->prepare(
                "SELECT 1 FROM {$_ot} WHERE (user_id = %d OR customer_email = %s) AND garden_key = %s AND status = 'active' LIMIT 1",
                $user_id, $user_email, $garden_key
            ));
            if ($has_order) {
                return 'owner';
            }
        }
    }

    // Fallback: if the garden is a friend's preferred garden, grant viewer role
    if ($user_id > 0 && $garden_key !== '' && function_exists('aitrongcay_get_friend_ids')) {
        $friend_ids = aitrongcay_get_friend_ids($user_id);
        if (is_array($friend_ids)) {
            foreach ($friend_ids as $fid) {
                $f_user = get_user_by('id', $fid);
                if ($f_user instanceof WP_User && aitrongcay_preferred_garden_key_for_user($f_user) === $garden_key) {
                    return 'viewer';
                }
            }
        }
    }

    return null;
}

function aitrongcay_user_can_control_garden(string $garden_key, int $user_id): bool
{
    if ($user_id > 0 && user_can($user_id, 'manage_options')) {
        return true;
    }
    return in_array(aitrongcay_user_garden_role($garden_key, $user_id), ['owner', 'co_owner'], true);
}

function aitrongcay_user_can_view_garden(string $garden_key, int $user_id): bool
{
    if ($user_id > 0 && user_can($user_id, 'manage_options')) {
        return true;
    }
    return in_array(aitrongcay_user_garden_role($garden_key, $user_id), ['owner', 'co_owner', 'viewer'], true);
}

function aitrongcay_get_garden_record(string $garden_key): ?array
{
    global $wpdb;
    static $cache = [];
    if ($garden_key === '') {
        return null;
    }
    if (array_key_exists($garden_key, $cache)) {
        return $cache[$garden_key];
    }
    $table = aitrongcay_gardens_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s LIMIT 1", $garden_key), ARRAY_A);
    $cache[$garden_key] = is_array($row) ? $row : null;
    return $cache[$garden_key];
}

function aitrongcay_upsert_garden_record(string $garden_key, int $owner_user_id, array $payload = []): bool
{
    global $wpdb;
    if ($garden_key === '' || $owner_user_id <= 0) {
        return false;
    }
    $table = aitrongcay_gardens_table();
    $now = current_time('mysql');
    $existing = aitrongcay_get_garden_record($garden_key);
    $data = [
        'owner_user_id' => $owner_user_id,
        'garden_code' => (string) ($payload['garden_code'] ?? ''),
        'garden_name' => (string) ($payload['garden_name'] ?? ''),
        'summary' => (string) ($payload['summary'] ?? ''),
        'status_line' => (string) ($payload['status_line'] ?? ''),
        'updated_at' => $now,
    ];
    if ($existing) {
        return false !== $wpdb->update($table, $data, ['garden_key' => $garden_key], ['%d','%s','%s','%s','%s','%s'], ['%s']);
    }
    return false !== $wpdb->insert(
        $table,
        array_merge(['garden_key' => $garden_key], $data, ['created_at' => $now]),
        ['%s','%d','%s','%s','%s','%s','%s','%s']
    );
}

function aitrongcay_get_db_pots(string $garden_key): array
{
    global $wpdb;
    static $cache = [];
    if ($garden_key === '') {
        return [];
    }
    if (isset($cache[$garden_key])) {
        return $cache[$garden_key];
    }
    $table = aitrongcay_garden_pots_table();
    
    // TỰ ĐỘNG ĐỒNG BỘ KHOANG TỪ SLOTS
    if (function_exists('aitrongcay_get_rack_slots')) {
        $slots = aitrongcay_get_rack_slots($garden_key);
        if (!empty($slots)) {
            $existing_pots = $wpdb->get_col($wpdb->prepare("SELECT pot_code FROM {$table} WHERE garden_key = %s", $garden_key));
            foreach ($slots as $slot) {
                $pot_code = trim((string) ($slot['pot_code'] ?? ''));
                if ($pot_code !== '' && !in_array($pot_code, $existing_pots, true)) {
                    $slot_label = (string) ($slot['slot_name'] ?? '');
                    if ($slot_label === '') {
                        $slot_index = (int) ($slot['slot_index'] ?? 0);
                        $slot_label = 'Khoang trống ' . $slot_index;
                    }
                    $plant_name = trim((string) ($slot['plant_name'] ?? ''));
                    if ($plant_name === '') {
                        $plant_name = 'Cây chưa xác định';
                    }

                    aitrongcay_upsert_db_pot($garden_key, [
                        'code' => $pot_code,
                        'name' => $slot_label,
                        'plant_name' => $plant_name,
                        'status' => 'Đang theo dõi',
                        'status_summary' => 'Khoang vừa được kích hoạt, đang bắt đầu theo dõi Ngày 1.',
                        'ai_note' => 'Khu vườn bắt đầu ghi nhận dữ liệu sinh trưởng của khoang mới.',
                        'created_at' => current_time('mysql'),
                        'sort_order' => (int) ($slot['slot_index'] ?? 0),
                        'light_device' => trim((string) ($slot['control_channel'] ?? '')),
                        'video_url' => trim((string) ($slot['camera_stream_url'] ?? ''))
                    ]);
                    $existing_pots[] = $pot_code;
                }
            }
        }
    }

    $cache[$garden_key] = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s ORDER BY sort_order ASC, id ASC", $garden_key), ARRAY_A) ?: [];
    return $cache[$garden_key];
}

function aitrongcay_upsert_db_pot(string $garden_key, array $pot): bool
{
    global $wpdb;
    $garden_key = trim($garden_key);
    $pot_code = trim((string) ($pot['pot_code'] ?? $pot['code'] ?? ''));
    if ($garden_key === '' || $pot_code === '') {
        return false;
    }
    $table = aitrongcay_garden_pots_table();
    $now = current_time('mysql');
    $existing_id = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE garden_key = %s AND pot_code = %s LIMIT 1", $garden_key, $pot_code));
    $normalized_created_at = aitrongcay_normalize_pot_created_at((string) ($pot['created_at'] ?? ''));
    $data = [
        'pot_name' => (string) ($pot['pot_name'] ?? $pot['name'] ?? ''),
        'plant_name' => (string) ($pot['plant_name'] ?? ''),
        'status' => (string) ($pot['status'] ?? ''),
        'status_summary' => (string) ($pot['status_summary'] ?? ''),
        'ph' => (string) ($pot['ph'] ?? ''),
        'temperature' => (string) ($pot['temperature'] ?? ''),
        'humidity' => (string) ($pot['humidity'] ?? ''),
        'light_label' => (string) ($pot['light_label'] ?? $pot['light'] ?? ''),
        'light_device' => (string) ($pot['light_device'] ?? ''),
        'pump_label' => (string) ($pot['pump_label'] ?? $pot['pump'] ?? ''),
        'irrigation' => (string) ($pot['irrigation'] ?? ''),
        'video_url' => (string) ($pot['video_url'] ?? $pot['video'] ?? ''),
        'image_url' => (string) ($pot['image_url'] ?? $pot['image'] ?? ''),
        'latest_photo_id' => (int) ($pot['latest_photo_id'] ?? 0),
        'latest_photo_at' => ! empty($pot['latest_photo_at']) ? (string) $pot['latest_photo_at'] : null,
        'latest_analysis_level' => (int) ($pot['latest_analysis_level'] ?? 0),
        'latest_analysis_color' => (string) ($pot['latest_analysis_color'] ?? ''),
        'latest_analysis_label' => (string) ($pot['latest_analysis_label'] ?? ''),
        'latest_analysis_current_stage' => (string) ($pot['latest_analysis_current_stage'] ?? ''),
        'latest_analysis_summary' => (string) ($pot['latest_analysis_summary'] ?? ''),
        'latest_analysis_actions' => is_array($pot['latest_analysis_actions'] ?? null) ? wp_json_encode(array_values($pot['latest_analysis_actions']), JSON_UNESCAPED_UNICODE) : (string) ($pot['latest_analysis_actions'] ?? ''),
        'latest_analysis_escalate' => is_array($pot['latest_analysis_escalate'] ?? null) ? wp_json_encode(array_values($pot['latest_analysis_escalate']), JSON_UNESCAPED_UNICODE) : (string) ($pot['latest_analysis_escalate'] ?? ''),
        'latest_analysis_updated_at' => ! empty($pot['latest_analysis_updated_at']) ? (string) $pot['latest_analysis_updated_at'] : null,
        'ai_note' => (string) ($pot['ai_note'] ?? ''),
        'harvest_eta' => (string) ($pot['harvest_eta'] ?? ''),
        'sort_order' => (int) ($pot['sort_order'] ?? 0),
        'updated_at' => $now,
    ];
    if ($normalized_created_at !== '') {
        $data['created_at'] = $normalized_created_at;
    }
    if ($existing_id > 0) {
        return false !== $wpdb->update($table, $data, ['id' => $existing_id]);
    }
    return false !== $wpdb->insert($table, array_merge(['garden_key' => $garden_key, 'pot_code' => $pot_code], $data, ['created_at' => $normalized_created_at !== '' ? $normalized_created_at : $now]));
}

function aitrongcay_attachment_orientation_class(int $attachment_id): string
{
    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return '';
    }
    $meta = wp_get_attachment_metadata($attachment_id);
    $width = (int) ($meta['width'] ?? 0);
    $height = (int) ($meta['height'] ?? 0);
    if ($width > 0 && $height > 0 && $height > $width) {
        return 'is-portrait-rotated';
    }
    return '';
}

function aitrongcay_landscape_preview_url(int $attachment_id): string
{
    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return '';
    }

    $existing = trim((string) get_post_meta($attachment_id, '_aitrongcay_landscape_preview_url', true));
    if ($existing !== '') {
        return wp_make_link_relative($existing);
    }

    $meta = wp_get_attachment_metadata($attachment_id);
    $width = (int) ($meta['width'] ?? 0);
    $height = (int) ($meta['height'] ?? 0);
    if ($width <= 0 || $height <= 0 || $height <= $width) {
        return '';
    }

    $source_path = get_attached_file($attachment_id);
    if (! is_string($source_path) || $source_path === '' || ! file_exists($source_path)) {
        return '';
    }

    $editor = wp_get_image_editor($source_path);
    if (is_wp_error($editor)) {
        return '';
    }

    $editor->rotate(90);
    $editor->resize(1600, 900, true);
    $saved = $editor->save();
    if (is_wp_error($saved) || empty($saved['path']) || empty($saved['file'])) {
        return '';
    }

    $upload = wp_get_upload_dir();
    $baseurl = (string) ($upload['baseurl'] ?? '');
    $basedir = (string) ($upload['basedir'] ?? '');
    $saved_path = (string) ($saved['path'] ?? '');
    if ($baseurl === '' || $basedir === '' || $saved_path === '' || ! str_starts_with($saved_path, $basedir)) {
        return '';
    }

    $relative = ltrim(str_replace($basedir, '', $saved_path), '/');
    $url = wp_make_link_relative(trailingslashit($baseurl) . str_replace('\\', '/', $relative));
    update_post_meta($attachment_id, '_aitrongcay_landscape_preview_url', $url);
    return $url;
}

function aitrongcay_set_latest_pot_photo(string $garden_key, string $pot_code, int $attachment_id): bool
{
    global $wpdb;
    $garden_key = trim($garden_key);
    $pot_code = trim($pot_code);
    $attachment_id = absint($attachment_id);
    if ($garden_key === '' || $pot_code === '' || $attachment_id <= 0) {
        return false;
    }
    $table = aitrongcay_garden_pots_table();
    $image_url = aitrongcay_landscape_preview_url($attachment_id);
    if ($image_url === '') {
        $image_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id)));
    }
    return false !== $wpdb->update(
        $table,
        [
            'image_url' => $image_url,
            'latest_photo_id' => $attachment_id,
            'latest_photo_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ],
        [
            'garden_key' => $garden_key,
            'pot_code' => $pot_code,
        ],
        ['%s', '%d', '%s', '%s'],
        ['%s', '%s']
    );
}

function aitrongcay_get_latest_pot_photo_context(string $garden_key, string $pot_code): array
{
    $garden_key = trim($garden_key);
    $pot_code = strtoupper(trim($pot_code));
    if ($garden_key === '' || $pot_code === '') {
        return [];
    }
    foreach (aitrongcay_get_db_pots($garden_key) as $pot) {
        if (strtoupper(trim((string) ($pot['pot_code'] ?? ''))) !== $pot_code) {
            continue;
        }
        $attachment_id = (int) ($pot['latest_photo_id'] ?? 0);
        $image_url = trim((string) ($pot['image_url'] ?? ''));
        $captured_at = trim((string) ($pot['latest_photo_at'] ?? ''));
        return [
            'pot_code' => $pot_code,
            'pot_name' => (string) ($pot['pot_name'] ?? $pot_code),
            'attachment_id' => $attachment_id,
            'image_url' => $image_url,
            'captured_at' => $captured_at,
            'title' => $attachment_id > 0 ? get_the_title($attachment_id) : '',
            'download_url' => $attachment_id > 0 ? wp_make_link_relative((string) wp_get_attachment_url($attachment_id)) : $image_url,
            'analysis_mode' => 'single-latest',
            'daily_photo_count' => 1,
            'daily_photos' => [],
        ];
    }
    return [];
}

function aitrongcay_get_same_day_pot_photo_context(string $garden_key, string $pot_code): array
{
    $base = aitrongcay_get_latest_pot_photo_context($garden_key, $pot_code);
    if (! $base || empty($base['captured_at'])) {
        return $base;
    }

    $captured_at = (string) $base['captured_at'];
    $captured_ts = strtotime($captured_at);
    $day_start = $captured_ts ? wp_date('Y-m-d 00:00:00', $captured_ts) : wp_date('Y-m-d 00:00:00');
    $day_end = $captured_ts ? wp_date('Y-m-d 23:59:59', $captured_ts) : wp_date('Y-m-d 23:59:59');

    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 100,
        'orderby' => 'date',
        'order' => 'ASC',
        'date_query' => [
            [
                'after' => $day_start,
                'before' => $day_end,
                'inclusive' => true,
                'column' => 'post_date_gmt',
            ],
        ],
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_aitrongcay_photo_garden_key',
                'value' => $garden_key,
            ],
            [
                'key' => '_aitrongcay_pot_code',
                'value' => strtoupper(trim($pot_code)),
            ],
        ],
    ]);

    $daily_photos = [];
    foreach ($attachments as $attachment) {
        $attachment_id = (int) $attachment->ID;
        $daily_photos[] = [
            'attachment_id' => $attachment_id,
            'title' => get_the_title($attachment_id),
            'image_url' => wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id))),
            'download_url' => wp_make_link_relative((string) wp_get_attachment_url($attachment_id)),
            'captured_at' => (string) ($attachment->post_date_gmt ?: $attachment->post_date),
        ];
    }

    $base['daily_photos'] = $daily_photos;
    $base['daily_photo_count'] = count($daily_photos);
    $base['analysis_mode'] = count($daily_photos) > 1 ? 'same-day-batch' : 'single-latest';
    return $base;
}

function aitrongcay_onboarding_analysis_palette(): array
{
    return [
        1 => ['color' => 'none', 'label' => 'Chưa đủ hồ sơ'],
        2 => ['color' => 'xanh-non', 'label' => 'Ổn nhưng cần theo dõi'],
        3 => ['color' => 'vàng', 'label' => 'Cần chú ý'],
        4 => ['color' => 'cam', 'label' => 'Cảnh báo cao'],
        5 => ['color' => 'do', 'label' => 'Cần xử lý ngay'],
    ];
}

function aitrongcay_onboarding_plant_record(int $plant_id): ?array
{
    global $wpdb;
    if ($plant_id <= 0 || ! function_exists('aitrongcay_onboarding_tables')) {
        return null;
    }

    $tables = aitrongcay_onboarding_tables();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tables['plants']} WHERE id = %d LIMIT 1", $plant_id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_build_onboarding_analysis_context(array $pot): array
{
    $resolved = function_exists('aitrongcay_resolve_onboarding_plant_for_pot')
        ? aitrongcay_resolve_onboarding_plant_for_pot($pot)
        : ['has_onboarding' => false, 'plant_name' => 'Cây chưa xác định', 'plant_id' => 0, 'plant_slug' => ''];

    $pot_name = trim((string) ($pot['pot_name'] ?? $pot['name'] ?? $pot['code'] ?? 'Khoang cây'));
    $plant_name = trim((string) ($resolved['plant_name'] ?? '')) ?: $pot_name;
    $plant_id = (int) ($resolved['plant_id'] ?? 0);

    if (empty($resolved['has_onboarding']) || $plant_id <= 0) {
        return [
            'has_onboarding' => false,
            'plant_name' => $plant_name,
            'message' => 'Cây chưa có hồ sơ onboarding nên Cindy chưa phân tích sâu cho khoang này.',
        ];
    }

    $plant = aitrongcay_onboarding_plant_record($plant_id);
    if (! is_array($plant)) {
        return [
            'has_onboarding' => false,
            'plant_name' => $plant_name,
            'message' => 'Cây chưa có hồ sơ onboarding hoàn chỉnh nên Cindy chưa phân tích sâu cho khoang này.',
        ];
    }

    $environment = function_exists('aitrongcay_plant_environment_profile') ? (aitrongcay_plant_environment_profile($plant_id) ?: []) : [];
    $nutrition = function_exists('aitrongcay_plant_nutrition_profile') ? (aitrongcay_plant_nutrition_profile($plant_id) ?: []) : [];
    $checklists = function_exists('aitrongcay_plant_checklists') ? aitrongcay_plant_checklists($plant_id) : [];
    $health_issues = function_exists('aitrongcay_plant_health_issues') ? aitrongcay_plant_health_issues($plant_id) : [];

    $context_lines = [];

    $plant_meta = [];
    foreach ([
        'Tên cây' => (string) ($plant['public_name'] ?? ''),
        'Slug' => (string) ($plant['slug'] ?? ''),
        'Tên nội bộ' => (string) ($plant['internal_name'] ?? ''),
        'Tên khoa học' => (string) ($plant['scientific_name'] ?? ''),
        'Giống' => (string) ($plant['variety_name'] ?? ''),
        'Chu kỳ mặc định (ngày)' => (string) ($plant['default_cycle_days'] ?? ''),
        'Nảy mầm (ngày)' => (string) ($plant['germination_days'] ?? ''),
        'Bắt đầu thu (ngày)' => (string) ($plant['harvest_start_day'] ?? ''),
        'Chiều cao khi cây trưởng thành (cm)' => (string) ($plant['mature_height_cm'] ?? ($plant['harvest_end_day'] ?? '')),
        'Mức độ khó' => (string) ($plant['difficulty_level'] ?? ''),
        'Mô tả ngắn' => (string) ($plant['short_description'] ?? ''),
    ] as $label => $value) {
        $value = trim($value);
        if ($value !== '') {
            $plant_meta[] = $label . ': ' . $value;
        }
    }
    if ($plant_meta !== []) {
        $context_lines[] = "[Hồ sơ cây]\n- " . implode("\n- ", $plant_meta);
    }

    $env_fields = [];
    foreach ([
        'Môi trường mục tiêu' => (string) ($environment['environment_summary'] ?? ''),
        'Nhiệt độ' => (string) ($environment['temperature_range'] ?? ''),
        'Độ ẩm' => (string) ($environment['humidity_range'] ?? ''),
        'Ánh sáng' => (string) ($environment['light_notes'] ?? ''),
        'Airflow' => (string) ($environment['airflow_notes'] ?? ''),
        'Nước / tưới' => (string) ($environment['watering_notes'] ?? ''),
        'Ghi chú' => (string) ($environment['environment_notes'] ?? ''),
    ] as $label => $value) {
        $value = trim($value);
        if ($value !== '') {
            $env_fields[] = $label . ': ' . $value;
        }
    }
    if ($env_fields !== []) {
        $context_lines[] = "[Môi trường]\n- " . implode("\n- ", $env_fields);
    }

    $nutrition_fields = [];
    foreach ([
        'Dinh dưỡng cơ bản' => (string) ($nutrition['nutrition_summary'] ?? ''),
        'EC' => (string) ($nutrition['ec_range'] ?? ''),
        'pH' => (string) ($nutrition['ph_range'] ?? ''),
        'Lịch cho ăn' => (string) ($nutrition['feeding_schedule'] ?? ''),
        'Ghi chú' => (string) ($nutrition['nutrition_notes'] ?? ''),
    ] as $label => $value) {
        $value = trim($value);
        if ($value !== '') {
            $nutrition_fields[] = $label . ': ' . $value;
        }
    }
    if ($nutrition_fields !== []) {
        $context_lines[] = "[Dinh dưỡng]\n- " . implode("\n- ", $nutrition_fields);
    }

    if ($checklists !== []) {
        $chunks = [];
        foreach (array_slice($checklists, 0, 12) as $item) {
            if (! is_array($item)) {
                continue;
            }
            $line = trim((string) ($item['item_text'] ?? ''));
            if ($line === '') {
                continue;
            }
            $type = trim((string) ($item['checklist_type'] ?? ''));
            $chunks[] = ($type !== '' ? '[' . $type . '] ' : '') . $line;
        }
        if ($chunks !== []) {
            $context_lines[] = "[Checklist]\n- " . implode("\n- ", $chunks);
        }
    }

    if ($health_issues !== []) {
        $chunks = [];
        foreach (array_slice($health_issues, 0, 8) as $issue) {
            if (! is_array($issue)) {
                continue;
            }
            $issue_name = trim((string) ($issue['issue_name'] ?? ''));
            $symptom = trim((string) ($issue['symptoms_text'] ?? ''));
            $response = trim((string) ($issue['response_guidance'] ?? ''));
            $line = $issue_name !== '' ? $issue_name : 'Dấu hiệu thường gặp';
            if ($symptom !== '') {
                $line .= ' | Triệu chứng: ' . $symptom;
            }
            if ($response !== '') {
                $line .= ' | Xử lý: ' . $response;
            }
            $chunks[] = $line;
        }
        if ($chunks !== []) {
            $context_lines[] = "[Vấn đề sức khỏe thường gặp]\n- " . implode("\n- ", $chunks);
        }
    }

    $longtext_tables = [
        'plant_qa_pairs' => 'AI Q&A',
        'plant_ai_schemas' => 'AI schema',
        'plant_decision_nodes' => 'Decision tree',
        'plant_alert_rules' => 'Alert rules',
        'plant_workflows' => 'Workflow',
        'plant_protocol_topics' => 'Protocol',
        'plant_robot_tasks' => 'Robot tasks',
    ];
    foreach ($longtext_tables as $table_key => $title) {
        if (! function_exists('aitrongcay_plant_longtext_pack')) {
            continue;
        }
        $text = trim((string) aitrongcay_plant_longtext_pack($plant_id, $table_key));
        if ($text === '') {
            continue;
        }
        $text = preg_replace('/\s+/', ' ', $text);
        $context_lines[] = '[' . $title . "]\n- " . mb_substr($text, 0, 1200);
    }

    $stage_reference = [];
    $growth_stages = function_exists('aitrongcay_plant_growth_stages') ? aitrongcay_plant_growth_stages($plant_id) : [];
    foreach ($growth_stages as $stage_row) {
        if (! is_array($stage_row)) {
            continue;
        }
        $stage_name = trim((string) ($stage_row['stage_name'] ?? ''));
        if ($stage_name === '') {
            continue;
        }
        $stage_reference[] = [
            'name' => $stage_name,
            'index' => (int) ($stage_row['stage_index'] ?? 0),
            'code' => trim((string) ($stage_row['stage_code'] ?? '')),
        ];
    }

    return [
        'has_onboarding' => true,
        'plant_id' => $plant_id,
        'plant_name' => $plant_name,
        'plant_slug' => (string) ($plant['slug'] ?? ''),
        'plant' => $plant,
        'environment' => $environment,
        'nutrition' => $nutrition,
        'checklists' => $checklists,
        'health_issues' => $health_issues,
        'growth_stages' => $stage_reference,
        'context_text' => implode("\n\n", array_filter($context_lines)),
    ];
}

function aitrongcay_extract_json_object_from_text(string $text): array
{
    $text = trim($text);
    if ($text === '') {
        return [];
    }
    $text = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $text);
    $decoded = json_decode($text, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    if (preg_match('/\{.*\}/su', $text, $matches) === 1) {
        $decoded = json_decode($matches[0], true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return [];
}

function aitrongcay_build_data_image_url_from_attachment(int $attachment_id): string
{
    $attachment_id = absint($attachment_id);
    if ($attachment_id <= 0) {
        return '';
    }

    $path = get_attached_file($attachment_id);
    if (! is_string($path) || $path === '' || ! file_exists($path) || ! is_readable($path)) {
        return '';
    }

    $mime = (string) get_post_mime_type($attachment_id);
    if ($mime === '' || ! str_starts_with($mime, 'image/')) {
        $mime = (string) mime_content_type($path);
    }
    if ($mime === '' || ! str_starts_with($mime, 'image/')) {
        $mime = 'image/jpeg';
    }

    $bytes = @file_get_contents($path);
    if (! is_string($bytes) || $bytes === '') {
        return '';
    }

    return 'data:' . $mime . ';base64,' . base64_encode($bytes);
}

function aitrongcay_extract_structured_analysis_from_text(string $text): array
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '') {
        return [];
    }

    $result = [];

    if (preg_match('/(mức|cấp|alert)[^0-9]{0,8}([1-5])/iu', $text, $m) === 1) {
        $result['level'] = (int) $m[2];
    }

    foreach ([
        'do' => ['đỏ', 'do'],
        'cam' => ['cam'],
        'vàng' => ['vàng', 'vang'],
        'xanh-non' => ['xanh non', 'xanh-non', 'xanh'],
    ] as $color => $variants) {
        foreach ($variants as $variant) {
            if (mb_stripos($text, $variant) !== false) {
                $result['color'] = $color;
                break 2;
            }
        }
    }

    if (preg_match('/(giai đoạn|current_stage)\s*[:\-]\s*(.+?)(?=(nhận định|tóm tắt|summary|khuyến nghị|actions|$))/iu', $text, $m) === 1) {
        $result['current_stage'] = trim($m[2]);
    }

    if (preg_match('/(nhận định|tóm tắt|summary)\s*[:\-]\s*(.+?)(?=(khuyến nghị|actions|cần theo dõi|escalate|$))/iu', $text, $m) === 1) {
        $result['summary'] = trim($m[2]);
    } else {
        $result['summary'] = $text;
    }

    if (preg_match('/(khuyến nghị|actions?)\s*[:\-]\s*(.+?)(?=(cần theo dõi thêm|escalate|knowledge|$))/iu', $text, $m) === 1) {
        $actions_text = trim($m[2]);
        $actions = preg_split('/\s*[•\-\n]\s*|\s*;\s*/u', $actions_text) ?: [];
        $result['actions'] = array_values(array_filter(array_map('trim', $actions)));
    }

    if (preg_match('/(cần theo dõi thêm|escalate_if|leo thang)\s*[:\-]\s*(.+?)(?=(knowledge|ghi chú|$))/iu', $text, $m) === 1) {
        $items = preg_split('/\s*[•\-\n]\s*|\s*;\s*/u', trim($m[2])) ?: [];
        $result['escalate_if'] = array_values(array_filter(array_map('trim', $items)));
    }

    if (preg_match('/(ghi chú tri thức|knowledge_note|căn cứ)\s*[:\-]\s*(.+)$/iu', $text, $m) === 1) {
        $result['knowledge_note'] = trim($m[2]);
    }

    return $result;
}

function aitrongcay_analysis_debug_sanitize(mixed $value, int $depth = 0): mixed
{
    if ($depth > 6) {
        return '[depth-truncated]';
    }

    if (is_array($value)) {
        $sanitized = [];
        foreach ($value as $key => $item) {
            if (in_array((string) $key, ['request_body', 'messages', 'input'], true)) {
                $sanitized[$key] = '[omitted-large-payload]';
                continue;
            }
            $sanitized[$key] = aitrongcay_analysis_debug_sanitize($item, $depth + 1);
        }
        return $sanitized;
    }

    if (! is_string($value)) {
        return $value;
    }

    if (str_starts_with($value, 'data:image/')) {
        return '[data-image-omitted]';
    }

    if (strlen($value) > 1200) {
        return substr($value, 0, 1200) . '…[truncated]';
    }

    return $value;
}

function aitrongcay_analysis_debug_log(string $event, array $data = []): void
{
    $upload_dir = wp_get_upload_dir();
    $base_dir = (string) ($upload_dir['basedir'] ?? '');
    if ($base_dir === '') {
        return;
    }

    $dir = trailingslashit($base_dir) . 'aitrongcay-debug';
    if (! is_dir($dir)) {
        wp_mkdir_p($dir);
    }
    if (! is_dir($dir)) {
        return;
    }

    $log_path = trailingslashit($dir) . 'analysis-debug.log';
    if (file_exists($log_path) && filesize($log_path) > 1024 * 1024) {
        @rename($log_path, trailingslashit($dir) . 'analysis-debug.prev.log');
    }

    $line = [
        'time' => wp_date('c'),
        'event' => $event,
        'data' => aitrongcay_analysis_debug_sanitize($data),
    ];
    @file_put_contents($log_path, wp_json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
}

function aitrongcay_extract_openresponses_text(array $response_json): string
{
    $direct = trim((string) ($response_json['output_text'] ?? ''));
    if ($direct !== '') {
        return $direct;
    }

    $chunks = [];
    foreach ((array) ($response_json['output'] ?? []) as $item) {
        if (! is_array($item)) {
            continue;
        }
        foreach ((array) ($item['content'] ?? []) as $content) {
            if (! is_array($content)) {
                continue;
            }
            $text = trim((string) ($content['text'] ?? $content['output_text'] ?? ''));
            if ($text !== '') {
                $chunks[] = $text;
            }
        }
    }

    return trim(implode("\n", array_filter($chunks)));
}

function aitrongcay_normalize_onboarding_stage_label(array $pot, string $raw_stage): string
{
    $raw_stage = trim($raw_stage);
    if ($raw_stage === '') {
        return '';
    }

    $onboarding = aitrongcay_build_onboarding_analysis_context($pot);
    $plant_id = (int) ($onboarding['plant_id'] ?? 0);
    if ($plant_id <= 0 || ! function_exists('aitrongcay_plant_growth_stages')) {
        return $raw_stage;
    }

    $raw_stage_lc = strtolower(remove_accents($raw_stage));
    $stage_signal_map = [
        'nay mam' => ['nay mam', 'mam', 'cay mam', 'germ'],
        'phat trien sinh duong' => ['sinh truong', 'sinh duong', 'than la', 'ra la', 'vegetative'],
        'ra hoa & thu phan' => ['ra hoa', 'thu phan', 'co hoa', 'flower'],
        'dau qua & phat trien qua' => ['dau qua', 'nuoi qua', 'qua non', 'phat trien qua', 'mang qua', 'fruit set', 'fruit'],
        'chin & thu hoach' => ['chin', 'thu hoach', 'sap thu', 'harvest'],
    ];

    foreach (aitrongcay_plant_growth_stages($plant_id) as $stage_row) {
        $stage_name = trim((string) ($stage_row['stage_name'] ?? ''));
        if ($stage_name === '') {
            continue;
        }
        $stage_name_lc = strtolower(remove_accents($stage_name));
        $signals = [$stage_name_lc];
        foreach ($stage_signal_map as $anchor => $aliases) {
            if ($stage_name_lc !== '' && str_contains($stage_name_lc, $anchor)) {
                $signals = array_merge($signals, $aliases);
            }
        }
        foreach (array_unique(array_filter($signals)) as $signal) {
            if (str_contains($stage_name_lc, $raw_stage_lc) || str_contains($raw_stage_lc, $stage_name_lc) || str_contains($raw_stage_lc, $signal) || str_contains($signal, $raw_stage_lc)) {
                return $stage_name;
            }
        }
    }

    return $raw_stage;
}

function aitrongcay_clean_analysis_recommendation_text(string $text): string
{
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?: $text);
    if ($text === '') {
        return '';
    }
    $drop_patterns = [
        '/^đây là kết quả đã được đối chiếu với hồ sơ onboarding của cây, không còn chỉ là nhận xét nền cũ\.?\s*/iu',
        '/^đã đối chiếu với hồ sơ onboarding của cây\.?\s*/iu',
    ];
    foreach ($drop_patterns as $pattern) {
        $text = trim((string) preg_replace($pattern, '', $text));
    }
    $text = trim($text, " ·,.;\t\n\r\0\x0B");
    return $text;
}

function aitrongcay_build_stage_specific_recommendation(array $pot, string $current_stage, array $fallback_analysis = []): string
{
    $onboarding = aitrongcay_build_onboarding_analysis_context($pot);
    $plant_id = (int) ($onboarding['plant_id'] ?? 0);
    if ($plant_id <= 0) {
        return trim((string) ($fallback_analysis['recommendation'] ?? ($fallback_analysis['actions'][0] ?? '')));
    }

    $environment = function_exists('aitrongcay_plant_environment_profile') ? (aitrongcay_plant_environment_profile($plant_id) ?: []) : [];
    $nutrition = function_exists('aitrongcay_plant_nutrition_profile') ? (aitrongcay_plant_nutrition_profile($plant_id) ?: []) : [];
    $stage_lc = strtolower(remove_accents(trim($current_stage)));

    $ec = trim((string) ($nutrition['ec_target'] ?? ''));
    if ($ec === '' || $ec === '0.00' || $ec === '0') {
        $ec = trim((string) ($nutrition['ec_range'] ?? $environment['ec_range'] ?? ''));
    }
    $ph = trim((string) ($nutrition['ph_target'] ?? ''));
    if ($ph === '' || $ph === '0.00' || $ph === '0') {
        $ph = trim((string) ($nutrition['ph_range'] ?? $environment['ph_range'] ?? ''));
    }
    $water_ml = (float) ($nutrition['water_ml_per_tray_per_day'] ?? 0);
    $temperature_range = trim((string) ($environment['temperature_range'] ?? ''));

    $parts = [];
    if (str_contains($stage_lc, 'qua') || str_contains($stage_lc, 'fruit') || str_contains($stage_lc, 'dau qua')) {
        $line = 'Giữ';
        $details = [];
        if ($ec !== '') $details[] = 'EC khoảng ' . $ec;
        if ($ph !== '') $details[] = 'pH khoảng ' . $ph;
        $line .= $details ? ' ' . implode(', ', $details) : ' dinh dưỡng và pH ổn định';
        $line .= ' để cây giữ nhịp nuôi quả ổn định.';
        $parts[] = $line;

        $monitor = [];
        if ($water_ml > 0) $monitor[] = 'lượng nước khoảng ' . rtrim(rtrim(number_format($water_ml, 0, ',', '.'), '0'), ',') . ' ml/khoang/ngày';
        else $monitor[] = 'mực nước mỗi ngày';
        if ($temperature_range !== '') $monitor[] = 'nhiệt độ dung dịch trong vùng ' . $temperature_range;
        else $monitor[] = 'nhiệt độ nước không tăng bất thường';
        $parts[] = 'Theo dõi ' . implode(' và ', $monitor) . ', đồng thời quan sát BER, nứt quả hoặc rụng quả non trong 1 đến 3 ngày tới.';
    }

    if ($parts === []) {
        $line = 'Giữ điều kiện môi trường và dinh dưỡng ổn định';
        $details = [];
        if ($ec !== '') $details[] = 'EC khoảng ' . $ec;
        if ($ph !== '') $details[] = 'pH khoảng ' . $ph;
        if ($temperature_range !== '') $details[] = 'nhiệt độ ' . $temperature_range;
        if ($details) $line .= ' với ' . implode(', ', $details);
        $line .= '.';
        $parts[] = $line;
        $parts[] = trim((string) ($fallback_analysis['actions'][0] ?? 'Theo dõi thêm ảnh mới trong 1 đến 3 ngày tới để đối chiếu đúng nhịp phát triển của cây.'));
    }

    return aitrongcay_clean_analysis_recommendation_text(implode(' ', array_filter(array_map('trim', $parts))));
}

function aitrongcay_generate_cindy_onboarding_analysis(array $pot, array $photo_context, array $fallback_analysis): array
{
    if (! function_exists('aitrongcay_ai_agent_config') || ! function_exists('aitrongcay_ai_agent_is_remote_enabled')) {
        return ['ok' => false, 'message' => 'AI bridge chưa sẵn sàng.'];
    }

    $onboarding = aitrongcay_build_onboarding_analysis_context($pot);
    if (empty($onboarding['has_onboarding'])) {
        $summary = (string) ($onboarding['message'] ?? 'Cây chưa có hồ sơ onboarding nên Cindy chưa phân tích sâu cho khoang này.');
        return [
            'ok' => true,
            'analysis' => [
                'level' => 1,
                'color' => 'none',
                'label' => 'Chưa có hồ sơ onboarding',
                'current_stage' => 'Chưa có hồ sơ đối chiếu',
                'summary' => $summary,
                'actions' => ['Hãy tạo hoặc hoàn thiện hồ sơ onboarding cho cây này rồi phân tích lại.'],
                'escalate_if' => [],
                'knowledge_note' => 'Phân tích sâu đang bị bỏ qua vì chưa có hồ sơ onboarding của cây.',
                'updated_at' => current_time('mysql'),
            ],
        ];
    }

    $config = aitrongcay_ai_agent_config();
    if (! aitrongcay_ai_agent_is_remote_enabled() || (string) ($config['mode'] ?? '') !== 'openai-chat' || trim((string) ($config['endpoint_url'] ?? '')) === '') {
        return ['ok' => false, 'message' => 'Cindy image analysis chỉ bật khi AI remote đang ở mode OpenClaw Chat API.'];
    }

    $pot_name = trim((string) ($pot['pot_name'] ?? $pot['name'] ?? $pot['code'] ?? 'Khoang cây'));
    $pot_code = trim((string) ($pot['pot_code'] ?? $pot['code'] ?? ''));
    $status = trim((string) ($pot['status'] ?? ''));
    $status_summary = trim((string) ($pot['status_summary'] ?? ''));
    $ai_note = trim((string) ($pot['ai_note'] ?? ''));
    $photo_count = max(1, (int) ($photo_context['daily_photo_count'] ?? 1));
    $captured_at = trim((string) ($photo_context['captured_at'] ?? ''));
    $analysis_mode = trim((string) ($photo_context['analysis_mode'] ?? 'single-latest'));

    $stage_focus = trim((string) ($fallback_analysis['current_stage'] ?? ''));
    $stage_focus = aitrongcay_normalize_onboarding_stage_label($pot, $stage_focus);
    $stage_focus_lc = strtolower(remove_accents($stage_focus));
    $stage_specific_context = [];
    $nutrition_profile = (array) ($onboarding['nutrition'] ?? []);
    $environment_profile = (array) ($onboarding['environment'] ?? []);
    $checklist_lines = [];
    foreach ((array) ($onboarding['checklists'] ?? []) as $item) {
        if (! is_array($item)) {
            continue;
        }
        $line = trim((string) ($item['item_text'] ?? ''));
        if ($line !== '') {
            $checklist_lines[] = $line;
        }
    }
    $health_lines = [];
    foreach ((array) ($onboarding['health_issues'] ?? []) as $issue) {
        if (! is_array($issue)) {
            continue;
        }
        $title = trim((string) ($issue['issue_name'] ?? $issue['symptom_title'] ?? ''));
        $detail = trim((string) ($issue['symptoms_text'] ?? $issue['symptom_detail'] ?? ''));
        $line = $title;
        if ($detail !== '') {
            $line .= ($line !== '' ? ': ' : '') . $detail;
        }
        if ($line !== '') {
            $health_lines[] = $line;
        }
    }
    $stage_specific_context[] = 'Giai đoạn cần ưu tiên đối chiếu: ' . ($stage_focus !== '' ? $stage_focus : 'chưa chắc chắn, cần suy ra từ ảnh + onboarding');
    $stage_specific_context[] = 'Danh sách giai đoạn chuẩn của cây: ' . wp_json_encode(array_map(static fn($row) => (string) ($row['name'] ?? ''), (array) ($onboarding['growth_stages'] ?? [])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stage_specific_context[] = 'Thông số mục tiêu nên ưu tiên khi viết recommendation: ' . wp_json_encode([
        'ec_target' => (string) ($nutrition_profile['ec_target'] ?? ''),
        'ec_range' => (string) ($nutrition_profile['ec_range'] ?? ''),
        'ph_target' => (string) ($nutrition_profile['ph_target'] ?? ''),
        'ph_range' => (string) ($nutrition_profile['ph_range'] ?? ''),
        'water_ml_per_tray_per_day' => (string) ($nutrition_profile['water_ml_per_tray_per_day'] ?? ''),
        'temperature_range' => (string) ($environment_profile['temperature_range'] ?? ''),
        'humidity_range' => (string) ($environment_profile['humidity_range'] ?? ''),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($stage_focus_lc !== '' && (str_contains($stage_focus_lc, 'qua') || str_contains($stage_focus_lc, 'fruit'))) {
        $stage_specific_context[] = 'Vì đây là pha nuôi quả/mang quả, recommendation phải ưu tiên: EC/pH đúng pha, mực nước hoặc lượng nước/khoang/ngày, nhiệt độ nước/dung dịch, Kali/Canxi, đỡ cành mang quả, thông thoáng tán, BER/nứt quả/rụng quả non.';
    }
    if ($checklist_lines !== []) {
        $stage_specific_context[] = 'Checklist đang có sẵn để bám khi viết recommendation: ' . wp_json_encode(array_slice($checklist_lines, 0, 8), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    if ($health_lines !== []) {
        $stage_specific_context[] = 'Vấn đề sức khỏe nổi bật cần ưu tiên nếu phù hợp với ảnh: ' . wp_json_encode(array_slice($health_lines, 0, 6), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $system_prompt = implode("\n", [
        'Anh là Cindy, trợ lý AI của aitrongcay.com.',
        'Nhiệm vụ hiện tại: phân tích chất lượng khoang cây dựa trên ảnh thật và hồ sơ onboarding của đúng loại cây đó.',
        'Ưu tiên tuyệt đối: không bịa dữ liệu, không suy luận vượt quá ảnh và hồ sơ được cung cấp.',
        'Nếu ảnh chưa đủ rõ thì nói rõ mức độ chắc chắn thấp hơn, nhưng vẫn đưa ra kết luận an toàn và hành động nhỏ tiếp theo.',
        'Phải đối chiếu nhận định ảnh với hồ sơ onboarding, SOP, môi trường mục tiêu, dinh dưỡng, checklist và các vấn đề thường gặp nếu được cung cấp.',
        'Trả lời bằng JSON thuần, không markdown, không giải thích ngoài JSON.',
        'Schema JSON bắt buộc: {"level":1-5,"color":"xam|xanh-non|vang|cam|do","label":"...","current_stage":"...","recommendation":"...","summary":"...","actions":["..."],"escalate_if":["..."],"knowledge_note":"..."}',
        'current_stage phải chọn hoặc quy chiếu về đúng một giai đoạn trong danh sách stage chuẩn của cây khi có thể.',
        'Recommendation là phần quan trọng nhất cho người vận hành, phải ngắn 1-3 câu, cụ thể, hành động được ngay, không nói câu xã giao, không nói kiểu "tiếp tục theo dõi" nếu chưa nêu rõ cần theo dõi cái gì và theo ngưỡng nào.',
        'Nếu onboarding có thông số cụ thể như EC, pH, lượng nước/khoang/ngày, nhiệt độ mục tiêu, hãy ưu tiên đưa các thông số đó vào recommendation thay vì nói chung chung.',
        'Recommendation phải bám sát giai đoạn sinh trưởng hiện tại và đúng việc cần làm ngay theo onboarding, checklist, dinh dưỡng và health issues của giai đoạn đó.',
        'Ví dụ tốt cho pha nuôi quả: "Giữ EC khoảng 2.0 và pH quanh 5.9, theo dõi lượng nước gần 900 ml/khoang/ngày và giữ nhiệt độ dung dịch trong vùng mục tiêu để cây không hụt sức nuôi quả. Quan sát BER, nứt quả hoặc rụng quả non trong 1-3 ngày tới."',
        'Dùng tiếng Việt, giọng dịu, nghiêm túc, rõ ràng, ngắn gọn.',
        'Nếu chưa đủ dấu hiệu để nâng cảnh báo mạnh thì ưu tiên kết luận an toàn, tránh làm người dùng hoảng.',
    ]);

    $user_prompt = implode("\n\n", array_filter([
        'Phân tích khoang cây sau và trả về JSON đúng schema.',
        'Thông tin khoang: ' . wp_json_encode([
            'pot_code' => $pot_code,
            'pot_name' => $pot_name,
            'plant_name' => (string) ($onboarding['plant_name'] ?? ''),
            'status' => $status,
            'status_summary' => $status_summary,
            'ai_note' => $ai_note,
            'analysis_mode' => $analysis_mode,
            'daily_photo_count' => $photo_count,
            'captured_at' => $captured_at,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'Hồ sơ onboarding đối chiếu:' . "\n" . (string) ($onboarding['context_text'] ?? ''),
        'Context stage-specific cần ưu tiên khi suy luận và viết recommendation:' . "\n" . implode("\n- ", array_merge([''], array_filter($stage_specific_context))),
        'Fallback nội bộ hiện tại để tham khảo chéo, không bắt buộc phải lặp lại nguyên văn:' . "\n" . wp_json_encode([
            'level' => (int) ($fallback_analysis['level'] ?? 0),
            'label' => (string) ($fallback_analysis['label'] ?? ''),
            'current_stage' => (string) ($fallback_analysis['current_stage'] ?? ''),
            'recommendation' => (string) ($fallback_analysis['recommendation'] ?? ''),
            'summary' => (string) ($fallback_analysis['summary'] ?? ''),
            'actions' => array_values((array) ($fallback_analysis['actions'] ?? [])),
            'escalate_if' => array_values((array) ($fallback_analysis['escalate_if'] ?? [])),
            'knowledge_note' => (string) ($fallback_analysis['knowledge_note'] ?? ''),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]));

    $content = [
        ['type' => 'text', 'text' => $user_prompt],
    ];

    $image_candidates = [];
    foreach ((array) ($photo_context['daily_photos'] ?? []) as $item) {
        if (! is_array($item)) {
            continue;
        }
        $attachment_id = absint($item['attachment_id'] ?? 0);
        $url = trim((string) ($item['download_url'] ?? $item['image_url'] ?? ''));
        $data_url = $attachment_id > 0 ? aitrongcay_build_data_image_url_from_attachment($attachment_id) : '';
        if ($data_url === '' && $url === '') {
            continue;
        }
        $image_candidates[] = [
            'url' => $data_url !== '' ? $data_url : $url,
            'source_type' => $data_url !== '' ? 'data' : 'url',
            'captured_at' => (string) ($item['captured_at'] ?? ''),
            'attachment_id' => $attachment_id,
        ];
    }
    if ($image_candidates === []) {
        $single_attachment_id = absint($photo_context['attachment_id'] ?? 0);
        $single_url = trim((string) ($photo_context['download_url'] ?? $photo_context['image_url'] ?? ''));
        $single_data_url = $single_attachment_id > 0 ? aitrongcay_build_data_image_url_from_attachment($single_attachment_id) : '';
        if ($single_data_url !== '' || $single_url !== '') {
            $image_candidates[] = [
                'url' => $single_data_url !== '' ? $single_data_url : $single_url,
                'source_type' => $single_data_url !== '' ? 'data' : 'url',
                'captured_at' => $captured_at,
                'attachment_id' => $single_attachment_id,
            ];
        }
    }
    foreach (array_slice($image_candidates, -3) as $image) {
        $content[] = [
            'type' => 'image_url',
            'image_url' => ['url' => (string) $image['url']],
        ];
    }

    $endpoint_url = (string) ($config['endpoint_url'] ?? '');
    $use_chat_completions = str_contains($endpoint_url, '/v1/chat/completions');

    $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept' => 'application/json',
        'x-openclaw-agent-id' => 'main',
    ];
    if ((string) ($config['bearer_token'] ?? '') !== '') {
        $headers['Authorization'] = 'Bearer ' . (string) $config['bearer_token'];
    }

    if ($use_chat_completions) {
        $body = [
            'model' => (string) ($config['model'] ?? 'openclaw'),
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system_prompt,
                ],
                [
                    'role' => 'user',
                    'content' => $content,
                ],
            ],
            'user' => 'aitrongcay:analysis:' . sanitize_key($pot_code !== '' ? $pot_code : $pot_name),
        ];
    } else {
        if (str_contains($endpoint_url, '/v1/chat/completions')) {
            $endpoint_url = str_replace('/v1/chat/completions', '/v1/responses', $endpoint_url);
        }

        $response_input = [[
            'type' => 'message',
            'role' => 'user',
            'content' => array_merge([
                ['type' => 'input_text', 'text' => $user_prompt],
            ], array_map(static function (array $image): array {
                return [
                    'type' => 'input_image',
                    'source' => [
                        'type' => 'url',
                        'url' => (string) ($image['url'] ?? ''),
                    ],
                ];
            }, array_slice($image_candidates, -3))),
        ]];

        $body = [
            'model' => (string) ($config['model'] ?? 'openclaw'),
            'response_format' => ['type' => 'json_object'],
            'instructions' => $system_prompt,
            'input' => $response_input,
            'user' => 'aitrongcay:analysis:' . sanitize_key($pot_code !== '' ? $pot_code : $pot_name),
        ];
    }

    aitrongcay_analysis_debug_log('cindy-request', [
        'pot_code' => $pot_code,
        'plant_name' => (string) ($onboarding['plant_name'] ?? ''),
        'analysis_mode' => $analysis_mode,
        'daily_photo_count' => $photo_count,
        'captured_at' => $captured_at,
        'endpoint_url' => $endpoint_url,
        'model' => (string) ($config['model'] ?? ''),
        'request_body' => [
            'model' => (string) ($body['model'] ?? ''),
            'response_format' => (array) ($body['response_format'] ?? []),
            'user' => (string) ($body['user'] ?? ''),
        ],
        'image_sources' => array_map(static function (array $image): array {
            $url = (string) ($image['url'] ?? '');
            return [
                'source_type' => (string) ($image['source_type'] ?? 'unknown'),
                'attachment_id' => (int) ($image['attachment_id'] ?? 0),
                'captured_at' => (string) ($image['captured_at'] ?? ''),
                'url_preview' => str_starts_with($url, 'data:') ? '[data-image-omitted]' : $url,
            ];
        }, array_slice($image_candidates, -3)),
    ]);

    $response = wp_remote_post($endpoint_url, [
        'timeout' => max(15, min(120, (int) ($config['timeout_seconds'] ?? 90))),
        'headers' => $headers,
        'body' => wp_json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    if (is_wp_error($response)) {
        aitrongcay_analysis_debug_log('cindy-response-error', [
            'pot_code' => $pot_code,
            'message' => $response->get_error_message(),
        ]);
        return ['ok' => false, 'message' => $response->get_error_message()];
    }

    $http_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body = (string) wp_remote_retrieve_body($response);
    $json = json_decode($raw_body, true);
    aitrongcay_analysis_debug_log('cindy-response-raw', [
        'pot_code' => $pot_code,
        'http_code' => $http_code,
        'raw_body' => strlen($raw_body) > 1500 ? (substr($raw_body, 0, 1500) . '…[truncated]') : $raw_body,
    ]);
    if ($http_code < 200 || $http_code >= 300 || ! is_array($json)) {
        return ['ok' => false, 'message' => 'Cindy trả về dữ liệu chưa hợp lệ.', 'http_code' => $http_code, 'raw_body' => $raw_body];
    }

    $reply_text = $use_chat_completions
        ? trim((string) ($json['choices'][0]['message']['content'] ?? ''))
        : aitrongcay_extract_openresponses_text($json);
    $decoded = aitrongcay_extract_json_object_from_text($reply_text);
    if ($decoded === []) {
        $decoded = aitrongcay_extract_structured_analysis_from_text($reply_text);
    }
    if ($decoded === []) {
        return ['ok' => false, 'message' => 'Cindy chưa trả JSON hợp lệ.', 'raw_reply' => $reply_text];
    }

    aitrongcay_analysis_debug_log('cindy-response-decoded', [
        'pot_code' => $pot_code,
        'reply_text' => $reply_text,
        'decoded' => $decoded,
    ]);

    $level = max(1, min(5, (int) ($decoded['level'] ?? ($fallback_analysis['level'] ?? 2))));
    $palette = aitrongcay_onboarding_analysis_palette();
    $color = trim((string) ($decoded['color'] ?? ''));
    if ($color === '' && isset($palette[$level]['color'])) {
        $color = (string) $palette[$level]['color'];
    }
    $label = trim((string) ($decoded['label'] ?? ''));
    if ($label === '' && isset($palette[$level]['label'])) {
        $label = (string) $palette[$level]['label'];
    }

    $summary = trim((string) ($decoded['summary'] ?? ''));
    if ($summary === '') {
        $summary = (string) ($fallback_analysis['summary'] ?? '');
    }
    if ($summary !== '' && $captured_at !== '' && ! preg_match('/ảnh|bộ ảnh/u', $summary)) {
        $summary .= ' Kết luận này dựa trên ' . ($photo_count > 1 ? ('bộ ' . $photo_count . ' ảnh cùng ngày') : 'ảnh mới nhất của khoang') . ', cập nhật lúc ' . mysql2date('H:i d/m/Y', $captured_at) . '.';
    }

    $analysis = [
        'level' => $level,
        'color' => $color,
        'label' => $label,
        'current_stage' => aitrongcay_normalize_onboarding_stage_label($pot, trim((string) ($decoded['current_stage'] ?? ($fallback_analysis['current_stage'] ?? 'Đang theo dõi')))),
        'recommendation' => aitrongcay_clean_analysis_recommendation_text(trim((string) ($decoded['recommendation'] ?? ($fallback_analysis['recommendation'] ?? '')))),
        'summary' => $summary,
        'actions' => array_values(array_filter(array_map('trim', (array) ($decoded['actions'] ?? ($fallback_analysis['actions'] ?? []))))),
        'escalate_if' => array_values(array_filter(array_map('trim', (array) ($decoded['escalate_if'] ?? ($fallback_analysis['escalate_if'] ?? []))))),
        'knowledge_note' => trim((string) ($decoded['knowledge_note'] ?? '')) ?: ('Cindy đã đối chiếu ảnh thật với hồ sơ onboarding của ' . (string) ($onboarding['plant_name'] ?? $pot_name) . '.'),
        'updated_at' => current_time('mysql'),
    ];

    if ($analysis['actions'] === []) {
        $analysis['actions'] = array_values((array) ($fallback_analysis['actions'] ?? []));
    }
    if ($analysis['recommendation'] === '') {
        $analysis['recommendation'] = aitrongcay_build_stage_specific_recommendation($pot, (string) ($analysis['current_stage'] ?? ''), $fallback_analysis);
    }

    $analysis['summary'] = trim((string) ($analysis['summary'] ?? ''));
    if ($analysis['actions'] !== []) {
        array_unshift($analysis['actions'], 'Đây là kết quả đã được đối chiếu với hồ sơ onboarding của cây, không còn chỉ là nhận xét nền cũ.');
        $analysis['actions'] = array_values(array_unique(array_filter(array_map('trim', $analysis['actions']))));
    }

    aitrongcay_analysis_debug_log('cindy-analysis-final', [
        'pot_code' => $pot_code,
        'analysis' => $analysis,
    ]);

    return ['ok' => true, 'analysis' => $analysis, 'raw_reply' => $reply_text];
}

function aitrongcay_generate_pot_analysis(array $pot, array $photo_context = []): array
{
    $pot_name = trim((string) ($pot['pot_name'] ?? $pot['name'] ?? $pot['code'] ?? 'Khoang cây'));
    $status = strtolower(trim((string) ($pot['status'] ?? '')));
    $summary = trim((string) ($pot['status_summary'] ?? ''));
    $ai_note = trim((string) ($pot['ai_note'] ?? ''));
    $pot_name_lc = strtolower($pot_name);

    $level = 2;
    $color = 'xanh-non';
    $label = 'Ổn nhưng cần theo dõi';
    $photo_count = max(1, (int) ($photo_context['daily_photo_count'] ?? 1));
    $short = $summary !== '' ? $summary : ('Hiện chưa có đủ dữ liệu sâu, nhưng ' . $pot_name . ' đang được theo dõi theo ' . ($photo_count > 1 ? ('bộ ' . $photo_count . ' ảnh cùng ngày') : 'ảnh mới nhất') . '.');
    $actions = [
        'Tiếp tục theo dõi ảnh mới nhất của chậu này mỗi ngày.',
        'Đối chiếu thêm lá ngọn, lá giữa và lá gốc nếu có thay đổi.',
    ];
    $escalate_if = [
        'Nếu dấu hiệu lan rộng hơn hoặc chạm tới phần lá non, nên nâng mức cảnh báo.',
    ];

    if ($status !== '') {
        if (str_contains($status, 'tốt') || str_contains($status, 'ổn')) {
            $level = 2;
            $color = 'xanh-non';
            $label = 'Ổn nhưng cần theo dõi';
        }
        if (str_contains($status, 'cần chú ý') || str_contains($status, 'theo dõi')) {
            $level = 3;
            $color = 'vàng';
            $label = 'Cần chú ý';
        }
        if (str_contains($status, 'cảnh báo') || str_contains($status, 'cao')) {
            $level = 4;
            $color = 'cam';
            $label = 'Cảnh báo cao';
        }
    }

    $signal_text = strtolower($summary . ' ' . $ai_note);
    if (preg_match('/vàng lá|la goc vang|héo|heo|rũ|ru|chậm|cham/', $signal_text)) {
        $level = max($level, 3);
        $color = $level >= 4 ? 'cam' : 'vàng';
        $label = $level >= 4 ? 'Cảnh báo cao' : 'Cần chú ý';
        $actions = [
            'Chụp thêm ảnh cận phần lá hoặc vùng đang có dấu hiệu bất thường.',
            'Theo dõi lại trong 24–48 giờ để xem dấu hiệu có lan rộng không.',
        ];
        $escalate_if = [
            'Nếu dấu hiệu lan từ gốc lên tầng giữa hoặc cây rũ hơn, nên nâng cảnh báo.',
        ];
    }

    if (str_contains($pot_name_lc, 'cà chua') || str_contains($pot_name_lc, 'ca chua') || str_contains($pot_name_lc, 'tomato')) {
        $fruiting = preg_match('/quả|qua|trái|trai|fruit/', $signal_text) === 1;
        $lower_leaf_yellowing = preg_match('/lá gốc|la goc|lá già|la gia|vàng lá|vang la/', $signal_text) === 1;
        $top_decline = preg_match('/lá non|la non|ngọn|ngon/', $signal_text) === 1 && preg_match('/vàng|vang|héo|heo|rũ|ru/', $signal_text) === 1;

        $level = min($level, 2) > 0 ? min($level, 2) : 2;
        $color = 'xanh-non';
        $label = 'Ổn nhưng cần theo dõi';
        $short = 'Chậu cà chua nên được đọc theo 3 tầng ngọn – giữa – gốc. Nếu phần trên vẫn ổn, còn dấu hiệu chỉ nằm ở lá già phía dưới, trước mắt nên theo dõi sát thay vì nâng cảnh báo quá mạnh.';
        $actions = [
            'Ưu tiên theo dõi riêng phần lá gốc và phần ngọn trong 1–3 ngày tới.',
            'Chụp thêm một ảnh cận phần lá dưới nếu muốn AI đối chiếu sát hơn.',
        ];
        $escalate_if = [
            'Nếu lá gốc vàng lan lên tầng giữa hoặc chạm lá non, nên nâng mức cảnh báo.',
            'Nếu cây rũ hơn hoặc quả lớn chậm rõ, nên kiểm tra sớm.',
        ];

        if ($fruiting) {
            $actions = [
                'Theo dõi tiếp nhịp nuôi quả và màu lá trong 1–3 ngày tới.',
                'Ưu tiên chụp thêm ảnh phần gốc nếu muốn theo dõi vàng lá chính xác hơn.',
            ];
            $escalate_if = [
                'Nếu lá non bắt đầu xuống màu hoặc quả phát triển chậm rõ, nên nâng cảnh báo.',
            ];
        }

        if ($fruiting && $lower_leaf_yellowing && ! $top_decline) {
            $level = 2;
            $color = 'xanh-non';
            $label = 'Ổn nhưng cần theo dõi';
            $short = 'Cà chua đang ở giai đoạn nuôi quả. Hiện có dấu hiệu lá già tầng dưới xuống màu, nhưng nếu phần ngọn vẫn ổn thì trước mắt nên theo dõi sát thay vì nâng cảnh báo quá cao.';
            if (! empty($photo_context['captured_at'])) {
                $short .= ' Kết luận này dựa trên ' . ($photo_count > 1 ? ('bộ ' . $photo_count . ' ảnh cùng ngày') : 'ảnh mới nhất') . ', cập nhật gần nhất lúc ' . mysql2date('H:i d/m/Y', (string) $photo_context['captured_at']) . '.';
            }
            $actions = [
                'Theo dõi xem vàng lá có lan từ gốc lên tầng giữa hay không.',
                'Quan sát tiếp tốc độ lớn của quả và màu của lá non.',
                'Chụp thêm ảnh cận phần lá gốc nếu muốn AI đánh giá sát hơn.',
            ];
            $escalate_if = [
                'Nếu lá non cũng bắt đầu vàng hoặc dấu hiệu lan nhanh trong 1–3 ngày, nên nâng cảnh báo.',
                'Nếu cây rũ hơn dù nhịp chăm không đổi, nên kiểm tra sớm.',
            ];
        } elseif ($top_decline) {
            $level = max($level, 3);
            $color = 'vàng';
            $label = 'Cần chú ý';
            $short = 'Cà chua đang có dấu hiệu cần chú ý hơn vì thay đổi đã chạm tới phần hoạt động mạnh của cây, không còn chỉ khu trú ở lá già phía dưới.';
            $actions = [
                'Theo dõi lại ảnh trong 24 giờ tới để xem dấu hiệu có lan nhanh không.',
                'Đối chiếu phần ngọn, lá non và tiến độ nuôi quả trước khi điều chỉnh mạnh.',
            ];
            $escalate_if = [
                'Nếu phần ngọn tiếp tục xuống màu hoặc quả có dấu hiệu chậm rõ, nên nâng cảnh báo tiếp.',
            ];
        }
    } elseif (str_contains($pot_name_lc, 'cải cúc') || str_contains($pot_name_lc, 'cai cuc') || str_contains($pot_name_lc, 'tần ô') || str_contains($pot_name_lc, 'tan o')) {
        $early_stage = preg_match('/cây con|cay con|lá mầm|la mam|lá thật|la that|nảy mầm|nay mam/', $signal_text) === 1;
        $uneven = preg_match('/không đều|khong deu|chưa đều|chua deu|mật độ|mat do|dày|day|thưa|thua/', $signal_text) === 1;
        $severe_decline = preg_match('/lan rộng|lan rong|yếu rõ|yeu ro|đi xuống|di xuong|chậm kéo dài|cham keo dai/', $signal_text) === 1;

        $level = 2;
        $color = 'xanh-non';
        $label = 'Ổn nhưng cần theo dõi';
        $short = 'Cải cúc nên được đọc theo toàn khoang trước, rồi mới nhìn từng cốc. Ở giai đoạn cây con sớm, điều quan trọng nhất là độ đồng đều, ẩm độ nền và việc các cụm mọc dày có chen nhau quá sớm hay không.';
        $actions = [
            'Theo dõi độ đồng đều giữa các cốc trong 2–5 ngày tới.',
            'Chụp lại cùng góc để AI so diễn biến thay vì chỉ nhìn một thời điểm.',
        ];
        $escalate_if = [
            'Nếu nhiều vị trí cùng chậm kéo dài hoặc một vùng khoang đi xuống rõ, nên nâng cảnh báo.',
        ];

        if ($early_stage && $uneven && ! $severe_decline) {
            $short = 'Cải cúc đang ở giai đoạn cây con sớm và nhìn chung vẫn đi lên được. Điểm cần theo dõi chính lúc này là độ đồng đều giữa các cốc, ẩm độ nền và khả năng cần tỉa bớt ở những vị trí mọc dày hơn.';
            $actions = [
                'Giữ điều kiện ổn định vài ngày liên tiếp trước khi điều chỉnh mạnh.',
                'Quan sát xem các vị trí mọc dày có bắt đầu chen nhau quá sớm không.',
                'Nếu cần, chụp cận thêm 1–2 cụm cây con để AI đối chiếu sát hơn.',
            ];
            $escalate_if = [
                'Nếu chênh lệch giữa các cốc lớn dần hoặc cây con yếu rõ trên diện rộng, nên nâng cảnh báo.',
            ];
        }

        if ($severe_decline) {
            $level = 3;
            $color = 'vàng';
            $label = 'Cần chú ý';
            $short = 'Khoang cải cúc đang có dấu hiệu cần chú ý hơn vì sự không đồng đều hoặc chậm phát triển không còn chỉ là lệch nhẹ ban đầu.';
            $actions = [
                'Chụp lại cùng góc trong 24–48 giờ tới để so rõ diễn biến.',
                'Đối chiếu những vùng đi chậm với phần còn lại của khoang trước khi điều chỉnh.',
            ];
            $escalate_if = [
                'Nếu nhiều vị trí tiếp tục đi xuống hoặc thân cây con yếu rõ hơn, nên nâng cảnh báo tiếp.',
            ];
        }
    } elseif (str_contains($pot_name_lc, 'cải xoong') || str_contains($pot_name_lc, 'cai xoong') || str_contains($pot_name_lc, 'watercress')) {
        $just_seeded = preg_match('/mới gieo|moi gieo|vừa gieo|vua gieo|chưa thấy mầm|chua thay mam/', $signal_text) === 1;
        $germinating = preg_match('/nảy mầm|nay mam|mầm|mam|nhú|nhu/', $signal_text) === 1;
        $delayed = preg_match('/chậm kéo dài|cham keo dai|không tiến triển|khong tien trien|khác hẳn|khac han/', $signal_text) === 1;

        $level = 2;
        $color = 'xanh-non';
        $label = 'Ổn nhưng cần theo dõi';
        $short = 'Cải xoong nên được đọc theo mốc thời gian. Ở giai đoạn mới gieo hoặc mới nhú mầm, điều quan trọng nhất là độ ổn định của nền và việc khoang có bắt đầu tiến triển đều qua các mốc tiếp theo hay không.';
        $actions = [
            'Theo dõi tiếp theo mốc 1–3 ngày thay vì kết luận sớm từ một ảnh.',
            'Giữ điều kiện nền ổn định trước khi mong thấy thay đổi rõ trên bề mặt.',
        ];
        $escalate_if = [
            'Nếu quá mốc theo dõi mà nhiều vùng vẫn không có tiến triển, nên nâng cảnh báo.',
        ];

        if ($just_seeded && ! $germinating && ! $delayed) {
            $short = 'Theo ảnh hiện tại, cải xoong vừa gieo hạt và chưa thấy mầm rõ trên bề mặt. Đây vẫn là giai đoạn còn sớm, nên việc ưu tiên lúc này là giữ nền ổn định và theo dõi thêm các mốc nảy mầm tiếp theo.';
            $actions = [
                'Chưa nên kết luận mạnh tay khi còn quá sớm.',
                'Theo dõi tiếp mốc nảy mầm ở những ngày kế tiếp.',
                'Giữ điều kiện nền ổn định trước khi mong thấy thay đổi rõ trên bề mặt.',
            ];
            $escalate_if = [
                'Nếu qua thêm nhiều mốc mà khoang vẫn không có tiến triển rõ, nên nâng cảnh báo.',
            ];
        }

        if ($delayed) {
            $level = 3;
            $color = 'vàng';
            $label = 'Cần chú ý';
            $short = 'Khoang cải xoong đang có dấu hiệu cần chú ý hơn vì tiến triển không còn đi đúng nhịp mong đợi ở giai đoạn đầu.';
            $actions = [
                'So lại ảnh cùng góc qua 24–72 giờ để kiểm tra tiến triển thực sự.',
                'Ưu tiên nhìn toàn khoang để xem sự chậm có lan trên diện rộng hay chỉ cục bộ.',
            ];
            $escalate_if = [
                'Nếu thêm nhiều vùng chậm kéo dài hoặc nền tiếp tục bất ổn, nên nâng cảnh báo tiếp.',
            ];
        }
    } elseif (str_contains($pot_name_lc, 'sâm ngọc linh') || str_contains($pot_name_lc, 'sam ngoc linh')) {
        $very_early = preg_match('/cây con rất sớm|cay con rat som|cây non|cay non|mới nhú|moi nhu|nhú|nhu/', $signal_text) === 1;
        $uneven = preg_match('/không đều|khong deu|chưa đồng đều|chua dong deu|đồng đều|dong deu/', $signal_text) === 1;
        $decline = preg_match('/đi xuống|di xuong|yếu rõ|yeu ro|chậm kéo dài|cham keo dai|lan rộng|lan rong/', $signal_text) === 1;

        $level = 2;
        $color = 'xanh-non';
        $label = 'Ổn nhưng cần theo dõi';
        $short = 'Sâm ngọc linh ở giai đoạn này nên được đọc theo cả cụm trước, thay vì nhìn một cây riêng lẻ. Điều quan trọng nhất là độ ổn định môi trường, mức độ đồng đều giữa các cốc và diễn biến của cụm cây trong vài ngày liên tiếp.';
        $actions = [
            'Theo dõi cụm cây bằng ảnh cùng góc trong 2–5 ngày tới.',
            'Ưu tiên giữ điều kiện ổn định thay vì can thiệp mạnh tay khi cây còn rất sớm.',
        ];
        $escalate_if = [
            'Nếu nhiều cốc cùng đi xuống rõ hoặc chênh lệch kéo dài hơn qua nhiều mốc, nên nâng cảnh báo.',
        ];

        if ($very_early && $uneven && ! $decline) {
            $short = 'Theo ảnh hiện tại, sâm ngọc linh đang ở giai đoạn cây con rất sớm. Nhiều cốc đã có cây non, nhưng mức phát triển chưa đồng đều hoàn toàn. Việc ưu tiên lúc này là giữ môi trường ổn định và theo dõi thêm diễn biến trong các mốc tiếp theo.';
            $actions = [
                'Giữ điều kiện ổn định và chụp lại cùng góc trong vài ngày tới.',
                'Quan sát xem độ đồng đều giữa các cốc có cải thiện dần không.',
                'Chưa nên kết luận mạnh tay nếu phần lớn cây vẫn đang giữ được nhịp đi lên.',
            ];
            $escalate_if = [
                'Nếu nhiều cốc chậm kéo dài hoặc xuất hiện vùng đi xuống rõ hơn phần còn lại, nên nâng cảnh báo.',
            ];
        }

        if ($decline) {
            $level = 3;
            $color = 'vàng';
            $label = 'Cần chú ý';
            $short = 'Cụm sâm ngọc linh đang có dấu hiệu cần chú ý hơn vì sự chênh lệch hoặc chậm phát triển không còn chỉ là lệch nhẹ của giai đoạn đầu.';
            $actions = [
                'So lại ảnh cùng góc qua 2–5 ngày để kiểm tra diễn biến thực sự.',
                'Ưu tiên nhìn toàn cụm để xem dấu hiệu có lan rộng hay chỉ cục bộ.',
            ];
            $escalate_if = [
                'Nếu thêm nhiều cốc tiếp tục đi xuống hoặc độ đồng đều giảm rõ hơn, nên nâng cảnh báo tiếp.',
            ];
        }
    } elseif (str_contains($pot_name_lc, 'ớt') || str_contains($pot_name_lc, 'ot') || str_contains($pot_name_lc, 'pepper') || str_contains($pot_name_lc, 'chili') || str_contains($pot_name_lc, 'chilli')) {
        $fruiting = preg_match('/quả|qua|trái|trai|fruit|hoa|flower/', $signal_text) === 1;
        $lower_leaf_signal = preg_match('/lá gốc|la goc|lá già|la gia|vàng lá|vang la/', $signal_text) === 1;
        $top_decline = preg_match('/lá non|la non|ngọn|ngon/', $signal_text) === 1 && preg_match('/vàng|vang|héo|heo|rũ|ru/', $signal_text) === 1;

        $level = 2;
        $color = 'xanh-non';
        $label = 'Ổn nhưng cần theo dõi';
        $short = 'Ớt nên được đọc theo 3 tầng ngọn – giữa – gốc, và nếu đã có hoa hoặc trái thì cần đối chiếu thêm nhịp nuôi trái. Điều quan trọng là nhìn cả tán lá lẫn trạng thái mang trái, không chỉ nhìn một lá đơn lẻ.';
        $actions = [
            'Chụp thêm ảnh đủ ngọn, giữa tán và lá gốc nếu muốn AI đọc sát hơn.',
            'Nếu cây đã có hoa hoặc trái, ưu tiên chụp thêm phần mang trái để đối chiếu cùng tán lá.',
        ];
        $escalate_if = [
            'Nếu dấu hiệu lan lên lá non, phần ngọn hoặc ảnh hưởng rõ tới nhịp nuôi trái, nên nâng cảnh báo.',
        ];

        if ($fruiting && $lower_leaf_signal && ! $top_decline) {
            $short = 'Ớt đang ở giai đoạn ra hoa hoặc nuôi trái. Nếu thay đổi chủ yếu còn nằm ở lá già phía dưới trong khi phần ngọn vẫn ổn, trước mắt nên theo dõi sát thay vì nâng cảnh báo quá mạnh.';
            $actions = [
                'Theo dõi tiếp lá gốc và tình trạng hoa / trái trong 1–3 ngày tới.',
                'Chụp thêm ảnh ngọn và cụm trái nếu muốn AI đối chiếu sát hơn.',
            ];
            $escalate_if = [
                'Nếu phần ngọn bắt đầu xuống màu hoặc trái phát triển chậm rõ, nên nâng cảnh báo.',
            ];
        }

        if ($top_decline) {
            $level = 3;
            $color = 'vàng';
            $label = 'Cần chú ý';
            $short = 'Cây ớt đang có dấu hiệu cần chú ý hơn vì thay đổi đã chạm tới phần hoạt động mạnh của cây, không còn chỉ khu trú ở lá già phía dưới.';
            $actions = [
                'Theo dõi lại ảnh trong 24–48 giờ tới để xem dấu hiệu có lan nhanh không.',
                'Đối chiếu đồng thời ngọn, giữa tán và phần hoa / trái trước khi điều chỉnh mạnh.',
            ];
            $escalate_if = [
                'Nếu ngọn tiếp tục xuống màu hoặc nhịp mang trái yếu đi rõ, nên nâng cảnh báo tiếp.',
            ];
        }
    }

    if (! empty($photo_context['captured_at'])) {
        if (! str_contains($short, 'Ảnh') && ! str_contains($short, 'ảnh') && ! str_contains($short, 'bộ')) {
            $short .= ' Kết luận này dựa trên ' . ($photo_count > 1 ? ('bộ ' . $photo_count . ' ảnh của chậu trong ngày') : 'ảnh mới nhất của chậu') . ', cập nhật lúc ' . mysql2date('H:i d/m/Y', (string) $photo_context['captured_at']) . '.';
        }
    }

    $current_stage = 'Đang chờ xác định rõ giai đoạn';
    $knowledge_note = '';
    if (str_contains($pot_name_lc, 'cà chua') || str_contains($pot_name_lc, 'ca chua') || str_contains($pot_name_lc, 'tomato')) {
        $current_stage = preg_match('/quả|qua|trái|trai|fruit/', $signal_text) === 1 ? 'Nuôi quả' : 'Sinh trưởng thân lá';
        $knowledge_note = 'Căn cứ nội bộ hiện dùng cho cà chua: đọc theo 3 tầng ngọn – giữa – gốc, ưu tiên phân biệt lá già tầng dưới với phần ngọn đang hoạt động mạnh, và luôn đối chiếu thêm trạng thái nuôi quả nếu có.';
    } elseif (str_contains($pot_name_lc, 'cải cúc') || str_contains($pot_name_lc, 'cai cuc') || str_contains($pot_name_lc, 'tần ô') || str_contains($pot_name_lc, 'tan o')) {
        $current_stage = preg_match('/lá thật|la that/', $signal_text) === 1 ? 'Cây con đã có lá thật' : 'Cây con sớm';
        $knowledge_note = 'Căn cứ nội bộ hiện dùng cho cải cúc: nhìn toàn khoang trước, sau đó mới soi từng cốc; ưu tiên độ đồng đều, mật độ dày/thưa, giai đoạn cây con sớm và diễn biến theo mốc 2–5 ngày.';
    } elseif (str_contains($pot_name_lc, 'cải xoong') || str_contains($pot_name_lc, 'cai xoong') || str_contains($pot_name_lc, 'watercress')) {
        $current_stage = preg_match('/nảy mầm|nay mam|mầm|mam|nhú|nhu/', $signal_text) === 1 ? 'Bắt đầu nảy mầm' : 'Mới gieo';
        $knowledge_note = 'Căn cứ nội bộ hiện dùng cho cải xoong: phân biệt rõ giai đoạn mới gieo với chậm bất thường kéo dài; ưu tiên theo dõi mốc 1–3 ngày, độ ổn định của nền và tiến triển của cả khoang.';
    } elseif (str_contains($pot_name_lc, 'sâm ngọc linh') || str_contains($pot_name_lc, 'sam ngoc linh')) {
        $current_stage = preg_match('/cây con ổn định|cay con on dinh/', $signal_text) === 1 ? 'Cây con ổn định hơn' : 'Cây con rất sớm';
        $knowledge_note = 'Căn cứ nội bộ hiện dùng cho sâm ngọc linh: nhìn theo cả cụm cây con, ưu tiên độ ổn định môi trường, độ đồng đều giữa các cốc và diễn biến theo mốc 2–5 ngày.';
    } elseif (str_contains($pot_name_lc, 'ớt') || str_contains($pot_name_lc, 'ot') || str_contains($pot_name_lc, 'pepper') || str_contains($pot_name_lc, 'chili') || str_contains($pot_name_lc, 'chilli')) {
        $current_stage = preg_match('/quả|qua|trái|trai|fruit|hoa|flower/', $signal_text) === 1 ? 'Ra hoa / nuôi trái' : 'Sinh trưởng thân lá';
        $knowledge_note = 'Căn cứ nội bộ hiện dùng cho cây ớt: đọc theo 3 tầng ngọn – giữa – gốc, và nếu đã có hoa hoặc trái thì luôn đối chiếu thêm trạng thái mang trái.';
    }

    $fallback_analysis = [
        'level' => $level,
        'color' => $color,
        'label' => $label,
        'current_stage' => aitrongcay_normalize_onboarding_stage_label($pot, $current_stage),
        'recommendation' => '',
        'summary' => $short,
        'actions' => $actions,
        'escalate_if' => $escalate_if,
        'knowledge_note' => $knowledge_note,
        'updated_at' => current_time('mysql'),
    ];

    $fallback_analysis['recommendation'] = aitrongcay_build_stage_specific_recommendation($pot, (string) ($fallback_analysis['current_stage'] ?? ''), $fallback_analysis);

    $cindy_analysis = aitrongcay_generate_cindy_onboarding_analysis($pot, $photo_context, $fallback_analysis);
    if (! empty($cindy_analysis['ok']) && is_array($cindy_analysis['analysis'] ?? null)) {
        return $cindy_analysis['analysis'];
    }

    $onboarding = aitrongcay_build_onboarding_analysis_context($pot);
    if (empty($onboarding['has_onboarding'])) {
        return [
            'level' => 1,
            'color' => 'none',
            'label' => 'Chưa có hồ sơ onboarding',
            'current_stage' => 'Chưa có hồ sơ đối chiếu',
            'summary' => (string) ($onboarding['message'] ?? 'Cây chưa có hồ sơ onboarding nên Cindy chưa phân tích sâu cho khoang này.'),
            'actions' => ['Hãy tạo hoặc hoàn thiện hồ sơ onboarding cho cây này rồi phân tích lại.'],
            'escalate_if' => [],
            'knowledge_note' => 'Phân tích sâu đang bị bỏ qua vì chưa có hồ sơ onboarding của cây.',
            'updated_at' => current_time('mysql'),
        ];
    }

    $fallback_analysis['summary'] = trim('Đã nhận ảnh mới của khoang và đã tìm thấy hồ sơ onboarding của ' . (string) ($onboarding['plant_name'] ?? $pot_name) . '. Tạm thời hệ thống đang dùng lớp phân tích nền an toàn: ' . (string) ($fallback_analysis['summary'] ?? ''));
    $fallback_actions = array_values((array) ($fallback_analysis['actions'] ?? []));
    array_unshift($fallback_actions, 'Hệ thống đã đối chiếu được cây với hồ sơ onboarding, nhưng bản trả lời sâu của Cindy chưa thay thế hoàn toàn lớp phân tích nền trong lượt này.');
    $fallback_analysis['actions'] = array_values(array_unique(array_filter(array_map('trim', $fallback_actions))));
    $fallback_analysis['knowledge_note'] = 'Khoang này đã match được hồ sơ onboarding. Nếu kết quả hiển thị vẫn thiên về lớp nền, cần tiếp tục hoàn thiện nhánh Cindy image-analysis thay vì để nó rơi về text cũ.';

    return $fallback_analysis;
}

function aitrongcay_store_pot_analysis(string $garden_key, string $pot_code, array $analysis): bool
{
    global $wpdb;
    $table = aitrongcay_garden_pots_table();
    return false !== $wpdb->update(
        $table,
        [
            'latest_analysis_level' => (int) ($analysis['level'] ?? 0),
            'latest_analysis_color' => (string) ($analysis['color'] ?? ''),
            'latest_analysis_label' => (string) ($analysis['label'] ?? ''),
            'latest_analysis_current_stage' => (string) ($analysis['current_stage'] ?? ''),
            'latest_analysis_recommendation' => (string) ($analysis['recommendation'] ?? ''),
            'latest_analysis_summary' => (string) ($analysis['summary'] ?? ''),
            'latest_analysis_actions' => wp_json_encode(array_values((array) ($analysis['actions'] ?? [])), JSON_UNESCAPED_UNICODE),
            'latest_analysis_escalate' => wp_json_encode(array_values((array) ($analysis['escalate_if'] ?? [])), JSON_UNESCAPED_UNICODE),
            'latest_analysis_updated_at' => (string) ($analysis['updated_at'] ?? current_time('mysql')),
            'updated_at' => current_time('mysql'),
        ],
        ['garden_key' => $garden_key, 'pot_code' => $pot_code],
        ['%d','%s','%s','%s','%s','%s','%s','%s','%s','%s'],
        ['%s','%s']
    );
}

function aitrongcay_replace_garden_pots(string $garden_key, array $pots): bool
{
    global $wpdb;
    if ($garden_key === '') {
        return false;
    }
    $table = aitrongcay_garden_pots_table();
    $wpdb->delete($table, ['garden_key' => $garden_key], ['%s']);
    foreach (array_values($pots) as $index => $pot) {
        if (! is_array($pot)) {
            continue;
        }
        $pot_code = trim((string) ($pot['pot_code'] ?? $pot['code'] ?? ''));
        $pot_name = trim((string) ($pot['pot_name'] ?? $pot['name'] ?? ''));
        if ($pot_code === '' || $pot_name === '') {
            continue;
        }
        $pot['sort_order'] = $index + 1;
        aitrongcay_upsert_db_pot($garden_key, $pot);
    }
    return true;
}

function aitrongcay_get_db_tools(string $garden_key): array
{
    global $wpdb;
    static $cache = [];
    if ($garden_key === '') {
        return [];
    }
    if (isset($cache[$garden_key])) {
        return $cache[$garden_key];
    }
    $table = aitrongcay_garden_tools_table();
    $cache[$garden_key] = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s ORDER BY sort_order ASC, id ASC", $garden_key), ARRAY_A) ?: [];
    return $cache[$garden_key];
}

function aitrongcay_replace_garden_tools(string $garden_key, array $tools): bool
{
    global $wpdb;
    if ($garden_key === '') {
        return false;
    }
    $table = aitrongcay_garden_tools_table();
    $wpdb->delete($table, ['garden_key' => $garden_key], ['%s']);
    $now = current_time('mysql');
    foreach (array_values($tools) as $index => $tool) {
        if (! is_array($tool)) {
            continue;
        }
        $name = trim((string) ($tool['name'] ?? ''));
        if ($name === '') {
            continue;
        }
        $tool_key = sanitize_key((string) ($tool['tool_key'] ?? $name ?: ('tool_' . ($index + 1))));
        $wpdb->insert($table, [
            'garden_key' => $garden_key,
            'tool_key' => $tool_key !== '' ? $tool_key : ('tool_' . ($index + 1)),
            'name' => $name,
            'type' => trim((string) ($tool['type'] ?? '')),
            'description' => trim((string) ($tool['description'] ?? '')),
            'owned' => (int) ($tool['owned'] ?? 0),
            'qty' => (int) ($tool['qty'] ?? 0),
            'image' => trim((string) ($tool['image'] ?? '')),
            'sort_order' => $index + 1,
            'created_at' => $now,
            'updated_at' => $now,
        ], ['%s','%s','%s','%s','%s','%d','%d','%s','%d','%s','%s']);
    }
    return true;
}

function aitrongcay_get_rack_record(string $garden_key): ?array
{
    global $wpdb;
    static $cache = [];

    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return null;
    }
    if (array_key_exists($garden_key, $cache)) {
        return $cache[$garden_key];
    }

    $table = aitrongcay_garden_racks_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE garden_key = %s LIMIT 1", $garden_key), ARRAY_A);
    $cache[$garden_key] = is_array($row) ? $row : null;
    return $cache[$garden_key];
}

function aitrongcay_get_rack_slots(string $garden_key): array
{
    global $wpdb;
    static $cache = [];

    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return [];
    }
    if (isset($cache[$garden_key])) {
        return $cache[$garden_key];
    }

    $racks_table = aitrongcay_garden_racks_table();
    $racks = $wpdb->get_col($wpdb->prepare("SELECT id FROM {$racks_table} WHERE garden_key = %s", $garden_key));
    
    if (empty($racks)) {
        $cache[$garden_key] = [];
        return [];
    }
    
    $rack_ids_placeholder = implode(',', array_map('intval', $racks));
    $table = aitrongcay_garden_rack_slots_table();
    $cache[$garden_key] = $wpdb->get_results("SELECT * FROM {$table} WHERE rack_id IN ({$rack_ids_placeholder}) ORDER BY rack_id ASC, slot_index ASC, id ASC", ARRAY_A) ?: [];
    return $cache[$garden_key];
}

function aitrongcay_upsert_rack_record(string $garden_key, array $payload = []): bool
{
    global $wpdb;
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return false;
    }

    $table = aitrongcay_garden_racks_table();
    $existing = null;
    $rack_id = isset($payload['id']) ? (int) $payload['id'] : 0;
    if ($rack_id > 0) {
        $existing = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id($rack_id) : null;
    }
    if (! $existing) {
        $rack_code = trim((string) ($payload['rack_code'] ?? ''));
        if ($rack_code !== '') {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE rack_code = %s LIMIT 1", $rack_code), ARRAY_A);
        }
    }
    if (! $existing) {
        $existing = aitrongcay_get_rack_record($garden_key);
    }
    $now = current_time('mysql');
    $data = [
        'rack_code' => trim((string) ($payload['rack_code'] ?? ($existing['rack_code'] ?? strtoupper($garden_key)))),
        'rack_name' => trim((string) ($payload['rack_name'] ?? ($existing['rack_name'] ?? ''))),
        'garden_key' => $garden_key,
        'owner_user_id' => isset($payload['owner_user_id']) ? (int) $payload['owner_user_id'] : (isset($existing['owner_user_id']) ? (int) $existing['owner_user_id'] : null),
        'status' => trim((string) ($payload['status'] ?? ($existing['status'] ?? 'draft'))),
        'slot_count' => max(0, (int) ($payload['slot_count'] ?? ($existing['slot_count'] ?? 0))),
        'controller_type' => trim((string) ($payload['controller_type'] ?? ($existing['controller_type'] ?? 'blynk'))),
        'controller_label' => trim((string) ($payload['controller_label'] ?? ($existing['controller_label'] ?? ''))),
        'blynk_template_id' => trim((string) ($payload['blynk_template_id'] ?? ($existing['blynk_template_id'] ?? ''))),
        'blynk_template_name' => trim((string) ($payload['blynk_template_name'] ?? ($existing['blynk_template_name'] ?? ''))),
        'blynk_auth_token' => array_key_exists('blynk_auth_token', $payload) ? (string) $payload['blynk_auth_token'] : (string) ($existing['blynk_auth_token'] ?? ''),
        'blynk_email' => trim((string) ($payload['blynk_email'] ?? ($existing['blynk_email'] ?? ''))),
        'connectivity_status' => trim((string) ($payload['connectivity_status'] ?? ($existing['connectivity_status'] ?? 'unknown'))),
        'last_seen_at' => ! empty($payload['last_seen_at']) ? (string) $payload['last_seen_at'] : ($existing['last_seen_at'] ?? null),
        'notes' => array_key_exists('notes', $payload) ? (string) $payload['notes'] : (string) ($existing['notes'] ?? ''),
        'updated_at' => $now,
    ];

    $formats = [
        '%s', // rack_code
        '%s', // rack_name
        '%s', // garden_key
        '%d', // owner_user_id
        '%s', // status
        '%d', // slot_count
        '%s', // controller_type
        '%s', // controller_label
        '%s', // blynk_template_id
        '%s', // blynk_template_name
        '%s', // blynk_auth_token
        '%s', // blynk_email
        '%s', // connectivity_status
        '%s', // last_seen_at
        '%s', // notes
        '%s', // updated_at
    ];
    if ($existing) {
        return false !== $wpdb->update($table, $data, ['id' => (int) $existing['id']], $formats, ['%d']);
    }

    $insert_result = $wpdb->insert($table, array_merge($data, ['created_at' => $now]), array_merge($formats, ['%s']));
    if ($insert_result === false) {
        error_log('Insert rack error: ' . $wpdb->last_error);
    }
    return false !== $insert_result;
}

function aitrongcay_replace_rack_slots(string $garden_key, array $slots): bool
{
    global $wpdb;
    $rack = aitrongcay_get_rack_record($garden_key);
    $rack_id = (int) ($rack['id'] ?? 0);
    if ($rack_id <= 0) {
        return false;
    }

    $table = aitrongcay_garden_rack_slots_table();
    $wpdb->delete($table, ['rack_id' => $rack_id], ['%d']);
    $now = current_time('mysql');

    foreach (array_values($slots) as $index => $slot) {
        if (! is_array($slot)) {
            continue;
        }
        $slot_index = (int) ($slot['slot_index'] ?? ($index + 1));
        if ($slot_index <= 0) {
            continue;
        }
        $wpdb->insert($table, [
            'rack_id' => $rack_id,
            'slot_index' => $slot_index,
            'slot_code' => (string) ($slot['slot_code'] ?? sprintf('%s-S%02d', (string) ($rack['rack_code'] ?? strtoupper($garden_key)), $slot_index)),
            'slot_name' => (string) ($slot['slot_name'] ?? ('Khoang ' . $slot_index)),
            'pot_code' => (string) ($slot['pot_code'] ?? sprintf('P-%03d', $slot_index)),
            'camera_label' => (string) ($slot['camera_label'] ?? ''),
            'camera_stream_url' => (string) ($slot['camera_stream_url'] ?? ''),
            'control_channel' => (string) ($slot['control_channel'] ?? ('light' . $slot_index)),
            'control_vpin' => (string) ($slot['control_vpin'] ?? ''),
            'is_enabled' => ! empty($slot['is_enabled']) ? 1 : 0,
            'crop_id' => isset($slot['crop_id']) ? (int) $slot['crop_id'] : null,
            'crop_cycle_id' => isset($slot['crop_cycle_id']) ? (int) $slot['crop_cycle_id'] : null,
            'status' => (string) ($slot['status'] ?? 'empty'),
            'created_at' => $now,
            'updated_at' => $now,
        ], ['%d','%d','%s','%s','%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s']);
    }

    return true;
}

function aitrongcay_sync_rack_from_blynk_config(string $garden_key, array $config = [], array $meta = []): bool
{
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return false;
    }

    if ($config === [] && function_exists('aitrongcay_blynk_config')) {
        $config = aitrongcay_blynk_config($garden_key);
    }
    if ($config === []) {
        return false;
    }

    $light_devices = function_exists('aitrongcay_blynk_active_light_devices')
        ? aitrongcay_blynk_active_light_devices($config)
        : ['light1', 'light2', 'light3', 'light4'];
    $slot_count = count($light_devices);
    if ($slot_count < 2) {
        return false;
    }

    $rack_code = trim((string) ($meta['rack_code'] ?? strtoupper($garden_key)));
    $rack_name = trim((string) ($meta['rack_name'] ?? ('Giá ' . $rack_code)));

    $saved = aitrongcay_upsert_rack_record($garden_key, [
        'rack_code' => $rack_code,
        'rack_name' => $rack_name,
        'owner_user_id' => (int) ($meta['owner_user_id'] ?? 0),
        'status' => (string) ($meta['status'] ?? 'inventory'),
        'slot_count' => $slot_count,
        'controller_type' => (string) ($meta['controller_type'] ?? 'blynk'),
        'controller_label' => (string) ($meta['controller_label'] ?? trim((string) ($config['token'] ?? ''))),
        'blynk_template_id' => (string) ($meta['blynk_template_id'] ?? ''),
        'blynk_template_name' => (string) ($meta['blynk_template_name'] ?? ''),
        'blynk_auth_token' => (string) ($meta['blynk_auth_token'] ?? ($config['token'] ?? '')),
        'blynk_email' => (string) ($meta['blynk_email'] ?? ''),
        'connectivity_status' => (string) ($meta['connectivity_status'] ?? 'unknown'),
        'last_seen_at' => $meta['last_seen_at'] ?? null,
        'notes' => (string) ($meta['notes'] ?? ''),
    ]);
    if (! $saved) {
        return false;
    }

    $slots = [];
    foreach (array_values($light_devices) as $index => $device) {
        $slot_index = $index + 1;
        $slots[] = [
            'slot_index' => $slot_index,
            'slot_code' => sprintf('%s-S%02d', $rack_code, $slot_index),
            'slot_name' => 'Khoang ' . $slot_index,
            'pot_code' => sprintf('P-%03d', $slot_index),
            'control_channel' => $device,
            'control_vpin' => (string) (($config['vpins'][$device] ?? '')),
            'is_enabled' => 1,
            'status' => 'empty',
        ];
    }

    return aitrongcay_replace_rack_slots($garden_key, $slots);
}

function aitrongcay_rack_total_slots(string $garden_key): int
{
    $rack = aitrongcay_get_rack_record($garden_key);
    $slot_count = (int) ($rack['slot_count'] ?? 0);
    if ($slot_count > 0) {
        return $slot_count;
    }
    return count(aitrongcay_get_rack_slots($garden_key));
}

function aitrongcay_garden_has_rack(string $garden_key): bool
{
    return aitrongcay_rack_total_slots($garden_key) >= 2;
}

function aitrongcay_build_inventory_rack_key(string $rack_code): string
{
    $rack_code = strtoupper(sanitize_key(str_replace('-', '_', $rack_code)));
    return 'inventory:' . ($rack_code !== '' ? $rack_code : wp_generate_password(8, false, false));
}

function aitrongcay_next_inventory_rack_number(): int
{
    global $wpdb;
    $table = aitrongcay_garden_racks_table();
    $codes = $wpdb->get_col("SELECT rack_code FROM {$table}") ?: [];
    $max = 0;
    foreach ($codes as $code) {
        $code = strtoupper(trim((string) $code));
        if (preg_match('/^RACK[_-]?(\d+)$/', $code, $matches)) {
            $max = max($max, (int) $matches[1]);
        }
    }
    return $max + 1;
}

function aitrongcay_inventory_rack_defaults(): array
{
    $number = aitrongcay_next_inventory_rack_number();
    return [
        'number' => $number,
        'rack_code' => 'RACK_' . $number,
        'rack_name' => 'Rack số ' . $number,
    ];
}

function aitrongcay_get_rack_by_id(int $rack_id): ?array
{
    global $wpdb;
    if ($rack_id <= 0) {
        return null;
    }
    $table = aitrongcay_garden_racks_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $rack_id), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_list_racks(string $status = ''): array
{
    global $wpdb;
    $table = aitrongcay_garden_racks_table();
    if ($status !== '') {
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE status = %s ORDER BY updated_at DESC, id DESC", $status), ARRAY_A) ?: [];
    }
    return $wpdb->get_results("SELECT * FROM {$table} ORDER BY updated_at DESC, id DESC", ARRAY_A) ?: [];
}

function aitrongcay_get_rack_inventory_events(int $limit = 100): array
{
    global $wpdb;
    $events_table = aitrongcay_garden_rack_inventory_events_table();
    $racks_table = aitrongcay_garden_racks_table();
    $limit = max(1, min(500, $limit));
    return $wpdb->get_results($wpdb->prepare(
        "SELECT e.*, r.rack_code, r.rack_name, r.garden_key
         FROM {$events_table} e
         LEFT JOIN {$racks_table} r ON r.id = e.rack_id
         ORDER BY e.created_at DESC, e.id DESC
         LIMIT %d",
        $limit
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_latest_rack_event(int $rack_id, string $event_type = ''): ?array
{
    global $wpdb;
    if ($rack_id <= 0) {
        return null;
    }
    $table = aitrongcay_garden_rack_inventory_events_table();
    if ($event_type !== '') {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE rack_id = %d AND event_type = %s ORDER BY created_at DESC, id DESC LIMIT 1",
            $rack_id,
            $event_type
        ), ARRAY_A);
        return is_array($row) ? $row : null;
    }
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE rack_id = %d ORDER BY created_at DESC, id DESC LIMIT 1",
        $rack_id
    ), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_get_rack_assignments(int $rack_id, bool $only_active = false, int $limit = 20): array
{
    global $wpdb;
    if ($rack_id <= 0) {
        return [];
    }

    $table = aitrongcay_garden_rack_assignments_table();
    $limit = max(1, min(100, $limit));
    if ($only_active) {
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE rack_id = %d AND status = %s ORDER BY assigned_at DESC, id DESC LIMIT %d",
            $rack_id,
            'active',
            $limit
        ), ARRAY_A) ?: [];
    }

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE rack_id = %d ORDER BY assigned_at DESC, id DESC LIMIT %d",
        $rack_id,
        $limit
    ), ARRAY_A) ?: [];
}

function aitrongcay_get_active_rack_assignment(int $rack_id): ?array
{
    $items = aitrongcay_get_rack_assignments($rack_id, true, 1);
    return isset($items[0]) && is_array($items[0]) ? $items[0] : null;
}

function aitrongcay_get_rack_slots_by_rack_id(int $rack_id): array
{
    global $wpdb;
    if ($rack_id <= 0) {
        return [];
    }
    $table = aitrongcay_garden_rack_slots_table();
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE rack_id = %d ORDER BY slot_index ASC, id ASC",
        $rack_id
    ), ARRAY_A) ?: [];
}

function aitrongcay_status_badge_style(string $status): string
{
    return match ($status) {
        'inventory' => 'background:#e8f7ee;color:#146c43;border:1px solid #b7e4c7;padding:4px 10px;border-radius:999px;font-weight:600;display:inline-block;',
        'assigned' => 'background:#e8f0fe;color:#1d4ed8;border:1px solid #bfdbfe;padding:4px 10px;border-radius:999px;font-weight:600;display:inline-block;',
        'draft' => 'background:#fff7e6;color:#b45309;border:1px solid #fcd34d;padding:4px 10px;border-radius:999px;font-weight:600;display:inline-block;',
        default => 'background:#f3f4f6;color:#374151;border:1px solid #d1d5db;padding:4px 10px;border-radius:999px;font-weight:600;display:inline-block;',
    };
}

function aitrongcay_describe_rack_holder(array $rack): string
{
    $rack_id = (int) ($rack['id'] ?? 0);
    $active_assignment = $rack_id > 0 ? aitrongcay_get_active_rack_assignment($rack_id) : null;
    if (is_array($active_assignment) && ! empty($active_assignment['garden_key'])) {
        $garden_key = trim((string) ($active_assignment['garden_key'] ?? ''));
    } else {
        $garden_key = trim((string) ($rack['garden_key'] ?? ''));
    }
    $status = trim((string) ($rack['status'] ?? ''));
    if ($garden_key === '' || $status === 'inventory') {
        return 'Trong kho';
    }
    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $owner_name = $owner instanceof WP_User ? trim((string) ($owner->display_name ?: $owner->user_login)) : '';
    $garden_name = function_exists('aitrongcay_get_garden_display_name') ? trim((string) aitrongcay_get_garden_display_name($garden_key, $owner instanceof WP_User ? $owner : null)) : '';
    $parts = array_values(array_filter([$garden_name, $owner_name, $garden_key]));
    return $parts ? implode(' • ', $parts) : $garden_key;
}

function aitrongcay_blynk_probe_rack(array $rack): array
{
    $garden_key = trim((string) ($rack['garden_key'] ?? ''));
    $rack_code = trim((string) ($rack['rack_code'] ?? ''));
    $cache_key = 'aitr_blynk_probe_' . md5($rack_code !== '' ? $rack_code : $garden_key);
    $cached = get_transient($cache_key);
    if (is_array($cached) && isset($cached['summary'])) {
        return $cached;
    }

    $cfg = function_exists('aitrongcay_blynk_runtime_config') ? aitrongcay_blynk_runtime_config($garden_key, $rack) : (function_exists('aitrongcay_blynk_config') ? aitrongcay_blynk_config($garden_key) : []);
    $base = (string) ($cfg['base'] ?? 'https://blynk.cloud/external/api');
    $shared_token = trim((string) ($cfg['token'] ?? ($rack['blynk_auth_token'] ?? '')));
    $results = [
        'ok' => [],
        'errors' => [],
        'controls' => [],
        'summary' => 'Chưa kiểm tra.',
        'connectivity_status' => 'unknown',
        'last_seen_at' => null,
    ];

    if ($shared_token === '') {
        $results['summary'] = 'Thiếu auth token Blynk.';
        $results['errors'][] = 'Thiếu auth token Blynk.';
        $results['connectivity_status'] = 'missing_token';
        set_transient($cache_key, $results, 300);
        return $results;
    }

    $shared_vpins = [];
    foreach (['temp', 'hum', 'soil', 'pump'] as $key) {
        $vpin = strtoupper(trim((string) ($cfg['vpins'][$key] ?? '')));
        if ($vpin !== '') {
            $shared_vpins[$key] = $vpin;
        }
    }
    $shared_read = function_exists('aitrongcay_blynk_read_values') ? aitrongcay_blynk_read_values($shared_token, array_values($shared_vpins), $base) : [];
    if ($shared_read === []) {
        $results['summary'] = 'Blynk không phản hồi hoặc đang bị giới hạn quota.';
        $results['errors'][] = 'Không đọc được nhóm cảm biến/chấp hành chính từ Blynk.';
        $results['connectivity_status'] = 'rate_limited';
        set_transient($cache_key, $results, 300);
        return $results;
    }
    foreach ($shared_vpins as $label => $vpin) {
        if (array_key_exists($vpin, $shared_read)) {
            $results['ok'][] = strtoupper($label) . ' đọc được qua ' . $vpin;
        } else {
            $results['errors'][] = strtoupper($label) . ' không đọc được qua ' . $vpin;
        }
    }

    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count((int) ($rack['slot_count'] ?? 0)) : max(2, min(12, (int) ($rack['slot_count'] ?? 0)));
    $light_map = [];
    foreach (range(1, min($slot_count, 4)) as $i) {
        $device = 'light' . $i;
        $vpin = strtoupper(trim((string) ($cfg['vpins'][$device] ?? '')));
        if ($vpin === '') {
            continue;
        }
        $light_map[$device] = $vpin;
    }

    $shared_light_read = $light_map && function_exists('aitrongcay_blynk_read_values') ? aitrongcay_blynk_read_values($shared_token, array_values($light_map), $base) : [];
    foreach ($light_map as $device => $vpin) {
        $slot_index = (int) str_replace('light', '', $device);
        $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($slot_index > 0 ? $slot_index : 1) : ['slot_label' => 'Khoang ' . $slot_index];
        if (array_key_exists($vpin, $shared_light_read)) {
            $results['ok'][] = 'Đèn ' . (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $slot_index)) . ' đọc được qua ' . $vpin;
        } else {
            $results['errors'][] = 'Đèn ' . (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $slot_index)) . ' không đọc được qua ' . $vpin;
        }
    }

    $results['summary'] = $results['errors'] === []
        ? 'Kết nối Blynk ổn, các điểm kiểm tra chính đều phản hồi.'
        : ('Có ' . count($results['errors']) . ' mục chưa ổn, cần kiểm tra lại token/VPin/thiết bị.');
    $results['connectivity_status'] = $results['errors'] === [] ? 'online' : (! empty($results['ok']) ? 'degraded' : 'offline');
    if ($results['connectivity_status'] !== 'offline') {
        $results['last_seen_at'] = current_time('mysql');
    }
    set_transient($cache_key, $results, 300);
    return $results;
}

function aitrongcay_format_rack_slot_summary(array $slots): string
{
    if ($slots === []) {
        return 'Chưa có khoang / khoang';
    }

    $parts = [];
    foreach ($slots as $slot) {
        if (! is_array($slot)) {
            continue;
        }
        $slot_index = (int) ($slot['slot_index'] ?? 0);
        $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($slot_index > 0 ? $slot_index : 1) : ['slot_label' => ($slot_index > 0 ? ('Khoang ' . $slot_index) : 'Khoang'), 'inlet_device' => '', 'drain_device' => ''];
        $label = trim((string) ($slot['slot_name'] ?? ''));
        if ($label === '') {
            $label = (string) ($slot_meta['slot_label'] ?? ($slot_index > 0 ? ('Khoang ' . $slot_index) : 'Khoang'));
        }
        $vpin = strtoupper(trim((string) ($slot['control_vpin'] ?? '')));
        $status = trim((string) ($slot['status'] ?? 'empty'));
        $camera_label = trim((string) ($slot['camera_label'] ?? ''));
        $camera_stream_url = trim((string) ($slot['camera_stream_url'] ?? ''));
        $water_text = '';
        if (! empty($slot_meta['inlet_device']) || ! empty($slot_meta['drain_device'])) {
            $water_text = ' · cấp: ' . strtoupper((string) ($slot_meta['inlet_device'] ?? '')) . ' · thoát: ' . strtoupper((string) ($slot_meta['drain_device'] ?? ''));
        }
        $camera_text = $camera_label !== ''
            ? (' · cam: ' . $camera_label)
            : ($camera_stream_url !== '' ? ' · cam: đã gắn' : '');
        $parts[] = $label . ($vpin !== '' ? (' · ' . $vpin) : '') . $water_text . ($status !== '' ? (' · ' . $status) : '') . $camera_text;
    }

    return $parts ? implode("\n", $parts) : 'Chưa có khoang / khoang';
}

function aitrongcay_build_rack_slots_payload(string $rack_code, int $slot_count): array
{
    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count($slot_count) : max(2, min(12, $slot_count));
    $slots = [];
    for ($i = 1; $i <= $slot_count; $i++) {
        $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($i) : ['slot_code' => sprintf('S%02d', $i), 'slot_label' => 'Khoang ' . $i, 'light_device' => 'light' . $i];
        $slots[] = [
            'slot_index' => $i,
            'slot_code' => sprintf('%s-%s', $rack_code, (string) ($slot_meta['slot_code'] ?? sprintf('S%02d', $i))),
            'slot_name' => (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $i)),
            'pot_code' => sprintf('P-%03d', $i),
            'camera_label' => '',
            'camera_stream_url' => '',
            'control_channel' => (string) ($slot_meta['light_device'] ?? ('light' . $i)),
            'control_vpin' => 'V' . (4 + $i),
            'is_enabled' => 1,
            'status' => 'empty',
        ];
    }
    return $slots;
}

function aitrongcay_save_rack_blynk_config(string $garden_key, string $auth_token, int $slot_count): void
{
    if ($garden_key === '' || ! function_exists('aitrongcay_blynk_default_config') || ! function_exists('aitrongcay_get_saved_blynk_configs') || ! function_exists('aitrongcay_save_blynk_configs')) {
        return;
    }
    $configs = aitrongcay_get_saved_blynk_configs();
    $config = isset($configs[$garden_key]) && is_array($configs[$garden_key])
        ? $configs[$garden_key]
        : aitrongcay_blynk_default_config();
    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count($slot_count) : max(2, min(12, $slot_count));
    $config['token'] = trim($auth_token);
    foreach (range(1, function_exists('aitrongcay_rack_max_compartments') ? aitrongcay_rack_max_compartments() : 6) as $compartment) {
        $config['vpins']['inlet' . $compartment] = $config['vpins']['inlet' . $compartment] ?? '';
        $config['vpins']['drain' . $compartment] = $config['vpins']['drain' . $compartment] ?? '';
        $config['devices']['inlet' . $compartment] = $config['devices']['inlet' . $compartment] ?? ('inlet' . $compartment);
        $config['devices']['drain' . $compartment] = $config['devices']['drain' . $compartment] ?? ('drain' . $compartment);
    }
    foreach (range(1, function_exists('aitrongcay_rack_max_slots') ? aitrongcay_rack_max_slots() : 12) as $i) {
        $light_key = 'light' . $i;
        $pot_code = sprintf('P-%03d', $i);
        if ($i <= $slot_count) {
            $config['vpins'][$light_key] = $config['vpins'][$light_key] ?? ('V' . (4 + $i));
            $config['devices'][$light_key] = $light_key;
            $config['pots'][$pot_code] = $light_key;
            $config['pot_tokens'][$pot_code] = '__shared__';
        } else {
            unset($config['vpins'][$light_key], $config['devices'][$light_key], $config['pots'][$pot_code], $config['pot_tokens'][$pot_code]);
        }
    }
    $configs[$garden_key] = $config;
    aitrongcay_save_blynk_configs($configs);
}

function aitrongcay_blynk_runtime_config(string $garden_key = '', $rack_id_or_code = null): array
{
    $config = function_exists('aitrongcay_blynk_config') ? aitrongcay_blynk_config($garden_key) : [];
    if (! is_array($config)) {
        $config = [];
    }

    $config['base'] = (string) ($config['base'] ?? 'https://blynk.cloud/external/api');
    $config['vpins'] = is_array($config['vpins'] ?? null) ? $config['vpins'] : [];
    $config['devices'] = is_array($config['devices'] ?? null) ? $config['devices'] : [];
    $config['pots'] = is_array($config['pots'] ?? null) ? $config['pots'] : [];
    $config['pot_tokens'] = is_array($config['pot_tokens'] ?? null) ? $config['pot_tokens'] : [];

    $rack = null;
    if ($rack_id_or_code !== null) {
        if (is_array($rack_id_or_code)) {
            $rack = $rack_id_or_code;
        } elseif (is_numeric($rack_id_or_code)) {
            $rack = function_exists('aitrongcay_get_rack_by_id') ? aitrongcay_get_rack_by_id((int) $rack_id_or_code) : null;
        } else {
            global $wpdb;
            $table = aitrongcay_garden_racks_table();
            $rack = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE rack_code = %s LIMIT 1", trim((string) $rack_id_or_code)), ARRAY_A);
        }
    }
    if (! is_array($rack)) {
        $rack = function_exists('aitrongcay_get_rack_record') ? aitrongcay_get_rack_record($garden_key) : null;
    }

    if (is_array($rack)) {
        $rack_token = trim((string) ($rack['blynk_auth_token'] ?? ''));
        if ($rack_token !== '' && trim((string) ($config['token'] ?? '')) === '') {
            $config['token'] = $rack_token;
        }

        $slots = function_exists('aitrongcay_get_rack_slots_by_rack_id') ? aitrongcay_get_rack_slots_by_rack_id((int) ($rack['id'] ?? 0)) : [];
        if (empty($slots)) {
            $slots = function_exists('aitrongcay_get_rack_slots') ? aitrongcay_get_rack_slots($garden_key) : [];
        }
        foreach ($slots as $slot) {
            if (! is_array($slot)) {
                continue;
            }
            $device = sanitize_key((string) ($slot['control_channel'] ?? ''));
            $vpin = strtoupper(trim((string) ($slot['control_vpin'] ?? '')));
            $pot_code = trim((string) ($slot['pot_code'] ?? ''));
            if ($device !== '') {
                $config['devices'][$device] = $device;
                if ($pot_code !== '') {
                    $config['pots'][$pot_code] = $device;
                    if (empty($config['pot_tokens'][$pot_code])) {
                        $config['pot_tokens'][$pot_code] = '__shared__';
                    }
                }
            }
            if ($device !== '' && $vpin !== '' && empty($config['vpins'][$device])) {
                $config['vpins'][$device] = $vpin;
            }
        }
    }

    return $config;
}

function aitrongcay_build_merged_rack_slots_payload(array $rack, int $slot_count): array
{
    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count($slot_count) : max(2, min(12, $slot_count));
    $rack_code = trim((string) ($rack['rack_code'] ?? 'RACK'));
    $existing_slots = aitrongcay_get_rack_slots_by_rack_id((int) ($rack['id'] ?? 0));
    $existing_map = [];
    foreach ($existing_slots as $slot) {
        if (! is_array($slot)) {
            continue;
        }
        $slot_index = (int) ($slot['slot_index'] ?? 0);
        if ($slot_index > 0) {
            $existing_map[$slot_index] = $slot;
        }
    }

    $slots = [];
    for ($i = 1; $i <= $slot_count; $i++) {
        $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($i) : ['slot_code' => sprintf('S%02d', $i), 'slot_label' => 'Khoang ' . $i, 'light_device' => 'light' . $i];
        $existing = $existing_map[$i] ?? [];
        $slots[] = [
            'slot_index' => $i,
            'slot_code' => (string) ($existing['slot_code'] ?? sprintf('%s-%s', $rack_code, (string) ($slot_meta['slot_code'] ?? sprintf('S%02d', $i)))),
            'slot_name' => (string) ($existing['slot_name'] ?? ($slot_meta['slot_label'] ?? ('Khoang ' . $i))),
            'pot_code' => (string) ($existing['pot_code'] ?? sprintf('P-%03d', $i)),
            'camera_label' => (string) ($existing['camera_label'] ?? ''),
            'camera_stream_url' => (string) ($existing['camera_stream_url'] ?? ''),
            'control_channel' => (string) ($existing['control_channel'] ?? ($slot_meta['light_device'] ?? ('light' . $i))),
            'control_vpin' => (string) ($existing['control_vpin'] ?? ('V' . (4 + $i))),
            'is_enabled' => array_key_exists('is_enabled', $existing) ? (int) (! empty($existing['is_enabled'])) : 1,
            'crop_id' => isset($existing['crop_id']) ? (int) $existing['crop_id'] : null,
            'crop_cycle_id' => isset($existing['crop_cycle_id']) ? (int) $existing['crop_cycle_id'] : null,
            'status' => (string) ($existing['status'] ?? 'empty'),
        ];
    }

    return $slots;
}

function aitrongcay_get_rack_slot_camera_stream_url(string $garden_key, string $pot_code): string
{
    static $cache = [];

    $garden_key = trim($garden_key);
    $pot_code = trim($pot_code);
    if ($garden_key === '' || $pot_code === '') {
        return '';
    }

    if (! isset($cache[$garden_key])) {
        $cache[$garden_key] = [];
        foreach (aitrongcay_get_rack_slots($garden_key) as $slot) {
            if (! is_array($slot)) {
                continue;
            }
            $slot_pot_code = trim((string) ($slot['pot_code'] ?? ''));
            if ($slot_pot_code === '') {
                continue;
            }
            $cache[$garden_key][$slot_pot_code] = trim((string) ($slot['camera_stream_url'] ?? ''));
        }
    }

    return (string) ($cache[$garden_key][$pot_code] ?? '');
}

function aitrongcay_update_rack_slot_cameras(int $rack_id, array $slot_cameras): array
{
    $rack = aitrongcay_get_rack_by_id($rack_id);
    if (! $rack) {
        return ['error' => 'Không tìm thấy rack để cập nhật camera.'];
    }

    $garden_key = trim((string) ($rack['garden_key'] ?? ''));
    if ($garden_key === '') {
        return ['error' => 'Rack này chưa có garden key hợp lệ để lưu camera.'];
    }

    $slots = aitrongcay_get_rack_slots_by_rack_id($rack_id);
    if (! $slots) {
        return ['error' => 'Rack này chưa có slot để gắn camera.'];
    }

    $updated = [];
    $changed_count = 0;
    foreach ($slots as $slot) {
        if (! is_array($slot)) {
            continue;
        }
        $slot_index = (int) ($slot['slot_index'] ?? 0);
        $camera_data = is_array($slot_cameras[$slot_index] ?? null) ? $slot_cameras[$slot_index] : [];
        $camera_label = trim((string) ($camera_data['camera_label'] ?? ($slot['camera_label'] ?? '')));
        $camera_stream_url = trim((string) ($camera_data['camera_stream_url'] ?? ($slot['camera_stream_url'] ?? '')));
        if ($camera_label !== trim((string) ($slot['camera_label'] ?? '')) || $camera_stream_url !== trim((string) ($slot['camera_stream_url'] ?? ''))) {
            $changed_count++;
        }
        $slot['camera_label'] = $camera_label;
        $slot['camera_stream_url'] = $camera_stream_url;
        $updated[] = $slot;
    }

    if (! aitrongcay_replace_rack_slots($garden_key, $updated)) {
        return ['error' => 'Không lưu được camera theo khoang.'];
    }

    if ($changed_count > 0) {
        // Sync webcam_url to dashboard configuration (wp_options)
        $configs = get_option('aitrongcay_rack_cfg_' . $garden_key, []);
        if (!is_array($configs)) {
            $configs = [];
        }
        $rack_token = trim((string) ($rack['blynk_auth_token'] ?? ''));
        $rack_index = -1;
        if ($rack_token !== '') {
            foreach ($configs as $idx => $cfg) {
                if (trim((string) ($cfg['blynk_auth_token'] ?? '')) === $rack_token) {
                    $rack_index = $idx;
                    break;
                }
            }
        }
        if ($rack_index < 0) {
            $rack_index = count($configs);
        }

        if ($rack_index >= 0) {
            if (!isset($configs[$rack_index])) {
                $configs[$rack_index] = [
                    'rack_name' => (string) ($rack['rack_name'] ?? 'Rack 1'),
                    'blynk_auth_token' => $rack_token,
                    'trays' => []
                ];
            }
            $trays = is_array($configs[$rack_index]['trays'] ?? null) ? $configs[$rack_index]['trays'] : [];
            foreach ($updated as $slot) {
                $slot_index = (int) ($slot['slot_index'] ?? 0);
                if ($slot_index > 0) {
                    $tray_idx = $slot_index - 1;
                    if (!isset($trays[$tray_idx])) {
                        $trays[$tray_idx] = [];
                    }
                    $trays[$tray_idx]['webcam_url'] = trim((string) ($slot['camera_stream_url'] ?? ''));
                }
            }
            $configs[$rack_index]['trays'] = $trays;
            update_option('aitrongcay_rack_cfg_' . $garden_key, $configs);
        }

        aitrongcay_log_rack_inventory_event($rack_id, 'update_slot_camera', (string) ($rack['status'] ?? ''), (string) ($rack['status'] ?? ''), 0, 'Cập nhật camera cho ' . $changed_count . ' khoang.', get_current_user_id());
    }

    return ['rack' => aitrongcay_get_rack_by_id($rack_id) ?: $rack, 'changed_count' => $changed_count];
}

function aitrongcay_probe_camera_stream_url(string $url): array
{
    $url = trim($url);
    if ($url === '') {
        return ['ok' => false, 'summary' => 'Chưa có link stream để kiểm tra.'];
    }

    if (! wp_http_validate_url($url)) {
        return ['ok' => false, 'summary' => 'Link stream không hợp lệ.'];
    }

    $args = [
        'timeout' => 12,
        'redirection' => 3,
        'sslverify' => false,
        'user-agent' => 'OpenClaw-Aitrongcay-CameraCheck/1.0',
    ];

    $response = wp_remote_head($url, $args);
    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) <= 0 || (int) wp_remote_retrieve_response_code($response) >= 400) {
        $response = wp_remote_get($url, array_merge($args, ['limit_response_size' => 2048]));
    }

    if (is_wp_error($response)) {
        return ['ok' => false, 'summary' => 'Không mở được link stream: ' . $response->get_error_message()];
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $content_type = trim((string) wp_remote_retrieve_header($response, 'content-type'));
    $body = (string) wp_remote_retrieve_body($response);
    $looks_like_stream = $content_type !== '' && (
        str_contains(strtolower($content_type), 'mpegurl')
        || str_contains(strtolower($content_type), 'application/vnd.apple.mpegurl')
        || str_contains(strtolower($content_type), 'video/')
        || str_contains(strtolower($content_type), 'octet-stream')
    );
    if (! $looks_like_stream && $body !== '') {
        $looks_like_stream = str_contains($body, '#EXTM3U') || str_contains($body, '#EXTINF');
    }

    $summary = 'HTTP ' . $status_code;
    if ($content_type !== '') {
        $summary .= ' • ' . $content_type;
    }
    if ($looks_like_stream) {
        $summary .= ' • link stream phản hồi ổn';
    } elseif ($status_code >= 200 && $status_code < 400) {
        $summary .= ' • có phản hồi nhưng chưa giống stream rõ ràng';
    } else {
        $summary .= ' • phản hồi chưa đạt';
    }

    return [
        'ok' => $status_code >= 200 && $status_code < 400,
        'status_code' => $status_code,
        'content_type' => $content_type,
        'looks_like_stream' => $looks_like_stream,
        'summary' => $summary,
    ];
}

function aitrongcay_update_rack_hardware(int $rack_id, array $payload): array
{
    $rack = aitrongcay_get_rack_by_id($rack_id);
    if (! $rack) {
        return ['error' => 'Không tìm thấy rack để cập nhật.'];
    }

    $garden_key = (string) ($rack['garden_key'] ?? '');
    $token = array_key_exists('blynk_auth_token', $payload)
        ? trim((string) $payload['blynk_auth_token'])
        : trim((string) ($rack['blynk_auth_token'] ?? ''));
    $requested_slot_count = array_key_exists('slot_count', $payload)
        ? (function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count((int) $payload['slot_count']) : max(2, min(12, (int) $payload['slot_count'])))
        : (int) ($rack['slot_count'] ?? 0);
    $current_slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count((int) ($rack['slot_count'] ?? 0)) : max(2, min(12, (int) ($rack['slot_count'] ?? 0)));
    $status = (string) ($rack['status'] ?? 'inventory');

    if ($requested_slot_count !== $current_slot_count && $status !== 'inventory' && $requested_slot_count < $current_slot_count) {
        $slots = aitrongcay_get_rack_slots_by_rack_id($rack_id);
        $blocked_slots = [];
        foreach ($slots as $slot) {
            $slot_index = (int) ($slot['slot_index'] ?? 0);
            if ($slot_index <= $requested_slot_count) {
                continue;
            }

            $has_crop = (int) ($slot['crop_id'] ?? 0) > 0 || (int) ($slot['crop_cycle_id'] ?? 0) > 0;
            $status_text = strtolower(trim((string) ($slot['status'] ?? 'empty')));
            $is_empty_status = in_array($status_text, ['', 'empty', 'available', 'inactive', 'disabled'], true);
            if ($has_crop || ! $is_empty_status) {
                $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($slot_index) : ['slot_label' => 'Khoang ' . $slot_index];
                $blocked_slots[] = (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $slot_index));
            }
        }

        if ($blocked_slots) {
            return ['error' => 'Chưa thể giảm số khoang vì ' . implode(', ', $blocked_slots) . ' vẫn đang có dữ liệu sử dụng. Anh làm trống các khoang này trước rồi lưu lại giúp em.'];
        }
    }

    $ok = aitrongcay_upsert_rack_record($garden_key, [
        'rack_code' => (string) ($rack['rack_code'] ?? ''),
        'rack_name' => (string) ($rack['rack_name'] ?? ''),
        'owner_user_id' => (int) ($rack['owner_user_id'] ?? 0),
        'status' => $status,
        'slot_count' => $requested_slot_count,
        'controller_type' => (string) ($rack['controller_type'] ?? 'blynk'),
        'controller_label' => $token,
        'blynk_auth_token' => $token,
        'blynk_template_id' => (string) ($rack['blynk_template_id'] ?? ''),
        'blynk_template_name' => (string) ($rack['blynk_template_name'] ?? ''),
        'blynk_email' => (string) ($rack['blynk_email'] ?? ''),
        'connectivity_status' => (string) ($rack['connectivity_status'] ?? 'unknown'),
        'last_seen_at' => (string) ($rack['last_seen_at'] ?? ''),
        'notes' => (string) ($rack['notes'] ?? ''),
    ]);
    if (! $ok) {
        return ['error' => 'Không cập nhật được cấu hình rack.'];
    }

    $updated_rack = aitrongcay_get_rack_by_id($rack_id) ?: $rack;
    aitrongcay_replace_rack_slots($garden_key, aitrongcay_build_merged_rack_slots_payload($updated_rack, $requested_slot_count));
    aitrongcay_save_rack_blynk_config($garden_key, $token, $requested_slot_count);

    if ($token !== trim((string) ($rack['blynk_auth_token'] ?? ''))) {
        aitrongcay_log_rack_inventory_event($rack_id, 'update_token', $status, $status, 0, 'Cập nhật auth token Blynk', get_current_user_id());
    }
    if ($requested_slot_count !== $current_slot_count) {
        aitrongcay_log_rack_inventory_event($rack_id, 'update_slot_count', $status, $status, 0, 'Đổi cấu hình rack từ ' . (function_exists('aitrongcay_rack_compartment_summary') ? aitrongcay_rack_compartment_summary($current_slot_count) : ($current_slot_count . ' khoang')) . ' sang ' . (function_exists('aitrongcay_rack_compartment_summary') ? aitrongcay_rack_compartment_summary($requested_slot_count) : ($requested_slot_count . ' khoang')), get_current_user_id());
    }

    return ['rack' => aitrongcay_get_rack_by_id($rack_id) ?: $updated_rack];
}

function aitrongcay_create_inventory_rack(array $payload): array
{
    $defaults = aitrongcay_inventory_rack_defaults();
    $rack_code = strtoupper(trim((string) ($payload['rack_code'] ?? '')));
    $rack_name = trim((string) ($payload['rack_name'] ?? ''));
    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count((int) ($payload['slot_count'] ?? 2)) : max(2, min(12, (int) ($payload['slot_count'] ?? 2)));
    $auth_token = trim((string) ($payload['blynk_auth_token'] ?? ''));
    if ($rack_code === '') {
        $rack_code = (string) ($defaults['rack_code'] ?? 'RACK_1');
    }
    if ($rack_name === '') {
        $rack_name = (string) ($defaults['rack_name'] ?? ('Rack số 1'));
    }
    $garden_key = aitrongcay_build_inventory_rack_key($rack_code);

    $saved = aitrongcay_upsert_rack_record($garden_key, [
        'rack_code' => $rack_code,
        'rack_name' => $rack_name,
        'owner_user_id' => 0,
        'status' => 'inventory',
        'slot_count' => $slot_count,
        'controller_type' => 'blynk',
        'controller_label' => $auth_token,
        'blynk_auth_token' => $auth_token,
        'notes' => (string) ($payload['notes'] ?? 'Nhập kho rack mới'),
    ]);
    if (! $saved) {
        return ['error' => 'Không tạo được rack mới trong kho. Có thể mã rack đang bị trùng.'];
    }

    aitrongcay_replace_rack_slots($garden_key, aitrongcay_build_rack_slots_payload($rack_code, $slot_count));
    aitrongcay_save_rack_blynk_config($garden_key, $auth_token, $slot_count);
    $rack = aitrongcay_get_rack_record($garden_key);
    if ($rack) {
        aitrongcay_log_rack_inventory_event((int) ($rack['id'] ?? 0), 'stock_in', '', 'inventory', 0, 'Nhập kho rack ' . $rack_code, get_current_user_id());
    }
    return ['rack' => $rack];
}

function aitrongcay_delete_rack(int $rack_id): bool
{
    global $wpdb;
    $rack = aitrongcay_get_rack_by_id($rack_id);
    if (! $rack) {
        return false;
    }
    $wpdb->delete(aitrongcay_garden_rack_slots_table(), ['rack_id' => $rack_id], ['%d']);
    $wpdb->delete(aitrongcay_garden_rack_assignments_table(), ['rack_id' => $rack_id], ['%d']);
    $wpdb->delete(aitrongcay_garden_rack_inventory_events_table(), ['rack_id' => $rack_id], ['%d']);
    if (function_exists('aitrongcay_get_saved_blynk_configs') && function_exists('aitrongcay_save_blynk_configs')) {
        $configs = aitrongcay_get_saved_blynk_configs();
        $garden_key = (string) ($rack['garden_key'] ?? '');
        if ($garden_key !== '' && isset($configs[$garden_key])) {
            unset($configs[$garden_key]);
            aitrongcay_save_blynk_configs($configs);
        }
    }
    return false !== $wpdb->delete(aitrongcay_garden_racks_table(), ['id' => $rack_id], ['%d']);
}

function aitrongcay_release_rack_to_inventory(int $rack_id): array
{
    global $wpdb;
    $rack = aitrongcay_get_rack_by_id($rack_id);
    if (! $rack) {
        return ['error' => 'Không tìm thấy rack để thu hồi.'];
    }
    $current_status = (string) ($rack['status'] ?? '');
    if ($current_status === 'inventory') {
        return ['rack' => $rack, 'already_inventory' => true];
    }
    $inventory_key   = aitrongcay_build_inventory_rack_key((string) ($rack['rack_code'] ?? ''));
    $from_garden_key = (string) ($rack['garden_key'] ?? '');

    // ── DATA HANDOFF: Đóng gói dữ liệu của KH cũ TRƯỚC khi thu hồi ──────────
    do_action('aitrongcay_before_rack_release', $rack_id, $from_garden_key);

    $updated = false !== $wpdb->update(
        aitrongcay_garden_racks_table(),
        [
            'garden_key'   => $inventory_key,
            'owner_user_id'=> 0,
            'status'       => 'inventory',
            'updated_at'   => current_time('mysql'),
        ],
        ['id' => $rack_id],
        ['%s','%d','%s','%s'],
        ['%d']
    );
    if (! $updated) {
        return ['error' => 'Không thu hồi rack về kho được.'];
    }
    $wpdb->update(
        aitrongcay_garden_rack_assignments_table(),
        [
            'released_at' => current_time('mysql'),
            'status'      => 'released',
            'notes'       => 'Thu hồi rack về kho',
        ],
        ['rack_id' => $rack_id, 'status' => 'active'],
        ['%s','%s','%s'],
        ['%d','%s']
    );
    aitrongcay_move_blynk_config_key($from_garden_key, $inventory_key);
    aitrongcay_log_rack_inventory_event($rack_id, 'release', $current_status, 'inventory', 0, 'Thu hồi rack về kho', get_current_user_id());
    return ['rack' => aitrongcay_get_rack_by_id($rack_id)];
}

function aitrongcay_find_available_inventory_rack(int $minimum_slots = 2): ?array
{
    global $wpdb;
    $table = aitrongcay_garden_racks_table();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = %s AND slot_count >= %d ORDER BY slot_count ASC, id ASC LIMIT 1",
        'inventory',
        max(2, $minimum_slots)
    ), ARRAY_A);
    return is_array($row) ? $row : null;
}

function aitrongcay_log_rack_inventory_event(int $rack_id, string $event_type, string $from_status = '', string $to_status = '', int $target_user_id = 0, string $notes = '', int $created_by_user_id = 0): bool
{
    global $wpdb;
    if ($event_type === '') {
        return false;
    }
    $table = aitrongcay_garden_rack_inventory_events_table();
    return false !== $wpdb->insert($table, [
        'rack_id' => max(0, $rack_id),
        'event_type' => $event_type,
        'from_status' => $from_status,
        'to_status' => $to_status,
        'target_user_id' => $target_user_id > 0 ? $target_user_id : null,
        'notes' => $notes,
        'created_by_user_id' => $created_by_user_id > 0 ? $created_by_user_id : null,
        'created_at' => current_time('mysql'),
    ], ['%d','%s','%s','%s','%d','%s','%d','%s']);
}

function aitrongcay_move_blynk_config_key(string $from_garden_key, string $to_garden_key): void
{
    if ($from_garden_key === '' || $to_garden_key === '' || $from_garden_key === $to_garden_key) {
        return;
    }
    if (! function_exists('aitrongcay_get_saved_blynk_configs') || ! function_exists('aitrongcay_save_blynk_configs')) {
        return;
    }
    $configs = aitrongcay_get_saved_blynk_configs();
    if (! isset($configs[$from_garden_key])) {
        return;
    }
    $configs[$to_garden_key] = $configs[$from_garden_key];
    unset($configs[$from_garden_key]);
    aitrongcay_save_blynk_configs($configs);
}

function aitrongcay_assign_inventory_rack_to_garden(string $garden_key, int $user_id = 0): array
{
    global $wpdb;
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return ['error' => 'Thiếu mã khu vườn để cấp rack.'];
    }

    $inventory_rack = aitrongcay_find_available_inventory_rack(2);
    if (! $inventory_rack) {
        aitrongcay_log_rack_inventory_event(0, 'out_of_stock', 'inventory', 'empty', $user_id, 'Kho rack đã hết khi yêu cầu cấp cho khu vườn ' . $garden_key, $user_id);
        return ['error' => 'Kho rack hiện đang hết. Anh vui lòng chờ thêm để em bổ sung rack mới rồi mình khởi tạo tiếp.'];
    }

    $from_garden_key = (string) ($inventory_rack['garden_key'] ?? '');
    $rack_id = (int) ($inventory_rack['id'] ?? 0);
    $now = current_time('mysql');
    $updated = false !== $wpdb->update(
        aitrongcay_garden_racks_table(),
        [
            'garden_key' => $garden_key,
            'owner_user_id' => $user_id > 0 ? $user_id : null,
            'status' => 'assigned',
            'updated_at' => $now,
        ],
        ['id' => $rack_id],
        ['%s','%d','%s','%s'],
        ['%d']
    );
    if (! $updated) {
        return ['error' => 'Em chưa cấp rack từ kho sang khu vườn này được.'];
    }

    $assignments_table = aitrongcay_garden_rack_assignments_table();
    $wpdb->insert($assignments_table, [
        'rack_id' => $rack_id,
        'user_id' => max(0, $user_id),
        'garden_key' => $garden_key,
        'household_key' => $garden_key,
        'assigned_at' => $now,
        'status' => 'active',
        'notes' => 'Cấp rack từ kho cho khu vườn người dùng',
    ], ['%d','%d','%s','%s','%s','%s','%s']);

    aitrongcay_move_blynk_config_key($from_garden_key, $garden_key);
    aitrongcay_log_rack_inventory_event($rack_id, 'assign', (string) ($inventory_rack['status'] ?? 'inventory'), 'assigned', $user_id, 'Cấp rack từ kho sang khu vườn ' . $garden_key, $user_id);

    // ── DATA HANDOFF: Chuẩn bị luồng dữ liệu sạch cho KH mới ─────────────────
    do_action('aitrongcay_after_rack_assign', $rack_id, $garden_key, $from_garden_key);

    $rack = aitrongcay_get_rack_record($garden_key);
    return ['rack' => $rack ?: $inventory_rack, 'assigned' => true];
}

function aitrongcay_rack_has_physical_device(array $rack): bool
{
    $controller_type = trim((string) ($rack['controller_type'] ?? ''));
    $token = trim((string) ($rack['blynk_auth_token'] ?? ''));
    return $controller_type === 'blynk' && $token !== '';
}

function aitrongcay_default_empty_tray_image_url(): string
{
    global $wpdb;
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $table = aitrongcay_garden_pots_table();
    $preferred = $wpdb->get_var(
        "SELECT image_url FROM {$table}
         WHERE image_url <> ''
           AND (
             LOWER(pot_name) LIKE '%trống%'
             OR LOWER(status) LIKE '%trống%'
             OR LOWER(status_summary) LIKE '%trống%'
             OR LOWER(pot_name) LIKE '%empty%'
             OR LOWER(status) LIKE '%empty%'
             OR LOWER(status_summary) LIKE '%empty%'
           )
         ORDER BY id DESC
         LIMIT 1"
    );
    if (is_string($preferred) && trim($preferred) !== '') {
        $cached = trim($preferred);
        return $cached;
    }

    $fallback = $wpdb->get_var("SELECT image_url FROM {$table} WHERE image_url <> '' ORDER BY id DESC LIMIT 1");
    $cached = is_string($fallback) && trim($fallback) !== ''
        ? trim($fallback)
        : (get_template_directory_uri() . '/assets/images/tool-tray-real.png');
    return $cached;
}

function aitrongcay_build_empty_placeholder_pots(string $garden_key, int $slot_count): array
{
    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count($slot_count) : max(2, min(12, $slot_count));
    $image_url = aitrongcay_default_empty_tray_image_url();
    $pots = [];
    for ($i = 1; $i <= $slot_count; $i++) {
        $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($i) : ['slot_label' => 'Khoang ' . $i];
        $pots[] = [
            'pot_code' => sprintf('P-%03d', $i),
            'pot_name' => (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $i)),
            'plant_name' => 'Cây chưa xác định',
            'status' => 'Đang theo dõi',
            'status_summary' => 'Khoang vừa được kích hoạt, đang bắt đầu theo dõi Ngày 1.',
            'created_at' => current_time('mysql'),
            'ph' => '--',
            'temperature' => '--',
            'humidity' => '--',
            'light_label' => 'Đèn ' . (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $i)),
            'light_device' => '',
            'pump_label' => 'Bơm chung',
            'irrigation' => 'Mới cấp nước',
            'video_url' => '',
            'image_url' => $image_url,
            'ai_note' => 'Khu vườn bắt đầu ghi nhận dữ liệu sinh trưởng của khoang mới.',
            'harvest_eta' => 'Chưa có cây',
            'sort_order' => $i,
        ];
    }

    return $pots;
}

function aitrongcay_seed_placeholder_pots_for_garden(string $garden_key, int $slot_count = 4): void
{
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return;
    }
    $existing = aitrongcay_get_db_pots($garden_key);
    if (count($existing) >= $slot_count) {
        return;
    }

    foreach (aitrongcay_build_empty_placeholder_pots($garden_key, $slot_count) as $pot) {
        aitrongcay_upsert_db_pot($garden_key, $pot);
    }
}

function aitrongcay_create_placeholder_rack_for_garden(WP_User $user, string $garden_key, int $slot_count = 4): array
{
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return ['error' => 'Chưa xác định được khu vườn để khởi tạo rack mặc định.'];
    }

    $existing = aitrongcay_get_rack_record($garden_key);
    if ($existing && (int) ($existing['slot_count'] ?? 0) >= 2) {
        return ['rack' => $existing, 'already_exists' => true];
    }

    $slot_count = function_exists('aitrongcay_normalize_rack_slot_count') ? aitrongcay_normalize_rack_slot_count($slot_count) : max(2, min(12, $slot_count));
    $rack_suffix = strtoupper(substr(md5($garden_key), 0, 6));
    $rack_code = 'RACK_' . $rack_suffix;
    $rack_name = 'Rack 1';
    $saved = aitrongcay_upsert_rack_record($garden_key, [
        'rack_code' => $rack_code,
        'rack_name' => $rack_name,
        'owner_user_id' => (int) $user->ID,
        'status' => 'assigned',
        'slot_count' => $slot_count,
        'controller_type' => 'manual',
        'controller_label' => 'Chưa gắn thiết bị',
        'blynk_auth_token' => '',
        'connectivity_status' => 'not_provisioned',
        'notes' => 'Rack mặc định cho user mới đăng nhập, chưa gắn thiết bị thật.',
    ]);
    if (! $saved) {
        return ['error' => 'Không tạo được rack mặc định cho user.'];
    }

    aitrongcay_replace_rack_slots($garden_key, aitrongcay_build_rack_slots_payload($rack_code, $slot_count));
    aitrongcay_seed_placeholder_pots_for_garden($garden_key, $slot_count);
    $rack = aitrongcay_get_rack_record($garden_key);
    if ($rack) {
        global $wpdb;
        $assignments_table = aitrongcay_garden_rack_assignments_table();
        $wpdb->insert($assignments_table, [
            'rack_id' => (int) ($rack['id'] ?? 0),
            'user_id' => (int) $user->ID,
            'garden_key' => $garden_key,
            'household_key' => $garden_key,
            'assigned_at' => current_time('mysql'),
            'status' => 'active',
            'notes' => 'Khởi tạo rack mặc định chưa gắn thiết bị cho user',
        ], ['%d','%d','%s','%s','%s','%s','%s']);
        aitrongcay_log_rack_inventory_event((int) ($rack['id'] ?? 0), 'auto_create_placeholder', '', 'assigned', (int) $user->ID, 'Tạo rack mặc định chưa gắn thiết bị cho user mới', (int) $user->ID);
    }

    if (function_exists('aitrongcay_upsert_garden_record')) {
        aitrongcay_upsert_garden_record($garden_key, (int) $user->ID, [
            'garden_name' => function_exists('aitrongcay_build_default_garden_name') ? aitrongcay_build_default_garden_name($garden_key, $user) : 'Khu vườn của bạn',
            'garden_code' => strtoupper(substr(md5($garden_key), 0, 6)),
            'summary' => 'Khu vườn đã có rack mặc định để người dùng bắt đầu tạo khoang, chưa gắn thiết bị thật.',
            'status_line' => (function_exists('aitrongcay_rack_compartment_summary') ? aitrongcay_rack_compartment_summary($slot_count) : ($slot_count . ' khoang')) . ' • chưa gắn thiết bị',
        ]);
    }

    return ['rack' => $rack, 'created' => true];
}

function aitrongcay_initialize_rack_for_user(WP_User $user, string $garden_key): array
{
    $garden_key = trim($garden_key);
    if ($garden_key === '') {
        return ['error' => 'Chưa xác định được khu vườn để khởi tạo rack.'];
    }

    $assigned = aitrongcay_assign_inventory_rack_to_garden($garden_key, (int) $user->ID);
    if (! empty($assigned['error'])) {
        return $assigned;
    }

    $rack = (array) ($assigned['rack'] ?? []);
    if ($rack !== [] && function_exists('aitrongcay_upsert_garden_record')) {
        $default_name = function_exists('aitrongcay_build_default_garden_name') ? aitrongcay_build_default_garden_name($garden_key, $user) : 'Khu vườn của bạn';
        aitrongcay_upsert_garden_record($garden_key, (int) $user->ID, [
            'garden_name' => $default_name,
            'garden_code' => strtoupper(substr(md5($garden_key), 0, 6)),
            'summary' => 'Khu vườn này đã được cấp 1 rack từ kho thiết bị và sẵn sàng khởi tạo theo mô hình rack > khoang > 2 khoang.',
            'status_line' => (function_exists('aitrongcay_rack_compartment_summary') ? aitrongcay_rack_compartment_summary((int) ($rack['slot_count'] ?? 0)) : (((int) ($rack['slot_count'] ?? 0)) . ' khoang')) . ' • rack đã sẵn sàng',
        ]);
    }

    return [
        'rack' => $rack,
        'garden_key' => $garden_key,
        'assigned' => ! empty($assigned['assigned']),
        'already_assigned' => ! empty($assigned['already_assigned']),
    ];
}

function aitrongcay_get_garden_pot_notes(string $garden_key): array
{
    global $wpdb;
    static $cache = [];
    if ($garden_key === '') {
        return [];
    }
    if (isset($cache[$garden_key])) {
        return $cache[$garden_key];
    }

    $table = aitrongcay_garden_notes_table();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT pot_code, note_text, updated_at, updated_by_user_id FROM {$table} WHERE garden_key = %s ORDER BY updated_at DESC, id DESC",
        $garden_key
    ), ARRAY_A) ?: [];

    $notes = [];
    foreach ($rows as $row) {
        $pot_code = sanitize_text_field((string) ($row['pot_code'] ?? ''));
        if ($pot_code === '' || isset($notes[$pot_code])) {
            continue;
        }
        $notes[$pot_code] = [
            'pot_code' => $pot_code,
            'note_text' => trim((string) ($row['note_text'] ?? '')),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
            'updated_by_user_id' => (int) ($row['updated_by_user_id'] ?? 0),
        ];
    }

    $cache[$garden_key] = $notes;
    return $cache[$garden_key];
}

function aitrongcay_save_garden_pot_note(string $garden_key, string $pot_code, string $note_text, int $user_id = 0): bool
{
    global $wpdb;
    if ($garden_key === '' || $pot_code === '') {
        return false;
    }

    $table = aitrongcay_garden_notes_table();
    $now = current_time('mysql');
    $existing_id = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE garden_key = %s AND pot_code = %s LIMIT 1",
        $garden_key,
        $pot_code
    ));

    $payload = [
        'note_text' => $note_text,
        'updated_by_user_id' => max(0, $user_id),
        'updated_at' => $now,
    ];

    if ($existing_id > 0) {
        return false !== $wpdb->update(
            $table,
            $payload,
            ['id' => $existing_id],
            ['%s', '%d', '%s'],
            ['%d']
        );
    }

    return false !== $wpdb->insert($table, [
        'garden_key' => $garden_key,
        'pot_code' => $pot_code,
        'note_text' => $note_text,
        'updated_by_user_id' => max(0, $user_id),
        'created_at' => $now,
        'updated_at' => $now,
    ], ['%s', '%s', '%s', '%d', '%s', '%s']);
}

function aitrongcay_normalize_pot_note_text(string $note_text, string $today_label = ''): string
{
    $note_text = preg_replace('/\r\n?|\n/u', "\n", $note_text);
    $note_text = trim((string) $note_text);
    if ($note_text === '') {
        return '';
    }

    $today_label = trim($today_label);
    if ($today_label === '') {
        $today_label = wp_date('d/m/Y');
    }

    if (preg_match('/^' . preg_quote($today_label, '/') . '$/m', $note_text)) {
        return $note_text;
    }

    $segments = preg_split('/\n{2,}/u', $note_text) ?: [];
    $normalized_segments = [];
    foreach ($segments as $segment) {
        $segment = trim((string) $segment);
        if ($segment === '') {
            continue;
        }

        $lines = preg_split('/\n/u', $segment) ?: [];
        $lines = array_values(array_filter(array_map(static function ($line) {
            $line = trim((string) $line);
            return $line === '' ? null : $line;
        }, $lines)));
        if (! $lines) {
            continue;
        }

        $first_line = $lines[0] ?? '';
        if (! preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $first_line)) {
            array_unshift($lines, $today_label);
        }

        $normalized_segments[] = implode("\n", $lines);
    }

    if (! $normalized_segments) {
        return '';
    }

    $last_index = count($normalized_segments) - 1;
    $last_lines = preg_split('/\n/u', $normalized_segments[$last_index]) ?: [];
    $last_lines = array_values(array_filter(array_map('trim', $last_lines), static fn($line) => $line !== ''));
    $last_date = $last_lines[0] ?? '';
    $last_body = implode("\n", array_slice($last_lines, 1));
    if ($last_date !== $today_label && $last_body !== '') {
        $normalized_segments[] = $today_label;
    }

    return trim(implode("\n\n", $normalized_segments));
}

function aitrongcay_preferred_garden_key_for_user(?WP_User $user = null): string
{
    global $wpdb;

    $user = $user instanceof WP_User ? $user : wp_get_current_user();
    if (! $user instanceof WP_User || ! $user->exists()) {
        return aitrongcay_legacy_garden_key_for_user($user);
    }

    $user_id = (int) $user->ID;
    if ($user_id > 0 && function_exists('aitrongcay_get_user_garden_memberships')) {
        $active_memberships = aitrongcay_get_user_garden_memberships($user_id, ['active']);
        foreach ($active_memberships as $membership) {
            if (($membership['role'] ?? '') === 'owner' && ! empty($membership['garden_key'])) {
                return trim((string) $membership['garden_key']);
            }
        }
    }

    if ($user_id > 0 && function_exists('aitrongcay_gardens_table')) {
        $gardens_table = aitrongcay_gardens_table();
        $owned_garden_key = $wpdb->get_var($wpdb->prepare(
            "SELECT garden_key FROM {$gardens_table} WHERE owner_user_id = %d ORDER BY updated_at DESC, id DESC LIMIT 1",
            $user_id
        ));
        $owned_garden_key = is_string($owned_garden_key) ? trim($owned_garden_key) : '';
        if ($owned_garden_key !== '') {
            return $owned_garden_key;
        }
    }

    if ($user_id > 0 && function_exists('aitrongcay_get_user_garden_memberships')) {
        $active_memberships = aitrongcay_get_user_garden_memberships($user_id, ['active']);
        foreach ($active_memberships as $membership) {
            if (! empty($membership['garden_key'])) {
                return trim((string) $membership['garden_key']);
            }
        }
    }

    // Fallback: lấy garden_key từ đơn hàng active nếu user chưa có membership
    if ($user_id > 0) {
        $_ot = $wpdb->prefix . 'aitr_orders';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$_ot}'") === $_ot) {
            $order_gk = $wpdb->get_var($wpdb->prepare(
                "SELECT garden_key FROM {$_ot} WHERE user_id = %d AND status = 'active' AND garden_key != '' ORDER BY updated_at DESC LIMIT 1",
                $user_id
            ));
            $order_gk = is_string($order_gk) ? trim($order_gk) : '';
            if ($order_gk !== '') {
                return $order_gk;
            }
            // Fallback thêm: tìm theo email nếu user_id chưa gắn
            $order_gk = $wpdb->get_var($wpdb->prepare(
                "SELECT garden_key FROM {$_ot} WHERE customer_email = %s AND status = 'active' AND garden_key != '' ORDER BY updated_at DESC LIMIT 1",
                $user->user_email
            ));
            $order_gk = is_string($order_gk) ? trim($order_gk) : '';
            if ($order_gk !== '') {
                return $order_gk;
            }
        }
    }

    return aitrongcay_legacy_garden_key_for_user($user);
}

function aitrongcay_seed_owner_membership(): void
{
    if (! is_user_logged_in()) {
        return;
    }

    global $wpdb;
    $user = wp_get_current_user();
    $user_id = (int) ($user->ID ?? 0);
    if ($user_id <= 0) {
        return;
    }

    $garden_key = aitrongcay_preferred_garden_key_for_user($user);
    $table = aitrongcay_garden_members_table();
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE garden_key = %s AND user_id = %d LIMIT 1", $garden_key, $user_id));
    if (! $exists) {
        $wpdb->insert($table, [
            'garden_key' => $garden_key,
            'user_id' => $user_id,
            'role' => 'owner',
            'status' => 'active',
            'invited_by_user_id' => null,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
    } else {
        $wpdb->update(
            $table,
            [
                'role' => 'owner',
                'status' => 'active',
                'updated_at' => current_time('mysql'),
            ],
            [
                'id' => (int) $exists,
            ],
            ['%s', '%s', '%s'],
            ['%d']
        );
    }

    aitrongcay_remember_selected_garden_key($user_id, $garden_key);
}
add_action('wp', 'aitrongcay_seed_owner_membership');

function aitrongcay_ensure_logged_in_user_has_default_rack(): void
{
    // LOGIC CŨ: Tự động khởi tạo 1 Rack (Rack 1) và 4 Khoang ảo (Pots) cho tất cả user đăng nhập.
    // LOGIC MỚI: Đã bị vô hiệu hóa. Khách hàng (và cả Admin) chỉ có Rack khi Admin chủ động gán Rack vật lý từ kho.
    return;
}
add_action('wp', 'aitrongcay_ensure_logged_in_user_has_default_rack', 11);

function aitrongcay_market_owner_garden_key_from_post(WP_Post $post): string
{
    $garden_key = trim((string) get_post_meta($post->ID, '_aitrongcay_market_garden_key', true));
    if ($garden_key !== '') {
        return $garden_key;
    }

    $author = get_user_by('id', (int) $post->post_author);
    return aitrongcay_primary_garden_key_for_user($author instanceof WP_User ? $author : null);
}

function aitrongcay_market_context_garden_key(): string
{
    if (! is_user_logged_in()) {
        return '';
    }

    return aitrongcay_resolve_active_garden_key(wp_get_current_user());
}

function aitrongcay_migrate_attachment_garden_key(int $attachment_id): string
{
    $garden_key = trim((string) get_post_meta($attachment_id, '_aitrongcay_photo_garden_key', true));
    if ($garden_key !== '') {
        return $garden_key;
    }

    $attachment = get_post($attachment_id);
    if (! $attachment || $attachment->post_type !== 'attachment') {
        return '';
    }

    $owner_id = (int) get_post_meta($attachment_id, '_aitrongcay_photo_owner', true);
    if ($owner_id <= 0) {
        $owner_id = (int) $attachment->post_author;
    }

    $garden_key = '';
    if ($owner_id > 0) {
        $owner = get_user_by('id', $owner_id);
        $garden_key = aitrongcay_primary_garden_key_for_user($owner instanceof WP_User ? $owner : null);
    }

    if ($garden_key === '') {
        $parent_id = (int) $attachment->post_parent;
        if ($parent_id > 0) {
            $parent_post = get_post($parent_id);
            if ($parent_post instanceof WP_Post) {
                $garden_key = aitrongcay_market_owner_garden_key_from_post($parent_post);
            }
        }
    }

    if ($garden_key !== '') {
        update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', $garden_key);
    }

    return $garden_key;
}

function aitrongcay_migrate_market_post_garden_key(int $post_id): string
{
    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post') {
        return '';
    }

    $garden_key = trim((string) get_post_meta($post_id, '_aitrongcay_market_garden_key', true));
    if ($garden_key === '') {
        $garden_key = aitrongcay_primary_garden_key_for_user(get_user_by('id', (int) $post->post_author) ?: null);
        if ($garden_key !== '') {
            update_post_meta($post_id, '_aitrongcay_market_garden_key', $garden_key);
        }
    }

    $gallery = array_map('absint', (array) get_post_meta($post_id, '_aitrongcay_market_gallery', true));
    foreach ($gallery as $attachment_id) {
        $attachment_garden = aitrongcay_migrate_attachment_garden_key($attachment_id);
        if ($garden_key === '' && $attachment_garden !== '') {
            $garden_key = $attachment_garden;
            update_post_meta($post_id, '_aitrongcay_market_garden_key', $garden_key);
        }
    }

    $thumb_id = (int) get_post_thumbnail_id($post_id);
    if ($thumb_id > 0) {
        $thumb_garden = aitrongcay_migrate_attachment_garden_key($thumb_id);
        if ($garden_key === '' && $thumb_garden !== '') {
            $garden_key = $thumb_garden;
            update_post_meta($post_id, '_aitrongcay_market_garden_key', $garden_key);
        }
    }

    return $garden_key;
}

function aitrongcay_migrate_legacy_garden_media(int $limit = 80): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $attachments = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => '_aitrongcay_photo_garden_key',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);
    foreach ($attachments as $attachment_id) {
        aitrongcay_migrate_attachment_garden_key((int) $attachment_id);
    }

    $market_posts = get_posts([
        'post_type' => 'aitr_market_post',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => '_aitrongcay_market_garden_key',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);
    foreach ($market_posts as $market_post_id) {
        aitrongcay_migrate_market_post_garden_key((int) $market_post_id);
    }
}
add_action('wp', static function (): void {
    aitrongcay_migrate_legacy_garden_media();
}, 15);

function aitrongcay_market_posts_query_args(string $garden_key = '', int $posts_per_page = 24): array
{
    $args = [
        'post_type' => 'aitr_market_post',
        'post_status' => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
        'ignore_sticky_posts' => true,
    ];

    $meta_query = [];
    if ($garden_key !== '') {
        $meta_query[] = [
            'key' => '_aitrongcay_market_garden_key',
            'value' => $garden_key,
        ];
    }

    $category = sanitize_text_field((string) ($_GET['market_category'] ?? ''));
    if ($category !== '') {
        $meta_query[] = [
            'key' => '_aitrongcay_market_category',
            'value' => $category,
        ];
    }

    $offerType = sanitize_text_field((string) ($_GET['market_offer_type'] ?? ''));
    if ($offerType !== '') {
        $meta_query[] = [
            'key' => '_aitrongcay_market_offer_type',
            'value' => $offerType,
        ];
    }

    if ($meta_query) {
        $args['meta_query'] = $meta_query;
    }

    $search = sanitize_text_field((string) ($_GET['market_search'] ?? ''));
    if ($search !== '') {
        $args['s'] = $search;
    }

    $sort = sanitize_key((string) ($_GET['market_sort'] ?? 'newest'));
    if ($sort === 'popular') {
        $args['meta_key'] = '_aitrongcay_market_share_count';
        $args['orderby'] = 'meta_value_num date';
        $args['order'] = 'DESC';
    }

    return $args;
}

function aitrongcay_blynk_get_status_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        $garden_key = function_exists('aitrongcay_market_context_garden_key') ? aitrongcay_market_context_garden_key() : '';
    }
    if ($garden_key !== '' && ! aitrongcay_user_can_view_garden($garden_key, get_current_user_id())) {
        wp_send_json_error(['message' => 'Không có quyền xem trạng thái khu vườn này.'], 403);
    }

    $req_rack_index = isset($_POST['rack_index']) ? (int)$_POST['rack_index'] : 0;
    $req_tray_index = isset($_POST['tray_index']) ? (int)$_POST['tray_index'] : 0;

    $cache_key = 'aitr_blynk_status_' . md5($garden_key . '_r' . $req_rack_index . '_t' . $req_tray_index);
    $cooldown_key = 'aitr_blynk_status_cooldown_' . md5($garden_key . '_r' . $req_rack_index . '_t' . $req_tray_index);
    $cached = get_transient($cache_key);
    // if (is_array($cached) && isset($cached['garden_key'])) {
    //     wp_send_json_success($cached);
    // }
    $cooldown = get_transient($cooldown_key);
    // if (is_array($cooldown) && isset($cooldown['message'])) {
    //     wp_send_json_error($cooldown);
    // }

    $rack_configs = function_exists('aitrongcay_get_rack_monitor_configs') ? aitrongcay_get_rack_monitor_configs($garden_key) : [];
    $target_rack = $rack_configs[$req_rack_index] ?? null;
    if (!$target_rack && !empty($rack_configs)) {
        $target_rack = $rack_configs[0];
    }

    $cfg = aitrongcay_blynk_runtime_config($garden_key, $target_rack);
    $base = (string) ($cfg['base'] ?? 'https://blynk.cloud/external/api');
    $vpins = (array) ($cfg['vpins'] ?? []);

    if ($target_rack) {
        $rack_token = trim((string) ($target_rack['blynk_auth_token'] ?? ''));
        if ($rack_token !== '') {
            $cfg['token'] = $rack_token;
        }
        $trays = (array) ($target_rack['trays'] ?? []);
        
        // Map all lights from the rack's trays
        foreach ($trays as $idx => $tray) {
            $light_key = 'light' . ($idx + 1);
            $vpin = strtoupper(trim((string) ($tray['vpin_light'] ?? '')));
            if ($vpin !== '') {
                $vpins[$light_key] = $vpin;
            }
        }

        $target_tray = $trays[$req_tray_index] ?? null;
        if (!$target_tray && !empty($trays)) {
            $target_tray = $trays[0];
        }
        if ($target_tray) {
            $base = trim((string) ($target_tray['blynk_base'] ?? $base)) ?: $base;
            // Map tray vpins → dashboard vpins
            foreach (['temp', 'hum', 'soil', 'ph', 'ec'] as $key) {
                $vpin = strtoupper(trim((string) ($target_tray['vpin_' . $key] ?? '')));
                if ($vpin !== '') {
                    $vpins[$key] = $vpin;
                }
            }
            $pump_vpin = strtoupper(trim((string) ($target_tray['vpin_pump'] ?? '')));
            if ($pump_vpin !== '') {
                $vpins['pump'] = $pump_vpin;
            }
            // For backward compatibility if any older code expects 'light'
            $vpins['light'] = $vpins['light' . ($req_tray_index + 1)] ?? '';
        }
    }

    $payload = ['garden_key' => $garden_key];
    $shared_vpins = [];
    foreach (['temp', 'hum', 'soil', 'pump'] as $device) {
        if (! empty($vpins[$device])) {
            $shared_vpins[$device] = (string) $vpins[$device];
        }
    }

    $shared_data = aitrongcay_blynk_read_values((string) ($cfg['token'] ?? ''), array_values($shared_vpins), $base);
    foreach ($shared_vpins as $device => $vpin) {
        $value = $shared_data[$vpin] ?? null;
        if (in_array($device, ['temp', 'hum', 'soil'], true)) {
            $payload[$device] = $value !== null ? (float) $value : null;
        } else {
            $payload[$device] = $value !== null ? (int) $value : null;
        }
    }

    $light_devices = [];
    foreach ($vpins as $key => $v) {
        if (strpos($key, 'light') === 0 && $v !== '') {
            $light_devices[] = $key;
        }
    }
    $has_any_light = false;
    $light_requests = [];
    foreach ($light_devices as $device) {
        $vpin = (string) ($vpins[$device] ?? '');
        if ($vpin === '') {
            $payload[$device] = null;
            continue;
        }

        $token = trim((string) ($cfg['token'] ?? ''));
        $pot_specific = aitrongcay_blynk_pot_token_for_device($garden_key, $device);
        if ($pot_specific !== '' && $pot_specific !== aitrongcay_blynk_effective_token($garden_key)) {
            $token = $pot_specific;
        }
        if ($token === '') {
            $payload[$device] = null;
            continue;
        }

        if (! isset($light_requests[$token])) {
            $light_requests[$token] = [
                'vpins' => [],
                'devices' => [],
            ];
        }
        $light_requests[$token]['vpins'][] = $vpin;
        $light_requests[$token]['devices'][$device] = $vpin;
    }

    foreach ($light_requests as $token => $request) {
        $light_data = aitrongcay_blynk_read_values((string) $token, array_values(array_unique((array) ($request['vpins'] ?? []))), $base);
        foreach ((array) ($request['devices'] ?? []) as $device => $vpin) {
            $value = $light_data[$vpin] ?? null;
            $payload[$device] = $value !== null ? (int) $value : null;
            if ($value !== null) {
                $has_any_light = true;
            }
        }
    }

    if ($shared_data === [] && ! $has_any_light) {
        set_transient($cooldown_key, ['message' => 'Blynk đang giới hạn quota hoặc chưa phản hồi, tạm ngưng gọi lại trong ít phút.'], 300);
        wp_send_json_error(['message' => 'Không đọc được dữ liệu Blynk.'], 502);
    }

    delete_transient($cooldown_key);
    set_transient($cache_key, $payload, 60);
    wp_send_json_success($payload);
}
add_action('wp_ajax_aitrongcay_blynk_get_status', 'aitrongcay_blynk_get_status_ajax');

function aitrongcay_blynk_send_control(string $device, int $state, string $garden_key = '', string $pot_code = '', int $req_rack_index = -1, int $req_tray_index = -1)
{
    if (! in_array($state, [0, 1], true)) {
        return new WP_Error('invalid_command', 'Lệnh điều khiển không hợp lệ.');
    }

    $cfg = aitrongcay_blynk_runtime_config($garden_key);

    // Fallback: đọc từ rack monitor config
    if (function_exists('aitrongcay_get_rack_monitor_configs')) {
        $rack_configs = aitrongcay_get_rack_monitor_configs($garden_key);
        
        $target_rack_id = 0;
        $target_tray_index = -1;

        if ($pot_code !== '' && function_exists('aitrongcay_get_rack_slots')) {
            $slots = aitrongcay_get_rack_slots($garden_key);
            foreach ($slots as $slot) {
                if (trim((string) ($slot['pot_code'] ?? '')) === $pot_code) {
                    $target_rack_id = (int) ($slot['rack_id'] ?? 0);
                    $target_tray_index = (int) ($slot['slot_index'] ?? 0) - 1;
                    break;
                }
            }
        }

        $target_rack = null;
        if ($req_rack_index >= 0 && $req_tray_index >= 0 && isset($rack_configs[$req_rack_index])) {
            $target_rack = $rack_configs[$req_rack_index];
            $target_tray_index = $req_tray_index;
        } else if ($target_rack_id > 0 && $target_tray_index >= 0) {
            foreach ($rack_configs as $rack) {
                if ((int)($rack['rack_id'] ?? 0) === $target_rack_id) {
                    $target_rack = $rack;
                    break;
                }
            }
        }

        if ($target_rack) {
            $rack_token = trim((string) ($target_rack['blynk_auth_token'] ?? ''));
            if ($rack_token !== '') {
                $cfg['token'] = $rack_token;
                if (isset($target_rack['trays'][$target_tray_index])) {
                    $tray = $target_rack['trays'][$target_tray_index];
                    if ($device === 'pump') {
                        $vpin = strtoupper(trim((string) ($tray['vpin_pump'] ?? '')));
                        if ($vpin !== '') $cfg['vpins']['pump'] = $vpin;
                    } else if (strpos($device, 'light') !== false) {
                        $vpin = strtoupper(trim((string) ($tray['vpin_light'] ?? '')));
                        if ($vpin !== '') $cfg['vpins'][$device] = $vpin;
                    }
                }
            }
        } else if (trim((string) ($cfg['token'] ?? '')) === '') {
            foreach ($rack_configs as $rack) {
                $rack_token = trim((string) ($rack['blynk_auth_token'] ?? ''));
                if ($rack_token === '') {
                    continue;
                }
                foreach ((array) ($rack['trays'] ?? []) as $tray) {
                    $cfg['token'] = $rack_token;
                    foreach (['temp', 'hum', 'soil', 'ph', 'ec', 'pump'] as $key) {
                        $vpin = strtoupper(trim((string) ($tray['vpin_' . $key] ?? '')));
                        if ($vpin !== '' && empty($cfg['vpins'][$key])) {
                            $cfg['vpins'][$key] = $vpin;
                        }
                    }
                    $light_vpin = strtoupper(trim((string) ($tray['vpin_light'] ?? '')));
                    if ($light_vpin !== '') {
                        if (empty($cfg['vpins']['light1'])) {
                            $cfg['vpins']['light1'] = $light_vpin;
                        }
                        if (empty($cfg['vpins']['light'])) {
                            $cfg['vpins']['light'] = $light_vpin;
                        }
                    }
                    break 2;
                }
            }
        }
    }

    $vpin = (string) ($cfg['vpins'][$device] ?? '');
    if ($vpin === '') {
        return new WP_Error('invalid_command', 'Thiết bị này chưa được map cho khu vườn đang chọn.');
    }

    $token = trim((string) ($cfg['token'] ?? ''));
    if ($device !== 'pump') {
        $pot_specific = aitrongcay_blynk_pot_token_for_device($garden_key, $device);
        if ($pot_specific !== '' && $pot_specific !== aitrongcay_blynk_effective_token($garden_key)) {
            $token = $pot_specific;
        }
    }
    if ($token === '') {
        return new WP_Error('missing_token', 'Thiết bị này chưa có Blynk token thật để gửi lệnh.');
    }

    $url = add_query_arg([
        'token' => $token,
        $vpin => $state,
    ], untrailingslashit((string) ($cfg['base'] ?? 'https://blynk.cloud/external/api')) . '/update');

    $response = wp_remote_get($url, ['timeout' => 10]);
    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = (string) wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    if ($code < 200 || $code >= 300) {
        $message = is_array($data) && ! empty($data['error']['message'])
            ? (string) $data['error']['message']
            : ('Blynk phản hồi HTTP ' . $code);
        return new WP_Error('blynk_http_error', $message);
    }
    if (is_array($data) && ! empty($data['error']['message'])) {
        return new WP_Error('blynk_api_error', (string) $data['error']['message']);
    }

    delete_transient('aitr_blynk_status_' . md5($garden_key !== '' ? $garden_key : 'default'));
    return true;
}

function aitrongcay_blynk_control_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key === '') {
        $garden_key = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key(wp_get_current_user()) : '';
    }
    if (! aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
        wp_send_json_error(['message' => 'Anh/chị chỉ có quyền xem khu vườn này.'], 403);
    }

    $device = sanitize_key((string) ($_POST['device'] ?? ''));
    $state = isset($_POST['state']) ? (int) $_POST['state'] : -1;
    $pot_code = sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''));
    $req_rack_index = isset($_POST['rack_index']) && $_POST['rack_index'] !== '' ? (int) $_POST['rack_index'] : -1;
    $req_tray_index = isset($_POST['tray_index']) && $_POST['tray_index'] !== '' ? (int) $_POST['tray_index'] : -1;

    $result = aitrongcay_blynk_send_control($device, $state, $garden_key, $pot_code, $req_rack_index, $req_tray_index);
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()], 400);
    }

    wp_send_json_success(['device' => $device, 'state' => $state]);
}
add_action('wp_ajax_aitrongcay_blynk_control', 'aitrongcay_blynk_control_ajax');

function aitrongcay_blynk_control_direct_url(string $device, int $state): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_blynk_control_direct',
        'device' => $device,
        'state' => $state,
        'redirect_to' => home_url('/portal/dashboard-2/'),
    ], admin_url('admin-post.php'));

    return wp_nonce_url($url, 'aitrongcay_blynk_control_direct');
}

function aitrongcay_blynk_control_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    check_admin_referer('aitrongcay_blynk_control_direct');

    $device = sanitize_key((string) ($_GET['device'] ?? $_POST['device'] ?? ''));
    $garden_key = sanitize_text_field((string) ($_GET['garden_key'] ?? $_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key(wp_get_current_user())));
    $state = isset($_GET['state']) ? (int) $_GET['state'] : (isset($_POST['state']) ? (int) $_POST['state'] : -1);

    $result = aitrongcay_blynk_send_control($device, $state, $garden_key);

    $redirect = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/');
    if (is_wp_error($result)) {
        $redirect = add_query_arg('blynk_ctrl', rawurlencode($result->get_error_message()), $redirect);
    } else {
        $redirect = add_query_arg('blynk_ctrl', rawurlencode('ok:' . $device . ':' . $state), $redirect);
    }

    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_aitrongcay_blynk_control_direct', 'aitrongcay_blynk_control_direct_submit');

function aitrongcay_capture_photo_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key()));
    $pot_code = strtoupper(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? '')));
    if ($garden_key === '' || $pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu thông tin khoang để lưu ảnh.'], 400);
    }
    if (! aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
        wp_send_json_error(['message' => 'Không có quyền chụp ảnh cho khu vườn này.'], 403);
    }

    $image_data = (string) wp_unslash($_POST['image'] ?? '');
    if (! preg_match('#^data:image/(png|jpeg);base64,#', $image_data, $matches)) {
        wp_send_json_error(['message' => 'Không đọc được ảnh chụp.'], 400);
    }

    $binary = base64_decode(substr($image_data, strpos($image_data, ',') + 1), true);
    if ($binary === false) {
        wp_send_json_error(['message' => 'Dữ liệu ảnh không hợp lệ.'], 400);
    }

    $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
    $filename = 'livecam-' . strtolower($pot_code) . '-' . wp_date('Ymd-His') . '.' . $extension;
    $uploaded = wp_upload_bits($filename, null, $binary);
    if (! empty($uploaded['error'])) {
        wp_send_json_error(['message' => $uploaded['error']], 500);
    }

    $filetype = wp_check_filetype($uploaded['file'], null);
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => $filetype['type'] ?: 'image/' . $extension,
        'post_title' => 'Ảnh ' . $pot_code . ' · ' . current_time('H:i d/m/Y'),
        'post_status' => 'inherit',
        'post_author' => get_current_user_id(),
    ], $uploaded['file']);

    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_send_json_error(['message' => 'Không tạo được attachment.'], 500);
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
    wp_update_attachment_metadata($attachment_id, $attach_data);
    update_post_meta($attachment_id, '_aitrongcay_photo_owner', get_current_user_id());
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', $garden_key);
    update_post_meta($attachment_id, '_aitrongcay_photo_source', 'livecam');
    update_post_meta($attachment_id, '_aitrongcay_pot_code', $pot_code);

    $updated_latest = aitrongcay_set_latest_pot_photo($garden_key, $pot_code, (int) $attachment_id);
    $preview_url = aitrongcay_landscape_preview_url((int) $attachment_id);
    if ($preview_url === '') {
        $preview_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: wp_get_attachment_url($attachment_id)));
    }

    wp_send_json_success([
        'id' => $attachment_id,
        'url' => $preview_url,
        'download' => wp_make_link_relative((string) wp_get_attachment_url($attachment_id)),
        'label' => get_the_title($attachment_id),
        'pot_code' => $pot_code,
        'updated_latest' => (bool) $updated_latest,
    ]);
}
add_action('wp_ajax_aitrongcay_capture_photo', 'aitrongcay_capture_photo_ajax');

/**
 * Server-side capture: PHP gọi go2rtc lấy JPEG, fallback sang timelapse mới nhất.
 * Tránh CORS khi browser không thể drawImage từ video cross-origin.
 */
function aitrongcay_capture_photo_server_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key()));
    $pot_code   = strtoupper(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? '')));

    if ($garden_key === '' || $pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu thông tin khoang.'], 400);
    }
    if (! aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
        wp_send_json_error(['message' => 'Không có quyền chụp ảnh.'], 403);
    }

    $binary = null;
    $source = 'livecam';

    $robot_stream = sanitize_text_field((string) wp_unslash($_POST['robot_stream'] ?? ''));

    // 1. Nếu có URL camera cố định của robot truyền lên, ưu tiên chụp thẳng từ link này
    if ($robot_stream !== '') {
        // Dùng cURL để lấy ảnh tĩnh từ luồng (bỏ qua các hàm parse phức tạp)
        $ch = curl_init($robot_stream);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $robot_img = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && !empty($robot_img)) {
            $binary = $robot_img;
            $source = 'robot';
        }
    }

    // 2. Thử lấy frame live từ cấu hình của từng Khoang (nếu không dùng camera robot)
    if ($binary === null) {
        $webcam = function_exists('aitrongcay_resolve_pot_webcam_info')
            ? aitrongcay_resolve_pot_webcam_info($garden_key, $pot_code)
            : [];

        if (! empty($webcam['slug']) && ! empty($webcam['base_url'])) {
            $live = function_exists('aitrongcay_fetch_live_frame')
                ? aitrongcay_fetch_live_frame($webcam['base_url'], $webcam['slug'])
                : null;
            if ($live !== null) {
                $binary = $live;
            }
        }
    }

    // 2. Fallback: đọc file timelapse mới nhất từ disk
    if ($binary === null && ! empty($webcam['slug'])) {
        $tl = function_exists('aitrongcay_get_latest_timelapse_for_pot')
            ? aitrongcay_get_latest_timelapse_for_pot($garden_key, $webcam['slug'])
            : null;
        if ($tl !== null && ! empty($tl['path']) && file_exists($tl['path'])) {
            $binary = @file_get_contents($tl['path']);
            $source = 'timelapse';
        }
    }

    if (! $binary) {
        wp_send_json_error(['message' => 'Không lấy được ảnh. Kiểm tra go2rtc hoặc cấu hình webcam của khoang.'], 500);
    }

    $filename = 'capture-' . strtolower($pot_code) . '-' . wp_date('Ymd-His', null, new DateTimeZone('Asia/Ho_Chi_Minh')) . '.jpg';
    $uploaded = wp_upload_bits($filename, null, $binary);
    if (! empty($uploaded['error'])) {
        wp_send_json_error(['message' => $uploaded['error']], 500);
    }

    $attachment_id = wp_insert_attachment([
        'post_mime_type' => 'image/jpeg',
        'post_title'     => 'Ảnh ' . $pot_code . ' · ' . wp_date('H:i d/m/Y', null, new DateTimeZone('Asia/Ho_Chi_Minh')),
        'post_status'    => 'inherit',
        'post_author'    => get_current_user_id(),
    ], $uploaded['file']);

    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_send_json_error(['message' => 'Không tạo được attachment.'], 500);
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $uploaded['file']));
    update_post_meta($attachment_id, '_aitrongcay_photo_owner',     get_current_user_id());
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', $garden_key);
    update_post_meta($attachment_id, '_aitrongcay_photo_source',    $source);
    update_post_meta($attachment_id, '_aitrongcay_pot_code',        $pot_code);

    aitrongcay_set_latest_pot_photo($garden_key, $pot_code, (int) $attachment_id);

    $url = wp_make_link_relative(
        (string) (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: wp_get_attachment_url($attachment_id))
    );

    wp_send_json_success([
        'id'      => $attachment_id,
        'url'     => $url,
        'label'   => get_the_title($attachment_id),
        'pot_code' => $pot_code,
        'source'  => $source,
    ]);
}
add_action('wp_ajax_aitrongcay_capture_photo_server', 'aitrongcay_capture_photo_server_ajax');

function aitrongcay_generate_demo_snapshot_attachment(int $user_id)
{
    if (! function_exists('imagecreatetruecolor')) {
        return new WP_Error('gd_missing', 'Server chưa bật GD để tạo ảnh demo.');
    }

    $width = 1600;
    $height = 900;
    $image = imagecreatetruecolor($width, $height);
    if (! $image) {
        return new WP_Error('canvas_failed', 'Không tạo được canvas ảnh demo.');
    }

    $bg = imagecolorallocate($image, 236, 245, 238);
    $green = imagecolorallocate($image, 31, 107, 69);
    $green2 = imagecolorallocate($image, 123, 196, 127);
    $white = imagecolorallocate($image, 255, 255, 255);
    $dark = imagecolorallocate($image, 26, 43, 36);

    imagefilledrectangle($image, 0, 0, $width, $height, $bg);
    imagefilledrectangle($image, 80, 90, 1520, 810, $white);
    imagefilledrectangle($image, 80, 90, 1520, 240, $green);
    imagefilledellipse($image, 420, 470, 420, 420, $green2);
    imagefilledellipse($image, 760, 500, 300, 300, $green);
    imagefilledellipse($image, 1080, 450, 360, 360, $green2);
    imagefilledrectangle($image, 160, 620, 1440, 700, $green);
    imagestring($image, 5, 130, 130, 'Ai trong cay - Snapshot demo', $white);
    imagestring($image, 4, 130, 275, 'Vuon dang duoc theo doi trong portal', $dark);
    imagestring($image, 4, 130, 315, 'Anh nay duoc tao boi server de demo luong chup anh on dinh', $dark);
    imagestring($image, 4, 130, 355, 'Sau nay co the doi sang snapshot that khi livecam cung domain', $dark);

    ob_start();
    imagepng($image);
    $contents = ob_get_clean();
    imagedestroy($image);

    if (! is_string($contents) || $contents === '') {
        return new WP_Error('render_failed', 'Không render được ảnh demo.');
    }

    $filename = 'anh-vuon-' . $user_id . '-' . wp_generate_password(8, false, false) . '.png';
    $uploaded = wp_upload_bits($filename, null, $contents);
    if (! empty($uploaded['error'])) {
        return new WP_Error('upload_failed', (string) $uploaded['error']);
    }

    $attachment_id = wp_insert_attachment([
        'post_mime_type' => 'image/png',
        'post_title' => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
        'post_status' => 'inherit',
        'post_author' => $user_id,
    ], $uploaded['file']);

    if (is_wp_error($attachment_id) || ! $attachment_id) {
        return new WP_Error('attachment_failed', 'Không tạo được attachment demo.');
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded['file']);
    wp_update_attachment_metadata($attachment_id, $attach_data);
    update_post_meta($attachment_id, '_aitrongcay_photo_owner', $user_id);
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', aitrongcay_primary_garden_key_for_user(get_user_by('id', $user_id) ?: null));
    update_post_meta($attachment_id, '_aitrongcay_photo_source', 'livecam');

    return [
        'id' => $attachment_id,
        'url' => wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: wp_get_attachment_url($attachment_id))),
        'label' => get_the_title($attachment_id),
    ];
}

function aitrongcay_capture_demo_photo_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $result = aitrongcay_generate_demo_snapshot_attachment(get_current_user_id());
    if (is_wp_error($result)) {
        wp_send_json_error(['message' => $result->get_error_message()], 500);
    }
    wp_send_json_success($result);
}
add_action('wp_ajax_aitrongcay_capture_demo_photo', 'aitrongcay_capture_demo_photo_ajax');

function aitrongcay_capture_demo_photo_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    check_admin_referer('aitrongcay_capture_demo_photo_submit', 'aitrongcay_capture_demo_nonce');
    $result = aitrongcay_generate_demo_snapshot_attachment(get_current_user_id());
    $redirect = home_url('/portal/dashboard-2/#photo-library');
    if (! is_wp_error($result) && ! empty($result['id'])) {
        $redirect = add_query_arg('photo_added', (string) $result['id'], home_url('/portal/dashboard-2/')) . '#photo-library';
    }
    wp_safe_redirect($redirect);
    exit;
}
add_action('admin_post_aitrongcay_capture_demo_photo_submit', 'aitrongcay_capture_demo_photo_submit');

function aitrongcay_create_public_livecam_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $token = wp_generate_password(24, false, false);
    update_user_meta(get_current_user_id(), '_aitrongcay_livecam_public_token', $token);

    wp_send_json_success([
        'token' => $token,
        'url' => add_query_arg('share_token', rawurlencode($token), home_url('/portal/webcam/')),
    ]);
}
add_action('wp_ajax_aitrongcay_create_public_livecam', 'aitrongcay_create_public_livecam_ajax');

function aitrongcay_disable_public_livecam_ajax(): void
{
    aitrongcay_require_portal_nonce();
    delete_user_meta(get_current_user_id(), '_aitrongcay_livecam_public_token');
    wp_send_json_success(['disabled' => true]);
}
add_action('wp_ajax_aitrongcay_disable_public_livecam', 'aitrongcay_disable_public_livecam_ajax');

function aitrongcay_market_structured_fields_schema(): array
{
    return [
        'category' => ['meta_key' => '_aitrongcay_market_category', 'type' => 'text'],
        'offer_type' => ['meta_key' => '_aitrongcay_market_offer_type', 'type' => 'text'],
        'quantity' => ['meta_key' => '_aitrongcay_market_quantity', 'type' => 'text'],
        'area' => ['meta_key' => '_aitrongcay_market_area', 'type' => 'text'],
        'availability' => ['meta_key' => '_aitrongcay_market_availability', 'type' => 'text'],
        'contact_text' => ['meta_key' => '_aitrongcay_market_contact_text', 'type' => 'text'],
    ];
}

function aitrongcay_get_market_structured_data(int $post_id): array
{
    static $cache = [];

    if (isset($cache[$post_id])) {
        return $cache[$post_id];
    }

    $all_meta = get_post_meta($post_id);
    $data = [];
    foreach (aitrongcay_market_structured_fields_schema() as $field => $config) {
        $meta_key = (string) $config['meta_key'];
        $raw = $all_meta[$meta_key][0] ?? '';
        $data[$field] = trim(is_string($raw) ? $raw : '');
    }

    $cache[$post_id] = $data;
    return $data;
}

function aitrongcay_save_market_structured_data(int $post_id, array $source): void
{
    foreach (aitrongcay_market_structured_fields_schema() as $field => $config) {
        $value = sanitize_text_field((string) wp_unslash($source[$field] ?? ''));
        if ($value === '') {
            delete_post_meta($post_id, (string) $config['meta_key']);
        } else {
            update_post_meta($post_id, (string) $config['meta_key'], $value);
        }
    }
}

function aitrongcay_market_summary_line(array $data): string
{
    $parts = array_values(array_filter([
        trim((string) ($data['offer_type'] ?? '')),
        trim((string) ($data['quantity'] ?? '')),
        trim((string) ($data['area'] ?? '')),
    ]));
    return implode(' • ', $parts);
}

function aitrongcay_migrate_market_structured_meta(): void
{
    if ((string) get_option('aitrongcay_market_structured_meta_version', '') === '1') {
        return;
    }

    $posts = get_posts([
        'post_type' => 'aitr_market_post',
        'post_status' => 'publish',
        'posts_per_page' => 200,
        'orderby' => 'date',
        'order' => 'DESC',
        'fields' => 'ids',
    ]);

    foreach ($posts as $post_id) {
        $post_id = (int) $post_id;
        $content = wp_strip_all_tags((string) get_post_field('post_content', $post_id));
        $title = (string) get_post_field('post_title', $post_id);
        $existing = aitrongcay_get_market_structured_data($post_id);

        if (($existing['offer_type'] ?? '') === '') {
            foreach (['Bán', 'Trao đổi', 'Chia sẻ', 'Nhận đặt trước'] as $offer) {
                if (str_contains(mb_strtolower($title . ' ' . $content), mb_strtolower($offer))) {
                    update_post_meta($post_id, '_aitrongcay_market_offer_type', $offer);
                    break;
                }
            }
        }

        if (($existing['category'] ?? '') === '') {
            $map = [
                'Hạt giống' => ['hạt', 'hạt giống'],
                'Cây giống' => ['cây giống', 'cây con'],
                'Dinh dưỡng cho cây' => ['dinh dưỡng', 'phân', 'giá thể'],
                'Các loại rau' => ['rau', 'xà lách', 'cải', 'rau muống'],
                'Hoa' => ['hoa', 'nụ', 'cúc'],
            ];
            foreach ($map as $category => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains(mb_strtolower($title . ' ' . $content), mb_strtolower($keyword))) {
                        update_post_meta($post_id, '_aitrongcay_market_category', $category);
                        break 2;
                    }
                }
            }
        }
    }

    update_option('aitrongcay_market_structured_meta_version', '1', false);
}
add_action('init', 'aitrongcay_migrate_market_structured_meta', 40);

function aitrongcay_upload_market_photo_ajax(): void
{
    aitrongcay_require_portal_nonce();

    if (empty($_FILES['market_photo']) || ! is_array($_FILES['market_photo'])) {
        wp_send_json_error(['message' => 'Chưa nhận được ảnh tải lên.'], 400);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload('market_photo', 0);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_send_json_error(['message' => 'Không tải được ảnh lên hệ thống.'], 500);
    }

    update_post_meta($attachment_id, '_aitrongcay_photo_owner', get_current_user_id());
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', aitrongcay_resolve_active_garden_key());

    wp_send_json_success([
        'id' => $attachment_id,
        'url' => wp_get_attachment_image_url($attachment_id, 'medium_large') ?: wp_get_attachment_url($attachment_id),
        'title' => get_the_title($attachment_id),
    ]);
}
add_action('wp_ajax_aitrongcay_upload_market_photo', 'aitrongcay_upload_market_photo_ajax');

function aitrongcay_create_market_post_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $title = sanitize_text_field((string) wp_unslash($_POST['title'] ?? ''));
    $content = wp_kses_post((string) wp_unslash($_POST['content'] ?? ''));
    $photo_ids = array_map('absint', (array) ($_POST['photo_ids'] ?? []));
    $photo_ids = array_values(array_filter($photo_ids));

    if ($title === '' || $content === '') {
        wp_send_json_error(['message' => 'Thiếu tiêu đề hoặc nội dung tin đăng.'], 400);
    }



    $post_id = wp_insert_post([
        'post_type' => 'aitr_market_post',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_content' => $content,
        'post_author' => get_current_user_id(),
        'comment_status' => 'open',
    ], true);

    if (is_wp_error($post_id) || ! $post_id) {
        wp_send_json_error(['message' => 'Không tạo được tin Chợ quê.'], 500);
    }

    $market_garden_key = aitrongcay_resolve_active_garden_key();
    update_post_meta($post_id, '_aitrongcay_market_garden_key', $market_garden_key);
    aitrongcay_save_market_structured_data($post_id, $_POST);

    if ($photo_ids) {
        set_post_thumbnail($post_id, $photo_ids[0]);
        update_post_meta($post_id, '_aitrongcay_market_gallery', $photo_ids);
    }

    wp_send_json_success([
        'id' => $post_id,
        'url' => add_query_arg('created_post', (string) $post_id, home_url('/cho-que/#market-drafts')),
        'title' => get_the_title($post_id),
        'structured' => aitrongcay_get_market_structured_data($post_id),
    ]);
}
add_action('wp_ajax_aitrongcay_create_market_post', 'aitrongcay_create_market_post_ajax');

function aitrongcay_update_market_post_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $post_id = absint($_POST['post_id'] ?? 0);
    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post' || (int) $post->post_author !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Không có quyền sửa tin này.'], 403);
    }

    $title = sanitize_text_field((string) wp_unslash($_POST['title'] ?? ''));
    $content = wp_kses_post((string) wp_unslash($_POST['content'] ?? ''));
    $new_photo_ids = array_map('absint', (array) ($_POST['photo_ids'] ?? []));
    $new_photo_ids = array_values(array_filter($new_photo_ids));
    $existing_photo_ids = array_map('absint', (array) ($_POST['existing_photo_ids'] ?? []));
    $existing_photo_ids = array_values(array_filter($existing_photo_ids));

    if ($title === '' || $content === '') {
        wp_send_json_error(['message' => 'Anh/chị vui lòng nhập đủ tiêu đề và nội dung tin đăng.'], 400);
    }


    $final_photo_ids = array_values(array_filter(array_merge($existing_photo_ids, $new_photo_ids)));

    wp_update_post(['ID' => $post_id, 'post_title' => $title, 'post_content' => $content]);
    aitrongcay_save_market_structured_data($post_id, $_POST);

    $market_garden_key = trim((string) get_post_meta($post_id, '_aitrongcay_market_garden_key', true));
    if ($market_garden_key === '') {
        update_post_meta($post_id, '_aitrongcay_market_garden_key', aitrongcay_primary_garden_key_for_user(get_user_by('id', (int) $post->post_author) ?: null));
    }

    if ($final_photo_ids) {
        set_post_thumbnail($post_id, $final_photo_ids[0]);
        update_post_meta($post_id, '_aitrongcay_market_gallery', $final_photo_ids);
    } else {
        delete_post_thumbnail($post_id);
        delete_post_meta($post_id, '_aitrongcay_market_gallery');
    }

    $gallery_items = [];
    foreach ($final_photo_ids as $attachment_id) {
        $url = (string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id));
        if (! $url) {
            continue;
        }
        $gallery_items[] = [
            'id' => $attachment_id,
            'url' => wp_make_link_relative($url),
            'title' => get_the_title($attachment_id),
        ];
    }

    $thumb_url = has_post_thumbnail($post_id) ? (string) get_the_post_thumbnail_url($post_id, 'large') : '';
    $gallery_url = ($gallery_items && ! empty($gallery_items[0]['url'])) ? (string) $gallery_items[0]['url'] : '';
    $image_url = $thumb_url ? wp_make_link_relative($thumb_url) : $gallery_url;

    wp_send_json_success([
        'id' => $post_id,
        'title' => $title,
        'content' => wp_strip_all_tags($content),
        'imageUrl' => $image_url ?: '',
        'gallery' => $gallery_items,
        'structured' => aitrongcay_get_market_structured_data($post_id),
        'summaryLine' => aitrongcay_market_summary_line(aitrongcay_get_market_structured_data($post_id)),
    ]);
}
add_action('wp_ajax_aitrongcay_update_market_post', 'aitrongcay_update_market_post_ajax');

function aitrongcay_delete_market_post_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $post_id = absint($_POST['post_id'] ?? 0);
    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post' || (int) $post->post_author !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Không có quyền xóa tin này.'], 403);
    }
    wp_trash_post($post_id);
    wp_send_json_success(['id' => $post_id]);
}
add_action('wp_ajax_aitrongcay_delete_market_post', 'aitrongcay_delete_market_post_ajax');

function aitrongcay_normalize_phone_digits(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?: '';
    if ($digits === '') {
        return '';
    }
    if (str_starts_with($digits, '84')) {
        return $digits;
    }
    if (str_starts_with($digits, '0')) {
        return '84' . substr($digits, 1);
    }
    // Trường hợp người dùng gõ thiếu số 0 ở đầu (VD: 369497545)
    // Số điện thoại di động VN sau khi bỏ số 0 có 9 chữ số.
    if (strlen($digits) === 9) {
        return '84' . $digits;
    }
    return $digits;
}

function aitrongcay_user_zalo_phone(int $user_id): string
{
    if ($user_id <= 0) {
        return '';
    }
    $phone = (string) get_user_meta($user_id, 'aitrongcay_phone', true);
    return aitrongcay_normalize_phone_digits($phone);
}

function aitrongcay_market_zalo_link_for_post(int $post_id): string
{
    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post') {
        return '';
    }
    
    // Ưu tiên lấy số điện thoại người dùng điền trong form bài đăng (trường Liên hệ)
    $structured = function_exists('aitrongcay_get_market_structured_data') ? aitrongcay_get_market_structured_data($post_id) : [];
    $post_contact = (string) ($structured['contact_text'] ?? '');
    $post_phone = aitrongcay_normalize_phone_digits($post_contact);
    
    // Nếu trong bài có số điện thoại hợp lệ, dùng số đó. Nếu không, lấy số từ Profile của tài khoản.
    if (strlen($post_phone) >= 9) {
        $phone = $post_phone;
    } else {
        $phone = aitrongcay_user_zalo_phone((int) $post->post_author);
    }

    if ($phone === '' || strlen($phone) < 9) {
        return '';
    }
    return 'https://zalo.me/' . rawurlencode($phone);
}

function aitrongcay_market_zalo_action_url(int $post_id): string
{
    $post_id = absint($post_id);
    if ($post_id <= 0) {
        return home_url('/cho-que/');
    }
    return add_query_arg('aitrongcay_market_zalo', (string) $post_id, home_url('/cho-que/'));
}

function aitrongcay_notify_plant_stage_change(string $garden_key, string $pot_code, string $plant_name, string $old_stage, string $new_stage): void
{
    $owner = function_exists('aitrongcay_get_garden_owner_user') ? aitrongcay_get_garden_owner_user($garden_key) : null;
    $user_id = $owner instanceof WP_User ? (int) $owner->ID : 0;
    
    if ($user_id <= 0) return;
    $user = get_userdata($user_id);
    if (!$user) return;

    $to = $user->user_email;
    if (empty($to)) return;

    $subject = "[Ai Trồng Cây] Thông báo: Cây {$plant_name} đã chuyển sang giai đoạn {$new_stage}";
    $message = "Chào {$user->display_name},\n\n";
    $message .= "Hệ thống AI vừa phân tích ảnh mới nhất từ khoang {$pot_code} và phát hiện cây {$plant_name} của bạn đã có sự phát triển mới. Cụ thể, cây đã chuyển từ giai đoạn '{$old_stage}' sang '{$new_stage}'.\n\n";
    $message .= "Xin chúc mừng! Hãy truy cập hệ thống để xem nhật ký ảnh và nhận các gợi ý chăm sóc mới nhất từ trợ lý AI Cindy nhé.\n\n";
    $message .= "Đăng nhập ngay: " . home_url('/portal/dashboard-2/') . "\n";

    wp_mail($to, $subject, $message);
    
    // Zalo ZNS API Stub
    $phone = function_exists('aitrongcay_user_zalo_phone') ? aitrongcay_user_zalo_phone($user_id) : '';
    if ($phone !== '') {
        error_log("ZALO_ZNS_STUB: Gui tin nhan toi {$phone} - {$subject}");
    }
}


add_action('template_redirect', static function (): void {
    $post_id = absint($_GET['aitrongcay_market_zalo'] ?? 0);
    if ($post_id <= 0) {
        return;
    }

    $url = aitrongcay_market_zalo_link_for_post($post_id);
    if ($url === '') {
        wp_safe_redirect(home_url('/cho-que/'));
        exit;
    }

    wp_redirect($url, 302, 'AiTrongCay');
    exit;
}, 1);

function aitrongcay_get_market_zalo_link_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $post_id = absint($_POST['post_id'] ?? 0);
    $url = aitrongcay_market_zalo_link_for_post($post_id);
    if ($url === '') {
        wp_send_json_error(['message' => 'Tin đăng này chưa có Zalo sẵn sàng để liên hệ.'], 404);
    }
    wp_send_json_success([
        'url' => $url,
        'post_id' => $post_id,
        'mode' => 'zalo-deeplink',
    ]);
}
add_action('wp_ajax_aitrongcay_get_market_zalo_link', 'aitrongcay_get_market_zalo_link_ajax');
add_action('wp_ajax_nopriv_aitrongcay_get_market_zalo_link', 'aitrongcay_get_market_zalo_link_ajax');

function aitrongcay_custom_pots_meta_key(): string
{
    return '_aitrongcay_custom_pots_by_garden';
}

function aitrongcay_get_custom_pots(string $garden_key, int $user_id): array
{
    static $cache = [];
    if ($garden_key === '') {
        return [];
    }

    $cache_key = $garden_key . '|' . $user_id;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $db_pots = function_exists('aitrongcay_get_db_pots') ? aitrongcay_get_db_pots($garden_key) : [];
    if ($db_pots) {
        $cache[$cache_key] = array_map(static function (array $pot): array {
            $analysis_actions = [];
            $analysis_actions_raw = (string) ($pot['latest_analysis_actions'] ?? '');
            if ($analysis_actions_raw !== '') {
                $decoded = json_decode($analysis_actions_raw, true);
                if (is_array($decoded)) {
                    $analysis_actions = array_values(array_filter(array_map('strval', $decoded)));
                }
            }
            $analysis_escalate = [];
            $analysis_escalate_raw = (string) ($pot['latest_analysis_escalate'] ?? '');
            if ($analysis_escalate_raw !== '') {
                $decoded_escalate = json_decode($analysis_escalate_raw, true);
                if (is_array($decoded_escalate)) {
                    $analysis_escalate = array_values(array_filter(array_map('strval', $decoded_escalate)));
                }
            }
            return [
                'code' => (string) ($pot['pot_code'] ?? ''),
                'name' => (string) ($pot['pot_name'] ?? ''),
                'plant_name' => (string) ($pot['plant_name'] ?? ''),
                'plant_id' => (int) ($pot['plant_id'] ?? 0),
                'status' => (string) ($pot['status'] ?? ''),
                'ph' => (string) ($pot['ph'] ?? ''),
                'temperature' => (string) ($pot['temperature'] ?? ''),
                'humidity' => (string) ($pot['humidity'] ?? ''),
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
                'latest_analysis_level' => (int) ($pot['latest_analysis_level'] ?? 0),
                'latest_analysis_color' => (string) ($pot['latest_analysis_color'] ?? ''),
                'latest_analysis_label' => (string) ($pot['latest_analysis_label'] ?? ''),
                'latest_analysis_current_stage' => (string) ($pot['latest_analysis_current_stage'] ?? ''),
                'latest_analysis_recommendation' => (string) ($pot['latest_analysis_recommendation'] ?? ''),
                'latest_analysis_summary' => (string) ($pot['latest_analysis_summary'] ?? ''),
                'latest_analysis_actions' => $analysis_actions,
                'latest_analysis_escalate' => $analysis_escalate,
                'latest_analysis_updated_at' => (string) ($pot['latest_analysis_updated_at'] ?? ''),
                'trays' => [(string) ($pot['pot_name'] ?? '')],
            ];
        }, $db_pots);
        return $cache[$cache_key];
    }

    if ($user_id <= 0) {
        return [];
    }

    $bucket = get_user_meta($user_id, aitrongcay_custom_pots_meta_key(), true);
    if (! is_array($bucket)) {
        return [];
    }

    $pots = $bucket[$garden_key] ?? [];
    $cache[$cache_key] = is_array($pots) ? array_values(array_filter($pots, 'is_array')) : [];
    return $cache[$cache_key];
}

function aitrongcay_store_custom_pots(string $garden_key, int $user_id, array $pots): void
{
    if ($garden_key === '') {
        return;
    }

    if (function_exists('aitrongcay_upsert_db_pot')) {
        foreach (array_values($pots) as $index => $pot) {
            if (! is_array($pot)) continue;
            $pot['sort_order'] = $index + 1;
            aitrongcay_upsert_db_pot($garden_key, $pot);
        }
    }

    if ($user_id <= 0) {
        return;
    }

    $bucket = get_user_meta($user_id, aitrongcay_custom_pots_meta_key(), true);
    if (! is_array($bucket)) {
        $bucket = [];
    }

    $bucket[$garden_key] = array_values($pots);
    update_user_meta($user_id, aitrongcay_custom_pots_meta_key(), $bucket);
}

function aitrongcay_pot_name_overrides_meta_key(): string
{
    return '_aitrongcay_pot_name_overrides_by_garden';
}

function aitrongcay_get_pot_name_overrides(string $garden_key, int $user_id): array
{
    static $cache = [];
    if ($garden_key === '' || $user_id <= 0) {
        return [];
    }

    $cache_key = $garden_key . '|' . $user_id;
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $bucket = get_user_meta($user_id, aitrongcay_pot_name_overrides_meta_key(), true);
    if (! is_array($bucket)) {
        return [];
    }

    $overrides = $bucket[$garden_key] ?? [];
    $cache[$cache_key] = is_array($overrides) ? $overrides : [];
    return $cache[$cache_key];
}

function aitrongcay_store_pot_name_overrides(string $garden_key, int $user_id, array $overrides): void
{
    if ($garden_key === '' || $user_id <= 0) {
        return;
    }

    $bucket = get_user_meta($user_id, aitrongcay_pot_name_overrides_meta_key(), true);
    if (! is_array($bucket)) {
        $bucket = [];
    }

    $bucket[$garden_key] = $overrides;
    update_user_meta($user_id, aitrongcay_pot_name_overrides_meta_key(), $bucket);
}

function aitrongcay_update_pot_name_for_garden(string $garden_key, int $user_id, string $pot_code, string $pot_name): bool
{
    $garden_key = trim($garden_key);
    $pot_code = trim($pot_code);
    $pot_name = trim($pot_name);

    if ($garden_key === '' || $user_id <= 0 || $pot_code === '' || $pot_name === '') {
        return false;
    }

    $custom_pots = aitrongcay_get_custom_pots($garden_key, $user_id);
    $custom_updated = false;
    foreach ($custom_pots as &$pot) {
        if ((string) ($pot['code'] ?? '') !== $pot_code) {
            continue;
        }
        $pot['name'] = $pot_name;
        if (empty($pot['trays']) || ! is_array($pot['trays'])) {
            $pot['trays'] = [$pot_name];
        } else {
            $pot['trays'][0] = $pot_name;
        }
        $custom_updated = true;
        break;
    }
    unset($pot);

    if ($custom_updated) {
        aitrongcay_store_custom_pots($garden_key, $user_id, $custom_pots);
        return true;
    }

    $dataset = function_exists('aitrongcay_portal_dataset_for_garden') ? (array) aitrongcay_portal_dataset_for_garden($garden_key, get_user_by('id', $user_id) ?: null) : [];
    $dataset_pots = (array) ($dataset['pots'] ?? []);
    $known = false;
    foreach ($dataset_pots as $pot) {
        if ((string) ($pot['code'] ?? '') === $pot_code) {
            $known = true;
            break;
        }
    }

    if (! $known) {
        return false;
    }

    $overrides = aitrongcay_get_pot_name_overrides($garden_key, $user_id);
    $overrides[$pot_code] = $pot_name;
    aitrongcay_store_pot_name_overrides($garden_key, $user_id, $overrides);

    return true;
}

function aitrongcay_next_custom_pot_code(array $existing_pots): string
{
    $max = 0;
    foreach ($existing_pots as $pot) {
        $code = (string) ($pot['code'] ?? '');
        if (preg_match('/P-(\d+)/', $code, $matches)) {
            $max = max($max, (int) $matches[1]);
        }
    }

    return 'P-' . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
}

function aitrongcay_build_custom_pot(string $plant_name, array $existing_pots, array $slot = []): array
{
    $plant_name = trim($plant_name);
    $code = trim((string) ($slot['pot_code'] ?? ''));
    if ($code === '') {
        $code = aitrongcay_next_custom_pot_code($existing_pots);
    }
    $pot_number = max(1, (int) ($slot['slot_index'] ?? (count($existing_pots) + 1)));
    $slot_meta = function_exists('aitrongcay_slot_to_compartment') ? aitrongcay_slot_to_compartment($pot_number) : ['slot_label' => 'Khoang ' . $pot_number];
    $light_device = trim((string) ($slot['control_channel'] ?? ''));
    if ($light_device === '') {
        $light_device = 'light' . $pot_number;
    }

    return [
        'code' => $code,
        'name' => $plant_name,
        'status' => 'Mới khởi tạo',
        'ph' => '--',
        'temperature' => '-- °C',
        'humidity' => '-- %',
        'light' => 'Đèn ' . (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $pot_number)),
        'light_device' => $light_device,
        'pump' => 'Bơm chung',
        'irrigation' => 'Sẽ gợi ý sau khi xác nhận giống cây',
        'video' => '',
        'image' => get_template_directory_uri() . '/assets/images/hero-greenhouse.svg',
        'ai_note' => 'Em vừa khởi tạo khoang này từ cuộc trò chuyện AI. Bước tiếp theo là theo dõi ảnh, chỉ số và nhịp chăm thực tế.',
        'status_summary' => (string) ($slot_meta['slot_label'] ?? ('Khoang ' . $pot_number)) . ' mới đã được tạo cho cây ' . $plant_name . '. Hiện đang ở bước khởi tạo thông tin nền và chờ anh bổ sung dữ liệu thực tế.',
        'harvest_eta' => 'Đang chờ AI gợi ý lịch chăm phù hợp',
        'trays' => [$plant_name],
        'is_custom' => true,
        'created_via' => 'ai_onboarding',
        'created_at' => wp_date('c'),
    ];
}

function aitrongcay_normalize_pot_created_at(string $raw_value): string
{
    $raw_value = trim($raw_value);
    if ($raw_value === '') {
        return '';
    }

    $timezone = wp_timezone();
    $formats = ['Y-m-d', 'd/m/Y', DATE_ATOM, 'Y-m-d H:i:s', 'Y-m-d\TH:i', 'Y-m-d\TH:i:s'];
    foreach ($formats as $format) {
        $dt = DateTimeImmutable::createFromFormat($format, $raw_value, $timezone);
        if ($dt instanceof DateTimeImmutable) {
            return $dt->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        }
    }

    $timestamp = strtotime($raw_value);
    if (! $timestamp) {
        return '';
    }

    return wp_date('Y-m-d 00:00:00', $timestamp, $timezone);
}

function aitrongcay_create_custom_pot_for_user(WP_User $user, string $garden_key, string $plant_name, string $created_at = ''): array
{
    $owner = aitrongcay_get_garden_owner_user($garden_key);
    $target_user = $owner instanceof WP_User ? $owner : $user;
    $target_user_id = (int) ($target_user->ID ?? 0);
    if ($target_user_id <= 0) {
        return ['error' => 'Không xác định được chủ khu vườn để tạo khoang.'];
    }

    $rack = aitrongcay_get_rack_record($garden_key);
    $rack_slots = aitrongcay_get_rack_slots($garden_key);
    if (! $rack || count($rack_slots) < 2) {
        return ['error' => 'Anh cần khởi tạo rack trước rồi mới tạo khoang trồng cây.'];
    }

    $existing_pots = array_merge(
        aitrongcay_portal_dataset_for_garden($garden_key, $target_user)['pots'] ?? [],
        aitrongcay_get_custom_pots($garden_key, $target_user_id)
    );

    $used_codes = [];
    foreach ($existing_pots as $existing_pot) {
        $used_code = trim((string) ($existing_pot['code'] ?? ''));
        if ($used_code !== '') {
            $used_codes[$used_code] = true;
        }
    }

    $available_slot = null;
    foreach ($rack_slots as $slot) {
        $pot_code = trim((string) ($slot['pot_code'] ?? ''));
        if ($pot_code === '' || isset($used_codes[$pot_code])) {
            continue;
        }
        $available_slot = $slot;
        break;
    }
    if (! is_array($available_slot)) {
        $slot_count = max((int) ($rack['slot_count'] ?? 0), count($rack_slots));
        return ['error' => 'Rack này đã dùng hết ' . $slot_count . ' khoang. Muốn thêm khoang mới, mình cần đổi hoặc cấp thêm rack khác trước.'];
    }

    $normalized_created_at = aitrongcay_normalize_pot_created_at($created_at);
    if ($normalized_created_at === '') {
        return ['error' => 'Anh cần nhập ngày khởi tạo khoang trước khi tạo mới.'];
    }

    $new_pot = aitrongcay_build_custom_pot($plant_name, $existing_pots, $available_slot);
    $new_pot['created_at'] = $normalized_created_at;
    $custom_pots = aitrongcay_get_custom_pots($garden_key, $target_user_id);
    $custom_pots[] = $new_pot;
    aitrongcay_store_custom_pots($garden_key, $target_user_id, $custom_pots);

    if (function_exists('aitrongcay_upsert_garden_record')) {
        $default_name = function_exists('aitrongcay_build_default_garden_name') ? aitrongcay_build_default_garden_name($garden_key, $target_user) : 'Khu vườn của bạn';
        aitrongcay_upsert_garden_record($garden_key, $target_user_id, [
            'garden_name' => $default_name,
            'garden_code' => strtoupper(substr(md5($garden_key), 0, 6)),
            'summary' => 'Khu vườn này đang bắt đầu có dữ liệu thật theo các khoang đã được tạo.',
            'status_line' => count($custom_pots) . ' khoang • đang theo dõi',
        ]);
    }

    return [
        'pot' => $new_pot,
        'slot' => $available_slot,
        'rack' => $rack,
        'garden_key' => $garden_key,
        'owner_user_id' => $target_user_id,
    ];
}

function aitrongcay_garden_assistant_build_reply(string $message, WP_User $user, string $garden_key = ''): array
{
    $normalized = strtolower(trim($message));
    $garden_ai = aitrongcay_portal_garden_ai($garden_key, $user);
    $pots = function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $user) : [];
    $pot_names = array_values(array_filter(array_map(static fn(array $pot): string => trim((string) ($pot['name'] ?? $pot['code'] ?? '')), $pots)));
    $sample_pot = $pot_names[0] ?? 'khoang đang theo dõi';
    $reply = (string) ($garden_ai['summary'] ?? 'Em đã nhận câu hỏi của anh/chị. Hiện em đang ưu tiên đọc dữ liệu đúng theo khu vườn đang mở và gợi ý những bước an toàn, dễ làm trước.');
    $matched_pot = null;
    foreach ($pots as $pot) {
        $pot_name = strtolower(trim((string) ($pot['name'] ?? '')));
        $pot_code = strtolower(trim((string) ($pot['code'] ?? '')));
        if (($pot_name !== '' && str_contains($normalized, $pot_name)) || ($pot_code !== '' && str_contains($normalized, $pot_code))) {
            $matched_pot = $pot;
            break;
        }
    }
    if (! $matched_pot && ! empty($pots[0])) {
        $matched_pot = $pots[0];
    }
    $photo_context = [];
    if (is_array($matched_pot)) {
        $photo_context = aitrongcay_get_latest_pot_photo_context($garden_key, (string) ($matched_pot['code'] ?? ''));
    }

    if ($normalized !== '') {
        if (str_contains($normalized, 'ph')) {
            $reply = 'Nếu anh/chị đang hỏi về pH, em khuyên xem trước khoang có dấu hiệu bất thường rõ nhất rồi điều chỉnh từng bước nhỏ. Nếu vườn này chưa có pH thật cho từng khoang, mình nên cập nhật dần để em bám sát hơn.';
        } elseif (str_contains($normalized, 'độ ẩm') || str_contains($normalized, 'am')) {
            $reply = 'Với độ ẩm giảm nhẹ, mình nên xem lại khoang nào có dấu hiệu khô trước rồi mới tăng tưới. Ưu tiên giữ ổn định thay vì thay đổi quá mạnh trong một lần.';
        } elseif (str_contains($normalized, 'đèn') || str_contains($normalized, 'den')) {
            $reply = 'Nếu cần tối ưu đèn, em khuyên tăng nhẹ theo nhịp 20–30 phút rồi theo dõi phản ứng của ' . $sample_pot . ' trong ngày kế tiếp. Ánh sáng đều thường an toàn hơn tăng mạnh một lần.';
        } elseif (str_contains($normalized, 'chợ quê') || str_contains($normalized, 'cho que')) {
            $reply = 'Nếu anh/chị muốn đăng Chợ quê, em khuyên chọn ảnh bìa rõ, tiêu đề ngắn và đưa ý chính lên đầu mô tả. Như vậy bài sẽ gọn và dễ được bấm xem hơn.';
        }
    }

    if ($photo_context) {
        $pot_label = (string) ($photo_context['pot_name'] ?: ($photo_context['pot_code'] ?? $sample_pot));
        $photo_hint = ' Em sẽ ưu tiên bám ảnh mới nhất của ' . $pot_label;
        if (! empty($photo_context['captured_at'])) {
            $photo_hint .= ' (cập nhật gần nhất: ' . mysql2date('H:i d/m/Y', (string) $photo_context['captured_at']) . ')';
        }
        $photo_hint .= ' khi phân tích và đưa cảnh báo.';
        $reply .= $photo_hint;
    }

    $session_label = 'garden-assistant-user-' . max(1, (int) $user->ID);

    return [
        'reply' => $reply,
        'sessionLabel' => $session_label,
        'mode' => 'adapter-ready',
        'agentStatus' => 'Chưa nối OpenClaw gateway thật — adapter đã sẵn sàng để cắm session riêng.',
        'latestPhoto' => $photo_context,
    ];
}

function aitrongcay_garden_assistant_chat_ajax(): void
{
    aitrongcay_require_portal_nonce();

    $message = sanitize_textarea_field((string) wp_unslash($_POST['message'] ?? ''));
    if ($message === '') {
        wp_send_json_error(['message' => 'Anh/chị vui lòng nhập nội dung cần hỏi.'], 400);
    }

    // ----- GUEST (not logged in) -----
    if (! is_user_logged_in()) {
        $guest_user = new WP_User(0);
        $assistant = aitrongcay_garden_assistant_build_reply($message, $guest_user, '');
        wp_send_json_success([
            'sessionId'    => 0,
            'sessionKey'   => 'guest',
            'sessionTitle' => 'Tư vấn nhanh',
            'messages'     => [
                ['role' => 'user',      'text' => $message,                           'time' => wp_date('c')],
                ['role' => 'assistant', 'text' => (string) ($assistant['reply'] ?? ''), 'time' => wp_date('c')],
            ],
            'reply'        => (string) ($assistant['reply'] ?? ''),
            'sessionLabel' => 'guest',
            'mode'         => (string) ($assistant['mode'] ?? 'guest-local'),
            'agentStatus'  => (string) ($assistant['agentStatus'] ?? 'guest-ready'),
            'latestPhoto'  => $assistant['latestPhoto'] ?? [],
            'latencyMs'    => 0,
        ]);
    }

    // ----- LOGGED IN -----
    $user             = wp_get_current_user();
    $session_id       = (int) ($_POST['session_id'] ?? 0);
    $force_new        = ! empty($_POST['new_session']);
    $session_mode     = sanitize_key((string) wp_unslash($_POST['mode'] ?? ''));
    $garden_key       = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        $garden_key = aitrongcay_resolve_active_garden_key($user);
    }
    aitrongcay_remember_selected_garden_key((int) $user->ID, $garden_key);

    $request_started_at = microtime(true);

    // --- Get / create session ---
    $thread    = function_exists('aitrongcay_ai_get_or_create_session')
        ? aitrongcay_ai_get_or_create_session($garden_key, $user, ['session_id' => $session_id, 'force_new' => $force_new, 'mode' => $session_mode])
        : [];
    $thread_id = (int) ($thread['id'] ?? 0);

    // --- Load history (does NOT yet include the current message) ---
    $history = $thread_id > 0 && function_exists('aitrongcay_ai_get_session_history')
        ? aitrongcay_ai_get_session_history($thread_id, 18)
        : [];

    // Build the payload history = stored history + current user message
    // (we do NOT write the user message to DB yet to avoid duplicate in the API call)
    $payload_history = array_merge(
        is_array($history) ? $history : [],
        [['role' => 'user', 'text' => $message, 'time' => wp_date('c')]]
    );

    // --- Call remote AI ---
    $assistant = function_exists('aitrongcay_ai_call_remote_agent')
        ? aitrongcay_ai_call_remote_agent($message, $user, $garden_key, $thread, $payload_history)
        : ['ok' => false, 'mode' => 'adapter-ready'];

    // --- Fallback to local reply if remote failed ---
    $used_fallback  = false;
    $remote_error   = '';
    if (empty($assistant['ok'])) {
        $remote_error   = (string) ($assistant['message'] ?? 'remote-disabled-or-failed');
        $assistant      = aitrongcay_garden_assistant_build_reply($message, $user, $garden_key);
        // Append the error info so the user sees it
        if ($remote_error !== '') {
            $assistant['reply'] = (string) ($assistant['reply'] ?? '') . "\n\n(Lỗi kết nối AI: {$remote_error})";
        }
        $used_fallback = true;
    }

    $reply_text = (string) ($assistant['reply'] ?? '');

    // --- Persist both messages to DB (only NOW, after we know the reply) ---
    if ($thread_id > 0 && function_exists('aitrongcay_ai_append_message_to_session')) {
        aitrongcay_ai_append_message_to_session($thread_id, 'user', $message, ['source' => 'portal-chat']);
        aitrongcay_ai_append_message_to_session($thread_id, 'assistant', $reply_text, [
            'mode'         => (string) ($assistant['mode'] ?? 'adapter-ready'),
            'session_label'=> (string) ($assistant['sessionLabel'] ?? ''),
        ]);
    }

    // --- Update session state ---
    if ($thread_id > 0 && function_exists('aitrongcay_ai_update_session_state')) {
        $summary_lines = array_slice(array_merge(
            array_map(static function (array $item): string {
                $role = (string) ($item['role'] ?? 'assistant');
                $text = trim((string) ($item['text'] ?? ''));
                return $text !== '' ? (($role === 'user' ? 'Anh/chị' : 'Cindy') . ': ' . wp_trim_words($text, 18, '...')) : '';
            }, is_array($history) ? $history : []),
            ['Anh/chị: ' . wp_trim_words($message, 18, '...')],
            ['Cindy: '   . wp_trim_words($reply_text, 18, '...')]
        ), -4);

        aitrongcay_ai_update_session_state($thread_id, [
            'remote_thread_key'      => (string) ($assistant['remoteThreadKey'] ?? ($thread['remote_thread_key'] ?? '')),
            'status'                 => $used_fallback ? 'fallback-local' : 'remote-ok',
            'last_error'             => $used_fallback ? $remote_error : '',
            'last_user_message'      => $message,
            'last_assistant_message' => $reply_text,
            'working_summary'        => implode("\n", array_filter($summary_lines)),
            'last_message_at'        => current_time('mysql'),
        ]);
    }

    if ($thread_id > 0 && function_exists('aitrongcay_ai_maybe_promote_memory')) {
        aitrongcay_ai_maybe_promote_memory($user, $thread, $message, $reply_text, 0);
    }

    // Response includes the full updated message list
    $messages_for_response = array_slice(
        array_merge(
            is_array($history) ? $history : [],
            [
                ['role' => 'user',      'text' => $message,    'time' => wp_date('c')],
                ['role' => 'assistant', 'text' => $reply_text, 'time' => wp_date('c')],
            ]
        ),
        -20
    );

    wp_send_json_success([
        'sessionId'    => $thread_id,
        'sessionKey'   => (string) ($thread['session_key'] ?? ''),
        'sessionTitle' => (string) ($thread['title'] ?? ''),
        'messages'     => $messages_for_response,
        'reply'        => $reply_text,
        'sessionLabel' => $assistant['sessionLabel'] ?? '',
        'mode'         => $assistant['mode'] ?? 'adapter-ready',
        'agentStatus'  => $assistant['agentStatus'] ?? '',
        'latestPhoto'  => $assistant['latestPhoto'] ?? [],
        'latencyMs'    => (int) round((microtime(true) - $request_started_at) * 1000),
    ]);
}
add_action('wp_ajax_aitrongcay_garden_assistant_chat', 'aitrongcay_garden_assistant_chat_ajax');
add_action('wp_ajax_nopriv_aitrongcay_garden_assistant_chat', 'aitrongcay_garden_assistant_chat_ajax');

function aitrongcay_ai_list_sessions_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập.'], 403);
    }
    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    $sessions = function_exists('aitrongcay_ai_list_sessions') ? aitrongcay_ai_list_sessions($user, $garden_key, 40) : [];
    $items = array_map(static function (array $session): array {
        return [
            'id' => (int) ($session['id'] ?? 0),
            'session_key' => (string) ($session['session_key'] ?? ''),
            'title' => (string) (($session['title'] ?? '') ?: 'Phiên chat'),
            'status' => (string) ($session['status'] ?? 'active'),
            'scope_type' => (string) ($session['scope_type'] ?? 'garden'),
            'updated_at' => (string) ($session['updated_at'] ?? ''),
            'last_message_at' => (string) ($session['last_message_at'] ?? ''),
            'last_user_message' => (string) ($session['last_user_message'] ?? ''),
        ];
    }, is_array($sessions) ? $sessions : []);
    wp_send_json_success(['sessions' => $items]);
}
add_action('wp_ajax_aitrongcay_ai_list_sessions', 'aitrongcay_ai_list_sessions_ajax');
add_action('wp_ajax_nopriv_aitrongcay_ai_list_sessions', static function (): void {
    aitrongcay_require_portal_nonce();
    wp_send_json_success(['sessions' => []]);
});

function aitrongcay_ai_create_session_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập.'], 403);
    }
    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? ''));
    if ($garden_key !== '' && ! aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        $garden_key = '';
    }
    $title = sanitize_text_field((string) wp_unslash($_POST['title'] ?? ''));
    $mode = sanitize_key((string) wp_unslash($_POST['mode'] ?? ''));
    $session = function_exists('aitrongcay_ai_create_session') ? aitrongcay_ai_create_session($user, $garden_key, ['title' => $title, 'mode' => $mode]) : [];
    if (! $session) {
        wp_send_json_error(['message' => 'Chưa tạo được session mới.'], 500);
    }
    wp_send_json_success(['session' => $session]);
}
add_action('wp_ajax_aitrongcay_ai_create_session', 'aitrongcay_ai_create_session_ajax');
add_action('wp_ajax_nopriv_aitrongcay_ai_create_session', static function (): void {
    aitrongcay_require_portal_nonce();
    wp_send_json_success(['session' => ['id' => 0, 'session_key' => 'guest', 'title' => 'Tư vấn nhanh']]);
});

function aitrongcay_ai_load_session_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập.'], 403);
    }
    $user = wp_get_current_user();
    $session_id = (int) ($_POST['session_id'] ?? 0);
    $session = function_exists('aitrongcay_ai_get_session_by_id') ? aitrongcay_ai_get_session_by_id($session_id, $user) : [];
    if (! $session) {
        wp_send_json_error(['message' => 'Không tìm thấy session.'], 404);
    }
    $messages = function_exists('aitrongcay_ai_get_session_history') ? aitrongcay_ai_get_session_history($session_id, 50) : [];
    wp_send_json_success(['session' => $session, 'messages' => $messages]);
}
add_action('wp_ajax_aitrongcay_ai_load_session', 'aitrongcay_ai_load_session_ajax');

function aitrongcay_ai_update_session_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập.'], 403);
    }
    $user = wp_get_current_user();
    $session_id = (int) ($_POST['session_id'] ?? 0);
    $session = function_exists('aitrongcay_ai_get_session_by_id') ? aitrongcay_ai_get_session_by_id($session_id, $user) : [];
    if (! $session) {
        wp_send_json_error(['message' => 'Không tìm thấy session.'], 404);
    }
    $patch = [];
    if (isset($_POST['title'])) {
        $patch['title'] = sanitize_text_field((string) wp_unslash($_POST['title']));
    }
    if (isset($_POST['status'])) {
        $status = sanitize_key((string) wp_unslash($_POST['status']));
        if (in_array($status, ['active', 'archived'], true)) {
            $patch['status'] = $status;
        }
    }
    if ($patch && function_exists('aitrongcay_ai_update_session_state')) {
        aitrongcay_ai_update_session_state($session_id, $patch);
    }
    $updated = function_exists('aitrongcay_ai_get_session_by_id') ? aitrongcay_ai_get_session_by_id($session_id, $user) : [];
    wp_send_json_success(['session' => $updated]);
}
add_action('wp_ajax_aitrongcay_ai_update_session', 'aitrongcay_ai_update_session_ajax');

function aitrongcay_init_rack_redirect_url(string $garden_key = '', string $status = '', string $message = ''): string
{
    $args = [];
    if ($garden_key !== '') {
        $args['garden'] = rawurlencode($garden_key);
    }
    if ($status !== '') {
        $args['rack_init'] = $status;
    }
    if ($message !== '') {
        $args['rack_notice'] = rawurlencode($message);
    }
    return add_query_arg($args, home_url('/portal/dashboard-2/'));
}

function aitrongcay_init_rack_for_current_user_action(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    if (! current_user_can('manage_options')) {
        wp_die('Chức năng tự động nhận Rack đã bị khóa. Khách hàng chỉ được sở hữu rack khi được quản trị viên giao vườn.');
    }

    check_admin_referer('aitrongcay_init_rack');

    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_REQUEST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        $garden_key = aitrongcay_resolve_active_garden_key($user);
    }

    $result = aitrongcay_initialize_rack_for_user($user, $garden_key);
    if (! empty($result['error'])) {
        wp_safe_redirect(aitrongcay_init_rack_redirect_url($garden_key, 'empty', (string) $result['error']));
        exit;
    }

    $rack = (array) ($result['rack'] ?? []);
    $slot_count = (int) ($rack['slot_count'] ?? 0);
    $message = ! empty($result['already_assigned'])
        ? 'Rack của khu vườn này đã có sẵn rồi, mình có thể khởi tạo khoang tiếp.'
        : ('Em đã cấp 1 rack ' . ($slot_count > 0 ? ($slot_count . ' khoang') : '') . ' từ kho cho khu vườn này.');
    wp_safe_redirect(aitrongcay_init_rack_redirect_url($garden_key, 'ok', $message));
    exit;
}
add_action('admin_post_aitrongcay_init_rack', 'aitrongcay_init_rack_for_current_user_action');

function aitrongcay_create_first_pot_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập để tạo khoang cây.'], 403);
    }

    $user = wp_get_current_user();
    $plant_name = sanitize_text_field((string) wp_unslash($_POST['plant_name'] ?? ''));
    $created_at_raw = sanitize_text_field((string) wp_unslash($_POST['created_at'] ?? ''));
    if ($plant_name === '') {
        wp_send_json_error(['message' => 'Anh/chị cho em biết mình muốn trồng cây gì nhé.'], 400);
    }
    if ($created_at_raw === '') {
        wp_send_json_error(['message' => 'Anh/chị cần nhập ngày khởi tạo khoang trước khi tạo mới.'], 400);
    }

    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        $garden_key = aitrongcay_resolve_active_garden_key($user);
    }

    $created = aitrongcay_create_custom_pot_for_user($user, $garden_key, $plant_name, $created_at_raw);
    if (! empty($created['error'])) {
        wp_send_json_error(['message' => (string) $created['error']], 400);
    }

    $pot = (array) ($created['pot'] ?? []);
    $slot = (array) ($created['slot'] ?? []);
    $pot_name = (string) ($pot['name'] ?? $plant_name);
    $slot_name = trim((string) ($slot['slot_name'] ?? ''));
    wp_send_json_success([
        'message' => 'Em đã tạo ' . $pot_name . ($slot_name !== '' ? (' vào ' . $slot_name) : '') . '. Mình quay về dashboard để theo dõi luôn nhé.',
        'pot' => $pot,
        'slot' => $slot,
        'redirect' => add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/dashboard-2/')),
        'garden_key' => $garden_key,
    ]);
}
add_action('wp_ajax_aitrongcay_create_first_pot', 'aitrongcay_create_first_pot_ajax');

function aitrongcay_rename_pot_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập để đổi tên khoang.'], 403);
    }

    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_control_garden($garden_key, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Anh/chị chưa có quyền đổi tên khoang này.'], 403);
    }

    $owner = aitrongcay_get_garden_owner_user($garden_key);
    $target_user_id = (int) (($owner instanceof WP_User ? $owner->ID : $user->ID) ?: 0);
    if ($target_user_id <= 0) {
        $target_user_id = (int) $user->ID;
    }
    $pot_code = sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''));
    $pot_name = sanitize_text_field((string) wp_unslash($_POST['pot_name'] ?? ''));
    $pot_name = trim(preg_replace('/\s+/u', ' ', $pot_name));

    if ($pot_code === '' || $pot_name === '') {
        wp_send_json_error(['message' => 'Tên khoang không được để trống.'], 400);
    }

    if (function_exists('mb_strlen') && mb_strlen($pot_name) > 120) {
        $pot_name = mb_substr($pot_name, 0, 120);
    }

    if (! aitrongcay_update_pot_name_for_garden($garden_key, $target_user_id, $pot_code, $pot_name)) {
        wp_send_json_error(['message' => 'Khoang này hiện chưa hỗ trợ đổi tên trực tiếp.'], 400);
    }

    wp_send_json_success([
        'pot_code' => $pot_code,
        'pot_name' => $pot_name,
        'message' => 'Đã lưu tên khoang mới.',
    ]);
}
add_action('wp_ajax_aitrongcay_rename_pot', 'aitrongcay_rename_pot_ajax');

function aitrongcay_save_pot_note_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập để lưu ghi chú.'], 403);
    }

    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_control_garden($garden_key, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Anh/chị chưa có quyền cập nhật nhật ký của khu vườn này.'], 403);
    }

    $pot_code = sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''));
    $note_text = trim((string) wp_unslash($_POST['note_text'] ?? ''));
    $note_text = preg_replace('/\r\n?|\n/u', "\n", $note_text);
    $note_text = trim((string) preg_replace('/[\t ]+/u', ' ', $note_text));
    $note_text = aitrongcay_normalize_pot_note_text($note_text, wp_date('d/m/Y'));

    if ($pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu mã khoang để lưu ghi chú.'], 400);
    }

    if (function_exists('mb_strlen') && mb_strlen($note_text) > 2000) {
        $note_text = mb_substr($note_text, 0, 2000);
    }

    if (! aitrongcay_save_garden_pot_note($garden_key, $pot_code, $note_text, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Chưa lưu được ghi chú cho khoang này.'], 500);
    }

    wp_send_json_success([
        'garden_key' => $garden_key,
        'pot_code' => $pot_code,
        'note_text' => $note_text,
        'updated_at' => current_time('mysql'),
        'message' => $note_text === '' ? 'Đã xóa ghi chú trống.' : 'Đã lưu ghi chú canh tác.',
    ]);
}
add_action('wp_ajax_aitrongcay_save_pot_note', 'aitrongcay_save_pot_note_ajax');

function aitrongcay_rename_garden_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập để đổi tên vườn.'], 403);
    }

    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    if ($garden_key === '' || ! aitrongcay_user_can_control_garden($garden_key, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Anh/chị chưa có quyền đổi tên khu vườn này.'], 403);
    }

    $owner = aitrongcay_get_garden_owner_user($garden_key);
    $target_user_id = (int) (($owner instanceof WP_User ? $owner->ID : $user->ID) ?: 0);
    if ($target_user_id <= 0) {
        $target_user_id = (int) $user->ID;
    }

    $garden_name = sanitize_text_field((string) wp_unslash($_POST['garden_name'] ?? ''));
    $garden_name = trim((string) preg_replace('/\s+/u', ' ', $garden_name));
    if ($garden_name === '') {
        $garden_name = aitrongcay_build_default_garden_name($garden_key, $user);
    }
    if ($garden_name === '') {
        $garden_name = 'Khu vườn của bạn';
    }

    if (function_exists('mb_strlen') && mb_strlen($garden_name) > 160) {
        $garden_name = mb_substr($garden_name, 0, 160);
    }

    aitrongcay_store_garden_name_override($garden_key, $target_user_id, $garden_name);
    if ((int) $user->ID !== $target_user_id) {
        aitrongcay_store_garden_name_override($garden_key, (int) $user->ID, $garden_name);
    }
    aitrongcay_remember_selected_garden_key((int) $user->ID, $garden_key);

    if (function_exists('aitrongcay_upsert_garden_record')) {
        $existing = aitrongcay_get_garden_record($garden_key) ?: [];
        $sync_owner_id = $target_user_id > 0 ? $target_user_id : (int) $user->ID;
        aitrongcay_upsert_garden_record($garden_key, $sync_owner_id, [
            'garden_name' => $garden_name,
            'garden_code' => (string) ($existing['garden_code'] ?? strtoupper(substr(md5($garden_key), 0, 6))),
            'summary' => (string) ($existing['summary'] ?? ''),
            'status_line' => (string) ($existing['status_line'] ?? ''),
        ]);
    }

    wp_send_json_success([
        'garden_key' => $garden_key,
        'garden_name' => $garden_name,
        'message' => 'Đã lưu tên khu vườn mới.',
    ]);
}
add_action('wp_ajax_aitrongcay_rename_garden', 'aitrongcay_rename_garden_ajax');

function aitrongcay_delete_photo_attachment_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $attachment_id = absint($_POST['attachment_id'] ?? 0);
    $attachment = get_post($attachment_id);
    if (! $attachment || $attachment->post_type !== 'attachment') {
        wp_send_json_error(['message' => 'Không tìm thấy ảnh cần xóa.'], 404);
    }
    $owner_id = (int) get_post_meta($attachment_id, '_aitrongcay_photo_owner', true);
    if ($owner_id !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Không có quyền xóa ảnh này.'], 403);
    }
    wp_delete_attachment($attachment_id, true);
    wp_send_json_success(['id' => $attachment_id]);
}
add_action('wp_ajax_aitrongcay_delete_photo_attachment', 'aitrongcay_delete_photo_attachment_ajax');

function aitrongcay_rename_photo_attachment_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $attachment_id = absint($_POST['attachment_id'] ?? 0);
    $title = sanitize_text_field((string) wp_unslash($_POST['title'] ?? ''));
    $attachment = get_post($attachment_id);
    if (! $attachment || $attachment->post_type !== 'attachment') {
        wp_send_json_error(['message' => 'Không tìm thấy ảnh cần đổi tên.'], 404);
    }
    $owner_id = (int) get_post_meta($attachment_id, '_aitrongcay_photo_owner', true);
    if ($owner_id !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Không có quyền đổi tên ảnh này.'], 403);
    }
    if ($title === '') {
        wp_send_json_error(['message' => 'Tên ảnh không được để trống.'], 400);
    }
    wp_update_post(['ID' => $attachment_id, 'post_title' => $title]);
    wp_send_json_success(['id' => $attachment_id, 'title' => get_the_title($attachment_id)]);
}
add_action('wp_ajax_aitrongcay_rename_photo_attachment', 'aitrongcay_rename_photo_attachment_ajax');

function aitrongcay_upload_photo_attachment_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $posted_garden_key = trim(sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? '')));
    $garden_key = $posted_garden_key !== '' ? $posted_garden_key : aitrongcay_resolve_active_garden_key();
    $pot_code = strtoupper(trim(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''))));
    $pot_name = trim(sanitize_text_field((string) wp_unslash($_POST['pot_name'] ?? '')));
    if (empty($_FILES['photo'])) {
        wp_send_json_error(['message' => 'Chưa có file ảnh để upload.'], 400);
    }
    if ($garden_key === '') {
        wp_send_json_error(['message' => 'Không xác định được khu vườn đang thao tác.'], 400);
    }
    if (! aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
        wp_send_json_error(['message' => 'Anh/chị chưa có quyền upload ảnh cho khu vườn này.'], 403);
    }
    if ($pot_code === '') {
        wp_send_json_error(['message' => 'Chưa chọn chậu cây để gắn ảnh.'], 400);
    }
    $pot_record = null;
    foreach (aitrongcay_get_custom_pots($garden_key, get_current_user_id()) as $pot) {
        $candidate_code = strtoupper(trim((string) ($pot['pot_code'] ?? $pot['code'] ?? '')));
        if ($candidate_code === $pot_code) {
            $pot_record = $pot;
            break;
        }
    }
    if (! is_array($pot_record)) {
        $pot_record = [
            'pot_code' => $pot_code,
            'code' => $pot_code,
            'pot_name' => $pot_name !== '' ? $pot_name : $pot_code,
            'name' => $pot_name !== '' ? $pot_name : $pot_code,
        ];
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $uploaded = wp_handle_upload($_FILES['photo'], ['test_form' => false]);
    if (! is_array($uploaded) || ! empty($uploaded['error']) || empty($uploaded['file'])) {
        wp_send_json_error(['message' => ! empty($uploaded['error']) ? (string) $uploaded['error'] : 'Không thể lưu file ảnh đã upload.'], 500);
    }
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => (string) ($uploaded['type'] ?? 'image/jpeg'),
        'post_title' => 'Ảnh ' . strtoupper($pot_code) . ' · ' . wp_date('H:i'),
        'post_status' => 'inherit',
        'post_author' => get_current_user_id(),
    ], (string) $uploaded['file']);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_send_json_error(['message' => is_wp_error($attachment_id) ? $attachment_id->get_error_message() : 'Không tạo được attachment ảnh.'], 500);
    }
    $attach_data = wp_generate_attachment_metadata($attachment_id, (string) $uploaded['file']);
    wp_update_attachment_metadata($attachment_id, $attach_data);
    update_post_meta($attachment_id, '_aitrongcay_photo_owner', get_current_user_id());
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', $garden_key);
    update_post_meta($attachment_id, '_aitrongcay_photo_source', 'manual');
    update_post_meta($attachment_id, '_aitrongcay_pot_code', $pot_code);
    wp_update_post(['ID' => $attachment_id, 'post_title' => 'Ảnh ' . strtoupper($pot_code) . ' · ' . wp_date('H:i')]);
    aitrongcay_upsert_db_pot($garden_key, array_merge($pot_record, ['pot_code' => $pot_code, 'code' => $pot_code, 'pot_name' => $pot_name !== '' ? $pot_name : (string) ($pot_record['pot_name'] ?? $pot_code), 'name' => $pot_name !== '' ? $pot_name : (string) ($pot_record['name'] ?? $pot_code)]));
    $updated_latest = aitrongcay_set_latest_pot_photo($garden_key, $pot_code, $attachment_id);
    if (! $updated_latest) {
        wp_send_json_error(['message' => 'Ảnh đã upload nhưng chưa gắn được vào ảnh mới nhất của khoang. Em đã chặn để tránh báo thành công giả.'], 500);
    }
    $preview_url = aitrongcay_landscape_preview_url($attachment_id);
    if ($preview_url === '') {
        $preview_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'medium_large') ?: wp_get_attachment_url($attachment_id)));
    }
    wp_send_json_success([
        'id' => $attachment_id,
        'pot_code' => strtoupper($pot_code),
        'url' => $preview_url,
        'title' => get_the_title($attachment_id),
        'download' => wp_make_link_relative((string) wp_get_attachment_url($attachment_id)),
        'orientationClass' => '',
    ]);
}
add_action('wp_ajax_aitrongcay_upload_photo_attachment', 'aitrongcay_upload_photo_attachment_ajax');

function aitrongcay_upload_photo_attachment_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }
    check_admin_referer('aitrongcay_upload_photo_submit', 'aitrongcay_upload_photo_nonce');
    $posted_garden_key = trim(sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? '')));
    $garden_key = $posted_garden_key !== '' ? $posted_garden_key : aitrongcay_resolve_active_garden_key();
    $pot_code = strtoupper(trim(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''))));
    $pot_name = trim(sanitize_text_field((string) wp_unslash($_POST['pot_name'] ?? '')));
    $redirect = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/nhat-ky-cham-soc/')) : home_url('/portal/nhat-ky-cham-soc/');
    if ($pot_code !== '') {
        $redirect .= '#photo-' . strtolower($pot_code);
    }
    if (empty($_FILES['photo']) || $garden_key === '' || $pot_code === '' || ! aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
        wp_safe_redirect(add_query_arg('photo_upload', 'failed', $redirect));
        exit;
    }
    $pot_record = null;
    foreach (aitrongcay_get_custom_pots($garden_key, get_current_user_id()) as $pot) {
        $candidate_code = strtoupper(trim((string) ($pot['pot_code'] ?? $pot['code'] ?? '')));
        if ($candidate_code === $pot_code) {
            $pot_record = $pot;
            break;
        }
    }
    if (! is_array($pot_record)) {
        $pot_record = [
            'pot_code' => $pot_code,
            'code' => $pot_code,
            'pot_name' => $pot_name !== '' ? $pot_name : $pot_code,
            'name' => $pot_name !== '' ? $pot_name : $pot_code,
        ];
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $uploaded = wp_handle_upload($_FILES['photo'], ['test_form' => false]);
    if (! is_array($uploaded) || ! empty($uploaded['error']) || empty($uploaded['file'])) {
        wp_safe_redirect(add_query_arg('photo_upload', 'failed', $redirect));
        exit;
    }
    $attachment_id = wp_insert_attachment([
        'post_mime_type' => (string) ($uploaded['type'] ?? 'image/jpeg'),
        'post_title' => 'Ảnh ' . strtoupper($pot_code) . ' · ' . wp_date('H:i'),
        'post_status' => 'inherit',
        'post_author' => get_current_user_id(),
    ], (string) $uploaded['file']);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_safe_redirect(add_query_arg('photo_upload', 'failed', $redirect));
        exit;
    }
    $attach_data = wp_generate_attachment_metadata($attachment_id, (string) $uploaded['file']);
    wp_update_attachment_metadata($attachment_id, $attach_data);
    update_post_meta($attachment_id, '_aitrongcay_photo_owner', get_current_user_id());
    update_post_meta($attachment_id, '_aitrongcay_photo_garden_key', $garden_key);
    update_post_meta($attachment_id, '_aitrongcay_photo_source', 'manual');
    update_post_meta($attachment_id, '_aitrongcay_pot_code', $pot_code);
    wp_update_post(['ID' => $attachment_id, 'post_title' => 'Ảnh ' . strtoupper($pot_code) . ' · ' . wp_date('H:i')]);
    aitrongcay_upsert_db_pot($garden_key, array_merge($pot_record, ['pot_code' => $pot_code, 'code' => $pot_code, 'pot_name' => $pot_name !== '' ? $pot_name : (string) ($pot_record['pot_name'] ?? $pot_code), 'name' => $pot_name !== '' ? $pot_name : (string) ($pot_record['name'] ?? $pot_code)]));
    $updated_latest = aitrongcay_set_latest_pot_photo($garden_key, $pot_code, $attachment_id);
    wp_safe_redirect(add_query_arg('photo_upload', $updated_latest ? 'ok' : 'failed', $redirect));
    exit;
}
add_action('admin_post_aitrongcay_upload_photo_submit', 'aitrongcay_upload_photo_attachment_submit');

function aitrongcay_food_safety_heuristic_counts(string $file_path): array
{
    if (! function_exists('getimagesize') || ! function_exists('imagecreatefromstring')) {
        return [];
    }

    $blob = @file_get_contents($file_path);
    if (! is_string($blob) || $blob === '') {
        return [];
    }

    $image = @imagecreatefromstring($blob);
    if (! $image) {
        return [];
    }

    $width = imagesx($image);
    $height = imagesy($image);
    if ($width < 2 || $height < 2) {
        imagedestroy($image);
        return [];
    }

    $step = max(4, (int) floor(max($width, $height) / 180));
    $counts = [
        'Khuẩn lạc trắng/kem' => 0,
        'Khuẩn lạc vàng' => 0,
        'Khuẩn lạc đỏ/hồng' => 0,
        'Khuẩn lạc xanh/lục' => 0,
    ];

    for ($y = 0; $y < $height; $y += $step) {
        for ($x = 0; $x < $width; $x += $step) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $brightness = ($r + $g + $b) / 3;

            if ($brightness > 190 && abs($r - $g) < 22 && abs($g - $b) < 22) {
                $counts['Khuẩn lạc trắng/kem']++;
                continue;
            }
            if ($r > 170 && $g > 120 && $b < 120) {
                $counts['Khuẩn lạc vàng']++;
                continue;
            }
            if ($r > 150 && $g < 120 && $b < 150) {
                $counts['Khuẩn lạc đỏ/hồng']++;
                continue;
            }
            if ($g > 120 && $g > $r + 12 && $g > $b + 12) {
                $counts['Khuẩn lạc xanh/lục']++;
            }
        }
    }

    imagedestroy($image);

    foreach ($counts as $label => $count) {
        $counts[$label] = (int) round($count / 18);
    }

    return array_filter($counts, static fn($count): bool => (int) $count > 0);
}

function aitrongcay_food_safety_build_analysis(int $attachment_id): array
{
    $file_path = get_attached_file($attachment_id);
    $image_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id)));
    $counts = is_string($file_path) && $file_path !== '' ? aitrongcay_food_safety_heuristic_counts($file_path) : [];

    if ($counts === []) {
        $counts = [
            'Khuẩn lạc nghi trắng/kem' => 0,
            'Khuẩn lạc nghi vàng' => 0,
            'Khuẩn lạc nghi đỏ/hồng' => 0,
            'Khuẩn lạc nghi xanh/lục' => 0,
        ];
    }

    $items = [];
    foreach ($counts as $label => $count) {
        $item = [
            'label' => $label,
            'count' => (int) $count,
        ];

        if ($label === 'Khuẩn lạc vàng' || $label === 'Khuẩn lạc nghi vàng') {
            $item['possible_match'] = 'Vibrio cholerae (khuẩn tả)';
            $item['risk_flag'] = 'Nghi khuẩn tả';
            $item['status'] = 'suspected-cholera';
            $item['status_label'] = 'Nghi khuẩn tả';
            $item['confidence'] = min(96, 55 + ((int) $count * 6));
            $item['reference_note'] = 'Trên môi trường TCBS agar, Vibrio cholerae điển hình tạo khuẩn lạc vàng, dẹt, đường kính khoảng 2–3 mm do lên men sucrose làm môi trường chuyển vàng.';
            $item['reference_details'] = [
                'Môi trường tham chiếu: TCBS agar (Thiosulfate-Citrate-Bile Salts-Sucrose).',
                'Đặc điểm hình thái điển hình: khuẩn lạc vàng, dẹt, cỡ 2–3 mm.',
                'Cơ chế màu: lên men sucrose làm acid hóa môi trường và chỉ thị đổi sang vàng.',
                'Một số chủng V. cholerae có thể xuất hiện xanh hoặc không màu nếu lên men sucrose chậm.',
                'Lưu ý: đây là gợi ý hình thái tham chiếu, không phải kết luận định danh cuối cùng; vẫn cần test xác nhận bổ sung.',
            ];
            $item['reference_source'] = 'https://microbiologyinfo.com/thiosulfate-citrate-bile-salts-sucrose-tcbs-agar-composition-principle-uses-preparation-and-colony-morphology/';
        } elseif ($label === 'Khuẩn lạc xanh/lục' || $label === 'Khuẩn lạc nghi xanh/lục') {
            $item['possible_match'] = 'Vibrio parahaemolyticus / Pseudomonas / Aeromonas';
            $item['status'] = 'needs-confirmation';
            $item['status_label'] = 'Cần test xác nhận';
            $item['confidence'] = min(88, 38 + ((int) $count * 5));
            $item['reference_note'] = 'Trên TCBS agar, Vibrio parahaemolyticus thường cho khuẩn lạc không màu với tâm xanh-lục; Pseudomonas và Aeromonas có thể cho khuẩn lạc xanh, nên cần phân biệt tiếp.';
            $item['reference_details'] = [
                'Vibrio parahaemolyticus: thường không màu, có tâm xanh-lục.',
                'Pseudomonas, Aeromonas: có thể cho khuẩn lạc xanh trên TCBS agar.',
                'Nhóm này dễ gây nhầm lẫn nếu chỉ nhìn màu/khuẩn lạc trên đĩa.',
                'Cần test xác nhận bổ sung để tách đúng tác nhân.',
            ];
            $item['reference_source'] = 'https://microbiologyinfo.com/thiosulfate-citrate-bile-salts-sucrose-tcbs-agar-composition-principle-uses-preparation-and-colony-morphology/';
        } elseif ($label === 'Khuẩn lạc trắng/kem' || $label === 'Khuẩn lạc nghi trắng/kem') {
            $item['possible_match'] = 'Enterobacteria hoặc khuẩn lạc nhỏ không điển hình';
            $item['status'] = 'not-typical-cholera';
            $item['status_label'] = 'Không điển hình khuẩn tả';
            $item['confidence'] = min(82, 30 + ((int) $count * 4));
            $item['reference_note'] = 'Trên TCBS agar, Enterobacteria hoặc một số vi khuẩn khác có thể xuất hiện như khuẩn lạc nhỏ, trong/nhạt màu; không phải hình thái điển hình nhất của Vibrio cholerae.';
            $item['reference_details'] = [
                'Thường biểu hiện dưới dạng khuẩn lạc nhỏ, trong hoặc nhạt màu.',
                'Không đủ để quy về khuẩn tả nếu chỉ dựa trên hình thái này.',
                'Nên kết hợp thêm môi trường không chọn lọc và test xác nhận.',
            ];
            $item['reference_source'] = 'https://microbiologyinfo.com/thiosulfate-citrate-bile-salts-sucrose-tcbs-agar-composition-principle-uses-preparation-and-colony-morphology/';
        } else {
            $item['status'] = 'needs-confirmation';
            $item['status_label'] = 'Cần test xác nhận';
            $item['confidence'] = min(80, 25 + ((int) $count * 4));
        }

        $items[] = $item;
    }

    usort($items, static fn(array $a, array $b): int => (int) $b['count'] <=> (int) $a['count']);

    $cholera_hint = '';
    foreach ($items as $item) {
        if (! empty($item['possible_match']) && (string) $item['possible_match'] === 'Vibrio cholerae (khuẩn tả)' && (int) ($item['count'] ?? 0) > 0) {
            $cholera_hint = ' Trong ảnh có nhóm khuẩn lạc vàng, nên hệ thống đã gắn thêm tham chiếu TCBS agar cho Vibrio cholerae (khuẩn tả): khuẩn lạc vàng, dẹt, thường khoảng 2–3 mm.';
            break;
        }
    }

    $highlight = null;
    foreach ($items as $item) {
        if (! empty($item['risk_flag']) && (int) ($item['count'] ?? 0) > 0) {
            $highlight = [
                'title' => (string) $item['risk_flag'],
                'match' => (string) ($item['possible_match'] ?? ''),
                'note' => (string) ($item['reference_note'] ?? ''),
                'count' => (int) ($item['count'] ?? 0),
                'confidence' => (int) ($item['confidence'] ?? 0),
                'status_label' => (string) ($item['status_label'] ?? ''),
                'source' => (string) ($item['reference_source'] ?? ''),
            ];
            break;
        }
    }

    if (! $highlight && ! empty($items[0])) {
        $top = $items[0];
        $highlight = [
            'title' => (string) ($top['status_label'] ?? 'Cần test xác nhận'),
            'match' => (string) ($top['possible_match'] ?? ($top['label'] ?? 'Nhóm khuẩn lạc')),
            'note' => (string) ($top['reference_note'] ?? 'Đây là nhóm nổi bật nhất theo ảnh hiện tại, nhưng vẫn nên xác nhận thêm bằng test phù hợp.'),
            'count' => (int) ($top['count'] ?? 0),
            'confidence' => (int) ($top['confidence'] ?? 0),
            'status_label' => (string) ($top['status_label'] ?? 'Cần test xác nhận'),
            'source' => (string) ($top['reference_source'] ?? ''),
        ];
    }

    return [
        'image' => [
            'attachment_id' => $attachment_id,
            'url' => $image_url,
            'title' => get_the_title($attachment_id),
        ],
        'agent' => [
            'mode' => 'adapter-ready',
            'name' => 'AI Food Safety Vision',
            'note' => 'Kết quả hiện được sinh từ bộ phân loại ảnh nội bộ để phục vụ luồng upload/lưu/hiển thị. Muốn nhận diện chính xác tới tên vi khuẩn thực tế cần nối thêm vision model chuyên dụng hoặc workflow lab-review.',
        ],
        'results' => $items,
        'highlight' => $highlight,
        'summary' => $items !== []
            ? 'Đã đọc ảnh và gom các vùng khả nghi theo nhóm màu/khuẩn lạc hiển thị. Có thể xem đây là kết quả sàng lọc trực quan ban đầu theo từng ảnh.' . $cholera_hint
            : 'Chưa tách được cụm khuẩn lạc rõ từ ảnh này.',
        'analyzed_at' => current_time('mysql'),
    ];
}

function aitrongcay_save_food_safety_scan(int $attachment_id, array $analysis): int
{
    $post_id = wp_insert_post([
        'post_type' => 'aitr_food_safety_scan',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
        'post_title' => 'Food safety scan #' . $attachment_id . ' · ' . current_time('d/m/Y H:i'),
        'post_content' => (string) ($analysis['summary'] ?? ''),
    ], true);

    if (is_wp_error($post_id) || ! $post_id) {
        return 0;
    }

    update_post_meta($post_id, '_aitrongcay_food_scan_attachment_id', $attachment_id);
    update_post_meta($post_id, '_aitrongcay_food_scan_image_url', (string) (($analysis['image']['url'] ?? '')));
    update_post_meta($post_id, '_aitrongcay_food_scan_results', wp_json_encode($analysis['results'] ?? [], JSON_UNESCAPED_UNICODE));
    update_post_meta($post_id, '_aitrongcay_food_scan_payload', wp_json_encode($analysis, JSON_UNESCAPED_UNICODE));
    update_post_meta($post_id, '_aitrongcay_food_scan_analyzed_at', (string) ($analysis['analyzed_at'] ?? current_time('mysql')));

    return (int) $post_id;
}

function aitrongcay_get_food_safety_scans(int $limit = 12): array
{
    $posts = get_posts([
        'post_type' => 'aitr_food_safety_scan',
        'post_status' => 'publish',
        'posts_per_page' => max(1, $limit),
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    $items = [];
    foreach ($posts as $post) {
        $payload = json_decode((string) get_post_meta($post->ID, '_aitrongcay_food_scan_payload', true), true);
        if (! is_array($payload)) {
            continue;
        }
        $payload['scan_id'] = (int) $post->ID;
        $items[] = $payload;
    }

    return $items;
}

function aitrongcay_upload_food_safety_image_ajax(): void
{
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');

    if (empty($_FILES['food_safety_image']) || ! is_array($_FILES['food_safety_image'])) {
        wp_send_json_error(['message' => 'Chưa nhận được ảnh tải lên.'], 400);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $attachment_id = media_handle_upload('food_safety_image', 0);
    if (is_wp_error($attachment_id) || ! $attachment_id) {
        wp_send_json_error(['message' => 'Không tải được ảnh lên hệ thống.'], 500);
    }

    update_post_meta($attachment_id, '_aitrongcay_food_safety_source', 'food-safety-page');
    update_post_meta($attachment_id, '_aitrongcay_photo_owner', get_current_user_id());

    wp_send_json_success([
        'id' => $attachment_id,
        'url' => wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id))),
        'title' => get_the_title($attachment_id),
    ]);
}
add_action('wp_ajax_aitrongcay_upload_food_safety_image', 'aitrongcay_upload_food_safety_image_ajax');
add_action('wp_ajax_nopriv_aitrongcay_upload_food_safety_image', 'aitrongcay_upload_food_safety_image_ajax');

function aitrongcay_analyze_food_safety_image_ajax(): void
{
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');

    $attachment_id = absint($_POST['attachment_id'] ?? 0);
    if ($attachment_id <= 0 || get_post_type($attachment_id) !== 'attachment') {
        wp_send_json_error(['message' => 'Không tìm thấy ảnh để phân tích.'], 404);
    }

    $analysis = aitrongcay_food_safety_build_analysis($attachment_id);
    $scan_id = aitrongcay_save_food_safety_scan($attachment_id, $analysis);
    $analysis['scan_id'] = $scan_id;

    wp_send_json_success($analysis);
}
add_action('wp_ajax_aitrongcay_analyze_food_safety_image', 'aitrongcay_analyze_food_safety_image_ajax');
add_action('wp_ajax_nopriv_aitrongcay_analyze_food_safety_image', 'aitrongcay_analyze_food_safety_image_ajax');

function aitrongcay_analyze_latest_pot_photo_ajax(): void
{
    aitrongcay_require_portal_nonce();
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Anh/chị cần đăng nhập để phân tích ảnh.'], 403);
    }
    $user = wp_get_current_user();
    $garden_key = sanitize_text_field((string) wp_unslash($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key($user)));
    $pot_code = strtoupper(trim(sanitize_text_field((string) wp_unslash($_POST['pot_code'] ?? ''))));
    if ($garden_key === '' || $pot_code === '') {
        wp_send_json_error(['message' => 'Thiếu thông tin chậu cần phân tích.'], 400);
    }
    if (! aitrongcay_user_can_view_garden($garden_key, (int) $user->ID)) {
        wp_send_json_error(['message' => 'Anh/chị chưa có quyền phân tích chậu này.'], 403);
    }
    $pot_record = null;
    foreach (aitrongcay_get_db_pots($garden_key) as $pot) {
        if (strtoupper(trim((string) ($pot['pot_code'] ?? ''))) === $pot_code) {
            $pot_record = $pot;
            break;
        }
    }
    if (! is_array($pot_record)) {
        wp_send_json_error(['message' => 'Không tìm thấy chậu cần phân tích.'], 404);
    }
    $photo_context = aitrongcay_get_same_day_pot_photo_context($garden_key, $pot_code);
    if (! $photo_context) {
        wp_send_json_error(['message' => 'Chậu này chưa có ảnh mới nhất để phân tích.'], 400);
    }
    $analysis = aitrongcay_generate_pot_analysis($pot_record, $photo_context);
    aitrongcay_store_pot_analysis($garden_key, $pot_code, $analysis);
    wp_send_json_success([
        'pot_code' => $pot_code,
        'analysis' => $analysis,
        'photo' => $photo_context,
    ]);
}
add_action('wp_ajax_aitrongcay_analyze_latest_pot_photo', 'aitrongcay_analyze_latest_pot_photo_ajax');

function aitrongcay_toggle_market_like_ajax(): void
{
    aitrongcay_require_portal_nonce();
    $post_id = absint($_POST['post_id'] ?? 0);
    $post = get_post($post_id);
    if (! $post || $post->post_type !== 'aitr_market_post') {
        wp_send_json_error(['message' => 'Không tìm thấy tin đăng.'], 404);
    }
    $user_id = get_current_user_id();
    $likes = array_map('intval', (array) get_post_meta($post_id, '_aitrongcay_market_likes', true));
    if (in_array($user_id, $likes, true)) {
        $likes = array_values(array_diff($likes, [$user_id]));
        $liked = false;
    } else {
        $likes[] = $user_id;
        $likes = array_values(array_unique($likes));
        $liked = true;
    }
    update_post_meta($post_id, '_aitrongcay_market_likes', $likes);
    wp_send_json_success(['liked' => $liked, 'count' => count($likes)]);
}
add_action('wp_ajax_aitrongcay_toggle_market_like', 'aitrongcay_toggle_market_like_ajax');

function aitrongcay_send_friend_request_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $target = sanitize_text_field((string) ($_POST['target'] ?? ''));
    $current_user_id = get_current_user_id();
    $target_user = is_email($target) ? get_user_by('email', $target) : get_user_by('login', $target);
    if (! $target_user instanceof WP_User) {
        wp_send_json_error(['message' => 'Không tìm thấy người dùng.'], 404);
    }
    if ((int) $target_user->ID === $current_user_id) {
        wp_send_json_error(['message' => 'Không thể tự kết nối với chính mình.'], 400);
    }
    $table = aitrongcay_friendships_table();
    $pair_key = aitrongcay_friend_pair_key($current_user_id, (int) $target_user->ID);
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE unique_pair_key = %s LIMIT 1", $pair_key));
    if ($exists) {
        wp_send_json_error(['message' => 'Hai người đã kết nối hoặc đang chờ lời mời kết nối.'], 400);
    }
    $wpdb->insert($table, [
        'requester_user_id' => $current_user_id,
        'addressee_user_id' => (int) $target_user->ID,
        'unique_pair_key' => $pair_key,
        'status' => 'pending',
        'created_at' => current_time('mysql'),
        'responded_at' => null,
    ]);
    wp_send_json_success(['message' => 'Đã gửi lời mời kết nối.']);
}
add_action('wp_ajax_aitrongcay_send_friend_request', 'aitrongcay_send_friend_request_ajax');

function aitrongcay_accept_friend_request_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $id = isset($_POST['friendship_id']) ? (int) $_POST['friendship_id'] : 0;
    $table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    if (! is_array($row) || (int) $row['addressee_user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'pending') {
        wp_send_json_error(['message' => 'Không tìm thấy lời mời hợp lệ.'], 404);
    }
    $wpdb->update($table, [
        'status' => 'accepted',
        'responded_at' => current_time('mysql'),
    ], ['id' => $id]);
    wp_send_json_success(['message' => 'Đã chấp nhận kết nối hàng xóm.']);
}
add_action('wp_ajax_aitrongcay_accept_friend_request', 'aitrongcay_accept_friend_request_ajax');

function aitrongcay_reject_friend_request_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $id = isset($_POST['friendship_id']) ? (int) $_POST['friendship_id'] : 0;
    $table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    if (! is_array($row) || (int) $row['addressee_user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'pending') {
        wp_send_json_error(['message' => 'Không tìm thấy lời mời hợp lệ.'], 404);
    }
    $wpdb->update($table, [
        'status' => 'declined',
        'responded_at' => current_time('mysql'),
    ], ['id' => $id]);
    wp_send_json_success(['message' => 'Đã từ chối lời mời kết nối.']);
}
add_action('wp_ajax_aitrongcay_reject_friend_request', 'aitrongcay_reject_friend_request_ajax');

function aitrongcay_remove_friend_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;

    $friend_user_id = isset($_POST['friend_user_id']) ? (int) $_POST['friend_user_id'] : 0;
    $current_user_id = get_current_user_id();
    if ($friend_user_id <= 0 || $friend_user_id === $current_user_id) {
        wp_send_json_error(['message' => 'Người bạn không hợp lệ.'], 400);
    }

    $friendships_table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$friendships_table} WHERE status = 'accepted' AND ((requester_user_id = %d AND addressee_user_id = %d) OR (requester_user_id = %d AND addressee_user_id = %d)) LIMIT 1",
        $current_user_id,
        $friend_user_id,
        $friend_user_id,
        $current_user_id
    ), ARRAY_A);

    if (! is_array($row)) {
        wp_send_json_error(['message' => 'Không tìm thấy kết nối hàng xóm để hủy.'], 404);
    }

    $wpdb->delete($friendships_table, ['id' => (int) $row['id']], ['%d']);

    $members_table = aitrongcay_garden_members_table();
    $owned_gardens = $wpdb->get_col($wpdb->prepare(
        "SELECT garden_key FROM {$members_table} WHERE user_id = %d AND role = 'owner' AND status = 'active'",
        $current_user_id
    )) ?: [];

    if ($owned_gardens) {
        foreach ($owned_gardens as $owned_garden_key) {
            $owned_garden_key = trim((string) $owned_garden_key);
            if ($owned_garden_key === '') {
                continue;
            }
            $wpdb->delete($members_table, [
                'garden_key' => $owned_garden_key,
                'user_id' => $friend_user_id,
            ], ['%s', '%d']);
        }
    }

    wp_send_json_success(['message' => 'Đã hủy kết nối hàng xóm.']);
}
add_action('wp_ajax_aitrongcay_remove_friend', 'aitrongcay_remove_friend_ajax');

function aitrongcay_friend_toggle_share_url(int $friend_user_id, int $membership_id, string $role): string
{
    $role = $role === 'co_owner' ? 'viewer' : 'co_owner';
    $url = add_query_arg([
        'action' => 'aitrongcay_friend_toggle_share',
        'friend_user_id' => $friend_user_id,
        'membership_id' => $membership_id,
        'role' => $role,
        'redirect_to' => home_url('/portal/hang-xom/'),
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_friend_toggle_share_' . $friend_user_id . '_' . $membership_id . '_' . $role);
}

function aitrongcay_remove_friend_url(int $friend_user_id): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_remove_friend_direct',
        'friend_user_id' => $friend_user_id,
        'redirect_to' => home_url('/portal/hang-xom/'),
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_remove_friend_direct_' . $friend_user_id);
}

function aitrongcay_send_friend_request_url(string $target, string $redirect_to = '', string $owner_search = ''): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_send_friend_request_direct',
        'target' => $target,
        'redirect_to' => $redirect_to !== '' ? $redirect_to : home_url('/portal/hang-xom/'),
        'owner_search' => $owner_search,
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_send_friend_request_direct_' . md5($target));
}

function aitrongcay_cancel_friend_request_url(int $friend_user_id, string $redirect_to = '', string $owner_search = ''): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_cancel_friend_request_direct',
        'friend_user_id' => $friend_user_id,
        'redirect_to' => $redirect_to !== '' ? $redirect_to : home_url('/portal/hang-xom/'),
        'owner_search' => $owner_search,
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_cancel_friend_request_direct_' . $friend_user_id);
}

function aitrongcay_accept_friend_request_url(int $friendship_id, string $redirect_to = ''): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_accept_friend_request_direct',
        'friendship_id' => $friendship_id,
        'redirect_to' => $redirect_to !== '' ? $redirect_to : home_url('/portal/hang-xom/'),
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_accept_friend_request_direct_' . $friendship_id);
}

function aitrongcay_reject_friend_request_url(int $friendship_id, string $redirect_to = ''): string
{
    $url = add_query_arg([
        'action' => 'aitrongcay_reject_friend_request_direct',
        'friendship_id' => $friendship_id,
        'redirect_to' => $redirect_to !== '' ? $redirect_to : home_url('/portal/hang-xom/'),
    ], admin_url('admin-post.php'));
    return wp_nonce_url($url, 'aitrongcay_reject_friend_request_direct_' . $friendship_id);
}

function aitrongcay_friend_toggle_share_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    $friend_user_id = isset($_GET['friend_user_id']) ? (int) $_GET['friend_user_id'] : 0;
    $membership_id = isset($_GET['membership_id']) ? (int) $_GET['membership_id'] : 0;
    $role = sanitize_key((string) ($_GET['role'] ?? 'viewer'));
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));

    check_admin_referer('aitrongcay_friend_toggle_share_' . $friend_user_id . '_' . $membership_id . '_' . $role);

    if (! in_array($role, ['co_owner', 'viewer'], true) || $friend_user_id <= 0) {
        wp_safe_redirect(add_query_arg('friend_action', 'invalid', $redirect_to));
        exit;
    }

    global $wpdb;
    $garden_key = aitrongcay_resolve_active_garden_key(wp_get_current_user());
    if (aitrongcay_user_garden_role($garden_key, get_current_user_id()) !== 'owner') {
        wp_safe_redirect(add_query_arg('friend_action', 'forbidden', $redirect_to));
        exit;
    }

    $table = aitrongcay_garden_members_table();
    if ($membership_id > 0) {
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $membership_id), ARRAY_A);
        if (! is_array($row)) {
            wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
            exit;
        }
        $wpdb->update($table, [
            'role' => $role,
            'updated_at' => current_time('mysql'),
        ], ['id' => $membership_id]);
    } else {
        $wpdb->insert($table, [
            'garden_key' => $garden_key,
            'user_id' => $friend_user_id,
            'role' => $role,
            'status' => 'active',
            'invited_by_user_id' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ], ['%s', '%d', '%s', '%s', '%d', '%s', '%s']);
    }

    if (function_exists('aitrongcay_add_notification')) {
        $current_user = wp_get_current_user();
        $sender_name = $current_user->display_name ?: $current_user->user_login;
        if ($role === 'co_owner') {
            aitrongcay_add_notification(
                $friend_user_id,
                '🪴 Bạn được mời xem vườn',
                sprintf('Hàng xóm %s vừa chia sẻ khu vườn của họ cho bạn.', esc_html($sender_name)),
                home_url('/portal/dashboard-2/?garden=' . rawurlencode($garden_key))
            );
        } else {
            aitrongcay_add_notification(
                $friend_user_id,
                '🚫 Hủy chia sẻ vườn',
                sprintf('Hàng xóm %s đã ngừng chia sẻ khu vườn với bạn.', esc_html($sender_name)),
                '#'
            );
        }
    }

    aitrongcay_remember_selected_garden_key($friend_user_id, $garden_key);
    wp_safe_redirect(add_query_arg('friend_action', 'share-updated', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_friend_toggle_share', 'aitrongcay_friend_toggle_share_submit');

function aitrongcay_remove_friend_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    global $wpdb;

    $friend_user_id = isset($_GET['friend_user_id']) ? (int) $_GET['friend_user_id'] : 0;
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));
    check_admin_referer('aitrongcay_remove_friend_direct_' . $friend_user_id);

    $current_user_id = get_current_user_id();
    if ($friend_user_id <= 0 || $friend_user_id === $current_user_id) {
        wp_safe_redirect(add_query_arg('friend_action', 'invalid', $redirect_to));
        exit;
    }

    $friendships_table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$friendships_table} WHERE status = 'accepted' AND ((requester_user_id = %d AND addressee_user_id = %d) OR (requester_user_id = %d AND addressee_user_id = %d)) LIMIT 1",
        $current_user_id,
        $friend_user_id,
        $friend_user_id,
        $current_user_id
    ), ARRAY_A);

    if (! is_array($row)) {
        wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
        exit;
    }

    $wpdb->delete($friendships_table, ['id' => (int) $row['id']], ['%d']);

    $members_table = aitrongcay_garden_members_table();
    $owned_gardens = $wpdb->get_col($wpdb->prepare(
        "SELECT garden_key FROM {$members_table} WHERE user_id = %d AND role = 'owner' AND status = 'active'",
        $current_user_id
    )) ?: [];

    if ($owned_gardens) {
        foreach ($owned_gardens as $owned_garden_key) {
            $owned_garden_key = trim((string) $owned_garden_key);
            if ($owned_garden_key === '') {
                continue;
            }
            $wpdb->delete($members_table, [
                'garden_key' => $owned_garden_key,
                'user_id' => $friend_user_id,
            ], ['%s', '%d']);
        }
    }

    wp_safe_redirect(add_query_arg('friend_action', 'removed', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_remove_friend_direct', 'aitrongcay_remove_friend_direct_submit');

function aitrongcay_send_friend_request_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    $target = sanitize_text_field((string) ($_GET['target'] ?? ''));
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));
    $owner_search = sanitize_text_field((string) ($_GET['owner_search'] ?? ''));
    check_admin_referer('aitrongcay_send_friend_request_direct_' . md5($target));

    if ($owner_search !== '') {
        $redirect_to = add_query_arg('owner_search', $owner_search, $redirect_to);
    }

    if ($target === '') {
        wp_safe_redirect(add_query_arg('friend_action', 'invalid', $redirect_to));
        exit;
    }

    global $wpdb;
    $current_user_id = get_current_user_id();
    $target_user = get_user_by('login', $target);
    if (! $target_user instanceof WP_User && is_email($target)) {
        $target_user = get_user_by('email', $target);
    }
    if (! $target_user instanceof WP_User || (int) $target_user->ID === $current_user_id) {
        wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
        exit;
    }

    $table = aitrongcay_friendships_table();
    $pair_key = aitrongcay_friend_pair_key($current_user_id, (int) $target_user->ID);
    $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE unique_pair_key = %s LIMIT 1", $pair_key));
    if ($exists) {
        wp_safe_redirect(add_query_arg('friend_action', 'exists', $redirect_to));
        exit;
    }

    $wpdb->insert($table, [
        'requester_user_id' => $current_user_id,
        'addressee_user_id' => (int) $target_user->ID,
        'unique_pair_key' => $pair_key,
        'status' => 'pending',
        'created_at' => current_time('mysql'),
        'responded_at' => null,
    ]);

    wp_safe_redirect(add_query_arg('friend_action', 'request-sent', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_send_friend_request_direct', 'aitrongcay_send_friend_request_direct_submit');

function aitrongcay_cancel_friend_request_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    global $wpdb;
    $friend_user_id = isset($_GET['friend_user_id']) ? (int) $_GET['friend_user_id'] : 0;
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));
    $owner_search = sanitize_text_field((string) ($_GET['owner_search'] ?? ''));
    check_admin_referer('aitrongcay_cancel_friend_request_direct_' . $friend_user_id);

    if ($owner_search !== '') {
        $redirect_to = add_query_arg('owner_search', $owner_search, $redirect_to);
    }

    $current_user_id = get_current_user_id();
    if ($friend_user_id <= 0 || $friend_user_id === $current_user_id) {
        wp_safe_redirect(add_query_arg('friend_action', 'invalid', $redirect_to));
        exit;
    }

    $table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE status = 'pending' AND requester_user_id = %d AND addressee_user_id = %d LIMIT 1",
        $current_user_id,
        $friend_user_id
    ), ARRAY_A);

    if (! is_array($row)) {
        wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
        exit;
    }

    $wpdb->delete($table, ['id' => (int) $row['id']], ['%d']);
    wp_safe_redirect(add_query_arg('friend_action', 'request-cancelled', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_cancel_friend_request_direct', 'aitrongcay_cancel_friend_request_direct_submit');

function aitrongcay_accept_friend_request_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    global $wpdb;
    $friendship_id = isset($_GET['friendship_id']) ? (int) $_GET['friendship_id'] : 0;
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));
    check_admin_referer('aitrongcay_accept_friend_request_direct_' . $friendship_id);

    $table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $friendship_id), ARRAY_A);
    if (! is_array($row) || (int) $row['addressee_user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'pending') {
        wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
        exit;
    }

    $wpdb->update($table, [
        'status' => 'accepted',
        'responded_at' => current_time('mysql'),
    ], ['id' => $friendship_id]);

    wp_safe_redirect(add_query_arg('friend_action', 'accepted', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_accept_friend_request_direct', 'aitrongcay_accept_friend_request_direct_submit');

function aitrongcay_reject_friend_request_direct_submit(): void
{
    if (! is_user_logged_in()) {
        wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
        exit;
    }

    global $wpdb;
    $friendship_id = isset($_GET['friendship_id']) ? (int) $_GET['friendship_id'] : 0;
    $redirect_to = esc_url_raw((string) ($_GET['redirect_to'] ?? home_url('/portal/hang-xom/')));
    check_admin_referer('aitrongcay_reject_friend_request_direct_' . $friendship_id);

    $table = aitrongcay_friendships_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $friendship_id), ARRAY_A);
    if (! is_array($row) || (int) $row['addressee_user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'pending') {
        wp_safe_redirect(add_query_arg('friend_action', 'notfound', $redirect_to));
        exit;
    }

    $wpdb->update($table, [
        'status' => 'declined',
        'responded_at' => current_time('mysql'),
    ], ['id' => $friendship_id]);

    wp_safe_redirect(add_query_arg('friend_action', 'declined', $redirect_to));
    exit;
}
add_action('admin_post_aitrongcay_reject_friend_request_direct', 'aitrongcay_reject_friend_request_direct_submit');

function aitrongcay_invite_garden_member_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $garden_key = sanitize_text_field((string) ($_POST['garden_key'] ?? aitrongcay_resolve_active_garden_key(wp_get_current_user())));
    if (aitrongcay_user_garden_role($garden_key, get_current_user_id()) !== 'owner') {
        wp_send_json_error(['message' => 'Chỉ chủ vườn mới được phân quyền người khác.'], 403);
    }
    $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $role = sanitize_key((string) ($_POST['role'] ?? 'viewer'));
    if (! in_array($role, ['co_owner', 'viewer'], true)) {
        wp_send_json_error(['message' => 'Vai trò không hợp lệ.'], 400);
    }
    if ($user_id <= 0 || $user_id === get_current_user_id()) {
        wp_send_json_error(['message' => 'Người được chọn không hợp lệ.'], 400);
    }

    $friendships_table = aitrongcay_friendships_table();
    $is_friend = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$friendships_table} WHERE status = 'accepted' AND ((requester_user_id = %d AND addressee_user_id = %d) OR (requester_user_id = %d AND addressee_user_id = %d)) LIMIT 1",
        get_current_user_id(),
        $user_id,
        $user_id,
        get_current_user_id()
    ));
    if ($is_friend <= 0) {
        wp_send_json_error(['message' => 'Chỉ có thể phân quyền cho người đã là hàng xóm.'], 403);
    }

    $table = aitrongcay_garden_members_table();
    $existing = $wpdb->get_row($wpdb->prepare("SELECT id, status, role FROM {$table} WHERE garden_key = %s AND user_id = %d LIMIT 1", $garden_key, $user_id), ARRAY_A);
    $payload = [
        'role' => $role,
        'status' => 'active',
        'invited_by_user_id' => get_current_user_id(),
        'updated_at' => current_time('mysql'),
    ];

    if (is_array($existing) && ! empty($existing['id'])) {
        $wpdb->update($table, $payload, ['id' => (int) $existing['id']], ['%s', '%s', '%d', '%s'], ['%d']);
    } else {
        $wpdb->insert($table, array_merge([
            'garden_key' => $garden_key,
            'user_id' => $user_id,
            'created_at' => current_time('mysql'),
        ], $payload), ['%s', '%d', '%s', '%s', '%s', '%d', '%s']);
    }

    aitrongcay_remember_selected_garden_key($user_id, $garden_key);
    $target_user = get_user_by('id', $user_id);
    $role_label = aitrongcay_get_role_label($role);
    wp_send_json_success([
        'message' => sprintf('Đã cấp quyền %s ngay cho %s.', $role_label, $target_user instanceof WP_User ? ($target_user->display_name ?: $target_user->user_login) : 'người dùng này'),
        'garden_key' => $garden_key,
        'role' => $role,
        'status' => 'active',
    ]);
}
add_action('wp_ajax_aitrongcay_invite_garden_member', 'aitrongcay_invite_garden_member_ajax');

function aitrongcay_accept_garden_invite_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
    $table = aitrongcay_garden_members_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    if (! is_array($row) || (int) $row['user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'invited') {
        wp_send_json_error(['message' => 'Không tìm thấy lời mời hợp lệ.'], 404);
    }
    $wpdb->update($table, [
        'status' => 'active',
        'updated_at' => current_time('mysql'),
    ], ['id' => $id]);

    aitrongcay_remember_selected_garden_key(get_current_user_id(), (string) ($row['garden_key'] ?? ''));
    wp_send_json_success(['message' => 'Đã tham gia khu vườn.', 'garden_key' => $row['garden_key'] ?? '']);
}
add_action('wp_ajax_aitrongcay_accept_garden_invite', 'aitrongcay_accept_garden_invite_ajax');

function aitrongcay_decline_garden_invite_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
    $table = aitrongcay_garden_members_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id), ARRAY_A);
    if (! is_array($row) || (int) $row['user_id'] !== get_current_user_id() || ($row['status'] ?? '') !== 'invited') {
        wp_send_json_error(['message' => 'Không tìm thấy lời mời hợp lệ.'], 404);
    }
    $wpdb->update($table, [
        'status' => 'declined',
        'updated_at' => current_time('mysql'),
    ], ['id' => $id]);
    wp_send_json_success(['message' => 'Đã từ chối lời mời vào khu vườn.']);
}
add_action('wp_ajax_aitrongcay_decline_garden_invite', 'aitrongcay_decline_garden_invite_ajax');

function aitrongcay_update_garden_member_role_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $membership_id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
    $role = sanitize_key((string) ($_POST['role'] ?? 'viewer'));
    if (! in_array($role, ['co_owner', 'viewer'], true)) {
        wp_send_json_error(['message' => 'Vai trò không hợp lệ.'], 400);
    }
    $table = aitrongcay_garden_members_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $membership_id), ARRAY_A);
    if (! is_array($row)) {
        wp_send_json_error(['message' => 'Không tìm thấy thành viên.'], 404);
    }
    if (aitrongcay_user_garden_role((string) $row['garden_key'], get_current_user_id()) !== 'owner') {
        wp_send_json_error(['message' => 'Chỉ chủ vườn mới được đổi quyền.'], 403);
    }
    if (($row['role'] ?? '') === 'owner') {
        wp_send_json_error(['message' => 'Không thể đổi quyền của chủ vườn.'], 400);
    }
    $wpdb->update($table, [
        'role' => $role,
        'updated_at' => current_time('mysql'),
    ], ['id' => $membership_id]);
    wp_send_json_success(['message' => 'Đã cập nhật quyền thành viên.']);
}
add_action('wp_ajax_aitrongcay_update_garden_member_role', 'aitrongcay_update_garden_member_role_ajax');

function aitrongcay_remove_garden_member_ajax(): void
{
    aitrongcay_require_portal_nonce();
    global $wpdb;
    $membership_id = isset($_POST['membership_id']) ? (int) $_POST['membership_id'] : 0;
    $table = aitrongcay_garden_members_table();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $membership_id), ARRAY_A);
    if (! is_array($row)) {
        wp_send_json_error(['message' => 'Không tìm thấy thành viên.'], 404);
    }
    if (aitrongcay_user_garden_role((string) $row['garden_key'], get_current_user_id()) !== 'owner') {
        wp_send_json_error(['message' => 'Chỉ chủ vườn mới được gỡ thành viên.'], 403);
    }
    if (($row['role'] ?? '') === 'owner') {
        wp_send_json_error(['message' => 'Không thể gỡ chủ vườn.'], 400);
    }
    $wpdb->delete($table, ['id' => $membership_id], ['%d']);
    wp_send_json_success(['message' => ($row['status'] ?? '') === 'invited' ? 'Đã hủy lời mời.' : 'Đã gỡ thành viên khỏi khu vườn.']);
}
add_action('wp_ajax_aitrongcay_remove_garden_member', 'aitrongcay_remove_garden_member_ajax');

function aitrongcay_apply_pending_rack3_mapping(): void
{
    $flag = 'aitrongcay_rack3_mapping_v1_applied';
    if (get_option($flag)) {
        return;
    }

    if (! function_exists('aitrongcay_get_saved_blynk_configs') || ! function_exists('aitrongcay_save_blynk_configs') || ! function_exists('aitrongcay_blynk_default_config') || ! function_exists('aitrongcay_list_racks')) {
        return;
    }

    $rack = null;
    foreach ((array) aitrongcay_list_racks() as $item) {
        if (strtoupper(trim((string) ($item['rack_code'] ?? ''))) === 'RACK_3') {
            $rack = $item;
            break;
        }
    }
    if (! is_array($rack)) {
        return;
    }

    $garden_key = 'inventory:RACK_3';
    $token = 'Xxq5KpwPz5AkLcf9_tr4e545lFQ7pcDK';
    $configs = aitrongcay_get_saved_blynk_configs();
    $config = isset($configs[$garden_key]) && is_array($configs[$garden_key])
        ? $configs[$garden_key]
        : aitrongcay_blynk_default_config();

    $config['base'] = 'https://blynk.cloud/external/api';
    $config['token'] = $token;
    $config['vpins']['temp'] = 'V0';
    $config['vpins']['hum'] = 'V1';
    $config['vpins']['pump'] = 'V2';
    $config['vpins']['soil'] = 'V11';
    $config['devices']['pump'] = 'pump';

    foreach (range(1, 12) as $i) {
        $light_key = 'light' . $i;
        $pot_code = sprintf('P-%03d', $i);
        $config['vpins'][$light_key] = 'V' . (4 + $i);
        $config['devices'][$light_key] = $light_key;
        $config['pots'][$pot_code] = $light_key;
        $config['pot_tokens'][$pot_code] = '__shared__';
    }

    foreach (range(1, 6) as $compartment) {
        $config['vpins']['inlet' . $compartment] = 'V' . (17 + (($compartment - 1) * 2));
        $config['vpins']['drain' . $compartment] = 'V' . (18 + (($compartment - 1) * 2));
        $config['devices']['inlet' . $compartment] = 'inlet' . $compartment;
        $config['devices']['drain' . $compartment] = 'drain' . $compartment;
    }

    $configs[$garden_key] = $config;
    aitrongcay_save_blynk_configs($configs);

    if (function_exists('aitrongcay_update_rack_hardware')) {
        aitrongcay_update_rack_hardware((int) ($rack['id'] ?? 0), [
            'blynk_auth_token' => $token,
            'slot_count' => 12,
        ]);
    }

    update_option($flag, current_time('mysql'), false);
}
add_action('init', 'aitrongcay_apply_pending_rack3_mapping', 80);
// ─── AJAX: Resolve Robot Node to Garden & Pot ─────────────────────────────────
add_action('wp_ajax_aitrongcay_resolve_robot_node', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    
    $cmd = sanitize_text_field($_POST['command'] ?? '');
    if (strlen($cmd) !== 5) {
        wp_send_json_error('Invalid command');
    }
    
    $node = substr($cmd, 0, 3); // N01
    $tier = substr($cmd, 3, 2); // H0
    
    $nodeNum = (int) substr($node, 1);
    $rackIndex = floor($nodeNum / 3);
    
    $trayIndex = (int) substr($tier, 1);
    $slotIndex = $trayIndex + 1; // 1-based
    
    global $wpdb;
    $rack_table = $wpdb->prefix . 'aitr_garden_racks';
    $slot_table = $wpdb->prefix . 'aitr_garden_rack_slots';
    
    // Racks are physically ordered, we assume ID or chronological order.
    $racks = $wpdb->get_results("SELECT * FROM {$rack_table} ORDER BY id ASC", ARRAY_A);
    $target_rack = $racks[$rackIndex] ?? null;
    
    if (!$target_rack) {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied for unassigned rack');
        }
        wp_send_json_success([
            'garden_key' => '',
            'pot_code' => sprintf('P-%03d', $slotIndex)
        ]);
        return;
    }
    
    $garden_key = $target_rack['garden_key'] ?? '';
    
    if (!current_user_can('manage_options')) {
        if ($garden_key === '' || !aitrongcay_user_can_control_garden($garden_key, get_current_user_id())) {
            wp_send_json_error('Permission denied for this garden');
        }
    }
    
    // Find slot
    $pot_code = sprintf('P-%03d', $slotIndex);
    if ($target_rack['id']) {
        $slot = $wpdb->get_row($wpdb->prepare("SELECT pot_code FROM {$slot_table} WHERE rack_id = %d AND slot_index = %d", $target_rack['id'], $slotIndex), ARRAY_A);
        if (!empty($slot['pot_code'])) {
            $pot_code = $slot['pot_code'];
        }
    }
    
    wp_send_json_success([
        'garden_key' => $garden_key,
        'pot_code' => $pot_code
    ]);
});

add_action('wp_ajax_aitrongcay_dismiss_notice', 'aitrongcay_dismiss_notice');
add_action('wp_ajax_nopriv_aitrongcay_dismiss_notice', 'aitrongcay_dismiss_notice');
function aitrongcay_dismiss_notice() {
    $id = sanitize_text_field($_GET['id'] ?? '');
    $garden_key = sanitize_text_field($_GET['garden_key'] ?? '');
    if ($id && $garden_key) {
        $notices = get_option('aitr_garden_notices_' . $garden_key, []);
        $new_notices = [];
        foreach ($notices as $n) {
            if ($n['id'] !== $id) {
                $new_notices[] = $n;
            }
        }
        update_option('aitr_garden_notices_' . $garden_key, $new_notices);
    }
    wp_send_json_success();
}

add_filter('comment_post_redirect', function ($location, $comment) {
    if (empty($comment->comment_post_ID)) return $location;
    $post = get_post($comment->comment_post_ID);
    if ($post && $post->post_type === 'aitr_market_post') {
        $referer = wp_get_referer();
        if ($referer) {
            $referer = preg_replace('/#.*/', '', $referer);
            return $referer . '#comment-' . $comment->comment_ID;
        }

        $garden_key = get_post_meta($post->ID, '_aitrongcay_market_garden_key', true);
        $url = home_url('/cho-que/');
        if ($garden_key) {
            $url = add_query_arg('garden', $garden_key, $url);
        }
        return $url . '#comment-' . $comment->comment_ID;
    }
    return $location;
}, 10, 2);

add_action('wp_ajax_aitrongcay_water_friend_garden', function() {
    $current_user_id = get_current_user_id();
    if (!$current_user_id) {
        wp_send_json_error(['message' => 'Bạn cần đăng nhập.']);
    }
    $friend_id = isset($_POST['friend_id']) ? (int) $_POST['friend_id'] : 0;
    if (!$friend_id) {
        wp_send_json_error(['message' => 'Lỗi xác định hàng xóm.']);
    }
    
    // Ensure they are actually friends
    $is_friend = false;
    $friends = aitrongcay_get_user_friends($current_user_id);
    foreach ($friends as $f) {
        if ($f['requester_user_id'] == $friend_id || $f['addressee_user_id'] == $friend_id) {
            $is_friend = true;
            break;
        }
    }
    if (!$is_friend) {
        wp_send_json_error(['message' => 'Chỉ có thể tưới nước cho hàng xóm đã kết nối.']);
    }
    
    // Rate limit: 1 time per day per friend
    $last_water_key = "_aitrongcay_watered_{$friend_id}_" . gmdate('Ymd');
    $has_watered = get_user_meta($current_user_id, $last_water_key, true);
    if ($has_watered) {
        wp_send_json_error(['message' => 'Bạn đã tưới nước cho vườn này hôm nay rồi!']);
    }
    
    update_user_meta($current_user_id, $last_water_key, 1);

    // Create a notification for the neighbor
    if (function_exists('aitrongcay_add_notification')) {
        $sender = get_user_by('id', $current_user_id);
        $sender_name = $sender ? ($sender->display_name ?: $sender->user_login) : 'Một người hàng xóm';
        
        $msg_title = '💦 Tưới nước hộ';
        $msg_body = 'Hàng xóm <b>' . esc_html($sender_name) . '</b> vừa ghé thăm và tặng 1 gáo nước mát cho khu vườn của bạn trên ứng dụng!';
        
        aitrongcay_add_notification(
            $friend_id,
            $msg_title,
            $msg_body,
            home_url('/portal/hang-xom/')
        );
        
        // Push to Email if enabled (remind them of their REAL plants)
        $friend_user = get_user_by('id', $friend_id);
        $wants_email = (string) get_user_meta($friend_id, 'aitrongcay_notify_email', true) !== '0';
        
        if ($friend_user && $wants_email) {
            $email_subject = '[Ai trồng cây] Hàng xóm vừa ghé thăm khu vườn của bạn!';
            $email_content = "Chào " . ($friend_user->display_name ?: 'bạn') . ",\n\n"
                           . "Hàng xóm " . $sender_name . " vừa ghé thăm và tưới nước ảo cho khu vườn của bạn.\n"
                           . "Hành động này mang ý nghĩa nhắc nhở: Những chậu cây thật của bạn ngoài đời có đang khát nước không? Đừng quên ra thăm và tưới cho chúng nhé!\n\n"
                           . "Xem chi tiết tại: " . home_url('/portal/hang-xom/');
            wp_mail($friend_user->user_email, $email_subject, $email_content);
        }
    }
    
    // Track watered count for the receiver's garden
    $watered_count = (int) get_user_meta($friend_id, '_aitrongcay_garden_watered_count', true);
    update_user_meta($friend_id, '_aitrongcay_garden_watered_count', $watered_count + 1);
    
    // Add 10 Eco Points
    $points = 10;
    $current_points = (int) get_user_meta($current_user_id, '_aitrongcay_eco_points', true);
    update_user_meta($current_user_id, '_aitrongcay_eco_points', $current_points + $points);
    
    wp_send_json_success(['message' => 'Tưới nước thành công!', 'points' => $points]);
});

/**
 * Gamification functions for Eco Points
 */
function aitrongcay_calculate_level(int $points): int {
    // RPG curve: Points = 50 * L * (L-1)
    // L=1(0pts), L=2(100pts), L=3(300pts), L=4(600pts), L=5(1000pts)...
    $level = floor( (1 + sqrt(1 + 8 * $points / 100)) / 2 );
    return max(1, (int)$level);
}

function aitrongcay_points_for_level(int $level): int {
    if ($level <= 1) return 0;
    return 50 * $level * ($level - 1);
}

// ─── Eco Points: Reward Catalogue ────────────────────────────────────────────
function aitrongcay_eco_reward_catalogue(): array {
    return [
        'rau_baby_mix'  => ['name' => 'Rau Baby Mix 200g',      'icon' => '🥗', 'points' => 150, 'stock' => 20],
        'rau_cai_xanh'  => ['name' => 'Cải xanh 500g',          'icon' => '🥬', 'points' => 200, 'stock' => 15],
        'rau_xalach'    => ['name' => 'Xà lách Romaine 300g',   'icon' => '🫛', 'points' => 180, 'stock' => 10],
        'goi_combo_vuon'=> ['name' => 'Combo Vườn Xanh',        'icon' => '🧺', 'points' => 400, 'stock' => 5],
        'voucher_10k'   => ['name' => 'Voucher Giảm 10.000đ',   'icon' => '🎟️', 'points' => 100, 'stock' => 99],
        'voucher_50k'   => ['name' => 'Voucher Giảm 50.000đ',   'icon' => '🎫', 'points' => 450, 'stock' => 30],
    ];
}

// ─── AJAX: Redeem Eco Points ─────────────────────────────────────────────────
add_action('wp_ajax_aitrongcay_redeem_points', function (): void {
    if (! is_user_logged_in()) {
        wp_send_json_error(['message' => 'Bạn cần đăng nhập để đổi thưởng.'], 401);
    }

    check_ajax_referer('aitrongcay_redeem_points', 'aitrongcay_redeem_nonce');

    $user_id   = get_current_user_id();
    $reward_id = sanitize_key((string) ($_POST['reward_id'] ?? ''));
    $catalogue = aitrongcay_eco_reward_catalogue();

    if (! isset($catalogue[$reward_id])) {
        wp_send_json_error(['message' => 'Phần thưởng không tồn tại.'], 400);
    }

    $reward         = $catalogue[$reward_id];
    $current_points = (int) get_user_meta($user_id, '_aitrongcay_eco_points', true);

    if ($current_points < $reward['points']) {
        wp_send_json_error(['message' => sprintf('Bạn không đủ điểm. Cần %d điểm, hiện có %d điểm.', $reward['points'], $current_points)], 400);
    }

    // Validate required fields
    $recipient_name    = sanitize_text_field((string) ($_POST['recipient_name'] ?? ''));
    $recipient_phone   = sanitize_text_field((string) ($_POST['recipient_phone'] ?? ''));
    $recipient_address = sanitize_textarea_field((string) ($_POST['recipient_address'] ?? ''));
    $note              = sanitize_text_field((string) ($_POST['note'] ?? ''));

    if ($recipient_name === '' || $recipient_phone === '' || $recipient_address === '') {
        wp_send_json_error(['message' => 'Vui lòng điền đầy đủ họ tên, số điện thoại và địa chỉ.'], 400);
    }

    // Deduct points
    $new_points = $current_points - $reward['points'];
    update_user_meta($user_id, '_aitrongcay_eco_points', $new_points);

    // Save redemption history
    $history   = (array) get_user_meta($user_id, '_aitrongcay_redeem_history', true);
    $history[] = [
        'id'        => uniqid('rdm_', true),
        'reward_id' => $reward_id,
        'name'      => $reward['name'],
        'icon'      => $reward['icon'],
        'points'    => $reward['points'],
        'time'      => time(),
        'status'    => 'pending',
        'recipient' => [
            'name'    => $recipient_name,
            'phone'   => $recipient_phone,
            'address' => $recipient_address,
            'note'    => $note,
        ],
    ];
    update_user_meta($user_id, '_aitrongcay_redeem_history', $history);

    // Notify admin via email
    $admin_email   = get_option('admin_email');
    $user          = get_user_by('id', $user_id);
    $user_display  = $user instanceof WP_User ? ($user->display_name ?: $user->user_login) : "User #{$user_id}";
    $email_subject = '[Ai trồng cây] Yêu cầu đổi thưởng mới: ' . $reward['name'];
    $email_body    = "Có yêu cầu đổi thưởng mới!\n\n"
        . "Người dùng: {$user_display} (ID: {$user_id})\n"
        . "Phần thưởng: {$reward['icon']} {$reward['name']}\n"
        . "Điểm dùng: {$reward['points']} điểm\n"
        . "Điểm còn lại của họ: {$new_points} điểm\n\n"
        . "--- Thông tin giao hàng ---\n"
        . "Người nhận: {$recipient_name}\n"
        . "Điện thoại: {$recipient_phone}\n"
        . "Địa chỉ: {$recipient_address}\n"
        . ($note !== '' ? "Ghi chú: {$note}\n" : '');
    wp_mail($admin_email, $email_subject, $email_body);

    // In-app notification to user
    if (function_exists('aitrongcay_add_notification')) {
        aitrongcay_add_notification(
            $user_id,
            '🎁 Đổi thưởng thành công!',
            "Yêu cầu đổi <b>{$reward['icon']} {$reward['name']}</b> đã được ghi nhận. Chúng tôi sẽ liên hệ sớm!",
            home_url('/portal/doi-diem/')
        );
    }

    wp_send_json_success([
        'message'          => "Yêu cầu đổi {$reward['name']} đã được ghi nhận! Chúng tôi sẽ liên hệ với bạn trong vòng 24 giờ.",
        'remaining_points' => $new_points,
    ]);
});
