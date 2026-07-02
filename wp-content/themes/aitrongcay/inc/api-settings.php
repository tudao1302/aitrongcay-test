<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_register_api_settings(): void
{
    register_setting('aitrongcay_api_settings_group', 'aitrongcay_zalo_access_token', 'sanitize_text_field');
    register_setting('aitrongcay_api_settings_group', 'aitrongcay_sms_api_key', 'sanitize_text_field');
    register_setting('aitrongcay_api_settings_group', 'aitrongcay_sms_secret_key', 'sanitize_text_field');
}
add_action('admin_init', 'aitrongcay_register_api_settings');

function aitrongcay_add_api_settings_menu(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta', // Moved under Ai Trong Cay
        'Cài đặt API (Zalo & SMS)',
        'API Zalo & SMS',
        'manage_options',
        'aitrongcay-api-settings',
        'aitrongcay_render_api_settings_page'
    );
}
add_action('admin_menu', 'aitrongcay_add_api_settings_menu', 20);

function aitrongcay_render_api_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Cài đặt API Keys (Zalo & SMS)</h1>
        <p>Khu vực dành cho quản trị viên cấu hình kết nối các dịch vụ gửi tin nhắn báo thức tưới cây cho người dùng.</p>
        
        <form method="post" action="options.php">
            <?php settings_fields('aitrongcay_api_settings_group'); ?>
            
            <section style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.03);max-width:800px;">
                <h2 style="margin-top:0;color:#2271b1">Zalo Notification Service (ZNS)</h2>
                <p style="color:#50575e;margin-bottom:18px">Lấy mã Access Token từ Zalo Cloud Account để hệ thống có thể tự động gửi ZNS.</p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="aitrongcay_zalo_access_token">Zalo Access Token</label></th>
                            <td>
                                <input name="aitrongcay_zalo_access_token" type="text" id="aitrongcay_zalo_access_token" value="<?php echo esc_attr(get_option('aitrongcay_zalo_access_token')); ?>" class="regular-text ltr" style="width:100%;">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.03);max-width:800px;">
                <h2 style="margin-top:0;color:#2271b1">SMS Gateway (eSMS / VietGuys)</h2>
                <p style="color:#50575e;margin-bottom:18px">Lấy thông tin API Key từ nhà cung cấp dịch vụ SMS để bắn tin nhắn qua số điện thoại.</p>
                
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="aitrongcay_sms_api_key">SMS API Key</label></th>
                            <td>
                                <input name="aitrongcay_sms_api_key" type="text" id="aitrongcay_sms_api_key" value="<?php echo esc_attr(get_option('aitrongcay_sms_api_key')); ?>" class="regular-text ltr" style="width:100%;">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="aitrongcay_sms_secret_key">SMS Secret Key</label></th>
                            <td>
                                <input name="aitrongcay_sms_secret_key" type="password" id="aitrongcay_sms_secret_key" value="<?php echo esc_attr(get_option('aitrongcay_sms_secret_key')); ?>" class="regular-text ltr" style="width:100%;">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
            
            <?php submit_button('Lưu Cấu Hình API'); ?>
        </form>
    </div>
    <?php
}
