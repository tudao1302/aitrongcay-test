<?php if (! defined('ABSPATH')) { exit; } ?>
<section class="section-tight eco-login-shell">
  <style>
    main > .section, main > .section > .container, main article.page{max-width:none !important;width:100% !important;padding:0 !important;margin:0 !important;background:transparent !important;box-shadow:none !important;border:none !important}
    main article.page > .eyebrow, main article.page > h1{display:none !important}
    main article.page > .entry-content{margin:0 !important}
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-login-shell{background:#121411;min-height:100vh;padding:0;position:relative;overflow:hidden}
    .eco-login-page{background:#121411;color:#e3e3de;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;font-family:'Manrope',sans-serif}
    .eco-login-bg{position:absolute;inset:0}
    .eco-login-bg img{width:100%;height:100%;object-fit:cover;opacity:.4}
    .eco-login-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(to top, rgba(18,20,17,1), transparent, rgba(18,20,17,.6))}
    .eco-login-main{position:relative;z-index:1;width:100%;max-width:480px;padding:48px 24px}
    .eco-login-brand{text-align:center;margin-bottom:40px}
    .eco-login-brand-icon{display:flex;justify-content:center;margin-bottom:24px}
    .eco-login-brand-icon>div{width:64px;height:64px;border-radius:999px;background:#333532;display:grid;place-items:center;box-shadow:inset 0 0 0 1px rgba(62,73,66,.3)}
    .eco-login-brand-mark{font-size:34px;line-height:1;color:#6FDBA8}
    .eco-login-brand h1{font-family:'Noto Serif',serif;font-size:42px;font-weight:700;line-height:1.1;letter-spacing:-.02em;margin:0;color:#e3e3de}
    .eco-login-brand p{margin:8px 0 0;color:#bdcac0;font-weight:300;letter-spacing:.04em}
    .eco-login-card{background:rgba(51,53,50,.4);backdrop-filter:blur(32px);-webkit-backdrop-filter:blur(32px);border-radius:24px;padding:32px;box-shadow:inset 0 0 0 1px rgba(62,73,66,.1),0 24px 60px rgba(0,0,0,.35)}
    .eco-login-form{display:grid;gap:24px}
    .eco-login-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.22em;color:#ffb68c;margin:0 0 8px 4px}
    .eco-login-field{position:relative}
    .eco-login-field input{width:100%;background:#292b27;border:none;border-radius:14px;padding:16px 16px 16px 48px;color:#e3e3de;outline:none;box-shadow:none}
    .eco-login-field input::placeholder{color:rgba(189,202,192,.4)}
    .eco-login-field input:focus{box-shadow:0 0 0 1px rgba(111,219,168,.4)}
    .eco-login-field .icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#bdcac0;pointer-events:none}
    .eco-login-field:focus-within .icon{color:#6FDBA8}
    .eco-login-row{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:0 4px}
    .eco-login-row a{font-size:10px;letter-spacing:.08em;text-transform:uppercase;color:rgba(111,219,168,.7);text-decoration:none}
    .eco-login-row a:hover{color:#6FDBA8}
    .eco-login-btn{width:100%;background:linear-gradient(135deg,#31A375 0%, #6FDBA8 100%);color:#003824;font-weight:800;padding:16px;border:none;border-radius:14px;box-shadow:0 0 20px rgba(111,219,168,.3);transition:.2s ease;cursor:pointer}
    .eco-login-btn:hover{box-shadow:0 0 35px rgba(111,219,168,.5);transform:scale(1.02)}
    .eco-login-btn:active{transform:scale(.98)}
    .eco-login-divider{position:relative;margin:32px 0}
    .eco-login-divider:before{content:'';position:absolute;left:0;right:0;top:50%;border-top:1px solid rgba(62,73,66,.2)}
    .eco-login-divider span{position:relative;display:inline-block;left:50%;transform:translateX(-50%);padding:0 16px;color:rgba(189,202,192,.6);font-size:11px;letter-spacing:.2em;text-transform:uppercase}
    .eco-login-social{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .eco-login-social button{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border-radius:14px;background:#0d0f0c;border:1px solid rgba(62,73,66,.1);color:#e3e3de;transition:.2s ease}
    .eco-login-social button:hover{background:#1a1c19}
    .eco-login-reset{margin-top:22px}
    .eco-login-foot{text-align:center;margin-top:28px;color:#bdcac0;font-size:14px}
    .eco-login-foot a{color:#ffe16d;font-weight:600;text-decoration:underline;text-underline-offset:4px}
    .eco-login-mini-footer{position:fixed;bottom:0;left:0;right:0;display:flex;justify-content:center;align-items:center;padding:20px 24px;opacity:.45;z-index:1;pointer-events:none}
    .eco-login-mini-footer span{font-size:10px;letter-spacing:.3em;text-transform:uppercase;color:rgba(189,202,192,.75)}
    .eco-login-card .notice,.eco-login-card .account-notice{margin-bottom:18px}
    @media (max-width: 640px){
      .eco-login-main{padding:32px 18px 90px}
      .eco-login-brand h1{font-size:34px}
      .eco-login-card{padding:22px}
      .eco-login-social{grid-template-columns:1fr}
    }
  </style>
  <div class="eco-login-page">
    <div class="eco-login-bg"><img src="https://lh3.googleusercontent.com/aida-public/AB6AXuACdWrtGgjafiMiqYRtbcjhpH33qQ_rzFoRiiHQYeFDj6EL6eASktB6cRDW3VW0mJPcHEP6P10Da8k7vcjAYkNNoLa1zUbxCYQGkTGHc__hqZYf15F_RcWZgQSstjxSynuub32SeTllsTPfDcGJU84YUbxlW1UDEFHw8vT2fpSEmXTZNVUr3Exc3A2DXIzsQRZBdSQhI4x6UYFkzI0KQFJM1_qdCWuUrZ49_wnRJz1vHTIugpvQx7ZTbk4mlW_YO2X2q8tl41dYKVyF" alt="Nền lá cây tối, mờ, tạo cảm giác một khu vườn công nghệ yên tĩnh"></div>
    <main class="eco-login-main">
      <div class="eco-login-brand">
        <div class="eco-login-brand-icon"><div><span class="eco-login-brand-mark">🌿</span></div></div>
        <h1>Chào mừng người nông dân trở lại</h1>
        <p>Mở lại khu vườn số của mình.</p>
      </div>
      <div class="eco-login-card">
        <?php aitrongcay_render_auth_notice(); ?>
        <?php aitrongcay_render_account_notice(); ?>

        <form class="eco-login-form" method="post" action="<?php echo esc_url(aitrongcay_login_action_url()); ?>">
          <input type="hidden" name="action" value="aitrongcay_login_submit">
          <?php wp_nonce_field('aitrongcay_login_submit', 'aitrongcay_login_nonce'); ?>
          <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">

          <div>
            <label class="eco-login-label" for="email">Email đăng nhập</label>
            <div class="eco-login-field"><span class="icon">@</span><input id="email" name="identity" placeholder="anhchi@email.com" type="email" autocomplete="username" required></div>
          </div>

          <div>
            <div class="eco-login-row"><label class="eco-login-label" for="password" style="margin:0">Mật khẩu</label><a href="#reset-password">Quên mật khẩu?</a></div>
            <div class="eco-login-field"><span class="icon">🔒</span><input id="password" name="password" placeholder="••••••••" type="password" autocomplete="current-password" required></div>
          </div>

          <button class="eco-login-btn" type="submit">Vào khu vườn</button>
        </form>

        <div id="reset-password" class="eco-login-divider"><span>Cổng hỗ trợ</span></div>

        <div class="eco-login-social">
          <button type="button" aria-disabled="true" disabled><span>🟢</span><span>Google</span></button>
          <button type="button" aria-disabled="true" disabled><span></span><span>Apple ID</span></button>
        </div>

        <form class="eco-login-form eco-login-reset" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="aitrongcay_password_reset_request">
          <div>
            <label class="eco-login-label" for="reset-email">Đặt lại mật khẩu</label>
            <div class="eco-login-field"><span class="icon">✉️</span><input id="reset-email" name="email" placeholder="anhchi@gmail.com" autocomplete="email"></div>
          </div>
          <button class="eco-login-btn" type="submit">Gửi email đặt lại mật khẩu</button>
        </form>
      </div>

      <div class="eco-login-foot">Chưa có tài khoản? <a href="<?php echo esc_url(home_url('/onboarding/')); ?>">Tạo tài khoản</a></div>
    </main>

    <div class="eco-login-mini-footer"><span>Ai trồng cây · khu vườn số cho gia đình</span></div>
  </div>
</section>
