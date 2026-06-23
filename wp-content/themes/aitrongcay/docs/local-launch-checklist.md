# Local launch checklist — Ai trồng cây WordPress theme

Mục tiêu: đưa theme `aitrongcay` lên một site WordPress local và kiểm tra nhanh xem đã đạt trạng thái **chạy được tương đối hoàn chỉnh** hay chưa.

## A. Chuẩn bị

- Có một site WordPress local chạy được
- Đã copy hoặc symlink thư mục theme vào:
  - `wp-content/themes/aitrongcay`
- Đã activate theme **Ai trồng cây**

## B. Seed tối thiểu

Có 2 mode chạy:

### Mode 1 — Demo nhanh bằng virtual fallback
Không cần tạo page đầy đủ trong admin ngay.

Chỉ cần:
- activate theme
- vào site để kiểm tra các route virtual đã hoạt động

### Mode 2 — WordPress native hơn
Tạo các page thật theo slug chuẩn:
- `cach-hoat-dong`
- `cho-que`
- `an-toan-thuc-pham`
- `chuyen-nha-nong`
- `faq`
- `dang-ky-tu-van`
- `onboarding`
- `dang-nhap`
- `portal`

Nếu dùng WP-CLI, xem:
- `docs/wp-cli-seed.md`

## C. Cấu hình nên bật

### 1) Static homepage
- Settings → Reading
- `Homepage displays` → `A static page`
- `Front page` → `Trang chủ`

### 2) Permalinks
- Settings → Permalinks
- Chọn `Post name`
- Save lại 1 lần để chắc rewrite rules được flush

### 3) Menu
Tạo ít nhất menu `Primary Menu` và gán vào location `primary`.

Nếu chưa gán menu thật, theme vẫn có fallback để demo không gãy.

## D. Checklist test route

## 1) Public pages
Các URL dưới đây phải mở được, không 404:
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

## 2) Portal routes
Các URL dưới đây phải mở được, không 404:
- `/portal/dashboard/`
- `/portal/webcam/`
- `/portal/tinh-trang-vuon/`
- `/portal/nhat-ky-cham-soc/`
- `/portal/chat-luong-an-toan/`
- `/portal/tro-ly-ai/`
- `/portal/kho-nong-cu/`

## 3) UI behavior cần kiểm tra
- menu desktop hiển thị đúng
- menu mobile mở được
- nav active đổi đúng theo route
- CTA về `Đăng ký tư vấn`, `Đăng nhập`, `Portal` hoạt động
- sidebar portal highlight đúng màn đang xem
- form `Đăng ký tư vấn` gửi xong quay về đúng trang với notice thành công hoặc lỗi hợp lệ
- trong admin xuất hiện bản ghi mới tại post type `Leads tư vấn`

## 4) WordPress-native behavior cần kiểm tra
- nếu đã tạo page thật đúng slug, theme **không bị đè** bởi virtual fallback
- nếu chưa tạo page thật, virtual route vẫn render bình thường
- title trình duyệt đổi đúng theo từng route

## E. Tiêu chí coi là qua checkpoint hướng 1

Theme được coi là đã qua checkpoint "WordPress chạy được hoàn chỉnh hơn" khi đạt đủ:
- public routes chạy ổn
- portal routes chạy ổn
- theme active không lỗi rõ ràng
- page native và virtual fallback cùng tồn tại không đá nhau
- menu/CTA điều hướng xuyên site mượt
- đã có starter content native đầu tiên cho ít nhất các page: `cach-hoat-dong`, `cho-que`, `an-toan-thuc-pham`, `dang-ky-tu-van`, `chuyen-nha-nong`, `faq`
- funnel công khai đã gom về `dang-ky-tu-van` với vòng đầu ngắn gọn, không hỏi `Quy mô gia đình` hay `Gói quan tâm`

## F. Bước tiếp theo sau checklist này

Khi checklist local đã ổn, nên làm tiếp theo thứ tự:
1. seed nội dung page native trong WordPress
2. nối form `Đăng ký tư vấn` sang CRM/email/Zalo nếu cần workflow thực chiến hơn
3. tách dữ liệu portal ra plugin / custom tables
4. chuẩn hóa auth và customer portal thật
