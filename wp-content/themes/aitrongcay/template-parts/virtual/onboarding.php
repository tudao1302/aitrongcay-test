<?php if (! defined('ABSPATH')) { exit; } ?>
<section class="section-tight eco-register-shell">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    main > .section, main > .section > .container, main article.page{max-width:none !important;width:100% !important;padding:0 !important;margin:0 !important;background:transparent !important;box-shadow:none !important;border:none !important}
    main article.page > .eyebrow, main article.page > h1{display:none !important}
    main article.page > .entry-content{margin:0 !important}
    .eco-register-shell{background:#121411;min-height:100vh;padding:0;position:relative;overflow:hidden}
    .eco-register-page{background:#121411;color:#e3e3de;min-height:100vh;display:flex;flex-direction:column;position:relative;font-family:'Manrope',sans-serif}
    .eco-register-bg{position:absolute;inset:0}
    .eco-register-bg img{width:100%;height:100%;object-fit:cover;opacity:.4}
    .eco-register-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(180deg, rgba(18,20,17,.8), transparent, rgba(18,20,17,1))}
    .eco-register-header{position:fixed;top:0;left:0;right:0;z-index:20;display:flex;justify-content:space-between;align-items:center;padding:16px 32px;background:rgba(18,20,17,.4);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
    .eco-register-logo{font-family:'Noto Serif',serif;font-size:30px;font-weight:700;color:#6FDBA8}
    .eco-register-help{color:#bdcac0;font-size:24px;line-height:1}
    .eco-register-main{position:relative;z-index:1;flex:1;display:flex;align-items:center;justify-content:center;padding:96px 24px 48px}
    .eco-register-wrap{width:min(100%, 640px);position:relative}
    .eco-register-card{background:rgba(51,53,50,.4);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border-radius:32px;padding:40px;box-shadow:0 24px 60px rgba(0,0,0,.35), inset 0 0 0 1px rgba(62,73,66,.1)}
    .eco-register-head{text-align:center;margin-bottom:40px}
    .eco-register-head h1{font-family:'Noto Serif',serif;font-size:54px;line-height:1.02;margin:0 0 12px;color:#e3e3de}
    .eco-register-head p{margin:0;color:#bdcac0;font-weight:300;letter-spacing:.04em;font-style:italic}
    .eco-register-form{display:grid;gap:24px}
    .eco-register-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px}
    .eco-register-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.22em;color:#ffb68c;margin:0 0 8px 4px}
    .eco-register-form input,.eco-register-form select{width:100%;background:#292b27;border:none;border-radius:14px;padding:16px 18px;color:#e3e3de;outline:none;box-shadow:none;-webkit-appearance:none;appearance:none}
    .eco-register-form input::placeholder{color:rgba(189,202,192,.4)}
    .eco-register-form input:focus,.eco-register-form select:focus{box-shadow:0 0 0 1px rgba(111,219,168,.4)}
    .eco-register-form select option{background:#292b27;color:#e3e3de}
    .eco-register-btn{width:100%;background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824;font-weight:800;padding:18px;border:none;border-radius:999px;font-size:18px;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 0 20px rgba(111,219,168,.2);transition:all .3s cubic-bezier(.34,1.56,.64,1);cursor:pointer}
    .eco-register-btn:hover{transform:scale(1.02);filter:brightness(1.08)}
    .eco-register-foot{text-align:center;padding-top:8px;color:#bdcac0}
    .eco-register-foot a{color:#6FDBA8;text-decoration:underline;text-underline-offset:6px}
    .eco-register-footer{position:fixed;bottom:0;left:0;right:0;z-index:20;display:flex;justify-content:space-between;align-items:center;padding:20px 48px;background:transparent;opacity:.6}
    .eco-register-footer a,.eco-register-footer span{font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#bdcac0}
    .eco-register-deco-right,.eco-register-deco-left{display:none}
    .eco-register-card .notice{margin-bottom:18px}
    @media (min-width: 900px){
      .eco-register-deco-right,.eco-register-deco-left{display:block;position:absolute;pointer-events:none;opacity:.2;line-height:1}
      .eco-register-deco-right{top:22%;right:-72px;font-size:120px;color:#6FDBA8}
      .eco-register-deco-left{bottom:8%;left:-92px;font-size:160px;color:#ffe16d}
    }
    @media (max-width:760px){
      .eco-register-header{padding:14px 18px}
      .eco-register-logo{font-size:24px}
      .eco-register-card{padding:28px 18px}
      .eco-register-head h1{font-size:40px}
      .eco-register-grid{grid-template-columns:1fr}
      .eco-register-footer{position:relative;flex-direction:column;gap:12px;padding:20px}
      .eco-register-main{padding:88px 16px 24px}
    }
  </style>
  <div class="eco-register-page">
    <div class="eco-register-bg"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuBEiHA0nmrtlRWEoDLWjYb6bwYFeAb6nWalheBT8jnzd_5jqOGBHnJJlSSsrI1SkkHcPUt2zS3v09KBNwiIF2gr3UJEoVDqZNIblql8ElDX5HwZFLZXLAr6lMJwdZ9SoI-EIT25uhbLljsgN5ezlzgeJJ8YL_cSxnr4Ia0vsqBTiWTFleHvT_5-jHXafVfwSkTZkaZvOBnLHN8iA9lK8EIcXi1SFXdtSfdtNBRHou04oLHj6a_q1BYx05DTWKz87Vat5Mt5T5DnRxFQ" alt="Nền lá cây tối, tạo cảm giác một khu vườn số yên tĩnh"></div>
    <header class="eco-register-header"><div class="eco-register-logo">Ai trồng cây</div><div class="eco-register-help">❔</div></header>
    <main class="eco-register-main">
      <div class="eco-register-wrap">
        <div class="eco-register-card">
          <div class="eco-register-head"><h1>Bắt đầu khu vườn số</h1><p>sử dụng AI và robot chăm sóc rau và hoa online,<br>nhận kết quả thực tế tận nhà.</p></div>
          <?php aitrongcay_render_auth_notice(); ?>
          <form class="eco-register-form" method="post" action="<?php echo esc_url(aitrongcay_login_action_url()); ?>">
            <input type="hidden" name="action" value="aitrongcay_register_submit">
            <?php wp_nonce_field('aitrongcay_register_submit', 'aitrongcay_register_nonce'); ?>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/onboarding/')); ?>">
            <?php if (isset($_GET['ref'])) : ?>
              <input type="hidden" name="ref" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['ref']))); ?>">
            <?php endif; ?>
            <div class="eco-register-grid">
              <div><label class="eco-register-label" for="register-salutation">Danh xưng</label><select id="register-salutation" name="salutation" required><option value="anh">Anh</option><option value="chị" selected>Chị</option></select></div>
              <div><label class="eco-register-label" for="register-phone">Số điện thoại</label><input id="register-phone" name="phone" type="tel" placeholder="09xx xxx xxx" autocomplete="tel" required></div>
            </div>
            <div><label class="eco-register-label" for="register-full-name">Họ và tên</label><input id="register-full-name" name="full_name" placeholder="Marry" autocomplete="name" required></div>
            <div><label class="eco-register-label" for="register-email-auth">Email</label><input id="register-email-auth" name="email" type="email" placeholder="anhchi@email.com" autocomplete="email" required></div>
            <div class="eco-register-grid">
              <div><label class="eco-register-label" for="register-password-auth">Mật khẩu</label><input id="register-password-auth" name="password" type="password" placeholder="••••••••" autocomplete="new-password" required></div>
              <div><label class="eco-register-label" for="register-password-confirm">Xác nhận mật khẩu</label><input id="register-password-confirm" type="password" placeholder="••••••••" autocomplete="new-password" required oninput="this.setCustomValidity(this.value!==document.getElementById('register-password-auth').value ? 'Mật khẩu chưa khớp' : '')"></div>
            </div>
            <div style="padding-top:12px"><button class="eco-register-btn" type="submit">Tạo tài khoản <span>→</span></button></div>
            <div class="eco-register-foot">Đã có tài khoản? <a href="<?php echo esc_url(home_url('/dang-nhap/')); ?>">Đăng nhập tại đây</a></div>
          </form>
        </div>
        <div class="eco-register-deco-right">🌿</div>
        <div class="eco-register-deco-left">🌱</div>
      </div>
    </main>
    <footer class="eco-register-footer"><span>© 2026 Ai trồng cây · khu vườn số cho gia đình</span><div style="display:flex;gap:24px"><a href="#">Chính sách riêng tư</a><a href="#">Điều khoản</a><a href="#">Báo cáo bền vững</a></div></footer>
  </div>
</section>
