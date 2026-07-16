<?php
declare(strict_types=1);

if (! is_user_logged_in()) {
    wp_safe_redirect(home_url('/dang-nhap/?auth_status=login-required'));
    exit;
}

$current_user = wp_get_current_user();
$garden_key = function_exists('aitrongcay_resolve_active_garden_key') ? aitrongcay_resolve_active_garden_key($current_user instanceof WP_User ? $current_user : null) : '';

global $wpdb;
$orders_table = function_exists('aitrongcay_orders_table') ? aitrongcay_orders_table() : $wpdb->prefix . 'aitr_orders';
$orders = [];
$modals_html = '';
if ($wpdb->get_var("SHOW TABLES LIKE '{$orders_table}'") === $orders_table) {
    $orders = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$orders_table} WHERE user_id = %d ORDER BY created_at DESC LIMIT 50",
        $current_user->ID
    ));
}

// Giả lập action xử lý thanh toán (Ví dụ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_order') {
    $pay_order_id = absint($_POST['order_id'] ?? 0);
    if ($pay_order_id > 0) {
        // Removed mock auto-payment logic
        wp_safe_redirect(home_url('/portal/lich-su-giao-dich/'));
        exit;
    }
}

$account_nav_items = array_map(static function (array $item) use ($garden_key) {
    $url = (string) ($item['url'] ?? '#');
    if ($garden_key !== '') {
        $url = add_query_arg('garden', $garden_key, $url);
    }
    return ['key' => (string) ($item['key'] ?? ''), 'label' => (string) ($item['label'] ?? ''), 'url' => $url];
}, function_exists('aitrongcay_eco_nav_items') ? aitrongcay_eco_nav_items() : []);

set_query_var('aitr_eco_shell', [
    'title' => 'Sổ giao dịch',
    'active' => '',
    'side_title' => 'Ai trồng cây',
    'side_subtitle' => 'Tài sản & Hợp đồng',
    'side_badge' => '📜',
    'top_icons' => ['🔔', '⚙️'],
    'search' => null,
    'nav' => $account_nav_items,
]);

get_template_part('template-parts/site/eco-shell-start');
set_query_var('aitr_eco_hero', ['title' => 'Sổ Giao Dịch & Hợp Đồng', 'body' => 'Quản lý lịch sử thuê rack và các gói dịch vụ của bạn. Xem lại các khoản thanh toán, hoá đơn để theo dõi tiến trình trải nghiệm khu vườn số.']);
get_template_part('template-parts/site/eco-hero');
?>
<section class="section-tight" style="background:#121411;min-height:100vh;padding:0">
  <style>
    .site-header,.account-menu,.footer,.floating-ai-chat{display:none !important}
    .eco-ledger-page{background:transparent;color:#e3e3de;font-family:'Manrope',sans-serif;padding-bottom:100px}
    .eco-ledger-grid{display:grid;grid-template-columns:1fr;gap:20px;max-width:900px;margin:0 auto}
    .eco-order-card{background:rgba(26,28,25,.94);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.05);border-radius:24px;padding:24px;display:flex;flex-direction:column;gap:16px;box-shadow:0 12px 32px rgba(0,0,0,.15);transition:transform 0.3s,box-shadow 0.3s;}
    .eco-order-card:hover{transform:translateY(-2px);box-shadow:0 16px 40px rgba(0,0,0,.25);border:1px solid rgba(111,219,168,.2);}
    .eco-order-header{display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid rgba(255,255,255,.05);padding-bottom:16px;}
    .eco-order-id{font-family:'Noto Serif',serif;font-size:20px;color:#fff;margin:0;}
    .eco-order-date{font-size:13px;color:#bdcac0;}
    .eco-order-status{padding:6px 12px;border-radius:12px;font-size:12px;font-weight:800;letter-spacing:0.05em;text-transform:uppercase;}
    .eco-order-status.pending{background:rgba(255,225,109,.1);color:#ffe16d;border:1px solid rgba(255,225,109,.2);}
    .eco-order-status.paid{background:rgba(111,219,168,.1);color:#6fdba8;border:1px solid rgba(111,219,168,.2);}
    .eco-order-status.cancelled{background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.2);}
    .eco-order-body{display:flex;justify-content:space-between;align-items:center;padding:8px 0;}
    .eco-order-items{font-size:15px;color:#e3e3de;line-height:1.6;}
    .eco-order-total{font-size:24px;font-weight:800;color:#6fdba8;}
    .eco-order-actions{display:flex;justify-content:flex-end;gap:12px;padding-top:16px;border-top:1px solid rgba(255,255,255,.05);}
    .eco-btn{padding:10px 20px;border:none;border-radius:12px;font-weight:700;font-size:14px;cursor:pointer;transition:all 0.2s;}
    .eco-btn.pay-now{background:linear-gradient(135deg,#31a375,#6fdba8);color:#003824;box-shadow:0 4px 12px rgba(111,219,168,.3);}
    .eco-btn.pay-now:hover{box-shadow:0 6px 16px rgba(111,219,168,.5);transform:scale(1.02);}
    .eco-btn.details{background:rgba(255,255,255,.05);color:#fff;}
    .eco-btn.details:hover{background:rgba(255,255,255,.1);}
    
    .eco-no-orders{text-align:center;padding:60px 20px;background:rgba(26,28,25,.94);border-radius:24px;border:1px dashed rgba(255,255,255,.1);}
    .eco-no-orders-icon{font-size:48px;margin-bottom:16px;opacity:0.6;}
    .eco-no-orders p{color:#bdcac0;margin-bottom:24px;}
  </style>

  <div class="eco-ledger-page">
    <div class="eco-account-shell">
      <main class="eco-account-main">
        <div class="eco-account-content">
          <div class="eco-account-inner">
            
            <?php if (isset($_GET['paid'])): ?>
                <div class="notice" style="background:rgba(111,219,168,.1);border:1px solid #6fdba8;color:#6fdba8;padding:16px;border-radius:16px;margin-bottom:24px;font-weight:600;">
                    🎉 Cảm ơn bạn! Thanh toán đã được ghi nhận thành công.
                </div>
            <?php endif; ?>

            <div class="eco-ledger-grid">
              <?php if (empty($orders)): ?>
                <div class="eco-no-orders">
                    <div class="eco-no-orders-icon">📦</div>
                    <h2 style="margin:0 0 12px;font-family:'Noto Serif',serif;font-size:24px;color:#fff">Chưa có giao dịch nào</h2>
                    <p>Khởi tạo khu vườn đầu tiên của bạn bằng cách thuê một rack mới.</p>
                    <a href="<?php echo esc_url(home_url('/nang-cap-goi/')); ?>" class="eco-btn pay-now" style="display:inline-block;text-decoration:none;">Xem dịch vụ</a>
                </div>
              <?php else: ?>
                  <?php foreach ($orders as $order): 
                      $items = json_decode((string)$order->items, true) ?: [];
                      $items_text = [];
                      foreach ($items as $item) {
                          $items_text[] = ($item['name'] ?? 'Sản phẩm') . ' x' . ($item['qty'] ?? 1);
                      }
                      $items_display = implode('<br>', $items_text);
                      if (empty($items_display)) $items_display = 'Dịch vụ hệ thống';
                      
                      $status_class = 'pending';
                      $status_text = 'Chờ thanh toán';
                      if ($order->status === 'paid') {
                          $status_class = 'paid';
                          $status_text = 'Đã thanh toán';
                      } elseif ($order->status === 'cancelled') {
                          $status_class = 'cancelled';
                          $status_text = 'Đã huỷ';
                      }
                  ?>
                  <div class="eco-order-card">
                      <div class="eco-order-header">
                          <div>
                              <h3 class="eco-order-id"><?php echo esc_html($order->order_id); ?></h3>
                              <div class="eco-order-date"><?php echo esc_html(mysql2date('d/m/Y H:i', $order->created_at)); ?></div>
                          </div>
                          <div class="eco-order-status <?php echo $status_class; ?>"><?php echo esc_html($status_text); ?></div>
                      </div>
                      <div class="eco-order-body">
                          <div class="eco-order-items"><?php echo $items_display; ?></div>
                          <div class="eco-order-total"><?php echo number_format((float)$order->total, 0, ',', '.'); ?>đ</div>
                      </div>
                      
                      <?php if ($order->status === 'pending_payment'): ?>
                      <div class="eco-order-actions">
                          <button class="eco-btn details" type="button" onclick="alert('Tính năng xem chi tiết đơn hàng sẽ sớm ra mắt.')">Chi tiết</button>
                          <button class="eco-btn pay-now" type="button" onclick="document.getElementById('payment-modal-<?php echo esc_attr($order->id); ?>').style.display='grid'">Thanh toán ngay</button>
                      </div>

                      <?php 
                      $bank_settings = function_exists('aitrongcay_get_bank_settings') ? aitrongcay_get_bank_settings() : [];
                      $bank_code = $bank_settings['bank_code'] ?? 'TCB';
                      $account_number = $bank_settings['account_number'] ?? '';
                      $account_name = $bank_settings['account_name'] ?? '';
                      $qr_url = function_exists('aitrongcay_build_vietqr_url') ? aitrongcay_build_vietqr_url($bank_code, $account_number, $account_name, (int)$order->total, $order->order_id) : '';
                      
                      ob_start();
                      ?>
                      <div id="payment-modal-<?php echo esc_attr($order->id); ?>" class="eco-warehouse-checkout-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);backdrop-filter:blur(8px);z-index:200;place-items:center;padding:16px;" onclick="if(event.target===this) this.style.display='none'">
                          <div style="background:rgba(26,28,25,.98);border:1px solid rgba(255,255,255,.08);border-radius:24px;width:100%;max-width:380px;position:relative;display:flex;flex-direction:column;max-height:90vh;overflow-y:auto;box-shadow:0 30px 60px rgba(0,0,0,.4)">
                              <button type="button" onclick="document.getElementById('payment-modal-<?php echo esc_attr($order->id); ?>').style.display='none'" style="position:absolute;top:16px;right:16px;background:none;border:none;color:rgba(227,227,222,.4);font-size:24px;cursor:pointer;line-height:1;z-index:10">&times;</button>
                              
                              <div style="padding:24px 24px 16px;">
                                  <div style="text-align:center;padding-bottom:8px">
                                      <h3 style="margin:0 0 3px;color:#6fdba8;font-size:18px;font-weight:800">Thanh toán đơn hàng</h3>
                                      <p style="color:rgba(227,227,222,.55);font-size:12px;margin:0">Mã đơn hàng</p>
                                      <p style="font-size:22px;font-weight:900;color:#6fdba8;margin:5px 0 0;letter-spacing:.5px"><?php echo esc_html($order->order_id); ?></p>
                                  </div>
                                  <div style="background:rgba(41,43,39,.8);border-radius:12px;padding:14px 16px;margin:14px 0 10px">
                                      <p style="font-size:11px;font-weight:700;color:rgba(227,227,222,.4);margin:0 0 8px;text-transform:uppercase;letter-spacing:.5px">Thông tin chuyển khoản</p>
                                      <table style="width:100%;font-size:13px;border-collapse:collapse">
                                          <tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Ngân hàng</td><td style="font-weight:700;text-align:right"><?php echo esc_html($bank_code); ?></td></tr>
                                          <tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Số tài khoản</td><td style="font-weight:700;font-size:15px;letter-spacing:.5px;text-align:right"><?php echo esc_html($account_number); ?></td></tr>
                                          <tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Chủ TK</td><td style="font-weight:700;text-align:right"><?php echo esc_html($account_name); ?></td></tr>
                                          <tr><td style="padding:5px 0;color:rgba(227,227,222,.55)">Số tiền</td><td style="font-weight:900;color:#f87171;font-size:16px;text-align:right"><?php echo number_format((float)$order->total, 0, ',', '.'); ?>đ</td></tr>
                                          <tr style="background:rgba(251,191,36,.08)"><td style="padding:6px 0;color:rgba(227,227,222,.55)">Nội dung CK ⚠️</td><td style="font-weight:900;color:#fbbf24;font-size:15px;text-align:right"><?php echo esc_html($order->order_id); ?></td></tr>
                                      </table>
                                  </div>
                                  
                                  <?php if ($qr_url): ?>
                                  <div style="text-align:center;margin:14px 0">
                                      <img src="<?php echo esc_url($qr_url); ?>" alt="QR chuyển khoản" width="180" height="180" style="border-radius:12px;display:block;margin:0 auto">
                                      <p style="font-size:11px;color:rgba(227,227,222,.45);margin:5px 0 0">Quét QR bằng app ngân hàng</p>
                                  </div>
                                  <?php endif; ?>
                                  
                                  <div style="background:rgba(249,115,22,.1);border-left:3px solid #f97316;padding:9px 12px;border-radius:0 8px 8px 0;font-size:12px;color:#fdba74;margin-bottom:4px">
                                      Vui lòng chuyển khoản với đúng nội dung CK để admin xác nhận.
                                  </div>
                              </div>
                              <div style="padding:16px 24px 24px;">
                                  <button type="button" class="eco-btn pay-now" style="width:100%" onclick="document.getElementById('payment-modal-<?php echo esc_attr($order->id); ?>').style.display='none'">Đã hiểu, tôi sẽ chuyển khoản →</button>
                              </div>
                          </div>
                      </div>
                      <?php 
                      $modals_html .= ob_get_clean();
                      endif; ?>
                  </div>
                  <?php endforeach; ?>
              <?php endif; ?>
            </div>
            
          </div>
        </div>
      </main>
    </div>
  </div>
</section>

<?php echo $modals_html; ?>

<?php get_template_part('template-parts/site/eco-shell-end'); ?>
