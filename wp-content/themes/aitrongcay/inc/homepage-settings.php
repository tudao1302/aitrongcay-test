<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

function aitrongcay_homepage_defaults(): array
{
    return [
        'hero_eyebrow' => 'Ai trồng cây',
        'hero_title' => 'Ai trồng cây',
        'hero_poem' => "Người đó có tiếng hát\nTrên vòm cây\nChim hót lời mê say.\n\nAi trồng cây\nNgười đó có ngọn gió\nRung cành cây\nHoa lá đùa lay lay.\n\nAi trồng cây\nNgười đó có bóng mát\nTrong vòm cây\nQuên nắng xa đường dài.\n\nAi trồng cây\nNgười đó có hạnh phúc\nMong chờ cây\nMau lớn lên từng ngày.\n\nAi trồng cây\nEm trồng cây\nEm trồng cây",
        'hero_lead' => 'Ai trồng cây là khu vườn số riêng cho gia đình. Anh/chị có thể theo dõi khu vườn của mình qua webcam, care log, dữ liệu môi trường và trợ lý AI.',
        'hero_primary_cta_label' => 'Đăng ký tư vấn trước',
        'hero_primary_cta_url' => '/dang-ky-tu-van/?from=hero',
        'hero_secondary_cta_label' => 'Xem trải nghiệm khu vườn',
        'hero_secondary_cta_url' => '/portal/',
        'hero_caption_title' => 'Một khu vườn có thể theo dõi mỗi ngày.',
        'hero_caption_body' => 'Anh/chị có thể mở lại để xem vườn, đọc nhật ký và hiểu điều gì đang diễn ra.',
        'hero_overlay_title' => 'Khu vườn của gia đình đang được theo dõi rõ ràng',
        'hero_overlay_body' => 'Khu vườn số gom webcam, dữ liệu môi trường, care log và trợ lý AI vào cùng một chỗ.',
        'hero_ai_summary' => 'Vườn đang ổn định. Gia đình có thể tiếp tục theo dõi và yên tâm hơn.',
        'value_props_eyebrow' => 'Vì sao mô hình này dễ tin hơn',
        'value_props_title' => 'Rõ hơn để gia đình yên tâm hơn mỗi ngày.',
        'value_prop_1_title' => 'Có khu vườn riêng để theo dõi',
        'value_prop_1_body' => 'Gia đình có thể xem webcam, ảnh theo mốc và hành trình lớn lên của khu vườn.',
        'value_prop_2_title' => 'Có quá trình để kiểm tra',
        'value_prop_2_body' => 'Mỗi việc chăm sóc quan trọng đều được ghi lại để anh/chị biết vườn đang được vận hành ra sao.',
        'value_prop_3_title' => 'Có trợ lý AI đồng hành',
        'value_prop_3_body' => 'Trợ lý AI giúp giải thích dữ liệu và hỗ trợ gia đình theo dõi vườn dễ hơn.',
        'value_prop_4_title' => 'Có cảm giác gắn bó thật hơn',
        'value_prop_4_body' => 'Đây không chỉ là một món hàng. Đây là một khu vườn số của riêng gia đình.',
        'journey_eyebrow' => 'Hành trình bắt đầu',
        'journey_title' => 'Từ lúc để lại thông tin đến lúc nhận lứa rau đầu tiên, mọi bước đều nên đủ rõ và không gây áp lực',
        'journey_side_kicker' => 'Điều được giữ xuyên suốt',
        'journey_side_title' => 'Một trải nghiệm nhẹ nhưng không sơ sài',
        'portal_eyebrow' => 'Khu vườn của gia đình',
        'portal_title' => 'Nơi gia đình theo dõi khu vườn của mình một cách rõ ràng',
        'portal_body' => 'Khu vườn số giúp anh/chị xem webcam, dữ liệu môi trường, care log và trợ lý AI trong cùng một chỗ. Khi cần kiểm tra, chỉ cần mở ra là thấy.',
        'packages_eyebrow' => 'Các mức bắt đầu',
        'packages_title' => 'Chọn nhịp phù hợp trước, rồi mới đi sâu hơn',
        'packages_body' => 'Các gói dưới đây nên được hiểu như điểm bắt đầu tham khảo để tư vấn, không phải ép chốt ngay trên website.',
        'trust_quote_title' => 'Thứ khiến mình yên tâm không phải là lời quảng cáo, mà là cảm giác luôn có chỗ để kiểm tra lại.',
        'trust_quote_body' => 'Khi một dịch vụ liên quan trực tiếp đến bữa ăn gia đình, sự tử tế thường nằm ở cách họ cho mình nhìn thấy quy trình và đối chiếu thông tin lúc cần.',
        'trust_quote_author' => '— Chị Hạnh, gói Family',
        'trust_eyebrow' => 'Những lớp tạo niềm tin',
        'trust_title' => '4 điều giúp gia đình thấy rõ hơn và tin hơn',
        'faq_eyebrow' => 'FAQ rút gọn',
        'faq_title' => 'Những câu hỏi ngắn để hiểu đúng mô hình khu vườn số cho gia đình',
        'cta_eyebrow' => 'Bắt đầu nhẹ nhàng',
        'cta_title' => 'Nếu thấy mô hình này hợp với gia đình mình, anh/chị có thể để lại thông tin để trao đổi trước',
        'cta_body' => 'Form dưới đây chỉ lấy những thông tin đủ cho vòng đầu. Mục tiêu là hiểu nhu cầu thật và xem mô hình có phù hợp hay không, chứ không đẩy anh/chị vào một quy trình quá dài ngay từ đầu.',
        'cta_submit_label' => 'Gửi thông tin tư vấn',
        'cta_secondary_label' => 'Xem khu vườn trước',
        'cta_secondary_url' => '/portal/',
    ];
}

function aitrongcay_homepage_content(): array
{
    $saved = get_option('aitrongcay_homepage_content', []);
    return array_merge(aitrongcay_homepage_defaults(), is_array($saved) ? $saved : []);
}

function aitrongcay_homepage_text(string $key): string
{
    $content = aitrongcay_homepage_content();
    return isset($content[$key]) ? (string) $content[$key] : '';
}

function aitrongcay_homepage_url(string $key): string
{
    $value = trim(aitrongcay_homepage_text($key));
    if ($value === '') {
        return home_url('/');
    }
    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }
    return home_url('/' . ltrim($value, '/'));
}

function aitrongcay_register_homepage_settings(): void
{
    register_setting('aitrongcay_homepage_content_group', 'aitrongcay_homepage_content', [
        'type' => 'array',
        'sanitize_callback' => 'aitrongcay_sanitize_homepage_content',
        'default' => aitrongcay_homepage_defaults(),
    ]);

    register_setting('aitrongcay_notification_settings_group', 'aitrongcay_notification_email', [
        'type' => 'string',
        'sanitize_callback' => static fn($v) => is_email(trim((string) $v)) ? trim((string) $v) : '',
        'default' => '',
    ]);
}
add_action('admin_init', 'aitrongcay_register_homepage_settings');

function aitrongcay_sanitize_homepage_content($input): array
{
    $defaults = aitrongcay_homepage_defaults();
    $input = is_array($input) ? $input : [];
    $output = [];
    foreach ($defaults as $key => $default) {
        $value = isset($input[$key]) ? (string) $input[$key] : $default;
        if (str_ends_with($key, '_url')) {
            $value = trim($value);
            $output[$key] = $value === '' ? $default : esc_url_raw($value);
            continue;
        }
        $output[$key] = sanitize_textarea_field($value);
    }
    return $output;
}

function aitrongcay_add_homepage_admin_page(): void
{
    add_submenu_page('aitrongcay-unified-admin-beta', 'Homepage content', 'Homepage content', 'edit_theme_options', 'aitrongcay-homepage-content', 'aitrongcay_render_homepage_admin_page');
}
add_action('admin_menu', 'aitrongcay_add_homepage_admin_page', 100);

function aitrongcay_homepage_admin_sections(): array
{
    return [
        'hero' => ['title' => 'Hero đầu trang', 'description' => 'Block mở đầu của homepage: phần text chính bên trái và cụm livecam bên phải.', 'fields' => [
            'hero_eyebrow' => 'Eyebrow', 'hero_title' => 'Tiêu đề chính', 'hero_poem' => 'Bài thơ hero', 'hero_lead' => 'Đoạn giới thiệu chính',
            'hero_primary_cta_label' => 'Nút chính - chữ', 'hero_primary_cta_url' => 'Nút chính - link', 'hero_secondary_cta_label' => 'Nút phụ - chữ', 'hero_secondary_cta_url' => 'Nút phụ - link',
            'hero_caption_title' => 'Caption livecam - tiêu đề', 'hero_caption_body' => 'Caption livecam - mô tả', 'hero_overlay_title' => 'Overlay livecam - tiêu đề', 'hero_overlay_body' => 'Overlay livecam - mô tả', 'hero_ai_summary' => 'AI summary',
        ]],
        'value_props' => ['title' => 'Block “Vì sao mô hình này dễ tin hơn”', 'description' => 'Ngay dưới hero. Gồm tiêu đề section và 4 card lợi ích.', 'fields' => [
            'value_props_eyebrow' => 'Eyebrow', 'value_props_title' => 'Tiêu đề', 'value_prop_1_title' => 'Card 1 - tiêu đề', 'value_prop_1_body' => 'Card 1 - mô tả', 'value_prop_2_title' => 'Card 2 - tiêu đề', 'value_prop_2_body' => 'Card 2 - mô tả', 'value_prop_3_title' => 'Card 3 - tiêu đề', 'value_prop_3_body' => 'Card 3 - mô tả', 'value_prop_4_title' => 'Card 4 - tiêu đề', 'value_prop_4_body' => 'Card 4 - mô tả',
        ]],
        'journey' => ['title' => 'Block “Hành trình bắt đầu”', 'description' => 'Khối timeline + khối phụ giải thích trải nghiệm.', 'fields' => [
            'journey_eyebrow' => 'Eyebrow', 'journey_title' => 'Tiêu đề', 'journey_side_kicker' => 'Khối phụ - kicker', 'journey_side_title' => 'Khối phụ - tiêu đề',
        ]],
        'portal' => ['title' => 'Block “Portal của khu vườn”', 'description' => 'Section dark band nói về portal và bằng chứng vận hành.', 'fields' => [
            'portal_eyebrow' => 'Eyebrow', 'portal_title' => 'Tiêu đề', 'portal_body' => 'Mô tả',
        ]],
        'packages' => ['title' => 'Block “Các mức bắt đầu”', 'description' => 'Section giới thiệu các gói bắt đầu.', 'fields' => [
            'packages_eyebrow' => 'Eyebrow', 'packages_title' => 'Tiêu đề', 'packages_body' => 'Mô tả',
        ]],
        'trust' => ['title' => 'Block niềm tin', 'description' => 'Khối quote và phần “4 điều giúp gia đình ra quyết định yên tâm hơn”.', 'fields' => [
            'trust_quote_title' => 'Quote - tiêu đề', 'trust_quote_body' => 'Quote - mô tả', 'trust_quote_author' => 'Quote - tác giả', 'trust_eyebrow' => 'Eyebrow', 'trust_title' => 'Tiêu đề',
        ]],
        'faq' => ['title' => 'Block FAQ rút gọn', 'description' => 'Tiêu đề của FAQ rút gọn trên homepage.', 'fields' => [
            'faq_eyebrow' => 'Eyebrow', 'faq_title' => 'Tiêu đề',
        ]],
        'cta' => ['title' => 'Block form cuối trang', 'description' => 'Section chốt cuối cùng trước form tư vấn.', 'fields' => [
            'cta_eyebrow' => 'Eyebrow', 'cta_title' => 'Tiêu đề', 'cta_body' => 'Mô tả', 'cta_submit_label' => 'Nút gửi - chữ', 'cta_secondary_label' => 'Nút phụ - chữ', 'cta_secondary_url' => 'Nút phụ - link',
        ]],
    ];
}

function aitrongcay_render_admin_text_field(string $name, string $id, string $label, string $value): void
{
    echo '<div style="margin-bottom:16px">';
    echo '<label for="' . esc_attr($id) . '" style="display:block;font-weight:600;margin-bottom:6px">' . esc_html($label) . '</label>';
    if (str_ends_with($name, '_url')) {
        echo '<input type="text" class="regular-text" style="width:100%;max-width:720px" id="' . esc_attr($id) . '" name="aitrongcay_homepage_content[' . esc_attr($name) . ']" value="' . esc_attr($value) . '">';
    } else {
        echo '<textarea class="large-text" rows="3" style="width:100%;max-width:980px" id="' . esc_attr($id) . '" name="aitrongcay_homepage_content[' . esc_attr($name) . ']">' . esc_textarea($value) . '</textarea>';
    }
    echo '</div>';
}

function aitrongcay_render_homepage_admin_page(): void
{
    if (! current_user_can('edit_pages')) {
        return;
    }
    $content = aitrongcay_homepage_content();
    $sections = aitrongcay_homepage_admin_sections();
    ?>
    <div class="wrap">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
            <div>
                <h1>Sửa nội dung trang chủ</h1>
                <p style="margin-top:6px">Các block bên dưới được xếp theo đúng thứ tự xuất hiện trên homepage để anh dễ hình dung đang sửa phần nào.</p>
            </div>
            <div>
                <a class="button button-secondary button-large" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Preview nhanh</a>
            </div>
        </div>
        <form method="post" action="options.php" style="margin-bottom:24px">
            <?php settings_fields('aitrongcay_notification_settings_group'); ?>
            <section style="background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.03)">
                <h2 style="margin:0 0 6px">Cài đặt thông báo</h2>
                <p style="margin:0 0 16px;color:#50575e">Email nhận thông báo mỗi khi có đăng ký tư vấn mới từ website.</p>
                <div style="max-width:480px">
                    <label for="aitrongcay_notification_email" style="display:block;font-weight:600;margin-bottom:6px">Email nhận lead tư vấn</label>
                    <input type="email" class="regular-text" style="width:100%" id="aitrongcay_notification_email" name="aitrongcay_notification_email" value="<?php echo esc_attr((string) get_option('aitrongcay_notification_email', '')); ?>" placeholder="Để trống = dùng email admin WordPress">
                    <p class="description" style="margin-top:6px">Hiện tại sẽ gửi đến: <strong><?php echo esc_html(aitrongcay_consultation_notification_email()); ?></strong></p>
                </div>
                <div style="margin-top:16px"><?php submit_button('Lưu email thông báo', 'secondary', '', false); ?></div>
            </section>
        </form>

        <form method="post" action="options.php">
            <?php settings_fields('aitrongcay_homepage_content_group'); ?>
            <?php foreach ($sections as $section_key => $section) : ?>
                <section style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:14px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.03)">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                        <div>
                            <div style="font-size:12px;font-weight:700;letter-spacing:.08em;color:#2271b1;text-transform:uppercase;margin-bottom:4px">Vị trí trên page</div>
                            <h2 style="margin:0 0 6px"><?php echo esc_html($section['title']); ?></h2>
                            <p style="margin:0;color:#50575e;max-width:900px"><?php echo esc_html($section['description'] ?? ''); ?></p>
                        </div>
                        <span style="display:inline-block;background:#f0f6fc;color:#0a4b78;border:1px solid #c3d9ed;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:600"><?php echo esc_html((string) $section_key); ?></span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px 24px">
                        <?php foreach ($section['fields'] as $key => $label) : ?>
                            <div><?php aitrongcay_render_admin_text_field($key, $key, $label, (string) ($content[$key] ?? '')); ?></div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <div style="margin-top:22px"><?php submit_button('Lưu nội dung trang chủ'); ?></div>
        </form>
    </div>
    <?php
}
