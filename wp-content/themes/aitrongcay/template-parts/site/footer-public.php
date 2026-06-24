<?php

declare(strict_types=1);

$company = aitrongcay_company_profile();
$footer_groups = aitrongcay_footer_groups();
?>
<footer class="footer" style="background:#121411;color:#e3e3de;padding:24px 0 34px">
    <style>
      .footer .eco-footer-wrap{max-width:1240px;margin:0 auto;padding:0 24px}
      .footer .eco-footer-shell{background:rgba(26,28,25,.94);border:1px solid rgba(255,255,255,.05);box-shadow:0 24px 52px rgba(0,0,0,.2);border-radius:34px;overflow:hidden}
      .footer .eco-footer-top{display:block;padding:28px;background:linear-gradient(180deg, rgba(7,33,24,.72), rgba(7,33,24,.38))}
      .footer .eco-footer-brand{font-family:'Noto Serif',serif;font-size:34px;font-style:italic;color:var(--primary);margin:0 0 10px}
      .footer .eco-footer-lead,.footer .eco-footer-meta-text{color:rgba(227,227,222,.72);line-height:1.8}
      .footer .eco-footer-card{background:rgba(51,53,50,.38);backdrop-filter:blur(20px);border-radius:24px;padding:20px}
      .footer .eco-footer-grid{display:grid;grid-template-columns:1.1fr repeat(3,minmax(0,1fr));gap:20px;padding:0 28px 28px}
      .footer .eco-footer-col h3{margin:0 0 12px;font-family:'Noto Serif',serif;color:#fff;font-size:22px}
      .footer .eco-footer-col a{display:block;color:rgba(227,227,222,.7);margin:8px 0}
      .footer .eco-footer-meta{display:flex;justify-content:space-between;gap:16px;align-items:center;padding:18px 28px 0;border-top:1px solid rgba(255,255,255,.05);margin:0 28px 28px}
      @media (max-width:980px){.footer .eco-footer-top,.footer .eco-footer-grid{grid-template-columns:1fr}.footer .eco-footer-meta{flex-direction:column;align-items:flex-start}}
    </style>
    <div class="eco-footer-wrap">
      <div class="eco-footer-shell">
        <div class="eco-footer-grid">
          <div class="eco-footer-col">
            <h3>Ai trồng cây</h3>
            <p class="eco-footer-meta-text">Một khu vườn số cho gia đình, đủ rõ để theo dõi và đủ yên để gắn bó mỗi ngày.</p>
          </div>
          <?php foreach ($footer_groups as $group) : ?>
            <div class="eco-footer-col">
              <h3><?php echo esc_html($group['title']); ?></h3>
              <?php foreach ($group['items'] as $item) : ?>
                <a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['label']); ?></a>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="eco-footer-meta">
          <span>© <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html($company['brand']); ?></span>
          <span class="eco-footer-meta-text">Công ty CP nghiên cứu giải pháp và phát triển công nghệ xanh - Số 180A, Âu Cơ, P. Hồng Hà, Hà Nội, Việt Nam.</span>
        </div>
      </div>
    </div>
</footer>

<a id="draggable-ai-chat" class="floating-ai-chat" href="<?php echo esc_url(home_url('/portal/tro-ly-ai/')); ?>" aria-label="Chat với AI">
    <span class="floating-ai-chat-icon" aria-hidden="true">💬</span>
    <span>Chat với AI</span>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBtn = document.getElementById('draggable-ai-chat');
    if (!chatBtn) return;

    let isDragging = false;
    let startX, startY;
    let initialLeft, initialTop;
    let wasDragged = false;

    // Remove inline styles if window resizes to avoid sticking off-screen
    window.addEventListener('resize', () => {
        chatBtn.style.left = '';
        chatBtn.style.top = '';
        chatBtn.style.right = '';
        chatBtn.style.bottom = '';
    });

    const dragStart = (e) => {
        if (e.type === 'touchstart') {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        } else {
            startX = e.clientX;
            startY = e.clientY;
            e.preventDefault(); // Prevent text selection/drag native
        }

        const rect = chatBtn.getBoundingClientRect();
        initialLeft = rect.left;
        initialTop = rect.top;
        
        isDragging = true;
        wasDragged = false;
        
        // Disable transition during drag for smoothness
        chatBtn.style.transition = 'none';
        
        // Remove fixed bottom/right constraints so left/top takes over
        chatBtn.style.bottom = 'auto';
        chatBtn.style.right = 'auto';
    };

    const dragMove = (e) => {
        if (!isDragging) return;
        
        let clientX, clientY;
        if (e.type === 'touchmove') {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
            // Prevent default only if moving to avoid blocking scrolling entirely if not needed,
            // but for a draggable element we usually prevent scroll while dragging it.
            if (e.cancelable) e.preventDefault();
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }

        const dx = clientX - startX;
        const dy = clientY - startY;

        if (Math.abs(dx) > 5 || Math.abs(dy) > 5) {
            wasDragged = true;
        }

        let newLeft = initialLeft + dx;
        let newTop = initialTop + dy;

        // Constrain to window bounds
        const maxLeft = window.innerWidth - chatBtn.offsetWidth;
        const maxTop = window.innerHeight - chatBtn.offsetHeight;
        
        if (newLeft < 0) newLeft = 0;
        if (newTop < 0) newTop = 0;
        if (newLeft > maxLeft) newLeft = maxLeft;
        if (newTop > maxTop) newTop = maxTop;

        chatBtn.style.left = newLeft + 'px';
        chatBtn.style.top = newTop + 'px';
    };

    const dragEnd = (e) => {
        isDragging = false;
        chatBtn.style.transition = ''; // Restore hover transitions
    };

    // Touch events
    chatBtn.addEventListener('touchstart', dragStart, {passive: false});
    document.addEventListener('touchmove', dragMove, {passive: false});
    document.addEventListener('touchend', dragEnd);

    // Mouse events
    chatBtn.addEventListener('mousedown', dragStart);
    document.addEventListener('mousemove', dragMove);
    document.addEventListener('mouseup', dragEnd);

    // Prevent click if dragged
    chatBtn.addEventListener('click', (e) => {
        if (wasDragged) {
            e.preventDefault();
            wasDragged = false;
        }
    });
});
</script>
