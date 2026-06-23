<section class="section-hero">
    <div class="container hero-grid">
        <div class="hero-card hero-shell">
            <span class="eyebrow"><?php echo esc_html(aitrongcay_homepage_text('hero_eyebrow')); ?></span>
            <h1 style="margin-bottom:20px"><?php echo esc_html(aitrongcay_homepage_text('hero_title')); ?></h1>
            <div class="hero-poem">
                <?php foreach (preg_split("/(\r\n|\n|\r)/", aitrongcay_homepage_text('hero_poem')) as $line) : ?>
                    <?php if (trim((string) $line) === '') : ?>
                        <br>
                    <?php else : ?>
                        <p><?php echo esc_html((string) $line); ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <p class="lead" style="margin-top:22px"><?php echo esc_html(aitrongcay_homepage_text('hero_lead')); ?></p>
            <div class="inline-list" style="margin:24px 0 18px">
                <a class="btn btn-primary" href="<?php echo esc_url(aitrongcay_homepage_url('hero_primary_cta_url')); ?>"><?php echo esc_html(aitrongcay_homepage_text('hero_primary_cta_label')); ?></a>
                <a class="btn btn-secondary" href="<?php echo esc_url(aitrongcay_homepage_url('hero_secondary_cta_url')); ?>"><?php echo esc_html(aitrongcay_homepage_text('hero_secondary_cta_label')); ?></a>
            </div>
        </div>
        <div class="hero-card hero-visual">
            <div class="device-stack">
                <div class="hero-screen device-tablet">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px"><span class="live-badge"><span class="dot"></span> LIVE • Khu vườn đang hoạt động</span><span class="label">Cập nhật lúc <span data-now></span></span></div>
                    <div class="camera-view media-frame media-frame-16x9" style="min-height:250px;margin-bottom:18px;position:relative;overflow:hidden;border-radius:24px;background:#0f172a">
                        <video class="media-fit-cover" autoplay muted loop playsinline controls poster="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-greenhouse.svg'); ?>" aria-label="Livecam demo"><source src="https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4" type="video/mp4"></video>
                        <div class="camera-overlay">
                            <div class="chips"><span class="label">Cam nhà lưới 01</span><span class="label">Có snapshot theo mốc</span></div>
                            <div><strong style="font-size:1.45rem"><?php echo esc_html(aitrongcay_homepage_text('hero_overlay_title')); ?></strong><p class="small"><?php echo esc_html(aitrongcay_homepage_text('hero_overlay_body')); ?></p></div>
                        </div>
                    </div>
                    <div class="hero-photo-caption" style="margin-bottom:18px">
                        <strong><?php echo esc_html(aitrongcay_homepage_text('hero_caption_title')); ?></strong>
                        <p class="small" style="margin:8px 0 0"><?php echo esc_html(aitrongcay_homepage_text('hero_caption_body')); ?></p>
                    </div>
                    <div class="stats-grid">
                        <div class="metric"><span class="subtle">Nhiệt độ</span><strong>24.8°C</strong></div>
                        <div class="metric"><span class="subtle">Độ ẩm</span><strong>71%</strong></div>
                        <div class="metric"><span class="subtle">Health score</span><strong>92/100</strong></div>
                    </div>
                    <div class="hero-floating-card"><div class="kicker">AI summary</div><p style="margin:0"><?php echo esc_html(aitrongcay_homepage_text('hero_ai_summary')); ?></p></div>
                </div>
            </div>
        </div>
    </div>
</section>
