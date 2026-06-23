<?php
if (! defined('ABSPATH')) { exit; }
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;0,700;1,400&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script>
window.tailwind = window.tailwind || {};
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'surface-container-lowest': '#0d0f0c',
        'on-surface-variant': '#bdcac0',
        'surface-container': '#1e201d',
        'surface-dim': '#121411',
        'on-surface': '#e3e3de',
        'primary': '#6fdba8',
        'surface-variant': '#333532',
        'surface-container-low': '#1a1c19',
        'background': '#121411',
        'primary-container': '#31a375',
        'outline-variant': '#3e4942',
        'surface-container-high': '#292b27'
      },
      fontFamily: {
        headline: ['Noto Serif', 'serif'],
        display: ['Noto Serif', 'serif'],
        body: ['Manrope', 'sans-serif'],
      }
    }
  }
};
</script>
<style>
  .faq-page { background:#121411; color:#e3e3de; font-family:'Manrope',sans-serif; }
  .faq-page .font-display { font-family:'Noto Serif',serif; }
  .faq-page .glass-panel { background:rgba(51,53,50,.5); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); }
  .faq-page .growth-gradient { background:linear-gradient(135deg,#31a375 0%,#6fdba8 100%); }
  .faq-page .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }
  
  details[open] summary .expand-icon { transform: rotate(180deg); color: #6fdba8; }
  details summary::-webkit-details-marker { display: none; }
  summary { list-style: none; }
</style>

<section class="faq-page dark overflow-x-hidden min-h-screen">
  <!-- Hero Section -->
  <section class="relative pt-32 pb-16 px-6 text-center">
    <div class="absolute inset-0 z-0">
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-primary/5 blur-[120px] rounded-full"></div>
    </div>
    <div class="relative z-10 max-w-4xl mx-auto">
      <span class="text-primary font-bold tracking-widest uppercase text-sm mb-4 block">Hỗ trợ khách hàng</span>
      <h1 class="font-display text-5xl md:text-6xl font-bold mb-6">Giải đáp thắc mắc</h1>
      <p class="text-on-surface-variant text-lg max-w-2xl mx-auto">Mọi điều bạn cần biết về mô hình vườn số Ai trồng cây. Nếu không tìm thấy câu trả lời, hãy hỏi trực tiếp trợ lý AI của chúng tôi.</p>
    </div>
  </section>

  <!-- FAQ Accordion -->
  <section class="max-w-4xl mx-auto px-6 pb-24 space-y-12">
    
    <!-- Nhóm 1: Về dịch vụ -->
    <div class="space-y-4">
      <h2 class="font-display text-2xl font-bold text-primary mb-6 flex items-center gap-3">
        <span class="material-symbols-outlined">info</span>
        Về dịch vụ
      </h2>
      <div class="space-y-3">
        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Ai trồng cây là gì?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Ai trồng cây là nền tảng vườn số (hydroponic garden SaaS) cho phép bạn sở hữu một khoang rau thực sự được chăm sóc bởi đội ngũ chuyên nghiệp và robot. Bạn có thể theo dõi hành trình của cây qua webcam, nhật ký điện tử và tương tác với trợ lý AI ngay trên điện thoại.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Tại sao tôi nên chọn Ai trồng cây thay vì mua rau ngoài chợ?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Điểm khác biệt lớn nhất là sự **minh bạch**. Bạn không chỉ nhận rau sạch, mà bạn nhìn thấy quá trình tạo ra nó. Từ nguồn nước, dinh dưỡng đến việc chăm sóc hàng ngày đều được số hóa và công khai. Bạn hoàn toàn yên tâm về nguồn gốc thực phẩm của gia đình.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Mô hình này khác gì với việc tôi tự trồng rau tại nhà?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Chúng tôi giải quyết các vấn đề: thiếu không gian, thiếu thời gian và thiếu kỹ thuật. Bạn vẫn có cảm giác "vườn nhà" nhưng không phải lo lắng về việc tưới nước, bắt sâu hay cây bị chết do thời tiết. Mọi việc nặng nhọc đã có hệ thống tự động và đội ngũ của chúng tôi lo.
          </div>
        </details>
      </div>
    </div>

    <!-- Nhóm 2: Cây trồng & chăm sóc -->
    <div class="space-y-4">
      <h2 class="font-display text-2xl font-bold text-primary mb-6 flex items-center gap-3">
        <span class="material-symbols-outlined">potted_plant</span>
        Cây trồng & chăm sóc
      </h2>
      <div class="space-y-3">
        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Tôi có thể trồng những loại cây gì?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Danh mục của chúng tôi rất đa dạng từ các loại rau ăn lá (xà lách, rau muống, cải...), các loại rau gia vị đến các loại quả nhỏ như cà chua bi, dưa leo. Bạn có thể xem danh mục đầy đủ trong phần Flower-bio trên portal.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Quy trình chăm sóc diễn ra như thế nào?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Chúng tôi áp dụng quy trình chuẩn (SOP) cho từng loại cây. Hệ thống IoT sẽ tự động điều chỉnh ánh sáng, nước và dinh dưỡng. Đội ngũ kỹ thuật sẽ kiểm tra định kỳ và can thiệp nếu có dấu hiệu bất thường về sinh trưởng.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Hệ thống có dùng thuốc trừ sâu hay chất kích thích không?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            **Tuyệt đối không.** Chúng tôi canh tác trong môi trường nhà màng kín, kiểm soát sâu bệnh bằng biện pháp vật lý và sinh học. Dinh dưỡng được cung cấp là dạng khoáng hữu cơ hòa tan, giúp cây phát triển tự nhiên và an toàn nhất.
          </div>
        </details>
      </div>
    </div>

    <!-- Nhóm 3: Theo dõi & portal -->
    <div class="space-y-4">
      <h2 class="font-display text-2xl font-bold text-primary mb-6 flex items-center gap-3">
        <span class="material-symbols-outlined">devices</span>
        Theo dõi & portal
      </h2>
      <div class="space-y-3">
        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Tôi theo dõi khu vườn của mình bằng cách nào?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Bạn đăng nhập vào Portal trên website. Tại đó, bạn có thể xem camera trực tiếp (Livecam), xem biểu đồ dữ liệu môi trường (nhiệt độ, độ ẩm) và nhật ký chăm sóc hàng ngày.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Trợ lý AI có vai trò gì trong việc chăm sóc vườn?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Trợ lý AI giúp bạn "đọc" dữ liệu khu vườn. Nó sẽ phân tích nếu cây đang cần thêm nước, hay dự báo khi nào cây sẽ đạt độ thu hoạch tốt nhất. Bạn cũng có thể hỏi AI về các mẹo nấu ăn với loại rau bạn đang trồng.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Tôi có thể trực tiếp điều khiển thiết bị trong vườn không?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Có, hệ thống cho phép bạn tương tác trực tiếp như bật/tắt máy bơm hoặc đèn LED ngay từ dashboard nếu bạn muốn tự tay trải nghiệm việc chăm sóc.
          </div>
        </details>
      </div>
    </div>

    <!-- Nhóm 4: Thu hoạch & giao hàng -->
    <div class="space-y-4">
      <h2 class="font-display text-2xl font-bold text-primary mb-6 flex items-center gap-3">
        <span class="material-symbols-outlined">local_shipping</span>
        Thu hoạch & giao hàng
      </h2>
      <div class="space-y-3">
        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Khi nào thì tôi được thu hoạch?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Tùy loại cây, trung bình từ 25-45 ngày. Hệ thống sẽ thông báo cho bạn qua email hoặc notification trên portal khi cây đã sẵn sàng để thu hoạch.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Quy trình giao hàng diễn ra như thế nào?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Sau khi thu hoạch, rau được đóng gói bảo quản mát và giao ngay đến địa chỉ đăng ký của bạn trong vòng 2-4 giờ để đảm bảo độ tươi ngon nhất (áp dụng cho khu vực nội thành Hà Nội).
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Nếu tôi đi vắng vào ngày thu hoạch thì sao?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Bạn có thể yêu cầu hoãn thu hoạch (tối đa 2-3 ngày tùy loại rau) hoặc yêu cầu chúng tôi tặng số rau đó cho người thân, bạn bè của bạn.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Rau sau khi thu hoạch có thể bảo quản được bao lâu?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Vì rau được giao ngay sau khi hái, độ tươi cao hơn nhiều so với rau siêu thị. Nếu bảo quản trong ngăn mát tủ lạnh đúng cách, rau vẫn giữ được độ ngon từ 5-7 ngày.
          </div>
        </details>
      </div>
    </div>

    <!-- Nhóm 5: Chi phí & đăng ký -->
    <div class="space-y-4">
      <h2 class="font-display text-2xl font-bold text-primary mb-6 flex items-center gap-3">
        <span class="material-symbols-outlined">payments</span>
        Chi phí & đăng ký
      </h2>
      <div class="space-y-3">
        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Chi phí cho một khu vườn số là bao nhiêu?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Chúng tôi có nhiều gói dịch vụ từ cá nhân đến gia đình đông người. Bạn có thể xem chi tiết bảng giá trong phần "Gói bắt đầu" trên trang chủ hoặc liên hệ để được tư vấn gói tối ưu nhất.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Tôi có thể hủy dịch vụ bất cứ lúc nào không?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Có, chúng tôi hỗ trợ hủy dịch vụ linh hoạt. Tuy nhiên, khuyến khích bạn hoàn thành chu kỳ trồng hiện tại để không lãng phí mầm sống đã gieo.
          </div>
        </details>

        <details class="group glass-panel rounded-2xl border border-white/5 overflow-hidden transition-all duration-300">
          <summary class="flex items-center justify-between p-6 cursor-pointer hover:bg-white/5">
            <h3 class="font-bold text-lg pr-8">Làm thế nào để tôi bắt đầu đăng ký?</h3>
            <span class="expand-icon material-symbols-outlined transition-transform duration-300 text-on-surface-variant">expand_more</span>
          </summary>
          <div class="p-6 pt-0 text-on-surface-variant leading-relaxed border-t border-white/5">
            Rất đơn giản, bạn chỉ cần bấm vào nút "Đăng ký tư vấn" và để lại thông tin. Đội ngũ của chúng tôi sẽ gọi lại để tìm hiểu nhu cầu thực sự của gia đình bạn trước khi chính thức bắt đầu.
          </div>
        </details>
      </div>
    </div>

  </section>

  <!-- CTA Section -->
  <section class="py-24 px-6 relative overflow-hidden bg-surface-container-low">
    <div class="max-w-4xl mx-auto text-center relative z-10">
      <h2 class="font-display text-4xl font-bold mb-8">Vẫn còn câu hỏi khác?</h2>
      <div class="flex flex-wrap justify-center gap-6">
        <a href="<?php echo esc_url(home_url('/portal/tro-ly-ai/')); ?>" class="growth-gradient text-on-primary px-8 py-4 rounded-xl font-bold text-lg hover:scale-[1.02] transition-all flex items-center gap-2">
          <span class="material-symbols-outlined">psychology</span>
          Hỏi trợ lý AI
        </a>
        <a href="<?php echo esc_url(home_url('/dang-ky-tu-van/')); ?>" class="glass-panel text-on-surface border border-white/10 px-8 py-4 rounded-xl font-bold text-lg hover:bg-surface-bright transition-all flex items-center gap-2">
          <span class="material-symbols-outlined">mail</span>
          Gửi yêu cầu tư vấn
        </a>
      </div>
    </div>
  </section>
</section>
