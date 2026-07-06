<?php
/**
 * Reward Management Admin Pages
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_register_rewards_admin_pages(): void {
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Lịch sử Đổi thưởng',
        'Lịch sử Đổi thưởng',
        'manage_options',
        'aitrongcay-reward-history',
        'aitrongcay_render_reward_history_page'
    );

}
add_action('admin_menu', 'aitrongcay_register_rewards_admin_pages', 101);

function aitrongcay_handle_reward_admin_actions(): void {
    if (! is_admin() || ! current_user_can('manage_options')) {
        return;
    }

    $action = sanitize_key((string) ($_POST['beta_action'] ?? ''));
    if ($action === '') {
        return;
    }

    if ($action === 'complete_redemption') {
        check_admin_referer('aitrongcay_beta_action_nonce');
        $user_id = absint($_POST['user_id'] ?? 0);
        $redeem_id = sanitize_text_field($_POST['redeem_id'] ?? '');
        if ($user_id > 0 && $redeem_id !== '') {
            $history = (array) get_user_meta($user_id, '_aitrongcay_redeem_history', true);
            foreach ($history as &$h) {
                if (($h['id'] ?? '') === $redeem_id) {
                    $h['status'] = 'completed';
                    break;
                }
            }
            update_user_meta($user_id, '_aitrongcay_redeem_history', $history);
            wp_safe_redirect(add_query_arg(['beta_success' => '1'], wp_get_referer()));
            exit;
        }
    }


}
add_action('admin_init', 'aitrongcay_handle_reward_admin_actions');

function aitrongcay_render_reward_history_page(): void {
    // Fetch Global Redemption History
    $all_history = [];
    $users_with_history = get_users(['meta_key' => '_aitrongcay_redeem_history', 'fields' => ['ID', 'user_email', 'display_name']]);
    foreach ($users_with_history as $u) {
        $history = (array) get_user_meta($u->ID, '_aitrongcay_redeem_history', true);
        foreach ($history as $h) {
            if (!is_array($h)) continue;
            $h['user_id'] = $u->ID;
            $h['user_email'] = $u->user_email;
            $h['display_name'] = $u->display_name;
            $all_history[] = $h;
        }
    }
    usort($all_history, function($a, $b) { return ($b['time'] ?? 0) <=> ($a['time'] ?? 0); });
    
    $success = isset($_GET['beta_success']) && $_GET['beta_success'] == '1';
    ?>
    <div class="wrap">
        <h1 style="display:flex;align-items:center;gap:8px">🎁 Quản lý Lịch sử Đổi thưởng</h1>
        
        <?php if ($success): ?>
            <div class="notice notice-success is-dismissible"><p>Thao tác thành công.</p></div>
        <?php endif; ?>

        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px; font-size: 13px;">
            <thead>
                <tr>
                    <th style="width: 250px;">Khách hàng</th>
                    <th style="width: 200px;">Phần thưởng</th>
                    <th style="width: 100px;">Điểm đã trừ</th>
                    <th style="width: 150px;">Thời gian</th>
                    <th style="width: 120px;">Trạng thái</th>
                    <th style="width: 150px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($all_history)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#64748b;padding: 20px;">Chưa có lịch sử đổi thưởng nào.</td></tr>
                <?php else: foreach ($all_history as $h): ?>
                    <tr>
                        <td>
                            <?php 
                            $account_name = $h['display_name'] ?: 'Khách hàng';
                            $has_shipping = !empty($h['recipient']['name']);
                            $recipient_name = $has_shipping ? $h['recipient']['name'] : '';
                            
                            // Mặc định hiển thị tên người nhận, nếu không có thì lấy tên tài khoản
                            $primary_name = $has_shipping ? $recipient_name : $account_name;
                            ?>
                            <div style="font-weight:600;font-size:13px;color:#1e293b;">
                                <?php echo esc_html($primary_name); ?>
                                <?php if($has_shipping && !empty($h['recipient']['phone'])): ?>
                                    <span style="color:#475569">- <?php echo esc_html($h['recipient']['phone']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="color:#64748b;font-size:11px;margin-top:2px;">Email: <?php echo esc_html($h['user_email']); ?></div>
                            
                            <?php if ($has_shipping): ?>
                                <div style="margin-top:6px;font-size:11px;color:#475569;line-height:1.4">
                                    <?php if (strcasecmp($account_name, $recipient_name) !== 0 && strcasecmp(trim($account_name), 'Khách hàng') !== 0): ?>
                                        <div style="margin-bottom:2px"><i class="fa-regular fa-user" style="width:12px;text-align:center"></i> Tài khoản: <?php echo esc_html($account_name); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($h['recipient']['address'])): ?>
                                        <div><i class="fa-solid fa-location-dot" style="width:12px;text-align:center"></i> <?php echo esc_html($h['recipient']['address']); ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if(!empty($h['recipient']['note'])): ?>
                                        <div style="color:#d97706;font-style:italic;margin-top: 4px;">Ghi chú: <?php echo esc_html($h['recipient']['note']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 600;"><?php echo esc_html(($h['icon'] ?? '') . ' ' . ($h['name'] ?? '')); ?></td>
                        <td style="color:#10b981;font-weight:700"><?php echo esc_html('-' . ($h['points'] ?? 0)); ?></td>
                        <td style="color: #64748b;"><?php echo date_i18n('d/m/Y H:i', (int)($h['time'] ?? 0)); ?></td>
                        <td>
                            <?php if (($h['status'] ?? 'pending') === 'pending'): ?>
                                <span style="display:inline-block;background:#fef3c7;color:#d97706;padding:4px 8px;border-radius:12px;font-size:11px;font-weight:700;">Chờ xử lý</span>
                            <?php else: ?>
                                <span style="display:inline-block;background:#dcfce7;color:#15803d;padding:4px 8px;border-radius:12px;font-size:11px;font-weight:700;">Hoàn thành</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($h['status'] ?? 'pending') === 'pending'): ?>
                                <form method="post" style="display:inline">
                                    <?php wp_nonce_field('aitrongcay_beta_action_nonce'); ?>
                                    <input type="hidden" name="beta_action" value="complete_redemption">
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($h['user_id']); ?>">
                                    <input type="hidden" name="redeem_id" value="<?php echo esc_attr($h['id'] ?? ''); ?>">
                                    <button type="submit" class="button button-primary" style="padding:0 8px;font-size:11px;min-height: 24px;line-height: 24px;"><i class="fa-solid fa-check"></i> Hoàn thành</button>
                                </form>
                            <?php else: ?>
                                <span style="color:#94a3b8;font-size:11px"><i class="fa-solid fa-check-circle"></i> Đã xử lý</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}


