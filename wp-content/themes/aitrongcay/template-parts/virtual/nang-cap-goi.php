<?php
declare(strict_types=1);

if (! is_user_logged_in()) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

$current_user = wp_get_current_user();
$user_email = $current_user->user_email;
$user_name = $current_user->display_name ?: $current_user->user_login;
$user_phone = (string) get_user_meta($current_user->ID, 'aitrongcay_phone', true);
$user_phone = (string) get_user_meta($current_user->ID, 'aitrongcay_phone', true);
$active_plan = function_exists('aitrongcay_get_active_subscription_plan') ? aitrongcay_get_active_subscription_plan($current_user->ID) : ['id' => ''];
$plan_id = $active_plan['id'];

$account_nav_items = array_map(static function (array $item) {
    return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => (string) ($item['url'] ?? '#')];
}, aitrongcay_eco_nav_items());

set_query_var('aitr_eco_shell', [
    'title' => 'Nâng cấp gói',
    'active' => 'tai-khoan',
    'side_title' => 'Ai trồng cây',
    'side_subtitle' => 'Hồ sơ khu vườn số',
    'side_badge' => '⭐',
    'top_icons' => ['🔔', '⚙️'],
    'search' => null,
    'nav' => $account_nav_items,
]);

get_template_part('template-parts/site/eco-shell-start');
set_query_var('aitr_eco_hero', ['title' => 'Nâng cấp trải nghiệm vườn số', 'body' => 'Chọn gói dịch vụ phù hợp để mở khóa toàn bộ khả năng chăm sóc và giám sát của hệ thống.']);
get_template_part('template-parts/site/eco-hero');
?>
<section class="section-tight" style="background:transparent;padding:0;min-height:auto">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-bottom:48px}
    .eco-pricing-card{background:rgba(26,28,25,.94);border:1px solid rgba(255,255,255,.05);border-radius:32px;padding:32px;box-shadow:0 24px 52px rgba(0,0,0,.22);display:flex;flex-direction:column;position:relative;overflow:hidden;transition:.3s}
    .eco-pricing-card:hover{transform:translateY(-5px)}
    .eco-pricing-card.is-popular{background:linear-gradient(180deg,rgba(18,28,21,.98),rgba(26,28,25,.94));border:1px solid rgba(111,219,168,.18)}
    .eco-pricing-card.is-popular::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#31a375,#6fdba8)}
    .eco-pricing-badge{display:inline-flex;padding:6px 12px;border-radius:10px;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase;margin-bottom:18px;align-self:flex-start}
    .eco-pricing-badge.basic{background:rgba(255,255,255,.1);color:#e3e3de}
    .eco-pricing-badge.prime{background:#ffe16d;color:#221b00}
    .eco-pricing-badge.premium{background:linear-gradient(135deg,#a855f7,#d946ef);color:#fff}
    .eco-pricing-title{font-family:'Noto Serif',serif;font-size:32px;color:#fff;margin:0 0 12px;line-height:1.1}
    .eco-pricing-desc{color:#bdcac0;line-height:1.6;font-size:14px;margin:0 0 24px;min-height:66px}
    .eco-pricing-price{font-size:36px;font-weight:800;color:#fff;margin:0 0 24px;display:flex;align-items:baseline;gap:4px}
    .eco-pricing-price span{font-size:14px;color:#bdcac0;font-weight:500}
    .eco-pricing-features{list-style:none;padding:0;margin:0 0 32px;flex:1;display:grid;gap:14px}
    .eco-pricing-features li{display:flex;align-items:flex-start;gap:12px;font-size:14px;color:#e3e3de;line-height:1.5}
    .eco-pricing-features li::before{content:'✓';color:#6fdba8;font-weight:900;font-size:16px}
    .eco-pricing-features li.unavailable{color:rgba(227,227,222,.4)}
    .eco-pricing-features li.unavailable::before{content:'−';color:rgba(227,227,222,.3)}
    
    .eco-pricing-action{padding:16px;border-radius:999px;font-weight:800;font-size:15px;text-align:center;cursor:pointer;border:none;width:100%;transition:.2s}
    .eco-pricing-action.primary{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013}
    .eco-pricing-action.primary:hover{filter:brightness(1.1);box-shadow:0 10px 24px rgba(111,219,168,.25)}
    .eco-pricing-action.secondary{background:rgba(51,53,50,.7);color:#e3e3de;border:1px solid rgba(255,255,255,.1)}
    .eco-pricing-action.secondary:hover{background:rgba(51,53,50,.9);color:#fff}
    
    /* Checkout Modal Styles */
    .eco-checkout-modal{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px}
    .eco-checkout-modal[hidden]{display:none !important}
    .eco-checkout-backdrop{position:absolute;inset:0;background:rgba(7,12,9,.8);backdrop-filter:blur(8px)}
    .eco-checkout-dialog{position:relative;background:#1a1c19;width:100%;max-width:540px;border-radius:32px;box-shadow:0 32px 64px rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;max-height:90vh}
    .eco-checkout-head{padding:24px 32px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center}
    .eco-checkout-head h3{margin:0;font-size:20px;font-weight:800;color:#fff}
    .eco-checkout-close{background:none;border:none;color:#bdcac0;font-size:24px;cursor:pointer;width:32px;height:32px;display:grid;place-items:center;border-radius:50%}
    .eco-checkout-close:hover{background:rgba(255,255,255,.1);color:#fff}
    .eco-checkout-body{padding:32px;overflow-y:auto;overscroll-behavior:contain}
    
    .eco-checkout-summary{background:rgba(51,53,50,.4);border-radius:18px;padding:20px;margin-bottom:24px}
    .eco-checkout-summary-row{display:flex;justify-content:space-between;margin-bottom:8px;font-size:15px;color:#e3e3de}
    .eco-checkout-summary-row:last-child{margin-bottom:0;padding-top:12px;border-top:1px dashed rgba(255,255,255,.1);font-weight:800;font-size:18px;color:#6fdba8;margin-top:12px}
    
    .eco-checkout-form{display:grid;gap:18px}
    .eco-checkout-field label{display:block;margin:0 0 8px 4px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.18em;color:#bdcac0}
    .eco-checkout-field input{width:100%;background:#292b27;border:1px solid rgba(255,255,255,.05);border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none;box-sizing:border-box}
    .eco-checkout-field input:focus{border-color:rgba(111,219,168,.4)}
    
    .eco-checkout-foot{padding:24px 32px;border-top:1px solid rgba(255,255,255,.06);display:flex;justify-content:flex-end;gap:12px;background:rgba(26,28,25,.9)}
    .eco-checkout-submit{padding:14px 28px;border-radius:999px;font-weight:800;font-size:15px;border:none;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;cursor:pointer}
    .eco-checkout-submit:disabled{opacity:0.6;cursor:not-allowed}
    
    /* Result state */
    .eco-checkout-result{text-align:center;padding:24px 0}
    .eco-checkout-qr{width:220px;height:220px;margin:24px auto;background:#fff;border-radius:24px;padding:12px}
    .eco-checkout-qr img{width:100%;height:100%;border-radius:12px;display:block}
    .eco-checkout-bank-info{background:rgba(51,53,50,.3);border-radius:16px;padding:18px;margin-bottom:24px;text-align:left}
    .eco-checkout-bank-row{display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px}
    .eco-checkout-bank-row:last-child{margin-bottom:0}
    .eco-checkout-bank-label{color:#bdcac0}
    .eco-checkout-bank-val{color:#fff;font-weight:700}
    .eco-checkout-bank-val.highlight{color:#ffb68c;font-size:16px;letter-spacing:1px}
  </style>

  <div class="eco-pricing-grid">
    <!-- Gói 1 -->
    <div class="eco-pricing-card <?php echo $plan_id === 'basic' ? 'is-popular' : ''; ?>">
      <?php if ($plan_id === 'basic'): ?>
      <div class="eco-pricing-badge prime">Gói hiện tại</div>
      <?php else: ?>
      <div class="eco-pricing-badge basic">Khởi đầu</div>
      <?php endif; ?>
      <h3 class="eco-pricing-title">Basic Seed</h3>
      <p class="eco-pricing-desc">Gói khởi đầu không sử dụng camera, phù hợp cho người mới bắt đầu chăm sóc theo dõi qua dữ liệu cơ bản.</p>
      <div class="eco-pricing-price">299.000đ <span>/ tháng</span></div>
      <ul class="eco-pricing-features">
        <li>Cập nhật ảnh chụp định kỳ hằng ngày</li>
        <li>Báo cáo độ ẩm, nhiệt độ, ánh sáng</li>
        <li>Trợ lý AI tư vấn mức độ cơ bản</li>
        <li class="unavailable">Camera giám sát trực tiếp 24/7</li>
        <li class="unavailable">Phân tích chuyên sâu về chất lượng đất</li>
      </ul>
      <?php if ($plan_id === 'basic'): ?>
      <button class="eco-pricing-action secondary" type="button" disabled style="opacity:0.6">Đang sử dụng</button>
      <?php else: ?>
      <button class="eco-pricing-action <?php echo $plan_id === '' ? 'primary' : 'secondary'; ?>" type="button" data-plan-id="basic" data-plan-name="Gói Basic Seed (Khởi đầu)" data-plan-price="299000">Đăng ký ngay</button>
      <?php endif; ?>
    </div>

    <!-- Gói 2 -->
    <div class="eco-pricing-card <?php echo ($plan_id === 'prime' || $plan_id === '') ? 'is-popular' : ''; ?>">
      <?php if ($plan_id === 'prime'): ?>
      <div class="eco-pricing-badge prime">Gói hiện tại</div>
      <?php else: ?>
      <div class="eco-pricing-badge prime" style="background:#6fdba8;color:#062013">Phổ biến</div>
      <?php endif; ?>
      <h3 class="eco-pricing-title">Verdant Prime</h3>
      <p class="eco-pricing-desc">Gói phổ biến nhất phù hợp cho một khu vườn gia đình với camera, dữ liệu cơ bản và AI đồng hành.</p>
      <div class="eco-pricing-price">699.000đ <span>/ tháng</span></div>
      <ul class="eco-pricing-features">
        <li>Camera giám sát sinh thái trực tiếp 24/7</li>
        <li>Hệ thống cảnh báo tưới tiêu tự động</li>
        <li>Phân tích chuyên sâu tình trạng đất và nước</li>
        <li>AI đồng hành toàn thời gian & nhắc lịch</li>
        <li class="unavailable">Kỹ sư nông nghiệp tư vấn trực tiếp</li>
      </ul>
      <?php if ($plan_id === 'prime'): ?>
      <button class="eco-pricing-action secondary" type="button" disabled style="opacity:0.6">Đang sử dụng</button>
      <?php else: ?>
      <button class="eco-pricing-action <?php echo ($plan_id === '' || $plan_id === 'basic') ? 'primary' : 'secondary'; ?>" type="button" data-plan-id="prime" data-plan-name="Gói Verdant Prime (Phổ biến)" data-plan-price="699000">Nâng cấp gói</button>
      <?php endif; ?>
    </div>

    <!-- Gói 3 -->
    <div class="eco-pricing-card <?php echo $plan_id === 'enterprise' ? 'is-popular' : ''; ?>">
      <?php if ($plan_id === 'enterprise'): ?>
      <div class="eco-pricing-badge prime">Gói hiện tại</div>
      <?php else: ?>
      <div class="eco-pricing-badge premium">Chuyên nghiệp</div>
      <?php endif; ?>
      <h3 class="eco-pricing-title">Eco Enterprise</h3>
      <p class="eco-pricing-desc">Dành cho quy mô trang trại lớn với hệ thống đa thiết bị, API tích hợp và chuyên gia tư vấn.</p>
      <div class="eco-pricing-price">1.999.000đ <span>/ tháng</span></div>
      <ul class="eco-pricing-features">
        <li>Quản lý không giới hạn camera và cảm biến</li>
        <li>Dashboard vận hành cho toàn bộ trang trại</li>
        <li>Tích hợp API với hệ thống quản lý có sẵn</li>
        <li>Hỗ trợ kỹ thuật ưu tiên 24/7</li>
        <li>1 buổi tư vấn 1-1 mỗi tháng với kỹ sư nông nghiệp</li>
      </ul>
      <?php if ($plan_id === 'enterprise'): ?>
      <button class="eco-pricing-action secondary" type="button" disabled style="opacity:0.6">Đang sử dụng</button>
      <?php else: ?>
      <button class="eco-pricing-action <?php echo $plan_id === 'prime' ? 'primary' : 'secondary'; ?>" type="button" data-plan-id="enterprise" data-plan-name="Gói Eco Enterprise (Chuyên nghiệp)" data-plan-price="1999000">Liên hệ nâng cấp</button>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Checkout Modal -->
<div class="eco-checkout-modal" id="upgrade-modal" hidden>
  <div class="eco-checkout-backdrop" data-close-modal></div>
  <div class="eco-checkout-dialog" role="dialog" aria-modal="true">
    <div class="eco-checkout-head">
      <h3 id="upgrade-modal-title">Xác nhận thanh toán</h3>
      <button class="eco-checkout-close" type="button" data-close-modal>×</button>
    </div>
    
    <!-- View 1: Form -->
    <div id="upgrade-form-view">
      <div class="eco-checkout-body">
        <div class="eco-checkout-summary">
          <div class="eco-checkout-summary-row">
            <span>Dịch vụ</span>
            <span id="summary-plan-name">...</span>
          </div>
          <div class="eco-checkout-summary-row">
            <span>Chu kỳ</span>
            <span>1 Tháng</span>
          </div>
          <div class="eco-checkout-summary-row">
            <span>Tổng thanh toán</span>
            <span id="summary-plan-price">...</span>
          </div>
        </div>
        
        <form id="upgrade-form" class="eco-checkout-form">
          <input type="hidden" id="upgrade-plan-id" name="plan_id">
          <input type="hidden" id="upgrade-plan-price" name="plan_price">
          
          <div class="eco-checkout-field">
            <label for="upgrade-name">Họ và tên</label>
            <input id="upgrade-name" name="customer_name" value="<?php echo esc_attr($user_name); ?>" required>
          </div>
          <div class="eco-checkout-field">
            <label for="upgrade-phone">Số điện thoại</label>
            <input id="upgrade-phone" name="customer_phone" value="<?php echo esc_attr($user_phone); ?>" required>
          </div>
        </form>
      </div>
      <div class="eco-checkout-foot">
        <button class="eco-pricing-action secondary" type="button" style="width:auto;padding:12px 24px" data-close-modal>Huỷ</button>
        <button class="eco-checkout-submit" type="button" id="upgrade-submit-btn">Tiến hành thanh toán</button>
      </div>
    </div>
    
    <!-- View 2: Result (QR) -->
    <div id="upgrade-result-view" hidden>
      <div class="eco-checkout-body">
        <div class="eco-checkout-result">
          <h2 style="margin:0 0 8px;color:#6fdba8">Đăng ký thành công!</h2>
          <p style="color:#bdcac0;margin:0 0 24px;font-size:14px">Vui lòng thanh toán qua mã QR dưới đây để kích hoạt gói dịch vụ.</p>
          
          <div class="eco-checkout-qr" id="upgrade-qr-container">
            <!-- QR code goes here -->
          </div>
          
          <div class="eco-checkout-bank-info">
            <div class="eco-checkout-bank-row">
              <span class="eco-checkout-bank-label">Ngân hàng</span>
              <span class="eco-checkout-bank-val" id="result-bank-name">...</span>
            </div>
            <div class="eco-checkout-bank-row">
              <span class="eco-checkout-bank-label">Số tài khoản</span>
              <span class="eco-checkout-bank-val" id="result-account-number">...</span>
            </div>
            <div class="eco-checkout-bank-row">
              <span class="eco-checkout-bank-label">Chủ tài khoản</span>
              <span class="eco-checkout-bank-val" id="result-account-name">...</span>
            </div>
            <div class="eco-checkout-bank-row" style="margin-top:12px;padding-top:12px;border-top:1px dashed rgba(255,255,255,.1)">
              <span class="eco-checkout-bank-label">Nội dung CK</span>
              <span class="eco-checkout-bank-val highlight" id="result-order-id">...</span>
            </div>
            <div class="eco-checkout-bank-row">
              <span class="eco-checkout-bank-label">Số tiền</span>
              <span class="eco-checkout-bank-val highlight" id="result-total-fmt" style="color:#6fdba8">...</span>
            </div>
          </div>
          
          <div style="background:rgba(255,182,140,.1);border-left:3px solid #ffb68c;padding:12px 16px;border-radius:0 8px 8px 0;font-size:12px;color:#ffb68c;text-align:left">
            ⚠️ Lưu ý: Bạn cần ghi chính xác <strong>Nội dung chuyển khoản</strong> để hệ thống tự động xác nhận và kích hoạt gói ngay lập tức.
          </div>
        </div>
      </div>
      <div class="eco-checkout-foot" style="justify-content:center">
        <a class="eco-pricing-action secondary" href="<?php echo esc_url(home_url('/tai-khoan/')); ?>" style="text-decoration:none">Trở về Tài khoản</a>
      </div>
    </div>
    
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('upgrade-modal');
  const formView = document.getElementById('upgrade-form-view');
  const resultView = document.getElementById('upgrade-result-view');
  const submitBtn = document.getElementById('upgrade-submit-btn');
  
  const form = document.getElementById('upgrade-form');
  const planIdInput = document.getElementById('upgrade-plan-id');
  const planPriceInput = document.getElementById('upgrade-plan-price');
  
  const elPlanName = document.getElementById('summary-plan-name');
  const elPlanPrice = document.getElementById('summary-plan-price');
  
  let currentPlanName = '';

  // Format currency
  function formatMoney(n) {
    return Number(n).toLocaleString('vi-VN') + 'đ';
  }

  // Open modal
  document.querySelectorAll('[data-plan-id]').forEach(btn => {
    btn.addEventListener('click', function() {
      const planId = this.getAttribute('data-plan-id');
      const planName = this.getAttribute('data-plan-name');
      const planPrice = parseInt(this.getAttribute('data-plan-price') || '0', 10);
      
      currentPlanName = planName;
      
      planIdInput.value = planId;
      planPriceInput.value = planPrice;
      
      elPlanName.textContent = planName;
      elPlanPrice.textContent = formatMoney(planPrice);
      
      formView.hidden = false;
      resultView.hidden = true;
      modal.hidden = false;
    });
  });

  // Close modal
  document.querySelectorAll('[data-close-modal]').forEach(btn => {
    btn.addEventListener('click', function() {
      modal.hidden = true;
    });
  });

  // Submit order
  submitBtn.addEventListener('click', async function() {
    if (!form.reportValidity()) return;
    
    const name = document.getElementById('upgrade-name').value.trim();
    const phone = document.getElementById('upgrade-phone').value.trim();
    const price = parseInt(planPriceInput.value, 10);
    const planId = planIdInput.value;
    
    if (!name || !phone) {
      alert("Vui lòng điền đủ thông tin.");
      return;
    }
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Đang xử lý...';
    
    try {
      const items = [{
        name: currentPlanName,
        price: price,
        qty: 1,
        category: 'Dịch vụ'
      }];
      
      const body = new URLSearchParams({
        action: 'aitrongcay_place_order',
        nonce: '<?php echo wp_create_nonce("aitrongcay_portal_actions"); ?>',
        customer_name: name,
        customer_phone: phone,
        customer_email: '<?php echo esc_js($user_email); ?>',
        plant_name: 'Nâng cấp ' + currentPlanName,
        total: price,
        items: JSON.stringify(items),
        note: 'Đơn đăng ký gói dịch vụ tự động từ Portal'
      });

      const res = await fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body
      });
      
      const data = await res.json();
      
      if (!data.success) {
        throw new Error(data.data.message || 'Có lỗi xảy ra khi tạo đơn.');
      }
      
      // Show result
      const info = data.data;
      document.getElementById('result-bank-name').textContent = info.bank_name;
      document.getElementById('result-account-number').textContent = info.account_number;
      document.getElementById('result-account-name').textContent = info.account_name;
      document.getElementById('result-order-id').textContent = info.order_id;
      document.getElementById('result-total-fmt').textContent = info.total_fmt + 'đ';
      
      const qrContainer = document.getElementById('upgrade-qr-container');
      if (info.qr_url) {
        qrContainer.innerHTML = '<img src="' + info.qr_url + '" alt="QR Thanh toán">';
      } else {
        qrContainer.innerHTML = '<div style="display:grid;place-items:center;height:100%;color:#6b7280;font-size:14px;text-align:center">Quý khách vui lòng chuyển khoản thủ công</div>';
      }
      
      formView.hidden = true;
      resultView.hidden = false;
      
    } catch (e) {
      alert(e.message);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Tiến hành thanh toán';
    }
  });
})();
</script>

<?php get_template_part('template-parts/site/eco-shell-end'); ?>
