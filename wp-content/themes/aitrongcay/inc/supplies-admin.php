<?php
if (!defined('ABSPATH')) {
    exit;
}

function aitrongcay_supplies_admin_menu(): void
{
    add_submenu_page(
        'aitrongcay-unified-admin-beta',
        'Kho vật tư',
        'Kho vật tư',
        'edit_theme_options',
        'aitrongcay-supplies',
        'aitrongcay_render_supplies_admin_page'
    );
}
add_action('admin_menu', 'aitrongcay_supplies_admin_menu', 100);

function aitrongcay_handle_supplies_admin_save(): void
{
    if (!is_admin() || !current_user_can('edit_theme_options')) {
        return;
    }

    if (($_POST['action'] ?? '') !== 'aitrongcay_save_supplies') {
        return;
    }

    check_admin_referer('aitrongcay_save_supplies');

    global $wpdb;
    $tables = function_exists('aitrongcay_onboarding_tables') ? aitrongcay_onboarding_tables() : [];
    $table = $tables['supplies'] ?? ($wpdb->prefix . 'aitr_supplies');

    $supplies = (array) ($_POST['supplies'] ?? []);

    foreach ($supplies as $id_str => $data) {
        if (isset($data['delete']) && $data['delete'] === '1') {
            if (is_numeric($id_str) && $id_str > 0) {
                $wpdb->delete($table, ['id' => (int) $id_str], ['%d']);
            }
            continue;
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            if (is_numeric($id_str) && $id_str > 0) {
                $wpdb->delete($table, ['id' => (int) $id_str], ['%d']);
            }
            continue;
        }

        $code = sanitize_text_field((string) ($data['code'] ?? ''));
        if ($code === '') {
            // Tự động sinh Mã SP từ Tên nếu để trống
            $code = strtoupper(str_replace('-', '_', sanitize_title($name)));
        }

        $row_data = [
            'name' => $name,
            'code' => $code,
            'type' => sanitize_text_field((string) ($data['type'] ?? '')),
            'spec' => sanitize_textarea_field((string) ($data['spec'] ?? '')),
            'cost_price' => max(0, (float) ($data['cost_price'] ?? 0)),
            'sale_price' => max(0, (float) ($data['sale_price'] ?? 0)),
            'stock_quantity' => max(0, (int) ($data['stock_quantity'] ?? 0)),
            'image_url' => sanitize_url((string) ($data['image_url'] ?? '')),
            'description' => sanitize_textarea_field((string) ($data['description'] ?? '')),
            'updated_at' => current_time('mysql'),
        ];

        if (strpos((string) $id_str, 'new_') === 0) {
            $wpdb->insert($table, $row_data, ['%s', '%s', '%s', '%s', '%f', '%f', '%d', '%s', '%s', '%s']);
        } else {
            $wpdb->update($table, $row_data, ['id' => (int) $id_str], ['%s', '%s', '%s', '%s', '%f', '%f', '%d', '%s', '%s', '%s'], ['%d']);
        }
    }

    wp_safe_redirect(add_query_arg(['page' => 'aitrongcay-supplies', 'updated' => '1'], admin_url('admin.php')));
    exit;
}
add_action('admin_init', 'aitrongcay_handle_supplies_admin_save');

function aitrongcay_render_supplies_admin_page(): void
{
    if (!current_user_can('edit_theme_options')) {
        wp_die('Không đủ quyền.');
    }

    global $wpdb;
    $tables = function_exists('aitrongcay_onboarding_tables') ? aitrongcay_onboarding_tables() : [];
    $table = $tables['supplies'] ?? ($wpdb->prefix . 'aitr_supplies');

    $supplies = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id ASC", ARRAY_A);

    ?>
    <div class="wrap">
        <h1>Quản lý Kho Vật Tư (Kho Tổng)</h1>
        <p>Danh sách các vật tư, thiết bị hiển thị trong phần Cửa hàng / Kho nông cụ.</p>

        <?php if (isset($_GET['updated'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Đã lưu danh sách vật tư.</p>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('aitrongcay_save_supplies'); ?>
            <input type="hidden" name="action" value="aitrongcay_save_supplies">

            <div
                style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;margin:20px 0;max-width:1400px;overflow-x:auto;">
                <table class="widefat striped" style="margin-bottom:15px;" data-supplies-table>
                    <thead>
                        <tr>
                            <th>Ảnh (URL)</th>
                            <th style="width:160px">Tên sản phẩm</th>
                            <th>Mã SP</th>
                            <th>Phân loại</th>
                            <th style="width:130px">Thông số</th>
                            <th>Giá gốc</th>
                            <th>Giá bán</th>
                            <th style="width:80px">Tồn kho</th>
                            <th style="width:180px">Mô tả</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($supplies as $supply):
                            $id = $supply['id'];
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($supply['image_url'])): ?>
                                        <img src="<?php echo esc_url($supply['image_url']); ?>"
                                            style="width:36px;height:36px;object-fit:cover;border-radius:4px;display:block;margin-bottom:4px;">
                                    <?php endif; ?>
                                    <input type="text" name="supplies[<?php echo esc_attr($id); ?>][image_url]"
                                        value="<?php echo esc_attr($supply['image_url'] ?? ''); ?>" class="regular-text"
                                        style="width:100%;">
                                </td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][name]"
                                        value="<?php echo esc_attr($supply['name'] ?? ''); ?>" class="regular-text"
                                        style="width:100%;"></td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][code]"
                                        value="<?php echo esc_attr($supply['code'] ?? ''); ?>" class="regular-text"
                                        style="width:80px;"></td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][type]"
                                        value="<?php echo esc_attr($supply['type'] ?? ''); ?>" class="regular-text"
                                        style="width:80px;"></td>
                                <td><textarea name="supplies[<?php echo esc_attr($id); ?>][spec]" rows="2"
                                        style="width:100%;"><?php echo esc_textarea($supply['spec'] ?? ''); ?></textarea></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][cost_price]"
                                        value="<?php echo esc_attr((float) ($supply['cost_price'] ?? 0)); ?>"
                                        class="regular-text" style="width:80px;" min="0"></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][sale_price]"
                                        value="<?php echo esc_attr((float) ($supply['sale_price'] ?? 0)); ?>"
                                        class="regular-text" style="width:80px;" min="0"></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][stock_quantity]"
                                        value="<?php echo esc_attr((int) ($supply['stock_quantity'] ?? 0)); ?>"
                                        class="regular-text" style="width:60px;" min="0"></td>
                                <td><textarea name="supplies[<?php echo esc_attr($id); ?>][description]" rows="2"
                                        style="width:100%;"><?php echo esc_textarea($supply['description'] ?? ''); ?></textarea>
                                </td>
                                <td><label><input type="checkbox" name="supplies[<?php echo esc_attr($id); ?>][delete]"
                                            value="1"> Xóa</label></td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Dòng trống để thêm mới vật tư -->
                        <?php for ($i = 1; $i <= 3; $i++):
                            $id = 'new_' . $i; ?>
                            <tr style="background-color: #f0f6fc;">
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][image_url]" value=""
                                        class="regular-text" style="width:100%;" placeholder="URL Ảnh"></td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][name]" value=""
                                        class="regular-text" style="width:100%;" placeholder="Tên sản phẩm mới"></td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][code]" value=""
                                        class="regular-text" style="width:80px;" placeholder="Tự tạo"></td>
                                <td><input type="text" name="supplies[<?php echo esc_attr($id); ?>][type]" value=""
                                        class="regular-text" style="width:80px;"></td>
                                <td><textarea name="supplies[<?php echo esc_attr($id); ?>][spec]" rows="2"
                                        style="width:100%;"></textarea></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][cost_price]"
                                        value="0" class="regular-text" style="width:80px;" min="0"></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][sale_price]"
                                        value="0" class="regular-text" style="width:80px;" min="0"></td>
                                <td><input type="number" step="1" name="supplies[<?php echo esc_attr($id); ?>][stock_quantity]"
                                        value="10" class="regular-text" style="width:60px;" min="0"></td>
                                <td><textarea name="supplies[<?php echo esc_attr($id); ?>][description]" rows="2"
                                        style="width:100%;"></textarea></td>
                                <td><span style="color:#666;font-size:12px;">(Mới)</span></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <?php submit_button('Lưu thay đổi'); ?>
            <p class="description">Lưu ý: Để xóa một sản phẩm, hãy tích vào ô "Xóa" hoặc xóa rỗng tên sản phẩm rồi bấm Lưu.
            </p>
    </div>
    <?php
}

