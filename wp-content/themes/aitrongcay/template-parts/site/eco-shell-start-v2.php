<?php
if (! defined('ABSPATH')) { exit; }
$eco = get_query_var('aitr_eco_shell', []);
$nav = $eco['nav'] ?? [];
$title = (string) ($eco['title'] ?? 'Eco-Tech Marketplace');
$active = (string) ($eco['active'] ?? '');
$sideTitle = (string) ($eco['side_title'] ?? 'Ai trồng cây');
$sideSubtitle = (string) ($eco['side_subtitle'] ?? '');
$sideBadge = (string) ($eco['side_badge'] ?? '🍃');
$search = is_array($eco['search'] ?? null) ? $eco['search'] : [];
$garden_query_value = isset($_GET['garden']) ? sanitize_text_field((string) wp_unslash($_GET['garden'])) : '';
$active_garden_key = $garden_query_value !== '' ? $garden_query_value : (function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key() : '');
$use_brand_title = in_array($active, ['cho-que', 'kho-nong-cu', 'hang-xom'], true);
$shared_top_links = [
    ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
    ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
    ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
];
foreach ($shared_top_links as &$shared_top_link) {
    if ($active_garden_key !== '') {
        $shared_top_link['url'] = add_query_arg('garden', $active_garden_key, $shared_top_link['url']);
    }
}
unset($shared_top_link);
$brand_home_url = $active_garden_key !== '' ? add_query_arg('garden', $active_garden_key, home_url('/portal/dashboard-2/')) : home_url('/portal/dashboard-2/');
$profile_links = is_user_logged_in()
    ? [
        ['label' => 'Quản lý tài khoản', 'url' => home_url('/tai-khoan/')],
        ['label' => 'Đăng xuất', 'url' => aitrongcay_logout_url(), 'danger' => true],
    ]
    : [
        ['label' => 'Đăng nhập', 'url' => home_url('/dang-nhap/')],
        ['label' => 'Tạo tài khoản', 'url' => home_url('/onboarding/')],
    ];

$header_avatar_html = '👤';
if (is_user_logged_in()) {
    $current_user_header = wp_get_current_user();
    $header_avatar_id = (int) get_user_meta($current_user_header->ID, 'aitrongcay_avatar_id', true);
    $header_avatar_url = $header_avatar_id ? (wp_get_attachment_image_url($header_avatar_id, 'thumbnail') ?: wp_get_attachment_url($header_avatar_id)) : '';
    if ($header_avatar_url) {
        $header_avatar_html = '<img src="' . esc_url($header_avatar_url) . '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;margin:0;padding:0;">';
    } else {
        $header_avatar_html = esc_html(mb_strtoupper(mb_substr($current_user_header->display_name ?: $current_user_header->user_login, 0, 1)));
    }
}
?>
<style>
.site-header,.account-menu{display:none !important}
.eco-shell{min-height:100vh;background:#121411;color:#e3e3de;font-family:'Manrope',sans-serif}
.eco-top{position:sticky;top:0;z-index:50;display:flex;justify-content:space-between;align-items:center;gap:18px;padding:18px 28px;background:linear-gradient(180deg, rgba(7,33,24,.82), rgba(7,33,24,.34));backdrop-filter:blur(24px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
.eco-top-left{display:flex;align-items:center;gap:22px;min-width:0;flex:1}.eco-top-title{font-family:'Noto Serif',serif;font-size:34px;font-style:italic;color:var(--primary);letter-spacing:-.03em;white-space:nowrap}.eco-top-title a{color:inherit}.eco-top-title-brand{display:inline-flex;align-items:baseline;gap:8px;font-style:normal;letter-spacing:-.03em}.eco-top-title-brand .brand-ai{color:#6fdba8;font-weight:900}.eco-top-title-brand .brand-rest{color:#ffffff;font-weight:700}
.eco-top-links{display:flex;align-items:center;gap:18px;flex-wrap:nowrap;min-width:0;margin-left:auto;overflow:auto;scrollbar-width:none}.eco-top-links::-webkit-scrollbar{display:none}.eco-top-links a{color:#e3e3de;transition:color .18s ease;white-space:nowrap}
.eco-top-links a:hover{color:#6fdba8}
.eco-top-search{display:flex;align-items:center;gap:10px;background:rgba(41,43,39,.72);border-radius:18px;padding:12px 16px;min-width:320px;border:1px solid rgba(111,219,168,.12)}.eco-top-search input{background:transparent;border:none;outline:none;width:100%;color:#e3e3de}
.eco-top-right{display:flex;flex:0 0 auto;align-items:center;justify-content:center;gap:10px;padding:8px 10px;border-radius:20px;border:1px solid rgba(111,219,168,.14);background:rgba(18,20,17,.58);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 14px 30px rgba(0,0,0,.14)}.eco-top-avatar{width:42px;height:42px;border-radius:999px;display:grid;place-items:center;border:2px solid rgba(111,219,168,.3);background:#1a1c19;overflow:hidden;color:#fff;font-weight:bold;padding:0;box-sizing:border-box}
.eco-profile-trigger{cursor:pointer}.eco-profile-popup{position:absolute;top:74px;right:28px;min-width:240px;background:rgba(26,28,25,.96);border:1px solid rgba(255,255,255,.06);border-radius:22px;padding:10px;box-shadow:0 24px 52px rgba(0,0,0,.28);z-index:70}.eco-profile-popup[hidden]{display:none}.eco-profile-popup a{display:block;padding:12px 14px;border-radius:14px;color:#e3e3de}.eco-profile-popup a:hover{background:rgba(51,53,50,.56)}.eco-profile-popup a.is-danger{color:#ffb4ab}
.eco-layout{display:grid;grid-template-columns:240px minmax(0,1fr);gap:28px;padding:24px 28px 36px}.eco-side{position:sticky;top:100px;align-self:start;background:rgba(7,33,24,.58);backdrop-filter:blur(24px);border-radius:30px;padding:24px 0;box-shadow:10px 0 30px rgba(0,0,0,.2)}
.eco-side-head{padding:0 24px 22px;display:flex;align-items:center;gap:12px}.eco-side-badge{width:48px;height:48px;border-radius:18px;background:linear-gradient(135deg,#31a375,#6fdba8);display:grid;place-items:center;color:#062013}.eco-side-head h3{margin:0;font-size:14px;color:var(--primary);font-weight:800}.eco-side-head p{margin:4px 0 0;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:rgba(227,227,222,.58)}
.eco-side nav{display:flex;flex-direction:column;gap:2px;margin-top:18px}.eco-side nav a{display:flex;align-items:center;gap:14px;padding:14px 24px;color:rgba(227,227,222,.62);transition:.2s}.eco-side-link-icon{flex:0 0 auto;font-size:16px;line-height:1}.eco-side-link-short{display:none}.eco-side nav a.active{background:linear-gradient(90deg,#31a375,#6fdba8);color:#062013;border-radius:0 999px 999px 0;font-weight:900}.eco-side nav a:not(.active):hover{transform:translateX(6px);color:var(--primary)}
.eco-main{min-width:0;max-width:1040px;margin:0 auto}.eco-hero{margin-bottom:24px}.eco-hero h1{margin:0 0 10px;font-family:'Noto Serif',serif;font-size:60px;line-height:1.03;color:var(--primary);font-style:italic;text-shadow:0 0 15px rgba(111,219,168,.25)}.eco-hero p{max-width:860px;color:rgba(189,202,192,.78);font-size:18px;line-height:1.8}
.eco-card{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);border-radius:32px;padding:28px;box-shadow:0 24px 52px rgba(0,0,0,.2);border:1px solid rgba(255,255,255,.05)}
.eco-kicker{display:inline-flex;padding:6px 10px;border-radius:999px;background:rgba(111,219,168,.08);color:var(--primary);font-size:10px;letter-spacing:.12em;text-transform:uppercase;font-weight:800;margin-bottom:14px}
.eco-top-right{display:flex;flex:0 0 auto;align-items:center;justify-content:center;gap:12px;padding:6px 12px;border-radius:28px;border:1px solid rgba(111,219,168,.14);background:rgba(18,20,17,.58);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 14px 30px rgba(0,0,0,.14)}
.eco-top-bell, .eco-top-settings {background: #252824; border: 1px solid rgba(255,255,255,0.05); color: #9da89f; width: 42px; height: 42px; border-radius: 50%; display: grid; place-items: center; cursor: pointer; position: relative; transition: .2s;}
.eco-top-bell:hover, .eco-top-settings:hover { background: #323531; color: #fff; }
.eco-top-bell[data-has-new="true"] { color: #f5a623; }
.eco-bell-dot { position: absolute; top: 8px; right: 10px; width: 8px; height: 8px; border-radius: 50%; background: #ff4757; display: none; box-shadow: 0 0 0 2px #121411; }
.eco-top-bell[data-has-new="true"] .eco-bell-dot { display: block; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite; }
@keyframes ping { 0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7); } 70% { transform: scale(1.5); box-shadow: 0 0 0 6px rgba(255, 71, 87, 0); } 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 71, 87, 0); } }
.eco-noti-popup { position: absolute; top: 74px; right: 80px; width: 340px; background: rgba(26,28,25,.96); border: 1px solid rgba(255,255,255,.06); border-radius: 22px; padding: 0; box-shadow: 0 24px 52px rgba(0,0,0,.4); z-index: 70; overflow: hidden; display: flex; flex-direction: column; max-height: 400px; }
.eco-noti-popup[hidden] { display: none; }
.eco-noti-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,.05); }
.eco-noti-header h4 { margin: 0; font-size: 16px; color: #fff; }
.eco-noti-list { overflow-y: auto; flex: 1; padding: 8px; }
.eco-noti-list::-webkit-scrollbar { width: 4px; }
.eco-noti-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.eco-noti-item { padding: 12px; border-radius: 12px; transition: .2s; margin-bottom: 4px; cursor: pointer; text-decoration: none; display: block; color: #e3e3de; }
.eco-noti-item:hover { background: rgba(51,53,50,.56); }
.eco-noti-item.unread { background: rgba(111,219,168,.08); border-left: 3px solid #6fdba8; }
.eco-noti-title { font-weight: bold; font-size: 14px; margin-bottom: 4px; color: #fff; }
.eco-noti-body { font-size: 12px; color: #a9b5ab; line-height: 1.4; }
.eco-noti-time { font-size: 11px; color: #7a827b; margin-top: 6px; }
@media (max-width:1100px){.eco-layout{grid-template-columns:1fr;padding:18px}.eco-side{position:static}}
@media (max-width:820px){.eco-shell{padding-bottom:calc(104px + env(safe-area-inset-bottom,0px))}.eco-top-left{display:contents}.eco-top{padding:14px 16px;gap:12px;flex-wrap:wrap;justify-content:space-between;align-items:flex-start}.eco-top-title{font-size:28px;flex:1 1 0;min-width:0;order:1}.eco-top-right{flex:0 0 auto;padding:6px 8px;border-radius:18px;order:2}.eco-top-links{order:3;flex:0 0 100%;margin-left:0;padding-bottom:4px;overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch}.eco-top-search{order:4;min-width:0;width:100%;flex:0 0 100%}.eco-layout{padding:18px 14px 14px}.eco-side{position:fixed;left:12px;right:12px;bottom:calc(16px + env(safe-area-inset-bottom,0px));top:auto;z-index:65;padding:11px 12px calc(11px + env(safe-area-inset-bottom,0px));border-radius:26px;background:rgba(7,33,24,.88);backdrop-filter:blur(26px);-webkit-backdrop-filter:blur(26px);box-shadow:0 20px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.06)}.eco-side-head{display:none}.eco-side nav{margin-top:0;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.eco-side nav a{flex-direction:column;justify-content:center;text-align:center;padding:10px 8px;border-radius:18px;font-size:11px;line-height:1.15;color:rgba(227,227,222,.74);gap:5px;font-weight:700}.eco-side nav a.is-desktop-only,.eco-side-link-label{display:none}.eco-side-link-short{display:block}.eco-side-link-icon{font-size:20px}.eco-side nav a.active{border-radius:18px;background:linear-gradient(180deg,rgba(111,219,168,.24),rgba(49,163,117,.92));color:#f7fff9;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 10px 22px rgba(49,163,117,.22)}.eco-side nav a:not(.active):hover{transform:none}.eco-main{max-width:none}.eco-hero h1{font-size:40px}}
</style>
<div class="eco-shell">
  <header class="eco-top">
    <div class="eco-top-left">
      <div class="eco-top-title">
        <?php if ($use_brand_title) : ?>
          <a href="<?php echo esc_url($brand_home_url); ?>"><span class="eco-top-title-brand"><span class="brand-ai">AI</span><span class="brand-rest">trồng cây</span></span></a>
        <?php else : ?>
          <?php 
          $viewable_gardens = is_user_logged_in() && function_exists('aitrongcay_get_viewable_gardens_for_user') ? aitrongcay_get_viewable_gardens_for_user(wp_get_current_user()) : [];
          if (count($viewable_gardens) > 1) : 
          ?>
            <div class="eco-garden-switcher" style="position:relative; display:inline-block;">
                <button class="eco-garden-btn" data-eco-garden-trigger style="background:transparent; border:none; color:inherit; padding:0; cursor:pointer; font-family:inherit; font-size:inherit; font-weight:inherit; font-style:inherit; letter-spacing:inherit; display:inline-flex; align-items:center; gap:8px;">
                    <?php echo esc_html($title); ?> 
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary, #6fdba8)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.8"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="eco-garden-popup" data-eco-garden-popup hidden style="position:absolute; top:calc(100% + 4px); left:0; min-width:240px; background:rgba(26,28,25,.98); border:1px solid rgba(255,255,255,.08); border-radius:16px; padding:6px; box-shadow:0 24px 48px rgba(0,0,0,.4); z-index:80; font-family:var(--ui-font,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif); font-style:normal; letter-spacing:normal; font-size:14px; line-height:1.4;">
                    <?php 
                    $has_own_garden = false;
                    foreach ($viewable_gardens as $g_key => $g_data) {
                        if (($g_data['role'] ?? '') === 'owner') {
                            $has_own_garden = true;
                            $is_active = $g_key === $active_garden_key;
                            echo '<a href="'.esc_url(home_url('/portal/dashboard-2/?garden='.urlencode($g_key))).'" style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; border-radius:12px; color:'.($is_active?'#6fdba8':'#e3e3de').'; text-decoration:none; background:'.($is_active?'rgba(111,219,168,.1)':'transparent').'; margin-bottom:4px; font-weight:700; transition:0.2s;">';
                            echo '<span>Vườn của tôi</span>';
                            if ($is_active) echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            echo '</a>';
                            break;
                        }
                    }
                    $has_shared = false;
                    foreach ($viewable_gardens as $g_key => $g_data) {
                        if (($g_data['role'] ?? '') !== 'owner') {
                            if (!$has_shared) {
                                if ($has_own_garden) {
                                    echo '<div style="height:1px; background:rgba(255,255,255,.06); margin:6px 0;"></div>';
                                }
                                $has_shared = true;
                            }
                            $g_prof = $g_data['profile'] ?? [];
                            $g_name = $g_prof['display_name'] ?? 'Hàng xóm';
                            $is_active = $g_key === $active_garden_key;
                            echo '<a href="'.esc_url(home_url('/portal/dashboard-2/?garden='.urlencode($g_key))).'" style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-radius:12px; color:'.($is_active?'#6fdba8':'#a9b5ab').'; text-decoration:none; background:'.($is_active?'rgba(111,219,168,.1)':'transparent').'; margin-bottom:2px; font-weight:600; transition:0.2s;">';
                            echo '<span style="display:flex; align-items:center; gap:8px;">Vườn của '.esc_html($g_name).' <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(111,219,168,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></span>';
                            if ($is_active) echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                            echo '</a>';
                        }
                    }
                    ?>
                </div>
            </div>
          <?php else : ?>
            <?php echo esc_html($title); ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <nav class="eco-top-links" aria-label="Điều hướng nhanh">
        <?php foreach ($shared_top_links as $top_link) : ?>
          <?php if ($active === $top_link['key']) { continue; } ?>
          <a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
        <?php endforeach; ?>
      </nav>
      <?php if ($search) : ?>
        <form class="eco-top-search" method="get" action="<?php echo esc_url((string) ($search['action'] ?? home_url('/'))); ?>">
          <?php foreach ((array) ($search['hidden'] ?? []) as $hidden_key => $hidden_value) : ?>
            <?php if ($hidden_value === null || $hidden_value === '') { continue; } ?>
            <input type="hidden" name="<?php echo esc_attr((string) $hidden_key); ?>" value="<?php echo esc_attr((string) $hidden_value); ?>">
          <?php endforeach; ?>
          <span><?php echo esc_html((string) ($search['icon'] ?? '🔎')); ?></span>
          <input type="search" name="<?php echo esc_attr((string) ($search['name'] ?? 'q')); ?>" value="<?php echo esc_attr((string) ($search['value'] ?? '')); ?>" placeholder="<?php echo esc_attr((string) ($search['placeholder'] ?? 'Search...')); ?>">
        </form>
      <?php endif; ?>
    </div>
    <div class="eco-top-right">
      <?php if (is_user_logged_in()) : ?>
      <button class="eco-top-bell" id="eco-notification-bell" data-has-new="false" title="Thông báo">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        <span class="eco-bell-dot"></span>
      </button>
      <a class="eco-top-settings" href="<?php echo esc_url(home_url('/tai-khoan/')); ?>" title="Cài đặt">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
      </a>
      <?php endif; ?>
      <button class="eco-top-avatar eco-profile-trigger" type="button" data-eco-profile-trigger aria-expanded="false" aria-haspopup="true"><?php echo $header_avatar_html; ?></button>
    </div>
    <div class="eco-noti-popup" id="eco-noti-popup" hidden>
      <div class="eco-noti-header">
        <h4>Thông báo</h4>
        <button type="button" id="eco-noti-mark-read" style="background:none;border:none;color:#6fdba8;cursor:pointer;font-size:12px">Đánh dấu đã đọc</button>
      </div>
      <div class="eco-noti-list" id="eco-noti-list">
         <div style="padding: 16px; text-align:center; color:#999">Đang tải...</div>
      </div>
    </div>
    <div class="eco-profile-popup" data-eco-profile-popup hidden>
      <?php foreach ($profile_links as $link) : ?>
        <a class="<?php echo ! empty($link['danger']) ? 'is-danger' : ''; ?>" href="<?php echo esc_url((string) ($link['url'] ?? '#')); ?>"><?php echo esc_html((string) ($link['label'] ?? '')); ?></a>
      <?php endforeach; ?>
    </div>
  </header>
  <script>
    (function(){
      var trigger=document.querySelector('[data-eco-profile-trigger]');
      var popup=document.querySelector('[data-eco-profile-popup]');
      var notiTrigger = document.getElementById('eco-notification-bell');
      var notiPopup = document.getElementById('eco-noti-popup');
      var gardenTrigger=document.querySelector('[data-eco-garden-trigger]');
      var gardenPopup=document.querySelector('[data-eco-garden-popup]');

      function closeProfile(){ if(popup) { popup.hidden=true; trigger.setAttribute('aria-expanded','false'); } }
      function closeNoti(){ if(notiPopup) { notiPopup.hidden=true; } }
      function closeGarden(){ if(gardenPopup) { gardenPopup.hidden=true; } }

      if(trigger && popup) {
          trigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var open=popup.hidden===false; closeNoti(); closeGarden(); popup.hidden=open; trigger.setAttribute('aria-expanded', open ? 'false':'true'); });
      }

      if (notiTrigger && notiPopup) {
          notiTrigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var open=notiPopup.hidden===false; closeProfile(); closeGarden(); notiPopup.hidden=open; });
      }

      if (gardenTrigger && gardenPopup) {
          gardenTrigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var open=gardenPopup.hidden===false; closeProfile(); closeNoti(); gardenPopup.hidden=open; });
      }

      document.addEventListener('click', function(e){ 
          if(popup && !popup.hidden && !popup.contains(e.target) && e.target!==trigger){ closeProfile(); } 
          if(notiPopup && !notiPopup.hidden && !notiPopup.contains(e.target) && e.target!==notiTrigger && !notiTrigger.contains(e.target)){ closeNoti(); } 
          if(gardenPopup && !gardenPopup.hidden && !gardenPopup.contains(e.target) && e.target!==gardenTrigger && !gardenTrigger.contains(e.target)){ closeGarden(); } 
      });
      document.addEventListener('keydown', function(e){ if(e.key==='Escape') { closeProfile(); closeNoti(); closeGarden(); } });

      if (notiTrigger && typeof aitrongcayTheme !== 'undefined') {
          var ajaxUrl = aitrongcayTheme.ajaxUrl;
          function fetchNotifications() {
              fetch(ajaxUrl + '?action=aitrongcay_get_notifications', {cache: 'no-store'})
                  .then(r => r.json())
                  .then(res => {
                      if (res.success && res.data) {
                          updateNotificationUI(res.data);
                      }
                  });
          }

          function timeAgo(ts) {
              var seconds = Math.floor(Date.now()/1000) - ts;
              if (seconds < 60) return "Vừa xong";
              if (seconds < 3600) return Math.floor(seconds/60) + " phút trước";
              if (seconds < 86400) return Math.floor(seconds/3600) + " giờ trước";
              return Math.floor(seconds/86400) + " ngày trước";
          }

          function updateNotificationUI(data) {
              var count = data.unread_count || 0;
              if (count > 0) {
                  notiTrigger.setAttribute('data-has-new', 'true');
              } else {
                  notiTrigger.setAttribute('data-has-new', 'false');
              }
              
              var list = document.getElementById('eco-noti-list');
              if (data.notifications && data.notifications.length > 0) {
                  var html = '';
                  data.notifications.forEach(n => {
                      var cls = n.read ? 'eco-noti-item' : 'eco-noti-item unread';
                      var href = n.link ? n.link : '#';
                      html += '<a href="'+href+'" class="'+cls+'">';
                      html += '<div class="eco-noti-title">'+n.title+'</div>';
                      html += '<div class="eco-noti-body">'+n.message+'</div>';
                      html += '<div class="eco-noti-time">'+timeAgo(n.time)+'</div>';
                      html += '</a>';
                  });
                  list.innerHTML = html;
              } else {
                  list.innerHTML = '<div style="padding: 16px; text-align:center; color:#999">Chưa có thông báo nào.</div>';
              }
          }

          document.getElementById('eco-noti-mark-read').addEventListener('click', function() {
              fetch(ajaxUrl + '?action=aitrongcay_mark_notifications_read')
                  .then(r => r.json())
                  .then(res => {
                      fetchNotifications();
                  });
          });

          fetchNotifications();
          setInterval(fetchNotifications, 5000);
      }
    })();
  </script>
  <div class="eco-layout">
    <aside class="eco-side">
      <div class="eco-side-head">
        <div class="eco-side-badge"><?php echo esc_html($sideBadge); ?></div>
        <div><h3><?php echo esc_html($sideTitle); ?></h3><p><?php echo esc_html($sideSubtitle); ?></p></div>
      </div>
      <nav>
        <?php foreach ($nav as $item) : ?>
          <a class="<?php echo ($active === ($item['key'] ?? '')) ? 'active' : ''; ?><?php echo (($item['key'] ?? '') === 'gioi-thieu') ? ' is-desktop-only' : ''; ?>" href="<?php echo esc_url((string) ($item['url'] ?? '#')); ?>">
            <span class="eco-side-link-icon" aria-hidden="true"><?php echo esc_html((string) ($item['icon'] ?? '🍃')); ?></span>
            <span class="eco-side-link-label"><?php echo esc_html((string) ($item['label'] ?? '')); ?></span>
            <span class="eco-side-link-short"><?php echo esc_html((string) ($item['short_label'] ?? ($item['label'] ?? ''))); ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>
    <main class="eco-main">
