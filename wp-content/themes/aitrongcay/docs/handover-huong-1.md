# Bàn giao hướng 1 — Ai trồng cây WordPress foundation

Mục tiêu của tài liệu này là giúp dev/owner cầm theme hiện tại lên và hoàn tất checkpoint **"xong hướng 1"** nhanh nhất có thể.

## 1) Trạng thái bàn giao hiện tại

Theme hiện tại đã có:
- public site chạy theo WordPress theme
- portal shell chạy bằng virtual routes
- starter content native đầu tiên cho các page public quan trọng
- form `Đăng ký tư vấn` dạng WordPress-native
- lưu lead nội bộ về admin qua post type `Leads tư vấn`
- docs seed/test/migration tương đối đầy đủ

Version hiện tại:
- `0.3.2`

## 2) Định nghĩa "xong hướng 1"

Được coi là xong khi đủ các điều kiện sau trên một site WordPress đang chạy:
1. theme `aitrongcay` được activate thành công
2. public routes mở được, không 404
3. portal routes mở được, không 404
4. page native và virtual fallback không đè nhau
5. starter content đã được import ít nhất cho các page public chính
6. form `Đăng ký tư vấn` gửi thành công
7. trong admin xuất hiện bản ghi mới tại `Leads tư vấn`

## 3) Việc cần làm ngay sau khi nhận bàn giao

### Bước 1 — Đưa theme lên WordPress
- copy hoặc symlink thư mục:
  - `wp-content/themes/aitrongcay`
- vào admin → Appearance → Themes
- activate `Ai trồng cây`

### Bước 2 — Cấu hình nền
- Settings → Permalinks → `Post name`
- Save
- Settings → Reading
  - Homepage displays → `A static page`
  - Front page → `Trang chủ`

### Bước 3 — Seed page native
Tạo hoặc import các page sau:
- `cach-hoat-dong`
- `cho-que`
- `an-toan-thuc-pham`
- `chuyen-nha-nong`
- `faq`
- `dang-ky-tu-van`
- `onboarding`
- `dang-nhap`
- `portal`

Tham khảo:
- `docs/wp-cli-seed.md`
- `docs/starter-content/README.md`

### Bước 4 — Tạo menu
Tạo ít nhất menu `Primary Menu` và gán location `primary`.

### Bước 5 — Test route
Test toàn bộ:
- `/`
- `/cach-hoat-dong/`
- `/cho-que/`
- `/an-toan-thuc-pham/`
- `/chuyen-nha-nong/`
- `/faq/`
- `/dang-ky-tu-van/`
- `/onboarding/`
- `/dang-nhap/`
- `/portal/`
- `/portal/dashboard/`
- `/portal/webcam/`
- `/portal/tinh-trang-vuon/`
- `/portal/nhat-ky-cham-soc/`
- `/portal/chat-luong-an-toan/`
- `/portal/tro-ly-ai/`
- `/portal/kho-nong-cu/`

### Bước 6 — Test funnel thật
- mở `/dang-ky-tu-van/`
- gửi thử form với họ tên + số điện thoại
- xác nhận quay về đúng trang với `consultation_status=success`
- vào admin kiểm tra có bản ghi mới trong `Leads tư vấn`

## 4) Checklist nghiệm thu cuối

### A. Public site
- [ ] homepage hiển thị đúng
- [ ] menu desktop/mobile hoạt động
- [ ] CTA dẫn đúng tới `dang-ky-tu-van`, `dang-nhap`, `portal`
- [ ] starter content public đã lên đúng page

### B. Portal shell
- [ ] `/portal/` mở được
- [ ] các route con của portal mở được
- [ ] sidebar active đúng route hiện tại

### C. Form/funnel
- [ ] form `Đăng ký tư vấn` submit thành công
- [ ] validation họ tên + số điện thoại hoạt động
- [ ] admin có lead mới
- [ ] notice thành công/lỗi hiển thị đúng

### D. WordPress behavior
- [ ] native page ưu tiên hơn virtual fallback khi cùng slug
- [ ] save permalinks xong route không gãy
- [ ] title page đổi đúng theo route

## 5) Còn ngoài phạm vi hướng 1

Các việc dưới đây **không phải blocker của hướng 1**:
- nối lead sang CRM/Zalo/email thực chiến
- auth thật cho portal
- data model thật cho garden/camera/care log
- plugin riêng cho portal/custom tables
- live production hardening

## 6) Nếu muốn đi tiếp ngay sau hướng 1

Thứ tự khuyến nghị:
1. nối `Leads tư vấn` sang CRM/email/Zalo
2. chuẩn hóa auth + customer account thật
3. tách portal ra plugin/service layer
4. bắt đầu data model custom tables
