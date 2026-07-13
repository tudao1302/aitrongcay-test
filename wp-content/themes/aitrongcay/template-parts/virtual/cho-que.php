<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$market_post_id = absint($_GET['market_post'] ?? 0);
$market_post = $market_post_id ? get_post($market_post_id) : null;
$created_post = absint($_GET['created_post'] ?? 0);
$market_context_garden_key = aitrongcay_market_context_garden_key();
$market_view_garden_key = '';
if (isset($_GET['garden'])) {
    $requested_market_garden = sanitize_text_field((string) wp_unslash($_GET['garden']));
    if ($requested_market_garden !== '' && is_user_logged_in() && aitrongcay_user_can_view_garden($requested_market_garden, get_current_user_id())) {
        $market_view_garden_key = $requested_market_garden;
    }
}
$market_context_profile = $market_view_garden_key !== '' ? aitrongcay_portal_profile_for_garden_context($market_view_garden_key, wp_get_current_user()) : null;
$market_list_url = $market_view_garden_key !== '' ? add_query_arg('garden', $market_view_garden_key, home_url('/cho-que/')) : home_url('/cho-que/');

if ($market_post && $market_post->post_type === 'aitr_market_post') {
    $gallery = array_map('absint', (array) get_post_meta($market_post->ID, '_aitrongcay_market_gallery', true));
    $market_structured = function_exists('aitrongcay_get_market_structured_data') ? aitrongcay_get_market_structured_data($market_post->ID) : [];
    $market_summary_line = function_exists('aitrongcay_market_summary_line') ? aitrongcay_market_summary_line($market_structured) : '';
    $author_id = (int) $market_post->post_author;
    $author_name = get_the_author_meta('display_name', $author_id);
    $detail_likes = array_map('intval', (array) get_post_meta($market_post->ID, '_aitrongcay_market_likes', true));
    $detail_liked = is_user_logged_in() && in_array(get_current_user_id(), $detail_likes, true);
    $market_comments = get_comments(['post_id' => $market_post->ID, 'status' => 'approve']);
    ?>
    <section class="section-tight market-page-shell">
        <article class="glass-card market-detail-card market-detail-clean">
            <div class="market-detail-head">
                <div>
                    <span class="eyebrow"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_eyebrow')); ?></span>
                    <h1><?php echo esc_html(get_the_title($market_post)); ?></h1>
                    <div class="market-meta-line">
                        <span><?php echo esc_html($author_name); ?></span>
                        <span><?php echo esc_html(get_the_date('d/m/Y H:i', $market_post)); ?></span>
                    </div>
                    <?php if ($market_summary_line !== '') : ?>
                        <div class="market-card-meta" style="margin-top:10px"><span><?php echo esc_html($market_summary_line); ?></span></div>
                    <?php endif; ?>
                </div>
                <div class="inline-list">
                    <a class="btn btn-primary" href="<?php echo esc_url(aitrongcay_market_zalo_action_url((int) $market_post->ID)); ?>" data-open-market-zalo="<?php echo esc_attr((string) $market_post->ID); ?>" style="display:inline-flex;align-items:center;gap:8px"><span>💬</span><span><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_contact_label')); ?></span></a>
                    <a class="btn btn-secondary" href="<?php echo esc_url($market_list_url); ?>"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_back_label')); ?></a>
                    <?php if (is_user_logged_in()) : ?>
                        <button class="btn btn-secondary" type="button" data-like-market-post="<?php echo esc_attr((string) $market_post->ID); ?>"><span data-like-label><?php echo esc_html($detail_liked ? aitrongcay_page_text('cho_que', 'listing_liked_label') : aitrongcay_page_text('cho_que', 'listing_like_label')); ?></span> · <span data-like-count><?php echo esc_html((string) count($detail_likes)); ?></span></button>
                    <?php endif; ?>
                </div>
            </div>
            <?php
            $detail_thumb_url = has_post_thumbnail($market_post) ? (string) get_the_post_thumbnail_url($market_post, 'full') : '';
            $detail_gallery_url = ($gallery && ! empty($gallery[0])) ? (string) wp_get_attachment_image_url($gallery[0], 'large') : '';
            $detail_image_url = $detail_thumb_url ?: $detail_gallery_url;
            $market_fallback_url = get_template_directory_uri() . '/assets/images/market-harvest.svg';
            ?>
            <div class="market-detail-image-wrap" style="margin-top:16px"><img class="market-detail-image" src="<?php echo esc_url($detail_image_url ?: $market_fallback_url); ?>" alt="<?php echo esc_attr(get_the_title($market_post)); ?>" loading="lazy"></div>
            <?php if ($gallery) : ?><div class="cards-4 market-gallery" style="margin-top:18px"><?php foreach ($gallery as $gallery_id) : $gallery_item_url = (string) wp_get_attachment_image_url($gallery_id, 'large'); ?><img class="media-thumb media-fit-cover" src="<?php echo esc_url($gallery_item_url ?: $market_fallback_url); ?>" alt="<?php echo esc_attr(get_the_title($market_post)); ?>" loading="lazy"><?php endforeach; ?></div><?php endif; ?>
            <div class="entry-content market-copy-clean" style="margin-top:20px"><?php echo wpautop(wp_kses_post($market_post->post_content)); ?></div>
            <div class="market-comments-block" style="margin-top:28px"><h3 style="margin-bottom:14px"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_comments_title')); ?></h3><?php if ($market_comments) : ?><div class="comment-list"><?php foreach ($market_comments as $comment_item) : ?><div class="comment"><strong><?php echo esc_html($comment_item->comment_author); ?></strong><div class="small subtle"><?php echo esc_html(get_date_from_gmt($comment_item->comment_date_gmt, 'd/m/Y H:i')); ?></div><p style="margin-top:8px"><?php echo esc_html($comment_item->comment_content); ?></p></div><?php endforeach; ?></div><?php endif; ?><?php if (is_user_logged_in()) : ?><?php comment_form(['title_reply' => aitrongcay_page_text('cho_que', 'detail_comment_form_title'), 'label_submit' => aitrongcay_page_text('cho_que', 'detail_comment_submit_label')], $market_post->ID); ?><?php else : ?><div class="notice"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_comment_login_notice')); ?></div><?php endif; ?></div>
        </article>
    </section>
    <?php
    return;
}

$market_posts = new WP_Query(aitrongcay_market_posts_query_args($market_view_garden_key, 24));
$active_market_search = sanitize_text_field((string) ($_GET['market_search'] ?? ''));
$eco_nav_items = array_map(static function (array $item) use ($market_view_garden_key) {
    $url = (string) ($item['url'] ?? '#');
    if (in_array((string) ($item['key'] ?? ''), ['dashboard', 'kho-nong-cu'], true) && $market_view_garden_key !== '') {
        $url = add_query_arg('garden', $market_view_garden_key, $url);
    }
    $item['url'] = $url;
    return $item;
}, aitrongcay_eco_nav_items());
set_query_var('aitr_eco_shell', [
    'title' => 'Eco-Tech Marketplace',
    'active' => 'cho-que',
    'side_title' => 'Master Gardener',
    'side_subtitle' => 'Eco-Level ' . (function_exists('aitrongcay_calculate_level') ? aitrongcay_calculate_level((int) get_user_meta(get_current_user_id(), '_aitrongcay_eco_points', true)) : 1),
    'side_badge' => '🌿',
    'top_icons' => ['🔔', '🛒'],
    'search' => null,
    'nav' => $eco_nav_items,
]);
get_template_part('template-parts/site/eco-shell-start-v2');
set_query_var('aitr_eco_hero', ['title' => 'Community Hub', 'body' => 'Connect, share, and trade with fellow Eco-Architects.']);
get_template_part('template-parts/site/eco-hero');
?>
<style>
.eco-market-compose{position:relative;margin-bottom:30px;padding:30px;border-radius:34px;background:rgba(51,53,50,.42);backdrop-filter:blur(20px);box-shadow:0 24px 52px rgba(0,0,0,.22);overflow:hidden;border:1px solid rgba(255,255,255,.05)}
.eco-market-compose::after{content:'';position:absolute;top:-80px;right:-80px;width:220px;height:220px;border-radius:999px;background:rgba(111,219,168,.08);filter:blur(36px)}
.eco-market-compose-row{position:relative;z-index:1;display:flex;gap:18px;align-items:flex-start}.eco-market-compose-avatar{width:56px;height:56px;border-radius:18px;background:#1a1c19;display:grid;place-items:center;border:2px solid rgba(111,219,168,.22)}
.eco-market-compose textarea{width:100%;min-height:90px;border:none;border-radius:20px;padding:18px;background:rgba(41,43,39,.62);color:#e3e3de;resize:vertical;outline:none}.eco-market-compose-actions{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-top:16px}.eco-market-compose-tools{display:flex;gap:18px;flex-wrap:wrap}.eco-market-compose-tools button{display:inline-flex;align-items:center;gap:8px;background:none;border:none;color:rgba(227,227,222,.62)}
.eco-market-compose-submit{display:inline-flex;align-items:center;gap:10px;padding:14px 24px;border-radius:999px;background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;font-weight:900;border:none}
.eco-market-filters{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:24px}.eco-market-filterbox{display:flex;align-items:center;gap:10px;background:rgba(26,28,25,.88);border-radius:18px;padding:12px 16px;min-width:280px;border:1px solid rgba(111,219,168,.12)}.eco-market-chips{display:flex;gap:8px;flex-wrap:wrap}.eco-market-chip{padding:10px 16px;border-radius:999px;background:rgba(51,53,50,.54);font-size:12px;font-weight:800;color:rgba(227,227,222,.64);text-decoration:none;cursor:pointer}.eco-market-chip.active{background:rgba(111,219,168,.12);color:var(--primary);border:1px solid rgba(111,219,168,.24)}.eco-market-chip:hover:not(.active){background:rgba(51,53,50,.8);color:rgba(227,227,222,.88)}
.eco-market-feed{display:grid;gap:24px}.eco-market-post{background:rgba(26,28,25,.94);border-radius:34px;overflow:hidden;box-shadow:0 22px 48px rgba(0,0,0,.22);transition:.35s}.eco-market-post:hover{transform:translateY(-4px)}
.eco-market-post-head{padding:24px 24px 18px;display:flex;justify-content:space-between;align-items:center;gap:16px}.eco-market-author{display:flex;align-items:center;gap:14px}.eco-market-author-thumb{width:50px;height:50px;border-radius:999px;background:#1a1c19;border:2px solid rgba(255,225,109,.3);display:grid;place-items:center}.eco-market-tier{display:inline-flex;padding:6px 10px;border-radius:10px;background:#ffe16d;color:#221b00;font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
.eco-market-post-body{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,.9fr);gap:0}.eco-market-post-copy{padding:0 24px 24px}.eco-market-post-copy h3{margin:0 0 10px;font-family:'Noto Serif',serif;font-size:34px;line-height:1.08;color:#fff}.eco-market-post-copy p{color:rgba(227,227,222,.76);line-height:1.7}.eco-market-meta{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0 16px}.eco-market-meta span{padding:8px 12px;border-radius:999px;background:rgba(51,53,50,.5);font-size:12px;color:#ffb68c}.eco-market-media{background:rgba(18,20,17,.92);aspect-ratio:16/9;align-self:start;overflow:hidden}.eco-market-media img{width:100%;height:100%;object-fit:cover}.market-detail-image-wrap{border-radius:24px;overflow:hidden;background:#0b120e}.market-detail-image{display:block;width:100%;height:auto;max-height:none;object-fit:contain}.market-card-excerpt{transition:opacity .22s ease,max-height .28s ease,margin .22s ease;max-height:10em;overflow:hidden}.eco-market-inline-detail{padding:0 24px 24px;display:grid;grid-template-rows:0fr;transition:grid-template-rows .32s ease,padding-top .28s ease}.eco-market-inline-detail[hidden]{display:grid !important}.eco-market-inline-detail-inner{min-height:0;overflow:hidden;padding-top:8px;border-top:1px solid rgba(255,255,255,.06)}.eco-market-post.is-expanded .eco-market-inline-detail{grid-template-rows:1fr}.eco-market-post.is-expanded .eco-market-post-body{grid-template-columns:1fr}.eco-market-post.is-expanded .eco-market-media{display:none}.eco-market-post.is-expanded .market-card-excerpt{opacity:0;max-height:0;margin:0}.market-copy-clean,.market-copy-clean p{color:#e8efe9;opacity:1}.market-gallery-slider{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:12px;align-items:center}.market-gallery-viewport{position:relative;overflow:hidden;touch-action:pan-y;cursor:grab}.market-gallery-viewport.is-dragging{cursor:grabbing}.market-gallery-track{display:flex;transition:transform .34s ease,opacity .22s ease;opacity:1}.market-gallery-track.is-fading{opacity:.7}.market-gallery-slide{min-width:100%}.market-gallery-slide-image{display:block;width:100%;height:auto;max-height:620px;object-fit:contain;border-radius:18px;background:#0b120e}.market-gallery-counter{position:absolute;right:14px;bottom:14px;padding:6px 10px;border-radius:999px;background:rgba(11,18,14,.72);color:#f5f7f3;font-size:12px;font-weight:700;backdrop-filter:blur(8px)}.market-gallery-nav{width:42px;height:42px;border:none;border-radius:999px;background:rgba(51,53,50,.78);color:#fff;font-size:28px;line-height:1;display:grid;place-items:center}.market-gallery-nav[disabled]{opacity:.35;cursor:not-allowed}.market-gallery-dots{grid-column:1/-1;display:flex;justify-content:center;gap:8px;margin-top:10px}.market-gallery-dot{width:10px;height:10px;border-radius:999px;border:none;background:rgba(255,255,255,.24)}.market-gallery-dot.is-active{background:#6fdba8;transform:scale(1.15)}.market-inline-editor{margin-top:22px;padding:18px;border:1px solid rgba(255,255,255,.07);border-radius:22px;background:rgba(255,255,255,.03)}.market-inline-editor input,.market-inline-editor textarea,.market-inline-editor select{width:100%;background:#292b27;border:1px solid rgba(255,255,255,.05);border-radius:14px;padding:14px 16px;color:#e3e3de;outline:none}.market-inline-editor textarea{min-height:150px;resize:vertical}.eco-market-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:18px;flex-wrap:wrap}.eco-market-actions-left,.eco-market-actions-right{display:flex;gap:10px;flex-wrap:wrap}.eco-market-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 14px;border-radius:999px;background:rgba(51,53,50,.5);color:#e3e3de;border:1px solid rgba(111,219,168,.08);transition:all .2s ease;cursor:pointer}.eco-market-btn:hover{background:rgba(71,73,70,.7);color:#fff}.eco-market-btn.primary{background:linear-gradient(135deg,#31a375,#6fdba8);color:#062013;border:none}.eco-market-empty{padding:32px;border-radius:28px;background:rgba(41,43,39,.42);color:rgba(227,227,222,.7)}

.market-comments-block .comment { color: #111; }
.market-comments-block .comment p, .market-comments-block .comment .small.subtle, .market-comments-block .comment strong { color: #222 !important; }
.market-comments-block h3, .market-comments-block .logged-in-as, .market-comments-block .comment-form label, .market-comments-block .comment-notes { color: rgba(255,255,255,0.8); }
.market-comments-block a { color: #3b82f6; text-decoration: none; }
.market-comments-block a:hover { text-decoration: underline; }
@media (max-width:1100px){.eco-market-post-body{grid-template-columns:1fr}.eco-market-media{aspect-ratio:16/9}}
@media (max-width:820px){.eco-market-compose{padding:18px}.eco-market-compose-row{display:grid;grid-template-columns:1fr}.eco-market-compose-avatar{display:none}.eco-market-filters{display:grid}.eco-market-filterbox{min-width:0;width:100%}}
</style>
<?php if ($created_post) : ?><div class="notice success" style="margin:0 0 18px"><strong><?php echo esc_html(aitrongcay_page_text('cho_que', 'listing_created_notice')); ?></strong></div><?php endif; ?>
<section class="eco-market-compose">
  <div class="eco-market-compose-row">
    <?php
    $avatar_html = '👤';
    if (is_user_logged_in()) {
        $current_user_header = wp_get_current_user();
        $avatar_id = (int) get_user_meta($current_user_header->ID, 'aitrongcay_avatar_id', true);
        $avatar_url = $avatar_id ? (wp_get_attachment_image_url($avatar_id, 'thumbnail') ?: wp_get_attachment_url($avatar_id)) : '';
        if ($avatar_url) {
            $avatar_html = '<img src="' . esc_url($avatar_url) . '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;">';
        } else {
            $avatar_html = esc_html(mb_strtoupper(mb_substr($current_user_header->display_name ?: $current_user_header->user_login, 0, 1)));
        }
    }
    ?>
    <div class="eco-market-compose-avatar" style="overflow:hidden;padding:0;box-sizing:border-box;color:#fff;font-weight:bold"><?php echo $avatar_html; ?></div>
    <div style="flex:1">
      <?php if (is_user_logged_in()) : ?>
        <textarea placeholder="<?php echo esc_attr(aitrongcay_page_text('cho_que', 'listing_primary_cta')); ?>" readonly onclick="var btn=document.querySelector('[data-open-market-compose]'); if(btn) btn.click();"></textarea>
        <div class="eco-market-compose-actions">
          <div class="eco-market-compose-tools">
            <button type="button" data-open-market-compose><span>🖼</span><span>Photo</span></button>
            <button type="button" data-open-market-compose><span>📍</span><span>Location</span></button>
          </div>
          <button class="eco-market-compose-submit" type="button" data-open-market-compose><span>🌱</span><span>Share Harvest</span></button>
        </div>
      <?php else : ?>
        <div style="padding:10px 0;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
          <span style="color:rgba(227,227,222,.62)">Sign in to post on the Rural Market.</span>
          <a class="btn btn-primary" href="<?php echo esc_url(home_url('/dang-nhap/')); ?>" style="flex-shrink:0">Sign In</a>
          <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/onboarding/')); ?>" style="flex-shrink:0">Create Account</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<div class="eco-market-filters">
  <form class="eco-market-filterbox" method="get" action="<?php echo esc_url($market_list_url); ?>">
    <?php if ($market_view_garden_key !== '') : ?><input type="hidden" name="garden" value="<?php echo esc_attr($market_view_garden_key); ?>"><?php endif; ?>
    <span>🎛️</span><input type="search" name="market_search" value="<?php echo esc_attr($active_market_search); ?>" placeholder="Filter by plant type..." style="background:transparent;border:none;outline:none;width:100%;color:#e3e3de">
  </form>
  <?php
  $current_sort      = sanitize_key((string) ($_GET['market_sort'] ?? ''));
  $current_offer     = sanitize_text_field((string) ($_GET['market_offer_type'] ?? ''));
  $chip_base_url     = remove_query_arg(['market_sort', 'market_offer_type'], $market_list_url);
  $chip_is_newest    = ($current_sort === '' || $current_sort === 'newest') && $current_offer === '';
  $chip_is_popular   = $current_sort === 'popular' && $current_offer === '';
  $chip_is_sale_only = $current_offer === 'Bán';
  $chip_is_gift      = $current_offer === 'Tặng hàng xóm';
  ?>
  <div class="eco-market-chips">
    <a href="<?php echo esc_url(add_query_arg('market_sort', 'newest', $chip_base_url)); ?>" class="eco-market-chip<?php echo $chip_is_newest ? ' active' : ''; ?>">Mới nhất</a>
    <a href="<?php echo esc_url(add_query_arg('market_sort', 'popular', $chip_base_url)); ?>" class="eco-market-chip<?php echo $chip_is_popular ? ' active' : ''; ?>">Nổi bật</a>
    <a href="<?php echo esc_url(add_query_arg(['market_offer_type' => 'Bán'], $chip_base_url)); ?>" class="eco-market-chip<?php echo $chip_is_sale_only ? ' active' : ''; ?>">Chỉ mua bán</a>
    <a href="<?php echo esc_url(add_query_arg(['market_offer_type' => 'Tặng hàng xóm'], $chip_base_url)); ?>" class="eco-market-chip<?php echo $chip_is_gift ? ' active' : ''; ?>">🎁 Quà tặng hàng xóm</a>
  </div>
</div>
<div class="market-compose-modal" data-market-edit-modal hidden>
  <div class="market-compose-backdrop" data-close-market-edit></div>
  <div class="market-compose-dialog market-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="market-edit-title">
    <button class="market-compose-close" type="button" aria-label="Đóng" data-close-market-edit>×</button>
    <div class="market-compose-head market-compose-head-ai"><div><span class="eyebrow">Sửa tin đăng</span><h3 id="market-edit-title">Chỉnh sửa tin Chợ quê</h3></div></div>
    <form class="market-compose-form" data-market-edit-form>
      <input type="hidden" data-market-edit-post-id>
      <div class="market-compose-surface">
        <div class="market-compose-title-wrap"><input id="market-edit-title-input" type="text" maxlength="140" placeholder="Tiêu đề" aria-label="Tiêu đề" data-market-edit-title><div class="small subtle" data-market-title-hint></div></div>
        <div class="market-structured-grid"><div><select id="market-edit-category" aria-label="Danh mục" data-market-edit-category><option value="">Danh mục</option><option>Hạt giống</option><option>Cây giống</option><option>Dinh dưỡng cho cây</option><option>Các loại rau</option><option>Hoa</option></select></div><div><select id="market-edit-offer-type" aria-label="Hình thức" data-market-edit-offer-type><option value="">Hình thức</option><option>Bán</option><option>Trao đổi</option><option>Tặng hàng xóm</option><option>Nhận đặt trước</option></select></div><div><input id="market-edit-quantity" type="text" placeholder="Số lượng" aria-label="Số lượng" data-market-edit-quantity></div><div><input id="market-edit-area" type="text" placeholder="Khu vực" aria-label="Khu vực" data-market-edit-area></div><div><input id="market-edit-availability" type="text" placeholder="Thời gian nhận/giao" aria-label="Thời gian nhận hoặc giao" data-market-edit-availability></div><div><input id="market-edit-contact" type="text" placeholder="Liên hệ" aria-label="Liên hệ" data-market-edit-contact></div></div>
        <div class="market-compose-body-wrap"><textarea id="market-edit-content-input" placeholder="Nội dung" aria-label="Nội dung" data-market-edit-content></textarea><div class="small subtle" data-market-content-hint></div></div>
        <div class="market-compose-toolbar"><label class="market-compose-upload" for="market-edit-photo-input"><span>＋</span><strong>Thay ảnh</strong></label><input id="market-edit-photo-input" type="file" accept="image/*" multiple data-market-edit-files></div>
      </div>
      <div class="market-edit-current-media" data-market-edit-current-media></div><div class="market-compose-preview" data-market-edit-preview></div><div class="market-edit-card-preview" data-market-edit-card-preview></div>
      <div class="market-compose-foot"><div class="notice" data-market-edit-notice style="display:none;margin:0"></div><div class="inline-list market-compose-actions" style="justify-content:flex-end"><button class="btn btn-ghost" type="button" data-close-market-edit>Đóng</button><button class="btn btn-primary" type="submit" data-market-edit-submit>Lưu thay đổi</button></div></div>
    </form>
  </div>
</div>
<div class="eco-market-feed">
<?php if ($market_posts->have_posts()) : while ($market_posts->have_posts()) : $market_posts->the_post();
    $post_garden_key = aitrongcay_migrate_market_post_garden_key(get_the_ID());
    $post_garden_profile = $post_garden_key !== '' ? aitrongcay_portal_profile_for_garden_context($post_garden_key, wp_get_current_user()) : null;
    $gallery = array_map('absint', (array) get_post_meta(get_the_ID(), '_aitrongcay_market_gallery', true));
    $market_structured = function_exists('aitrongcay_get_market_structured_data') ? aitrongcay_get_market_structured_data(get_the_ID()) : [];
    $market_summary_line = function_exists('aitrongcay_market_summary_line') ? aitrongcay_market_summary_line($market_structured) : '';
    $author_id = (int) get_post_field('post_author', get_the_ID());
    $author_name = get_the_author_meta('display_name', $author_id);
    $likes = array_map('intval', (array) get_post_meta(get_the_ID(), '_aitrongcay_market_likes', true));
    $liked = is_user_logged_in() && in_array(get_current_user_id(), $likes, true);
    $share_count = (int) get_post_meta(get_the_ID(), '_aitrongcay_market_share_count', true);
    $thumb_url = has_post_thumbnail() ? (string) get_the_post_thumbnail_url(get_the_ID(), 'large') : '';
    $gallery_url = ($gallery && ! empty($gallery[0])) ? (string) wp_get_attachment_image_url($gallery[0], 'large') : '';
    $market_image_url = $thumb_url ?: $gallery_url;
    $market_fallback_url = get_template_directory_uri() . '/assets/images/market-harvest.svg';
    $market_gallery_payload = [];
    foreach ($gallery as $attachment_id) { $attachment_url = (string) (wp_get_attachment_image_url($attachment_id, 'large') ?: wp_get_attachment_url($attachment_id)); if (! $attachment_url) continue; $market_gallery_payload[] = ['id' => $attachment_id, 'url' => $attachment_url, 'title' => get_the_title($attachment_id)]; }
    $inline_comments = get_comments(['post_id' => get_the_ID(), 'status' => 'approve']);
    ob_start();
    ?>
    <div class="market-comments-block" style="margin-top:28px"><h3 style="margin-bottom:14px"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_comments_title')); ?></h3><?php if ($inline_comments) : ?><div class="comment-list"><?php foreach ($inline_comments as $comment_item) : ?><div class="comment"><strong><?php echo esc_html($comment_item->comment_author); ?></strong><div class="small subtle"><?php echo esc_html(gmdate('d/m/Y H:i', strtotime($comment_item->comment_date_gmt) + 7 * 3600)); ?></div><p style="margin-top:8px"><?php echo esc_html($comment_item->comment_content); ?></p></div><?php endforeach; ?></div><?php endif; ?><?php if (is_user_logged_in()) : ?><?php comment_form(['title_reply' => aitrongcay_page_text('cho_que', 'detail_comment_form_title'), 'label_submit' => aitrongcay_page_text('cho_que', 'detail_comment_submit_label')], get_the_ID()); ?><?php else : ?><div class="notice"><?php echo esc_html(aitrongcay_page_text('cho_que', 'detail_comment_login_notice')); ?></div><?php endif; ?></div>
    <?php
    $inline_comments_html = (string) ob_get_clean();
    set_query_var('aitr_market_card', [
        'id' => (string) get_the_ID(),
        'created' => $created_post === get_the_ID(),
        'author_name' => $post_garden_key !== '' ? (string) ($post_garden_profile['garden_name'] ?? $post_garden_key) : $author_name,
        'date' => get_the_date('d/m/Y', get_the_ID()),
        'tier' => (string) ($market_structured['offer_type'] ?: 'RARE HARVEST'),
        'title' => get_the_title(),
        'meta' => array_values(array_filter([$market_summary_line])),
        'excerpt' => wp_trim_words(wp_strip_all_tags(get_the_content()), 40, '...'),
        'zalo_url' => aitrongcay_market_zalo_action_url((int) get_the_ID()),
        'detail_url' => add_query_arg(array_filter(['market_post' => (string) get_the_ID(), 'garden' => $market_view_garden_key]), home_url('/cho-que/')),
        'show_like' => is_user_logged_in(),
        'liked' => $liked,
        'like_count' => count($likes),
        'share_count' => $share_count,
        'show_edit' => ((int) get_post_field('post_author', get_the_ID()) === get_current_user_id()),
        'content' => wp_strip_all_tags(get_the_content()),
        'full_content' => (string) get_the_content(null, false, get_the_ID()),
        'image' => $market_image_url ?: $market_fallback_url,
        'gallery_json' => wp_json_encode($market_gallery_payload),
        'gallery' => $market_gallery_payload,
        'category' => (string) ($market_structured['category'] ?? ''),
        'offer_type' => (string) ($market_structured['offer_type'] ?? ''),
        'quantity' => (string) ($market_structured['quantity'] ?? ''),
        'area' => (string) ($market_structured['area'] ?? ''),
        'availability' => (string) ($market_structured['availability'] ?? ''),
        'contact' => (string) ($market_structured['contact_text'] ?? ''),
        'edit_label' => aitrongcay_page_text('cho_que', 'listing_edit_label'),
        'comments_html' => $inline_comments_html,
    ]);
    get_template_part('template-parts/site/eco-market-card');
endwhile; wp_reset_postdata(); else : ?>
  <div class="eco-market-empty"><?php echo esc_html($market_view_garden_key !== '' ? 'Khu vườn này chưa có tin Chợ quê nào. Anh/chị có thể đăng từ portal của đúng vườn để giữ context đồng nhất.' : aitrongcay_page_text('cho_que', 'empty_notice')); ?></div>
<?php endif; ?>
</div>
<script>
(function(){
  var toggles = document.querySelectorAll('[data-toggle-market-detail]');
  if (!toggles.length) return;
  function collapse(card){
    if (!card) return;
    card.classList.remove('is-expanded');
    var detail = card.querySelector('[data-market-inline-detail]');
    var btn = card.querySelector('[data-toggle-market-detail]');
    var label = card.querySelector('[data-detail-toggle-label]');
    if (detail) {
      window.setTimeout(function(){ if (!card.classList.contains('is-expanded')) detail.hidden = true; }, 280);
    }
    if (btn) btn.setAttribute('aria-expanded','false');
    if (label) label.textContent = 'Xem chi tiết';
  }
  function expand(card){
    document.querySelectorAll('[data-market-card].is-expanded').forEach(function(other){ if (other !== card) collapse(other); });
    card.classList.add('is-expanded');
    var detail = card.querySelector('[data-market-inline-detail]');
    var btn = card.querySelector('[data-toggle-market-detail]');
    var label = card.querySelector('[data-detail-toggle-label]');
    if (detail) detail.hidden = false;
    if (btn) btn.setAttribute('aria-expanded','true');
    if (label) label.textContent = 'Thu gọn';
    window.setTimeout(function(){
      var image = card.querySelector('.market-detail-image-wrap') || card;
      var rect = image.getBoundingClientRect();
      var target = window.scrollY + rect.top - 110;
      window.scrollTo({ top: Math.max(target, 0), behavior: 'smooth' });
    }, 80);
  }
  toggles.forEach(function(btn){
    btn.addEventListener('click', function(){
      var card = btn.closest('[data-market-card]');
      if (!card) return;
      if (card.classList.contains('is-expanded')) collapse(card); else expand(card);
    });
  });
  if (window.location.hash && window.location.hash.indexOf('#comment-') === 0) {
    var commentEl = document.querySelector(window.location.hash);
    if (commentEl) {
      var card = commentEl.closest('[data-market-card]');
      if (card) {
        expand(card);
        window.setTimeout(function() { commentEl.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 350);
      }
    }
  }
  document.querySelectorAll('[data-toggle-market-editor]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var card = btn.closest('[data-market-card]');
      if (!card) return;
      var editor = card.querySelector('[data-market-inline-editor]');
      if (!editor) return;
      
      if (editor.hidden) {
          if (!card.classList.contains('is-expanded')) expand(card);
          editor.hidden = false;
      } else {
          editor.hidden = true;
          collapse(card);
      }
    });
  });
  document.querySelectorAll('[data-market-inline-editor]').forEach(function(editor){
    var card = editor.closest('[data-market-card]');
    var postId = card ? (card.querySelector('[data-toggle-market-detail]')?.getAttribute('data-toggle-market-detail') || '') : '';
    var title = editor.querySelector('[data-inline-market-title]');
    var content = editor.querySelector('[data-inline-market-content]');
    var category = editor.querySelector('[data-inline-market-category]');
    var offerType = editor.querySelector('[data-inline-market-offer-type]');
    var quantity = editor.querySelector('[data-inline-market-quantity]');
    var area = editor.querySelector('[data-inline-market-area]');
    var availability = editor.querySelector('[data-inline-market-availability]');
    var contact = editor.querySelector('[data-inline-market-contact]');
    var filesInput = editor.querySelector('[data-inline-market-files]');
    var saveStatus = editor.querySelector('[data-inline-market-save-status]');
    var saveBtn = editor.querySelector('[data-inline-market-save-btn]');
    var existingPhotosInput = editor.querySelector('[data-inline-market-existing-photos]');
    var preview = editor.querySelector('[data-inline-market-preview]');
    var timer = null;
    function renderPreview(){
      var files = Array.from((filesInput && filesInput.files) || []);
      if (!preview) return;
      if (!files.length) { preview.innerHTML = ''; return; }
      preview.innerHTML = files.map(function(file){ var url = URL.createObjectURL(file); return '<div class="market-compose-thumb"><img src="'+url+'" alt="'+file.name+'"><span>'+file.name+'</span></div>'; }).join('');
    }
    async function saveNow(){
      if (!postId) return;
      var structured = { category: (category?.value || '').trim(), offer_type: (offerType?.value || '').trim(), quantity: (quantity?.value || '').trim(), area: (area?.value || '').trim(), availability: (availability?.value || '').trim(), contact_text: (contact?.value || '').trim() };
      var titleValue = (title?.value || '').trim();
      var contentValue = (content?.value || '').trim();
      var validationError = validateMarketPostInput({ title: titleValue, content: contentValue, structured: structured });
      if (validationError) { if (saveStatus) saveStatus.textContent = validationError; return; }
      if (saveStatus) saveStatus.textContent = 'Đang lưu...';
      if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Đang lưu...'; }
      try {
        var photoIds = filesInput && filesInput.files && filesInput.files.length ? await uploadMarketFiles(Array.from(filesInput.files)) : [];
        var existingPhotoIds = [];
        try { if (existingPhotosInput && existingPhotosInput.value) existingPhotoIds = JSON.parse(existingPhotosInput.value); } catch(e){}
        
        var body = new URLSearchParams({ action: 'aitrongcay_update_market_post', nonce: ajaxNonce, post_id: postId, title: titleValue, content: contentValue, category: structured.category, offer_type: structured.offer_type, quantity: structured.quantity, area: structured.area, availability: structured.availability, contact_text: structured.contact_text });
        photoIds.forEach(function(id){ body.append('photo_ids[]', String(id)); });
        existingPhotoIds.forEach(function(id){ body.append('existing_photo_ids[]', String(id)); });
        
        var response = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body: body });
        var result = await response.json();
        if (!result.success) throw new Error((result.data && result.data.message) || 'Không lưu được.');
        
        var titleEl = card && card.querySelector('[data-market-title-render]');
        var excerptEl = card && card.querySelector('[data-market-content-render]');
        var imageEl = card && card.querySelector('.eco-market-media img');
        if (titleEl) titleEl.textContent = titleValue;
        if (excerptEl) excerptEl.textContent = trimMarketPreviewText(contentValue, 180);
        if (imageEl && result.data && result.data.imageUrl) imageEl.src = result.data.imageUrl;
        var detailText = card && card.querySelector('.market-copy-clean');
        if (detailText) detailText.innerHTML = '<p>' + contentValue.replace(/\n{2,}/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
        
        // Update existing photos so next save includes new photos properly
        if (result.data && result.data.gallery) {
            var newGalleryIds = result.data.gallery.map(function(item){ return item.id; });
            if (existingPhotosInput) existingPhotosInput.value = JSON.stringify(newGalleryIds);
        }
        
        if (filesInput) filesInput.value = '';
        renderPreview();
        if (saveStatus) saveStatus.textContent = 'Đã lưu thành công';
        if (card && typeof collapse === 'function') collapse(card);
      } catch (error) {
        if (saveStatus) saveStatus.textContent = error.message || 'Lỗi khi lưu';
      } finally {
        if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = 'Lưu lại'; }
      }
    }
    function queueSave(){ if (timer) clearTimeout(timer); if (saveStatus) saveStatus.textContent = 'Đang chờ tự lưu...'; timer = window.setTimeout(saveNow, 900); }
    [title, content, category, offerType, quantity, area, availability, contact].forEach(function(field){ if (field) field.addEventListener('input', queueSave); if (field && field.tagName === 'SELECT') field.addEventListener('change', queueSave); });
    if (filesInput) filesInput.addEventListener('change', function(){ renderPreview(); queueSave(); });
    if (saveBtn) saveBtn.addEventListener('click', function(e){ e.preventDefault(); if (timer) clearTimeout(timer); saveNow(); });
  });
  document.querySelectorAll('[data-market-gallery-slider]').forEach(function(slider){
    var track = slider.querySelector('.market-gallery-track');
    var slides = slider.querySelectorAll('.market-gallery-slide');
    var prev = slider.querySelector('[data-market-gallery-prev]');
    var next = slider.querySelector('[data-market-gallery-next]');
    var viewport = slider.querySelector('[data-market-gallery-viewport]');
    var dots = slider.querySelectorAll('[data-market-gallery-dot]');
    var counter = slider.querySelector('[data-market-gallery-counter]');
    if (!track || !slides.length) return;
    var index = 0;
    var touchStartX = 0;
    var touchDeltaX = 0;
    var mouseDown = false;
    function render(withFade){
      if (withFade) {
        track.classList.add('is-fading');
        window.setTimeout(function(){ track.classList.remove('is-fading'); }, 180);
      }
      track.style.transform = 'translateX(' + (-index * 100) + '%)';
      if (prev) prev.disabled = index <= 0;
      if (next) next.disabled = index >= slides.length - 1;
      dots.forEach(function(dot, dotIndex){ dot.classList.toggle('is-active', dotIndex === index); });
      if (counter) counter.textContent = (index + 1) + '/' + slides.length;
    }
    function moveBySwipe(deltaX){
      if (Math.abs(deltaX) < 40) return;
      if (deltaX < 0 && index < slides.length - 1) { index += 1; render(true); }
      if (deltaX > 0 && index > 0) { index -= 1; render(true); }
    }
    if (prev) prev.addEventListener('click', function(){ if (index > 0) { index -= 1; render(true); } });
    if (next) next.addEventListener('click', function(){ if (index < slides.length - 1) { index += 1; render(true); } });
    dots.forEach(function(dot){
      dot.addEventListener('click', function(){
        var nextIndex = Number(dot.getAttribute('data-market-gallery-dot') || '0');
        if (!Number.isNaN(nextIndex) && nextIndex !== index) { index = nextIndex; render(true); }
      });
    });
    if (viewport) {
      viewport.addEventListener('touchstart', function(e){ touchStartX = e.changedTouches[0].clientX; touchDeltaX = 0; }, {passive:true});
      viewport.addEventListener('touchmove', function(e){ touchDeltaX = e.changedTouches[0].clientX - touchStartX; }, {passive:true});
      viewport.addEventListener('touchend', function(){ moveBySwipe(touchDeltaX); }, {passive:true});
      viewport.addEventListener('mousedown', function(e){ mouseDown = true; touchStartX = e.clientX; touchDeltaX = 0; viewport.classList.add('is-dragging'); });
      viewport.addEventListener('mousemove', function(e){ if (!mouseDown) return; touchDeltaX = e.clientX - touchStartX; });
      viewport.addEventListener('mouseup', function(){ if (!mouseDown) return; mouseDown = false; viewport.classList.remove('is-dragging'); moveBySwipe(touchDeltaX); });
      viewport.addEventListener('mouseleave', function(){ if (!mouseDown) return; mouseDown = false; viewport.classList.remove('is-dragging'); moveBySwipe(touchDeltaX); });
    }
    render(false);
  });
})();
</script>
<?php if (is_user_logged_in()) : ?>
<div class="market-compose-modal" data-market-compose-modal hidden>
  <div class="market-compose-backdrop" data-close-market-compose></div>
  <div class="market-compose-dialog" role="dialog" aria-modal="true" aria-labelledby="market-compose-title-id">
    <button class="market-compose-close" type="button" aria-label="Đóng" data-close-market-compose>×</button>
    <div class="market-compose-head">
      <div><span class="eyebrow">Rural Market</span><h3 id="market-compose-title-id">New Listing</h3></div>
    </div>
    <form class="market-compose-form" data-market-compose-form>
      <div class="market-compose-surface">
        <div class="market-compose-title-wrap">
          <input type="text" maxlength="140" placeholder="Tiêu đề tin đăng" aria-label="Title" data-market-compose-title>
        </div>
        <div class="market-structured-grid">
          <div>
            <select aria-label="Category" data-market-compose-category>
              <option value="">Danh mục</option>
              <option>Hạt giống</option><option>Cây giống</option>
              <option>Dinh dưỡng cho cây</option><option>Các loại rau</option><option>Hoa</option>
            </select>
          </div>
          <div>
            <select aria-label="Offer type" data-market-compose-offer-type>
              <option value="">Hình thức</option>
              <option>Bán</option><option>Trao đổi</option><option>Tặng hàng xóm</option><option>Nhận đặt trước</option>
            </select>
          </div>
          <div><input type="text" placeholder="Số lượng" aria-label="Quantity" data-market-compose-quantity></div>
          <div><input type="text" placeholder="Khu vực" aria-label="Location" data-market-compose-area></div>
          <div><input type="text" placeholder="Thời gian nhận/giao" aria-label="Availability" data-market-compose-availability></div>
          <div><input type="text" placeholder="Liên hệ (SĐT / Zalo)" aria-label="Contact" data-market-compose-contact></div>
        </div>
        <div class="market-compose-body-wrap">
          <textarea placeholder="Mô tả chi tiết: tình trạng cây, điều kiện giao dịch..." aria-label="Description" data-market-compose-content></textarea>
        </div>
        <div class="market-compose-toolbar">
          <label class="market-compose-upload" for="market-compose-photo-input"><span>＋</span><strong>Add Photo</strong></label>
          <input id="market-compose-photo-input" type="file" accept="image/*" multiple data-market-compose-files hidden>
        </div>
      </div>
      <div class="market-compose-preview" data-market-compose-preview></div>
      <div class="market-compose-foot">
        <div class="notice" data-market-compose-notice style="display:none;margin:0 0 12px"></div>
        <div class="inline-list market-compose-actions" style="justify-content:flex-end">
          <button class="btn btn-ghost" type="button" data-close-market-compose>Close</button>
          <button class="btn btn-primary" type="submit" data-market-compose-submit>Post Now</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
<?php get_template_part('template-parts/site/eco-shell-end'); ?>
