<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$page = aitrongcay_current_virtual_page();
$slug = $page['slug'] ?? 'portal';
$portal_nav = aitrongcay_portal_nav_items();
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$share_token = isset($_GET['share_token']) ? sanitize_text_field((string) wp_unslash($_GET['share_token'])) : '';

$needs_garden_ai = in_array($slug, ['portal', 'portal/dashboard', 'portal/dashboard-2', 'portal/tro-ly-ai'], true);
$needs_pots = in_array($slug, ['portal', 'portal/dashboard', 'portal/dashboard-2', 'portal/webcam', 'portal/tro-ly-ai', 'portal/kho-nong-cu', 'portal/kho-nong-cu-2', 'portal/nhat-ky-cham-soc'], true);
$needs_tool_shelf = in_array($slug, ['portal/dashboard', 'portal/dashboard-2', 'portal/kho-nong-cu', 'portal/kho-nong-cu-2'], true);
$needs_pot_notes = in_array($slug, ['portal/dashboard', 'portal/dashboard-2', 'portal/tro-ly-ai', 'portal/nhat-ky-cham-soc'], true);
$needs_garden_members = in_array($slug, ['portal/dashboard', 'portal/dashboard-2', 'portal/chia-se-khu-vuon', 'portal/ban-be', 'portal/hang-xom'], true);
$needs_viewable_gardens = in_array($slug, ['portal/dashboard', 'portal/dashboard-2', 'portal/chia-se-khu-vuon'], true);
$needs_photo_library = in_array($slug, ['portal/dashboard', 'portal/dashboard-2', 'portal/nhat-ky-cham-soc'], true);

$allow_guest_ai_portal = $slug === 'portal/tro-ly-ai';
$is_guest_ai_portal = $allow_guest_ai_portal && ! $is_logged_in;
$garden_key = $is_logged_in ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$active_profile = $is_logged_in ? aitrongcay_portal_profile_for_garden_context($garden_key, $current_user instanceof WP_User ? $current_user : null) : null;
$garden_ai = ($needs_garden_ai && ! $is_guest_ai_portal) ? aitrongcay_portal_garden_ai($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
$pots = ($needs_pots && ! $is_guest_ai_portal) ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
$rack_record = $is_logged_in && function_exists('aitrongcay_get_rack_record') ? aitrongcay_get_rack_record($garden_key) : null;
$has_rack = is_array($rack_record) && ((int) ($rack_record['slot_count'] ?? 0) >= 2);
$tool_shelf = ($needs_tool_shelf && ! $is_guest_ai_portal) ? aitrongcay_portal_tool_shelf($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
$pot_notes = $is_logged_in && $needs_pot_notes && function_exists('aitrongcay_get_garden_pot_notes') ? aitrongcay_get_garden_pot_notes($garden_key) : [];

$has_valid_public_livecam_token = false;
if ($slug === 'portal/webcam' && $share_token !== '') {
    $matched_users = get_users([
        'meta_key' => '_aitrongcay_livecam_public_token',
        'meta_value' => $share_token,
        'number' => 1,
        'count_total' => false,
    ]);
    if ($matched_users) {
        $current_user = $matched_users[0];
        $garden_key = function_exists('aitrongcay_primary_garden_key_for_user') ? aitrongcay_primary_garden_key_for_user($current_user instanceof WP_User ? $current_user : null) : aitrongcay_current_garden_key($current_user instanceof WP_User ? $current_user : null);
        $active_profile = aitrongcay_portal_profile_for_garden_context($garden_key, $current_user instanceof WP_User ? $current_user : null);
        $garden_ai = $needs_garden_ai ? aitrongcay_portal_garden_ai($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
        $pots = $needs_pots ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
        $tool_shelf = $needs_tool_shelf ? aitrongcay_portal_tool_shelf($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];
        $pot_notes = $needs_pot_notes && function_exists('aitrongcay_get_garden_pot_notes') ? aitrongcay_get_garden_pot_notes($garden_key) : [];
        $is_logged_in = true;
        $has_valid_public_livecam_token = true;
    }
}

if ($slug !== 'portal' && ! $is_logged_in && ! $allow_guest_ai_portal) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

if ($slug === 'portal/webcam' && $share_token !== '' && ! $has_valid_public_livecam_token) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

$current_role = $is_logged_in ? (aitrongcay_user_garden_role($garden_key, (int) $current_user->ID) ?? 'owner') : null;
$current_role_label = $is_logged_in ? aitrongcay_get_role_label((string) $current_role) : '';
$current_role_badge_class = $is_logged_in ? aitrongcay_get_role_badge_class((string) $current_role) : 'is-sand';
$can_manage_members = $current_role === 'owner';
$can_control_garden = $is_logged_in ? aitrongcay_user_can_control_garden($garden_key, (int) $current_user->ID) : false;
$friends = $is_logged_in ? aitrongcay_get_user_friends((int) $current_user->ID) : [];
$friend_invites = $is_logged_in ? aitrongcay_get_friend_invites_received((int) $current_user->ID) : [];
$friend_owner_search = $is_logged_in && in_array($slug, ['portal/ban-be', 'portal/hang-xom'], true) ? sanitize_text_field((string) wp_unslash($_GET['owner_search'] ?? '')) : '';
$active_garden_owners = $is_logged_in && in_array($slug, ['portal/ban-be', 'portal/hang-xom'], true) && $friend_owner_search !== '' ? aitrongcay_get_active_garden_owners((int) $current_user->ID, $friend_owner_search) : [];
$garden_invites = $is_logged_in ? aitrongcay_get_garden_invites_received((int) $current_user->ID) : [];
$garden_members = $is_logged_in && $needs_garden_members ? aitrongcay_get_garden_members($garden_key) : [];
$garden_owner_user = $is_logged_in ? aitrongcay_get_garden_owner_user($garden_key) : null;
$viewable_gardens = $is_logged_in && $needs_viewable_gardens ? aitrongcay_get_viewable_gardens_for_user($current_user instanceof WP_User ? $current_user : null) : [];
$garden_display_name = $is_logged_in
    ? trim((string) (function_exists('aitrongcay_get_garden_display_name')
        ? aitrongcay_get_garden_display_name($garden_key, $current_user instanceof WP_User ? $current_user : null)
        : ((string) ($active_profile['garden_name'] ?? ''))))
    : '';
if ($garden_display_name === '' && is_array($active_profile)) {
    $garden_display_name = trim((string) ($active_profile['garden_name'] ?? ''));
}
$has_real_pots = ! empty($pots);
$active_member_count = count(array_filter($garden_members, static fn(array $member): bool => ($member['status'] ?? '') === 'active'));
$pending_member_count = count(array_filter($garden_members, static fn(array $member): bool => ($member['status'] ?? '') === 'invited'));
$owner_member = null;
$co_owner_members = [];
$viewer_members = [];
$pending_members = [];
foreach ($garden_members as $member) {
    $member_role = (string) ($member['role'] ?? 'viewer');
    $member_status = (string) ($member['status'] ?? '');
    if ($member_status === 'invited') {
        $pending_members[] = $member;
        continue;
    }
    if ($member_role === 'owner' && $owner_member === null) {
        $owner_member = $member;
        continue;
    }
    if ($member_role === 'co_owner') {
        $co_owner_members[] = $member;
        continue;
    }
    $viewer_members[] = $member;
}

$render_portal_nav = static function () use ($portal_nav, $slug, $garden_key): void {
    foreach ($portal_nav as $item) {
        $is_active = $slug === $item['slug'] || ($item['slug'] === 'portal/kho-nong-cu' && $slug === 'portal/dashboard');
        $base_href = $item['slug'] === 'portal/kho-nong-cu'
            ? home_url('/portal/dashboard-2/#tool-shelf')
            : home_url('/' . $item['slug'] . '/');
        $href = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), $base_href) : $base_href;
        printf(
            '<a%s href="%s"><span class="bottom-nav-icon" aria-hidden="true">%s</span><span class="bottom-nav-label">%s</span><span class="bottom-nav-short">%s</span></a>',
            $is_active ? ' class="active"' : '',
            esc_url($href),
            esc_html((string) ($item['icon'] ?? '🍃')),
            esc_html((string) ($item['label'] ?? '')),
            esc_html((string) ($item['short_label'] ?? ($item['label'] ?? '')))
        );
    }
};

if ($slug === 'portal') : ?>
<section class="section-hero">
    <div class="container grid-2">
        <div>
            <span class="eyebrow">Khu vườn của bạn</span>
            <h1>Sở hữu từng khoang cây. Theo dõi cả khu vườn.</h1>
            <p class="lead">Mỗi khoang có camera riêng, cảm biến riêng và lịch sử riêng. Mỗi khu vườn có một AI Agent đồng hành và theo dõi mọi việc cùng gia đình.</p>
            <div class="inline-list" style="margin-top:20px">
                <a class="btn btn-primary" href="<?php echo esc_url($garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/')); ?>">Mở khu vườn của tôi</a>
                <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/dang-nhap/')); ?>">Đăng nhập</a>
            </div>
        </div>
        <div class="glass-card editorial-card">
            <div class="kpi-row">
                <div class="metric"><span class="subtle">Số khoang</span><strong><?php echo esc_html((string) count($pots)); ?></strong></div>
                <div class="metric"><span class="subtle">Mỗi khoang</span><strong>6 khoang</strong></div>
                <div class="metric"><span class="subtle">AI Agent</span><strong>1 trợ lý riêng</strong></div>
            </div>
            <p style="margin-top:18px">Khu vườn giúp gia đình theo dõi, điều khiển và giám sát quá trình canh tác theo từng khoang cây.</p>
        </div>
    </div>
</section>
<?php return; endif; ?>

<?php if (! $active_profile && $slug !== 'portal/webcam') : ?>
<section class="section-tight">
    <div class="container">
        <div class="notice error"><strong>Tài khoản này chưa được gán khu vườn nào.</strong><div style="margin-top:6px">Khi tài khoản được gắn đúng khu vườn hoặc quyền truy cập, dashboard sẽ hiện dữ liệu tương ứng.</div></div>
    </div>
</section>
<?php return; endif; ?>

<?php
if ($slug === 'portal/webcam' && $share_token !== '' && $has_valid_public_livecam_token && $active_profile) :
?>
<section class="section-hero public-livecam-shell">
    <div class="container" style="max-width:980px">
        <div class="glass-card editorial-card public-livecam-card">
            <span class="eyebrow">Livecam được chia sẻ</span>
            <h1><?php echo esc_html($active_profile['garden_code'] . ' • ' . $active_profile['garden_name']); ?></h1>
            <div class="small subtle" style="margin-top:8px" data-garden-display-name><?php echo esc_html($active_profile['garden_name']); ?></div>
            <p class="lead">Một link xem gọn, chỉ giữ đúng khung camera và nhịp hiện tại của khu vườn.</p>
            <div class="public-livecam-frame">
                <video data-livecam autoplay muted loop playsinline controls poster="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-greenhouse.svg'); ?>" style="display:block;width:100%;height:auto;aspect-ratio:16/9;background:#0f172a"><source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4">Trình duyệt này chưa phát được video demo.</video>
            </div>
            <div class="chips" style="margin-top:18px">
                <span class="chip"><?php echo esc_html($active_profile['status']); ?></span>
                <span class="chip">Chỉ xem livecam</span>
            </div>
        </div>
    </div>
</section>
<?php return; endif; ?>

<?php
$media_owner_id = $is_logged_in ? aitrongcay_resolve_garden_owner_id($garden_key, $current_user instanceof WP_User ? $current_user : null) : 0;

$latest_pot_photo_cards = [];
$pot_photo_groups = [];
$pot_lookup = [];
if ($needs_photo_library) {
    foreach ($pots as $pot_item) {
        $latest_image_url = trim((string) ($pot_item['image'] ?? ''));
        if ($latest_image_url === '') {
            $latest_image_url = trim((string) ($pot_item['image_url'] ?? ''));
        }
        $latest_attachment_id = (int) ($pot_item['latest_photo_id'] ?? 0);
        $latest_pot_photo_cards[(string) ($pot_item['code'] ?? '')] = [
            'url' => $latest_image_url,
            'title' => $latest_image_url !== '' ? 'Ảnh tự động cập nhật' : '',
            'download' => $latest_image_url,
            'attachment_id' => $latest_attachment_id,
            'orientation_class' => $latest_attachment_id > 0 && function_exists('aitrongcay_attachment_orientation_class') ? aitrongcay_attachment_orientation_class($latest_attachment_id) : '',
        ];
    }

    $library_query = new WP_Query([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 50,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_mime_type' => 'image',
        'no_found_rows' => true,
        'update_post_meta_cache' => true,
        'update_post_term_cache' => false,
        'meta_query' => [
            [
                'key' => '_aitrongcay_photo_garden_key',
                'value' => $garden_key,
            ],
        ],
    ]);

    foreach ($pots as $pot_item) {
        $pot_lookup[$pot_item['code']] = $pot_item;
        $pot_photo_groups[$pot_item['code']] = [];
    }
    if ($library_query->have_posts()) {
        foreach ($library_query->posts as $photo_post) {
            $pot_code = (string) get_post_meta($photo_post->ID, '_aitrongcay_pot_code', true);
            if ($pot_code === '' || ! isset($pot_photo_groups[$pot_code])) {
                $pot_code = 'UNGROUPED';
                if (! isset($pot_photo_groups[$pot_code])) {
                    $pot_photo_groups[$pot_code] = [];
                }
            }
            $pot_photo_groups[$pot_code][] = $photo_post;
            if ($pot_code !== 'UNGROUPED' && isset($latest_pot_photo_cards[$pot_code]) && $latest_pot_photo_cards[$pot_code]['title'] === '') {
                $latest_pot_photo_cards[$pot_code] = [
                    'url' => wp_make_link_relative((string) (wp_get_attachment_image_url($photo_post->ID, 'large') ?: wp_get_attachment_url($photo_post->ID))),
                    'title' => get_the_title($photo_post->ID),
                    'download' => wp_make_link_relative((string) wp_get_attachment_url($photo_post->ID)),
                ];
            }
        }
    }
}
?>

<?php if (in_array($slug, ['portal/ban-be', 'portal/hang-xom'], true)) : ?>
    <?php
    $friend_count = count($friends);
    $pending_friend_count = count($friend_invites);
    $discover_count = count($active_garden_owners);
    $share_garden_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/chia-se-khu-vuon/')) : home_url('/portal/chia-se-khu-vuon/');
    $friends_page_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/hang-xom/')) : home_url('/portal/hang-xom/');
    $friends_search_redirect_url = $friends_page_url;
    $friends_nav = array_map(static function (array $item) use ($garden_key) {
        $url = (string) ($item['url'] ?? '#');
        if (in_array((string) ($item['key'] ?? ''), ['dashboard', 'kho-nong-cu', 'hang-xom'], true) && $garden_key !== '') {
            $url = add_query_arg('garden', $garden_key, $url);
        }
        return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => $url];
    }, aitrongcay_eco_nav_items());
    set_query_var('aitr_eco_shell', [
        'title' => 'Hàng xóm',
        'active' => 'hang-xom',
        'side_title' => 'Ai trồng cây',
        'side_subtitle' => 'Kết nối khu vườn',
        'side_badge' => '👥',
        'top_icons' => ['🔔', '⚙️'],
        'search' => null,
        'nav' => $friends_nav,
    ]);
    get_template_part('template-parts/site/eco-shell-start-v2');
    set_query_var('aitr_eco_hero', ['title' => '', 'body' => 'Kết nối, chia sẻ và học kinh nghiệm canh tác từ mọi người.']);
    get_template_part('template-parts/site/eco-hero');
    ?>
    <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-hero{margin-bottom:24px}
    .eco-hero p{color:#6fdba8 !important}
    .eco-friends-toolbar{display:block;margin-bottom:24px}
    .eco-friends-search{display:flex;align-items:center;gap:10px;background:#292b27;border-radius:16px;padding:12px 16px;width:100%}
    .eco-friends-search input{width:100%;border:none;background:transparent;color:#e3e3de;outline:none}
    .eco-friends-grid{display:grid;grid-template-columns:1fr;gap:24px}.eco-friends-panel{background:rgba(18,20,17,.94);border:1px solid rgba(255,255,255,.05);border-radius:30px;padding:24px;box-shadow:0 22px 48px rgba(0,0,0,.22)}
    .eco-friends-panel h4{margin:0 0 16px;font-family:'Noto Serif',serif;font-size:28px;color:#fff}.eco-friends-stack{display:grid;gap:16px}
    .eco-friend-card{display:grid;grid-template-columns:auto 1fr auto;gap:16px;align-items:center;padding:18px;border-radius:24px;background:rgba(51,53,50,.38);border:1px solid rgba(255,255,255,.05)}
    .eco-friend-avatar{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg, rgba(49,163,117,.32), rgba(111,219,168,.12));display:grid;place-items:center;font-size:28px;color:var(--primary)}
    .eco-friend-name{font-family:'Noto Serif',serif;font-size:24px;color:#fff;font-weight:700}.eco-friend-sub{font-size:12px;color:rgba(227,227,222,.58);margin-top:4px}
    .eco-friend-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.eco-friend-action,.eco-friend-primary,.eco-friend-zalo,.eco-friend-danger{border:none;border-radius:16px;padding:12px 14px;display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;line-height:1;text-decoration:none}
    .eco-friend-action{background:rgba(18,20,17,.7);color:#e3e3de}
    .eco-friend-primary{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-weight:900;cursor:pointer}
    .eco-friend-zalo{background:#0068ff;color:#fff;font-weight:800}
    .eco-friend-danger{background:rgba(120,24,24,.22);color:#ffb4ab;cursor:pointer;padding:12px}
    .eco-friend-action:hover,.eco-friend-primary:hover,.eco-friend-zalo:hover{transform:translateY(-1px)}
    .eco-friend-danger:hover{transform:translateY(-1px);background:rgba(120,24,24,.32)}
    .eco-friend-primary.is-co-owner{background:linear-gradient(135deg,#ffe16d,#ffb68c);color:#2f2410}
    .eco-friend-request-actions{display:flex;gap:10px;flex-wrap:wrap}.eco-friend-request-actions .btn{border-radius:999px}
    @media (max-width:760px){.eco-friends-search{width:100%}.eco-friend-card{grid-template-columns:1fr}.eco-friend-actions{grid-template-columns:1fr 1fr 1fr}}
    </style>
    <div class="eco-friends-toolbar">
      <form method="get" class="eco-friends-search" action="<?php echo esc_url($friends_page_url); ?>">
        <?php if ($garden_key !== '') : ?><input type="hidden" name="garden" value="<?php echo esc_attr($garden_key); ?>"><?php endif; ?>
        <span>🔎</span>
        <input id="owner-search" type="search" name="owner_search" value="<?php echo esc_attr($friend_owner_search); ?>" placeholder="Tìm hàng xóm...">
      </form>
    </div>
    <?php if ($friend_owner_search !== '') : ?><section class="eco-friends-panel" style="margin-top:0 0 24px"><h4>Kết quả tìm kiếm</h4><?php if ($active_garden_owners) : ?><div class="eco-friends-stack"><?php foreach ($active_garden_owners as $owner_card) : $owner_id = (int) ($owner_card['user_id'] ?? 0); $owner_name = (string) ($owner_card['display_name'] ?? $owner_card['user_login'] ?? 'Chủ vườn'); $owner_target = (string) ($owner_card['user_login'] ?? ''); $friendship_status = (string) ($owner_card['friendship_status'] ?? 'none'); $friendship_direction = (string) ($owner_card['friendship_direction'] ?? 'none'); $cta_label = 'Kết nối'; $cta_disabled = false; $cta_url = $owner_target !== '' ? aitrongcay_send_friend_request_url($owner_target, $friends_search_redirect_url, $friend_owner_search) : ''; if ($friendship_status === 'accepted') { $cta_label = 'Đã là hàng xóm'; $cta_disabled = true; } elseif ($friendship_status === 'pending' && $friendship_direction === 'outgoing') { $cta_label = 'Hủy lời mời kết nối'; $cta_url = $owner_id > 0 ? aitrongcay_cancel_friend_request_url($owner_id, $friends_search_redirect_url, $friend_owner_search) : ''; } elseif ($friendship_status === 'pending' && $friendship_direction === 'incoming') { $cta_label = 'Đang chờ phản hồi'; $cta_disabled = true; } ?><article class="eco-friend-card"><div class="eco-friend-avatar">🌱</div><div><div class="eco-friend-name"><?php echo esc_html($owner_name); ?></div><div class="eco-friend-sub">Chủ vườn đang hoạt động</div></div><div class="eco-friend-request-actions"><?php if ($cta_disabled) : ?><button class="btn btn-secondary" type="button" disabled aria-disabled="true"><?php echo esc_html($cta_label); ?></button><?php else : ?><a class="btn btn-primary" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_label); ?></a><?php endif; ?></div></article><?php endforeach; ?></div><?php else : ?><div class="notice">Không tìm thấy người phù hợp với tên anh vừa nhập.</div><?php endif; ?></section><?php endif; ?>
    <?php if ($pending_friend_count > 0) : ?><section class="eco-friends-panel" style="margin-top:24px"><h4>Lời mời đang chờ</h4><div class="eco-friend-sub" style="margin:-4px 0 18px"><?php echo esc_html('Có ' . $pending_friend_count . ' lời mời kết nối đang chờ'); ?></div><div class="eco-friends-stack"><?php foreach ($friend_invites as $invite) : $sender = get_user_by('id', (int) $invite['requester_user_id']); $invite_id = (int) ($invite['id'] ?? 0); $current_url = home_url((string) ($_SERVER['REQUEST_URI'] ?? '/portal/hang-xom/')); ?><article class="eco-friend-card"><div class="eco-friend-avatar">🫶</div><div><div class="eco-friend-name"><?php echo esc_html($sender instanceof WP_User ? ($sender->display_name ?: $sender->user_login) : 'Người dùng'); ?></div><div class="eco-friend-sub">Đang chờ anh phản hồi</div></div><div class="eco-friend-request-actions"><a class="btn btn-primary" href="<?php echo esc_url(aitrongcay_accept_friend_request_url($invite_id, $current_url)); ?>">Chấp nhận</a><a class="btn btn-secondary" href="<?php echo esc_url(aitrongcay_reject_friend_request_url($invite_id, $current_url)); ?>">Từ chối</a></div></article><?php endforeach; ?></div></section><?php endif; ?>
    <div class="eco-friends-grid" style="margin-top:24px">
      <section class="eco-friends-panel"><h4>Hàng xóm hiện tại</h4><div class="eco-friend-sub" style="margin:-4px 0 18px"><?php echo esc_html($friend_count > 0 ? 'Hiện tại có ' . $friend_count . ' hàng xóm' : 'Hiện tại chưa có hàng xóm nào'); ?></div><?php if ($friends) : ?><div class="eco-friends-stack"><?php $friend_membership_map = []; foreach ($garden_members as $member) { $member_user_id = (int) ($member['user_id'] ?? 0); if ($member_user_id > 0) { $friend_membership_map[$member_user_id] = $member; } } foreach ($friends as $friendship) : $friend_id = (int) (($friendship['requester_user_id'] == $current_user->ID) ? $friendship['addressee_user_id'] : $friendship['requester_user_id']); $friend = get_user_by('id', $friend_id); $friend_garden_key = $friend instanceof WP_User ? aitrongcay_preferred_garden_key_for_user($friend) : ''; $friend_garden_url = $friend_garden_key !== '' ? add_query_arg('garden', rawurlencode($friend_garden_key), home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/'); $friend_phone = $friend instanceof WP_User ? aitrongcay_user_zalo_phone((int) $friend->ID) : ''; $friend_zalo_url = $friend_phone !== '' ? 'https://zalo.me/' . rawurlencode($friend_phone) : ''; $membership = $friend_membership_map[$friend_id] ?? null; $friend_role = (string) ($membership['role'] ?? 'viewer'); $membership_id = (int) ($membership['id'] ?? 0); $share_label = $friend_role === 'co_owner' ? 'Đã share vườn' : 'Share vườn'; $share_url = aitrongcay_friend_toggle_share_url($friend_id, $membership_id, $friend_role); $remove_url = aitrongcay_remove_friend_url($friend_id); ?><article class="eco-friend-card"><div class="eco-friend-avatar">🌿</div><div><div class="eco-friend-name"><?php echo esc_html($friend instanceof WP_User ? ($friend->display_name ?: $friend->user_login) : 'Người dùng'); ?></div><div class="eco-friend-sub">Hàng xóm trong hệ sinh thái vườn số</div></div><div class="eco-friend-actions"><a class="eco-friend-action" href="<?php echo esc_url($friend_garden_url); ?>">Xem vườn</a><button type="button" class="eco-friend-primary aitr-water-friend" data-friend-id="<?php echo esc_attr((string)$friend_id); ?>" style="background:linear-gradient(135deg, #3b82f6, #60a5fa);color:#fff">💦 Tưới nước hộ</button><?php if ($friend_zalo_url !== '') : ?><a class="eco-friend-zalo" href="<?php echo esc_url($friend_zalo_url); ?>" target="_blank" rel="noopener" aria-label="Nhắn Zalo"><span aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none"><rect x="3" y="3" width="18" height="18" rx="6" fill="#fff"></rect><path d="M7.5 8.2h9l-6.05 7.6h5.95l-9 0 6.05-7.6H7.5Z" fill="#0068FF"></path></svg></span><span>Zalo</span></a><?php else : ?><button class="eco-friend-action" type="button" disabled>Chưa có Zalo</button><?php endif; ?><a class="eco-friend-primary<?php echo $friend_role === 'co_owner' ? ' is-co-owner' : ''; ?>" href="<?php echo esc_url($share_url); ?>"><?php echo esc_html($share_label); ?></a><a class="eco-friend-danger" href="<?php echo esc_url($remove_url); ?>" aria-label="Hủy kết nối hàng xóm"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></a></div></article><?php endforeach; ?></div><?php else : ?><div class="notice">Anh chưa có hàng xóm nào. Anh có thể tìm và kết nối bằng ô tìm kiếm phía trên.</div><?php endif; ?></section>
      
      <script>
      function aitrongcay_show_toast(msg, isError) {
          var toast = document.createElement('div');
          toast.style.cssText = 'position:fixed;bottom:30px;right:30px;padding:16px 24px;border-radius:20px;background:' + (isError ? 'rgba(239,68,68,0.95)' : 'rgba(16,185,129,0.95)') + ';color:#fff;font-family:"Inter",sans-serif;font-weight:600;font-size:15px;box-shadow:0 20px 40px rgba(0,0,0,0.3);z-index:99999;transform:translateY(100px);opacity:0;transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:12px;';
          toast.innerHTML = (isError ? '<span>⚠️</span>' : '<span>✅</span>') + '<span>' + msg + '</span>';
          document.body.appendChild(toast);
          
          // Animate in
          requestAnimationFrame(function() {
              requestAnimationFrame(function() {
                  toast.style.transform = 'translateY(0)';
                  toast.style.opacity = '1';
              });
          });
          
          // Remove after 3.5 seconds
          setTimeout(function() {
              toast.style.transform = 'translateY(100px)';
              toast.style.opacity = '0';
              setTimeout(function() { toast.remove(); }, 400);
          }, 3500);
      }

      document.addEventListener('DOMContentLoaded', function() {
          var waterBtns = document.querySelectorAll('.aitr-water-friend');
          waterBtns.forEach(function(btn) {
              btn.addEventListener('click', function() {
                  var fId = this.getAttribute('data-friend-id');
                  var self = this;
                  self.disabled = true;
                  self.style.opacity = '0.7';
                  self.textContent = 'Đang tưới...';
                  
                  // AJAX Call
                  var formData = new FormData();
                  formData.append('action', 'aitrongcay_water_friend_garden');
                  formData.append('friend_id', fId);
                  
                  fetch('<?php echo esc_js(admin_url('admin-ajax.php')); ?>', {
                      method: 'POST',
                      body: formData
                  })
                  .then(r => r.json())
                  .then(data => {
                      if(data.success) {
                          self.style.background = '#10b981';
                          self.textContent = '✅ Đã tưới xong';
                          var msg = "Bạn vừa tưới nước hộ thành công!";
                          if (data.data.points) msg += " Nhận được " + data.data.points + " Eco Points.";
                          aitrongcay_show_toast(msg, false);
                      } else {
                          self.disabled = false;
                          self.style.opacity = '1';
                          self.textContent = '💦 Tưới nước hộ';
                          aitrongcay_show_toast(data.data.message || 'Có lỗi xảy ra.', true);
                      }
                  })
                  .catch(err => {
                      self.disabled = false;
                      self.style.opacity = '1';
                      self.textContent = '💦 Tưới nước hộ';
                      aitrongcay_show_toast('Lỗi mạng. Vui lòng thử lại.', true);
                  });
              });
          });
      });
      </script>
    </div>
    <?php get_template_part('template-parts/site/eco-shell-end'); return; ?>
<?php endif; ?>

<?php if ($slug === 'portal/nhat-ky-cham-soc') : ?>
    <?php
    $photo_nav = array_map(static function (array $item) use ($garden_key) {
        $url = (string) ($item['url'] ?? '#');
        if (in_array((string) ($item['key'] ?? ''), ['dashboard', 'kho-nong-cu', 'hang-xom'], true) && $garden_key !== '') {
            $url = add_query_arg('garden', $garden_key, $url);
        }
        return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => $url];
    }, aitrongcay_eco_nav_items());
    $photo_library_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/nhat-ky-cham-soc/')) : home_url('/portal/nhat-ky-cham-soc/');
    $photo_nav[] = ['key' => 'kho-anh', 'label' => '🖼 Kho ảnh', 'url' => $photo_library_url];
    set_query_var('aitr_eco_shell', [
        'title' => 'Kho ảnh',
        'active' => 'kho-anh',
        'side_title' => 'Ai trồng cây',
        'side_subtitle' => 'Thư viện theo từng khoang',
        'side_badge' => '🖼',
        'top_icons' => ['📷', '🌿'],
        'search' => null,
        'nav' => $photo_nav,
    ]);
    get_template_part('template-parts/site/eco-shell-start-v2');
    
    ?>
    <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .photo-library-grid{display:grid;gap:24px}
    .photo-library-card{background:linear-gradient(180deg, rgba(20,24,20,.96), rgba(14,18,15,.96));border:1px solid rgba(255,255,255,.05);border-radius:30px;padding:24px;box-shadow:0 22px 48px rgba(0,0,0,.22)}
    .photo-library-head{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap}.photo-library-head h3{margin:0;font-family:'Noto Serif',serif;font-size:30px;color:#fff}.photo-library-meta{color:rgba(227,227,222,.62);font-size:14px;line-height:1.7}
    .photo-library-slider{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:14px;align-items:center}.photo-library-viewport{position:relative;overflow:hidden;touch-action:pan-y;cursor:grab}.photo-library-viewport.is-dragging{cursor:grabbing}.photo-library-track{display:flex;transition:transform .34s ease,opacity .22s ease}.photo-library-track.is-fading{opacity:.72}.photo-library-slide{min-width:100%}
    .photo-library-image-wrap{position:relative}.photo-library-image{display:block;width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:22px;background:#0b120e;cursor:zoom-in}
    .photo-library-caption{position:absolute;left:12px;right:12px;bottom:12px;pointer-events:none;text-shadow:0 1px 3px rgba(0,0,0,0.8), 0 1px 2px rgba(0,0,0,0.9)}
    .photo-library-caption strong{display:block;color:#fff;font-size:12px;font-weight:600;line-height:1.4}.photo-library-caption span{display:block;color:rgba(255,255,255,.85);font-size:11px;line-height:1.5}.photo-library-counter{position:absolute;right:14px;top:14px;padding:6px 10px;border-radius:999px;background:rgba(11,18,14,.72);color:#f5f7f3;font-size:12px;font-weight:700;backdrop-filter:blur(8px)}
    .photo-library-nav{width:44px;height:44px;border:none;border-radius:999px;background:rgba(51,53,50,.78);color:#fff;font-size:28px;line-height:1;display:grid;place-items:center}.photo-library-nav[disabled]{opacity:.35;cursor:not-allowed}
    .photo-library-dots{grid-column:1/-1;display:flex;justify-content:center;gap:8px;margin-top:12px}.photo-library-dot{width:10px;height:10px;border:none;border-radius:999px;background:rgba(255,255,255,.24)}.photo-library-dot.is-active{background:#6fdba8;transform:scale(1.15)}
    .photo-library-actions{display:flex;align-items:center;gap:10px;margin-top:16px;flex-wrap:wrap}.photo-library-actions a{color:#6fdba8;font-weight:600;font-size:14px;text-decoration:none;padding:8px 16px;background:rgba(111,219,168,.15);border-radius:12px;transition:all .2s ease}.photo-library-actions a:hover{background:rgba(111,219,168,.25)}.photo-library-actions .btn-ghost{margin-left:auto;color:#ef4444;background:rgba(239,68,68,.1)}.photo-library-upload{display:block;margin-top:18px}.photo-library-upload-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px;align-items:center}.photo-library-file{display:flex;align-items:center;gap:12px;background:#fff;border-radius:18px;padding:14px 16px;min-height:64px}.photo-library-file input[type="file"]{width:100%;color:#3a433d}.photo-library-upload .btn{min-height:64px;padding:0 24px;border-radius:18px}.photo-library-status{display:block;margin-top:10px;color:rgba(227,227,222,.58)}
    .photo-library-empty{padding:24px;border-radius:22px;background:rgba(41,43,39,.42);color:rgba(227,227,222,.7)}
    .photo-lightbox{position:fixed;inset:0;z-index:120;background:rgba(5,8,6,.92);display:flex;align-items:center;justify-content:center;padding:28px}.photo-lightbox[hidden]{display:none !important}.photo-lightbox-inner{max-width:min(96vw,1200px);max-height:92vh;display:grid;gap:14px}.photo-lightbox img{display:block;max-width:100%;max-height:78vh;border-radius:22px;background:#0b120e}.photo-lightbox-meta{display:flex;justify-content:space-between;gap:14px;align-items:center;color:#e3e3de}.photo-lightbox-close{position:absolute;top:18px;right:18px;width:46px;height:46px;border:none;border-radius:999px;background:rgba(255,255,255,.08);color:#fff;font-size:28px}
    @media (max-width:760px){.photo-library-slider{grid-template-columns:1fr}.photo-library-nav{display:none}.photo-library-upload-form{grid-template-columns:1fr}.photo-library-upload .btn{width:100%}}
    </style>
    
    <?php
    // Auto-fix overlapping pot_codes across racks
    global $wpdb;
    $rack_slots_table = aitrongcay_garden_rack_slots_table();
    $pots_table = aitrongcay_garden_pots_table();
    $all_garden_racks = $wpdb->get_col($wpdb->prepare("SELECT id FROM " . aitrongcay_garden_racks_table() . " WHERE garden_key = %s ORDER BY id ASC", $garden_key));
    
    $used_pcodes = [];
    $slots_to_sync = [];
    foreach ($all_garden_racks as $r_id) {
        $slots = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$rack_slots_table} WHERE rack_id = %d ORDER BY slot_index ASC", $r_id), ARRAY_A);
        foreach ($slots as $slot) {
            $pcode = strtolower(trim((string)$slot['pot_code']));
            $slot_id = (int)$slot['id'];
            
            // If empty or already used by another rack, we MUST generate a unique one
            if ($pcode === '' || isset($used_pcodes[$pcode])) {
                $new_pcode = sprintf('R%d-S%02d', $r_id, (int)$slot['slot_index']);
                $wpdb->update($rack_slots_table, ['pot_code' => $new_pcode], ['id' => $slot_id]);
                $pcode = strtolower($new_pcode);
                $slot['pot_code'] = $new_pcode;
            }
            $used_pcodes[$pcode] = true;
            $slots_to_sync[] = $slot;
        }
    }
    
    // Ensure all unique slots exist in garden_pots so they show up
    foreach ($slots_to_sync as $slot) {
        $pcode = trim((string)$slot['pot_code']);
        $pname = trim((string)$slot['slot_name']);
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$pots_table} WHERE garden_key = %s AND pot_code = %s", $garden_key, $pcode));
        if (!$exists) {
            aitrongcay_upsert_db_pot($garden_key, [
                'pot_code' => $pcode,
                'code' => $pcode,
                'pot_name' => $pname,
                'name' => $pname,
                'status' => 'Sẵn sàng trồng',
            ]);
        }
    }
    
    // Reload pots after sync
    $pots = ($needs_pots && ! $is_guest_ai_portal) ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];

    $nk_rack_slots = function_exists('aitrongcay_get_rack_slots') ? aitrongcay_get_rack_slots($garden_key) : [];
    $nk_rack_by_pot = [];
    $nk_rack_names = [];
    foreach ($nk_rack_slots as $slot) {
        $pcode = strtolower(trim((string)($slot['pot_code'] ?? '')));
        $r_id = (int)($slot['rack_id'] ?? 0);
        
        if ($pcode !== '' && $r_id > 0) {
            $nk_rack_names[$r_id] = trim((string)($slot['rack_name'] ?? '')) ?: 'Rack ' . $r_id;
            $nk_rack_by_pot[$pcode] = $r_id;
        }
    }
    
    // Ensure Racks are sorted alphabetically (e.g. Rack 1, Rack 2)
    asort($nk_rack_names);
    
    $pots_by_rack_nk = [];
    foreach ($pots as $pot_item) {
        $normalized_code = strtolower(trim((string)$pot_item['code']));
        $r_id = $nk_rack_by_pot[$normalized_code] ?? -1;
        
        if (!isset($nk_rack_names[$r_id])) {
            $nk_rack_names[$r_id] = $r_id === -1 ? 'Chưa phân rack' : 'Rack ' . $r_id;
        }
        if (!isset($pots_by_rack_nk[$r_id])) $pots_by_rack_nk[$r_id] = [];
        $pots_by_rack_nk[$r_id][] = $pot_item;
    }
    $first_rack_id = count($nk_rack_names) > 0 ? array_keys($nk_rack_names)[0] : -1;
    if (isset($_GET['rack']) && isset($nk_rack_names[(int)$_GET['rack']])) {
        $first_rack_id = (int)$_GET['rack'];
    }
    $first_pot_code = isset($_GET['pot']) ? sanitize_text_field(wp_unslash($_GET['pot'])) : 'all';
    ?>
    
    <div style="margin-bottom:24px;display:flex;align-items:center;gap:8px;flex-wrap:nowrap;overflow-x:auto">
        <div style="display:inline-flex;align-items:center;gap:8px;flex-shrink:0">
            <?php if (count($nk_rack_names) > 1): ?>
            <label style="font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.05);padding:7px 14px;border-radius:14px;color:#fff;white-space:nowrap">Rack:
                <select id="nkRackSelect" onchange="AITR_NK_FILTER_RACK(this.value)" style="border:none;background:transparent;font-weight:700;font-size:14px;color:#6fdba8;outline:none;cursor:pointer;max-width:120px">
                    <?php foreach ($nk_rack_names as $r_id => $r_name): ?>
                    <option value="<?php echo esc_attr((string)$r_id); ?>" <?php selected((string)$r_id, (string)$first_rack_id); ?> style="color:#000"><?php echo esc_html($r_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <?php else: ?>
            <input type="hidden" id="nkRackSelect" value="<?php echo esc_attr((string)$first_rack_id); ?>">
            <?php endif; ?>

            <label style="font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.05);padding:7px 14px;border-radius:14px;color:#fff;white-space:nowrap">Khoang:
                <select id="nkKhoangSelect" onchange="AITR_NK_FILTER_KHOANG(this.value)" style="border:none;background:transparent;font-weight:700;font-size:14px;color:#6fdba8;outline:none;cursor:pointer;max-width:140px">
                </select>
            </label>
        </div>

        <a href="#" id="nkViewTimelapseBtn" class="btn btn-primary" style="margin-left:auto;flex-shrink:0;border-radius:14px;padding:7px 16px;white-space:nowrap;font-size:14px">Xem Timelapse</a>
    </div>

    <script>
        var nkPotsData = <?php echo wp_json_encode($pots_by_rack_nk); ?>;
        var nkGardenKey = <?php echo wp_json_encode($garden_key); ?>;
        
        function AITR_NK_FILTER_RACK(rackId, initPotCode) {
            var url = new URL(window.location.href);
            url.searchParams.set('rack', rackId);
            if (!initPotCode) {
                url.searchParams.delete('pot');
            }
            window.history.replaceState({}, '', url);

            var kSelect = document.getElementById('nkKhoangSelect');
            if(!kSelect) return;
            kSelect.innerHTML = '';
            
            var pots = nkPotsData[rackId] || [];
            
            // Add "Tất cả khoang" option
            var allOpt = document.createElement('option');
            allOpt.value = 'all';
            allOpt.textContent = 'Tất cả khoang';
            allOpt.style.color = '#000';
            kSelect.appendChild(allOpt);
            
            pots.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.code;
                opt.textContent = p.name;
                opt.style.color = '#000';
                if (initPotCode === p.code) {
                    opt.selected = true;
                }
                kSelect.appendChild(opt);
            });
            
            var selectedPot = kSelect.value;
            AITR_NK_FILTER_KHOANG(selectedPot, true);
        }
        
        function AITR_NK_FILTER_KHOANG(potCode, skipUrlUpdate) {
            if (!skipUrlUpdate) {
                var url = new URL(window.location.href);
                if (potCode === 'all') {
                    url.searchParams.delete('pot');
                } else {
                    url.searchParams.set('pot', potCode);
                }
                window.history.replaceState({}, '', url);
            }

            var rackId = '';
            var rSelect = document.getElementById('nkRackSelect');
            if (rSelect) rackId = String(rSelect.value);
            
            document.querySelectorAll('[data-nk-pot-section]').forEach(function(el) {
                var elRack = el.getAttribute('data-nk-rack');
                var elPot = el.getAttribute('data-nk-pot-section');
                
                var show = false;
                if (String(elRack) === rackId) {
                    if (potCode === 'all' || elPot === potCode) {
                        show = true;
                    }
                }
                
                el.style.display = show ? '' : 'none';
            });
            
            // Update Timelapse Link
            var tlBtn = document.getElementById('nkViewTimelapseBtn');
            if (tlBtn) {
                if (potCode && potCode !== 'all') {
                    var url = '<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>';
                    url += '?garden=' + encodeURIComponent(nkGardenKey) + '&view=timelapse&tl_rack=' + encodeURIComponent(rackId) + '&tl_stream=' + encodeURIComponent(potCode);
                    tlBtn.href = url;
                    tlBtn.style.display = 'inline-flex';
                } else {
                    tlBtn.style.display = 'none';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var rSelect = document.getElementById('nkRackSelect');
            var firstRack = rSelect ? rSelect.value : '<?php echo esc_js((string)$first_rack_id); ?>';
            var firstPot = '<?php echo esc_js((string)$first_pot_code); ?>';
            AITR_NK_FILTER_RACK(firstRack, firstPot);
        });
    </script>

    <div class="photo-library-grid">
      <?php foreach ($pots as $pot_item) : ?>
        <?php 
        $group_items = $pot_photo_groups[$pot_item['code']] ?? []; 
        $normalized_code = strtolower(trim((string)$pot_item['code']));
        $r_id = $nk_rack_by_pot[$normalized_code] ?? -1;
        ?>
        <section class="photo-library-card" id="photo-<?php echo esc_attr(strtolower((string) $pot_item['code'])); ?>" data-nk-pot-section="<?php echo esc_attr($pot_item['code']); ?>" data-nk-rack="<?php echo esc_attr((string)$r_id); ?>">
          <div class="photo-library-head">
            <div>
              <div class="eco-kicker"><?php echo esc_html((string) ($pot_item['code'] ?? '')); ?></div>
              <h3><?php echo esc_html((string) ($pot_item['name'] ?? 'Khoang cây')); ?></h3>
              <div class="photo-library-meta">Ảnh của khoang này · <?php echo esc_html((string) ($pot_item['status'] ?? 'Đang theo dõi')); ?> · <?php echo esc_html((string) ($pot_item['light'] ?? '')); ?></div>
            </div>
            <div class="photo-library-meta"><?php echo esc_html((string) count($group_items)); ?> ảnh</div>
          </div>
          <?php if ($group_items) : ?>
            <div class="photo-library-slider" data-pot-photo-slider>
              <button class="photo-library-nav prev" type="button" data-pot-photo-prev aria-label="Ảnh trước">‹</button>
              <div class="photo-library-viewport" data-pot-photo-viewport>
                <div class="photo-library-track">
                  <?php foreach ($group_items as $index => $photo_post) : ?>
                    <?php
                    $attachment_id = (int) $photo_post->ID;
                    $image_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id)));
                    $download_url = wp_make_link_relative((string) wp_get_attachment_url($attachment_id));
                    $title = get_the_title($attachment_id);
                    $caption = trim((string) ($photo_post->post_content ?: 'Ảnh thực tế của ' . $pot_item['name']));
                    ?>
                    <article class="photo-library-slide" data-photo-card data-photo-id="<?php echo esc_attr((string) $attachment_id); ?>">
                      <div class="photo-library-image-wrap">
                        <img class="photo-library-image" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>" data-photo-lightbox-image="<?php echo esc_url($image_url); ?>" data-photo-lightbox-title="<?php echo esc_attr($title); ?>" data-photo-lightbox-caption="<?php echo esc_attr($caption); ?>">
                        <div class="photo-library-caption"><div><strong><?php echo esc_html($title); ?></strong><span><?php echo esc_html($caption); ?></span></div></div>
                      </div>
                      <div class="photo-library-actions">
                        <a class="small-link" href="<?php echo esc_url($download_url); ?>" target="_blank" rel="noopener">Mở ảnh gốc</a>
                        <a class="small-link" href="<?php echo esc_url($download_url); ?>" download>Tải ảnh</a>
                        <button class="btn btn-ghost" type="button" data-delete-photo="<?php echo esc_attr((string) $attachment_id); ?>" aria-label="Xóa ảnh này" title="Xóa ảnh này" style="min-width:auto;padding:6px 10px;border-radius:999px">✕</button>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
                <div class="photo-library-counter" data-pot-photo-counter>1/<?php echo esc_html((string) count($group_items)); ?></div>
              </div>
              <button class="photo-library-nav next" type="button" data-pot-photo-next aria-label="Ảnh sau">›</button>
              <div class="photo-library-dots">
                <?php foreach ($group_items as $index => $photo_post) : ?>
                  <button class="photo-library-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-pot-photo-dot="<?php echo esc_attr((string) $index); ?>" aria-label="Xem ảnh <?php echo esc_attr((string) ($index + 1)); ?>"></button>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else : ?>
            <div class="photo-library-empty">Chưa có ảnh nào được gắn vào khoang này.</div>
          <?php endif; ?>
          <div class="photo-library-upload">
            <form class="photo-library-upload-form" data-pot-photo-upload-form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="margin:0">
              <?php wp_nonce_field('aitrongcay_upload_photo_submit', 'aitrongcay_upload_photo_nonce'); ?>
              <input type="hidden" name="action" value="aitrongcay_upload_photo_submit">
              <input type="hidden" name="garden_key" value="<?php echo esc_attr($garden_key); ?>">
              <input type="hidden" name="pot_code" value="<?php echo esc_attr((string) ($pot_item['code'] ?? '')); ?>">
              <input type="hidden" name="pot_name" value="<?php echo esc_attr((string) ($pot_item['name'] ?? '')); ?>">
              <label class="photo-library-file"><input type="file" name="photo" accept="image/*" required></label>
              <button class="btn btn-primary" type="submit">Upload ảnh cho khoang này</button>
              <span class="small subtle photo-library-status" data-pot-photo-upload-status><?php echo esc_html((string) ($pot_item['code'] ?? 'Khoang')); ?> đang chờ ảnh mới.</span>
            </form>
          </div>
        </section>
      <?php endforeach; ?>
      <?php if (! empty($pot_photo_groups['UNGROUPED'])) : ?>
        <section class="photo-library-card">
          <div class="eco-kicker">Chưa phân loại</div>
          <h3 style="margin:0 0 10px;font-family:'Noto Serif',serif;color:#fff">Ảnh cần gắn vào khoang cụ thể</h3>
          <div class="photo-library-meta">Những ảnh này đã thuộc vườn nhưng chưa có mã khoang.</div>
        </section>
      <?php endif; ?>
    </div>
    <div class="photo-lightbox" data-photo-lightbox hidden>
      <button class="photo-lightbox-close" type="button" data-photo-lightbox-close aria-label="Đóng">×</button>
      <div class="photo-lightbox-inner">
        <img src="" alt="" data-photo-lightbox-target>
        <div class="photo-lightbox-meta"><div><strong data-photo-lightbox-title></strong><div class="small subtle" data-photo-lightbox-caption></div></div></div>
      </div>
    </div>
    <script>
    (function(){
      var lightbox = document.querySelector('[data-photo-lightbox]');
      var lightboxImg = document.querySelector('[data-photo-lightbox-target]');
      var lightboxTitle = document.querySelector('[data-photo-lightbox-title]');
      var lightboxCaption = document.querySelector('[data-photo-lightbox-caption]');
      var lightboxClose = document.querySelector('[data-photo-lightbox-close]');
      function closeLightbox(){ if(lightbox) lightbox.hidden = true; }
      if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
      if (lightbox) lightbox.addEventListener('click', function(e){ if(e.target === lightbox) closeLightbox(); });
      document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeLightbox(); });
      document.querySelectorAll('[data-photo-lightbox-image]').forEach(function(img){ img.addEventListener('click', function(){ if(!lightbox||!lightboxImg) return; lightboxImg.src = img.getAttribute('data-photo-lightbox-image') || ''; lightboxImg.alt = img.alt || ''; if(lightboxTitle) lightboxTitle.textContent = img.getAttribute('data-photo-lightbox-title') || ''; if(lightboxCaption) lightboxCaption.textContent = img.getAttribute('data-photo-lightbox-caption') || ''; lightbox.hidden = false; }); });
      document.querySelectorAll('[data-pot-photo-slider]').forEach(function(slider){
        var track = slider.querySelector('.photo-library-track');
        var slides = slider.querySelectorAll('.photo-library-slide');
        var prev = slider.querySelector('[data-pot-photo-prev]');
        var next = slider.querySelector('[data-pot-photo-next]');
        var viewport = slider.querySelector('[data-pot-photo-viewport]');
        var dots = slider.querySelectorAll('[data-pot-photo-dot]');
        var counter = slider.querySelector('[data-pot-photo-counter]');
        if (!track || !slides.length) return;
        var index = 0, startX = 0, deltaX = 0, mouseDown = false;
        function render(withFade){
          if (withFade) { track.classList.add('is-fading'); window.setTimeout(function(){ track.classList.remove('is-fading'); }, 180); }
          track.style.transform = 'translateX(' + (-index * 100) + '%)';
          if (prev) prev.disabled = index <= 0;
          if (next) next.disabled = index >= slides.length - 1;
          if (counter) counter.textContent = (index + 1) + '/' + slides.length;
          dots.forEach(function(dot, i){ dot.classList.toggle('is-active', i === index); });
        }
        function move(delta){ if (Math.abs(delta) < 40) return; if (delta < 0 && index < slides.length - 1) index++; if (delta > 0 && index > 0) index--; render(true); }
        if (prev) prev.addEventListener('click', function(){ if(index>0){ index--; render(true);} });
        if (next) next.addEventListener('click', function(){ if(index<slides.length-1){ index++; render(true);} });
        dots.forEach(function(dot){ dot.addEventListener('click', function(){ var n = Number(dot.getAttribute('data-pot-photo-dot')||'0'); if(!Number.isNaN(n)){ index=n; render(true);} }); });
        if (viewport) {
          viewport.addEventListener('touchstart', function(e){ startX=e.changedTouches[0].clientX; deltaX=0; }, {passive:true});
          viewport.addEventListener('touchmove', function(e){ deltaX=e.changedTouches[0].clientX-startX; }, {passive:true});
          viewport.addEventListener('touchend', function(){ move(deltaX); }, {passive:true});
          viewport.addEventListener('mousedown', function(e){ mouseDown=true; startX=e.clientX; deltaX=0; viewport.classList.add('is-dragging'); });
          viewport.addEventListener('mousemove', function(e){ if(!mouseDown) return; deltaX=e.clientX-startX; });
          viewport.addEventListener('mouseup', function(){ if(!mouseDown) return; mouseDown=false; viewport.classList.remove('is-dragging'); move(deltaX); });
          viewport.addEventListener('mouseleave', function(){ if(!mouseDown) return; mouseDown=false; viewport.classList.remove('is-dragging'); move(deltaX); });
        }
        render(false);
      });
    })();
    </script>
    <?php get_template_part('template-parts/site/eco-shell-end'); return; ?>
<?php endif; ?>

<?php $use_shared_eco_shell = $slug === 'portal/tro-ly-ai'; ?>
<?php if ($use_shared_eco_shell) : ?>
    <?php
    $ai_assistant_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/tro-ly-ai/')) : home_url('/portal/tro-ly-ai/');
    $ai_shell_nav = array_map(static function (array $item) use ($garden_key) {
        $url = (string) ($item['url'] ?? '#');
        if (in_array((string) ($item['key'] ?? ''), ['dashboard', 'kho-nong-cu', 'hang-xom'], true) && $garden_key !== '') {
            $url = add_query_arg('garden', $garden_key, $url);
        }
        return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => $url];
    }, aitrongcay_eco_nav_items());
    $ai_shell_nav[] = ['key' => 'tro-ly-ai', 'label' => '🤖 Trợ lý AI', 'url' => $ai_assistant_url];
    set_query_var('aitr_eco_shell', [
        'title' => 'Trợ lý AI',
        'active' => 'tro-ly-ai',
        'side_title' => 'Ai trồng cây',
        'side_subtitle' => 'Cindy đang trực',
        'side_badge' => '🤖',
        'top_icons' => ['🔔', '⚙️'],
        'search' => null,
        'nav' => $ai_shell_nav,
    ]);
    get_template_part('template-parts/site/eco-shell-start-v2');
    ?>
    <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-main{max-width:none;width:100%;margin:0}
    .portal-main--shared-shell{width:100%}
    .ai-agent-exact-page{min-height:auto}
    .ai-agent-exact-main{width:100%;padding-top:0;align-items:stretch;height:calc(100vh - 120px)}
    .ai-agent-exact-panel{height:100%;overflow:hidden}
    .ai-agent-design-log{min-height:0 !important;flex:1;overflow-y:auto}
    </style>
    <main class="portal-main portal-main--shared-shell">
<?php else : ?>
    <div class="portal-shell">
        <div class="portal-layout">
            <aside class="sidebar">
                <div class="logo" style="margin-bottom:20px"><span class="logo-badge">🌿</span><span>Ai trồng cây</span></div>

                <div class="sidebar-group">
                    <?php $render_portal_nav(); ?>
                </div>
                <div class="sidebar-group">
                    <h4>Tài khoản</h4>
                    <a href="<?php echo esc_url($garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/')); ?>"><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></a>
                    <a href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a>
                </div>
                <div class="sidebar-group">
                    <h4>Website</h4>
                    <a href="<?php echo esc_url(home_url('/')); ?>">← Quay lại website</a>
                </div>
            </aside>
            <main class="portal-main">
<?php endif; ?>
            <?php if (isset($_GET['auth_status'])) : ?>
                <div style="margin-bottom:20px"><?php aitrongcay_render_auth_notice(); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['photo_added'])) : ?>
                <div class="notice success" style="margin-bottom:20px"><strong>Ảnh mới đã vào kho ảnh.</strong><div style="margin-top:6px">Anh/chị có thể chọn ảnh này ngay để đăng Chợ quê.</div></div>
            <?php endif; ?>
            <?php if (isset($_GET['blynk_ctrl'])) : ?>
                <?php $blynk_ctrl_msg = sanitize_text_field((string) wp_unslash($_GET['blynk_ctrl'])); ?>
                <?php if (strpos($blynk_ctrl_msg, 'ok:') === 0) : ?>
                    <div class="notice success" style="margin-bottom:20px"><strong>Lệnh điều khiển đã được gửi thành công.</strong><div style="margin-top:6px">Thiết bị đang cập nhật trạng thái mới.</div></div>
                <?php else : ?>
                    <div class="notice error" style="margin-bottom:20px"><strong>Không gửi được lệnh điều khiển.</strong><div style="margin-top:6px">Vui lòng thử lại sau vài giây.</div></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($slug === 'portal/dashboard') : ?>
                <div class="portal-hero"><div class="portal-hero-top"><div class="portal-garden-name-wrap"><?php if ($can_control_garden) : ?><div class="garden-inline-rename" data-garden-inline-name data-garden-name="<?php echo esc_attr($garden_display_name); ?>"><div class="garden-inline-row"><h2 class="garden-inline-name-text" data-garden-display-name><?php echo esc_html($garden_display_name); ?></h2><input type="text" class="garden-inline-input" value="<?php echo esc_attr($garden_display_name); ?>" data-garden-inline-input maxlength="160" aria-label="Tên khu vườn" hidden><button class="garden-inline-name-edit" type="button" data-garden-inline-edit aria-label="Đổi tên khu vườn">✏️</button><span class="garden-inline-save-status" data-garden-inline-status hidden>Nhấn Enter hoặc click ra ngoài để lưu</span></div></div><?php else : ?><h2 data-garden-display-name><?php echo esc_html($garden_display_name); ?></h2><?php endif; ?></div><?php if ($has_real_pots) : ?><div class="portal-hero-actions inline-list"><a class="btn btn-primary" href="<?php echo esc_url(add_query_arg(['compose' => '1', 'garden' => $garden_key], home_url('/cho-que/'))); ?>">Đăng tin lên Chợ quê</a><button id="garden-view-mode-toggle" class="view-mode-switch is-private" type="button" data-state="private"><span class="view-mode-dot"></span><span class="view-mode-icon" aria-hidden="true">🔒</span><span class="view-mode-label">Chế độ view riêng tư</span></button><a class="btn btn-secondary" href="<?php echo esc_url(home_url('/portal/nhat-ky-cham-soc/')); ?>">Kho ảnh</a></div><?php endif; ?></div><?php if (! empty($garden_ai['summary'])) : ?><p style="margin:14px 0 14px"><?php echo esc_html($garden_ai['summary']); ?></p><?php endif; ?><?php if (! empty($garden_ai['tips'])) : ?><div class="cards-3"><?php foreach ($garden_ai['tips'] as $tip) : ?><div class="card soft-card" style="background:#ffffff;color:#0f172a;border:1px solid rgba(15,23,42,.12)"><p style="margin:0;color:#0f172a;font-weight:500"><?php echo esc_html($tip); ?></p></div><?php endforeach; ?></div><?php endif; ?></div>
                <div class="portal-cards">
                    <?php if ($current_role !== 'owner') : ?>
                        <section class="portal-card span-12"><div class="notice role-explainer role-explainer--<?php echo esc_attr($current_role); ?>" style="margin:0"><strong><?php echo esc_html($current_role === 'co_owner' ? 'Anh đang hỗ trợ khu vườn này với quyền đồng sở hữu.' : 'Anh đang mở khu vườn này ở chế độ chỉ xem.'); ?></strong><div style="margin-top:6px"><?php if ($garden_owner_user instanceof WP_User) : ?>Chủ vườn hiện tại là <?php echo esc_html($garden_owner_user->display_name ?: $garden_owner_user->user_login); ?>. <?php endif; ?><?php echo esc_html($current_role === 'co_owner' ? 'Anh vẫn điều khiển được đèn và bơm, nhưng chưa thể mời thêm người hay đổi quyền thành viên.' : 'Anh vẫn xem được dashboard, ảnh và trạng thái thiết bị, nhưng các nút điều khiển và quản trị chia sẻ sẽ bị khóa.'); ?></div></div></section>
                    <?php endif; ?>
                    <?php if ($has_real_pots) : ?>
                    <section class="portal-card span-12">
    <div class="section-head" style="margin-bottom:18px">
        <span class="eyebrow">Các khoang cây đang sở hữu</span>
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,1fr);gap:16px">
        <?php foreach ($pots as $pot) : ?>
            <?php
            $pot_light_device = trim((string) ($pot['light_device'] ?? ''));
            $pot_has_device = $pot_light_device !== '' && ! empty($pot['has_device']);
            $pot_can_control_device = $can_control_garden && $pot_has_device;
            $pot_latest_card = $latest_pot_photo_cards[(string) ($pot['code'] ?? '')] ?? ['url' => '', 'title' => '', 'download' => '', 'attachment_id' => 0, 'orientation_class' => ''];
            ?>
            <details class="card pot-card" style="padding:18px">
                <summary style="cursor:pointer">
                    <div class="pot-row-summary">
                        <div class="pot-row-media-wrap">
                            <div class="pot-row-media media-frame media-frame-16x9">
                                <img class="media-fit-cover <?php echo esc_attr((string) ($pot_latest_card['orientation_class'] ?? '')); ?>" src="<?php echo esc_url($pot['image'] ?? (get_template_directory_uri() . '/assets/images/hero-greenhouse.svg')); ?>" alt="<?php echo esc_attr($pot['name']); ?>">
                            </div>
                            <?php if (! empty($pot_latest_card['url'])) : ?>
                            <?php endif; ?>
                            <div class="pot-detail-toolbar pot-detail-toolbar-pro" style="margin-top:10px">
                                <form data-pot-photo-upload-form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" class="pot-toolbar-upload-form" style="margin:0">
                                    <?php wp_nonce_field('aitrongcay_upload_photo_submit', 'aitrongcay_upload_photo_nonce'); ?>
                                    <input type="hidden" name="action" value="aitrongcay_upload_photo_submit">
                                    <input type="hidden" name="garden_key" value="<?php echo esc_attr($garden_key); ?>">
                                    <input type="hidden" name="pot_code" value="<?php echo esc_attr($pot['code']); ?>">
                                    <input type="hidden" name="pot_name" value="<?php echo esc_attr($pot['name']); ?>">
                                    <input type="file" name="photo" accept="image/*" required hidden id="pot-photo-quick-<?php echo esc_attr(strtolower($pot['code'])); ?>">
                                    <label class="btn btn-secondary pot-toolbar-btn" for="pot-photo-quick-<?php echo esc_attr(strtolower($pot['code'])); ?>">📷 Thêm ảnh</label>
                                    <button type="submit" hidden aria-hidden="true" tabindex="-1">Upload</button>
                                    <span class="small subtle" data-pot-photo-upload-status style="display:none"></span>
                                </form>
                                <a class="btn btn-secondary pot-toolbar-btn" href="<?php echo esc_url(home_url('/portal/nhat-ky-cham-soc/#photo-' . strtolower((string) $pot['code']))); ?>">🖼️ Kho ảnh</a>
                                <button class="btn btn-secondary pot-toolbar-btn" type="button" data-analyze-latest-photo data-pot-code="<?php echo esc_attr($pot['code']); ?>">✨ Phân tích ảnh</button>
                            </div>
                        </div>

                        <div class="pot-row-main">
                            <div class="pot-row-top pot-row-top-compact">
                                <div class="pot-row-heading-block">
                                    <div class="kicker"><?php echo esc_html($pot['code']); ?></div>
                                    <?php if ($can_control_garden) : ?>
                                        <div
                                            class="pot-inline-rename"
                                            data-pot-inline-name
                                            data-pot-code="<?php echo esc_attr($pot['code']); ?>"
                                            data-pot-name="<?php echo esc_attr($pot['name']); ?>"
                                        >
                                            <div class="pot-inline-row">
                                                <button
                                                    class="pot-inline-name-edit"
                                                    type="button"
                                                    data-pot-inline-edit
                                                    aria-label="Đổi tên khoang <?php echo esc_attr($pot['code']); ?>"
                                                >✏️</button>
                                                <span class="pot-inline-name-text"><?php echo esc_html($pot['name']); ?></span>
                                                <a class="btn btn-secondary pot-inline-post-btn" href="<?php echo esc_url(add_query_arg(['compose' => '1', 'garden' => $garden_key], home_url('/cho-que/'))); ?>">Đăng tin</a>
                                                <span class="chip pot-metric-chip" style="margin-left:8px"><?php echo esc_html((string) ($pot['plant_name'] ?? 'Cây chưa xác định')); ?></span>
                                                <input
                                                    type="text"
                                                    class="pot-inline-input"
                                                    value="<?php echo esc_attr($pot['name']); ?>"
                                                    data-pot-inline-input
                                                    maxlength="120"
                                                    aria-label="Tên khoang <?php echo esc_attr($pot['code']); ?>"
                                                    hidden
                                                >
                                                <span class="pot-inline-save-status" data-pot-inline-status hidden>Nhấn Enter hoặc click ra ngoài để lưu</span>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <div class="pot-inline-row"><h3><?php echo esc_html($pot['name']); ?></h3><a class="btn btn-secondary pot-inline-post-btn" href="<?php echo esc_url(add_query_arg(['compose' => '1', 'garden' => $garden_key], home_url('/cho-que/'))); ?>">Đăng tin</a><span class="chip pot-metric-chip"><?php echo esc_html((string) ($pot['plant_name'] ?? 'Cây chưa xác định')); ?></span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="control-environment-metrics pot-metric-inline">
                                    <span class="chip pot-metric-chip">pH: <?php echo esc_html($pot['ph']); ?></span>
                                    <span class="chip pot-metric-chip" data-pot-temp-chip>Nhiệt độ: -- °C</span>
                                    <span class="chip pot-metric-chip" data-pot-hum-chip>Độ ẩm: -- %</span>
                                </div>
                            </div>

                            <div class="pot-control-layout pot-control-layout-flat">
                                <div class="control-environment-metrics pot-metric-grid">
                                    <span class="chip control-environment-status-chip"><?php echo esc_html($pot['status']); ?></span>
                                </div>

                                <div class="pot-value-placeholders">
                                    <div class="pot-value-chip">
                                        <span>Sản lượng dự kiến</span>
                                        <strong>Đang cập nhật</strong>
                                    </div>
                                    <div class="pot-value-chip">
                                        <span>Giá trị đã đầu tư</span>
                                        <strong>Đang cập nhật</strong>
                                    </div>
                                </div>

                                <div class="toggle-row control-toggle-row pot-control-buttons">
                                    <div class="pot-control-button-group">
                                        <?php if ($pot_can_control_device) : ?>
                                            <button class="blynk-light-toggle is-on" type="button" data-blynk-light-toggle="<?php echo esc_attr($pot_light_device); ?>" data-pot-code="<?php echo esc_attr($pot['code']); ?>" data-light-label="<?php echo esc_attr($pot['light']); ?>" data-action-on="Tắt đèn đi" data-action-off="Bật đèn lên" data-icon-on="💡" data-icon-off="🔅" data-state="1" onclick="return window.aitrLightToggle(this, event)">
                                                <span class="blynk-light-main">
                                                    <span class="blynk-light-icon">💡</span>
                                                    <span class="blynk-light-copy">
                                                        <span class="blynk-light-status-row"><span class="blynk-light-status-pill">Đang bật</span></span>
                                                        <span class="blynk-light-action-row"><span class="blynk-light-action-text">Tắt đèn đi</span></span>
                                                    </span>
                                                </span>
                                            </button>
                                        <?php elseif ($can_control_garden) : ?>
                                            <button class="blynk-light-toggle is-disabled" type="button" disabled aria-disabled="true" title="Khoang này chưa gắn bộ device">
                                                <span class="blynk-light-main"><span class="blynk-light-icon">💡</span><span class="blynk-light-off">Chưa gắn device</span></span>
                                            </button>
                                        <?php else : ?>
                                            <button class="blynk-light-toggle is-disabled" type="button" disabled aria-disabled="true">
                                                <span class="blynk-light-main"><span class="blynk-light-icon">💡</span><span class="blynk-light-off">Chỉ xem</span></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div class="pot-control-button-group">
                                        <?php if ($pot_can_control_device) : ?>
                                            <button class="blynk-light-toggle is-off" type="button" data-blynk-pump-toggle="pump" data-pot-code="<?php echo esc_attr($pot['code']); ?>" data-action-on="Tắt bơm đi" data-action-off="Bật bơm lên" data-icon-on="🫧" data-icon-off="💧" data-state="0">
                                                <span class="blynk-light-main">
                                                    <span class="blynk-light-icon">💧</span>
                                                    <span class="blynk-light-copy">
                                                        <span class="blynk-light-status-row"><span class="blynk-light-status-pill">Đang tắt</span><span class="blynk-light-status-text">Bơm chung hiện chưa chạy</span></span>
                                                        <span class="blynk-light-action-row"><span class="blynk-light-action-text">Bật bơm lên</span></span>
                                                    </span>
                                                </span>
                                            </button>
                                        <?php elseif ($can_control_garden) : ?>
                                            <button class="blynk-light-toggle is-disabled" type="button" disabled aria-disabled="true" title="Khoang này chưa gắn bộ device">
                                                <span class="blynk-light-main"><span class="blynk-light-icon">🫧</span><span class="blynk-light-off">Chưa gắn device</span></span>
                                            </button>
                                        <?php else : ?>
                                            <button class="blynk-light-toggle is-disabled" type="button" disabled aria-disabled="true">
                                                <span class="blynk-light-main"><span class="blynk-light-icon">🫧</span><span class="blynk-light-off">Chỉ xem</span></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $pot_start_date = trim((string) ($pot['created_at'] ?? ''));
                            if ($pot_start_date !== '') {
                                $pot_start_ts = strtotime($pot_start_date);
                                $pot_start_label = $pot_start_ts ? wp_date('d/m/Y', $pot_start_ts, wp_timezone()) : $pot_start_date;
                            } else {
                                $pot_start_label = 'Đang cập nhật';
                            }
                            $pot_harvest_label = trim((string) ($pot['harvest_eta'] ?? ''));
                            if ($pot_harvest_label === '') {
                                $pot_harvest_label = 'Đang cập nhật';
                            }
                            ?>
                            <div class="pot-control-layout pot-control-layout-flat pot-date-bar">
                                <div class="pot-date-pill"><span>Ngày bắt đầu</span><strong><?php echo esc_html($pot_start_label); ?></strong></div>
                                <div class="pot-date-pill"><span>Ngày dự kiến thu hoạch</span><strong><?php echo esc_html($pot_harvest_label); ?></strong></div>
                            </div>

                            <?php if ($pot_has_device) : ?>
                                <div class="small subtle live-device-status" style="margin-top:6px" data-pot-live-status="<?php echo esc_attr($pot_light_device); ?>">Trạng thái đèn của khoang đang đồng bộ...</div>
                            <?php elseif ($can_control_garden) : ?>
                                <div class="small subtle live-device-status" style="margin-top:6px">Khoang này chưa gắn bộ device nên chưa có trạng thái điều khiển live.</div>
                            <?php else : ?>
                                <div class="small subtle live-device-status" style="margin-top:6px">Chế độ chỉ xem.</div>
                            <?php endif; ?>

                            <div class="pot-analysis-inline pot-analysis-card pot-analysis-<?php echo esc_attr((string) ($pot['latest_analysis_color'] ?: 'none')); ?>" data-pot-analysis-card="<?php echo esc_attr($pot['code']); ?>">
                                <div class="pot-analysis-inline-head">
                                    <div class="pot-analysis-badge pot-analysis-inline-badge" data-pot-analysis-badge>Nhận xét của AI: <?php echo esc_html(! empty($pot['latest_analysis_label']) ? ('Cấp ' . (int) ($pot['latest_analysis_level'] ?? 0) . ' - ' . $pot['latest_analysis_label']) : 'Chưa có phân tích gần nhất'); ?></div>
                                </div>
                                <p class="small subtle" style="margin:10px 0 8px;font-weight:700;color:var(--forest-deep)" data-pot-analysis-stage>
                                    <?php
                                    $pot_name_lc_inline = strtolower((string) ($pot['name'] ?? ''));
                                    $pot_stage_text = trim((string) ($pot['harvest_eta'] ?? ''));
                                    if ($pot_stage_text === '') {
                                        if (str_contains($pot_name_lc_inline, 'cà chua') || str_contains($pot_name_lc_inline, 'ca chua') || str_contains($pot_name_lc_inline, 'tomato')) {
                                            $pot_stage_text = 'Sinh trưởng thân lá';
                                        } elseif (str_contains($pot_name_lc_inline, 'cải cúc') || str_contains($pot_name_lc_inline, 'cai cuc') || str_contains($pot_name_lc_inline, 'tần ô') || str_contains($pot_name_lc_inline, 'tan o')) {
                                            $pot_stage_text = 'Cây con sớm';
                                        } elseif (str_contains($pot_name_lc_inline, 'cải xoong') || str_contains($pot_name_lc_inline, 'cai xoong') || str_contains($pot_name_lc_inline, 'watercress')) {
                                            $pot_stage_text = 'Mới gieo';
                                        } elseif (str_contains($pot_name_lc_inline, 'sâm ngọc linh') || str_contains($pot_name_lc_inline, 'sam ngoc linh')) {
                                            $pot_stage_text = 'Cây con rất sớm';
                                        } elseif (str_contains($pot_name_lc_inline, 'ớt') || str_contains($pot_name_lc_inline, 'ot') || str_contains($pot_name_lc_inline, 'pepper') || str_contains($pot_name_lc_inline, 'chili') || str_contains($pot_name_lc_inline, 'chilli')) {
                                            $pot_stage_text = 'Sinh trưởng thân lá';
                                        } else {
                                            $pot_stage_text = 'Đang chờ xác định rõ giai đoạn';
                                        }
                                    }
                                    echo esc_html($pot_stage_text);
                                    ?>
                                </p>
                                <p class="small subtle" style="margin:0 0 8px" data-pot-analysis-summary><?php echo esc_html(! empty($pot['latest_analysis_summary']) ? (string) $pot['latest_analysis_summary'] : ((string) ($pot['status_summary'] ?? 'Khi anh bấm phân tích, hệ thống sẽ lấy toàn bộ ảnh của chậu trong ngày gần nhất để tạo cảnh báo và gợi ý chăm sóc.'))); ?></p>
                                <div class="small subtle" style="margin:0 0 8px" data-pot-analysis-actions>
                                    <?php if (! empty($pot['latest_analysis_actions']) && is_array($pot['latest_analysis_actions'])) : ?>
                                        <strong>Người dùng nên làm gì:</strong> <?php echo esc_html(implode(' · ', array_map('strval', $pot['latest_analysis_actions']))); ?>
                                    <?php else : ?>
                                        <strong>Người dùng nên làm gì:</strong> Chờ phân tích ảnh mới nhất để nhận hướng dẫn cụ thể.
                                    <?php endif; ?>
                                </div>
                                <div class="small subtle" style="margin:0 0 8px" data-pot-analysis-escalate>
                                    <?php if (! empty($pot['latest_analysis_escalate']) && is_array($pot['latest_analysis_escalate'])) : ?>
                                        <strong>Khi nào cần lo hơn:</strong> <?php echo esc_html(implode(' · ', array_map('strval', $pot['latest_analysis_escalate']))); ?>
                                    <?php else : ?>
                                        <strong>Khi nào cần lo hơn:</strong> Khi có dấu hiệu lan rộng hơn hoặc chạm tới lá non.
                                    <?php endif; ?>
                                </div>
                                <div class="small subtle" style="margin:0" data-pot-analysis-knowledge>
                                    <strong>Căn cứ phân tích:</strong>
                                    <?php
                                    if (str_contains($pot_name_lc_inline, 'cà chua') || str_contains($pot_name_lc_inline, 'ca chua') || str_contains($pot_name_lc_inline, 'tomato')) {
                                        echo esc_html('Đọc theo 3 tầng ngọn – giữa – gốc và đối chiếu thêm trạng thái nuôi quả nếu có.');
                                    } elseif (str_contains($pot_name_lc_inline, 'cải cúc') || str_contains($pot_name_lc_inline, 'cai cuc') || str_contains($pot_name_lc_inline, 'tần ô') || str_contains($pot_name_lc_inline, 'tan o')) {
                                        echo esc_html('Nhìn toàn khoang trước, rồi mới soi từng cốc; ưu tiên độ đồng đều, mật độ và giai đoạn cây con.');
                                    } elseif (str_contains($pot_name_lc_inline, 'cải xoong') || str_contains($pot_name_lc_inline, 'cai xoong') || str_contains($pot_name_lc_inline, 'watercress')) {
                                        echo esc_html('Phân biệt giai đoạn mới gieo với chậm bất thường kéo dài; ưu tiên theo dõi mốc 1–3 ngày.');
                                    } elseif (str_contains($pot_name_lc_inline, 'sâm ngọc linh') || str_contains($pot_name_lc_inline, 'sam ngoc linh')) {
                                        echo esc_html('Nhìn theo cả cụm cây con; ưu tiên độ ổn định môi trường, độ đồng đều giữa các cốc và mốc 2–5 ngày.');
                                    } elseif (str_contains($pot_name_lc_inline, 'ớt') || str_contains($pot_name_lc_inline, 'ot') || str_contains($pot_name_lc_inline, 'pepper') || str_contains($pot_name_lc_inline, 'chili') || str_contains($pot_name_lc_inline, 'chilli')) {
                                        echo esc_html('Đọc theo 3 tầng ngọn – giữa – gốc; nếu có hoa hoặc trái thì đối chiếu thêm trạng thái mang trái.');
                                    } else {
                                        echo esc_html('Đối chiếu ảnh mới nhất với giai đoạn sinh trưởng và diễn biến gần nhất của chậu.');
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </summary>

                <div class="pot-detail-grid">
                    <div>
                        <div style="margin-top:14px">
                            <label for="note-<?php echo esc_attr($pot['code']); ?>" class="small subtle">Ghi thêm vào nhật ký canh tác</label>
                            <textarea id="note-<?php echo esc_attr($pot['code']); ?>" data-autosave-note data-note-key="<?php echo esc_attr($pot['code']); ?>" placeholder="Ví dụ: hôm nay vừa gieo hạt, vừa thay nước, lá gốc bắt đầu vàng nhẹ..."><?php echo esc_textarea((string) ($pot_notes[(string) ($pot['code'] ?? '')]['note_text'] ?? '')); ?></textarea>
                            <div class="inline-list" style="margin-top:10px"><span class="note-status-live" data-note-status>Sẵn sàng</span></div>
                        </div>
                    </div>
                </div>
            </details>
        <?php endforeach; ?>
        <div class="pot-add-cta-wrap">
            <?php if ($has_rack) : ?>
                <a class="btn btn-primary pot-add-cta" href="<?php echo esc_url($garden_key !== '' ? add_query_arg(['garden' => rawurlencode($garden_key), 'mode' => 'onboarding'], home_url('/portal/tro-ly-ai/')) : add_query_arg('mode', 'onboarding', home_url('/portal/tro-ly-ai/'))); ?>">＋ khoang cây</a>
            <?php else : ?>
                <span class="btn btn-primary pot-add-cta" style="opacity:0.6; cursor:not-allowed;" title="Vui lòng liên hệ quản trị viên để được cấp Rack">🧰 chờ cấp rack</span>
            <?php endif; ?>
        </div>
    </div>
</section>
                    <section class="portal-card span-12"><div class="section-head" style="margin-bottom:18px"><span class="eyebrow">Lịch sử canh tác</span><h3 style="margin-bottom:0">Các mốc thực tế đang theo dõi</h3><div class="inline-list" style="margin-top:14px"><a class="btn btn-secondary" href="<?php echo esc_url(home_url('/portal/nhat-ky-cham-soc/')); ?>">Mở kho ảnh</a></div></div><?php $garden_activity_cards = []; foreach ($pots as $pot_item) : $pot_code = trim((string) ($pot_item['code'] ?? '')); $pot_name = trim((string) ($pot_item['name'] ?? '')); $pot_note = trim((string) ($pot_notes[$pot_code]['note_text'] ?? '')); $pot_summary = trim((string) ($pot_item['status_summary'] ?? '')); $pot_status = trim((string) ($pot_item['status'] ?? '')); $pot_eta = trim((string) ($pot_item['harvest_eta'] ?? '')); $activity_text = $pot_note !== '' ? $pot_note : ($pot_summary !== '' ? $pot_summary : ($pot_status !== '' ? $pot_status : '')); if ($activity_text === '' && $pot_eta !== '') { $activity_text = $pot_eta; } if ($activity_text === '') { continue; } $activity_label = $pot_code !== '' ? $pot_code : ($pot_name !== '' ? $pot_name : 'Khoang cây'); $garden_activity_cards[] = ['label' => $activity_label, 'text' => $activity_text, 'is_note' => $pot_note !== '']; if (count($garden_activity_cards) >= 3) { break; } endforeach; ?><?php if ($garden_activity_cards) : ?><div class="cards-3"><?php foreach ($garden_activity_cards as $activity_item) : ?><div class="card soft-card"><div class="kicker"><?php echo esc_html($activity_item['label']); ?><?php if (! empty($activity_item['is_note'])) : ?> · Ghi chú mới<?php endif; ?></div><p style="margin:0"><?php echo esc_html($activity_item['text']); ?></p></div><?php endforeach; ?></div><?php else : ?><div class="notice">Vườn này chưa có mốc canh tác nào được ghi nhận.</div><?php endif; ?></section>
                    <section class="portal-card span-12 farm-game-shelf" id="tool-shelf" style="scroll-margin-top:24px">
    <div class="section-head farm-game-shelf-head" style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap">
        <div>
            <span class="eyebrow">Kho nông cụ</span>
            <h3 style="margin-bottom:0">Kho nông cụ của khu vườn</h3>
            <p class="small subtle" style="margin:8px 0 0">Thiết kế theo cảm hứng game nông trại: vui mắt, rõ đồ, dễ chọn nhanh.</p>
        </div>
        <div class="inline-list farm-game-shelf-nav"><button class="btn btn-secondary" type="button" data-shelf-prev>◀</button><button class="btn btn-secondary" type="button" data-shelf-next>▶</button></div>
    </div>

    <div class="cards-4 farm-game-shelf-grid" data-tool-shelf>
        <?php foreach ($tool_shelf as $index => $item) : ?>
            <?php $tool_image = function_exists('aitrongcay_portal_default_tool_image') ? aitrongcay_portal_default_tool_image((array) $item) : ((string) ($item['image'] ?? 'tools-shed.svg')); ?>
            <?php $tool_image_url = get_template_directory_uri() . '/assets/images/' . $tool_image; ?>
            <div class="store-item-card farm-game-item-card" data-shelf-item data-shelf-index="<?php echo esc_attr((string) $index); ?>">
                <div class="store-item-media-wrap">
                    <a href="#" class="store-item-open" data-tool-popup-open data-tool-name="<?php echo esc_attr($item['name']); ?>" data-tool-type="<?php echo esc_attr($item['type']); ?>" data-tool-desc="<?php echo esc_attr($item['description']); ?>" data-tool-image="<?php echo esc_attr($tool_image_url); ?>">
                        <img src="<?php echo esc_url($tool_image_url); ?>" alt="<?php echo esc_attr($item['name']); ?>" class="store-item-media">
                    </a>
                    <span class="store-stock-badge farm-game-stock-badge"><?php echo esc_html((string) $item['owned']); ?></span>
                </div>
                <a href="#" class="store-item-title store-item-open" data-tool-popup-open data-tool-name="<?php echo esc_attr($item['name']); ?>" data-tool-type="<?php echo esc_attr($item['type']); ?>" data-tool-desc="<?php echo esc_attr($item['description']); ?>" data-tool-image="<?php echo esc_attr($tool_image_url); ?>"><?php echo esc_html($item['name']); ?></a>
                <small class="subtle"><?php echo esc_html($item['type']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="notice farm-game-shelf-note" style="margin-top:14px"><div class="inline-list" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap"><strong>Kho này giúp anh nhìn nhanh các nông cụ và vật tư đang dùng, giống một kho đồ trong game nhưng bám đúng khu vườn thật.</strong><a class="btn btn-secondary" href="<?php echo esc_url(home_url('/portal/tro-ly-ai/')); ?>">💬 Hỏi trợ lý AI</a></div></div>

    <div class="tool-popup" data-tool-popup hidden>
        <div class="tool-popup-backdrop" data-tool-popup-close></div>
        <div class="tool-popup-card farm-game-tool-popup" role="dialog" aria-modal="true" aria-label="Chi tiết vật phẩm">
            <button class="tool-popup-close" type="button" data-tool-popup-close>×</button>
            <img src="" alt="" class="tool-popup-image" data-tool-popup-image>
            <div class="kicker" data-tool-popup-type></div>
            <h3 style="margin:8px 0 10px" data-tool-popup-name></h3>
            <p style="margin:0" data-tool-popup-desc></p>
        </div>
    </div>
</section>
                    <?php else : ?>
                    <section class="portal-card span-12">
                        <div class="notice" style="margin:0;padding:24px;border-radius:24px;background:linear-gradient(180deg,#f7fff8 0%,#eefbf0 100%);border:1px solid rgba(47,123,69,.16)">
                            <div class="section-head" style="margin-bottom:14px">
                                <span class="eyebrow">Khu vườn của anh đang chờ</span>
                                <h3 style="margin-bottom:0">Chưa có khoang trồng cây nào ở đây cả</h3>
                            </div>
                            <p style="margin:0 0 14px">Giống như một góc vườn mới dọn đất xong, chỗ này đang chờ anh đặt khoang trồng cây đầu tiên vào để bắt đầu theo dõi mỗi ngày.</p>
                            <div class="inline-list" style="margin-top:14px">
                                <?php if ($has_rack) : ?>
                                    <a class="btn btn-primary" href="<?php echo esc_url($garden_key !== '' ? add_query_arg(['garden' => rawurlencode($garden_key), 'mode' => 'onboarding'], home_url('/portal/tro-ly-ai/')) : add_query_arg('mode', 'onboarding', home_url('/portal/tro-ly-ai/'))); ?>">Thêm khoang trồng cây đầu tiên</a>
                                <?php else : ?>
                                    <span class="btn btn-primary" style="opacity:0.6; cursor:not-allowed;" title="Vui lòng liên hệ quản trị viên để được cấp Rack">Đang chờ Admin cấp Rack</span>
                                <?php endif; ?>
                                <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/portal/chia-se-khu-vuon/')); ?>">Mời người vào vườn</a>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>
            <?php elseif ($slug === 'portal/nhat-ky-cham-soc') : ?>
                <?php
                $nk_rack_slots = function_exists('aitrongcay_get_rack_slots') ? aitrongcay_get_rack_slots($garden_key) : [];
                $nk_rack_by_pot = [];
                $nk_rack_names = [];
                foreach ($nk_rack_slots as $slot) {
                    $pcode = trim((string)($slot['pot_code'] ?? ''));
                    $r_id = (int)($slot['rack_id'] ?? 0);
                    if ($pcode !== '' && $r_id > 0) {
                        $nk_rack_by_pot[$pcode] = $r_id;
                        $nk_rack_names[$r_id] = trim((string)($slot['rack_name'] ?? '')) ?: 'Rack ' . $r_id;
                    }
                }
                
                $pots_by_rack_nk = [];
                foreach ($pots as $pot_item) {
                    $r_id = $nk_rack_by_pot[$pot_item['code']] ?? -1;
                    if (!isset($nk_rack_names[$r_id])) {
                        $nk_rack_names[$r_id] = $r_id === -1 ? 'Chưa phân rack' : 'Rack ' . $r_id;
                    }
                    if (!isset($pots_by_rack_nk[$r_id])) $pots_by_rack_nk[$r_id] = [];
                    $pots_by_rack_nk[$r_id][] = $pot_item;
                }
                $first_rack_id = count($nk_rack_names) > 0 ? array_keys($nk_rack_names)[0] : -1;
                ?>
                <div class="portal-cards">
                    <section class="portal-card span-12" style="padding-bottom:24px">
                        <div class="section-head" style="margin-bottom:0">
                            <span class="eyebrow">Kho ảnh của vườn</span>
                            <h3 style="margin-bottom:0">Nhật ký hình ảnh từng khoang</h3>
                            <p class="small subtle" style="margin:8px 0 0">Chọn đúng Rack và Khoang để xem chi tiết ảnh chụp từ hệ thống hoặc thêm ảnh mới. Ảnh sẽ dùng cho Timelapse và trợ lý AI.</p>
                            
                            <div style="margin-top:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                                <?php if (count($nk_rack_names) > 1): ?>
                                <label style="max-width:100%;font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:8px;background:#f8fafc;padding:6px 14px;border-radius:12px;border:1px solid rgba(15,23,42,.08)">Rack: 
                                    <select id="nkRackSelect" onchange="AITR_NK_FILTER_RACK(this.value)" style="max-width:100%;border:none;background:transparent;font-weight:700;font-size:15px;color:#2f7b45;outline:none;cursor:pointer">
                                        <?php foreach ($nk_rack_names as $r_id => $r_name): ?>
                                        <option value="<?php echo esc_attr((string)$r_id); ?>"><?php echo esc_html($r_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <?php endif; ?>
                                
                                <label style="max-width:100%;font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:8px;background:#f8fafc;padding:6px 14px;border-radius:12px;border:1px solid rgba(15,23,42,.08)">Khoang: 
                                    <select id="nkKhoangSelect" onchange="AITR_NK_FILTER_KHOANG(this.value)" style="max-width:100%;border:none;background:transparent;font-weight:700;font-size:15px;color:#2f7b45;outline:none;cursor:pointer">
                                    </select>
                                </label>
                            </div>

                            <script>
                                var nkPotsData = <?php echo wp_json_encode($pots_by_rack_nk); ?>;
                                
                                function AITR_NK_FILTER_RACK(rackId) {
                                    var kSelect = document.getElementById('nkKhoangSelect');
                                    kSelect.innerHTML = '';
                                    var pots = nkPotsData[rackId] || [];
                                    pots.forEach(function(p) {
                                        var opt = document.createElement('option');
                                        opt.value = p.code;
                                        opt.textContent = p.name;
                                        kSelect.appendChild(opt);
                                    });
                                    if (pots.length > 0) {
                                        AITR_NK_FILTER_KHOANG(pots[0].code);
                                    } else {
                                        AITR_NK_FILTER_KHOANG('');
                                    }
                                }
                                
                                function AITR_NK_FILTER_KHOANG(potCode) {
                                    document.querySelectorAll('[data-nk-pot-section]').forEach(function(el) {
                                        if (el.getAttribute('data-nk-pot-section') === potCode) {
                                            el.style.display = '';
                                        } else {
                                            el.style.display = 'none';
                                        }
                                    });
                                }

                                document.addEventListener('DOMContentLoaded', function() {
                                    var rSelect = document.getElementById('nkRackSelect');
                                    var firstRack = rSelect ? rSelect.value : '<?php echo esc_js((string)$first_rack_id); ?>';
                                    AITR_NK_FILTER_RACK(firstRack);
                                });
                            </script>
                        </div>
                    </section>
                    <?php foreach ($pots as $pot_item) : ?>
                        <?php 
                        $group_items = $pot_photo_groups[$pot_item['code']] ?? []; 
                        // Mặc định ẩn tất cả các section, JS sẽ hiển thị section đầu tiên
                        $display_style = 'display:none;';
                        ?>
                        <section class="portal-card span-12" id="photo-<?php echo esc_attr(strtolower($pot_item['code'])); ?>" data-nk-pot-section="<?php echo esc_attr($pot_item['code']); ?>" style="<?php echo $display_style; ?>">
                            <div class="section-head" style="margin-bottom:18px">
                                <span class="eyebrow"><?php echo esc_html($pot_item['code']); ?></span>
                                <h3 style="margin-bottom:0"><?php echo esc_html($pot_item['name']); ?></h3>
                                <p class="small subtle" style="margin:8px 0 12px">Ảnh thuộc khoang này · <?php echo esc_html($pot_item['status']); ?> · <?php echo esc_html($pot_item['light']); ?></p>
                                
                                <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key, 'view' => 'timelapse', 'tl_rack' => $pot_rack_id, 'tl_stream' => $pot_item['code']], home_url('/portal/dashboard-2/'))); ?>" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                                    Xem Timelapse khoang này
                                </a>
                            </div>
                            <?php $latest_card = $latest_pot_photo_cards[$pot_item['code']] ?? ['url' => '', 'title' => '', 'download' => '', 'attachment_id' => 0, 'orientation_class' => '']; ?>
                            <div class="grid-2" style="margin-bottom:18px;align-items:start">
                                <div class="card soft-card">
                                    <div class="kicker">Ảnh mới nhất của chậu</div>
                                    <?php if (! empty($latest_card['url'])) : ?>
                                        <div class="card-media media-frame media-frame-16x9" style="margin-top:10px"><img class="media-thumb media-fit-cover <?php echo esc_attr((string) ($latest_card['orientation_class'] ?? '')); ?>" src="<?php echo esc_url($latest_card['url']); ?>" alt="<?php echo esc_attr($pot_item['name']); ?>" data-latest-pot-photo-preview="<?php echo esc_attr($pot_item['code']); ?>"></div>
                                        <p class="small subtle" style="margin:10px 0 0" data-latest-pot-photo-label="<?php echo esc_attr($pot_item['code']); ?>"><?php echo esc_html($latest_card['title'] !== '' ? $latest_card['title'] : 'Ảnh mới nhất của chậu này'); ?></p>
                                    <?php else : ?>
                                        <div class="notice" style="margin-top:10px" data-latest-pot-photo-empty="<?php echo esc_attr($pot_item['code']); ?>">Chưa có ảnh mới nhất cho chậu này.</div>
                                    <?php endif; ?>
                                </div>
                                <div class="card soft-card">
                                    <div class="kicker">Thêm ảnh mới</div>
                                    <p class="small subtle" style="margin:8px 0 12px">Tải ảnh trực tiếp cho đúng chậu cây này. Ảnh mới nhất sẽ được lưu vào DB để portal, AI và phần viết bài dùng chung.</p>
                                    <form data-pot-photo-upload-form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                                        <?php wp_nonce_field('aitrongcay_upload_photo_submit', 'aitrongcay_upload_photo_nonce'); ?>
                                        <input type="hidden" name="action" value="aitrongcay_upload_photo_submit">
                                        <input type="hidden" name="garden_key" value="<?php echo esc_attr($garden_key); ?>">
                                        <input type="hidden" name="pot_code" value="<?php echo esc_attr($pot_item['code']); ?>">
                                        <input type="hidden" name="pot_name" value="<?php echo esc_attr($pot_item['name']); ?>">
                                        <input type="file" name="photo" accept="image/*" required>
                                        <div class="inline-list" style="margin-top:12px">
                                            <button class="btn btn-primary" type="submit">Upload ảnh cho chậu này</button>
                                            <span class="small subtle" data-pot-photo-upload-status><?php echo esc_html($pot_item['code']); ?> đang chờ ảnh mới.</span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php if (! empty($group_items)) : ?>
                                <div class="cards-3">
                                    <?php foreach ($group_items as $photo_post) : ?>
                                        <?php
                                        $attachment_id = (int) $photo_post->ID;
                                        $image_url = wp_make_link_relative((string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id)));
                                        $download_url = wp_make_link_relative((string) wp_get_attachment_url($attachment_id));
                                        $title = get_the_title($attachment_id);
                                        $caption = trim((string) ($photo_post->post_content ?: 'Ảnh thực tế của ' . $pot_item['name']));
                                        ?>
                                        <article class="card soft-card" data-photo-card data-photo-id="<?php echo esc_attr((string) $attachment_id); ?>">
                                            <div class="card-media media-frame media-frame-16x9">
                                                <img class="media-thumb media-fit-cover" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                                            </div>
                                            <div class="kicker"><?php echo esc_html($pot_item['code']); ?> · Ảnh thật</div>
                                            <h4 data-photo-title style="margin:8px 0 6px"><?php echo esc_html($title); ?></h4>
                                            <p class="small subtle" style="margin:0"><?php echo esc_html($caption); ?></p>
                                            <div class="inline-list photo-actions" style="margin-top:12px;justify-content:space-between;align-items:center;gap:10px">
                                                <a class="small-link" href="<?php echo esc_url($download_url); ?>" target="_blank" rel="noopener">Mở ảnh gốc</a>
                                                <a class="small-link" href="<?php echo esc_url($download_url); ?>" download>Tải ảnh</a>
                                                <button class="btn btn-ghost" type="button" data-delete-photo="<?php echo esc_attr((string) $attachment_id); ?>" aria-label="Xóa ảnh này" title="Xóa ảnh này" style="min-width:auto;padding:6px 10px;border-radius:999px">✕</button>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div class="notice">Chưa có ảnh nào được gắn vào khoang này.</div>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                    <?php if (! empty($pot_photo_groups['UNGROUPED'])) : ?>
                        <section class="portal-card span-12">
                            <div class="section-head" style="margin-bottom:18px">
                                <span class="eyebrow">Chưa phân loại</span>
                                <h3 style="margin-bottom:0">Ảnh cần gắn thêm vào khoang cụ thể</h3>
                            </div>
                            <div class="cards-3">
                                <?php foreach ($pot_photo_groups['UNGROUPED'] as $photo_post) : ?>
                                    <?php $attachment_id = (int) $photo_post->ID; ?>
                                    <article class="card soft-card"><h4 style="margin:0 0 8px"><?php echo esc_html(get_the_title($attachment_id)); ?></h4><p class="small subtle" style="margin:0">Ảnh này đã thuộc tài khoản vườn nhưng chưa gắn mã khoang.</p></article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            <?php elseif ($slug === 'portal/chia-se-khu-vuon') : ?>
                <?php
                $friend_rows = [];
                foreach (array_merge($co_owner_members, $viewer_members) as $member) {
                    $friend_id = (int) ($member['user_id'] ?? 0);
                    if ($friend_id <= 0) {
                        continue;
                    }
                    $friend = get_user_by('id', $friend_id);
                    if (! $friend instanceof WP_User) {
                        continue;
                    }
                    $friend_rows[] = [
                        'user' => $friend,
                        'role' => (string) ($member['role'] ?? 'viewer'),
                        'membership_id' => (int) ($member['id'] ?? 0),
                    ];
                }
                usort($friend_rows, static function (array $a, array $b): int {
                    $aw = ($a['role'] ?? 'viewer') === 'co_owner' ? 0 : 1;
                    $bw = ($b['role'] ?? 'viewer') === 'co_owner' ? 0 : 1;
                    if ($aw !== $bw) return $aw <=> $bw;
                    return strcasecmp((string) (($a['user']->display_name ?? $a['user']->user_login ?? '')), (string) (($b['user']->display_name ?? $b['user']->user_login ?? '')));
                });
                ?>
                <div class="portal-cards">
                    <section class="portal-card span-12 share-hero-card">
                        <div class="portal-switcher-inline-meta" style="justify-content:space-between;align-items:center">
                            <h3 style="margin:0" data-garden-display-name><?php echo esc_html($active_profile['garden_name']); ?></h3>
                            <div class="inline-list">
                                <span class="member-badge <?php echo esc_attr($current_role_badge_class); ?>"><?php echo esc_html($current_role_label); ?></span>
                                <span class="chip"><?php echo esc_html($active_member_count); ?></span>
                            </div>
                        </div>
                    </section>
                    <section class="portal-card span-12">
                        <div class="social-stack">
                            <div class="member-group share-single-card">
                                <div class="member-group-head share-single-card-head"><strong>Chia sẻ khu vườn</strong><span class="small subtle"><?php echo esc_html(count($friend_rows)); ?></span></div>
                                <article class="social-item member-row member-row-owner">
                                    <div class="social-item-main">
                                        <div class="member-row-head">
                                            <strong><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></strong>
                                            <span class="member-badge <?php echo esc_attr(aitrongcay_get_role_badge_class('owner')); ?>">Chủ vườn</span>
                                        </div>
                                    </div>
                                </article>
                                <?php foreach ($friend_rows as $row) : $friend = $row['user']; $role = (string) $row['role']; $next_role = $role === 'co_owner' ? 'viewer' : 'co_owner'; ?>
                                    <article class="social-item member-row share-friend-row share-friend-row--<?php echo esc_attr($role); ?>" tabindex="0" role="button" data-friend-role-toggle data-user-id="<?php echo esc_attr((string) $friend->ID); ?>" data-membership-id="<?php echo esc_attr((string) $row['membership_id']); ?>" data-current-role="<?php echo esc_attr($role); ?>" data-next-role="<?php echo esc_attr($next_role); ?>">
                                        <div class="social-item-main">
                                            <div class="member-row-head">
                                                <strong class="share-friend-name"><?php echo esc_html($friend->display_name ?: $friend->user_login); ?></strong>
                                                <span class="member-badge <?php echo esc_attr(aitrongcay_get_role_badge_class($role)); ?> share-role-badge"><?php echo esc_html(aitrongcay_get_role_label($role)); ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            <?php elseif ($slug === 'portal/tro-ly-ai') : ?>
                <?php $is_ai_onboarding = isset($_GET['mode']) && sanitize_key((string) wp_unslash($_GET['mode'])) === 'onboarding'; ?>
                <?php $ai_sessions = $is_logged_in && function_exists('aitrongcay_ai_list_sessions') ? aitrongcay_ai_list_sessions($current_user, $garden_key, 30) : []; ?>
                <?php $guest_ai_summary = 'Chào anh, em có thể tư vấn chọn cây theo nhu cầu dinh dưỡng, mùa vụ và mục tiêu sử dụng của gia đình. Anh có thể bắt đầu hỏi ngay ở chế độ khách, khi cần lưu hành trình hoặc gắn với khu vườn cụ thể thì mình đăng nhập sau cũng được.'; ?>
                <?php 
                $active_session_title = 'Cuộc trò chuyện mới';
                if ($is_logged_in && !empty($ai_sessions[0])) {
                    $active_session_title = trim((string) ($ai_sessions[0]['last_user_message'] ?? ''));
                    if ($active_session_title === '') $active_session_title = 'Cuộc trò chuyện mới';
                    else $active_session_title = wp_trim_words($active_session_title, 8, '...');
                }
                ?>
                <div class="ai-agent-exact-page">
                    <div class="ai-agent-exact-main">
                        <?php if ($is_logged_in) : ?>
                        <aside class="ai-agent-session-rail">
                            <div class="ai-agent-session-rail-head">
                                <button class="btn btn-secondary ai-session-new-btn" type="button" data-ai-session-new>＋ Chat mới</button>
                            </div>
                            <div class="ai-agent-session-list" data-ai-session-list>
                                <?php foreach ($ai_sessions as $index => $session_item) : ?>
                                    <?php 
                                        $display_title = trim((string) ($session_item['last_user_message'] ?? ''));
                                        if ($display_title === '') $display_title = 'Cuộc trò chuyện mới';
                                        else $display_title = wp_trim_words($display_title, 8, '...');
                                    ?>
                                    <button
                                        class="ai-agent-session-item<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                        type="button"
                                        data-ai-session-item
                                        data-session-id="<?php echo esc_attr((string) ($session_item['id'] ?? 0)); ?>"
                                        data-session-title="<?php echo esc_attr($display_title); ?>"
                                    >
                                        <strong style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block"><?php echo esc_html($display_title); ?></strong>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </aside>
                        <?php endif; ?>
                        <div class="ai-agent-exact-panel" data-garden-ai-chat<?php echo $is_ai_onboarding ? ' data-ai-onboarding="1"' : ''; ?>>
                            <div class="ai-agent-design-header" style="display:flex;flex-direction:row;align-items:center;gap:12px">
                                <?php if ($is_logged_in) : ?>
                                    <button type="button" class="btn btn-ghost ai-agent-rail-toggle" data-ai-rail-toggle aria-label="Mở lịch sử chat" style="padding:8px;border-radius:12px;margin-left:-8px">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                                    </button>
                                <?php endif; ?>
                                <div style="min-width:0;flex:1">
                                    <h1 class="ai-agent-design-title" data-ai-session-title style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($is_ai_onboarding ? 'Giao thức khởi tạo khoang' : (! $is_logged_in ? 'AI tư vấn trồng cây cho gia đình' : $active_session_title)); ?></h1>
                                    <?php if (! $is_logged_in && ! $is_ai_onboarding) : ?>
                                        <div class="ai-agent-design-meta small subtle" style="margin-top:4px">Đăng nhập để lưu lịch sử tư vấn và gắn đề xuất này với khu vườn của anh.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="garden-ai-chat-log garden-ai-chat-log-page ai-agent-design-log" data-garden-ai-log>
                                <?php if ($is_ai_onboarding) : ?>
                                    <div class="garden-ai-message assistant"><div class="garden-ai-bubble"><?php echo esc_html($has_rack ? 'Chào anh, em sẽ hỗ trợ tạo khoang đầu tiên cho khu vườn này. Anh muốn trồng cây gì ạ?' : 'Chào anh, khu vườn này cần khởi tạo rack trước rồi mới tạo khoang. Nếu rack trong kho còn, hệ thống sẽ cấp cho anh ngay.'); ?></div></div>
                                <?php elseif (! $is_logged_in) : ?>
                                    <div class="garden-ai-message assistant"><div class="garden-ai-bubble"><?php echo esc_html($guest_ai_summary); ?></div></div>
                                <?php else : ?>
                                    <div class="garden-ai-message assistant"><div class="garden-ai-bubble"><?php echo esc_html((string) (($garden_ai['summary'] ?? '') ?: 'Chào anh, em đang sẵn sàng hỗ trợ khu vườn này.')); ?></div></div>
                                <?php endif; ?>
                            </div>

                            <form class="garden-ai-chat-form ai-agent-design-form" data-garden-ai-form data-session-id="<?php echo esc_attr((string) ((int) (!empty($ai_sessions[0]['id']) ? $ai_sessions[0]['id'] : 0))); ?>"<?php echo $is_ai_onboarding ? ' data-ai-onboarding-form="1"' : ''; ?>>
                                <?php if (! $is_ai_onboarding) : ?>
                                    <div class="ai-agent-design-chips">
                                        <button class="ai-agent-design-chip" type="button" data-ai-fill="Báo cáo sức khỏe khu vườn của anh hôm nay.">Báo cáo sức khỏe</button>
                                        <button class="ai-agent-design-chip" type="button" data-ai-fill="Khả năng tưới hôm nay có ổn không?">Tưới ngay</button>
                                        <button class="ai-agent-design-chip" type="button" data-ai-fill="Dự báo thu hoạch của khu vườn này giúp anh.">Dự báo thu hoạch</button>
                                        <button class="ai-agent-design-chip" type="button" data-ai-fill="Quét sâu tình trạng hiện tại của khu vườn này.">Quét sâu</button>
                                    </div>
                                <?php endif; ?>

                                <div class="ai-agent-design-input-wrap">
                                    <div class="ai-agent-design-input">
                                        <button class="ai-agent-design-icon" type="button" aria-label="Attach">📎</button>
                                        <textarea data-garden-ai-input placeholder="<?php echo esc_attr($is_ai_onboarding ? 'Ví dụ: em muốn trồng cà chua bi' : 'Hỏi về hệ sinh thái thực vật của bạn...'); ?>" rows="2"></textarea>
                                        <button class="ai-agent-design-icon" type="button" aria-label="Mic">🎤</button>
                                        <button class="ai-agent-design-send" type="submit" data-garden-ai-submit aria-label="<?php echo esc_attr($is_ai_onboarding ? 'Tạo khoang này' : 'Gửi'); ?>">➤</button>
                                    </div>
                                </div>
                                <?php if ($is_ai_onboarding) : ?>
                                    <div class="ai-agent-design-input-wrap" style="margin-top:10px">
                                        <div class="ai-agent-design-input">
                                            <span class="ai-agent-design-icon" aria-hidden="true">📅</span>
                                            <input type="date" data-garden-pot-created-at max="<?php echo esc_attr(wp_date('Y-m-d')); ?>" required aria-label="Ngày khởi tạo khoang">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="ai-agent-design-meta small subtle" data-garden-ai-meta><?php echo esc_html($is_ai_onboarding ? ($has_rack ? 'Khi khởi tạo khoang, anh cần nhập cả tên cây và ngày khởi tạo. Sau đó em sẽ tạo khoang ngay và mở dashboard để mình theo dõi tiếp.' : 'Nếu kho đang hết rack, hệ thống sẽ báo anh chờ đến khi có rack mới.') : (! $is_logged_in ? 'Anh đang dùng chế độ khách. Mình vẫn có thể hỏi AI để chọn cây và định hướng dinh dưỡng trước, rồi đăng nhập sau khi muốn lưu lại hành trình.' : '')); ?></div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="notice">Trang này đang được giữ tối giản trong lần vá ổn định hiện tại.</div>
            <?php endif; ?>
        </main>
<?php if ($use_shared_eco_shell) : ?>
    <?php get_template_part('template-parts/site/eco-shell-end'); ?>
<?php else : ?>
    </div>
</div>
<?php endif; ?>
<script>
var AITR_AJAX_URL = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
var AITR_AJAX_NONCE = <?php echo wp_json_encode(wp_create_nonce('aitrongcay_portal_actions')); ?>;
var AITR_GARDEN_KEY = <?php echo wp_json_encode($garden_key); ?>;

(function () {
  var sessionRail = document.querySelector('.ai-agent-session-rail');
  
  // Toggle sidebar logic
  document.addEventListener('click', function(e) {
    var toggleBtn = e.target.closest('[data-ai-rail-toggle]');
    if (toggleBtn && sessionRail) {
      sessionRail.classList.toggle('is-open');
      return;
    }
    // Close when clicking outside rail on mobile
    if (sessionRail && sessionRail.classList.contains('is-open') && !e.target.closest('.ai-agent-session-rail')) {
      sessionRail.classList.remove('is-open');
    }
  });

  document.addEventListener('click', function (event) {
    var chip = event.target.closest('[data-ai-fill]');
    if (!chip) {
      return;
    }
    var shell = chip.closest('[data-garden-ai-chat]');
    var input = shell ? shell.querySelector('[data-garden-ai-input]') : null;
    if (!input) {
      return;
    }
    input.value = chip.getAttribute('data-ai-fill') || '';
    input.focus();
  });

  var style = document.createElement('style');
  style.textContent = '' +
    '.pot-analysis-card{transition:background .18s ease,border-color .18s ease,box-shadow .18s ease;}' +
    '.pot-analysis-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;font-weight:700;line-height:1.2;}' +
    '.pot-analysis-none{background:#fff;border-color:rgba(15,23,42,.08);}' +
    '.pot-analysis-none .pot-analysis-badge{background:rgba(255,250,241,.92);color:#0f172a;}' +
    '.pot-analysis-xanh-la-dam{background:linear-gradient(180deg,#f2fff5 0%,#ecfbf0 100%);border-color:rgba(47,123,69,.22)!important;box-shadow:0 12px 24px rgba(47,123,69,.08);}' +
    '.pot-analysis-xanh-la-dam .pot-analysis-badge{background:url("' + <?php echo wp_json_encode(get_template_directory_uri() . '/assets/images/ui-badge-green.png'); ?> + '") center/100% 100% no-repeat;color:#173220;}' +
    '.pot-analysis-xanh-non{background:linear-gradient(180deg,#f6fff7 0%,#f1fbf3 100%);border-color:rgba(87,166,104,.24)!important;box-shadow:0 12px 24px rgba(87,166,104,.08);}' +
    '.pot-analysis-xanh-non .pot-analysis-badge{background:url("' + <?php echo wp_json_encode(get_template_directory_uri() . '/assets/images/ui-badge-green.png'); ?> + '") center/100% 100% no-repeat;color:#173220;}' +
    '.pot-analysis-vàng{background:linear-gradient(180deg,#fffdf2 0%,#fff7de 100%);border-color:rgba(214,170,34,.28)!important;box-shadow:0 12px 24px rgba(214,170,34,.10);}' +
    '.pot-analysis-vàng .pot-analysis-badge{background:url("' + <?php echo wp_json_encode(get_template_directory_uri() . '/assets/images/ui-badge-amber.png'); ?> + '") center/100% 100% no-repeat;color:#4c3206;}' +
    '.pot-analysis-cam{background:linear-gradient(180deg,#fff7f1 0%,#ffeddc 100%);border-color:rgba(224,126,52,.30)!important;box-shadow:0 12px 24px rgba(224,126,52,.12);}' +
    '.pot-analysis-cam .pot-analysis-badge{background:url("' + <?php echo wp_json_encode(get_template_directory_uri() . '/assets/images/ui-badge-amber.png'); ?> + '") center/100% 100% no-repeat;color:#4c3206;}' +
    '.pot-analysis-do{background:linear-gradient(180deg,#fff3f2 0%,#ffe5e2 100%);border-color:rgba(194,63,45,.34)!important;box-shadow:0 12px 24px rgba(194,63,45,.12);}' +
    '.pot-analysis-do .pot-analysis-badge{background:url("' + <?php echo wp_json_encode(get_template_directory_uri() . '/assets/images/ui-badge-red.png'); ?> + '") center/100% 100% no-repeat;color:#fff;}' +
    '.pot-inline-rename{margin:6px 0 0;max-width:min(100%,420px);}' +
    '.garden-inline-rename{max-width:min(100%,680px);}' +
    '.garden-inline-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}' +
    '.garden-inline-name-text{margin:0;font-size:clamp(28px,4vw,40px);line-height:1.08;color:#0f172a;}' +
    '.garden-inline-input{min-width:min(100%,420px);max-width:100%;padding:12px 14px;border-radius:14px;border:1px solid rgba(15,23,42,.16);font:inherit;font-size:clamp(22px,3vw,32px);font-weight:700;line-height:1.08;color:#0f172a;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.08);}' +
    '.garden-inline-input:focus{outline:none;border-color:rgba(47,123,69,.55);box-shadow:0 0 0 3px rgba(47,123,69,.12);}' +
    '.garden-inline-name-edit{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:none;border-radius:999px;background:rgba(47,123,69,.12);cursor:pointer;font-size:18px;line-height:1;transition:transform .18s ease,background .18s ease;}' +
    '.garden-inline-name-edit:hover{transform:translateY(-1px);background:rgba(47,123,69,.18);}' +
    '.garden-inline-save-status{font-size:13px;color:#5c6b61;}' +
    '.garden-inline-save-status.is-saving{color:#7a5d16;}' +
    '.garden-inline-save-status.is-success{color:#2f7b45;}' +
    '.garden-inline-save-status.is-error{color:#bb3e2a;}' +
    '.pot-inline-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}' +
    '.pot-inline-name-text{font-size:1.55rem;font-weight:700;line-height:1.15;min-width:0;font-family:"Noto Serif",Georgia,serif;color:#061b0e;}' +
    '.pot-inline-name-edit{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;padding:0;border:0;border-radius:999px;background:rgba(15,23,42,.06);color:inherit;cursor:pointer;font-size:.95rem;opacity:.72;transition:opacity .18s ease, transform .18s ease, background .18s ease;}' +
    '.pot-inline-name-edit:hover,.pot-inline-name-edit:focus-visible{opacity:1;transform:translateY(-1px);background:rgba(47,123,69,.14);outline:none;}' +
    '.pot-inline-input{flex:1 1 220px;min-width:180px;max-width:100%;padding:6px 0;border:0;border-bottom:2px solid rgba(47,123,69,.36);border-radius:0;font:inherit;font-weight:700;font-size:1.5rem;line-height:1.2;color:#0f172a;background:transparent;box-shadow:none;}' +
    '.pot-inline-input:focus{outline:none;border-bottom-color:rgba(47,123,69,.75);box-shadow:none;}' +
    '.pot-inline-save-status{font-size:.82rem;color:#5b6470;white-space:nowrap;}' +
    '.pot-inline-save-status.is-saving{color:#2f7b45;}' +
    '.pot-inline-save-status.is-error{color:#b42318;}' +
    '.pot-inline-save-status.is-success{color:#2f7b45;}' +
    '.share-hero-card{padding:18px 20px 14px;}' +
    '.share-single-card{border:1px solid rgba(15,23,42,.08);border-radius:22px;background:#fff;overflow:hidden;box-shadow:0 14px 30px rgba(15,23,42,.05);}' +
    '.share-single-card-head{padding:4px 4px 12px;margin-bottom:2px;}' +
    '.share-single-card .member-row{margin:0;padding:16px 18px;border-radius:0;background:transparent;border-top:1px solid rgba(15,23,42,.08);box-shadow:none;}' +
    '.share-single-card .member-row:first-of-type{border-top:none;}' +
    '.member-row-owner{background:linear-gradient(180deg,#f8fbf8 0%,#f3f8f4 100%);}' +
    '.share-friend-row{transition:background .18s ease;cursor:pointer;}' +
    '.share-friend-row:hover{background:rgba(15,23,42,.02);}' +
    '.share-friend-row:focus-visible{outline:none;box-shadow:inset 0 0 0 2px rgba(47,123,69,.22);background:rgba(47,123,69,.04);}' +
    '.share-friend-name{color:#0f172a;font-weight:700;font-size:1rem;line-height:1.35;}' +
    '.share-role-badge{min-width:110px;justify-content:center;}' +
    '.share-single-card .social-actions{gap:10px;}' +
    '@media (max-width: 768px){.share-single-card .member-row{padding:14px 14px;}.share-role-badge{min-width:auto;}.garden-inline-input{min-width:100%;font-size:24px;padding:10px;}.pot-inline-input{min-width:100%;font-size:1.3rem;}.garden-inline-name-text{font-size:26px;}.pot-inline-name-text{font-size:1.35rem;}.pot-inline-row{row-gap:10px;}}';
  document.head.appendChild(style);
})();

(function () {
  var btn = document.getElementById('garden-view-mode-toggle');
  if (!btn) return;
  var states = [
    { key: 'private', label: 'Chế độ view riêng tư', icon: '🔒', cls: 'is-private' },
    { key: 'friend', label: 'Friend view', icon: '👥', cls: 'is-friend' },
    { key: 'public', label: 'Public view', icon: '🌍', cls: 'is-public' }
  ];
  var label = btn.querySelector('.view-mode-label');
  var icon = btn.querySelector('.view-mode-icon');
  function render(stateKey) {
    var state = states.find(function (item) { return item.key === stateKey; }) || states[0];
    states.forEach(function (item) { btn.classList.remove(item.cls); });
    btn.classList.add(state.cls);
    btn.setAttribute('data-state', state.key);
    if (label) label.textContent = state.label;
    if (icon) icon.textContent = state.icon;
  }
  btn.onclick = function () {
    var current = btn.getAttribute('data-state') || 'private';
    var idx = states.findIndex(function (item) { return item.key === current; });
    var next = states[(idx + 1) % states.length];
    render(next.key);
  };
  render(btn.getAttribute('data-state') || 'private');
})();

(function () {
  var card = document.getElementById('blynk-live-card');

  var tempEl = card ? card.querySelector('[data-blynk-temp]') : null;
  var humEl = card ? card.querySelector('[data-blynk-hum]') : null;
  var soilEl = card ? card.querySelector('[data-blynk-soil]') : null;
  var statusEl = card ? card.querySelector('[data-blynk-status]') : null;

  function setStatus(text) {
    if (statusEl) statusEl.textContent = text;
  }

  function post(action, extra) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', AITR_AJAX_NONCE);
    Object.keys(extra || {}).forEach(function (k) { body.set(k, String(extra[k])); });
    return fetch(AITR_AJAX_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      credentials: 'same-origin'
    }).then(function (r) {
      return r.text().then(function (text) {
        var data = null;
        try {
          data = text ? JSON.parse(text) : null;
        } catch (e) {
          if (!r.ok) throw new Error('Máy chủ trả về lỗi ' + r.status + '.');
          throw new Error('Phản hồi từ máy chủ không hợp lệ.');
        }
        if (!r.ok && (!data || !data.data || !data.data.message)) {
          throw new Error('Máy chủ trả về lỗi ' + r.status + '.');
        }
        return data;
      });
    }).catch(function (error) {
      if (error && /Failed to fetch/i.test(String(error.message || ''))) {
        throw new Error('Không kết nối được tới máy chủ phân tích.');
      }
      throw error;
    });
  }

  function renderSegmentToggle(btn, isOn, onText, offText) {
    if (!btn) return;
    btn.classList.toggle('is-on', !!isOn);
    btn.classList.toggle('is-off', !isOn);
    btn.setAttribute('data-state', isOn ? '1' : '0');
    btn.setAttribute('aria-pressed', isOn ? 'true' : 'false');
    var statusPill = btn.querySelector('.blynk-light-status-pill');
    var statusText = btn.querySelector('.blynk-light-status-text');
    var actionText = btn.querySelector('.blynk-light-action-text');
    var icon = btn.querySelector('.blynk-light-icon');
    if (statusPill) statusPill.textContent = isOn ? 'Đang bật' : 'Đang tắt';
    if (statusText) statusText.textContent = isOn ? onText : offText;
    if (icon) {
      var iconOn = btn.getAttribute('data-icon-on') || '💡';
      var iconOff = btn.getAttribute('data-icon-off') || iconOn;
      icon.textContent = isOn ? iconOn : iconOff;
    }
    if (actionText) {
      var actionOn = btn.getAttribute('data-action-on') || 'Tắt';
      var actionOff = btn.getAttribute('data-action-off') || 'Bật';
      actionText.textContent = isOn ? actionOn : actionOff;
    }
  }

  function syncSharedClimate(d) {
    var tempText = (d.temp != null ? d.temp.toFixed(1) : '--') + ' °C';
    var humText = (d.hum != null ? d.hum.toFixed(1) : '--') + ' %';
    document.querySelectorAll('[data-pot-temp-chip]').forEach(function (chip) {
      chip.textContent = 'Nhiệt độ: ' + tempText;
    });
    document.querySelectorAll('[data-pot-hum-chip]').forEach(function (chip) {
      chip.textContent = 'Độ ẩm: ' + humText;
    });
  }

  function renderLightToggle(btn, isOn) {
    renderSegmentToggle(btn, isOn, '', '');
    if (!btn) return;
    var device = btn.getAttribute('data-blynk-light-toggle');
    var statusText = '';
    var chip = document.querySelector('[data-light-status-chip="' + device + '"]');
    var live = document.querySelector('[data-pot-live-status="' + device + '"]');
    if (chip) chip.textContent = '💡 ' + (btn.getAttribute('data-light-label') || 'Đèn') + ': ' + (isOn ? 'BẬT' : 'TẮT');
    if (live) live.textContent = statusText;
  }

  function renderPumpToggle(btn, isOn) {
    renderSegmentToggle(btn, isOn, 'Bơm đang bật', 'Bơm đang tắt');
    document.querySelectorAll('[data-pump-status-chip]').forEach(function (chip) {
      chip.textContent = '🫧 Bơm chung: ' + (isOn ? 'BẬT' : 'TẮT');
    });
  }

  function refresh() {
    setStatus('Đang lấy dữ liệu...');
    post('aitrongcay_blynk_get_status', { garden_key: AITR_GARDEN_KEY }).then(function (res) {
      if (!res || !res.success) throw new Error((res && res.data && res.data.message) || 'Lỗi đọc dữ liệu');
      var d = res.data || {};
      if (tempEl) tempEl.textContent = (d.temp != null ? d.temp.toFixed(1) : '--') + ' °C';
      if (humEl) humEl.textContent = (d.hum != null ? d.hum.toFixed(1) : '--') + ' %';
      if (soilEl) soilEl.textContent = (d.soil != null ? d.soil.toFixed(1) : '--') + ' %';
      syncSharedClimate(d);
      ['light1', 'light2', 'light3', 'light4'].forEach(function (key) {
        document.querySelectorAll('[data-blynk-light-toggle="' + key + '"]').forEach(function (btn) {
          renderLightToggle(btn, d[key] === 1);
        });
      });
      renderPumpToggle(document.querySelector('[data-blynk-pump-toggle="pump"]'), d.pump === 1);
      setStatus(
        'Bơm: ' + (d.pump === 1 ? 'BẬT' : 'TẮT') +
        ' • Đèn 1: ' + (d.light1 === 1 ? 'BẬT' : 'TẮT') +
        ' • Đèn 2: ' + (d.light2 === 1 ? 'BẬT' : 'TẮT') +
        ' • Đèn 3: ' + (d.light3 === 1 ? 'BẬT' : 'TẮT') +
        ' • Đèn 4: ' + (d.light4 === 1 ? 'BẬT' : 'TẮT')
      );
    }).catch(function () {
      setStatus('Không có tín hiệu từ thiết bị trong vườn');
    });
  }

  function handlePumpToggle(pumpBtn, event) {
    if (!pumpBtn) return false;
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    var pumpDevice = pumpBtn.getAttribute('data-blynk-pump-toggle');
    var pumpCurrentState = pumpBtn.getAttribute('data-state') === '1';
    var pumpNextState = pumpCurrentState ? '0' : '1';
    var potCode = pumpBtn.getAttribute('data-pot-code') || '';
    setStatus('Đang gửi lệnh...');
    post('aitrongcay_blynk_control', { garden_key: AITR_GARDEN_KEY, device: pumpDevice, state: pumpNextState, pot_code: potCode }).then(function (res) {
      if (!res || !res.success) throw new Error();
      renderPumpToggle(pumpBtn, pumpNextState === '1');
      refresh();
    }).catch(function () {
      setStatus('Gửi lệnh thất bại');
    });
    return false;
  }

  function handleLightToggle(lightBtn, event) {
    if (!lightBtn) return false;
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    var lightDevice = lightBtn.getAttribute('data-blynk-light-toggle');
    var lightCurrentState = lightBtn.getAttribute('data-state') === '1';
    var lightNextState = lightCurrentState ? '0' : '1';
    var potCode = lightBtn.getAttribute('data-pot-code') || '';
    setStatus('Đang gửi lệnh...');
    post('aitrongcay_blynk_control', { garden_key: AITR_GARDEN_KEY, device: lightDevice, state: lightNextState, pot_code: potCode }).then(function (res) {
      if (!res || !res.success) throw new Error();
      renderLightToggle(lightBtn, lightNextState === '1');
      refresh();
    }).catch(function () {
      setStatus('Gửi lệnh thất bại');
    });
    return false;
  }

  window.aitrLightToggle = handleLightToggle;

  document.addEventListener('click', function (event) {
    var pumpBtn = event.target.closest('[data-blynk-pump-toggle]');
    if (pumpBtn) {
      handlePumpToggle(pumpBtn, event);
      return;
    }

    var lightBtn = event.target.closest('[data-blynk-light-toggle]');
    if (lightBtn) {
      handleLightToggle(lightBtn, event);
    }
  });

  var refreshBtn = card ? card.querySelector('[data-blynk-refresh]') : null;
  if (refreshBtn) refreshBtn.addEventListener('click', refresh);

  refresh();
  setInterval(function () {
    if (document.visibilityState === 'visible') {
      refresh();
    }
  }, 300000);
})();

(function () {
  var switcher = document.querySelector('[data-garden-switcher]');
  if (switcher) {
    switcher.addEventListener('change', function () {
      var nextGarden = switcher.value || '';
      var url = new URL(window.location.href);
      if (nextGarden) {
        url.searchParams.set('garden', nextGarden);
      } else {
        url.searchParams.delete('garden');
      }
      window.location.href = url.toString();
    });
  }

  function post(action, extra) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', AITR_AJAX_NONCE);
    Object.keys(extra || {}).forEach(function (k) { body.set(k, String(extra[k])); });
    return fetch(AITR_AJAX_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); });
  }

  var friendTarget = document.querySelector('[data-friend-target]');
  var friendMessage = document.querySelector('[data-friend-message]');
  var sendFriendBtn = document.querySelector('[data-send-friend-request]');
  if (sendFriendBtn && friendTarget) {
    function syncFriendRequestState() {
      var hasValue = !!(friendTarget.value || '').trim();
      sendFriendBtn.disabled = !hasValue;
      sendFriendBtn.setAttribute('aria-disabled', hasValue ? 'false' : 'true');
      if (friendMessage && !hasValue) friendMessage.textContent = 'Nhập email hoặc username để mở nút gửi lời mời kết nối.';
    }
    syncFriendRequestState();
    friendTarget.addEventListener('input', syncFriendRequestState);
    sendFriendBtn.addEventListener('click', function () {
      if (!((friendTarget.value || '').trim())) {
        if (friendMessage) friendMessage.textContent = 'Anh cần nhập email hoặc username trước khi gửi.';
        syncFriendRequestState();
        return;
      }
      if (friendMessage) friendMessage.textContent = 'Đang gửi lời mời kết nối...';
      post('aitrongcay_send_friend_request', { target: friendTarget.value || '' }).then(function (res) {
        if (friendMessage) friendMessage.textContent = res && res.success ? 'Đã gửi lời mời. Khi người kia chấp nhận, họ sẽ xuất hiện ngay ở danh sách hàng xóm.' : ((res && res.data && res.data.message) || 'Chưa gửi được lời mời. Anh thử lại giúp em.');
      }).catch(function () {
        if (friendMessage) friendMessage.textContent = 'Chưa gửi được lời mời. Anh thử lại giúp em.';
      });
    });
  }

  document.querySelectorAll('[data-send-friend-request-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = btn.getAttribute('data-send-friend-request-target') || '';
      if (!target) return;
      if (friendTarget) {
        friendTarget.value = target;
        if (typeof syncFriendRequestState === 'function') syncFriendRequestState();
      }
      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
      btn.textContent = 'Đang gửi...';
      if (friendMessage) friendMessage.textContent = 'Đang gửi lời mời kết nối đến ' + target + '...';
      post('aitrongcay_send_friend_request', { target: target }).then(function (res) {
        if (res && res.success) {
          btn.textContent = 'Đã gửi lời mời';
          btn.classList.remove('btn-primary');
          btn.classList.add('btn-secondary');
          if (friendMessage) friendMessage.textContent = 'Đã gửi lời mời. Khi người kia chấp nhận, họ sẽ xuất hiện ngay ở danh sách hàng xóm.';
          return;
        }
        btn.disabled = false;
        btn.setAttribute('aria-disabled', 'false');
        btn.textContent = 'Kết nối';
        if (friendMessage) friendMessage.textContent = (res && res.data && res.data.message) || 'Chưa gửi được lời mời. Anh thử lại giúp em.';
      }).catch(function () {
        btn.disabled = false;
        btn.setAttribute('aria-disabled', 'false');
        btn.textContent = 'Kết nối';
        if (friendMessage) friendMessage.textContent = 'Chưa gửi được lời mời. Anh thử lại giúp em.';
      });
    });
  });

  document.querySelectorAll('[data-accept-friendship]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('aitrongcay_accept_friend_request', { friendship_id: btn.getAttribute('data-accept-friendship') || '' }).then(function () {
        window.location.reload();
      }).catch(function () {});
    });
  });

  document.querySelectorAll('[data-reject-friendship]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('aitrongcay_reject_friend_request', { friendship_id: btn.getAttribute('data-reject-friendship') || '' }).then(function () {
        window.location.reload();
      }).catch(function () {});
    });
  });

  document.querySelectorAll('[data-accept-garden-invite]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('aitrongcay_accept_garden_invite', { membership_id: btn.getAttribute('data-accept-garden-invite') || '' }).then(function (res) {
        if (res && res.success && res.data && res.data.garden_key) {
          window.location.href = '/portal/dashboard-2/?garden=' + encodeURIComponent(res.data.garden_key);
          return;
        }
        window.location.reload();
      }).catch(function () {});
    });
  });

  document.querySelectorAll('[data-decline-garden-invite]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      post('aitrongcay_decline_garden_invite', { membership_id: btn.getAttribute('data-decline-garden-invite') || '' }).then(function () {
        window.location.reload();
      }).catch(function () {});
    });
  });

  document.querySelectorAll('[data-friend-role-toggle]').forEach(function (row) {
    function toggleFriendRole() {
      var membershipId = row.getAttribute('data-membership-id') || '';
      var userId = row.getAttribute('data-user-id') || '';
      var nextRole = row.getAttribute('data-next-role') || 'viewer';
      var action = membershipId ? 'aitrongcay_update_garden_member_role' : 'aitrongcay_invite_garden_member';
      var payload = membershipId
        ? { membership_id: membershipId, role: nextRole }
        : { garden_key: AITR_GARDEN_KEY, user_id: userId, role: nextRole };
      post(action, payload).then(function (res) {
        if (res && res.success) {
          window.location.reload();
          return;
        }
        window.alert((res && res.data && res.data.message) || 'Chưa cập nhật được quyền share vườn.');
      }).catch(function () {
        window.alert('Chưa cập nhật được quyền share vườn.');
      });
    }
    row.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      toggleFriendRole();
    });
    row.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        event.stopPropagation();
        toggleFriendRole();
      }
    });
  });

  document.querySelectorAll('[data-remove-friend]').forEach(function (btn) {
    btn.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      var friendUserId = btn.getAttribute('data-remove-friend') || '';
      if (!friendUserId) return;
      if (!window.confirm('Anh có chắc muốn hủy kết nối hàng xóm không?')) return;
      post('aitrongcay_remove_friend', { friend_user_id: friendUserId }).then(function (res) {
        if (res && res.success) {
          window.location.reload();
          return;
        }
        window.alert((res && res.data && res.data.message) || 'Chưa hủy được kết nối hàng xóm.');
      }).catch(function () {
        window.alert('Chưa hủy được kết nối hàng xóm.');
      });
    });
  });
})();

(function () {
  function post(action, extra) {
    var body = new URLSearchParams();
    body.set('action', action);
    body.set('nonce', AITR_AJAX_NONCE);
    Object.keys(extra || {}).forEach(function (k) { body.set(k, String(extra[k])); });
    return fetch(AITR_AJAX_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); });
  }

  var gardenWrap = document.querySelector('[data-garden-inline-name]');
  if (gardenWrap) {
    var gardenEditButton = gardenWrap.querySelector('[data-garden-inline-edit]');
    var gardenInput = gardenWrap.querySelector('[data-garden-inline-input]');
    var gardenStatus = gardenWrap.querySelector('[data-garden-inline-status]');
    var gardenText = gardenWrap.querySelector('.garden-inline-name-text');
    var gardenOriginalName = (gardenWrap.getAttribute('data-garden-name') || gardenText.textContent || gardenInput.value || 'Khu vườn của bạn').trim();
    var gardenIsEditing = false;
    var gardenIsSaving = false;

    if (gardenEditButton && gardenInput && gardenStatus && gardenText) {
      function setGardenStatus(message, className, show) {
        gardenStatus.textContent = message || '';
        gardenStatus.className = 'garden-inline-save-status';
        if (className) gardenStatus.classList.add(className);
        gardenStatus.hidden = !show;
      }

      function openGardenEditor() {
        if (gardenIsSaving) return;
        gardenIsEditing = true;
        gardenText.hidden = true;
        gardenInput.hidden = false;
        gardenEditButton.hidden = true;
        gardenInput.value = gardenOriginalName;
        setGardenStatus('Nhấn Enter hoặc click ra ngoài để lưu', '', true);
        window.requestAnimationFrame(function () {
          gardenInput.focus();
          gardenInput.select();
        });
      }

      function closeGardenEditor(keepStatus) {
        gardenIsEditing = false;
        gardenText.hidden = false;
        gardenInput.hidden = true;
        gardenEditButton.hidden = false;
        gardenInput.value = gardenOriginalName;
        if (!keepStatus) gardenStatus.hidden = true;
      }

      function applyGardenName(nextName) {
        gardenOriginalName = nextName;
        gardenWrap.setAttribute('data-garden-name', nextName);
        gardenText.textContent = nextName;
        document.querySelectorAll('[data-garden-display-name]').forEach(function (node) {
          node.textContent = nextName;
        });
      }

      function saveGardenName(nextName) {
        nextName = (nextName || '').trim().replace(/\s+/g, ' ');
        if (!nextName) {
          nextName = gardenOriginalName || 'Khu vườn của bạn';
        }
        if (nextName === gardenOriginalName) {
          closeGardenEditor();
          return;
        }
        if (gardenIsSaving) return;
        gardenIsSaving = true;
        setGardenStatus('Đang lưu tên mới...', 'is-saving', true);
        post('aitrongcay_rename_garden', {
          garden_key: AITR_GARDEN_KEY,
          garden_name: nextName
        }).then(function (res) {
          if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Lưu thất bại');
          applyGardenName(res.data.garden_name || nextName);
          setGardenStatus('Đã lưu tự động.', 'is-success', true);
          closeGardenEditor(true);
          window.setTimeout(function () {
            if (!gardenIsEditing && !gardenIsSaving) gardenStatus.hidden = true;
          }, 1600);
        }).catch(function (err) {
          setGardenStatus((err && err.message) || 'Chưa lưu được tên khu vườn.', 'is-error', true);
          window.requestAnimationFrame(function () {
            gardenInput.focus();
            gardenInput.select();
          });
        }).finally(function () {
          gardenIsSaving = false;
        });
      }

      gardenEditButton.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        openGardenEditor();
      });

      gardenInput.addEventListener('click', function (event) {
        event.stopPropagation();
      });

      gardenInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
          event.preventDefault();
          saveGardenName(gardenInput.value);
        } else if (event.key === 'Escape') {
          event.preventDefault();
          gardenInput.value = gardenOriginalName;
          closeGardenEditor();
        }
      });

      gardenInput.addEventListener('blur', function () {
        saveGardenName(gardenInput.value);
      });
    }
  }

  document.querySelectorAll('[data-pot-inline-name]').forEach(function (wrap) {
    var editButton = wrap.querySelector('[data-pot-inline-edit]');
    var input = wrap.querySelector('[data-pot-inline-input]');
    var status = wrap.querySelector('[data-pot-inline-status]');
    var text = wrap.querySelector('.pot-inline-name-text');
    if (!editButton || !input || !status || !text) return;

    var originalName = (wrap.getAttribute('data-pot-name') || '').trim();
    var potCode = wrap.getAttribute('data-pot-code') || '';
    var isSaving = false;
    var isEditing = false;

    function setStatus(textValue, cls, keepVisible) {
      status.textContent = textValue;
      status.classList.remove('is-saving', 'is-error', 'is-success');
      if (cls) status.classList.add(cls);
      status.hidden = !keepVisible && !textValue;
    }

    function closeEditor(keepStatus) {
      isEditing = false;
      input.hidden = true;
      text.hidden = false;
      editButton.hidden = false;
      if (!keepStatus) {
        status.hidden = true;
        setStatus('', '', false);
      }
    }

    function openEditor() {
      isEditing = true;
      input.hidden = false;
      text.hidden = true;
      editButton.hidden = true;
      input.value = originalName;
      setStatus('Nhấn Enter hoặc click ra ngoài để lưu', '', true);
      window.requestAnimationFrame(function () {
        input.focus();
        input.select();
      });
    }

    function applyName(nextName) {
      originalName = nextName;
      wrap.setAttribute('data-pot-name', nextName);
      text.textContent = nextName;
      var img = wrap.closest('.pot-row-summary');
      if (img) {
        var media = img.querySelector('.pot-row-media img');
        if (media) media.alt = nextName;
      }
    }

    function saveName(nextName) {
      nextName = (nextName || '').trim().replace(/\s+/g, ' ');
      if (!nextName) {
        setStatus('Tên khoang không được để trống.', 'is-error', true);
        input.value = originalName;
        window.requestAnimationFrame(function () {
          input.focus();
          input.select();
        });
        return;
      }
      if (nextName === originalName) {
        closeEditor();
        return;
      }
      if (isSaving) return;
      isSaving = true;
      setStatus('Đang lưu tên mới...', 'is-saving', true);
      post('aitrongcay_rename_pot', {
        garden_key: AITR_GARDEN_KEY,
        pot_code: potCode,
        pot_name: nextName
      }).then(function (res) {
        if (!res || !res.success || !res.data) throw new Error((res && res.data && res.data.message) || 'Lưu thất bại');
        applyName(res.data.pot_name || nextName);
        setStatus('Đã lưu tự động.', 'is-success', true);
        closeEditor(true);
        window.setTimeout(function () {
          if (!isEditing && !isSaving) {
            status.hidden = true;
          }
        }, 1600);
      }).catch(function (err) {
        setStatus((err && err.message) || 'Chưa lưu được tên khoang.', 'is-error', true);
        window.requestAnimationFrame(function () {
          input.focus();
          input.select();
        });
      }).finally(function () {
        isSaving = false;
      });
    }

    editButton.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopPropagation();
      openEditor();
    });

    input.addEventListener('click', function (event) {
      event.stopPropagation();
    });

    input.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        saveName(input.value);
      } else if (event.key === 'Escape') {
        event.preventDefault();
        input.value = originalName;
        closeEditor();
      }
    });

    input.addEventListener('blur', function () {
      saveName(input.value);
    });
  });
})();

(function () {
  document.querySelectorAll('[data-analyze-latest-photo]').forEach(function (button) {
    button.addEventListener('click', function () {
      var potCode = button.getAttribute('data-pot-code') || '';
      var card = document.querySelector('[data-pot-analysis-card="' + potCode + '"]');
      var badge = card ? card.querySelector('[data-pot-analysis-badge]') : null;
      var stage = card ? card.querySelector('[data-pot-analysis-stage]') : null;
      var summary = card ? card.querySelector('[data-pot-analysis-summary]') : null;
      var actions = card ? card.querySelector('[data-pot-analysis-actions]') : null;
      var escalate = card ? card.querySelector('[data-pot-analysis-escalate]') : null;
      var knowledge = card ? card.querySelector('[data-pot-analysis-knowledge]') : null;
      var originalBtnHtml = button.innerHTML;
      button.disabled = true;
      button.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;margin-right:4px;">⏳</span> Đang nhờ Cindy phân tích...';
      if (card) card.classList.add('is-analyzing');
      
      if (summary) summary.innerHTML = '<div style="display:flex;align-items:center;gap:8px;color:#0369a1;font-weight:500;padding:12px 0;"><span style="animation:pulse 1.5s ease-in-out infinite;">✨</span> Trợ lý Cindy đang soi từng chiếc lá...</div>';
      if (actions) actions.innerHTML = '';
      if (escalate) escalate.innerHTML = '';
      if (knowledge) knowledge.innerHTML = '';

      var body = new URLSearchParams();
      body.set('action', 'aitrongcay_analyze_latest_pot_photo');
      body.set('nonce', AITR_AJAX_NONCE);
      body.set('garden_key', AITR_GARDEN_KEY);
      body.set('pot_code', potCode);
      fetch(AITR_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString(),
        credentials: 'same-origin'
      }).then(function (response) {
        return response.json();
      }).then(function (json) {
        if (!json || !json.success || !json.data || !json.data.analysis) {
          throw new Error((json && json.data && json.data.message) || 'Chưa phân tích được ảnh mới nhất.');
        }
        var analysis = json.data.analysis;
        if (card) {
          card.classList.remove('is-analyzing', 'pot-analysis-none','pot-analysis-xanh-la-dam','pot-analysis-xanh-non','pot-analysis-vàng','pot-analysis-cam','pot-analysis-do', 'pot-analysis-xam');
          card.classList.add('pot-analysis-' + (analysis.color || 'none'));
        }
        if (badge) badge.textContent = 'Bậc ' + analysis.level + ' — ' + analysis.label;
        if (stage) stage.textContent = (analysis.current_stage || 'Đang chờ xác định rõ giai đoạn');
        if (summary) summary.textContent = analysis.summary || '';
        if (actions) actions.innerHTML = '<strong>Người dùng nên làm gì:</strong> ' + (((analysis.actions || []).join(' · ')) || 'Đã cập nhật khuyến nghị chăm sóc.');
        if (escalate) escalate.innerHTML = '<strong>Khi nào cần lo hơn:</strong> ' + (((analysis.escalate_if || []).join(' · ')) || 'Nếu dấu hiệu lan rộng hơn hoặc chạm lá non, nên nâng mức cảnh báo.');
        if (knowledge) knowledge.innerHTML = '<strong>Căn cứ phân tích:</strong> ' + (analysis.knowledge_note || 'Đối chiếu ảnh mới nhất với giai đoạn sinh trưởng và diễn biến gần nhất của chậu.');
      }).catch(function (error) {
        if (summary) summary.textContent = error && error.message ? error.message : 'Chưa phân tích được ảnh mới nhất.';
        if (card) card.classList.remove('is-analyzing');
      }).finally(function () {
        button.disabled = false;
        button.innerHTML = originalBtnHtml;
      });
    });
  });
})();

(function () {
  document.querySelectorAll('[data-pot-photo-upload-form]').forEach(function (form) {
    var fileInput = form.querySelector('input[type="file"][name="photo"]');
    var potCodeInput = form.querySelector('input[name="pot_code"]');
    var status = form.querySelector('[data-pot-photo-upload-status]');

    function setStatus(text, tone) {
      if (!status) return;
      status.textContent = text;
      status.style.color = tone === 'error' ? '#b42318' : (tone === 'success' ? '#2f7b45' : '#5b6470');
    }

    if (fileInput) {
      fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length) {
          form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
      });
    }

    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (!fileInput || !fileInput.files || !fileInput.files.length) {
        setStatus('Anh chưa chọn ảnh để upload.', 'error');
        return;
      }
      var potCode = (potCodeInput && potCodeInput.value) ? potCodeInput.value : '';
      var body = new FormData();
      body.append('action', 'aitrongcay_upload_photo_attachment');
      body.append('nonce', AITR_AJAX_NONCE);
      body.append('garden_key', (form.querySelector('input[name="garden_key"]') || {}).value || AITR_GARDEN_KEY || '');
      body.append('pot_code', potCode);
      body.append('pot_name', (form.querySelector('input[name="pot_name"]') || {}).value || '');
      body.append('photo', fileInput.files[0]);

      setStatus('Đang upload ảnh mới...', 'info');
      fetch(AITR_AJAX_URL, {
        method: 'POST',
        body: body,
        credentials: 'same-origin'
      }).then(function (response) {
        return response.json();
      }).then(function (json) {
        if (!json || !json.success) {
          throw new Error((json && json.data && json.data.message) || 'Upload ảnh chưa thành công.');
        }
        var data = json.data || {};
        var preview = document.querySelector('[data-latest-pot-photo-preview="' + potCode + '"]');
        var previewLabel = document.querySelector('[data-latest-pot-photo-label="' + potCode + '"]');
        var emptyState = document.querySelector('[data-latest-pot-photo-empty="' + potCode + '"]');
        if (preview) {
          preview.src = data.url || preview.src;
          preview.classList.remove('is-portrait-rotated');
          if (data.orientationClass) preview.classList.add(data.orientationClass);
        } else if (emptyState && emptyState.parentNode) {
          emptyState.outerHTML = '<div class="card-media media-frame media-frame-16x9" style="margin-top:10px"><img class="media-thumb media-fit-cover ' + (data.orientationClass || '') + '" src="' + (data.url || '') + '" alt="' + potCode + '" data-latest-pot-photo-preview="' + potCode + '"></div>';
        }
        if (previewLabel) {
          previewLabel.textContent = data.title || ('Ảnh mới nhất của ' + potCode);
        } else {
          var latestCard = form.closest('.grid-2');
          if (latestCard) {
            var firstCard = latestCard.querySelector('.card.soft-card');
            if (firstCard) {
              var p = document.createElement('p');
              p.className = 'small subtle';
              p.style.margin = '10px 0 0';
              p.setAttribute('data-latest-pot-photo-label', potCode);
              p.textContent = data.title || ('Ảnh mới nhất của ' + potCode);
              firstCard.appendChild(p);
            }
          }
        }
        setStatus('Đã upload xong. Ảnh mới nhất của chậu đã được cập nhật.', 'success');
        fileInput.value = '';
        window.setTimeout(function () { window.location.reload(); }, 800);
      }).catch(function (error) {
        setStatus('Đang chuyển sang chế độ upload ổn định...', 'info');
        window.setTimeout(function () { try { form.submit(); } catch (e) { setStatus(error && error.message ? error.message : 'Upload ảnh chưa thành công.', 'error'); } }, 60);
      });
    });
  });
})();

(function () {
  var popup = document.querySelector('[data-tool-popup]');
  if (!popup) return;

  var image = popup.querySelector('[data-tool-popup-image]');
  var name = popup.querySelector('[data-tool-popup-name]');
  var type = popup.querySelector('[data-tool-popup-type]');
  var desc = popup.querySelector('[data-tool-popup-desc]');

  function openPopup(data) {
    if (image) { image.src = data.image || ''; image.alt = data.name || ''; }
    if (name) name.textContent = data.name || '';
    if (type) type.textContent = data.type || '';
    if (desc) desc.textContent = data.desc || '';
    popup.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closePopup() {
    popup.hidden = true;
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-tool-popup-open]').forEach(function (el) {
    el.addEventListener('click', function (event) {
      event.preventDefault();
      openPopup({
        image: el.getAttribute('data-tool-image') || '',
        name: el.getAttribute('data-tool-name') || '',
        type: el.getAttribute('data-tool-type') || '',
        desc: el.getAttribute('data-tool-desc') || ''
      });
    });
  });

  popup.querySelectorAll('[data-tool-popup-close]').forEach(function (el) {
    el.addEventListener('click', closePopup);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !popup.hidden) closePopup();
  });
})();

(function () {
  document.querySelectorAll('[data-delete-photo]').forEach(function (button) {
    button.addEventListener('click', function () {
      var attachmentId = button.getAttribute('data-delete-photo') || '';
      var card = button.closest('[data-photo-card]');
      if (!attachmentId) return;
      if (!window.confirm('Xóa ảnh này khỏi kho ảnh?')) return;
      button.disabled = true;

      var body = new URLSearchParams();
      body.set('action', 'aitrongcay_delete_photo_attachment');
      body.set('nonce', AITR_AJAX_NONCE);
      body.set('attachment_id', attachmentId);

      fetch(AITR_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString(),
        credentials: 'same-origin'
      }).then(function (response) {
        return response.json();
      }).then(function (json) {
        if (!json || !json.success) {
          throw new Error((json && json.data && json.data.message) || 'Chưa xóa được ảnh.');
        }
        if (card) {
          card.remove();
        }
      }).catch(function (error) {
        window.alert(error && error.message ? error.message : 'Chưa xóa được ảnh.');
        button.disabled = false;
      });
    });
  });
})();
</script>
