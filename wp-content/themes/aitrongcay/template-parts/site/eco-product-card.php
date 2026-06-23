<?php
if (! defined('ABSPATH')) { exit; }
$item = get_query_var('aitr_product_card', []);
if (! is_array($item) || empty($item['name'])) { return; }
$theme_image_base = trailingslashit(get_template_directory_uri() . '/assets/images');
?>
<article class="eco-warehouse-card">
  <div class="eco-warehouse-badge"><?php echo esc_html((string) ($item['badge'] ?? 'ESSENTIAL')); ?></div>
  <div class="eco-warehouse-card-media">
    <div class="eco-warehouse-card-img-wrap">
      <img src="<?php echo esc_url((string) ($item['image'] ?? ($theme_image_base . 'tools-shed.svg'))); ?>" alt="<?php echo esc_attr((string) ($item['name'] ?? 'Vật tư')); ?>">
    </div>
  </div>
  <h3><?php echo esc_html((string) ($item['name'] ?? 'Vật tư')); ?></h3>
  <div class="eco-warehouse-meta"><?php echo esc_html((string) ($item['category_label'] ?? 'Vật tư')); ?></div>
  <p class="eco-warehouse-desc"><?php echo esc_html((string) ($item['description'] ?? '')); ?></p>
  <div class="eco-warehouse-price-row">
    <div>
      <div class="eco-warehouse-price"><?php echo esc_html(number_format((int) ($item['price'] ?? 0))); ?></div>
    </div>
  </div>
<?php 
$stock = (int) ($item['stock_quantity'] ?? 0);
$is_out_of_stock = $stock <= 0;
?>
  <div class="eco-warehouse-buy-row" data-eco-warehouse-buy-row data-tool-name="<?php echo esc_attr((string) ($item['name'] ?? 'Vật tư')); ?>" data-tool-price="<?php echo esc_attr((string) ((int) ($item['price'] ?? 0))); ?>" data-tool-image="<?php echo esc_attr((string) ($item['image'] ?? ($theme_image_base . 'tools-shed.svg'))); ?>" data-tool-category="<?php echo esc_attr((string) ($item['category_label'] ?? 'Kho')); ?>" data-tool-stock="<?php echo esc_attr((string) $stock); ?>">
    <?php if ($is_out_of_stock) : ?>
      <button class="eco-warehouse-cta" type="button" disabled style="background:#4b5563;color:#9ca3af;box-shadow:none;cursor:not-allowed">Hết hàng</button>
    <?php else: ?>
      <button class="eco-warehouse-cta" type="button" data-eco-warehouse-buy>Mua</button>
    <?php endif; ?>
  </div>
</article>
