<?php
if (! defined('ABSPATH')) { exit; }
$hero = get_query_var('aitr_eco_hero', []);
$title = (string) ($hero['title'] ?? '');
$body = (string) ($hero['body'] ?? '');
$kicker = (string) ($hero['kicker'] ?? '');
?>
<header class="eco-hero">
  <?php if ($kicker !== '') : ?><span class="eco-kicker"><?php echo esc_html($kicker); ?></span><?php endif; ?>
  <?php if ($title !== '') : ?><h1><?php echo esc_html($title); ?></h1><?php endif; ?>
  <?php if ($body !== '') : ?><p><?php echo esc_html($body); ?></p><?php endif; ?>
</header>
