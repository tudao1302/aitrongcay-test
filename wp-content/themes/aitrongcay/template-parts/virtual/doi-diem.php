<?php
declare(strict_types=1);

if (! is_user_logged_in()) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

$current_user   = wp_get_current_user();
$garden_key     = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';
$current_points = (int) get_user_meta($current_user->ID, '_aitrongcay_eco_points', true);
$current_level  = function_exists('aitrongcay_calculate_level') ? aitrongcay_calculate_level($current_points) : 1;
$history        = array_filter((array) get_user_meta($current_user->ID, '_aitrongcay_redeem_history', true), function($h) {
    return !empty($h['reward_id']) && !empty($h['points']);
});

// Dynamic Reward catalogue from admin panel
$catalogue = function_exists('aitrongcay_eco_reward_catalogue') ? aitrongcay_eco_reward_catalogue() : [];

$rewards = [];
$default_extras = [
    'rau_baby_mix'   => ['desc' => 'Hộp rau mầm hỗn hợp tươi ngon, thu hoạch trong ngày từ vườn thủy canh.', 'color' => '#31a375', 'badge' => 'Phổ biến'],
    'rau_cai_xanh'   => ['desc' => 'Cải xanh trồng thủy canh không thuốc trừ sâu, đảm bảo an toàn cho cả nhà.', 'color' => '#4caf50', 'badge' => ''],
    'rau_xalach'     => ['desc' => 'Xà lách giòn, ngọt, phù hợp cho salad, cuốn hay ăn sống đều tuyệt.', 'color' => '#8bc34a', 'badge' => ''],
    'goi_combo_vuon' => ['desc' => 'Bộ rau tổng hợp 1kg gồm cải, xà lách, rau thơm — đủ dùng cho cả tuần.', 'color' => '#ff9800', 'badge' => 'Giá trị'],
    'voucher_10k'    => ['desc' => 'Mã giảm giá áp dụng cho đơn hàng tiếp theo tại Chợ quê Ai trồng cây.', 'color' => '#9c27b0', 'badge' => 'Nhanh nhất'],
    'voucher_50k'    => ['desc' => 'Mã giảm giá lớn cho đơn hàng từ 150.000đ trở lên tại Chợ quê.', 'color' => '#e91e63', 'badge' => ''],
];
$fallback_colors = ['#31a375', '#4caf50', '#8bc34a', '#ff9800', '#9c27b0', '#e91e63', '#2196f3', '#00bcd4'];
$color_idx = 0;

foreach ($catalogue as $id => $item) {
    $extras = $default_extras[$id] ?? [
        'desc'  => 'Phần thưởng hấp dẫn từ hệ sinh thái Ai Trồng Cây.',
        'color' => $fallback_colors[$color_idx % count($fallback_colors)],
        'badge' => ''
    ];
    $rewards[] = [
        'id'     => $id,
        'name'   => $item['name'] ?? '',
        'desc'   => $extras['desc'],
        'points' => (int)($item['points'] ?? 0),
        'icon'   => $item['icon'] ?? '🎁',
        'color'  => $extras['color'],
        'badge'  => $extras['badge'],
        'stock'  => (int)($item['stock'] ?? 0),
    ];
    $color_idx++;
}

$nav_items = [
    ['key' => 'doi-diem',        'label' => 'Cửa hàng đổi điểm', 'icon' => '🏪', 'url' => home_url('/portal/doi-diem/')],
    ['key' => 'lich-su-doi',     'label' => 'Lịch sử đổi thưởng', 'icon' => '📜', 'url' => home_url('/portal/doi-diem/#lich-su')],
    ['key' => 'tai-khoan',       'label' => 'Tài khoản',          'icon' => '👤', 'url' => home_url('/tai-khoan/')],
    ['key' => 'dashboard-2',     'label' => 'Khu vườn',           'icon' => '🌱', 'url' => home_url('/portal/dashboard-2/')],
];

foreach ($nav_items as &$item) {
    if ($garden_key !== '') {
        $item['url'] = add_query_arg('garden', $garden_key, $item['url']);
    }
}
unset($item);

set_query_var('aitr_eco_shell', [
    'title'       => 'Đổi điểm',
    'active'      => 'doi-diem',
    'side_title'  => 'Eco Points',
    'side_subtitle'=> 'Đổi điểm lấy phần thưởng',
    'side_badge'  => '🌿',
    'top_icons'   => ['🔔', '⚙️'],
    'search'      => null,
    'nav'         => $nav_items,
]);
get_template_part('template-parts/site/eco-shell-start');
?>
<style>
.site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
.doi-diem-layout{display:flex;gap:32px;max-width:1140px;margin:0 auto;padding:28px;align-items:flex-start}
.doi-diem-main{flex:1;min-width:0}
.doi-diem-sidebar{width:340px;flex-shrink:0;position:sticky;top:28px}
.doi-diem-hero{margin-bottom:32px}
.doi-diem-points-banner{background:linear-gradient(135deg,rgba(49,163,117,.18),rgba(255,225,109,.06));border:1px solid rgba(111,219,168,.2);border-radius:28px;padding:28px 32px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;margin-bottom:32px}
.doi-diem-points-left h2{margin:0 0 4px;font-family:'Noto Serif',serif;font-size:36px;color:#fff}
.doi-diem-points-left p{margin:0;color:#bdcac0;font-size:14px}
.doi-diem-points-num{font-family:'Noto Serif',serif;font-size:64px;font-weight:900;color:#ffe16d;line-height:1;text-shadow:0 0 40px rgba(255,225,109,.35)}
.doi-diem-points-unit{font-size:20px;color:#6fdba8;font-weight:800;margin-left:6px}
.doi-diem-section-title{font-family:'Noto Serif',serif;font-size:28px;color:#fff;margin:0 0 20px}
.reward-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;margin-bottom:40px}
.reward-card{background:rgba(26,28,25,.92);border:1px solid rgba(255,255,255,.06);border-radius:28px;padding:24px;display:flex;flex-direction:column;gap:12px;transition:.25s;position:relative;overflow:hidden}
.reward-card:hover{transform:translateY(-4px);box-shadow:0 24px 52px rgba(0,0,0,.3);border-color:rgba(111,219,168,.2)}
.reward-card-badge{position:absolute;top:16px;right:16px;background:rgba(255,225,109,.15);color:#ffe16d;font-size:10px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;padding:4px 10px;border-radius:999px;border:1px solid rgba(255,225,109,.25)}
.reward-icon{width:64px;height:64px;border-radius:20px;display:grid;place-items:center;font-size:32px;flex:0 0 auto}
.reward-name{font-family:'Noto Serif',serif;font-size:20px;color:#fff;margin:0}
.reward-desc{color:#bdcac0;font-size:13px;line-height:1.7;margin:0;flex:1}
.reward-points{display:flex;align-items:center;gap:8px}
.reward-points-num{font-size:28px;font-weight:900;color:#ffe16d;font-family:'Noto Serif',serif}
.reward-points-label{font-size:12px;color:#6fdba8;font-weight:700;text-transform:uppercase;letter-spacing:.1em}
.reward-btn{width:100%;padding:14px;border:none;border-radius:16px;font-weight:900;font-size:15px;cursor:pointer;transition:.2s;letter-spacing:.02em}
.reward-btn.can-redeem{background:linear-gradient(135deg,#31a375,#6fdba8);color:#003824}
.reward-btn.can-redeem:hover{opacity:.9;transform:scale(1.02)}
.reward-btn.cannot-redeem{background:rgba(51,53,50,.6);color:rgba(227,227,222,.3);cursor:not-allowed}
.reward-stock{font-size:11px;color:#7a827b;text-align:right}
.history-table{width:100%;border-collapse:collapse}
.history-table th{text-align:left;padding:12px 16px;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#7a827b;border-bottom:1px solid rgba(255,255,255,.05)}
.history-table td{padding:14px 16px;border-bottom:1px solid rgba(255,255,255,.04);color:#e3e3de;font-size:14px}
.history-table tr:last-child td{border-bottom:none}
.history-badge{display:inline-flex;padding:4px 10px;border-radius:999px;font-size:11px;font-weight:800}
.history-badge.pending{background:rgba(255,152,0,.12);color:#ff9800}
.history-badge.confirmed{background:rgba(111,219,168,.12);color:#6fdba8}
.history-badge.delivered{background:rgba(49,163,117,.18);color:#31a375}
/* Daily Missions */
.mission-box{background:rgba(26,28,25,.6);border:1px solid rgba(111,219,168,.15);border-radius:24px;padding:24px;box-shadow:0 12px 40px rgba(0,0,0,.2)}
.mission-box-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.mission-box-title{font-family:'Noto Serif',serif;font-size:22px;color:#fff;margin:0}
.mission-box-count{background:rgba(49,163,117,.2);color:#6fdba8;font-size:12px;font-weight:800;padding:4px 10px;border-radius:12px}
.mission-card{background:rgba(26,28,25,.92);border:1px solid rgba(255,255,255,.06);border-radius:18px;padding:16px;margin-bottom:12px;display:flex;flex-direction:column;gap:12px;text-decoration:none;transition:.2s;position:relative;overflow:hidden}
.mission-card:last-child{margin-bottom:0}
.mission-card:hover{background:rgba(35,38,34,1);border-color:rgba(111,219,168,.3);transform:translateY(-2px)}
.mission-card.is-completed{border-color:rgba(111,219,168,.4);background:rgba(49,163,117,.08)}
.mission-card-top{display:flex;gap:12px;align-items:center}
.mission-icon{width:48px;height:48px;border-radius:14px;background:rgba(51,53,50,.5);display:grid;place-items:center;font-size:24px;flex-shrink:0}
.mission-info{flex:1}
.mission-title{color:#fff;font-size:14px;font-weight:700;margin:0 0 4px}
.mission-reward{color:#ffe16d;font-size:13px;font-weight:800}
.mission-progress-bar{height:6px;background:rgba(255,255,255,.1);border-radius:999px;overflow:hidden;position:relative}
.mission-progress-fill{position:absolute;top:0;left:0;bottom:0;background:linear-gradient(90deg,#31a375,#6fdba8);border-radius:999px;transition:width .3s ease}
.mission-meta{display:flex;justify-content:space-between;font-size:11px;font-weight:700;color:#bdcac0}
.mission-done-badge{position:absolute;top:0;right:0;background:#31a375;color:#fff;font-size:10px;padding:4px 12px;border-bottom-left-radius:12px;font-weight:900;letter-spacing:0.05em;text-transform:uppercase}
/* Modal */
.redeem-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);z-index:200;display:flex;align-items:center;justify-content:center;padding:20px}
.redeem-modal-overlay[hidden]{display:none}
.redeem-modal{background:#1a1c19;border:1px solid rgba(111,219,168,.15);border-radius:32px;padding:36px;max-width:480px;width:100%;position:relative}
.redeem-modal h3{margin:0 0 8px;font-family:'Noto Serif',serif;font-size:28px;color:#fff}
.redeem-modal p{margin:0 0 20px;color:#bdcac0;font-size:14px;line-height:1.7}
.redeem-modal-close{position:absolute;top:20px;right:20px;background:rgba(51,53,50,.8);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:18px;display:grid;place-items:center}
.redeem-modal-cost{background:rgba(255,225,109,.08);border:1px solid rgba(255,225,109,.15);border-radius:16px;padding:16px 20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center}
.redeem-modal-cost span{color:#bdcac0;font-size:14px}
.redeem-modal-cost strong{color:#ffe16d;font-size:22px;font-weight:900}
.redeem-modal-field label{display:block;margin-bottom:8px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.16em;color:#bdcac0}
.redeem-modal-field input,.redeem-modal-field textarea,.redeem-modal-field select{width:100%;background:#242622;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none;font-size:14px;margin-bottom:16px;box-sizing:border-box}
.redeem-modal-field textarea{min-height:80px;resize:vertical}
.redeem-submit-btn{width:100%;padding:16px;border:none;border-radius:16px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#003824;font-weight:900;font-size:16px;cursor:pointer;transition:.2s}
.redeem-submit-btn:hover{opacity:.9}
.redeem-submit-btn:disabled{opacity:.5;cursor:not-allowed}
.redeem-msg{padding:14px 18px;border-radius:14px;font-size:14px;font-weight:600;margin-top:14px;display:none}
.redeem-msg.success{background:rgba(111,219,168,.12);color:#6fdba8;border:1px solid rgba(111,219,168,.2)}
.redeem-msg.error{background:rgba(255,100,100,.1);color:#ff9999;border:1px solid rgba(255,100,100,.2)}
@media(max-width:992px){.doi-diem-layout{flex-direction:column-reverse;padding:20px;gap:24px}.doi-diem-sidebar{width:100%;position:static}}
@media(max-width:640px){.doi-diem-layout{margin-left:-16px !important}.doi-diem-points-banner{flex-direction:column;text-align:center;padding:20px}.doi-diem-points-num{font-size:48px}.reward-grid{grid-template-columns:repeat(2,1fr);gap:12px}.reward-card{padding:14px;gap:8px}.reward-icon{width:48px;height:48px;font-size:24px;border-radius:14px}.reward-name{font-size:15px}.reward-desc{font-size:11px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}.reward-points-num{font-size:20px}.reward-points-label{font-size:10px}.reward-btn{font-size:12px;padding:10px}.reward-card-badge{font-size:9px;padding:3px 6px;top:12px;right:12px}}
</style>

<section style="padding:0;min-height:100vh">
  <div class="doi-diem-layout">
    
    <div class="doi-diem-main">
      <!-- Points Banner -->
      <div class="doi-diem-points-banner">
        <div class="doi-diem-points-left">
          <h2>Cửa hàng Đổi Điểm Eco 🌿</h2>
          <p>Dùng điểm tích lũy để đổi lấy rau sạch và voucher ưu đãi</p>
          <p style="margin-top:8px;font-size:12px;color:#7a827b">Tích điểm thông qua Nhiệm vụ hàng ngày ở bên phải 👉</p>
        </div>
        <div style="text-align:right">
          <div>
            <span class="doi-diem-points-num"><?php echo esc_html(number_format($current_points)); ?></span>
            <span class="doi-diem-points-unit">điểm</span>
          </div>
          <div style="color:#bdcac0;font-size:13px;margin-top:4px">Cấp độ <?php echo esc_html($current_level); ?> · <a href="<?php echo esc_url(home_url('/tai-khoan/')); ?>" style="color:#6fdba8;text-decoration:none">Xem tài khoản</a></div>
        </div>
      </div>

      <!-- Rewards Grid -->
      <h2 class="doi-diem-section-title">🎁 Phần thưởng có thể đổi</h2>
      <div class="reward-grid">
        <?php foreach ($rewards as $reward) :
          $can = $current_points >= $reward['points'];
          $icon_bg = $can ? "background:rgba(" . implode(',', sscanf(ltrim($reward['color'],'#'), '%02x%02x%02x')) . ",.15)" : 'background:rgba(51,53,50,.5)';
        ?>
        <div class="reward-card" data-reward-id="<?php echo esc_attr($reward['id']); ?>">
          <?php if ($reward['badge']) : ?>
            <div class="reward-card-badge"><?php echo esc_html($reward['badge']); ?></div>
          <?php endif; ?>
          <div class="reward-icon" style="<?php echo esc_attr($icon_bg); ?>"><?php echo esc_html($reward['icon']); ?></div>
          <h3 class="reward-name"><?php echo esc_html($reward['name']); ?></h3>
          <p class="reward-desc"><?php echo esc_html($reward['desc']); ?></p>
          <div class="reward-points">
            <span class="reward-points-num"><?php echo esc_html(number_format($reward['points'])); ?></span>
            <span class="reward-points-label">điểm Eco</span>
          </div>
          <div class="reward-stock">Còn <?php echo esc_html($reward['stock']); ?> phần thưởng</div>
          <button
            class="reward-btn <?php echo $can ? 'can-redeem' : 'cannot-redeem'; ?>"
            <?php if ($can) : ?>
              data-open-redeem="1"
              data-reward-id="<?php echo esc_attr($reward['id']); ?>"
              data-reward-name="<?php echo esc_attr($reward['name']); ?>"
              data-reward-points="<?php echo esc_attr($reward['points']); ?>"
              data-reward-icon="<?php echo esc_attr($reward['icon']); ?>"
            <?php else : ?>
              disabled title="Bạn cần <?php echo esc_attr(number_format($reward['points'] - $current_points)); ?> điểm nữa"
            <?php endif; ?>
          >
            <?php if ($can) : ?>🎁 Đổi ngay<?php else : ?>🔒 Thiếu <?php echo esc_html(number_format($reward['points'] - $current_points)); ?> điểm<?php endif; ?>
          </button>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Redemption History -->
      <div id="lich-su">
        <h2 class="doi-diem-section-title">📜 Lịch sử đổi thưởng</h2>
        <div class="eco-card" style="padding:0;overflow:hidden">
          <?php if (empty($history)) : ?>
            <div style="padding:40px;text-align:center;color:#7a827b">
              <div style="font-size:48px;margin-bottom:12px">🎁</div>
              <p style="margin:0">Bạn chưa đổi phần thưởng nào. Bắt đầu tích điểm và đổi ngay nhé!</p>
            </div>
          <?php else : ?>
            <table class="history-table">
              <thead>
                <tr>
                  <th>Phần thưởng</th>
                  <th>Điểm dùng</th>
                  <th>Ngày đổi</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach (array_reverse($history) as $h) : 
                  $status_map = ['pending'=>'Đang xử lý','confirmed'=>'Đã xác nhận','delivered'=>'Đã giao'];
                  $s = $h['status'] ?? 'pending';
                ?>
                <tr>
                  <td><?php echo esc_html($h['icon'] ?? '🎁'); ?> <?php echo esc_html($h['name'] ?? ''); ?></td>
                  <td style="color:#ffe16d;font-weight:700">-<?php echo esc_html(number_format((int)($h['points'] ?? 0))); ?>đ</td>
                  <td><?php echo esc_html(wp_date('d/m/Y H:i', (int)($h['time'] ?? 0))); ?></td>
                  <td><span class="history-badge <?php echo esc_attr($s); ?>"><?php echo esc_html($status_map[$s] ?? $s); ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Sidebar: Daily Missions -->
    <aside class="doi-diem-sidebar">
      <div class="mission-box">
        <!-- Header will be moved below after calculating variables -->
        
        <?php 
        $today = current_time('Ymd');
        $user_id = get_current_user_id();
        $g_param = $garden_key ? "?garden=" . $garden_key : "";
        $g_param_amp = $garden_key ? "&garden=" . $garden_key : "";

        // Fetch real data from user meta
        $tuoi_nuoc_count = (int) get_user_meta($user_id, "_aitrongcay_daily_waters_count_{$today}", true);
        $chup_anh_count = (int) get_user_meta($user_id, "_aitrongcay_daily_chup_anh_{$today}", true);
        if ($chup_anh_count === 0 && get_user_meta($user_id, '_aitrongcay_last_photo_bonus_date', true) === $today) {
            $chup_anh_count = 1;
        }
        $thu_hoach_count = (int) get_user_meta($user_id, "_aitrongcay_daily_thu_hoach_{$today}", true);
        $moi_ban_count = (int) get_user_meta($user_id, "_aitrongcay_total_referrals", true);

        // Cap maximum values to ensure progress bar doesn't overflow
        $tuoi_nuoc_count = min($tuoi_nuoc_count, 3);
        $chup_anh_count = min($chup_anh_count, 1);
        $thu_hoach_count = min($thu_hoach_count, 1);

        $completed_missions = 0;
        if ($tuoi_nuoc_count >= 3) $completed_missions++;
        if ($chup_anh_count >= 1) $completed_missions++;
        if ($thu_hoach_count >= 1) $completed_missions++;
        
        $missions = [
          [
            'id' => 'tuoi_nuoc',
            'icon' => '💦',
            'title' => 'Tưới nước hộ hàng xóm',
            'reward' => '+10đ / lần',
            'current' => $tuoi_nuoc_count,
            'max' => 3,
            'link' => home_url('/portal/hang-xom/') . $g_param
          ],
          [
            'id' => 'chup_anh',
            'icon' => '📸',
            'title' => 'Chụp ảnh khu vườn',
            'reward' => '+5đ',
            'current' => $chup_anh_count,
            'max' => 1,
            'link' => home_url('/portal/dashboard-2/') . '?view=timelapse' . $g_param_amp
          ],
          [
            'id' => 'thu_hoach',
            'icon' => '🌱',
            'title' => 'Thu hoạch rau đúng kỳ',
            'reward' => '+50đ / vụ',
            'current' => $thu_hoach_count,
            'max' => 1,
            'link' => home_url('/portal/dashboard-2/') . $g_param
          ],
          [
            'id' => 'moi_ban',
            'icon' => '🤝',
            'title' => 'Mời bạn bè tham gia',
            'reward' => '+100đ / người',
            'current' => $moi_ban_count,
            'max' => '∞', // infinity sign
            'link' => home_url('/portal/dashboard-2/') . '?share=1' . $g_param_amp,
            'is_infinite' => true
          ],
        ];
        ?>
        
        <div class="mission-box-header">
          <h3 class="mission-box-title">📋 Nhiệm vụ ngày</h3>
          <div class="mission-box-count"><?php echo esc_html($completed_missions); ?>/3 Xong</div>
        </div>
        
        <?php

        foreach ($missions as $m) : 
          $pct = isset($m['is_infinite']) ? 0 : ($m['current'] / $m['max']) * 100;
          $is_done = !isset($m['is_infinite']) && $m['current'] >= $m['max'];
        ?>
        <a href="<?php echo esc_url($m['link']); ?>" class="mission-card <?php echo $is_done ? 'is-completed' : ''; ?>">
          <?php if ($is_done): ?>
            <div class="mission-done-badge">Đã xong</div>
          <?php endif; ?>
          <div class="mission-card-top">
            <div class="mission-icon"><?php echo esc_html($m['icon']); ?></div>
            <div class="mission-info">
              <h4 class="mission-title"><?php echo esc_html($m['title']); ?></h4>
              <div class="mission-reward"><?php echo esc_html($m['reward']); ?></div>
            </div>
          </div>
          <div class="mission-progress">
            <div class="mission-meta">
              <span>Tiến độ</span>
              <span><?php echo esc_html($m['current'] . ' / ' . $m['max']); ?></span>
            </div>
            <div class="mission-progress-bar" style="margin-top:6px">
              <div class="mission-progress-fill" style="width: <?php echo esc_attr($pct); ?>%;"></div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
        
      </div>
    </aside>

  </div>
</section>

<!-- Redeem Modal -->
<div class="redeem-modal-overlay" id="redeemModalOverlay" hidden>
  <div class="redeem-modal" role="dialog" aria-modal="true">
    <button class="redeem-modal-close" id="redeemModalClose" type="button">✕</button>
    <div id="redeemModalIcon" style="font-size:48px;margin-bottom:12px">🎁</div>
    <h3 id="redeemModalTitle">Xác nhận đổi thưởng</h3>
    <p id="redeemModalDesc">Điền thông tin giao hàng để chúng tôi gửi phần thưởng đến bạn.</p>

    <div class="redeem-modal-cost">
      <span>Điểm sẽ dùng</span>
      <strong id="redeemModalPoints">0 điểm</strong>
    </div>

    <form id="redeemForm">
      <input type="hidden" id="redeemRewardId" name="reward_id" value="">
      <?php wp_nonce_field('aitrongcay_redeem_points', 'aitrongcay_redeem_nonce'); ?>

      <div class="redeem-modal-field">
        <label for="redeemName">Họ và tên người nhận</label>
        <input id="redeemName" name="recipient_name" required placeholder="<?php echo esc_attr($current_user->display_name ?: $current_user->user_login); ?>" value="<?php echo esc_attr($current_user->display_name ?: ''); ?>">
      </div>
      <div class="redeem-modal-field">
        <label for="redeemPhone">Số điện thoại</label>
        <input id="redeemPhone" name="recipient_phone" required placeholder="09xx xxx xxx" value="<?php echo esc_attr((string) get_user_meta($current_user->ID, 'aitrongcay_phone', true)); ?>">
      </div>
      <div class="redeem-modal-field">
        <label for="redeemAddress">Địa chỉ giao hàng</label>
        <textarea id="redeemAddress" name="recipient_address" required placeholder="Số nhà, đường, phường/xã, quận/huyện, thành phố"><?php
          $parts = array_filter([
            get_user_meta($current_user->ID, 'aitrongcay_address_line', true),
            get_user_meta($current_user->ID, 'aitrongcay_ward', true),
            get_user_meta($current_user->ID, 'aitrongcay_district', true),
            get_user_meta($current_user->ID, 'aitrongcay_city', true),
          ]);
          echo esc_textarea(implode(', ', $parts));
        ?></textarea>
      </div>
      <div class="redeem-modal-field">
        <label for="redeemNote">Ghi chú thêm (không bắt buộc)</label>
        <input id="redeemNote" name="note" placeholder="Giao giờ hành chính, gọi trước khi đến...">
      </div>

      <button type="submit" class="redeem-submit-btn" id="redeemSubmitBtn">✅ Xác nhận đổi thưởng</button>
      <div class="redeem-msg" id="redeemMsg"></div>
    </form>
  </div>
</div>

<script>
(function(){
  var overlay = document.getElementById('redeemModalOverlay');
  var closeBtn = document.getElementById('redeemModalClose');
  var form = document.getElementById('redeemForm');
  var submitBtn = document.getElementById('redeemSubmitBtn');
  var msg = document.getElementById('redeemMsg');

  function openModal(btn) {
    document.getElementById('redeemRewardId').value = btn.dataset.rewardId;
    document.getElementById('redeemModalTitle').textContent = 'Đổi: ' + btn.dataset.rewardName;
    document.getElementById('redeemModalIcon').textContent = btn.dataset.rewardIcon;
    document.getElementById('redeemModalPoints').textContent = parseInt(btn.dataset.rewardPoints).toLocaleString('vi-VN') + ' điểm';
    msg.style.display = 'none';
    msg.textContent = '';
    submitBtn.disabled = false;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    overlay.hidden = true;
    document.body.style.overflow = '';
  }

  document.querySelectorAll('[data-open-redeem="1"]').forEach(function(btn){
    btn.addEventListener('click', function(){ openModal(btn); });
  });

  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function(e){ if(e.target === overlay) closeModal(); });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeModal(); });

  form.addEventListener('submit', function(e){
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.textContent = '⏳ Đang xử lý...';
    msg.style.display = 'none';

    var data = new FormData(form);
    data.append('action', 'aitrongcay_redeem_points');

    fetch(typeof aitrongcayTheme !== 'undefined' ? aitrongcayTheme.ajaxUrl : '/wp-admin/admin-ajax.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: data,
    })
    .then(function(r){ return r.json(); })
    .then(function(res){
      if (res.success) {
        msg.className = 'redeem-msg success';
        msg.style.display = 'block';
        msg.textContent = '🎉 ' + (res.data.message || 'Đổi thưởng thành công!');
        submitBtn.textContent = '✅ Đã gửi yêu cầu';
        // Update points display
        if (res.data.remaining_points !== undefined) {
          updatePoints(res.data.remaining_points);
        }
        
        // Show a toast instead of reloading
        showToast('🎁 Đổi thưởng', res.data.message || 'Đổi thưởng thành công!');
        closeModal();
      } else {
        msg.className = 'redeem-msg error';
        msg.style.display = 'block';
        msg.textContent = '❌ ' + (res.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        submitBtn.disabled = false;
        submitBtn.textContent = '✅ Xác nhận đổi thưởng';
      }
    })
    .catch(function(){
      msg.className = 'redeem-msg error';
      msg.style.display = 'block';
      msg.textContent = '❌ Lỗi kết nối. Vui lòng thử lại.';
      submitBtn.disabled = false;
      submitBtn.textContent = '✅ Xác nhận đổi thưởng';
    });
  });

  function updatePoints(newPoints) {
    document.querySelector('.doi-diem-points-num').textContent = parseInt(newPoints).toLocaleString('vi-VN');
    // Update all reward buttons based on new points
    document.querySelectorAll('.reward-card').forEach(function(card) {
      var btn = card.querySelector('.reward-btn');
      var reqPoints = parseInt(btn.dataset.rewardPoints || card.querySelector('.reward-points-num').textContent.replace(/\D/g, ''));
      if (newPoints >= reqPoints) {
        btn.className = 'reward-btn can-redeem';
        btn.innerHTML = '🎁 Đổi ngay';
        btn.disabled = false;
        if (!btn.hasAttribute('data-open-redeem')) {
           btn.setAttribute('data-open-redeem', '1');
           var name = card.querySelector('.reward-name').textContent;
           var icon = card.querySelector('.reward-icon').textContent;
           var id = card.dataset.rewardId;
           btn.dataset.rewardId = id;
           btn.dataset.rewardName = name;
           btn.dataset.rewardPoints = reqPoints;
           btn.dataset.rewardIcon = icon;
           btn.addEventListener('click', function(){ openModal(btn); });
        }
      } else {
        btn.className = 'reward-btn cannot-redeem';
        btn.innerHTML = '🔒 Thiếu ' + parseInt(reqPoints - newPoints).toLocaleString('vi-VN') + ' điểm';
        btn.disabled = true;
      }
    });
  }

  function showToast(title, message) {
    var toast = document.createElement('div');
    toast.style.position = 'fixed';
    toast.style.bottom = '24px';
    toast.style.right = '24px';
    toast.style.background = 'rgba(26,28,25,0.95)';
    toast.style.border = '1px solid rgba(111,219,168,0.3)';
    toast.style.color = '#fff';
    toast.style.padding = '16px 24px';
    toast.style.borderRadius = '16px';
    toast.style.boxShadow = '0 12px 40px rgba(0,0,0,0.3)';
    toast.style.zIndex = '99999';
    toast.style.fontFamily = '"Noto Serif", serif';
    toast.style.fontSize = '15px';
    toast.style.transition = 'opacity 0.4s ease, transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
    toast.style.transform = 'translateY(20px)';
    toast.style.opacity = '0';
    
    toast.innerHTML = '<strong style="color:#6fdba8">' + title + '</strong><br>' + message;
    
    document.body.appendChild(toast);
    
    setTimeout(function() {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    }, 10);
    
    setTimeout(function() {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 400);
    }, 4000);
  }

  // Global helper to trigger mission and update UI in doi-diem.php
  window.aitrongcay_trigger_daily_mission = function(missionId) {
    var fd = new FormData();
    fd.append('action', 'aitrongcay_complete_daily_mission');
    fd.append('mission_id', missionId);
    fetch(typeof aitrongcayTheme !== 'undefined' ? aitrongcayTheme.ajaxUrl : '/wp-admin/admin-ajax.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success && res.data && res.data.message) {
          showToast('🎉 Điểm danh!', res.data.message);
          
          if (res.data.total_points !== undefined) {
             updatePoints(res.data.total_points);
          }
          
          // Dynamically update mission sidebar
          var mIdMap = {
            'tuoi_nuoc': 'Tưới nước hộ hàng xóm',
            'chup_anh': 'Chụp ảnh khu vườn',
            'thu_hoach': 'Thu hoạch rau đúng kỳ',
            'moi_ban': 'Mời bạn bè tham gia'
          };
          var titleText = mIdMap[missionId];
          if (titleText) {
            document.querySelectorAll('.mission-card').forEach(function(card) {
               if (card.querySelector('.mission-title').textContent === titleText) {
                  var max = res.data.max;
                  var cur = res.data.current;
                  if (cur > max) cur = max;
                  card.querySelector('.mission-meta span:last-child').textContent = cur + ' / ' + max;
                  var pct = (cur / max) * 100;
                  card.querySelector('.mission-progress-fill').style.width = pct + '%';
                  if (cur >= max && !card.classList.contains('is-completed')) {
                     card.classList.add('is-completed');
                     var badge = document.createElement('div');
                     badge.className = 'mission-done-badge';
                     badge.textContent = 'Đã xong';
                     card.insertBefore(badge, card.firstChild);
                     
                     // Update completed missions counter
                     var countEl = document.querySelector('.mission-box-count');
                     if (countEl) {
                        var m = countEl.textContent.match(/(\d+)\/(\d+)/);
                        if (m) {
                           countEl.textContent = (parseInt(m[1]) + 1) + '/' + m[2] + ' Xong';
                        }
                     }
                  }
               }
            });
          }
        }
      }).catch(function(e) { console.error('Lỗi nhiệm vụ', e); });
  };
})();
</script>

<?php get_template_part('template-parts/site/eco-shell-end'); ?>
