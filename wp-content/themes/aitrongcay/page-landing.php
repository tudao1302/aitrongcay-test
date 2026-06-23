<?php
/*
Template Name: Landing Blank
*/
declare(strict_types=1);
if (! defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>

<html class="dark" lang="vi"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>AI Trồng Cây | Hệ Sinh Thái Nông Nghiệp Thông Minh</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif:wght@400;700;900&amp;family=Manrope:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary-fixed-dim": "#ffb68c",
                        "on-secondary-container": "#725f00",
                        "surface-container": "#1e201d",
                        "surface-bright": "#383a36",
                        "outline": "#87948b",
                        "on-tertiary-fixed-variant": "#753401",
                        "on-secondary": "#3a3000",
                        "on-error-container": "#ffdad6",
                        "surface-container-high": "#292b27",
                        "on-primary-fixed-variant": "#005236",
                        "background": "#121411",
                        "secondary": "#fff9ef",
                        "on-tertiary-fixed": "#321200",
                        "primary-container": "#31a375",
                        "surface-variant": "#333532",
                        "error": "#ffb4ab",
                        "surface-tint": "#6fdba8",
                        "surface-container-low": "#1a1c19",
                        "secondary-fixed-dim": "#e9c400",
                        "outline-variant": "#3e4942",
                        "inverse-on-surface": "#2f312e",
                        "on-tertiary-container": "#481d00",
                        "on-tertiary": "#532200",
                        "on-primary": "#003824",
                        "on-error": "#690005",
                        "primary-fixed-dim": "#6fdba8",
                        "on-background": "#e3e3de",
                        "inverse-surface": "#e3e3de",
                        "on-primary-container": "#00311f",
                        "tertiary": "#ffb68c",
                        "secondary-fixed": "#ffe16d",
                        "on-surface": "#e3e3de",
                        "tertiary-fixed": "#ffdbc9",
                        "primary": "#6fdba8",
                        "inverse-primary": "#006c49",
                        "surface-container-highest": "#333532",
                        "tertiary-container": "#d07b46",
                        "on-primary-fixed": "#002113",
                        "surface-dim": "#121411",
                        "surface": "#121411",
                        "surface-container-lowest": "#0d0f0c",
                        "on-secondary-fixed-variant": "#544600",
                        "on-secondary-fixed": "#221b00",
                        "error-container": "#93000a",
                        "secondary-container": "#ffdb3c",
                        "on-surface-variant": "#bdcac0",
                        "primary-fixed": "#8bf8c3"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Noto Serif"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    }
                },
            },
        }
    </script>
<style>
        body { font-family: 'Manrope', sans-serif; background-color: #121411; color: #e3e3de; }
        .font-serif { font-family: 'Noto Serif', serif; }
        .glass-panel { background: rgba(51, 53, 50, 0.4); backdrop-filter: blur(20px); }
        .growth-gradient { background: linear-gradient(135deg, #31a375 0%, #6fdba8 100%); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    </style>
</head>
<body class="overflow-x-hidden">
<!-- TopNavBar -->
<nav class="fixed top-0 w-full z-50 bg-emerald-950/40 backdrop-blur-xl shadow-[0_20px_40px_rgba(0,0,0,0.08)]">
<div class="flex justify-between items-center px-12 py-6 max-w-screen-2xl mx-auto">
<div class="text-2xl font-['Noto_Serif'] font-black text-emerald-400 tracking-tight">AI Trồng Cây</div>
<div class="hidden md:flex gap-8 font-['Noto_Serif'] font-medium text-lg">
<a class="text-emerald-300 border-b-2 border-emerald-400 pb-1 hover:text-emerald-300 transition-colors duration-300" href="#">Our Vision</a>
<a class="text-emerald-100/70 hover:text-emerald-300 transition-colors duration-300" href="#">Technician</a>
<a class="text-emerald-100/70 hover:text-emerald-300 transition-colors duration-300" href="#">Growth Lab</a>
<a class="text-emerald-100/70 hover:text-emerald-300 transition-colors duration-300" href="#">Partners</a>
</div>
<button class="growth-gradient text-on-primary-container px-6 py-2 rounded-xl font-bold scale-102 hover:opacity-90 transition-all">Launch Command</button>
</div>
</nav>
<!-- 1. Hero Section -->
<section class="relative min-h-screen flex items-center pt-24 overflow-hidden">
<div class="absolute inset-0 z-0">
<div class="absolute top-[-10%] right-[-10%] w-[600px] height-[600px] bg-primary/10 blur-[120px] rounded-full"></div>
<div class="absolute bottom-[-10%] left-[-10%] w-[500px] height-[500px] bg-tertiary/5 blur-[100px] rounded-full"></div>
</div>
<div class="container mx-auto px-6 relative z-10 grid lg:grid-cols-2 gap-12 items-center">
<div class="space-y-8">
<span class="inline-block px-4 py-1 rounded-full border border-primary/30 text-primary text-sm font-bold tracking-widest uppercase">Kỷ Nguyên Nông Nghiệp Số</span>
<h1 class="text-6xl md:text-7xl font-serif font-black leading-tight text-on-background">
                    Một khu vườn số <br/><span class="text-secondary-fixed">cho gia đình</span>
</h1>
<p class="text-xl text-on-surface-variant max-w-lg leading-relaxed">
                    Theo dõi từng mầm xanh qua webcam AI, nhận thực phẩm organic sạch tận cửa. Trải nghiệm cảm giác làm nông hiện đại ngay trên điện thoại.
                </p>
<div class="flex flex-wrap gap-4">
<button class="growth-gradient text-on-primary font-bold py-4 px-8 rounded-xl scale-102 hover:opacity-90 transition-all flex items-center gap-2">
                        Đăng ký trải nghiệm
                        <span class="material-symbols-outlined">arrow_forward</span>
</button>
<button class="bg-surface-container-high hover:bg-surface-container-highest text-on-surface font-bold py-4 px-8 rounded-xl transition-all border border-outline-variant/30">
                        Xem cách hoạt động
                    </button>
</div>
</div>
<div class="relative">
<div class="glass-panel p-4 rounded-[2rem] border border-outline-variant/20 shadow-2xl relative overflow-hidden">
<img class="rounded-[1.5rem] w-full aspect-video object-cover" data-alt="a high-tech gardening dashboard showing real-time webcam feed of leafy greens and growth data charts in a dark mode ui" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQppCrvrbMH1l9TTQ3j1dhCRGzwLBdO0eUS8wHrMXUtqiv11UX61v4PEglLE6VXjemNBqQ2z8t4jier6gKdsKg3cNX0x9Iw_Ul1alybHIRA9rma2R026Wprg2-jI8qff48j2G0ezDyiAyHeaRY89t5nIdYWEZ7tY02_oi3MJSBB56olwOoxusQ-3n9wihc3UggokxamSZbVh8exTQS987Ja3b6w1YASLAZjiLyl94E__1Pmt7cSmEV7ri7J9uwmbEIxEGpk9NIcS4"/>
<div class="absolute top-8 right-8 bg-error/80 text-white px-3 py-1 rounded-lg flex items-center gap-2 animate-pulse">
<span class="w-2 h-2 bg-white rounded-full"></span>
<span class="text-xs font-bold">LIVE FEED</span>
</div>
</div>
<div class="absolute -bottom-6 -left-6 glass-panel p-6 rounded-2xl border border-primary/20 shadow-xl hidden md:block">
<div class="flex items-center gap-4">
<div class="p-3 bg-primary-container/20 rounded-full text-primary">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">eco</span>
</div>
<div>
<div class="text-sm text-on-surface-variant">Độ ẩm đất</div>
<div class="text-2xl font-bold text-primary">82%</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 2. Vấn đề (Pain Points) -->
<section class="py-24 bg-surface-container-low">
<div class="container mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-serif font-bold text-on-surface mb-4">Bạn có đang lo lắng về bữa ăn gia đình?</h2>
<div class="w-24 h-1 growth-gradient mx-auto rounded-full"></div>
</div>
<div class="grid md:grid-cols-3 gap-8">
<div class="p-8 rounded-3xl bg-surface-container-lowest border border-outline-variant/10 hover:border-primary/30 transition-all">
<span class="material-symbols-outlined text-4xl text-error mb-6">warning</span>
<h3 class="text-xl font-bold mb-4">Nguồn gốc mập mờ</h3>
<p class="text-on-surface-variant">Khó kiểm chứng thực phẩm "sạch" tại siêu thị hay chợ truyền thống.</p>
</div>
<div class="p-8 rounded-3xl bg-surface-container-lowest border border-outline-variant/10 hover:border-primary/30 transition-all">
<span class="material-symbols-outlined text-4xl text-tertiary mb-6">timer_off</span>
<h3 class="text-xl font-bold mb-4">Không có thời gian</h3>
<p class="text-on-surface-variant">Muốn tự trồng nhưng công việc bận rộn không cho phép chăm sóc hàng ngày.</p>
</div>
<div class="p-8 rounded-3xl bg-surface-container-lowest border border-outline-variant/10 hover:border-primary/30 transition-all">
<span class="material-symbols-outlined text-4xl text-secondary-fixed mb-6">distance</span>
<h3 class="text-xl font-bold mb-4">Mất kết nối với thiên nhiên</h3>
<p class="text-on-surface-variant">Con trẻ lớn lên trong thành phố thiếu trải nghiệm về sự phát triển của cây cối.</p>
</div>
</div>
</div>
</section>
<!-- 3. Giải pháp (Solution) -->
<section class="py-24">
<div class="container mx-auto px-6">
<div class="grid lg:grid-cols-2 gap-16 items-center">
<div>
<h2 class="text-5xl font-serif font-bold leading-tight mb-8">AI Trồng Cây: <br/><span class="text-primary">Khu vườn trong tầm tay</span></h2>
<p class="text-lg text-on-surface-variant mb-12">Chúng tôi kết hợp công nghệ AI và mô hình nông nghiệp bền vững để mang cả nông trại về chiếc điện thoại của bạn.</p>
<div class="grid grid-cols-2 gap-6">
<div class="p-6 rounded-2xl bg-surface-container-high">
<span class="material-symbols-outlined text-primary mb-2">videocam</span>
<div class="font-bold">Webcam 24/7</div>
<div class="text-sm text-on-surface-variant">Quan sát cây lớn từng ngày</div>
</div>
<div class="p-6 rounded-2xl bg-surface-container-high">
<span class="material-symbols-outlined text-primary mb-2">menu_book</span>
<div class="font-bold">Nhật ký số</div>
<div class="text-sm text-on-surface-variant">Lịch sử chăm sóc minh bạch</div>
</div>
<div class="p-6 rounded-2xl bg-surface-container-high">
<span class="material-symbols-outlined text-primary mb-2">monitoring</span>
<div class="font-bold">Dữ liệu thực</div>
<div class="text-sm text-on-surface-variant">Chỉ số đất, nước, dinh dưỡng</div>
</div>
<div class="p-6 rounded-2xl bg-surface-container-high">
<span class="material-symbols-outlined text-primary mb-2">person_check</span>
<div class="font-bold">Hồ sơ kỹ sư</div>
<div class="text-sm text-on-surface-variant">Biết rõ ai đang chăm sóc vườn</div>
</div>
</div>
</div>
<div class="relative">
<img class="rounded-[3rem] w-full object-cover shadow-2xl border border-outline-variant/20" data-alt="close-up of a hand holding a sleek smartphone displaying a plant growth app in front of a blurred lush greenhouse background" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAD6CbVGNhb1JDi9YEHANWjWa1hCoT3K31QaaXSDA0wwo9ybun8VUft56FLSRuzjWXOcg00KCydfMbDWrJ2NzFNcI7jc1VGGc6JIhQWMKyPb_ApYaxjiVBvXwGXCtp-KgKIvfe-eycj6wpxGCTBgMZRSSEzNGH8SzwZrbQlyNJEwGMmMMR0vYDwdTGnXso3lsP34WArHuYtyWnUoLBr_OmqWLzrnITyRh6Gr-3TLWnkYrDnabmSl1wFspYqIVksN6P9dwAilB0ut2Q"/>
<div class="absolute -top-10 -right-10 w-40 h-40 bg-secondary-fixed/20 blur-3xl rounded-full"></div>
</div>
</div>
</div>
</section>
<!-- 4. Lợi ích (Benefits) -->
<section class="py-24 bg-surface-container-lowest">
<div class="container mx-auto px-6">
<div class="text-center mb-20">
<h2 class="text-4xl font-serif font-bold">Giá trị vượt xa thực phẩm</h2>
</div>
<div class="grid md:grid-cols-3 gap-12">
<!-- Benefit 1 -->
<div class="text-center space-y-6">
<div class="aspect-square rounded-full bg-primary/10 flex items-center justify-center p-12 mx-auto">
<img class="w-full h-full object-contain" data-alt="stylized 3D icon of a wooden crate filled with vibrant organic vegetables with a soft atmospheric glow" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB4Hz3FY7PCFV31xQk6GA9cNdf2ocN9XsVLbhq9VKF-bPnZg1BipVZde-IAhbr-c__QaihXrMI1jgZqbGzANCisMpzv-GxMQseC6zVaX4EsmHcZJVaF76xo3GldQbaHW4wmavNs1hp6BhmskC3Pwxf0unEURNpA14Da_VY1gujs2wVAhcedmE53LRjyixN5afZKk4h9bksey_5J65fyEmgreBX4U3QvsmeP7E2LwjH9tyyhMa0iq6n2vClxRs-6hfVVHSoLcI9o1rc"/>
</div>
<h3 class="text-2xl font-serif font-bold text-primary">Bữa ăn an tâm</h3>
<p class="text-on-surface-variant">Thực phẩm tươi sống, không hóa chất, thu hoạch và giao ngay trong ngày.</p>
</div>
<!-- Benefit 2 -->
<div class="text-center space-y-6">
<div class="aspect-square rounded-full bg-secondary-fixed/10 flex items-center justify-center p-12 mx-auto">
<img class="w-full h-full object-contain" data-alt="stylized 3D icon of a happy family silhouettes interacting with a large digital sprout, soft yellow and green lighting" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDF_3f7quUyit7I4PyYA3Ln_mq45wjS_VUBtoTE3Vh0pWKHh7Gq8ee3pddhTIUxJOS20oLqBOJ2hq31ORsdFhZhlXYJ7N-XPvUVxCh3Y177FZRtabwJ01naUJKGuv07awUMA_VqhCW6UjnjP_AnopDT9KHjtXFzPb2wIjRmIiN3ruN2-RvBCd6BO1Ebr8AFAv99OxMo0KD9f-yICov8P-iD6M6MknyDFCshqmsONMcgoNbVjJmT7ToxeasY8_StGTqoDQOj-HBRGCo"/>
</div>
<h3 class="text-2xl font-serif font-bold text-secondary-fixed">Gia đình gắn kết</h3>
<p class="text-on-surface-variant">Hoạt động ngoại khóa thú vị cho trẻ em khi cùng ba mẹ theo dõi khu vườn số.</p>
</div>
<!-- Benefit 3 -->
<div class="text-center space-y-6">
<div class="aspect-square rounded-full bg-tertiary/10 flex items-center justify-center p-12 mx-auto">
<img class="w-full h-full object-contain" data-alt="stylized 3D icon of a yoga pose surrounded by floating green leaves, symbolizing healthy modern lifestyle" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAPkvkR49h0mgeVqTkNgneag5-VA0HW0uPnzwNS-6zBUpoonJocD_AxM8QJ1fd6Fcmg67n6J5yuVJIUAg-OUiVRLmcB2FHjlAaGmvxLoodd9zhTqekvexpTq-Ufoxy55xdrjs_xt1TXn5ZNuRJGPLzJlOOT4_Hvl3ypQqMs9e4GgUlKC64WThk_4bcZc3Loj95CPMYwNaD5yvjNgymBfWPNYomnYhcER-NFS7IfJSVgH2pQKPEDL8rWQasSRKhwasmWOr-sr_X9ryE"/>
</div>
<h3 class="text-2xl font-serif font-bold text-tertiary">Lối sống xanh</h3>
<p class="text-on-surface-variant">Góp phần bảo vệ môi trường và định hình phong cách sống hiện đại, bền vững.</p>
</div>
</div>
</div>
</section>
<!-- 5. Cách hoạt động (Process) -->
<section class="py-24">
<div class="container mx-auto px-6">
<h2 class="text-4xl font-serif font-bold text-center mb-20">Hành trình sở hữu khu vườn số</h2>
<div class="grid md:grid-cols-4 gap-4 relative">
<div class="hidden md:block absolute top-1/2 left-0 w-full h-[2px] bg-outline-variant/30 -z-10"></div>
<div class="space-y-6 text-center">
<div class="w-16 h-16 rounded-full growth-gradient mx-auto flex items-center justify-center text-on-primary font-black text-xl border-4 border-background">1</div>
<div class="glass-panel p-6 rounded-2xl">
<h4 class="font-bold mb-2">Đăng ký</h4>
<p class="text-sm text-on-surface-variant">Chọn gói vườn phù hợp với quy mô gia đình.</p>
</div>
</div>
<div class="space-y-6 text-center">
<div class="w-16 h-16 rounded-full bg-surface-container-high mx-auto flex items-center justify-center text-on-surface font-black text-xl border-4 border-background">2</div>
<div class="glass-panel p-6 rounded-2xl">
<h4 class="font-bold mb-2">Tạo tài khoản</h4>
<p class="text-sm text-on-surface-variant">Thiết lập hồ sơ vườn và chọn loại cây mong muốn.</p>
</div>
</div>
<div class="space-y-6 text-center">
<div class="w-16 h-16 rounded-full bg-surface-container-high mx-auto flex items-center justify-center text-on-surface font-black text-xl border-4 border-background">3</div>
<div class="glass-panel p-6 rounded-2xl">
<h4 class="font-bold mb-2">Theo dõi</h4>
<p class="text-sm text-on-surface-variant">Xem live camera và tương tác với kỹ sư nông nghiệp.</p>
</div>
</div>
<div class="space-y-6 text-center">
<div class="w-16 h-16 rounded-full bg-surface-container-high mx-auto flex items-center justify-center text-on-surface font-black text-xl border-4 border-background">4</div>
<div class="glass-panel p-6 rounded-2xl">
<h4 class="font-bold mb-2">Nhận thành quả</h4>
<p class="text-sm text-on-surface-variant">Thực phẩm sạch được giao tận nhà sau thu hoạch.</p>
</div>
</div>
</div>
</div>
</section>
<!-- 6. Đối tượng (Target Audience) -->
<section class="py-24 bg-surface-container-low">
<div class="container mx-auto px-6">
<h2 class="text-4xl font-serif font-bold text-center mb-16">Mô hình này phù hợp với ai?</h2>
<div class="grid md:grid-cols-2 gap-12 max-w-5xl mx-auto">
<div class="p-10 rounded-[2rem] bg-primary/5 border border-primary/20">
<h3 class="text-2xl font-bold text-primary mb-8 flex items-center gap-3">
<span class="material-symbols-outlined">check_circle</span> Phù hợp
                    </h3>
<ul class="space-y-6">
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary">done</span>
<span class="text-on-surface-variant">Gia đình có trẻ nhỏ cần nguồn thực phẩm tuyệt đối an toàn.</span>
</li>
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary">done</span>
<span class="text-on-surface-variant">Người yêu thiên nhiên nhưng sống tại căn hộ diện tích hẹp.</span>
</li>
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-primary">done</span>
<span class="text-on-surface-variant">Người bận rộn muốn ăn sạch nhưng không có thời gian làm nông.</span>
</li>
</ul>
</div>
<div class="p-10 rounded-[2rem] bg-surface-container-highest border border-outline-variant/20 opacity-80">
<h3 class="text-2xl font-bold text-on-surface mb-8 flex items-center gap-3">
<span class="material-symbols-outlined">cancel</span> Không phù hợp
                    </h3>
<ul class="space-y-6">
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-outline">close</span>
<span class="text-on-surface-variant">Người muốn tự tay cầm xẻng, bón phân mỗi ngày tại nhà.</span>
</li>
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-outline">close</span>
<span class="text-on-surface-variant">Người ưu tiên giá rẻ hơn là nguồn gốc và chất lượng minh bạch.</span>
</li>
<li class="flex items-start gap-4">
<span class="material-symbols-outlined text-outline">close</span>
<span class="text-on-surface-variant">Người không có thói quen sử dụng ứng dụng công nghệ.</span>
</li>
</ul>
</div>
</div>
</div>
</section>
<!-- 7. Bằng chứng (Proof/Social Proof) -->
<section class="py-24">
<div class="container mx-auto px-6">
<h2 class="text-4xl font-serif font-bold text-center mb-16">Niềm tin đến từ những gì có thể kiểm tra</h2>
<div class="grid lg:grid-cols-3 gap-8">
<div class="lg:col-span-2 glass-panel p-6 rounded-[2rem] border border-outline-variant/10">
<img class="rounded-2xl w-full mb-6" data-alt="screenshot of a professional farm management app showing multi-cam grid views of various crops and detailed growth metrics" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAwxT3YPjvsJ70zt6NHY-vfilw8WefBGWqbeitK3dBbUiuY0d-dKeZSayFievAEpvzd3AqpwiAFWqjXaWAemOMJkToV2e9bVtbFsvUhE3BCweLJIQSWOC4GS7b3R9xu9WPFyxAFBTknPmmMQFFX4sdZbcpSsSDnZ5x9p2-7_H4_IwbA58yd-lqtrz8iXcfdjpEsMx5qcSR_nSLObzHAbDPZprDL9-jbY4z-yib662z6SIlKEcCCovWGjeefBacxN8LE5ExBQO6gDRE"/>
<div class="grid grid-cols-3 gap-4">
<img class="rounded-xl aspect-square object-cover" data-alt="real-time camera shot of healthy kale leaves growing in a hydroponic setup" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDoyjWVP0eas1V5oRtEtdvmQ5P8LJL-926SqpYJ1MwDupKDog2IdbAslVj1K8W9YRMTbXagqtGAtkIThNTrLORlKh34vOM5taEJSzdnykOeAZtiljptUZuKcQiQkD8QtvoDckjCqM3yG-bnnPxIS_y8lu4AbjVORnwiQ-9-r_oQBoZi7wWeKg4XghAINJX_N4vuyEEzTjARq3JPCldZG3Ui5pRY_49viN5Eb6X1RcOzSC9loHIPaGUt1ll86mVP6RXE-p_sEQxgfe8"/>
<img class="rounded-xl aspect-square object-cover" data-alt="macro photo of small tomato fruits on a vine in a controlled environment" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDkey4tFQnFQIHHz36-KpgGTLDP7M_K0Yy-Wn-M3glYHmJunuyI8xHyVyKuzkKk_WY2ZtdrEfdwk01ilA2IDmSEoBNJvOxKwm-ka8tMUMHePtuGn2tX84uq1yuac2O-UNKyqEUDZ81vvxpwGo7o6VmwsA-F9Z5X1JuQLwXTvbUHjvPEWsv40gRFiJYwI0OwXKNMIsPWswgBU4CkeUsvrNS-OcaXpLY9R-gT8kR3oZWqkP9-oWkS21YhvVrvPnEZpj8fnLrupl4gM-w"/>
<img class="rounded-xl aspect-square object-cover" data-alt="technician in clean gardening gear checking data on a tablet in a greenhouse" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBprd_Ky-dFd5Xevo5g_LNNjmTfm7Isj8BmdSnTe1vyfR5UFaJZXdJ71W0pUoOCEJQckt5iips1OmsyvAhMJ5nRv7UYRpiPa_G4PPiclzHDY6DFK4WyLnMyZC8mk7jaUiTDHfnC1suvBGbDREJzTiO-Oi87HFbsls4-i9lMIjBGsPaIeLd2KMEc5V1HAYHvm6U_S92YtMNqenY-Nn7V15LRA2blR05A0gnhxdkn4OLrHpFLqC5Oc-M8twq6uQZusPsRULkiwsjLJdk"/>
</div>
</div>
<div class="space-y-6">
<div class="p-8 rounded-3xl bg-surface-container-high border border-outline-variant/20 italic text-on-surface-variant relative">
<span class="material-symbols-outlined absolute top-4 right-4 text-primary opacity-30 text-5xl">format_quote</span>
                        "Từ khi dùng AI Trồng Cây, mình không còn phải lo lắng rau này phun thuốc gì nữa. Các con cũng hào hứng xem camera đợi ngày rau lớn."
                        <div class="mt-6 flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-secondary-fixed/20 overflow-hidden">
<img class="w-full h-full object-cover" data-alt="portrait of a smiling young mother in a modern home environment" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDXCac9xjogPuv0KObu3I_-31mO45mbJkA9WATO4yIS_x5C5qaWzThfmlT7rj2yirLrvL0VfSASt07BGnhLL8nDUQgPFljEoSoUATOLaii_eDSvawJ27s385pDR7KmtFgmM9SCGmbPR6s9ml-qD7CQ33PZ2LL0j6kRGWGAl0UmJB9CiAFP8DzaNTRs4VBM8rYt2anGCUOltg0zm851nNO4Gn9nbbMQ_H-XIsurtfZCWuaETB-OTYAZ9L9CmNkj-1_5E5LGHMQ0_2ec"/>
</div>
<div>
<div class="font-bold not-italic">Chị Thu Trang</div>
<div class="text-xs not-italic">Quận 2, TP.HCM</div>
</div>
</div>
</div>
<div class="p-8 rounded-3xl bg-surface-container-high border border-outline-variant/20 italic text-on-surface-variant relative">
<span class="material-symbols-outlined absolute top-4 right-4 text-primary opacity-30 text-5xl">format_quote</span>
                        "Dịch vụ chuyên nghiệp, ứng dụng mượt mà. Cảm giác như mình thực sự có một nông trại riêng dù chỉ sống ở chung cư."
                        <div class="mt-6 flex items-center gap-4">
<div class="w-12 h-12 rounded-full bg-primary/20 overflow-hidden">
<img class="w-full h-full object-cover" data-alt="portrait of a professional looking man in his 40s with a warm expression" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCJErHzTzNUjRhrErlR4WJNe64GZ-MdToKa2Y4fEyJqic4eD9DYCtDpmjBkaURfXg7TJ6xwb7F75PAzAw-UMeC0V7YBYMyYkQAfuai5O33pnmLZoLH_95BUW7bz-uY6fYEfadfmqvtrW9NzrCmlLuRY1kelYD9kJfmgECIuhWCBItMdhSI86Yd4NQ50tRyOgTct45MY89vCo9nlIGnq66LPtMOPGYUBFo12GyvfyWuxM1eQgb6lsWTBl7NnHKOUKIt1vE0CHo0X27A"/>
</div>
<div>
<div class="font-bold not-italic">Anh Minh Quân</div>
<div class="text-xs not-italic">Hà Đông, Hà Nội</div>
</div>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- 8. Hệ sinh thái (Ecosystem) -->
<section class="py-24 bg-surface-container-lowest">
<div class="container mx-auto px-6">
<h2 class="text-4xl font-serif font-bold text-center mb-16">Hơn cả một khu vườn</h2>
<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
<div class="p-8 rounded-[2rem] bg-surface-container-low border border-outline-variant/10 text-center group hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined text-5xl text-primary mb-4 group-hover:scale-110 transition-transform">storefront</span>
<h4 class="text-xl font-bold mb-2">Chợ Quê</h4>
<p class="text-sm text-on-surface-variant">Trao đổi nông sản sạch giữa các chủ vườn.</p>
</div>
<div class="p-8 rounded-[2rem] bg-surface-container-low border border-outline-variant/10 text-center group hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined text-5xl text-secondary-fixed mb-4 group-hover:scale-110 transition-transform">auto_stories</span>
<h4 class="text-xl font-bold mb-2">Chuyện Nhà Nông</h4>
<p class="text-sm text-on-surface-variant">Kiến thức nông nghiệp và cảm hứng sống xanh.</p>
</div>
<div class="p-8 rounded-[2rem] bg-surface-container-low border border-outline-variant/10 text-center group hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined text-5xl text-tertiary mb-4 group-hover:scale-110 transition-transform">smart_toy</span>
<h4 class="text-xl font-bold mb-2">Trợ Lý AI</h4>
<p class="text-sm text-on-surface-variant">Tư vấn dinh dưỡng và gợi ý thực đơn từ vườn.</p>
</div>
<div class="p-8 rounded-[2rem] bg-surface-container-low border border-outline-variant/10 text-center group hover:bg-surface-container-high transition-all">
<span class="material-symbols-outlined text-5xl text-primary mb-4 group-hover:scale-110 transition-transform">groups</span>
<h4 class="text-xl font-bold mb-2">Cộng Đồng</h4>
<p class="text-sm text-on-surface-variant">Kết nối những gia đình yêu thích lối sống lành mạnh.</p>
</div>
</div>
</div>
</section>
<!-- 9. FAQ -->
<section class="py-24">
<div class="container mx-auto px-6 max-w-3xl">
<h2 class="text-4xl font-serif font-bold text-center mb-16">Câu hỏi thường gặp</h2>
<div class="space-y-4">
<div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10">
<button class="w-full flex justify-between items-center text-left font-bold">
                        Tôi có thể chọn loại cây muốn trồng không?
                        <span class="material-symbols-outlined">expand_more</span>
</button>
<p class="mt-4 text-on-surface-variant text-sm">Hoàn toàn có thể. Chúng tôi có danh mục các loại rau củ quả theo mùa để bạn lựa chọn cho khu vườn của mình.</p>
</div>
<div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10">
<button class="w-full flex justify-between items-center text-left font-bold">
                        Nếu cây bị sâu bệnh thì sao?
                        <span class="material-symbols-outlined">expand_more</span>
</button>
<p class="mt-4 text-on-surface-variant text-sm">Các kỹ sư của chúng tôi theo dõi 24/7. Nếu có vấn đề, AI sẽ cảnh báo và kỹ sư sẽ xử lý bằng phương pháp hữu cơ ngay lập tức.</p>
</div>
<div class="bg-surface-container p-6 rounded-2xl border border-outline-variant/10">
<button class="w-full flex justify-between items-center text-left font-bold">
                        Chi phí vận chuyển rau về nhà được tính thế nào?
                        <span class="material-symbols-outlined">expand_more</span>
</button>
<p class="mt-4 text-on-surface-variant text-sm">Tùy gói đăng ký, chi phí vận chuyển có thể được bao gồm sẵn hoặc tính theo khoảng cách thực tế từ nông trại gần nhất.</p>
</div>
</div>
</div>
</section>
<!-- 10. Final CTA -->
<section class="py-24 relative overflow-hidden">
<div class="absolute inset-0 bg-primary/10 -z-10 blur-[150px]"></div>
<div class="container mx-auto px-6 text-center max-w-4xl space-y-12">
<h2 class="text-5xl md:text-6xl font-serif font-black leading-tight">
                Bắt đầu từ một khu vườn <br/><span class="text-primary">rõ ràng hơn cho gia đình</span>
</h2>
<p class="text-xl text-on-surface-variant">Tham gia cùng hàng nghìn gia đình đang kiến tạo tương lai thực phẩm sạch ngay hôm nay.</p>
<div class="flex flex-wrap justify-center gap-6">
<button class="growth-gradient text-on-primary font-black py-5 px-12 rounded-2xl text-xl shadow-lg shadow-primary/20 hover:scale-105 transition-all">
                    Đăng ký ngay
                </button>
<button class="bg-surface-bright text-on-surface font-bold py-5 px-12 rounded-2xl text-xl border border-outline-variant/30 hover:bg-surface-variant transition-all">
                    Tư vấn miễn phí
                </button>
</div>
<div class="flex items-center justify-center gap-8 pt-12 opacity-60 grayscale">
<img alt="VietGAP Logo" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAieIF9AXiZ4Yv0gH9FiDpRQg-e8uhxB28P9Xn69mVgRFc0onRwCOthOvSegH52-Yr7_E5lPUt9Pu8VM_TTciC2M00-ZtfUlc8-NVZMt7710qfUnXkZORu_L6ZFXlFegk8f9gEO0ToIt7_ATDTbm9d4JLqAgDXXzpxI2P3FWtB_az89kw9_-AfiGjGVXSEtPJ_5SLDB620Sk6n2GZZ8I_GxjPR78kzA5L14bgQfHrC784_TxqW6EzzNN-T7OTzfmrV2yl2L3QEttFg"/>
<img alt="Organic Certified" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAf-ZljAmE16WG9_15aGgumQzpeASP4tL-d2IhNcgkJ8gK-WW3GNoMgUerTVf_rrrM2t3wFGUyC9zNIHrdY-gSurFKivbBvegOVeIgPvMON3ymnaK2IxjsHCC0iGZJJOuuxZA6yu-CPebb-aI7jgZ-9AW3gyBMLoHSP0pozns6CSumtjQiGNYX_vF-uVXSZYNZ235WNKXVPDpCBbSA1Ycmyu66_JIf4ZC3N5ujUmmY4fK7YpsfW2JM9aWGi88w21RH5WSu_SLPKyH8"/>
<img alt="AI Technology Partnership" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDhPeaNvW4W5pd4eja8mL1Y9yu3haJVNfKtKtL14Ii3dApuNulUMNom5abCEwrkgGqpQsY005JwUAHaX29bZ9VbASryPAuL7aPb-VwDcgqBbo8mB1tdHL7SqzrfIzTnYvMh83Sd4cDmwm4t2hSrwxEecA2OsF88r0SdE-UbvzgGDXtLIIGBECGHlIzYPz13hrudnfK7vVR3byjXlNbY-bPyPFZg5fIS7jeTEDCLQ6bHWeDfhGn4nhx5tdT_nUrM5_dT0r-duPo46qw"/>
</div>
</div>
</section>
<!-- Footer -->
<footer class="w-full rounded-t-[2rem] mt-24 bg-[#121411] tonal-transition bg-emerald-950/20">
<div class="grid grid-cols-1 md:grid-cols-4 gap-12 px-16 py-20 max-w-screen-2xl mx-auto">
<div class="space-y-6">
<div class="text-xl font-['Noto_Serif'] font-bold text-emerald-400">AI Trồng Cây</div>
<p class="text-emerald-100/50 text-sm font-['Manrope']">Hệ sinh thái nông nghiệp thông minh, kết nối gia đình hiện đại với thiên nhiên qua công nghệ AI hàng đầu.</p>
</div>
<div>
<h5 class="font-['Manrope'] text-sm uppercase tracking-widest text-emerald-400 mb-6 font-bold">Khám phá</h5>
<ul class="space-y-4 font-['Manrope'] text-sm uppercase tracking-widest text-emerald-100/50">
<li><a class="hover:text-yellow-200 transition-all" href="#">Impact Report</a></li>
<li><a class="hover:text-yellow-200 transition-all" href="#">Documentation</a></li>
<li><a class="hover:text-yellow-200 transition-all" href="#">Media Kit</a></li>
</ul>
</div>
<div>
<h5 class="font-['Manrope'] text-sm uppercase tracking-widest text-emerald-400 mb-6 font-bold">Chính sách</h5>
<ul class="space-y-4 font-['Manrope'] text-sm uppercase tracking-widest text-emerald-100/50">
<li><a class="hover:text-yellow-200 transition-all" href="#">Privacy Policy</a></li>
<li><a class="hover:text-yellow-200 transition-all" href="#">Service Terms</a></li>
</ul>
</div>
<div class="space-y-6">
<h5 class="font-['Manrope'] text-sm uppercase tracking-widest text-emerald-400 mb-6 font-bold">Bản tin</h5>
<div class="flex gap-2">
<input class="bg-surface-container-high border-none rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 w-full" placeholder="Email của bạn" type="email"/>
<button class="bg-emerald-500 p-2 rounded-xl text-on-primary">
<span class="material-symbols-outlined">send</span>
</button>
</div>
</div>
</div>
<div class="text-center pb-12 border-t border-outline-variant/10 pt-8">
<div class="font-['Manrope'] text-sm uppercase tracking-widest text-emerald-100/50">© 2024 AI Trồng Cây. Engineered for the Earth.</div>
</div>
</footer>
</body></html>