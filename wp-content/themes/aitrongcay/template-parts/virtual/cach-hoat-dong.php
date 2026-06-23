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
        'on-tertiary': '#532200',
        'on-secondary': '#3a3000',
        'surface-container-lowest': '#0d0f0c',
        'on-surface-variant': '#bdcac0',
        'on-primary-fixed-variant': '#005236',
        'inverse-on-surface': '#2f312e',
        'tertiary-container': '#d07b46',
        'secondary-container': '#ffdb3c',
        'on-surface': '#e3e3de',
        'surface-tint': '#6fdba8',
        'outline': '#87948b',
        'on-tertiary-fixed-variant': '#753401',
        'surface-container': '#1e201d',
        'surface-dim': '#121411',
        'on-tertiary-container': '#481d00',
        'secondary-fixed': '#ffe16d',
        'on-primary-container': '#00311f',
        'surface-variant': '#333532',
        'tertiary-fixed': '#ffdbc9',
        'surface-container-low': '#1a1c19',
        'background': '#121411',
        'on-secondary-fixed-variant': '#544600',
        'surface': '#121411',
        'on-primary': '#003824',
        'secondary': '#fff9ef',
        'tertiary': '#ffb68c',
        'on-secondary-fixed': '#221b00',
        'on-primary-fixed': '#002113',
        'primary-fixed-dim': '#6fdba8',
        'on-tertiary-fixed': '#321200',
        'on-error': '#690005',
        'surface-bright': '#383a36',
        'surface-container-highest': '#333532',
        'on-background': '#e3e3de',
        'inverse-primary': '#006c49',
        'tertiary-fixed-dim': '#ffb68c',
        'secondary-fixed-dim': '#e9c400',
        'outline-variant': '#3e4942',
        'error-container': '#93000a',
        'error': '#ffb4ab',
        'inverse-surface': '#e3e3de',
        'on-secondary-container': '#725f00',
        'primary': '#6fdba8',
        'primary-fixed': '#8bf8c3',
        'primary-container': '#31a375',
        'on-error-container': '#ffdad6',
        'surface-container-high': '#292b27'
      },
      fontFamily: {
        headline: ['Noto Serif', 'serif'],
        display: ['Noto Serif', 'serif'],
        body: ['Manrope', 'sans-serif'],
        label: ['Manrope', 'sans-serif']
      }
    }
  }
};
</script>
<style>
  .cach-hoat-dong-page { background:#121411; color:#e3e3de; font-family:'Manrope',sans-serif; }
  .cach-hoat-dong-page .font-display { font-family:'Noto Serif',serif; }
  .cach-hoat-dong-page .glass-panel { background:rgba(51,53,50,.5); backdrop-filter:blur(20px); -webkit-backdrop-filter:blur(20px); }
  .cach-hoat-dong-page .growth-gradient { background:linear-gradient(135deg,#31a375 0%,#6fdba8 100%); }
  .cach-hoat-dong-page .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }
  .floating-ai-chat { display:none !important; }
</style>
<section class="cach-hoat-dong-page dark overflow-x-hidden">
  <section class="relative min-h-[819px] flex items-center justify-center px-6 pt-28">
    <div class="absolute inset-0 z-0">
      <div class="absolute top-1/4 -left-20 w-96 h-96 bg-primary/20 blur-[120px] rounded-full"></div>
      <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-secondary/10 blur-[100px] rounded-full"></div>
    </div>
    <div class="relative z-10 max-w-5xl text-center">
      <h1 class="font-display text-5xl md:text-7xl font-bold mb-8 leading-tight tracking-tighter">Trồng rau sạch trên điện thoại</h1>
      <p class="text-lg md:text-xl text-on-surface-variant max-w-3xl mx-auto mb-12 leading-relaxed">Lựa chọn đúng loại cây trồng để đảm bảo dinh dưỡng cho cả gia đình. Quá trình canh tác được thực hiện tự động bằng robot, sử dụng trí tuệ nhân tạo để chăm sóc khu vườn của bạn 24/7. Sản phẩm khi thu hoạch được ship đến tận nhà.</p>
      <div class="flex flex-wrap justify-center gap-4">
        <a href="<?php echo esc_url(home_url('/portal/tro-ly-ai/')); ?>" class="growth-gradient text-on-primary px-8 py-4 rounded-xl font-bold text-lg transition-all inline-flex items-center gap-2 hover:scale-[1.02]">
          <span class="material-symbols-outlined">psychology</span>
          Hãy để AI tư vấn cho bạn
        </a>
        <a href="<?php echo esc_url(home_url('/portal/flower-bio/')); ?>" class="glass-panel text-primary border border-primary/20 px-8 py-4 rounded-xl font-bold text-lg hover:bg-surface-bright transition-all inline-flex items-center gap-2">
          <span class="material-symbols-outlined">local_florist</span>
          Khám phá flower-bio
        </a>
        <a href="<?php echo esc_url(add_query_arg('garden', 'tung-01', home_url('/portal/kho-nong-cu-2/'))); ?>" class="text-secondary-fixed font-bold px-8 py-4 hover:underline transition-all inline-flex items-center">Bắt đầu hành trình trồng</a>
      </div>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 py-24 grid grid-cols-1 md:grid-cols-12 gap-6">
    <div class="md:col-span-7 glass-panel rounded-3xl p-10 flex flex-col justify-end min-h-[400px] relative overflow-hidden group">
      <div class="absolute top-0 right-0 p-8 opacity-20 group-hover:opacity-40 transition-opacity">
        <span class="material-symbols-outlined text-9xl text-primary" style="font-variation-settings:'FILL' 1;">family_restroom</span>
      </div>
      <div class="relative z-10">
        <span class="text-secondary-fixed font-bold tracking-widest uppercase text-xs mb-4 block">Bước 1</span>
        <h2 class="font-display text-3xl font-bold mb-4 text-primary-fixed">Dinh dưỡng có thể được thiết kế thông minh hơn</h2>
        <p class="text-on-surface-variant text-lg">Theo WHO, người trưởng thành cần tối thiểu 400 g rau và quả mỗi ngày, trong khi trẻ em cần mức phù hợp theo độ tuổi để hỗ trợ tăng trưởng và phát triển khỏe mạnh. Nghiên cứu hiện đại cũng cho thấy mô hình hiệu quả là khoảng 3 phần rau và 2 phần quả mỗi ngày.</p>
      </div>
    </div>

    <div class="md:col-span-5 bg-surface-container-low rounded-3xl p-10 flex flex-col justify-between border border-outline-variant/10">
      <div class="w-16 h-16 rounded-2xl growth-gradient flex items-center justify-center mb-8">
        <span class="material-symbols-outlined text-on-primary text-3xl">insights</span>
      </div>
      <div>
        <span class="text-tertiary font-bold tracking-widest uppercase text-xs mb-4 block">Bước 2</span>
        <h2 class="font-display text-3xl font-bold mb-4 text-on-surface">Thiết kế cơ cấu cây trồng riêng cho gia đình bạn bằng trí tuệ nhân tạo</h2>
        <p class="text-on-surface-variant">Thay vì gợi ý theo sở thích chung chung, AI của hệ thống đưa ra đề xuất dựa trên dữ liệu dinh dưỡng cần thiết cho từng thành viên trong gia đình bạn, bối cảnh mùa vụ và điều kiện vận hành thực tế của khu vườn. Nhờ đó, mỗi lựa chọn đều hướng đến sự phù hợp toàn diện: dễ triển khai, ổn định trong quá trình sinh trưởng và tối ưu thực đơn rau quả cho cả tháng mà bạn không cần đau đầu suy nghĩ xem hôm nay ăn gì.</p>
      </div>
    </div>

    <div class="md:col-span-12 glass-panel rounded-3xl p-10 flex flex-col md:flex-row items-center gap-12 border border-primary/10">
      <div class="md:w-1/2">
        <img alt="Botanical varieties" class="rounded-2xl w-full h-[300px] object-cover shadow-2xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBBIR8xbTYnCXYwmyxyoDQNgjUnShEqbg45XWb2oCl4SDhHtkclX9QYMliM1XO1MkVH7FUDmeTl1TRJo1kSUMV5KMzKnF7FJ3Jh9Endew0lpPZk2DyRsEOaVIrO2o9dKi7ACkntlHg7LFqHmMkfKaNadfF8ugz_6YHKf4YX_wOmUBqH6-3veyIz8Xw9HqdjSTGsIi5RIqmrFaYjjAHCUX4kd6X5DyP0k45XSbQAv03_YbFGuq-J8VOlOp85rcN-0o-3-ndYVW7Epu_r" />
      </div>
      <div class="md:w-1/2">
        <span class="text-secondary-fixed font-bold tracking-widest uppercase text-xs mb-4 block">Bước 3</span>
        <h2 class="font-display text-4xl font-bold mb-6">Chọn một vài loại cây để bắt đầu gieo trồng</h2>
        <p class="text-on-surface-variant mb-8 text-lg">Bạn có thể bắt đầu từ một gợi ý của AI, hoặc tự mình khám phá trong flower-bio để tìm loại cây phù hợp nhất. Mỗi lựa chọn đều là một khởi đầu riêng, dành cho nhu cầu và nhịp sống của gia đình bạn.</p>
        <div class="flex gap-4 flex-wrap">
          <a href="<?php echo esc_url(home_url('/portal/tro-ly-ai/')); ?>" class="bg-surface-container-highest p-4 rounded-xl flex items-center gap-3 hover:bg-surface-bright transition-all">
            <span class="material-symbols-outlined text-primary">auto_awesome</span>
            <span class="font-medium">AI Suggestions</span>
          </a>
          <a href="<?php echo esc_url(home_url('/portal/flower-bio/')); ?>" class="bg-surface-container-highest p-4 rounded-xl flex items-center gap-3 hover:bg-surface-bright transition-all">
            <span class="material-symbols-outlined text-secondary">eco</span>
            <span class="font-medium">Flower-bio</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-surface-container-lowest py-24">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-16">
        <h2 class="font-display text-4xl md:text-5xl font-bold mb-6">Một không gian trồng được thiết kế như một hệ điều hành sống</h2>
        <p class="text-on-surface-variant max-w-4xl mx-auto">Mỗi rack gồm 4 đến 12 khoang, và mỗi khoang là một môi trường trồng độc lập cho một loại cây, một chu kỳ phát triển, một bộ dữ liệu riêng. Toàn bộ được đồng bộ theo thời gian thực lên giao diện người dùng, từ nhiệt độ, độ ẩm không khí, độ ẩm đất, pH, EC đến quyền điều khiển máy bơm, ánh sáng và camera giám sát 24/7.</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="relative">
          <div class="absolute -inset-4 bg-primary/10 blur-3xl rounded-full"></div>
          <img alt="Hydroponic Rack System" class="relative rounded-3xl z-10 border border-outline-variant/20" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDfjmRWxPHmS1he3daWUjuRbGOs9DTH7gflMrOpoZpQp0_iPLxiVPBJDtAuYigVA25NJGcmlkx4gVrGv0jheGnkaepMKGETz66Cp8u2gFIStf-88ANcJnaw2e9ifoDOd-RIgzSt9Aq_orDP4yZG1YCPtMSdVFsprnTD5MlWLsgicnWIWUPQeAKDdfCQItz5T6UReyLNEBgB9yD4OfHk4nIh3G0ETNCBmz5ISwUP3xaIpSRSbhS0RzBA5GqJo4xx0XLMXXu3wfsT_3OD" />
        </div>
        <div class="space-y-8">
          <div class="p-8 rounded-3xl bg-surface-container-low border-l-4 border-primary">
            <h3 class="font-display text-2xl font-bold mb-4">Một rack, nhiều lớp dữ liệu sống</h3>
            <p class="text-on-surface-variant">Mỗi rack không chỉ là khung đặt cây, mà là nơi toàn bộ trạng thái vận hành được cảm biến hóa, ghi nhận và hiển thị theo thời gian thực để người dùng luôn nhìn thấy điều gì đang diễn ra trong khu vườn của mình.</p>
          </div>
          <div class="p-8 rounded-3xl bg-surface-container-low border-l-4 border-secondary">
            <h3 class="font-display text-2xl font-bold mb-4">Công nghệ để quan sát, kiểm soát và tinh chỉnh</h3>
            <p class="text-on-surface-variant">Đây không chỉ là một hệ thống trồng cây. Đó là cách công nghệ mang lại khả năng quan sát, kiểm soát và tinh chỉnh từng hành trình sinh trưởng với độ chính xác cao, trong một trải nghiệm tối giản, cao cấp và minh bạch.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 py-24">
    <div class="flex flex-col md:flex-row-reverse items-center gap-16">
      <div class="md:w-1/2">
        <img alt="Growing tray" class="rounded-full aspect-square object-cover border-8 border-surface-container-high shadow-2xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCAY32l_dIVClPY2NXV-k2CMjY7UAITLRdkEPyvN-eoNDThQRLxqwuXy003GhZDkaIS5UiA8OveaPCXmVxx1-evQToBO8z7cTLSrf6ea7cLfGBQBetWDGKcJdeSg8jzBME8ptTdjCCODQGtHFO5hNn5hM5q-XTNDEnQldt2NS31yHA6c8JpABwr1_6ZI26s7Ahg-o_mL1kDwx0m_iSTYILz80fX0n-Z2gp99rM31az_NiQzCEpggexjadZ9nSzdthKla7YLDEWy3qRQ" />
      </div>
      <div class="md:w-1/2">
        <span class="text-tertiary-fixed font-bold tracking-widest uppercase text-xs mb-4 block">Cá nhân hóa</span>
        <h2 class="font-display text-4xl font-bold mb-6">Mỗi khoang là một hành trình sống, được định danh riêng</h2>
        <p class="text-on-surface-variant text-lg leading-relaxed mb-8">Khi bạn chọn một loại cây, hệ thống sẽ khởi tạo một hành trình trồng độc lập cho chính lựa chọn đó. Mỗi khoang có thể được đặt tên riêng, để khu vườn không chỉ được vận hành bằng dữ liệu, mà còn mang dấu ấn rất riêng của từng gia đình.</p>
        <div class="flex items-center gap-4 text-primary font-bold mb-4">
          <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1;">label</span>
          <span>Định danh riêng cho từng mầm sống</span>
        </div>
        <p class="text-on-surface-variant text-lg leading-relaxed">Mỗi khoang sở hữu một hồ sơ sinh trưởng riêng, với nhật ký vận hành chi tiết và ảnh chụp tự động mỗi 4 giờ để ghi lại toàn bộ quá trình phát triển theo thời gian. Từ lớp dữ liệu liên tục này, AI Agents phân tích diễn biến sinh trưởng, nhận diện tín hiệu bất thường và đưa ra những khuyến nghị chăm sóc phù hợp, giúp cây phát triển tốt hơn trong từng giai đoạn.</p>
      </div>
    </div>
  </section>

  <section class="bg-surface-container-low py-24 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 relative z-10">
      <div class="flex flex-col md:flex-row gap-12 items-center">
        <div class="md:w-1/2 space-y-6">
          <h2 class="font-display text-4xl md:text-5xl font-bold">Theo dõi và trực tiếp tham gia chăm sóc</h2>
          <p class="text-on-surface-variant text-lg">Bạn có thể theo dõi hành trình lớn lên của từng khoang qua dashboard vườn, rồi chọn cách mình muốn đồng hành mỗi ngày.</p>
          <div class="space-y-4">
            <div class="glass-panel p-6 rounded-2xl flex items-start gap-4">
              <span class="material-symbols-outlined text-secondary text-3xl">touch_app</span>
              <div>
                <h4 class="font-bold text-on-surface">Tự tay chăm sóc</h4>
                <p class="text-sm text-on-surface-variant">Bạn có thể dùng các nút điều khiển trên dashboard để trực tiếp thao tác với từng khoang và từng rack khi muốn.</p>
              </div>
            </div>
            <div class="glass-panel p-6 rounded-2xl flex items-start gap-4 border-l-4 border-primary">
              <span class="material-symbols-outlined text-primary text-3xl">smart_toy</span>
              <div>
                <h4 class="font-bold text-on-surface">Ủy quyền cho AI</h4>
                <p class="text-sm text-on-surface-variant">Nếu muốn nhẹ nhàng hơn, bạn có thể để AI chăm sóc tự động và chỉ cần định kỳ ghé vào kiểm tra, theo dõi sự phát triển của khu vườn.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="md:w-1/2">
          <div class="bg-surface-container-lowest p-4 rounded-[40px] shadow-2xl border border-outline-variant/30">
            <div class="bg-surface-container rounded-[32px] aspect-[9/16] overflow-hidden relative">
              <div class="absolute inset-0 bg-gradient-to-b from-primary/10 to-transparent"></div>
              <div class="p-8 relative">
                <div class="flex justify-between items-center mb-10">
                  <div class="font-bold">Dashboard</div>
                  <div class="w-8 h-8 rounded-full bg-primary/20"></div>
                </div>
                <div class="space-y-6">
                  <div class="h-32 rounded-2xl bg-surface-variant/50"></div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="h-20 rounded-2xl bg-surface-variant/50"></div>
                    <div class="h-20 rounded-2xl bg-surface-variant/50"></div>
                  </div>
                  <div class="h-40 rounded-2xl bg-surface-variant/50"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-6 py-24">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
      <div class="space-y-6">
        <div class="aspect-video rounded-3xl overflow-hidden shadow-xl">
          <img alt="Harvesting" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAKN-AXZtSa7LpraXrqvYvESPtNGDeRkfQ3VGOzjZweFZ5GConH2yWE-lX08gA2Qiy0sISrhvYJQow3Wq1gQlPsjgyXmKJiyw-uh3tCfDT49gi9JS0GAyFrXWEKKn4FafPHt6TwX4HTl1tTH5vbfQHpCzb_ITVcIwLRkHZ5OISchVAL4LUdkcknr0h1zMcVL1E9MIVYzGulR9ix_9LU8YpuegzCmQCCIOhMbJTogI6YaX5d1iOSdMrYl3cS_YTdyQ18OnYhY02rTCcJ" />
        </div>
        <h3 class="font-display text-3xl font-bold">Thu hoạch và gửi về tận tay bạn</h3>
        <p class="text-on-surface-variant">Khi đến kỳ thu hoạch, đội ngũ vận hành sẽ ghi nhận cẩn thận quá trình thu hoạch, chuẩn bị sản phẩm và gửi về tận tay gia đình bạn qua dịch vụ chuyển phát.</p>
      </div>
      <div class="space-y-6">
        <div class="aspect-video rounded-3xl overflow-hidden shadow-xl">
          <img alt="New cycle" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9auxyN5vxHJWyjQW8tWUb4mIs4-7dpCQK2OIIyLEqhyqDodeQQqedxwjGm0XFDT21WibxyYyhyxhArPnkB6rs8pn_rVYRUekZOooksm9xRwwhHclXRKJwUbVxy795ljtt3LSjKBLkygpx-QlGGX1dDgDaNAMHDW4yf34X_E2pHkPIh4i9rOg_fgWENiHDrE-CbbEf8Yc6eRg0s-RL5xVoxSfyxotzaikBpS208IzmfeLmMUhv6WLOqjJ7cQnzsxiCcsZv0UdgVNL7" />
        </div>
        <h3 class="font-display text-3xl font-bold">Một hành trình mới lại bắt đầu</h3>
        <p class="text-on-surface-variant">Sau khi thu hoạch, khoang được làm mới để sẵn sàng cho một mùa tiếp theo. Nhờ vậy, khu vườn luôn có thể tiếp tục lớn lên cùng những nhu cầu mới của gia đình bạn.</p>
      </div>
    </div>
  </section>

  <section class="bg-surface-container-high py-24">
    <div class="max-w-5xl mx-auto px-6">
      <h2 class="font-display text-4xl font-bold text-center mb-16 italic">Tóm tắt hành trình xanh</h2>
      <div class="space-y-4">
        <div class="flex items-center gap-6 p-4 border-b border-outline-variant/20 group hover:bg-surface-bright transition-all rounded-xl"><span class="text-4xl font-display text-primary/30 group-hover:text-primary transition-colors">01</span><span class="text-xl font-medium">Khảo sát nhu cầu và tư vấn cá nhân hóa cùng AI</span></div>
        <div class="flex items-center gap-6 p-4 border-b border-outline-variant/20 group hover:bg-surface-bright transition-all rounded-xl"><span class="text-4xl font-display text-primary/30 group-hover:text-primary transition-colors">02</span><span class="text-xl font-medium">Thiết lập rack và chọn cây trồng phù hợp</span></div>
        <div class="flex items-center gap-6 p-4 border-b border-outline-variant/20 group hover:bg-surface-bright transition-all rounded-xl"><span class="text-4xl font-display text-primary/30 group-hover:text-primary transition-colors">03</span><span class="text-xl font-medium">Theo dõi tăng trưởng và chăm sóc theo cách bạn muốn</span></div>
        <div class="flex items-center gap-6 p-4 border-b border-outline-variant/20 group hover:bg-surface-bright transition-all rounded-xl"><span class="text-4xl font-display text-primary/30 group-hover:text-primary transition-colors">04</span><span class="text-xl font-medium">Thu hoạch và giao sản phẩm về tận tay bạn</span></div>
        <div class="flex items-center gap-6 p-4 group hover:bg-surface-bright transition-all rounded-xl"><span class="text-4xl font-display text-primary/30 group-hover:text-primary transition-colors">05</span><span class="text-xl font-medium">Làm mới khoang và bắt đầu vụ mùa tiếp theo</span></div>
      </div>
    </div>
  </section>

  <section class="py-32 px-6 relative overflow-hidden">
    <div class="absolute inset-0 z-0"><div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-primary/5 blur-[150px]"></div></div>
    <div class="relative z-10 max-w-4xl mx-auto text-center glass-panel p-16 rounded-[48px] border border-primary/20">
      <h2 class="font-display text-4xl md:text-5xl font-bold mb-8">Sẵn sàng bắt đầu một hành trình trồng phù hợp hơn cho gia đình bạn?</h2>
      <div class="flex flex-wrap justify-center gap-6">
        <a href="<?php echo esc_url(add_query_arg('garden', 'tung-01', home_url('/portal/kho-nong-cu-2/'))); ?>" class="growth-gradient text-on-primary px-10 py-5 rounded-2xl font-bold text-xl hover:scale-[1.02] transition-all shadow-xl shadow-primary/20">Bắt đầu ngay hôm nay</a>
        <a href="<?php echo esc_url(home_url('/portal/flower-bio/')); ?>" class="bg-transparent text-on-surface px-10 py-5 rounded-2xl font-bold text-xl border border-outline-variant hover:bg-surface-bright transition-all">Liên hệ tư vấn</a>
      </div>
    </div>
  </section>
</section>
