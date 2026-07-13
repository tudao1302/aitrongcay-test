<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

$page = aitrongcay_current_virtual_page();
$slug = $page['slug'] ?? 'portal/hydration';
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

$garden_key = $is_logged_in ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$active_profile = $is_logged_in ? aitrongcay_portal_profile_for_garden_context($garden_key, $current_user instanceof WP_User ? $current_user : null) : null;
$garden_display_name = trim((string) ($active_profile['garden_name'] ?? 'Vườn Thủy Canh'));
$owner_name = trim((string) ($active_profile['owner_name'] ?? ($current_user instanceof WP_User ? $current_user->display_name : 'Khách hàng')));

if (!$is_logged_in) {
  wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
  exit;
}

$pots = function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];

$friends_url = add_query_arg(['garden' => $garden_key], home_url('/portal/hang-xom/'));
$photo_library_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/nhat-ky-cham-soc/')) : home_url('/portal/nhat-ky-cham-soc/');
$flower_bio_url = add_query_arg(['garden' => $garden_key], home_url('/portal/flower-bio/'));

// Lấy Token của vườn để sử dụng chung
$blynk_token = '';
global $wpdb;
$racks_table = $wpdb->prefix . 'aitr_garden_racks';
if ($wpdb->get_var("SHOW TABLES LIKE '$racks_table'") === $racks_table) {
    $rack_token = (string) $wpdb->get_var($wpdb->prepare("SELECT blynk_auth_token FROM $racks_table WHERE garden_key = %s LIMIT 1", $garden_key));
    if (!empty($rack_token)) {
        $blynk_token = $rack_token;
    }
}
if (empty($blynk_token)) {
    $blynk_cfg = get_option("aitrongcay_blynk_config_{$garden_key}", []);
    $blynk_token = trim((string) ($blynk_cfg['token'] ?? ''));
}

// ---------------------------------------------
// TASK 3.2: KIỂM TRA PHẦN CỨNG ONLINE/OFFLINE
// ---------------------------------------------
$is_hardware_online = true; // Mặc định là online nếu không check được
if ($blynk_token !== '') {
    $online_api_url = "https://blynk.cloud/external/api/isHardwareConnected?token={$blynk_token}";
    $resp_online = wp_remote_get($online_api_url, ['timeout' => 3]);
    if (!is_wp_error($resp_online) && wp_remote_retrieve_response_code($resp_online) === 200) {
        $body = trim(wp_remote_retrieve_body($resp_online));
        if (strtolower($body) === 'false') {
            $is_hardware_online = false;
        }
    }
}

// ---------------------------------------------
// TASK 3.1: ĐỒNG BỘ TRẠNG THÁI BƠM HIỆN TẠI
// ---------------------------------------------
$is_pump_running = false;
if ($blynk_token !== '' && $is_hardware_online) {
    $pump_api_url = "https://blynk.cloud/external/api/get?token={$blynk_token}&v1";
    $resp_pump = wp_remote_get($pump_api_url, ['timeout' => 3]);
    if (!is_wp_error($resp_pump) && wp_remote_retrieve_response_code($resp_pump) === 200) {
        $body = trim(wp_remote_retrieve_body($resp_pump));
        if ($body === '1' || strtolower($body) === 'on') {
            $is_pump_running = true;
        }
    }
}

// ---------------------------------------------
// TASK 2.2: ĐỒNG BỘ MỰC NƯỚC (Từ Blynk API)
// ---------------------------------------------
$water_level = 0; 
if ($blynk_token !== '' && $is_hardware_online) { // Chỉ lấy số khi online
    // V12 là chân Water Level mặc định theo cấu hình.
    // Dùng wp_remote_get để lấy số liệu thực từ Blynk
    $blynk_api_url = "https://blynk.cloud/external/api/get?token={$blynk_token}&v12";
    $response = wp_remote_get($blynk_api_url, ['timeout' => 3]);
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = trim(wp_remote_retrieve_body($response));
        if (is_numeric($body)) {
            $water_level = (int) $body;
        }
    }
}

// Tự động phân tích cảnh báo
if ($water_level >= 80) {
    $water_status_text = 'Mực nước đầy';
    $water_status_color = '#3b82f6'; // Xanh dương
} elseif ($water_level >= 25) {
    $water_status_text = 'Mực nước ổn định';
    $water_status_color = '#31a375'; // Xanh lá
} else {
    $water_status_text = 'Cảnh báo cạn nước!';
    $water_status_color = '#ef4444'; // Đỏ cảnh báo
}

// ---------------------------------------------
// TASK 2.1: BÁO CÁO TIÊU HAO THỰC TẾ (Từ Database)
// ---------------------------------------------
$monthly_consumption_liters = 0;
$power_kwh = 0;
$reports_table = $wpdb->prefix . 'aitr_garden_reports';

if ($wpdb->get_var("SHOW TABLES LIKE '$reports_table'") === $reports_table && $blynk_token !== '') {
    $current_month = current_time('Y-m');
    $stats = $wpdb->get_row($wpdb->prepare(
        "SELECT SUM(water_consumed_liters) as total_water, SUM(power_consumed_kwh) as total_power 
         FROM $reports_table 
         WHERE device_token = %s AND report_date LIKE %s",
        $blynk_token,
        $current_month . '%'
    ), ARRAY_A);

    if ($stats) {
        $monthly_consumption_liters = round((float) ($stats['total_water'] ?? 0), 1);
        $power_kwh = round((float) ($stats['total_power'] ?? 0), 2);
    }
}

$saved_liters = round($monthly_consumption_liters * 2.5, 1); // Giả lập tỷ lệ tiết kiệm so với trồng đất (250%)
$saved_percentage = 72;

$shared_top_links = [
  ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
  ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
  ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
  ['key' => 'dashboard-2', 'label' => 'Vào khu vườn của tôi', 'url' => home_url('/portal/dashboard-2/')],
];
foreach ($shared_top_links as &$shared_top_link) {
  if ($garden_key !== '') {
    $shared_top_link['url'] = add_query_arg('garden', $garden_key, $shared_top_link['url']);
  }
}
unset($shared_top_link);
?>
<section class="d2-app">
  <style>
    .site-header, .site-footer { display: none !important; }
    
    .d2-app {
      --bg: #121411;
      --bg-2: #1a1c19;
      --panel: rgba(22, 26, 22, 0.6);
      --panel-border: rgba(111, 219, 168, 0.08);
      --primary: #6fdba8;
      --primary-dim: rgba(111, 219, 168, 0.15);
      --text: #e3e3de;
      --muted: #bdcac0;
      --line: rgba(255, 255, 255, 0.05);
      --water: #3b82f6;
      --water-dim: rgba(59, 130, 246, 0.15);
      font-family: 'Manrope', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      margin: -32px calc(50% - 50vw) 0;
      padding: 0;
      overflow-x: hidden;
    }

    /* Bê nguyên CSS Sidebar của Dashboard 2 */
    .d2-shell {
      display: grid;
      grid-template-columns: 272px minmax(0, 1fr);
      min-height: 100vh
    }

    .d2-side {
      background: linear-gradient(180deg, rgba(6, 27, 14, .78), rgba(0, 42, 32, .84));
      backdrop-filter: blur(28px) saturate(120%);
      padding: 24px 16px 28px;
      border-right: 1px solid rgba(255, 255, 255, .08);
      box-shadow: 0 30px 80px rgba(0, 0, 0, .34), inset -1px 0 0 rgba(255, 255, 255, .05);
      display: flex;
      flex-direction: column;
    }

    .d2-brand {
      font-family: 'Noto Serif', Georgia, serif;
      font-size: 34px;
      font-style: italic;
      color: var(--primary);
      margin: 2px 12px 28px
    }

    .d2-level {
      display: flex;
      gap: 14px;
      align-items: center;
      margin: 0 8px 14px;
      padding: 12px 14px;
      border-radius: 24px;
      background: rgba(111, 219, 168, .08);
      border: 1px solid rgba(255, 255, 255, .06);
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 12px 28px rgba(0, 0, 0, .12)
    }

    .d2-level-badge {
      width: 44px;
      height: 44px;
      border-radius: 16px;
      background: rgba(111, 219, 168, .14);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      font-weight: 800
    }

    .d2-upgrade {
      display: flex;
      align-items: center;
      gap: 14px;
      margin: 0 6px 8px;
      padding: 16px 18px;
      border-radius: 20px;
      background: transparent;
      color: rgba(227, 227, 222, .46);
      font-weight: 600;
      text-decoration: none;
      transition: all .18s ease;
    }
    .d2-upgrade:hover {
      color: var(--primary);
      text-shadow: 0 0 12px rgba(111, 219, 168, .28)
    }

    .d2-nav {
      display: grid;
      gap: 8px;
      padding: 0 6px
    }

    .d2-nav a {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 16px 18px;
      border-radius: 20px;
      color: rgba(227, 227, 222, .46);
      font-weight: 600;
      text-decoration: none;
      transition: all .18s ease;
    }

    .d2-nav .bottom-nav-short { display: none }

    .d2-nav a:hover {
      color: var(--primary);
      text-shadow: 0 0 12px rgba(111, 219, 168, .28)
    }

    .d2-nav a.active {
      background: rgba(6, 77, 58, .7);
      color: var(--primary);
      box-shadow: inset 0 0 0 1px rgba(111, 219, 168, .08)
    }

    .d2-side-footer {
      margin-top: auto;
      padding: 24px 16px 0;
      color: rgba(227, 227, 222, .5);
      display: grid;
      gap: 10px;
      font-size: 14px
    }

    /* Main Content */
    .d2-main {
      padding: 30px 28px 40px;
      background: radial-gradient(circle at top right, rgba(111, 219, 168, .06), transparent 24%), radial-gradient(circle at top left, rgba(255, 225, 109, .05), transparent 22%), var(--bg);
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .d2-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 8px;
      position: relative;
    }

    .d2-hamburger {
      display: none;
      background: rgba(111, 219, 168, 0.1);
      border: 1px solid rgba(111, 219, 168, 0.2);
      border-radius: 12px;
      color: var(--primary);
      cursor: pointer;
      padding: 8px;
    }

    .d2-top-links {
      display: flex;
      gap: 18px;
    }

    .d2-top-links a {
      color: var(--muted);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }
    .d2-top-links a:hover { color: var(--primary); }

    .d2-garden-name {
      font-size: 28px;
      font-weight: 800;
      font-family: 'Noto Serif', serif;
      margin: 0 0 4px;
      color: var(--primary);
      letter-spacing: -0.02em;
    }
    
    .d2-subtitle {
      color: var(--muted);
      font-size: 15px;
      margin: 0;
    }

    /* Hydration Grid */
    .hy-grid {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 24px;
      align-items: start;
    }

    .hy-card {
      background: var(--panel);
      border: 1px solid var(--panel-border);
      border-radius: 24px;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    .hy-card-title {
      font-size: 16px;
      font-weight: 700;
      color: #fff;
      margin: 0 0 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* Water Level Tank */
    .hy-tank-wrap {
      display: flex;
      align-items: center;
      gap: 30px;
    }

    .hy-tank {
      width: 120px;
      height: 240px;
      background: rgba(0,0,0,0.3);
      border: 2px solid var(--line);
      border-radius: 16px;
      position: relative;
      overflow: hidden;
      box-shadow: inset 0 10px 30px rgba(0,0,0,0.5);
      flex-shrink: 0;
    }

    .hy-tank-fill {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
      transition: height 1s ease-in-out;
      box-shadow: 0 -5px 20px rgba(59, 130, 246, 0.4);
    }
    
    .hy-tank-fill::before {
      content: '';
      position: absolute;
      top: -10px;
      left: 0;
      width: 200%;
      height: 20px;
      background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 100 20" xmlns="http://www.w3.org/2000/svg"><path d="M0 10 Q 25 0 50 10 T 100 10 L 100 20 L 0 20 Z" fill="rgba(255,255,255,0.2)"/></svg>') repeat-x;
      animation: wave 4s linear infinite;
    }

    @keyframes wave {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }

    .hy-tank-percent {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      text-shadow: 0 2px 10px rgba(0,0,0,0.5);
      z-index: 2;
    }

    .hy-tank-info {
      flex: 1;
    }

    .hy-status-badge {
      display: inline-block;
      padding: 6px 12px;
      background: rgba(49, 163, 117, 0.15);
      color: #6fdba8;
      border: 1px solid rgba(49, 163, 117, 0.3);
      border-radius: 99px;
      font-size: 13px;
      font-weight: 700;
      margin-bottom: 16px;
    }

    .hy-btn {
      background: var(--primary);
      color: #030504;
      border: none;
      padding: 12px 24px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 14px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
    }
    
    .hy-btn:hover {
      background: #5bc995;
      transform: translateY(-2px);
    }
    
    .hy-btn.secondary {
      background: var(--line);
      color: var(--text);
    }
    
    .hy-btn.secondary:hover {
      background: rgba(255,255,255,0.1);
    }

    /* Stats Grid */
    .hy-stats-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
      margin-top: 24px;
    }

    .hy-stat-box {
      background: rgba(0,0,0,0.2);
      border-radius: 16px;
      padding: 16px;
      border: 1px solid var(--line);
    }

    .hy-stat-val {
      font-size: 28px;
      font-weight: 800;
      color: #fff;
      margin: 8px 0 4px;
    }

    .hy-stat-label {
      font-size: 13px;
      color: var(--muted);
    }

    /* Automation Rules */
    .hy-rule-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px;
      background: rgba(255,255,255,0.02);
      border: 1px solid var(--line);
      border-radius: 12px;
      margin-bottom: 12px;
      gap: 16px;
    }

    .hy-rule-info {
      flex: 1;
      min-width: 0; /* Cho phép truncate nếu cần */
    }

    .hy-rule-info h5 {
      margin: 0 0 4px;
      font-size: 15px;
      color: #fff;
    }
    
    .hy-rule-info p {
      margin: 0;
      font-size: 13px;
      color: var(--muted);
      line-height: 1.4;
    }

    /* Toggle Switch Fixed (No Flex Shrink Oval) */
    .hy-toggle {
      width: 44px;
      height: 24px;
      flex-shrink: 0; /* FIX 1: Chống méo oval trên Desktop */
      background: rgba(255,255,255,0.1);
      border-radius: 12px; /* FIX 2: Bo tròn hoàn hảo */
      position: relative;
      cursor: pointer;
      transition: background 0.3s ease;
      box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
    }
    .hy-toggle.active {
      background: var(--primary);
    }
    .hy-toggle::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: 20px;
      height: 20px;
      background: #fff;
      border-radius: 50%;
      transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
      box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    .hy-toggle.active::after {
      transform: translateX(20px);
    }

    /* Responsive Sidebar from Dashboard-2 */
    @media (max-width: 1024px) {
      .d2-shell {
        grid-template-columns: 1fr;
      }
      .d2-side {
        display: block;
        position: fixed;
        left: 12px;
        right: 12px;
        bottom: calc(16px + env(safe-area-inset-bottom, 0px));
        top: auto;
        z-index: 70;
        height: auto;
        padding: 11px 12px calc(11px + env(safe-area-inset-bottom, 0px));
        border-right: none;
        border-radius: 26px;
        background: rgba(6, 27, 14, .88);
        backdrop-filter: blur(26px) saturate(120%);
        -webkit-backdrop-filter: blur(26px) saturate(120%);
        box-shadow: 0 20px 44px rgba(0, 0, 0, .30), inset 0 1px 0 rgba(255, 255, 255, .06);
      }
      .d2-brand, .d2-level, .d2-upgrade, .d2-side-footer { display: none; }
      .d2-nav {
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 4px;
        padding: 0;
      }
      .d2-nav a {
        flex-direction: column;
        justify-content: center;
        text-align: center;
        padding: 8px 4px;
        border-radius: 14px;
        font-size: 9px;
        line-height: 1.15;
        gap: 4px;
        font-weight: 700;
        color: rgba(227, 227, 222, .74);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
      .d2-nav a.active {
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(111, 219, 168, .24), rgba(49, 163, 117, .92));
        color: #f7fff9;
        font-weight: 800;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .16), 0 10px 22px rgba(49, 163, 117, .22);
      }
      .d2-nav .bottom-nav-label { display: none; }
      .d2-nav .bottom-nav-short { display: block; }
      .d2-nav .bottom-nav-icon { font-size: 20px; line-height: 1; }
      .hy-grid { grid-template-columns: 1fr; }
      
      .d2-top { flex-wrap: nowrap; gap: 14px; align-items: center; }
      .d2-top > div:first-child { flex: 1 1 0; min-width: 0; }
      .d2-hamburger { display: block; flex-shrink: 0; }
      .d2-top-links { 
        display: none; 
        position: absolute; 
        top: 100%; 
        right: 0; 
        margin-top: 12px; 
        background: rgba(26, 28, 25, 0.98); 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        border-radius: 16px; 
        padding: 8px 0; 
        box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        flex-direction: column; 
        gap: 0; 
        z-index: 100; 
        min-width: 220px; 
      }
      .d2-top-links.is-open { display: flex; }
      .d2-top-links a { padding: 14px 20px; color: #e3e3de; border-bottom: 1px solid rgba(255,255,255,0.04); }
      .d2-top-links a:last-child { border-bottom: none; }
      .d2-main { padding: calc(42px + env(safe-area-inset-top, 0px)) 14px calc(104px + env(safe-area-inset-bottom, 0px)); }
    }
  </style>

  <div class="d2-shell">
    
    <!-- SAO CHÉP CHÍNH XÁC SIDEBAR TỪ DASHBOARD-2.PHP -->
    <aside class="d2-side">
      <div class="d2-brand">Ai trồng cây</div>
      <div class="d2-level">
        <div class="d2-level-badge">🛡</div>
        <div>
          <?php
          $_lvl = 1;
          if (isset($current_user->ID)) {
              $_pts = (int) get_user_meta($current_user->ID, '_aitrongcay_eco_points', true);
              if (function_exists('aitrongcay_calculate_level')) {
                  $_lvl = aitrongcay_calculate_level($_pts);
              }
          }
          ?>
          <div style="font-weight:800;color:var(--primary)">Level <?php echo esc_html($_lvl); ?></div>
          <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(227,227,222,.46)">
            <?php echo esc_html($owner_name); ?></div>
        </div>
      </div>
      <a class="d2-upgrade" href="<?php echo esc_url($friends_url); ?>">👥 Hàng xóm</a>
      <nav class="d2-nav">
        <a href="<?php echo esc_url($photo_library_url); ?>"><span class="bottom-nav-icon"
            aria-hidden="true">🖼</span><span class="bottom-nav-label">Galleries</span><span
            class="bottom-nav-short">Ảnh</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/hydration/'))); ?>" class="active"><span class="bottom-nav-icon" aria-hidden="true">💧</span><span
            class="bottom-nav-label">Hydration</span><span class="bottom-nav-short">Nước</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/soil-health/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">🌿</span><span class="bottom-nav-label">Soil
            Health</span><span class="bottom-nav-short">Giá thể</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key, 'view' => 'timelapse'], home_url('/portal/dashboard-2/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">📽</span><span
            class="bottom-nav-label">Timelapse</span><span class="bottom-nav-short">TL</span></a>
        <a href="<?php echo esc_url($flower_bio_url); ?>"><span class="bottom-nav-icon"
            aria-hidden="true">🍃</span><span class="bottom-nav-label">Bách thảo</span><span
            class="bottom-nav-short">Bách thảo</span></a>
        <a href="<?php echo esc_url(add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/kho-nong-cu-2/'))); ?>"><span
            class="bottom-nav-icon" aria-hidden="true">🗄</span><span class="bottom-nav-label">Seed Bank</span><span
            class="bottom-nav-short">Kho</span></a>
      </nav>
      <div class="d2-side-footer">
        <div>🤖 AI Status: Active</div>
        <div>🌱 <?php echo esc_html((string) count($pots)); ?> khoang đang theo dõi</div>
      </div>
    </aside>

    <!-- NỘI DUNG CHÍNH (HYDRATION) -->
    <main class="d2-main">
      <div class="d2-top">
        <div>
          <h1 class="d2-garden-name">💦 Hydration Center</h1>
          <p class="d2-subtitle">Quản lý tưới tiêu thông minh & Giám sát lượng nước</p>
        </div>
        <button class="d2-hamburger" type="button" aria-label="Menu" aria-expanded="false" onclick="document.getElementById('hyTopMenu').classList.toggle('is-open');">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="d2-top-links" id="hyTopMenu">
          <?php foreach ($shared_top_links as $top_link): ?>
            <a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="hy-grid">
        <!-- Cột trái: Mực nước & Thống kê -->
        <div style="display: flex; flex-direction: column; gap: 24px; position: relative;">

          <?php if (!$is_hardware_online): ?>
            <!-- Lớp phủ xám khi mất mạng -->
            <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: grayscale(80%) blur(2px); z-index: 10; border-radius: 24px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
               <div style="background: rgba(239, 68, 68, 0.9); padding: 12px 24px; border-radius: 12px; color: white; font-weight: bold; text-align: center; box-shadow: 0 10px 20px rgba(0,0,0,0.5);">
                 ⚠️ CẢNH BÁO: Mất kết nối WiFi <br>
                 <span style="font-size: 13px; font-weight: normal;">Thiết bị phần cứng tại vườn đang Offline. Không thể điều khiển lúc này!</span>
               </div>
            </div>
          <?php endif; ?>
          
          <div class="hy-card">
            <h3 class="hy-card-title">💧 Trạng thái Bồn chứa</h3>
            <div class="hy-tank-wrap">
              <div class="hy-tank">
                <div class="hy-tank-fill" style="height: <?php echo $water_level; ?>%;"></div>
                <div class="hy-tank-percent"><?php echo $water_level; ?>%</div>
              </div>
              <div class="hy-tank-info">
                <?php if (!$is_hardware_online): ?>
                  <div class="hy-status-badge" style="background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid #ef4444;">🔴 Thiết bị đang Offline</div>
                <?php else: ?>
                  <div class="hy-status-badge">🟢 <?php echo $water_status_text; ?></div>
                <?php endif; ?>
                <p style="color: var(--muted); font-size: 14px; margin-bottom: 20px; line-height: 1.6;">
                  Mực nước đang ở mức an toàn. Hệ thống tự động tưới vẫn hoạt động bình thường. Dự kiến còn <strong>7 ngày</strong> nữa mới cần châm thêm nước.
                </p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                  <button id="hy-btn-pump-main" class="hy-btn" 
                          <?php if (!$is_hardware_online) echo 'disabled style="opacity: 0.5; cursor: not-allowed;"'; ?> 
                          style="<?php echo $is_pump_running ? 'background: #ef4444; color: #fff; border-color: #ef4444; box-shadow: 0 0 15px rgba(239,68,68,0.4);' : ''; ?>">
                    <?php echo $is_pump_running ? '🛑 ĐANG BƠM (Bấm để Tắt)' : 'Bơm ngay 5 phút'; ?>
                  </button>
                  <button class="hy-btn secondary" onclick="document.getElementById('hy-history-modal').style.display='flex'">Lịch sử bơm</button>
                </div>
              </div>
            </div>
          </div>

          <div class="hy-card">
            <h3 class="hy-card-title">🔬 Chỉ số Dung dịch (Water Quality)</h3>
            <div class="hy-stats-grid" style="grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 0;">
              <div class="hy-stat-box" style="text-align: center; padding: 16px 8px;">
                <div class="hy-stat-label">Độ pH</div>
                <div class="hy-stat-val" style="color: #6fdba8; font-size: 24px;">6.2</div>
                <div style="font-size: 11px; color: var(--muted);">Tối ưu: 5.8 - 6.5</div>
              </div>
              <div class="hy-stat-box" style="text-align: center; padding: 16px 8px;">
                <div class="hy-stat-label">TDS / EC</div>
                <div class="hy-stat-val" style="color: #ffe16d; font-size: 24px;">1250</div>
                <div style="font-size: 11px; color: var(--muted);">PPM (Dinh dưỡng)</div>
              </div>
              <div class="hy-stat-box" style="text-align: center; padding: 16px 8px;">
                <div class="hy-stat-label">Nhiệt độ nước</div>
                <div class="hy-stat-val" style="color: #60a5fa; font-size: 24px;">25.3°C</div>
                <div style="font-size: 11px; color: var(--muted);">Mát mẻ</div>
              </div>
            </div>
            <p style="color: var(--muted); font-size: 13px; margin: 16px 0 0; text-align: center;">
              * Các chỉ số này quyết định tốc độ hấp thụ dinh dưỡng của rễ cây.
            </p>
          </div>

          <div class="hy-card">
            <h3 class="hy-card-title">📊 Báo cáo Tiêu hao (Tháng này)</h3>
            <p style="color: var(--muted); font-size: 14px; margin-bottom: 16px;">
              Mô hình thủy canh tuần hoàn giúp bạn tiết kiệm đáng kể lượng nước so với trồng đất truyền thống.
            </p>
            
            <div class="hy-stats-grid">
              <div class="hy-stat-box">
                <div class="hy-stat-label">Nước đã tiêu thụ</div>
                <div class="hy-stat-val" style="color: #60a5fa;"><?php echo $monthly_consumption_liters; ?> <span style="font-size: 14px; color: var(--muted);">Lít</span></div>
                <div style="font-size: 12px; color: var(--muted); margin-top: 4px;">~ <?php echo $power_kwh; ?> kWh điện năng</div>
              </div>
              <div class="hy-stat-box">
                <div class="hy-stat-label">Nước tiết kiệm được</div>
                <div class="hy-stat-val" style="color: var(--primary);"><?php echo $saved_liters; ?> <span style="font-size: 14px; color: var(--muted);">Lít</span></div>
                <div style="font-size: 12px; color: var(--primary); margin-top: 4px;">↑ Tiết kiệm <?php echo $saved_percentage; ?>%</div>
              </div>
            </div>
            
            <!-- Mock Chart Bar -->
            <div style="margin-top: 24px;">
              <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); margin-bottom: 8px;">
                <span>T2</span><span>T3</span><span>T4</span><span>T5</span><span>T6</span><span>T7</span><span>CN</span>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: flex-end; height: 100px; gap: 8px;">
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 40%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 60%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 30%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 80%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 50%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: var(--water-dim); border-radius: 4px 4px 0 0; height: 90%;"><div style="background: var(--water); height: 100%; border-radius: inherit; opacity: 0.8;"></div></div>
                <div style="flex:1; background: rgba(111,219,168,0.2); border-radius: 4px 4px 0 0; height: 45%;"><div style="background: var(--primary); height: 100%; border-radius: inherit; opacity: 1;"></div></div>
              </div>
            </div>
          </div>

        </div>

        <!-- Cột phải: Automation Rules -->
        <div class="hy-card">
          <h3 class="hy-card-title">⚡ Kịch bản Tự động (Rules)</h3>
          <p style="color: var(--muted); font-size: 13px; margin-bottom: 20px;">
            Tự động điều chỉnh lịch tưới dựa trên các tín hiệu môi trường để tối ưu hóa sự phát triển.
          </p>

          <div class="hy-rule-item" style="border-color: rgba(111,219,168,0.3); background: rgba(111,219,168,0.05);">
            <div class="hy-rule-info">
              <h5 style="color: var(--primary);">🤖 Dự đoán cạn nước (AI Refill Predictor)</h5>
              <p style="color: var(--muted); font-size: 13px; line-height: 1.5; margin-top: 6px;">
                Dựa vào nhiệt độ, độ ẩm môi trường và tốc độ hụt nước, hệ thống dự đoán và đếm ngược: 
                <i style="color: #fff;">"Dự kiến bồn nước sẽ cạn sau 3 ngày nữa. Vui lòng châm thêm"</i>.
              </p>
            </div>
          </div>

          <div class="hy-rule-item">
            <div class="hy-rule-info">
              <h5>Ngắt bơm khi cạn nước</h5>
              <p>Failsafe: Tắt bơm ngay lập tức nếu bồn hết nước để chống cháy.</p>
            </div>
            <div class="hy-toggle active"></div>
          </div>

          <div class="hy-rule-item">
            <div class="hy-rule-info">
              <h5>Làm mát khẩn cấp</h5>
              <p>Nếu Nhiệt độ > 35°C, tự động tăng gấp đôi thời gian tưới.</p>
            </div>
            <div class="hy-toggle active"></div>
          </div>

          <div class="hy-rule-item">
            <div class="hy-rule-info">
              <h5>Trời mưa ngừng tưới</h5>
              <p>Liên kết API thời tiết: Nếu đang mưa, hệ thống ngừng tưới 24h.</p>
            </div>
            <div class="hy-toggle"></div>
          </div>

          <div class="hy-rule-item">
            <div class="hy-rule-info">
              <h5>Chế độ ban đêm</h5>
              <p>Giảm 50% tần suất tưới từ 22:00 đến 06:00 sáng.</p>
            </div>
            <div class="hy-toggle active"></div>
          </div>

          <button class="hy-btn secondary" style="width: 100%; justify-content: center; margin-top: 12px;">+ Thêm Kịch bản mới</button>
        </div>

      </div>
    </main>
  </div>

<?php
// Log query sử dụng $blynk_token đã lấy ở trên đầu file

$blynk_pump_pin = 'V1'; // Chân V-Pin mặc định cho máy bơm (TIME ON/OFF)

$logs = [];
if (function_exists('aitrongcay_blynk_get_pump_history') && $blynk_token !== '') {
    $logs = aitrongcay_blynk_get_pump_history($blynk_token, $blynk_pump_pin, 20);
}
?>
<!-- Lịch sử bơm Modal -->
<div id="hy-history-modal" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div style="background: var(--bg-2); border: 1px solid var(--panel-border); width: 100%; max-width: 500px; border-radius: 24px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #fff; font-size: 20px; font-weight: 800;">⏱ Lịch sử Máy bơm (Blynk)</h3>
      <button onclick="document.getElementById('hy-history-modal').style.display='none'" style="background: none; border: none; color: var(--muted); font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    
    <div id="hy-history-list" style="max-height: 400px; overflow-y: auto; padding-right: 8px;">
      <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $log): 
          // Database đang lưu UTC (hoặc lệch múi giờ). Dùng JS để format lại cho chắc, nhưng PHP cũng có thể cộng bù 7 tiếng.
          $time = date('H:i:s d/m/Y', strtotime($log['logged_at']) + 7 * 3600);
          $status = ($log['value'] === '1' || strtolower($log['value']) === 'on') ? 'Bật bơm' : 'Tắt bơm';
          $color = ($log['value'] === '1' || strtolower($log['value']) === 'on') ? 'var(--primary)' : 'var(--muted)';
        ?>
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--line);">
             <span style="color: <?php echo $color; ?>; font-weight: bold;"><?php echo $status; ?></span>
             <span style="color: var(--muted); font-size: 13px;"><?php echo $time; ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align: center; padding: 20px; color: var(--muted); font-size: 14px; background: rgba(255,255,255,0.02); border-radius: 12px;">
           <i>Đang chờ dữ liệu bơm từ Blynk Webhook...</i><br>
           <span style="font-size: 12px;">Hãy chắc chắn bạn đã cài Webhook cho Timer trong app Blynk.</span>
        </div>
      <?php endif; ?>
    </div>
    
    <div style="margin-top: 20px; text-align: center; display: flex; flex-direction: column; gap: 8px;">
      <p style="font-size: 12px; color: #6fdba8; margin: 0;">🟢 Đang tự động làm mới dữ liệu Real-time (3s/lần)</p>
    </div>
  </div>
</div>

</section>
<script>
  // Script xử lý Toggle (UI only)
  document.querySelectorAll('.hy-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function() {
      this.classList.toggle('active');
    });
  });

  // AJAX Polling for Real-time History
  let hyPollInterval = null;
  const hyModal = document.getElementById('hy-history-modal');
  const blynkToken = "<?php echo esc_js($blynk_token); ?>";
  const apiUrl = "<?php echo esc_url(rest_url('aitrongcay/v1/pump-history')); ?>";

  function fetchPumpHistory() {
    if (!blynkToken) return;
    
    fetch(`${apiUrl}?token=${blynkToken}&pin=V1`)
      .then(res => res.json())
      .then(data => {
        const listDiv = document.getElementById('hy-history-list');
        if (Array.isArray(data) && data.length > 0) {
          let html = '';
          data.forEach(log => {
             const isBật = (log.value === '1' || String(log.value).toLowerCase() === 'on');
             const status = isBật ? 'Bật bơm' : 'Tắt bơm';
             const color = isBật ? 'var(--primary)' : 'var(--muted)';
             // Format time (YYYY-MM-DD HH:mm:ss to HH:mm:ss DD/MM/YYYY)
             // Browser sẽ tự động đổi UTC sang Local Time
             let timeStr = log.logged_at;
             try {
                // Biến log.logged_at thành chuẩn ISO 'YYYY-MM-DDTHH:mm:ssZ' để browser hiểu đây là UTC
                const utcDateString = log.logged_at.replace(' ', 'T') + 'Z';
                const d = new Date(utcDateString);
                
                // Format thành HH:mm:ss DD/MM/YYYY
                const hh = String(d.getHours()).padStart(2, '0');
                const mm = String(d.getMinutes()).padStart(2, '0');
                const ss = String(d.getSeconds()).padStart(2, '0');
                const DD = String(d.getDate()).padStart(2, '0');
                const MM = String(d.getMonth() + 1).padStart(2, '0');
                const YYYY = d.getFullYear();
                
                timeStr = `${hh}:${mm}:${ss} ${DD}/${MM}/${YYYY}`;
             } catch(e) {}
             
             html += `
             <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--line);">
               <span style="color: ${color}; font-weight: bold;">${status}</span>
               <span style="color: var(--muted); font-size: 13px;">${timeStr}</span>
             </div>`;
          });
          listDiv.innerHTML = html;
        }
      })
      .catch(err => console.error('Error fetching pump history:', err));
  }

  // Khởi động Real-time poll khi mở Modal
  function openHyHistoryModal() {
    hyModal.style.display = 'flex';
    fetchPumpHistory(); // Gọi ngay 1 lần đầu
    if (hyPollInterval) clearInterval(hyPollInterval);
    hyPollInterval = setInterval(fetchPumpHistory, 3000); // 3 giây quét 1 lần
  }

  // Hủy quét khi đóng Modal
  function closeHyHistoryModal() {
    hyModal.style.display = 'none';
    if (hyPollInterval) {
      clearInterval(hyPollInterval);
      hyPollInterval = null;
    }
  }

  // TASK 3.1: Đồng bộ trạng thái nút Bơm (2 chiều) Real-time
  const btnPump = document.getElementById('hy-btn-pump-main');
  const isHardwareOnline = <?php echo $is_hardware_online ? 'true' : 'false'; ?>;
  
  if (isHardwareOnline && blynkToken) {
    setInterval(() => {
      fetch(`https://blynk.cloud/external/api/get?token=${blynkToken}&v1`)
        .then(res => res.text())
        .then(val => {
           // Cập nhật UI nút bấm dựa trên giá trị thực tế từ mạch IoT
           if (val.trim() === '1') {
             btnPump.style.background = '#ef4444';
             btnPump.style.color = '#fff';
             btnPump.style.borderColor = '#ef4444';
             btnPump.style.boxShadow = '0 0 15px rgba(239,68,68,0.4)';
             btnPump.innerHTML = '🛑 ĐANG BƠM (Bấm để Tắt)';
           } else {
             btnPump.style.background = '';
             btnPump.style.color = '';
             btnPump.style.borderColor = '';
             btnPump.style.boxShadow = '';
             btnPump.innerHTML = 'Bơm ngay 5 phút';
           }
        })
        .catch(err => console.error(err));
    }, 2000); // Quét 2 giây/lần cho cảm giác mượt mà
  }

  // Gắn sự kiện click cho nút mở modal (Thay vì onClick hardcode)
  const btnHistory = document.querySelector('button[onclick*="hy-history-modal"]');
  if (btnHistory) {
    btnHistory.removeAttribute('onclick');
    btnHistory.addEventListener('click', openHyHistoryModal);
  }
</script>


