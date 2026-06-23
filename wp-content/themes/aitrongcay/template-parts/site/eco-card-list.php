<?php
if (! defined('ABSPATH')) { exit; }
$list = get_query_var('aitr_eco_card_list', []);
$items = is_array($list['items'] ?? null) ? $list['items'] : [];
$columns = max(1, min(4, (int) ($list['columns'] ?? 3)));
?>
<div class="eco-card-list" style="display:grid;grid-template-columns:repeat(<?php echo (int) $columns; ?>,minmax(0,1fr));gap:20px">
  <?php foreach ($items as $item) : ?>
    <article class="eco-card">
      <?php if (! empty($item['icon'])) : ?><div style="font-size:34px;margin-bottom:14px"><?php echo esc_html((string) $item['icon']); ?></div><?php endif; ?>
      <?php if (! empty($item['kicker'])) : ?><span class="eco-kicker"><?php echo esc_html((string) $item['kicker']); ?></span><?php endif; ?>
      <?php if (! empty($item['title'])) : ?><h3 style="margin:0 0 12px;font-family:'Noto Serif',serif;color:#fff;font-size:28px;line-height:1.12"><?php echo esc_html((string) $item['title']); ?></h3><?php endif; ?>
      <?php if (! empty($item['body'])) : ?><p style="color:rgba(227,227,222,.76);line-height:1.8"><?php echo esc_html((string) $item['body']); ?></p><?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
