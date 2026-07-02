<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

// ─── DB version ──────────────────────────────────────────────────────────────
define('AITR_ORDERS_DB_VER', '1.1');

// ─── Install / migrate DB table ──────────────────────────────────────────────
function aitrongcay_install_orders_table(): void {
    global $wpdb;
    $table = $wpdb->prefix . 'aitr_orders';
    $cs    = $wpdb->get_charset_collate();
    $sql   = "CREATE TABLE {$table} (
      id               bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      order_id         varchar(24)         NOT NULL DEFAULT '',
      status           varchar(30)         NOT NULL DEFAULT 'pending_payment',
      customer_name    varchar(200)        NOT NULL DEFAULT '',
      customer_phone   varchar(30)         NOT NULL DEFAULT '',
      customer_email   varchar(200)        NOT NULL DEFAULT '',
      user_id          bigint(20) unsigned NOT NULL DEFAULT 0,
      garden_key       varchar(100)        NOT NULL DEFAULT '',
      plant_name       varchar(200)        NOT NULL DEFAULT '',
      items            longtext            NOT NULL,
      total            int(10) unsigned    NOT NULL DEFAULT 0,
      note             text                NOT NULL,
      bank_ref         varchar(100)        NOT NULL DEFAULT '',
      paid_at          datetime            DEFAULT NULL,
      created_at       datetime            NOT NULL,
      updated_at       datetime            NOT NULL,
      PRIMARY KEY      (id),
      UNIQUE KEY       order_id   (order_id),
      KEY              status     (status),
      KEY              user_id    (user_id),
      KEY              created_at (created_at)
    ) {$cs};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
    update_option('aitrongcay_orders_db_version', AITR_ORDERS_DB_VER);
}

add_action('after_switch_theme', 'aitrongcay_install_orders_table');
add_action('admin_init', function (): void {
    if (get_option('aitrongcay_orders_db_version') !== AITR_ORDERS_DB_VER) {
        aitrongcay_install_orders_table();
    }
});

// ─── Generate unique order ID (AITRYYYYMMDD-NNNN) ───────────────────────────
function aitrongcay_generate_order_id(): string {
    global $wpdb;
    $table    = $wpdb->prefix . 'aitr_orders';
    $today    = current_time('Y-m-d');
    $count    = (int) $wpdb->get_var(
        $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = %s", $today)
    );
    return 'AITR' . current_time('Ymd') . '-' . sprintf('%04d', $count + 1);
}

// ─── Bank settings ───────────────────────────────────────────────────────────
function aitrongcay_get_bank_settings(): array {
    $stored = get_option('aitrongcay_bank_settings', []);
    if (! is_array($stored)) {
        $stored = [];
    }
    return array_merge([
        'bank_code'      => 'TCB',
        'bank_name'      => 'Techcombank',
        'account_number' => '',
        'account_name'   => 'CONG TY CO PHAN NGHIEN CUU GIAI PHAP VA PHAT TRIEN CONG NGHE XANH',
    ], $stored);
}

function aitrongcay_build_vietqr_url(string $bank_code, string $account_number, string $account_name, int $amount, string $order_id): string {
    return 'https://img.vietqr.io/image/' . rawurlencode($bank_code) . '-' . rawurlencode($account_number)
        . '-compact2.png?amount=' . $amount
        . '&addInfo=' . rawurlencode($order_id)
        . '&accountName=' . rawurlencode($account_name);
}

// ─── Order status labels ─────────────────────────────────────────────────────
function aitrongcay_order_status_labels(): array {
    return [
        'pending_payment'   => 'Chờ thanh toán',
        'payment_received'  => 'Đã nhận tiền',
        'setup_in_progress' => 'Đang setup vườn',
        'active'            => 'Đang hoạt động',
        'cancelled'         => 'Đã huỷ',
    ];
}

// ─── Email: Customer order confirmation ──────────────────────────────────────
function aitrongcay_send_order_confirmation_email(array $order): void {
    $to = sanitize_email((string) ($order['customer_email'] ?? ''));
    if ($to === '') {
        return;
    }

    $bank      = aitrongcay_get_bank_settings();
    $order_id  = esc_html((string) ($order['order_id']       ?? ''));
    $name      = esc_html((string) ($order['customer_name']  ?? ''));
    $plant     = esc_html((string) ($order['plant_name']     ?? ''));
    $total     = (int) ($order['total'] ?? 0);
    $total_fmt = number_format($total, 0, ',', '.');
    $tel       = esc_html(get_option('aitrongcay_support_phone', '0983.660.988'));

    $qr_block = '';
    if ($bank['account_number'] !== '') {
        $qr_url   = aitrongcay_build_vietqr_url($bank['bank_code'], $bank['account_number'], $bank['account_name'], $total, (string) ($order['order_id'] ?? ''));
        $qr_block = '<div style="text-align:center;margin:20px 0">'
            . '<img src="' . esc_url($qr_url) . '" alt="QR chuyển khoản" width="200" height="200" style="border-radius:12px">'
            . '<p style="font-size:12px;color:#6b7280;margin:6px 0 0">Quét QR bằng app ngân hàng của bạn</p>'
            . '</div>';
    }

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head>'
        . '<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:24px;margin:0">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:linear-gradient(135deg,#31a375,#6fdba8);padding:28px 32px">'
        . '<h1 style="margin:0;color:#fff;font-size:22px">🌿 AI trồng cây</h1>'
        . '<p style="margin:5px 0 0;color:rgba(255,255,255,.85);font-size:14px">Xác nhận đặt dịch vụ</p>'
        . '</div>'
        . '<div style="padding:28px 32px">'
        . '<p style="margin-top:0">Xin chào <strong>' . $name . '</strong>,</p>'
        . '<p>Cảm ơn bạn đã đặt dịch vụ trồng rau tại <strong>AI trồng cây</strong>! Đơn hàng của bạn đã được ghi nhận.</p>'
        . '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin:16px 0">'
        . '<table style="width:100%;border-collapse:collapse;font-size:14px">'
        . '<tr><td style="padding:4px 0;color:#6b7280;width:40%">Mã đơn hàng</td><td style="font-weight:700;color:#15803d;font-size:16px">' . $order_id . '</td></tr>'
        . ($plant !== '' ? '<tr><td style="padding:4px 0;color:#6b7280">Cây dự kiến</td><td style="font-weight:600">' . $plant . '</td></tr>' : '')
        . '<tr><td style="padding:4px 0;color:#6b7280">Tổng tiền</td><td style="font-weight:700;font-size:16px;color:#dc2626">' . $total_fmt . 'đ</td></tr>'
        . '</table>'
        . '</div>'
        . '<h3 style="color:#111827;margin:20px 0 12px">Thông tin chuyển khoản</h3>'
        . '<table style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">'
        . '<tr style="background:#f9fafb"><td style="padding:9px 14px;color:#6b7280;width:40%">Ngân hàng</td><td style="padding:9px 14px;font-weight:700">' . esc_html($bank['bank_name']) . '</td></tr>'
        . '<tr><td style="padding:9px 14px;color:#6b7280;border-top:1px solid #e5e7eb">Số tài khoản</td><td style="padding:9px 14px;font-weight:700;font-size:16px;letter-spacing:.5px;border-top:1px solid #e5e7eb">' . esc_html($bank['account_number']) . '</td></tr>'
        . '<tr style="background:#f9fafb"><td style="padding:9px 14px;color:#6b7280;border-top:1px solid #e5e7eb">Chủ tài khoản</td><td style="padding:9px 14px;font-weight:700;border-top:1px solid #e5e7eb">' . esc_html($bank['account_name']) . '</td></tr>'
        . '<tr><td style="padding:9px 14px;color:#6b7280;border-top:1px solid #e5e7eb">Số tiền</td><td style="padding:9px 14px;font-weight:700;color:#dc2626;font-size:16px;border-top:1px solid #e5e7eb">' . $total_fmt . 'đ</td></tr>'
        . '<tr style="background:#fefce8;border-top:2px solid #fde68a"><td style="padding:9px 14px;color:#92400e;font-weight:700">Nội dung CK ⚠️</td><td style="padding:9px 14px;font-weight:900;color:#92400e;font-size:16px">' . $order_id . '</td></tr>'
        . '</table>'
        . $qr_block
        . '<div style="background:#fff7ed;border-left:3px solid #f97316;padding:12px 16px;border-radius:0 8px 8px 0;font-size:13px;color:#9a3412;margin:16px 0">'
        . '⚠️ Vui lòng ghi đúng nội dung chuyển khoản <strong>' . $order_id . '</strong> để chúng tôi xác nhận đơn nhanh nhất.'
        . '</div>'
        . '<p style="font-size:14px;color:#374151">Sau khi chuyển khoản, chúng tôi sẽ xác nhận và liên hệ trong vòng <strong>24 giờ làm việc</strong>.</p>'
        . '<p style="font-size:14px;color:#374151">Hỗ trợ: <a href="tel:+84983660988" style="color:#15803d;font-weight:700">' . $tel . '</a></p>'
        . '</div>'
        . '<div style="background:#f9fafb;padding:14px 32px;text-align:center;font-size:12px;color:#9ca3af">'
        . 'AI trồng cây · Số 180A, đường Âu Cơ, Phường Tứ Liên, Quận Tây Hồ, Hà Nội'
        . '</div>'
        . '</div></body></html>';

    wp_mail($to, "[AI trồng cây] Xác nhận đơn #{$order_id} – Thông tin chuyển khoản", $html, ['Content-Type: text/html; charset=UTF-8']);
}

// ─── Email: Status update to customer ────────────────────────────────────────
function aitrongcay_send_order_status_email(array $order, string $admin_note = ''): void {
    $to = sanitize_email((string) ($order['customer_email'] ?? ''));
    if ($to === '') {
        return;
    }

    $status    = (string) ($order['status'] ?? '');
    $order_id  = esc_html((string) ($order['order_id']      ?? ''));
    $name      = esc_html((string) ($order['customer_name'] ?? ''));
    $tel       = esc_html(get_option('aitrongcay_support_phone', '0983.660.988'));
    $labels    = aitrongcay_order_status_labels();
    $label     = $labels[$status] ?? $status;

    $messages = [
        'payment_received'  => 'Chúng tôi đã nhận được thanh toán của bạn. Đội ngũ đang chuẩn bị setup khu vườn cho bạn.',
        'setup_in_progress' => 'Chúng tôi đang tiến hành lắp đặt khu vườn. Quá trình này mất khoảng 1–3 ngày làm việc.',
        'active'            => 'Khu vườn của bạn đã sẵn sàng! Đăng nhập tài khoản như thường lệ — vườn của bạn sẽ hiện ngay trên trang portal.',
        'cancelled'         => 'Đơn hàng của bạn đã bị huỷ. Nếu có thắc mắc, vui lòng liên hệ đội ngũ hỗ trợ.',
    ];
    $body_text = esc_html($messages[$status] ?? "Trạng thái đơn hàng của bạn đã được cập nhật: {$label}.");

    $colors = [
        'payment_received'  => '#2563eb',
        'setup_in_progress' => '#d97706',
        'active'            => '#16a34a',
        'cancelled'         => '#6b7280',
    ];
    $badge_color = $colors[$status] ?? '#374151';

    $portal_block = '';
    if ($status === 'active') {
        $portal_url   = home_url('/portal/dashboard-2/');
        $portal_block = '<div style="text-align:center;margin:24px 0">'
            . '<a href="' . esc_url($portal_url) . '" style="display:inline-block;padding:14px 30px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;text-decoration:none;border-radius:12px;font-weight:800;font-size:15px">🌿 Truy cập Portal vườn của tôi</a>'
            . '</div>';
    }

    $note_block = '';
    if ($admin_note !== '') {
        $note_block = '<div style="background:#f0f9ff;border-left:3px solid #38bdf8;padding:12px 16px;border-radius:0 8px 8px 0;font-size:13px;margin:12px 0">'
            . '<strong>Ghi chú từ đội ngũ:</strong> ' . esc_html($admin_note)
            . '</div>';
    }

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head>'
        . '<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:24px;margin:0">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:linear-gradient(135deg,#31a375,#6fdba8);padding:28px 32px">'
        . '<h1 style="margin:0;color:#fff;font-size:22px">🌿 AI trồng cây</h1>'
        . '<p style="margin:5px 0 0;color:rgba(255,255,255,.85);font-size:14px">Cập nhật đơn hàng</p>'
        . '</div>'
        . '<div style="padding:28px 32px">'
        . '<p style="margin-top:0">Xin chào <strong>' . $name . '</strong>,</p>'
        . '<div style="display:inline-block;background:' . $badge_color . ';color:#fff;border-radius:20px;padding:5px 14px;font-size:13px;font-weight:700;margin-bottom:14px">' . esc_html($label) . '</div>'
        . '<p style="margin-top:0">' . $body_text . '</p>'
        . $note_block
        . $portal_block
        . '<div style="background:#f9fafb;border-radius:8px;padding:10px 14px;font-size:13px;color:#6b7280;margin-top:16px">'
        . 'Mã đơn hàng: <strong>' . $order_id . '</strong>'
        . '</div>'
        . '<p style="font-size:13px;color:#374151;margin-top:16px">Hỗ trợ: <a href="tel:+84983660988" style="color:#15803d;font-weight:700">' . $tel . '</a></p>'
        . '</div>'
        . '<div style="background:#f9fafb;padding:14px 32px;text-align:center;font-size:12px;color:#9ca3af">'
        . 'AI trồng cây · Số 180A, đường Âu Cơ, Phường Tứ Liên, Quận Tây Hồ, Hà Nội'
        . '</div>'
        . '</div></body></html>';

    wp_mail($to, "[AI trồng cây] Đơn #{$order_id} – {$label}", $html, ['Content-Type: text/html; charset=UTF-8']);
}

// ─── Email: Admin notification ────────────────────────────────────────────────
function aitrongcay_send_admin_order_email(array $order): void {
    $admin_email = get_option('admin_email', '');
    if ($admin_email === '') {
        return;
    }

    $order_id  = esc_html((string) ($order['order_id']       ?? ''));
    $name      = esc_html((string) ($order['customer_name']  ?? ''));
    $phone     = esc_html((string) ($order['customer_phone'] ?? ''));
    $email     = esc_html((string) ($order['customer_email'] ?? ''));
    $plant     = esc_html((string) ($order['plant_name']     ?? ''));
    $total_fmt = number_format((int) ($order['total'] ?? 0), 0, ',', '.');
    $note      = esc_html((string) ($order['note'] ?? ''));
    $time      = wp_date('d/m/Y H:i:s');
    $admin_url = esc_url(admin_url('admin.php?page=aitrongcay-orders'));

    $items_html = '';
    foreach ((array) ($order['items'] ?? []) as $it) {
        $lt          = (int) ($it['price'] ?? 0) * (int) ($it['qty'] ?? 1);
        $items_html .= '<tr>'
            . '<td style="padding:6px 12px;border-bottom:1px solid #e5e7eb">' . esc_html((string) ($it['name'] ?? '')) . '</td>'
            . '<td style="padding:6px 12px;border-bottom:1px solid #e5e7eb;text-align:center">' . (int) ($it['qty'] ?? 1) . '</td>'
            . '<td style="padding:6px 12px;border-bottom:1px solid #e5e7eb;text-align:right">' . number_format($lt, 0, ',', '.') . 'đ</td>'
            . '</tr>';
    }

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head>'
        . '<body style="font-family:Arial,sans-serif;background:#f3f4f6;padding:24px;margin:0">'
        . '<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden">'
        . '<div style="background:#1e293b;padding:20px 28px">'
        . '<h2 style="margin:0;color:#fff;font-size:18px">🛎️ Đơn hàng mới – AI trồng cây</h2>'
        . '</div>'
        . '<div style="padding:24px 28px">'
        . '<table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:16px">'
        . '<tr><td style="padding:6px 0;color:#6b7280;width:35%">Mã đơn</td><td style="font-weight:700;color:#15803d;font-size:15px">' . $order_id . '</td></tr>'
        . '<tr><td style="padding:6px 0;color:#6b7280">Khách hàng</td><td style="font-weight:600">' . $name . '</td></tr>'
        . '<tr><td style="padding:6px 0;color:#6b7280">Số điện thoại</td><td><a href="tel:' . esc_attr((string) ($order['customer_phone'] ?? '')) . '" style="color:#15803d;font-weight:700">' . $phone . '</a></td></tr>'
        . ($email !== '' ? '<tr><td style="padding:6px 0;color:#6b7280">Email</td><td>' . $email . '</td></tr>' : '')
        . ($plant !== '' ? '<tr><td style="padding:6px 0;color:#6b7280">Cây dự kiến</td><td>' . $plant . '</td></tr>' : '')
        . '<tr><td style="padding:6px 0;color:#6b7280">Tổng tiền</td><td style="font-weight:700;font-size:16px;color:#dc2626">' . $total_fmt . 'đ</td></tr>'
        . '<tr><td style="padding:6px 0;color:#6b7280">Thời gian</td><td>' . $time . '</td></tr>'
        . ($note !== '' ? '<tr><td style="padding:6px 0;color:#6b7280">Ghi chú</td><td>' . $note . '</td></tr>' : '')
        . '</table>'
        . ($items_html !== '' ?
            '<table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden">'
            . '<thead><tr style="background:#f9fafb"><th style="padding:7px 12px;text-align:left;font-size:12px;color:#6b7280">Vật tư</th><th style="padding:7px 12px;font-size:12px;color:#6b7280">SL</th><th style="padding:7px 12px;text-align:right;font-size:12px;color:#6b7280">Thành tiền</th></tr></thead>'
            . '<tbody>' . $items_html . '</tbody></table>' : '')
        . '<div style="text-align:center;margin-top:20px">'
        . '<a href="' . $admin_url . '" style="display:inline-block;padding:13px 26px;background:#15803d;color:#fff;text-decoration:none;border-radius:10px;font-weight:700;font-size:14px">Xem & duyệt đơn trong WP Admin →</a>'
        . '</div></div></body></html>';

    wp_mail($admin_email, "[AI trồng cây] Đơn mới #{$order_id} – {$name} – {$total_fmt}đ", $html, ['Content-Type: text/html; charset=UTF-8']);
}

// ─── AJAX: Place order (public) ───────────────────────────────────────────────
add_action('wp_ajax_aitrongcay_place_order',        'aitrongcay_handle_place_order');
add_action('wp_ajax_nopriv_aitrongcay_place_order', 'aitrongcay_handle_place_order');

function aitrongcay_handle_place_order(): void {
    check_ajax_referer('aitrongcay_portal_actions', 'nonce');
    global $wpdb;

    $customer_name  = sanitize_text_field((string)    ($_POST['customer_name']  ?? ''));
    $customer_phone = sanitize_text_field((string)    ($_POST['customer_phone'] ?? ''));
    $customer_email = sanitize_email((string)          ($_POST['customer_email'] ?? ''));
    $note           = sanitize_textarea_field((string) ($_POST['note']           ?? ''));
    $plant_name     = sanitize_text_field((string)    ($_POST['plant_name']     ?? ''));
    $total          = max(0, (int) ($_POST['total']   ?? 0));
    $items_raw      = (string) ($_POST['items']       ?? '');

    if ($customer_name === '' || $customer_phone === '') {
        wp_send_json_error(['message' => 'Vui lòng nhập đầy đủ họ tên và số điện thoại.']);
        return;
    }

    $items = json_decode(wp_unslash($items_raw), true);
    if (! is_array($items)) {
        $items = [];
    }

    $sanitized_items = [];
    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }
        $sanitized_items[] = [
            'name'     => sanitize_text_field((string) ($item['name']     ?? '')),
            'price'    => max(0, (int)               ($item['price']    ?? 0)),
            'qty'      => max(1, (int)               ($item['qty']      ?? 1)),
            'category' => sanitize_text_field((string) ($item['category'] ?? '')),
        ];
    }
    
    if (empty($sanitized_items)) {
        wp_send_json_error(['message' => 'Giỏ hàng của bạn đang trống. Vui lòng chọn sản phẩm trước khi thanh toán.']);
        return;
    }
    
    // Check stock limits before allowing order
    if (function_exists('aitrongcay_onboarding_tables')) {
        $obs = aitrongcay_onboarding_tables();
        $supplies_table = $obs['supplies'];
        foreach ($sanitized_items as $sit) {
            $name = $sit['name'];
            $qty = $sit['qty'];
            if ($name !== '' && $qty > 0) {
                if (($sit['category'] ?? '') === 'Dịch vụ') {
                    continue;
                }
                $row = $wpdb->get_row($wpdb->prepare("SELECT stock_quantity FROM {$supplies_table} WHERE name = %s LIMIT 1", $name), ARRAY_A);
                if ($row !== null) {
                    $stock = (int) $row['stock_quantity'];
                    if ($qty > $stock) {
                        wp_send_json_error(['message' => 'Sản phẩm "' . esc_html($name) . '" chỉ còn ' . $stock . ' sản phẩm trong kho. Vui lòng giảm số lượng.']);
                        return;
                    }
                }
            }
        }
    }

    // Auto-fill email from WP user if not provided
    $current_user = wp_get_current_user();
    $user_id      = ($current_user instanceof WP_User && $current_user->ID > 0) ? $current_user->ID : 0;
    if ($customer_email === '' && $user_id > 0) {
        $customer_email = sanitize_email((string) $current_user->user_email);
    }

    $order_id = aitrongcay_generate_order_id();
    $now      = current_time('mysql');
    $table    = $wpdb->prefix . 'aitr_orders';

    $inserted = $wpdb->insert(
        $table,
        [
            'order_id'       => $order_id,
            'status'         => 'pending_payment',
            'customer_name'  => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'user_id'        => $user_id,
            'garden_key'     => '',
            'plant_name'     => $plant_name,
            'items'          => wp_json_encode($sanitized_items),
            'total'          => $total,
            'note'           => $note,
            'bank_ref'       => '',
            'created_at'     => $now,
            'updated_at'     => $now,
        ],
        ['%s','%s','%s','%s','%s','%d','%s','%s','%s','%d','%s','%s','%s','%s']
    );

    if (! $inserted) {
        wp_send_json_error(['message' => 'Lỗi lưu đơn hàng, vui lòng thử lại hoặc liên hệ hỗ trợ.']);
        return;
    }

    $order = [
        'order_id'       => $order_id,
        'customer_name'  => $customer_name,
        'customer_phone' => $customer_phone,
        'customer_email' => $customer_email,
        'user_id'        => $user_id,
        'garden_key'     => '',
        'plant_name'     => $plant_name,
        'items'          => $sanitized_items,
        'total'          => $total,
        'note'           => $note,
        'status'         => 'pending_payment',
    ];

    aitrongcay_send_order_confirmation_email($order);
    aitrongcay_send_admin_order_email($order);
    
    if (function_exists('aitrongcay_add_notification') && $user_id > 0) {
        aitrongcay_add_notification(
            $user_id,
            'Đặt hàng thành công',
            'Đơn hàng ' . esc_html($order_id) . ' đã được ghi nhận. Chúng tôi sẽ sớm liên hệ lại với bạn!',
            '#'
        );
    }

    $bank   = aitrongcay_get_bank_settings();
    $qr_url = '';
    if ($bank['account_number'] !== '') {
        $qr_url = aitrongcay_build_vietqr_url($bank['bank_code'], $bank['account_number'], $bank['account_name'], $total, $order_id);
    }

    wp_send_json_success([
        'order_id'       => $order_id,
        'total'          => $total,
        'total_fmt'      => number_format($total, 0, ',', '.'),
        'bank_name'      => $bank['bank_name'],
        'account_number' => $bank['account_number'],
        'account_name'   => $bank['account_name'],
        'qr_url'         => $qr_url,
        'has_email'      => $customer_email !== '',
    ]);
}

// ─── AJAX: Admin status update ────────────────────────────────────────────────
add_action('wp_ajax_aitrongcay_order_update_status', 'aitrongcay_handle_order_update_status');

function aitrongcay_handle_order_update_status(): void {
    check_ajax_referer('aitrongcay_admin_orders', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Không có quyền.']);
        return;
    }
    global $wpdb;

    $order_id   = sanitize_text_field((string)    ($_POST['order_id']    ?? ''));
    $status     = sanitize_key((string)            ($_POST['status']      ?? ''));
    $admin_note = sanitize_textarea_field((string) ($_POST['admin_note']  ?? ''));
    $bank_ref   = sanitize_text_field((string)    ($_POST['bank_ref']    ?? ''));

    if (! array_key_exists($status, aitrongcay_order_status_labels())) {
        wp_send_json_error(['message' => 'Trạng thái không hợp lệ.']);
        return;
    }

    $table         = $wpdb->prefix . 'aitr_orders';
    $order         = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE order_id = %s", $order_id), ARRAY_A);
    
    if (! $order) {
        wp_send_json_error(['message' => 'Không tìm thấy đơn hàng.']);
        return;
    }

    $update_data   = ['status' => $status, 'updated_at' => current_time('mysql')];
    $update_format = ['%s', '%s'];

    if ($bank_ref !== '') {
        $update_data['bank_ref'] = $bank_ref;
        $update_format[]         = '%s';
    }
    
    $is_newly_paid = false;
    if (in_array($status, ['payment_received', 'active'], true)) {
        if (empty($order['paid_at'])) {
            $update_data['paid_at'] = current_time('mysql');
            $update_format[]        = '%s';
            $is_newly_paid = true;
        }
    }
    
    $is_newly_cancelled = false;
    if ($status === 'cancelled' && $order['status'] !== 'cancelled' && !empty($order['paid_at'])) {
        $is_newly_cancelled = true;
    }

    $updated = $wpdb->update($table, $update_data, ['order_id' => $order_id], $update_format, ['%s']);
    if ($updated === false) {
        wp_send_json_error(['message' => 'Lỗi cập nhật DB.']);
        return;
    }

    if ($is_newly_paid || $is_newly_cancelled) {
        $items = json_decode((string) $order['items'], true);
        if (is_array($items) && function_exists('aitrongcay_onboarding_tables')) {
            $obs = aitrongcay_onboarding_tables();
            $supplies_table = $obs['supplies'];
            foreach ($items as $it) {
                $qty  = (int) ($it['qty'] ?? 1);
                $name = trim((string) ($it['name'] ?? ''));
                if ($qty > 0 && $name !== '') {
                    if ($is_newly_paid) {
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$supplies_table} SET stock_quantity = GREATEST(0, stock_quantity - %d) WHERE name = %s",
                            $qty, $name
                        ));
                    } else {
                        // Restore stock
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$supplies_table} SET stock_quantity = stock_quantity + %d WHERE name = %s",
                            $qty, $name
                        ));
                    }
                }
            }
        }
    }

    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE order_id = %s", $order_id), ARRAY_A);
    if ($order) {
        $order['status'] = $status;
        aitrongcay_send_order_status_email($order, $admin_note);
        if ($is_newly_paid) {
            aitrongcay_maybe_auto_upgrade_user_plan($order);
        }
        
        if (function_exists('aitrongcay_add_notification') && !empty($order['user_id'])) {
            $labels = aitrongcay_order_status_labels();
            $label  = $labels[$status] ?? $status;
            aitrongcay_add_notification(
                (int)$order['user_id'],
                'Cập nhật đơn hàng',
                'Đơn hàng ' . esc_html($order_id) . ' của bạn vừa được cập nhật trạng thái: ' . esc_html($label),
                '#'
            );
        }
    }

    wp_send_json_success(['message' => 'Đã cập nhật.']);
}

function aitrongcay_get_active_subscription_plan(int $user_id): array {
    $plan_id = (string) get_user_meta($user_id, 'aitrongcay_subscription_plan', true);
    $expiry  = (int) get_user_meta($user_id, 'aitrongcay_subscription_expiry', true);
    
    if ($plan_id !== '' && $expiry > 0 && time() > $expiry) {
        // Hết hạn -> Hạ cấp tự động
        delete_user_meta($user_id, 'aitrongcay_subscription_plan');
        delete_user_meta($user_id, 'aitrongcay_subscription_expiry');
        $plan_id = '';
        $expiry = 0;
    }
    
    return [
        'id'     => $plan_id,
        'expiry' => $expiry,
    ];
}

function aitrongcay_maybe_auto_upgrade_user_plan(array $order): void {
    $user_id = (int) ($order['user_id'] ?? 0);
    if ($user_id <= 0 && !empty($order['customer_email'])) {
        $wp_user = get_user_by('email', sanitize_email((string) $order['customer_email']));
        if ($wp_user instanceof WP_User) {
            $user_id = $wp_user->ID;
        }
    }
    if ($user_id <= 0) {
        return;
    }
    
    $items = json_decode((string) ($order['items'] ?? ''), true);
    if (! is_array($items)) return;
    
    $plan_mapping = [
        'basic seed'      => 'basic',
        'verdant prime'   => 'prime',
        'eco enterprise'  => 'enterprise',
    ];
    
    foreach ($items as $it) {
        $name = mb_strtolower(trim((string) ($it['name'] ?? '')));
        $cat  = mb_strtolower(trim((string) ($it['category'] ?? '')));
        if (str_contains($cat, 'dịch vụ') || str_contains($cat, 'service')) {
            foreach ($plan_mapping as $keyword => $plan_id) {
                if (str_contains($name, $keyword)) {
                    $qty = (int) ($it['qty'] ?? 1);
                    if ($qty < 1) $qty = 1;
                    
                    $duration_seconds = $qty * 30 * 86400; // Mặc định 1 qty = 30 ngày
                    $current_plan = (string) get_user_meta($user_id, 'aitrongcay_subscription_plan', true);
                    $current_expiry = (int) get_user_meta($user_id, 'aitrongcay_subscription_expiry', true);
                    $now = time();
                    
                    if ($current_plan === $plan_id && $current_expiry > $now) {
                        // Cộng dồn nếu mua tiếp gói đang dùng
                        $new_expiry = $current_expiry + $duration_seconds;
                    } else {
                        // Thiết lập hạn mới từ đầu
                        $new_expiry = $now + $duration_seconds;
                    }
                    
                    update_user_meta($user_id, 'aitrongcay_subscription_plan', $plan_id);
                    update_user_meta($user_id, 'aitrongcay_subscription_expiry', $new_expiry);
                    return; // Upgrade applied
                }
            }
        }
    }
}

// ─── AJAX: Admin provision garden for order ───────────────────────────────────
add_action('wp_ajax_aitrongcay_admin_provision_garden', 'aitrongcay_handle_provision_garden');

function aitrongcay_handle_provision_garden(): void {
    check_ajax_referer('aitrongcay_admin_orders', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Không có quyền.']);
        return;
    }
    global $wpdb;

    $orders_table = $wpdb->prefix . 'aitr_orders';
    $order_id_req = sanitize_text_field((string) ($_POST['order_id'] ?? ''));
    $rack_id_req  = (int) ($_POST['rack_id'] ?? 0);

    if ($order_id_req === '') {
        wp_send_json_error(['message' => 'Thiếu mã đơn hàng.']);
        return;
    }

    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$orders_table} WHERE order_id = %s LIMIT 1", $order_id_req), ARRAY_A);
    if (! is_array($order)) {
        wp_send_json_error(['message' => 'Không tìm thấy đơn hàng.']);
        return;
    }

    if (! empty($order['garden_key'])) {
        wp_send_json_error(['message' => 'Đơn này đã được giao vườn rồi.']);
        return;
    }

    // Find WP user by user_id or by email
    $user_id        = (int) ($order['user_id'] ?? 0);
    $user_id_linked = false;
    if ($user_id <= 0 && ! empty($order['customer_email'])) {
        $wp_user = get_user_by('email', sanitize_email((string) $order['customer_email']));
        if ($wp_user instanceof WP_User) {
            $user_id        = (int) $wp_user->ID;
            $user_id_linked = true; // cần ghi lại vào order sau
        }
    }
    if ($user_id <= 0) {
        wp_send_json_error(['message' => 'Khách chưa có tài khoản trên web. Vào WP Admin → Users → Add New để tạo tài khoản trước, sau đó giao vườn lại.']);
        return;
    }

    // Reuse existing garden if user already owns one
    $garden_key = '';
    if (function_exists('aitrongcay_get_user_garden_memberships')) {
        foreach (aitrongcay_get_user_garden_memberships($user_id, ['active']) as $m) {
            if (($m['role'] ?? '') === 'owner' && ! empty($m['garden_key'])) {
                $garden_key = (string) $m['garden_key'];
                break;
            }
        }
    }
    if ($garden_key === '') {
        $garden_key = 'garden:' . md5((string) $user_id . $order_id_req . (string) time());
    }

    // Create/update garden record
    if (function_exists('aitrongcay_upsert_garden_record')) {
        $customer_name = sanitize_text_field((string) ($order['customer_name'] ?? ''));
        aitrongcay_upsert_garden_record($garden_key, $user_id, [
            'garden_name' => 'Vườn của ' . $customer_name,
        ]);
    }

    // Create owner membership if not exists
    if (function_exists('aitrongcay_garden_members_table')) {
        $mt  = aitrongcay_garden_members_table();
        $now = current_time('mysql');
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$mt} WHERE garden_key = %s AND user_id = %d LIMIT 1", $garden_key, $user_id));
        if (! $exists) {
            $wpdb->insert($mt, [
                'garden_key'         => $garden_key,
                'user_id'            => $user_id,
                'role'               => 'owner',
                'status'             => 'active',
                'invited_by_user_id' => get_current_user_id(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ], ['%s','%d','%s','%s','%d','%s','%s']);
        }
    }

    // Assign rack
    if ($rack_id_req > 0 && function_exists('aitrongcay_get_rack_by_id') && function_exists('aitrongcay_garden_racks_table') && function_exists('aitrongcay_garden_rack_assignments_table')) {
        $rack = aitrongcay_get_rack_by_id($rack_id_req);
        if (! is_array($rack) || ($rack['status'] ?? '') !== 'inventory') {
            wp_send_json_error(['message' => 'Rack này không còn trong kho, vui lòng chọn rack khác.']);
            return;
        }
        $from_gk = (string) ($rack['garden_key'] ?? '');
        $now     = current_time('mysql');
        $wpdb->update(
            aitrongcay_garden_racks_table(),
            ['garden_key' => $garden_key, 'owner_user_id' => $user_id, 'status' => 'assigned', 'updated_at' => $now],
            ['id' => $rack_id_req],
            ['%s','%d','%s','%s'],
            ['%d']
        );
        $wpdb->insert(aitrongcay_garden_rack_assignments_table(), [
            'rack_id'       => $rack_id_req,
            'user_id'       => $user_id,
            'garden_key'    => $garden_key,
            'household_key' => $garden_key,
            'assigned_at'   => $now,
            'status'        => 'active',
            'notes'         => 'Giao từ đơn hàng ' . $order_id_req,
        ], ['%d','%d','%s','%s','%s','%s','%s']);
        if (function_exists('aitrongcay_move_blynk_config_key')) {
            aitrongcay_move_blynk_config_key($from_gk, $garden_key);
        }
        if (function_exists('aitrongcay_log_rack_inventory_event')) {
            aitrongcay_log_rack_inventory_event($rack_id_req, 'assign', 'inventory', 'assigned', $user_id, 'Giao rack từ đơn hàng ' . $order_id_req, get_current_user_id());
        }
    } elseif (function_exists('aitrongcay_assign_inventory_rack_to_garden')) {
        $result = aitrongcay_assign_inventory_rack_to_garden($garden_key, $user_id);
        if (isset($result['error'])) {
            wp_send_json_error(['message' => (string) $result['error']]);
            return;
        }
    }

    // Update order: set garden_key + advance status to active
    // Nếu order trước đó là guest (user_id=0) nhưng nay đã tìm được user qua email → backfill user_id
    $order_update_data   = ['garden_key' => $garden_key, 'status' => 'active', 'updated_at' => current_time('mysql')];
    $order_update_format = ['%s', '%s', '%s'];
    if ($user_id_linked) {
        $order_update_data['user_id']  = $user_id;
        $order_update_format[]         = '%d';
    }
    $is_newly_paid = empty($order['paid_at']);
    if ($is_newly_paid) {
        $order_update_data['paid_at'] = current_time('mysql');
        $order_update_format[] = '%s';
    }

    $wpdb->update(
        $orders_table,
        $order_update_data,
        ['order_id' => $order_id_req],
        $order_update_format,
        ['%s']
    );

    if ($is_newly_paid) {
        $items_to_deduct = json_decode((string) $order['items'], true);
        if (is_array($items_to_deduct) && function_exists('aitrongcay_onboarding_tables')) {
            $obs = aitrongcay_onboarding_tables();
            $supplies_table = $obs['supplies'];
            foreach ($items_to_deduct as $it) {
                $qty  = (int) ($it['qty'] ?? 1);
                $name = trim((string) ($it['name'] ?? ''));
                if ($qty > 0 && $name !== '') {
                    $wpdb->query($wpdb->prepare(
                        "UPDATE {$supplies_table} SET stock_quantity = GREATEST(0, stock_quantity - %d) WHERE name = %s",
                        $qty, $name
                    ));
                }
            }
        }
    }

    // Auto-provision tools to the user's garden
    if (function_exists('aitrongcay_garden_tools_table')) {
        $tools_table = aitrongcay_garden_tools_table();
        $items = json_decode((string) ($order['items'] ?? ''), true);
        if (is_array($items)) {
            foreach ($items as $it) {
                $tname = trim((string) ($it['name'] ?? ''));
                $tqty  = (int) ($it['qty'] ?? 1);
                $tcat  = trim((string) ($it['category'] ?? 'Vật tư'));
                if ($tname !== '' && $tqty > 0) {
                    $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tools_table} WHERE garden_key = %s AND name = %s LIMIT 1", $garden_key, $tname), ARRAY_A);
                    if ($existing) {
                        $wpdb->query($wpdb->prepare(
                            "UPDATE {$tools_table} SET owned = owned + %d, qty = qty + %d, updated_at = %s WHERE id = %d",
                            $tqty, $tqty, current_time('mysql'), $existing['id']
                        ));
                    } else {
                        $wpdb->insert($tools_table, [
                            'garden_key' => $garden_key,
                            'tool_key'   => sanitize_title($tname),
                            'name'       => $tname,
                            'type'       => $tcat,
                            'owned'      => $tqty,
                            'qty'        => $tqty,
                            'created_at' => current_time('mysql'),
                            'updated_at' => current_time('mysql'),
                        ]);
                    }
                }
            }
        }
    }

    // Send "active" status email with portal link
    $order['garden_key']     = $garden_key;
    $order['status']         = 'active';
    aitrongcay_send_order_status_email($order);
    
    // Auto-upgrade plan if this order includes a plan
    aitrongcay_maybe_auto_upgrade_user_plan($order);

    wp_send_json_success([
        'garden_key' => $garden_key,
        'message'    => 'Đã giao vườn thành công! Email thông báo (kèm link portal) đã gửi cho khách.',
    ]);
}

// ─── Admin menu ───────────────────────────────────────────────────────────────
function aitrongcay_register_orders_admin_pages(): void {
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Đơn hàng',
        'Đơn hàng',
        'manage_options',
        'aitrongcay-orders',
        'aitrongcay_render_orders_page'
    );
}
add_action('admin_menu', 'aitrongcay_register_orders_admin_pages', 100);

// ─── Admin page: Orders list ──────────────────────────────────────────────────
function aitrongcay_render_orders_page(): void {
    global $wpdb;
    $table         = $wpdb->prefix . 'aitr_orders';
    $status_filter = sanitize_key((string) ($_GET['status'] ?? 'all'));
    $labels        = aitrongcay_order_status_labels();
    $nonce         = wp_create_nonce('aitrongcay_admin_orders');
    $ajax_url      = admin_url('admin-ajax.php');

    // Available racks for provisioning dropdown
    $racks_table     = $wpdb->prefix . 'aitr_garden_racks';
    $available_racks = $wpdb->get_results("SELECT id, rack_code, rack_name, slot_count FROM {$racks_table} WHERE status = 'inventory' ORDER BY id ASC LIMIT 50", ARRAY_A) ?: [];

    $where  = ($status_filter !== 'all') ? $wpdb->prepare('WHERE status = %s', $status_filter) : '';
    $orders = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT 300", ARRAY_A);

    $counts_raw = (array) $wpdb->get_results("SELECT status, COUNT(*) as cnt FROM {$table} GROUP BY status", ARRAY_A);
    $counts     = ['all' => 0];
    foreach ($counts_raw as $row) {
        $counts[(string) $row['status']] = (int) $row['cnt'];
        $counts['all']                  += (int) $row['cnt'];
    }

    $badge_colors = [
        'pending_payment'   => '#ca8a04',
        'payment_received'  => '#2563eb',
        'setup_in_progress' => '#d97706',
        'active'            => '#16a34a',
        'cancelled'         => '#9ca3af',
    ];

    echo '<div class="wrap">';
    echo '<h1 style="display:flex;align-items:center;gap:8px">🌿 Quản lý đơn hàng</h1>';

    // Status filter tabs
    $all_tabs = array_merge(['all' => 'Tất cả'], $labels);
    echo '<div style="display:flex;gap:6px;margin:14px 0 20px;flex-wrap:wrap">';
    foreach ($all_tabs as $k => $v) {
        $cnt    = $counts[$k] ?? 0;
        $active = ($k === $status_filter);
        $url    = add_query_arg(['page' => 'aitrongcay-orders', 'status' => $k], admin_url('admin.php'));
        echo '<a href="' . esc_url($url) . '" style="display:inline-flex;align-items:center;gap:5px;padding:5px 14px;border-radius:20px;text-decoration:none;font-size:13px;font-weight:600;'
            . ($active ? 'background:#15803d;color:#fff' : 'background:#f3f4f6;color:#374151') . '">'
            . esc_html($v) . ' <span style="background:' . ($active ? 'rgba(255,255,255,.25)' : '#e5e7eb') . ';border-radius:10px;padding:1px 8px;font-size:11px">' . $cnt . '</span></a>';
    }
    echo '</div>';

    if (empty($orders)) {
        echo '<p style="color:#9ca3af">Chưa có đơn hàng nào.</p></div>';
        return;
    }

    echo '<table class="wp-list-table widefat fixed striped" style="font-size:13px">';
    echo '<thead><tr>'
        . '<th style="width:150px">Mã đơn</th>'
        . '<th>Khách hàng</th>'
        . '<th style="width:150px">Cây / Dịch vụ</th>'
        . '<th style="width:110px">Tổng tiền</th>'
        . '<th style="width:120px">Ngày đặt</th>'
        . '<th style="width:130px">Trạng thái</th>'
        . '<th style="width:200px">Giao vườn</th>'
        . '<th style="width:220px">Cập nhật</th>'
        . '</tr></thead><tbody>';

    foreach ($orders as $o) {
        $oid       = esc_html((string) $o['order_id']);
        $color     = $badge_colors[$o['status']] ?? '#9ca3af';
        $label     = $labels[$o['status']] ?? (string) $o['status'];
        $date      = date_i18n('d/m/Y H:i', strtotime((string) $o['created_at']));
        $total_fmt = number_format((int) $o['total'], 0, ',', '.');

        // Build "Cây / Dịch vụ" cell from items JSON (fallback to plant_name)
        $_items_decoded = json_decode((string) ($o['items'] ?? ''), true);
        if (is_array($_items_decoded) && count($_items_decoded) > 0) {
            $_item_lines = array_map(static function (array $it): string {
                $nm  = trim((string) ($it['name'] ?? ''));
                $var = trim((string) ($it['variant'] ?? ''));
                $qty = (int) ($it['qty'] ?? 1);
                $lbl = $nm !== '' ? $nm : 'Dịch vụ';
                if ($var !== '') $lbl .= ' – ' . $var;
                if ($qty > 1)    $lbl .= ' ×' . $qty;
                return esc_html($lbl);
            }, array_filter($_items_decoded, 'is_array'));
            $plant_cell = implode('<br>', $_item_lines) ?: esc_html((string) ($o['plant_name'] ?? ''));
        } else {
            $plant_cell = esc_html((string) ($o['plant_name'] ?? ''));
        }

        // Build "Giao vườn" cell
        $existing_garden_key = trim((string) ($o['garden_key'] ?? ''));
        if ($existing_garden_key !== '') {
            $gk_short        = esc_html(substr($existing_garden_key, 0, 20) . (strlen($existing_garden_key) > 20 ? '…' : ''));
            $rack_setup_url  = esc_url(home_url('/portal/dashboard-2/') . '?garden=' . rawurlencode($existing_garden_key));
            $provision_cell  = '<div style="display:flex;flex-direction:column;gap:5px">'
                . '<span title="' . esc_attr($existing_garden_key) . '" style="display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#15803d;border-radius:8px;padding:3px 8px;font-size:11px;font-weight:700">🌿 ' . $gk_short . '</span>'
                . '<a href="' . $rack_setup_url . '" target="_blank" style="display:inline-flex;align-items:center;gap:4px;padding:4px 8px;background:#0f172a;color:#6fdba8;border-radius:6px;font-size:11px;font-weight:700;text-decoration:none;width:fit-content">⚙️ Cài đặt Rack</a>'
                . '</div>';
        } else {
            $rack_opts = '<option value="0">Tự động chọn</option>';
            foreach ($available_racks as $ar) {
                $rack_opts .= '<option value="' . (int) $ar['id'] . '">'
                    . esc_html((string) $ar['rack_code']) . ' – ' . esc_html((string) $ar['rack_name'])
                    . ' (' . (int) $ar['slot_count'] . ' khoang)</option>';
            }
            $provision_cell = (empty($available_racks) && $rack_opts === '<option value="0">Tự động chọn</option>')
                ? '<span style="color:#9ca3af;font-size:11px">Kho rack trống</span>'
                : '<div style="display:flex;flex-direction:column;gap:4px">'
                    . '<select data-rack-for="' . $oid . '" style="padding:3px 5px;border-radius:6px;border:1px solid #d1d5db;font-size:11px">' . $rack_opts . '</select>'
                    . '<button type="button" data-provision="' . $oid . '" style="padding:4px 8px;background:#15803d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:11px;font-weight:700">Giao vườn →</button>'
                    . '<span data-provision-msg="' . $oid . '" style="font-size:11px;display:none"></span>'
                    . '</div>';
        }

        echo '<tr>'
            . '<td><strong style="color:#15803d">' . $oid . '</strong>'
            . ($o['bank_ref'] !== '' ? '<br><span style="font-size:11px;color:#9ca3af">Ref: ' . esc_html((string) $o['bank_ref']) . '</span>' : '')
            . '</td>'
            . '<td>'
            . '<div style="font-weight:600">' . esc_html((string) $o['customer_name']) . '</div>'
            . '<div><a href="tel:' . esc_attr((string) $o['customer_phone']) . '" style="color:#15803d">' . esc_html((string) $o['customer_phone']) . '</a></div>'
            . ($o['customer_email'] !== '' ? '<div style="font-size:11px;color:#9ca3af">' . esc_html((string) $o['customer_email']) . '</div>' : '')
            . '</td>'
            . '<td style="font-size:12px;line-height:1.4">' . $plant_cell . '</td>'
            . '<td style="font-weight:700;color:#dc2626">' . $total_fmt . 'đ</td>'
            . '<td style="color:#6b7280">' . $date . '</td>'
            . '<td><span style="display:inline-block;background:' . $color . ';color:#fff;border-radius:12px;padding:3px 10px;font-size:12px;font-weight:700">' . esc_html($label) . '</span></td>'
            . '<td>' . $provision_cell . '</td>'
            . '<td>'
            . '<div style="display:flex;gap:5px;align-items:center">'
            . '<select data-oid="' . $oid . '" style="flex:1;padding:3px 5px;border-radius:6px;border:1px solid #d1d5db;font-size:12px">';

        foreach ($labels as $sv => $sl) {
            echo '<option value="' . esc_attr($sv) . '"' . selected((string) $o['status'], $sv, false) . '>' . esc_html($sl) . '</option>';
        }

        echo '</select>'
            . '<button type="button" data-save="' . $oid . '" style="padding:4px 10px;background:#15803d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600;white-space:nowrap">Lưu</button>'
            . '</div>'
            . '<input type="text" data-ref="' . $oid . '" placeholder="Mã CK (tuỳ chọn)" value="' . esc_attr((string) $o['bank_ref']) . '" style="width:100%;margin-top:4px;padding:3px 6px;border-radius:6px;border:1px solid #d1d5db;font-size:11px">'
            . '<textarea data-note="' . $oid . '" placeholder="Ghi chú gửi KH..." style="width:100%;margin-top:3px;padding:3px 6px;border-radius:6px;border:1px solid #d1d5db;font-size:11px;height:42px;resize:vertical"></textarea>'
            . '</td>'
            . '</tr>';

        if (trim((string) $o['note']) !== '') {
            echo '<tr style="background:#fffbeb"><td colspan="8" style="padding:5px 16px;font-size:12px;color:#92400e">📝 ' . esc_html((string) $o['note']) . '</td></tr>';
        }
    }

    echo '</tbody></table>';
    echo '<p style="font-size:12px;color:#9ca3af;margin-top:8px">Khi lưu trạng thái, email thông báo sẽ được gửi tự động tới khách hàng.</p>';

    // Inline JS
    echo '<script>
document.querySelectorAll("[data-provision]").forEach(function(btn){
  btn.addEventListener("click",function(){
    var oid     = btn.getAttribute("data-provision");
    var sel     = document.querySelector("[data-rack-for=\""+oid+"\"]");
    var msgEl   = document.querySelector("[data-provision-msg=\""+oid+"\"]");
    var rackId  = sel ? sel.value : "0";
    var fd      = new FormData();
    fd.append("action",   "aitrongcay_admin_provision_garden");
    fd.append("nonce",    ' . wp_json_encode($nonce) . ');
    fd.append("order_id", oid);
    fd.append("rack_id",  rackId);
    btn.disabled=true; btn.textContent="Đang xử lý...";
    if(msgEl){msgEl.style.display="none";}
    fetch(' . wp_json_encode($ajax_url) . ',{method:"POST",body:fd})
      .then(function(r){return r.json();})
      .then(function(res){
        if(res.success){
          var cell = btn.closest("td");
          if(cell){
            var gk = res.data && res.data.garden_key ? res.data.garden_key : "";
            var short = gk.length>20 ? gk.substring(0,20)+"…" : gk;
            cell.innerHTML = "<span title=\""+gk+"\" style=\"display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#15803d;border-radius:8px;padding:3px 8px;font-size:11px;font-weight:700\">🌿 "+short+"</span>"
              +"<div style=\"font-size:11px;color:#15803d;margin-top:3px\">✓ "+(res.data && res.data.message ? res.data.message : "Đã giao")+"</div>";
          }
        } else {
          btn.disabled=false; btn.textContent="Giao vườn →";
          if(msgEl){msgEl.style.display="block";msgEl.style.color="#dc2626";msgEl.textContent=(res.data&&res.data.message)?res.data.message:"Lỗi không xác định.";}
        }
      })
      .catch(function(){
        btn.disabled=false; btn.textContent="Giao vườn →";
        if(msgEl){msgEl.style.display="block";msgEl.style.color="#dc2626";msgEl.textContent="Lỗi kết nối.";}
      });
  });
});

document.querySelectorAll("[data-save]").forEach(function(btn){
  btn.addEventListener("click",function(){
    var oid  = btn.getAttribute("data-save");
    var sel  = document.querySelector("[data-oid=\""+oid+"\"]");
    var ref  = document.querySelector("[data-ref=\""+oid+"\"]");
    var note = document.querySelector("[data-note=\""+oid+"\"]");
    var fd   = new FormData();
    fd.append("action",     "aitrongcay_order_update_status");
    fd.append("nonce",      ' . wp_json_encode($nonce) . ');
    fd.append("order_id",   oid);
    fd.append("status",     sel ? sel.value : "");
    fd.append("bank_ref",   ref  ? ref.value  : "");
    fd.append("admin_note", note ? note.value : "");
    btn.disabled=true; btn.textContent="...";
    fetch(' . wp_json_encode($ajax_url) . ',{method:"POST",body:fd})
      .then(function(r){return r.json();})
      .then(function(res){
        btn.textContent = res.success ? "✓ Đã lưu" : "Lỗi";
        btn.style.background = res.success ? "#16a34a" : "#dc2626";
        setTimeout(function(){btn.disabled=false;btn.textContent="Lưu";btn.style.background="#15803d";},2800);
      })
      .catch(function(){btn.textContent="Lỗi";btn.disabled=false;});
  });
});
</script>';
    echo '</div>';
}

// ─── Admin page: Payment settings ────────────────────────────────────────────
function aitrongcay_render_payment_settings_page(): void {
    $bank  = aitrongcay_get_bank_settings();
    $saved = (isset($_GET['saved']) && $_GET['saved'] === '1');

    $bank_options = [
        'VCB'  => 'Vietcombank (VCB)',
        'TCB'  => 'Techcombank (TCB)',
        'MB'   => 'MB Bank',
        'ACB'  => 'ACB',
        'BIDV' => 'BIDV',
        'VTB'  => 'Vietinbank (VTB)',
        'VPB'  => 'VPBank',
        'TPB'  => 'TPBank',
        'STB'  => 'Sacombank',
        'HDB'  => 'HDBank',
        'MSB'  => 'MSB',
        'OCB'  => 'OCB',
        'SEAB' => 'SeABank',
    ];

    echo '<div class="wrap">';
    echo '<h1>💳 Cài đặt thanh toán</h1>';
    if ($saved) {
        echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt thành công.</p></div>';
    }

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="aitrongcay_save_payment_settings">';
    wp_nonce_field('aitrongcay_save_payment_settings');

    echo '<table class="form-table"><tbody>';
    echo '<tr><th scope="row"><label for="bank_code">Ngân hàng</label></th><td>'
        . '<select name="bank_code" id="bank_code" style="min-width:240px">';
    foreach ($bank_options as $code => $label) {
        echo '<option value="' . esc_attr($code) . '"' . selected($bank['bank_code'], $code, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select><p class="description">Mã ngân hàng dùng để tạo QR VietQR.</p></td></tr>';

    echo '<tr><th scope="row"><label for="bank_name">Tên hiển thị</label></th><td>'
        . '<input type="text" name="bank_name" id="bank_name" value="' . esc_attr($bank['bank_name']) . '" class="regular-text">'
        . '<p class="description">Tên ngân hàng hiển thị trong email gửi khách (VD: Techcombank).</p></td></tr>';

    echo '<tr><th scope="row"><label for="account_number">Số tài khoản</label></th><td>'
        . '<input type="text" name="account_number" id="account_number" value="' . esc_attr($bank['account_number']) . '" class="regular-text" placeholder="VD: 1234567890">'
        . '</td></tr>';

    echo '<tr><th scope="row"><label for="account_name">Tên chủ TK</label></th><td>'
        . '<input type="text" name="account_name" id="account_name" value="' . esc_attr($bank['account_name']) . '" class="regular-text">'
        . '<p class="description">Viết HOA không dấu theo chuẩn ngân hàng (VD: NGUYEN VAN A).</p></td></tr>';

    echo '</tbody></table>';
    submit_button('Lưu cài đặt');
    echo '</form>';

    // QR preview
    if ($bank['account_number'] !== '') {
        $preview_qr = aitrongcay_build_vietqr_url($bank['bank_code'], $bank['account_number'], $bank['account_name'], 150000, 'AITRTEST001');
        echo '<hr><h3>QR mẫu (150.000đ – nội dung AITRTEST001)</h3>';
        echo '<img src="' . esc_url($preview_qr) . '" alt="QR mẫu" width="200" height="200" style="border-radius:12px;border:1px solid #e5e7eb">';
        echo '<p style="font-size:13px;color:#6b7280;margin-top:6px">Quét thử bằng app ngân hàng để kiểm tra trước khi dùng thật.</p>';
    }

    echo '</div>';
}

add_action('admin_post_aitrongcay_save_payment_settings', 'aitrongcay_save_payment_settings');

function aitrongcay_save_payment_settings(): void {
    check_admin_referer('aitrongcay_save_payment_settings');
    if (! current_user_can('manage_options')) {
        wp_die('Không có quyền.');
    }

    $allowed = ['VCB','TCB','MB','ACB','BIDV','VTB','VPB','TPB','STB','HDB','MSB','OCB','SEAB'];
    $code    = strtoupper(sanitize_key((string) ($_POST['bank_code'] ?? '')));
    if (! in_array($code, $allowed, true)) {
        $code = 'TCB';
    }

    update_option('aitrongcay_bank_settings', [
        'bank_code'      => $code,
        'bank_name'      => sanitize_text_field((string) ($_POST['bank_name']      ?? '')),
        'account_number' => sanitize_text_field((string) ($_POST['account_number'] ?? '')),
        'account_name'   => strtoupper(sanitize_text_field((string) ($_POST['account_name'] ?? ''))),
    ]);

    wp_redirect(add_query_arg(['page' => 'aitrongcay-payment-settings', 'saved' => '1'], admin_url('admin.php')));
    exit;
}
