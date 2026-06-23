<?php
/**
 * Template Name: Portal Landing
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main>
    <section class="section dark-band">
        <div class="container grid-2">
            <div>
                <span class="eyebrow" style="background:rgba(255,255,255,.12);color:white">Portal với Kho nông cụ</span>
                <h1 style="color:white">Một cửa vào chung cho dashboard, webcam, care log và Kho nông cụ</h1>
                <p>Ở checkpoint này, portal trong WordPress được dựng như một landing/handoff page. Các màn hình sâu hơn vẫn đang có bản HTML tĩnh để bóc tiếp thành app hoặc template riêng ở vòng sau.</p>
                <div class="inline-list" style="margin-top:24px">
                    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/portal/dashboard-2/')); ?>">Mở dashboard</a>
                    <a class="btn btn-secondary" href="<?php echo esc_url(home_url('/portal/kho-nong-cu/')); ?>">Xem Kho nông cụ</a>
                </div>
            </div>
            <div class="portal-card">
                <h3>Map portal đã sẵn</h3>
                <ul class="bullet-list">
                    <li>/portal/dashboard-2/ → dashboard tổng quan khu vườn</li>
                    <li>/portal/webcam/ → live cam & timelapse</li>
                    <li>/portal/tinh-trang-vuon/ → health / status</li>
                    <li>/portal/nhat-ky-cham-soc/ → care log</li>
                    <li>/portal/chat-luong-an-toan/ → quality & safety</li>
                    <li>/portal/tro-ly-ai/ → AI gardener</li>
                    <li>/portal/kho-nong-cu/ → Kho nông cụ</li>
                </ul>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();
