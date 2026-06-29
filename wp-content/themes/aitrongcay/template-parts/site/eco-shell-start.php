<?php
if (! defined('ABSPATH')) { exit; }
$eco = get_query_var('aitr_eco_shell', []);
$nav = $eco['nav'] ?? [];
$title = (string) ($eco['title'] ?? 'Eco-Tech Marketplace');
$use_brand_title = in_array($active, ['cho-que', 'kho-nong-cu', 'hang-xom'], true);
$active = (string) ($eco['active'] ?? '');
$sideTitle = (string) ($eco['side_title'] ?? 'Ai trồng cây');
$sideSubtitle = (string) ($eco['side_subtitle'] ?? '');
$sideBadge = (string) ($eco['side_badge'] ?? '🍃');
$topIcons = (array) ($eco['top_icons'] ?? ['🌿','📷']);
$search = is_array($eco['search'] ?? null) ? $eco['search'] : [];
$garden_query_value = isset($_GET['garden']) ? sanitize_text_field((string) wp_unslash($_GET['garden'])) : '';
$shared_top_links = [
    ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
    ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
    ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
    ['key' => 'dashboard-2', 'label' => 'Vào khu vườn của tôi', 'url' => home_url('/portal/dashboard-2/')],
];
foreach ($shared_top_links as &$shared_top_link) {
    if ($garden_query_value !== '' && in_array($shared_top_link['key'], ['kho-nong-cu', 'hang-xom', 'dashboard-2'], true)) {
        $shared_top_link['url'] = add_query_arg('garden', $garden_query_value, $shared_top_link['url']);
    }
}
unset($shared_top_link);
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
.eco-top-left{display:flex;align-items:center;gap:22px;min-width:0;flex:1}.eco-top-title{font-family:'Noto Serif',serif;font-size:34px;font-style:italic;color:var(--primary);letter-spacing:-.03em;white-space:nowrap}.eco-top-title-brand{display:inline-flex;align-items:baseline;gap:8px;font-style:normal;letter-spacing:-.03em}.eco-top-title-brand .brand-ai{color:#6fdba8;font-weight:900}.eco-top-title-brand .brand-rest{color:#ffffff;font-weight:700}
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
@media (max-width:1100px){.eco-layout{grid-template-columns:1fr;padding:18px}.eco-side{position:static}}
@media (max-width:820px){.eco-shell{padding-bottom:calc(104px + env(safe-area-inset-bottom,0px))}.eco-top{padding:14px 16px;gap:12px;flex-wrap:nowrap;align-items:center}.eco-top-left{min-width:0;flex-wrap:nowrap;gap:12px}.eco-top-title{font-size:28px;flex:0 0 auto}.eco-top-links{flex:1 1 auto;margin-left:0;padding-bottom:0}.eco-top-search{min-width:0;width:100%}.eco-top-right{flex:0 0 auto;padding:6px 8px;border-radius:18px}.eco-layout{padding:18px 14px 14px}.eco-side{position:fixed;left:12px;right:12px;bottom:calc(16px + env(safe-area-inset-bottom,0px));top:auto;z-index:65;padding:11px 12px calc(11px + env(safe-area-inset-bottom,0px));border-radius:26px;background:rgba(7,33,24,.88);backdrop-filter:blur(26px);-webkit-backdrop-filter:blur(26px);box-shadow:0 20px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.06)}.eco-side-head{display:none}.eco-side nav{margin-top:0;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}.eco-side nav a{flex-direction:column;justify-content:center;text-align:center;padding:10px 8px;border-radius:18px;font-size:11px;line-height:1.15;color:rgba(227,227,222,.74);gap:5px;font-weight:700}.eco-side nav a.is-desktop-only,.eco-side-link-label{display:none}.eco-side-link-short{display:block}.eco-side-link-icon{font-size:20px}.eco-side nav a.active{border-radius:18px;background:linear-gradient(180deg,rgba(111,219,168,.24),rgba(49,163,117,.92));color:#f7fff9;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 10px 22px rgba(49,163,117,.22)}.eco-side nav a:not(.active):hover{transform:none}.eco-main{max-width:none}.eco-hero h1{font-size:40px}}
</style>
<div class="eco-shell">
  <header class="eco-top">
    <div class="eco-top-left">
      <div class="eco-top-title"><?php if ($use_brand_title) : ?><span class="eco-top-title-brand"><span class="brand-ai">AI</span><span class="brand-rest">trồng cây</span></span><?php else : ?><?php echo esc_html($title); ?><?php endif; ?></div>
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
      <button class="eco-top-avatar eco-profile-trigger" type="button" data-eco-profile-trigger aria-expanded="false" aria-haspopup="true"><?php echo $header_avatar_html; ?></button>
    </div>
    <div class="eco-profile-popup" data-eco-profile-popup hidden>
      <?php foreach ($profile_links as $link) : ?>
        <a class="<?php echo ! empty($link['danger']) ? 'is-danger' : ''; ?>" href="<?php echo esc_url((string) $link['url']); ?>"><?php echo esc_html((string) $link['label']); ?></a>
      <?php endforeach; ?>
    </div>
  </header>
  <script>
    (function(){
      var trigger=document.querySelector('[data-eco-profile-trigger]');
      var popup=document.querySelector('[data-eco-profile-popup]');
      if(!trigger||!popup) return;
      function close(){ popup.hidden=true; trigger.setAttribute('aria-expanded','false'); }
      trigger.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); var open=popup.hidden===false; popup.hidden=open; trigger.setAttribute('aria-expanded', open ? 'false':'true'); });
      document.addEventListener('click', function(e){ if(!popup.hidden && !popup.contains(e.target) && e.target!==trigger){ close(); } });
      document.addEventListener('keydown', function(e){ if(e.key==='Escape') close(); });
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
