<section class="section dark-band">
    <div class="container grid-2">
        <div>
            <span class="eyebrow" style="background:rgba(255,255,255,.12);color:white"><?php echo esc_html(aitrongcay_homepage_text('portal_eyebrow')); ?></span>
            <h2><?php echo esc_html(aitrongcay_homepage_text('portal_title')); ?></h2>
            <p><?php echo esc_html(aitrongcay_homepage_text('portal_body')); ?></p>
            <div class="tabs" data-tabs>
                <button class="tab-button active" data-tab-button="cam">Live cam</button>
                <button class="tab-button" data-tab-button="log">Care log</button>
                <button class="tab-button" data-tab-button="quality">Quality</button>
            </div>
            <div data-tab-panel="cam" class="tab-panel active"><p>Xem webcam để nhận ra những thay đổi thật: ánh sáng, luống rau, nhịp phát triển và cảm giác vườn đang hiện diện mỗi ngày.</p></div>
            <div data-tab-panel="log" class="tab-panel"><p>Đọc nhật ký để biết đã có việc gì được làm, vào lúc nào, bởi ai và vì sao cần làm như vậy.</p></div>
            <div data-tab-panel="quality" class="tab-panel"><p>Xem hồ sơ theo lô để nối mạch từ quá trình chăm sóc đến kỳ thu hoạch và bữa ăn của gia đình.</p></div>
        </div>
        <div class="portal-card">
            <div class="media-frame media-frame-16x9" style="border-radius:28px;overflow:hidden;background:#0f172a;position:relative"><video class="media-fit-cover" autoplay muted loop playsinline controls poster="<?php echo esc_url(get_template_directory_uri() . '/assets/images/story-morning.svg'); ?>"><source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4">Trình duyệt này chưa phát được video demo.</video><div class="camera-overlay"><div class="chips"><span class="label">Khu vườn số</span><span class="label">Theo dõi hằng ngày</span></div><div><h3 style="font-size:1.9rem;margin:0 0 8px">Một khu vườn của gia đình</h3><p>Giao diện này cho thấy cách khu vườn số gom webcam, nhật ký chăm sóc và dữ liệu theo dõi vào cùng một chỗ, đủ rõ để xem mỗi ngày.</p></div></div></div>
            <div class="badge-grid" style="margin-top:18px">
                <div class="metric"><span class="subtle">AI summary</span><strong>Dễ hiểu</strong></div>
                <div class="metric"><span class="subtle">Snapshot</span><strong>Theo mốc</strong></div>
                <div class="metric"><span class="subtle">Care log</span><strong>Có đối chiếu</strong></div>
            </div>
        </div>
    </div>
</section>
