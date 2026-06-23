<section class="section dark-band">
    <div class="container grid-2">
        <div><span class="eyebrow" style="background:rgba(255,255,255,.12);color:white"><?php echo esc_html(aitrongcay_homepage_text('cta_eyebrow')); ?></span><h2><?php echo esc_html(aitrongcay_homepage_text('cta_title')); ?></h2><p><?php echo esc_html(aitrongcay_homepage_text('cta_body')); ?></p><div class="timeline timeline-compact" style="margin-top:22px"><div class="timeline-item"><div class="time-pill">A</div><div><h3>Điền form ngắn</h3><p>Đủ để bên em biết nên bắt đầu cuộc trò chuyện từ đâu.</p></div></div><div class="timeline-item"><div class="time-pill">B</div><div><h3>Trao đổi ngắn</h3><p>Làm rõ mong muốn, nhịp sinh hoạt gia đình và mức quan tâm tới việc theo dõi khu vườn.</p></div></div><div class="timeline-item"><div class="time-pill">C</div><div><h3>Chỉ đi tiếp khi phù hợp</h3><p>Nếu hợp, mình mới bàn sâu hơn về gói, nhịp vận hành và cách bắt đầu.</p></div></div></div></div>
        <div>
            <?php aitrongcay_render_consultation_notice(); ?>
            <form class="portal-card" method="post" action="<?php echo aitrongcay_consultation_action_url(); ?>"><input type="hidden" name="action" value="aitrongcay_consultation_submit"><?php wp_nonce_field('aitrongcay_consultation_submit', 'aitrongcay_consultation_nonce'); ?><input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/#homepage-consultation')); ?>"><input type="hidden" name="funnelStage" value="homepage-cta"><input type="hidden" name="funnelSource" value="front-page-cta"><div id="homepage-consultation" class="form-grid">
                <div><label>Họ và tên</label><input name="fullName" placeholder="Anh/chị nhập họ tên" required></div>
                <div><label>Số điện thoại</label><input name="phone" placeholder="09xx xxx xxx" required></div>
                <div><label>Email</label><input name="email" placeholder="anhchi@email.com"></div>
                <div><label>Mốc bắt đầu mong muốn</label><input name="startWindow" placeholder="Ví dụ: trong tháng tới"></div>
                <div><label>Điều anh/chị muốn hiểu trước</label><select name="goal"><option value="">Chọn một mục</option><option>Mô hình vận hành như thế nào</option><option>Rau sạch cho gia đình</option><option>Tôi muốn xem khu vườn trước</option><option>Tư vấn gói phù hợp</option></select></div>
            </div>
            <div style="margin-top:16px"><label>Mối quan tâm chính</label><textarea name="focus" placeholder="Ví dụ: muốn rau sạch cho con nhỏ, cần minh bạch quy trình, muốn theo dõi khu vườn rõ ràng"></textarea></div>
            <div style="margin-top:16px" class="inline-list"><button class="btn btn-primary" type="submit"><?php echo esc_html(aitrongcay_homepage_text('cta_submit_label')); ?></button><a class="btn btn-secondary" href="<?php echo esc_url(aitrongcay_homepage_url('cta_secondary_url')); ?>"><?php echo esc_html(aitrongcay_homepage_text('cta_secondary_label')); ?></a></div>
        </form>
        </div>
    </div>
</section>
