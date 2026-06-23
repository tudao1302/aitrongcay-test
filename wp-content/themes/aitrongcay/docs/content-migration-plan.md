# Content migration plan — từ static HTML sang WordPress native

Mục tiêu của pass này là chuyển dần site từ trạng thái **theme có fallback** sang **WordPress native content** mà không làm gãy route đang chạy.

## Nguyên tắc

- Route phải ổn định trước, rồi mới thay nội dung dần.
- Nếu page native đã có, ưu tiên page native.
- Nếu page native chưa sẵn sàng, giữ virtual fallback để không chặn demo.

## Ưu tiên migrate theo thứ tự

### 1) Nhóm ảnh hưởng chuyển đổi cao
- `cho-que`
- `cach-hoat-dong`
- `an-toan-thuc-pham`
- `dang-ky-tu-van`

Lý do:
- đây là nhóm ảnh hưởng nhiều nhất tới niềm tin + chuyển đổi
- nên chuyển sớm sang page native / block content / ACF hoặc pattern thật
- riêng `dang-ky-tu-van` là điểm gom funnel, nên phải đi native sớm để gắn form thật mà không phá CTA toàn site

### 2) Nhóm trust + storytelling
- `chuyen-nha-nong`
- `faq`

### 3) Nhóm funnel / activation
- `dang-ky-tu-van`
- `dang-nhap`
- `onboarding`

### 4) Nhóm portal shell
- giữ tạm trong theme ở giai đoạn này
- chỉ tách sang plugin/app layer khi auth + data model rõ hơn

## Cách migrate an toàn

### Cách A — page content native
Phù hợp với:
- FAQ
- Chuyện nhà nông
- một phần Cách hoạt động

### Cách B — template + editable fields
Phù hợp với:
- Chợ quê
- An toàn thực phẩm
- Đăng ký tư vấn

### Cách C — portal shell giữ trong theme tạm thời
Phù hợp với:
- dashboard
- webcam
- care log
- quality & safety
- AI gardener
- kho nông cụ

## Definition of done cho từng page

Một page được coi là migrate xong khi:
- route không đổi
- title/meta hợp lý
- header/footer/theme style đồng bộ
- nội dung không còn phụ thuộc file HTML cũ
- sửa nội dung được ngay trong WordPress hoặc qua field rõ ràng

## Gợi ý owner-review

Khi cần duyệt nhanh, nên review theo block:
1. homepage
2. Chợ quê
3. Cách hoạt động
4. An toàn thực phẩm
5. đăng ký tư vấn
6. portal shell
