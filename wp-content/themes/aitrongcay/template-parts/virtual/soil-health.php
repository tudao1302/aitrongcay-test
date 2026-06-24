<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
  exit;
}

$page = aitrongcay_current_virtual_page();
$slug = $page['slug'] ?? 'portal/soil-health';
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();

$garden_key = $is_logged_in ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$active_profile = $is_logged_in ? aitrongcay_portal_profile_for_garden_context($garden_key, $current_user instanceof WP_User ? $current_user : null) : null;
$owner_name = trim((string) ($active_profile['owner_name'] ?? ($current_user instanceof WP_User ? $current_user->display_name : 'Khách hàng')));

if (!$is_logged_in) {
  wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
  exit;
}

$pots = function_exists('aitrongcay_portal_pots') ? aitrongcay_portal_pots($garden_key, $current_user instanceof WP_User ? $current_user : null) : [];

$friends_url = add_query_arg(['garden' => $garden_key], home_url('/portal/hang-xom/'));
$photo_library_url = $garden_key !== '' ? add_query_arg('garden', rawurlencode($garden_key), home_url('/portal/nhat-ky-cham-soc/')) : home_url('/portal/nhat-ky-cham-soc/');
$flower_bio_url = add_query_arg(['garden' => $garden_key], home_url('/portal/flower-bio/'));

$shared_top_links = [
  ['key' => 'cho-que', 'label' => 'Chợ quê', 'url' => home_url('/cho-que/')],
  ['key' => 'kho-nong-cu', 'label' => 'Kho nông cụ', 'url' => home_url('/portal/kho-nong-cu-2/')],
  ['key' => 'hang-xom', 'label' => 'Hàng xóm', 'url' => home_url('/portal/hang-xom/')],
  ['key' => 'dashboard-2', 'label' => 'Vào khu vườn của tôi', 'url' => home_url('/portal/dashboard-2/')],
];
foreach ($shared_top_links as &$shared_top_link) {
  if ($garden_key !== '' && in_array($shared_top_link['key'], ['kho-nong-cu', 'hang-xom', 'dashboard-2'], true)) {
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
      --panel: rgba(22, 26, 22, 0.6);
      --primary: #6fdba8;
      --text: #e3e3de;
      --muted: #bdcac0;
      --line: rgba(255, 255, 255, 0.05);
      font-family: 'Manrope', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      margin: -32px calc(50% - 50vw) 0;
      padding: 0;
      overflow-x: hidden;
    }

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

    .d2-brand { font-family: 'Noto Serif', serif; font-size: 34px; font-style: italic; color: var(--primary); margin: 2px 12px 28px; }
    
    .d2-level { display: flex; gap: 14px; align-items: center; margin: 0 8px 14px; padding: 12px 14px; border-radius: 24px; background: rgba(111, 219, 168, .08); border: 1px solid rgba(255, 255, 255, .06); }
    .d2-level-badge { width: 44px; height: 44px; border-radius: 16px; background: rgba(111, 219, 168, .14); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 800; }
    
    .d2-upgrade { display: flex; align-items: center; gap: 14px; margin: 0 6px 8px; padding: 16px 18px; border-radius: 20px; color: rgba(227, 227, 222, .46); font-weight: 600; text-decoration: none; transition: all .18s; }
    .d2-upgrade:hover { color: var(--primary); }

    .d2-nav { display: grid; gap: 8px; padding: 0 6px; }
    .d2-nav a { display: flex; align-items: center; gap: 14px; padding: 16px 18px; border-radius: 20px; color: rgba(227, 227, 222, .46); font-weight: 600; text-decoration: none; transition: all .18s; }
    .d2-nav .bottom-nav-short { display: none; }
    .d2-nav a:hover { color: var(--primary); }
    .d2-nav a.active { background: rgba(6, 77, 58, .7); color: var(--primary); box-shadow: inset 0 0 0 1px rgba(111, 219, 168, .08); }

    .d2-side-footer { margin-top: auto; padding: 24px 16px 0; color: rgba(227, 227, 222, .5); display: grid; gap: 10px; font-size: 14px; }

    .d2-main {
      padding: 30px 28px 40px;
      background: radial-gradient(circle at top right, rgba(139, 107, 74, .08), transparent 24%), radial-gradient(circle at top left, rgba(160, 200, 100, .05), transparent 22%), var(--bg);
      display: flex;
      flex-direction: column;
      gap: 24px;
    }

    .d2-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; position: relative; }
    .d2-hamburger { display: none; background: rgba(111, 219, 168, 0.1); border: 1px solid rgba(111, 219, 168, 0.2); border-radius: 12px; color: var(--primary); cursor: pointer; padding: 8px; }
    .d2-top-links { display: flex; gap: 18px; }
    .d2-top-links a { color: var(--muted); text-decoration: none; font-weight: 600; transition: color 0.2s; }
    .d2-top-links a:hover { color: var(--primary); }

    .sh-title { font-size: 28px; font-weight: 800; font-family: 'Noto Serif', serif; margin: 0 0 4px; color: #d6b08e; }
    .sh-subtitle { color: var(--muted); font-size: 15px; margin: 0; }

    .sh-grid { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
    
    .sh-card {
      background: var(--panel);
      border: 1px solid rgba(139, 107, 74, 0.15);
      border-radius: 24px;
      padding: 24px;
      position: relative;
    }
    
    .sh-card-title { font-size: 16px; font-weight: 700; color: #fff; margin: 0 0 20px; display: flex; align-items: center; gap: 8px; }
    
    .sh-vital-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .sh-vital-box { background: rgba(0,0,0,0.3); padding: 16px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.03); text-align: center; }
    .sh-vital-val { font-size: 28px; font-weight: 800; margin: 8px 0 4px; font-variant-numeric: tabular-nums; }
    .sh-vital-label { font-size: 13px; color: var(--muted); }

    @media (max-width: 1024px) {
      .d2-shell { grid-template-columns: 1fr; }
      .d2-side {
        position: fixed; left: 12px; right: 12px; bottom: 16px; top: auto; z-index: 70;
        border-radius: 26px; padding: 11px 12px;
      }
      .d2-brand, .d2-level, .d2-upgrade, .d2-side-footer { display: none; }
      .d2-nav { grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 4px; padding: 0; }
      .d2-nav a { flex-direction: column; padding: 8px 4px; border-radius: 14px; font-size: 9px; line-height: 1.15; gap: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; justify-content: center; text-align: center; }
      .d2-nav .bottom-nav-label { display: none; }
      .d2-nav .bottom-nav-short { display: block; }
      .sh-grid { grid-template-columns: 1fr; }
      
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
    <aside class="d2-side">
      <div class="d2-brand">Ai trồng cây</div>
      <div class="d2-level">
        <div class="d2-level-badge">🛡</div>
        <div>
          <div style="font-weight:800;color:var(--primary)">Level 42</div>
          <div style="font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:rgba(227,227,222,.46)">
            <?php echo esc_html($owner_name); ?></div>
        </div>
      </div>
      <a class="d2-upgrade" href="<?php echo esc_url($friends_url); ?>">👥 Hàng xóm</a>
      <nav class="d2-nav">
        <a href="<?php echo esc_url($photo_library_url); ?>"><span class="bottom-nav-icon" aria-hidden="true">🖼</span><span class="bottom-nav-label">Galleries</span><span class="bottom-nav-short">Ảnh</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/hydration/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">💧</span><span class="bottom-nav-label">Hydration</span><span class="bottom-nav-short">Nước</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key], home_url('/portal/soil-health/'))); ?>" class="active"><span class="bottom-nav-icon" aria-hidden="true">🌿</span><span class="bottom-nav-label">Soil Health</span><span class="bottom-nav-short">Giá thể</span></a>
        <a href="<?php echo esc_url(add_query_arg(['garden' => $garden_key, 'view' => 'timelapse'], home_url('/portal/dashboard-2/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">📽</span><span class="bottom-nav-label">Timelapse</span><span class="bottom-nav-short">TL</span></a>
        <a href="<?php echo esc_url($flower_bio_url); ?>"><span class="bottom-nav-icon" aria-hidden="true">🍃</span><span class="bottom-nav-label">Bách thảo</span><span class="bottom-nav-short">Bách thảo</span></a>
        <a href="<?php echo esc_url(add_query_arg(array_filter(['garden' => $garden_key]), home_url('/portal/kho-nong-cu-2/'))); ?>"><span class="bottom-nav-icon" aria-hidden="true">🗄</span><span class="bottom-nav-label">Seed Bank</span><span class="bottom-nav-short">Kho</span></a>
      </nav>
      <div class="d2-side-footer">
        <div>🤖 AI Status: Active</div>
        <div>🌱 <?php echo esc_html((string) count($pots)); ?> khoang đang theo dõi</div>
      </div>
    </aside>

    <main class="d2-main">
      <div class="d2-top">
        <div>
          <h1 class="sh-title">🌿 Soil Health Center</h1>
          <p class="sh-subtitle">Quản lý vòng đời và dinh dưỡng hệ vi sinh Giá thể / Đất trồng.</p>
        </div>
        <button class="d2-hamburger" type="button" aria-label="Menu" aria-expanded="false" onclick="document.getElementById('shTopMenu').classList.toggle('is-open');">
          <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div class="d2-top-links" id="shTopMenu">
          <?php foreach ($shared_top_links as $top_link): ?>
            <a href="<?php echo esc_url($top_link['url']); ?>"><?php echo esc_html($top_link['label']); ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="sh-grid">
        <div style="display: flex; flex-direction: column; gap: 24px;">
          <div class="sh-card">
            <h3 class="sh-card-title">🟤 Chỉ số Sinh hiệu Trung bình Khu vườn</h3>
            <?php
            $avg_moisture = 0;
            $avg_ec = 0;
            $avg_temp = 0;
            $valid_pots = 0;
            
            foreach ($pots as $pot) {
                // Giả lập dữ liệu nếu chưa có cảm biến thực tế, lấy từ DB nếu có
                $moisture_val = (isset($pot['soil_moisture']) && (float)$pot['soil_moisture'] > 0) ? $pot['soil_moisture'] : (60 + abs(crc32($pot['code'] ?? '')) % 30);
                $ec_val = (isset($pot['soil_ec']) && (float)$pot['soil_ec'] > 0) ? $pot['soil_ec'] : (1.0 + (abs(crc32($pot['code'] ?? '')) % 10) / 10);
                $temp_val = (isset($pot['soil_temp']) && (float)$pot['soil_temp'] > 0) ? $pot['soil_temp'] : ((isset($pot['temperature']) && (float)$pot['temperature'] > 0) ? $pot['temperature'] : (24 + abs(crc32($pot['code'] ?? '')) % 6));

                $moisture = (float) str_replace('%', '', (string) $moisture_val);
                $ec = (float) str_replace([' µS', ' mS'], '', (string) $ec_val);
                $temp = (float) str_replace('°C', '', (string) $temp_val);
                
                $avg_moisture += $moisture;
                $avg_ec += $ec;
                $avg_temp += $temp;
                $valid_pots++;
            }
            
            if ($valid_pots > 0) {
                $avg_moisture = round($avg_moisture / $valid_pots);
                $avg_ec = number_format($avg_ec / $valid_pots, 1);
                $avg_temp = round($avg_temp / $valid_pots, 1);
            } else {
                $avg_moisture = 72;
                $avg_ec = 1.4;
                $avg_temp = 26;
            }
            ?>
            <div class="sh-vital-grid">
              <div class="sh-vital-box">
                <div class="sh-vital-label">Độ ẩm Giá thể</div>
                <div class="sh-vital-val" style="color: #60a5fa;"><?php echo $avg_moisture; ?>%</div>
                <div style="font-size: 11px; color: var(--muted);"><?php echo $avg_moisture > 60 && $avg_moisture < 80 ? 'Tối ưu' : 'Cần chú ý'; ?></div>
              </div>
              <div class="sh-vital-box">
                <div class="sh-vital-label">Dinh dưỡng (EC)</div>
                <div class="sh-vital-val" style="color: #ffb68c;"><?php echo $avg_ec; ?> <span style="font-size: 14px;">mS</span></div>
                <div style="font-size: 11px; color: var(--muted);"><?php echo $avg_ec >= 1.0 && $avg_ec <= 2.0 ? 'Bình thường' : 'Mất cân bằng'; ?></div>
              </div>
              <div class="sh-vital-box">
                <div class="sh-vital-label">Nhiệt độ nền</div>
                <div class="sh-vital-val" style="color: #6fdba8;"><?php echo $avg_temp; ?>°C</div>
                <div style="font-size: 11px; color: var(--muted);"><?php echo $avg_temp < 28 ? 'Mát mẻ' : 'Hơi nóng'; ?></div>
              </div>
            </div>
          </div>
          
          <div class="sh-card">
            <h3 class="sh-card-title">⏳ Vòng đời Giá thể (Lifespan)</h3>
            <?php 
            if (empty($pots)) {
              echo '<div style="text-align: center; padding: 20px; color: var(--muted); font-size: 13px;">Chưa có dữ liệu khoang trồng.</div>';
            }
            foreach ($pots as $index => $pot): 
              $p_code = $pot['code'] ?? $pot['pot_code'] ?? '';
              $p_name = $pot['name'] ?? $pot['pot_name'] ?? 'Khoang chưa đặt tên';
              
              // Giả lập tính toán vòng đời dựa trên thời gian tạo khoang
              $created_time = strtotime($pot['created_at'] ?? 'now');
              $days_active = max(1, round((time() - $created_time) / 86400));
              if ($days_active < 2) $days_active = 14 + ($index * 15); // Fallback data
              
              $total_days = 90; // Typical lifespan of coco peat
              $percent = min(100, round(($days_active / $total_days) * 100));
              
              $color = '#6fdba8';
              $status_text = "Đang trồng ($days_active ngày)";
              $warning = '';
              
              if ($percent > 80) {
                  $color = '#ef4444';
                  $status_text = "Đã dùng lâu ($days_active ngày)";
                  $warning = 'Cảnh báo: Giá thể đang có dấu hiệu mục, độ xốp giảm sút. Nên trộn thêm vỏ trấu hoặc thay mới ở vụ sau.';
              } elseif ($percent > 40) {
                  $color = '#ffb68c';
              }
            ?>
            <div style="margin-bottom: 16px;">
              <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin-bottom: 8px;">
                <span><?php echo esc_html($p_name); ?> (<?php echo esc_html($p_code); ?>)</span>
                <span style="color: <?php echo $color; ?>;"><?php echo esc_html($status_text); ?></span>
              </div>
              <div style="height: 12px; background: rgba(0,0,0,0.4); border-radius: 6px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php if($percent > 80) echo 'linear-gradient(90deg, #ffb68c, #ef4444)'; else echo $color; ?>;"></div>
              </div>
              <?php if ($warning): ?>
              <div style="font-size: 12px; color: rgba(227,227,222,0.5); margin-top: 8px;">
                <?php echo esc_html($warning); ?>
              </div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          <!-- Bác sĩ Đất -->
          <div class="sh-card" style="background: linear-gradient(145deg, rgba(22,26,22,0.9), rgba(111,219,168,0.05)); border-left: 3px solid #6fdba8;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
              <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(111,219,168,0.2); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">👩‍🔬</div>
              <div>
                <h3 class="sh-card-title" style="margin-bottom: 6px; font-size: 16px;">Cindy - Bác sĩ Đất</h3>
                <?php
                $has_perlite = false;
                $all_tools = function_exists('aitrongcay_supplies_for_linking') ? aitrongcay_supplies_for_linking() : [];
                foreach ($all_tools as $t) {
                    $tn = mb_strtolower($t['name'] ?? '');
                    if (strpos($tn, 'perlite') !== false || strpos($tn, 'trấu') !== false) $has_perlite = true;
                }
                ?>
                <p style="font-size: 13px; color: var(--text); line-height: 1.5; margin: 0;">
                  "Phân tích sinh hiệu cho thấy một số khoang đang có dấu hiệu nén chặt. 
                  <?php if ($has_perlite): ?>
                  Trong kho của anh hiện <strong>đang có sẵn Đá Perlite/Vỏ trấu</strong>, em đề xuất trộn thêm 20% vào giá thể ở vụ tiếp theo để tăng độ tơi xốp và hô hấp cho rễ nhé!"
                  <?php else: ?>
                  Em đề xuất anh nên bổ sung thêm <strong>Đá Perlite hoặc Vỏ trấu hun</strong> vào kho và trộn thêm 20% vào khoang để giúp đất thoát nước tốt hơn nhé!"
                  <?php endif; ?>
                </p>
                <div style="margin-top: 12px; display: flex; gap: 8px;">
                  <button style="padding: 6px 12px; background: rgba(111,219,168,0.1); border: 1px solid rgba(111,219,168,0.3); color: #6fdba8; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 700;">✅ Chấp nhận kê đơn</button>
                  <button style="padding: 6px 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--muted); border-radius: 8px; font-size: 12px; cursor: pointer;">Bỏ qua</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="sh-card">
          <h3 class="sh-card-title">🦠 Nhật ký Vi sinh (Microbiome)</h3>
          <p style="font-size: 13px; color: var(--muted); margin-bottom: 16px;">
            Ghi chép và theo dõi sự phát triển của hệ vi sinh vật có lợi trong giá thể trồng của bạn.
          </p>
          
          <?php
          $soil_logs = function_exists('aitrongcay_soil_get_logs') ? aitrongcay_soil_get_logs($garden_key) : [];
          
          // Tính toán Microbiome Index
          $mb_score = 30; 
          $conflict_warning = '';
          if (!empty($soil_logs)) {
              foreach ($soil_logs as $log) {
                  $days = (time() - strtotime($log['created_at'])) / 86400;
                  if ($days < 30) {
                      $mb_score += max(0, 30 - $days) * 1.5; 
                  }
                  $lname = mb_strtolower($log['supply_name']);
                  if (strpos($lname, 'hóa học') !== false || strpos($lname, 'trừ nấm') !== false || strpos($lname, 'diệt') !== false) {
                      $conflict_warning = 'Cảnh báo: Bạn vừa dùng hóa chất/thuốc trừ nấm gần đây. Nó sẽ tiêu diệt hệ vi sinh Trichoderma/EM hữu ích có trong đất!';
                      $mb_score -= 40;
                  }
              }
          }
          $mb_score = min(100, max(0, (int) round($mb_score)));
          $mb_color = $mb_score > 70 ? '#6fdba8' : ($mb_score > 40 ? '#ffb68c' : '#ef4444');
          ?>
          <div style="margin-bottom: 24px; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 12px; border: 1px solid var(--line);">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
              <div>
                <div style="font-size: 13px; color: var(--muted); margin-bottom: 4px;">Mật độ vi sinh có lợi</div>
                <div style="font-size: 24px; font-weight: 800; color: <?php echo $mb_color; ?>;"><?php echo $mb_score; ?> <span style="font-size: 14px; font-weight: normal;">/ 100</span></div>
              </div>
              <div style="font-size: 24px;">🧫</div>
            </div>
            <div style="height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-bottom: 12px;">
              <div style="height: 100%; width: <?php echo $mb_score; ?>%; background: <?php echo $mb_color; ?>; transition: width 1s ease;"></div>
            </div>
            
            <?php if ($conflict_warning): ?>
            <div style="padding: 10px; background: rgba(239,68,68,0.1); border: 1px dashed rgba(239,68,68,0.4); border-radius: 8px; display: flex; gap: 8px; align-items: flex-start;">
              <span style="font-size: 16px;">⚠️</span>
              <span style="font-size: 12px; color: #ef4444; line-height: 1.4;"><?php echo esc_html($conflict_warning); ?></span>
            </div>
            <?php elseif ($mb_score > 70): ?>
            <div style="font-size: 12px; color: #6fdba8;">Hệ vi sinh đang hoạt động rất mạnh mẽ, phân giải hữu cơ tốt.</div>
            <?php else: ?>
            <div style="font-size: 12px; color: var(--muted);">Đất đang nghèo vi sinh, cần bổ sung thêm Trichoderma hoặc EM.</div>
            <?php endif; ?>
          </div>
          
          <div id="sh-logs-container">
            <?php
            if (!empty($soil_logs)):
              foreach ($soil_logs as $log):
                $time_diff = human_time_diff(strtotime($log['created_at']), current_time('timestamp')) . ' trước';
            ?>
              <div style="background: rgba(111,219,168,0.1); border: 1px dashed rgba(111,219,168,0.3); padding: 16px; border-radius: 12px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between;">
                  <div style="font-weight: 700; color: #6fdba8; font-size: 14px; margin-bottom: 4px;"><?php echo esc_html($log['supply_name']); ?> (<?php echo esc_html($log['amount_label']); ?>)</div>
                  <div style="font-size: 11px; color: var(--muted);"><?php echo esc_html($log['pot_code']); ?></div>
                </div>
                <div style="font-size: 12px; color: var(--muted);">Bổ sung lần cuối: <?php echo esc_html($time_diff); ?></div>
                <?php if (!empty($log['notes'])): ?>
                  <div style="font-size: 12px; color: rgba(227,227,222,0.6); margin-top: 6px; font-style: italic;">"<?php echo esc_html($log['notes']); ?>"</div>
                <?php endif; ?>
              </div>
            <?php
              endforeach;
            else:
            ?>
              <div style="text-align: center; padding: 20px; color: var(--muted); font-size: 13px;">Chưa có nhật ký nào được ghi lại.</div>
            <?php endif; ?>
          </div>
          
          <button onclick="document.getElementById('sh-add-log-modal').style.display='flex'" style="width: 100%; padding: 12px; background: rgba(139, 107, 74, 0.2); color: #d6b08e; border: 1px solid rgba(139, 107, 74, 0.4); border-radius: 12px; font-weight: 700; cursor: pointer; margin-top: 10px;">
            + Bổ sung chế phẩm
          </button>
        </div>
      </div>
    </main>
  </div>
</section>

<!-- Add Log Modal -->
<div id="sh-add-log-modal" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div style="background: #161a16; border: 1px solid rgba(139, 107, 74, 0.2); width: 100%; max-width: 450px; border-radius: 24px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h3 style="margin: 0; color: #fff; font-size: 20px; font-weight: 800;">🦠 Ghi chú Bổ sung</h3>
      <button onclick="document.getElementById('sh-add-log-modal').style.display='none'" style="background: none; border: none; color: #bdcac0; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    
    <form id="sh-add-log-form">
      <div id="sh-form-msg" style="display: none; padding: 12px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; font-weight: 600; text-align: center;"></div>
      
      <input type="hidden" name="action" value="aitrongcay_soil_add_log">
      <input type="hidden" name="garden_key" value="<?php echo esc_attr($garden_key); ?>">
      
      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Áp dụng cho Khoang</label>
        <select name="pot_code" required style="width: 100%; background: #0c0d0c; border: 1px solid var(--line); color: #fff; padding: 12px; border-radius: 12px;">
          <?php foreach ($pots as $pot): 
              $p_code = $pot['code'] ?? $pot['pot_code'] ?? '';
              $p_name = $pot['name'] ?? $pot['pot_name'] ?? 'Khoang chưa đặt tên';
          ?>
            <option value="<?php echo esc_attr($p_code); ?>"><?php echo esc_html($p_name); ?> (<?php echo esc_html($p_code); ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Loại chế phẩm</label>
        <select name="supply_code" id="sh-supply-select" required style="width: 100%; background: #0c0d0c; border: 1px solid var(--line); color: #fff; padding: 12px; border-radius: 12px;" onchange="document.getElementById('sh-supply-name').value = this.options[this.selectedIndex].text;">
          <?php 
          $db_supplies = function_exists('aitrongcay_supplies_for_linking') ? aitrongcay_supplies_for_linking() : [];
          $filtered_supplies = [];
          $valid_keywords = ['phân', 'dinh', 'chế phẩm', 'vi sinh', 'đất', 'giá thể', 'dịch', 'trùn quế', 'nấm', 'khoáng'];
          
          foreach ($db_supplies as $sup) {
              $sup_type = mb_strtolower((string)($sup['type'] ?? ''));
              $sup_name = mb_strtolower((string)($sup['name'] ?? ''));
              $is_valid = false;
              
              foreach ($valid_keywords as $kw) {
                  if (strpos($sup_type, $kw) !== false || strpos($sup_name, $kw) !== false) {
                      $is_valid = true;
                      break;
                  }
              }
              
              // Loại trừ nếu vẫn dính từ khóa hạt giống, khoang, thiết bị
              if (strpos($sup_type, 'hạt') !== false || strpos($sup_name, 'hạt giống') !== false) $is_valid = false;
              if (strpos($sup_type, 'thiết bị') !== false || strpos($sup_name, 'đèn') !== false || strpos($sup_type, 'khoang') !== false) $is_valid = false;
              
              if ($is_valid) {
                  $filtered_supplies[] = $sup;
              }
          }

          if (!empty($filtered_supplies)):
              foreach ($filtered_supplies as $sup):
          ?>
            <option value="<?php echo esc_attr($sup['code'] ?? $sup['id'] ?? ''); ?>"><?php echo esc_html($sup['name']); ?> (<?php echo esc_html($sup['type'] ?? 'Vật tư'); ?>)</option>
          <?php 
              endforeach;
          else: 
          ?>
            <option value="tricho-01">Trichoderma (Nấm đối kháng)</option>
            <option value="em-01">Chế phẩm EM (Vi sinh vật hữu hiệu)</option>
            <option value="dich-trun">Dịch Trùn Quế</option>
            <option value="phan-huu-co">Phân hữu cơ / Phân chuồng ủ hoai</option>
            <option value="vo-trau">Vỏ trấu hun / Đá Perlite (Làm xốp)</option>
          <?php endif; ?>
        </select>
        <input type="hidden" name="supply_name" id="sh-supply-name" value="Trichoderma (Nấm đối kháng)">
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Liều lượng</label>
        <input type="text" name="amount_label" placeholder="VD: 200g, 50ml, 1 nắm..." required style="width: 100%; background: #0c0d0c; border: 1px solid var(--line); color: #fff; padding: 12px; border-radius: 12px; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px;">Ghi chú thêm</label>
        <textarea name="notes" placeholder="VD: Bổ sung sau khi thu hoạch vụ trước..." rows="2" style="width: 100%; background: #0c0d0c; border: 1px solid var(--line); color: #fff; padding: 12px; border-radius: 12px; box-sizing: border-box; resize: none;"></textarea>
      </div>

      <button type="submit" id="sh-submit-btn" style="width: 100%; padding: 14px; background: #6fdba8; color: #030504; border: none; border-radius: 12px; font-weight: 800; font-size: 15px; cursor: pointer;">
        Lưu Nhật ký
      </button>
    </form>
  </div>
</div>

<script>
function shShowMsg(text, isError) {
  const msgDiv = document.getElementById('sh-form-msg');
  msgDiv.style.display = 'block';
  msgDiv.innerText = text;
  if (isError) {
    msgDiv.style.background = 'rgba(239, 68, 68, 0.1)';
    msgDiv.style.color = '#ef4444';
    msgDiv.style.border = '1px solid rgba(239, 68, 68, 0.3)';
  } else {
    msgDiv.style.background = 'rgba(111, 219, 168, 0.15)';
    msgDiv.style.color = '#6fdba8';
    msgDiv.style.border = '1px solid rgba(111, 219, 168, 0.4)';
  }
}

document.getElementById('sh-add-log-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const btn = document.getElementById('sh-submit-btn');
  btn.disabled = true;
  btn.innerText = 'Đang lưu...';
  
  // Ẩn message cũ nếu có
  document.getElementById('sh-form-msg').style.display = 'none';

  // Cập nhật lại supply_name chuẩn xác theo giá trị đang chọn
  const select = document.getElementById('sh-supply-select');
  if (select) {
      document.getElementById('sh-supply-name').value = select.options[select.selectedIndex].text;
  }

  const formData = new FormData(form);

  fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      shShowMsg('✅ Đã lưu thành công! Đang tải lại...', false);
      setTimeout(() => window.location.reload(), 1200);
    } else {
      shShowMsg('⚠️ ' + (res.data.message || 'Lỗi hệ thống!'), true);
      btn.disabled = false;
      btn.innerText = 'Lưu Nhật ký';
    }
  })
  .catch(err => {
    shShowMsg('❌ Lỗi mạng, vui lòng thử lại sau!', true);
    btn.disabled = false;
    btn.innerText = 'Lưu Nhật ký';
  });
});
</script>
