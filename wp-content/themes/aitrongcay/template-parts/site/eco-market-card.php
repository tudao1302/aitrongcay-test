<?php
if (! defined('ABSPATH')) { exit; }
$card = get_query_var('aitr_market_card', []);
if (! is_array($card) || empty($card['title'])) { return; }
?>
<article class="eco-market-post<?php echo ! empty($card['created']) ? ' created-market-post' : ''; ?>" data-market-card>
  <div class="eco-market-post-head">
    <div class="eco-market-author">
      <div class="eco-market-author-thumb">🌿</div>
      <div>
        <h4 style="margin:0;font-weight:800;color:#fff"><?php echo esc_html((string) ($card['author_name'] ?? '')); ?></h4>
        <div style="display:flex;align-items:center;gap:8px;margin-top:4px">
          <span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--primary);font-weight:900">Elite Tier</span>
          <span style="width:4px;height:4px;border-radius:999px;background:#3e4942"></span>
          <span style="font-size:10px;color:#87948b;font-weight:600"><?php echo esc_html((string) ($card['date'] ?? '')); ?></span>
        </div>
      </div>
    </div>
    <div class="eco-market-tier"><?php echo esc_html((string) ($card['tier'] ?? 'RARE HARVEST')); ?></div>
  </div>
  <div class="eco-market-post-body">
    <div class="eco-market-post-copy">
      <h3 data-market-title-render><?php echo esc_html((string) $card['title']); ?></h3>
      <div class="eco-market-meta">
        <?php foreach ((array) ($card['meta'] ?? []) as $meta) : ?>
          <span><?php echo esc_html((string) $meta); ?></span>
        <?php endforeach; ?>
      </div>
      <p data-market-content-render class="market-card-excerpt"><?php echo esc_html((string) ($card['excerpt'] ?? '')); ?></p>
      <div class="eco-market-actions">
        <div class="eco-market-actions-left">
          <a class="eco-market-btn primary" href="<?php echo esc_url((string) ($card['zalo_url'] ?? '#')); ?>">💬 Zalo</a>
          <button class="eco-market-btn" type="button" data-toggle-market-detail="<?php echo esc_attr((string) ($card['id'] ?? '')); ?>" aria-expanded="false">👁 <span data-detail-toggle-label>Xem chi tiết</span></button>
        </div>
        <div class="eco-market-actions-right">
          <?php if (! empty($card['show_like'])) : ?>
            <button class="eco-market-btn<?php echo ! empty($card['liked']) ? ' active' : ''; ?>" type="button" data-like-market-post="<?php echo esc_attr((string) ($card['id'] ?? '')); ?>">❤ <span data-like-count><?php echo esc_html((string) ($card['like_count'] ?? 0)); ?></span></button>
          <?php endif; ?>
          <button class="eco-market-btn" type="button" data-share-market-post="<?php echo esc_attr((string) ($card['id'] ?? '')); ?>">↗ <span data-share-count><?php echo esc_html((string) ($card['share_count'] ?? 0)); ?></span></button>
          <?php if (! empty($card['show_edit'])) : ?>
            <button class="eco-market-btn" type="button" data-toggle-market-editor="<?php echo esc_attr((string) ($card['id'] ?? '')); ?>">✏️ <?php echo esc_html((string) ($card['edit_label'] ?? 'Sửa')); ?></button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="eco-market-media">
      <img src="<?php echo esc_url((string) ($card['image'] ?? '')); ?>" alt="<?php echo esc_attr((string) $card['title']); ?>" loading="lazy">
    </div>
  </div>
  <div class="eco-market-inline-detail" data-market-inline-detail hidden>
    <div class="eco-market-inline-detail-inner">
      <?php if (! empty($card['gallery']) && is_array($card['gallery'])) : ?>
        <div class="market-gallery-slider" data-market-gallery-slider>
          <button class="market-gallery-nav prev" type="button" data-market-gallery-prev aria-label="Ảnh trước">‹</button>
          <div class="market-gallery-viewport" data-market-gallery-viewport>
            <div class="market-gallery-track">
              <?php foreach ((array) $card['gallery'] as $gallery_item) : ?>
                <div class="market-gallery-slide">
                  <img class="market-gallery-slide-image" src="<?php echo esc_url((string) ($gallery_item['url'] ?? '')); ?>" alt="<?php echo esc_attr((string) ($gallery_item['title'] ?? $card['title'])); ?>" loading="lazy">
                </div>
              <?php endforeach; ?>
            </div>
            <div class="market-gallery-counter" data-market-gallery-counter>1/<?php echo esc_html((string) count((array) $card['gallery'])); ?></div>
          </div>
          <button class="market-gallery-nav next" type="button" data-market-gallery-next aria-label="Ảnh sau">›</button>
          <div class="market-gallery-dots" data-market-gallery-dots>
            <?php foreach ((array) $card['gallery'] as $index => $gallery_item) : ?>
              <button class="market-gallery-dot<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-market-gallery-dot="<?php echo esc_attr((string) $index); ?>" aria-label="Xem ảnh <?php echo esc_attr((string) ($index + 1)); ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php else : ?>
        <div class="market-detail-image-wrap"><img class="market-detail-image" src="<?php echo esc_url((string) ($card['image'] ?? '')); ?>" alt="<?php echo esc_attr((string) $card['title']); ?>" loading="lazy"></div>
      <?php endif; ?>
      <div class="entry-content market-copy-clean" style="margin-top:20px"><?php echo wpautop(wp_kses_post((string) ($card['full_content'] ?? ''))); ?></div>
      <?php if (! empty($card['show_edit'])) : ?>
        <div class="market-inline-editor" data-market-inline-editor hidden>
          <div class="small subtle" style="margin:18px 0 10px">Chỉnh sửa tại chỗ · tự lưu sau khi anh dừng gõ</div>
          <div class="market-compose-title-wrap"><input type="text" maxlength="140" value="<?php echo esc_attr((string) ($card['title'] ?? '')); ?>" data-inline-market-title></div>
          <div class="market-structured-grid" style="margin-top:14px"><div><select data-inline-market-category><option value="">Danh mục</option><option<?php selected(($card['category'] ?? ''), 'Hạt giống'); ?>>Hạt giống</option><option<?php selected(($card['category'] ?? ''), 'Cây giống'); ?>>Cây giống</option><option<?php selected(($card['category'] ?? ''), 'Dinh dưỡng cho cây'); ?>>Dinh dưỡng cho cây</option><option<?php selected(($card['category'] ?? ''), 'Các loại rau'); ?>>Các loại rau</option><option<?php selected(($card['category'] ?? ''), 'Hoa'); ?>>Hoa</option></select></div><div><select data-inline-market-offer-type><option value="">Hình thức</option><option<?php selected(($card['offer_type'] ?? ''), 'Bán'); ?>>Bán</option><option<?php selected(($card['offer_type'] ?? ''), 'Trao đổi'); ?>>Trao đổi</option><option<?php selected(($card['offer_type'] ?? ''), 'Chia sẻ'); ?>>Chia sẻ</option><option<?php selected(($card['offer_type'] ?? ''), 'Nhận đặt trước'); ?>>Nhận đặt trước</option></select></div><div><input type="text" value="<?php echo esc_attr((string) ($card['quantity'] ?? '')); ?>" placeholder="Số lượng" data-inline-market-quantity></div><div><input type="text" value="<?php echo esc_attr((string) ($card['area'] ?? '')); ?>" placeholder="Khu vực" data-inline-market-area></div><div><input type="text" value="<?php echo esc_attr((string) ($card['availability'] ?? '')); ?>" placeholder="Thời gian nhận/giao" data-inline-market-availability></div><div><input type="text" value="<?php echo esc_attr((string) ($card['contact'] ?? '')); ?>" placeholder="Liên hệ" data-inline-market-contact></div></div>
          <div class="market-compose-body-wrap" style="margin-top:14px"><textarea data-inline-market-content><?php echo esc_textarea((string) ($card['content'] ?? '')); ?></textarea></div>
          <div class="market-compose-toolbar" style="margin-top:14px"><label class="market-compose-upload"><span>＋</span><strong>Thêm ảnh</strong><input type="file" accept="image/*" multiple data-inline-market-files hidden></label><div class="small subtle" data-inline-market-save-status>Sẵn sàng chỉnh sửa</div></div>
          <div class="market-compose-preview" data-inline-market-preview></div>
        </div>
      <?php endif; ?>
      <?php echo (string) ($card['comments_html'] ?? ''); ?>
    </div>
  </div>
</article>
