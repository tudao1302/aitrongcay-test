<?php

declare(strict_types=1);

$nav_items = [
    ['label' => 'Giới thiệu', 'url' => home_url('/cach-hoat-dong/'), 'match' => ['cach-hoat-dong']],
    ['label' => 'Chợ quê', 'url' => home_url('/cho-que/'), 'match' => ['cho-que']],
    ['label' => 'FAQ', 'url' => home_url('/faq/'), 'match' => ['faq']],
];
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$current_path = trim((string) wp_parse_url(home_url(add_query_arg([], $GLOBALS['wp']->request ?? '')), PHP_URL_PATH), '/');

$header_avatar_html = '👤';
if ($is_logged_in) {
    $header_avatar_id = (int) get_user_meta($current_user->ID, 'aitrongcay_avatar_id', true);
    $header_avatar_url = $header_avatar_id ? (wp_get_attachment_image_url($header_avatar_id, 'thumbnail') ?: wp_get_attachment_url($header_avatar_id)) : '';
    if ($header_avatar_url) {
        $header_avatar_html = '<img src="' . esc_url($header_avatar_url) . '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;margin:0;padding:0;">';
    } else {
        $header_avatar_html = esc_html(mb_strtoupper(mb_substr($current_user->display_name ?: $current_user->user_login, 0, 1)));
    }
}
?>
<header class="site-header site-header--frontend-unified">
    <div class="container nav-row nav-row--frontend-unified">
        <nav class="nav-menu nav-menu--frontend-unified" aria-label="<?php esc_attr_e('Main navigation', 'aitrongcay'); ?>">
            <?php foreach ($nav_items as $item) :
                $is_active = false;
                foreach ((array) ($item['match'] ?? []) as $slug) {
                    if ($current_path === trim((string) $slug, '/')) {
                        $is_active = true;
                        break;
                    }
                }
            ?>
                <a class="<?php echo $is_active ? 'active' : ''; ?>" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
            <?php endforeach; ?>
        </nav>

        <div class="nav-actions nav-actions--frontend-unified">
            <?php if ($is_logged_in) : ?>
                <details class="account-menu frontend-account-menu">
                    <summary class="account-menu-toggle frontend-account-toggle" aria-haspopup="menu">
                        <span class="account-menu-avatar frontend-account-avatar" style="padding:0;overflow:hidden"><?php echo $header_avatar_html; ?></span>
                    </summary>
                    <div class="account-menu-panel">
                        <div class="account-menu-head">
                            <div class="account-menu-avatar large" style="padding:0;overflow:hidden"><?php echo $header_avatar_html; ?></div>
                            <div>
                                <strong style="display:block"><?php echo esc_html($current_user->display_name ?: $current_user->user_login); ?></strong>
                                <span class="subtle small"><?php echo esc_html($current_user->user_email); ?></span>
                            </div>
                        </div>
                        <div class="account-menu-links" role="menu">
                            <a class="account-menu-link" href="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">Khu vườn của tôi</a>
                            <a class="account-menu-link" href="<?php echo esc_url(home_url('/cho-que/')); ?>">Chợ quê</a>
                            <a class="account-menu-link" href="<?php echo esc_url(home_url('/portal/lich-su-giao-dich/')); ?>">Sổ giao dịch & Hợp đồng</a>
                            <a class="account-menu-link" href="<?php echo esc_url(home_url('/tai-khoan/')); ?>">Hồ sơ tài khoản</a>
                            <a class="account-menu-link" href="<?php echo esc_url(home_url('/tai-khoan/#doi-mat-khau')); ?>">Đổi mật khẩu</a>
                            <a class="account-menu-link danger" href="<?php echo esc_url(aitrongcay_logout_url()); ?>">Đăng xuất</a>
                        </div>
                    </div>
                </details>
            <?php else : ?>
                <a class="frontend-account-login" href="<?php echo esc_url(home_url('/dang-nhap/')); ?>">
                    <span class="frontend-account-avatar">👤</span>
                </a>
            <?php endif; ?>
        </div>

        <button class="btn btn-secondary menu-toggle" data-mobile-toggle>Menu</button>
    </div>
    <div class="container" data-mobile-panel style="display:none"></div>
</header>
