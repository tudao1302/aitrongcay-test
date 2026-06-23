<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/wp-load.php';

if (is_user_logged_in()) {
    wp_safe_redirect(home_url('/portal/dashboard-2/'));
    exit;
}

$auth_status  = sanitize_text_field(wp_unslash($_GET['auth_status'] ?? ''));
$action_url   = esc_url(admin_url('admin-post.php'));
$redirect_to  = esc_url(home_url('/portal/dashboard-2/'));
$register_url = esc_url(home_url('/onboarding/'));
$demo_url     = esc_url(home_url('/portal/dashboard-2/'));

$status_map = [
    'login-error'      => ['error',   'Email / số điện thoại hoặc mật khẩu chưa đúng. Anh/chị kiểm tra lại nhé.'],
    'register-success' => ['success', 'Đăng ký thành công! Đang mở khu vườn của anh/chị…'],
    'logged-out'       => ['info',    'Anh/chị đã đăng xuất thành công.'],
    'register-invalid' => ['error',   'Thông tin đăng ký chưa hợp lệ. Vui lòng thử lại.'],
];
[$msg_type, $msg_text] = $status_map[$auth_status] ?? [null, null];
?><!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập — Ai trồng cây</title>
  <meta name="description" content="Đăng nhập vào không gian riêng của khu vườn để xem webcam, care log, hồ sơ chất lượng và tiến độ mùa vụ.">
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
      <a class="btn btn-secondary" href="<?php echo esc_url($register_url); ?>">Đăng ký</a>
      <a class="btn btn-primary" href="<?php echo esc_url($demo_url); ?>">Mở khu vườn của bạn</a>
    </div>
    <button class="btn btn-secondary menu-toggle" data-mobile-toggle>Menu</button>
  </div>
  <div class="container" data-mobile-panel style="display:none"></div>
</header>
<main>
  <section class="section-hero auth-hero">
    <div class="container auth-shell">
      <div class="auth-copy">
        <span class="eyebrow">Đăng nhập</span>
        <h1>Quay lại khu vườn của anh/chị.</h1>
        <p class="lead">Mở lại webcam, xem care log hôm nay, kiểm tra hồ sơ chất lượng hoặc chỉ đơn giản nhìn cây một chút cho yên lòng.</p>
      </div>
      <div class="card auth-card auth-card-strong">
        <?php if ($msg_type): ?>
          <div class="form-result is-<?php echo esc_attr($msg_type); ?>" style="margin-bottom:16px">
            <?php echo esc_html((string) $msg_text); ?>
          </div>
        <?php endif; ?>
        <form class="auth-form" method="post" action="<?php echo $action_url; ?>">
          <?php wp_nonce_field('aitrongcay_login_submit', 'aitrongcay_login_nonce'); ?>
          <input type="hidden" name="action"      value="aitrongcay_login_submit">
          <input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>">
          <div class="form-stack">
            <div>
              <label for="login-identity">Email hoặc số điện thoại</label>
              <input id="login-identity" name="identity"
                     placeholder="anhchi@email.com hoặc 09xx xxx xxx"
                     autocomplete="username" required>
            </div>
            <div>
              <label for="login-password">Mật khẩu</label>
              <input id="login-password" name="password" type="password"
                     placeholder="••••••••" autocomplete="current-password" required>
            </div>
          </div>
          <div class="auth-inline-meta">
            <label class="check-row">
              <input type="checkbox" name="remember" value="1" checked>
              <span>Ghi nhớ trên thiết bị này</span>
            </label>
            <a class="small-link" href="<?php echo esc_url($register_url); ?>">Chưa có tài khoản?</a>
          </div>
          <div class="auth-actions">
            <button class="btn btn-primary" type="submit">Vào khu vườn</button>
            <a class="btn btn-secondary" href="<?php echo esc_url($demo_url); ?>">Xem khu vườn mẫu</a>
          </div>
          <div class="form-result" aria-live="polite"></div>
        </form>
      </div>
    </div>
  </section>
</main>
<footer class="footer">
  <div class="container footer-top">
    <div class="footer-card">
      <h3>CÔNG TY CỔ PHẦN NGHIÊN CỨU GIẢI PHÁP VÀ PHÁT TRIỂN CÔNG NGHỆ XANH</h3>
      <p>Địa chỉ: Số 180A, đường Âu Cơ, Phường Tứ Liên, Quận Tây Hồ, Thành phố Hà Nội, Việt Nam</p>
      <p>Điện thoại: 0983.660.988 – 0876.666.114</p>
    </div>
    <div class="footer-card">
      <h3>Ai trồng cây</h3>
      <p>Một lối vào nhẹ nhàng để trở lại với khu vườn của mình.</p>
    </div>
  </div>
  <div class="container footer-grid">
    <div>
      <div class="logo" style="margin-bottom:12px"><span class="logo-badge">🌿</span><span>Ai trồng cây</span></div>
      <p>Cổng vào để tiếp tục theo dõi webcam, care log, AI gardener và hồ sơ chất lượng của khu vườn.</p>
    </div>
    <div>
      <h3>Khu vườn</h3>
      <p>
        <a href="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">Tổng quan vườn</a><br>
        <a href="<?php echo esc_url(home_url('/portal/kho-nong-cu-2/')); ?>">Kho nông cụ</a>
      </p>
    </div>
    <div>
      <h3>Bắt đầu</h3>
      <p>
        <a href="<?php echo esc_url(home_url('/dang-ky-tu-van/')); ?>">Đăng ký tư vấn</a><br>
        <a href="<?php echo esc_url(home_url('/onboarding/')); ?>">Tạo tài khoản</a>
      </p>
    </div>
    <div>
      <h3>Website</h3>
      <p>
        <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><br>
        <a href="<?php echo esc_url(home_url('/cach-hoat-dong/')); ?>">Cách hoạt động</a>
      </p>
    </div>
  </div>
  <div class="container footer-meta">
    <span>© <?php echo esc_html((string) date('Y')); ?> Ai trồng cây</span>
  </div>
</footer>
<script src="../assets/js/main.js"></script>
</body>
</html>
