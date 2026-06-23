# Starter content — Ai trồng cây

Đây là bộ nội dung native đầu tiên để seed nhanh các page quan trọng trong WordPress.

## File hiện có

- `cach-hoat-dong.html`
- `cho-que.html`
- `an-toan-thuc-pham.html`
- `dang-ky-tu-van.html`
- `chuyen-nha-nong.html`
- `faq.html`

## Cách dùng nhanh

### Cách 1 — copy/paste vào editor page
- Tạo page đúng slug
- Chuyển editor sang Code editor hoặc Custom HTML block
- Dán nội dung từ file tương ứng

### Cách 2 — dùng với WP-CLI
- Tạo page đúng slug trước
- Dùng `wp post update <ID> --post_content="$(cat ... )"`
- Xem `../wp-cli-seed.md` để lấy lệnh mẫu

## Mục tiêu của bộ này

- không còn phụ thuộc hoàn toàn vào virtual page fallback
- có một lớp nội dung native đầu tiên để owner chỉnh ngay trong WordPress
- giữ đúng tinh thần ngắn, ấm, nhẹ, không corporate
