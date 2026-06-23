<?php if (! defined('ABSPATH')) { exit; } 
$current_user = wp_get_current_user();
$can_manage_catalog = function_exists('aitrongcay_can_manage_onboarding_catalog') ? aitrongcay_can_manage_onboarding_catalog($current_user) : current_user_can('manage_options');
if (! is_user_logged_in()) {
    wp_safe_redirect(wp_login_url(home_url('/portal/vat-tu-thiet-bi-moi/')));
    exit;
}
if (! $can_manage_catalog) {
    wp_safe_redirect(add_query_arg('catalog_access', 'denied', home_url('/portal/kho-nong-cu-2/')));
    exit;
}

$saved_state = isset($_GET['saved']) ? sanitize_key((string) $_GET['saved']) : '';
$search_term = isset($_GET['q']) ? sanitize_text_field((string) $_GET['q']) : '';
$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$supply_limit = $search_term !== '' ? 12 : 8;
$supplies = function_exists('aitrongcay_supplies_latest') ? aitrongcay_supplies_latest($supply_limit, $search_term) : [];
$editing_supply = function_exists('aitrongcay_supply_find') ? aitrongcay_supply_find($edit_id) : null;
$warehouse_url = home_url('/portal/kho-nong-cu-2/');
$self_url = home_url('/portal/vat-tu-thiet-bi-moi/');
$status_options = function_exists('aitrongcay_supply_status_options') ? aitrongcay_supply_status_options() : ['active' => 'Đang dùng', 'draft' => 'Nháp', 'inactive' => 'Ngưng dùng'];
$type_options = function_exists('aitrongcay_supply_type_options') ? aitrongcay_supply_type_options() : [];
$editing_metrics = '';
if (is_array($editing_supply) && ! empty($editing_supply['optional_metrics_json'])) {
  $decoded_metrics = json_decode((string) $editing_supply['optional_metrics_json'], true);
  if (is_array($decoded_metrics)) {
    $editing_metrics = (string) ($decoded_metrics['raw_text'] ?? '');
  }
}
$upload_nonce = wp_create_nonce('aitrongcay_upload_media_image');
$eco_nav_items = function_exists('aitrongcay_eco_nav_items') ? aitrongcay_eco_nav_items() : [];
?>
<section class="section-tight eco-item-entry-shell">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    main > .section, main > .section > .container, main article.page{max-width:none !important;width:100% !important;padding:0 !important;margin:0 !important;background:transparent !important;box-shadow:none !important;border:none !important}
    main article.page > .eyebrow, main article.page > h1{display:none !important}
    main article.page > .entry-content{margin:0 !important}
    .eco-item-entry-shell{background:#121411;min-height:100vh;padding:0;position:relative;overflow:hidden}
    .eco-item-entry-page{background:#121411;color:#e3e3de;min-height:100vh;position:relative;font-family:'Manrope',sans-serif}
    .eco-item-entry-page *{box-sizing:border-box}
    .eco-item-topbar{position:fixed;top:0;left:0;right:0;z-index:50;height:72px;padding:0 28px;display:flex;align-items:center;justify-content:space-between;background:rgba(18,20,17,.82);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);box-shadow:0 20px 40px rgba(0,0,0,.08)}
    .eco-item-brand{display:flex;align-items:center;gap:18px}
    .eco-item-brand-mark{font-family:'Noto Serif',serif;font-size:30px;font-weight:700;color:#6FDBA8;letter-spacing:-.02em}
    .eco-item-brand-divider{width:1px;height:28px;background:rgba(62,73,66,.45)}
    .eco-item-brand-title{font-family:'Noto Serif',serif;font-size:26px;font-weight:700;letter-spacing:-.03em;color:#e3e3de}
    .eco-item-actions{display:flex;align-items:center;gap:12px}
    .eco-btn{border:none;cursor:pointer;border-radius:18px;padding:12px 18px;font-size:14px;font-weight:700;letter-spacing:.01em;transition:all .24s ease}
    .eco-btn-secondary{background:rgba(51,53,50,.48);color:#e3e3de;backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px);box-shadow:inset 0 0 0 1px rgba(62,73,66,.16)}
    .eco-btn-secondary:hover{transform:translateY(-1px);background:rgba(65,68,63,.7)}
    .eco-btn-primary{background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824;box-shadow:0 0 24px rgba(111,219,168,.22)}
    .eco-btn-primary:hover{transform:scale(1.02);filter:brightness(1.05)}
    .eco-item-layout{padding-top:72px;min-height:100vh;background:
      radial-gradient(circle at top right, rgba(255,225,109,.08), transparent 24%),
      radial-gradient(circle at 20% 10%, rgba(111,219,168,.12), transparent 26%),
      #121411}
    .eco-item-shell-grid{max-width:1360px;margin:0 auto;padding:32px 28px 56px;display:grid;grid-template-columns:240px minmax(0,1fr);gap:24px;align-items:start}
    .eco-shared-side{position:sticky;top:104px;align-self:start;background:rgba(7,33,24,.58);backdrop-filter:blur(24px);border-radius:30px;padding:24px 0;box-shadow:10px 0 30px rgba(0,0,0,.2)}
    .eco-shared-side-head{padding:0 24px 22px;display:flex;align-items:center;gap:12px}.eco-shared-side-badge{width:48px;height:48px;border-radius:18px;background:linear-gradient(135deg,#31a375,#6fdba8);display:grid;place-items:center;color:#062013}
    .eco-shared-side-head h3{margin:0;font-size:14px;color:#6FDBA8;font-weight:800}.eco-shared-side-head p{margin:4px 0 0;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:rgba(227,227,222,.58)}
    .eco-shared-side nav{display:flex;flex-direction:column;gap:2px;margin-top:18px}.eco-shared-side nav a{display:flex;align-items:center;gap:14px;padding:14px 24px;color:rgba(227,227,222,.62);transition:.2s}.eco-side-link-icon{flex:0 0 auto;font-size:16px;line-height:1}.eco-side-link-short{display:none}.eco-shared-side nav a.active{background:linear-gradient(90deg,#31a375,#6fdba8);color:#062013;border-radius:0 999px 999px 0;font-weight:900}.eco-shared-side nav a:not(.active):hover{transform:translateX(6px);color:#6FDBA8}
    .eco-item-main{min-width:0;display:grid;grid-template-columns:minmax(0,1.55fr) minmax(320px,.75fr);gap:24px;align-items:start}
    .eco-glass-card{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:30px;padding:26px 26px 24px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-section-header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px}
    .eco-section-header h1,.eco-section-header h2{margin:0;font-family:'Noto Serif',serif;font-size:36px;line-height:1.05;letter-spacing:-.03em;color:#e3e3de}
    .eco-section-header p{margin:8px 0 0;color:#bdcac0;font-size:14px;max-width:760px}
    .eco-field-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .eco-field-grid--three{grid-template-columns:repeat(3,minmax(0,1fr))}
    .eco-field{display:grid;gap:8px}
    .eco-field label{font-size:11px;font-weight:800;letter-spacing:.22em;text-transform:uppercase;color:#ffb68c;padding-left:4px}
    .eco-field input,.eco-field select,.eco-field textarea{width:100%;background:#292b27;border:none;border-radius:18px;padding:16px 18px;color:#e3e3de;outline:none;font:inherit;box-shadow:none;resize:vertical;min-height:56px}
    .eco-field textarea{min-height:110px}
    .eco-field input::placeholder,.eco-field textarea::placeholder{color:rgba(189,202,192,.38)}
    .eco-field input:focus,.eco-field select:focus,.eco-field textarea:focus{box-shadow:0 0 0 1px rgba(111,219,168,.4)}
    .eco-pills{display:flex;gap:10px;flex-wrap:wrap}
    .eco-pill{padding:10px 14px;border-radius:999px;background:#292b27;color:#bdcac0;font-size:12px;font-weight:700;letter-spacing:.04em}
    .eco-pill.is-active{background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824}
    .eco-side-stack{display:grid;gap:22px;position:sticky;top:104px}
    .eco-item-entry-notice{margin:0 auto 18px;max-width:1360px;padding:0 28px}
    .eco-item-entry-notice .notice-box{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:22px;padding:16px 18px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12);font-size:14px}
    .eco-item-entry-notice .notice-box.is-success{color:#dff7ea;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(111,219,168,.18)}
    .eco-item-entry-notice .notice-box.is-error{color:#ffdad6;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(255,180,171,.18)}
    .eco-preview-card{background:rgba(51,53,50,.42);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:30px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.08), inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-preview-card h3{margin:0 0 14px;font-size:22px;font-family:'Noto Serif',serif}
    .eco-preview-card img{width:100%;aspect-ratio:1/1;border-radius:24px;object-fit:cover;display:block;margin-bottom:16px}
    .eco-preview-card ul{margin:0;padding:0;list-style:none;display:grid;gap:10px}
    .eco-preview-card li{display:flex;justify-content:space-between;gap:12px;font-size:13px;color:#bdcac0}
    .eco-preview-card li strong{color:#e3e3de}
    .eco-mini-card{background:#1a1c19;border-radius:24px;padding:20px;display:grid;gap:14px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12)}
    .eco-mini-card h3{margin:0;font-size:18px;color:#e3e3de}
    .eco-mini-card p{margin:6px 0 0;color:#87948b;font-size:13px;line-height:1.6}
    .eco-mini-card ul{margin:0;padding-left:18px;color:#bdcac0;font-size:14px;line-height:1.7}
    .eco-result-list{display:grid;gap:12px}
    .eco-result-item{background:#1a1c19;border-radius:20px;padding:16px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.12);display:grid;gap:12px}
    .eco-result-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
    .eco-result-title{display:grid;gap:6px}
    .eco-result-title strong{font-size:15px;color:#e3e3de}
    .eco-result-meta{display:flex;flex-wrap:wrap;gap:8px}
    .eco-result-chip{display:inline-flex;align-items:center;justify-content:center;padding:8px 10px;border-radius:999px;background:#292b27;color:#bdcac0;font-size:11px;font-weight:700;letter-spacing:.04em}
    .eco-result-chip.is-status-active{background:rgba(111,219,168,.16);color:#6FDBA8}
    .eco-result-chip.is-status-draft{background:rgba(255,225,109,.14);color:#ffe16d}
    .eco-result-chip.is-status-inactive{background:rgba(255,180,171,.16);color:#ffb4ab}
    .eco-result-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .eco-result-cell{background:#292b27;border-radius:14px;padding:10px 12px}
    .eco-result-cell label{display:block;font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#87948b;margin-bottom:6px}
    .eco-result-cell strong{font-size:13px;color:#e3e3de}
    .eco-resizable-display{display:block;width:100%;min-height:44px;max-height:240px;overflow:auto;resize:vertical;padding:10px 12px;border-radius:12px;background:#1f211e;color:#e3e3de;line-height:1.55;white-space:pre-wrap;word-break:break-word}
    .eco-resizable-display.is-compact{min-height:34px;padding:8px 10px}
    .eco-resizable-display:focus{outline:1px solid rgba(111,219,168,.4)}
    .eco-result-actions{display:flex;justify-content:flex-end}
    .eco-result-actions .eco-btn{padding:10px 14px;font-size:13px;border-radius:14px}
    @media (max-width: 1024px){
      .eco-item-shell-grid{grid-template-columns:1fr}
      .eco-item-main{grid-template-columns:1fr}
      .eco-shared-side,.eco-side-stack{position:relative;top:auto}
    }
    @media (max-width: 860px){
      .eco-item-entry-page{padding-bottom:calc(104px + env(safe-area-inset-bottom,0px))}
      .eco-item-topbar{padding:14px 16px;height:auto;min-height:72px;gap:14px;flex-wrap:wrap}
      .eco-item-brand{width:100%}
      .eco-item-actions{width:100%;justify-content:space-between;flex-wrap:wrap}
      .eco-item-shell-grid{padding:24px 16px 14px}
      .eco-shared-side{position:fixed;left:12px;right:12px;bottom:calc(16px + env(safe-area-inset-bottom,0px));top:auto;z-index:65;padding:11px 12px calc(11px + env(safe-area-inset-bottom,0px));border-radius:26px;background:rgba(7,33,24,.88);backdrop-filter:blur(26px);-webkit-backdrop-filter:blur(26px);box-shadow:0 20px 44px rgba(0,0,0,.30),inset 0 1px 0 rgba(255,255,255,.06)}
      .eco-shared-side-head{display:none}
      .eco-shared-side nav{margin-top:0;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
      .eco-shared-side nav a{flex-direction:column;justify-content:center;text-align:center;padding:10px 8px;border-radius:18px;font-size:11px;line-height:1.15;gap:5px;font-weight:700;color:rgba(227,227,222,.74)}
      .eco-shared-side nav a.is-desktop-only,.eco-side-link-label{display:none}
      .eco-side-link-short{display:block}
      .eco-side-link-icon{font-size:20px}
      .eco-shared-side nav a.active{border-radius:18px;background:linear-gradient(180deg,rgba(111,219,168,.24),rgba(49,163,117,.92));color:#f7fff9;font-weight:800;box-shadow:inset 0 1px 0 rgba(255,255,255,.16),0 10px 22px rgba(49,163,117,.22)}
      .eco-shared-side nav a:not(.active):hover{transform:none}
      .eco-field-grid,.eco-field-grid--three{grid-template-columns:1fr}
      .eco-section-header h1,.eco-section-header h2{font-size:30px}
    }
  </style>
  <div class="eco-item-entry-page">
    <header class="eco-item-topbar">
      <div class="eco-item-brand">
        <div class="eco-item-brand-mark">Eco-Tech Command</div>
        <div class="eco-item-brand-divider"></div>
        <div class="eco-item-brand-title">Vật tư / thiết bị mới</div>
      </div>
      <div class="eco-item-actions">
        <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url($self_url); ?>">Tạo vật tư / thiết bị mới</a>
        <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url($warehouse_url); ?>">Xem trong kho nông cụ</a>
        <button class="eco-btn eco-btn-primary" type="submit" form="aitr-supply-form">Lưu vào DB</button>
      </div>
    </header>

    <?php if ($saved_state !== '') : ?>
      <div class="eco-item-entry-notice">
        <?php if ($saved_state === '1') : ?>
          <div class="notice-box is-success">Đã lưu vật tư / thiết bị mới vào DB hiện có. Từ giờ item này có thể được dùng lại trong flow onboarding cây mới.</div>
        <?php elseif ($saved_state === 'updated') : ?>
          <div class="notice-box is-success">Đã cập nhật vật tư / thiết bị thành công.</div>
        <?php elseif ($saved_state === 'deleted') : ?>
          <div class="notice-box is-success">Đã xóa vật tư / thiết bị khỏi DB.</div>
        <?php elseif ($saved_state === 'duplicated') : ?>
          <div class="notice-box is-success">Đã nhân bản vật tư / thiết bị thành công.</div>
        <?php elseif ($saved_state === 'missing-name') : ?>
          <div class="notice-box is-error">Chưa lưu được vì còn thiếu tên vật tư / thiết bị.</div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="eco-item-layout">
      <div class="eco-item-shell-grid">
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
      <main class="eco-item-main">
        <div>
        <section class="eco-glass-card" style="margin-bottom:22px">
          <div class="eco-section-header">
            <div>
              <h2>Tìm kiếm vật tư đã có</h2>
            </div>
          </div>
          <form method="get" action="<?php echo esc_url(home_url('/portal/vat-tu-thiet-bi-moi/')); ?>">
            <div class="eco-field" style="margin-bottom:12px"><input type="text" name="q" value="<?php echo esc_attr($search_term); ?>" placeholder="Tìm theo tên, mã, nhóm, quy cách..."></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
              <button class="eco-btn eco-btn-secondary" type="submit" style="width:100%;display:flex;justify-content:center;align-items:center">Tìm kiếm</button>
              <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url(home_url('/portal/vat-tu-thiet-bi-moi/')); ?>" style="width:100%;display:flex;justify-content:center;align-items:center">Làm mới</a>
            </div>
          </form>
          <?php if ($search_term !== '') : ?>
          <div style="margin-top:18px">
            <?php if ($supplies !== []) : ?>
              <div class="eco-result-list">
                <?php foreach ($supplies as $supply) : ?>
                  <?php
                    $status = (string) ($supply['status'] ?? 'active');
                    $status_label = $status_options[$status] ?? $status;
                    $cost_price = (float) ($supply['cost_price'] ?? 0);
                    $sale_price = (float) ($supply['sale_price'] ?? 0);
                  ?>
                  <article class="eco-result-item">
                    <div class="eco-result-top">
                      <div style="display:flex;gap:12px;align-items:flex-start;min-width:0;flex:1">
                        <img src="<?php echo esc_url((string) (($supply['image_url'] ?? '') !== '' ? $supply['image_url'] : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBY5K37VgnoYGlLanP2EMEF0fGNGjZrt5UMB1C81D1m-m1diExcr0QQQMepm-vQgq8LJ1p3iL5MW24gN6omSTDUz-wq-c-4q5gswVXDRbWGLN6IZiYVApbz5FPaZW3-SJz80duT_acV-VzauJyKZdRaEc8jroP5MBtVWmljZCUyySYOK6k5oTMEOzqkPLiMmT0su68BiEBmDLlIvKLD-h2K0e9mFviFj2Qs_w4Jp9qkSCJlHgE9EXotPnoGhyID1pbAAHXVa6TPQrEC')); ?>" alt="Thumbnail vật tư" loading="lazy" style="width:56px;height:56px;border-radius:14px;object-fit:cover;flex:0 0 auto">
                        <div class="eco-result-title" style="min-width:0">
                          <strong><?php echo esc_html((string) ($supply['name'] ?? '')); ?></strong>
                          <div class="eco-result-meta">
                            <span class="eco-result-chip"><?php echo esc_html((string) ($supply['type'] ?? 'Vật tư')); ?></span>
                            <span class="eco-result-chip <?php echo esc_attr('is-status-' . $status); ?>"><?php echo esc_html($status_label); ?></span>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="eco-result-grid">
                      <div class="eco-result-cell"><label>Mã nội bộ</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html((string) (($supply['code'] ?? '') !== '' ? $supply['code'] : '—')); ?></div></div>
                      <div class="eco-result-cell"><label>Quy cách</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html((string) (($supply['spec'] ?? '') !== '' ? $supply['spec'] : 'Chưa nhập')); ?></div></div>
                      <div class="eco-result-cell"><label>Giá đầu vào</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html($cost_price > 0 ? number_format($cost_price, 0, ',', '.') . 'đ' : '—'); ?></div></div>
                      <div class="eco-result-cell"><label>Giá bán ra</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html($sale_price > 0 ? number_format($sale_price, 0, ',', '.') . 'đ' : '—'); ?></div></div>
                      <div class="eco-result-cell"><label>Ngày cập nhật</label><div class="eco-resizable-display is-compact" tabindex="0"><?php echo esc_html(isset($supply['updated_at']) ? date_i18n('d/m/Y H:i', strtotime((string) $supply['updated_at'])) : '—'); ?></div></div>
                      <div class="eco-result-cell"><label>Nhà cung cấp</label><div class="eco-resizable-display" tabindex="0"><?php echo esc_html((string) (($supply['supplier_name'] ?? '') !== '' ? $supply['supplier_name'] : '—')); ?></div></div>
                    </div>
                    <div class="eco-result-actions" style="gap:8px;justify-content:flex-end;flex-wrap:wrap">
                      <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url(add_query_arg(['edit' => (int) $supply['id']], home_url('/portal/vat-tu-thiet-bi-moi/'))); ?>" style="display:inline-flex;align-items:center;justify-content:center">Sửa</a>
                      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-flex;margin:0">
                        <input type="hidden" name="action" value="aitrongcay_supply_duplicate">
                        <input type="hidden" name="supply_id" value="<?php echo esc_attr((string) $supply['id']); ?>">
                        <?php wp_nonce_field('aitrongcay_supply_duplicate', 'aitrongcay_supply_duplicate_nonce'); ?>
                        <button class="eco-btn eco-btn-secondary" type="submit">Nhân bản</button>
                      </form>
                      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-flex;margin:0" onsubmit="return confirm('Xóa vật tư / thiết bị này khỏi DB?');">
                        <input type="hidden" name="action" value="aitrongcay_supply_delete">
                        <input type="hidden" name="supply_id" value="<?php echo esc_attr((string) $supply['id']); ?>">
                        <?php wp_nonce_field('aitrongcay_supply_delete', 'aitrongcay_supply_delete_nonce'); ?>
                        <button class="eco-btn eco-btn-secondary" type="submit">Xóa</button>
                      </form>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else : ?>
              <p style="margin:0;color:#bdcac0">Không có kết quả phù hợp. Nhập từ khóa khác hoặc tạo item mới.</p>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </section>
        <section class="eco-glass-card">
          <div class="eco-section-header">
            <div>
              <h1><?php echo $editing_supply ? 'Chỉnh sửa vật tư / thiết bị' : 'Form thông tin vật phẩm'; ?></h1>
            </div>
            <?php if ($editing_supply) : ?>
              <a class="eco-btn eco-btn-secondary" href="<?php echo esc_url($self_url); ?>" style="display:inline-flex;align-items:center;justify-content:center;white-space:nowrap">+ Tạo vật tư / thiết bị mới</a>
            <?php endif; ?>
          </div>

          <form id="aitr-supply-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="aitrongcay_supply_create">
            <input type="hidden" name="supply_id" value="<?php echo esc_attr((string) ($editing_supply['id'] ?? 0)); ?>">
            <?php wp_nonce_field('aitrongcay_supply_create', 'aitrongcay_supply_nonce'); ?>
            <div class="eco-field-grid" style="grid-template-columns:1fr">
              <div class="eco-field"><label>Tên vật phẩm</label><input type="text" name="supply_name" value="<?php echo esc_attr((string) ($editing_supply['name'] ?? '')); ?>" placeholder="Ví dụ: Hạt giống bông cải" required></div>
            </div>

            <div class="eco-field-grid" style="margin-top:18px">
              <div class="eco-field"><label>Nhóm</label><select name="supply_type"><?php foreach ($type_options as $type_option) : ?><option value="<?php echo esc_attr($type_option); ?>" <?php selected((string) ($editing_supply['type'] ?? ''), $type_option); ?>><?php echo esc_html($type_option); ?></option><?php endforeach; ?></select></div>
              <div class="eco-field"><label>Mã nội bộ</label><input type="text" name="supply_code" value="<?php echo esc_attr((string) ($editing_supply['code'] ?? '')); ?>" placeholder="ITEM-BRO-001"></div>
            </div>

            <div class="eco-field-grid" style="margin-top:18px">
              <div class="eco-field"><label>Quy cách</label><input type="text" name="supply_spec" value="<?php echo esc_attr((string) ($editing_supply['spec'] ?? '')); ?>" placeholder="Ví dụ: Gói 500g / Chai 1 lít / Khoang 63x36 cm"></div>
              <div class="eco-field"><label>Đơn vị tính</label><input type="text" name="supply_unit" value="<?php echo esc_attr((string) ($editing_supply['unit'] ?? '')); ?>" placeholder="kg / gói / chai / cái / bộ"></div>
            </div>

            <div class="eco-field-grid" style="margin-top:18px">
              <div class="eco-field"><label>Giá đầu vào</label><input type="text" name="supply_cost_price" value="<?php echo esc_attr((string) ((float) ($editing_supply['cost_price'] ?? 0) > 0 ? number_format((float) $editing_supply['cost_price'], 0, ',', '.') . 'đ' : '')); ?>" placeholder="Ví dụ: 250.000đ"></div>
              <div class="eco-field"><label>Giá bán ra</label><input type="text" name="supply_sale_price" value="<?php echo esc_attr((string) ((float) ($editing_supply['sale_price'] ?? 0) > 0 ? number_format((float) $editing_supply['sale_price'], 0, ',', '.') . 'đ' : '')); ?>" placeholder="Ví dụ: 320.000đ"></div>
            </div>

            <div class="eco-field-grid" style="margin-top:18px">
              <div class="eco-field"><label>Nhà cung cấp</label><input type="text" name="supply_supplier_name" value="<?php echo esc_attr((string) ($editing_supply['supplier_name'] ?? '')); ?>" placeholder="Tên nhà cung cấp"></div>
              <div class="eco-field"><label>Trạng thái</label><select name="supply_status"><?php foreach ($status_options as $status_key => $status_label) : ?><option value="<?php echo esc_attr($status_key); ?>" <?php selected((string) ($editing_supply['status'] ?? 'active'), $status_key); ?>><?php echo esc_html($status_label); ?></option><?php endforeach; ?></select></div>
            </div>

            <input type="hidden" id="supply_existing_image_id" name="supply_existing_image_id" value="<?php echo esc_attr((string) ($editing_supply['image_id'] ?? 0)); ?>">
            <input type="hidden" id="supply_image_url" name="supply_image_url" value="<?php echo esc_attr((string) ($editing_supply['image_url'] ?? '')); ?>">
            <div class="eco-field" style="margin-top:18px"><label>Ảnh vật tư / thiết bị</label><input id="supply_image_file" type="file" name="supply_image_file" accept="image/*"></div>
            <div class="eco-field" style="margin-top:18px"><label>Mô tả ngắn</label><textarea name="supply_description" placeholder="Mô tả nhanh về công dụng và phạm vi sử dụng của vật tư / thiết bị này."><?php echo esc_textarea((string) ($editing_supply['description'] ?? '')); ?></textarea></div>
            <div class="eco-field" style="margin-top:18px"><label>Các chỉ số tùy chọn</label><textarea name="supply_optional_metrics" placeholder="Ví dụ: tỷ lệ nảy mầm, nồng độ, công suất đèn, chất liệu, kích thước, thể tích, tuổi thọ, điều kiện bảo quản..."><?php echo esc_textarea($editing_metrics); ?></textarea></div>
            <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:22px">
              <button class="eco-btn eco-btn-secondary" type="reset">Nhập lại</button>
              <button class="eco-btn eco-btn-primary" type="submit"><?php echo $editing_supply ? 'Cập nhật vật tư' : 'Lưu vào DB'; ?></button>
            </div>
          </form>
        </section>
        </div>

        <aside class="eco-side-stack">
          <section class="eco-preview-card">
            <h3>Ảnh vật tư / thiết bị</h3>
            <img id="supply-preview-image" src="<?php echo esc_url((string) ((is_array($editing_supply) && ! empty($editing_supply['image_url'])) ? $editing_supply['image_url'] : 'https://lh3.googleusercontent.com/aida-public/AB6AXuBY5K37VgnoYGlLanP2EMEF0fGNGjZrt5UMB1C81D1m-m1diExcr0QQQMepm-vQgq8LJ1p3iL5MW24gN6omSTDUz-wq-c-4q5gswVXDRbWGLN6IZiYVApbz5FPaZW3-SJz80duT_acV-VzauJyKZdRaEc8jroP5MBtVWmljZCUyySYOK6k5oTMEOzqkPLiMmT0su68BiEBmDLlIvKLD-h2K0e9mFviFj2Qs_w4Jp9qkSCJlHgE9EXotPnoGhyID1pbAAHXVa6TPQrEC')); ?>" alt="Ảnh vật tư hoặc thiết bị hiện tại">
          </section>

          <section class="eco-mini-card">
            <h3>Page này dùng để làm gì?</h3>
            <p>Đây là page riêng để admin tạo vật tư / thiết bị mới và lưu vào DB. Sau khi tạo xong, các record này sẽ được dùng lại trong page onboarding cây mới để chọn vào danh sách vật tư hỗ trợ của từng loại cây.</p>
            <ul>
              <li>Tạo item mới trong DB</li>
              <li>Gắn ảnh đại diện</li>
              <li>Nhập quy cách và giá</li>
              <li>Thêm các chỉ số tùy chọn theo từng nhóm vật tư</li>
            </ul>
          </section>
        </aside>
      </main>
      </div>
    </div>
  </div>
  <script>
    (() => {
      const input = document.getElementById('supply_image_file');
      const preview = document.getElementById('supply-preview-image');
      const imageId = document.getElementById('supply_existing_image_id');
      const imageUrl = document.getElementById('supply_image_url');
      const statusEl = document.getElementById('supply-image-status');
      const sourceEl = document.getElementById('supply-image-source');
      if (!input || !preview || !imageId || !imageUrl) return;
      input.addEventListener('change', async () => {
        const file = input.files && input.files[0];
        if (!file) return;
        const form = new FormData();
        form.append('action', 'aitrongcay_upload_media_image');
        form.append('nonce', <?php echo wp_json_encode($upload_nonce); ?>);
        form.append('field_name', 'supply_image_file');
        form.append('supply_image_file', file);
        try {
          const res = await fetch(<?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>, { method: 'POST', body: form });
          const json = await res.json();
          if (!json.success) throw new Error((json.data && json.data.message) || 'Không thể upload ảnh');
          preview.src = json.data.url;
          imageId.value = String(json.data.attachment_id || 0);
          imageUrl.value = String(json.data.url || '');
          if (statusEl) statusEl.textContent = 'Đã lưu media';
          if (sourceEl) sourceEl.textContent = 'WordPress Media';
        } catch (e) {
          alert(e.message || 'Có lỗi khi upload ảnh');
        }
      });
    })();
  </script>
</section>
