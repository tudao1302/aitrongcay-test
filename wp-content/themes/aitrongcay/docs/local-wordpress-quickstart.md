# Local WordPress quickstart

Mục tiêu của bước này là chạy được WordPress local với chính theme `Ai trồng cây` vừa tạo, không cần môi trường production.

## 1) Chuẩn bị một bộ WordPress local

Có thể dùng 1 trong 3 cách quen thuộc:

- LocalWP
- Laravel Herd Pro / WordPress local nếu anh đang dùng sẵn
- Docker / `wp-env` nếu muốn code-first

## 2) Chép theme vào đúng chỗ

Từ repo này, theme nằm tại:

```text
wp-content/themes/aitrongcay
```

Khi tạo site local WordPress, chỉ cần copy hoặc symlink thư mục đó vào:

```text
<wordpress-site>/wp-content/themes/aitrongcay
```

## 3) Kích hoạt theme

Trong admin WordPress:

- Appearance → Themes
- Activate `Ai trồng cây`

## 4) Tạo các page cơ bản

Tạo nhanh các page với đúng slug sau:

- Trang chủ → đặt làm static front page
- `cach-hoat-dong`
- `cho-que`
- `an-toan-thuc-pham`
- `chuyen-nha-nong`
- `faq`
- `dang-ky-tu-van`
- `onboarding`
- `dang-nhap`
- `portal`

Nếu muốn, page `portal` có thể gán template `Portal Landing`.

## 5) Vào Settings → Reading

- Homepage displays → `A static page`
- Front page → chọn `Trang chủ`

## 6) Import dần nội dung

Checkpoint hiện tại đã có:

- `front-page.php` dùng được ngay
- header/footer WordPress-ready
- assets đã copy vào theme
- docs map static → WordPress slug
- bộ starter content native đầu tiên ở `docs/starter-content/`

Việc kế tiếp không còn là copy tay từ HTML cũ nữa, mà là:
1. tạo page đúng slug
2. dán hoặc WP-CLI import starter content tương ứng
3. tinh chỉnh tiếp ngay trong WordPress editor

## 7) Gợi ý bước tiếp theo sau khi local đã lên

1. Seed nội dung cho `Chợ quê` và `Cách hoạt động`.
2. Tạo menu WordPress thật thay cho nav hardcoded fallback.
3. Kiểm tra khu vực admin đã xuất hiện post type `Leads tư vấn` và test gửi thử form `Đăng ký tư vấn`.
4. Quyết định portal là:
   - WordPress pages thuần,
   - template riêng trong theme,
   - hay app/headless shell nhúng vào WordPress.
