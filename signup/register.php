<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/wp-load.php';

if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/portal/dashboard-2/'));
    exit;
}

$consultation_status = sanitize_text_field(wp_unslash($_GET['consultation_status'] ?? ''));
$action_url  = esc_url(admin_url('admin-post.php'));
$redirect_to = esc_url(home_url('/onboarding/'));
$login_url   = esc_url(home_url('/dang-nhap/'));

$status_map = [
    'success' => ['success', 'Đã ghi nhận. Mời anh/chị bước tiếp theo: tạo tài khoản để kích hoạt khu vườn.'],
    'invalid' => ['error',   'Anh/chị vui lòng để lại ít nhất họ tên và số điện thoại.'],
];
[$msg_type, $msg_text] = $status_map[$consultation_status] ?? [null, null];
?><!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng ký tư vấn — Ai trồng cây</title>
  <meta name="description" content="Đăng ký tư vấn gói vườn phù hợp, để lại nhu cầu gia đình và bắt đầu hành trình sở hữu khu vườn số cùng Ai trồng cây.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<header class="site-header">
  <div class="container nav-row">
    <a class="logo" href="<?php echo esc_url(home_url('/')); ?>"><span class="logo-badge">🌿</span><span>Ai trồng cây</span></a>
    <nav class="nav-menu">
      <a href="<?php echo esc_url(home_url('/cach-hoat-dong/')); ?>">Cách hoạt động</a>
      <a href="<?php echo esc_url(home_url('/cho-que/')); ?>">Chợ quê</a>
      <a href="<?php echo esc_url(home_url('/dang-ky-tu-van/')); ?>">Tư vấn gói vườn</a>
    </nav>
    <div class="nav-actions">
      <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/onboarding/')); ?>">Xem onboarding</a>
      <a class="btn btn-primary" href="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">Xem khu vườn mẫu</a>
    </div>
    <button class="btn btn-secondary menu-toggle" data-mobile-toggle>Menu</button>
  </div>
  <div class="container" data-mobile-panel style="display:none"></div>
</header>
<main>
  <section class="section-hero auth-hero">
    <div class="container auth-shell">
      <div class="auth-copy">
        <span class="eyebrow">Đăng ký tư vấn</span>
        <h1>Điền vài thông tin cơ bản để bắt đầu một cuộc trao đổi ngắn, đúng điều gia đình anh/chị đang quan tâm.</h1>
        <p class="lead">Form này chỉ lấy những thông tin cần cho vòng đầu. Sau khi gửi, đội ngũ sẽ liên hệ ngắn gọn để hiểu nhu cầu.</p>
        <div class="auth-journey">
          <div class="journey-step is-active"><span>1</span>
            <div><strong>Gửi form ngắn</strong><small>Chia sẻ nhu cầu chính và cách liên hệ thuận tiện.</small></div>
          </div>
          <div class="journey-step"><span>2</span>
            <div><strong>Tạo tài khoản</strong><small>Điền thông tin và mật khẩu để kích hoạt khu vườn số.</small></div>
          </div>
          <div class="journey-step"><span>3</span>
            <div><strong>Vào portal</strong><small>Xem webcam, care log và theo dõi khu vườn ngay.</small></div>
          </div>
        </div>
      </div>
      <div class="card auth-card auth-card-wide">
        <div class="auth-card-head">
          <div><span class="eyebrow">Form tư vấn</span><h2>Ngắn gọn, đủ dùng và không làm mất thời gian</h2></div>
          <span class="stat-pill">Khoảng 1 phút</span>
        </div>
        <?php if ($msg_type): ?>
          <div class="form-result is-<?php echo esc_attr($msg_type); ?>" style="margin-bottom:16px">
            <?php echo esc_html((string) $msg_text); ?>
          </div>
        <?php endif; ?>
        <form class="auth-form" method="post" action="<?php echo $action_url; ?>">
          <?php wp_nonce_field('aitrongcay_consultation_submit', 'aitrongcay_consultation_nonce'); ?>
          <input type="hidden" name="action"       value="aitrongcay_consultation_submit">
          <input type="hidden" name="redirect_to"  value="<?php echo $redirect_to; ?>">
          <input type="hidden" name="funnelStage"  value="consultation">
          <input type="hidden" name="funnelSource" value="signup-register">
          <div class="form-grid">
            <div>
              <label for="register-name">Họ và tên</label>
              <input id="register-name" name="fullName" placeholder="Nguyễn Minh Anh" autocomplete="name" required>
            </div>
            <div>
              <label for="register-phone">Số điện thoại</label>
              <input id="register-phone" name="phone" placeholder="09xx xxx xxx" autocomplete="tel" required>
            </div>
            <div>
              <label for="register-email">Email</label>
              <input id="register-email" name="email" placeholder="anhchi@email.com" autocomplete="email">
            </div>
            <div>
              <label for="register-goal">Điều anh/chị quan tâm nhất</label>
              <select id="register-goal" name="goal">
                <option value="">Chọn một mục</option>
                <option>Muốn rau sạch minh bạch hơn</option>
                <option>Muốn theo dõi bằng webcam</option>
                <option>Muốn cả nhà cùng xem khu vườn</option>
              </select>
            </div>
          </div>
          <div style="margin-top:16px">
            <label for="register-focus">Ghi chú thêm</label>
            <textarea id="register-focus" name="focus" placeholder="Ví dụ: nhà có con nhỏ, muốn biết rõ quy trình chăm và hồ sơ chất lượng"></textarea>
          </div>
          <div class="auth-actions">
            <button class="btn btn-primary" type="submit">Gửi đăng ký tư vấn</button>
            <a class="btn btn-secondary" href="<?php echo esc_url($login_url); ?>">Tôi đã có tài khoản</a>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>
<footer class="footer">
  <div class="container footer-meta">
    <span>© <?php echo esc_html((string) date('Y')); ?> Ai trồng cây</span>
  </div>
</footer>
<script src="../assets/js/main.js"></script>
</body>
</html>
