<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$page = aitrongcay_current_virtual_page();

if (! $page && is_page()) {
    $native_slug = get_post_field('post_name', get_queried_object_id());
    $native_overrides = [
        'cach-hoat-dong' => ['title' => 'Giới thiệu', 'template' => 'template-parts/virtual/cach-hoat-dong.php'],
        'cho-que' => ['title' => 'Chợ quê', 'template' => 'template-parts/virtual/cho-que.php'],
        'tai-khoan' => ['title' => 'Tài khoản', 'template' => 'template-parts/virtual/tai-khoan.php'],
    ];
    if (is_string($native_slug) && isset($native_overrides[$native_slug])) {
        $page = $native_overrides[$native_slug] + ['slug' => $native_slug];
    }
}

if (! $page) {
    include get_404_template();
    return;
}

get_header();
?>
<main>
    <?php include get_template_directory() . '/' . $page['template']; ?>
</main>
<?php
get_footer();
