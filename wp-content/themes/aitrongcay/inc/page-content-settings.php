<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_page_content_definitions(): array
{
    return [
        'gioi_thieu' => [
            'option_name' => 'aitrongcay_content_gioi_thieu',
            'menu_title' => 'Giới thiệu',
            'page_title' => 'Sửa nội dung Giới thiệu',
            'preview_url' => home_url('/cach-hoat-dong/'),
            'sections' => [
                'hero' => [
                    'title' => 'Hero',
                    'fields' => [
                        'hero_eyebrow' => 'Eyebrow',
                        'hero_title' => 'Tiêu đề',
                        'hero_lead' => 'Mô tả',
                        'visual_kicker' => 'Visual - kicker',
                        'visual_title' => 'Visual - tiêu đề',
                        'visual_body' => 'Visual - mô tả',
                    ],
                ],
                'difference' => [
                    'title' => 'Khối khác biệt',
                    'fields' => [
                        'difference_eyebrow' => 'Eyebrow',
                        'difference_title' => 'Tiêu đề',
                        'difference_body' => 'Mô tả',
                        'benefit_eyebrow' => 'Khối phải - eyebrow',
                        'benefit_1' => 'Lợi ích 1',
                        'benefit_2' => 'Lợi ích 2',
                        'benefit_3' => 'Lợi ích 3',
                        'benefit_4' => 'Lợi ích 4',
                    ],
                ],
                'story' => [
                    'title' => 'Khối nội dung chính',
                    'fields' => [
                        'story_eyebrow' => 'Eyebrow',
                        'story_title' => 'Tiêu đề',
                        'story_p1' => 'Đoạn 1',
                        'story_p2' => 'Đoạn 2',
                        'story_p3' => 'Đoạn 3',
                    ],
                ],
                'cta' => [
                    'title' => 'Khối cuối trang',
                    'fields' => [
                        'cta_eyebrow' => 'Eyebrow',
                        'cta_title' => 'Tiêu đề',
                        'cta_body' => 'Mô tả',
                        'metric_1_label' => 'Metric 1 - nhãn',
                        'metric_1_value' => 'Metric 1 - giá trị',
                        'metric_2_label' => 'Metric 2 - nhãn',
                        'metric_2_value' => 'Metric 2 - giá trị',
                        'metric_3_label' => 'Metric 3 - nhãn',
                        'metric_3_value' => 'Metric 3 - giá trị',
                        'primary_cta' => 'Nút chính',
                        'secondary_cta' => 'Nút phụ',
                    ],
                ],
            ],
            'defaults' => [
                'hero_eyebrow' => 'Giới thiệu',
                'hero_title' => 'Ai trồng cây là một khu vườn số cho gia đình.',
                'hero_lead' => 'Anh/chị không chỉ nhận rau. Anh/chị còn theo dõi được cả quá trình. Có webcam, care log, dữ liệu môi trường và hồ sơ theo lô.',
                'visual_kicker' => 'Từ khu vườn đến bữa ăn',
                'visual_title' => 'Mọi thứ đủ rõ để gia đình yên tâm hơn',
                'visual_body' => 'Khu vườn không còn là một lời hứa mơ hồ. Anh/chị có thể mở ra và tự kiểm tra khi cần.',
                'difference_eyebrow' => 'Điểm khác biệt',
                'difference_title' => 'Không chỉ nhìn vào sản phẩm cuối',
                'difference_body' => 'Ai trồng cây cho anh/chị thấy cả quá trình. Nhờ vậy, cảm giác yên tâm đến từ những gì có thể kiểm tra, không chỉ từ lời giới thiệu.',
                'benefit_eyebrow' => 'Gia đình nhận được gì',
                'benefit_1' => 'Một khu vườn có thể xem bằng webcam',
                'benefit_2' => 'Nhật ký chăm sóc rõ ràng, dễ theo dõi',
                'benefit_3' => 'Dữ liệu môi trường được trình bày dễ hiểu',
                'benefit_4' => 'Hồ sơ theo lô đi cùng kỳ thu hoạch',
                'story_eyebrow' => 'Vì sao đáng bắt đầu',
                'story_title' => 'Một cách sống xanh gần gũi hơn',
                'story_p1' => 'Nhiều gia đình muốn ăn an tâm hơn. Nhưng không phải ai cũng có thời gian tự điều khiển và giám sát quá trình canh tác mỗi ngày.',
                'story_p2' => 'Ai trồng cây giữ lại cảm giác có một khu vườn của riêng mình. Đồng thời, hệ thống theo dõi giúp mọi thứ gọn hơn và rõ hơn.',
                'story_p3' => 'Mục tiêu cuối cùng là làm bữa ăn nhẹ lòng hơn. Và làm việc sống xanh trở nên dễ bắt đầu hơn.',
                'cta_eyebrow' => 'Bắt đầu từ sự phù hợp',
                'cta_title' => 'Mô hình này phù hợp khi gia đình cần sự rõ ràng và cảm giác gắn bó',
                'cta_body' => 'Anh/chị có thể xem trải nghiệm khu vườn trước để hiểu rõ hơn cách khu vườn vận hành mỗi ngày.',
                'metric_1_label' => 'Trọng tâm',
                'metric_1_value' => 'Minh bạch',
                'metric_2_label' => 'Cảm giác',
                'metric_2_value' => 'Sở hữu',
                'metric_3_label' => 'Giá trị',
                'metric_3_value' => 'An tâm hơn',
                'primary_cta' => 'Xem trải nghiệm khu vườn',
                'secondary_cta' => 'Xem Chợ quê',
            ],
        ],
        'cho_que' => [
            'option_name' => 'aitrongcay_content_cho_que',
            'menu_title' => 'Chợ quê',
            'page_title' => 'Sửa nội dung Chợ quê',
            'preview_url' => home_url('/cho-que/'),
            'sections' => [
                'listing_hero' => [
                    'title' => 'Danh sách Chợ quê',
                    'fields' => [
                        'listing_eyebrow' => 'Eyebrow',
                        'listing_title' => 'Tiêu đề',
                        'listing_primary_cta' => 'Nút chính',
                        'listing_created_notice' => 'Thông báo đăng tin thành công',
                        'listing_contact_label' => 'Nút liên hệ ở card',
                        'listing_view_label' => 'Nút xem tin ở card',
                        'listing_edit_label' => 'Nút sửa tin',
                        'listing_delete_label' => 'Nút xóa tin',
                        'listing_like_label' => 'Nhãn chưa thích',
                        'listing_liked_label' => 'Nhãn đã thích',
                        'empty_notice' => 'Thông báo khi chưa có tin',
                    ],
                ],
                'detail' => [
                    'title' => 'Chi tiết tin Chợ quê',
                    'fields' => [
                        'detail_eyebrow' => 'Eyebrow',
                        'detail_back_label' => 'Nút quay về',
                        'detail_contact_label' => 'Nút liên hệ',
                        'detail_comments_title' => 'Tiêu đề bình luận',
                        'detail_comment_form_title' => 'Tiêu đề form bình luận',
                        'detail_comment_submit_label' => 'Nút gửi bình luận',
                        'detail_comment_login_notice' => 'Thông báo cần đăng nhập để bình luận',
                    ],
                ],
            ],
            'defaults' => [
                'listing_eyebrow' => 'Chợ quê',
                'listing_title' => 'Rau, hoa, giống cây và đồ nhà vườn',
                'listing_primary_cta' => 'Đăng tin từ vườn của tôi',
                'listing_created_notice' => 'Đã đăng tin thành công.',
                'listing_contact_label' => 'Liên hệ',
                'listing_view_label' => 'Xem tin',
                'listing_edit_label' => 'Sửa tin',
                'listing_delete_label' => 'Xóa tin',
                'listing_like_label' => 'Thích',
                'listing_liked_label' => 'Đã thích',
                'empty_notice' => 'Chưa có tin nào. Người bán và người mua có thể bắt đầu đăng tin từ đây.',
                'detail_eyebrow' => 'Chợ quê',
                'detail_back_label' => '← Về Chợ quê',
                'detail_contact_label' => 'Liên hệ người đăng',
                'detail_comments_title' => 'Bình luận',
                'detail_comment_form_title' => 'Để lại bình luận',
                'detail_comment_submit_label' => 'Gửi bình luận',
                'detail_comment_login_notice' => 'Đăng nhập để bình luận về tin này.',
            ],
        ],
    ];
}

function aitrongcay_page_content_defaults(string $page): array
{
    $definitions = aitrongcay_page_content_definitions();
    return $definitions[$page]['defaults'] ?? [];
}

function aitrongcay_page_content_option_name(string $page): string
{
    $definitions = aitrongcay_page_content_definitions();
    return (string) ($definitions[$page]['option_name'] ?? '');
}

function aitrongcay_page_content_preview_url(string $page): string
{
    $definitions = aitrongcay_page_content_definitions();
    return isset($definitions[$page]['preview_url']) ? (string) $definitions[$page]['preview_url'] : home_url('/');
}

function aitrongcay_page_content(string $page): array
{
    $defaults = aitrongcay_page_content_defaults($page);
    $option_name = aitrongcay_page_content_option_name($page);
    $saved = $option_name !== '' ? get_option($option_name, []) : [];
    return array_merge($defaults, is_array($saved) ? $saved : []);
}

function aitrongcay_page_text(string $page, string $key): string
{
    $content = aitrongcay_page_content($page);
    return isset($content[$key]) ? (string) $content[$key] : '';
}

function aitrongcay_register_page_content_settings(): void
{
    foreach (aitrongcay_page_content_definitions() as $page => $definition) {
        register_setting(
            'aitrongcay_content_group_' . $page,
            (string) $definition['option_name'],
            [
                'type' => 'array',
                'sanitize_callback' => static fn($input): array => aitrongcay_sanitize_page_content($page, $input),
                'default' => (array) $definition['defaults'],
            ]
        );
    }
}
add_action('admin_init', 'aitrongcay_register_page_content_settings');

function aitrongcay_sanitize_page_content(string $page, $input): array
{
    $defaults = aitrongcay_page_content_defaults($page);
    $input = is_array($input) ? $input : [];
    $output = [];

    foreach ($defaults as $key => $default) {
        $output[$key] = sanitize_textarea_field((string) ($input[$key] ?? $default));
    }

    return $output;
}

function aitrongcay_add_content_admin_menu(): void
{
    add_menu_page(
        'Sửa nội dung website',
        'Sửa nội dung website',
        'edit_pages',
        'aitrongcay-site-content',
        'aitrongcay_render_site_content_welcome',
        'dashicons-edit-large',
        31
    );

    add_submenu_page(
        'aitrongcay-site-content',
        'Sửa nội dung trang chủ',
        'Trang chủ',
        'edit_pages',
        'aitrongcay-homepage-content-main',
        'aitrongcay_render_homepage_admin_page'
    );

    foreach (aitrongcay_page_content_definitions() as $page => $definition) {
        add_submenu_page(
            'aitrongcay-site-content',
            (string) $definition['page_title'],
            (string) $definition['menu_title'],
            'edit_pages',
            'aitrongcay-content-' . $page,
            static fn() => aitrongcay_render_page_content_admin_page($page)
        );
    }
}
add_action('admin_menu', 'aitrongcay_add_content_admin_menu');

function aitrongcay_render_site_content_welcome(): void
{
    ?>
    <div class="wrap">
        <h1>Sửa nội dung website</h1>
        <p>Anh có thể sửa nhanh text của các trang chính ngay trong menu này.</p>
        <ul style="line-height:1.9">
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=aitrongcay-homepage-content-main')); ?>">Trang chủ</a></li>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=aitrongcay-content-cho_que')); ?>">Chợ quê</a></li>
        </ul>
    </div>
    <?php
}

function aitrongcay_render_page_content_admin_page(string $page): void
{
    if (! current_user_can('edit_pages')) {
        return;
    }

    $definitions = aitrongcay_page_content_definitions();
    $definition = $definitions[$page] ?? null;
    if (! is_array($definition)) {
        return;
    }

    $content = aitrongcay_page_content($page);
    ?>
    <div class="wrap">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
            <div>
                <h1><?php echo esc_html((string) $definition['page_title']); ?></h1>
                <p style="margin-top:6px">Sửa xong có thể bấm Lưu rồi mở Preview nhanh để xem ngay ngoài site.</p>
            </div>
            <div>
                <a class="button button-secondary button-large" href="<?php echo esc_url(aitrongcay_page_content_preview_url($page)); ?>" target="_blank" rel="noopener">Preview nhanh</a>
            </div>
        </div>
        <form method="post" action="options.php">
            <?php settings_fields('aitrongcay_content_group_' . $page); ?>
            <?php $section_index = 1; ?>
            <?php foreach ((array) $definition['sections'] as $section) : ?>
                <section style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.03)">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                        <div>
                            <div style="font-size:12px;font-weight:700;letter-spacing:.08em;color:#2271b1;text-transform:uppercase;margin-bottom:4px">Vị trí trên page</div>
                            <h2 style="margin:0 0 6px"><?php echo esc_html((string) $section['title']); ?></h2>
                            <p style="margin:0;color:#50575e;max-width:900px">Block này được đặt theo đúng thứ tự hiển thị trên trang để anh dễ hình dung đang sửa đoạn nào.</p>
                        </div>
                        <span style="display:inline-block;background:#f0f6fc;color:#0a4b78;border:1px solid #c3d9ed;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:600">Block <?php echo esc_html((string) $section_index); ?></span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px 24px">
                        <?php foreach ((array) $section['fields'] as $key => $label) : ?>
                            <div>
                                <label for="<?php echo esc_attr($page . '_' . $key); ?>" style="display:block;font-weight:600;margin-bottom:6px"><?php echo esc_html((string) $label); ?></label>
                                <textarea class="large-text" rows="3" style="width:100%;max-width:980px" id="<?php echo esc_attr($page . '_' . $key); ?>" name="<?php echo esc_attr((string) $definition['option_name']); ?>[<?php echo esc_attr((string) $key); ?>]"><?php echo esc_textarea((string) ($content[$key] ?? '')); ?></textarea>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php $section_index++; ?>
            <?php endforeach; ?>
            <div style="margin-top:22px"><?php submit_button('Lưu nội dung trang'); ?></div>
        </form>
    </div>
    <?php
}
