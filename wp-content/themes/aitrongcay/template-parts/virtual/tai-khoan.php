<?php

declare(strict_types=1);

if (! is_user_logged_in()) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

$current_user = wp_get_current_user();
$phone = (string) get_user_meta($current_user->ID, 'aitrongcay_phone', true);
$city = (string) get_user_meta($current_user->ID, 'aitrongcay_city', true);
$household = (string) get_user_meta($current_user->ID, 'aitrongcay_household', true);
$address_line = (string) get_user_meta($current_user->ID, 'aitrongcay_address_line', true);
$ward = (string) get_user_meta($current_user->ID, 'aitrongcay_ward', true);
$district = (string) get_user_meta($current_user->ID, 'aitrongcay_district', true);
$account_note = (string) get_user_meta($current_user->ID, 'aitrongcay_account_note', true);
$notify_email = (string) get_user_meta($current_user->ID, 'aitrongcay_notify_email', true) !== '0';
$notify_sms = (string) get_user_meta($current_user->ID, 'aitrongcay_notify_sms', true) === '1';
$notify_zalo = (string) get_user_meta($current_user->ID, 'aitrongcay_notify_zalo', true) === '1';
$notify_harvest = (string) get_user_meta($current_user->ID, 'aitrongcay_notify_harvest', true) !== '0';
$member_since = get_date_from_gmt($current_user->user_registered, 'd/m/Y');
$avatar_id = (int) get_user_meta($current_user->ID, 'aitrongcay_avatar_id', true);
$avatar_url = $avatar_id ? (wp_get_attachment_image_url($avatar_id, 'medium') ?: wp_get_attachment_url($avatar_id)) : '';
$avatar_fallback = mb_strtoupper(mb_substr($current_user->display_name ?: $current_user->user_login, 0, 1));

$active_plan = function_exists('aitrongcay_get_active_subscription_plan') ? aitrongcay_get_active_subscription_plan($current_user->ID) : ['id' => '', 'expiry' => 0];
$plan_id = $active_plan['id'];
$expiry_text = $active_plan['expiry'] > 0 ? 'Đáo hạn: ' . wp_date('d/m/Y', $active_plan['expiry']) : '';

$plan_name = 'Free Account';
$plan_desc = 'Bạn hiện chưa sử dụng dịch vụ nâng cao nào. Nâng cấp ngay để kích hoạt các tính năng của vườn số.';
$plan_badge = 'Cơ bản';

if ($plan_id === 'basic') {
    $plan_name = 'Basic Seed';
    $plan_desc = 'Gói khởi đầu không sử dụng camera, theo dõi qua dữ liệu cơ bản và hình ảnh.';
    $plan_badge = 'Khởi đầu';
} elseif ($plan_id === 'prime') {
    $plan_name = 'Verdant Prime';
    $plan_desc = 'Gói phổ biến nhất phù hợp cho một khu vườn gia đình với camera, dữ liệu cơ bản và AI đồng hành.';
    $plan_badge = 'Hiện tại';
} elseif ($plan_id === 'enterprise') {
    $plan_name = 'Eco Enterprise';
    $plan_desc = 'Dành cho quy mô trang trại lớn với hệ thống đa thiết bị, API tích hợp và chuyên gia tư vấn.';
    $plan_badge = 'Chuyên nghiệp';
}

$account_nav_items = array_map(static function (array $item) {
    return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => (string) ($item['url'] ?? '#')];
}, aitrongcay_eco_nav_items());
set_query_var('aitr_eco_shell', [
    'title' => 'Tài khoản',
    'active' => '',
    'side_title' => 'Ai trồng cây',
    'side_subtitle' => 'Hồ sơ khu vườn số',
    'side_badge' => '👤',
    'top_icons' => ['🔔', '⚙️'],
    'search' => null,
    'nav' => $account_nav_items,
]);
get_template_part('template-parts/site/eco-shell-start');
set_query_var('aitr_eco_hero', ['title' => 'Quản lý tài khoản', 'body' => 'Giữ mọi thông tin cá nhân, cài đặt và quyền truy cập của anh trong cùng một khu điều khiển gọn gàng, đồng bộ với toàn bộ trải nghiệm vườn số.']);
get_template_part('template-parts/site/eco-hero');
?>
<section class="section-tight" style="background:#121411;min-height:100vh;padding:0">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-account-page{background:transparent;color:#e3e3de;min-height:auto;font-family:'Manrope',sans-serif}
    .eco-account-head{display:none}
    .eco-account-grid{display:grid;grid-template-columns:minmax(340px,380px) minmax(620px,1fr);gap:24px;align-items:start;width:100%;max-width:none}.eco-account-card{background:rgba(26,28,25,.94);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.05);border-radius:32px;padding:28px;box-shadow:0 24px 52px rgba(0,0,0,.22);width:100%;box-sizing:border-box}
    .eco-account-profile{text-align:center;position:relative;overflow:hidden}.eco-account-profile:before{content:'';position:absolute;right:-40px;top:-40px;width:180px;height:180px;border-radius:999px;background:rgba(111,219,168,.08);filter:blur(24px)}
    .eco-account-avatar-wrap{width:128px;height:128px;border-radius:999px;padding:4px;background:linear-gradient(135deg,#31a375,#ffe16d);margin:0 auto 16px}.eco-account-avatar{width:100%;height:100%;border-radius:999px;overflow:hidden;background:#121411;display:grid;place-items:center;font-size:42px;font-weight:900}
    .eco-account-profile h3{margin:0;font-family:'Noto Serif',serif;font-size:30px;color:#fff}.eco-account-rank{color:#6FDBA8;font-size:12px;letter-spacing:.16em;text-transform:uppercase;font-weight:800;margin-top:6px}
    .eco-account-stat{background:rgba(51,53,50,.38);border-radius:18px;padding:16px;display:flex;justify-content:space-between;align-items:center}.eco-account-stat span{color:#bdcac0;font-size:14px}.eco-account-stat strong{color:#fff}.eco-account-progress{margin-top:18px}.eco-account-progress-bar{height:8px;background:#333532;border-radius:999px;overflow:hidden}.eco-account-progress-bar>div{height:100%;width:82%;background:linear-gradient(135deg,#31a375,#6fdba8)}
    .eco-account-form-card h2{margin:0 0 18px;font-family:'Noto Serif',serif;font-size:30px;color:#fff}.eco-account-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}.eco-account-field label{display:block;margin:0 0 8px 4px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.18em;color:#bdcac0}.eco-account-field input,.eco-account-field textarea{width:100%;background:#292b27;border:1px solid rgba(255,255,255,.05);border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none}.eco-account-field textarea{min-height:120px;resize:vertical}
    .eco-account-tier{position:relative;overflow:hidden;background:linear-gradient(135deg, rgba(111,219,168,.12), rgba(255,225,109,.04));border:1px solid rgba(111,219,168,.12)}.eco-account-tier-badge{display:inline-flex;padding:6px 10px;border-radius:10px;background:#ffe16d;color:#221b00;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
    .eco-account-actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}.eco-account-btn{padding:14px 18px;border:none;border-radius:14px;font-weight:800}.eco-account-btn.primary{background:linear-gradient(135deg,#31a375,#6fdba8);color:#003824}.eco-account-btn.secondary{background:#333532;color:#fff}
    @media (max-width:1100px){.eco-account-grid{grid-template-columns:1fr}}
    @media (max-width:760px){.eco-account-form-grid{grid-template-columns:1fr}}
  </style>
  <div class="eco-account-page">
    <div class="eco-account-shell">
      <main class="eco-account-main">
        <div class="eco-account-content">
          <div class="eco-account-inner">
          <?php aitrongcay_render_account_notice(); ?>
          <div class="eco-account-grid">
            <section style="display:grid;gap:24px">
              <div class="eco-account-card eco-account-profile">
                <div class="eco-account-avatar-wrap"><div class="eco-account-avatar" style="padding:0;overflow:hidden"><?php if ($avatar_url) : ?><img src="<?php echo esc_url($avatar_url); ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;display:block;margin:0;padding:0"><?php else : ?><?php echo esc_html($avatar_fallback); ?><?php endif; ?></div></div>
                <h3><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></h3>
                <div class="eco-account-rank">Hồ sơ khu vườn số</div>
                <div style="display:grid;gap:12px;margin-top:22px">
                  <?php
                    $current_points = (int) get_user_meta($current_user->ID, '_aitrongcay_eco_points', true);
                    $current_level = function_exists('aitrongcay_calculate_level') ? aitrongcay_calculate_level($current_points) : 1;
                    
                    // Progress to next level calculation
                    $points_for_current_level = function_exists('aitrongcay_points_for_level') ? aitrongcay_points_for_level($current_level) : 0;
                    $points_for_next_level = function_exists('aitrongcay_points_for_level') ? aitrongcay_points_for_level($current_level + 1) : 100;
                    
                    $points_needed = $points_for_next_level - $points_for_current_level;
                    $points_gained = $current_points - $points_for_current_level;
                    $level_progress_percent = $points_needed > 0 ? min(100, round(($points_gained / $points_needed) * 100)) : 0;
                  ?>
                  <div class="eco-account-stat"><span>Mức tài khoản</span><strong>LVL <?php echo esc_html($current_level); ?></strong></div>
                  <div class="eco-account-stat"><span>Eco Points (Điểm)</span><strong style="color:#ffe16d"><?php echo esc_html($current_points); ?> 🍃</strong></div>
                  <div class="eco-account-stat"><span>Thành viên từ</span><strong><?php echo esc_html($member_since); ?></strong></div>
                </div>
                <div class="eco-account-progress">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:8px">
                        <span style="color:#bdcac0">Tiến trình lên LVL <?php echo esc_html($current_level + 1); ?></span>
                        <span style="color:#6FDBA8"><?php echo esc_html($points_gained); ?> / <?php echo esc_html($points_needed); ?></span>
                    </div>
                    <div class="eco-account-progress-bar"><div style="width:<?php echo esc_attr($level_progress_percent); ?>%"></div></div>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display:grid;gap:10px;margin-top:22px"><input type="hidden" name="action" value="aitrongcay_account_avatar_update"><?php wp_nonce_field('aitrongcay_account_avatar_submit', 'aitrongcay_account_avatar_nonce'); ?><input id="account-avatar" type="file" name="avatar" accept="image/*"><button class="eco-account-btn secondary" type="submit">Cập nhật ảnh</button></form>
              </div>
              <div class="eco-account-card"><h2 style="font-size:24px">Chỉ số tài khoản</h2><div style="display:grid;grid-template-columns:1fr 1fr;gap:14px"><div class="eco-account-stat" style="display:block"><span style="display:block;margin-bottom:6px">Khu vườn theo dõi</span><strong style="color:#6FDBA8">01</strong></div><div class="eco-account-stat" style="display:block"><span style="display:block;margin-bottom:6px">Nhắc việc đang bật</span><strong style="color:#6FDBA8"><?php
$active_notifs = 0;
if ($notify_email) $active_notifs++;
if ($notify_sms) $active_notifs++;
if ($notify_zalo) $active_notifs++;
if ($notify_harvest) $active_notifs++;
echo sprintf('%02d', $active_notifs);
?></strong></div></div></div>
            </section>
            <section style="display:grid;gap:24px">
              <div class="eco-account-card eco-account-form-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px"><h2>Thông tin cá nhân</h2><span style="color:#6FDBA8;font-size:14px;font-weight:700">Chỉnh sửa</span></div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                  <input type="hidden" name="action" value="aitrongcay_account_update"><?php wp_nonce_field('aitrongcay_account_update_submit', 'aitrongcay_account_update_nonce'); ?>
                  <div class="eco-account-form-grid">
                    <div class="eco-account-field"><label for="account-display-name">Tên hiển thị</label><input id="account-display-name" name="display_name" value="<?php echo esc_attr($current_user->display_name); ?>"></div>
                    <div class="eco-account-field"><label for="account-email">Email</label><input id="account-email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>"></div>
                    <div class="eco-account-field"><label for="account-phone">Số điện thoại</label><input id="account-phone" name="phone" value="<?php echo esc_attr($phone); ?>"></div>
                    <div class="eco-account-field"><label for="account-city">Thành phố</label><input id="account-city" name="city" value="<?php echo esc_attr($city); ?>"></div>
                    <div class="eco-account-field"><label for="account-household">Ghi chú ngắn</label><input id="account-household" name="household" value="<?php echo esc_attr($household); ?>"></div>
                    <div class="eco-account-field"><label for="account-district">Quận / Huyện</label><input id="account-district" name="district" value="<?php echo esc_attr($district); ?>"></div>
                    <div class="eco-account-field" style="grid-column:1/-1"><label for="account-address">Địa chỉ</label><input id="account-address" name="address_line" value="<?php echo esc_attr($address_line); ?>"></div>
                    <div class="eco-account-field"><label for="account-ward">Phường / Xã</label><input id="account-ward" name="ward" value="<?php echo esc_attr($ward); ?>"></div>
                    <div class="eco-account-field" style="grid-column:1/-1"><label for="account-note">Ghi chú tài khoản</label><textarea id="account-note" name="account_note"><?php echo esc_textarea($account_note); ?></textarea></div>
                  </div>
                  <div style="display:grid;gap:12px;margin-top:24px">
                    <label style="display:flex;align-items:center;gap:10px;color:#bdcac0;font-size:14px;cursor:pointer;"><input type="checkbox" name="notify_email" value="1" <?php checked($notify_email); ?> style="width:18px;height:18px;margin:0;"> Nhận thông báo qua email</label>
                    <label style="display:flex;align-items:center;gap:10px;color:#bdcac0;font-size:14px;cursor:pointer;"><input type="checkbox" name="notify_sms" value="1" <?php checked($notify_sms); ?> style="width:18px;height:18px;margin:0;"> Nhận thông báo qua SMS</label>
                    <label style="display:flex;align-items:center;gap:10px;color:#bdcac0;font-size:14px;cursor:pointer;"><input type="checkbox" name="notify_zalo" value="1" <?php checked($notify_zalo); ?> style="width:18px;height:18px;margin:0;"> Nhận thông báo qua Zalo</label>
                    <label style="display:flex;align-items:center;gap:10px;color:#bdcac0;font-size:14px;cursor:pointer;"><input type="checkbox" name="notify_harvest" value="1" <?php checked($notify_harvest); ?> style="width:18px;height:18px;margin:0;"> Nhắc thu hoạch</label>
                  </div>
                  <div class="eco-account-actions"><button class="eco-account-btn primary" type="submit">Lưu thông tin</button><a class="eco-account-btn secondary" href="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">Khu vườn của tôi</a></div>
                </form>
              </div>
              <div class="eco-account-card eco-account-tier"><div class="eco-account-tier-badge"><?php echo esc_html($plan_badge); ?></div><h2 style="margin:14px 0 2px;font-family:'Noto Serif',serif;font-size:34px;color:#fff"><?php echo esc_html($plan_name); ?></h2><?php if($expiry_text): ?><div style="font-size:13px;color:#6fdba8;font-weight:600;margin-bottom:12px;letter-spacing:0.02em;"><?php echo esc_html($expiry_text); ?></div><?php else: ?><div style="height:12px"></div><?php endif; ?><p style="color:#bdcac0;line-height:1.8;margin-top:0"><?php echo esc_html($plan_desc); ?></p><div class="eco-account-actions"><a class="eco-account-btn primary" href="<?php echo esc_url(home_url('/nang-cap-goi/')); ?>" style="text-decoration:none">Nâng cấp gói</a><a class="eco-account-btn secondary" href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a></div></div>
              <div class="eco-account-card"><h2 style="margin:0 0 18px;font-family:'Noto Serif',serif;font-size:28px;color:#fff">Đổi mật khẩu</h2><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="aitrongcay_account_password_update"><?php wp_nonce_field('aitrongcay_account_password_submit', 'aitrongcay_account_password_nonce'); ?><div class="eco-account-form-grid"><div class="eco-account-field"><label for="account-new-password">Mật khẩu mới</label><input id="account-new-password" name="new_password" type="password" autocomplete="new-password"></div><div class="eco-account-field"><label for="account-confirm-password">Xác nhận mật khẩu mới</label><input id="account-confirm-password" name="confirm_password" type="password" autocomplete="new-password"></div></div><div class="eco-account-actions"><button class="eco-account-btn primary" type="submit">Cập nhật mật khẩu</button></div></form></div>
            </section>
          </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</section>
<?php get_template_part('template-parts/site/eco-shell-end'); ?>
