<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
$created_post = absint($_GET['created_post'] ?? 0);
$fallback_market_view_garden_key = '';
if (isset($_GET['garden'])) {
    $requested_market_garden = sanitize_text_field((string) wp_unslash($_GET['garden']));
    if ($requested_market_garden !== '' && is_user_logged_in() && aitrongcay_user_can_view_garden($requested_market_garden, get_current_user_id())) {
        $fallback_market_view_garden_key = $requested_market_garden;
    }
}
$fallback_market_list_url = $fallback_market_view_garden_key !== '' ? add_query_arg('garden', $fallback_market_view_garden_key, home_url('/cho-que/')) : home_url('/cho-que/');
?>
<main>
    <section class="section">
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $managed_templates = [
                        'cach-hoat-dong' => 'template-parts/virtual/cach-hoat-dong.php',
                        'cho-que' => 'template-parts/virtual/cho-que.php',
                        'tai-khoan' => 'template-parts/virtual/tai-khoan.php',
                    ];
                    $current_slug = get_post_field('post_name', get_the_ID());
                    if (is_string($current_slug) && isset($managed_templates[$current_slug])) {
                        include get_template_directory() . '/' . $managed_templates[$current_slug];
                        continue;
                    }
                    ?>
                    <article <?php post_class('glass-card'); ?>>
                        <span class="eyebrow"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Trang'); ?></span>
                        <h1><?php the_title(); ?></h1>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    </article>

                    <?php if (is_page('cho-que')) : ?>
                        <section class="section-tight">
                            <div style="border-radius:28px;overflow:hidden;background:#0f172a;box-shadow:0 24px 60px rgba(15,23,42,.14)">
                                <video autoplay muted loop playsinline controls poster="<?php echo esc_url(get_template_directory_uri() . '/assets/images/story-morning.svg'); ?>" style="display:block;width:100%;height:auto;aspect-ratio:16/9;background:#0f172a">
                                    <source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4">
                                    Trình duyệt này chưa phát được video demo.
                                </video>
                            </div>
                            <p class="small subtle" style="margin-top:12px">Video demo được chèn thêm để anh xem cảm giác media sống trên trang. Sau này có thể thay bằng video đúng chủ đề từng page.</p>
                        </section>
                    <?php endif; ?>

                    <?php if (is_page('cho-que') && isset($_GET['market_post'])) : ?>
                        <?php $market_post_id = absint($_GET['market_post'] ?? 0); $market_post = $market_post_id ? get_post($market_post_id) : null; ?>
                        <section class="section-tight market-page-shell">
                            <?php if ($market_post && $market_post->post_type === 'aitr_market_post') : ?>
                                <?php
                                $gallery = array_map('absint', (array) get_post_meta($market_post->ID, '_aitrongcay_market_gallery', true));
                                $author_id = (int) $market_post->post_author;
                                $author_name = get_the_author_meta('display_name', $author_id);
                                $author_email = get_the_author_meta('user_email', $author_id);
                                ?>
                                <article class="glass-card market-detail-card market-detail-clean">
                                    <div class="market-detail-head">
                                        <div>
                                            <span class="eyebrow">Chợ quê</span>
                                            <h1><?php echo esc_html(get_the_title($market_post)); ?></h1>
                                            <div class="market-meta-line">
                                                <span><?php echo esc_html($author_name); ?></span>
                                                <span><?php echo esc_html(get_the_date('d/m/Y H:i', $market_post)); ?></span>
                                            </div>
                                        </div>
                                        <div class="inline-list">
                                            <a class="btn btn-primary" href="<?php echo esc_url(aitrongcay_market_zalo_action_url((int) $market_post->ID)); ?>">Nhắn Zalo</a>
                                            <a class="btn btn-secondary" href="<?php echo esc_url($fallback_market_list_url); ?>">← Về Chợ quê</a>
                                        </div>
                                    </div>
                                    <?php if (has_post_thumbnail($market_post)) : ?><div class="card-media media-frame media-frame-16x9" style="margin-top:16px"><img class="media-thumb media-fit-cover" src="<?php echo esc_url(get_the_post_thumbnail_url($market_post, 'full')); ?>" alt="<?php echo esc_attr(get_the_title($market_post)); ?>"></div><?php endif; ?>
                                    <?php if ($gallery) : ?><div class="cards-4 market-gallery" style="margin-top:18px"><?php foreach ($gallery as $gallery_id) : ?><img class="media-thumb media-fit-cover" src="<?php echo esc_url(wp_make_link_relative((string) wp_get_attachment_image_url($gallery_id, 'large'))); ?>" alt="gallery image"><?php endforeach; ?></div><?php endif; ?>
                                    <div class="entry-content market-copy-clean" style="margin-top:20px"><?php echo wpautop(wp_kses_post($market_post->post_content)); ?></div>
                                </article>
                            <?php else : ?>
                                <div class="notice error">Không tìm thấy tin đăng này.</div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (is_page('cho-que') && ! isset($_GET['market_post'])) : ?>
                        <?php
                        $active_market_category = sanitize_text_field((string) ($_GET['market_category'] ?? ''));
                        $active_market_offer_type = sanitize_text_field((string) ($_GET['market_offer_type'] ?? ''));
                        $active_market_sort = sanitize_key((string) ($_GET['market_sort'] ?? 'newest'));
                        $market_categories = ['Hạt giống', 'Cây giống', 'Dinh dưỡng cho cây', 'Các loại rau', 'Hoa'];
                        $market_offer_types = ['Bán', 'Trao đổi', 'Chia sẻ', 'Nhận đặt trước'];
                        $market_posts = new WP_Query(aitrongcay_market_posts_query_args($fallback_market_view_garden_key, 12));
                        ?>
                        <section class="section-tight market-page-shell" id="market-drafts">
                            <div class="market-board-head">
                                <div>
                                    <span class="eyebrow">Chợ quê</span>
                                    <h2>Rau, hoa, giống cây và đồ nhà vườn</h2>
                                    <div class="small subtle" style="margin-top:6px">Có <strong><?php echo esc_html((string) $market_posts->found_posts); ?></strong> kết quả<?php echo $active_market_category !== '' || $active_market_offer_type !== '' ? ' sau khi lọc' : ''; ?>.</div>
                                </div>
                                <div class="inline-list">
                                    <?php if (is_user_logged_in()) : ?>
                                        <a class="btn btn-primary" href="<?php echo esc_url(home_url('/portal/dashboard-2/#photo-library')); ?>">Đăng tin từ vườn của tôi</a>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary" type="button" data-open-market-compose>Đăng tin mới</button>
                                </div>
                            </div>
                            <?php if ($created_post) : ?><div class="notice success" style="margin:12px 0 20px"><strong>Đã đăng tin thành công.</strong></div><?php endif; ?>
                            <div class="market-compose-modal" data-market-edit-modal hidden>
                                <div class="market-compose-backdrop" data-close-market-edit></div>
                                <div class="market-compose-dialog market-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="market-edit-title-fallback">
                                    <button class="market-compose-close" type="button" aria-label="Đóng" data-close-market-edit>×</button>
                                    <div class="market-compose-head market-compose-head-ai">
                                        <div>
                                            <span class="eyebrow">Sửa tin đăng</span>
                                            <h3 id="market-edit-title-fallback">Chỉnh sửa tin Chợ quê</h3>
                                        </div>
                                    </div>
                                    <form class="market-compose-form" data-market-edit-form>
                                        <input type="hidden" data-market-edit-post-id>
                                        <div class="market-compose-surface">
                                            <div class="market-compose-title-wrap">
                                                <input id="market-edit-title-input-fallback" type="text" maxlength="140" placeholder="Tiêu đề" aria-label="Tiêu đề" data-market-edit-title>
                                            </div>
                                            <div class="market-structured-grid">
                                                <div><select id="market-edit-category-fallback" aria-label="Danh mục" data-market-edit-category><option value="">Danh mục</option><option>Hạt giống</option><option>Cây giống</option><option>Dinh dưỡng cho cây</option><option>Các loại rau</option><option>Hoa</option></select></div>
                                                <div><select id="market-edit-offer-type-fallback" aria-label="Hình thức" data-market-edit-offer-type><option value="">Hình thức</option><option>Bán</option><option>Trao đổi</option><option>Chia sẻ</option><option>Nhận đặt trước</option></select></div>
                                                <div><input id="market-edit-quantity-fallback" type="text" placeholder="Số lượng" aria-label="Số lượng" data-market-edit-quantity></div>
                                                <div><input id="market-edit-area-fallback" type="text" placeholder="Khu vực" aria-label="Khu vực" data-market-edit-area></div>
                                                <div><input id="market-edit-availability-fallback" type="text" placeholder="Thời gian nhận/giao" aria-label="Thời gian nhận hoặc giao" data-market-edit-availability></div>
                                                <div><input id="market-edit-contact-fallback" type="text" placeholder="Liên hệ" aria-label="Liên hệ" data-market-edit-contact></div>
                                            </div>
                                            <div class="market-compose-body-wrap">
                                                <textarea id="market-edit-content-input-fallback" placeholder="Nội dung" aria-label="Nội dung" data-market-edit-content></textarea>
                                            </div>
                                            <div class="market-compose-toolbar">
                                                <label class="market-compose-upload" for="market-edit-photo-input-fallback">
                                                    <span>＋</span>
                                                    <strong>Thay ảnh</strong>
                                                </label>
                                                <input id="market-edit-photo-input-fallback" type="file" accept="image/*" multiple data-market-edit-files>
                                            </div>
                                        </div>
                                        <div class="market-edit-current-media" data-market-edit-current-media></div>
                                        <div class="market-compose-preview" data-market-edit-preview></div>
                                        <div class="market-compose-foot">
                                            <div class="notice" data-market-edit-notice style="display:none;margin:0"></div>
                                            <div class="inline-list market-compose-actions" style="justify-content:flex-end">
                                                <button class="btn btn-ghost" type="button" data-close-market-edit>Đóng</button>
                                                <button class="btn btn-primary" type="submit" data-market-edit-submit>Lưu thay đổi</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="market-compose-modal" data-market-compose-modal hidden>
                                <div class="market-compose-backdrop" data-close-market-compose></div>
                                <div class="market-compose-dialog" role="dialog" aria-modal="true" aria-labelledby="market-compose-title">
                                    <button class="market-compose-close" type="button" aria-label="Đóng" data-close-market-compose>×</button>
                                    <?php if (is_user_logged_in()) : ?>
                                        <div class="market-compose-head market-compose-head-ai">
                                            <div>
                                                <span class="eyebrow">Đăng tin mới</span>
                                                <h3 id="market-compose-title">Đăng tin Chợ quê</h3>
                                            </div>
                                        </div>
                                        <form class="market-compose-form" data-market-compose-form>
                                            <div class="market-compose-surface">
                                                <div class="market-compose-title-wrap">
                                                    <input id="market-compose-title-input" type="text" maxlength="140" placeholder="Tiêu đề" aria-label="Tiêu đề" data-market-compose-title>
                                                </div>
                                                <div class="market-structured-grid">
                                                    <div><select id="market-compose-category" aria-label="Danh mục" data-market-compose-category><option value="">Danh mục</option><option>Hạt giống</option><option>Cây giống</option><option>Dinh dưỡng cho cây</option><option>Các loại rau</option><option>Hoa</option></select></div>
                                                    <div><select id="market-compose-offer-type" aria-label="Hình thức" data-market-compose-offer-type><option value="">Hình thức</option><option>Bán</option><option>Trao đổi</option><option>Chia sẻ</option><option>Nhận đặt trước</option></select></div>
                                                    <div><input id="market-compose-quantity" type="text" placeholder="Số lượng" aria-label="Số lượng" data-market-compose-quantity></div>
                                                    <div><input id="market-compose-area" type="text" placeholder="Khu vực" aria-label="Khu vực" data-market-compose-area></div>
                                                    <div><input id="market-compose-availability" type="text" placeholder="Thời gian nhận/giao" aria-label="Thời gian nhận hoặc giao" data-market-compose-availability></div>
                                                    <div><input id="market-compose-contact" type="text" placeholder="Liên hệ" aria-label="Liên hệ" data-market-compose-contact></div>
                                                </div>
                                                <div class="market-compose-body-wrap">
                                                    <textarea id="market-compose-content-input" placeholder="Nội dung" aria-label="Nội dung" data-market-compose-content></textarea>
                                                </div>
                                                <div class="market-compose-toolbar">
                                                    <label class="market-compose-upload" for="market-compose-photo-input">
                                                        <span>＋</span>
                                                        <strong>Thêm ảnh</strong>
                                                    </label>
                                                    <input id="market-compose-photo-input" type="file" accept="image/*" multiple data-market-compose-files>
                                                </div>
                                            </div>
                                            <div class="market-compose-preview" data-market-compose-preview></div>
                                            <div class="market-compose-foot">
                                                <div class="notice" data-market-compose-notice style="display:none;margin:0"></div>
                                                <div class="inline-list market-compose-actions" style="justify-content:flex-end">
                                                    <button class="btn btn-ghost" type="button" data-close-market-compose>Đóng</button>
                                                    <button class="btn btn-primary" type="submit" data-market-compose-submit>Đăng tin ngay</button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else : ?>
                                        <div class="market-compose-head">
                                            <span class="eyebrow">Đăng tin mới</span>
                                            <h3 id="market-compose-title">Anh/chị cần đăng nhập để đăng tin lên Chợ quê</h3>
                                            <p class="subtle">Sau khi đăng nhập, anh/chị có thể tạo tin mới, chọn ảnh minh họa và quản lý tin đăng của mình ngay trên trang này.</p>
                                        </div>
                                        <div class="inline-list" style="margin-top:18px">
                                            <a class="btn btn-primary" href="<?php echo esc_url(wp_login_url($fallback_market_list_url)); ?>">Đăng nhập</a>
                                            <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/dang-ky-tu-van/')); ?>">Nhờ hỗ trợ</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card soft-card market-filter-card" style="padding:18px;margin:0 0 18px;">
                                <form method="get" class="market-filter-form" style="display:grid;gap:10px">
                                    <select name="market_category">
                                        <option value="">Tất cả danh mục</option>
                                        <?php foreach ($market_categories as $category_label) : ?>
                                            <option value="<?php echo esc_attr($category_label); ?>" <?php selected($active_market_category, $category_label); ?>><?php echo esc_html($category_label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="market_offer_type">
                                        <option value="">Tất cả hình thức</option>
                                        <?php foreach ($market_offer_types as $offer_label) : ?>
                                            <option value="<?php echo esc_attr($offer_label); ?>" <?php selected($active_market_offer_type, $offer_label); ?>><?php echo esc_html($offer_label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="market_sort">
                                        <option value="newest" <?php selected($active_market_sort, 'newest'); ?>>Mới nhất</option>
                                        <option value="popular" <?php selected($active_market_sort, 'popular'); ?>>Nhiều chia sẻ</option>
                                    </select>
                                    <div class="inline-list" style="gap:8px;justify-content:flex-start">
                                        <button class="btn btn-secondary" type="submit">Lọc</button>
                                        <a class="btn btn-ghost" href="<?php echo esc_url($fallback_market_list_url); ?>">Xóa lọc</a>
                                    </div>
                                </form>
                            </div>
                            <div class="market-list-clean market-grid-clean">
                                <?php if ($market_posts->have_posts()) : ?>
                                    <?php while ($market_posts->have_posts()) : $market_posts->the_post(); ?>
                                        <?php
                                        $gallery = array_map('absint', (array) get_post_meta(get_the_ID(), '_aitrongcay_market_gallery', true));
                                        $author_id = (int) get_post_field('post_author', get_the_ID());
                                        $author_name = get_the_author_meta('display_name', $author_id);
                                        $author_email = get_the_author_meta('user_email', $author_id);
                                        ?>
                                        <?php
                                        $thumb_url = has_post_thumbnail() ? (string) get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
                                        $gallery_url = ($gallery && ! empty($gallery[0])) ? (string) wp_get_attachment_image_url($gallery[0], 'large') : '';
                                        $market_image_url = $thumb_url ?: $gallery_url;
                                        $market_fallback_url = get_template_directory_uri() . '/assets/images/market-harvest.svg';
                                        ?>
                                        <article class="market-row-card<?php echo $created_post === get_the_ID() ? ' created-market-post' : ''; ?>" data-market-card>
                                            <div class="market-row-media media-frame media-frame-16x9">
                                                <?php if ($market_image_url) : ?>
                                                    <img class="media-thumb media-fit-cover" src="<?php echo esc_url(wp_make_link_relative($market_image_url)); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" onerror="this.onerror=null;this.src='<?php echo esc_url($market_fallback_url); ?>';this.alt='<?php echo esc_attr(get_the_title()); ?>';">
                                                <?php else : ?>
                                                    <img class="media-thumb media-fit-cover" src="<?php echo esc_url($market_fallback_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
                                                <?php endif; ?>
                                            </div>
                                            <div class="market-row-text">
                                                <div class="market-card-topline">
                                                    <span class="kicker"><?php echo esc_html($author_name); ?></span>
                                                    <span class="small subtle"><?php echo esc_html(get_the_date('d/m/Y', get_the_ID())); ?></span>
                                                </div>
                                                <h3 data-market-title-render><?php the_title(); ?></h3>
                                                <div class="market-contact-line">
                                                    <span>Liên hệ: Nhắn Zalo</span>
                                                </div>
                                                <div class="entry-content market-copy-clean" data-market-content-render><?php echo wpautop(esc_html(wp_trim_words(wp_strip_all_tags(get_the_content()), 36, '...'))); ?></div>
                                                <?php $likes = array_map('intval', (array) get_post_meta(get_the_ID(), '_aitrongcay_market_likes', true)); $liked = is_user_logged_in() && in_array(get_current_user_id(), $likes, true); ?>
                                                <div class="market-card-actions-row single-line" style="margin-top:12px">
                                                    <div class="market-card-social single-line">
                                                        <a class="market-social-btn market-owner-pill" href="<?php echo esc_url(aitrongcay_market_zalo_action_url((int) get_the_ID())); ?>" aria-label="Nhắn Zalo"><span class="market-social-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none"><rect x="3" y="3" width="18" height="18" rx="6" fill="#0068FF"></rect><path d="M7.5 8.2h9l-6.05 7.6h5.95l-9 0 6.05-7.6H7.5Z" fill="#fff"></path></svg></span><span>Zalo</span></a>
                                                        <a class="market-icon-btn" href="<?php echo esc_url(add_query_arg(array_filter(['market_post' => (string) get_the_ID(), 'garden' => $fallback_market_view_garden_key]), home_url('/cho-que/'))); ?>" aria-label="Xem chi tiết tin"><span class="market-social-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></span></a>
                                                        <?php if (is_user_logged_in()) : ?><button class="market-social-btn<?php echo $liked ? ' active' : ''; ?>" type="button" data-like-market-post="<?php echo esc_attr((string) get_the_ID()); ?>" aria-label="Yêu thích tin đăng"><span class="market-social-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 21s-6.7-4.35-9.33-8.07C.54 9.93 2.2 5.5 6.54 5.5c2.11 0 3.52 1.17 4.3 2.33.78-1.16 2.2-2.33 4.31-2.33 4.33 0 6 4.43 3.87 7.43C18.7 16.65 12 21 12 21Z"/></svg></span><span data-like-count><?php echo esc_html((string) count($likes)); ?></span></button><?php endif; ?>
                                                        <?php if ((int) get_post_field('post_author', get_the_ID()) === get_current_user_id()) : ?><button class="market-social-btn market-owner-pill" type="button" data-edit-market-post="<?php echo esc_attr((string) get_the_ID()); ?>" data-market-title="<?php echo esc_attr(get_the_title()); ?>" data-market-content="<?php echo esc_attr(wp_strip_all_tags(get_the_content())); ?>" data-market-image="<?php echo esc_url($market_image_url ? wp_make_link_relative($market_image_url) : $market_fallback_url); ?>" data-market-gallery="[]" data-market-category="" data-market-offer-type="" data-market-quantity="" data-market-area="" data-market-availability="" data-market-contact=""><span class="market-social-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg></span><span>Sửa tin</span></button><button class="market-social-btn market-owner-pill market-owner-pill-danger" type="button" data-delete-market-post="<?php echo esc_attr((string) get_the_ID()); ?>"><span class="market-social-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg></span><span>Xóa tin</span></button><?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                <?php else : ?>
                                    <div class="notice">Chưa có tin nào. Người bán và người mua có thể bắt đầu đăng tin từ đây.</div>
                                <?php endif; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
